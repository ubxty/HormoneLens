import React, { useRef, useState, useMemo, useCallback, Suspense } from 'react';
import { Canvas, useFrame } from '@react-three/fiber';
import { OrbitControls, Html, ContactShadows, Float } from '@react-three/drei';

/* ═══════════════════════════════════════════════════════════════════════════
   BODY PART DEFINITIONS — female silhouette built from capsule + sphere prims
   ═══════════════════════════════════════════════════════════════════════════ */
const BASE_PARTS = [
  // Head & neck
  { n: 'head',    g: 'sphere',  p: [0, 1.62, 0],   a: [0.17, 32, 32],            s: [1, 1.06, 0.94] },
  { n: 'neck',    g: 'capsule', p: [0, 1.43, 0],    a: [0.048, 0.06, 8, 16],      s: [1, 1, 0.88] },
  // Torso
  { n: 'shoulderBridge', g: 'capsule', p: [0, 1.33, 0], a: [0.055, 0.04, 8, 16],  s: [3.2, 0.75, 0.82] },
  { n: 'upperTorso',     g: 'capsule', p: [0, 1.21, 0], a: [0.115, 0.1, 8, 16],   s: [1.48, 1, 0.82] },
  { n: 'bustL',          g: 'sphere',  p: [-0.075, 1.2, 0.06], a: [0.06, 16, 16] },
  { n: 'bustR',          g: 'sphere',  p: [0.075, 1.2, 0.06],  a: [0.06, 16, 16] },
  { n: 'midTorso',       g: 'capsule', p: [0, 1.06, 0], a: [0.098, 0.07, 8, 16],  s: [1.22, 1, 0.78] },
  { n: 'waist',          g: 'sphere',  p: [0, 0.93, 0], a: [0.1, 16, 16],          s: [1.18, 0.9, 0.78] },
  // Abdomen — dynamically scaled
  { n: 'abdomen',        g: 'sphere',  p: [0, 0.81, 0.01], a: [0.125, 20, 20],     s: [1.3, 1, 0.85], dynamic: 'abdomen' },
  // Hips & pelvis
  { n: 'hips',           g: 'sphere',  p: [0, 0.69, 0],    a: [0.145, 20, 20],     s: [1.6, 0.72, 0.82] },
  { n: 'pelvis',         g: 'sphere',  p: [0, 0.6, 0],     a: [0.1, 16, 16],       s: [1.38, 0.58, 0.78] },
  // Legs — thighs dynamically scaled
  { n: 'thighL', g: 'capsule', p: [-0.1, 0.36, 0],  a: [0.065, 0.28, 8, 16], s: [1, 1, 0.88], dynamic: 'thigh' },
  { n: 'thighR', g: 'capsule', p: [0.1, 0.36, 0],   a: [0.065, 0.28, 8, 16], s: [1, 1, 0.88], dynamic: 'thigh' },
  { n: 'calfL',  g: 'capsule', p: [-0.1, -0.04, 0],  a: [0.048, 0.3, 8, 16] },
  { n: 'calfR',  g: 'capsule', p: [0.1, -0.04, 0],   a: [0.048, 0.3, 8, 16] },
  { n: 'footL',  g: 'capsule', p: [-0.1, -0.36, 0.02], a: [0.033, 0.055, 8, 16], s: [0.88, 1, 1.4], r: [0.3, 0, 0] },
  { n: 'footR',  g: 'capsule', p: [0.1, -0.36, 0.02],  a: [0.033, 0.055, 8, 16], s: [0.88, 1, 1.4], r: [0.3, 0, 0] },
  // Arms
  { n: 'upperArmL', g: 'capsule', p: [-0.235, 1.16, 0], a: [0.034, 0.17, 8, 16], r: [0, 0, 0.08] },
  { n: 'upperArmR', g: 'capsule', p: [0.235, 1.16, 0],  a: [0.034, 0.17, 8, 16], r: [0, 0, -0.08] },
  { n: 'forearmL',   g: 'capsule', p: [-0.25, 0.9, 0],   a: [0.028, 0.17, 8, 16], r: [0, 0, 0.04] },
  { n: 'forearmR',   g: 'capsule', p: [0.25, 0.9, 0],    a: [0.028, 0.17, 8, 16], r: [0, 0, -0.04] },
  { n: 'handL',      g: 'sphere',  p: [-0.26, 0.74, 0],  a: [0.028, 12, 12], s: [0.7, 1, 0.5] },
  { n: 'handR',      g: 'sphere',  p: [0.26, 0.74, 0],   a: [0.028, 12, 12], s: [0.7, 1, 0.5] },
];

