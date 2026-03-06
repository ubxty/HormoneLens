/**
 * CharacterViewer.jsx
 * ─────────────────────────────────────────────────────────────────────────────
 * Standalone Three.js character viewer with built-in debug inspection.
 * Suitable for previewing any FBX model independently — no Alpine.js events,
 * no impact zones, just the model + debug panel.
 *
 * Props:
 *   fbxUrl   {string}    Vite-resolved URL for the FBX file (import as ?url)
 *   gender   {string}    'male' | 'female'  (cosmetic only)
 *   style    {object}    Optional style overrides for the outer container
 *   onReady  {(caps: CharacterCapabilities) => void}
 *            Called once, right after the model is loaded and inspected.
 *            Useful when the parent needs the capability report too.
 *
 * Usage:
 *   import CharacterViewer from './CharacterViewer';
 *   import modelUrl from './MyCharacter.fbx?url';
 *
 *   <CharacterViewer fbxUrl={modelUrl} gender="female" />
 */

import React, {
    useRef,
    useMemo,
    useState,
    useEffect,
    useCallback,
    Suspense,
    memo,
} from 'react';
import { Canvas, useFrame }                                  from '@react-three/fiber';
import { OrbitControls, ContactShadows, Float, useFBX }     from '@react-three/drei';
import * as THREE                                            from 'three';
import { SkeletonUtils }                                     from 'three-stdlib';

import { inspectCharacter }  from './CharacterInspector';
import CharacterDebugPanel   from './CharacterDebugPanel';

// ─────────────────────────────────────────────────────────────────────────────
// Internal: Three.js component — loads, clones, normalises and renders the FBX
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Loads and renders a single FBX via useFBX (cached by R3F).
 * Calls onInspected once after the model is ready.
 *
 * @param {{ fbxUrl: string, onInspected: Function }} props
 */
const CharacterMesh = memo(({ fbxUrl, onInspected }) => {
    // useFBX suspends until the asset is ready — Suspense handles the fallback
    const source       = useFBX(fbxUrl);
    const groupRef     = useRef();
    const reportedRef  = useRef(false); // guard: fire onInspected only once

    // ── Clone + process the source object ────────────────────────────────────
    // useMemo runs once per unique source reference (i.e. once per URL)
    const { root, normalized, capabilities } = useMemo(() => {
        const cloned = SkeletonUtils.clone(source);

        // Material setup
        cloned.traverse((child) => {
            if (!child.isMesh) return;
            child.castShadow    = true;
            child.receiveShadow = true;
            if (child.material && !Array.isArray(child.material)) {
                child.material              = child.material.clone();
                child.material.transparent  = false;
                child.material.roughness    = 0.6;
                child.material.metalness    = 0.05;
            }
        });

        // Bring Mixamo T-pose arms down to a relaxed standing position.
        // Covers the three common Mixamo naming conventions.
        const boneMap = {};
        cloned.traverse((c) => { if (c.name) boneMap[c.name] = c; });
        const get = (...names) => names.reduce((found, n) => found || boneMap[n], null);

        const leftArm   = get('mixamorigLeftArm',      'mixamorig6:LeftArm',      'mixamorig:LeftArm',      'LeftArm');
        const rightArm  = get('mixamorigRightArm',     'mixamorig6:RightArm',     'mixamorig:RightArm',     'RightArm');
        const leftFore  = get('mixamorigLeftForeArm',  'mixamorig6:LeftForeArm',  'mixamorig:LeftForeArm',  'LeftForeArm');
        const rightFore = get('mixamorigRightForeArm', 'mixamorig6:RightForeArm', 'mixamorig:RightForeArm', 'RightForeArm');

        if (leftArm)  leftArm.rotation.z  = -Math.PI * 0.5;
        if (rightArm) rightArm.rotation.z =  Math.PI * 0.5;
        if (leftFore)  leftFore.rotation.z  = -Math.PI * 0.06;
        if (rightFore) rightFore.rotation.z =  Math.PI * 0.06;

        // Centre & normalise scale so the model is always 2 units tall
        const box    = new THREE.Box3().setFromObject(cloned);
        const size   = box.getSize(new THREE.Vector3());
        const center = box.getCenter(new THREE.Vector3());
        const scale  = size.y > 0 ? 2.0 / size.y : 1;

        // Run the capability inspector — also prints the console report
        const caps = inspectCharacter(cloned, source.animations ?? []);

        return {
            root:         cloned,
            normalized:   { scale, centerX: center.x, minY: box.min.y, centerZ: center.z },
            capabilities: caps,
        };
    }, [source]);

    // ── Fire onInspected exactly once when capabilities become available ──────
    useEffect(() => {
        if (capabilities && !reportedRef.current) {
            reportedRef.current = true;
            onInspected?.(capabilities);
        }
    }, [capabilities]); // eslint-disable-line react-hooks/exhaustive-deps

    // ── Apply normalised scale each frame (cheap, no GC) ─────────────────────
    useFrame(() => {
        groupRef.current?.scale.setScalar(normalized.scale);
    });

    return (
        <group
            ref={groupRef}
            position={[
                -normalized.centerX * normalized.scale,
                -normalized.minY    * normalized.scale - 0.44,
                -normalized.centerZ * normalized.scale,
            ]}
        >
            <primitive object={root} />
        </group>
    );
});

