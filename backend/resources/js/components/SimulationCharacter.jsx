import React, { useRef, useMemo, useEffect, useState, useCallback, Suspense } from 'react';
import { inspectCharacter }  from './CharacterInspector';
import { Canvas, useFrame, useThree } from '@react-three/fiber';
import { OrbitControls, ContactShadows, Float, Html, useFBX } from '@react-three/drei';
import * as THREE from 'three';
import { SkeletonUtils } from 'three-stdlib';

/* ── Asset imports ── */
import maleFbxUrl   from './Ch09_nonPBR.fbx?url';
import femaleFbxUrl from './Standing Idle(1).fbx?url';

/* Animation FBX files (contain clips, not visible meshes) */
import idleAnimUrl  from './Standing Idle(1).fbx?url';
import clapAnimUrl  from './Clapping.fbx?url';
import sadAnimUrl   from './Sad Idle.fbx?url';

/* ── Constants ── */
const FADE_DURATION   = 0.3;  // seconds for cross-fade between animations
const TARGET_HEIGHT   = 1.6;  // normalized model height in scene units

/* ── Retarget animation tracks from one FBX skeleton to another ── */
function retargetClip(clip, targetRoot) {
    const nodeNames = new Set();
    targetRoot.traverse((n) => { if (n.name) nodeNames.add(n.name); });

    const retargeted = clip.clone();
    const valid = [];
    retargeted.tracks.forEach((track) => {
        const lastDot  = track.name.lastIndexOf('.');
        if (lastDot === -1) return;
        const nodePath = track.name.substring(0, lastDot);
        const property = track.name.substring(lastDot + 1);

        if (nodeNames.has(nodePath)) { valid.push(track); return; }

        const parts = nodePath.split('.');
        for (let i = 1; i < parts.length; i++) {
            const candidate = parts.slice(i).join('.');
            if (nodeNames.has(candidate)) {
                track.name = candidate + '.' + property;
                valid.push(track);
                return;
            }
        }
    });
    retargeted.tracks = valid;
    return retargeted;
}

/* ── Clone & prepare character mesh (no manual arm rotation — idle anim handles pose) ── */
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

        const capabilities = inspectCharacter(cloned, source.animations ?? []);
        return { model: cloned, capabilities };
    }, [source]);
}