/* ═══════════════════════════════════════════════════════════════════════════
   HORMONE NODE DEFINITIONS
   ═══════════════════════════════════════════════════════════════════════════ */
const HORMONE_NODES = [
  { id: 'pituitary', label: 'Pituitary Gland', info: 'Master endocrine regulator · HPA Axis',     pos: [0, 1.64, 0.14],    r: 0.032 },
  { id: 'thyroid',   label: 'Thyroid',          info: 'T3/T4 metabolic rate control',              pos: [0, 1.42, 0.06],    r: 0.036 },
  { id: 'pancreas',  label: 'Pancreas',         info: 'Insulin & glucagon secretion',              pos: [0.08, 0.91, 0.1],  r: 0.034 },
  { id: 'ovaryL',    label: 'Left Ovary',       info: 'Estrogen · Progesterone · PCOS marker',     pos: [-0.09, 0.67, 0.06], r: 0.026 },
  { id: 'ovaryR',    label: 'Right Ovary',      info: 'Estrogen · Progesterone · PCOS marker',     pos: [0.09, 0.67, 0.06],  r: 0.026 },
];

/* ---------- Node glow color logic ---------- */
function getNodeColor(id, pcosIdx, insulinRes, thyroidFac, sim) {
  if (sim) return '#10b981';
  switch (id) {
    case 'ovaryL': case 'ovaryR':
      return pcosIdx > 5 ? '#a855f7' : pcosIdx > 2 ? '#c084fc' : '#10b981';
    case 'pancreas':
      return insulinRes > 5 ? '#3b82f6' : insulinRes > 2 ? '#60a5fa' : '#10b981';
    case 'thyroid':
      return thyroidFac < 0.4 ? '#f97316' : thyroidFac < 0.7 ? '#fb923c' : '#10b981';
    default: return '#8b5cf6';
  }
}

/* ---------- Status text for tooltip ---------- */
function getNodeStatus(id, pcosIdx, insulinRes, thyroidFac) {
  switch (id) {
    case 'ovaryL': case 'ovaryR':
      return pcosIdx > 5 ? `PCOS Imbalance \u2191 ${(pcosIdx * 10).toFixed(0)}%` :
             pcosIdx > 2 ? `Mild PCOS Signal \u2191 ${(pcosIdx * 10).toFixed(0)}%` : 'Normal Range';
    case 'pancreas':
      return insulinRes > 5 ? `Insulin Sensitivity \u2193 ${(insulinRes * 12).toFixed(0)}%` :
             insulinRes > 2 ? `Mild Resistance \u2193 ${(insulinRes * 7).toFixed(0)}%` : 'Normal Range';
    case 'thyroid':
      return thyroidFac < 0.4 ? `Thyroid Dysfunction \u2193 ${((1 - thyroidFac) * 100).toFixed(0)}%` :
             thyroidFac < 0.7 ? `Mild Hypofunction \u2193 ${((1 - thyroidFac) * 100).toFixed(0)}%` : 'Normal Range';
    default: return 'Active Regulation';
  }
}

/* ═══════════════════════════════════════════════════════════════════════════
   HORMONE NODE — inner core + outer glow + optional tooltip
   ═══════════════════════════════════════════════════════════════════════════ */
