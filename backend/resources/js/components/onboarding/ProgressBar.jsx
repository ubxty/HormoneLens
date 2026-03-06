import React from 'react';

export default function ProgressBar({ current, total }) {
    const pct = Math.round((current / total) * 100);

    return (
        <div style={{ width: '100%', maxWidth: 480, margin: '0 auto' }}>
            <div style={{
                display: 'flex', justifyContent: 'space-between', alignItems: 'center',
                marginBottom: 8, fontFamily: 'system-ui, -apple-system, sans-serif',
            }}>
                <span style={{ fontSize: 13, fontWeight: 600, color: '#7c3aed' }}>
                    Step {current} of {total}
                </span>
                <span style={{ fontSize: 12, color: '#9ca3af' }}>{pct}%</span>
            </div>

            <div style={{
                height: 8, borderRadius: 99, background: '#ede9fe',
                overflow: 'hidden', position: 'relative',
            }}>
                <div style={{
                    height: '100%', borderRadius: 99,
                    background: 'linear-gradient(90deg, #7c3aed, #a78bfa)',
                    width: `${pct}%`,
                    transition: 'width 0.5s cubic-bezier(.4,0,.2,1)',
                }} />
            </div>
        </div>
    );
}
