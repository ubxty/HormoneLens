import React, { useRef, useMemo, useEffect, useState, Suspense } from 'react';
import { Canvas, useFrame } from '@react-three/fiber';
import { OrbitControls, ContactShadows, Float, Html, useFBX } from '@react-three/drei';
import * as THREE from 'three';
import { SkeletonUtils } from 'three-stdlib';

import maleFbxUrl from './Ch09_nonPBR.fbx?url';
import femaleFbxUrl from './Standing Idle(1).fbx?url';

/* ── Impact Zones ── */
const IMPACT_ZONES = [
    { id: 'brain',    label: 'Brain / HPA Axis',   info: 'Stress & appetite regulation',        pos: [0, 1.66, 0.12], r: 0.035 },
    { id: 'thyroid',  label: 'Thyroid',             info: 'Metabolic rate control',              pos: [0, 1.42, 0.06], r: 0.032 },
    { id: 'stomach',  label: 'Stomach / Digestion', info: 'Glycemic response & nutrient intake', pos: [0.02, 1.02, 0.12], r: 0.04 },
    { id: 'pancreas', label: 'Pancreas',            info: 'Insulin & glucagon secretion',        pos: [0.09, 0.93, 0.1], r: 0.032 },
    { id: 'liver',    label: 'Liver',               info: 'Glucose storage & metabolism',        pos: [-0.08, 1.0, 0.08], r: 0.034 },
];

function clamp01(v) { return Math.min(1, Math.max(0, v)); }

/* ── Pulse Node ── */
function ImpactNode({ node, color, intensity, label, isHovered, onHover }) {
    const glowRef = useRef();
    const coreRef = useRef();
    const idx = IMPACT_ZONES.indexOf(node);

    useFrame((state) => {
        const t = state.clock.elapsedTime;
        if (glowRef.current) {
            glowRef.current.scale.setScalar(1.0 + Math.sin(t * 2.5 + idx * 1.3) * 0.4);
            glowRef.current.material.opacity = 0.14 + Math.sin(t * 2.5 + idx) * 0.07;
        }
        if (coreRef.current) {
            coreRef.current.material.emissiveIntensity = intensity * (0.7 + Math.sin(t * 3 + idx) * 0.35);
        }
    });

    return (
        <group position={node.pos}>
            <mesh ref={coreRef}
                  onPointerEnter={(e) => { e.stopPropagation(); onHover(node.id); }}
                  onPointerLeave={(e) => { e.stopPropagation(); onHover(null); }}>
                <sphereGeometry args={[node.r, 20, 20]} />
                <meshStandardMaterial color={color} emissive={color} emissiveIntensity={intensity} toneMapped={false} />
            </mesh>
            <mesh ref={glowRef}>
                <sphereGeometry args={[node.r * 2.8, 16, 16]} />
                <meshBasicMaterial color={color} transparent opacity={0.14} depthWrite={false} />
            </mesh>
            {isHovered && (
                <Html center distanceFactor={5.5} style={{ pointerEvents: 'none', userSelect: 'none' }}>
                    <div style={{
                        background: 'rgba(12,6,22,0.94)', backdropFilter: 'blur(16px)',
                        border: `1px solid ${color}55`, borderRadius: 14, padding: '12px 16px',
                        minWidth: 170, maxWidth: 220,
                        boxShadow: `0 10px 40px ${color}44, 0 2px 8px rgba(0,0,0,0.3)`,
                        fontFamily: 'system-ui, -apple-system, sans-serif',
                    }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 6 }}>
                            <div style={{ width: 10, height: 10, borderRadius: '50%', background: color, boxShadow: `0 0 8px ${color}` }} />
                            <span style={{ fontSize: 12, fontWeight: 800, color: '#e2ddf5' }}>{node.label}</span>
                        </div>
                        <p style={{ fontSize: 10, color: '#9b8fc4', margin: '0 0 6px', lineHeight: 1.4 }}>{node.info}</p>
                        {label && (
                            <div style={{ padding: '5px 10px', background: `${color}18`, borderRadius: 8, border: `1px solid ${color}30` }}>
                                <span style={{ fontSize: 11, fontWeight: 700, color }}>{label}</span>
                            </div>
                        )}
                    </div>
                </Html>
            )}
        </group>
    );
}

/* ── Character model loader ── */
function useCharacter(url) {
    const source = useFBX(url);
    return useMemo(() => {
        const cloned = SkeletonUtils.clone(source);
        cloned.traverse((child) => {
            if (!child.isMesh) return;
            child.castShadow = true;
            child.receiveShadow = true;
            if (child.material && !Array.isArray(child.material)) {
                child.material = child.material.clone();
                child.material.transparent = false;
                child.material.roughness = 0.6;
                child.material.metalness = 0.05;
            }
        });
        return cloned;
    }, [source]);
}

