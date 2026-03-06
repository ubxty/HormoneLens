/**
 * CharacterDebugPanel.jsx
 * ─────────────────────────────────────────────────────────────────────────────
 * Pure DOM overlay — completely outside the Three.js canvas so it never
 * interferes with WebGL rendering.
 *
 * Props:
 *   capabilities  {CharacterCapabilities | null}  – report from CharacterInspector
 *   visible       {boolean}                        – show/hide the panel body
 *   onToggle      {() => void}                     – toggled by the debug button
 *
 * The component is wrapped in React.memo so it only re-renders when the
 * capabilities object or visibility flag actually changes.
 */

import React, { memo, useState, useCallback } from 'react';

// ─────────────────────────────────────────────────────────────────────────────
// Design tokens — purple theme matching the rest of HormoneLens
// ─────────────────────────────────────────────────────────────────────────────
const T = {
    bg:           'rgba(8, 3, 18, 0.97)',
    bgSection:    'rgba(124, 58, 237, 0.07)',
    border:       'rgba(124, 58, 237, 0.22)',
    borderAccent: 'rgba(124, 58, 237, 0.48)',
    purple:       '#7c3aed',
    purpleLight:  '#a78bfa',
    purpleDim:    '#6b5fa6',
    text:         '#e2ddf5',
    textMuted:    '#9b8fc4',
    green:        '#10b981',
    red:          '#ef4444',
    font:         'system-ui, -apple-system, sans-serif',
};

// ─────────────────────────────────────────────────────────────────────────────
// Small shared primitives
// ─────────────────────────────────────────────────────────────────────────────

/** Status dot: green = feature present, red = feature missing */
const Dot = ({ ok }) => (
    <span style={{
        display:      'inline-block',
        width:        8, height: 8,
        borderRadius: '50%',
        background:   ok ? T.green : T.red,
        boxShadow:    `0 0 6px ${ok ? T.green : T.red}88`,
        marginRight:  6,
        flexShrink:   0,
    }} />
);

/** A single bullet-list entry with an optional subdued label */
const Item = ({ name, sub }) => (
    <div style={{ display: 'flex', alignItems: 'baseline', gap: 6, padding: '2px 0' }}>
        <span style={{ color: T.purpleDim, fontSize: 9, lineHeight: 1 }}>•</span>
        <span style={{ fontSize: 10, color: T.text, lineHeight: 1.5, wordBreak: 'break-all' }}>
            {name}
        </span>
        {sub && (
            <span style={{ fontSize: 9, color: T.textMuted, flexShrink: 0, marginLeft: 'auto' }}>
                {sub}
            </span>
        )}
    </div>
);

/** A key/value stat row with an optional indicator dot */
const StatRow = ({ label, value, ok }) => (
    <div style={{
        display:       'flex',
        justifyContent:'space-between',
        alignItems:    'center',
        padding:       '4px 0',
        borderBottom:  `1px solid rgba(124,58,237,0.07)`,
    }}>
        <span style={{ fontSize: 10, color: T.textMuted }}>{label}</span>
        <div style={{ display: 'flex', alignItems: 'center', gap: 5 }}>
            {ok !== undefined && <Dot ok={ok} />}
            <span style={{
                fontSize:   10,
                fontWeight: 700,
                color:      ok === false ? T.red : T.text,
            }}>
                {value}
            </span>
        </div>
    </div>
);

