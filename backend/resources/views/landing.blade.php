<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HormoneLens AI — Predictive Metabolic Simulation Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: { extend: { colors: {
            brand: { 50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca',800:'#3730a3',900:'#312e81' }
        }}}
    }
    </script>
    <style>
        /* ═══ Global animation foundations ═══ */
        @keyframes float    { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }
        @keyframes fadeUp   { from{opacity:0;transform:translateY(30px)} to{opacity:1;transform:translateY(0)} }

        .float-anim       { animation: float 6s ease-in-out infinite; }
        .float-anim-delay { animation: float 6s ease-in-out 2s infinite; }

        .fade-up    { animation: fadeUp 0.8s ease-out both; }
        .fade-up-d1 { animation-delay: 0.1s;  }
        .fade-up-d2 { animation-delay: 0.25s; }
        .fade-up-d3 { animation-delay: 0.4s;  }
        .fade-up-d4 { animation-delay: 0.55s; }

        /* ═══ Glassmorphic navbar ═══ */
        @keyframes navEntrance {
            from { opacity:0; transform:translateY(-20px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .nav-glass {
            position: fixed; top:0; left:0; right:0; z-index:50;
            background: linear-gradient(
                to right,
                rgba(124,58,237,0.05),
                rgba(59,130,246,0.05)
            ), rgba(255,255,255,0.45);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255,255,255,0.2);
            box-shadow: 0 4px 30px rgba(124,58,237,0.08);
            opacity: 0;
            transform: translateY(-20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }
        .nav-glass.nav-visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* ═══ Logo gradient text ═══ */
        .logo-gradient {
            background: linear-gradient(90deg, #7C3AED, #A78BFA);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ═══ AI Engine pulse dot ═══ */
        @keyframes enginePulse {
            0%,100% { transform:scale(1);   opacity:0.6; box-shadow:0 0 0 0 rgba(16,185,129,0); }
            50%     { transform:scale(1.2); opacity:1;   box-shadow:0 0 8px 3px rgba(16,185,129,0.35); }
        }
        .engine-dot { animation: enginePulse 2s ease-in-out infinite; }

        /* ═══ Card entrance — JS-triggered ═══ */
        @keyframes fadeSlideUp {
            from { opacity:0; transform:translateY(30px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .card-entrance {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s cubic-bezier(0.4,0,0.2,1),
                        transform 0.8s cubic-bezier(0.4,0,0.2,1);
        }
        .card-entrance.card-visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* ═══ Alert pulse glow ═══ */
        @keyframes alertPulse {
            0%,100% { box-shadow:0 0 0 0 rgba(239,68,68,0); background-color:rgba(254,242,242,1); }
            50%     { box-shadow:0 0 18px 4px rgba(239,68,68,0.12); background-color:rgba(254,226,226,1); }
        }
        .alert-pulse { animation: alertPulse 2s ease-in-out infinite; }

        @keyframes alertIconPulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.1)} }
        .alert-icon-pulse { animation: alertIconPulse 2s ease-in-out infinite; }

        /* ═══ AI floating particles ═══ */
        @keyframes particleDrift {
            0%   { transform:translate(0,0) scale(1);     opacity:0.15; }
            50%  { opacity:0.28; }
            100% { transform:translate(280px,-380px) scale(1.2); opacity:0.15; }
        }
        .ai-particle {
            position:absolute; border-radius:50%; filter:blur(70px);
            pointer-events:none; will-change:transform,opacity;
        }
        .ai-particle-1 {
            width:300px; height:300px;
            background:linear-gradient(135deg,#818cf8 0%,#a78bfa 50%,#c084fc 100%);
            bottom:-80px; left:-50px;
            animation:particleDrift 14s ease-in-out infinite;
        }
        .ai-particle-2 {
            width:220px; height:220px;
            background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 50%,#d946ef 100%);
            bottom:20px; left:18%;
            animation:particleDrift 18s ease-in-out 3s infinite;
        }
        .ai-particle-3 {
            width:180px; height:180px;
            background:linear-gradient(135deg,#a5b4fc 0%,#c4b5fd 50%,#e879f9 100%);
            bottom:-30px; left:40%;
            animation:particleDrift 22s ease-in-out 7s infinite;
        }

        /* ═══ Count-up ═══ */
        .score-num { font-variant-numeric:tabular-nums; }

        /* ═══ Metabolic silhouette (background context) ═══ */
        .silhouette-bg {
            filter: blur(2px) drop-shadow(0 0 30px rgba(167,139,250,0.08));
            opacity: 0.10;
            z-index: 0;
        }

        /* ═══ CTA → Card glow beam ═══ */
        @keyframes beamPulse {
            0%, 100% { opacity: 0.3; filter: blur(1px); }
            50%      { opacity: 0.6; filter: blur(0.5px); }
        }
        .cta-beam {
            position: absolute;
            height: 2px;
            background: linear-gradient(90deg, #7c3aed, #6366f1, #3b82f6);
            box-shadow: 0 0 12px 2px rgba(99,102,241,0.2), 0 0 4px rgba(124,58,237,0.15);
            border-radius: 2px;
            animation: beamPulse 3s ease-in-out infinite;
            pointer-events: none;
            z-index: 5;
        }

        /* ═══ Simulation workflow cards ═══ */
        .sim-panel {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.55s ease-out, transform 0.55s ease-out,
                        box-shadow 0.25s ease, border-color 0.25s ease;
        }
        .sim-panel.sim-visible {
            opacity: 1;
            transform: translateY(0);
        }
        .sim-panel:hover {
            transform: translateY(-6px);
            box-shadow: 0 10px 30px rgba(124,58,237,0.12);
            border-color: rgba(124,58,237,0.3);
        }

        /* ═══ Flow connector line ═══ */
        @keyframes flowLine {
            0%   { background-position: -100% 0; }
            100% { background-position: 200% 0; }
        }
        .flow-connector {
            height: 1px;
            background: linear-gradient(90deg,
                transparent 0%,
                rgba(124,58,237,0.35) 40%,
                rgba(99,102,241,0.35) 60%,
                transparent 100%);
            background-size: 200% 100%;
            animation: flowLine 4s linear infinite;
            opacity: 0.5;
            border-radius: 1px;
        }

        /* ═══ Status dot (badge + footer) ═══ */
        @keyframes statusPulse {
            0%,100% { opacity: 0.5; transform: scale(1); }
            50%     { opacity: 1;   transform: scale(1.25); }
        }
        .status-dot        { animation: statusPulse 2s ease-in-out infinite; }
        .status-dot-delay  { animation: statusPulse 2s ease-in-out 0.7s infinite; }
        .status-dot-delay2 { animation: statusPulse 2s ease-in-out 1.4s infinite; }

        /* ═══ AI Model Running badge (glass) ═══ */
        .badge-glass {
            background: rgba(255,255,255,0.5);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            border: 1px solid rgba(255,255,255,0.35);
        }

        /* ═══ Hero badge pulsing dot — target only the dot inside badge */
        .dot-pulse {
            width: 8px;
            height: 8px;
            background: #22c55e;
            border-radius: 9999px;
            position: relative;
            display: inline-block;
            flex-shrink: 0;
        }
        .dot-pulse::after {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 9999px;
            background: #22c55e;
            animation: pulseDot 1.6s infinite;
        }
        @keyframes pulseDot {
            0% {
                transform: scale(1);
                opacity: 0.7;
            }
            70% {
                transform: scale(2.5);
                opacity: 0;
            }
            100% {
                transform: scale(1);
                opacity: 0;
            }
        }

        /* ═══ Disease cards — scroll entrance ═══ */
        .disease-card {
            opacity: 0;
            transform: translateY(40px) scale(0.97);
            transition:
                opacity 0.6s ease-out,
                transform 0.6s ease-out,
                box-shadow 0.25s ease,
                border-color 0.25s ease;
        }
        .disease-card.dc-visible {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
        .disease-card:hover {
            transform: translateY(-6px) scale(1);
            box-shadow: 0 12px 40px rgba(124,58,237,0.12);
            border-color: rgba(124,58,237,0.3) !important;
        }
        .disease-card:hover .dc-icon {
            transform: scale(1.05);
        }
        .dc-icon {
            display: inline-block;
            transition: transform 0.25s ease;
        }

        /* ═══ Expandable fields ═══ */
        .extra-fields {
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: max-height 0.4s ease, opacity 0.4s ease;
        }
        .extra-fields.ef-open {
            max-height: 300px;
            opacity: 1;
        }
        .expand-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-top: 8px;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: 600;
            color: #7c3aed;
            background: rgba(124,58,237,0.06);
            border: 1px solid rgba(124,58,237,0.15);
            border-radius: 9999px;
            cursor: pointer;
            transition: background 0.2s ease, border-color 0.2s ease;
        }
        .expand-btn:hover {
            background: rgba(124,58,237,0.12);
            border-color: rgba(124,58,237,0.3);
        }

        /* ═══ Onboarding banner ═══ */
        .onboard-banner {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }
        .onboard-banner.ob-visible {
            opacity: 1;
            transform: translateY(0);
        }
        .activation-frame {
            position: relative;
            display: inline-block;
        }
        @keyframes moveLight {
            0%   { background-position: 0% 50%; }
            100% { background-position: 200% 50%; }
        }
        .activation-frame::before {
            content: '';
            position: absolute;
            top: -10px;
            left: -10px;
            width: calc(100% + 20px);
            height: calc(100% + 20px);
            border-radius: 16px;
            border: 2px solid rgba(124,58,237,0.2);
            background: linear-gradient(90deg, transparent, rgba(124,58,237,0.5), transparent);
            background-size: 200% 200%;
            animation: moveLight 3s linear infinite;
            pointer-events: none;
            z-index: 0;
        }
        @keyframes obGlowPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(124,58,237,0.4); }
            50%      { box-shadow: 0 0 0 10px rgba(124,58,237,0); }
        }
        .ob-cta-btn {
            position: relative;
            z-index: 1;
            display: block;
            background: linear-gradient(135deg, #7C3AED, #A78BFA);
            border-radius: 14px;
            padding: 16px 40px;
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            animation: obGlowPulse 2s ease-in-out infinite;
        }
        .ob-cta-btn:hover {
            transform: scale(1.03);
            box-shadow: 0 0 20px rgba(124,58,237,0.25);
        }
        @keyframes obDotPulse {
            0%, 100% { opacity: 0.5; transform: scale(1); }
            50%      { opacity: 1;   transform: scale(1.3); }
        }
        .ob-status-dot {
            display: inline-block;
            width: 7px; height: 7px;
            border-radius: 50%;
            background: #10b981;
            animation: obDotPulse 2s ease-in-out infinite;
            flex-shrink: 0;
        }
        .ob-progress-track {
            width: 100%;
            height: 3px;
            border-radius: 2px;
            background: rgba(124,58,237,0.1);
            overflow: hidden;
        }
        @keyframes obProgressFill {
            0%   { width: 0%; }
            100% { width: 100%; }
        }
        .ob-progress-fill {
            height: 100%;
            border-radius: 2px;
            background: linear-gradient(90deg, #7C3AED, #A78BFA);
            width: 0%;
            animation: obProgressFill 4s linear infinite;
        }

        /* ═══ Simulation loop (How It Works) ═══ */
        .sim-loop-wrap {
            opacity: 0;
            transform: scale(0.9);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }
        .sim-loop-wrap.sl-visible {
            opacity: 1;
            transform: scale(1);
        }
        .sim-loop-container {
            position: relative;
            width: 540px;
            height: 540px;
            margin: 0 auto;
        }
        /* Cardinal positions — cards centered on a 190px-radius circle (center 270,270) */
        .sim-step-node {
            position: absolute;
            width: 128px;
            text-align: center;
            background: #fff;
            border-radius: 14px;
            border: 1px solid rgba(124,58,237,0.12);
            padding: 14px 10px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
            z-index: 2;
        }
        .sim-step-node:hover {
            transform: scale(1.08);
            box-shadow: 0 6px 24px rgba(124,58,237,0.15);
            border-color: rgba(124,58,237,0.4);
        }
        .sim-step-top    { top:  12px;         left: 206px; }
        .sim-step-right  { top:  206px;        right: 12px; }
        .sim-step-bottom { bottom: 12px;       left: 206px; }
        .sim-step-left   { top:  206px;        left:  12px; }
        .sim-center-node {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            z-index: 3;
            width: 130px;
        }
        /* Mobile fallback */
        @media (max-width: 600px) {
            .sim-loop-container { width: 100%; height: auto; min-height: 0; }
            .sim-step-node  { position: static; width: 100%; margin-bottom: 12px; }
            .sim-step-top, .sim-step-right, .sim-step-bottom, .sim-step-left { position: static; }
            .sim-center-node { position: static; transform: none; width: 100%; margin-bottom: 12px; }
            .sim-svg-layer  { display: none; }
        }
        @keyframes obParticleDrift {
            0%   { transform: translateX(0)   translateY(0)   scale(1);   opacity: 0.12; }
            50%  { opacity: 0.18; }
            100% { transform: translateX(160px) translateY(-40px) scale(1.1); opacity: 0.12; }
        }
        .ob-particle {
            position: absolute;
            border-radius: 50%;
            filter: blur(55px);
            pointer-events: none;
        }
        .ob-particle-1 {
            width: 260px; height: 260px;
            background: linear-gradient(135deg, #818cf8, #a78bfa);
            top: -60px; left: -40px;
            animation: obParticleDrift 8s ease-in-out infinite;
        }
        .ob-particle-2 {
            width: 180px; height: 180px;
            background: linear-gradient(135deg, #6366f1, #c084fc);
            bottom: -40px; right: 5%;
            animation: obParticleDrift 11s ease-in-out 2s infinite reverse;
        }
    </style>
</head>
<body class="bg-white text-gray-800 antialiased">

{{-- ═══════════ NAVBAR ═══════════ --}}
<nav id="mainNav" class="nav-glass">
    <div class="max-w-7xl mx-auto flex items-center justify-between px-6 h-16">
        <a href="/" class="flex items-center gap-2.5 text-xl font-bold">
            <span class="text-brand-600">🔬</span>
            <span class="flex flex-col leading-tight">
                <span class="flex items-center gap-1.5">
                    <span class="logo-gradient font-extrabold tracking-tight">HormoneLens AI</span>
                    <span class="engine-dot inline-block w-2 h-2 rounded-full bg-emerald-400"></span>
                </span>
                <span class="text-[9px] font-medium tracking-widest text-gray-400 uppercase">Predictive Hormone Intelligence Engine</span>
            </span>
        </a>
        <div class="hidden sm:flex items-center gap-6 text-sm font-medium text-gray-500">
            <a href="#features" class="hover:text-violet-700 transition-colors duration-200">Features</a>
            <a href="#conditions" class="hover:text-violet-700 transition-colors duration-200">Health Conditions</a>
            <a href="#how" class="hover:text-violet-700 transition-colors duration-200">How It Works</a>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('login') }}"
               class="text-sm font-medium text-gray-500 hover:text-violet-700 transition-colors duration-200">Log in</a>
            <a href="{{ route('register') }}"
               class="px-4 py-2 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white text-sm font-semibold rounded-lg transition-all shadow-md shadow-violet-200/50">
                Get Started</a>
        </div>
    </div>
</nav>

{{-- ═══════════ HERO ═══════════ --}}
<section class="relative pb-20 lg:pb-24 overflow-hidden bg-gradient-to-b from-white via-brand-50/30 to-white" style="padding-top:20px; margin-top:64px;">
    {{-- AI floating particles (background) --}}
    <div class="ai-particle ai-particle-1"></div>
    <div class="ai-particle ai-particle-2"></div>

    {{-- Gradient orbs --}}
    <div class="absolute top-20 -left-40 w-96 h-96 bg-brand-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 float-anim"></div>
    <div class="absolute top-40 -right-40 w-96 h-96 bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 float-anim-delay"></div>



    <div class="relative max-w-7xl mx-auto px-6 grid lg:grid-cols-2 gap-12 items-center">
        {{-- Left: copy --}}
        <div class="fade-up">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-brand-50 text-brand-700 mb-6 border border-brand-100">
                🧬 AI-Powered Metabolic Simulation Engine
            </span>
            <h1 class="text-4xl md:text-5xl lg:text-6xl leading-tight font-extrabold tracking-tight">
                Simulate the <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-600 via-purple-600 to-fuchsia-500">Hormonal Impact</span>
                of Your Lifestyle Before It Affects Your Health
            </h1>
            <p class="mt-5 text-lg text-gray-500 max-w-xl leading-relaxed">
                Run AI-powered simulations to predict how sleep, diet, stress, and physical activity influence your risk of PCOS, Type 2 Diabetes, insulin resistance, and metabolic imbalance.
            </p>
            <p class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 bg-purple-50 border border-purple-100 rounded-lg text-xs font-medium text-purple-700">
                <span class="w-1.5 h-1.5 rounded-full bg-purple-500 animate-pulse"></span>
                Designed for PCOS, Thyroid Dysfunction, Type 2 Diabetes &amp; Insulin Resistance Monitoring
            </p>
            <div class="mt-8 flex flex-wrap gap-4">
                <a href="{{ route('register') }}"
                   class="group px-6 py-3.5 bg-gradient-to-r from-brand-600 to-purple-600 hover:from-brand-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all shadow-lg shadow-brand-200/50 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 transition-transform group-hover:rotate-12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/></svg>
                    Run My Hormone Simulation
                </a>
                <a href="#how"
                   class="px-6 py-3.5 bg-white border border-gray-200 hover:border-brand-300 hover:bg-brand-50 text-gray-700 font-semibold rounded-xl transition-all text-sm flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4 text-brand-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM9.555 7.168A1 1 0 0 0 8 8v4a1 1 0 0 0 1.555.832l3-2a1 1 0 0 0 0-1.664l-3-2Z" clip-rule="evenodd"/></svg>
                    Explore How It Works
                </a>
            </div>

        </div>

        {{-- Right: AI Simulation dashboard card --}}
        <div id="heroCard" class="relative card-entrance" style="z-index:10;">
            {{-- Metabolic silhouette behind card --}}
            <div class="hidden lg:block absolute -inset-12 pointer-events-none silhouette-bg" aria-hidden="true">
                <svg class="w-full h-full" viewBox="0 0 320 500" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="160" cy="48" r="32" stroke="url(#bodyGradBg)" stroke-width="1.5" fill="none"/>
                    <line x1="160" y1="80" x2="160" y2="105" stroke="url(#bodyGradBg)" stroke-width="1.5"/>
                    <path d="M160 105 Q 160 115, 100 130" stroke="url(#bodyGradBg)" stroke-width="1.5" fill="none"/>
                    <path d="M160 105 Q 160 115, 220 130" stroke="url(#bodyGradBg)" stroke-width="1.5" fill="none"/>
                    <path d="M100 130 Q 85 180, 75 240" stroke="url(#bodyGradBg)" stroke-width="1.5" fill="none"/>
                    <path d="M220 130 Q 235 180, 245 240" stroke="url(#bodyGradBg)" stroke-width="1.5" fill="none"/>
                    <path d="M100 130 L 110 260 Q 115 280, 130 290" stroke="url(#bodyGradBg)" stroke-width="1.5" fill="none"/>
                    <path d="M220 130 L 210 260 Q 205 280, 190 290" stroke="url(#bodyGradBg)" stroke-width="1.5" fill="none"/>
                    <path d="M130 290 Q 160 305, 190 290" stroke="url(#bodyGradBg)" stroke-width="1.5" fill="none"/>
                    <path d="M140 295 Q 135 360, 125 440" stroke="url(#bodyGradBg)" stroke-width="1.5" fill="none"/>
                    <path d="M180 295 Q 185 360, 195 440" stroke="url(#bodyGradBg)" stroke-width="1.5" fill="none"/>
                    <path d="M125 440 Q 120 455, 108 460" stroke="url(#bodyGradBg)" stroke-width="1.5" fill="none"/>
                    <path d="M195 440 Q 200 455, 212 460" stroke="url(#bodyGradBg)" stroke-width="1.5" fill="none"/>
                    <defs>
                        <linearGradient id="bodyGradBg" x1="80" y1="0" x2="240" y2="460" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#c4b5fd"/>
                            <stop offset="50%" stop-color="#a78bfa"/>
                            <stop offset="100%" stop-color="#ddd6fe"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>

            <div class="relative bg-white rounded-2xl shadow-[0_25px_60px_-12px_rgba(0,0,0,0.12)] border border-gray-100/80 p-6 max-w-md mx-auto ring-1 ring-black/[0.03]" style="z-index:12;">
                {{-- Top label --}}
                <div class="flex items-center gap-2 mb-5 pb-3 border-b border-gray-100">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <p class="text-[11px] font-medium text-gray-400 tracking-wide uppercase">Lifestyle Simulation Output <span class="text-gray-300">•</span> Based on Profile Inputs</p>
                </div>

                {{-- Overall Risk Score --}}
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wider mb-1">Overall Risk Score</p>
                        <p class="text-5xl font-extrabold text-amber-500 score-num" data-countup="68.4" data-decimals="1">0.0</p>
                    </div>
                    <span class="px-3.5 py-1.5 bg-amber-100 text-amber-700 text-xs font-bold rounded-full tracking-wide">⚠ MEDIUM</span>
                </div>

                {{-- Score cards --}}
                <div class="grid grid-cols-3 gap-3 mb-5">
                    <div class="text-center p-3 bg-indigo-50/80 rounded-xl border border-indigo-100/50">
                        <p class="text-xl font-bold text-indigo-600 score-num" data-countup="82.5" data-decimals="1">0.0</p>
                        <p class="text-[10px] text-gray-500 mt-0.5 font-medium">Metabolic</p>
                    </div>
                    <div class="text-center p-3 bg-purple-50/80 rounded-xl border border-purple-100/50">
                        <p class="text-xl font-bold text-purple-600 score-num" data-countup="55.0" data-decimals="1">0.0</p>
                        <p class="text-[10px] text-gray-500 mt-0.5 font-medium">Insulin Res.</p>
                    </div>
                    <div class="text-center p-3 bg-blue-50/80 rounded-xl border border-blue-100/50">
                        <p class="text-xl font-bold text-blue-600 score-num" data-countup="70.0" data-decimals="1">0.0</p>
                        <p class="text-[10px] text-gray-500 mt-0.5 font-medium">Sleep Score</p>
                    </div>
                </div>

                {{-- Critical alert with pulse — Diabetes-focused --}}
                <div class="alert-pulse flex items-start gap-3 p-3.5 rounded-xl text-sm border border-red-100">
                    <span class="alert-icon-pulse text-lg leading-none mt-0.5">⛔</span>
                    <div>
                        <p class="font-semibold text-red-700">Diabetes Risk Elevated — Blood Sugar: 210 mg/dL</p>
                        <p class="text-xs text-red-500 mt-0.5">Immediate Lifestyle Intervention Recommended</p>
                    </div>
                </div>
            </div>

            {{-- Top-right: Live Simulation badge --}}
            <div class="absolute -top-3 -right-3 bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-xs font-bold px-3.5 py-1.5 rounded-full shadow-lg float-anim flex items-center gap-1.5" style="z-index:14;">
                <span class="flex h-1.5 w-1.5 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-white"></span>
                </span>
                Live Simulation
            </div>

            {{-- Bottom-left: AI Model Running badge --}}
            <div class="badge-glass absolute -bottom-2.5 -left-2.5 flex items-center gap-1.5 px-3 py-1.5 rounded-full shadow-md text-xs font-semibold text-gray-600" style="z-index:14;">
                <span class="dot-pulse"></span>
                AI Model Running
            </div>
        </div>

        {{-- CTA → Card glow beam (lg+ only) --}}
        <div class="hidden lg:block cta-beam" id="ctaBeam" style="top:50%; left:38%; width:14%;"></div>
    </div>
</section>

{{-- ═══════════ Init Script ═══════════ --}}
<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ── Navbar entrance (0ms) ── */
    requestAnimationFrame(() => {
        document.getElementById('mainNav').classList.add('nav-visible');
    });

    /* ── Card entrance (200ms delay) ── */
    setTimeout(() => {
        const card = document.getElementById('heroCard');
        if (card) card.classList.add('card-visible');
    }, 200);

    /* ── Simulation workflow: staggered entrance on scroll ── */
    const simPanels = document.querySelectorAll('.sim-panel');
    const simObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const el    = entry.target;
            const delay = parseInt(el.dataset.simDelay || '0', 10);
            setTimeout(() => el.classList.add('sim-visible'), delay);
            simObserver.unobserve(el);
        });
    }, { threshold: 0.15 });
    simPanels.forEach(el => simObserver.observe(el));

    /* ── Count-up on viewport entry ── */
    const els = document.querySelectorAll('[data-countup]');
    const duration = 1500;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const el = entry.target;
            observer.unobserve(el);

            const target   = parseFloat(el.dataset.countup);
            const decimals = parseInt(el.dataset.decimals || '0', 10);
            const start    = performance.now();

            function tick(now) {
                const elapsed  = now - start;
                const progress = Math.min(elapsed / duration, 1);
                const eased    = 1 - Math.pow(1 - progress, 3);
                el.textContent = (eased * target).toFixed(decimals);
                if (progress < 1) requestAnimationFrame(tick);
            }
            requestAnimationFrame(tick);
        });
    }, { threshold: 0.3 });

    els.forEach(el => observer.observe(el));

    /* ── Disease card scroll entrance ── */
    const diseaseCards = document.querySelectorAll('.disease-card');
    const dcObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const el    = entry.target;
            const delay = parseInt(el.dataset.dcDelay || '0', 10);
            setTimeout(() => el.classList.add('dc-visible'), delay);
            dcObserver.unobserve(el);
        });
    }, { threshold: 0.12 });
    diseaseCards.forEach(el => dcObserver.observe(el));

    /* ── Expand / collapse extra fields ── */
    document.querySelectorAll('.expand-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const extra = btn.previousElementSibling;
            const open  = extra.classList.toggle('ef-open');
            btn.textContent = open ? 'Show Less ▲' : btn.dataset.label;
        });
    });

    /* ── Onboarding banner entrance ── */
    const obBanner = document.getElementById('onboardBanner');
    if (obBanner) {
        const obObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('ob-visible');
                obObserver.unobserve(entry.target);
            });
        }, { threshold: 0.15 });
        obObserver.observe(obBanner);
    }

    /* ── Sim loop circle entrance ── */
    const simLoopWrap = document.getElementById('simLoopWrap');
    if (simLoopWrap) {
        const slObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('sl-visible');
                slObserver.unobserve(entry.target);
            });
        }, { threshold: 0.15 });
        slObserver.observe(simLoopWrap);
    }
});
</script>

