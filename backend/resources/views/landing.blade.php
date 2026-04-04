<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

        /* ═══ Navbar brand logo ═══ */
        .navbar-brand {
            display: flex;
            align-items: center;
        }
        .navbar-brand img {
            height: 48px;
            width: auto;
            display: block;
            object-fit: contain;
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

        /* ═══ Cloudpanzer tooltip ═══ */
        .cp-tooltip {
            position: relative;
            display: inline-flex;
            align-items: center;
        }
        .cp-tooltip .cp-tip {
            visibility: hidden;
            opacity: 0;
            width: 260px;
            background: linear-gradient(135deg, #1e1b4b, #312e81);
            color: #fff;
            font-size: 11px;
            font-weight: 500;
            line-height: 1.6;
            text-align: center;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid rgba(124,58,237,0.3);
            box-shadow: 0 8px 24px rgba(0,0,0,0.25);
            position: absolute;
            bottom: calc(100% + 10px);
            left: 50%;
            transform: translateX(-50%);
            transition: opacity 0.25s ease, visibility 0.25s ease;
            pointer-events: none;
            z-index: 100;
        }
        .cp-tooltip .cp-tip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 6px solid transparent;
            border-top-color: #312e81;
        }
        .cp-tooltip:hover .cp-tip {
            visibility: visible;
            opacity: 1;
        }

        /* ═══ Mobile menu ═══ */
        .mobile-menu-dropdown {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s cubic-bezier(0.4,0,0.2,1), opacity 0.3s ease;
            opacity: 0;
        }
        .mobile-menu-dropdown.menu-open {
            max-height: 320px;
            opacity: 1;
        }

        /* ═══ AWS section ═══ */
        .aws-card {
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }
        .aws-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 32px rgba(255,153,0,0.12);
            border-color: rgba(255,153,0,0.3) !important;
        }
        @keyframes awsGlow {
            0%,100% { box-shadow: 0 0 0 0 rgba(255,153,0,0); }
            50%      { box-shadow: 0 0 0 6px rgba(255,153,0,0.08); }
        }
        .aws-badge-glow {
            animation: awsGlow 3s ease-in-out infinite;
        }

        /* ═══ Team section ═══ */
        .team-card {
            opacity: 0;
            transform: translateY(32px);
            transition: opacity 0.65s cubic-bezier(0.4,0,0.2,1),
                        transform 0.65s cubic-bezier(0.4,0,0.2,1),
                        box-shadow 0.28s ease,
                        border-color 0.28s ease;
        }
        .team-card.tc-visible {
            opacity: 1;
            transform: translateY(0);
        }
        .team-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 48px rgba(124,58,237,0.14);
            border-color: rgba(124,58,237,0.25) !important;
        }
        .team-avatar-ring {
            background: linear-gradient(135deg,#7c3aed,#6366f1,#3b82f6);
            padding: 3px;
            border-radius: 9999px;
            display: inline-flex;
        }
        .team-avatar-inner {
            background: linear-gradient(135deg,#ede9fe,#ddd6fe);
            border-radius: 9999px;
            width: 84px; height: 84px;
            display: flex; align-items: center; justify-content: center;
        }
        .team-linkedin {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 14px;
            border-radius: 9999px;
            border: 1px solid rgba(99,102,241,0.25);
            color: #6366f1;
            font-size: 12px; font-weight: 600;
            text-decoration: none;
            transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }
        .team-linkedin:hover {
            background: linear-gradient(90deg,#7c3aed,#6366f1);
            border-color: transparent;
            color: #fff;
            transform: scale(1.04);
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
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: linear-gradient(135deg, #7C3AED, #A78BFA);
            border-radius: 10px;
            height: 44px;
            padding: 0 20px;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            text-decoration: none;
            transition: all 0.2s ease;
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
        /* ── Hero typing heading ── */
        .hero-type-wrap {
            min-height: 2.4em;   /* two lines at line-height 1.1 → no layout shift */
            display: block;
            line-height: 1.1;
        }
        .hero-typed-text {
            display: block;
            min-height: 1.1em;
            background: linear-gradient(110deg, #7c3aed 0%, #6366f1 40%, #a21caf 80%, #ec4899 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
            filter: drop-shadow(0 0 16px rgba(124,58,237,0.22));
            animation: heroGradientShift 5s linear infinite;
        }
        @keyframes heroGradientShift {
            0%   { background-position: 0%   center; }
            100% { background-position: 200% center; }
        }
        @keyframes cursorBlink {
            0%,100% { opacity: 1; }
            50%      { opacity: 0; }
        }
        .hero-cursor {
            display: inline-block;
            width: 3px;
            height: 0.82em;
            background: #7c3aed;
            margin-left: 2px;
            vertical-align: middle;
            border-radius: 2px;
            animation: cursorBlink 0.75s step-end infinite;
        }

        /* ═══════════════════════════════════════════════════
           DESIGN SYSTEM  —  Compact SaaS Spacing System
           ═══════════════════════════════════════════════════
           Scale:  4 · 8 · 12 · 16 · 20 · 24 · 32 · 40 · 48 · 64 px
           Section padding:  64 px top & bottom
           Container:        max-width 1200px · 24px side padding
           Card padding:     20 px
           Button:           height 44px · padding 10px 20px · radius 10px
           Grid gap:         24 px
        ═══════════════════════════════════════════════════ */

        /* ── Hero: 64 px vertical padding ── */
        .hero { padding-top: 64px; padding-bottom: 64px; }

        /* ── Primary CTA button ── */
        .btn-primary {
            display:         inline-flex;
            align-items:     center;
            justify-content: center;
            gap:             8px;
            height:          44px;
            padding:         0 20px;
            border-radius:   10px;
            font-weight:     600;
            font-size:       14px;
            line-height:     1;
            background:      linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);
            color:           #fff;
            border:          none;
            text-decoration: none;
            cursor:          pointer;
            white-space:     nowrap;
            transition:      all 0.2s ease;
            box-shadow:      0 2px 12px rgba(99,102,241,0.22);
        }
        .btn-primary:hover {
            transform:  translateY(-2px);
            box-shadow: 0 6px 20px rgba(99,102,241,0.38);
        }

        /* ── Secondary / outline button ── */
        .btn-secondary {
            display:         inline-flex;
            align-items:     center;
            justify-content: center;
            gap:             8px;
            height:          44px;
            padding:         0 20px;
            border-radius:   10px;
            font-weight:     600;
            font-size:       14px;
            line-height:     1;
            background:      #fff;
            color:           #374151;
            border:          1.5px solid #e5e7eb;
            text-decoration: none;
            cursor:          pointer;
            white-space:     nowrap;
            transition:      all 0.2s ease;
        }
        .btn-secondary:hover {
            transform:    translateY(-2px);
            border-color: rgba(124,58,237,0.5);
            background:   #faf5ff;
            box-shadow:   0 4px 10px rgba(0,0,0,0.06);
        }
    </style>
</head>
<body class="bg-white text-gray-800 antialiased">

{{-- ═══════════ NAVBAR ═══════════ --}}
<nav id="mainNav" class="nav-glass">
    <div class="max-w-[1200px] mx-auto flex items-center justify-between px-6 h-16">
        <a href="/" class="flex-shrink-0">
            <div class="navbar-brand">
                <img src="/images/hormonelogo-navbar.png" alt="HormoneLens Logo">
            </div>
        </a>
        <div class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-500">
            <a href="#features" class="hover:text-violet-700 transition-colors duration-200">Features</a>
            <a href="#conditions" class="hover:text-violet-700 transition-colors duration-200">Health Conditions</a>
            <a href="#how" class="hover:text-violet-700 transition-colors duration-200">How It Works</a>
        </div>
        <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
            <a href="{{ route('login') }}"
               class="hidden sm:block text-sm font-medium text-gray-500 hover:text-violet-700 transition-colors duration-200">Log in</a>
            <a href="{{ route('register') }}"
               class="hidden sm:inline-flex px-5 py-2.5 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md shadow-violet-200/50 items-center gap-1.5">
                Get Started</a>
            {{-- Mobile hamburger --}}
            <button id="mobileMenuBtn" class="md:hidden flex flex-col justify-center items-center w-10 h-10 rounded-lg border border-gray-200 bg-white/80 gap-[5px] flex-shrink-0" aria-label="Toggle navigation menu">
                <span id="mbLine1" class="block w-5 h-0.5 bg-gray-600 transition-all duration-300 origin-center"></span>
                <span id="mbLine2" class="block w-5 h-0.5 bg-gray-600 transition-all duration-300"></span>
                <span id="mbLine3" class="block w-5 h-0.5 bg-gray-600 transition-all duration-300 origin-center"></span>
            </button>
        </div>
    </div>
    {{-- Mobile dropdown menu --}}
    <div id="mobileMenuDropdown" class="mobile-menu-dropdown md:hidden bg-white/96 backdrop-blur-sm border-t border-gray-100">
        <div class="px-5 py-4 flex flex-col gap-1">
            <a href="#features"   class="mobile-nav-link py-2.5 px-3 rounded-lg text-sm font-medium text-gray-600 hover:text-violet-700 hover:bg-violet-50 transition-colors">Features</a>
            <a href="#conditions" class="mobile-nav-link py-2.5 px-3 rounded-lg text-sm font-medium text-gray-600 hover:text-violet-700 hover:bg-violet-50 transition-colors">Health Conditions</a>
            <a href="#how"        class="mobile-nav-link py-2.5 px-3 rounded-lg text-sm font-medium text-gray-600 hover:text-violet-700 hover:bg-violet-50 transition-colors">How It Works</a>
            <div class="border-t border-gray-100 my-1"></div>
            <a href="{{ route('login') }}"    class="mobile-nav-link py-2.5 px-3 rounded-lg text-sm font-medium text-gray-600 hover:text-violet-700 hover:bg-violet-50 transition-colors">Log in</a>
            <a href="{{ route('register') }}" class="mt-1 text-center py-2.5 px-4 bg-gradient-to-r from-violet-600 to-indigo-600 text-white text-sm font-semibold rounded-lg shadow-md shadow-violet-200/50">Get Started Free →</a>
        </div>
    </div>
</nav>

<section class="hero relative overflow-hidden bg-gradient-to-b from-white via-brand-50/30 to-white" style="margin-top:64px;">
    {{-- AI floating particles (background) --}}
    <div class="ai-particle ai-particle-1"></div>
    <div class="ai-particle ai-particle-2"></div>

    {{-- Gradient orbs --}}
    <div class="absolute top-20 -left-40 w-96 h-96 bg-brand-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 float-anim"></div>
    <div class="absolute top-40 -right-40 w-96 h-96 bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 float-anim-delay"></div>

    <div class="relative max-w-[1200px] mx-auto px-6 grid lg:grid-cols-2 gap-6 lg:gap-8 items-center">
        {{-- Left: copy --}}
        <div class="fade-up text-center lg:text-left">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-brand-50 text-brand-700 mb-4 border border-brand-100">
                🧬 AI-Powered Metabolic Simulation Engine
            </span>
            <h1 class="text-[38px] sm:text-[42px] font-extrabold tracking-tight hero-type-wrap leading-[1.1]"
                aria-label="See Your Health Before You Live It!">
                <span id="heroLine1" class="hero-typed-text"></span>
                <span id="heroLine2" class="hero-typed-text"><span id="heroCursor" class="hero-cursor" aria-hidden="true"></span></span>
            </h1>
            <p class="mt-4 sm:mt-5 text-base sm:text-lg text-gray-500 max-w-xl mx-auto lg:mx-0 leading-relaxed">
                Run AI-powered hormone simulations with cortisol predictions, HbA1c projections, chained what-if scenarios, and long-term PCOS/Diabetes risk forecasting — all in real time.
            </p>
            <p class="mt-3 inline-flex items-center gap-2 px-2.5 py-1 bg-purple-50 border border-purple-100 rounded-lg text-xs font-medium text-purple-700">
                <span class="w-1.5 h-1.5 rounded-full bg-purple-500 animate-pulse flex-shrink-0"></span>
                Designed for PCOS, Thyroid Dysfunction, Type 2 Diabetes &amp; Insulin Resistance Monitoring
            </p>
            <div class="mt-5 flex flex-col sm:flex-row flex-wrap gap-3 items-center lg:items-start justify-center lg:justify-start">
                <a href="{{ route('register') }}"
                   class="btn-primary w-full sm:w-auto group">
                    <svg class="w-4 h-4 transition-transform group-hover:rotate-12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/></svg>
                    Run My Hormone Simulation
                </a>
                <a href="#how"
                   class="btn-secondary w-full sm:w-auto">
                    <svg class="w-4 h-4 text-brand-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM9.555 7.168A1 1 0 0 0 8 8v4a1 1 0 0 0 1.555.832l3-2a1 1 0 0 0 0-1.664l-3-2Z" clip-rule="evenodd"/></svg>
                    Explore How It Works
                </a>
            </div>
        </div>

        {{-- Right: AI Simulation dashboard card --}}
        <div id="heroCard" class="relative card-entrance mt-4 lg:mt-0" style="z-index:10;">
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

            <div class="relative bg-white rounded-2xl shadow-[0_20px_50px_-12px_rgba(0,0,0,0.10)] border border-gray-100/80 p-4 max-w-sm sm:max-w-md mx-auto ring-1 ring-black/[0.03]" style="z-index:12;">
                {{-- Top label --}}
                <div class="flex items-center gap-2 mb-3 pb-2.5 border-b border-gray-100">
                    <span class="flex h-2 w-2 relative flex-shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <p class="text-[10px] sm:text-[11px] font-medium text-gray-400 tracking-wide uppercase">Lifestyle Simulation Output <span class="text-gray-300">•</span> Based on Profile Inputs</p>
                </div>

                {{-- Overall Risk Score --}}
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wider mb-1">Overall Risk Score</p>
                        <p class="text-4xl font-extrabold text-amber-500 score-num" data-countup="68.4" data-decimals="1">0.0</p>
                    </div>
                    <span class="px-3 sm:px-3.5 py-1 sm:py-1.5 bg-amber-100 text-amber-700 text-xs font-bold rounded-full tracking-wide">⚠ MEDIUM</span>
                </div>

                {{-- Score cards --}}
                <div class="grid grid-cols-3 gap-2 mb-3">
                    <div class="text-center p-2 bg-indigo-50/80 rounded-xl border border-indigo-100/50">
                        <p class="text-lg font-bold text-indigo-600 score-num" data-countup="82.5" data-decimals="1">0.0</p>
                        <p class="text-[9px] sm:text-[10px] text-gray-500 mt-0.5 font-medium">Metabolic</p>
                    </div>
                    <div class="text-center p-2 bg-purple-50/80 rounded-xl border border-purple-100/50">
                        <p class="text-lg font-bold text-purple-600 score-num" data-countup="55.0" data-decimals="1">0.0</p>
                        <p class="text-[9px] sm:text-[10px] text-gray-500 mt-0.5 font-medium">Insulin Res.</p>
                    </div>
                    <div class="text-center p-2 bg-blue-50/80 rounded-xl border border-blue-100/50">
                        <p class="text-lg font-bold text-blue-600 score-num" data-countup="70.0" data-decimals="1">0.0</p>
                        <p class="text-[9px] sm:text-[10px] text-gray-500 mt-0.5 font-medium">Sleep Score</p>
                    </div>
                </div>

                {{-- Critical alert with pulse --}}
                <div class="alert-pulse flex items-start gap-3 p-3 sm:p-3.5 rounded-xl text-sm border border-red-100">
                    <span class="alert-icon-pulse text-lg leading-none mt-0.5 flex-shrink-0">⛔</span>
                    <div>
                        <p class="font-semibold text-red-700 text-xs sm:text-sm">Diabetes Risk Elevated — Blood Sugar: 210 mg/dL</p>
                        <p class="text-xs text-red-500 mt-0.5">Immediate Lifestyle Intervention Recommended</p>
                    </div>
                </div>
            </div>

            {{-- Top-right: Live Simulation badge --}}
            <div class="absolute -top-3 right-2 sm:-right-3 bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-xs font-bold px-3 sm:px-3.5 py-1.5 rounded-full shadow-lg float-anim flex items-center gap-1.5" style="z-index:14;">
                <span class="flex h-1.5 w-1.5 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-white"></span>
                </span>
                Live Simulation
            </div>

            {{-- Bottom-left: AI Model Running badge --}}
            <div class="badge-glass absolute -bottom-2.5 left-2 sm:-left-2.5 flex items-center gap-1.5 px-3 py-1.5 rounded-full shadow-md text-xs font-semibold text-gray-600" style="z-index:14;">
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

    /* ── Hero typing animation ──
       Line 1: types once ("See Your Health")
       Line 2: fade-in → type → pause 2s → fade-out → next sentence → loop ∞ */
    (function () {
        const line1El = document.getElementById('heroLine1');
        const line2El = document.getElementById('heroLine2');
        const cursor  = document.getElementById('heroCursor');
        if (!line1El || !line2El || !cursor) return;

        const LINE1 = 'Stay Ahead Before';
        const SENTENCES = [
            'Your PCOS Symptoms Worsen',
            'Insulin Resistance Begins',
            'Metabolic Damage Happens',
            'Hormonal Imbalance Starts',
        ];
        const SPEED    = 55;   // ms per character
        const PAUSE_MS = 2000; // ms to hold full sentence
        const FADE_MS  = 550;  // ms for fade transition

        let sentenceIdx = 0;

        /*
         * Reliable opacity fade.
         * KEY: disable transition → set start opacity → force browser layout
         * flush (getBoundingClientRect) → re-enable transition → set end opacity.
         * Without the flush the browser batches both style changes together and
         * never actually transitions — causing the "instant disappear" bug.
         */
        function fadeEl(el, from, to, duration, done) {
            el.style.transition = 'none';
            el.style.opacity    = String(from);
            el.getBoundingClientRect(); // force layout flush — commits `from` opacity
            el.style.transition = 'opacity ' + duration + 'ms ease';
            el.style.opacity    = String(to);
            setTimeout(done, duration + 20);
        }

        function attachCursor(el) { el.appendChild(cursor); }

        function clearText(el) {
            Array.from(el.childNodes).forEach(function (n) {
                if (n !== cursor) el.removeChild(n);
            });
        }

        function typeInto(el, text, done) {
            var i = 0;
            attachCursor(el);
            (function tick() {
                if (i < text.length) {
                    el.insertBefore(document.createTextNode(text[i++]), cursor);
                    setTimeout(tick, SPEED);
                } else {
                    if (done) done();
                }
            })();
        }

        function runSentence() {
            var text = SENTENCES[sentenceIdx];
            sentenceIdx = (sentenceIdx + 1) % SENTENCES.length;

            // Start hidden & cleared
            clearText(line2El);
            attachCursor(line2El);
            line2El.style.transition = 'none';
            line2El.style.opacity    = '0';
            line2El.getBoundingClientRect();

            // 1. Fade in
            fadeEl(line2El, 0, 1, FADE_MS, function () {
                // 2. Type letter-by-letter
                typeInto(line2El, text, function () {
                    // 3. Pause
                    setTimeout(function () {
                        // 4. Fade out
                        fadeEl(line2El, 1, 0, FADE_MS, function () {
                            // 5. Next sentence
                            runSentence();
                        });
                    }, PAUSE_MS);
                });
            });
        }

        // Phase 1: type Line 1 once, then kick off the loop
        setTimeout(function () {
            typeInto(line1El, LINE1, function () {
                setTimeout(runSentence, 350);
            });
        }, 400);
    })();

    /* ── Navbar entrance (0ms) ── */
    requestAnimationFrame(() => {
        document.getElementById('mainNav').classList.add('nav-visible');
    });

    /* ── Mobile hamburger menu ── */
    const mmbtn = document.getElementById('mobileMenuBtn');
    const mmenu = document.getElementById('mobileMenuDropdown');
    const mbLine1 = document.getElementById('mbLine1');
    const mbLine2 = document.getElementById('mbLine2');
    const mbLine3 = document.getElementById('mbLine3');
    let menuOpen = false;
    if (mmbtn && mmenu) {
        mmbtn.addEventListener('click', () => {
            menuOpen = !menuOpen;
            mmenu.classList.toggle('menu-open', menuOpen);
            if (menuOpen) {
                mbLine1.style.transform = 'translateY(6px) rotate(45deg)';
                mbLine2.style.opacity   = '0';
                mbLine3.style.transform = 'translateY(-6px) rotate(-45deg)';
            } else {
                mbLine1.style.transform = '';
                mbLine2.style.opacity   = '';
                mbLine3.style.transform = '';
            }
        });
        mmenu.querySelectorAll('.mobile-nav-link').forEach(a => {
            a.addEventListener('click', () => {
                menuOpen = false;
                mmenu.classList.remove('menu-open');
                mbLine1.style.transform = '';
                mbLine2.style.opacity   = '';
                mbLine3.style.transform = '';
            });
        });
    }

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

{{-- ═══════════ HEALTHY SNAKE GAME ═══════════ --}}
<section id="sleep-game" class="py-16 bg-gradient-to-b from-white via-purple-50/40 to-white overflow-hidden">
    <div class="max-w-[1200px] mx-auto px-6">
        <div class="text-center mb-6">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-purple-50 text-purple-700 mb-4 border border-purple-100">
                🐍 Interactive Health Game
            </span>
            <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight text-gray-900">
                Can You <span class="bg-gradient-to-r from-brand-600 to-purple-600 bg-clip-text text-transparent">Eat Healthy?</span>
            </h2>
            <p class="mt-3 text-gray-500 max-w-2xl mx-auto text-sm lg:text-base">
                Play <strong>Healthy Snake</strong> — guide your snake to eat nutritious foods, avoid junk food, and survive.
            </p>
        </div>

        {{-- Desktop: side-by-side | Mobile: stacked --}}
        <div class="flex flex-col lg:flex-row gap-5 items-stretch">

            {{-- Game iframe --}}
            <div class="lg:flex-[3] min-w-0">
                <div id="game-frame-wrap" class="rounded-2xl overflow-hidden shadow-[0_25px_60px_-12px_rgba(0,0,0,0.25)] border border-purple-100/60 ring-1 ring-black/[0.03] h-full" style="background: #0d1117;">
                    <iframe
                        src="{{ asset('sleep-catcher/index.html') }}"
                        title="Healthy Snake — Eat Healthy, Stay Alive"
                        class="w-full border-0"
                        style="height: clamp(380px, 46vw, 600px);"
                        allow="autoplay"
                        loading="lazy"
                        sandbox="allow-scripts allow-same-origin"
                    ></iframe>
                </div>
            </div>

            {{-- Info sidebar (desktop) --}}
            <div class="lg:flex-[1.2] min-w-0 flex flex-col gap-4">
                {{-- How to play card --}}
                <div class="rounded-2xl border border-purple-100/60 bg-white/80 backdrop-blur p-5 shadow-sm flex-1">
                    <h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-purple-100 flex items-center justify-center text-sm">🎮</span>
                        How to Play
                    </h3>
                    <ul class="space-y-2.5 text-sm text-gray-600">
                        <li class="flex items-start gap-2">
                            <span class="mt-0.5 w-5 h-5 rounded-full bg-green-50 text-green-600 flex items-center justify-center text-xs font-bold shrink-0">1</span>
                            <span>Select your <strong>gender</strong> and <strong>character</strong> body type</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-0.5 w-5 h-5 rounded-full bg-green-50 text-green-600 flex items-center justify-center text-xs font-bold shrink-0">2</span>
                            <span>Use <kbd class="px-1 py-0.5 bg-gray-100 rounded text-[10px] font-mono text-gray-500">Arrow keys</kbd> or <kbd class="px-1 py-0.5 bg-gray-100 rounded text-[10px] font-mono text-gray-500">WASD</kbd> to move</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-0.5 w-5 h-5 rounded-full bg-green-50 text-green-600 flex items-center justify-center text-xs font-bold shrink-0">3</span>
                            <span>Eat <strong class="text-green-600">healthy food</strong> to grow your snake</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-0.5 w-5 h-5 rounded-full bg-red-50 text-red-500 flex items-center justify-center text-xs font-bold shrink-0">✗</span>
                            <span>Avoid <strong class="text-red-500">junk food</strong> — 3 strikes and you're out!</span>
                        </li>
                    </ul>
                </div>

                {{-- Health facts card --}}
                <div class="rounded-2xl border border-purple-100/60 bg-white/80 backdrop-blur p-5 shadow-sm flex-1">
                    <h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center text-sm">🧠</span>
                        Did You Know?
                    </h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start gap-2">
                            <span class="text-purple-400 mt-0.5">▸</span>
                            <span>Processed food can spike <strong>insulin levels</strong> by up to 300%</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-purple-400 mt-0.5">▸</span>
                            <span>A balanced diet reduces <strong>PCOS symptoms</strong> by 40%</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-purple-400 mt-0.5">▸</span>
                            <span>Leafy greens boost <strong>thyroid function</strong> naturally</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-purple-400 mt-0.5">▸</span>
                            <span>Regular healthy eating lowers <strong>metabolic syndrome</strong> risk by 50%</span>
                        </li>
                    </ul>
                </div>

                {{-- Controls hint (desktop only) --}}
                <div class="hidden lg:block rounded-2xl border border-gray-100 bg-gray-50/80 p-4">
                    <div class="flex items-center gap-3 text-xs text-gray-500">
                        <div class="grid grid-cols-3 gap-1 shrink-0">
                            <span></span>
                            <kbd class="w-6 h-6 flex items-center justify-center bg-white rounded border border-gray-200 text-[10px] font-mono shadow-sm">↑</kbd>
                            <span></span>
                            <kbd class="w-6 h-6 flex items-center justify-center bg-white rounded border border-gray-200 text-[10px] font-mono shadow-sm">←</kbd>
                            <kbd class="w-6 h-6 flex items-center justify-center bg-white rounded border border-gray-200 text-[10px] font-mono shadow-sm">↓</kbd>
                            <kbd class="w-6 h-6 flex items-center justify-center bg-white rounded border border-gray-200 text-[10px] font-mono shadow-sm">→</kbd>
                        </div>
                        <div>
                            <p class="font-medium text-gray-700 mb-0.5">Keyboard Controls</p>
                            <p>Press <kbd class="px-1 py-0.5 bg-white rounded text-[10px] font-mono border border-gray-200">ESC</kbd> to unlock page scroll</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Mobile-only controls hint --}}
        <p class="text-center text-xs text-gray-400 mt-4 lg:hidden">
            Swipe to move on mobile &bull; 3 junk food strikes = death
        </p>
    </div>
</section>

<script>
(function () {
    var gameActive = false;
    var scrollKeys = [32, 37, 38, 39, 40]; // Space, arrows

    function preventScroll(e) {
        if (scrollKeys.includes(e.keyCode)) { e.preventDefault(); }
    }

    // Listen for messages from the game iframe
    window.addEventListener('message', function (ev) {
        var d = ev.data;
        if (!d || typeof d.type !== 'string') return;

        if (d.type === 'GAME_FOCUS') {
            gameActive = true;
            document.body.style.overflow = 'hidden';
            window.addEventListener('keydown', preventScroll, { passive: false });
        }
        if (d.type === 'GAME_BLUR' || d.type === 'GAME_EXIT') {
            gameActive = false;
            document.body.style.overflow = '';
            window.removeEventListener('keydown', preventScroll);
        }
    });

    // Clicking inside iframe wrapper → lock scroll; mouse leaving wrapper → unlock
    var wrap = document.getElementById('game-frame-wrap');
    if (wrap) {
        wrap.addEventListener('click', function () {
            gameActive = true;
            document.body.style.overflow = 'hidden';
            window.addEventListener('keydown', preventScroll, { passive: false });
        });

        // Restore scroll as soon as the cursor leaves the game area
        wrap.addEventListener('mouseleave', function () {
            gameActive = false;
            document.body.style.overflow = '';
            window.removeEventListener('keydown', preventScroll);
        });
    }

    // Also restore scroll when clicking anywhere outside the wrapper
    document.addEventListener('click', function (e) {
        if (wrap && !wrap.contains(e.target)) {
            gameActive = false;
            document.body.style.overflow = '';
            window.removeEventListener('keydown', preventScroll);
        }
    });

    // Restore scroll on wheel/trackpad swipe when cursor is outside game area
    document.addEventListener('wheel', function (e) {
        if (gameActive && wrap && !wrap.contains(e.target)) {
            gameActive = false;
            document.body.style.overflow = '';
            window.removeEventListener('keydown', preventScroll);
        }
    }, { passive: true });

    // ESC from parent page while game is active → scroll to section / unlock
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && gameActive) {
            gameActive = false;
            document.body.style.overflow = '';
            window.removeEventListener('keydown', preventScroll);
            // move focus away from iframe
            if (wrap) { var btn = wrap.querySelector('iframe'); if (btn) btn.blur(); }
        }
    });
})();
</script>

