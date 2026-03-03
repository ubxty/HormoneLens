/**
 * GameScene.js — Core spaceship combat gameplay
 * Pilot ship, destroy hormone disruptors, collect power-ups
 */

/* ── Hormone educational facts ─────────────────────── */
const HORMONE_FACTS = {
    'enemy_cortisol': [
        'Cortisol is the "stress hormone" — it rises during physical and emotional stress.',
        'Chronic high cortisol leads to weight gain, especially around the abdomen.',
        'Cortisol peaks in the morning to help you wake up — it follows a circadian rhythm.',
        'High cortisol worsens insulin resistance, a key driver of PCOS.',
        'Elevated cortisol at night disrupts sleep quality and recovery.',
        'Deep breathing and meditation can reduce cortisol levels by up to 25%.'
    ],
    'enemy_insulin': [
        'Insulin helps cells absorb glucose — too much causes insulin resistance.',
        'About 70% of women with PCOS have some degree of insulin resistance.',
        'High insulin signals the ovaries to produce excess androgens.',
        'Managing insulin through diet significantly improves PCOS symptoms.',
        'Refined carbs and sugars spike insulin — whole grains are a better choice.',
        'Exercise increases insulin sensitivity for up to 48 hours after a session.'
    ],
    'enemy_estrogen': [
        'Estrogen dominance disrupts the balance of other reproductive hormones.',
        'Environmental toxins (xenoestrogens) can mimic estrogen in the body.',
        'Estrogen regulates the menstrual cycle and supports bone health.',
        'In PCOS, the estrogen-progesterone balance is often disrupted.',
        'Fiber-rich foods help the body eliminate excess estrogen naturally.',
        'Excess body fat increases estrogen production through the enzyme aromatase.'
    ],
    'enemy_androgen': [
        'Excess androgens cause acne, hair loss, and excess hair growth in PCOS.',
        'Androgens include testosterone, DHEA, and androstenedione.',
        'High androgen levels interfere with ovulation in women with PCOS.',
        'Regular exercise helps lower androgen levels naturally.',
        'Spearmint tea has been shown to reduce androgen levels in clinical studies.',
        'Androgens are produced by both the ovaries and adrenal glands.'
    ]
};

const ENEMY_INFO = {
    'enemy_cortisol': { name: 'CORTISOL', color: '#FF6644', colorInt: 0xFF6644 },
    'enemy_insulin':  { name: 'INSULIN',  color: '#FFCC44', colorInt: 0xFFCC44 },
    'enemy_estrogen': { name: 'ESTROGEN', color: '#66FF66', colorInt: 0x66FF66 },
    'enemy_androgen': { name: 'ANDROGEN', color: '#FF88CC', colorInt: 0xFF88CC }
};

const POWERUP_INFO = {
    'estrogen': { name: 'BALANCE', color: '#FF6B9D' },
    'shield':   { name: 'SHIELD',  color: '#00DDFF' },
    'rapid':    { name: 'RAPID \u26A1', color: '#AA66FF' },
    'heal':     { name: 'HEAL \u2764', color: '#44FF88' },
    'magnet':   { name: 'MAGNET', color: '#FFAA33' },
    'slowtime': { name: 'SLOW \u23F1', color: '#88DDFF' },
    'double':   { name: '2X SCORE', color: '#FFDD44' }
};

/* ── Periodic hormone tips (not tied to kills) ─── */
const HORMONE_TIPS = [
    { icon: '\uD83E\uDDE0', text: 'Your brain controls hormone release through the hypothalamus and pituitary gland.', color: '#00DDFF' },
    { icon: '\uD83D\uDCA4', text: '7-9 hours of sleep helps regulate cortisol, insulin, and growth hormone overnight.', color: '#AA88FF' },
    { icon: '\uD83C\uDF4E', text: 'A balanced diet with fiber, protein, and healthy fats stabilizes insulin levels.', color: '#66FF88' },
    { icon: '\uD83C\uDFC3', text: '30 minutes of exercise reduces cortisol and improves insulin sensitivity.', color: '#FF6B9D' },
    { icon: '\uD83D\uDCA7', text: 'Staying hydrated supports thyroid function and overall hormone transport.', color: '#00CCFF' },
    { icon: '\u2764', text: 'Chronic stress disrupts nearly every hormone in the body \u2014 manage it daily.', color: '#FF4466' },
    { icon: '\uD83C\uDF1F', text: 'Vitamin D acts like a hormone and regulates over 200 genes in the body.', color: '#FFDD44' },
    { icon: '\uD83E\uDD66', text: 'Cruciferous vegetables (broccoli, kale) help the liver metabolize excess estrogen.', color: '#66CC66' },
    { icon: '\u2615', text: 'Too much caffeine raises cortisol. Limit to 1-2 cups of coffee per day.', color: '#DDAA66' },
    { icon: '\uD83E\uDDD8', text: 'Yoga and deep breathing activate the parasympathetic system, lowering cortisol.', color: '#BB88FF' },
    { icon: '\uD83C\uDF1B', text: 'Melatonin, the sleep hormone, is disrupted by blue light at night.', color: '#8888FF' },
    { icon: '\uD83E\uDD5A', text: 'Omega-3 fatty acids reduce inflammation and support hormonal balance.', color: '#FFBB44' }
];

class GameScene extends Phaser.Scene {
    constructor() {
        super({ key: 'GameScene' });
    }

    init() {
        this.score = 0;
        this.hormoneBalance = 100;
        this.hp = 100;
        this.maxHp = 100;
        this.gameTime = 0;
        this.gameDuration = 60000;
        this.gameOver = false;
        this.isShielded = false;
        this.isRapidFire = false;
        this.fireRate = 320;
        this.lastFired = 0;
        this.waveNum = 1;
        this.killCount = 0;
        this.isMobile = ResponsiveScale.isMobile();
        this.shipSpeed = 280;
        this.factKillCounter = 0;
        this.factPaused = false;
        this.factOverlay = null;
        this.factPanel = null;
        this.factElements = [];
        this.isMagnet = false;
        this.isSlowTime = false;
        this.isDoubleScore = false;
        this.tipIndex = 0;
        this.tipShownTimes = [];
    }

    create() {
        const { width, height } = this.scale;
        this.cameras.main.fadeIn(400);

        this._createBackground(width, height);
        this._createPlayer(width, height);
        this._createGroups();
        this._createControls(width, height);
        this._createTimers();
        this._createAudio();
        this._createEngineTrail();
        this._setupVisibilityHandling();

        this.scale.on('resize', this._onResize, this);
    }

