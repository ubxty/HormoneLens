import React, { useState, useEffect, useCallback, useRef } from 'react';

/**
 * Full-screen overlay that dims everything except a spotlight cutout
 * around the element matching `targetSelector`.
 */
export default function HighlightOverlay({ targetSelector, padding = 14, active = true }) {
    const [rect, setRect] = useState(null);
    const rafRef = useRef(null);

    const measure = useCallback(() => {
        if (!targetSelector || !active) { setRect(null); return; }
        const el = document.querySelector(targetSelector);
        if (!el) { setRect(null); return; }
        const r = el.getBoundingClientRect();
        setRect({
            x: r.x - padding,
            y: r.y - padding,
            w: r.width + padding * 2,
            h: r.height + padding * 2,
        });
    }, [targetSelector, padding, active]);

    /* Re-measure on mount, resize, scroll */
    useEffect(() => {
        measure();
        const handleResize = () => { cancelAnimationFrame(rafRef.current); rafRef.current = requestAnimationFrame(measure); };
        window.addEventListener('resize', handleResize);
        window.addEventListener('scroll', handleResize, true);
        return () => {
            window.removeEventListener('resize', handleResize);
            window.removeEventListener('scroll', handleResize, true);
            cancelAnimationFrame(rafRef.current);
        };
    }, [measure]);

    if (!active) return null;

    return (
        <div style={{ position: 'fixed', inset: 0, zIndex: 9990, pointerEvents: 'none' }}>
            {/* SVG overlay with mask cutout */}
            <svg width="100%" height="100%" style={{ position: 'absolute', inset: 0 }}>
                <defs>
                    <mask id="hl-tour-mask">
                        <rect width="100%" height="100%" fill="white" />
                        {rect && (
                            <rect
                                x={rect.x} y={rect.y}
                                width={rect.w} height={rect.h}
                                rx={16} ry={16} fill="black"
                            />
                        )}
                    </mask>
                </defs>
                <rect
                    width="100%" height="100%"
                    fill="rgba(0,0,0,0.52)"
                    mask="url(#hl-tour-mask)"
                    style={{ transition: 'opacity 0.4s' }}
                />
            </svg>

            {/* Glow border around highlighted area */}
            {rect && (
                <div style={{
                    position: 'fixed',
                    left: rect.x, top: rect.y,
                    width: rect.w, height: rect.h,
                    borderRadius: 16,
                    border: '2px solid rgba(124,58,237,0.55)',
                    boxShadow:
                        '0 0 18px rgba(124,58,237,0.35), 0 0 56px rgba(124,58,237,0.12), inset 0 0 18px rgba(124,58,237,0.06)',
                    pointerEvents: 'none',
                    transition: 'all 0.45s cubic-bezier(.4,0,.2,1)',
                    animation: 'hlGlow 2.4s ease-in-out infinite',
                }} />
            )}

            <style>{`
                @keyframes hlGlow {
                    0%,100% { box-shadow: 0 0 18px rgba(124,58,237,0.35), 0 0 56px rgba(124,58,237,0.12); }
                    50%     { box-shadow: 0 0 26px rgba(124,58,237,0.50), 0 0 72px rgba(124,58,237,0.20); }
                }
            `}</style>
        </div>
    );
}
