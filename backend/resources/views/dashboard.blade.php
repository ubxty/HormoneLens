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
<div class="dash-bg -m-4 sm:-m-6 p-4 sm:p-6">

    {{-- Ambient particles --}}
    <div class="dash-particle dash-particle-1"></div>
    <div class="dash-particle dash-particle-2"></div>
    <div class="dash-particle dash-particle-3"></div>

    {{-- ══════════════════════════════════════════════
         Section 1: Welcome Panel
         ══════════════════════════════════════════════ --}}
    <div class="welcome-banner p-6 sm:p-8 mb-8 anim-enter" data-anim>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-400 status-pulse"></div>
                    <span class="text-white/70 text-xs font-medium tracking-wide uppercase">AI Simulation Active</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-white mb-1">
                    Welcome back, {{ $user->name }} 👋
                </h1>
                @if($age)
                <p class="text-white/70 text-sm">Age: ~{{ $age }} • Simulation Profile: <span class="text-emerald-300 font-medium">Active</span></p>
                @else
                <p class="text-white/70 text-sm">Simulation Profile: <span class="text-emerald-300 font-medium">Active</span></p>
                @endif
            </div>
            <div class="flex flex-col items-center sm:items-end">
                <p class="text-white/60 text-xs uppercase tracking-wider mb-1">Health Risk Score</p>
                <div class="flex items-baseline gap-1">
                    <span class="text-4xl sm:text-5xl font-black text-white count-num" id="riskScoreNum">0</span>
                    <span class="text-white/60 text-lg">%</span>
                </div>
                <p class="text-xs mt-1 {{ $riskScore >= 60 ? 'text-red-200' : ($riskScore >= 40 ? 'text-amber-200' : 'text-emerald-200') }}">
                    {{ $riskScore >= 60 ? '⚠ High Risk' : ($riskScore >= 40 ? '⚡ Moderate Risk' : '✓ Low Risk') }}
                </p>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         Section 2: Simulation Metrics Cards
         ══════════════════════════════════════════════ --}}
    <div class="mb-8">
        <h2 class="text-lg font-bold text-gray-700 mb-4 flex items-center gap-2 anim-enter" data-anim>
            <span class="gradient-text">🧬</span> Simulation Metrics
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            @php
                $metrics = [
                    ['label' => 'Metabolic Score',    'key' => 'metabolic_score',  'icon' => '🔥', 'iconBg' => 'bg-indigo-100',  'iconColor' => 'text-indigo-600'],
                    ['label' => 'Insulin Resistance', 'key' => 'insulin_score',    'icon' => '💉', 'iconBg' => 'bg-purple-100',  'iconColor' => 'text-purple-600'],
                    ['label' => 'Sleep Score',        'key' => 'sleep_score',      'icon' => '🌙', 'iconBg' => 'bg-blue-100',    'iconColor' => 'text-blue-600'],
                    ['label' => 'Stress Score',       'key' => 'stress_score',     'icon' => '🧠', 'iconBg' => 'bg-amber-100',   'iconColor' => 'text-amber-600'],
                    ['label' => 'Diet Impact',        'key' => 'diet_score',       'icon' => '🥗', 'iconBg' => 'bg-emerald-100', 'iconColor' => 'text-emerald-600'],
                ];
            @endphp

            @foreach($metrics as $i => $m)
                @php
                    $val = $sim ? round((float) $sim->{$m['key']}) : 0;
                    $glowClass = $val >= 70 ? 'glow-green' : ($val >= 50 ? 'glow-yellow' : 'glow-red');
                @endphp
                <div class="glass-card metric-card p-5 anim-enter anim-d{{ $i + 1 }}" data-anim>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="score-icon {{ $m['iconBg'] }} {{ $m['iconColor'] }}">
                            {{ $m['icon'] }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-500 truncate">{{ $m['label'] }}</p>
                            <p class="text-2xl font-bold text-gray-800 count-num" data-count="{{ $val }}">0</p>
                        </div>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill {{ $glowClass }}" data-width="{{ $val }}%"></div>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-2 text-right">
                        {{ $val >= 70 ? 'Optimal' : ($val >= 50 ? 'Moderate' : 'Needs Attention') }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         Section 3: Hormonal Prediction Panel
         ══════════════════════════════════════════════ --}}
    <div class="mb-8">
        <h2 class="text-lg font-bold text-gray-700 mb-4 flex items-center gap-2 anim-enter" data-anim>
            <span class="gradient-text">🔮</span> Hormonal Risk Predictions
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            @php
                $predictions = [
                    ['label' => 'PCOS Risk',               'key' => 'pcos_risk',               'gradId' => 'gradPcos',    'gradFrom' => '#c24dff', 'gradTo' => '#ff6ec7'],
                    ['label' => 'Type 2 Diabetes Risk',    'key' => 'diabetes_risk',           'gradId' => 'gradDiab',    'gradFrom' => '#5f6fff', 'gradTo' => '#c24dff'],
                    ['label' => 'Insulin Resistance Risk', 'key' => 'insulin_resistance_risk', 'gradId' => 'gradInsulin', 'gradFrom' => '#ff6ec7', 'gradTo' => '#5f6fff'],
                ];
            @endphp

            @foreach($predictions as $j => $p)
                @php
                    $pVal = $sim ? round((float) $sim->{$p['key']}) : 0;
                    $circumference = 314.16;
                    $offset = $circumference - ($circumference * $pVal / 100);
                @endphp
                <div class="glass-card p-6 flex flex-col items-center anim-enter anim-d{{ $j + 3 }}" data-anim>
                    <div class="ring-container mb-3">
                        <svg class="ring-svg" viewBox="0 0 108 108">
                            <defs>
                                <linearGradient id="{{ $p['gradId'] }}_{{ $j }}" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0%" stop-color="{{ $p['gradFrom'] }}"/>
                                    <stop offset="100%" stop-color="{{ $p['gradTo'] }}"/>
                                </linearGradient>
                            </defs>
                            <circle class="ring-bg" cx="54" cy="54" r="50"/>
                            <circle class="ring-progress"
                                    cx="54" cy="54" r="50"
                                    stroke="url(#{{ $p['gradId'] }}_{{ $j }})"
                                    data-offset="{{ $offset }}"/>
                        </svg>
                        <div class="ring-label">
                            <span class="text-2xl font-black gradient-text count-num" data-count="{{ $pVal }}">0</span>
                            <span class="text-[10px] text-gray-400">%</span>
                        </div>
                    </div>
                    <p class="text-sm font-semibold text-gray-700">{{ $p['label'] }}</p>
                    <p class="text-[11px] mt-1 {{ $pVal >= 60 ? 'text-red-500' : ($pVal >= 40 ? 'text-amber-500' : 'text-emerald-500') }}">
                        {{ $pVal >= 60 ? 'High Risk' : ($pVal >= 40 ? 'Moderate' : 'Low Risk') }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         Section 4: Lifestyle Simulation Activity Graph
         ══════════════════════════════════════════════ --}}
    <div class="mb-8">
        <h2 class="text-lg font-bold text-gray-700 mb-4 flex items-center gap-2 anim-enter" data-anim>
            <span class="gradient-text">📊</span> Lifestyle Simulation Overview
        </h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="chart-glass p-6 anim-enter anim-d5" data-anim>
                <h3 class="text-sm font-semibold text-gray-600 mb-4">Lifestyle Activity Radar</h3>
                <div style="position:relative; height:300px;">
                    <canvas id="lifestyleRadar"></canvas>
                </div>
            </div>
            <div class="chart-glass p-6 anim-enter anim-d6" data-anim>
                <h3 class="text-sm font-semibold text-gray-600 mb-4">Simulation Scores Breakdown</h3>
                <div style="position:relative; height:300px;">
                    <canvas id="scoresBar"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         Quick Actions
         ══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
        @php
            $actions = [
                ['route' => 'health-profile', 'icon' => '👤', 'label' => 'Health Profile'],
                ['route' => 'digital-twin',   'icon' => '🧬', 'label' => 'Digital Twin'],
                ['route' => 'simulations',    'icon' => '⚡', 'label' => 'Simulations'],
                ['route' => 'knowledge',      'icon' => '📚', 'label' => 'Knowledge Base'],
            ];
        @endphp
        @foreach($actions as $k => $a)
            <a href="{{ route($a['route']) }}"
               class="glass-card p-4 text-center group anim-enter anim-d{{ $k + 5 }}" data-anim>
                <div class="text-2xl mb-1 transition-transform duration-300 group-hover:scale-110">{{ $a['icon'] }}</div>
                <span class="text-sm font-medium text-gray-700 group-hover:gradient-text">{{ $a['label'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- No simulation data prompt --}}
    @if(!$sim)
    <div class="glass-card p-6 text-center anim-enter anim-d3" data-anim>
        <div class="text-4xl mb-3">🧪</div>
        <p class="font-semibold text-gray-700 mb-1">No Simulation Data Yet</p>
        <p class="text-sm text-gray-500 mb-4">Complete your Health Profile and generate your Digital Twin to unlock AI predictions.</p>
        <div class="flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('health-profile') }}" class="px-4 py-2 text-sm font-medium rounded-xl border border-purple-200 text-purple-700 hover:bg-purple-50 transition">Health Profile</a>
            <a href="{{ route('digital-twin') }}" class="px-4 py-2 text-sm font-medium rounded-xl text-white transition" style="background: linear-gradient(135deg, #5f6fff, #c24dff);">Generate Twin →</a>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ═══ IntersectionObserver — animate on enter ═══ */
    var animEls = document.querySelectorAll('[data-anim]');
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('anim-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });
    animEls.forEach(function (el) { observer.observe(el); });

    /* ═══ Count-up animation ═══ */
    function countUp(el, target, duration) {
        if (duration === undefined) duration = 1800;
        var start = 0;
        var startTime = null;
        function step(time) {
            if (!startTime) startTime = time;
            var progress = Math.min((time - startTime) / duration, 1);
            var ease = 1 - Math.pow(1 - progress, 3);
            var current = Math.round(ease * target);
            el.textContent = current;
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    /* Risk score count-up */
    var riskEl = document.getElementById('riskScoreNum');
    if (riskEl) {
        setTimeout(function () { countUp(riskEl, {{ $riskScore }}, 2000); }, 400);
    }

    /* Metric card count-ups */
    var countEls = document.querySelectorAll('.count-num[data-count]');
    var countObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                var target = parseInt(entry.target.getAttribute('data-count'), 10);
                countUp(entry.target, target, 1500);
                countObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.3 });
    countEls.forEach(function (el) { countObserver.observe(el); });

    /* ═══ Progress bar fill ═══ */
    var bars = document.querySelectorAll('.progress-fill[data-width]');
    var barObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                var w = entry.target.getAttribute('data-width');
                setTimeout(function () {
                    entry.target.style.width = w;
                }, 300);
                barObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.3 });
    bars.forEach(function (b) { barObserver.observe(b); });

    /* ═══ Ring animation ═══ */
    var rings = document.querySelectorAll('.ring-progress[data-offset]');
    var ringObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                var offset = entry.target.getAttribute('data-offset');
                setTimeout(function () {
                    entry.target.style.strokeDashoffset = offset;
                }, 400);
                ringObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.3 });
    rings.forEach(function (r) { ringObserver.observe(r); });

    /* ═══ Chart.js — Lifestyle Radar ═══ */
    var lifestyleData = @json($lifestyleData);

    if (document.getElementById('lifestyleRadar')) {
        new Chart(document.getElementById('lifestyleRadar'), {
            type: 'radar',
            data: {
                labels: ['Sleep (hrs)', 'Stress Level', 'Physical Activity', 'Diet Quality'],
                datasets: [{
                    label: 'Your Lifestyle',
                    data: [lifestyleData.sleep, lifestyleData.stress, lifestyleData.activity, lifestyleData.diet],
                    backgroundColor: 'rgba(194, 77, 255, 0.12)',
                    borderColor: 'rgba(194, 77, 255, 0.7)',
                    borderWidth: 2,
                    pointBackgroundColor: '#c24dff',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 1500, easing: 'easeOutQuart' },
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 10,
                        ticks: { stepSize: 2, display: false },
                        grid: { color: 'rgba(0,0,0,0.06)' },
                        angleLines: { color: 'rgba(0,0,0,0.06)' },
                        pointLabels: { font: { size: 11, weight: '500' }, color: '#6b7280' }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    /* ═══ Chart.js — Scores Bar ═══ */
    @if($sim)
    if (document.getElementById('scoresBar')) {
        new Chart(document.getElementById('scoresBar'), {
            type: 'bar',
            data: {
                labels: ['Metabolic', 'Insulin Res.', 'Sleep', 'Stress', 'Diet'],
                datasets: [{
                    label: 'Score',
                    data: [
                        {{ (float) $sim->metabolic_score }},
                        {{ (float) $sim->insulin_score }},
                        {{ (float) $sim->sleep_score }},
                        {{ (float) $sim->stress_score }},
                        {{ (float) $sim->diet_score }}
                    ],
                    backgroundColor: [
                        'rgba(95, 111, 255, 0.7)',
                        'rgba(194, 77, 255, 0.7)',
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(245, 158, 11, 0.7)',
                        'rgba(16, 185, 129, 0.7)',
                    ],
                    borderColor: [
                        '#5f6fff',
                        '#c24dff',
                        '#3b82f6',
                        '#f59e0b',
                        '#10b981',
                    ],
                    borderWidth: 2,
                    borderRadius: 10,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 1500, easing: 'easeOutQuart' },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { color: 'rgba(0,0,0,0.04)' },
                        ticks: { font: { size: 11 }, color: '#9ca3af' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11, weight: '500' }, color: '#6b7280' }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
    @else
    if (document.getElementById('scoresBar')) {
        new Chart(document.getElementById('scoresBar'), {
            type: 'bar',
            data: {
                labels: ['Metabolic', 'Insulin Res.', 'Sleep', 'Stress', 'Diet'],
                datasets: [{
                    label: 'Score',
                    data: [0, 0, 0, 0, 0],
                    backgroundColor: 'rgba(156, 163, 175, 0.3)',
                    borderColor: '#d1d5db',
                    borderWidth: 1,
                    borderRadius: 10,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, max: 100, grid: { color: 'rgba(0,0,0,0.04)' } },
                    x: { grid: { display: false } }
                },
                plugins: { legend: { display: false } }
            }
        });
    }
    @endif

});
</script>
@endpush
