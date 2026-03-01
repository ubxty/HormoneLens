/**
 * BootScene.js — Generates all spaceship game textures & sounds procedurally
 * Theme: Hormone Defense — pilot a ship through the body, attack disruptors
 */
class BootScene extends Phaser.Scene {
    constructor() {
        super({ key: 'BootScene' });
    }

    create() {
        this.generateTextures();
        this.generateSounds();
        this.scene.start('PreloadScene');
    }

    generateTextures() {
        this._genShip();
        this._genBackgrounds();
        this._genEnemies();
        this._genProjectiles();
        this._genPowerUps();
        this._genUIElements();
        this._genParticles();
    }

    /* ── Player Spaceship ──────────────────────────── */
    _genShip() {
        const W = 56, H = 48;
        const g = this.make.graphics({ add: false });

        for (let frame = 0; frame < 4; frame++) {
            g.clear();
            const thrustFlicker = Math.sin(frame * Math.PI / 2) * 2;

            // Engine glow
            g.fillStyle(0x00DDFF, 0.15 + frame * 0.05);
            g.fillCircle(8, H / 2, 14 + thrustFlicker);

            // Ship body — sleek arrow
            g.fillStyle(0x7B5EA7, 1);
            g.beginPath();
            g.moveTo(W - 4, H / 2);        // nose
            g.lineTo(14, 6);                // top-back
            g.lineTo(8, H / 2 - 4);        // top indent
            g.lineTo(4, H / 2);             // back center
            g.lineTo(8, H / 2 + 4);        // bottom indent
            g.lineTo(14, H - 6);            // bottom-back
            g.closePath();
            g.fillPath();

            // Hull highlight
            g.fillStyle(0xB8A0D8, 0.6);
            g.beginPath();
            g.moveTo(W - 6, H / 2);
            g.lineTo(20, 12);
            g.lineTo(16, H / 2 - 2);
            g.lineTo(16, H / 2);
            g.closePath();
            g.fillPath();

            // Cockpit glow
            g.fillStyle(0x00FFCC, 0.9);
            g.fillCircle(38, H / 2, 5);
            g.fillStyle(0xFFFFFF, 0.4);
            g.fillCircle(39, H / 2 - 1, 2.5);

            // Wing accents
            g.fillStyle(0xFF6B9D, 0.8);
            g.fillTriangle(18, 8, 26, 4, 22, 12);
            g.fillTriangle(18, H - 8, 26, H - 4, 22, H - 12);

            // Engine thrust
            const thrustLen = 8 + thrustFlicker * 2;
            g.fillStyle(0x00DDFF, 0.7);
            g.fillTriangle(4, H / 2 - 6, 4, H / 2 + 6, 4 - thrustLen, H / 2);
            g.fillStyle(0xFFFFFF, 0.5);
            g.fillTriangle(4, H / 2 - 3, 4, H / 2 + 3, 4 - thrustLen * 0.6, H / 2);

            // Shield ring faint
            g.lineStyle(1, 0x00FFCC, 0.12);
            g.strokeCircle(28, H / 2, 22);

            g.generateTexture('ship_f' + frame, W, H);
        }
        g.destroy();
    }