    /* ── Background ────────────────────────────────── */
    _createBackground(w, h) {
        this.bgSpace = this.add.image(w / 2, h / 2, 'bg_space').setDisplaySize(w, h).setScrollFactor(0);
        this.starsFar = this.add.tileSprite(w / 2, h / 2, w, h, 'bg_stars_far').setScrollFactor(0).setAlpha(0.5);
        this.starsNear = this.add.tileSprite(w / 2, h / 2, w, h, 'bg_stars_near').setScrollFactor(0).setAlpha(0.5);
        this.nebulaBg = this.add.tileSprite(w / 2, h / 2, w, h, 'bg_nebula').setScrollFactor(0).setAlpha(0.2);

        // Planet deco
        this.bgPlanet = this.add.image(w * 0.85, h * 0.12, 'bg_planet')
            .setScrollFactor(0).setAlpha(0.35).setScale(0.7);
        this.tweens.add({
            targets: this.bgPlanet,
            y: this.bgPlanet.y + 8,
            duration: 6000,
            yoyo: true,
            repeat: -1,
            ease: 'Sine.easeInOut'
        });
    }

    /* ── Player Ship ───────────────────────────────── */
    _createPlayer(w, h) {
        this.player = this.physics.add.sprite(w * 0.15, h / 2, 'ship_f0');
        this.player.setCollideWorldBounds(true);
        this.player.setDepth(10);
        this.player.setSize(40, 30);
        this.player.setOffset(8, 9);

        // Ship animation — engine flicker
        this.anims.create({
            key: 'fly',
            frames: [
                { key: 'ship_f0' },
                { key: 'ship_f1' },
                { key: 'ship_f2' },
                { key: 'ship_f3' }
            ],
            frameRate: 10,
            repeat: -1
        });
        this.player.play('fly');
    }

    /* ── Groups ────────────────────────────────────── */
    _createGroups() {
        this.playerBullets = this.physics.add.group({ allowGravity: false, maxSize: 40 });
        this.enemyBullets = this.physics.add.group({ allowGravity: false, maxSize: 30 });
        this.enemies = this.physics.add.group({ allowGravity: false });
        this.powerUps = this.physics.add.group({ allowGravity: false });

        // Player bullets -> enemies
        this.physics.add.overlap(this.playerBullets, this.enemies, this._bulletHitEnemy, null, this);
        // Enemy bullets -> player
        this.physics.add.overlap(this.player, this.enemyBullets, this._enemyBulletHitPlayer, null, this);
        // Enemies -> player (collision)
        this.physics.add.overlap(this.player, this.enemies, this._enemyCollidePlayer, null, this);
        // Player -> power-ups
        this.physics.add.overlap(this.player, this.powerUps, this._collectPowerUp, null, this);
    }

    /* ── Controls ──────────────────────────────────── */
    _createControls(w, h) {
        // Ensure canvas has focus so keyboard events reach Phaser
        this.game.canvas.setAttribute('tabindex', '1');
        this.game.canvas.style.outline = 'none';
        this.game.canvas.focus();

        this.cursors = this.input.keyboard.createCursorKeys();
        this.wasd = {
            up: this.input.keyboard.addKey(Phaser.Input.Keyboard.KeyCodes.W),
            down: this.input.keyboard.addKey(Phaser.Input.Keyboard.KeyCodes.S),
            left: this.input.keyboard.addKey(Phaser.Input.Keyboard.KeyCodes.A),
            right: this.input.keyboard.addKey(Phaser.Input.Keyboard.KeyCodes.D)
        };
        this.spaceKey = this.input.keyboard.addKey(Phaser.Input.Keyboard.KeyCodes.SPACE);

        // Capture these keys so browser doesn't handle them
        this.input.keyboard.addCapture([
            Phaser.Input.Keyboard.KeyCodes.SPACE,
            Phaser.Input.Keyboard.KeyCodes.UP,
            Phaser.Input.Keyboard.KeyCodes.DOWN,
            Phaser.Input.Keyboard.KeyCodes.LEFT,
            Phaser.Input.Keyboard.KeyCodes.RIGHT
        ]);

        // ESC → exit game back to MenuScene
        this.input.keyboard.once('keydown-ESC', () => this._exitToMenu());

        // Mobile: pointer drag to move, auto fire
        this.pointerDown = false;
        this.pointerTarget = { x: this.player.x, y: this.player.y };

        this.input.on('pointerdown', (pointer) => {
            this.pointerDown = true;
            this.pointerTarget.x = pointer.x;
            this.pointerTarget.y = pointer.y;
        });
        this.input.on('pointermove', (pointer) => {
            if (pointer.isDown) {
                this.pointerDown = true;
                this.pointerTarget.x = pointer.x;
                this.pointerTarget.y = pointer.y;
            }
        });
        this.input.on('pointerup', () => {
            this.pointerDown = false;
        });
    }

    /* ── Exit to Menu ────────────────────────────── */
    _exitToMenu() {
        // Notify parent page so it can unlock scroll
        try { window.parent.postMessage({ type: 'GAME_EXIT' }, '*'); } catch (e) {}
        this.cameras.main.fadeOut(300, 4, 5, 24);
        this.time.delayedCall(300, () => {
            this.scene.stop('UIScene');
            this.scene.stop('GameScene');
            this.scene.start('MenuScene');
        });
    }

    /* ── Timers ────────────────────────────────────── */
    _createTimers() {
        // Enemy wave spawner
        this.enemyTimer = this.time.addEvent({
            delay: 1600,
            callback: this._spawnEnemyWave,
            callbackScope: this,
            loop: true
        });

        // Power-up spawn
        this.powerUpTimer = this.time.addEvent({
            delay: 6000,
            callback: this._spawnPowerUp,
            callbackScope: this,
            loop: true
        });

        // Wave escalation
        this.waveTimer = this.time.addEvent({
            delay: 12000,
            callback: () => {
                this.waveNum++;
            },
            callbackScope: this,
            loop: true
        });

        // Periodic hormone tip banner (every 10s, non-blocking)
        this.tipTimer = this.time.addEvent({
            delay: 10000,
            callback: this._showHormoneTipBanner,
            callbackScope: this,
            loop: true
        });
    }

    /* ── Firing ────────────────────────────────────── */
    _fire(time) {
        const rate = this.isRapidFire ? this.fireRate * 0.4 : this.fireRate;
        if (time < this.lastFired + rate) return;
        this.lastFired = time;

        const key = this.isRapidFire ? 'bullet_charged' : 'bullet_player';
        const bullet = this.playerBullets.get(this.player.x + 28, this.player.y, key);
        if (!bullet) return;

        bullet.setTexture(key);
        bullet.setActive(true).setVisible(true).setDepth(8);
        if (bullet.body) {
            bullet.body.enable = true;
            bullet.body.setSize(bullet.width, bullet.height);
        }
        bullet.setVelocityX(this.isRapidFire ? 550 : 480);
        bullet.setVelocityY(0);

        this._playSound(this.isRapidFire ? 'snd_charged' : 'snd_shoot');
    }

