import React, { useState } from 'react';

const cardBase = {
    display: 'flex', alignItems: 'center', gap: 14,
    padding: '16px 20px',
    borderRadius: 16,
    border: '2px solid transparent',
    background: 'rgba(255,255,255,0.85)',
    backdropFilter: 'blur(8px)',
    cursor: 'pointer',
    transition: 'all 0.28s cubic-bezier(.4,0,.2,1)',
    fontFamily: 'system-ui, -apple-system, sans-serif',
    userSelect: 'none',
    position: 'relative',
    overflow: 'hidden',
};

const selectedStyle = {
    borderColor: '#7c3aed',
    background: 'linear-gradient(135deg, rgba(124,58,237,0.08), rgba(167,139,250,0.12))',
    boxShadow: '0 0 0 3px rgba(124,58,237,0.12), 0 8px 24px rgba(124,58,237,0.15)',
    transform: 'scale(1.02)',
};

const hoverStyle = {
    borderColor: '#c4b5fd',
    boxShadow: '0 4px 16px rgba(124,58,237,0.10)',
    transform: 'scale(1.015)',
};

export default function SelectionCard({ emoji, label, description, selected, onClick }) {
    const [hovered, setHovered] = useState(false);

    const style = {
        ...cardBase,
        ...(selected ? selectedStyle : hovered ? hoverStyle : {}),
    };

    return (
        <div
            role="button"
            tabIndex={0}
            onClick={onClick}
            onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); onClick(); } }}
            onMouseEnter={() => setHovered(true)}
            onMouseLeave={() => setHovered(false)}
            style={style}
        >
            {/* Glow effect when selected */}
            {selected && (
                <div style={{
                    position: 'absolute', inset: 0,
                    background: 'radial-gradient(circle at 20% 50%, rgba(124,58,237,0.06), transparent 70%)',
                    pointerEvents: 'none',
                }} />
            )}

            <span style={{
                fontSize: 28, lineHeight: 1,
                filter: selected ? 'none' : 'grayscale(0.3)',
                transition: 'filter 0.3s, transform 0.3s',
                transform: selected ? 'scale(1.15)' : hovered ? 'scale(1.08)' : 'scale(1)',
            }}>
                {emoji}
            </span>

            <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{
                    fontSize: 14.5, fontWeight: 600,
                    color: selected ? '#4c1d95' : '#374151',
                    transition: 'color 0.2s',
                    lineHeight: 1.4,
                }}>
                    {label}
                </div>
                {description && (
                    <div style={{
                        fontSize: 12, color: '#9ca3af',
                        marginTop: 2, lineHeight: 1.3,
                    }}>
                        {description}
                    </div>
                )}
            </div>

            {/* Check indicator */}
            <div style={{
                width: 22, height: 22, borderRadius: '50%',
                border: selected ? 'none' : '2px solid #d1d5db',
                background: selected ? 'linear-gradient(135deg, #7c3aed, #a78bfa)' : 'transparent',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                transition: 'all 0.25s',
                flexShrink: 0,
            }}>
                {selected && (
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                        <path d="M2.5 6L5 8.5L9.5 3.5" stroke="#fff" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                    </svg>
                )}
            </div>
        </div>
    );
}
