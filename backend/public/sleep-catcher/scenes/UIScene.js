/**
 * UIScene.js — Premium HUD overlay: HP bar, hormone meter, score, wave, mission timer
 */
class UIScene extends Phaser.Scene {
    constructor() {
        super({ key: 'UIScene' });
    }

    create() {
        const { width, height } = this.scale;
        const s = ResponsiveScale.getScale(this.game);
        const pad = 14;

        this.score = 0;
        this.balance = 100;
        this.hp = 100;
        this.progress = 0;
        this.wave = 1;

        /* ── Top Left: Score ───────────────────────── */
        const scorePW = Math.min(150, width * 0.3);
        this.add.graphics()
            .fillStyle(0x0A0C2A, 0.55)
            .fillRoundedRect(pad, pad, scorePW, 40, 12)
            .lineStyle(1, 0x00FFCC, 0.1)
            .strokeRoundedRect(pad, pad, scorePW, 40, 12);

        this.add.text(pad + 12, pad + 8, '⊕', {
            fontSize: `${Math.round(16 * s)}px`,
            color: '#00FFCC'
        });

        this.scoreText = this.add.text(pad + 34, pad + 10, '0', {
            fontFamily: '"Segoe UI", system-ui, sans-serif',
            fontSize: `${Math.round(15 * s)}px`,
            color: '#00FFCC',
            fontStyle: 'bold'
        });

        /* ── Top Left: Wave Badge ──────────────────── */
        this.waveText = this.add.text(pad + scorePW + 10, pad + 10, 'W1', {
            fontFamily: '"Segoe UI", system-ui, sans-serif',
            fontSize: `${Math.round(12 * s)}px`,
            color: '#7B5EA7',
            fontStyle: 'bold'
        });

        /* ── Top Right: HP Bar ─────────────────────── */
        const hpW = Math.min(130, width * 0.25);
        const hpX = width - pad - hpW;
        const hpY = pad;

        this.add.graphics()
            .fillStyle(0x0A0C2A, 0.55)
            .fillRoundedRect(hpX - 8, hpY, hpW + 16, 40, 12)
            .lineStyle(1, 0xFF6B9D, 0.1)
            .strokeRoundedRect(hpX - 8, hpY, hpW + 16, 40, 12);

        this.add.text(hpX, hpY + 6, 'HP', {
            fontFamily: '"Segoe UI", system-ui, sans-serif',
            fontSize: `${Math.round(9 * s)}px`,
            color: '#FF6B9D',
            fontStyle: 'bold'
        });

        // HP bar bg
        this.hpBarX = hpX;
        this.hpBarY = hpY + 22;
        this.hpBarW = hpW;
        this.add.graphics()
            .fillStyle(0xFFFFFF, 0.06)
            .fillRoundedRect(this.hpBarX, this.hpBarY, this.hpBarW, 8, 4);

        this.hpBarGraphics = this.add.graphics();
        this._drawHpBar(100);

        this.hpValueText = this.add.text(hpX + hpW, hpY + 5, '100', {
            fontFamily: '"Segoe UI", system-ui, sans-serif',
            fontSize: `${Math.round(13 * s)}px`,
            color: '#FF6B9D',
            fontStyle: 'bold'
        }).setOrigin(1, 0);

        /* ── Top Center: Hormone Balance Meter ─────── */
        this.meterX = width / 2;
        this.meterY = pad + 22;
        this.meterR = 18;

        this.add.text(this.meterX, pad + 4, 'BALANCE', {
            fontFamily: '"Segoe UI", system-ui, sans-serif',
            fontSize: `${Math.round(7 * s)}px`,
            color: '#7B5EA7',
            fontStyle: 'bold',
            letterSpacing: 1
        }).setOrigin(0.5);

        this.meterGfx = this.add.graphics();
        this._drawMeter(100);

        /* ── Bottom: Mission Progress ──────────────── */
        const barW = Math.min(width - pad * 4, 450);
        const barX = (width - barW) / 2;
        const barY = height - pad - 18;

        this.add.graphics()
            .fillStyle(0xFFFFFF, 0.05)
            .fillRoundedRect(barX, barY, barW, 6, 3);

        this.add.text(barX, barY - 12, 'MISSION', {
            fontFamily: '"Segoe UI", system-ui, sans-serif',
            fontSize: `${Math.round(7 * s)}px`,
            color: '#7B5EA7',
            letterSpacing: 1
        });
        this.add.text(barX + barW, barY - 12, 'COMPLETE', {
            fontFamily: '"Segoe UI", system-ui, sans-serif',
            fontSize: `${Math.round(7 * s)}px`,
            color: '#00FFCC',
            alpha: 0.5,
            letterSpacing: 1
        }).setOrigin(1, 0);

        this.progressGfx = this.add.graphics();
        this.progressBarX = barX;
        this.progressBarY = barY;
        this.progressBarW = barW;

        // Ship icon on progress
        this.progressShip = this.add.text(barX, barY - 2, '▸', {
            fontSize: `${Math.round(10 * s)}px`,
            color: '#00FFCC'
        }).setOrigin(0.5, 1);

        /* ── Listen for game events ────────────────── */
        const gameScene = this.scene.get('GameScene');
        gameScene.events.on('scoreUpdate', this._onScoreUpdate, this);

        this.scale.on('resize', this._onResize, this);
    }

