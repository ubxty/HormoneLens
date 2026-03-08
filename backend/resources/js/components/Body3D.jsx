import React, { useRef, useMemo, Suspense } from 'react';
import { Canvas, useFrame } from '@react-three/fiber';
import { OrbitControls, Html, ContactShadows, Float, useGLTF } from '@react-three/drei';
import * as THREE from 'three';
import { SkeletonUtils } from 'three-stdlib';
import characterGlbUrl from './Ch09_nonPBR.glb?url';

const HORMONE_NODES = [
  { id: 'pituitary', label: 'Pituitary Gland', info: 'Master endocrine regulator · HPA Axis',     pos: [0, 1.64, 0.14],    r: 0.032 },
  { id: 'thyroid',   label: 'Thyroid',          info: 'T3/T4 metabolic rate control',              pos: [0, 1.42, 0.06],    r: 0.036 },
  { id: 'pancreas',  label: 'Pancreas',         info: 'Insulin & glucagon secretion',              pos: [0.08, 0.91, 0.1],  r: 0.034 },
  { id: 'ovaryL',    label: 'Left Ovary',       info: 'Estrogen · Progesterone · PCOS marker',    pos: [-0.09, 0.67, 0.06], r: 0.026 },
  { id: 'ovaryR',    label: 'Right Ovary',      info: 'Estrogen · Progesterone · PCOS marker',    pos: [0.09, 0.67, 0.06],  r: 0.026 },
];

function clamp01(value) {
  return Math.min(1, Math.max(0, value));
}

function getNodeColor(id, pcosIdx, insulinRes, thyroidFac, sim) {
  if (sim) return '#10b981';
  switch (id) {
    case 'ovaryL':
    case 'ovaryR':
      return pcosIdx > 5 ? '#a855f7' : pcosIdx > 2 ? '#c084fc' : '#10b981';
    case 'pancreas':
      return insulinRes > 5 ? '#3b82f6' : insulinRes > 2 ? '#60a5fa' : '#10b981';
    case 'thyroid':
      return thyroidFac < 0.4 ? '#f97316' : thyroidFac < 0.7 ? '#fb923c' : '#10b981';
    default:
      return '#8b5cf6';
  }
}

function getNodeStatus(id, pcosIdx, insulinRes, thyroidFac) {
  switch (id) {
    case 'ovaryL':
    case 'ovaryR':
      return pcosIdx > 5
        ? `PCOS Imbalance ↑ ${(pcosIdx * 10).toFixed(0)}%`
        : pcosIdx > 2
        ? `Mild PCOS Signal ↑ ${(pcosIdx * 10).toFixed(0)}%`
        : 'Normal Range';
    case 'pancreas':
      return insulinRes > 5
        ? `Insulin Sensitivity ↓ ${(insulinRes * 12).toFixed(0)}%`
        : insulinRes > 2
        ? `Mild Resistance ↓ ${(insulinRes * 7).toFixed(0)}%`
        : 'Normal Range';
    case 'thyroid':
      return thyroidFac < 0.4
        ? `Thyroid Dysfunction ↓ ${((1 - thyroidFac) * 100).toFixed(0)}%`
        : thyroidFac < 0.7
        ? `Mild Hypofunction ↓ ${((1 - thyroidFac) * 100).toFixed(0)}%`
        : 'Normal Range';
    default:
      return 'Active Regulation';
  }
}

