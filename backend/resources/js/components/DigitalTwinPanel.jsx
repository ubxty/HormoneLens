import React, { useRef, useState, useEffect, useMemo, useCallback } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import Body3D from './Body3D';

/* ═══════════════════════════════════════════════════════════════════════════
   CONSTANTS
   ═══════════════════════════════════════════════════════════════════════════ */
const GLAND_CARDS = [
  { nodeId: 'pituitary', label: 'Pituitary Gland', icon: '\uD83E\uDDE0', desc: 'HPA Axis · Master regulator', from: '#8b5cf6', to: '#c084fc' },
  { nodeId: 'thyroid',   label: 'Thyroid',          icon: '\uD83E\uDD8B', desc: 'T3/T4 · Metabolic rate',      from: '#f97316', to: '#fb923c' },
  { nodeId: 'pancreas',  label: 'Pancreas',         icon: '\uD83E\uDE78', desc: 'Insulin sensitivity',         from: '#3b82f6', to: '#60a5fa' },
  { nodeId: 'ovaryL',    label: 'Left Ovary',       icon: '\uD83C\uDF38', desc: 'Estrogen · PCOS marker',      from: '#a855f7', to: '#d946ef' },
  { nodeId: 'ovaryR',    label: 'Right Ovary',      icon: '\uD83C\uDF38', desc: 'Progesterone · PCOS marker',  from: '#a855f7', to: '#d946ef' },
];

function riskLabel(v) { return v >= 7 ? 'High' : v >= 4 ? 'Moderate' : 'Low'; }
function riskColor(v) { return v >= 7 ? '#ef4444' : v >= 4 ? '#f59e0b' : '#10b981'; }
function bmiLabel(b)  { return b < 18.5 ? 'Underweight' : b < 25 ? 'Normal' : b < 30 ? 'Overweight' : 'Obese'; }

/* ═══════════════════════════════════════════════════════════════════════════
   DIGITAL TWIN PANEL — flex-safe dashboard right-panel wrapper
   ═══════════════════════════════════════════════════════════════════════════ */