// ─────────────────────────────────────────────────────────────────────────────
// Collapsible section  — click the header to expand / collapse
// ─────────────────────────────────────────────────────────────────────────────
function Section({ title, count, ok, children, maxHeight = 120 }) {
    // Each section manages its own open/closed state independently
    const [open, setOpen] = useState(true);
    const toggle = useCallback(() => setOpen((v) => !v), []);

    return (
        <div style={{ marginBottom: 14 }}>
            {/* ── Section header button ── */}
            <button
                onClick={toggle}
                style={{
                    width:      '100%',
                    background: 'none',
                    border:     'none',
                    cursor:     'pointer',
                    padding:    0,
                    textAlign:  'left',
                }}
            >
                <div style={{
                    display:        'flex',
                    alignItems:     'center',
                    justifyContent: 'space-between',
                    marginBottom:   open ? 6 : 0,
                    paddingBottom:  open ? 4 : 0,
                    borderBottom:   open ? `1px solid ${T.border}` : 'none',
                    transition:     'border 0.15s',
                }}>
                    {/* Left: status dot + title */}
                    <div style={{ display: 'flex', alignItems: 'center' }}>
                        <Dot ok={ok} />
                        <span style={{
                            fontSize:      11,
                            fontWeight:    800,
                            color:         T.purpleLight,
                            letterSpacing: 1,
                            textTransform: 'uppercase',
                        }}>
                            {title}
                        </span>
                    </div>

                    {/* Right: item count badge + chevron */}
                    <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                        {count !== undefined && (
                            <span style={{
                                fontSize:   9,
                                fontWeight: 700,
                                color:      T.purpleDim,
                                background: 'rgba(124,58,237,0.14)',
                                border:     `1px solid ${T.border}`,
                                borderRadius: 10,
                                padding:    '1px 7px',
                            }}>
                                {count}
                            </span>
                        )}
                        <span style={{ fontSize: 9, color: T.purpleDim }}>
                            {open ? '▲' : '▼'}
                        </span>
                    </div>
                </div>
            </button>

            {/* ── Section body — scrollable when content overflows ── */}
            {open && (
                <div style={{
                    maxHeight,
                    overflowY:    'auto',
                    paddingRight: 2,
                    scrollbarWidth: 'thin',
                    scrollbarColor: `${T.purple} transparent`,
                }}>
                    {children}
                </div>
            )}
        </div>
    );
}

// ─────────────────────────────────────────────────────────────────────────────
// Material swatch row (colour preview + name + type)
// ─────────────────────────────────────────────────────────────────────────────
const MaterialRow = ({ mat }) => (
    <div style={{ display: 'flex', alignItems: 'center', gap: 7, padding: '3px 0' }}>
        {/* Colour swatch — shown only when colour data is available */}
        {mat.color ? (
            <div style={{
                width:        11,
                height:       11,
                borderRadius: 3,
                background:   mat.color,
                border:       '1px solid rgba(255,255,255,0.15)',
                flexShrink:   0,
            }} />
        ) : (
            <div style={{
                width:        11,
                height:       11,
                borderRadius: 3,
                border:       `1px dashed ${T.purpleDim}`,
                flexShrink:   0,
            }} />
        )}
        <span style={{ fontSize: 10, color: T.text, wordBreak: 'break-all', flex: 1 }}>
            {mat.name}
        </span>
        <span style={{ fontSize: 9, color: T.textMuted, flexShrink: 0 }}>
            {mat.type}
        </span>
    </div>
);

// ─────────────────────────────────────────────────────────────────────────────
// Empty-state message
// ─────────────────────────────────────────────────────────────────────────────
const Empty = ({ text }) => (
    <span style={{ fontSize: 10, color: T.textMuted, fontStyle: 'italic' }}>
        {text}
    </span>
);

// ─────────────────────────────────────────────────────────────────────────────
// Main exported component
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Floating debug panel — rendered as a plain DOM overlay, not inside <Canvas>.
 * Position it by wrapping the caller in position:relative.
 */