function HormoneNode({ node, color, intensity, isHovered, onHover, statusText }) {
  const glowRef = useRef();
  const coreRef = useRef();
  const idx = HORMONE_NODES.indexOf(node);

  useFrame(function (state) {
    const t = state.clock.elapsedTime;
    if (glowRef.current) {
      const pulse = 1.0 + Math.sin(t * 2.2 + idx * 1.4) * 0.35;
      glowRef.current.scale.setScalar(pulse);
      glowRef.current.material.opacity = 0.12 + Math.sin(t * 2.2 + idx) * 0.06;
    }
    if (coreRef.current) {
      coreRef.current.material.emissiveIntensity = intensity * (0.7 + Math.sin(t * 3 + idx) * 0.4);
    }
  });

  return (
    <group position={node.pos}>
      <mesh
        ref={coreRef}
        onPointerEnter={function (e) {
          e.stopPropagation();
          onHover(node.id);
        }}
        onPointerLeave={function (e) {
          e.stopPropagation();
          onHover(null);
        }}
      >
        <sphereGeometry args={[node.r, 20, 20]} />
        <meshStandardMaterial color={color} emissive={color} emissiveIntensity={intensity} toneMapped={false} />
      </mesh>

      <mesh ref={glowRef}>
        <sphereGeometry args={[node.r * 2.8, 16, 16]} />
        <meshBasicMaterial color={color} transparent opacity={0.12} depthWrite={false} />
      </mesh>

      {isHovered && (
        <Html center distanceFactor={5.5} style={{ pointerEvents: 'none', userSelect: 'none' }}>
          <div
            style={{
              background: 'rgba(12,6,22,0.94)',
              backdropFilter: 'blur(16px)',
              WebkitBackdropFilter: 'blur(16px)',
              border: '1px solid ' + color + '55',
              borderRadius: 14,
              padding: '12px 16px',
              minWidth: 170,
              maxWidth: 220,
              boxShadow: '0 10px 40px ' + color + '44, 0 2px 8px rgba(0,0,0,0.3)',
              fontFamily: 'system-ui, -apple-system, sans-serif',
            }}
          >
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 6 }}>
              <div style={{ width: 10, height: 10, borderRadius: '50%', background: color, boxShadow: '0 0 8px ' + color }} />
              <span style={{ fontSize: 12, fontWeight: 800, color: '#e2ddf5', letterSpacing: '0.02em' }}>{node.label}</span>
            </div>
            <p style={{ fontSize: 10, color: '#9b8fc4', margin: '0 0 8px', lineHeight: 1.4 }}>{node.info}</p>
            <div
              style={{
                padding: '6px 10px',
                background: color + '18',
                borderRadius: 8,
                border: '1px solid ' + color + '30',
              }}
            >
              <span style={{ fontSize: 11, fontWeight: 700, color: color }}>{statusText}</span>
            </div>
          </div>
        </Html>
      )}
    </group>
  );
}

function useCharacterModel() {
  const { scene: source } = useGLTF(characterGlbUrl);

  return useMemo(function () {
    const cloned = SkeletonUtils.clone(source);
    let bodyMesh = null;

    cloned.traverse(function (child) {
      if (!child.isMesh) return;
      if (!bodyMesh && /body/i.test(child.name)) bodyMesh = child;

      child.castShadow = true;
      child.receiveShadow = true;

      if (child.material && !Array.isArray(child.material)) {
        child.material = child.material.clone();
        child.material.transparent = false;
        child.material.roughness = 0.6;
        child.material.metalness = 0.05;
      }
    });

    return { root: cloned, bodyMesh };
  }, [source]);
}

