<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — HormoneLens</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: { extend: { colors: {
            brand: { 50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',
                     400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca' }
        }}}
    }
    </script>
    <style>
        * { box-sizing: border-box; }

        /* ── Layout ── */
        html, body { height: 100%; margin: 0; }
        .split-screen {
            display: flex;
            min-height: 100vh;
        }
        .left-panel  { width: 55%; position: relative; overflow: hidden; }
        .right-panel {
            width: 45%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f7ff;
            padding: 40px 32px;
        }
        @media (max-width: 768px) {
            .left-panel  { display: none; }
            .right-panel { width: 100%; background: linear-gradient(135deg,#ede9fe,#dbeafe); }
        }

        /* ── Left panel background ── */
        .left-panel-bg {
            position: absolute; inset: 0;
            background: linear-gradient(155deg,
                #f3f0ff 0%,
                #ede9fe 35%,
                #dde8ff 70%,
                #e8f0fe 100%);
        }

        /* ── Floating particles ── */
        @keyframes floatParticle {
            0%,100% { transform: translate(0,0) scale(1);   opacity: 0.18; }
            50%      { transform: translate(20px,-30px) scale(1.08); opacity: 0.28; }
        }
        .lp-particle {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            pointer-events: none;
            animation: floatParticle var(--dur,10s) ease-in-out var(--delay,0s) infinite;
        }
        .lp-p1 { width:280px;height:280px; background:linear-gradient(135deg,#a78bfa,#818cf8); top:-60px;left:-60px; --dur:10s;--delay:0s; }
        .lp-p2 { width:200px;height:200px; background:linear-gradient(135deg,#6366f1,#c084fc); bottom:-40px;right:-30px; --dur:13s;--delay:2s; }
        .lp-p3 { width:150px;height:150px; background:linear-gradient(135deg,#93c5fd,#a5b4fc); bottom:30%;left:20%; --dur:16s;--delay:4s; }

        /* ── Welcome badge ── */
        @keyframes welcomeFloat {
            0%   { transform: translateX(calc(-50% - 30px)); opacity: 0.7; }
            50%  { transform: translateX(calc(-50% + 30px)); opacity: 1; }
            100% { transform: translateX(calc(-50% - 30px)); opacity: 0.7; }
        }
        .welcome-badge {
            position: absolute;
            top: 36px;
            left: 50%;
            display: inline-block;
            white-space: nowrap;
            z-index: 8;
            background: rgba(255,255,255,0.35);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.5);
            border-radius: 999px;
            padding: 8px 22px;
            font-size: 13px;
            font-weight: 500;
            color: #4338ca;
            animation: welcomeFloat 6s ease-in-out infinite;
        }

        /* ── Pulse ring behind silhouette ── */
        @keyframes pulseRing {
            0%   { transform: translate(-50%,-50%) scale(1);   opacity: 0.25; }
            50%  { transform: translate(-50%,-50%) scale(1.1); opacity: 0.10; }
            100% { transform: translate(-50%,-50%) scale(1);   opacity: 0.25; }
        }
        .pulse-ring {
            position: absolute;
            top: 50%; left: 50%;
            width: 280px; height: 280px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(124,58,237,0.35) 0%, transparent 70%);
            animation: pulseRing 4s ease-in-out infinite;
            pointer-events: none;
            z-index: 1;
        }

        /* ── Mini floating up-down particles ── */
        @keyframes floatUpDown {
            0%,100% { transform: translateY(0px);   opacity: 0.55; }
            50%      { transform: translateY(-12px); opacity: 0.85; }
        }
        .mini-particle {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            animation: floatUpDown var(--mp-dur,6s) ease-in-out var(--mp-delay,0s) infinite;
        }

        /* ── Scanning light sweep ── */
        @keyframes scanLight {
            0%   { background-position: -300px center; }
            100% { background-position: calc(100% + 300px) center; }
        }
        .scan-light {
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 5;
            background: linear-gradient(
                90deg,
                transparent 0%,
                rgba(124,58,237,0.10) 45%,
                rgba(167,139,250,0.15) 50%,
                rgba(124,58,237,0.10) 55%,
                transparent 100%
            );
            background-size: 600px 100%;
            background-repeat: no-repeat;
            animation: scanLight 5s linear infinite;
        }

        /* ── Horizontal scan lines overlay ── */
        @keyframes scanLines {
            0%   { background-position: 0px 0px; }
            100% { background-position: 0px 120px; }
        }
        .scan-lines {
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 2;
            background: repeating-linear-gradient(
                180deg,
                transparent,
                transparent 6px,
                rgba(124,58,237,0.05) 7px
            );
            animation: scanLines 6s linear infinite;
        }

        /* ── AI glow particles ── */
        @keyframes aiParticleFloat {
            0%   { transform: translateY(0px);   opacity: 0.6; }
            50%  { transform: translateY(-12px); opacity: 1;   }
            100% { transform: translateY(0px);   opacity: 0.6; }
        }
        .ai-particle {
            position: absolute;
            width: 6px;
            height: 6px;
            background: rgba(124,58,237,0.4);
            border-radius: 50%;
            pointer-events: none;
            z-index: 2;
            animation: aiParticleFloat 5s ease-in-out var(--ap-delay,0s) infinite;
        }

        /* ── Floating tags ── */
        @keyframes floatTag {
            0%   { transform: translateY(0px); }
            50%  { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        .float-tag {
            position: absolute;
            background: rgba(255,255,255,0.40);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.40);
            border-radius: 999px;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 500;
            color: #4c1d95;
            white-space: nowrap;
            z-index: 1;
            pointer-events: none;
            animation: floatTag 8s ease-in-out var(--tf-delay,0s) infinite;
        }

        /* ── Anatomical body figure ── */
        #body-figure {
            position: absolute;
            top: 50%; left: 50%;
            transform: translateX(-50%) translateY(-50%);
            width: 140px;
            height: auto;
            z-index: 2;
            pointer-events: none;
            transform-origin: center top;
            animation: figBreath 4s ease-in-out infinite;
        }
        @keyframes figBreath {
            0%,100% { transform: translateX(-50%) translateY(-50%) scaleY(1);    }
            50%      { transform: translateX(-50%) translateY(-50%) scaleY(1.014); }
        }

        /* Part base + glow class */
        .bfig-part {
            transition: filter 0.3s ease;
        }
        .bfig-part.bfig-glow {
            filter: drop-shadow(0 0 8px rgba(124,58,237,0.7));
        }

        /* HEAD tilt */
        @keyframes bHeadTilt {
            0%,100% { transform: rotate(0deg); }
            25%      { transform: rotate(-10deg); }
            70%      { transform: rotate(7deg); }
        }
        #bfig-head.bpm {
            transform-box: fill-box; transform-origin: bottom center;
            animation: bHeadTilt 0.9s ease-in-out forwards;
        }

        /* LEFT ARM wave (pivot at shoulder = top-right of arm bounding box) */
        @keyframes bLArmWave {
            0%   { transform: rotate(0deg); }
            25%  { transform: rotate(-32deg); }
            75%  { transform: rotate(14deg); }
            100% { transform: rotate(0deg); }
        }
        #bfig-larm.bpm {
            transform-box: fill-box; transform-origin: top right;
            animation: bLArmWave 0.85s ease-in-out forwards;
        }

        /* RIGHT ARM wave */
        @keyframes bRArmWave {
            0%   { transform: rotate(0deg); }
            25%  { transform: rotate(32deg); }
            75%  { transform: rotate(-14deg); }
            100% { transform: rotate(0deg); }
        }
        #bfig-rarm.bpm {
            transform-box: fill-box; transform-origin: top left;
            animation: bRArmWave 0.85s ease-in-out forwards;
        }

        /* TORSO pulse */
        @keyframes bTorsoPulse {
            0%,100% { filter: drop-shadow(0 0 3px rgba(124,58,237,0.25)); }
            50%      { filter: drop-shadow(0 0 16px rgba(124,58,237,0.65)); }
        }
        #bfig-torso.bpm, #bfig-hips.bpm { animation: bTorsoPulse 0.85s ease-in-out forwards; }

        /* LEFT LEG step (pivot at hip = top center) */
        @keyframes bLLegStep {
            0%,100% { transform: rotate(0deg); }
            30%      { transform: rotate(-15deg); }
            70%      { transform: rotate(6deg); }
        }
        #bfig-lleg.bpm {
            transform-box: fill-box; transform-origin: top center;
            animation: bLLegStep 0.85s ease-in-out forwards;
        }

        /* RIGHT LEG step */
        @keyframes bRLegStep {
            0%,100% { transform: rotate(0deg); }
            30%      { transform: rotate(15deg); }
            70%      { transform: rotate(-6deg); }
        }
        #bfig-rleg.bpm {
            transform-box: fill-box; transform-origin: top center;
            animation: bRLegStep 0.85s ease-in-out forwards;
        }

        /* ── Body hover zones ── */
        .body-zone {
            position: absolute;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: transparent;
            cursor: pointer;
            z-index: 3;
        }
        .body-zone:hover { background: rgba(124,58,237,0.07); }

        /* ── Body tooltip ── */
        #body-tooltip {
            position: absolute;
            padding: 6px 12px;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            color: #4c1d95;
            font-size: 12px;
            font-weight: 600;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            z-index: 20;
            transition: opacity 0.25s ease, transform 0.25s ease;
            transform: translateY(4px);
        }
        #body-tooltip.tt-show {
            opacity: 1;
            transform: translateY(0);
        }

        /* ── Cloud blobs ── */
        @keyframes cloudFloat {
            0%   { transform: translateY(0px); }
            50%  { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        .cloud-blob {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(124,58,237,0.18), transparent);
            filter: blur(45px);
            opacity: 0.6;
            z-index: 0;
            pointer-events: none;
            animation: cloudFloat var(--cb-dur,8s) ease-in-out var(--cb-delay,0s) infinite;
        }

        /* ── Left copy ── */
        .left-copy {
            position: absolute;
            bottom: 52px; left: 48px; right: 48px;
            z-index: 15;
        }

        /* ── Right: login box entrance ── */
        @keyframes loginSlideIn {
            from { opacity:0; transform:translateX(40px); }
            to   { opacity:1; transform:translateX(0); }
        }
        .login-box {
            width: 100%;
            max-width: 420px;
            background: rgba(255,255,255,0.75);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-radius: 20px;
            padding: 44px 40px;
            box-shadow: 0 8px 40px rgba(124,58,237,0.12), 0 1px 0 rgba(255,255,255,0.6) inset;
            border: 1px solid rgba(255,255,255,0.55);
            animation: loginSlideIn 0.6s ease-out both;
        }

        /* ── Inputs ── */
        .hl-input {
            width: 100%;
            padding: 11px 16px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            background: rgba(255,255,255,0.8);
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
            color: #1f2937;
        }
        .hl-input:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124,58,237,0.12);
            background: rgba(255,255,255,1);
        }

        /* ── Submit button ── */
        @keyframes btnGlow {
            0%,100% { box-shadow: 0 4px 14px rgba(124,58,237,0.25); }
            50%      { box-shadow: 0 4px 22px rgba(124,58,237,0.45); }
        }
        .hl-btn {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #7c3aed, #6366f1);
            color: #fff;
            font-weight: 700;
            font-size: 15px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            animation: btnGlow 2s ease-in-out infinite;
        }
        .hl-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(124,58,237,0.4);
        }
    </style>
