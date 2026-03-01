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

        /* pulse-ring removed */

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

        /* ── Avatar: HormoneLens simulation figure (men.svg) ── */

        /* Wrapper: fills the entire left panel */
        #avatar-wrap {
            position: absolute;
            inset: 0;                  /* stretch to all four edges */
            z-index: 6;
            cursor: pointer;
        }

        /* Base body layer */
        #avatar-svg-body {
            width: 100%;
            height: 100%;
            display: block;
            pointer-events: none;
        }
        #avatar-svg-body svg {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: contain;
        }

        /* ── SVG gradient theming via CSS fill override ──
           Gradient: #5f6fff → #c24dff → #ff6ec7
           Defined in a hidden <svg><defs> block above the avatar in the HTML.
           fill: url(#hlThemeGrad) !important overrides every presentation-
           attribute fill on the imported SVG paths.                          */
        #avatar-svg-body svg path[fill="#FEFEFE"] {
            display: none !important;          /* hide white background rect */
        }
        #avatar-svg-body svg path,
        #avatar-svg-body svg rect,
        #avatar-svg-body svg ellipse,
        #avatar-svg-body svg circle,
        #avatar-svg-body svg line {
            fill:   url(#hlThemeGrad) !important;
            stroke: none !important;
        }

        /* ── Interactive body hotspots — dark purple pulsing dots ── */
        .hl-hotspot {
            position: absolute;
            width: 18px; height: 18px;
            transform: translate(-50%, -50%);
            cursor: pointer;
            z-index: 12;
            border-radius: 50%;
            background: #4c1d95;
            box-shadow: 0 0 0 0 rgba(76,29,149,0.6);
            animation: hsPulse 2.2s ease-out infinite;
            transition: background 0.18s ease, transform 0.18s ease;
        }
        /* Invisible 46px hit-area so hover triggers even if cursor is nearby */
        .hl-hotspot::before {
            content: '';
            position: absolute;
            inset: -14px;
            border-radius: 50%;
        }
        .hl-hotspot::after { content: none; }
        .hl-hotspot:hover {
            background: #6d28d9;
            transform: translate(-50%, -50%) scale(1.35);
        }
        @keyframes hsPulse {
            0%   { box-shadow: 0 0 0 0   rgba(76,29,149,0.65); }
            70%  { box-shadow: 0 0 0 12px rgba(76,29,149,0);   }
            100% { box-shadow: 0 0 0 0   rgba(76,29,149,0);    }
        }

        /* ── Body info tooltip card ── */
        #hl-tooltip {
            position: absolute;
            z-index: 20;
            width: 218px;
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(139,92,246,0.28);
            border-radius: 14px;
            padding: 13px 32px 12px 16px;
            box-shadow: 0 10px 32px rgba(124,58,237,0.22);
            pointer-events: none;
            opacity: 0;
            transform: scale(0.88) translateY(6px);
            transition: opacity 0.2s ease, transform 0.2s ease;
            transform-origin: top left;
        }
        #hl-tooltip.hl-visible {
            opacity: 1;
            pointer-events: auto;
            transform: scale(1) translateY(0);
        }
        #hl-tip-icon { font-size: 22px; margin-bottom: 4px; }
        #hl-tip-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #7c3aed;
            margin-bottom: 5px;
        }
        #hl-tip-body { font-size: 12px; line-height: 1.55; color: #374151; }
        #hl-tip-close {
            position: absolute; top: 8px; right: 10px;
            background: none; border: none; cursor: pointer;
            font-size: 17px; color: #9ca3af; line-height: 1; padding: 2px 4px;
        }
        #hl-tip-close:hover { color: #6b7280; }

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

        {{-- ── Hidden gradient defs: referenced by CSS `fill: url(#hlThemeGrad)` ── --}}
        <svg style="position:absolute;width:0;height:0;overflow:hidden" aria-hidden="true">
            <defs>
                <!-- Gradient tuned to the soft lavender background palette
                     #f3f0ff → #ede9fe → #dde8ff — keeps the figure harmonious
                     with the panel instead of clashing against it. -->
                <linearGradient id="hlThemeGrad" x1="0%" y1="0%" x2="60%" y2="100%">
                    <stop offset="0%"   stop-color="#7c3aed" stop-opacity="0.82"/>
                    <stop offset="50%"  stop-color="#8b5cf6" stop-opacity="0.75"/>
                    <stop offset="100%" stop-color="#a78bfa" stop-opacity="0.65"/>
                </linearGradient>
            </defs>
        </svg>

        {{-- ── Avatar: men.svg with theme gradient + right-arm wave ── --}}
        {{-- Gradient applied via CSS fill:url(#hlThemeGrad) — no SVG path edits --}}
        @php
            $svgRaw = file_get_contents(public_path('images/men.svg'));
            // Strip XML declaration
            $svgRaw = preg_replace('/<\?xml[^?]*\?>\s*/', '', $svgRaw);
            // IMPORTANT: add viewBox BEFORE stripping width/height, so SVG
            // content scales properly inside the CSS-sized container.
            // Without viewBox the paths render at their native 467×350
            // coordinate scale and only a tiny corner is visible.
            $svgRaw = preg_replace(
                '/(<svg[^>]*?)\s+width="(\d+(?:\.\d+)?)"\s+height="(\d+(?:\.\d+)?)"/',
                '$1 viewBox="0 0 $2 $3" preserveAspectRatio="xMidYMid meet"',
                $svgRaw
            );
            $svgRaw = preg_replace('/\s+width="\d+(\.\d+)?"/', '', $svgRaw);
            $svgRaw = preg_replace('/\s+height="\d+(\.\d+)?"/', '', $svgRaw);
        @endphp

        <div id="avatar-wrap">
            {{-- Body SVG — gradient themed via CSS --}}
            <div id="avatar-svg-body">{!! $svgRaw !!}</div>

            {{-- ── Hotspots: positioned relative to the SVG viewBox (467×350).
                 SVG fills panel width; letterboxed ~13% top & bottom at 1366×768.
                 Figure spans SVG y: 17–334 (of 350), x-centre: 241/467 ≈ 52%.
                 Panel % = letterbox_offset + (svgY/350) × svgHeightRatio         ── --}}

            {{-- Head / Brain --}}
            <div class="hl-hotspot"
                 data-icon="🧠"
                 data-title="Brain & HPA Axis"
                 data-body="Your hypothalamic-pituitary-adrenal (HPA) axis controls cortisol release. Chronic stress keeps cortisol elevated, disrupting sleep, thyroid output, and reproductive hormone cycles."
                 style="top:21%;left:52%;"></div>

            {{-- Neck / Thyroid --}}
            <div class="hl-hotspot"
                 data-icon="🦋"
                 data-title="Thyroid Gland"
                 data-body="T3 & T4 hormones set your metabolic rate, body temperature, and heart rhythm. Subclinical hypothyroidism is a common hidden driver of fatigue, weight gain, and cycle irregularity."
                 style="top:28%;left:52%;"></div>

            {{-- Chest / Heart & Adrenals --}}
            <div class="hl-hotspot"
                 data-icon="❤️"
                 data-title="Heart & Adrenal Health"
                 data-body="Adrenaline (epinephrine) and cortisol from the adrenal glands regulate heart rate and blood pressure. Excess cortisol over time elevates cardiovascular risk and disrupts insulin signalling."
                 style="top:36%;left:48%;"></div>

            {{-- Left arm (viewer left = figure's right) — Muscle & Insulin --}}
            <div class="hl-hotspot"
                 data-icon="💪"
                 data-title="Muscle & Insulin Sensitivity"
                 data-body="Skeletal muscle is the largest site of insulin-driven glucose uptake. Every 1 kg of lean muscle gained can reduce fasting insulin by ~5%. Resistance training is medicine here."
                 style="top:42%;left:43%;"></div>

            {{-- Right arm (viewer right = figure's left) — Blood Panel --}}
            <div class="hl-hotspot"
                 data-icon="🩸"
                 data-title="Hormone Blood Panel"
                 data-body="Venous draws from the arm measure estrogen, progesterone, testosterone, insulin, cortisol, and thyroid markers. These numbers form your complete metabolic fingerprint."
                 style="top:42%;left:58%;"></div>

            {{-- Abdomen / Gut-Hormone Axis --}}
            <div class="hl-hotspot"
                 data-icon="🦠"
                 data-title="Gut–Hormone Axis"
                 data-body="Over 70% of serotonin is produced in the gut. Your microbiome regulates estrogen recycling (estrobolome), modulates cortisol feedback, and directly influences insulin sensitivity."
                 style="top:53%;left:52%;"></div>

            {{-- Pelvis / Reproductive --}}
            <div class="hl-hotspot"
                 data-icon="🌸"
                 data-title="Reproductive Hormones"
                 data-body="Ovaries and adrenal glands produce estrogen, progesterone, and testosterone. These hormones govern cycle regularity, mood stability, bone density, and metabolic efficiency."
                 style="top:64%;left:52%;"></div>

            {{-- Legs / Glucose sink --}}
            <div class="hl-hotspot"
                 data-icon="⚡"
                 data-title="Leg Muscles & Glucose"
                 data-body="Leg muscles account for ~30% of total blood glucose disposal. A 30-minute walk after meals can lower post-prandial glucose by 20–30% and meaningfully reduce HbA1c over time."
                 style="top:76%;left:52%;"></div>
        </div>

        {{-- Body part info tooltip (sibling of avatar-wrap so it is not clipped by it) --}}
        <div id="hl-tooltip" role="tooltip">
            <button id="hl-tip-close" aria-label="Close">&times;</button>
            <div id="hl-tip-icon"></div>
            <div id="hl-tip-title"></div>
            <div id="hl-tip-body"></div>
        </div>

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
                    <div class="relative">
                        <input id="password" name="password" type="password" required
                               class="hl-input" placeholder="••••••••" style="padding-right:44px;">
                        <button type="button" id="togglePassword"
                                onclick="(function(){
                                    var inp = document.getElementById('password');
                                    var btn = document.getElementById('togglePassword');
                                    if(inp.type==='password'){
                                        inp.type='text';
                                        btn.innerHTML='<svg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.8\' stroke=\'currentColor\' class=\'w-5 h-5\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88\'/></svg>';
                                    } else {
                                        inp.type='password';
                                        btn.innerHTML='<svg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.8\' stroke=\'currentColor\' class=\'w-5 h-5\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z\'/></svg>';
                                    }
                                })()"
                                aria-label="Toggle password visibility"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#7c3aed;padding:4px;display:flex;align-items:center;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </button>
                    </div>
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
    'use strict';

    var tooltip  = document.getElementById('hl-tooltip');
    var tipIcon  = document.getElementById('hl-tip-icon');
    var tipTitle = document.getElementById('hl-tip-title');
    var tipBody  = document.getElementById('hl-tip-body');
    var tipClose = document.getElementById('hl-tip-close');
    var panel    = document.querySelector('.left-panel');
    if (!tooltip || !panel) return;

    var hideTimer = null;

    function showTip(hs) {
        clearTimeout(hideTimer);
        tipIcon.textContent  = hs.dataset.icon;
        tipTitle.textContent = hs.dataset.title;
        tipBody.textContent  = hs.dataset.body;
        var pr  = panel.getBoundingClientRect();
        var hsr = hs.getBoundingClientRect();
        var cx  = hsr.left + hsr.width  / 2 - pr.left;
        var cy  = hsr.top  + hsr.height / 2 - pr.top;
        var TW  = 218, TH = 160;
        var tx  = cx + 18;
        if (tx + TW > pr.width  - 8) tx = cx - TW - 14;
        var ty  = cy - 40;
        if (ty + TH > pr.height - 8) ty = pr.height - TH - 8;
        if (ty < 8) ty = 8;
        tooltip.style.left = tx + 'px';
        tooltip.style.top  = ty + 'px';
        tooltip.classList.add('hl-visible');
    }

    function hideTip() {
        hideTimer = setTimeout(function() {
            tooltip.classList.remove('hl-visible');
        }, 120);
    }

    document.querySelectorAll('.hl-hotspot').forEach(function(hs) {
        hs.addEventListener('mouseenter', function() { showTip(hs); });
        hs.addEventListener('mouseleave', hideTip);
    });

    /* keep tooltip open when cursor moves onto the card */
    tooltip.addEventListener('mouseenter', function() { clearTimeout(hideTimer); });
    tooltip.addEventListener('mouseleave', hideTip);

    tipClose.addEventListener('click', function(e) {
        e.stopPropagation();
        clearTimeout(hideTimer);
        tooltip.classList.remove('hl-visible');
    });

    panel.addEventListener('click', function() {
        tooltip.classList.remove('hl-visible');
    });

})();
</script>
</body>
</html>
