import React, { useState, useEffect, useRef } from 'react';

const TYPING_SPEED = 32; // ms per character

export default function SpeechBubble({ messages, onDone }) {
    const [msgIdx, setMsgIdx]     = useState(0);
    const [charIdx, setCharIdx]   = useState(0);
    const [finished, setFinished] = useState(false);
    const timerRef = useRef(null);

    const currentMsg = messages[msgIdx] ?? '';

    /* Typing effect */
    useEffect(() => {
        if (finished) return;
        if (charIdx < currentMsg.length) {
            timerRef.current = setTimeout(() => setCharIdx((c) => c + 1), TYPING_SPEED);
            return () => clearTimeout(timerRef.current);
        }

        /* Current message fully typed — pause then advance */
        const delay = setTimeout(() => {
            if (msgIdx + 1 < messages.length) {
                setMsgIdx((i) => i + 1);
                setCharIdx(0);
            } else {
                setFinished(true);
                onDone?.();
            }
        }, 1200);
        return () => clearTimeout(delay);
    }, [charIdx, msgIdx, currentMsg, messages, finished, onDone]);

    /* Reset when messages array changes */
    useEffect(() => {
        setMsgIdx(0);
        setCharIdx(0);
        setFinished(false);
    }, [messages]);

    const displayedText = currentMsg.slice(0, charIdx);
    const isTyping = charIdx < currentMsg.length && !finished;

    return (
        <div style={{
            position: 'relative',
            background: '#fff',
            border: '1.5px solid #e9e5f5',
            borderRadius: 20,
            padding: '20px 24px',
            maxWidth: 420,
            minHeight: 60,
            boxShadow: '0 4px 24px rgba(124,58,237,0.08), 0 1px 4px rgba(0,0,0,0.04)',
            fontFamily: 'system-ui, -apple-system, sans-serif',
        }}>
            {/* Tail pointing left */}
            <div style={{
                position: 'absolute',
                left: -12,
                top: 24,
                width: 0, height: 0,
                borderTop: '8px solid transparent',
                borderBottom: '8px solid transparent',
                borderRight: '12px solid #fff',
                filter: 'drop-shadow(-2px 0 1px rgba(124,58,237,0.06))',
            }} />

            <p style={{
                fontSize: 15,
                lineHeight: 1.6,
                color: '#1e1b2e',
                margin: 0,
                minHeight: 24,
            }}>
                {displayedText}
                {isTyping && (
                    <span style={{
                        display: 'inline-block',
                        width: 2,
                        height: 16,
                        background: '#7c3aed',
                        marginLeft: 2,
                        verticalAlign: 'text-bottom',
                        animation: 'blink 0.6s step-end infinite',
                    }} />
                )}
            </p>

            {/* Dot indicator for multi-message */}
            {messages.length > 1 && (
                <div style={{ display: 'flex', gap: 5, justifyContent: 'center', marginTop: 12 }}>
                    {messages.map((_, i) => (
                        <div key={i} style={{
                            width: 6, height: 6, borderRadius: '50%',
                            background: i <= msgIdx ? '#7c3aed' : '#e2ddf5',
                            transition: 'background 0.3s',
                        }} />
                    ))}
                </div>
            )}

            <style>{`@keyframes blink { 50% { opacity: 0; } }`}</style>
        </div>
    );
}
