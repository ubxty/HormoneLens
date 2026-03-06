/**
 * CharacterInspector.jsx
 * ─────────────────────────────────────────────────────────────────────────────
 * Pure utility module — no React, no Three.js imports needed at runtime.
 * Traverses any Three.js Object3D and returns a structured "capability report"
 * describing everything the loaded model supports:
 *   animations, meshes, bones, morph targets, materials, polygon/vertex counts.
 *
 * Usage:
 *   import { inspectCharacter } from './CharacterInspector';
 *   const caps = inspectCharacter(fbxObject, fbxObject.animations);
 *
 * The report can be passed directly to <CharacterDebugPanel capabilities={caps} />.
 */

// ─────────────────────────────────────────────────────────────────────────────
// Type definitions (JSDoc only — no TypeScript required)
// ─────────────────────────────────────────────────────────────────────────────

/**
 * @typedef {Object} AnimationInfo
 * @property {string} name       - Clip name (e.g. "idle", "walk")
 * @property {number} duration   - Duration in seconds (2 decimal places)
 * @property {number} tracks     - Number of property tracks inside the clip
 */

/**
 * @typedef {Object} MeshInfo
 * @property {string}  name      - Object name from the FBX hierarchy
 * @property {string}  type      - 'Mesh' or 'SkinnedMesh'
 * @property {number}  vertices  - Vertex count
 * @property {number}  polygons  - Triangle count
 * @property {boolean} hasMorphs - Whether morph target dictionary exists
 */

/**
 * @typedef {Object} MorphTargetInfo
 * @property {string} name   - Morph target / shape-key name
 * @property {string} meshOn - Name of the mesh that owns it
 */

/**
 * @typedef {Object} MaterialInfo
 * @property {string}      name  - Material name (falls back to type)
 * @property {string}      type  - Three.js material type string
 * @property {string|null} color - Hex string like "#ff8800", or null if unavailable
 */

/**
 * @typedef {Object} CharacterCapabilities
 * @property {AnimationInfo[]}   animations       - All animation clips
 * @property {MeshInfo[]}        meshes           - All mesh objects found
 * @property {{name:string}[]}   bones            - All bone objects found
 * @property {MorphTargetInfo[]} morphTargets     - All morph targets / blend shapes
 * @property {MaterialInfo[]}    materials        - Deduplicated material list
 * @property {boolean}           skeletonDetected - True if at least one SkinnedMesh has a skeleton
 * @property {number}            polygonCount     - Total triangle count across all meshes
 * @property {number}            vertexCount      - Total vertex count across all meshes
 */

// ─────────────────────────────────────────────────────────────────────────────
// Main Inspector Function
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Traverse a Three.js Object3D and produce a full CharacterCapabilities report.
 * Safe to call on any FBX/GLTF/OBJ object — gracefully handles missing data.
 *
 * @param {import('three').Object3D}       object  - Root of the loaded model
 * @param {import('three').AnimationClip[]} clips  - Clips array (e.g. source.animations)
 * @returns {CharacterCapabilities}
 */
export function inspectCharacter(object, clips = []) {
    // ── 1. Animation clips ──────────────────────────────────────────────────
    // Clips come from the FBX loader and live on the original (non-cloned) asset.
    const animations = (clips ?? []).map((clip) => ({
        name:     clip.name     || '(unnamed)',
        duration: parseFloat((clip.duration ?? 0).toFixed(2)),
        tracks:   clip.tracks?.length ?? 0,
    }));

    // ── 2. Scene-graph traversal buckets ───────────────────────────────────
    const meshes        = [];
    const bones         = [];
    const morphTargets  = [];
    const materials     = [];
    const seenMaterials = new Set(); // used for deduplication

    let skeletonDetected = false;
    let polygonCount     = 0;
    let vertexCount      = 0;

    object.traverse((child) => {

        // ── Meshes (regular + skinned) ─────────────────────────────────────
        if (child.isMesh || child.isSkinnedMesh) {
            const geo   = child.geometry;
            const verts = geo?.attributes?.position?.count ?? 0;

            // Triangle count: use index buffer when available for accuracy
            const polys = geo?.index
                ? geo.index.count / 3
                : Math.floor(verts / 3);

            vertexCount  += verts;
            polygonCount += polys;

            meshes.push({
                name:      child.name || '(unnamed)',
                type:      child.isSkinnedMesh ? 'SkinnedMesh' : 'Mesh',
                vertices:  verts,
                polygons:  Math.floor(polys),
                hasMorphs: !!child.morphTargetDictionary,
            });

            // ── Morph targets / blend shapes ──────────────────────────────
            // morphTargetDictionary maps morph name → index
            if (child.morphTargetDictionary) {
                Object.keys(child.morphTargetDictionary).forEach((key) => {
                    // Deduplicate by name across meshes
                    if (!morphTargets.find((m) => m.name === key)) {
                        morphTargets.push({
                            name:   key,
                            meshOn: child.name || '(unnamed)',
                        });
                    }
                });
            }

            // ── Materials — deduplicated by name+type key ─────────────────
            const mats = Array.isArray(child.material)
                ? child.material
                : [child.material];

            mats.forEach((mat) => {
                if (!mat) return;
                const id = `${mat.name}::${mat.type}`;
                if (seenMaterials.has(id)) return;
                seenMaterials.add(id);
                materials.push({
                    name:  mat.name || mat.type || '(unnamed)',
                    type:  mat.type ?? 'Unknown',
                    // color is a THREE.Color on most standard materials
                    color: mat.color ? '#' + mat.color.getHexString() : null,
                });
            });

            // ── Skeleton detection ─────────────────────────────────────────
            if (child.isSkinnedMesh && child.skeleton) {
                skeletonDetected = true;
            }
        }

        // ── Bones ──────────────────────────────────────────────────────────
        if (child.isBone) {
            bones.push({ name: child.name || '(unnamed)' });
        }
    });

    // ── 3. Assemble final report ────────────────────────────────────────────
    const report = {
        animations,
        meshes,
        bones,
        morphTargets,
        materials,
        skeletonDetected,
        polygonCount: Math.floor(polygonCount),
        vertexCount,
    };

    // ── 4. Structured console output (visible in DevTools) ──────────────────
    // Grouped so developers can expand individual sections at will.
    console.group('%c🔬 Character Capability Report', 'color:#a78bfa; font-size:13px; font-weight:bold;');
    console.log(`%cAnimations   (${animations.length})`,   'color:#6ee7b7; font-weight:600;', animations);
    console.log(`%cMeshes       (${meshes.length})`,       'color:#6ee7b7; font-weight:600;', meshes);
    console.log(`%cBones        (${bones.length})`,        'color:#6ee7b7; font-weight:600;', bones);
    console.log(`%cMorph Targets(${morphTargets.length})`, 'color:#6ee7b7; font-weight:600;', morphTargets);
    console.log(`%cMaterials    (${materials.length})`,    'color:#6ee7b7; font-weight:600;', materials);
    console.log('%cSkeleton Detected:', 'color:#6ee7b7; font-weight:600;', skeletonDetected ? '✅ Yes' : '❌ No');
    console.log(
        `%cGeometry — Vertices: ${vertexCount.toLocaleString()} | Polygons: ${Math.floor(polygonCount).toLocaleString()}`,
        'color:#6ee7b7; font-weight:600;'
    );
    console.groupEnd();

    return report;
}