function HormoneNode({ node, color, intensity, isHovered, onHover, statusText }) {
  const glowRef = useRef();
  const coreRef = useRef();
  const idx = HORMONE_NODES.indexOf(node);

  useFrame(function (state) {
    var t = state.clock.elapsedTime;
    if (glowRef.current) {
      var pulse = 1.0 + Math.sin(t * 2.2 + idx * 1.4) * 0.35;
      glowRef.current.scale.setScalar(pulse);
      glowRef.current.material.opacity = 0.12 + Math.sin(t * 2.2 + idx) * 0.06;
    }
    if (coreRef.current) {
      coreRef.current.material.emissiveIntensity = intensity * (0.7 + Math.sin(t * 3 + idx) * 0.4);
    }
  });

  return (
    <group position={node.pos}>
      {/* Core sphere */}
      <mesh
        ref={coreRef}
        onPointerEnter={function (e) { e.stopPropagation(); onHover(node.id); }}
        onPointerLeave={function (e) { e.stopPropagation(); onHover(null); }}
      >
        <sphereGeometry args={[node.r, 20, 20]} />
        <meshStandardMaterial
          color={color}
          emissive={color}
          emissiveIntensity={intensity}
          toneMapped={false}
        />
      </mesh>
      {/* Outer glow halo */}
      <mesh ref={glowRef}>
        <sphereGeometry args={[node.r * 2.8, 16, 16]} />
        <meshBasicMaterial color={color} transparent opacity={0.12} depthWrite={false} />
      </mesh>
      {/* Tooltip via drei Html */}
      {isHovered && (
        <Html center distanceFactor={5.5} style={{ pointerEvents: 'none', userSelect: 'none' }}>
          <div style={{
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
          }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 6 }}>
              <div style={{ width: 10, height: 10, borderRadius: '50%', background: color, boxShadow: '0 0 8px ' + color }} />
              <span style={{ fontSize: 12, fontWeight: 800, color: '#e2ddf5', letterSpacing: '0.02em' }}>{node.label}</span>
            </div>
            <p style={{ fontSize: 10, color: '#9b8fc4', margin: '0 0 8px', lineHeight: 1.4 }}>{node.info}</p>
            <div style={{
              padding: '6px 10px',
              background: color + '18',
              borderRadius: 8,
              border: '1px solid ' + color + '30',
            }}>
              <span style={{ fontSize: 11, fontWeight: 700, color: color }}>{statusText}</span>
            </div>
          </div>
        </Html>
      )}
    </group>
  );
}

/* ═══════════════════════════════════════════════════════════════════════════
   FEMALE BODY — group of primitive meshes + hormone nodes
   ═══════════════════════════════════════════════════════════════════════════ */
function FemaleBody(props) {
  var userHeight       = props.userHeight || 165;
  var userWeight       = props.userWeight || 58;
  var pcosIndex        = props.pcosIndex || 0;
  var insulinResistance = props.insulinResistance || 0;
  var thyroidFactor    = props.thyroidFactor != null ? props.thyroidFactor : 1;
  var isSimulating     = props.isSimulating || false;
  var hoveredNode      = props.hoveredNode;
  var onNodeHover      = props.onNodeHover || function () {};

  /* Dynamic scale factors */
  var heightScale = useMemo(function () { return userHeight / 165; }, [userHeight]);
  var bmi = useMemo(function () {
    if (!userWeight || !userHeight || userHeight < 1) return 22;
    return userWeight / Math.pow(userHeight / 100, 2);
  }, [userWeight, userHeight]);
  var widthScale = useMemo(function () {
    if (bmi < 18.5) return Math.max(0.84, 0.84 + (bmi - 15) * 0.028);
    if (bmi <= 25) return 1.0;
    return Math.min(1 + (bmi - 25) * 0.024, 1.38);
  }, [bmi]);

  var abdomenMult = useMemo(function () {
    return 1 + pcosIndex * 0.028 + insulinResistance * 0.022;
  }, [pcosIndex, insulinResistance]);
  var thighMult = useMemo(function () {
    return 1 + pcosIndex * 0.018;
  }, [pcosIndex]);
  var glowBase = useMemo(function () {
    return 0.04 + thyroidFactor * 0.14;
  }, [thyroidFactor]);

  /* Simulation pulse */
  var bodyGroupRef = useRef();
  var simPulseRef = useRef(0);

  useFrame(function (state) {
    if (!bodyGroupRef.current) return;
    var t = state.clock.elapsedTime;
    if (isSimulating) {
      simPulseRef.current = Math.min(simPulseRef.current + 0.02, 1);
    } else {
      simPulseRef.current = Math.max(simPulseRef.current - 0.01, 0);
    }
    /* Idle breathing */
    var breathe = 1 + Math.sin(t * 1.2) * 0.004;
    var sx = widthScale * breathe;
    var sy = heightScale;
    bodyGroupRef.current.scale.set(sx, sy, sx * 0.95);
  });

  /* Body material color (slightly purple-tinted for digital look) */
  var skinColor = '#d0bfea';
  var emissiveColor = '#6d28d9';

  /* Render parts */
  var parts = useMemo(function () {
    return BASE_PARTS.map(function (part) {
      var sc = part.s ? part.s.slice() : [1, 1, 1];
      if (part.dynamic === 'abdomen') {
        sc[0] *= abdomenMult;
        sc[2] *= abdomenMult * 0.9;
      }
      if (part.dynamic === 'thigh') {
        sc[0] *= thighMult;
        sc[2] *= thighMult * 0.95;
      }
      return { key: part.n, geom: part.g, pos: part.p, args: part.a, scale: sc, rot: part.r || [0, 0, 0] };
    });
  }, [abdomenMult, thighMult]);

  return (
    <group ref={bodyGroupRef}>
      {/* Body parts */}
      {parts.map(function (p) {
        return (
          <mesh key={p.key} position={p.pos} scale={p.scale} rotation={p.rot}>
            {p.geom === 'sphere' ? (
              <sphereGeometry args={p.args} />
            ) : (
              <capsuleGeometry args={p.args} />
            )}
            <meshStandardMaterial
              color={skinColor}
              emissive={emissiveColor}
              emissiveIntensity={glowBase + simPulseRef.current * 0.18}
              metalness={0.12}
              roughness={0.52}
              transparent
              opacity={0.9}
            />
          </mesh>
        );
      })}

      {/* Hormone nodes */}
      {HORMONE_NODES.map(function (node) {
        var color = getNodeColor(node.id, pcosIndex, insulinResistance, thyroidFactor, isSimulating);
        var status = getNodeStatus(node.id, pcosIndex, insulinResistance, thyroidFactor);
        var intensity = isSimulating ? 2.0 : 1.2;
        return (
          <HormoneNode
            key={node.id}
            node={node}
            color={color}
            intensity={intensity}
            isHovered={hoveredNode === node.id}
            onHover={onNodeHover}
            statusText={isSimulating ? 'Simulating\u2026' : status}
          />
        );
      })}

      {/* Contact shadow below feet */}
      <ContactShadows
        position={[0, -0.44, 0]}
        opacity={0.35}
        scale={1.6}
        blur={2.2}
        far={1}
        color="#4c1d95"
      />
    </group>
  );
}