/* ── Character mesh with animation (idle / clap / sad) ── */
function CharacterModel({ url, isSimulating, riskChange, isMale, onInspected }) {
    const groupRef    = useRef();
    const reportedRef = useRef(false);
    const mixerRef    = useRef(null);
    const actionsRef  = useRef({});
    const activeRef   = useRef(null);

    const { model: root, capabilities } = useCharacter(url);
    const emissiveColor = useMemo(() => new THREE.Color('#6d28d9'), []);

    const idleFbx  = useFBX(idleAnimUrl);
    const clapFbx  = useFBX(clapAnimUrl);
    const sadFbx   = useFBX(sadAnimUrl);

    useEffect(() => {
        if (!root) return;
        const mixer = new THREE.AnimationMixer(root);
        mixerRef.current = mixer;

        const load = (fbx, loop = true) => {
            if (!fbx?.animations?.length) return null;
            const clip   = retargetClip(fbx.animations[0], root);
            if (!clip.tracks.length) return null;
            const action = mixer.clipAction(clip);
            action.setLoop(loop ? THREE.LoopRepeat : THREE.LoopOnce, loop ? Infinity : 1);
            action.clampWhenFinished = !loop;
            return action;
        };

        const actions = {
            idle: load(idleFbx, true),
            clap: load(clapFbx, false),
            sad:  load(sadFbx, true),
        };
        actionsRef.current = actions;

        if (actions.idle) { actions.idle.play(); activeRef.current = actions.idle; }

        const onFinished = (e) => {
            if (e.action === actions.clap) crossFade(actions.idle, true);
        };
        mixer.addEventListener('finished', onFinished);

        return () => {
            mixer.removeEventListener('finished', onFinished);
            mixer.stopAllAction();
            mixer.uncacheRoot(root);
        };
    }, [root]); // eslint-disable-line react-hooks/exhaustive-deps

    useEffect(() => {
        if (capabilities && !reportedRef.current) {
            reportedRef.current = true;
            onInspected?.(capabilities);
        }
    }, [capabilities]); // eslint-disable-line react-hooks/exhaustive-deps

    const crossFade = useCallback((nextAction, loop = true) => {
        if (!nextAction || nextAction === activeRef.current) return;
        const prev = activeRef.current;
        nextAction.reset();
        nextAction.setLoop(loop ? THREE.LoopRepeat : THREE.LoopOnce, loop ? Infinity : 1);
        nextAction.clampWhenFinished = !loop;
        nextAction.setEffectiveTimeScale(1);
        nextAction.setEffectiveWeight(1);
        nextAction.fadeIn(FADE_DURATION);
        if (prev) prev.fadeOut(FADE_DURATION);
        nextAction.play();
        activeRef.current = nextAction;
    }, []);

    const playSimReaction = useCallback((positive) => {
        const actions = actionsRef.current;
        if (positive && actions.clap) crossFade(actions.clap, false);
        else if (!positive && actions.sad) crossFade(actions.sad, true);
    }, [crossFade]);

    const returnToIdle = useCallback(() => {
        if (actionsRef.current.idle) crossFade(actionsRef.current.idle, true);
    }, [crossFade]);

    useEffect(() => {
        if (groupRef.current) groupRef.current.userData._anim = { playSimReaction, returnToIdle };
    }, [playSimReaction, returnToIdle]);

    /* ── Normalise model ── */
    const normalized = useMemo(() => {
        const box    = new THREE.Box3().setFromObject(root);
        const size   = box.getSize(new THREE.Vector3());
        const center = box.getCenter(new THREE.Vector3());
        const ratio  = size.y > 0 ? TARGET_HEIGHT / size.y : 1;
        return { scale: ratio, centerX: center.x, minY: box.min.y, centerZ: center.z };
    }, [root]);

    /* ── Material setup ── */
    const meshMaterials = useMemo(() => {
        const mats = [];
        root.traverse((child) => {
            if (!child.isMesh || Array.isArray(child.material) || !child.material) return;
            child.material.emissive = emissiveColor;
            mats.push(child.material);
        });
        return mats;
    }, [root, emissiveColor]);

    /* ── Per-frame: mixer, scale, glow, hover ── */
    useFrame((_, delta) => {
        mixerRef.current?.update(delta);

        if (!groupRef.current) return;
        const pulse = isSimulating ? (1 + Math.sin(performance.now() * 0.005) * 0.008) : 1;
        const s = normalized.scale * pulse;
        groupRef.current.scale.set(s, s, s);

        const baseIntensity = 0.03;
        const riskGlow = riskChange != null ? Math.min(0.15, Math.abs(riskChange) * 0.02) : 0;
        meshMaterials.forEach((mat) => { mat.emissiveIntensity = baseIntensity + riskGlow; });
    });

    return (
        <group ref={groupRef}
               position={[
                   -normalized.centerX * normalized.scale,
                   -normalized.minY * normalized.scale - 0.44,
                   -normalized.centerZ * normalized.scale,
               ]}>
            <primitive object={root} />
        </group>
    );
}

