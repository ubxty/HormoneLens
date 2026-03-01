/**
 * ResultScene.js — Clean mission debrief with stats, education, and CTAs
 */
class ResultScene extends Phaser.Scene {
    constructor() {
        super({ key: 'ResultScene' });
    }

    init(data) {
        this.finalScore = data.score || 0;
        this.hormoneBalance = data.hormoneBalance || 0;
        this.hp = data.hp || 0;
        this.killCount = data.killCount || 0;
        this.wavesCleared = data.wavesCleared || 1;
        this.survived = data.survived !== false;
    }

    create() {
        const { width, height } = this.scale;
        const cx = width / 2;
        const s = Math.max(ResponsiveScale.getScale(this.game), 0.55);
        const font = '"Segoe UI", system-ui, sans-serif';

        this.cameras.main.fadeIn(500);

        // Space bg
        this.add.image(cx, height / 2, 'bg_space').setDisplaySize(width, height);
        if (this.textures.exists('bg_stars_far')) {
            const stars = this.add.tileSprite(cx, height / 2, width, height, 'bg_stars_far').setAlpha(0.4);
            this.tweens.add({ targets: stars, tilePositionX: 100, duration: 20000, repeat: -1 });
        }

        // Glass panel
        const panelW = Math.min(480, width * 0.92);
        const panelH = Math.min(560, height * 0.94);
        const panelX = cx - panelW / 2;
        const panelY = (height - panelH) / 2;
        const pad = panelW * 0.06;

        const gfx = this.add.graphics();
        gfx.fillStyle(0x0A0C2A, 0.8);
        gfx.fillRoundedRect(panelX, panelY, panelW, panelH, 18);
        gfx.lineStyle(1, this.survived ? 0x00FFCC : 0xFF4444, 0.12);
        gfx.strokeRoundedRect(panelX, panelY, panelW, panelH, 18);

        let yPos = panelY + 28;

        // ── Status icon (procedural, no emoji) ──
        const iconR = 22 * s;
        const iconG = this.add.graphics();
        if (this.survived) {
            iconG.fillStyle(0x00FFCC, 0.12);
            iconG.fillCircle(cx, yPos + iconR, iconR + 4);
            iconG.lineStyle(3, 0x00FFCC, 0.9);
            iconG.strokeCircle(cx, yPos + iconR, iconR);
            // checkmark
            iconG.lineStyle(3, 0x00FFCC, 1);
            iconG.beginPath();
            iconG.moveTo(cx - 10 * s, yPos + iconR + 1);
            iconG.lineTo(cx - 2 * s, yPos + iconR + 9 * s);
            iconG.lineTo(cx + 11 * s, yPos + iconR - 8 * s);
            iconG.strokePath();
        } else {
            iconG.fillStyle(0xFF4444, 0.12);
            iconG.fillCircle(cx, yPos + iconR, iconR + 4);
            iconG.lineStyle(3, 0xFF4444, 0.9);
            iconG.strokeCircle(cx, yPos + iconR, iconR);
            // X mark
            iconG.lineStyle(3, 0xFF4444, 1);
            const xOff = 8 * s;
            iconG.beginPath();
            iconG.moveTo(cx - xOff, yPos + iconR - xOff);
            iconG.lineTo(cx + xOff, yPos + iconR + xOff);
            iconG.strokePath();
            iconG.beginPath();
            iconG.moveTo(cx + xOff, yPos + iconR - xOff);
            iconG.lineTo(cx - xOff, yPos + iconR + xOff);
            iconG.strokePath();
        }
        yPos += iconR * 2 + 14;

        // ── Headline ──
        const headline = this.survived ? 'MISSION COMPLETE' : 'MISSION FAILED';
        const headColor = this.survived ? '#00FFCC' : '#FF5555';
        this.add.text(cx, yPos, headline, {
            fontFamily: font, fontSize: Math.round(26 * s) + 'px',
            color: headColor, fontStyle: 'bold', letterSpacing: 3
        }).setOrigin(0.5);
        yPos += 30 * s;

        const subHead = this.survived
            ? 'Hormonal balance defended'
            : 'Disruptors overwhelmed you';
        this.add.text(cx, yPos, subHead, {
            fontFamily: font, fontSize: Math.round(13 * s) + 'px',
            color: '#97A8C8'
        }).setOrigin(0.5);
        yPos += 28 * s;

        // divider
        this._drawDivider(gfx, panelX + pad, yPos, panelW - pad * 2);
        yPos += 14;

        // ── Performance grade ──
        const grade = this._calcGrade();
        this.add.text(cx, yPos, grade.letter, {
            fontFamily: font, fontSize: Math.round(44 * s) + 'px',
            color: grade.color, fontStyle: 'bold'
        }).setOrigin(0.5);
        yPos += 48 * s;

        this.add.text(cx, yPos, grade.label, {
            fontFamily: font, fontSize: Math.round(12 * s) + 'px',
            color: '#97A8C8', fontStyle: 'bold', letterSpacing: 3
        }).setOrigin(0.5);
        yPos += 24 * s;

        // ── Stats row ──
        const statData = [
            { label: 'SCORE', value: '0', color: '#00FFCC', key: 'score' },
            { label: 'KILLS', value: String(this.killCount), color: '#FF6B9D', key: 'kills' },
            { label: 'WAVES', value: String(this.wavesCleared), color: '#AA66FF', key: 'waves' },
            { label: 'BALANCE', value: Math.round(this.hormoneBalance) + '%', color: this.hormoneBalance >= 50 ? '#44DDAA' : '#FF5555', key: 'balance' }
        ];
        const colW = (panelW - pad * 2) / statData.length;
        const statStartX = panelX + pad;

        this.statTexts = {};
        statData.forEach((stat, i) => {
            const sx = statStartX + colW * i + colW / 2;
            // value first (big)
            this.statTexts[stat.key] = this.add.text(sx, yPos, stat.value, {
                fontFamily: font, fontSize: Math.round(24 * s) + 'px',
                color: stat.color, fontStyle: 'bold'
            }).setOrigin(0.5);
            // label below
            this.add.text(sx, yPos + 26 * s, stat.label, {
                fontFamily: font, fontSize: Math.round(9.5 * s) + 'px',
                color: '#7B8EC8', letterSpacing: 1
            }).setOrigin(0.5);

            // vertical separator (except last)
            if (i < statData.length - 1) {
                const sepX = statStartX + colW * (i + 1);
                gfx.lineStyle(1, 0xFFFFFF, 0.05);
                gfx.beginPath();
                gfx.moveTo(sepX, yPos - 10 * s);
                gfx.lineTo(sepX, yPos + 34 * s);
                gfx.strokePath();
            }
        });
        yPos += 54 * s;

        // divider
        this._drawDivider(gfx, panelX + pad, yPos, panelW - pad * 2);
        yPos += 14;

        // ── Fact card (expanded) ──
        const factW = panelW - pad * 2;
        const factX = panelX + pad;

        this.add.text(factX + 12, yPos + 4 * s, 'DID YOU KNOW?', {
            fontFamily: font, fontSize: Math.round(10 * s) + 'px',
            color: '#FF6B9D', fontStyle: 'bold', letterSpacing: 2
        });
        yPos += 22 * s;

        const allFacts = [
            'Chronic stress elevates cortisol, disrupting ovulation and worsening PCOS symptoms.',
            'Insulin resistance affects 70% of PCOS patients, driving excess androgen production.',
            'Estrogen disruptors in plastics and pesticides can interfere with hormonal balance.',
            'Regular exercise can lower insulin resistance and improve hormonal balance significantly.',
            'Sleep deprivation raises cortisol levels and worsens hormonal imbalance.',
            'High androgen levels cause acne, hair thinning, and irregular periods in PCOS.',
            'A balanced diet rich in fiber helps regulate insulin and lower inflammation.',
            'Vitamin D deficiency is common in PCOS and may worsen insulin resistance.'
        ];
        // Pick 3 random unique facts
        const shuffled = Phaser.Utils.Array.Shuffle([...allFacts]);
        const picked = shuffled.slice(0, 3);

        picked.forEach((fact, i) => {
            const bulletColor = ['#00FFCC', '#FF6B9D', '#AA66FF'][i];
            // bullet dot
            const dotG = this.add.graphics();
            dotG.fillStyle(Phaser.Display.Color.HexStringToColor(bulletColor).color, 0.8);
            dotG.fillCircle(factX + 8, yPos + 8 * s, 3);

            this.add.text(factX + 20, yPos, fact, {
                fontFamily: font, fontSize: Math.round(11 * s) + 'px',
                color: '#BCC8E0', lineSpacing: 3,
                wordWrap: { width: factW - 30 }
            });
            yPos += 36 * s;
        });

        yPos += 4 * s;

        // ── Play Again ──
        const replayBtn = this.add.text(cx, yPos, '\u21BB  Play Again', {
            fontFamily: font, fontSize: Math.round(15 * s) + 'px',
            color: '#00FFCC'
        }).setOrigin(0.5).setAlpha(0.5).setInteractive({ useHandCursor: true });
        replayBtn.on('pointerover', () => replayBtn.setAlpha(1));
        replayBtn.on('pointerout', () => replayBtn.setAlpha(0.5));
        replayBtn.on('pointerdown', () => this._replay());

        // Branding
        this.add.text(cx, panelY + panelH - 14, 'HormoneLens', {
            fontFamily: font, fontSize: Math.round(7.5 * s) + 'px',
            color: '#7B5EA7'
        }).setOrigin(0.5).setAlpha(0.2);

        // Sparkle particles on success
        if (this.textures.exists('particle_sparkle') && this.survived) {
            this.add.particles(0, 0, 'particle_sparkle', {
                x: { min: panelX + 20, max: panelX + panelW - 20 },
                y: { min: panelY, max: panelY + 50 },
                speedY: { min: -8, max: -18 },
                scale: { start: 0.25, end: 0 },
                alpha: { start: 0.4, end: 0 },
                tint: [0x00FFCC, 0x00DDFF, 0xFFFFFF],
                lifespan: 2000,
                frequency: 500,
                quantity: 1
            });
        }

        // Keyboard
        this.input.keyboard.once('keydown-SPACE', () => this._replay());
        this.input.keyboard.once('keydown-ENTER', () => this._replay());

        // Animate score counting up
        this.tweens.addCounter({
            from: 0, to: this.finalScore, duration: 1200, ease: 'Cubic.easeOut',
            onUpdate: (tween) => {
                this.statTexts.score.setText(String(Math.floor(tween.getValue())));
            }
        });
    }