    /* ── Backgrounds ───────────────────────────────── */
    _genBackgrounds() {
        const W = 800, H = 600;

        // Deep space gradient
        const sky = this.make.graphics({ add: false });
        for (let y = 0; y < H; y++) {
            const t = y / H;
            const r = Math.floor(Phaser.Math.Interpolation.Linear([0x04, 0x0A], t));
            const g2 = Math.floor(Phaser.Math.Interpolation.Linear([0x05, 0x0C], t));
            const b = Math.floor(Phaser.Math.Interpolation.Linear([0x18, 0x2A], t));
            sky.fillStyle(Phaser.Display.Color.GetColor(r, g2, b), 1);
            sky.fillRect(0, y, W, 1);
        }
        sky.generateTexture('bg_space', W, H);
        sky.destroy();

        // Far stars — slow parallax
        const stars1 = this.make.graphics({ add: false });
        for (let i = 0; i < 120; i++) {
            const x = Phaser.Math.Between(0, W * 2);
            const y = Phaser.Math.Between(0, H);
            const size = Phaser.Math.FloatBetween(0.3, 1.5);
            const alpha = Phaser.Math.FloatBetween(0.2, 0.8);
            stars1.fillStyle(0xFFFFFF, alpha);
            stars1.fillCircle(x, y, size);
        }
        stars1.generateTexture('bg_stars_far', W * 2, H);
        stars1.destroy();

        // Near stars — faster parallax, some colored
        const stars2 = this.make.graphics({ add: false });
        const starColors = [0xFFFFFF, 0xCCDDFF, 0xFFCCDD, 0xCCFFEE, 0xFFEECC];
        for (let i = 0; i < 60; i++) {
            const x = Phaser.Math.Between(0, W * 2);
            const y = Phaser.Math.Between(0, H);
            const size = Phaser.Math.FloatBetween(0.8, 2.5);
            const col = Phaser.Utils.Array.GetRandom(starColors);
            stars2.fillStyle(col, Phaser.Math.FloatBetween(0.4, 1));
            stars2.fillCircle(x, y, size);
        }
        stars2.generateTexture('bg_stars_near', W * 2, H);
        stars2.destroy();

        // Nebula layer — colorful gas clouds
        const nebula = this.make.graphics({ add: false });
        const nebulaColors = [
            [0x7B5EA7, 0.06], [0xFF6B9D, 0.04], [0x00DDFF, 0.03],
            [0x5F4B8B, 0.07], [0xE6D6FF, 0.03]
        ];
        for (let i = 0; i < 10; i++) {
            const nc = Phaser.Utils.Array.GetRandom(nebulaColors);
            nebula.fillStyle(nc[0], nc[1]);
            const cx = Phaser.Math.Between(0, W * 2);
            const cy = Phaser.Math.Between(50, H - 50);
            nebula.fillEllipse(cx, cy, Phaser.Math.Between(150, 400), Phaser.Math.Between(80, 200));
        }
        nebula.generateTexture('bg_nebula', W * 2, H);
        nebula.destroy();

        // Planet in distance
        const planet = this.make.graphics({ add: false });
        planet.fillStyle(0x5F4B8B, 0.8);
        planet.fillCircle(50, 50, 45);
        planet.fillStyle(0x7B6EAA, 0.5);
        planet.fillCircle(50, 50, 42);
        // Atmosphere ring
        planet.lineStyle(3, 0x00DDFF, 0.2);
        planet.strokeCircle(50, 50, 48);
        planet.lineStyle(1, 0x00FFCC, 0.15);
        planet.strokeCircle(50, 50, 52);
        // Surface bands
        planet.fillStyle(0x4A3070, 0.4);
        planet.fillEllipse(50, 40, 70, 8);
        planet.fillEllipse(50, 55, 60, 6);
        // Light side
        planet.fillStyle(0xE6D6FF, 0.15);
        planet.fillCircle(38, 38, 30);
        planet.generateTexture('bg_planet', 104, 104);
        planet.destroy();
    }