    /* ── Enemy Spawning ────────────────────────────── */
    _spawnEnemyWave() {
        if (this.gameOver) return;
        const { width, height } = this.scale;
        const count = Math.min(1 + Math.floor(this.waveNum / 2), 4);

        for (let i = 0; i < count; i++) {
            this.time.delayedCall(i * 300, () => this._spawnEnemy(width, height));
        }
    }

    _spawnEnemy(w, h) {
        if (this.gameOver) return;
        const enemyTypes = [
            { key: 'enemy_cortisol', hp: 2, speed: 80, points: 15, shoots: false },
            { key: 'enemy_insulin', hp: 1, speed: 120, points: 10, shoots: false },
            { key: 'enemy_estrogen', hp: 2, speed: 90, points: 20, shoots: true }
        ];
        // Androgen boss every 3 waves
        if (this.waveNum >= 3 && Phaser.Math.Between(1, 6) === 1) {
            enemyTypes.push({ key: 'enemy_androgen', hp: 5, speed: 50, points: 50, shoots: true });
        }

        const type = Phaser.Utils.Array.GetRandom(enemyTypes);
        const y = Phaser.Math.Between(40, h - 40);

        const enemy = this.enemies.create(w + 30, y, type.key);
        const speedMult = this.isSlowTime ? 0.4 : 1;
        enemy.setVelocityX((-type.speed - this.waveNum * 8) * speedMult);
        enemy.setData('hp', type.hp);
        enemy.setData('maxHp', type.hp);
        enemy.setData('points', type.points);
        enemy.setData('shoots', type.shoots);
        enemy.setData('type', type.key);
        enemy.setDepth(6);
        enemy.body.setSize(enemy.width * 0.75, enemy.height * 0.75);

        // Scale up on mobile for visibility
        if (this.isMobile) enemy.setScale(1.25);

        // Wobble
        this.tweens.add({
            targets: enemy,
            y: y + Phaser.Math.Between(-20, 20),
            duration: Phaser.Math.Between(1500, 2500),
            yoyo: true,
            repeat: -1,
            ease: 'Sine.easeInOut'
        });

        // Fade in
        enemy.setAlpha(0);
        this.tweens.add({ targets: enemy, alpha: 1, duration: 250 });

        // Enemy shooting
        if (type.shoots) {
            const shootDelay = Phaser.Math.Between(1500, 3000);
            this.time.addEvent({
                delay: shootDelay,
                callback: () => this._enemyFire(enemy),
                callbackScope: this,
                loop: true
            });
        }

        // Self-destruct off screen
        this.time.delayedCall(10000, () => { if (enemy.active) enemy.destroy(); });
    }

    _enemyFire(enemy) {
        if (!enemy.active || this.gameOver) return;
        // Only fire if enemy is ahead (to the right) of player — no attacks from behind
        if (enemy.x < this.player.x) return;

        const bullet = this.enemyBullets.get(enemy.x - 10, enemy.y, 'bullet_enemy');
        if (!bullet) return;

        bullet.setTexture('bullet_enemy');
        bullet.setActive(true).setVisible(true).setDepth(5);
        if (bullet.body) {
            bullet.body.enable = true;
            bullet.body.setSize(bullet.width, bullet.height);
        }

        // Fire ONLY toward the left — never behind the ship
        const speed = 180 + this.waveNum * 10;
        const angleToPlayer = Phaser.Math.Angle.Between(enemy.x, enemy.y, this.player.x, this.player.y);
        // Clamp to fire leftward only (between ~135° and ~225° or π/2 to 3π/2 — i.e. leftward)
        bullet.setVelocity(
            -speed,
            Math.sin(angleToPlayer) * speed * 0.5
        );
    }

    /* ── Power-Up Spawning ─────────────────────────── */
    _spawnPowerUp() {
        if (this.gameOver) return;
        const { width, height } = this.scale;
        const types = [
            { key: 'pw_estrogen', type: 'estrogen' },
            { key: 'pw_shield', type: 'shield' },
            { key: 'pw_rapid', type: 'rapid' },
            { key: 'pw_heal', type: 'heal' },
            { key: 'pw_magnet', type: 'magnet' },
            { key: 'pw_slowtime', type: 'slowtime' },
            { key: 'pw_double', type: 'double' }
        ];
        const chosen = Phaser.Utils.Array.GetRandom(types);
        const y = Phaser.Math.Between(50, height - 50);

        const pw = this.powerUps.create(width + 20, y, chosen.key);
        pw.setVelocityX(-100);
        pw.setDepth(7);
        pw.setData('type', chosen.type);
        pw.setScale(this.isMobile ? 1.5 : 1.2);

        // Glow pulse
        this.tweens.add({
            targets: pw,
            scaleX: 1.2,
            scaleY: 1.2,
            alpha: 0.7,
            duration: 600,
            yoyo: true,
            repeat: -1,
            ease: 'Sine.easeInOut'
        });

        this.time.delayedCall(8000, () => { if (pw.active) pw.destroy(); });
    }

    /* ── Collision Handlers ────────────────────────── */
    _bulletHitEnemy(bullet, enemy) {
        // Deactivate bullet
        bullet.setActive(false);
        bullet.setVisible(false);
        bullet.body.enable = false;

        // Damage enemy
        let hp = enemy.getData('hp') - (this.isRapidFire ? 2 : 1);
        enemy.setData('hp', hp);

        // Hit flash
        enemy.setTint(0xFFFFFF);
        this.time.delayedCall(60, () => { if (enemy.active) enemy.clearTint(); });

        if (hp <= 0) {
            this._destroyEnemy(enemy);
        } else {
            // Damage spark
            this._spawnHitSparks(enemy.x, enemy.y, 0xFFAA44, 4);
        }
    }

    _destroyEnemy(enemy) {
        const basePoints = enemy.getData('points');
        const points = this.isDoubleScore ? basePoints * 2 : basePoints;
        const enemyType = enemy.getData('type');
        this.score += points;
        this.killCount++;
        this.hormoneBalance = Math.min(100, this.hormoneBalance + 2);

        // Explosion VFX
        this._spawnExplosion(enemy.x, enemy.y, enemyType);

        this._playSound('snd_explode');
        this._showFloatingText('+' + points, enemy.x, enemy.y, '#00FFCC');

        // Screen shake for big enemies
        if (enemy.getData('maxHp') >= 4) {
            this.cameras.main.shake(200, 0.015);
        }

        enemy.destroy();

        // Hormone fact popup every 5th kill
        this.factKillCounter++;
        if (this.factKillCounter % 5 === 0 && !this.factPaused) {
            this._showHormoneFact(enemyType);
        }

        this.events.emit('scoreUpdate', {
            score: this.score,
            balance: this.hormoneBalance,
            hp: this.hp
        });
    }