/* ── Character mesh ── */
function CharacterModel({ url, isSimulating, riskChange }) {
    const groupRef = useRef();
    const root = useCharacter(url);
    const emissiveColor = useMemo(() => new THREE.Color('#6d28d9'), []);

    const normalized = useMemo(() => {
        const box = new THREE.Box3().setFromObject(root);
        const size = box.getSize(new THREE.Vector3());
        const center = box.getCenter(new THREE.Vector3());
        const targetHeight = 2.0;
        const ratio = size.y > 0 ? targetHeight / size.y : 1;
        return { scale: ratio, centerX: center.x, minY: box.min.y, centerZ: center.z };
    }, [root]);

    const meshMaterials = useMemo(() => {
        const mats = [];
        root.traverse((child) => {
            if (!child.isMesh || Array.isArray(child.material) || !child.material) return;
            child.material.emissive = emissiveColor;
            mats.push(child.material);
        });
        return mats;
    }, [root, emissiveColor]);

    useFrame(() => {
        if (!groupRef.current) return;
        const pulse = isSimulating ? (1 + Math.sin(performance.now() * 0.005) * 0.008) : 1;
        const s = normalized.scale * pulse;
        groupRef.current.scale.set(s, s, s);

        // Glow intensity based on risk change
        const baseIntensity = 0.03;
        const riskGlow = riskChange != null ? Math.min(0.15, Math.abs(riskChange) * 0.02) : 0;
        meshMaterials.forEach((mat) => { mat.emissiveIntensity = baseIntensity + riskGlow; });
    });

    return (
        <group ref={groupRef}
               position={[-normalized.centerX * normalized.scale, -normalized.minY * normalized.scale - 0.44, -normalized.centerZ * normalized.scale]}>
            <primitive object={root} />
        </group>
    );
}

/* ── Scene ── */
function SceneContent({ gender, isSimulating, riskChange, result, hoveredNode, onNodeHover }) {
    const url = gender === 'male' ? maleFbxUrl : femaleFbxUrl;

    // Determine node colors based on result
    const getNodeColor = (id) => {
        if (!result) return '#8b5cf6'; // default purple
        if (isSimulating) return '#facc15'; // yellow while simulating
        const change = result.risk_change ?? 0;
        if (change > 2) return '#ef4444';  // red = bad
        if (change > 0) return '#f97316';  // orange = slight bad
        if (change < -1) return '#10b981'; // green = good
        return '#8b5cf6';
    };

    const getNodeLabel = (id) => {
        if (!result) return null;
        if (isSimulating) return 'Simulating…';
        const change = result.risk_change ?? 0;
        if (id === 'stomach') return change > 0 ? `Risk ↑ ${change.toFixed(2)}` : `Risk ↓ ${Math.abs(change).toFixed(2)}`;
        if (id === 'pancreas') return change > 1 ? 'Insulin spike detected' : 'Insulin stable';
        if (id === 'brain') return change > 1 ? 'Stress response elevated' : 'Normal regulation';
        if (id === 'liver') return change > 1 ? 'Glucose processing ↑' : 'Normal metabolism';
        if (id === 'thyroid') return 'Metabolic adjustment';
        return null;
    };

    return (
        <>
            <ambientLight intensity={0.6} color="#e0d4f5" />
            <directionalLight position={[4, 8, 6]} intensity={1.05} color="#f0e6ff" castShadow />
            <directionalLight position={[-3, 4, -5]} intensity={0.25} color="#c084fc" />
            <hemisphereLight skyColor="#ddd6fe" groundColor="#3b0764" intensity={0.45} />
            <pointLight position={[0, 2.2, 2]} intensity={0.35} color="#a78bfa" distance={5} />

            <Float speed={1.4} rotationIntensity={0} floatIntensity={0.15} floatingRange={[-0.02, 0.02]}>
                <CharacterModel url={url} isSimulating={isSimulating} riskChange={result?.risk_change} />

                {result && IMPACT_ZONES.map((node) => (
                    <ImpactNode
                        key={node.id}
                        node={node}
                        color={getNodeColor(node.id)}
                        intensity={isSimulating ? 2.2 : 1.4}
                        label={getNodeLabel(node.id)}
                        isHovered={hoveredNode === node.id}
                        onHover={onNodeHover}
                    />
                ))}
            </Float>

            <ContactShadows position={[0, -0.44, 0]} opacity={0.35} scale={2} blur={2.5} far={1.2} color="#4c1d95" />
            <OrbitControls enableZoom={false} enablePan={false}
                           minPolarAngle={Math.PI * 0.3} maxPolarAngle={Math.PI * 0.7}
                           autoRotate autoRotateSpeed={0.5} enableDamping dampingFactor={0.08} />
        </>
    );
}

