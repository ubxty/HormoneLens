import React, { useMemo } from 'react';

/**
 * AssistantCharacter2D — lightweight CSS-animated avatar.
 * Drop-in replacement for the heavy Three.js AssistantCharacter.
 * Same props: { isSpeaking, animationState }.
 * Zero FBX downloads (~0 bytes vs ~31 MB).
 */

const ANIM_STYLES = {
    idle: {
        body: 'ac2d-idle',
        hands: '',
    },
    wave: {
        body: 'ac2d-wave',
        hands: 'ac2d-hand-wave',
    },
    clap: {
        body: 'ac2d-clap',
        hands: 'ac2d-hand-clap',
    },
    spin: {
        body: 'ac2d-spin',
        hands: '',
    },
};

export default function AssistantCharacter2D({ isSpeaking = false, animationState = 'idle' }) {
    const anim = ANIM_STYLES[animationState] || ANIM_STYLES.idle;

    const speakClass = isSpeaking ? 'ac2d-speaking' : '';

    return (
        <div className="ac2d-root" style={{ width: '100%', height: '100%', display: 'flex', alignItems: 'center', justifyContent: 'center', position: 'relative' }}>
            <style>{cssText}</style>
            <div className={`ac2d-scene ${anim.body} ${speakClass}`}>
                {/* Shadow */}
                <div className="ac2d-shadow" />

                {/* Body group */}
                <div className="ac2d-body-group">
                    {/* Head */}
                    <div className="ac2d-head">
                        {/* Eyes */}
                        <div className="ac2d-eyes">
                            <div className="ac2d-eye ac2d-eye-l">
                                <div className="ac2d-pupil" />
                            </div>
                            <div className="ac2d-eye ac2d-eye-r">
                                <div className="ac2d-pupil" />
                            </div>
                        </div>
                        {/* Mouth */}
                        <div className={`ac2d-mouth ${isSpeaking ? 'ac2d-mouth-talk' : ''}`} />
                    </div>

                    {/* Torso */}
                    <div className="ac2d-torso">
                        {/* Left arm */}
                        <div className={`ac2d-arm ac2d-arm-l ${anim.hands}`} />
                        {/* Right arm */}
                        <div className={`ac2d-arm ac2d-arm-r ${anim.hands}`} />
                    </div>

                    {/* Legs */}
                    <div className="ac2d-legs">
                        <div className="ac2d-leg ac2d-leg-l" />
                        <div className="ac2d-leg ac2d-leg-r" />
                    </div>
                </div>

                {/* Decorative floating particles */}
                <div className="ac2d-particle ac2d-p1" />
                <div className="ac2d-particle ac2d-p2" />
                <div className="ac2d-particle ac2d-p3" />
            </div>
        </div>
    );
}

