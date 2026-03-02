import React, { useRef, useEffect, useState, useMemo, useCallback, Suspense } from 'react';
import { Canvas, useFrame, useThree } from '@react-three/fiber';
import { OrbitControls } from '@react-three/drei';
import * as THREE from 'three';
import { SVGLoader } from 'three/addons/loaders/SVGLoader.js';

// ── Zone overlay geometry definitions (Y positions in normalized body space) ──
// Body SVG viewBox: "182 8 118 335" → width=118, height=335
// We center the body at origin, so Y goes from +halfH to -halfH
const SVG_VB = { x: 182, y: 8, w: 118, h: 335 };
const BODY_SCALE = 0.035; // Scales SVG units → Three.js units

const ZONE_DEFS = [
  { id: 'head',    yStart: 0.0,  yEnd: 0.12, color: '#ef4444' },
  { id: 'chest',   yStart: 0.17, yEnd: 0.29, color: '#3b82f6' },
  { id: 'waist',   yStart: 0.28, yEnd: 0.40, color: '#f97316' },
  { id: 'abdomen', yStart: 0.38, yEnd: 0.51, color: '#a855f7' },
  { id: 'thighs',  yStart: 0.59, yEnd: 0.75, color: '#6366f1' },
];

// ── Parse SVG string → array of THREE.Shape ──────────────────────────────────
function parseSvgToShapes(svgText) {
  const loader = new SVGLoader();
  const data = loader.parse(svgText);
  const shapes = [];

  for (const path of data.paths) {
    // Skip the white background path (#FEFEFE)
    const fillColor = path.userData?.style?.fill;
    if (fillColor === '#FEFEFE' || fillColor === '#fefefe') continue;
    // Skip paths with fill="none"
    if (fillColor === 'none') continue;

    const pathShapes = SVGLoader.createShapes(path);
    for (const shape of pathShapes) {
      shapes.push({
        shape,
        color: fillColor || '#232021',
      });
    }
  }
  return shapes;
}

// ── Extruded SVG body mesh component ─────────────────────────────────────────
function BodyMesh({ shapes, scaleX = 1, scaleY = 1, hovered, hoveredZone, zoneScores }) {
  const groupRef = useRef();
  const [centered, setCentered] = useState(false);

  // Create geometries from shapes
  const meshes = useMemo(() => {
    if (!shapes.length) return [];

    const extrudeSettings = {
      depth: 3,
      bevelEnabled: false,
      curveSegments: 24,
    };

    return shapes.map(({ shape, color }, i) => {
      const geometry = new THREE.ExtrudeGeometry(shape, extrudeSettings);
      return { geometry, color, key: i };
    });
  }, [shapes]);

  // Center the geometry group after first render
  useEffect(() => {
    if (!groupRef.current || !meshes.length || centered) return;

    const box = new THREE.Box3();
    groupRef.current.traverse((child) => {
      if (child.isMesh) {
        child.geometry.computeBoundingBox();
        const childBox = child.geometry.boundingBox.clone();
        childBox.applyMatrix4(child.matrixWorld);
        box.union(childBox);
      }
    });

    const center = new THREE.Vector3();
    box.getCenter(center);
    const size = new THREE.Vector3();
    box.getSize(size);

    // Offset to center
    groupRef.current.position.set(-center.x, -center.y, -center.z);
    setCentered(true);
  }, [meshes, centered]);

  // Idle float animation
  useFrame((state) => {
    if (!groupRef.current) return;
    const t = state.clock.elapsedTime;
    groupRef.current.position.y = (-groupRef.current.userData.centerY || 0) + Math.sin(t * 0.6) * 0.08;
  });

  // Store centerY for float reference
  useEffect(() => {
    if (groupRef.current && centered) {
      groupRef.current.userData.centerY = groupRef.current.position.y;
    }
  }, [centered]);

  return (
    <group
      ref={groupRef}
      scale={[BODY_SCALE * scaleX, -BODY_SCALE * scaleY, BODY_SCALE]}
      rotation={[0, 0, 0]}
    >
      {meshes.map(({ geometry, color, key }) => (
        <mesh key={key} geometry={geometry}>
          <meshStandardMaterial
            color={color}
            metalness={0.1}
            roughness={0.7}
            side={THREE.DoubleSide}
          />
        </mesh>
      ))}
    </group>
  );
}