function LoadingFallback() {
    const ref = useRef();
    useFrame((state) => { if (ref.current) ref.current.rotation.y = state.clock.elapsedTime * 1.5; });
    return (
        <mesh ref={ref}>
            <boxGeometry args={[0.4, 1.2, 0.1]} />
            <meshStandardMaterial color="#7c3aed" transparent opacity={0.25} />
        </mesh>
    );
}

/* ── Status overlay ── */
function StatusOverlay({ isSimulating, result }) {
    if (isSimulating) {
        return (
            <div style={{
                position: 'absolute', bottom: 16, left: '50%', transform: 'translateX(-50%)',
                background: 'rgba(124,58,237,0.9)', backdropFilter: 'blur(10px)',
                borderRadius: 20, padding: '8px 20px',
                display: 'flex', alignItems: 'center', gap: 10, zIndex: 10,
            }}>
                <div style={{
                    width: 16, height: 16, border: '2px solid rgba(255,255,255,0.3)',
                    borderTopColor: '#fff', borderRadius: '50%',
                    animation: 'spin 0.8s linear infinite',
                }} />
                <span style={{ color: '#fff', fontSize: 12, fontWeight: 700 }}>Simulating impact…</span>
            </div>
        );
    }
    if (result) {
        const change = result.risk_change ?? 0;
        const isGood = change <= 0;
        return (
            <div style={{
                position: 'absolute', bottom: 16, left: '50%', transform: 'translateX(-50%)',
                background: isGood ? 'rgba(16,185,129,0.9)' : 'rgba(239,68,68,0.9)',
                backdropFilter: 'blur(10px)', borderRadius: 20, padding: '8px 20px', zIndex: 10,
            }}>
                <span style={{ color: '#fff', fontSize: 12, fontWeight: 700 }}>
                    {isGood ? '✓' : '⚠'} Risk {change > 0 ? '+' : ''}{change.toFixed(2)} — {result.risk_category_after}
                </span>
            </div>
        );
    }
    return null;
}

/* ── Main Component ── */
export default function SimulationCharacter() {
    const [profile, setProfile] = useState(null);
    const [loading, setLoading] = useState(true);
    const [isSimulating, setIsSimulating] = useState(false);
    const [result, setResult] = useState(null);
    const [hoveredNode, setHoveredNode] = useState(null);

    // Fetch health profile
    useEffect(() => {
        (async () => {
            try {
                const csrf = document.querySelector('meta[name=csrf-token]')?.content ?? '';
                const opts = { headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf }, credentials: 'same-origin' };
                const r = await fetch('/api/health-profile', opts);
                const d = await r.json();
                if (d?.success) setProfile(d.data);
            } catch (e) { console.error("Failed to load profile:", e); }
            finally { setLoading(false); }
        })();
    }, []);

    // Listen for simulation events dispatched from Alpine.js
    useEffect(() => {
        const handleStart = () => { setIsSimulating(true); setResult(null); };
        const handleResult = (e) => { setIsSimulating(false); setResult(e.detail); };
        const handleReset = () => { setIsSimulating(false); setResult(null); };

        window.addEventListener('sim:start', handleStart);
        window.addEventListener('sim:result', handleResult);
        window.addEventListener('sim:reset', handleReset);
        return () => {
            window.removeEventListener('sim:start', handleStart);
            window.removeEventListener('sim:result', handleResult);
            window.removeEventListener('sim:reset', handleReset);
        };
    }, []);

    if (loading) return (
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 40 }}>
            <div style={{ width: 32, height: 32, border: '3px solid #e9d5ff', borderTopColor: '#7c3aed', borderRadius: '50%', animation: 'spin 0.8s linear infinite' }} />
        </div>
    );

    const gender = profile?.gender || 'female';

    return (
        <div style={{ position: 'relative', width: '100%', height: '100%', minHeight: 420 }}>
            <style>{`@keyframes spin { to { transform: rotate(360deg); } }`}</style>
            <Canvas
                camera={{ position: [0, 0.85, 3.4], fov: 36, near: 0.1, far: 50 }}
                gl={{ antialias: true, alpha: true, powerPreference: 'high-performance' }}
                dpr={[1, 1.5]}
                style={{ background: 'transparent' }}
            >
                <Suspense fallback={<LoadingFallback />}>
                    <SceneContent
                        gender={gender}
                        isSimulating={isSimulating}
                        result={result}
                        hoveredNode={hoveredNode}
                        onNodeHover={setHoveredNode}
                    />
                </Suspense>
            </Canvas>
            <StatusOverlay isSimulating={isSimulating} result={result} />
        </div>
    );
}