    /* ── Enemies ───────────────────────────────────── */
    _genEnemies() {
        // Cortisol Drone — stress hormone disruptor (red/orange)
        this._drawTex('enemy_cortisol', 44, 44, (g) => {
            // Outer glow
            g.fillStyle(0xFF4444, 0.1);
            g.fillCircle(22, 22, 22);
            // Body
            g.fillStyle(0xCC3333, 0.9);
            g.fillCircle(22, 22, 14);
            g.fillStyle(0xFF6644, 0.7);
            g.fillCircle(22, 22, 10);
            // Spikes
            for (let i = 0; i < 6; i++) {
                const a = (i / 6) * Math.PI * 2;
                const x1 = 22 + Math.cos(a) * 14;
                const y1 = 22 + Math.sin(a) * 14;
                const x2 = 22 + Math.cos(a) * 22;
                const y2 = 22 + Math.sin(a) * 22;
                g.lineStyle(3, 0xFF4444, 0.8);
                g.lineBetween(x1, y1, x2, y2);
            }
            // Eye
            g.fillStyle(0xFFFF00, 0.9);
            g.fillCircle(22, 20, 4);
            g.fillStyle(0x660000, 1);
            g.fillCircle(22, 20, 2);
        });

        // Insulin Blocker — metabolic disruptor (yellow/amber)
        this._drawTex('enemy_insulin', 40, 40, (g) => {
            g.fillStyle(0xFFAA00, 0.12);
            g.fillCircle(20, 20, 20);
            // Hexagonal body
            g.fillStyle(0xDD8800, 0.9);
            const pts = [];
            for (let i = 0; i < 6; i++) {
                const a = (i / 6) * Math.PI * 2 - Math.PI / 6;
                pts.push({ x: 20 + Math.cos(a) * 14, y: 20 + Math.sin(a) * 14 });
            }
            g.beginPath();
            g.moveTo(pts[0].x, pts[0].y);
            for (let i = 1; i < 6; i++) g.lineTo(pts[i].x, pts[i].y);
            g.closePath();
            g.fillPath();
            g.fillStyle(0xFFCC44, 0.6);
            g.fillCircle(20, 20, 8);
            // Warning symbol
            g.lineStyle(2, 0x660000, 0.8);
            g.lineBetween(20, 13, 20, 22);
            g.fillStyle(0x660000, 0.8);
            g.fillCircle(20, 26, 1.5);
        });

        // Estrogen Disruptor — endocrine disruptor (toxic green)
        this._drawTex('enemy_estrogen', 42, 42, (g) => {
            g.fillStyle(0x44FF44, 0.08);
            g.fillCircle(21, 21, 21);
            // Toxic droplet body
            g.fillStyle(0x228822, 0.85);
            g.fillCircle(21, 24, 13);
            g.fillTriangle(21, 4, 10, 20, 32, 20);
            // Inner glow
            g.fillStyle(0x66FF66, 0.4);
            g.fillCircle(21, 22, 7);
            // Skull-ish face
            g.fillStyle(0x003300, 0.9);
            g.fillCircle(17, 22, 2.5);
            g.fillCircle(25, 22, 2.5);
            g.fillStyle(0x003300, 0.7);
            g.fillRect(18, 27, 6, 1.5);
        });

        // Androgen Raider — large, tough (dark magenta)
        this._drawTex('enemy_androgen', 52, 52, (g) => {
            g.fillStyle(0xAA3388, 0.1);
            g.fillCircle(26, 26, 26);
            // Shield ring
            g.lineStyle(2, 0xFF66CC, 0.5);
            g.strokeCircle(26, 26, 22);
            // Body
            g.fillStyle(0x882266, 0.9);
            g.beginPath();
            g.moveTo(26, 4);
            g.lineTo(48, 20);
            g.lineTo(44, 44);
            g.lineTo(8, 44);
            g.lineTo(4, 20);
            g.closePath();
            g.fillPath();
            g.fillStyle(0xBB4488, 0.5);
            g.fillTriangle(26, 10, 38, 22, 14, 22);
            // Core
            g.fillStyle(0xFF88CC, 0.7);
            g.fillCircle(26, 28, 6);
            g.fillStyle(0xFFFFFF, 0.3);
            g.fillCircle(25, 27, 2.5);
        });
    }

    /* ── Projectiles ───────────────────────────────── */
    _genProjectiles() {
        // Player laser bolt
        this._drawTex('bullet_player', 20, 8, (g) => {
            g.fillStyle(0x00FFCC, 0.3);
            g.fillEllipse(10, 4, 20, 8);
            g.fillStyle(0x00FFCC, 0.8);
            g.fillEllipse(10, 4, 14, 5);
            g.fillStyle(0xFFFFFF, 0.9);
            g.fillEllipse(12, 4, 8, 3);
        });

        // Player charged shot
        this._drawTex('bullet_charged', 28, 12, (g) => {
            g.fillStyle(0xFF6B9D, 0.2);
            g.fillEllipse(14, 6, 28, 12);
            g.fillStyle(0xFF6B9D, 0.7);
            g.fillEllipse(14, 6, 20, 8);
            g.fillStyle(0xFFCCDD, 0.9);
            g.fillEllipse(16, 6, 10, 4);
        });

        // Enemy bullet
        this._drawTex('bullet_enemy', 12, 12, (g) => {
            g.fillStyle(0xFF4444, 0.2);
            g.fillCircle(6, 6, 6);
            g.fillStyle(0xFF6644, 0.8);
            g.fillCircle(6, 6, 4);
            g.fillStyle(0xFFCC44, 0.6);
            g.fillCircle(6, 6, 2);
        });
    }

