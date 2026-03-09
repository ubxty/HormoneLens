import React, { useState, useEffect, useRef } from 'react';

const TYPING_SPEED = 24;

/**
 * Tour speech bubble with typing animation + navigation buttons.
 * Rendered as a fixed/absolute-positioned panel next to the character.
 */
export default function TourSpeechBubble({
    text,
    stepIndex,
    totalSteps,
    onNext,
    onBack,
    onSkip,
    onFinish,
    isLast = false,
    tailSide = 'right',
    width = 320,
}) {
    const [charIdx, setCharIdx] = useState(0);
    const [doneTyping, setDoneTyping] = useState(false);
    const timerRef = useRef(null);

    /* Reset typing when text changes */
    useEffect(() => {
        setCharIdx(0);
        setDoneTyping(false);
    }, [text]);

    /* Typing effect */
    useEffect(() => {
        if (doneTyping) return;
        if (charIdx < text.length) {
            timerRef.current = setTimeout(() => setCharIdx((c) => c + 1), TYPING_SPEED);
            return () => clearTimeout(timerRef.current);
        }
        setDoneTyping(true);
    }, [charIdx, text, doneTyping]);

    const displayed = text.slice(0, charIdx);
    const isTyping = charIdx < text.length;

    return (
        <div style={{
            background: '#fff',
            border: '1.5px solid #e9e5f5',
            borderRadius: 20,
            padding: '20px 22px 16px',
            width,
            boxShadow: '0 8px 32px rgba(124,58,237,0.10), 0 2px 8px rgba(0,0,0,0.06)',
            fontFamily: 'system-ui, -apple-system, sans-serif',
            position: 'relative',
            boxSizing: 'border-box',
        }}>
            {/* Tail pointing toward character */}
            <div style={{
                position: 'absolute',
                ...(tailSide === 'right' ? {
                    right: -11,
                    borderLeft: '11px solid #fff',
                } : {
                    left: -11,
                    borderRight: '11px solid #fff',
                }),
                top: 28,
                width: 0, height: 0,
                borderTop: '9px solid transparent',
                borderBottom: '9px solid transparent',
                filter: 'drop-shadow(2px 0 1px rgba(124,58,237,0.05))',
            }} />

            {/* Text */}
            <p style={{
                fontSize: 14.5,
                lineHeight: 1.65,
                color: '#1e1b2e',
                margin: '0 0 16px',
                minHeight: 44,
            }}>
                {displayed}
                {isTyping && (
                    <span style={{
                        display: 'inline-block',
                        width: 2, height: 15,
                        background: '#7c3aed',
                        marginLeft: 2,
                        verticalAlign: 'text-bottom',
                        animation: 'tourBlink 0.6s step-end infinite',
                    }} />
                )}
            </p>

            {/* Step dots */}
            <div style={{ display: 'flex', gap: 5, justifyContent: 'center', marginBottom: 14 }}>
                {Array.from({ length: totalSteps }, (_, i) => (
                    <div key={i} style={{
                        width: 7, height: 7, borderRadius: '50%',
                        background: i === stepIndex ? '#7c3aed' : i < stepIndex ? '#c4b5fd' : '#e2ddf5',
                        transition: 'background 0.3s',
                    }} />
                ))}
            </div>

            {/* Navigation buttons */}
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <button
                    onClick={onSkip}
                    style={{
                        background: 'none', border: 'none',
                        color: '#a78bfa', fontSize: 12.5,
                        cursor: 'pointer', padding: '4px 0',
                        fontFamily: 'inherit',
                    }}
                >
                    Skip Tour
                </button>
                <div style={{ display: 'flex', gap: 8 }}>
                    {stepIndex > 0 && (
                        <button onClick={onBack} style={{
                            padding: '7px 16px', borderRadius: 10,
                            border: '1.5px solid #e2ddf5', background: '#fff',
                            color: '#6d28d9', fontSize: 13, fontWeight: 600,
                            cursor: 'pointer', fontFamily: 'inherit',
                        }}>
                            Back
                        </button>
                    )}
                    <button
                        onClick={isLast ? onFinish : onNext}
                        style={{
                            padding: '7px 20px', borderRadius: 10,
                            border: 'none',
                            background: 'linear-gradient(135deg, #7c3aed, #6d28d9)',
                            color: '#fff', fontSize: 13, fontWeight: 600,
                            cursor: 'pointer', fontFamily: 'inherit',
                            boxShadow: '0 2px 8px rgba(124,58,237,0.25)',
                        }}
                    >
                        {isLast ? 'Got it!' : 'Next'}
                    </button>
                </div>
            </div>

            <style>{`@keyframes tourBlink { 50% { opacity: 0; } }`}</style>
        </div>
    );
}
