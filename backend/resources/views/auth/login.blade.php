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
            background: linear-gradient(135deg,
                #ede9fe 0%,
                #ddd6fe 30%,
                #c7d2fe 60%,
                #bfdbfe 100%);
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

        /* ── Floating tags ── */
        @keyframes tagFloat {
            0%,100% { transform: translateY(0); }
            50%      { transform: translateY(-10px); }
        }
        .float-tag {
            position: absolute;
            background: rgba(255,255,255,0.55);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.5);
            border-radius: 9999px;
            padding: 5px 14px;
            font-size: 11px;
            font-weight: 600;
            color: #4338ca;
            opacity: 0.75;
            white-space: nowrap;
            animation: tagFloat var(--tf-dur,5s) ease-in-out var(--tf-delay,0s) infinite;
        }

        /* ── Silhouette SVG ── */
        .silhouette-wrap {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 200px; opacity: 0.12;
            filter: blur(1px);
        }

        /* ── Left copy ── */
        .left-copy {
            position: absolute;
            bottom: 60px; left: 48px; right: 48px;
            z-index: 10;
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

        {{-- Particles --}}
        <div class="lp-particle lp-p1"></div>
        <div class="lp-particle lp-p2"></div>
        <div class="lp-particle lp-p3"></div>

        {{-- Floating tags --}}
        <span class="float-tag" style="top:18%;left:8%;  --tf-dur:5s;--tf-delay:0s;">💤 Sleep Pattern</span>
        <span class="float-tag" style="top:25%;right:10%;--tf-dur:6s;--tf-delay:1s;">🥗 Diet Impact</span>
        <span class="float-tag" style="top:55%;left:5%; --tf-dur:7s;--tf-delay:2s;">😓 Stress Levels</span>
        <span class="float-tag" style="top:62%;right:8%;--tf-dur:5.5s;--tf-delay:0.5s;">💉 Insulin Sensitivity</span>

        {{-- Metabolic silhouette --}}
        <div class="silhouette-wrap">
            <svg viewBox="0 0 200 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="sil" x1="40" y1="0" x2="160" y2="400" gradientUnits="userSpaceOnUse">
                        <stop offset="0%"   stop-color="#7c3aed"/>
                        <stop offset="50%"  stop-color="#6366f1"/>
                        <stop offset="100%" stop-color="#a78bfa"/>
                    </linearGradient>
                </defs>
                <circle cx="100" cy="36" r="26" stroke="url(#sil)" stroke-width="2" fill="none"/>
                <line x1="100" y1="62" x2="100" y2="90" stroke="url(#sil)" stroke-width="2"/>
                <path d="M100 90 Q100 100, 50 115" stroke="url(#sil)" stroke-width="2" fill="none"/>
                <path d="M100 90 Q100 100, 150 115" stroke="url(#sil)" stroke-width="2" fill="none"/>
                <path d="M50 115 Q38 155, 32 200" stroke="url(#sil)" stroke-width="2" fill="none"/>
                <path d="M150 115 Q162 155, 168 200" stroke="url(#sil)" stroke-width="2" fill="none"/>
                <path d="M50 115 L60 230 Q65 248, 78 255" stroke="url(#sil)" stroke-width="2" fill="none"/>
                <path d="M150 115 L140 230 Q135 248, 122 255" stroke="url(#sil)" stroke-width="2" fill="none"/>
                <path d="M78 255 Q100 266, 122 255" stroke="url(#sil)" stroke-width="2" fill="none"/>
                <path d="M86 260 Q82 315, 74 380" stroke="url(#sil)" stroke-width="2" fill="none"/>
                <path d="M114 260 Q118 315, 126 380" stroke="url(#sil)" stroke-width="2" fill="none"/>
            </svg>
        </div>

        {{-- Copy --}}
        <div class="left-copy">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold text-indigo-700 mb-4"
                 style="background:rgba(255,255,255,0.45);border:1px solid rgba(255,255,255,0.5);backdrop-filter:blur(6px);">
                🧬 AI Hormone Simulation
            </div>
            <h1 class="text-2xl font-extrabold text-gray-800 leading-snug mb-3">
                Welcome to Your<br>Hormone Simulation Lab
            </h1>
            <p class="text-sm text-gray-600 leading-relaxed max-w-xs">
                Log in to simulate how your lifestyle influences hormonal balance and metabolic risk.
            </p>
        </div>
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
</body>
</html>