    _enemyBulletHitPlayer(player, bullet) {
        bullet.setActive(false);
        bullet.setVisible(false);
        bullet.body.enable = false;

        if (this.isShielded) {
            this._spawnHitSparks(player.x, player.y, 0x00FFCC, 6);
            return;
        }

        this._damagePlayer(12);
    }

    _enemyCollidePlayer(player, enemy) {
        if (this.isShielded) {
            this._destroyEnemy(enemy);
            return;
        }
        this._damagePlayer(20);
        enemy.destroy();
        this._spawnExplosion(enemy.x, enemy.y, 'enemy_cortisol');
        this._playSound('snd_explode');
    }

    _damagePlayer(amount) {
        this.hp = Math.max(0, this.hp - amount);
        this.hormoneBalance = Math.max(0, this.hormoneBalance - amount * 0.5);
        this._playSound('snd_hit');

        // Red flash
        const flash = this.add.image(this.scale.width / 2, this.scale.height / 2, 'red_flash')
            .setDisplaySize(this.scale.width, this.scale.height)
            .setScrollFactor(0).setDepth(100).setAlpha(0.5);
        this.tweens.add({
            targets: flash,
            alpha: 0,
            duration: 250,
            onComplete: () => flash.destroy()
        });

        // Shake
        this.cameras.main.shake(150, 0.01);

        // Player flash
        this.tweens.add({
            targets: this.player,
            alpha: 0.3,
            duration: 80,
            yoyo: true,
            repeat: 3
        });

        this.events.emit('scoreUpdate', {
            score: this.score,
            balance: this.hormoneBalance,
            hp: this.hp
        });
    }

    _collectPowerUp(player, item) {
        if (this.gameOver) return;
        const type = item.getData('type');
        item.destroy();

        this._playSound('snd_collect');
        this._spawnHitSparks(item.x, item.y, 0x00FFCC, 10);

        switch (type) {
            case 'estrogen':
                this.score += 20;
                this.hormoneBalance = Math.min(100, this.hormoneBalance + 10);
                this._showFloatingText('+10 Balance', item.x, item.y, '#FF6B9D');
                break;
            case 'shield':
                this._activateShield();
                this._showFloatingText('🛡 Shield!', item.x, item.y, '#00FFCC');
                break;
            case 'rapid':
                this._activateRapidFire();
                this._showFloatingText('⚡ Rapid Fire!', item.x, item.y, '#AA66FF');
                break;
            case 'heal':
                this.hp = Math.min(this.maxHp, this.hp + 25);
                this._showFloatingText('+25 HP', item.x, item.y, '#44FF88');
                break;
            case 'magnet':
                this._activateMagnet();
                this._showFloatingText('\uD83E\uDDF2 Magnet!', item.x, item.y, '#FFAA33');
                break;
            case 'slowtime':
                this._activateSlowTime();
                this._showFloatingText('\u23F1 Slow Time!', item.x, item.y, '#88DDFF');
                break;
            case 'double':
                this._activateDoubleScore();
                this._showFloatingText('\u2728 2X Score!', item.x, item.y, '#FFDD44');
                break;
        }

        this.events.emit('scoreUpdate', {
            score: this.score,
            balance: this.hormoneBalance,
            hp: this.hp
        });
    }

    /* ── Power-Up Effects ──────────────────────────── */
    _activateShield() {
        this.isShielded = true;

        // Shield visual
        if (this.shieldSprite) this.shieldSprite.destroy();
        this.shieldSprite = this.add.image(this.player.x, this.player.y, 'shield_bubble')
            .setDepth(11).setAlpha(0.7);
        this.tweens.add({
            targets: this.shieldSprite,
            alpha: { from: 0.5, to: 0.8 },
            scaleX: { from: 0.95, to: 1.05 },
            scaleY: { from: 0.95, to: 1.05 },
            duration: 500,
            yoyo: true,
            repeat: -1
        });

        this.time.delayedCall(4000, () => {
            this.isShielded = false;
            if (this.shieldSprite) {
                this.tweens.add({
                    targets: this.shieldSprite,
                    alpha: 0,
                    duration: 300,
                    onComplete: () => { if (this.shieldSprite) { this.shieldSprite.destroy(); this.shieldSprite = null; } }
                });
            }
        });
    }

    _activateRapidFire() {
        this.isRapidFire = true;
        this.player.setTint(0xAA66FF);

        this.time.delayedCall(4000, () => {
            this.isRapidFire = false;
            this.player.clearTint();
        });
    }

    _activateMagnet() {
        this.isMagnet = true;
        this.player.setTint(0xFFAA33);

        this.time.delayedCall(5000, () => {
            this.isMagnet = false;
            if (!this.isRapidFire) this.player.clearTint();
        });
    }

    _activateSlowTime() {
        this.isSlowTime = true;

        // Slow all existing enemies
        this.enemies.getChildren().forEach(e => {
            if (e.active) e.body.velocity.x *= 0.4;
        });
        this.enemyBullets.getChildren().forEach(b => {
            if (b.active) { b.body.velocity.x *= 0.5; b.body.velocity.y *= 0.5; }
        });

        // Tint for visual feedback
        this._showSlowEffect();

        this.time.delayedCall(4000, () => {
            this.isSlowTime = false;
        });
    }

    _showSlowEffect() {
        const { width, height } = this.scale;
        const overlay = this.add.rectangle(width / 2, height / 2, width, height, 0x88DDFF, 0.08)
            .setDepth(95).setScrollFactor(0);
        this.tweens.add({
            targets: overlay,
            alpha: 0,
            duration: 4000,
            onComplete: () => overlay.destroy()
        });
    }

    _activateDoubleScore() {
        this.isDoubleScore = true;
        const { width } = this.scale;

        // Show 2X indicator
        const indicator = this.add.text(width / 2, 58, '2X SCORE', {
            fontFamily: '"Segoe UI", system-ui, sans-serif',
            fontSize: '14px',
            color: '#FFDD44',
            fontStyle: 'bold',
            stroke: '#040518',
            strokeThickness: 3
        }).setOrigin(0.5).setDepth(100);

        this.tweens.add({
            targets: indicator,
            alpha: { from: 0.6, to: 1 },
            duration: 400,
            yoyo: true,
            repeat: -1
        });

        this.time.delayedCall(6000, () => {
            this.isDoubleScore = false;
            if (indicator && indicator.active) indicator.destroy();
        });
    }