function FbxCharacter({ bodyShape, baseHeightScale, isSimulating, thyroidFactor }) {
  const groupRef = useRef();
  const shapeRef = useRef(clamp01(bodyShape));
  const { root, bodyMesh } = useCharacterModel();
  const emissiveColor = useMemo(() => new THREE.Color('#6d28d9'), []);

  const parts = useMemo(function () {
    const byName = {};
    root.traverse(function (child) {
      byName[child.name] = child;
    });

    const hips = byName['mixamorig6:Hips'];
    const spine = byName['mixamorig6:Spine'];
    const spine1 = byName['mixamorig6:Spine1'];
    const spine2 = byName['mixamorig6:Spine2'];
    const leftUpLeg = byName['mixamorig6:LeftUpLeg'];
    const rightUpLeg = byName['mixamorig6:RightUpLeg'];

    return { hips, spine, spine1, spine2, leftUpLeg, rightUpLeg };
  }, [root]);

  const normalized = useMemo(function () {
    const box = new THREE.Box3().setFromObject(root);
    const size = box.getSize(new THREE.Vector3());
    const center = box.getCenter(new THREE.Vector3());

    const targetHeight = 2.0;
    const ratio = size.y > 0 ? targetHeight / size.y : 1;

    return {
      scale: ratio,
      centerX: center.x,
      minY: box.min.y,
      centerZ: center.z,
    };
  }, [root]);

  const meshMaterials = useMemo(function () {
    const materials = [];
    root.traverse(function (child) {
      if (!child.isMesh || Array.isArray(child.material) || !child.material) return;
      child.material.emissive = emissiveColor;
      materials.push(child.material);
    });
    return materials;
  }, [root, emissiveColor]);

  useFrame(function (_, delta) {
    const target = clamp01(bodyShape);
    shapeRef.current = THREE.MathUtils.lerp(shapeRef.current, target, Math.min(1, delta * 5));

    const s = shapeRef.current;
    const bulk = THREE.MathUtils.lerp(0.84, 1.22, s);
    const thighBulk = THREE.MathUtils.lerp(0.86, 1.28, s);
    const torsoBulk = THREE.MathUtils.lerp(0.9, 1.2, s);

    if (parts.hips) parts.hips.scale.set(bulk * 1.1, 1, bulk * 1.05);
    if (parts.spine) parts.spine.scale.set(torsoBulk, 1, torsoBulk * 1.03);
    if (parts.spine1) parts.spine1.scale.set(torsoBulk * 1.05, 1, torsoBulk * 1.06);
    if (parts.spine2) parts.spine2.scale.set(torsoBulk * 1.02, 1, torsoBulk * 1.02);
    if (parts.leftUpLeg) parts.leftUpLeg.scale.set(thighBulk, 1, thighBulk);
    if (parts.rightUpLeg) parts.rightUpLeg.scale.set(thighBulk, 1, thighBulk);

    if (bodyMesh) {
      const meshBulk = THREE.MathUtils.lerp(0.95, 1.08, s);
      bodyMesh.scale.set(meshBulk, 1, meshBulk * 1.02);
    }

    if (groupRef.current) {
      const simPulse = isSimulating ? (1 + Math.sin(performance.now() * 0.005) * 0.006) : 1;
      const uniform = normalized.scale * baseHeightScale * simPulse;
      groupRef.current.scale.set(uniform, uniform, uniform);
    }

    const emissiveIntensity = 0.03 + thyroidFactor * 0.07;
    meshMaterials.forEach(function (material) {
      material.emissiveIntensity = emissiveIntensity;
    });
  });

  return (
    <group ref={groupRef} position={[-normalized.centerX * normalized.scale, -normalized.minY * normalized.scale - 0.44, -normalized.centerZ * normalized.scale]}>
      <primitive object={root} />
    </group>
  );
}

function FemaleBody(props) {
  const userHeight = props.userHeight || 165;
  const userWeight = props.userWeight || 58;
  const pcosIndex = props.pcosIndex || 0;
  const insulinResistance = props.insulinResistance || 0;
  const thyroidFactor = props.thyroidFactor != null ? props.thyroidFactor : 1;
  const isSimulating = props.isSimulating || false;
  const hoveredNode = props.hoveredNode;
  const onNodeHover = props.onNodeHover || function () {};

  const bmi = useMemo(function () {
    if (!userWeight || !userHeight || userHeight < 1) return 22;
    return userWeight / Math.pow(userHeight / 100, 2);
  }, [userWeight, userHeight]);

  const automaticShape = useMemo(function () {
    if (bmi <= 18.5) return 0.1;
    if (bmi >= 32) return 1;
    return (bmi - 18.5) / (32 - 18.5);
  }, [bmi]);

  const bodyShape = props.bodyShape != null ? clamp01(props.bodyShape) : automaticShape;

  const heightScale = useMemo(function () {
    return Math.min(1.15, Math.max(0.84, userHeight / 165));
  }, [userHeight]);

  return (
    <group>
      <FbxCharacter
        bodyShape={bodyShape}
        baseHeightScale={heightScale}
        isSimulating={isSimulating}
        thyroidFactor={thyroidFactor}
      />

      {HORMONE_NODES.map(function (node) {
        const color = getNodeColor(node.id, pcosIndex, insulinResistance, thyroidFactor, isSimulating);
        const status = getNodeStatus(node.id, pcosIndex, insulinResistance, thyroidFactor);
        const intensity = isSimulating ? 2.0 : 1.2;
        return (
          <HormoneNode
            key={node.id}
            node={node}
            color={color}
            intensity={intensity}
            isHovered={hoveredNode === node.id}
            onHover={onNodeHover}
            statusText={isSimulating ? 'Simulating…' : status}
          />
        );
      })}

      <ContactShadows position={[0, -0.44, 0]} opacity={0.35} scale={2} blur={2.5} far={1.2} color="#4c1d95" />
    </group>
  );
}

