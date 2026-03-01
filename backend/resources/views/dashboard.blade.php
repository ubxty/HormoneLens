@extends('layouts.app')
@section('title','Simulation Dashboard — HormoneLens')
@section('heading','Predictive Metabolic Simulation')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════
   HormoneLens — Predictive Metabolic Simulation Dashboard
   Pastel gradient healthcare AI theme
   ═══════════════════════════════════════════════════════ */

/* ── Base gradient background ── */
.dash-bg {
    background: linear-gradient(135deg, rgba(95,111,255,0.06) 0%, rgba(194,77,255,0.06) 50%, rgba(255,110,199,0.06) 100%);
    min-height: 100%;
    position: relative;
    overflow: hidden;
}

/* ── Floating ambient particles ── */
.dash-particle {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    pointer-events: none;
    opacity: 0.12;
    will-change: transform;
}
.dash-particle-1 {
    width: 320px; height: 320px;
    background: linear-gradient(135deg, #5f6fff, #c24dff);
    top: -80px; right: -60px;
    animation: particleFloat 16s ease-in-out infinite;
}
.dash-particle-2 {
    width: 250px; height: 250px;
    background: linear-gradient(135deg, #c24dff, #ff6ec7);
    bottom: 10%; left: -40px;
    animation: particleFloat 20s ease-in-out 4s infinite;
}
.dash-particle-3 {
    width: 200px; height: 200px;
    background: linear-gradient(135deg, #ff6ec7, #5f6fff);
    top: 40%; right: 15%;
    animation: particleFloat 18s ease-in-out 8s infinite;
}
@keyframes particleFloat {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33%      { transform: translate(30px, -20px) scale(1.05); }
    66%      { transform: translate(-20px, 15px) scale(0.95); }
}

/* ── Glassmorphism Card ── */
.glass-card {
    background: rgba(255, 255, 255, 0.55);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.35);
    border-radius: 22px;
    box-shadow: 0 8px 32px rgba(95, 111, 255, 0.08);
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1),
                box-shadow 0.4s ease,
                border-color 0.4s ease;
    position: relative;
    overflow: hidden;
}
.glass-card::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 22px;
    padding: 1px;
    background: linear-gradient(135deg, rgba(95,111,255,0.2), rgba(194,77,255,0.15), rgba(255,110,199,0.1));
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.4s ease;
}
.glass-card:hover::before {
    opacity: 1;
}
.glass-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(95, 111, 255, 0.14), 0 0 20px rgba(194, 77, 255, 0.06);
}

/* ── Floating metric card ── */
.metric-card {
    animation: cardFloat 6s ease-in-out infinite;
}
.metric-card:nth-child(2) { animation-delay: 0.8s; }
.metric-card:nth-child(3) { animation-delay: 1.6s; }
.metric-card:nth-child(4) { animation-delay: 2.4s; }
.metric-card:nth-child(5) { animation-delay: 3.2s; }

@keyframes cardFloat {
    0%, 100% { transform: translateY(0); }
    50%      { transform: translateY(-6px); }
}

/* ── Entrance animations ── */
@keyframes fadeSlideUp {
    from { opacity: 0; transform: translateY(30px); }
    to   { opacity: 1; transform: translateY(0); }
}
.anim-enter {
    opacity: 0;
    transform: translateY(30px);
}
.anim-enter.anim-visible {
    animation: fadeSlideUp 0.7s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}
.anim-d1 { animation-delay: 0.1s !important; }
.anim-d2 { animation-delay: 0.2s !important; }
.anim-d3 { animation-delay: 0.3s !important; }
.anim-d4 { animation-delay: 0.4s !important; }
.anim-d5 { animation-delay: 0.5s !important; }
.anim-d6 { animation-delay: 0.6s !important; }
.anim-d7 { animation-delay: 0.7s !important; }
.anim-d8 { animation-delay: 0.8s !important; }

/* ── Dashboard right panel: override dash-bg overflow so sticky works ── */
.dash-bg { overflow: visible !important; }
.dash-particles-wrap { position:absolute;inset:0;overflow:clip;pointer-events:none;z-index:0; }

/* ── Twin body panel ── */
.dash-twin-panel { height:calc(100vh - 3.5rem); }