{{-- ═══════════ CAPABILITIES ═══════════ --}}
<section id="features" class="py-20 bg-gray-50 overflow-hidden">
    <div class="max-w-7xl mx-auto px-6">

        {{-- Section header --}}
        <div class="text-center max-w-2xl mx-auto mb-14 fade-up">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-violet-50 text-violet-700 mb-4 border border-violet-100">
                🧠 AI-Powered Health Intelligence
            </span>
            <h2 class="text-3xl font-bold text-gray-900">Core Simulation Capabilities of HormoneLens</h2>
            <p class="mt-3 text-gray-500">Advanced AI-driven metabolic modeling designed to predict lifestyle-induced hormonal and diabetes risk outcomes.</p>
        </div>

        {{-- Capability cards --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">

            <div class="sim-panel bg-white rounded-xl border border-gray-100 p-6" data-sim-delay="0">
                <div class="w-10 h-10 flex items-center justify-center rounded-xl text-xl mb-4" style="background:rgba(124,58,237,0.08);">🛎️</div>
                <h3 class="font-semibold text-gray-800 mb-2">Lifestyle Data Integration</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Aggregates sleep patterns, dietary inputs, physical activity, and stress indicators for personalized simulation modeling.</p>
            </div>

            <div class="sim-panel bg-white rounded-xl border border-gray-100 p-6" data-sim-delay="100">
                <div class="w-10 h-10 flex items-center justify-center rounded-xl text-xl mb-4" style="background:rgba(124,58,237,0.08);">🧬</div>
                <h3 class="font-semibold text-gray-800 mb-2">Metabolic Digital Twin</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Constructs a real-time physiological model of insulin sensitivity and glucose metabolism.</p>
            </div>

            <div class="sim-panel bg-white rounded-xl border border-gray-100 p-6" data-sim-delay="200">
                <div class="w-10 h-10 flex items-center justify-center rounded-xl text-xl mb-4" style="background:rgba(124,58,237,0.08);">📈</div>
                <h3 class="font-semibold text-gray-800 mb-2">Behavioral Impact Simulation</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Evaluates how daily lifestyle changes influence endocrine and metabolic health parameters.</p>
            </div>

            <div class="sim-panel bg-white rounded-xl border border-gray-100 p-6" data-sim-delay="300">
                <div class="w-10 h-10 flex items-center justify-center rounded-xl text-xl mb-4" style="background:rgba(124,58,237,0.08);">🔬</div>
                <h3 class="font-semibold text-gray-800 mb-2">PCOS &amp; Diabetes Risk Analysis</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Forecasts insulin resistance progression and Type 2 Diabetes probability through hormonal response mapping.</p>
            </div>

            <div class="sim-panel bg-white rounded-xl border border-gray-100 p-6" data-sim-delay="400">
                <div class="w-10 h-10 flex items-center justify-center rounded-xl text-xl mb-4" style="background:rgba(124,58,237,0.08);">🧠</div>
                <h3 class="font-semibold text-gray-800 mb-2">AI Risk Assessment Engine</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Translates simulated metabolic outcomes into clinically relevant health risk levels.</p>
            </div>

            <div class="sim-panel bg-white rounded-xl border border-gray-100 p-6" data-sim-delay="500">
                <div class="w-10 h-10 flex items-center justify-center rounded-xl text-xl mb-4" style="background:rgba(124,58,237,0.08);">🔔</div>
                <h3 class="font-semibold text-gray-800 mb-2">Adaptive Health Alerts</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Delivers predictive alerts when simulated health indicators enter elevated risk zones.</p>
            </div>

        </div>
    </div>
</section>

{{-- ═══════════ ONBOARDING BANNER ═══════════ --}}
<div class="max-w-7xl mx-auto px-6">
    <div id="onboardBanner" class="onboard-banner relative overflow-hidden rounded-[20px] border px-10 py-10 sm:px-16 sm:py-12 my-16"
         style="background:linear-gradient(to right,rgba(124,58,237,0.08),rgba(59,130,246,0.08)); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); border-color:rgba(255,255,255,0.25);">

        {{-- Floating particles --}}
        <div class="ob-particle ob-particle-1"></div>
        <div class="ob-particle ob-particle-2"></div>

        {{-- Content --}}
        <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-8">

            {{-- Left: copy --}}
            <div class="flex-1 text-center lg:text-left">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-violet-50 text-violet-700 mb-4 border border-violet-100">
                    🧬 AI Hormone Simulation
                </span>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3 leading-snug">
                    Activate Your Personalized<br class="hidden sm:block"> Hormone Simulation
                </h2>
                <p class="text-gray-500 text-sm leading-relaxed max-w-lg">
                    Create your HormoneLens account to simulate how lifestyle patterns impact your risk of PCOS, Insulin Resistance, and Type 2 Diabetes.
                </p>
            </div>

            {{-- Right: CTA --}}
            <div class="flex flex-col items-center gap-3 flex-shrink-0">

                {{-- Activation frame wrapper --}}
                <div class="activation-frame">
                    <a href="{{ route('register') }}" class="ob-cta-btn">
                        Create Simulation Profile
                    </a>
                </div>

                {{-- Status chip --}}
                <div class="flex items-center gap-1.5">
                    <span class="ob-status-dot"></span>
                    <span class="text-xs font-medium text-gray-500">Create Your Personalized Simulation</span>
                </div>

                {{-- Micro progress bar --}}
                <div class="ob-progress-track" style="min-width:180px;">
                    <div class="ob-progress-fill"></div>
                </div>

            </div>

        </div>
    </div>
</div>

{{-- ═══════════ DISEASES ═══════════ --}}
<section id="conditions" class="py-20">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-14 fade-up">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-violet-50 text-violet-700 mb-4 border border-violet-100">
                🔬 Clinical Risk Parameter Mapping
            </span>
            <h2 class="text-3xl font-bold">Dynamic Health Condition Tracking</h2>
            <p class="mt-3 text-gray-500">Extensible to any number of conditions — each with custom fields, validation rules, and risk scoring.</p>
        </div>

        @php
        $clinicalData = [
            'Type-2 Diabetes' => [
                'summary' => ['Average Blood Sugar (mg/dL)', 'Family History of Diabetes', 'Frequent Urination', 'Excessive Thirst'],
                'extra'   => ['HbA1c Level', 'Fasting Plasma Glucose', 'Postprandial Glucose', 'BMI', 'Insulin Sensitivity Index', 'Daily Carbohydrate Intake', 'Waist–Hip Ratio', 'Blood Pressure', 'Lipid Profile', 'Family History'],
            ],
            'PCOD / PCOS' => [
                'summary' => ['Menstrual Cycle Regularity', 'Average Cycle Length (days)', 'Excess Facial/Body Hair (Hirsutism)', 'Acne / Oily Skin'],
                'extra'   => ['Menstrual Irregularity', 'Hirsutism Score', 'Serum Testosterone', 'LH/FSH Ratio', 'Ovulation Frequency', 'Weight Gain Pattern', 'Insulin Resistance', 'Acne Severity', 'Sleep Duration', 'Hair Loss'],
            ],
            'Thyroid Disorders' => [
                'summary' => ['TSH Level (mIU/L)', 'T4 Level (µg/dL)', 'Type of Thyroid Condition', 'Currently on Thyroid Medication'],
                'extra'   => ['TSH Level', 'Free T3', 'Free T4', 'Thyroid Antibodies', 'Medication Status', 'Fatigue Level', 'Cold Sensitivity', 'Heart Rate', 'Weight Fluctuation', 'Metabolic Rate'],
            ],
            'Metabolic Syndrome' => [
                'summary' => ['Waist Circumference (cm)', 'Fasting Blood Sugar (mg/dL)', 'Systolic Blood Pressure (mmHg)', 'Diastolic Blood Pressure (mmHg)'],
                'extra'   => ['Waist Circumference', 'HDL Cholesterol', 'LDL Cholesterol', 'Triglycerides', 'Blood Pressure', 'Fasting Glucose', 'Insulin Level', 'BMI', 'Sleep Duration', 'Dietary Fat Intake'],
            ],
        ];
        @endphp

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach(\App\Models\Disease::active()->ordered()->with('fields')->get() as $i => $d)
            @php
                $key     = $d->name;
                $clinical = $clinicalData[$key] ?? null;
                $summary  = $clinical ? $clinical['summary'] : $d->fields->take(4)->pluck('label')->toArray();
                $extra    = $clinical ? $clinical['extra']   : [];
                $delay    = $loop->index * 100;
                $btnLabel = '+' . count($extra) . ' more ▼';
            @endphp
            <div class="disease-card bg-white rounded-xl border border-gray-100 p-6 text-center" data-dc-delay="{{ $delay }}">
                <div class="dc-icon text-4xl mb-3">{{ $d->icon }}</div>
                <h3 class="font-semibold text-gray-800 mb-1">{{ $d->name }}</h3>
                <p class="text-xs text-gray-400 mb-3">{{ $d->fields->count() }} tracked fields</p>

                {{-- Summary fields (always visible) --}}
                <div class="flex flex-wrap justify-center gap-1 mb-1">
                    @foreach($summary as $label)
                    <span class="px-2 py-0.5 bg-gray-100 text-[10px] text-gray-500 rounded-full">{{ $label }}</span>
                    @endforeach
                </div>

                @if(count($extra) > 0)
                {{-- Extra fields (expandable) --}}
                <div class="extra-fields">
                    <div class="flex flex-wrap justify-center gap-1 pt-2">
                        @foreach($extra as $label)
                        <span class="px-2 py-0.5 bg-violet-50 text-[10px] text-violet-600 rounded-full border border-violet-100">{{ $label }}</span>
                        @endforeach
                    </div>
                </div>
                <button class="expand-btn" data-label="{{ $btnLabel }}">{{ $btnLabel }}</button>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════ HOW IT WORKS ═══════════ --}}