    _onScoreUpdate(data) {
        if (data.score !== undefined) this.score = data.score;
        if (data.balance !== undefined) this.balance = data.balance;
        if (data.hp !== undefined) this.hp = data.hp;
        if (data.progress !== undefined) this.progress = data.progress;
        if (data.wave !== undefined) this.wave = data.wave;

        this.scoreText.setText(Math.floor(this.score));
        this.waveText.setText('W' + this.wave);
        this._drawHpBar(this.hp);
        this.hpValueText.setText(Math.round(this.hp));
        this._drawMeter(this.balance);
        this._drawProgress(this.progress);
    }

    _drawHpBar(value) {
        const g = this.hpBarGraphics;
        g.clear();

        const fillW = (value / 100) * this.hpBarW;
        let color;
        if (value >= 60) color = 0x44FF88;
        else if (value >= 30) color = 0xFFAA33;
        else color = 0xFF4444;

        g.fillStyle(color, 0.85);
        g.fillRoundedRect(this.hpBarX, this.hpBarY, Math.max(fillW, 4), 8, 4);
    }

    _drawMeter(value) {
        const g = this.meterGfx;
        g.clear();

        const x = this.meterX;
        const y = this.meterY;
        const r = this.meterR;

        // Ring bg
        g.lineStyle(4, 0xFFFFFF, 0.08);
        g.beginPath();
        g.arc(x, y, r, 0, Math.PI * 2);
        g.strokePath();

        // Colored arc
        let color;
        if (value >= 60) color = 0x00FFCC;
        else if (value >= 30) color = 0xFFAA33;
        else color = 0xFF4444;

        const angle = (value / 100) * Math.PI * 2;
        g.lineStyle(4, color, 0.85);
        g.beginPath();
        g.arc(x, y, r, -Math.PI / 2, -Math.PI / 2 + angle);
        g.strokePath();

        // Center value
        if (this.meterValText) this.meterValText.destroy();
        const s = ResponsiveScale.getScale(this.game);
        this.meterValText = this.add.text(x, y, Math.round(value) + '%', {
            fontFamily: '"Segoe UI", system-ui, sans-serif',
            fontSize: `${Math.round(9 * s)}px`,
            color: '#FFFFFF',
            fontStyle: 'bold'
        }).setOrigin(0.5);
    }

    _drawProgress(progress) {
        const g = this.progressGfx;
        g.clear();
        const fillW = this.progressBarW * Math.min(progress, 1);

        g.fillStyle(0x00DDFF, 0.7);
        g.fillRoundedRect(this.progressBarX, this.progressBarY, Math.max(fillW, 4), 6, 3);

        this.progressShip.setX(this.progressBarX + fillW);
    }

    _onResize() {
        this.scene.restart();
    }

    shutdown() {
        this.scale.off('resize', this._onResize, this);
        const gameScene = this.scene.get('GameScene');
        if (gameScene) gameScene.events.off('scoreUpdate', this._onScoreUpdate, this);
    }
}