    /* ── Power-Ups ─────────────────────────────────── */
    _genPowerUps() {
        // Estrogen Orb — balance power (soft pink)
        this._drawTex('pw_estrogen', 28, 28, (g) => {
            g.fillStyle(0xFF6B9D, 0.15);
            g.fillCircle(14, 14, 14);
            g.fillStyle(0xFF6B9D, 0.8);
            g.fillCircle(14, 14, 10);
            g.fillStyle(0xFFCCDD, 0.5);
            g.fillCircle(12, 12, 5);
            // + symbol
            g.lineStyle(2, 0xFFFFFF, 0.8);
            g.lineBetween(14, 9, 14, 19);
            g.lineBetween(9, 14, 19, 14);
        });

        // Progesterone Shield — defense (teal)
        this._drawTex('pw_shield', 28, 28, (g) => {
            g.fillStyle(0x00DDFF, 0.12);
            g.fillCircle(14, 14, 14);
            // Shield shape
            g.fillStyle(0x00CCBB, 0.8);
            g.beginPath();
            g.moveTo(14, 3);
            g.lineTo(25, 8);
            g.lineTo(24, 20);
            g.lineTo(14, 26);
            g.lineTo(4, 20);
            g.lineTo(3, 8);
            g.closePath();
            g.fillPath();
            g.fillStyle(0x66FFEE, 0.4);
            g.beginPath();
            g.moveTo(14, 6);
            g.lineTo(22, 10);
            g.lineTo(14, 14);
            g.lineTo(6, 10);
            g.closePath();
            g.fillPath();
        });

        // Melatonin Boost — rapid fire (purple)
        this._drawTex('pw_rapid', 28, 28, (g) => {
            g.fillStyle(0xAA66FF, 0.12);
            g.fillCircle(14, 14, 14);
            g.fillStyle(0x9944FF, 0.8);
            g.fillCircle(14, 14, 10);
            // Lightning bolt
            g.fillStyle(0xFFFF66, 0.9);
            g.beginPath();
            g.moveTo(16, 4);
            g.lineTo(10, 14);
            g.lineTo(14, 14);
            g.lineTo(12, 24);
            g.lineTo(18, 14);
            g.lineTo(14, 14);
            g.closePath();
            g.fillPath();
        });

        // Thyroid Heal — restore HP (green)
        this._drawTex('pw_heal', 28, 28, (g) => {
            g.fillStyle(0x44FF88, 0.12);
            g.fillCircle(14, 14, 14);
            g.fillStyle(0x22CC66, 0.8);
            g.fillCircle(14, 14, 10);
            // Heart
            g.fillStyle(0xFF6B9D, 0.9);
            g.fillCircle(11, 12, 4);
            g.fillCircle(17, 12, 4);
            g.fillTriangle(7, 13, 21, 13, 14, 22);
        });

        // Magnet — attracts power-ups (gold/orange)
        this._drawTex('pw_magnet', 28, 28, (g) => {
            g.fillStyle(0xFFAA33, 0.12);
            g.fillCircle(14, 14, 14);
            g.fillStyle(0xFF8800, 0.85);
            g.fillCircle(14, 14, 10);
            // U-shape magnet
            g.lineStyle(3, 0xFFFFFF, 0.9);
            g.beginPath();
            g.arc(14, 12, 6, Math.PI, 0, false);
            g.strokePath();
            g.lineStyle(3, 0xFF4444, 0.9);
            g.lineBetween(8, 12, 8, 20);
            g.lineStyle(3, 0x4444FF, 0.9);
            g.lineBetween(20, 12, 20, 20);
        });

        // Slow-Time — slows enemies (ice blue)
        this._drawTex('pw_slowtime', 28, 28, (g) => {
            g.fillStyle(0x88DDFF, 0.12);
            g.fillCircle(14, 14, 14);
            g.fillStyle(0x3399CC, 0.85);
            g.fillCircle(14, 14, 10);
            // Clock face
            g.lineStyle(1.5, 0xFFFFFF, 0.8);
            g.strokeCircle(14, 14, 7);
            // Hands
            g.lineBetween(14, 14, 14, 9);
            g.lineBetween(14, 14, 18, 14);
            // Snowflake dots
            g.fillStyle(0xCCEEFF, 0.7);
            g.fillCircle(14, 5, 1.5);
            g.fillCircle(14, 23, 1.5);
            g.fillCircle(5, 14, 1.5);
            g.fillCircle(23, 14, 1.5);
        });

        // Double Score — 2x points (gold star)
        this._drawTex('pw_double', 28, 28, (g) => {
            g.fillStyle(0xFFDD44, 0.12);
            g.fillCircle(14, 14, 14);
            g.fillStyle(0xEECC00, 0.85);
            g.fillCircle(14, 14, 10);
            // 2X text-like shape
            g.fillStyle(0xFFFFFF, 0.9);
            g.fillRect(9, 11, 4, 2);
            g.fillRect(13, 9, 2, 2);
            g.fillRect(9, 13, 4, 2);
            g.fillRect(9, 15, 2, 2);
            g.fillRect(9, 17, 6, 2);
            // X
            g.lineBetween(17, 10, 22, 18);
            g.lineBetween(22, 10, 17, 18);
            g.lineStyle(2, 0xFFFFFF, 0.9);
            g.lineBetween(17, 10, 22, 18);
            g.lineBetween(22, 10, 17, 18);
        });
    }