const cssText = `
/* ── AC2D — Lightweight animated avatar ── */
.ac2d-root { user-select: none; }

.ac2d-scene {
    position: relative;
    width: 200px;
    height: 340px;
}

/* ── Shadow ── */
.ac2d-shadow {
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 18px;
    background: radial-gradient(ellipse, rgba(124,58,237,0.2) 0%, transparent 70%);
    border-radius: 50%;
    animation: ac2d-shadow-pulse 2s ease-in-out infinite;
}

/* ── Body group — idle breathing ── */
.ac2d-body-group {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    animation: ac2d-breathe 3s ease-in-out infinite;
}

/* ── Head ── */
.ac2d-head {
    width: 72px;
    height: 72px;
    background: linear-gradient(145deg, #ede9fe, #ddd6fe);
    border-radius: 50%;
    position: relative;
    z-index: 2;
    box-shadow: 0 4px 20px rgba(124,58,237,0.15), inset 0 -3px 8px rgba(124,58,237,0.08);
    border: 2.5px solid rgba(139,92,246,0.25);
}

.ac2d-eyes {
    position: absolute;
    top: 26px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 14px;
}

.ac2d-eye {
    width: 10px;
    height: 10px;
    background: #4c1d95;
    border-radius: 50%;
    position: relative;
    animation: ac2d-blink 4s ease-in-out infinite;
}
.ac2d-eye-l { animation-delay: 0s; }
.ac2d-eye-r { animation-delay: 0.05s; }

.ac2d-pupil {
    width: 4px;
    height: 4px;
    background: white;
    border-radius: 50%;
    position: absolute;
    top: 2px;
    right: 2px;
}

.ac2d-mouth {
    position: absolute;
    bottom: 16px;
    left: 50%;
    transform: translateX(-50%);
    width: 14px;
    height: 6px;
    background: #7c3aed;
    border-radius: 0 0 8px 8px;
    transition: all 0.15s ease;
}

.ac2d-mouth-talk {
    animation: ac2d-talk 0.3s ease-in-out infinite alternate;
}

/* ── Torso ── */
.ac2d-torso {
    width: 56px;
    height: 70px;
    background: linear-gradient(180deg, #7c3aed 0%, #6d28d9 100%);
    border-radius: 18px 18px 12px 12px;
    position: relative;
    margin-top: -8px;
    z-index: 1;
    box-shadow: 0 4px 16px rgba(109,40,217,0.2);
}

/* ── Arms ── */
.ac2d-arm {
    position: absolute;
    width: 16px;
    height: 50px;
    background: linear-gradient(180deg, #8b5cf6, #7c3aed);
    border-radius: 10px;
    top: 4px;
    transform-origin: top center;
    transition: transform 0.4s cubic-bezier(.4,0,.2,1);
}
.ac2d-arm-l { left: -14px; animation: ac2d-arm-idle-l 3s ease-in-out infinite; }
.ac2d-arm-r { right: -14px; animation: ac2d-arm-idle-r 3s ease-in-out infinite; }

/* ── Legs ── */
.ac2d-legs {
    display: flex;
    gap: 6px;
    margin-top: -2px;
}
.ac2d-leg {
    width: 18px;
    height: 55px;
    background: linear-gradient(180deg, #6d28d9, #5b21b6);
    border-radius: 8px 8px 10px 10px;
}

/* ── Floating particles ── */
.ac2d-particle {
    position: absolute;
    border-radius: 50%;
    opacity: 0.5;
    pointer-events: none;
}
.ac2d-p1 {
    width: 8px; height: 8px;
    background: #a78bfa;
    top: 20%; left: 15%;
    animation: ac2d-float 4s ease-in-out infinite;
}
.ac2d-p2 {
    width: 6px; height: 6px;
    background: #c4b5fd;
    top: 40%; right: 12%;
    animation: ac2d-float 5s ease-in-out infinite 1s;
}
.ac2d-p3 {
    width: 10px; height: 10px;
    background: #ddd6fe;
    top: 65%; left: 10%;
    animation: ac2d-float 6s ease-in-out infinite 2s;
}

/* ══════════ Animations ══════════ */

/* Idle */
@keyframes ac2d-breathe {
    0%, 100% { transform: translateX(-50%) translateY(0); }
    50% { transform: translateX(-50%) translateY(-4px); }
}

@keyframes ac2d-shadow-pulse {
    0%, 100% { transform: translateX(-50%) scale(1); opacity: 0.6; }
    50% { transform: translateX(-50%) scale(0.85); opacity: 0.4; }
}

@keyframes ac2d-arm-idle-l {
    0%, 100% { transform: rotate(4deg); }
    50% { transform: rotate(-2deg); }
}
@keyframes ac2d-arm-idle-r {
    0%, 100% { transform: rotate(-4deg); }
    50% { transform: rotate(2deg); }
}

/* Blink */
@keyframes ac2d-blink {
    0%, 42%, 44%, 100% { transform: scaleY(1); }
    43% { transform: scaleY(0.1); }
}

/* Talk */
@keyframes ac2d-talk {
    0% { height: 4px; width: 12px; border-radius: 0 0 6px 6px; }
    100% { height: 10px; width: 16px; border-radius: 50%; }
}

/* Float particles */
@keyframes ac2d-float {
    0%, 100% { transform: translateY(0) scale(1); opacity: 0.4; }
    50% { transform: translateY(-16px) scale(1.15); opacity: 0.7; }
}

/* Speaking body sway */
.ac2d-speaking .ac2d-body-group {
    animation: ac2d-speak-sway 1.5s ease-in-out infinite;
}
@keyframes ac2d-speak-sway {
    0%, 100% { transform: translateX(-50%) translateY(0) rotate(0deg); }
    25% { transform: translateX(-50%) translateY(-3px) rotate(-1deg); }
    75% { transform: translateX(-50%) translateY(-3px) rotate(1deg); }
}

/* Wave animation */
.ac2d-wave .ac2d-body-group {
    animation: ac2d-breathe 3s ease-in-out infinite;
}
.ac2d-hand-wave.ac2d-arm-r {
    animation: ac2d-wave-anim 0.6s ease-in-out infinite alternate !important;
}
@keyframes ac2d-wave-anim {
    0% { transform: rotate(-60deg); }
    100% { transform: rotate(-90deg); }
}
.ac2d-hand-wave.ac2d-arm-l {
    animation: ac2d-arm-idle-l 3s ease-in-out infinite !important;
}

/* Clap animation */
.ac2d-hand-clap.ac2d-arm-l {
    animation: ac2d-clap-l 0.4s ease-in-out infinite alternate !important;
}
.ac2d-hand-clap.ac2d-arm-r {
    animation: ac2d-clap-r 0.4s ease-in-out infinite alternate !important;
}
@keyframes ac2d-clap-l {
    0% { transform: rotate(30deg); }
    100% { transform: rotate(55deg); }
}
@keyframes ac2d-clap-r {
    0% { transform: rotate(-30deg); }
    100% { transform: rotate(-55deg); }
}

/* Spin */
.ac2d-spin .ac2d-body-group {
    animation: ac2d-spin-anim 1s cubic-bezier(.4,0,.2,1) infinite;
}
@keyframes ac2d-spin-anim {
    0% { transform: translateX(-50%) rotateY(0deg); }
    100% { transform: translateX(-50%) rotateY(360deg); }
}
`;
