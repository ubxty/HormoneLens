/**
 * TourAssistantCharacter2D — lightweight CSS-animated avatar for the tour widget.
 * Drop-in replacement for the heavy Three.js TourAssistantCharacter.
 * Same props: { isSpeaking, animationState }.
 */
import React from 'react';

const ANIM_MAP = {
    idle: 'tac2d-anim-idle',
    wave: 'tac2d-anim-wave',
    clap: 'tac2d-anim-clap',
};

export default function TourAssistantCharacter2D({ isSpeaking = false, animationState = 'idle' }) {
    const animClass = ANIM_MAP[animationState] || ANIM_MAP.idle;
    const speakClass = isSpeaking ? 'tac2d-speaking' : '';

    return (
        <div style={{ width: '100%', height: '100%', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <style>{cssText}</style>
            <div className={`tac2d-char ${animClass} ${speakClass}`}>
                {/* Head */}
                <div className="tac2d-head">
                    <div className="tac2d-eye tac2d-eye-l"><div className="tac2d-pupil" /></div>
                    <div className="tac2d-eye tac2d-eye-r"><div className="tac2d-pupil" /></div>
                    <div className={`tac2d-mouth ${isSpeaking ? 'tac2d-mouth-talk' : ''}`} />
                </div>
                {/* Body */}
                <div className="tac2d-body">
                    <div className="tac2d-arm tac2d-arm-l" />
                    <div className="tac2d-arm tac2d-arm-r" />
                </div>
                {/* Legs */}
                <div className="tac2d-legs">
                    <div className="tac2d-leg" />
                    <div className="tac2d-leg" />
                </div>
            </div>
        </div>
    );
}

const cssText = `
.tac2d-char {
    display: flex;
    flex-direction: column;
    align-items: center;
    animation: tac2d-breathe 3s ease-in-out infinite;
}
.tac2d-head {
    width: 44px; height: 44px;
    background: linear-gradient(145deg, #ede9fe, #ddd6fe);
    border-radius: 50%;
    position: relative;
    border: 2px solid rgba(139,92,246,0.25);
    box-shadow: 0 2px 10px rgba(124,58,237,0.12);
}
.tac2d-eye {
    width: 6px; height: 6px;
    background: #4c1d95;
    border-radius: 50%;
    position: absolute;
    top: 16px;
    animation: tac2d-blink 4s ease-in-out infinite;
}
.tac2d-eye-l { left: 12px; }
.tac2d-eye-r { right: 12px; }
.tac2d-pupil {
    width: 2.5px; height: 2.5px;
    background: white;
    border-radius: 50%;
    position: absolute;
    top: 1px; right: 1px;
}
.tac2d-mouth {
    position: absolute;
    bottom: 10px; left: 50%;
    transform: translateX(-50%);
    width: 10px; height: 4px;
    background: #7c3aed;
    border-radius: 0 0 6px 6px;
}
.tac2d-mouth-talk { animation: tac2d-talk 0.3s ease-in-out infinite alternate; }
.tac2d-body {
    width: 34px; height: 42px;
    background: linear-gradient(180deg, #7c3aed, #6d28d9);
    border-radius: 12px 12px 8px 8px;
    margin-top: -5px;
    position: relative;
}
.tac2d-arm {
    position: absolute;
    width: 10px; height: 30px;
    background: linear-gradient(180deg, #8b5cf6, #7c3aed);
    border-radius: 6px;
    top: 3px;
    transform-origin: top center;
}
.tac2d-arm-l { left: -9px; animation: tac2d-arm-idle-l 3s ease-in-out infinite; }
.tac2d-arm-r { right: -9px; animation: tac2d-arm-idle-r 3s ease-in-out infinite; }
.tac2d-legs { display: flex; gap: 4px; margin-top: -1px; }
.tac2d-leg {
    width: 12px; height: 32px;
    background: linear-gradient(180deg, #6d28d9, #5b21b6);
    border-radius: 6px 6px 7px 7px;
}

@keyframes tac2d-breathe {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-3px); }
}
@keyframes tac2d-blink {
    0%, 42%, 44%, 100% { transform: scaleY(1); }
    43% { transform: scaleY(0.1); }
}
@keyframes tac2d-talk {
    0% { height: 3px; width: 8px; }
    100% { height: 7px; width: 11px; border-radius: 50%; }
}
@keyframes tac2d-arm-idle-l {
    0%, 100% { transform: rotate(3deg); } 50% { transform: rotate(-2deg); }
}
@keyframes tac2d-arm-idle-r {
    0%, 100% { transform: rotate(-3deg); } 50% { transform: rotate(2deg); }
}

/* Speaking */
.tac2d-speaking { animation: tac2d-speak-sway 1.5s ease-in-out infinite; }
@keyframes tac2d-speak-sway {
    0%, 100% { transform: translateY(0) rotate(0); }
    25% { transform: translateY(-2px) rotate(-1deg); }
    75% { transform: translateY(-2px) rotate(1deg); }
}

/* Wave */
.tac2d-anim-wave .tac2d-arm-r {
    animation: tac2d-wave 0.5s ease-in-out infinite alternate !important;
}
@keyframes tac2d-wave {
    0% { transform: rotate(-55deg); } 100% { transform: rotate(-80deg); }
}

/* Clap */
.tac2d-anim-clap .tac2d-arm-l {
    animation: tac2d-clap-l 0.35s ease-in-out infinite alternate !important;
}
.tac2d-anim-clap .tac2d-arm-r {
    animation: tac2d-clap-r 0.35s ease-in-out infinite alternate !important;
}
@keyframes tac2d-clap-l { 0% { transform: rotate(25deg); } 100% { transform: rotate(50deg); } }
@keyframes tac2d-clap-r { 0% { transform: rotate(-25deg); } 100% { transform: rotate(-50deg); } }
`;