// ── Zone glow overlays (transparent cylinders around body regions) ────────────
function ZoneGlows({ hoveredZone, zoneScores }) {
  const glowsRef = useRef({});

  useFrame(() => {
    for (const zone of ZONE_DEFS) {
      const mesh = glowsRef.current[zone.id];
      if (!mesh) continue;
      const score = zoneScores?.[zone.id] ?? 0;
      const isHov = hoveredZone === zone.id;
      const targetOpacity = isHov ? 0.35 : score >= 6 ? 0.18 : 0;
      mesh.material.opacity += (targetOpacity - mesh.material.opacity) * 0.1;
    }
  });

  const halfH = (SVG_VB.h * BODY_SCALE) / 2;

  return (
    <group>
      {ZONE_DEFS.map((zone) => {
        const yTop = halfH - zone.yStart * (SVG_VB.h * BODY_SCALE);
        const yBot = halfH - zone.yEnd * (SVG_VB.h * BODY_SCALE);
        const height = Math.abs(yTop - yBot);
        const yCenter = (yTop + yBot) / 2;
        const width = SVG_VB.w * BODY_SCALE * 0.6;

        return (
          <mesh
            key={zone.id}
            ref={(el) => { if (el) glowsRef.current[zone.id] = el; }}
            position={[0, yCenter, 0.1]}
          >
            <planeGeometry args={[width, height]} />
            <meshBasicMaterial
              color={zone.color}
              transparent
              opacity={0}
              side={THREE.DoubleSide}
              depthWrite={false}
            />
          </mesh>
        );
      })}
    </group>
  );
}

// ── Scene setup (lights + camera adjustment) ──────────────────────────────────
function SceneLighting() {
  return (
    <>
      <ambientLight intensity={0.6} />
      <directionalLight position={[5, 8, 10]} intensity={0.8} castShadow={false} />
      <directionalLight position={[-5, 4, -8]} intensity={0.3} />
      <hemisphereLight skyColor="#e0e7ff" groundColor="#4a1d96" intensity={0.4} />
    </>
  );
}

// ── Inner scene content ──────────────────────────────────────────────────────
function SceneContent({ shapes, scaleX, scaleY, hoveredZone, zoneScores, onZoneHover }) {
  const controlsRef = useRef();

  return (
    <>
      <SceneLighting />
      <BodyMesh
        shapes={shapes}
        scaleX={scaleX}
        scaleY={scaleY}
        hoveredZone={hoveredZone}
        zoneScores={zoneScores}
      />
      <ZoneGlows hoveredZone={hoveredZone} zoneScores={zoneScores} />
      <OrbitControls
        ref={controlsRef}
        enableZoom={false}
        enablePan={false}
        minPolarAngle={Math.PI / 2}
        maxPolarAngle={Math.PI / 2}
        autoRotate
        autoRotateSpeed={0.8}
        dampingFactor={0.08}
        enableDamping
      />
    </>
  );
}

// ── Loading fallback ─────────────────────────────────────────────────────────
function LoadingFallback() {
  return (
    <mesh>
      <boxGeometry args={[1, 2, 0.1]} />
      <meshStandardMaterial color="#c24dff" opacity={0.2} transparent />
    </mesh>
  );
}

// ── Main exported component ──────────────────────────────────────────────────
export default function Body3D({
  scaleX = 1,
  scaleY = 1,
  hoveredZone = null,
  zoneScores = {},
  onZoneHover,
  width = '100%',
  height = 460,
  style = {},
}) {
  const [shapes, setShapes] = useState([]);
  const [loading, setLoading] = useState(true);

  // Fetch and parse SVG
  useEffect(() => {
    fetch('/images/men.svg')
      .then((r) => r.text())
      .then((svgText) => {
        const parsed = parseSvgToShapes(svgText);
        setShapes(parsed);
        setLoading(false);
      })
      .catch(() => setLoading(false));
  }, []);

  if (loading) {
    return (
      <div style={{
        width, height, display: 'flex', alignItems: 'center', justifyContent: 'center',
        ...style,
      }}>
        <div style={{ textAlign: 'center' }}>
          <div style={{
            width: 36, height: 36, border: '3px solid rgba(194,77,255,.25)',
            borderTopColor: '#c24dff', borderRadius: '50%',
            animation: 'body3dSpin .8s linear infinite',
            margin: '0 auto 10px',
          }} />
          <p style={{ color: '#9ca3af', fontSize: 12 }}>Loading 3D Model…</p>
          <style>{`@keyframes body3dSpin { to { transform: rotate(360deg); } }`}</style>
        </div>
      </div>
    );
  }

  return (
    <div style={{ width, height, position: 'relative', ...style }}>
      <Canvas
        camera={{ position: [0, 0, 10], fov: 45, near: 0.1, far: 100 }}
        gl={{ antialias: true, alpha: true }}
        style={{ background: 'transparent' }}
        dpr={[1, 2]}
      >
        <Suspense fallback={<LoadingFallback />}>
          <SceneContent
            shapes={shapes}
            scaleX={scaleX}
            scaleY={scaleY}
            hoveredZone={hoveredZone}
            zoneScores={zoneScores}
            onZoneHover={onZoneHover}
          />
        </Suspense>
      </Canvas>
    </div>
  );
}
