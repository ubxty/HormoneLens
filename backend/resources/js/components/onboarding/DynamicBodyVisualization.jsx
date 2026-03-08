import React, { useState, useEffect, useRef, useMemo } from 'react';

/**
 * Dynamic SVG body visualization that transforms based on form answers.
 * Loads men.svg or women.svg from /images/ and applies CSS transforms
 * to reflect stress, activity, and body composition.
 */

const BODY_TRAITS = {
    /* stress_level → posture rotation */
    stress: { high: -3, medium: -1, low: 0 },
    /* physical_activity → body scale */
    activity: { sedentary: 1.08, moderate: 1.0, active: 0.94 },
};

export default function DynamicBodyVisualization({ answers = {} }) {
    const containerRef = useRef(null);
    const [svgContent, setSvgContent] = useState('');
    const [loaded, setLoaded] = useState(false);

    const gender = answers.gender || 'male';
    const svgPath = gender === 'female' ? '/images/women.svg' : '/images/men.svg';

    /* Load SVG content */
    useEffect(() => {
        setLoaded(false);
        fetch(svgPath)
            .then((r) => r.text())
            .then((text) => {
                /* Strip XML declaration, add viewBox if missing */
                let cleaned = text.replace(/<\?xml[^?]*\?>\s*/g, '');
                cleaned = cleaned.replace(
                    /(<svg[^>]*?)\s+width="(\d+(?:\.\d+)?)"\s+height="(\d+(?:\.\d+)?)"/,
                    '$1 viewBox="0 0 $2 $3" preserveAspectRatio="xMidYMid meet"'
                );
                cleaned = cleaned.replace(/\s+width="\d+(\.\d+)?"/g, '');
                cleaned = cleaned.replace(/\s+height="\d+(\.\d+)?"/g, '');
                setSvgContent(cleaned);
                setLoaded(true);
            })
            .catch(() => {
                setSvgContent('');
                setLoaded(true);
            });
    }, [svgPath]);

    /* Compute body transform values */
    const transforms = useMemo(() => {
        const stress = answers.stress_level || '';
        const activity = answers.physical_activity || '';

        const postureRotate = BODY_TRAITS.stress[stress] || 0;
        const bodyScale = BODY_TRAITS.activity[activity] || 1.0;

        /* Color tint based on overall health signals */
        let hueRotate = 0;
        let saturation = 1;
        if (stress === 'high') { hueRotate = -10; saturation = 0.85; }
        if (activity === 'active') { saturation = 1.15; }

        return { postureRotate, bodyScale, hueRotate, saturation };
    }, [answers.stress_level, answers.physical_activity]);

    /* Progress-based glow intensity */
    const filledCount = Object.keys(answers).filter((k) => answers[k]).length;
    const progress = Math.min(filledCount / 9, 1);
    const glowIntensity = 0.1 + progress * 0.5;

    return (
        <div
            ref={containerRef}
            style={{
                position: 'relative',
                width: '100%',
                height: '100%',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                overflow: 'hidden',
            }}
        >
            {/* Background glow that intensifies with progress */}
            <div style={{
                position: 'absolute',
                inset: 0,
                background: `radial-gradient(ellipse at 50% 50%, rgba(124,58,237,${glowIntensity * 0.15}), transparent 70%)`,
                transition: 'background 0.8s ease',
                pointerEvents: 'none',
            }} />

            {/* Pulsing ring effect — grows with form progress */}
            <div style={{
                position: 'absolute',
                width: `${60 + progress * 40}%`,
                height: `${60 + progress * 40}%`,
                borderRadius: '50%',
                border: `2px solid rgba(124,58,237,${0.08 + progress * 0.12})`,
                transition: 'all 1s cubic-bezier(.4,0,.2,1)',
                animation: 'bodyPulse 3s ease-in-out infinite',
                pointerEvents: 'none',
            }} />

            {/* SVG body with dynamic transforms */}
            {svgContent && (
                <div
                    style={{
                        width: '75%',
                        maxWidth: 320,
                        height: '80%',
                        transition: 'transform 0.8s cubic-bezier(.4,0,.2,1), filter 0.8s ease',
                        transform: `
                            scale(${transforms.bodyScale})
                            rotate(${transforms.postureRotate}deg)
                        `,
                        filter: `
                            hue-rotate(${transforms.hueRotate}deg)
                            saturate(${transforms.saturation})
                            drop-shadow(0 0 ${12 + progress * 20}px rgba(124,58,237,${glowIntensity}))
                        `,
                        opacity: loaded ? 1 : 0,
                    }}
                    dangerouslySetInnerHTML={{ __html: svgContent }}
                />
            )}

            {/* Loading state */}
            {!loaded && (
                <div style={{
                    fontSize: 14, color: '#a78bfa',
                    fontFamily: 'system-ui, -apple-system, sans-serif',
                }}>
                    Loading body model…
                </div>
            )}

            {/* Twin building label */}
            <div style={{
                position: 'absolute',
                bottom: 24,
                left: '50%',
                transform: 'translateX(-50%)',
                background: 'rgba(255,255,255,0.85)',
                backdropFilter: 'blur(8px)',
                borderRadius: 12,
                padding: '8px 16px',
                fontSize: 12,
                fontWeight: 600,
                color: '#7c3aed',
                boxShadow: '0 2px 12px rgba(124,58,237,0.1)',
                whiteSpace: 'nowrap',
                opacity: progress > 0 ? 1 : 0,
                transition: 'opacity 0.5s',
                fontFamily: 'system-ui, -apple-system, sans-serif',
            }}>
                🧬 Building your digital twin… {Math.round(progress * 100)}%
            </div>

            <style>{`
                @keyframes bodyPulse {
                    0%, 100% { opacity: 0.6; transform: scale(1); }
                    50%      { opacity: 1;   transform: scale(1.03); }
                }
            `}</style>
        </div>
    );
}
