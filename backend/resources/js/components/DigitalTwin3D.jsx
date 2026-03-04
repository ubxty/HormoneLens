import React, { useEffect, useState, useMemo } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import Body3D from './Body3D';

// ─────────────────────────────────────────────────────────────────────────────
// Constants & helpers
// ─────────────────────────────────────────────────────────────────────────────
const computeBmi   = (w, h) => (!w || !h || h < 1) ? 22 : w / ((h / 100) * (h / 100));
const bmiLabel     = (b) => b < 18.5 ? 'Underweight' : b < 25 ? 'Normal' : b < 30 ? 'Overweight' : 'Obese';
const bmiToBodyShape = (b) => {
  if (b <= 18.5) return 0.1;
  if (b >= 32) return 1;
  return (b - 18.5) / (32 - 18.5);
};
const riskLabel    = (v) => v >= 7 ? 'High' : v >= 4 ? 'Moderate' : 'Low';
const riskHex      = (v) => v >= 7 ? '#ef4444' : v >= 4 ? '#f59e0b' : '#10b981';

// Health zone definitions — positioned over the SVG body (top/left/w/h as % of body container)
const BODY_ZONES = [
  { id: 'head',    label: 'Stress',             desc: 'Cortisol Elevated',         icon: '🧠', scoreKey: 'stress_score',             glowColor: '#ef4444', top: '2.5%',  left: '28%', w: '44%', h: '12%' },
  { id: 'chest',   label: 'Sleep Recovery',      desc: 'Autonomic Disruption',       icon: '😴', scoreKey: 'sleep_score',              glowColor: '#3b82f6', top: '17%',   left: '23%', w: '54%', h: '12%' },
  { id: 'waist',   label: 'Insulin Resistance',  desc: 'Low Insulin Sensitivity',    icon: '🩸', scoreKey: 'insulin_resistance_score', glowColor: '#f97316', top: '28%',   left: '8%',  w: '84%', h: '12%' },
  { id: 'abdomen', label: 'PCOS / Hormonal',     desc: 'Hormonal Imbalance Zone',    icon: '⚕️', scoreKey: 'metabolic_health_score',   glowColor: '#a855f7', top: '38%',   left: '22%', w: '56%', h: '13%' },
  { id: 'thighs',  label: 'Metabolic Risk',       desc: 'Lower Body Stress',          icon: '⚡', scoreKey: 'diet_score',               glowColor: '#6366f1', top: '59%',   left: '18%', w: '64%', h: '16%' },
];

// Score cards
const SCORE_CARDS = [
  { key: 'stress_score',               label: 'Stress Level',       icon: '🧠', from: '#f59e0b', to: '#ef4444', zoneId: 'head'    },
  { key: 'sleep_score',                label: 'Sleep Recovery',      icon: '😴', from: '#3b82f6', to: '#8b5cf6', zoneId: 'chest'   },
  { key: 'insulin_resistance_score',   label: 'Insulin Resist.',     icon: '🩸', from: '#c24dff', to: '#f97316', zoneId: 'waist'   },
  { key: 'metabolic_health_score',     label: 'Metabolic Health',    icon: '⚡', from: '#7c3aed', to: '#c24dff', zoneId: 'abdomen' },
  { key: 'diet_score',                 label: 'Diet Quality',        icon: '🥗', from: '#10b981', to: '#06b6d4', zoneId: 'thighs'  },
];

// SVG connector lines endpoints (0-100 viewBox space)
const CARD_LINE_Y  = [36, 46, 56, 66, 76];
const ZONE_LINE_Y  = { head: 9, chest: 23, waist: 34, abdomen: 45, thighs: 67 };
const LINE_START_X = 35;
const LINE_END_X   = 54;

