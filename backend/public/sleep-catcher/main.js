/**
 * main.js — Phaser 3 Game Configuration for Hormone Defense
 */
(function () {
    'use strict';

    const size = ResponsiveScale.getGameSize();

    const config = {
        type: Phaser.AUTO,
        parent: 'game-container',
        width: size.width,
        height: size.height,
        backgroundColor: '#040518',

        scale: {
            mode: Phaser.Scale.FIT,
            autoCenter: Phaser.Scale.CENTER_BOTH,
            min: {
                width: 320,
                height: 480
            },
            max: {
                width: 1200,
                height: 1000
            }
        },

        physics: {
            default: 'arcade',
            arcade: {
                gravity: { y: 0 },
                debug: false
            }
        },

        scene: [
            BootScene,
            PreloadScene,
            MenuScene,
            GameScene,
            UIScene,
            ResultScene
        ],

        render: {
            pixelArt: false,
            antialias: true,
            roundPixels: true
        },

        input: {
            activePointers: 2,
            keyboard: true,
            touch: {
                capture: true
            }
        },

        fps: {
            target: 60,
            forceSetTimeOut: false
        },

        banner: false
    };

    const game = new Phaser.Game(config);

    window.addEventListener('resize', () => {
        const newSize = ResponsiveScale.getGameSize();
        game.scale.resize(newSize.width, newSize.height);
    });
})();