<section id="how" class="py-20 bg-gray-50">
    <div class="max-w-5xl mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-10 fade-up">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-violet-50 text-violet-700 mb-4 border border-violet-100">
                ♻️ Continuous AI Simulation Cycle
            </span>
            <h2 class="text-3xl font-bold">How It Works</h2>
            <p class="mt-3 text-gray-500">See Your Health Before You Live It!</p>
        </div>

        <div id="simLoopWrap" class="sim-loop-wrap">

            {{-- Desktop circular layout --}}
            <div class="sim-loop-container hidden sm:block">

                {{-- SVG ring + animated dot --}}
                <svg class="sim-svg-layer" style="position:absolute;inset:0;width:100%;height:100%;overflow:visible;z-index:1;" viewBox="0 0 540 540" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="ringGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%"   stop-color="#7C3AED" stop-opacity="0.2"/>
                            <stop offset="50%"  stop-color="#A78BFA" stop-opacity="0.3"/>
                            <stop offset="100%" stop-color="#6366f1" stop-opacity="0.2"/>
                        </linearGradient>
                        <filter id="dotGlow">
                            <feGaussianBlur stdDeviation="3" result="blur"/>
                            <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
                        </filter>
                    </defs>

                    {{-- Faint circular ring --}}
                    <circle cx="270" cy="270" r="190"
                        fill="none"
                        stroke="url(#ringGrad)"
                        stroke-width="1.5"
                        stroke-dasharray="8 6"/>

                    {{-- Animated glowing dot --}}
                    <path id="simLoopPath" d="M 270,80 A 190,190 0 0,1 460,270 A 190,190 0 0,1 270,460 A 190,190 0 0,1 80,270 A 190,190 0 0,1 270,80 Z" fill="none"/>
                    <circle r="7" fill="#7C3AED" filter="url(#dotGlow)" opacity="0.9">
                        <animateMotion dur="5s" repeatCount="indefinite">
                            <mpath href="#simLoopPath"/>
                        </animateMotion>
                    </circle>
                    {{-- Soft trail dot --}}
                    <circle r="4" fill="#A78BFA" opacity="0.45">
                        <animateMotion dur="5s" repeatCount="indefinite" begin="0.15s">
                            <mpath href="#simLoopPath"/>
                        </animateMotion>
                    </circle>
                </svg>

                {{-- Top: Create Profile --}}
                <div class="sim-step-node sim-step-top">
                    <div class="w-9 h-9 mx-auto flex items-center justify-center rounded-xl bg-violet-50 text-violet-700 font-extrabold text-sm mb-2">01</div>
                    <h3 class="font-semibold text-gray-800 text-xs leading-snug mb-1">Create Profile</h3>
                    <p class="text-[10px] text-gray-400 leading-relaxed">Weight, sleep, stress &amp; activity.</p>
                </div>

                {{-- Right: Add Disease Data --}}
                <div class="sim-step-node sim-step-right">
                    <div class="w-9 h-9 mx-auto flex items-center justify-center rounded-xl bg-purple-50 text-purple-600 font-extrabold text-sm mb-2">02</div>
                    <h3 class="font-semibold text-gray-800 text-xs leading-snug mb-1">Add Disease Data</h3>
                    <p class="text-[10px] text-gray-400 leading-relaxed">Blood sugar, hormones &amp; symptoms.</p>
                </div>

                {{-- Bottom: Generate Digital Twin --}}
                <div class="sim-step-node sim-step-bottom">
                    <div class="w-9 h-9 mx-auto flex items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 font-extrabold text-sm mb-2">03</div>
                    <h3 class="font-semibold text-gray-800 text-xs leading-snug mb-1">Generate Digital Twin</h3>
                    <p class="text-[10px] text-gray-400 leading-relaxed">6 health scores with risk category.</p>
                </div>

                {{-- Left: Simulate Lifestyle Impact --}}
                <div class="sim-step-node sim-step-left">
                    <div class="w-9 h-9 mx-auto flex items-center justify-center rounded-xl bg-amber-50 text-amber-600 font-extrabold text-sm mb-2">04</div>
                    <h3 class="font-semibold text-gray-800 text-xs leading-snug mb-1">Simulate Lifestyle Impact</h3>
                    <p class="text-[10px] text-gray-400 leading-relaxed">What-if food, sleep &amp; stress scenarios.</p>
                </div>

                {{-- Center: Engine label --}}
                <div class="sim-center-node">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-2" style="background:linear-gradient(135deg,rgba(124,58,237,0.12),rgba(99,102,241,0.1));border:1.5px solid rgba(124,58,237,0.2);">
                        <span class="text-2xl">🧬</span>
                    </div>
                    <p class="text-[10px] font-bold text-violet-700 uppercase tracking-widest leading-tight">Hormone<br>Simulation<br>Engine</p>
                </div>

            </div>

            {{-- Mobile fallback: vertical list --}}
            <div class="sm:hidden flex flex-col gap-4">
                @php
                $mSteps = [
                    ['num'=>'01','title'=>'Create Profile',           'desc'=>'Weight, sleep, stress & activity.','color'=>'violet'],
                    ['num'=>'02','title'=>'Add Disease Data',         'desc'=>'Blood sugar, hormones & symptoms.','color'=>'purple'],
                    ['num'=>'03','title'=>'Generate Digital Twin',    'desc'=>'6 health scores with risk category.','color'=>'emerald'],
                    ['num'=>'04','title'=>'Simulate Lifestyle Impact','desc'=>'What-if food, sleep & stress scenarios.','color'=>'amber'],
                ];
                @endphp
                @foreach($mSteps as $ms)
                <div class="bg-white rounded-xl border border-gray-100 p-4 flex items-center gap-4">
                    <div class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-xl bg-{{ $ms['color'] }}-50 text-{{ $ms['color'] }}-600 font-extrabold text-sm">{{ $ms['num'] }}</div>
                    <div>
                        <h3 class="font-semibold text-gray-800 text-sm">{{ $ms['title'] }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $ms['desc'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
    </div>
</section>

{{-- ═══════════ FOOTER ═══════════ --}}
<footer style="background:linear-gradient(to right,rgba(124,58,237,0.08),rgba(59,130,246,0.08)); border-top:1px solid rgba(255,255,255,0.15); padding:60px 0;">
    <div class="max-w-7xl mx-auto px-6">

        {{-- Three-column grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-12 mb-12">

            {{-- Left: brand + description --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-lg">🔬</span>
                    <span class="logo-gradient text-lg font-extrabold tracking-tight">HormoneLens AI</span>
                    <span class="status-dot inline-block w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0"></span>
                </div>
                <p class="text-[10px] font-semibold tracking-widest text-gray-400 uppercase mb-4">Predictive Hormone Intelligence Engine</p>
                <p class="text-sm text-gray-500 leading-relaxed max-w-xs">
                    Simulating the metabolic impact of lifestyle to predict risks of PCOS, Insulin Resistance, and Type 2 Diabetes using AI-driven hormone modeling.
                </p>
            </div>

            {{-- Center: navigation --}}
            <div class="flex flex-col gap-3">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">Navigation</p>
                <a href="#features"   class="text-sm text-gray-500 hover:text-violet-700 transition-colors duration-200">Features</a>
                <a href="#conditions" class="text-sm text-gray-500 hover:text-violet-700 transition-colors duration-200">Health Conditions</a>
                <a href="#how"        class="text-sm text-gray-500 hover:text-violet-700 transition-colors duration-200">How It Works</a>
                <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-violet-700 transition-colors duration-200">Simulation Dashboard</a>
            </div>

            {{-- Right: AI system status --}}
            <div class="flex flex-col gap-3">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">System Status</p>
                <div class="flex items-center gap-2.5">
                    <span class="status-dot        inline-block w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0"></span>
                    <span class="text-sm text-gray-600">AI Prediction Engine Active</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="status-dot-delay  inline-block w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0"></span>
                    <span class="text-sm text-gray-600">Simulation Model Synced</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="status-dot-delay2 inline-block w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0"></span>
                    <span class="text-sm text-gray-600">Lifestyle Risk Mapping Enabled</span>
                </div>
            </div>
        </div>

        {{-- Gradient divider --}}
        <div style="height:1px; background:linear-gradient(to right,#7C3AED,#A78BFA); border-radius:1px; margin-bottom:24px;"></div>

        {{-- Bottom bar --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-gray-400">
            <p>&copy; 2026 <span class="logo-gradient font-semibold">HormoneLens AI</span></p>
            <p class="tracking-wide">Metabolic Simulation Platform</p>
        </div>

    </div>
</footer>

</body>
</html>