{{-- ═══════════ CAPABILITIES ═══════════ --}}
<section id="features" class="py-16 bg-gray-50 overflow-hidden">
    <div class="max-w-[1200px] mx-auto px-6">

        {{-- Section header --}}
        <div class="text-center max-w-2xl mx-auto mb-6 fade-up">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-violet-50 text-violet-700 mb-3 border border-violet-100">
                🧠 AI-Powered Health Intelligence
            </span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">The Most Advanced Hormone Simulation Engine Ever Built</h2>
            <p class="mt-3 text-gray-500">12 powerful AI-driven capabilities — from real-time hormone prediction to long-term disease risk projection — all powered by Amazon Bedrock.</p>
        </div>

        {{-- Capability cards --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">

            {{-- 1. Metabolic Digital Twin --}}
            <div class="sim-panel bg-white rounded-xl border border-gray-100 p-6" data-sim-delay="0">
                <div class="w-10 h-10 flex items-center justify-center rounded-xl text-xl mb-4" style="background:rgba(124,58,237,0.08);">🧬</div>
                <h3 class="font-semibold text-gray-800 mb-2">Metabolic Digital Twin</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Constructs a real-time physiological model of insulin sensitivity, glucose metabolism, and hormonal balance — your virtual health replica.</p>
            </div>

            {{-- 2. Hormone Prediction Engine (NEW) --}}
            <div class="sim-panel bg-white rounded-xl border border-gray-100 p-6 relative" data-sim-delay="100">
                <span class="absolute top-3 right-3 px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded-full uppercase tracking-wide">New</span>
                <div class="w-10 h-10 flex items-center justify-center rounded-xl text-xl mb-4" style="background:rgba(16,185,129,0.08);">🔮</div>
                <h3 class="font-semibold text-gray-800 mb-2">Hormone Prediction Engine</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Predicts cortisol curves, androgen levels, menstrual cycle regularity, and HbA1c trajectories using AI-driven physiological modeling.</p>
                <div class="mt-3 flex flex-wrap gap-1">
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 text-[10px] rounded-full border border-emerald-100 font-medium">Cortisol</span>
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 text-[10px] rounded-full border border-emerald-100 font-medium">Androgen</span>
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 text-[10px] rounded-full border border-emerald-100 font-medium">Cycle</span>
                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 text-[10px] rounded-full border border-emerald-100 font-medium">HbA1c</span>
                </div>
            </div>

            {{-- 3. Long-Term Health Projections (NEW) --}}
            <div class="sim-panel bg-white rounded-xl border border-gray-100 p-6 relative" data-sim-delay="200">
                <span class="absolute top-3 right-3 px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded-full uppercase tracking-wide">New</span>
                <div class="w-10 h-10 flex items-center justify-center rounded-xl text-xl mb-4" style="background:rgba(245,158,11,0.08);">📊</div>
                <h3 class="font-semibold text-gray-800 mb-2">Long-Term Health Projections</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Projects PCOS progression, diabetes complications, thyroid dysfunction trajectories, and fertility outlook over months and years.</p>
                <div class="mt-3 flex flex-wrap gap-1">
                    <span class="px-2 py-0.5 bg-amber-50 text-amber-600 text-[10px] rounded-full border border-amber-100 font-medium">PCOS</span>
                    <span class="px-2 py-0.5 bg-amber-50 text-amber-600 text-[10px] rounded-full border border-amber-100 font-medium">Diabetes</span>
                    <span class="px-2 py-0.5 bg-amber-50 text-amber-600 text-[10px] rounded-full border border-amber-100 font-medium">Thyroid</span>
                    <span class="px-2 py-0.5 bg-amber-50 text-amber-600 text-[10px] rounded-full border border-amber-100 font-medium">Fertility</span>
                </div>
            </div>

            {{-- 4. Chained What-If Simulations (NEW) --}}
            <div class="sim-panel bg-white rounded-xl border border-gray-100 p-6 relative" data-sim-delay="300">
                <span class="absolute top-3 right-3 px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded-full uppercase tracking-wide">New</span>
                <div class="w-10 h-10 flex items-center justify-center rounded-xl text-xl mb-4" style="background:rgba(99,102,241,0.08);">🔗</div>
                <h3 class="font-semibold text-gray-800 mb-2">Chained What-If Simulations</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Chain multiple simulations together — see how changing sleep after adjusting diet compounds your metabolic outcomes over time.</p>
            </div>

            {{-- 5. Side-by-Side Comparison (NEW) --}}
            <div class="sim-panel bg-white rounded-xl border border-gray-100 p-6 relative" data-sim-delay="400">
                <span class="absolute top-3 right-3 px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded-full uppercase tracking-wide">New</span>
                <div class="w-10 h-10 flex items-center justify-center rounded-xl text-xl mb-4" style="background:rgba(168,85,247,0.08);">⚖️</div>
                <h3 class="font-semibold text-gray-800 mb-2">Side-by-Side Comparison</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Compare up to 5 simulations side-by-side — visualize how different lifestyle scenarios stack up against each other in risk outcomes.</p>
            </div>

            {{-- 6. AI Food Intelligence (NEW) --}}
            <div class="sim-panel bg-white rounded-xl border border-gray-100 p-6 relative" data-sim-delay="500">
                <span class="absolute top-3 right-3 px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded-full uppercase tracking-wide">New</span>
                <div class="w-10 h-10 flex items-center justify-center rounded-xl text-xl mb-4" style="background:rgba(34,197,94,0.08);">🍽️</div>
                <h3 class="font-semibold text-gray-800 mb-2">AI Food Intelligence</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Glycemic load analysis with AI-powered natural language meal parsing — just type "2 rotis with dal" and get instant metabolic impact.</p>
                <div class="mt-3 flex flex-wrap gap-1">
                    <span class="px-2 py-0.5 bg-green-50 text-green-600 text-[10px] rounded-full border border-green-100 font-medium">GL Scoring</span>
                    <span class="px-2 py-0.5 bg-green-50 text-green-600 text-[10px] rounded-full border border-green-100 font-medium">NLP Parsing</span>
                    <span class="px-2 py-0.5 bg-green-50 text-green-600 text-[10px] rounded-full border border-green-100 font-medium">Temporal</span>
                </div>
            </div>

            {{-- 7. PCOS & Diabetes Risk Analysis --}}
            <div class="sim-panel bg-white rounded-xl border border-gray-100 p-6" data-sim-delay="600">
                <div class="w-10 h-10 flex items-center justify-center rounded-xl text-xl mb-4" style="background:rgba(124,58,237,0.08);">🔬</div>
                <h3 class="font-semibold text-gray-800 mb-2">PCOS &amp; Diabetes Risk Analysis</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Forecasts insulin resistance progression and Type 2 Diabetes probability through hormonal response mapping with cached risk scores.</p>
            </div>

            {{-- 8. Real-Time Adaptive Alerts (NEW) --}}
            <div class="sim-panel bg-white rounded-xl border border-gray-100 p-6 relative" data-sim-delay="700">
                <span class="absolute top-3 right-3 px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded-full uppercase tracking-wide">New</span>
                <div class="w-10 h-10 flex items-center justify-center rounded-xl text-xl mb-4" style="background:rgba(239,68,68,0.08);">🚨</div>
                <h3 class="font-semibold text-gray-800 mb-2">Real-Time Adaptive Alerts</h3>
                <p class="text-sm text-gray-500 leading-relaxed">WebSocket-powered live alerts that broadcast when risk thresholds are breached — with adaptive thresholds that learn from your history.</p>
            </div>

            {{-- 9. RAG Knowledge Engine (NEW) --}}
            <div class="sim-panel bg-white rounded-xl border border-gray-100 p-6 relative" data-sim-delay="800">
                <span class="absolute top-3 right-3 px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded-full uppercase tracking-wide">New</span>
                <div class="w-10 h-10 flex items-center justify-center rounded-xl text-xl mb-4" style="background:rgba(59,130,246,0.08);">📚</div>
                <h3 class="font-semibold text-gray-800 mb-2">RAG Knowledge Engine</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Tree-based retrieval-augmented generation for medical Q&amp;A — hierarchical keyword-scored traversal with logarithmic confidence scoring.</p>
            </div>

            {{-- 10. AI Risk Assessment Engine --}}
            <div class="sim-panel bg-white rounded-xl border border-gray-100 p-6" data-sim-delay="900">
                <div class="w-10 h-10 flex items-center justify-center rounded-xl text-xl mb-4" style="background:rgba(124,58,237,0.08);">🧠</div>
                <h3 class="font-semibold text-gray-800 mb-2">AI Risk Assessment Engine</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Translates simulated metabolic outcomes into clinically relevant health risk levels with field-level caching for lightning-fast responses.</p>
            </div>

            {{-- 11. AI Guardrail Protection (NEW) --}}
            <div class="sim-panel bg-white rounded-xl border border-gray-100 p-6 relative" data-sim-delay="1000">
                <span class="absolute top-3 right-3 px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded-full uppercase tracking-wide">New</span>
                <div class="w-10 h-10 flex items-center justify-center rounded-xl text-xl mb-4" style="background:rgba(220,38,38,0.08);">🛡️</div>
                <h3 class="font-semibold text-gray-800 mb-2">AI Guardrail Protection</h3>
                <p class="text-sm text-gray-500 leading-relaxed">14-pattern prompt injection detection shields every AI interaction — blocking jailbreak attempts, role manipulation, and data exfiltration.</p>
            </div>

            {{-- 12. Redis Performance Engine (NEW) --}}
            <div class="sim-panel bg-white rounded-xl border border-gray-100 p-6 relative" data-sim-delay="1100">
                <span class="absolute top-3 right-3 px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded-full uppercase tracking-wide">New</span>
                <div class="w-10 h-10 flex items-center justify-center rounded-xl text-xl mb-4" style="background:rgba(245,158,11,0.08);">⚡</div>
                <h3 class="font-semibold text-gray-800 mb-2">Redis Performance Engine</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Intelligent caching layer for risk scores, food lookups, RAG results, and prediction data — sub-millisecond response times at scale.</p>
            </div>

        </div>
    </div>
</section>

{{-- ═══════════ ONBOARDING BANNER ═══════════ --}}
<div class="max-w-[1200px] mx-auto px-6">
    <div id="onboardBanner" class="onboard-banner relative overflow-hidden rounded-2xl border p-6 sm:p-8 my-8"
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
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2 leading-snug">
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
<section id="conditions" class="py-16">
    <div class="max-w-[1200px] mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-6 fade-up">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-violet-50 text-violet-700 mb-3 border border-violet-100">
                🔬 Clinical Risk Parameter Mapping
            </span>
            <h2 class="text-[28px] md:text-[32px] font-extrabold tracking-tight">Dynamic Health Condition Tracking</h2>
            <p class="mt-3 text-sm text-gray-500">Extensible to any number of conditions — each with custom fields, validation rules, and risk scoring.</p>
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
            <div class="disease-card bg-white rounded-xl border border-gray-100 p-5 text-center" data-dc-delay="{{ $delay }}">
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
<section id="how" class="py-16 bg-gray-50">
    <div class="max-w-[1200px] mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-6 fade-up">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-violet-50 text-violet-700 mb-3 border border-violet-100">
                ♻️ Continuous AI Simulation Cycle
            </span>
            <h2 class="text-[28px] md:text-[32px] font-extrabold tracking-tight">How It Works</h2>
            <p class="mt-2 text-sm text-gray-500">See Your Health Before You Live It!</p>
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

{{-- ═══════════ AWS POWERED SECTION ═══════════ --}}
<section id="aws-powered" class="py-16 bg-gradient-to-b from-gray-50 to-white overflow-hidden">
    <div class="max-w-[1200px] mx-auto px-6">

        {{-- Section header --}}
        <div class="text-center max-w-3xl mx-auto mb-6">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-50 border border-amber-200 text-amber-700 text-xs font-bold uppercase tracking-widest mb-5">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M18.75 3a.75.75 0 0 1 .75.75v16.5a.75.75 0 0 1-1.5 0V3.75a.75.75 0 0 1 .75-.75Zm-13.5 0a.75.75 0 0 1 .75.75v16.5a.75.75 0 0 1-1.5 0V3.75a.75.75 0 0 1 .75-.75ZM12 3a.75.75 0 0 1 .75.75v16.5a.75.75 0 0 1-1.5 0V3.75A.75.75 0 0 1 12 3Z"/></svg>
                ☁️ Enterprise AWS Cloud Infrastructure
            </div>
            <h2 class="text-[28px] sm:text-[32px] font-extrabold tracking-tight text-gray-900 mb-3">
                Built on the Cloud That Powers <span style="background:linear-gradient(90deg,#FF9900,#FFB347);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">the World</span>
            </h2>
            <p class="text-sm text-gray-500 leading-relaxed max-w-2xl mx-auto">
                HormoneLens AI is proudly powered by <strong class="text-gray-700">Amazon Web Services</strong> — the world's most trusted cloud. Every simulation, every AI inference, every data point runs on battle-tested AWS infrastructure designed for healthcare-grade reliability and scale.
            </p>
        </div>

        {{-- AWS Service Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">

            {{-- Amazon Bedrock --}}
            <div class="aws-card bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:border-amber-200">
                <div class="flex items-start gap-4 mb-4">
                    <div class="w-12 h-12 flex-shrink-0 flex items-center justify-center rounded-xl" style="background:linear-gradient(135deg,#FF9900,#FFB347);">
                        <span class="text-white text-xl">🤖</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">Amazon Bedrock</h3>
                        <span class="text-[10px] font-semibold uppercase tracking-widest text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">Core AI Engine</span>
                    </div>
                </div>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Powers the heart of HormoneLens — AI-driven hormone simulations, RAG-based health Q&A, and intelligent risk predictions via foundation models including Claude &amp; Titan.
                </p>
                <div class="mt-4 flex flex-wrap gap-1.5">
                    <span class="px-2 py-0.5 bg-amber-50 text-amber-700 text-[10px] rounded-full border border-amber-100 font-medium">Claude Models</span>
                    <span class="px-2 py-0.5 bg-amber-50 text-amber-700 text-[10px] rounded-full border border-amber-100 font-medium">RAG Knowledge Base</span>
                    <span class="px-2 py-0.5 bg-amber-50 text-amber-700 text-[10px] rounded-full border border-amber-100 font-medium">AI Inference</span>
                </div>
            </div>

            {{-- Amazon EC2 --}}
            <div class="aws-card bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:border-amber-200">
                <div class="flex items-start gap-4 mb-4">
                    <div class="w-12 h-12 flex-shrink-0 flex items-center justify-center rounded-xl" style="background:linear-gradient(135deg,#e55c00,#FF9900);">
                        <span class="text-white text-xl">⚡</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">Amazon EC2</h3>
                        <span class="text-[10px] font-semibold uppercase tracking-widest text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full">Compute Layer</span>
                    </div>
                </div>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Scalable virtual compute instances run the Laravel application server, processing thousands of metabolic simulations with low-latency response times.
                </p>
                <div class="mt-4 flex flex-wrap gap-1.5">
                    <span class="px-2 py-0.5 bg-orange-50 text-orange-700 text-[10px] rounded-full border border-orange-100 font-medium">Auto Scaling</span>
                    <span class="px-2 py-0.5 bg-orange-50 text-orange-700 text-[10px] rounded-full border border-orange-100 font-medium">High Availability</span>
                    <span class="px-2 py-0.5 bg-orange-50 text-orange-700 text-[10px] rounded-full border border-orange-100 font-medium">Load Balanced</span>
                </div>
            </div>

            {{-- Amazon RDS --}}
            <div class="aws-card bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:border-amber-200">
                <div class="flex items-start gap-4 mb-4">
                    <div class="w-12 h-12 flex-shrink-0 flex items-center justify-center rounded-xl" style="background:linear-gradient(135deg,#2563eb,#3b82f6);">
                        <span class="text-white text-xl">🗄️</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">Amazon RDS</h3>
                        <span class="text-[10px] font-semibold uppercase tracking-widest text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">Managed Database</span>
                    </div>
                </div>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Fully managed MySQL database stores health profiles, simulation history, and digital twin data with automated backups and multi-AZ failover.
                </p>
                <div class="mt-4 flex flex-wrap gap-1.5">
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 text-[10px] rounded-full border border-blue-100 font-medium">Multi-AZ</span>
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 text-[10px] rounded-full border border-blue-100 font-medium">Auto Backups</span>
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 text-[10px] rounded-full border border-blue-100 font-medium">Encryption</span>
                </div>
            </div>

            {{-- Amazon S3 --}}
            <div class="aws-card bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:border-amber-200">
                <div class="flex items-start gap-4 mb-4">
                    <div class="w-12 h-12 flex-shrink-0 flex items-center justify-center rounded-xl" style="background:linear-gradient(135deg,#16a34a,#22c55e);">
                        <span class="text-white text-xl">📦</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">Amazon S3</h3>
                        <span class="text-[10px] font-semibold uppercase tracking-widest text-green-600 bg-green-50 px-2 py-0.5 rounded-full">Object Storage</span>
                    </div>
                </div>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Stores the RAG knowledge base documents, medical research embeddings, and simulation reports with 99.999999999% durability.
                </p>
                <div class="mt-4 flex flex-wrap gap-1.5">
                    <span class="px-2 py-0.5 bg-green-50 text-green-700 text-[10px] rounded-full border border-green-100 font-medium">RAG Documents</span>
                    <span class="px-2 py-0.5 bg-green-50 text-green-700 text-[10px] rounded-full border border-green-100 font-medium">11 Nines Durability</span>
                    <span class="px-2 py-0.5 bg-green-50 text-green-700 text-[10px] rounded-full border border-green-100 font-medium">Versioned</span>
                </div>
            </div>

            {{-- Amazon Nova Pro --}}
            <div class="aws-card bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:border-amber-200">
                <div class="flex items-start gap-4 mb-4">
                    <div class="w-12 h-12 flex-shrink-0 flex items-center justify-center rounded-xl" style="background:linear-gradient(135deg,#7c3aed,#a78bfa);">
                        <span class="text-white text-xl">✨</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">Amazon Nova Pro</h3>
                        <span class="text-[10px] font-semibold uppercase tracking-widest text-violet-600 bg-violet-50 px-2 py-0.5 rounded-full">Multimodal AI Model</span>
                    </div>
                </div>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Powers advanced hormone simulation reasoning and health Q&amp;A via Amazon Bedrock — delivering fast, accurate, multimodal AI inference for complex metabolic predictions.
                </p>
                <div class="mt-4 flex flex-wrap gap-1.5">
                    <span class="px-2 py-0.5 bg-violet-50 text-violet-700 text-[10px] rounded-full border border-violet-100 font-medium">Text &amp; Vision</span>
                    <span class="px-2 py-0.5 bg-violet-50 text-violet-700 text-[10px] rounded-full border border-violet-100 font-medium">Low Latency</span>
                    <span class="px-2 py-0.5 bg-violet-50 text-violet-700 text-[10px] rounded-full border border-violet-100 font-medium">Bedrock Native</span>
                </div>
            </div>

            {{-- IAM + Security --}}
            <div class="aws-card bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:border-amber-200">
                <div class="flex items-start gap-4 mb-4">
                    <div class="w-12 h-12 flex-shrink-0 flex items-center justify-center rounded-xl" style="background:linear-gradient(135deg,#dc2626,#f87171);">
                        <span class="text-white text-xl">🔐</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">AWS IAM &amp; Security</h3>
                        <span class="text-[10px] font-semibold uppercase tracking-widest text-red-600 bg-red-50 px-2 py-0.5 rounded-full">Zero-Trust Security</span>
                    </div>
                </div>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Fine-grained IAM roles, encrypted health data in transit and at rest, VPC isolation, and security groups ensuring HIPAA-aligned data protection.
                </p>
                <div class="mt-4 flex flex-wrap gap-1.5">
                    <span class="px-2 py-0.5 bg-red-50 text-red-700 text-[10px] rounded-full border border-red-100 font-medium">IAM Roles</span>
                    <span class="px-2 py-0.5 bg-red-50 text-red-700 text-[10px] rounded-full border border-red-100 font-medium">VPC Isolation</span>
                    <span class="px-2 py-0.5 bg-red-50 text-red-700 text-[10px] rounded-full border border-red-100 font-medium">KMS Encryption</span>
                </div>
            </div>

        </div>

        {{-- Powered By Badges --}}
        <div class="flex flex-col items-center gap-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 max-w-2xl w-full">
                {{-- AWS Powered Badge --}}
                <div class="aws-badge-glow flex items-center gap-4 px-5 py-4 bg-white rounded-2xl border border-amber-200 shadow-lg">
                    <div class="w-14 h-14 flex-shrink-0 flex items-center justify-center rounded-xl" style="background:linear-gradient(135deg,#FF9900,#FFB347);">
                        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg"><path d="M18.75 11.35a4.32 4.32 0 0 0-3.93-2.57c-.55 0-1.08.12-1.56.33A5.15 5.15 0 0 0 3.08 12a5.18 5.18 0 0 0 .66 2.52A3.74 3.74 0 0 0 2 17.63 3.78 3.78 0 0 0 5.78 21.4h12.53a3.37 3.37 0 0 0 3.38-3.37 3.38 3.38 0 0 0-2.94-3.35v-.01a4.33 4.33 0 0 0 0-3.32ZM7.27 17.13a.63.63 0 0 1 0-.87l2.85-2.85a.63.63 0 0 1 .87.87l-2.85 2.85a.63.63 0 0 1-.87 0Zm2.72-5.66-.87.87a.63.63 0 0 1-.87-.87l.87-.87a.63.63 0 0 1 .87.87Zm2.2 5.66a.63.63 0 0 1 0-.87l4.24-4.24a.63.63 0 0 1 .87.87l-4.24 4.24a.63.63 0 0 1-.87 0Z"/></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Powered by</p>
                        <p class="text-base font-extrabold text-gray-900">Amazon Web Services</p>
                        <p class="text-[10px] text-amber-600 font-semibold mt-0.5">AI for Bharat Hackathon</p>
                    </div>
                </div>

                {{-- Cloudpanzer Deployed Badge --}}
                <a href="https://cloudpanzer.com/" target="_blank" rel="noopener noreferrer" class="cp-deploy-card flex items-center gap-4 px-5 py-4 bg-white rounded-2xl border border-indigo-200 shadow-lg hover:border-violet-400 hover:shadow-xl transition-all duration-300 group">
                    <div class="w-14 h-14 flex-shrink-0 flex items-center justify-center rounded-xl" style="background:linear-gradient(135deg,#7c3aed,#6366f1);">
                        <span class="text-2xl">🚀</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Deployed via</p>
                        <p class="text-base font-extrabold text-gray-900 group-hover:text-violet-700 transition-colors">Cloudpanzer</p>
                        <p class="text-[10px] text-violet-600 font-semibold mt-0.5">One-click GitHub Deployments</p>
                    </div>
                </a>
            </div>
            <p class="text-center text-xs text-gray-400 max-w-lg">
                Proudly built for the <strong class="text-gray-600">AI for Bharat</strong> hackathon in partnership with <strong class="text-amber-600">AWS</strong> — democratizing AI-driven preventive healthcare for India and beyond.
            </p>
        </div>

    </div>
</section>

{{-- ═══════════ MEET THE TEAM ═══════════ --}}
<section id="team" class="py-16 bg-gradient-to-b from-white via-violet-50/30 to-white overflow-hidden">
    <div class="max-w-[1200px] mx-auto px-6">

        {{-- Section header --}}
        <div class="text-center max-w-2xl mx-auto mb-6">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-violet-50 border border-violet-200 text-violet-700 text-xs font-bold uppercase tracking-widest mb-5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m4-4a4 4 0 100-8 4 4 0 000 8z"/></svg>
                The People Behind It
            </div>
            <h2 class="text-[28px] sm:text-[32px] font-extrabold tracking-tight text-gray-900 mb-3">
                Meet the <span style="background:linear-gradient(90deg,#7c3aed,#6366f1,#3b82f6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Team</span>
            </h2>
            <p class="text-sm text-gray-500 leading-relaxed">
                A passionate group of engineers and innovators building AI-powered preventive healthcare for the&nbsp;world.
            </p>
        </div>

        {{-- Team cards grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

            {{-- Card 1: Ravdeep Singh --}}
            <div class="team-card bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col items-center text-center">
                {{-- Avatar --}}
                <div class="team-avatar-ring mb-4">
                    <img src="{{ asset('images/profile/ravdeep.jpg') }}" alt="Ravdeep Singh" class="w-[84px] h-[84px] rounded-full object-cover">
                </div>
                {{-- Name & Role --}}
                <h3 class="text-base font-extrabold text-gray-900 mb-1">Ravdeep Singh</h3>
                <span class="inline-block px-3 py-0.5 rounded-full text-xs font-bold bg-violet-100 text-violet-700 mb-3 uppercase tracking-wide">Team Leader</span>
                {{-- Description --}}
                <p class="text-xs text-gray-500 leading-relaxed mb-4"><strong class="text-gray-700">Founder</strong> of <strong class="text-gray-700">UBXTY Unboxing Technology</strong> and a software engineer with 10+ years of experience in web, mobile, and cloud development. Leads the vision, planning, and technical implementation with deep expertise in Laravel, Flutter, and scalable product architecture.
                </p>
                {{-- LinkedIn --}}
                <a href="https://www.linkedin.com/in/ravdeep-singh-a4544abb/" target="_blank" rel="noopener noreferrer" class="team-linkedin mt-auto">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    LinkedIn
                </a>
            </div>

            {{-- Card 2: Jinia Chhabra --}}
            <div class="team-card bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col items-center text-center" style="transition-delay:0.12s">
                {{-- Avatar --}}
                <div class="team-avatar-ring mb-4" style="background:linear-gradient(135deg,#ec4899,#f472b6,#fb7185);">
                    <img src="{{ asset('images/profile/jinia.jpeg') }}" alt="Jinia Chhabra" class="w-[84px] h-[84px] rounded-full object-cover">
                </div>
                {{-- Name & Role --}}
                <h3 class="text-base font-extrabold text-gray-900 mb-1">Jinia Chhabra</h3>
                <span class="inline-block px-3 py-0.5 rounded-full text-xs font-bold bg-pink-100 text-pink-700 mb-3 uppercase tracking-wide">Development &amp; Documentation</span>
                {{-- Description --}}
                <p class="text-xs text-gray-500 leading-relaxed mb-4">
                    <strong class="text-gray-700">Intern</strong> at <strong class="text-gray-700">UBXTY Unboxing Technology</strong>. Contributes to development, documentation, media preparation, and presentation design — bridging the gap between technical ideas and clear communication.
                </p>
                {{-- LinkedIn --}}
                <a href="https://www.linkedin.com/in/jinia-chhabra-84b123235/" target="_blank" rel="noopener noreferrer" class="team-linkedin mt-auto" style="color:#db2777; border-color:rgba(219,39,119,0.25);">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    LinkedIn
                </a>
            </div>

            {{-- Card 3: Sahil Sethi --}}
            <div class="team-card bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col items-center text-center sm:col-span-2 lg:col-span-1" style="transition-delay:0.24s">
                {{-- Avatar --}}
                <div class="team-avatar-ring mb-4" style="background:linear-gradient(135deg,#0ea5e9,#38bdf8,#22d3ee);">
                    <img src="{{ asset('images/profile/vansh.jpeg') }}" alt="Vansh Sethi" class="w-[84px] h-[84px] rounded-full object-cover">
                </div>
                {{-- Name & Role --}}
                <h3 class="text-lg font-extrabold text-gray-900 mb-1">Sahil Sethi</h3>
                <span class="inline-block px-3 py-0.5 rounded-full text-xs font-bold bg-sky-100 text-sky-700 mb-4 uppercase tracking-wide">Testing &amp; Quality Assurance</span>
                {{-- Description --}}
                <p class="text-xs text-gray-500 leading-relaxed mb-4">
                 <strong class="text-gray-700">Intern</strong> at <strong class="text-gray-700">UBXTY Unboxing Technology</strong>  . Focuses on testing and quality assurance to ensure the platform works smoothly and delivers a reliable, bug-free user experience.
                </p>
                <a href="https://www.linkedin.com/in/sahil-sethi-3b8a14374" target="_blank" rel="noopener noreferrer" class="team-linkedin mt-auto" style="color:#0ea5e9; border-color:rgba(14,165,233,0.25);">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    LinkedIn
                </a>
            </div>

        </div>
    </div>
</section>

{{-- ═════ JS: animate team cards on scroll ═════ --}}
<script>
(function(){
    var cards = document.querySelectorAll('.team-card');
    if(!cards.length) return;
    var io = new IntersectionObserver(function(entries){
        entries.forEach(function(e){
            if(e.isIntersecting){ e.target.classList.add('tc-visible'); io.unobserve(e.target); }
        });
    }, { threshold: 0.15 });
    cards.forEach(function(c){ io.observe(c); });
})();
</script>

{{-- ═══════════ FOOTER ═══════════ --}}
<footer style="background:linear-gradient(to right,rgba(124,58,237,0.08),rgba(59,130,246,0.08)); border-top:1px solid rgba(255,255,255,0.15); padding:48px 0;">
    <div class="max-w-[1200px] mx-auto px-6">

        {{-- Three-column grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-10">

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
                    <span class="text-sm text-gray-600">Hormone Prediction Engine Active</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="status-dot-delay  inline-block w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0"></span>
                    <span class="text-sm text-gray-600">12 Simulation Capabilities Online</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="status-dot-delay2 inline-block w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0"></span>
                    <span class="text-sm text-gray-600">Real-Time Alert Broadcasting Active</span>
                </div>
            </div>
        </div>

        {{-- Gradient divider --}}
        <div style="height:1px; background:linear-gradient(to right,#7C3AED,#A78BFA); border-radius:1px; margin-bottom:24px;"></div>

        {{-- Bottom bar --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-400">
            <p>&copy; 2026 <span class="logo-gradient font-semibold">HormoneLens AI</span></p>

            {{-- Powered by AWS + Cloudpanzer --}}
            <div class="flex flex-col sm:flex-row items-center gap-3 sm:gap-4">
                {{-- AWS Badge --}}
                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 border border-amber-200">
                    <svg class="h-3.5 w-auto" viewBox="0 0 100 60" xmlns="http://www.w3.org/2000/svg">
                        <text x="50" y="28" font-family="Arial" font-size="16" font-weight="bold" fill="#FF9900" text-anchor="middle">amazon</text>
                        <text x="50" y="50" font-family="Arial" font-size="13" font-weight="bold" fill="#FF9900" text-anchor="middle">web services</text>
                    </svg>
                    <span class="text-amber-700 font-semibold text-[10px]">Powered by AWS</span>
                </div>

                {{-- Cloudpanzer Badge with Tooltip --}}
                <div class="cp-tooltip">
                    <a href="https://cloudpanzer.com/" target="_blank" rel="noopener noreferrer"
                       class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200 hover:border-violet-300 hover:bg-violet-50 transition-colors duration-200">
                        <span class="text-[10px]">🚀</span>
                        <span class="text-slate-600 font-semibold text-[10px] hover:text-violet-700">Deployed via Cloudpanzer</span>
                    </a>
                    <span class="cp-tip">
                        <strong class="text-amber-300">🚀 Cloudpanzer</strong><br>
                        One-click deployments of your GitHub repositories — straight to production.<br>
                        <span class="text-slate-300">World-class EC2 fleet management, auto-scaling, and zero-downtime deployments. Built for developers who ship fast.</span>
                    </span>
                </div>
            </div>

            <p class="tracking-wide">Metabolic Simulation Platform</p>
        </div>

    </div>
</footer>

{{-- ═══════════════════════════════════════════════════════════
     ANVI — AI HEALTH CHATBOT WIDGET
═══════════════════════════════════════════════════════════ --}}
<style>
/* ── Chat bubble ── */
#anvi-bubble {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #818cf8);
    box-shadow: 0 4px 20px rgba(99,102,241,0.4);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: none;
    outline: none;
}
#anvi-bubble:hover { transform: scale(1.08); box-shadow: 0 6px 24px rgba(99,102,241,0.5); }
#anvi-bubble svg { width: 24px; height: 24px; color: #fff; }

/* ── Chat window ── */
#anvi-window {
    position: fixed;
    bottom: 84px;
    right: 24px;
    z-index: 9998;
    width: 360px;
    max-width: calc(100vw - 32px);
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 12px 48px rgba(0,0,0,0.15), 0 2px 8px rgba(99,102,241,0.12);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid rgba(99,102,241,0.15);
    transform: translateY(12px);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.22s ease, transform 0.22s ease;
    max-height: 520px;
}
#anvi-window.anvi-open {
    opacity: 1;
    transform: translateY(0);
    pointer-events: all;
}

/* ── Header ── */
#anvi-header {
    background: linear-gradient(135deg, #6366f1, #818cf8);
    padding: 14px 16px;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}
.anvi-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255,255,255,0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.anvi-header-info { flex: 1; min-width: 0; }
.anvi-header-info strong { display: block; color: #fff; font-size: 13.5px; font-weight: 700; line-height: 1.2; }
.anvi-header-info span { color: rgba(255,255,255,0.78); font-size: 11px; }
#anvi-close {
    background: rgba(255,255,255,0.18);
    border: none;
    border-radius: 8px;
    width: 28px;
    height: 28px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    flex-shrink: 0;
    transition: background 0.15s;
}
#anvi-close:hover { background: rgba(255,255,255,0.3); }

/* ── Messages ── */
#anvi-messages {
    flex: 1;
    overflow-y: auto;
    padding: 14px 14px 8px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    scroll-behavior: smooth;
}
#anvi-messages::-webkit-scrollbar { width: 4px; }
#anvi-messages::-webkit-scrollbar-track { background: transparent; }
#anvi-messages::-webkit-scrollbar-thumb { background: rgba(99,102,241,0.2); border-radius: 4px; }

.anvi-msg {
    max-width: 88%;
    font-size: 13px;
    line-height: 1.55;
    padding: 9px 13px;
    border-radius: 14px;
    word-break: break-word;
}
.anvi-msg.bot {
    background: #f3f4ff;
    color: #1e1b4b;
    align-self: flex-start;
    border-bottom-left-radius: 4px;
}
.anvi-msg.user {
    background: linear-gradient(135deg, #6366f1, #818cf8);
    color: #fff;
    align-self: flex-end;
    border-bottom-right-radius: 4px;
}

/* ── Typing dots ── */
.anvi-typing {
    display: flex;
    gap: 4px;
    padding: 10px 13px;
    background: #f3f4ff;
    border-radius: 14px;
    border-bottom-left-radius: 4px;
    align-self: flex-start;
    align-items: center;
}
.anvi-typing span {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #6366f1;
    animation: anvi-bounce 1.2s infinite;
    display: block;
}
.anvi-typing span:nth-child(2) { animation-delay: 0.2s; }
.anvi-typing span:nth-child(3) { animation-delay: 0.4s; }
@keyframes anvi-bounce {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.5; }
    30% { transform: translateY(-5px); opacity: 1; }
}

/* ── Input area ── */
#anvi-footer {
    padding: 10px 12px 12px;
    border-top: 1px solid #f0f0f5;
    display: flex;
    gap: 8px;
    flex-shrink: 0;
    background: #fff;
}
#anvi-input {
    flex: 1;
    border: 1.5px solid #e0e0ee;
    border-radius: 10px;
    padding: 8px 12px;
    font-size: 13px;
    outline: none;
    color: #1e1b4b;
    background: #fafaff;
    resize: none;
    min-height: 38px;
    max-height: 80px;
    line-height: 1.5;
    transition: border-color 0.15s;
    font-family: inherit;
}
#anvi-input:focus { border-color: #6366f1; background: #fff; }
#anvi-input::placeholder { color: #a5a5bd; }
#anvi-send {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: linear-gradient(135deg, #6366f1, #818cf8);
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: opacity 0.15s, transform 0.15s;
    align-self: flex-end;
}
#anvi-send:hover { opacity: 0.88; transform: scale(1.05); }
#anvi-send:disabled { opacity: 0.45; cursor: default; transform: none; }
#anvi-send svg { width: 16px; height: 16px; color: #fff; }
</style>

{{-- Bubble button --}}
<button id="anvi-bubble" aria-label="Chat with Anvi">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
    </svg>
</button>

{{-- Chat window --}}
<div id="anvi-window" role="dialog" aria-label="Anvi AI Health Assistant">
    <div id="anvi-header">
        <div class="anvi-avatar">🌸</div>
        <div class="anvi-header-info">
            <strong>Anvi</strong>
            <span>HormoneLens AI · Health Assistant</span>
        </div>
        <button id="anvi-close" aria-label="Close chat">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M1 1l12 12M13 1L1 13"/>
            </svg>
        </button>
    </div>

    <div id="anvi-messages" aria-live="polite"></div>

    <div id="anvi-footer">
        <textarea id="anvi-input" rows="1" placeholder="Ask about hormones, PCOS, sleep…" maxlength="1000"></textarea>
        <button id="anvi-send" aria-label="Send message">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
        </button>
    </div>
</div>

<script>
(function () {
    var bubble   = document.getElementById('anvi-bubble');
    var win      = document.getElementById('anvi-window');
    var closeBtn = document.getElementById('anvi-close');
    var msgs     = document.getElementById('anvi-messages');
    var input    = document.getElementById('anvi-input');
    var sendBtn  = document.getElementById('anvi-send');
    var isOpen   = false;
    var busy     = false;
    var greeted  = false;

    var INTRO = "Hi, I'm **Anvi**, the AI assistant for HormoneLens 👋\n\nYou can ask me questions about hormones, PCOS, insulin resistance, metabolism, sleep, stress, or lifestyle health.\n\nI'll do my best to explain things in simple and helpful ways.";

    /* ── helpers ── */
    function md(text) {
        // minimal markdown: **bold** and newlines
        return text
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\n/g, '<br>');
    }

    function appendMsg(text, role) {
        var div = document.createElement('div');
        div.className = 'anvi-msg ' + role;
        div.innerHTML = md(text);
        msgs.appendChild(div);
        msgs.scrollTop = msgs.scrollHeight;
        return div;
    }

    function showTyping() {
        var t = document.createElement('div');
        t.className = 'anvi-typing';
        t.innerHTML = '<span></span><span></span><span></span>';
        t.id = 'anvi-typing-indicator';
        msgs.appendChild(t);
        msgs.scrollTop = msgs.scrollHeight;
    }

    function removeTyping() {
        var t = document.getElementById('anvi-typing-indicator');
        if (t) t.remove();
    }

    /* ── open / close ── */
    function openChat() {
        isOpen = true;
        win.classList.add('anvi-open');
        if (!greeted) {
            greeted = true;
            appendMsg(INTRO, 'bot');
        }
        setTimeout(function () { input.focus(); }, 250);
    }

    function closeChat() {
        isOpen = false;
        win.classList.remove('anvi-open');
    }

    bubble.addEventListener('click', function () {
        isOpen ? closeChat() : openChat();
    });
    closeBtn.addEventListener('click', closeChat);

    /* ── auto-resize textarea ── */
    input.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 80) + 'px';
    });

    /* ── send message ── */
    function sendMessage() {
        var text = input.value.trim();
        if (!text || busy) return;

        appendMsg(text, 'user');
        input.value = '';
        input.style.height = 'auto';
        busy = true;
        sendBtn.disabled = true;
        showTyping();

        fetch('/api/anvi/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ message: text }),
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            removeTyping();
            appendMsg(data.reply || "Sorry, I couldn't get a response. Please try again!", 'bot');
        })
        .catch(function () {
            removeTyping();
            appendMsg("Something went wrong. Please check your connection and try again 🙏", 'bot');
        })
        .finally(function () {
            busy = false;
            sendBtn.disabled = false;
            input.focus();
        });
    }

    sendBtn.addEventListener('click', sendMessage);
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
})();
</script>

</body>
</html>