/* ── Scene ── */
function SceneContent({ gender, isSimulating, result, onInspected }) {
    const url = gender === 'male' ? maleFbxUrl : femaleFbxUrl;

    return (
        <>
            <ambientLight intensity={0.6} color="#e0d4f5" />
            <directionalLight position={[4, 8, 6]} intensity={1.05} color="#f0e6ff" castShadow />
            <directionalLight position={[-3, 4, -5]} intensity={0.25} color="#c084fc" />
            <hemisphereLight skyColor="#ddd6fe" groundColor="#3b0764" intensity={0.45} />
            <pointLight position={[0, 2.2, 2]} intensity={0.35} color="#a78bfa" distance={5} />

            <Float speed={1.4} rotationIntensity={0} floatIntensity={0.15} floatingRange={[-0.02, 0.02]}>
                <CharacterModel
                    url={url}
                    isSimulating={isSimulating}
                    riskChange={result?.risk_change}
                    isMale={gender === 'male'}
                    onInspected={onInspected}
                />
            </Float>

            <ContactShadows position={[0, -0.44, 0]} opacity={0.35} scale={2} blur={2.5} far={1.2} color="#4c1d95" />
            <OrbitControls enableZoom={false} enablePan={false}
                           target={[0, 0.56, 0]}
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
function StatusOverlay({ isSimulating }) {
    if (!isSimulating) return null;
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

/* ── Main Component ── */
export default function SimulationCharacter() {
    const [profile, setProfile]           = useState(null);
    const [loading, setLoading]           = useState(true);
    const [isSimulating, setIsSimulating] = useState(false);
    const [result, setResult]             = useState(null);
    const [canvasError, setCanvasError]   = useState(null);
    const containerRef = useRef(null);
    const charAnimRef  = useRef(null);

    const handleInspected = useCallback(() => {}, []);

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
        const handleStart  = () => { setIsSimulating(true); setResult(null); };
        const handleResult = (e) => {
            setIsSimulating(false);
            const detail = e.detail;
            setResult(detail);

            // Trigger character reaction based on result
            // We need a small delay to let the Canvas re-render with the new result
            // so the CharacterModel._anim ref is populated.
            requestAnimationFrame(() => {
                // Walk up from the canvas to find the charGroup's _anim handle
                // stored via userData in CharacterModel's useEffect
                if (charAnimRef.current) {
                    const positive = (detail?.risk_change ?? 0) <= 0;
                    charAnimRef.current.playSimReaction(positive);

                    // If it's a one-shot reaction (clap), mixer 'finished' returns to idle.
                    // For sad (loop), return to idle after 5 seconds.
                    if (!positive) {
                        setTimeout(() => charAnimRef.current?.returnToIdle(), 5000);
                    }
                }
            });
        };
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
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', height: '100%', minHeight: 480 }}>
            <div style={{ width: 32, height: 32, border: '3px solid #e9d5ff', borderTopColor: '#7c3aed', borderRadius: '50%', animation: 'spin 0.8s linear infinite' }} />
        </div>
    );

    const gender = profile?.gender || 'female';

    if (canvasError) return (
        <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', height: '100%', minHeight: 480, color: '#6b7280', fontFamily: 'system-ui' }}>
            <div style={{ fontSize: 48, marginBottom: 12 }}>🧍</div>
            <p style={{ fontSize: 12, fontWeight: 600 }}>3D model loading failed</p>
            <p style={{ fontSize: 10, color: '#9ca3af', marginTop: 4 }}>{canvasError}</p>
        </div>
    );

    return (
        <div ref={containerRef} style={{ position: 'relative', width: '100%', height: '100%', minHeight: 480 }}>
            <style>{`@keyframes spin { to { transform: rotate(360deg); } }`}</style>
            <Canvas
                camera={{ position: [0, 0.56, 3.2], fov: 42, near: 0.1, far: 50 }}
                gl={{ antialias: true, alpha: true, powerPreference: 'high-performance' }}
                dpr={[1, 1.5]}
                style={{ background: 'transparent', width: '100%', height: '100%' }}
                onCreated={({ gl }) => { gl.setClearColor(0x000000, 0); }}
            >
                <Suspense fallback={<LoadingFallback />}>
                    <SceneContent
                        gender={gender}
                        isSimulating={isSimulating}
                        result={result}
                        onInspected={handleInspected}
                    />
                    {/* Invisible helper to capture the CharacterModel's imperative anim ref */}
                    <CharAnimRefCapture targetRef={charAnimRef} />
                </Suspense>
            </Canvas>

            <StatusOverlay isSimulating={isSimulating} />
        </div>
    );
}

/* Helper: captures CharacterModel._anim from the scene graph into a parent ref.
   Runs once per frame — near-zero cost. */
function CharAnimRefCapture({ targetRef }) {
    const { scene } = useThree();
    const captured = useRef(false);

    useFrame(() => {
        if (captured.current && targetRef.current) return;
        scene.traverse((child) => {
            if (child.userData?._anim) {
                targetRef.current = child.userData._anim;
                captured.current  = true;
            }
        });
    });
    return null;
}