    /* ── UI Elements ───────────────────────────────── */
    _genUIElements() {
        // Glass panel
        const panel = this.make.graphics({ add: false });
        panel.fillStyle(0x0A0C2A, 0.7);
        panel.fillRoundedRect(0, 0, 200, 60, 16);
        panel.lineStyle(1, 0x00FFCC, 0.15);
        panel.strokeRoundedRect(0, 0, 200, 60, 16);
        panel.generateTexture('ui_glass_panel', 200, 60);
        panel.destroy();

        // Primary button
        const btn = this.make.graphics({ add: false });
        btn.fillStyle(0x00DDFF, 0.9);
        btn.fillRoundedRect(0, 0, 260, 56, 28);
        btn.fillStyle(0xFFFFFF, 0.15);
        btn.fillRoundedRect(2, 2, 256, 26, 24);
        btn.generateTexture('ui_button', 260, 56);
        btn.destroy();

        // Secondary button
        const btn2 = this.make.graphics({ add: false });
        btn2.fillStyle(0x7B5EA7, 0.9);
        btn2.fillRoundedRect(0, 0, 260, 50, 25);
        btn2.fillStyle(0xFFFFFF, 0.1);
        btn2.fillRoundedRect(2, 2, 256, 22, 22);
        btn2.generateTexture('ui_button_secondary', 260, 50);
        btn2.destroy();

        // Damage flash overlay
        const red = this.make.graphics({ add: false });
        red.fillStyle(0xFF4444, 0.25);
        red.fillRect(0, 0, 800, 600);
        red.generateTexture('red_flash', 800, 600);
        red.destroy();

        // Shield overlay
        this._drawTex('shield_bubble', 72, 64, (g) => {
            g.lineStyle(2, 0x00FFCC, 0.5);
            g.strokeCircle(36, 32, 30);
            g.lineStyle(1, 0x00DDFF, 0.25);
            g.strokeCircle(36, 32, 34);
            g.fillStyle(0x00FFCC, 0.06);
            g.fillCircle(36, 32, 30);
        });
    }

    /* ── Particles ─────────────────────────────────── */
    _genParticles() {
        // Soft glow
        const glow = this.make.graphics({ add: false });
        glow.fillStyle(0xFFFFFF, 0.8);
        glow.fillCircle(8, 8, 8);
        glow.fillStyle(0xFFFFFF, 0.4);
        glow.fillCircle(8, 8, 5);
        glow.generateTexture('particle_glow', 16, 16);
        glow.destroy();

        // Sparkle 4-point star
        const sparkle = this.make.graphics({ add: false });
        sparkle.fillStyle(0x00FFCC, 1);
        sparkle.beginPath();
        sparkle.moveTo(8, 0);
        sparkle.lineTo(10, 6);
        sparkle.lineTo(16, 8);
        sparkle.lineTo(10, 10);
        sparkle.lineTo(8, 16);
        sparkle.lineTo(6, 10);
        sparkle.lineTo(0, 8);
        sparkle.lineTo(6, 6);
        sparkle.closePath();
        sparkle.fillPath();
        sparkle.generateTexture('particle_sparkle', 16, 16);
        sparkle.destroy();

        // Engine trail
        const trail = this.make.graphics({ add: false });
        trail.fillStyle(0x00DDFF, 0.6);
        trail.fillCircle(6, 6, 6);
        trail.fillStyle(0x7B5EA7, 0.3);
        trail.fillCircle(6, 6, 4);
        trail.generateTexture('particle_trail', 12, 12);
        trail.destroy();

        // Explosion fragment
        const frag = this.make.graphics({ add: false });
        frag.fillStyle(0xFF6644, 1);
        frag.fillRect(0, 0, 6, 6);
        frag.generateTexture('particle_frag', 6, 6);
        frag.destroy();

        // Smoke
        const smoke = this.make.graphics({ add: false });
        smoke.fillStyle(0x333344, 0.4);
        smoke.fillCircle(12, 12, 12);
        smoke.generateTexture('particle_smoke', 24, 24);
        smoke.destroy();

        // Ring burst (for explosions)
        const ring = this.make.graphics({ add: false });
        ring.lineStyle(2, 0xFFAA44, 0.8);
        ring.strokeCircle(16, 16, 14);
        ring.lineStyle(1, 0xFFFFFF, 0.4);
        ring.strokeCircle(16, 16, 10);
        ring.generateTexture('particle_ring', 32, 32);
        ring.destroy();

        // Star dust
        const dust = this.make.graphics({ add: false });
        dust.fillStyle(0xE6D6FF, 0.3);
        dust.fillCircle(8, 8, 8);
        dust.generateTexture('particle_dust', 16, 16);
        dust.destroy();
    }