function SceneLights() {
  return (
    <>
      <ambientLight intensity={0.6} color="#e0d4f5" />
      <directionalLight position={[4, 8, 6]} intensity={1.05} color="#f0e6ff" castShadow />
      <directionalLight position={[-3, 4, -5]} intensity={0.25} color="#c084fc" />
      <hemisphereLight skyColor="#ddd6fe" groundColor="#3b0764" intensity={0.45} />
      <pointLight position={[0, 2.2, 2]} intensity={0.35} color="#a78bfa" distance={5} />
    </>
  );
}

function SceneContent(props) {
  return (
    <>
      <SceneLights />
      <Float speed={1.4} rotationIntensity={0} floatIntensity={0.2} floatingRange={[-0.03, 0.03]}>
        <FemaleBody
          userHeight={props.userHeight}
          userWeight={props.userWeight}
          bodyShape={props.bodyShape}
          pcosIndex={props.pcosIndex}
          insulinResistance={props.insulinResistance}
          thyroidFactor={props.thyroidFactor}
          isSimulating={props.isSimulating}
          hoveredNode={props.hoveredNode}
          onNodeHover={props.onNodeHover}
        />
      </Float>
      <OrbitControls
        enableZoom={false}
        enablePan={false}
        minPolarAngle={Math.PI * 0.3}
        maxPolarAngle={Math.PI * 0.7}
        autoRotate
        autoRotateSpeed={0.6}
        enableDamping
        dampingFactor={0.08}
      />
    </>
  );
}

function LoadingFallback() {
  const ref = useRef();
  useFrame(function (state) {
    if (ref.current) ref.current.rotation.y = state.clock.elapsedTime * 1.5;
  });
  return (
    <mesh ref={ref}>
      <boxGeometry args={[0.4, 1.2, 0.1]} />
      <meshStandardMaterial color="#7c3aed" transparent opacity={0.25} />
    </mesh>
  );
}

function Body3D(props) {
  const width = props.width || '100%';
  const height = props.height || '100%';
  const style = props.style || {};

  const userHeight = props.userHeight || 165;
  const userWeight = props.userWeight || 58;
  const bodyShape = props.bodyShape;
  const pcosIndex = props.pcosIndex || 0;
  const insulinResistance = props.insulinResistance || 0;
  const thyroidFactor = props.thyroidFactor != null ? props.thyroidFactor : 1;
  const isSimulating = props.isSimulating || false;
  const hoveredNode = props.hoveredNode != null ? props.hoveredNode : null;
  const onNodeHover = props.onNodeHover || function () {};

  return (
    <div style={Object.assign({ width: width, height: height, position: 'relative', overflow: 'hidden' }, style)}>
      <Canvas
        camera={{ position: [0, 0.85, 3.4], fov: 36, near: 0.1, far: 50 }}
        gl={{ antialias: true, alpha: true, powerPreference: 'high-performance' }}
        dpr={[1, 1.5]}
        style={{ background: 'transparent' }}
      >
        <Suspense fallback={<LoadingFallback />}>
          <SceneContent
            userHeight={userHeight}
            userWeight={userWeight}
            bodyShape={bodyShape}
            pcosIndex={pcosIndex}
            insulinResistance={insulinResistance}
            thyroidFactor={thyroidFactor}
            isSimulating={isSimulating}
            hoveredNode={hoveredNode}
            onNodeHover={onNodeHover}
          />
        </Suspense>
      </Canvas>
    </div>
  );
}

export default Body3D;