    _calcGrade() {
        const sc = this.finalScore;
        if (sc >= 800) return { letter: 'S', color: '#FFD700', label: 'LEGENDARY' };
        if (sc >= 500) return { letter: 'A', color: '#00FFCC', label: 'EXCELLENT' };
        if (sc >= 300) return { letter: 'B', color: '#66BBFF', label: 'GOOD WORK' };
        if (sc >= 150) return { letter: 'C', color: '#FFAA44', label: 'KEEP TRYING' };
        return { letter: 'D', color: '#FF5555', label: 'TRAIN HARDER' };
    }

    _drawDivider(g, x, y, w) {
        g.lineStyle(1, 0x00FFCC, 0.06);
        g.beginPath();
        g.moveTo(x, y);
        g.lineTo(x + w, y);
        g.strokePath();
    }

    _addBtnHover(btn, txt, sc) {
        btn.on('pointerover', () => this.tweens.add({ targets: [btn, txt], scaleX: sc * 1.04, scaleY: sc * 1.04, duration: 120 }));
        btn.on('pointerout', () => this.tweens.add({ targets: [btn, txt], scaleX: sc, scaleY: sc, duration: 120 }));
    }

    _replay() {
        this.cameras.main.fadeOut(300, 4, 5, 24);
        this.time.delayedCall(300, () => {
            this.scene.start('GameScene');
            this.scene.start('UIScene');
        });
    }
}
