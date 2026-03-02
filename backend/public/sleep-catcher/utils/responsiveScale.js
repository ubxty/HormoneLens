/**
 * responsiveScale.js — Viewport scaling utility for Hormone Defense
 */
const ResponsiveScale = {
    BASE_WIDTH: 800,
    BASE_HEIGHT: 600,

    getGameSize() {
        const w = window.innerWidth;
        const h = window.innerHeight;
        const aspectRatio = w / h;

        if (aspectRatio >= 1) {
            // Landscape / desktop
            return {
                width: Math.min(800, w),
                height: Math.min(600, h)
            };
        } else {
            // Portrait mobile — use more screen by matching aspect ratio
            return {
                width: Math.min(600, w),
                height: Math.min(900, h)
            };
        }
    },

    applyResize(game) {
        const size = this.getGameSize();
        game.scale.resize(size.width, size.height);
    },

    isMobile() {
        return /Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
            || ('ontouchstart' in window)
            || (navigator.maxTouchPoints > 0);
    },

    isPortrait() {
        return window.innerHeight > window.innerWidth;
    },

    getScale(game) {
        return Math.min(
            game.scale.width / this.BASE_WIDTH,
            game.scale.height / this.BASE_HEIGHT
        );
    }
};