    /* ── Helper ────────────────────────────────────── */
    _drawTex(key, w, h, drawFn) {
        const g = this.make.graphics({ add: false });
        drawFn(g);
        g.generateTexture(key, w, h);
        g.destroy();
    }

    /* ── Sound Generation via Web Audio ────────────── */
    generateSounds() {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        this.game.registry.set('audioCtx', audioCtx);

        // Laser shot — short high sine sweep
        this._genSoundBuffer(audioCtx, 'snd_shoot', 0.12, (ctx, buf) => {
            const data = buf.getChannelData(0);
            for (let i = 0; i < data.length; i++) {
                const t = i / ctx.sampleRate;
                data[i] = Math.sin(2 * Math.PI * (1200 - t * 4000) * t) * Math.exp(-t * 20) * 0.25;
            }
        });

        // Charged shot — deeper, more resonant
        this._genSoundBuffer(audioCtx, 'snd_charged', 0.2, (ctx, buf) => {
            const data = buf.getChannelData(0);
            for (let i = 0; i < data.length; i++) {
                const t = i / ctx.sampleRate;
                data[i] = (Math.sin(2 * Math.PI * 600 * t) * 0.3 +
                    Math.sin(2 * Math.PI * 900 * t) * 0.2) * Math.exp(-t * 8) * 0.3;
            }
        });

        // Power-up collect — ascending chime
        this._genSoundBuffer(audioCtx, 'snd_collect', 0.25, (ctx, buf) => {
            const data = buf.getChannelData(0);
            for (let i = 0; i < data.length; i++) {
                const t = i / ctx.sampleRate;
                data[i] = (Math.sin(2 * Math.PI * (800 + t * 1200) * t) +
                    Math.sin(2 * Math.PI * 1200 * t) * 0.4) * Math.exp(-t * 7) * 0.18;
            }
        });

        // Explosion — noise + low rumble
        this._genSoundBuffer(audioCtx, 'snd_explode', 0.3, (ctx, buf) => {
            const data = buf.getChannelData(0);
            for (let i = 0; i < data.length; i++) {
                const t = i / ctx.sampleRate;
                data[i] = ((Math.random() * 2 - 1) * 0.4 +
                    Math.sin(2 * Math.PI * 80 * t) * 0.3) * Math.exp(-t * 6) * 0.3;
            }
        });

        // Player hit — crunch
        this._genSoundBuffer(audioCtx, 'snd_hit', 0.15, (ctx, buf) => {
            const data = buf.getChannelData(0);
            for (let i = 0; i < data.length; i++) {
                const t = i / ctx.sampleRate;
                data[i] = (Math.random() * 2 - 1) * Math.exp(-t * 14) * 0.3;
            }
        });

        // Ambient space hum
        this._genSoundBuffer(audioCtx, 'snd_ambient', 4.0, (ctx, buf) => {
            const data = buf.getChannelData(0);
            for (let i = 0; i < data.length; i++) {
                const t = i / ctx.sampleRate;
                data[i] = (
                    Math.sin(2 * Math.PI * 55 * t) * 0.025 +
                    Math.sin(2 * Math.PI * 82.5 * t) * 0.02 +
                    Math.sin(2 * Math.PI * 110 * t) * 0.015 +
                    (Math.random() * 2 - 1) * 0.005
                ) * (0.7 + 0.3 * Math.sin(2 * Math.PI * 0.15 * t));
            }
        });
    }

    _genSoundBuffer(audioCtx, key, duration, fillFn) {
        const sampleRate = audioCtx.sampleRate;
        const buffer = audioCtx.createBuffer(1, Math.floor(sampleRate * duration), sampleRate);
        fillFn(audioCtx, buffer);
        this.game.registry.set(key, buffer);
    }
}