function DigitalTwinPanel(props) {
  /* ── Props with defaults ─────────────────────────────────────────────── */
  var userHeight        = props.userHeight || 165;
  var userWeight        = props.userWeight || 58;
  var pcosIndex         = props.pcosIndex || 0;
  var insulinResistance = props.insulinResistance || 0;
  var thyroidFactor     = props.thyroidFactor != null ? props.thyroidFactor : 1;
  var overallRisk       = props.overallRisk || 0;
  var riskCategory      = props.riskCategory || '';
  var scores            = props.scores || {};
  var onSimulate        = props.onSimulate;

  /* ── State ───────────────────────────────────────────────────────────── */
  var _hov = useState(null);
  var hoveredNode = _hov[0];
  var setHoveredNode = _hov[1];

  var _sim = useState(false);
  var isSimulating = _sim[0];
  var setIsSimulating = _sim[1];

  var _mob = useState(false);
  var isMobile = _mob[0];
  var setIsMobile = _mob[1];

  /* ── Responsive — window access inside useEffect ─────────────────────── */
  useEffect(function () {
    function check() { setIsMobile(window.innerWidth < 900); }
    check();
    window.addEventListener('resize', check);
    return function () { window.removeEventListener('resize', check); };
  }, []);

  /* ── BMI computation ─────────────────────────────────────────────────── */
  var bmi = useMemo(function () {
    if (!userWeight || !userHeight || userHeight < 1) return 22;
    return userWeight / Math.pow(userHeight / 100, 2);
  }, [userWeight, userHeight]);

  /* ── Simulate handler ────────────────────────────────────────────────── */
  var handleSimulate = useCallback(function () {
    setIsSimulating(true);
    if (onSimulate) onSimulate();
    setTimeout(function () { setIsSimulating(false); }, 4000);
  }, [onSimulate]);

  /* ── Score for a node ────────────────────────────────────────────────── */
  function nodeScore(nodeId) {
    switch (nodeId) {
      case 'ovaryL': case 'ovaryR': return pcosIndex;
      case 'pancreas': return insulinResistance;
      case 'thyroid': return (1 - thyroidFactor) * 10;
      default: return overallRisk || 0;
    }
  }

  /* ── Render ──────────────────────────────────────────────────────────── */
  return (
    <div style={{
      display: 'flex',
      flexDirection: isMobile ? 'column' : 'row',
      width: '100%',
      height: isMobile ? 'auto' : '100%',
      minHeight: isMobile ? 600 : 0,
      overflow: 'hidden',
      position: 'relative',
      background: 'linear-gradient(135deg, rgba(12,6,22,0.97) 0%, rgba(30,15,50,0.96) 50%, rgba(15,8,30,0.97) 100%)',
      fontFamily: 'system-ui, -apple-system, sans-serif',
      borderRadius: 20,
    }}>

      {/* ── Ambient glow blobs (decorative) ───────────────────────────── */}
      <div style={{ position: 'absolute', inset: 0, pointerEvents: 'none', overflow: 'hidden', zIndex: 0 }}>
        <div style={{ position: 'absolute', width: 260, height: 260, borderRadius: '50%', background: 'radial-gradient(circle,#7c3aed,transparent 70%)', filter: 'blur(80px)', opacity: 0.12, top: -60, right: -40 }} />
        <div style={{ position: 'absolute', width: 200, height: 200, borderRadius: '50%', background: 'radial-gradient(circle,#ec4899,transparent 70%)', filter: 'blur(80px)', opacity: 0.1, bottom: 40, left: -30 }} />
        <div style={{ position: 'absolute', width: 160, height: 160, borderRadius: '50%', background: 'radial-gradient(circle,#6366f1,transparent 70%)', filter: 'blur(60px)', opacity: 0.08, top: '45%', right: '15%' }} />
      </div>

      {/* ═══════════════════════════════════════════════════════════════
          LEFT PANEL — gland cards + stats
          ═══════════════════════════════════════════════════════════════ */}
      <div style={{
        width: isMobile ? '100%' : '38%',
        padding: isMobile ? '20px 16px 12px' : '24px 16px 24px 24px',
        display: 'flex',
        flexDirection: 'column',
        justifyContent: isMobile ? 'flex-start' : 'center',
        gap: 10,
        position: 'relative',
        zIndex: 5,
        overflowY: isMobile ? 'visible' : 'auto',
      }}>

        {/* Header */}
        <motion.div
          initial={{ opacity: 0, y: -14 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.55 }}
          style={{ marginBottom: 4 }}
        >
          <div style={{ display: 'flex', alignItems: 'center', gap: 6, marginBottom: 3 }}>
            <div style={{ width: 7, height: 7, borderRadius: '50%', background: '#34d399', boxShadow: '0 0 6px #34d399' }} />
            <span style={{ fontSize: 9, fontWeight: 700, color: '#6b7280', textTransform: 'uppercase', letterSpacing: '0.1em' }}>
              Predictive Metabolic Simulation
            </span>
          </div>
          <h2 style={{ fontSize: 18, fontWeight: 900, margin: 0, background: 'linear-gradient(135deg,#7c3aed,#a855f7,#ec4899)', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent', backgroundClip: 'text' }}>
            {'\uD83E\uDDEC'} Digital Twin
          </h2>
          <p style={{ fontSize: 10, color: '#6b7280', marginTop: 3 }}>
            BMI {bmi.toFixed(1)} · {bmiLabel(bmi)} · {userHeight} cm / {userWeight} kg
          </p>
        </motion.div>

        {/* Gland score cards */}
        {GLAND_CARDS.map(function (card, i) {
          var score = nodeScore(card.nodeId);
          var pct = Math.min(score * 10, 100);
          var isHov = hoveredNode === card.nodeId;
          return (
            <motion.div
              key={card.nodeId}
              initial={{ x: -40, opacity: 0 }}
              animate={{ x: 0, opacity: 1 }}
              transition={{ duration: 0.55, delay: i * 0.1, ease: [0.4, 0, 0.2, 1] }}
              onMouseEnter={function () { setHoveredNode(card.nodeId); }}
              onMouseLeave={function () { setHoveredNode(null); }}
              style={{
                background: isHov ? 'rgba(255,255,255,0.1)' : 'rgba(255,255,255,0.04)',
                backdropFilter: 'blur(14px)',
                WebkitBackdropFilter: 'blur(14px)',
                border: isHov ? '1px solid ' + card.from + '55' : '1px solid rgba(255,255,255,0.08)',
                borderRadius: 14,
                padding: '10px 13px',
                cursor: 'pointer',
                transition: 'all 0.22s ease',
                transform: isHov ? 'translateX(4px)' : 'translateX(0)',
                boxShadow: isHov ? '0 4px 20px ' + card.from + '30' : 'none',
              }}
            >
              <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                <div style={{
                  width: 34, height: 34, borderRadius: 10, flexShrink: 0,
                  background: 'linear-gradient(135deg,' + card.from + '22,' + card.to + '22)',
                  border: '1px solid ' + card.from + '33',
                  display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 15,
                }}>
                  {card.icon}
                </div>
                <div style={{ flex: 1, minWidth: 0 }}>
                  <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 4 }}>
                    <span style={{ fontSize: 10, fontWeight: 700, color: '#d1d5db', lineHeight: 1 }}>{card.label}</span>
                    <span style={{
                      fontSize: 13, fontWeight: 900, lineHeight: 1,
                      background: 'linear-gradient(135deg,' + card.from + ',' + card.to + ')',
                      WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent', backgroundClip: 'text',
                    }}>
                      {score.toFixed(1)}
                    </span>
                  </div>
                  <div style={{ fontSize: 9, color: '#6b7280', marginBottom: 5 }}>{card.desc}</div>
                  <div style={{ height: 3, background: 'rgba(255,255,255,0.06)', borderRadius: 99, overflow: 'hidden' }}>
                    <motion.div
                      initial={{ width: 0 }}
                      animate={{ width: pct + '%' }}
                      transition={{ duration: 1.1, delay: i * 0.1 + 0.3, ease: 'easeOut' }}
                      style={{
                        height: '100%', borderRadius: 99,
                        background: 'linear-gradient(90deg,' + card.from + ',' + card.to + ')',
                      }}
                    />
                  </div>
                </div>
              </div>
            </motion.div>
          );
        })}

        {/* Overall risk badge */}
        {overallRisk > 0 && (
          <motion.div
            initial={{ opacity: 0, y: 8 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.8 }}
            style={{
              display: 'flex', alignItems: 'center', gap: 8,
              padding: '7px 12px',
              background: 'rgba(255,255,255,0.05)',
              backdropFilter: 'blur(12px)',
              border: '1px solid rgba(255,255,255,0.08)',
              borderRadius: 12,
              fontSize: 11,
              marginTop: 2,
            }}
          >
            <span style={{ color: '#9ca3af' }}>Overall Risk</span>
            <span style={{ fontWeight: 900, background: 'linear-gradient(135deg,#7c3aed,#ec4899)', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent', backgroundClip: 'text' }}>
              {overallRisk.toFixed(1)}
            </span>
            <span style={{
              fontSize: 9, fontWeight: 700,
              color: riskColor(overallRisk),
              background: riskColor(overallRisk) + '1a',
              borderRadius: 6, padding: '2px 7px',
            }}>
              {(riskCategory || riskLabel(overallRisk)).toUpperCase()}
            </span>
          </motion.div>
        )}

        {/* Simulate button */}
        <motion.button
          initial={{ opacity: 0, y: 8 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 1.0 }}
          onClick={handleSimulate}
          disabled={isSimulating}
          style={{
            marginTop: 6,
            padding: '10px 18px',
            background: isSimulating
              ? 'linear-gradient(135deg,#10b981,#059669)'
              : 'linear-gradient(135deg,#7c3aed,#a855f7,#ec4899)',
            color: '#fff',
            border: 'none',
            borderRadius: 12,
            fontSize: 12,
            fontWeight: 800,
            cursor: isSimulating ? 'wait' : 'pointer',
            letterSpacing: '0.04em',
            transition: 'all 0.3s ease',
            boxShadow: isSimulating
              ? '0 4px 20px rgba(16,185,129,0.3)'
              : '0 4px 20px rgba(124,58,237,0.3)',
          }}
        >
          {isSimulating ? '\u26A1 Simulating\u2026' : '\uD83E\uDDEC Run Metabolic Simulation'}
        </motion.button>
      </div>

      {/* ═══════════════════════════════════════════════════════════════
          RIGHT PANEL — 3D Body (50-62% width)
          ═══════════════════════════════════════════════════════════════ */}
      <div style={{
        flex: 1,
        position: 'relative',
        zIndex: 5,
        minHeight: isMobile ? 480 : 0,
        display: 'flex',
        alignItems: 'stretch',
      }}>
        <Body3D
          width="100%"
          height="100%"
          userHeight={userHeight}
          userWeight={userWeight}
          pcosIndex={pcosIndex}
          insulinResistance={insulinResistance}
          thyroidFactor={thyroidFactor}
          isSimulating={isSimulating}
          hoveredNode={hoveredNode}
          onNodeHover={setHoveredNode}
          style={{ flex: 1, minHeight: isMobile ? 460 : 400 }}
        />

        {/* Bottom legend dots */}
        <div style={{
          position: 'absolute', bottom: 16, left: 0, right: 0,
          display: 'flex', justifyContent: 'center', gap: 16, zIndex: 10,
        }}>
          {[
            { color: '#a855f7', label: 'PCOS' },
            { color: '#3b82f6', label: 'Insulin' },
            { color: '#f97316', label: 'Thyroid' },
            { color: '#10b981', label: 'Healthy' },
          ].map(function (item) {
            return (
              <div key={item.label} style={{ display: 'flex', alignItems: 'center', gap: 4 }}>
                <div style={{ width: 7, height: 7, borderRadius: '50%', background: item.color, boxShadow: '0 0 6px ' + item.color + '88' }} />
                <span style={{ fontSize: 9, color: '#9ca3af', fontWeight: 600 }}>{item.label}</span>
              </div>
            );
          })}
        </div>
      </div>

      {/* Global keyframes */}
      <style>{'\n@keyframes dtpSpin { to { transform: rotate(360deg); } }\n'}</style>
    </div>
  );
}

export default DigitalTwinPanel;