/* ── Body viz shared animations ── */
@keyframes dtBodyIn{from{opacity:0;transform:translateY(30px) scale(.78)}to{opacity:1;transform:translateY(0) scale(1)}}
@keyframes dtTagFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
@keyframes dtNodePulse{0%,100%{opacity:.3;box-shadow:0 0 0 0 rgba(91,33,182,.45)}50%{opacity:1;box-shadow:0 0 0 5px rgba(91,33,182,0)}}
@keyframes dtLineDash{to{stroke-dashoffset:-16}}
.dt-body-anim{animation:dtBodyIn 1.1s cubic-bezier(.4,0,.2,1) both}
.dt-hv-glow{position:absolute;border-radius:50%;filter:blur(26px);pointer-events:none;transform:translateX(-50%);transition:opacity .7s ease}
.dt-node{position:absolute;width:6px;height:6px;border-radius:50%;background:#5b21b6;animation:dtNodePulse 2s ease-in-out infinite;transform:translate(-50%,-50%)}
.dt-hv-tag{background:rgba(255,255,255,.93);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);border:1px solid rgba(139,92,246,.22);border-radius:8px;padding:.22rem .48rem;box-shadow:0 3px 10px rgba(139,92,246,.1);animation:dtTagFloat 3.5s ease-in-out infinite;width:70px}
.dt-conn-line{stroke-dasharray:5 3;animation:dtLineDash 1.5s linear infinite}

/* ── Progress bar animation ── */
.progress-track {
    height: 8px;
    border-radius: 999px;
    background: rgba(0, 0, 0, 0.06);
    overflow: hidden;
}
.progress-fill {
    height: 100%;
    border-radius: 999px;
    width: 0%;
    transition: width 1.5s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Progress glow levels */
.glow-green  { background: linear-gradient(90deg, #34d399, #10b981); box-shadow: 0 0 10px rgba(16,185,129,0.3); }
.glow-yellow { background: linear-gradient(90deg, #fbbf24, #f59e0b); box-shadow: 0 0 10px rgba(245,158,11,0.3); }
.glow-red    { background: linear-gradient(90deg, #f87171, #ef4444); box-shadow: 0 0 10px rgba(239,68,68,0.3); }

/* ── Circular progress ring ── */
.ring-container {
    position: relative;
    width: 120px;
    height: 120px;
}
.ring-svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}
.ring-bg {
    fill: none;
    stroke: rgba(0, 0, 0, 0.06);
    stroke-width: 8;
}
.ring-progress {
    fill: none;
    stroke-width: 8;
    stroke-linecap: round;
    stroke-dasharray: 314.16;
    stroke-dashoffset: 314.16;
    transition: stroke-dashoffset 2s cubic-bezier(0.4, 0, 0.2, 1);
}
.ring-label {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

/* ── Gradient text ── */
.gradient-text {
    background: linear-gradient(135deg, #5f6fff, #c24dff, #ff6ec7);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* ── Status pulse ── */
@keyframes statusPulse {
    0%, 100% { opacity: 0.5; transform: scale(1); }
    50%      { opacity: 1; transform: scale(1.3); }
}
.status-pulse {
    animation: statusPulse 2s ease-in-out infinite;
}

/* ── Count-up numbers ── */
.count-num {
    font-variant-numeric: tabular-nums;
}

/* ── Score icon ── */
.score-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    transition: box-shadow 0.3s ease, transform 0.3s ease;
}
.glass-card:hover .score-icon {
    transform: scale(1.08);
}

/* ── Chart container ── */
.chart-glass {
    background: rgba(255, 255, 255, 0.65);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 22px;
    box-shadow: 0 8px 32px rgba(95, 111, 255, 0.08);
}

/* ── Welcome banner gradient ── */
.welcome-banner {
    background: linear-gradient(135deg, #5f6fff, #c24dff, #ff6ec7);
    border-radius: 22px;
    position: relative;
    overflow: hidden;
}
.welcome-banner::after {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 80% 20%, rgba(255,255,255,0.15) 0%, transparent 60%);
    pointer-events: none;
}

/* ── Responsive fixes ── */
@media (max-width: 640px) {
    .ring-container { width: 100px; height: 100px; }
    .ring-svg .ring-bg,
    .ring-svg .ring-progress { stroke-width: 6; }
}
</style>
@endpush


@section('content')
<div id="twin-root" class="-m-4 sm:-m-6" style="min-height:calc(100vh - 56px)"></div>
@endsection

@push('scripts')
@vite('resources/js/dashboard-twin.jsx')
@endpush