    /* \u2500\u2500 Non-blocking Hormone Tip Banner \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500 */
    _showHormoneTipBanner() {
        if (this.gameOver || this.factPaused) return;
        const { width } = this.scale;
        const s = Math.max(ResponsiveScale.getScale(this.game), 0.6);
        const tip = HORMONE_TIPS[this.tipIndex % HORMONE_TIPS.length];
        this.tipIndex++;

        // Slide-in banner at top (does NOT pause the game)
        const bannerH = 44;
        const bg = this.add.rectangle(width / 2, -bannerH / 2, width, bannerH, 0x0A0C2A, 0.88)
            .setDepth(180).setScrollFactor(0);
        const txt = this.add.text(width / 2, -bannerH / 2, tip.icon + '  ' + tip.text, {
            fontFamily: '"Segoe UI", system-ui, sans-serif',
            fontSize: Math.round(11 * s) + 'px',
            color: tip.color,
            align: 'center',
            wordWrap: { width: width - 30 }
        }).setOrigin(0.5).setDepth(181).setScrollFactor(0);

        // Slide in
        this.tweens.add({ targets: [bg, txt], y: '+='.concat(bannerH / 2 + 60), duration: 350, ease: 'Back.easeOut' });
        // Slide out after 4s
        this.time.delayedCall(4500, () => {
            this.tweens.add({
                targets: [bg, txt],
                y: -bannerH,
                alpha: 0,
                duration: 300,
                ease: 'Cubic.easeIn',
                onComplete: () => { bg.destroy(); txt.destroy(); }
            });
        });
    }

    /* ── Hormone Fact Popup ────────────────────────── */
    _showHormoneFact(enemyType) {
        this.factPaused = true;
        this.physics.pause();
        if (this.enemyTimer) this.enemyTimer.paused = true;
        if (this.powerUpTimer) this.powerUpTimer.paused = true;
        if (this.waveTimer) this.waveTimer.paused = true;
        if (this.tipTimer) this.tipTimer.paused = true;

        const { width, height } = this.scale;
        const s = Math.max(ResponsiveScale.getScale(this.game), 0.6);

        const info = ENEMY_INFO[enemyType] || ENEMY_INFO['enemy_cortisol'];
        const facts = HORMONE_FACTS[enemyType] || HORMONE_FACTS['enemy_cortisol'];
        const fact = Phaser.Utils.Array.GetRandom(facts);

        // Dark overlay
        this.factOverlay = this.add.rectangle(width / 2, height / 2, width, height, 0x000000, 0.55)
            .setDepth(200).setInteractive();

        // Panel
        const panelW = Math.min(380, width * 0.88);
        const panelH = Math.min(240, height * 0.45);
        const px = width / 2 - panelW / 2;
        const py = height / 2 - panelH / 2;

        this.factPanel = this.add.graphics().setDepth(201);
        this.factPanel.fillStyle(0x0A0C2A, 0.94);
        this.factPanel.fillRoundedRect(px, py, panelW, panelH, 16);
        this.factPanel.lineStyle(2, info.colorInt, 0.6);
        this.factPanel.strokeRoundedRect(px, py, panelW, panelH, 16);
        // Top accent glow
        this.factPanel.fillStyle(info.colorInt, 0.04);
        this.factPanel.fillRoundedRect(px + 2, py + 2, panelW - 4, panelH * 0.3, 14);

        this.factElements = [];

        // Header
        this.factElements.push(this.add.text(width / 2, py + 28, '\uD83E\uDDEC ' + info.name + ' FACT', {
            fontFamily: '"Segoe UI", system-ui, sans-serif',
            fontSize: Math.round(18 * s) + 'px',
            color: info.color,
            fontStyle: 'bold'
        }).setOrigin(0.5).setDepth(202));

        // Divider
        const div = this.add.graphics().setDepth(202);
        div.lineStyle(1, info.colorInt, 0.3);
        div.lineBetween(px + 24, py + 50, px + panelW - 24, py + 50);
        this.factElements.push(div);

        // Fact text
        this.factElements.push(this.add.text(width / 2, py + panelH / 2 + 8, fact, {
            fontFamily: '"Segoe UI", system-ui, sans-serif',
            fontSize: Math.round(13 * s) + 'px',
            color: '#CCDDEE',
            align: 'center',
            wordWrap: { width: panelW - 48 },
            lineSpacing: 5
        }).setOrigin(0.5).setDepth(202));

        // Continue prompt
        const promptMsg = this.isMobile ? '\u25B8 Tap to continue' : '\u25B8 Press SPACE to continue';
        const prompt = this.add.text(width / 2, py + panelH - 28, promptMsg, {
            fontFamily: '"Segoe UI", system-ui, sans-serif',
            fontSize: Math.round(11 * s) + 'px',
            color: '#00FFCC'
        }).setOrigin(0.5).setDepth(202).setAlpha(0.7);
        this.factElements.push(prompt);

        this.tweens.add({
            targets: prompt,
            alpha: { from: 0.4, to: 0.95 },
            duration: 600,
            yoyo: true,
            repeat: -1
        });

        // Dismiss listeners
        this.input.keyboard.once('keydown-SPACE', () => this._dismissFact());
        this.factOverlay.once('pointerdown', () => this._dismissFact());
    }

    _dismissFact() {
        if (!this.factPaused) return;

        // Destroy UI immediately
        if (this.factOverlay) { this.factOverlay.destroy(); this.factOverlay = null; }
        if (this.factPanel) { this.factPanel.destroy(); this.factPanel = null; }
        this.factElements.forEach(el => { if (el && el.destroy) el.destroy(); });
        this.factElements = [];

        // Resume on next frame to avoid input-lag stutter
        this.time.delayedCall(1, () => {
            this.factPaused = false;
            this.physics.resume();
            if (this.enemyTimer) this.enemyTimer.paused = false;
            if (this.powerUpTimer) this.powerUpTimer.paused = false;
            if (this.waveTimer) this.waveTimer.paused = false;
            if (this.tipTimer) this.tipTimer.paused = false;
        });
    }