const CharacterDebugPanel = memo(({ capabilities, visible, onToggle }) => {
    const caps = capabilities;
    // 'idle' | 'copied' | 'error'
    const [copyState, setCopyState] = useState('idle');

    const handleCopy = useCallback(() => {
        if (!caps) return;
        // Build a clean, human-readable text snapshot of every section
        const lines = [
            '═══════════════════════════════════════',
            '  CHARACTER DEBUG REPORT',
            '═══════════════════════════════════════',
            '',
            `Skeleton Detected : ${caps.skeletonDetected ? 'Yes ✅' : 'No ❌'}`,
            `Mesh Count        : ${caps.meshes.length}`,
            `Bone Count        : ${caps.bones.length}`,
            `Vertex Count      : ${caps.vertexCount.toLocaleString()}`,
            `Polygon Count     : ${caps.polygonCount.toLocaleString()}`,
            `Materials         : ${caps.materials.length}`,
            `Morph Targets     : ${caps.morphTargets.length || 'None'}`,
            '',
            '─── ANIMATIONS (' + caps.animations.length + ') ───────────────────',
            ...(caps.animations.length === 0
                ? ['  (none)'] 
                : caps.animations.map((a) => `  • ${a.name}  [${a.duration}s, ${a.tracks} tracks]`)),
            '',
            '─── MESHES (' + caps.meshes.length + ') ──────────────────────────',
            ...(caps.meshes.length === 0
                ? ['  (none)']
                : caps.meshes.map((m) => `  • ${m.name}  [${m.type}, ${m.vertices.toLocaleString()} verts, ${m.polygons.toLocaleString()} polys]`)),
            '',
            '─── BONES (' + caps.bones.length + ') ───────────────────────────',
            ...(caps.bones.length === 0
                ? ['  (none)']
                : caps.bones.map((b) => `  • ${b.name}`)),
            '',
            '─── MORPH TARGETS (' + caps.morphTargets.length + ') ─────────────',
            ...(caps.morphTargets.length === 0
                ? ['  (none)']
                : caps.morphTargets.map((m) => `  • ${m.name}  [on: ${m.meshOn}]`)),
            '',
            '─── MATERIALS (' + caps.materials.length + ') ─────────────────────',
            ...(caps.materials.length === 0
                ? ['  (none)']
                : caps.materials.map((m) => `  • ${m.name}  [${m.type}${m.color ? ', ' + m.color : ''}]`)),
            '',
            '═══════════════════════════════════════',
        ];
        const text = lines.join('\n');
        navigator.clipboard.writeText(text)
            .then(() => {
                setCopyState('copied');
                setTimeout(() => setCopyState('idle'), 2200);
            })
            .catch(() => {
                setCopyState('error');
                setTimeout(() => setCopyState('idle'), 2200);
            });
    }, [caps]);

    return (
        <>
            {/* ── Toggle button — always visible in the top-right corner ── */}
            <button
                onClick={onToggle}
                title={visible ? 'Close debug panel' : 'Open character debug panel'}
                style={{
                    position:        'absolute',
                    top:             12,
                    right:           12,
                    zIndex:          30,
                    background:      visible
                        ? T.purple
                        : 'rgba(124,58,237,0.20)',
                    border:          `1px solid ${T.borderAccent}`,
                    borderRadius:    10,
                    padding:         '6px 14px',
                    color:           '#fff',
                    fontSize:        11,
                    fontWeight:      700,
                    cursor:          'pointer',
                    fontFamily:      T.font,
                    backdropFilter:  'blur(8px)',
                    letterSpacing:   0.4,
                    transition:      'background 0.2s, box-shadow 0.2s',
                    boxShadow:       visible ? `0 0 16px ${T.purple}66` : 'none',
                    userSelect:      'none',
                }}
            >
                {visible ? '✕ Close Debug' : '🔍 Debug Panel'}
            </button>

            {/* ── Panel body — only rendered when visible ── */}
            {visible && (
                <div
                    role="complementary"
                    aria-label="Character debug panel"
                    style={{
                        position:       'absolute',
                        top:            46,
                        right:          12,
                        zIndex:         20,
                        width:          248,
                        maxHeight:      'calc(100% - 62px)',
                        overflowY:      'auto',
                        background:     T.bg,
                        backdropFilter: 'blur(22px)',
                        border:         `1px solid ${T.borderAccent}`,
                        borderRadius:   16,
                        padding:        '14px 14px 12px',
                        fontFamily:     T.font,
                        boxShadow:      `0 0 48px rgba(124,58,237,0.18), 0 12px 40px rgba(0,0,0,0.65)`,
                        scrollbarWidth: 'thin',
                        scrollbarColor: `${T.purple} transparent`,
                    }}
                >
                    {/* ── Panel title bar ── */}
                    <div style={{
                        display:       'flex',
                        alignItems:    'center',
                        gap:           8,
                        marginBottom:  10,
                        paddingBottom: 9,
                        borderBottom:  `1px solid ${T.borderAccent}`,
                    }}>
                        {/* Live indicator dot */}
                        <div style={{
                            width:      8,
                            height:     8,
                            borderRadius: '50%',
                            background: caps ? T.green : T.textMuted,
                            boxShadow:  caps ? `0 0 8px ${T.green}` : 'none',
                            flexShrink: 0,
                            animation:  caps ? 'dbgPulse 2s ease-in-out infinite' : 'none',
                        }} />
                        <div>
                            <div style={{ fontSize: 12, fontWeight: 900, color: T.text, letterSpacing: 0.4 }}>
                                Character Debug
                            </div>
                            <div style={{ fontSize: 9, color: T.textMuted, marginTop: 1 }}>
                                FBX capability report — live inspection
                            </div>
                        </div>
                    </div>

                    {/* ── Loading state ── */}
                    {!caps ? (
                        <div style={{
                            textAlign: 'center',
                            padding:   '24px 0',
                            color:     T.textMuted,
                            fontSize:  11,
                        }}>
                            <div style={{ fontSize: 22, marginBottom: 8 }}>⏳</div>
                            Waiting for model…
                        </div>
                    ) : (
                        <>
                            {/* ── Animations ─────────────────────────────────── */}
                            <Section
                                title="Animations"
                                count={caps.animations.length}
                                ok={caps.animations.length > 0}
                                maxHeight={110}
                            >
                                {caps.animations.length === 0
                                    ? <Empty text="None found in this FBX" />
                                    : caps.animations.map((a, i) => (
                                        <Item
                                            key={i}
                                            name={a.name}
                                            sub={`${a.duration}s · ${a.tracks} tracks`}
                                        />
                                    ))
                                }
                            </Section>

                            {/* ── Meshes ──────────────────────────────────────── */}
                            <Section
                                title="Meshes"
                                count={caps.meshes.length}
                                ok={caps.meshes.length > 0}
                                maxHeight={120}
                            >
                                {caps.meshes.length === 0
                                    ? <Empty text="No meshes detected" />
                                    : caps.meshes.map((m, i) => (
                                        <Item
                                            key={i}
                                            name={m.name}
                                            sub={
                                                m.type === 'SkinnedMesh'
                                                    ? `Skinned · ${m.vertices.toLocaleString()}v`
                                                    : `${m.vertices.toLocaleString()}v`
                                            }
                                        />
                                    ))
                                }
                            </Section>

                            {/* ── Bones ───────────────────────────────────────── */}
                            <Section
                                title="Bones"
                                count={caps.bones.length}
                                ok={caps.bones.length > 0}
                                maxHeight={120}
                            >
                                {caps.bones.length === 0
                                    ? <Empty text="No bones detected" />
                                    : caps.bones.map((b, i) => (
                                        <Item key={i} name={b.name} />
                                    ))
                                }
                            </Section>

                            {/* ── Morph Targets ───────────────────────────────── */}
                            <Section
                                title="Morph Targets"
                                count={caps.morphTargets.length}
                                ok={caps.morphTargets.length > 0}
                                maxHeight={100}
                            >
                                {caps.morphTargets.length === 0
                                    ? <Empty text="None found (no blend shapes)" />
                                    : caps.morphTargets.map((m, i) => (
                                        <Item key={i} name={m.name} sub={m.meshOn} />
                                    ))
                                }
                            </Section>

                            {/* ── Materials ───────────────────────────────────── */}
                            <Section
                                title="Materials"
                                count={caps.materials.length}
                                ok={caps.materials.length > 0}
                                maxHeight={100}
                            >
                                {caps.materials.length === 0
                                    ? <Empty text="No materials" />
                                    : caps.materials.map((mat, i) => (
                                        <MaterialRow key={i} mat={mat} />
                                    ))
                                }
                            </Section>

                            {/* ── Model Stats ─────────────────────────────────── */}
                            <div style={{
                                background:   T.bgSection,
                                border:       `1px solid ${T.border}`,
                                borderRadius: 10,
                                padding:      '9px 10px',
                            }}>
                                <div style={{
                                    fontSize:      10,
                                    fontWeight:    800,
                                    color:         T.purpleLight,
                                    letterSpacing: 1,
                                    textTransform: 'uppercase',
                                    marginBottom:  6,
                                }}>
                                    Model Stats
                                </div>
                                <StatRow
                                    label="Skeleton"
                                    value={caps.skeletonDetected ? 'Detected' : 'Not Found'}
                                    ok={caps.skeletonDetected}
                                />
                                <StatRow label="Mesh Count"    value={caps.meshes.length} />
                                <StatRow label="Bone Count"    value={caps.bones.length} />
                                <StatRow label="Vertex Count"  value={caps.vertexCount.toLocaleString()} />
                                <StatRow label="Polygon Count" value={caps.polygonCount.toLocaleString()} />
                                <StatRow label="Materials"     value={caps.materials.length} />
                                <StatRow
                                    label="Morph Targets"
                                    value={caps.morphTargets.length > 0 ? caps.morphTargets.length : 'None'}
                                    ok={caps.morphTargets.length > 0}
                                />
                            </div>
                        </>
                    )}

                    {/* ── Copy All Debug Info button ── */}
                    {caps && (
                        <button
                            onClick={handleCopy}
                            disabled={copyState !== 'idle'}
                            title="Copy full capability report to clipboard"
                            style={{
                                marginTop:     12,
                                width:         '100%',
                                padding:       '8px 0',
                                background:    copyState === 'copied'
                                    ? 'rgba(16,185,129,0.18)'
                                    : copyState === 'error'
                                        ? 'rgba(239,68,68,0.18)'
                                        : 'rgba(124,58,237,0.15)',
                                border:        `1px solid ${
                                    copyState === 'copied' ? T.green
                                    : copyState === 'error' ? T.red
                                    : T.borderAccent}`,
                                borderRadius:  10,
                                color:         copyState === 'copied' ? T.green
                                    : copyState === 'error' ? T.red
                                    : T.purpleLight,
                                fontSize:      11,
                                fontWeight:    700,
                                cursor:        copyState !== 'idle' ? 'default' : 'pointer',
                                fontFamily:    T.font,
                                letterSpacing: 0.3,
                                transition:    'background 0.2s, color 0.2s, border-color 0.2s',
                                display:       'flex',
                                alignItems:    'center',
                                justifyContent:'center',
                                gap:           7,
                            }}
                        >
                            {copyState === 'copied' && <span>✓</span>}
                            {copyState === 'error'  && <span>✕</span>}
                            {copyState === 'idle'   && (
                                // Clipboard icon (inline SVG — no extra dependency)
                                <svg width="12" height="12" viewBox="0 0 16 16" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <rect x="5" y="2" width="8" height="11" rx="1.5"
                                          stroke="currentColor" strokeWidth="1.4"/>
                                    <path d="M5 4H4a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h7a1 1 0 0 0 1-1v-1"
                                          stroke="currentColor" strokeWidth="1.4" strokeLinecap="round"/>
                                </svg>
                            )}
                            {copyState === 'copied' ? 'Copied to clipboard!'
                             : copyState === 'error'  ? 'Copy failed — try again'
                             : 'Copy All Debug Info'}
                        </button>
                    )}

                    {/* Keyframe for the live-indicator pulse */}
                    <style>{`
                        @keyframes dbgPulse {
                            0%, 100% { opacity: 1; }
                            50%       { opacity: 0.4; }
                        }
                    `}</style>
                </div>
            )}
        </>
    );
});

CharacterDebugPanel.displayName = 'CharacterDebugPanel';

export default CharacterDebugPanel;
