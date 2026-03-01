/**
 * MenuScene.js — Clean cinematic intro with parallax space
 */
class MenuScene extends Phaser.Scene {
    constructor() {
        super({ key: 'MenuScene' });
    }

    create() {
        const { width, height } = this.scale;
        const cx = width / 2;
        const cy = height / 2;
        const s = Math.max(ResponsiveScale.getScale(this.game), 0.55);
        const font = '"Segoe UI", system-ui, sans-serif';

        // Space background
        this.add.image(cx, cy, 'bg_space').setDisplaySize(width, height);
        this.starsFar = this.add.tileSprite(cx, cy, width, height, 'bg_stars_far')
            .setScrollFactor(0).setAlpha(0.6);
        this.starsNear = this.add.tileSprite(cx, cy, width, height, 'bg_stars_near')
            .setScrollFactor(0).setAlpha(0.5);
        this.nebulaBg = this.add.tileSprite(cx, cy, width, height, 'bg_nebula')
            .setScrollFactor(0).setAlpha(0.3);

        // Planet deco
        const planet = this.add.image(width * 0.82, height * 0.12, 'bg_planet')
            .setScale(s * 0.7).setAlpha(0.5);
        this.tweens.add({ targets: planet, y: planet.y + 5, duration: 5000, yoyo: true, repeat: -1, ease: 'Sine.easeInOut' });

        // Dust particles
        if (this.textures.exists('particle_dust')) {
            this.add.particles(0, 0, 'particle_dust', {
                x: { min: 0, max: width }, y: { min: 0, max: height },
                speedX: { min: -8, max: -20 }, speedY: { min: -3, max: 3 },
                scale: { start: 0.3, end: 0 }, alpha: { start: 0.15, end: 0 },
                lifespan: 5000, frequency: 500, quantity: 1
            });
        }

        // ── Glass panel ──
        const panelW = Math.min(500, width * 0.94);
        const panelH = Math.min(540, height * 0.94);
        const panelX = cx - panelW / 2;
        const panelY = cy - panelH / 2;
        const pad = panelW * 0.06;        // inner padding

        const panel = this.add.graphics();
        panel.fillStyle(0x0A0C2A, 0.78);
        panel.fillRoundedRect(panelX, panelY, panelW, panelH, 18);
        panel.lineStyle(1, 0x00FFCC, 0.1);
        panel.strokeRoundedRect(panelX, panelY, panelW, panelH, 18);

        // ── cursor for vertical layout ──
        let yPos = panelY + 22;

        // ── Ship + Title ──
        const ship = this.add.image(cx - 80 * s, yPos + 14, 'ship_f0').setScale(s * 2.2).setAlpha(0);
        this.tweens.add({ targets: ship, alpha: 1, scaleX: s * 2.5, scaleY: s * 2.5, duration: 500, ease: 'Back.easeOut' });
        this.tweens.add({ targets: ship, y: ship.y + 4, duration: 2200, yoyo: true, repeat: -1, ease: 'Sine.easeInOut', delay: 500 });

        this.add.text(cx + 24 * s, yPos, 'HORMONE', {
            fontFamily: font, fontSize: Math.round(30 * s) + 'px',
            color: '#FFFFFF', fontStyle: 'bold', letterSpacing: 3
        }).setOrigin(0.5, 0);
        this.add.text(cx + 24 * s, yPos + 32 * s, 'DEFENSE', {
            fontFamily: font, fontSize: Math.round(22 * s) + 'px',
            color: '#00FFCC', fontStyle: 'bold', letterSpacing: 6
        }).setOrigin(0.5, 0);

        yPos += 68 * s;

        // subtle divider
        this._drawDivider(panel, panelX + pad, yPos, panelW - pad * 2);
        yPos += 14;

        // ── Mission Briefing ──
        this.add.text(cx, yPos, 'MISSION BRIEFING', {
            fontFamily: font, fontSize: Math.round(12 * s) + 'px',
            color: '#00FFCC', fontStyle: 'bold', letterSpacing: 4
        }).setOrigin(0.5, 0);
        yPos += 24 * s;

        const descText =
            'Hormonal disruptors are invading your body.\n' +
            'Pilot your ship, destroy them, and collect\n' +
            'power-ups to stay balanced for 60 seconds.\n\n' +
            'Learn real facts about PCOS along the way.';
        const desc = this.add.text(cx, yPos, descText, {
            fontFamily: font, fontSize: Math.round(13.5 * s) + 'px',
            color: '#C0D0E4', align: 'center', lineSpacing: 6,
            wordWrap: { width: panelW - pad * 2 }
        }).setOrigin(0.5, 0);
        yPos += desc.height + 16;

        // subtle divider
        this._drawDivider(panel, panelX + pad, yPos, panelW - pad * 2);
        yPos += 14;

        // ── THREATS row ──
        this.add.text(panelX + pad, yPos, 'THREATS', {
            fontFamily: font, fontSize: Math.round(10 * s) + 'px',
            color: '#FF6B6B', fontStyle: 'bold', letterSpacing: 2
        });
        yPos += 20 * s;

        const enemies = [
            { tex: 'enemy_cortisol', label: 'Cortisol', color: '#FF6644' },
            { tex: 'enemy_insulin',  label: 'Insulin',  color: '#FFCC44' },
            { tex: 'enemy_estrogen', label: 'Estrogen', color: '#66FF66' },
            { tex: 'enemy_androgen', label: 'Androgen', color: '#FF88CC' }
        ];
        const eGap = (panelW - pad * 2) / enemies.length;
        const eStartX = panelX + pad + eGap / 2;
        enemies.forEach((e, i) => {
            const ex = eStartX + i * eGap;
            this.add.image(ex, yPos, e.tex).setScale(s * 0.45);
            this.add.text(ex, yPos + 18, e.label, {
                fontFamily: font, fontSize: Math.max(9, Math.round(10 * s)) + 'px',
                color: e.color, stroke: '#040518', strokeThickness: 2
            }).setOrigin(0.5, 0);
        });
        yPos += 38 * s;

        // ── POWER-UPS row ──
        this.add.text(panelX + pad, yPos, 'POWER-UPS', {
            fontFamily: font, fontSize: Math.round(10 * s) + 'px',
            color: '#00FFCC', fontStyle: 'bold', letterSpacing: 2
        });
        yPos += 20 * s;

        const pups = [
            { tex: 'pw_shield',   label: 'Shield',  color: '#00DDFF' },
            { tex: 'pw_heal',     label: 'Heal',    color: '#44FF88' },
            { tex: 'pw_rapid',    label: 'Rapid',   color: '#AA66FF' },
            { tex: 'pw_magnet',   label: 'Magnet',  color: '#FFAA33' },
            { tex: 'pw_slowtime', label: 'Slow',    color: '#88DDFF' },
            { tex: 'pw_double',   label: '2X',      color: '#FFDD44' }
        ];
        const pGap = (panelW - pad * 2) / pups.length;
        const pStartX = panelX + pad + pGap / 2;
        pups.forEach((p, i) => {
            const px2 = pStartX + i * pGap;
            this.add.image(px2, yPos, p.tex).setScale(s * 0.55);
            this.add.text(px2, yPos + 18, p.label, {
                fontFamily: font, fontSize: Math.max(9, Math.round(10 * s)) + 'px',
                color: p.color, stroke: '#040518', strokeThickness: 2
            }).setOrigin(0.5, 0);
        });
        yPos += 42 * s;

        // subtle divider
        this._drawDivider(panel, panelX + pad, yPos, panelW - pad * 2);
        yPos += 12;

        // ── Controls hint ──
        const isMob = ResponsiveScale.isMobile();
        const ctrlText = isMob
            ? 'Drag to move  \u2022  Auto-fire'
            : 'Arrow / WASD  \u2022  Space to fire  \u2022  ESC to exit';
        this.add.text(cx, yPos, ctrlText, {
            fontFamily: font, fontSize: Math.round(12 * s) + 'px',
            color: '#97A8C8'
        }).setOrigin(0.5, 0);

        // ── Start button ──
        const btnY = panelY + panelH - 40;
        const startBtn = this.add.image(cx, btnY, 'ui_button').setScale(s).setInteractive({ useHandCursor: true });
        const startLabel = this.add.text(cx, btnY, '\u25B6  LAUNCH', {
            fontFamily: font, fontSize: Math.round(18 * s) + 'px',
            color: '#040518', fontStyle: 'bold'
        }).setOrigin(0.5);

        startBtn.on('pointerover', () => {
            this.tweens.add({ targets: [startBtn, startLabel], scaleX: s * 1.05, scaleY: s * 1.05, duration: 150 });
        });
        startBtn.on('pointerout', () => {
            this.tweens.add({ targets: [startBtn, startLabel], scaleX: s, scaleY: s, duration: 150 });
        });
        startBtn.on('pointerdown', () => {
            this.tweens.add({
                targets: [startBtn, startLabel],
                scaleX: s * 0.95, scaleY: s * 0.95,
                duration: 80, yoyo: true,
                onComplete: () => this._startGame()
            });
        });

        // Keyboard
        this.input.keyboard.once('keydown-SPACE', () => this._startGame());
        this.input.keyboard.once('keydown-ENTER', () => this._startGame());
    }

    /** Draws a subtle horizontal divider line */
    _drawDivider(gfx, x, y, w) {
        gfx.lineStyle(1, 0x00FFCC, 0.08);
        gfx.beginPath();
        gfx.moveTo(x, y);
        gfx.lineTo(x + w, y);
        gfx.strokePath();
    }

    update() {
        if (this.starsFar) this.starsFar.tilePositionX += 0.1;
        if (this.starsNear) this.starsNear.tilePositionX += 0.2;
        if (this.nebulaBg) this.nebulaBg.tilePositionX += 0.05;
    }

    _startGame() {
        this.cameras.main.fadeOut(400, 4, 5, 24);
        this.time.delayedCall(400, () => {
            this.scene.start('GameScene');
            this.scene.start('UIScene');
        });
    }
}