/* ═══════════════════════════════════════════════════════════════════════════
   SCENE LIGHTING — ambient + directional + hemisphere for depth
   ═══════════════════════════════════════════════════════════════════════════ */
function SceneLights() {
  return (
    <>
      <ambientLight intensity={0.5} color="#e0d4f5" />
      <directionalLight position={[4, 8, 6]} intensity={0.9} color="#f0e6ff" />
      <directionalLight position={[-3, 4, -5]} intensity={0.25} color="#c084fc" />
      <hemisphereLight skyColor="#ddd6fe" groundColor="#3b0764" intensity={0.45} />
      <pointLight position={[0, 2.2, 2]} intensity={0.3} color="#a78bfa" distance={5} />
    </>
  );
}

/* ═══════════════════════════════════════════════════════════════════════════
   SCENE CONTENT — assembled inside Canvas
   ═══════════════════════════════════════════════════════════════════════════ */
function SceneContent(props) {
  return (
    <>
      <SceneLights />
      <Float speed={1.4} rotationIntensity={0} floatIntensity={0.3} floatingRange={[-0.04, 0.04]}>
        <FemaleBody
          userHeight={props.userHeight}
          userWeight={props.userWeight}
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

/* ═══════════════════════════════════════════════════════════════════════════
   LOADING FALLBACK (inside Canvas)
   ═══════════════════════════════════════════════════════════════════════════ */
function LoadingFallback() {
  var ref = useRef();
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

/* ═══════════════════════════════════════════════════════════════════════════
   BODY3D — main exported component with Canvas
   ═══════════════════════════════════════════════════════════════════════════ */
function Body3D(props) {
  var width  = props.width  || '100%';
  var height = props.height || '100%';
  var style  = props.style  || {};

  var userHeight       = props.userHeight || 165;
  var userWeight       = props.userWeight || 58;
  var pcosIndex        = props.pcosIndex || 0;
  var insulinResistance = props.insulinResistance || 0;
  var thyroidFactor    = props.thyroidFactor != null ? props.thyroidFactor : 1;
  var isSimulating     = props.isSimulating || false;
  var hoveredNode      = props.hoveredNode != null ? props.hoveredNode : null;
  var onNodeHover      = props.onNodeHover || function () {};

  return (
    <div style={Object.assign({ width: width, height: height, position: 'relative', overflow: 'hidden' }, style)}>
      <Canvas
        camera={{ position: [0, 0.7, 3.2], fov: 38, near: 0.1, far: 50 }}
        gl={{ antialias: true, alpha: true, powerPreference: 'high-performance' }}
        dpr={[1, 1.5]}
        style={{ background: 'transparent' }}
      >
        <Suspense fallback={<LoadingFallback />}>
          <SceneContent
            userHeight={userHeight}
            userWeight={userWeight}
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