// ─────────────────────────────────────────────────────────────────────────────
// Floating zone tooltip
// ─────────────────────────────────────────────────────────────────────────────
function ZoneTooltip({ zone, score, side }) {
  const rl = riskLabel(score);
  const rc = riskHex(score);
  const posStyle = side === 'right'
    ? { right: 'calc(100% + 8px)', top: zone.top }
    : { left:  'calc(100% + 8px)', top: zone.top };

  return (
    <motion.div
      key={zone.id}
      initial={{ opacity: 0, scale: 0.86, y: 8 }}
      animate={{ opacity: 1, scale: 1, y: 0 }}
      exit={{ opacity: 0, scale: 0.86, y: 8 }}
      transition={{ duration: 0.18, ease: 'easeOut' }}
      style={{ position: 'absolute', zIndex: 60, pointerEvents: 'none', width: 152, ...posStyle }}
    >
      <div style={{
        background: 'rgba(255,255,255,0.20)',
        backdropFilter: 'blur(20px)',
        WebkitBackdropFilter: 'blur(20px)',
        border: `1px solid ${zone.glowColor}44`,
        borderRadius: 14,
        padding: '10px 13px',
        boxShadow: `0 12px 40px ${zone.glowColor}28, 0 2px 8px rgba(0,0,0,.10)`,
      }}>
        <div style={{ fontSize: 22, marginBottom: 5 }}>{zone.icon}</div>
        <div style={{ fontSize: 9, fontWeight: 800, color: '#581c87', textTransform: 'uppercase', letterSpacing: '.08em', marginBottom: 2 }}>{zone.label}</div>
        <div style={{ fontSize: 10, color: '#6b7280', marginBottom: 7 }}>{zone.desc}</div>
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
          <span style={{ fontSize: 18, fontWeight: 900, background: 'linear-gradient(135deg,#7c3aed,#ec4899)', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent', backgroundClip: 'text' }}>
            {typeof score === 'number' ? score.toFixed(1) : '—'}
          </span>
          <span style={{ fontSize: 9, fontWeight: 700, color: rc, background: `${rc}1a`, borderRadius: 6, padding: '2px 7px' }}>
            {rl} Risk
          </span>
        </div>
      </div>
    </motion.div>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// Root component
// ─────────────────────────────────────────────────────────────────────────────
export default function DigitalTwin3D() {
  const [twin,        setTwin]        = useState(null);
  const [profile,     setProfile]     = useState(null);
  const [loading,     setLoading]     = useState(true);
  const [hoveredZone, setHoveredZone] = useState(null);
  const [isMobile,    setIsMobile]    = useState(window.innerWidth < 900);
  const [bodyShape,   setBodyShape]   = useState(0.45);

  // Responsive
  useEffect(() => {
    const h = () => setIsMobile(window.innerWidth < 900);
    window.addEventListener('resize', h);
    return () => window.removeEventListener('resize', h);
  }, []);

  // Data fetch
  useEffect(() => {
    const csrf = document.querySelector('meta[name=csrf-token]')?.content ?? '';
    const opts = { headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf }, credentials: 'same-origin' };
    Promise.all([
      fetch('/api/digital-twin/active', opts).then(r => r.json()).catch(() => ({})),
      fetch('/api/health-profile',       opts).then(r => r.json()).catch(() => ({})),
    ]).then(([td, hd]) => {
      if (td?.success && td.data) setTwin(td.data);
      if (hd?.success && hd.data) setProfile(hd.data);
      setLoading(false);
    });
  }, []);

  // Computed
  const bmi     = computeBmi(profile?.weight, profile?.height);
  const bodyShapeAuto = useMemo(() => bmiToBodyShape(bmi), [bmi]);

  useEffect(() => {
    setBodyShape(bodyShapeAuto);
  }, [bodyShapeAuto]);

  return (
    <div style={{
      display: 'flex',
      flexDirection: isMobile ? 'column' : 'row',
      height: isMobile ? 'auto' : 'calc(100vh - 56px)',
      background: 'linear-gradient(135deg, rgba(109,40,217,.07) 0%, rgba(139,92,246,.06) 40%, rgba(236,72,153,.06) 100%)',
      position: 'relative',
      overflow: isMobile ? 'visible' : 'hidden',
      fontFamily: 'inherit',
    }}>

      {/* Ambient blobs */}
      <div style={{ position: 'absolute', inset: 0, pointerEvents: 'none', overflow: 'hidden', zIndex: 0 }}>
        <div style={{ position: 'absolute', width: 300, height: 300, borderRadius: '50%', background: 'radial-gradient(circle,#7c3aed,transparent 70%)', filter: 'blur(70px)', opacity: .13, top: -80, right: -60, animation: 'dt3Blob 18s ease-in-out infinite' }} />
        <div style={{ position: 'absolute', width: 240, height: 240, borderRadius: '50%', background: 'radial-gradient(circle,#ec4899,transparent 70%)', filter: 'blur(70px)', opacity: .11, bottom: '5%', left: -40, animation: 'dt3Blob 24s ease-in-out 6s infinite' }} />
        <div style={{ position: 'absolute', width: 180, height: 180, borderRadius: '50%', background: 'radial-gradient(circle,#818cf8,transparent 70%)', filter: 'blur(60px)', opacity: .10, top: '40%', right: '20%', animation: 'dt3Blob 20s ease-in-out 3s infinite' }} />
      </div>

      {/* SVG connector lines */}
      {!isMobile && (
        <svg
          viewBox="0 0 100 100" preserveAspectRatio="none"
          style={{ position: 'absolute', inset: 0, width: '100%', height: '100%', pointerEvents: 'none', zIndex: 4 }}
        >
          <defs>
            {SCORE_CARDS.map(c => (
              <linearGradient key={c.key} id={`dt3Lg-${c.key}`} x1="0" y1="0" x2="1" y2="0">
                <stop offset="0%"   stopColor={c.from} stopOpacity=".55" />
                <stop offset="100%" stopColor={c.to}   stopOpacity=".07" />
              </linearGradient>
            ))}
          </defs>
          {twin && SCORE_CARDS.map((c, i) => {
            const sy0 = CARD_LINE_Y[i];
            const ey0 = ZONE_LINE_Y[c.zoneId] ?? 50;
            const mx  = (LINE_START_X + LINE_END_X) / 2;
            return (
              <path
                key={c.key}
                d={`M ${LINE_START_X} ${sy0} C ${mx - 2} ${sy0}, ${mx + 2} ${ey0}, ${LINE_END_X} ${ey0}`}
                fill="none" stroke={`url(#dt3Lg-${c.key})`}
                strokeWidth=".32" strokeLinecap="round"
                strokeDasharray=".85 .65"
                style={{ animation: 'dt3Dash 1.5s linear infinite', animationDelay: `${i * 0.2}s` }}
              />
            );
          })}
        </svg>
      )}

      {/* LEFT: Score Cards */}
      <div style={{
        width: isMobile ? '100%' : '34%',
        padding: isMobile ? '20px 16px 12px' : '20px 12px 20px 20px',
        display: 'flex', flexDirection: 'column',
        justifyContent: isMobile ? 'flex-start' : 'center',
        gap: 10, position: 'relative', zIndex: 5,
        overflowY: isMobile ? 'visible' : 'auto',
      }}>

        <motion.div initial={{ opacity: 0, y: -16 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: .55 }} style={{ marginBottom: 6 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 6, marginBottom: 3 }}>
            <div style={{ width: 7, height: 7, borderRadius: '50%', background: '#34d399', animation: 'dt3Status 2s ease-in-out infinite' }} />
            <span style={{ fontSize: 9, fontWeight: 700, color: '#9ca3af', textTransform: 'uppercase', letterSpacing: '.08em' }}>AI Metabolic Model</span>
          </div>
          <h2 style={{ fontSize: 18, fontWeight: 900, background: 'linear-gradient(135deg,#7c3aed,#a855f7,#ec4899)', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent', backgroundClip: 'text', margin: 0 }}>
            🧬 Digital Twin
          </h2>
          {profile ? (
            <p style={{ fontSize: 10, color: '#6b7280', marginTop: 3 }}>
              BMI {bmi.toFixed(1)} · {bmiLabel(bmi)} · {profile.height} cm / {profile.weight} kg
            </p>
          ) : (
            <p style={{ fontSize: 10, color: '#f59e0b', marginTop: 3 }}>Add Health Profile to enable body model</p>
          )}
        </motion.div>

        <motion.p initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ delay: 1.2 }}
          style={{ fontSize: 9, color: '#9ca3af', letterSpacing: '.05em', userSelect: 'none', margin: '-4px 0 2px' }}>
          ↔ Drag to rotate 360° · Hover zones to inspect
        </motion.p>

        {SCORE_CARDS.map((card, i) => {
          const score = twin?.[card.key] ?? 0;
          const pct   = Math.min(score * 10, 100);
          const isHov = hoveredZone === card.zoneId;
          const zone  = BODY_ZONES.find(z => z.id === card.zoneId);
          return (
            <motion.div
              key={card.key}
              initial={{ x: -55, opacity: 0 }}
              animate={{ x: 0, opacity: 1 }}
              transition={{ duration: .6, delay: i * .12, ease: [.4, 0, .2, 1] }}
              onMouseEnter={() => setHoveredZone(card.zoneId)}
              onMouseLeave={() => setHoveredZone(null)}
              style={{
                background: isHov ? 'rgba(255,255,255,.82)' : 'rgba(255,255,255,.50)',
                backdropFilter: 'blur(16px)', WebkitBackdropFilter: 'blur(16px)',
                border: isHov ? `1px solid ${zone?.glowColor}55` : '1px solid rgba(255,255,255,.35)',
                borderRadius: 15, padding: '11px 13px',
                boxShadow: isHov ? `0 6px 24px ${zone?.glowColor}30` : '0 3px 12px rgba(109,40,217,.06)',
                cursor: 'pointer', transition: 'all .22s ease',
                transform: isHov ? 'translateX(4px)' : 'translateX(0)',
              }}
            >
              <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                <div style={{ width: 35, height: 35, borderRadius: 11, flexShrink: 0, background: `linear-gradient(135deg,${card.from}1e,${card.to}1e)`, border: `1px solid ${card.from}2e`, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 15 }}>
                  {card.icon}
                </div>
                <div style={{ flex: 1, minWidth: 0 }}>
                  <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 5 }}>
                    <span style={{ fontSize: 10, fontWeight: 700, color: '#374151', lineHeight: 1 }}>{card.label}</span>
                    <span style={{ fontSize: 14, fontWeight: 900, background: `linear-gradient(135deg,${card.from},${card.to})`, WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent', backgroundClip: 'text', lineHeight: 1 }}>
                      {score.toFixed(1)}
                    </span>
                  </div>
                  <div style={{ height: 4, background: 'rgba(0,0,0,.07)', borderRadius: 99, overflow: 'hidden' }}>
                    <motion.div
                      initial={{ width: 0 }}
                      animate={{ width: `${pct}%` }}
                      transition={{ duration: 1.2, delay: i * .12 + .4, ease: 'easeOut' }}
                      style={{ height: '100%', background: `linear-gradient(90deg,${card.from},${card.to})`, borderRadius: 99 }}
                    />
                  </div>
                </div>
              </div>
            </motion.div>
          );
        })}

        {!loading && !twin && (
          <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ delay: .9 }}
            style={{ textAlign: 'center', padding: '16px 12px', background: 'rgba(255,255,255,.4)', borderRadius: 14, border: '1px dashed rgba(139,92,246,.3)', marginTop: 4 }}
          >
            <div style={{ fontSize: 28, marginBottom: 7 }}>🧬</div>
            <p style={{ fontSize: 11, fontWeight: 700, color: '#374151', marginBottom: 4 }}>No Twin Generated Yet</p>
            <p style={{ fontSize: 10, color: '#9ca3af', marginBottom: 10, lineHeight: 1.5 }}>Complete your Health Profile to activate the body model.</p>
            <a href="/digital-twin" style={{ display: 'inline-block', padding: '6px 16px', background: 'linear-gradient(135deg,#7c3aed,#ec4899)', color: '#fff', borderRadius: 10, fontSize: 11, fontWeight: 700, textDecoration: 'none' }}>
              Generate Twin →
            </a>
          </motion.div>
        )}

        {twin && (
          <motion.div initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 1.0 }}
            style={{ display: 'flex', alignItems: 'center', gap: 8, padding: '7px 12px', background: 'rgba(255,255,255,.6)', backdropFilter: 'blur(12px)', WebkitBackdropFilter: 'blur(12px)', border: '1px solid rgba(255,255,255,.4)', borderRadius: 12, fontSize: 11, marginTop: 2 }}
          >
            <span style={{ color: '#6b7280' }}>Overall Risk</span>
            <span style={{ fontWeight: 900, background: 'linear-gradient(135deg,#7c3aed,#ec4899)', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent', backgroundClip: 'text' }}>
              {twin.overall_risk_score?.toFixed(1)}
            </span>
            <span style={{ fontSize: 9, fontWeight: 700, color: riskHex((twin.overall_risk_score || 0) / 10), background: `${riskHex((twin.overall_risk_score || 0) / 10)}1a`, borderRadius: 6, padding: '2px 7px' }}>
              {(twin.risk_category || '').toUpperCase()}
            </span>
          </motion.div>
        )}
      </div>

      {/* RIGHT: 3D Body */}
      <div style={{
        flex: 1,
        display: 'flex', flexDirection: 'column',
        alignItems: 'center', justifyContent: 'center',
        position: 'relative', zIndex: 5,
        padding: isMobile ? '16px' : '16px 24px 16px 0',
        minHeight: isMobile ? 520 : 'auto',
      }}>

        {loading && (
          <div style={{ position: 'absolute', inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 10 }}>
            <div style={{ textAlign: 'center' }}>
              <div style={{ width: 40, height: 40, border: '3px solid rgba(124,58,237,.25)', borderTopColor: '#7c3aed', borderRadius: '50%', animation: 'dt3Spin .8s linear infinite', margin: '0 auto 12px' }} />
              <p style={{ color: '#9ca3af', fontSize: 13 }}>Loading Digital Twin…</p>
            </div>
          </div>
        )}

        {/* Drag hint */}
        <motion.p
          initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ delay: 1.2 }}
          style={{ fontSize: 9, color: '#9ca3af', marginBottom: 6, letterSpacing: '.05em', userSelect: 'none', textAlign: 'center' }}
        >
          ↔ Drag to rotate 360° &nbsp;·&nbsp; Hover zones to inspect
        </motion.p>

        <motion.div
          initial={{ opacity: 0, y: 8 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 1.15 }}
          style={{
            width: '100%',
            maxWidth: 360,
            marginBottom: 8,
            background: 'rgba(255,255,255,.65)',
            border: '1px solid rgba(255,255,255,.4)',
            borderRadius: 12,
            padding: '10px 12px',
            backdropFilter: 'blur(12px)',
            WebkitBackdropFilter: 'blur(12px)',
          }}
        >
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 6 }}>
            <span style={{ fontSize: 11, fontWeight: 700, color: '#4b5563' }}>Body Shape</span>
            <span style={{ fontSize: 10, color: '#6b7280' }}>{Math.round(bodyShape * 100)}%</span>
          </div>
          <input
            type="range"
            min="0"
            max="100"
            value={Math.round(bodyShape * 100)}
            onChange={(e) => setBodyShape(Number(e.target.value) / 100)}
            style={{ width: '100%' }}
          />
          <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: 4, fontSize: 9, color: '#6b7280' }}>
            <span>Slim</span>
            <span>Fat</span>
          </div>
        </motion.div>

        {/* 3D Body Canvas */}
        <motion.div
          initial={{ scale: .6, opacity: 0, y: 32 }}
          animate={{ scale: 1,  opacity: 1, y: 0  }}
          transition={{ duration: 1.2, ease: [.4, 0, .2, 1] }}
          style={{ position: 'relative', width: '100%', maxWidth: 360 }}
        >
          <Body3D
            userHeight={profile?.height || 165}
            userWeight={profile?.weight || 58}
            bodyShape={bodyShape}
            hoveredNode={null}
            onNodeHover={() => {}}
            pcosIndex={twin?.metabolic_health_score || 0}
            insulinResistance={twin?.insulin_resistance_score || 0}
            thyroidFactor={Math.max(0.3, 1 - ((twin?.sleep_score || 0) / 20))}
            height={isMobile ? 440 : 480}
          />

          {/* Zone tooltip overlay */}
          <div style={{ position: 'absolute', inset: 0, pointerEvents: 'none' }}>
            <AnimatePresence>
              {hoveredZone && (() => {
                const zone  = BODY_ZONES.find(z => z.id === hoveredZone);
                if (!zone) return null;
                const score = twin?.[zone.scoreKey] ?? 0;
                const side  = zone.id === 'waist' || zone.id === 'thighs' ? 'right' : 'left';
                return <ZoneTooltip key={hoveredZone} zone={zone} score={score} side={side} />;
              })()}
            </AnimatePresence>
          </div>
        </motion.div>

        {/* BMI pill */}
        {profile && (
          <motion.div
            initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 1.3 }}
            style={{ marginTop: 10, display: 'flex', alignItems: 'center', gap: 8, padding: '5px 14px', background: 'rgba(255,255,255,.65)', backdropFilter: 'blur(12px)', WebkitBackdropFilter: 'blur(12px)', border: '1px solid rgba(255,255,255,.4)', borderRadius: 20, fontSize: 11 }}
          >
            <span style={{ color: '#6b7280' }}>BMI</span>
            <span style={{ fontWeight: 900, background: 'linear-gradient(135deg,#7c3aed,#ec4899)', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent', backgroundClip: 'text' }}>
              {bmi.toFixed(1)}
            </span>
            <span style={{ color: '#9ca3af' }}>·</span>
            <span style={{ color: '#374151', fontWeight: 700 }}>{bmiLabel(bmi)}</span>
            <span style={{ color: '#9ca3af' }}>·</span>
            <span style={{ color: '#6b7280' }}>{profile.height} cm</span>
          </motion.div>
        )}

        {/* Zone legend dots */}
        <div style={{ display: 'flex', gap: 10, marginTop: 10 }}>
          {BODY_ZONES.map((z) => (
            <div
              key={z.id}
              onMouseEnter={() => setHoveredZone(z.id)}
              onMouseLeave={() => setHoveredZone(null)}
              title={z.label}
              style={{ width: 8, height: 8, borderRadius: '50%', background: z.glowColor, opacity: hoveredZone === z.id ? 1 : 0.45, cursor: 'pointer', transition: 'opacity .2s, transform .2s', transform: hoveredZone === z.id ? 'scale(1.7)' : 'scale(1)', boxShadow: hoveredZone === z.id ? `0 0 8px ${z.glowColor}` : 'none' }}
            />
          ))}
        </div>
      </div>

      <style>{`
        @keyframes dt3Spin   { to { transform: rotate(360deg); } }
        @keyframes dt3Blob   { 0%,100%{transform:translate(0,0) scale(1)} 33%{transform:translate(25px,-18px) scale(1.04)} 66%{transform:translate(-18px,12px) scale(.96)} }
        @keyframes dt3Status { 0%,100%{opacity:.5;transform:scale(1)} 50%{opacity:1;transform:scale(1.3)} }
        @keyframes dt3Dash   { to { stroke-dashoffset: -1.5; } }
      `}</style>
    </div>
  );
}