    /* ── VFX ───────────────────────────────────────── */
    _spawnExplosion(x, y, type) {
        // Frag particles
        if (this.textures.exists('particle_frag')) {
            const tintColors = {
                'enemy_cortisol': [0xFF4444, 0xFF6644, 0xFFAA44],
                'enemy_insulin': [0xFFAA00, 0xFFCC44, 0xFFFF88],
                'enemy_estrogen': [0x44FF44, 0x88FF88, 0x66FF66],
                'enemy_androgen': [0xFF66CC, 0xFF88DD, 0xFFAAEE]
            };
            const tints = tintColors[type] || [0xFF6644, 0xFFAA44, 0xFFFFFF];

            const emitter = this.add.particles(x, y, 'particle_frag', {
                speed: { min: 60, max: 200 },
                angle: { min: 0, max: 360 },
                scale: { start: 1, end: 0 },
                alpha: { start: 1, end: 0 },
                tint: tints,
                lifespan: 500,
                quantity: 12,
                emitting: false
            });
            emitter.setDepth(20);
            emitter.explode(12);
            this.time.delayedCall(600, () => emitter.destroy());
        }

        // Smoke puff
        if (this.textures.exists('particle_smoke')) {
            const smokeE = this.add.particles(x, y, 'particle_smoke', {
                speed: { min: 10, max: 40 },
                scale: { start: 0.5, end: 1.5 },
                alpha: { start: 0.4, end: 0 },
                lifespan: 600,
                quantity: 4,
                emitting: false
            });
            smokeE.setDepth(19);
            smokeE.explode(4);
            this.time.delayedCall(700, () => smokeE.destroy());
        }

        // Flash ring
        if (this.textures.exists('particle_ring')) {
            const ring = this.add.image(x, y, 'particle_ring').setDepth(21).setScale(0.5).setAlpha(0.8);
            this.tweens.add({
                targets: ring,
                scaleX: 2.5,
                scaleY: 2.5,
                alpha: 0,
                duration: 350,
                ease: 'Cubic.easeOut',
                onComplete: () => ring.destroy()
            });
        }
    }

    _spawnHitSparks(x, y, color, count) {
        if (!this.textures.exists('particle_sparkle')) return;
        const emitter = this.add.particles(x, y, 'particle_sparkle', {
            speed: { min: 30, max: 100 },
            scale: { start: 0.5, end: 0 },
            alpha: { start: 1, end: 0 },
            tint: [color],
            lifespan: 350,
            quantity: count,
            emitting: false
        });
        emitter.setDepth(22);
        emitter.explode(count);
        this.time.delayedCall(450, () => emitter.destroy());
    }

    _createEngineTrail() {
        if (!this.textures.exists('particle_trail')) return;
        this.engineTrail = this.add.particles(0, 0, 'particle_trail', {
            follow: this.player,
            followOffset: { x: -28, y: 0 },
            speedX: { min: -60, max: -120 },
            speedY: { min: -8, max: 8 },
            scale: { start: 0.5, end: 0 },
            alpha: { start: 0.6, end: 0 },
            tint: [0x00DDFF, 0x7B5EA7, 0x00FFCC],
            lifespan: 400,
            frequency: 30,
            quantity: 1
        }).setDepth(9);
    }

    _showFloatingText(text, x, y, color) {
        const ft = this.add.text(x, y, text, {
            fontFamily: '"Segoe UI", system-ui, sans-serif',
            fontSize: '15px',
            color: color,
            fontStyle: 'bold',
            stroke: '#040518',
            strokeThickness: 3
        }).setOrigin(0.5).setDepth(90);

        this.tweens.add({
            targets: ft,
            y: y - 35,
            alpha: 0,
            duration: 700,
            ease: 'Cubic.easeOut',
            onComplete: () => ft.destroy()
        });
    }

    /* ── Audio ─────────────────────────────────────── */
    _createAudio() {
        this.audioCtx = this.game.registry.get('audioCtx');
        this._startAmbientLoop();
    }

    _startAmbientLoop() {
        if (!this.audioCtx) return;
        const buffer = this.game.registry.get('snd_ambient');
        if (!buffer) return;
        try {
            if (this.audioCtx.state === 'suspended') this.audioCtx.resume();
            this.ambientSource = this.audioCtx.createBufferSource();
            this.ambientSource.buffer = buffer;
            this.ambientSource.loop = true;
            const gain = this.audioCtx.createGain();
            gain.gain.value = 0.2;
            this.ambientSource.connect(gain);
            gain.connect(this.audioCtx.destination);
            this.ambientSource.start(0);
        } catch (e) { /* audio context may not be ready */ }
    }

    _playSound(key) {
        if (!this.audioCtx) return;
        const buffer = this.game.registry.get(key);
        if (!buffer) return;
        try {
            if (this.audioCtx.state === 'suspended') this.audioCtx.resume();
            const source = this.audioCtx.createBufferSource();
            source.buffer = buffer;
            const gain = this.audioCtx.createGain();
            gain.gain.value = 0.2;
            source.connect(gain);
            gain.connect(this.audioCtx.destination);
            source.start(0);
        } catch (e) { /* silently fail */ }
    }

    _stopAmbient() {
        try { if (this.ambientSource) { this.ambientSource.stop(); this.ambientSource = null; } } catch (e) {}
    }

