import React, { useEffect, useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';

/* ═══════════════════════════════════════════════════════════════════════════════
   HormoneLens — Interactive Digital Twin Dashboard
   Gender-aware SVG avatar (men-purple.svg / women-purple.svg)
   ═══════════════════════════════════════════════════════════════════════════════ */

// ── Score cards (left panel) ──────────────────────────────────────────────────
const SCORE_CARDS = [
  { key: 'stress_score',             label: 'Stress Level',      icon: '🧠', from: '#f59e0b', to: '#ef4444', zone: 'head'    },
  { key: 'sleep_score',              label: 'Sleep Recovery',    icon: '😴', from: '#3b82f6', to: '#8b5cf6', zone: 'chest'   },
  { key: 'insulin_resistance_score', label: 'Insulin Resistance',icon: '🩸', from: '#c24dff', to: '#ff6ec7', zone: 'abdomen' },
  { key: 'metabolic_health_score',   label: 'Metabolic Health',  icon: '⚡',    from: '#5f6fff', to: '#c24dff', zone: 'ovaries' },
  { key: 'diet_score',               label: 'Diet Quality',      icon: '🥗', from: '#10b981', to: '#06b6d4', zone: 'thighs'  },
];

// ── Hormone zones with per-gender positions (% of image) ─────────────────────
const BODY_ZONES = [
  { id: 'head',    label: 'Brain / HPA Axis',   desc: 'Cortisol & Stress Response',    icon: '🧠', scoreKey: 'stress_score',             color: '#ef4444', pos: { male: { x: 50, y: 9  }, female: { x: 48, y: 10 } } },
  { id: 'chest',   label: 'Thyroid / Chest',     desc: 'Metabolic Rate Control',        icon: '🦋', scoreKey: 'sleep_score',              color: '#3b82f6', pos: { male: { x: 50, y: 38 }, female: { x: 48, y: 36 } } },
  { id: 'abdomen', label: 'Pancreas / Gut',      desc: 'Insulin & Blood Sugar',         icon: '🩸', scoreKey: 'insulin_resistance_score', color: '#f97316', pos: { male: { x: 50, y: 55 }, female: { x: 48, y: 50 } } },
  { id: 'ovaries', label: 'Reproductive / PCOS', desc: 'Hormonal Balance Zone',         icon: '⚕️', scoreKey: 'metabolic_health_score',   color: '#a855f7', pos: { male: { x: 50, y: 67 }, female: { x: 48, y: 60 } } },
  { id: 'thighs',  label: 'Lower Body',          desc: 'Fat Distribution & Metabolism', icon: '⚡', scoreKey: 'diet_score',               color: '#6366f1', pos: { male: { x: 50, y: 85 }, female: { x: 48, y: 82 } } },
];

// ── BMI / Height helpers ──────────────────────────────────────────────────────
const computeBmi = (w, h) => (!w || !h || h < 1) ? 22 : w / ((h / 100) ** 2);
const bmiLabel   = b => b < 18.5 ? 'Underweight' : b < 25 ? 'Normal' : b < 30 ? 'Overweight' : 'Obese';
const riskLabel  = v => v >= 7 ? 'High' : v >= 4 ? 'Moderate' : 'Low';
const riskHex    = v => v >= 7 ? '#ef4444' : v >= 4 ? '#f59e0b' : '#10b981';

// ── Zone tooltip ──────────────────────────────────────────────────────────────
function ZoneTooltip({ zone, score, position }) {
  const rl = riskLabel(score);
  const rc = riskHex(score);
  const onRight = position.x > 50;
  return (
    <motion.div
      initial={{ opacity: 0, scale: 0.85, y: 6 }}
      animate={{ opacity: 1, scale: 1, y: 0 }}
      exit={{ opacity: 0, scale: 0.85, y: 6 }}
      transition={{ duration: .18 }}
      style={{
        position: 'absolute',
        left: onRight ? 'auto' : `${position.x + 8}%`,
        right: onRight ? `${108 - position.x}%` : 'auto',
        top: `${position.y - 3}%`,
        zIndex: 60, pointerEvents: 'none', width: 172,
      }}
    >
      <div style={{
        background: 'rgba(255,255,255,.92)',
        backdropFilter: 'blur(20px)', WebkitBackdropFilter: 'blur(20px)',
        border: `1.5px solid ${zone.color}40`,
        borderRadius: 14, padding: '12px 14px',
        boxShadow: `0 12px 40px ${zone.color}25, 0 2px 8px rgba(0,0,0,.08)`,
      }}>
        <div style={{ fontSize: 24, marginBottom: 5 }}>{zone.icon}</div>
        <div style={{ fontSize: 10, fontWeight: 800, color: '#581c87', textTransform: 'uppercase', letterSpacing: '.08em', marginBottom: 2 }}>{zone.label}</div>
        <div style={{ fontSize: 10, color: '#6b7280', marginBottom: 8 }}>{zone.desc}</div>
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
          <span style={{ fontSize: 20, fontWeight: 900, background: `linear-gradient(135deg,${zone.color},#c24dff)`, WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent', backgroundClip: 'text' }}>
            {typeof score === 'number' ? score.toFixed(1) : '\u2014'}
          </span>
          <span style={{ fontSize: 9, fontWeight: 700, color: rc, background: `${rc}15`, borderRadius: 6, padding: '2px 8px' }}>{rl} Risk</span>
        </div>
      </div>
    </motion.div>
  );
}

// ══════════════════════════════════════════════════════════════════════════════
export default function InteractiveDigitalTwin() {
  const [twin, setTwin]           = useState(null);
  const [profile, setProfile]     = useState(null);
  const [loading, setLoading]     = useState(true);
  const [hoveredZone, setHovered] = useState(null);
  const [isMobile, setIsMobile]   = useState(window.innerWidth < 900);

  useEffect(() => {
    const h = () => setIsMobile(window.innerWidth < 900);
    window.addEventListener('resize', h);
    return () => window.removeEventListener('resize', h);
  }, []);

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

  const height = profile?.height || 165;
  const weight = profile?.weight || 58;
  const gender = profile?.gender || 'female';
  const bmi    = computeBmi(weight, height);

  const imgSrc = gender === 'male' ? '/images/men-purple.svg' : '/images/women-purple.svg';

  const CARD_Y = [22, 36, 50, 64, 78];
  const zoneYPct = { head: 10, chest: 28, abdomen: 42, ovaries: 53, thighs: 70 };

  if (loading) return (
    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', minHeight: '100vh' }}>
      <div style={{ textAlign: 'center' }}>
        <div style={{ width: 44, height: 44, border: '3px solid rgba(194,77,255,.25)', borderTopColor: '#c24dff', borderRadius: '50%', animation: 'dtSpin .7s linear infinite', margin: '0 auto 14px' }} />
        <p style={{ color: '#9ca3af', fontSize: 13 }}>Loading Digital Twin\u2026</p>
        <style>{'@keyframes dtSpin { to { transform: rotate(360deg); } }'}</style>
      </div>
    </div>
  );

  return (
    <div style={{
      display: 'flex', flexDirection: isMobile ? 'column' : 'row',
      height: isMobile ? 'auto' : 'calc(100vh - 56px)',
      background: 'linear-gradient(135deg,rgba(95,111,255,.05),rgba(194,77,255,.05) 50%,rgba(255,110,199,.05))',
      position: 'relative', overflow: isMobile ? 'visible' : 'hidden',
    }}>

      {/* Ambient blobs */}
      <div style={{ position: 'absolute', inset: 0, pointerEvents: 'none', overflow: 'hidden', zIndex: 0 }}>
        <div style={{ position: 'absolute', width: 300, height: 300, borderRadius: '50%', background: 'radial-gradient(circle,rgba(95,111,255,.12),transparent 70%)', top: -80, right: -60, animation: 'dtBlob 20s ease-in-out infinite' }} />
        <div style={{ position: 'absolute', width: 250, height: 250, borderRadius: '50%', background: 'radial-gradient(circle,rgba(194,77,255,.10),transparent 70%)', bottom: '5%', left: -40, animation: 'dtBlob 24s ease-in-out 6s infinite' }} />
      </div>

      {/* SVG connector lines */}
      {!isMobile && (
        <svg viewBox="0 0 100 100" preserveAspectRatio="none"
          style={{ position: 'absolute', inset: 0, width: '100%', height: '100%', pointerEvents: 'none', zIndex: 4 }}>
          <defs>
            {SCORE_CARDS.map(c => (
              <linearGradient key={c.key} id={`lg-${c.key}`} x1="0" y1="0" x2="1" y2="0">
                <stop offset="0%" stopColor={c.from} stopOpacity=".5" />
                <stop offset="100%" stopColor={c.to} stopOpacity=".08" />
              </linearGradient>
            ))}
          </defs>
          {twin && SCORE_CARDS.map((c, i) => {
            const sy = CARD_Y[i];
            const ey = zoneYPct[c.zone] ?? 50;
            return (
              <path key={c.key}
                d={`M 22 ${sy} C 30 ${sy}, 34 ${ey}, 42 ${ey}`}
                fill="none" stroke={`url(#lg-${c.key})`} strokeWidth=".28"
                strokeLinecap="round" strokeDasharray=".8 .6"
                style={{ animation: 'dtDash 1.5s linear infinite', animationDelay: `${i * .2}s` }}
              />
            );
          })}
        </svg>
      )}

      {/* ═══ LEFT: Score Cards ═══ */}
      <div style={{
        width: isMobile ? '100%' : '22%', minWidth: isMobile ? 'auto' : 200, maxWidth: isMobile ? '100%' : 260,
        padding: isMobile ? '14px 10px 8px' : '12px 10px 12px 14px',
        display: 'flex', flexDirection: 'column',
        justifyContent: isMobile ? 'flex-start' : 'center',
        gap: 6, position: 'relative', zIndex: 5,
        overflowY: isMobile ? 'visible' : 'auto',
      }}>
        <motion.div initial={{ opacity: 0, y: -16 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: .55 }} style={{ marginBottom: 2 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 4, marginBottom: 2 }}>
            <div style={{ width: 5, height: 5, borderRadius: '50%', background: '#34d399', animation: 'dtPulse 2s ease-in-out infinite' }} />
            <span style={{ fontSize: 8, fontWeight: 700, color: '#9ca3af', textTransform: 'uppercase', letterSpacing: '.06em' }}>AI Metabolic Model</span>
          </div>
          <h2 style={{ fontSize: 15, fontWeight: 900, background: 'linear-gradient(135deg,#5f6fff,#c24dff,#ff6ec7)', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent', backgroundClip: 'text', margin: 0 }}>
            {'🧬'} Digital Twin
          </h2>
          {profile ? (
            <p style={{ fontSize: 9, color: '#6b7280', marginTop: 2 }}>
              {gender === 'male' ? '♂' : '♀'} {height} cm · {weight} kg · BMI {bmi.toFixed(1)} <span style={{ color: bmi < 18.5 ? '#3b82f6' : bmi < 25 ? '#10b981' : bmi < 30 ? '#f59e0b' : '#ef4444', fontWeight: 700 }}>({bmiLabel(bmi)})</span>
            </p>
          ) : (
            <p style={{ fontSize: 9, color: '#f59e0b', marginTop: 2 }}>Add Health Profile to personalize</p>
          )}
        </motion.div>

        {SCORE_CARDS.map((card, i) => {
          const score = twin?.[card.key] ?? 0;
          const pct = Math.min(score * 10, 100);
          const isHov = hoveredZone === card.zone;
          const zone = BODY_ZONES.find(z => z.id === card.zone);
          return (
            <motion.div key={card.key}
              initial={{ x: -50, opacity: 0 }} animate={{ x: 0, opacity: 1 }}
              transition={{ duration: .5, delay: i * .1, ease: [.4, 0, .2, 1] }}
              onMouseEnter={() => setHovered(card.zone)}
              onMouseLeave={() => setHovered(null)}
              style={{
                background: isHov ? 'rgba(255,255,255,.85)' : 'rgba(255,255,255,.50)',
                backdropFilter: 'blur(16px)', WebkitBackdropFilter: 'blur(16px)',
                border: isHov ? `1.5px solid ${zone?.color}50` : '1px solid rgba(255,255,255,.35)',
                borderRadius: 10, padding: '7px 9px',
                boxShadow: isHov ? `0 4px 16px ${zone?.color}25` : '0 1px 6px rgba(95,111,255,.05)',
                cursor: 'pointer', transition: 'all .2s ease',
                transform: isHov ? 'translateX(3px)' : 'none',
              }}
            >
              <div style={{ display: 'flex', alignItems: 'center', gap: 7 }}>
                <div style={{
                  width: 26, height: 26, borderRadius: 7, flexShrink: 0,
                  background: `linear-gradient(135deg,${card.from}18,${card.to}18)`,
                  border: `1px solid ${card.from}28`,
                  display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 12,
                }}>{card.icon}</div>
                <div style={{ flex: 1, minWidth: 0 }}>
                  <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 3 }}>
                    <span style={{ fontSize: 9, fontWeight: 700, color: '#374151' }}>{card.label}</span>
                    <span style={{ fontSize: 12, fontWeight: 900, background: `linear-gradient(135deg,${card.from},${card.to})`, WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent', backgroundClip: 'text' }}>
                      {score.toFixed(1)}
                    </span>
                  </div>
                  <div style={{ height: 3, background: 'rgba(0,0,0,.06)', borderRadius: 99 }}>
                    <motion.div initial={{ width: 0 }} animate={{ width: `${pct}%` }}
                      transition={{ duration: 1, delay: i * .1 + .4, ease: 'easeOut' }}
                      style={{ height: '100%', background: `linear-gradient(90deg,${card.from},${card.to})`, borderRadius: 99 }}
                    />
                  </div>
                </div>
              </div>
            </motion.div>
          );
        })}

        {!twin && (
          <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ delay: .8 }}
            style={{ textAlign: 'center', padding: '10px 8px', background: 'rgba(255,255,255,.4)', borderRadius: 10, border: '1px dashed rgba(139,92,246,.3)', marginTop: 2 }}>
            <div style={{ fontSize: 22, marginBottom: 4 }}>{'🧬'}</div>
            <p style={{ fontSize: 10, fontWeight: 700, color: '#374151', marginBottom: 2 }}>No Twin Generated Yet</p>
            <p style={{ fontSize: 9, color: '#9ca3af', marginBottom: 8, lineHeight: 1.4 }}>Complete Health Profile & Disease Data, then generate your Digital Twin.</p>
            <a href="/digital-twin" style={{ display: 'inline-block', padding: '5px 14px', background: 'linear-gradient(135deg,#5f6fff,#c24dff)', color: '#fff', borderRadius: 8, fontSize: 9, fontWeight: 700, textDecoration: 'none' }}>
              Generate Twin →
            </a>
          </motion.div>
        )}

        {twin && (
          <motion.div initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: .9 }}
            style={{ display: 'flex', alignItems: 'center', gap: 6, padding: '5px 9px', background: 'rgba(255,255,255,.6)', backdropFilter: 'blur(12px)', WebkitBackdropFilter: 'blur(12px)', border: '1px solid rgba(255,255,255,.4)', borderRadius: 10, fontSize: 9, marginTop: 2 }}>
            <span style={{ color: '#6b7280' }}>Overall Risk</span>
            <span style={{ fontWeight: 900, background: 'linear-gradient(135deg,#5f6fff,#c24dff)', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent', backgroundClip: 'text' }}>
              {twin.overall_risk_score?.toFixed(1)}
            </span>
            <span style={{ fontSize: 9, fontWeight: 700, color: riskHex((twin.overall_risk_score || 0) / 10), background: `${riskHex((twin.overall_risk_score || 0) / 10)}15`, borderRadius: 6, padding: '2px 8px' }}>
              {(twin.risk_category || '').toUpperCase()}
            </span>
          </motion.div>
        )}
      </div>

      {/* ═══ RIGHT: Avatar Image (full height) ═══ */}
      <div style={{
        flex: 1, display: 'flex', flexDirection: 'column',
        alignItems: 'center', justifyContent: 'center',
        position: 'relative', zIndex: 5,
        padding: isMobile ? '12px 8px' : '8px 16px 8px 0',
        minHeight: isMobile ? 520 : 'auto',
      }}>
        <motion.p initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ delay: 1 }}
          style={{ fontSize: 10, color: '#9ca3af', marginBottom: 12, letterSpacing: '.04em', userSelect: 'none' }}>
          Hover body zones to inspect · Customized to your profile
        </motion.p>

        <motion.div
          initial={{ scale: .7, opacity: 0, y: 30 }}
          animate={{ scale: 1, opacity: 1, y: 0 }}
          transition={{ duration: 1, ease: [.4, 0, .2, 1] }}
          style={{ position: 'relative', height: isMobile ? 'auto' : 'calc(100vh - 120px)', maxHeight: isMobile ? 'none' : 'calc(100vh - 120px)', display: 'flex', flexDirection: 'column', alignItems: 'center' }}
        >
          {/* Avatar SVG image — full height */}
          <img
            src={imgSrc}
            alt={`${gender === 'male' ? 'Male' : 'Female'} Digital Twin`}
            style={{
              height: isMobile ? 'auto' : '100%',
              width: isMobile ? '90%' : 'auto',
              maxHeight: '100%',
              objectFit: 'contain',
              display: 'block',
              filter: 'drop-shadow(0 8px 36px rgba(139,92,246,.25))',
              userSelect: 'none',
              pointerEvents: 'none',
            }}
            draggable={false}
          />

          {/* Zone hotspot overlay */}
          {BODY_ZONES.map(zone => {
            const pos = zone.pos[gender] || zone.pos.female;
            const score = twin?.[zone.scoreKey] ?? 0;
            const isHov = hoveredZone === zone.id;
            const risk = score >= 7 ? 1 : score >= 4 ? 0.6 : 0.3;
            return (
              <div
                key={zone.id}
                onMouseEnter={() => setHovered(zone.id)}
                onMouseLeave={() => setHovered(null)}
                style={{
                  position: 'absolute',
                  left: `${pos.x}%`,
                  top: `${pos.y}%`,
                  transform: 'translate(-50%, -50%)',
                  cursor: 'pointer',
                  zIndex: 10,
                }}
              >
                {/* Outer glow */}
                <div style={{
                  position: 'absolute',
                  left: '50%', top: '50%',
                  transform: 'translate(-50%, -50%)',
                  width: isHov ? 40 : 28,
                  height: isHov ? 40 : 28,
                  borderRadius: '50%',
                  background: `radial-gradient(circle, ${zone.color}40, transparent 70%)`,
                  opacity: isHov ? 0.8 : risk * 0.4,
                  transition: 'all .25s ease',
                }} />
                {/* Inner ring */}
                <div style={{
                  position: 'absolute',
                  left: '50%', top: '50%',
                  transform: 'translate(-50%, -50%)',
                  width: isHov ? 22 : 16,
                  height: isHov ? 22 : 16,
                  borderRadius: '50%',
                  background: `${zone.color}${isHov ? '38' : '10'}`,
                  border: `${isHov ? 1.5 : 0.5}px solid ${zone.color}${isHov ? 'cc' : '40'}`,
                  transition: 'all .25s ease',
                }} />
                {/* Center dot */}
                <div style={{
                  position: 'relative',
                  width: isHov ? 9 : 6,
                  height: isHov ? 9 : 6,
                  borderRadius: '50%',
                  background: zone.color,
                  opacity: isHov ? 1 : 0.7,
                  boxShadow: isHov ? `0 0 10px ${zone.color}80` : 'none',
                  transition: 'all .2s ease',
                  animation: twin ? 'dtZonePulse 2s ease-in-out infinite' : 'none',
                }} />
                {/* Score label on hover */}
                {isHov && (
                  <div style={{
                    position: 'absolute',
                    left: '50%', top: -18,
                    transform: 'translateX(-50%)',
                    fontSize: 10, fontWeight: 800,
                    color: '#fff',
                    textShadow: '0 1px 4px rgba(0,0,0,.5)',
                    whiteSpace: 'nowrap',
                  }}>
                    {score.toFixed(1)}
                  </div>
                )}
              </div>
            );
          })}

          {/* Tooltip overlay */}
          <AnimatePresence>
            {hoveredZone && (() => {
              const zone = BODY_ZONES.find(z => z.id === hoveredZone);
              if (!zone) return null;
              const pos = zone.pos[gender] || zone.pos.female;
              const score = twin?.[zone.scoreKey] ?? 0;
              return <ZoneTooltip key={hoveredZone} zone={zone} score={score} position={{ x: pos.x / 2, y: pos.y }} />;
            })()}
          </AnimatePresence>
        </motion.div>

        {/* Gender & BMI pill */}
        {profile && (
          <motion.div initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 1.2 }}
            style={{ marginTop: 8, display: 'flex', alignItems: 'center', gap: 8, padding: '4px 12px', background: 'rgba(255,255,255,.65)', backdropFilter: 'blur(12px)', WebkitBackdropFilter: 'blur(12px)', border: '1px solid rgba(255,255,255,.4)', borderRadius: 16, fontSize: 10 }}>
            <span style={{ fontWeight: 800, color: gender === 'male' ? '#3b82f6' : '#a855f7' }}>{gender === 'male' ? '♂ Male' : '♀ Female'}</span>
            <span style={{ color: '#d1d5db' }}>|</span>
            <span style={{ color: '#6b7280' }}>BMI</span>
            <span style={{ fontWeight: 900, background: 'linear-gradient(135deg,#5f6fff,#c24dff)', WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent', backgroundClip: 'text' }}>{bmi.toFixed(1)}</span>
            <span style={{ fontWeight: 700, color: bmi < 18.5 ? '#3b82f6' : bmi < 25 ? '#10b981' : bmi < 30 ? '#f59e0b' : '#ef4444' }}>{bmiLabel(bmi)}</span>
            <span style={{ color: '#d1d5db' }}>|</span>
            <span style={{ color: '#6b7280' }}>{height} cm · {weight} kg</span>
          </motion.div>
        )}
      </div>

      <style>{`
        @keyframes dtSpin  { to { transform: rotate(360deg); } }
        @keyframes dtBlob  { 0%,100%{transform:translate(0,0) scale(1)} 33%{transform:translate(18px,-12px) scale(1.03)} 66%{transform:translate(-12px,8px) scale(.97)} }
        @keyframes dtPulse { 0%,100%{opacity:.5;transform:scale(1)} 50%{opacity:1;transform:scale(1.3)} }
        @keyframes dtDash  { to { stroke-dashoffset: -1.5; } }
        @keyframes dtZonePulse { 0%,100%{transform:scale(1);opacity:.7} 50%{transform:scale(1.3);opacity:1} }
      `}</style>
    </div>
  );
}