</head>
<body>

<div class="split-screen">

    {{-- ═══ LEFT PANEL ═══ --}}
    <div class="left-panel">
        <div class="left-panel-bg"></div>

        {{-- Scanning light sweep --}}
        <div class="scan-light"></div>

        {{-- Large blurred background particles --}}
        <div class="lp-particle lp-p1"></div>
        <div class="lp-particle lp-p2"></div>
        <div class="lp-particle lp-p3"></div>

        {{-- Extra cloud blobs for depth --}}
        <div class="cloud-blob" style="width:160px;height:160px;top:6%;left:-20px;    --cb-dur:9s; --cb-delay:0s;"></div>
        <div class="cloud-blob" style="width:180px;height:180px;top:38%;right:-25px;  --cb-dur:11s;--cb-delay:3s;"></div>
        <div class="cloud-blob" style="width:160px;height:160px;bottom:8%;left:8%;   --cb-dur:8s; --cb-delay:1.5s;"></div>

        {{-- Mini floating up-down particles --}}
        <div class="mini-particle" style="width:10px;height:10px;background:#a78bfa;top:35%;left:15%;  --mp-dur:6s;--mp-delay:0s;"></div>
        <div class="mini-particle" style="width:7px; height:7px; background:#818cf8;top:42%;right:18%;--mp-dur:7s;--mp-delay:1s;"></div>
        <div class="mini-particle" style="width:9px; height:9px; background:#c084fc;top:58%;left:28%; --mp-dur:5s;--mp-delay:2s;"></div>
        <div class="mini-particle" style="width:6px; height:6px; background:#6366f1;top:65%;right:22%;--mp-dur:8s;--mp-delay:0.5s;"></div>
        <div class="mini-particle" style="width:8px; height:8px; background:#93c5fd;top:28%;left:42%; --mp-dur:9s;--mp-delay:3s;"></div>
        <div class="mini-particle" style="width:5px; height:5px; background:#a5b4fc;top:72%;left:50%; --mp-dur:6.5s;--mp-delay:1.5s;"></div>

        {{-- AI glow particles (z-index:2, float up) --}}
        <div class="ai-particle" style="top:38%;left:30%; --ap-delay:0s;"></div>
        <div class="ai-particle" style="top:48%;right:28%;--ap-delay:1.2s;"></div>
        <div class="ai-particle" style="top:60%;left:38%; --ap-delay:2.5s;"></div>
        <div class="ai-particle" style="top:55%;right:35%;--ap-delay:3.8s;"></div>

        {{-- Welcome badge top-left --}}
        <div class="welcome-badge">✨ Welcome to Your Digital Hormone Lab</div>

        {{-- AI body scan lines overlay --}}
        <div class="scan-lines"></div>

        {{-- Pulse ring behind silhouette --}}
        <div class="pulse-ring"></div>

        {{-- Anatomical body figure SVG --}}
        {{-- Purple silhouette body — clean minimal style like reference --}}
        <svg id="body-figure" viewBox="0 0 100 300" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="bGrad" x1="50" y1="0" x2="50" y2="300" gradientUnits="userSpaceOnUse">
                    <stop offset="0%"   stop-color="#5b21b6" stop-opacity="0.85"/>
                    <stop offset="50%"  stop-color="#7c3aed" stop-opacity="0.78"/>
                    <stop offset="100%" stop-color="#8b5cf6" stop-opacity="0.68"/>
                </linearGradient>
            </defs>

            {{-- HEAD — solid round skull --}}
            <g id="bfig-head" class="bfig-part">
                <circle cx="50" cy="14" r="13.5"
                        fill="url(#bGrad)" stroke="rgba(167,139,250,0.55)" stroke-width="0.5"/>
            </g>

            {{-- NECK --}}
            <path d="M44.5 27 L55.5 27 L53.5 43 L46.5 43 Z"
                  fill="url(#bGrad)" stroke="rgba(167,139,250,0.4)" stroke-width="0.4"/>

            {{-- TORSO — V-taper: broad shoulders → narrow waist --}}
            <g id="bfig-torso" class="bfig-part">
                <path d="M46.5 43 L53.5 43
                         C 64 43 81 49 86 60
                         L 83 112
                         C 81 128 73 141 70 149
                         L 30 149
                         C 27 141 19 128 17 112
                         L 14 60
                         C 19 49 36 43 46.5 43 Z"
                      fill="url(#bGrad)" stroke="rgba(167,139,250,0.50)" stroke-width="0.5"/>
            </g>

            {{-- HIPS / PELVIS --}}
            <g id="bfig-hips" class="bfig-part">
                <path d="M28 149 L72 149
                         C 80 153 85 166 81 178
                         L 19 178
                         C 15 166 20 153 28 149 Z"
                      fill="url(#bGrad)" stroke="rgba(167,139,250,0.45)" stroke-width="0.5"/>
            </g>

            {{-- LEFT ARM — hangs at side with gap from torso --}}
            <g id="bfig-larm" class="bfig-part">
                {{-- Upper arm --}}
                <path d="M11 58
                         C  5 74  2 102  3 120
                         L 15 119
                         C 14  100 16  75 21 62 Z"
                      fill="url(#bGrad)" stroke="rgba(167,139,250,0.50)" stroke-width="0.5"/>
                {{-- Elbow --}}
                <ellipse cx="9" cy="120" rx="7" ry="5.5"
                         fill="url(#bGrad)" stroke="rgba(167,139,250,0.40)" stroke-width="0.4"/>
                {{-- Forearm --}}
                <path d="M2 120
                         C  0 139  0 156  2 168
                         L 16 166
                         C 15 153 13 136 15 120 Z"
                      fill="url(#bGrad)" stroke="rgba(167,139,250,0.50)" stroke-width="0.5"/>
                {{-- Hand --}}
                <ellipse cx="9" cy="173" rx="7.5" ry="9.5"
                         fill="url(#bGrad)" stroke="rgba(167,139,250,0.45)" stroke-width="0.5"/>
            </g>

            {{-- RIGHT ARM --}}
            <g id="bfig-rarm" class="bfig-part">
                <path d="M89 58
                         C 95 74 98 102 97 120
                         L 85 119
                         C 84 100 84  75 79 62 Z"
                      fill="url(#bGrad)" stroke="rgba(167,139,250,0.50)" stroke-width="0.5"/>
                <ellipse cx="91" cy="120" rx="7" ry="5.5"
                         fill="url(#bGrad)" stroke="rgba(167,139,250,0.40)" stroke-width="0.4"/>
                <path d="M98 120
                         C 100 139 100 156  98 168
                         L  84 166
                         C  85 153  87 136  85 120 Z"
                      fill="url(#bGrad)" stroke="rgba(167,139,250,0.50)" stroke-width="0.5"/>
                <ellipse cx="91" cy="173" rx="7.5" ry="9.5"
                         fill="url(#bGrad)" stroke="rgba(167,139,250,0.45)" stroke-width="0.5"/>
            </g>

            {{-- LEFT LEG — thigh · knee · calf · foot --}}
            <g id="bfig-lleg" class="bfig-part">
                {{-- Thigh --}}
                <path d="M19 178
                         C 13 202 11 232 13 252
                         L 30 250
                         C 28 230 29 202 35 178 Z"
                      fill="url(#bGrad)" stroke="rgba(167,139,250,0.50)" stroke-width="0.5"/>
                {{-- Knee --}}
                <ellipse cx="21.5" cy="252" rx="11" ry="8.5"
                         fill="url(#bGrad)" stroke="rgba(167,139,250,0.40)" stroke-width="0.4"/>
                {{-- Calf --}}
                <path d="M11 252
                         C  9 272  9 284 11 295
                         L 31 294
                         C 30 283 29 271 31 252 Z"
                      fill="url(#bGrad)" stroke="rgba(167,139,250,0.50)" stroke-width="0.5"/>
                {{-- Foot --}}
                <path d="M9 294 C  6 298  4 300  5 300
                         L 34 300 C 36 298 35 294 31 294 Z"
                      fill="url(#bGrad)" stroke="rgba(167,139,250,0.45)" stroke-width="0.5"/>
            </g>

            {{-- RIGHT LEG --}}
            <g id="bfig-rleg" class="bfig-part">
                <path d="M81 178
                         C 87 202 89 232 87 252
                         L 70 250
                         C 72 230 71 202 65 178 Z"
                      fill="url(#bGrad)" stroke="rgba(167,139,250,0.50)" stroke-width="0.5"/>
                <ellipse cx="78.5" cy="252" rx="11" ry="8.5"
                         fill="url(#bGrad)" stroke="rgba(167,139,250,0.40)" stroke-width="0.4"/>
                <path d="M89 252
                         C 91 272 91 284 89 295
                         L 69 294
                         C 70 283 71 271 69 252 Z"
                      fill="url(#bGrad)" stroke="rgba(167,139,250,0.50)" stroke-width="0.5"/>
                <path d="M91 294 C 94 298 96 300 95 300
                         L 66 300 C 64 298 65 294 69 294 Z"
                      fill="url(#bGrad)" stroke="rgba(167,139,250,0.45)" stroke-width="0.5"/>
            </g>
        </svg>


        {{-- Body tooltip --}}
        <div id="body-tooltip"></div>

        {{-- Hover zones (140px wide × 399px tall, centred at 50%/50%) --}}
        {{-- Scale: 140/100=1.4; half-height=200px; zone_top = (SVG_y * 1.4) - 200 - 24 --}}
        {{-- Head cy=19 → 26.6 - 200 - 24 = -197 --}}
        <div class="body-zone" data-zone="head" data-label="Hormonal Regulation"
             style="left:calc(50% - 24px); top:calc(50% - 197px);"></div>
        {{-- Chest abs-row y≈80 → 112 - 200 - 24 = -112 --}}
        <div class="body-zone" data-zone="chest" data-label="Thyroid Activity"
             style="left:calc(50% - 24px); top:calc(50% - 112px);"></div>
        {{-- Abdomen navel y≈120 → 168 - 200 - 24 = -56 --}}
        <div class="body-zone" data-zone="abdomen" data-label="Insulin Resistance"
             style="left:calc(50% - 24px); top:calc(50% - 56px);"></div>
        {{-- Hips y≈158 → 221 - 200 - 24 = -3 --}}
        <div class="body-zone" data-zone="hips" data-label="Metabolic Fatigue"
             style="left:calc(50% - 24px); top:calc(50% - 3px);"></div>
        {{-- Thigh y≈200 → 280 - 200 - 24 = 56 --}}
        <div class="body-zone" data-zone="legs" data-label="Physical Activity Impact"
             style="left:calc(50% - 24px); top:calc(50% + 56px);"></div>

        {{-- Floating health tags — 6 pills, z-index:1 --}}
        <span class="float-tag" style="top:14%;left:5%;   --tf-delay:0s;">💤 Sleep Pattern</span>
        <span class="float-tag" style="top:22%;right:5%;  --tf-delay:1.2s;">🦵 Hormonal Balance</span>
        <span class="float-tag" style="top:38%;left:4%;   --tf-delay:2.4s;">🧠 Stress Levels</span>
        <span class="float-tag" style="top:52%;right:4%;  --tf-delay:3.6s;">🥗 Diet Impact</span>
        <span class="float-tag" style="top:68%;left:5%;   --tf-delay:4.8s;">💉 Insulin Sensitivity</span>
        <span class="float-tag" style="top:78%;right:6%;  --tf-delay:6s;">📊 Glucose Response</span>
    </div>

    {{-- ═══ RIGHT PANEL ═══ --}}
    <div class="right-panel">
        <div class="login-box">

            {{-- Brand header --}}
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-2 mb-2">
                    <span class="text-2xl">🔬</span>
                    <span class="text-xl font-extrabold"
                          style="background:linear-gradient(90deg,#7c3aed,#6366f1);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                        HormoneLens
                    </span>
                </div>
                <p class="text-xs text-gray-400 font-semibold tracking-widest uppercase">Metabolic Health Simulation Platform</p>
            </div>

            <h2 class="text-xl font-bold text-gray-800 mb-6">Sign in to your account</h2>

            {{-- Errors --}}
            @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl">
                <ul class="text-sm text-red-600 space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(session('status'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-600">
                {{ session('status') }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                           class="hl-input" placeholder="you@example.com">
                </div>
                <div>
                    <label for="password" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Password</label>
                    <input id="password" name="password" type="password" required
                           class="hl-input" placeholder="••••••••">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="remember" id="remember"
                           class="w-4 h-4 rounded border-gray-300 text-violet-600 focus:ring-violet-500">
                    <label for="remember" class="text-sm text-gray-500">Remember me</label>
                </div>
                <button type="submit" class="hl-btn">
                    Sign In to Simulation Lab
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-500">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-semibold text-violet-600 hover:text-violet-700 transition-colors">
                    Create Simulation Profile
                </a>
            </p>
        </div>
    </div>

</div>

<script>
(function () {
    var tip   = document.getElementById('body-tooltip');
    var panel = document.querySelector('.left-panel');

    /* zone key → which body-part ids get movement (.bpm) and glow (.bfig-glow) */
    var zoneMap = {
        head:    { move: ['bfig-head'],                              glow: ['bfig-head'] },
        chest:   { move: ['bfig-larm','bfig-rarm','bfig-torso'],     glow: ['bfig-torso','bfig-larm','bfig-rarm'] },
        abdomen: { move: ['bfig-torso','bfig-hips'],                 glow: ['bfig-torso','bfig-hips'] },
        hips:    { move: ['bfig-hips','bfig-lleg','bfig-rleg'],      glow: ['bfig-hips'] },
        legs:    { move: ['bfig-lleg','bfig-rleg'],                  glow: ['bfig-lleg','bfig-rleg'] }
    };

    document.querySelectorAll('.body-zone').forEach(function (zone) {
        zone.addEventListener('mouseenter', function () {
            var info = zoneMap[this.dataset.zone] || {};

            /* tooltip */
            tip.textContent = this.dataset.label || '';
            tip.classList.add('tt-show');
            positionTip(this);

            /* glow */
            (info.glow || []).forEach(function (id) {
                var el = document.getElementById(id);
                if (el) el.classList.add('bfig-glow');
            });

            /* movement: restart animation each hover */
            (info.move || []).forEach(function (id) {
                var el = document.getElementById(id);
                if (!el) return;
                el.classList.remove('bpm');
                void el.offsetWidth;          /* reflow — restarts animation */
                el.classList.add('bpm');
                el.addEventListener('animationend', function handler() {
                    el.classList.remove('bpm');
                    el.removeEventListener('animationend', handler);
                });
            });
        });

        zone.addEventListener('mousemove', function () { positionTip(this); });

        zone.addEventListener('mouseleave', function () {
            var info = zoneMap[this.dataset.zone] || {};
            tip.classList.remove('tt-show');
            (info.glow || []).forEach(function (id) {
                var el = document.getElementById(id);
                if (el) el.classList.remove('bfig-glow');
            });
        });
    });

    function positionTip(zone) {
        var pr   = panel.getBoundingClientRect();
        var zr   = zone.getBoundingClientRect();
        var top  = zr.top  - pr.top  - (tip.offsetHeight || 30) - 8;
        var left = zr.left - pr.left + zr.width / 2 - (tip.offsetWidth || 132) / 2;
        left = Math.max(8, Math.min(left, pr.width  - (tip.offsetWidth  || 132) - 8));
        top  = Math.max(8, Math.min(top,  pr.height - (tip.offsetHeight || 30)  - 8));
        tip.style.left = left + 'px';
        tip.style.top  = top  + 'px';
    }
})();
</script>
</body>
</html>
