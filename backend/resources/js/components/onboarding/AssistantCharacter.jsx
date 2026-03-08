import React, { useRef, useEffect, useMemo, useState } from 'react';
import { Canvas, useFrame, useLoader } from '@react-three/fiber';
import { OrbitControls, ContactShadows, useGLTF } from '@react-three/drei';
import * as THREE from 'three';
import { SkeletonUtils } from 'three-stdlib';
import { GLTFLoader } from 'three-stdlib';

import characterUrl from '../Standing Idle(1).glb?url';
import wavingUrl from '../Waving.glb?url';
import clappingUrl from '../Clapping.glb?url';
import spinUrl from '../Northern Soul Spin Combo.glb?url';

const TARGET_HEIGHT = 1.7;
const CROSSFADE_DURATION = 0.4;

/* ── Retarget animation tracks ── */
function retargetClip(clip, targetRoot) {
    const nodeNames = new Set();
    targetRoot.traverse((n) => { if (n.name) nodeNames.add(n.name); });

    const retargeted = clip.clone();
    const valid = [];
    retargeted.tracks.forEach((track) => {
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
    retargeted.tracks = valid;
    return retargeted;
}

/* ── Animation state: idle | wave | clap | spin ── */
const ANIM_URLS = {
    idle: null, // embedded in the character FBX
    wave: wavingUrl,
    clap: clappingUrl,
    spin: spinUrl,
};

/* ── Inner 3D character with multi-animation support ── */
function CharacterMesh({ animationState, isSpeaking }) {
    const groupRef = useRef();
    const mixerRef = useRef(null);
    const actionsRef = useRef({});
    const currentActionRef = useRef(null);
    const { scene: source, animations: sourceAnims } = useGLTF(characterUrl);

    /* Load external animation GLBs */
    const { scene: waveGltf, animations: waveAnims } = useLoader(GLTFLoader, wavingUrl);
    const { scene: clapGltf, animations: clapAnims } = useLoader(GLTFLoader, clappingUrl);
    const { scene: spinGltf, animations: spinAnims } = useLoader(GLTFLoader, spinUrl);

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

    /* Set up mixer + all animations */
    useEffect(() => {
        if (!model) return;
        const mixer = new THREE.AnimationMixer(model);
        mixerRef.current = mixer;
        const actions = {};

        /* Idle clip from the character GLB */
        if (sourceAnims?.length) {
            const clip = retargetClip(sourceAnims[0], model);
            if (clip.tracks.length) {
                actions.idle = mixer.clipAction(clip);
                actions.idle.setLoop(THREE.LoopRepeat, Infinity);
            }
        }

        /* Wave */
        if (waveAnims?.length) {
            const clip = retargetClip(waveAnims[0], model);
            if (clip.tracks.length) {
                actions.wave = mixer.clipAction(clip);
                actions.wave.setLoop(THREE.LoopRepeat, 2);
                actions.wave.clampWhenFinished = true;
            }
        }

        /* Clap */
        if (clapAnims?.length) {
            const clip = retargetClip(clapAnims[0], model);
            if (clip.tracks.length) {
                actions.clap = mixer.clipAction(clip);
                actions.clap.setLoop(THREE.LoopRepeat, 3);
                actions.clap.clampWhenFinished = true;
            }
        }

        /* Spin */
        if (spinAnims?.length) {
            const clip = retargetClip(spinAnims[0], model);
            if (clip.tracks.length) {
                actions.spin = mixer.clipAction(clip);
                actions.spin.setLoop(THREE.LoopOnce, 1);
                actions.spin.clampWhenFinished = true;
            }
        }

        actionsRef.current = actions;

        /* Start idle */
        if (actions.idle) {
            actions.idle.play();
            currentActionRef.current = actions.idle;
        }

        /* Return to idle when a non-looping animation finishes */
        const onFinished = (e) => {
            const finishedAction = e.action;
            if (finishedAction !== actions.idle && actions.idle) {
                finishedAction.fadeOut(CROSSFADE_DURATION);
                actions.idle.reset().fadeIn(CROSSFADE_DURATION).play();
                currentActionRef.current = actions.idle;
            }
        };
        mixer.addEventListener('finished', onFinished);

        return () => {
            mixer.removeEventListener('finished', onFinished);
            mixer.stopAllAction();
            mixer.uncacheRoot(model);
        };
    }, [model, source, sourceAnims, waveAnims, clapAnims, spinAnims]);

    /* Transition animations on state change */
    useEffect(() => {
        const actions = actionsRef.current;
        const target = actions[animationState];
        const current = currentActionRef.current;
        if (!target || target === current) return;

        if (current) current.fadeOut(CROSSFADE_DURATION);
        target.reset().fadeIn(CROSSFADE_DURATION).play();
        currentActionRef.current = target;
    }, [animationState]);

    /* Normalize size */
    const normalized = useMemo(() => {
        const box = new THREE.Box3().setFromObject(model);
        const size = box.getSize(new THREE.Vector3());
        const center = box.getCenter(new THREE.Vector3());
        const ratio = size.y > 0 ? TARGET_HEIGHT / size.y : 1;
        return { scale: ratio, centerX: center.x, minY: box.min.y, centerZ: center.z };
    }, [model]);

    /* Per-frame: mixer + subtle breathing */
    useFrame((_, delta) => {
        mixerRef.current?.update(delta);
        if (!groupRef.current) return;

        const t = performance.now() * 0.001;
        const breathe = 1 + Math.sin(t * 1.8) * 0.003;
        const s = normalized.scale * breathe;
        groupRef.current.scale.set(s, s, s);

        /* Subtle sway when speaking */
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

/* ── Exported Canvas wrapper ── */
export default function AssistantCharacter({ isSpeaking = false, animationState = 'idle' }) {
    return (
        <Canvas
            camera={{ position: [0, 0.55, 3.2], fov: 40, near: 0.1, far: 50 }}
            gl={{ antialias: true, alpha: true, powerPreference: 'high-performance' }}
            dpr={[1, 1.5]}
            style={{ background: 'transparent', width: '100%', height: '100%' }}
            onCreated={({ gl }) => gl.setClearColor(0x000000, 0)}
        >
            <ambientLight intensity={0.7} color="#f5f0ff" />
            <directionalLight position={[3, 6, 5]} intensity={1.1} color="#f8f4ff" castShadow />
            <directionalLight position={[-2, 3, -4]} intensity={0.2} color="#c084fc" />
            <hemisphereLight skyColor="#ede9fe" groundColor="#f5f3ff" intensity={0.5} />

            <CharacterMesh isSpeaking={isSpeaking} animationState={animationState} />

            <ContactShadows
                position={[0, -0.48, 0]}
                opacity={0.25}
                scale={2.5}
                blur={2.5}
                far={1.2}
                color="#7c3aed"
            />
            <OrbitControls
                enableZoom={false}
                enablePan={false}
                target={[0, 0.5, 0]}
                minPolarAngle={Math.PI * 0.35}
                maxPolarAngle={Math.PI * 0.65}
                enableDamping
                dampingFactor={0.08}
            />
        </Canvas>
    );
}
