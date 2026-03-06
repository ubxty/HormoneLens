import React, { useState } from 'react';
import ChatWindow from './ChatWindow';

/**
 * Floating circular chat button (bottom-right) that expands into ChatWindow.
 */
export default function AssistantChatWidget({ visible = true }) {
    const [open, setOpen] = useState(false);

    if (!visible) return null;

    return (
        <div style={{
            position: 'fixed',
            bottom: 24, right: 24,
            zIndex: 9980,
            fontFamily: 'system-ui, -apple-system, sans-serif',
        }}>
            {/* Chat window */}
            {open && (
                <div style={{
                    position: 'absolute',
                    bottom: 64, right: 0,
                    animation: 'chatWidgetIn 0.3s cubic-bezier(.4,0,.2,1)',
                }}>
                    <ChatWindow onClose={() => setOpen(false)} />
                </div>
            )}

            {/* FAB button */}
            <button
                onClick={() => setOpen((o) => !o)}
                style={{
                    width: 56, height: 56,
                    borderRadius: '50%',
                    border: 'none',
                    background: 'linear-gradient(135deg, #7c3aed, #6d28d9)',
                    color: '#fff',
                    fontSize: 24,
                    cursor: 'pointer',
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                    boxShadow: '0 6px 24px rgba(124,58,237,0.35), 0 2px 8px rgba(0,0,0,0.1)',
                    transition: 'transform 0.2s, box-shadow 0.2s',
                    transform: open ? 'rotate(45deg)' : 'rotate(0deg)',
                }}
                onMouseEnter={(e) => { e.currentTarget.style.transform = open ? 'rotate(45deg) scale(1.08)' : 'scale(1.08)'; }}
                onMouseLeave={(e) => { e.currentTarget.style.transform = open ? 'rotate(45deg)' : 'scale(1)'; }}
                aria-label="Chat with assistant"
            >
                {open ? '＋' : '💬'}
            </button>

            {/* Tooltip when closed */}
            {!open && (
                <div style={{
                    position: 'absolute',
                    bottom: 66, right: 0,
                    background: '#1e1b2e',
                    color: '#fff',
                    fontSize: 12, fontWeight: 600,
                    padding: '6px 12px',
                    borderRadius: 10,
                    whiteSpace: 'nowrap',
                    pointerEvents: 'none',
                    animation: 'chatTooltipPulse 3s ease-in-out infinite',
                    boxShadow: '0 2px 8px rgba(0,0,0,0.2)',
                }}>
                    Need help? Chat with Luna!
                    <div style={{
                        position: 'absolute',
                        bottom: -5, right: 20,
                        width: 0, height: 0,
                        borderLeft: '6px solid transparent',
                        borderRight: '6px solid transparent',
                        borderTop: '6px solid #1e1b2e',
                    }} />
                </div>
            )}

            <style>{`
                @keyframes chatWidgetIn {
                    from { opacity: 0; transform: translateY(12px) scale(0.95); }
                    to   { opacity: 1; transform: translateY(0) scale(1); }
                }
                @keyframes chatTooltipPulse {
                    0%,70%,100% { opacity: 1; }
                    85% { opacity: 0.6; }
                }
            `}</style>
        </div>
    );
}