    /* ── Visibility Handling ───────────────────────── */
    _setupVisibilityHandling() {
        this._visHandler = () => {
            if (document.hidden) { this.scene.pause(); this.scene.pause('UIScene'); }
            else { this.scene.resume(); this.scene.resume('UIScene'); }
        };
        document.addEventListener('visibilitychange', this._visHandler);

        if (typeof IntersectionObserver !== 'undefined') {
            const canvas = this.game.canvas;
            this._observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (!entry.isIntersecting) { this.scene.pause(); this.scene.pause('UIScene'); }
                    else { this.scene.resume(); this.scene.resume('UIScene'); }
                });
            }, { threshold: 0.3 });
            this._observer.observe(canvas);
        }
    }

    /* ── Resize ────────────────────────────────────── */
    _onResize(gameSize) {
        const w = gameSize.width;
        const h = gameSize.height;
        if (this.bgSpace) this.bgSpace.setDisplaySize(w, h);
    }

    /* ── Main Update Loop ──────────────────────────── */
    update(time, delta) {
        if (this.gameOver) return;
        if (this.factPaused) return;

        // Timer
        this.gameTime += delta;
        const progress = Math.min(this.gameTime / this.gameDuration, 1);

        // Difficulty ramp
        const spawnDelay = Math.max(700, 1600 - progress * 800);
        if (this.enemyTimer) this.enemyTimer.delay = spawnDelay;

        // Parallax
        if (this.starsFar) this.starsFar.tilePositionX += 0.3;
        if (this.starsNear) this.starsNear.tilePositionX += 0.7;
        if (this.nebulaBg) this.nebulaBg.tilePositionX += 0.15;

        // Movement — keyboard
        let vx = 0, vy = 0;
        if (this.cursors.left.isDown || this.wasd.left.isDown) vx = -this.shipSpeed;
        if (this.cursors.right.isDown || this.wasd.right.isDown) vx = this.shipSpeed;
        if (this.cursors.up.isDown || this.wasd.up.isDown) vy = -this.shipSpeed;
        if (this.cursors.down.isDown || this.wasd.down.isDown) vy = this.shipSpeed;

        // Movement — touch/pointer
        if (this.pointerDown && this.isMobile) {
            const dx = this.pointerTarget.x - this.player.x;
            const dy = this.pointerTarget.y - this.player.y;
            const dist = Math.sqrt(dx * dx + dy * dy);
            if (dist > 10) {
                vx = (dx / dist) * this.shipSpeed;
                vy = (dy / dist) * this.shipSpeed;
            }
        }

        this.player.setVelocity(vx, vy);

        // Ship tilt based on vy
        this.player.setRotation(Phaser.Math.Clamp(vy * 0.001, -0.15, 0.15));

        // Shield follow
        if (this.shieldSprite && this.shieldSprite.active) {
            this.shieldSprite.setPosition(this.player.x, this.player.y);
        }

        // Firing — keyboard or auto-fire on mobile
        if (this.spaceKey.isDown || (this.isMobile && !this.gameOver)) {
            this._fire(time);
        }

        // Hormone decay
        this.hormoneBalance = Math.max(0, this.hormoneBalance - delta * 0.002);

        // Emit UI update
        this.events.emit('scoreUpdate', {
            score: this.score,
            balance: this.hormoneBalance,
            hp: this.hp,
            progress: progress,
            wave: this.waveNum
        });

        // Magnet: attract nearby power-ups toward ship
        if (this.isMagnet) {
            this.powerUps.getChildren().forEach(pw => {
                if (!pw.active) return;
                const dx = this.player.x - pw.x;
                const dy = this.player.y - pw.y;
                const dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < 250) {
                    const pull = 220 / Math.max(dist, 30);
                    pw.body.velocity.x += dx * pull;
                    pw.body.velocity.y += dy * pull;
                }
            });
        }

        // Cleanup off-screen bullets
        this._cleanupBullets();

        // End conditions
        if (this.hp <= 0 || this.gameTime >= this.gameDuration) {
            this._endGame();
        }
    }

    _cleanupBullets() {
        const w = this.scale.width;
        [this.playerBullets, this.enemyBullets].forEach(group => {
            group.getChildren().forEach(b => {
                if (b.active && (b.x < -20 || b.x > w + 50 || b.y < -20 || b.y > this.scale.height + 20)) {
                    b.setActive(false);
                    b.setVisible(false);
                    b.body.enable = false;
                }
            });
        });
    }

    /* ── End Game ──────────────────────────────────── */
    _endGame() {
        this.gameOver = true;
        this._stopAmbient();

        // Stop all timers
        if (this.enemyTimer) this.enemyTimer.remove();
        if (this.powerUpTimer) this.powerUpTimer.remove();
        if (this.waveTimer) this.waveTimer.remove();
        if (this.tipTimer) this.tipTimer.remove();

        // Slow then freeze enemies
        this.physics.world.timeScale = 3;

        const survived = this.hp > 0;

        if (!survived) {
            // Death: explosion + fade out
            this._spawnExplosion(this.player.x, this.player.y, 'enemy_cortisol');
            this.player.setVisible(false);
            this.cameras.main.fadeOut(800, 4, 5, 24);
            this.time.delayedCall(900, () => this._goToResult(false));
            return;
        }

        // ── Victory: cinematic mothership docking ──
        const { width, height } = this.scale;
        const cx = width / 2;
        const cy = height / 2;

        // Freeze all gameplay
        this.enemies.getChildren().forEach(e => { if (e.active && e.body) e.body.enable = false; });
        this.enemyBullets.getChildren().forEach(b => { if (b.active && b.body) b.body.enable = false; });
        this.powerUps.getChildren().forEach(p => { if (p.active && p.body) p.body.enable = false; });
        if (this.player.body) this.player.body.enable = false;

        // Fade out all enemies/bullets
        this.tweens.add({
            targets: [...this.enemies.getChildren(), ...this.enemyBullets.getChildren(), ...this.powerUps.getChildren()],
            alpha: 0, duration: 800
        });

        // ── Build cinematic mothership (drawn offscreen right) ──
        const msContainer = this.add.container(width + 200, cy).setDepth(60);
        const msG = this.add.graphics();

        // Ambient glow aura (outermost)
        msG.fillStyle(0x00FFCC, 0.04);
        msG.fillEllipse(0, 0, 260, 140);
        msG.fillStyle(0x7B5EA7, 0.06);
        msG.fillEllipse(0, 0, 220, 120);

        // Hull — layered for depth
        msG.fillStyle(0x1A1040, 1);
        msG.fillEllipse(0, 0, 180, 80);
        msG.fillStyle(0x2A1860, 1);
        msG.fillEllipse(0, 2, 160, 68);
        msG.fillStyle(0x3D2580, 0.9);
        msG.fillEllipse(0, 4, 140, 56);

        // Hull detail lines
        msG.lineStyle(1, 0x7B5EA7, 0.3);
        msG.strokeEllipse(0, 0, 170, 74);
        msG.lineStyle(1, 0x00FFCC, 0.15);
        msG.strokeEllipse(0, 2, 150, 62);

        // Engine strip (back)
        msG.fillStyle(0x00DDFF, 0.6);
        msG.fillRoundedRect(70, -6, 20, 12, 4);
        msG.fillStyle(0x00FFCC, 0.4);
        msG.fillRoundedRect(78, -3, 14, 6, 3);

        // Command dome (top, glass)
        msG.fillStyle(0x0A0C2A, 0.9);
        msG.fillEllipse(0, -20, 64, 32);
        msG.fillStyle(0x00DDFF, 0.25);
        msG.fillEllipse(0, -22, 52, 24);
        // Dome reflection
        msG.fillStyle(0xFFFFFF, 0.08);
        msG.fillEllipse(-8, -26, 24, 8);

        // Window lights across hull
        const windowColors = [0x00FFCC, 0x00DDFF, 0xFF6B9D, 0xFFDD44];
        for (let i = -4; i <= 4; i++) {
            msG.fillStyle(windowColors[(i + 4) % windowColors.length], 0.7);
            msG.fillCircle(i * 14, 0, 2.5);
            msG.fillStyle(0xFFFFFF, 0.15);
            msG.fillCircle(i * 14, 0, 1.2);
        }

        // Underside detail
        msG.fillStyle(0x7B5EA7, 0.4);
        msG.fillEllipse(0, 16, 100, 16);
        msG.lineStyle(1, 0x00FFCC, 0.1);
        msG.strokeEllipse(0, 16, 100, 16);

        // Landing lights (bottom)
        msG.fillStyle(0xFF6B9D, 0.5);
        msG.fillCircle(-30, 28, 3);
        msG.fillCircle(30, 28, 3);
        msG.fillStyle(0x00FFCC, 0.5);
        msG.fillCircle(0, 30, 2.5);

        msContainer.add(msG);

        // Tractor beam (separate for animation)
        const beam = this.add.graphics().setDepth(55);
        beam.setPosition(width + 200, cy);
        beam.fillStyle(0x00FFCC, 0.06);
        beam.fillTriangle(-90, -20, -90, 20, -220, 0);
        beam.fillStyle(0x00DDFF, 0.04);
        beam.fillTriangle(-85, -14, -85, 14, -200, 0);
        beam.fillStyle(0xFFFFFF, 0.02);
        beam.fillTriangle(-80, -8, -80, 8, -180, 0);
        beam.setAlpha(0);

        // Mothership running lights pulse
        const runLights = this.add.graphics().setDepth(61);
        runLights.setPosition(width + 200, cy);
        runLights.fillStyle(0x00FFCC, 0.6);
        runLights.fillCircle(-88, 0, 4);
        runLights.fillStyle(0x00DDFF, 0.3);
        runLights.fillCircle(-88, 0, 8);
        this.tweens.add({ targets: runLights, alpha: 0.2, duration: 600, yoyo: true, repeat: -1 });

        // === PHASE 1: Mothership warps in ===
        const targetX = width * 0.72;

        // Camera shake for dramatic entrance
        this.time.delayedCall(300, () => this.cameras.main.shake(400, 0.008));

        // Warp flash
        const warpFlash = this.add.graphics().setDepth(100);
        warpFlash.fillStyle(0xFFFFFF, 0.3);
        warpFlash.fillRect(0, 0, width, height);
        warpFlash.setAlpha(0);
        this.time.delayedCall(200, () => {
            this.tweens.add({ targets: warpFlash, alpha: 1, duration: 150, yoyo: true, onComplete: () => warpFlash.destroy() });
        });

        // Slide mothership in with elastic ease
        this.tweens.add({
            targets: [msContainer, beam, runLights],
            x: targetX,
            duration: 1600,
            ease: 'Cubic.easeOut',
            delay: 400
        });

        // Gentle hover
        this.time.delayedCall(500, () => {
            this.tweens.add({ targets: msContainer, y: cy - 3, duration: 2000, yoyo: true, repeat: -1, ease: 'Sine.easeInOut' });
        });

        // Sparkle particles around mothership
        if (this.textures.exists('particle_sparkle')) {
            this.time.delayedCall(1000, () => {
                this.add.particles(0, 0, 'particle_sparkle', {
                    follow: msContainer,
                    followOffset: { x: 0, y: 0 },
                    speedX: { min: -30, max: 30 },
                    speedY: { min: -20, max: 20 },
                    scale: { start: 0.3, end: 0 },
                    alpha: { start: 0.5, end: 0 },
                    tint: [0x00FFCC, 0x00DDFF, 0x7B5EA7],
                    lifespan: 1500,
                    frequency: 100,
                    quantity: 1
                }).setDepth(59);
            });
        }

        // === PHASE 2: Activate tractor beam ===
        this.time.delayedCall(2200, () => {
            // Beam pulses on
            this.tweens.add({
                targets: beam,
                alpha: 1, duration: 500,
                yoyo: true, hold: 300, repeat: 1,
                onComplete: () => beam.setAlpha(0.7)
            });

            // Beam particles streaming toward ship
            if (this.textures.exists('particle_glow')) {
                this.add.particles(0, 0, 'particle_glow', {
                    x: { min: this.player.x, max: targetX - 80 },
                    y: { min: cy - 15, max: cy + 15 },
                    speedX: { min: 60, max: 140 },
                    scale: { start: 0.2, end: 0 },
                    alpha: { start: 0.6, end: 0 },
                    tint: 0x00FFCC,
                    lifespan: 800,
                    frequency: 50,
                    quantity: 2
                }).setDepth(56);
            }
        });

        // === PHASE 3: Ship flies into mothership ===
        this.time.delayedCall(3200, () => {
            // Ship speeds toward mothership
            this.tweens.add({
                targets: this.player,
                x: targetX - 70,
                y: cy,
                scaleX: 0.5,
                scaleY: 0.5,
                duration: 1200,
                ease: 'Quad.easeIn',
                onComplete: () => {
                    // Ship enters — shrink & vanish
                    this.tweens.add({
                        targets: this.player,
                        x: targetX - 20,
                        scaleX: 0,
                        scaleY: 0,
                        alpha: 0,
                        duration: 400,
                        ease: 'Cubic.easeIn',
                        onComplete: () => {
                            // Dock flash
                            const flash2 = this.add.graphics().setDepth(100);
                            flash2.fillStyle(0x00FFCC, 0.35);
                            flash2.fillRect(0, 0, width, height);
                            this.tweens.add({ targets: flash2, alpha: 0, duration: 500, onComplete: () => flash2.destroy() });

                            // Camera zoom into mothership
                            this.cameras.main.zoomTo(1.15, 800, 'Cubic.easeInOut');

                            // "MISSION COMPLETE" text
                            const completeText = this.add.text(cx, cy + 60, 'MISSION COMPLETE', {
                                fontFamily: '"Segoe UI", system-ui, sans-serif',
                                fontSize: '30px',
                                color: '#00FFCC',
                                fontStyle: 'bold',
                                letterSpacing: 5,
                                stroke: '#040518',
                                strokeThickness: 5
                            }).setOrigin(0.5).setDepth(100).setAlpha(0).setScale(0.5);

                            this.tweens.add({
                                targets: completeText,
                                alpha: 1, scaleX: 1, scaleY: 1, y: cy + 50,
                                duration: 700, ease: 'Back.easeOut'
                            });

                            // Fade to result
                            this.time.delayedCall(1800, () => {
                                this.cameras.main.fadeOut(800, 4, 5, 24);
                                this.time.delayedCall(900, () => this._goToResult(true));
                            });
                        }
                    });
                }
            });
        });
    }

    _goToResult(survived) {
        this.scene.stop('UIScene');
        this.scene.start('ResultScene', {
            score: this.score,
            hormoneBalance: this.hormoneBalance,
            hp: this.hp,
            killCount: this.killCount,
            wavesCleared: this.waveNum,
            survived: survived
        });
    }

    shutdown() {
        this._dismissFact();
        this._stopAmbient();
        this.scale.off('resize', this._onResize, this);
        if (this._visHandler) document.removeEventListener('visibilitychange', this._visHandler);
        if (this._observer) this._observer.disconnect();
    }
}
