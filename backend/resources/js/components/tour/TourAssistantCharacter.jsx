import React, { useRef, useEffect, useMemo } from 'react';
import { Canvas, useFrame } from '@react-three/fiber';
import { OrbitControls, ContactShadows, useFBX } from '@react-three/drei';
import * as THREE from 'three';
import { SkeletonUtils } from 'three-stdlib';

import characterUrl from '../Standing Idle(1).fbx?url';

const TARGET_HEIGHT = 1.7;

/* Retarget animation tracks (same logic as onboarding) */
function retargetClip(clip, targetRoot) {
    const nodeNames = new Set();
    targetRoot.traverse((n) => { if (n.name) nodeNames.add(n.name); });

    const cloned = clip.clone();
    const valid = [];
    cloned.tracks.forEach((track) => {
        const lastDot = track.name.lastIndexOf('.');
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
    cloned.tracks = valid;
    return cloned;
}

/* Inner 3D mesh */
function CharacterMesh({ isSpeaking }) {
    const groupRef = useRef();
    const mixerRef = useRef(null);
    const source = useFBX(characterUrl);

    const model = useMemo(() => {
        const cloned = SkeletonUtils.clone(source);
        cloned.traverse((child) => {
            if (!child.isMesh) return;
            child.castShadow = true;
            child.receiveShadow = true;
            if (child.material && !Array.isArray(child.material)) {
                child.material = child.material.clone();
                child.material.transparent = false;
                child.material.roughness = 0.55;
                child.material.metalness = 0.05;
            }
        });
        return cloned;
    }, [source]);

    useEffect(() => {
        if (!model) return;
        const mixer = new THREE.AnimationMixer(model);
        mixerRef.current = mixer;

        if (source.animations?.length) {
            const clip = retargetClip(source.animations[0], model);
            if (clip.tracks.length) {
                const action = mixer.clipAction(clip);
                action.setLoop(THREE.LoopRepeat, Infinity);
                action.play();
            }
        }
        return () => { mixer.stopAllAction(); mixer.uncacheRoot(model); };
    }, [model, source]);

    const normalized = useMemo(() => {
        const box = new THREE.Box3().setFromObject(model);
        const size = box.getSize(new THREE.Vector3());
        const center = box.getCenter(new THREE.Vector3());
        const ratio = size.y > 0 ? TARGET_HEIGHT / size.y : 1;
        return { scale: ratio, centerX: center.x, minY: box.min.y, centerZ: center.z };
    }, [model]);

    useFrame((_, delta) => {
        mixerRef.current?.update(delta);
        if (!groupRef.current) return;

        const t = performance.now() * 0.001;
        const breathe = 1 + Math.sin(t * 1.8) * 0.003;
        const s = normalized.scale * breathe;
        groupRef.current.scale.set(s, s, s);

        if (isSpeaking) {
            groupRef.current.rotation.y = Math.sin(t * 0.7) * 0.02;
        } else {
            groupRef.current.rotation.y *= 0.95;
        }
    });

    return (
        <group
            ref={groupRef}
            position={[
                -normalized.centerX * normalized.scale,
                -normalized.minY * normalized.scale - 0.48,
                -normalized.centerZ * normalized.scale,
            ]}
        >
            <primitive object={model} />
        </group>
    );
}

/* Exported Canvas wrapper — compact for tour use */
export default function TourAssistantCharacter({ isSpeaking = false }) {
    return (
        <Canvas
            camera={{ position: [0, 0.5, 2.8], fov: 38, near: 0.1, far: 50 }}
            gl={{ antialias: true, alpha: true, powerPreference: 'high-performance' }}
            dpr={[1, 1.5]}
            style={{ background: 'transparent', width: '100%', height: '100%' }}
            onCreated={({ gl }) => gl.setClearColor(0x000000, 0)}
        >
            <ambientLight intensity={0.7} color="#f5f0ff" />
            <directionalLight position={[3, 6, 5]} intensity={1.1} color="#f8f4ff" castShadow />
            <directionalLight position={[-2, 3, -4]} intensity={0.2} color="#c084fc" />
            <hemisphereLight skyColor="#ede9fe" groundColor="#f5f3ff" intensity={0.5} />

            <CharacterMesh isSpeaking={isSpeaking} />

            <ContactShadows
                position={[0, -0.48, 0]}
                opacity={0.2} scale={2} blur={2.5} far={1.2}
                color="#7c3aed"
            />
            <OrbitControls
                enableZoom={false}
                enablePan={false}
                target={[0, 0.45, 0]}
                minPolarAngle={Math.PI * 0.35}
                maxPolarAngle={Math.PI * 0.65}
                enableDamping dampingFactor={0.08}
            />
        </Canvas>
    );
}