CharacterMesh.displayName = 'CharacterMesh';

// ─────────────────────────────────────────────────────────────────────────────
// Suspense fallback — visible while FBX is being fetched
// ─────────────────────────────────────────────────────────────────────────────
function SpinningFallback() {
    const ref = useRef();
    useFrame((s) => { if (ref.current) ref.current.rotation.y = s.clock.elapsedTime * 1.5; });
    return (
        <mesh ref={ref}>
            <boxGeometry args={[0.4, 1.2, 0.1]} />
            <meshStandardMaterial color="#7c3aed" transparent opacity={0.3} />
        </mesh>
    );
}

// ─────────────────────────────────────────────────────────────────────────────
// CharacterViewer — exported component
// ─────────────────────────────────────────────────────────────────────────────

export default function CharacterViewer({ fbxUrl, gender = 'female', style, onReady }) {
    // Capability report populated after the model loads
    const [capabilities, setCapabilities] = useState(null);
    // Toggle state for the debug overlay
    const [debugVisible, setDebugVisible] = useState(false);

    // Stable callbacks — avoid passing new references each render
    const handleInspected = useCallback((caps) => {
        setCapabilities(caps);
        onReady?.(caps); // optionally propagate to parent
    }, [onReady]);

    const toggleDebug = useCallback(() => setDebugVisible((v) => !v), []);

    return (
        // position:relative is required so the absolutely-positioned
        // debug panel is contained within this element
        <div style={{
            position:  'relative',
            width:     '100%',
            height:    '100%',
            minHeight: 480,
            ...style,
        }}>
            {/* ── Three.js canvas — transparent background so it composes cleanly ── */}
            <Canvas
                camera={{ position: [0, 0.56, 3.2], fov: 42, near: 0.1, far: 50 }}
                gl={{ antialias: true, alpha: true, powerPreference: 'high-performance' }}
                dpr={[1, 1.5]}
                style={{ background: 'transparent', width: '100%', height: '100%' }}
                onCreated={({ gl }) => gl.setClearColor(0x000000, 0)}
            >
                {/* Lighting — soft purple-tinted setup */}
                <ambientLight     intensity={0.6}  color="#e0d4f5" />
                <directionalLight position={[4, 8, 6]}   intensity={1.05} color="#f0e6ff" castShadow />
                <directionalLight position={[-3, 4, -5]} intensity={0.25} color="#c084fc" />
                <hemisphereLight  skyColor="#ddd6fe" groundColor="#3b0764" intensity={0.45} />
                <pointLight       position={[0, 2.2, 2]} intensity={0.35} color="#a78bfa" distance={5} />

                {/* Gentle floating animation */}
                <Float speed={1.4} rotationIntensity={0} floatIntensity={0.15} floatingRange={[-0.02, 0.02]}>
                    <Suspense fallback={<SpinningFallback />}>
                        <CharacterMesh fbxUrl={fbxUrl} onInspected={handleInspected} />
                    </Suspense>
                </Float>

                {/* Ground shadow */}
                <ContactShadows
                    position={[0, -0.44, 0]}
                    opacity={0.35}
                    scale={2}
                    blur={2.5}
                    far={1.2}
                    color="#4c1d95"
                />

                {/* Orbit controls — zoom/pan disabled, gentle auto-rotation */}
                <OrbitControls
                    enableZoom={false}
                    enablePan={false}
                    target={[0, 0.56, 0]}
                    minPolarAngle={Math.PI * 0.3}
                    maxPolarAngle={Math.PI * 0.7}
                    autoRotate
                    autoRotateSpeed={0.5}
                    enableDamping
                    dampingFactor={0.08}
                />
            </Canvas>

            {/* ── Debug panel overlay — lives outside the canvas, plain DOM ──
                Positioned absolutely within this container.
                The CharacterDebugPanel is wrapped in React.memo and only
                re-renders when capabilities or visibility changes. ── */}
            <CharacterDebugPanel
                capabilities={capabilities}
                visible={debugVisible}
                onToggle={toggleDebug}
            />
        </div>
    );
}
