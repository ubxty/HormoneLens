/**
 * PreloadScene.js — Cinematic loading screen with ship reveal
 */
class PreloadScene extends Phaser.Scene {
    constructor() {
        super({ key: 'PreloadScene' });
    }

    create() {
        const { width, height } = this.scale;
        const cx = width / 2;
        const cy = height / 2;

        this.cameras.main.setBackgroundColor('#040518');

        // Starfield dots
        for (let i = 0; i < 40; i++) {
            const star = this.add.circle(
                Phaser.Math.Between(0, width),
                Phaser.Math.Between(0, height),
                Phaser.Math.FloatBetween(0.5, 1.5),
                0xFFFFFF, Phaser.Math.FloatBetween(0.15, 0.5)
            );
            this.tweens.add({
                targets: star,
                alpha: Phaser.Math.FloatBetween(0.05, 0.3),
                duration: Phaser.Math.Between(1000, 3000),
                yoyo: true,
                repeat: -1
            });
        }

        // Ship silhouette
        const ship = this.add.image(cx, cy - 40, 'ship_f0')
            .setScale(2.5).setAlpha(0).setTint(0x00DDFF);
        this.tweens.add({
            targets: ship,
            alpha: 0.7,
            scaleX: 3,
            scaleY: 3,
            duration: 800,
            ease: 'Cubic.easeOut'
        });

        // Title
        const title = this.add.text(cx, cy + 30, 'HORMONE DEFENSE', {
            fontFamily: '"Segoe UI", system-ui, sans-serif',
            fontSize: '24px',
            color: '#00FFCC',
            fontStyle: 'bold',
            letterSpacing: 4
        }).setOrigin(0.5).setAlpha(0);
        this.tweens.add({
            targets: title,
            alpha: 1,
            y: cy + 24,
            duration: 600,
            delay: 200,
            ease: 'Cubic.easeOut'
        });

        // Subtitle
        const sub = this.add.text(cx, cy + 52, 'Initializing defense systems...', {
            fontFamily: '"Segoe UI", system-ui, sans-serif',
            fontSize: '12px',
            color: '#7B5EA7'
        }).setOrigin(0.5).setAlpha(0);
        this.tweens.add({ targets: sub, alpha: 0.7, duration: 400, delay: 400 });

        // Loading bar
        const barW = Math.min(220, width * 0.5);
        const barH = 4;
        const barY = cy + 76;
        this.add.graphics()
            .fillStyle(0xFFFFFF, 0.06)
            .fillRoundedRect(cx - barW / 2, barY, barW, barH, 2);

        const barFill = this.add.graphics();

        let progress = 0;
        this.time.addEvent({
            delay: 16,
            repeat: 60,
            callback: () => {
                progress += 1 / 60;
                if (progress > 1) progress = 1;
                barFill.clear();
                barFill.fillStyle(0x00DDFF, 0.9);
                barFill.fillRoundedRect(cx - barW / 2, barY, barW * progress, barH, 2);

                if (progress >= 1) {
                    this.cameras.main.fadeOut(300, 4, 5, 24);
                    this.time.delayedCall(350, () => this.scene.start('MenuScene'));
                }
            }
        });
    }
}
