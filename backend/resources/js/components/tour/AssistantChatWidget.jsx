import React, { useState, Suspense } from 'react';
import ChatWindow from './ChatWindow';
import TourAssistantCharacter from './TourAssistantCharacter';

/**
 * The character IS the chat trigger — no circle button.
 * Hover → wave, click → open/close chat window.
 */
export default function AssistantChatWidget({ visible = true }) {
    const [open, setOpen] = useState(false);
    const [hovered, setHovered] = useState(false);

    if (!visible) return null;

    /* Determine the animation state sent to the character */
    const animState = open ? 'idle' : hovered ? 'wave' : 'idle';

    return (
        <div style={{
            position: 'fixed',
            bottom: 20, right: 30,
            zIndex: 9980,
            fontFamily: 'system-ui, -apple-system, sans-serif',
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'flex-end',
            gap: 0,
        }}>
            {/* Chat window — slides up from character */}
            {open && (
                <div style={{
                    marginBottom: 8,
                    animation: 'chatSlideUp 0.32s cubic-bezier(.4,0,.2,1)',
                    transformOrigin: 'bottom right',
                }}>
                    <ChatWindow onClose={() => setOpen(false)} />
                </div>
            )}

            {/* Tooltip above character — visible only when closed */}
            {!open && (
                <div style={{
                    marginBottom: 8,
                    background: '#1e1b2e',
                    color: '#fff',
                    fontSize: 12, fontWeight: 600,
                    padding: '6px 14px',
                    borderRadius: 10,
                    whiteSpace: 'nowrap',
                    pointerEvents: 'none',
                    animation: 'chatTooltipPulse 3.5s ease-in-out infinite',
                    boxShadow: '0 3px 12px rgba(0,0,0,0.22)',
                    alignSelf: 'center',
                    position: 'relative',
                }}>
                    Need help? Chat with Luna!
                    {/* Arrow pointing down toward character */}
                    <div style={{
                        position: 'absolute',
                        bottom: -6, left: '50%',
                        transform: 'translateX(-50%)',
                        width: 0, height: 0,
                        borderLeft: '7px solid transparent',
                        borderRight: '7px solid transparent',
                        borderTop: '7px solid #1e1b2e',
                    }} />
                </div>
            )}

            {/* Character — the actual clickable trigger */}
            <div
                role="button"
                aria-label={open ? 'Close chat' : 'Chat with Luna'}
                tabIndex={0}
                onClick={() => setOpen((o) => !o)}
                onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); setOpen((o) => !o); } }}
                onMouseEnter={() => setHovered(true)}
                onMouseLeave={() => setHovered(false)}
                style={{
                    width: 120,
                    height: 160,
                    cursor: 'pointer',
                    position: 'relative',
                    /* Idle bob or hover lift */
                    animation: hovered
                        ? 'charHoverLift 0.35s cubic-bezier(.4,0,.2,1) forwards'
                        : 'charIdleBob 4s ease-in-out infinite',
                    filter: hovered
                        ? 'drop-shadow(0 8px 24px rgba(124,58,237,0.35))'
                        : 'drop-shadow(0 4px 12px rgba(124,58,237,0.18))',
                    transition: 'filter 0.3s',
                    outline: 'none',
                }}
            >
                <Suspense fallback={null}>
                    <TourAssistantCharacter
                        isSpeaking={false}
                        animationState={animState}
                    />
                </Suspense>

                {/* Small "✕ close" badge when open */}
                {open && (
                    <div style={{
                        position: 'absolute',
                        top: 6, right: 4,
                        width: 20, height: 20,
                        borderRadius: '50%',
                        background: 'linear-gradient(135deg, #7c3aed, #6d28d9)',
                        color: '#fff',
                        fontSize: 12, fontWeight: 700,
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                        boxShadow: '0 2px 8px rgba(124,58,237,0.4)',
                        pointerEvents: 'none',
                        lineHeight: 1,
                    }}>✕</div>
                )}
            </div>

            <style>{`
                @keyframes chatSlideUp {
                    from { opacity: 0; transform: translateY(16px) scale(0.97); }
                    to   { opacity: 1; transform: translateY(0)   scale(1); }
                }
                @keyframes chatTooltipPulse {
                    0%,65%,100% { opacity: 1; }
                    82% { opacity: 0.55; }
                }
                @keyframes charIdleBob {
                    0%,100% { transform: translateY(0); }
                    50%     { transform: translateY(-6px); }
                }
                @keyframes charHoverLift {
                    from { transform: translateY(0); }
                    to   { transform: translateY(-10px) scale(1.05); }
                }
            `}</style>
        </div>
    );
}
