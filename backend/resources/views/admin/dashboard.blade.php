@extends('layouts.admin')
@section('heading','AI Metabolic Command Center')

@push('styles')
<style>
/* ═══ DASHBOARD CUSTOM ANIMATIONS ═══ */
@keyframes fadeSlideUp   { from { opacity:0; transform:translateY(36px); } to { opacity:1; transform:translateY(0); } }
@keyframes shimmer       { 0% { background-position:-200% 0; } 100% { background-position:200% 0; } }
@keyframes heroFloat     { 0%,100% { transform:translateY(0) rotate(0deg); } 38% { transform:translateY(-14px) rotate(2deg); } 70% { transform:translateY(6px) rotate(-1.5deg); } }
@keyframes orbDrift      { 0%,100% { transform:translate(0,0) scale(1); } 33% { transform:translate(30px,-20px) scale(1.06); } 66% { transform:translate(-18px,14px) scale(.95); } }
@keyframes liveBlink     { 0%,100% { opacity:1; box-shadow:0 0 10px rgba(16,185,129,.7); } 50% { opacity:.35; box-shadow:none; } }
@keyframes sparkDraw     { from { stroke-dashoffset:200; } to { stroke-dashoffset:0; } }
@keyframes countBounce   { 0% { opacity:0; transform:scale(.6) translateY(12px); } 60% { transform:scale(1.06); } 100% { opacity:1; transform:scale(1) translateY(0); } }
@keyframes popIn         { 0% { transform:scale(0) rotate(-15deg); opacity:0; } 70% { transform:scale(1.1) rotate(3deg); } 100% { transform:scale(1) rotate(0deg); opacity:1; } }

/* ── Hero ── */
.dh-hero {
    background: linear-gradient(135deg, #0f0c29 0%, #1e1b4b 25%, #312e81 55%, #4c1d95 80%, #6d28d9 100%);
    border-radius: 24px;
    position: relative;
    overflow: hidden;
}
.dh-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse at 75% 40%, rgba(194,77,255,.28) 0%, transparent 55%),
                radial-gradient(ellipse at 15% 85%, rgba(95,111,255,.22) 0%, transparent 50%);
}
.dh-hero-orb { position:absolute; border-radius:50%; pointer-events:none; filter:blur(64px); }

/* ── KPI cards ── */
.dh-kpi {
    background: rgba(255,255,255,.58);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    border: 1px solid rgba(255,255,255,.45);
    border-radius: 20px;
    position: relative; overflow: hidden;
    transition: transform .4s cubic-bezier(.4,0,.2,1), box-shadow .4s ease;
}
.dh-kpi:hover { transform: translateY(-7px) scale(1.015); }
.dh-kpi::after {
    content: '';
    position: absolute; bottom:0; left:0; right:0; height:3px;
    border-radius: 0 0 20px 20px;
}
.dh-kpi-purple::after  { background:linear-gradient(90deg,#5f6fff,#c24dff); }
.dh-kpi-amber::after   { background:linear-gradient(90deg,#f59e0b,#fbbf24); }
.dh-kpi-red::after     { background:linear-gradient(90deg,#ef4444,#dc2626); }
.dh-kpi-pink::after    { background:linear-gradient(90deg,#ff6ec7,#f472b6); }
.dh-kpi-emerald::after { background:linear-gradient(90deg,#10b981,#34d399); }

.dh-kpi:hover.dh-kpi-purple  { box-shadow:0 20px 42px rgba(95,111,255,.3),  0 0 0 1px rgba(95,111,255,.12); }
.dh-kpi:hover.dh-kpi-amber   { box-shadow:0 20px 42px rgba(245,158,11,.28), 0 0 0 1px rgba(245,158,11,.12); }
.dh-kpi:hover.dh-kpi-red     { box-shadow:0 20px 42px rgba(239,68,68,.28),  0 0 0 1px rgba(239,68,68,.12); }
.dh-kpi:hover.dh-kpi-pink    { box-shadow:0 20px 42px rgba(236,72,153,.28), 0 0 0 1px rgba(236,72,153,.12); }
.dh-kpi:hover.dh-kpi-emerald { box-shadow:0 20px 42px rgba(16,185,129,.28), 0 0 0 1px rgba(16,185,129,.12); }

/* ── Sparklines ── */
.dh-spark { stroke-dasharray:200; stroke-dashoffset:200; animation:sparkDraw 1.4s ease forwards; }

/* ── Progress bars ── */
.dh-bar { height:5px; border-radius:99px; background:rgba(0,0,0,.06); overflow:hidden; }
.dh-bar-fill { height:100%; border-radius:99px; width:0; transition:width 1.6s cubic-bezier(.4,0,.2,1) .2s; }

/* ── Live dot ── */
.dh-live { width:8px; height:8px; border-radius:50%; background:#10b981; animation:liveBlink 1.8s ease infinite; }

/* ── Shimmer skeleton ── */
.dh-shimmer {
    background: linear-gradient(90deg,rgba(0,0,0,.04) 25%,rgba(0,0,0,.09) 50%,rgba(0,0,0,.04) 75%);
    background-size:200% 100%;
    animation: shimmer 1.6s ease infinite;
    border-radius: 16px;
}

/* ── Entrance fade-up (JS-driven) ── */
.dh-fade { opacity:0; transform:translateY(32px); transition:opacity .65s ease, transform .65s cubic-bezier(.4,0,.2,1); }
.dh-fade.dh-in { opacity:1; transform:translateY(0); }

/* ── Action card ── */
.dh-action {
    background:rgba(255,255,255,.55);
    backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px);
    border:1px solid rgba(255,255,255,.38);
    border-radius:18px;
    transition:transform .35s cubic-bezier(.4,0,.2,1), box-shadow .35s ease;
    display:block; overflow:hidden; position:relative;
}
.dh-action::before {
    content:''; position:absolute; inset:0; border-radius:18px; padding:1px;
    background:linear-gradient(135deg,rgba(95,111,255,.18),rgba(194,77,255,.12));
    -webkit-mask:linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite:xor; mask-composite:exclude; pointer-events:none;
    opacity:0; transition:opacity .35s;
}
.dh-action:hover::before { opacity:1; }
.dh-action:hover { transform:translateY(-5px); box-shadow:0 14px 40px rgba(95,111,255,.15); }

/* ── Floating icons in hero ── */
.dh-hero-icon:nth-child(1) { animation:heroFloat 6s ease-in-out infinite; }
.dh-hero-icon:nth-child(2) { animation:heroFloat 8s ease-in-out 1.2s infinite; }
.dh-hero-icon:nth-child(3) { animation:heroFloat 7s ease-in-out 2.4s infinite; }
.dh-hero-icon:nth-child(4) { animation:heroFloat 9s ease-in-out 0.6s infinite; }
</style>
@endpush

@section('content')
<div x-data="adminDashboard()" x-init="init()">

    {{-- LOADING SKELETON --}}
    <div x-show="loading" class="space-y-5">
        <div class="dh-shimmer h-52 rounded-3xl"></div>
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="i in 6"><div class="dh-shimmer h-40 rounded-2xl"></div></template>
        </div>
        <div class="grid lg:grid-cols-5 gap-4">
            <div class="dh-shimmer h-72 rounded-2xl lg:col-span-2"></div>
            <div class="dh-shimmer h-72 rounded-2xl lg:col-span-3"></div>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div x-show="!loading" x-cloak class="space-y-5">

        {{-- HERO BANNER --}}
        <div class="dh-hero p-7 dh-fade" data-dh>
            <div class="dh-hero-orb w-72 h-72 bg-purple-400/25" style="top:-80px;right:-40px;animation:orbDrift 18s ease-in-out infinite;"></div>
            <div class="dh-hero-orb w-52 h-52 bg-pink-400/18"   style="bottom:-60px;left:18%;animation:orbDrift 22s ease-in-out 4s infinite;"></div>
            <div class="dh-hero-orb w-36 h-36 bg-blue-400/20"   style="top:15%;left:42%;animation:orbDrift 14s ease-in-out 8s infinite;"></div>

            <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-2xl bg-white/15 backdrop-blur-sm flex items-center justify-center text-2xl shadow-lg border border-white/20" style="animation:popIn .7s ease .2s both">&#x1F9EC;</div>
                        <div>
                            <div class="flex items-center gap-2 mb-0.5">
                                <div class="dh-live"></div>
                                <span class="text-white/60 text-[10px] font-bold uppercase tracking-widest">Live System</span>
                            </div>
                            <h1 class="text-xl font-black text-white leading-tight">AI Metabolic Command Center</h1>
                        </div>
                    </div>
                    <p class="text-white/50 text-sm max-w-md leading-relaxed">Population health intelligence &bull; Real-time risk monitoring &bull; AI-powered analytics</p>

                    <div class="flex flex-wrap items-center gap-3 mt-4">
                        <div class="px-3.5 py-2 bg-white/10 rounded-xl border border-white/15 backdrop-blur-sm">
                            <p class="text-white/55 text-[10px] font-medium">Users tracked</p>
                            <p class="text-white font-black text-base leading-tight" x-text="d.total_users ?? '—'"></p>
                        </div>
                        <div class="px-3.5 py-2 bg-white/10 rounded-xl border border-white/15 backdrop-blur-sm">
                            <p class="text-white/55 text-[10px] font-medium">Avg risk score</p>
                            <p class="text-white font-black text-base leading-tight" x-text="d.avg_risk_score ?? '—'"></p>
                        </div>
                        <div class="px-3.5 py-2 bg-white/10 rounded-xl border border-white/15 backdrop-blur-sm">
                            <p class="text-white/55 text-[10px] font-medium">Simulations today</p>
                            <p class="text-white font-black text-base leading-tight" x-text="d.simulations_today ?? '0'"></p>
                        </div>
                        <div class="px-3.5 py-2 bg-white/10 rounded-xl border border-white/15 backdrop-blur-sm" x-show="(d.unread_alerts ?? 0) > 0">
                            <p class="text-red-300 text-[10px] font-bold animate-pulse">&#x26A0; Active alerts</p>
                            <p class="text-red-200 font-black text-base leading-tight" x-text="d.unread_alerts"></p>
                        </div>
                    </div>
                </div>

                <div class="hidden lg:flex items-end gap-3">
                    <div class="dh-hero-icon w-14 h-14 rounded-2xl bg-white/12 border border-white/22 flex items-center justify-center text-2xl">&#x1F4CA;</div>
                    <div class="dh-hero-icon w-12 h-12 rounded-xl bg-white/10 border border-white/18 flex items-center justify-center text-xl mb-4">&#x26A1;</div>
                    <div class="dh-hero-icon w-11 h-11 rounded-xl bg-white/10 border border-white/18 flex items-center justify-center text-lg">&#x1F514;</div>
                    <div class="dh-hero-icon w-10 h-10 rounded-xl bg-white/8 border border-white/15 flex items-center justify-center text-base mb-6">&#x1F9EA;</div>
                </div>
            </div>
        </div>

        {{-- KPI GRID --}}
        <section class="grid grid-cols-2 lg:grid-cols-3 gap-4">

            <div class="dh-kpi dh-kpi-purple p-5 dh-fade" data-dh style="transition-delay:.04s">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[#5f6fff]/20 to-[#c24dff]/20 flex items-center justify-center text-xl">&#x1F465;</div>
                    <span class="text-[9px] font-bold uppercase tracking-widest text-indigo-500 bg-indigo-50 px-2 py-1 rounded-lg">Users</span>
                </div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Users</p>
                <h3 class="text-3xl font-black text-gray-800 leading-none mt-1" data-val="total_users">—</h3>
                <p class="text-[10px] text-gray-400 mt-1">+<span data-val="new_users_7d">0</span> this week</p>
                <div class="dh-bar mt-3"><div class="dh-bar-fill bg-gradient-to-r from-[#5f6fff] to-[#c24dff]" data-pct-key="total_users_pct"></div></div>
                <svg class="w-full mt-2.5 h-8" viewBox="0 0 80 24" fill="none"><polyline class="dh-spark" stroke="#5f6fff" stroke-width="1.8" points="0,20 10,16 20,14 30,10 40,12 50,8 60,5 70,7 80,3"/></svg>
            </div>

            <div class="dh-kpi dh-kpi-amber p-5 dh-fade" data-dh style="transition-delay:.08s">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[#f59e0b]/20 to-[#fbbf24]/20 flex items-center justify-center text-xl">&#x1F4C8;</div>
                    <span class="text-[9px] font-bold uppercase tracking-widest px-2 py-1 rounded-lg"
                          :class="parseFloat(d.avg_risk_score)>=7?'bg-red-50 text-red-500':parseFloat(d.avg_risk_score)>=4?'bg-amber-50 text-amber-500':'bg-emerald-50 text-emerald-500'"
                          x-text="parseFloat(d.avg_risk_score)>=7?'Critical':parseFloat(d.avg_risk_score)>=4?'Warning':'Healthy'"></span>
                </div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Avg Risk Score</p>
                <h3 class="text-3xl font-black leading-none mt-1"
                    :class="parseFloat(d.avg_risk_score)>=7?'text-red-600':parseFloat(d.avg_risk_score)>=4?'text-amber-600':'text-emerald-600'"
                    data-val="avg_risk_score">—</h3>
                <p class="text-[10px] text-gray-400 mt-1">Population avg / 10</p>
                <div class="dh-bar mt-3"><div class="dh-bar-fill" style="background:linear-gradient(90deg,#f59e0b,#ef4444)" data-pct-key="avg_risk_pct"></div></div>
                <svg class="w-full mt-2.5 h-8" viewBox="0 0 80 24" fill="none"><polyline class="dh-spark" stroke="#f59e0b" stroke-width="1.8" points="0,18 10,15 20,17 30,12 40,15 50,11 60,14 70,9 80,12" style="animation-delay:.15s"/></svg>
            </div>

            <div class="dh-kpi dh-kpi-red p-5 dh-fade col-span-2 lg:col-span-1" data-dh style="transition-delay:.12s">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[#ef4444]/20 to-[#f87171]/20 flex items-center justify-center text-xl">&#x1F6A8;</div>
                    <span class="text-[9px] font-bold uppercase tracking-widest text-red-400 bg-red-50 px-2 py-1 rounded-lg animate-pulse">Alert</span>
                </div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">High Risk Users</p>
                <h3 class="text-3xl font-black text-red-600 leading-none mt-1" data-val="high_risk">—</h3>
                <p class="text-[10px] text-gray-400 mt-1">High + critical tier</p>
                <div class="dh-bar mt-3"><div class="dh-bar-fill" style="background:linear-gradient(90deg,#ef4444,#991b1b)" data-pct-key="high_risk_pct"></div></div>
                <svg class="w-full mt-2.5 h-8" viewBox="0 0 80 24" fill="none"><polyline class="dh-spark" stroke="#ef4444" stroke-width="1.8" points="0,20 10,18 20,20 30,15 40,18 50,12 60,16 70,10 80,13" style="animation-delay:.3s"/></svg>
            </div>

            <div class="dh-kpi dh-kpi-purple p-5 dh-fade" data-dh style="transition-delay:.16s">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[#c24dff]/20 to-[#a855f7]/20 flex items-center justify-center text-xl">&#x26A1;</div>
                    <span class="text-[9px] font-bold uppercase tracking-widest text-purple-500 bg-purple-50 px-2 py-1 rounded-lg">Today</span>
                </div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Simulations</p>
                <h3 class="text-3xl font-black text-purple-700 leading-none mt-1" data-val="simulations_today">—</h3>
                <p class="text-[10px] text-gray-400 mt-1">Total: <span data-val="simulations_total">—</span></p>
                <div class="dh-bar mt-3"><div class="dh-bar-fill bg-gradient-to-r from-[#c24dff] to-[#a855f7]" data-pct-key="simulations_pct"></div></div>
                <svg class="w-full mt-2.5 h-8" viewBox="0 0 80 24" fill="none"><polyline class="dh-spark" stroke="#c24dff" stroke-width="1.8" points="0,22 10,18 20,20 30,14 40,17 50,10 60,13 70,6 80,9" style="animation-delay:.45s"/></svg>
            </div>

            <div class="dh-kpi dh-kpi-pink p-5 dh-fade" data-dh style="transition-delay:.20s">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[#ff6ec7]/20 to-[#f472b6]/20 flex items-center justify-center text-xl">&#x1F4C5;</div>
                    <span class="text-[9px] font-bold uppercase tracking-widest text-pink-500 bg-pink-50 px-2 py-1 rounded-lg">Week</span>
                </div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">This Week</p>
                <h3 class="text-3xl font-black text-pink-600 leading-none mt-1" data-val="simulations_week">—</h3>
                <p class="text-[10px] text-gray-400 mt-1">Last 7 days</p>
                <div class="dh-bar mt-3"><div class="dh-bar-fill bg-gradient-to-r from-[#ff6ec7] to-[#f472b6]" data-pct-key="week_pct"></div></div>
                <svg class="w-full mt-2.5 h-8" viewBox="0 0 80 24" fill="none"><polyline class="dh-spark" stroke="#ff6ec7" stroke-width="1.8" points="0,24 10,20 20,22 30,16 40,19 50,13 60,15 70,8 80,11" style="animation-delay:.6s"/></svg>
            </div>

            <div class="dh-kpi dh-kpi-red p-5 dh-fade" data-dh style="transition-delay:.24s">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[#ef4444]/20 to-[#dc2626]/20 flex items-center justify-center text-xl">&#x1F514;</div>
                    <span x-show="(d.unread_alerts||0) > 0" class="text-[9px] font-bold uppercase tracking-widest text-red-400 bg-red-50 px-2 py-1 rounded-lg animate-bounce">Unread</span>
                </div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Active Alerts</p>
                <h3 class="text-3xl font-black text-red-500 leading-none mt-1" data-val="unread_alerts">—</h3>
                <p class="text-[10px] text-gray-400 mt-1">Unresolved incidents</p>
                <div class="dh-bar mt-3"><div class="dh-bar-fill" style="background:linear-gradient(90deg,#ef4444,#dc2626)" data-pct-key="alerts_pct"></div></div>
                <svg class="w-full mt-2.5 h-8" viewBox="0 0 80 24" fill="none"><polyline class="dh-spark" stroke="#ef4444" stroke-width="1.8" points="0,16 10,20 20,14 30,19 40,10 50,16 60,8 70,13 80,6" style="animation-delay:.75s"/></svg>
            </div>

        </section>

        {{-- CHARTS --}}
        <section class="grid lg:grid-cols-5 gap-5">
            <div class="adm-chart-glass p-6 lg:col-span-2 dh-fade" data-dh style="transition-delay:.06s">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h4 class="text-sm font-bold adm-grad-text">Risk Distribution</h4>
                        <p class="text-[10px] text-gray-400 mt-0.5">Population snapshot</p>
                    </div>
                    <div class="flex items-center gap-1.5 bg-emerald-50 px-2.5 py-1.5 rounded-xl text-[10px] text-emerald-600 font-bold">
                        <div class="dh-live !w-2 !h-2"></div> Live
                    </div>
                </div>
                <div class="relative flex items-center justify-center">
                    <canvas id="riskChart" class="max-h-56"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none" style="padding-bottom:3rem">
                        <p class="text-2xl font-black adm-grad-text leading-none" x-text="d.total_users || 0"></p>
                        <p class="text-[10px] text-gray-400 mt-0.5">total users</p>
                    </div>
                </div>
            </div>

            <div class="adm-chart-glass p-6 lg:col-span-3 dh-fade" data-dh style="transition-delay:.12s">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h4 class="text-sm font-bold adm-grad-text">Simulation Activity</h4>
                        <p class="text-[10px] text-gray-400 mt-0.5">Last 30 days trend</p>
                    </div>
                    <div class="flex items-center gap-1.5 text-[10px] text-gray-400">
                        <span class="inline-block w-4 h-1.5 rounded-full bg-gradient-to-r from-purple-500 to-pink-500"></span>Simulations
                    </div>
                </div>
                <div class="h-56"><canvas id="simChart"></canvas></div>
            </div>
        </section>

        {{-- RISK TIERS + QUICK ACTIONS --}}
        <section class="grid lg:grid-cols-3 gap-5">

            <div class="adm-chart-glass p-6 dh-fade" data-dh style="transition-delay:.05s">
                <h4 class="text-sm font-bold adm-grad-text mb-5">Risk Tier Breakdown</h4>
                <div class="space-y-4">
                    <template x-for="tier in riskTiers()" :key="tier.label">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" :class="tier.dot"></div>
                                    <span class="text-xs font-semibold text-gray-600" x-text="tier.label"></span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-black text-gray-800" x-text="tier.count"></span>
                                    <span class="text-[10px] text-gray-400" x-text="'(' + tier.pct + '%)'"></span>
                                </div>
                            </div>
                            <div class="dh-bar">
                                <div class="dh-bar-fill rounded-full" :class="tier.bar" :style="'width:' + tier.pct + '%'"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="lg:col-span-2 dh-fade" data-dh style="transition-delay:.10s">
                <h4 class="text-sm font-bold adm-grad-text mb-4">Quick Actions</h4>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <a href="{{ route('admin.users') }}" class="dh-action p-4">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#5f6fff] to-[#c24dff] flex items-center justify-center text-xl mb-3 shadow-md" style="animation:popIn .6s ease .1s both">&#x1F465;</div>
                        <p class="text-sm font-bold text-gray-700">User Monitoring</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">Live profiles &amp; vitals</p>
                    </a>
                    <a href="{{ route('admin.risk-analysis') }}" class="dh-action p-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl mb-3 shadow-md" style="background:linear-gradient(135deg,#f59e0b,#fbbf24);animation:popIn .6s ease .2s both">&#x1F4C8;</div>
                        <p class="text-sm font-bold text-gray-700">Risk Analysis</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">Population insights</p>
                    </a>
                    <a href="{{ route('admin.simulations') }}" class="dh-action p-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl mb-3 shadow-md" style="background:linear-gradient(135deg,#c24dff,#a855f7);animation:popIn .6s ease .3s both">&#x26A1;</div>
                        <p class="text-sm font-bold text-gray-700">Simulations</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">Run history &amp; logs</p>
                    </a>
                    <a href="{{ route('admin.alerts') }}" class="dh-action p-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl mb-3 shadow-md" style="background:linear-gradient(135deg,#ef4444,#dc2626);animation:popIn .6s ease .4s both">&#x1F6A8;</div>
                        <p class="text-sm font-bold text-gray-700">Alert Oversight</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">Incident management</p>
                    </a>
                    <a href="{{ route('admin.reports') }}" class="dh-action p-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl mb-3 shadow-md" style="background:linear-gradient(135deg,#3b82f6,#60a5fa);animation:popIn .6s ease .5s both">&#x1F4D1;</div>
                        <p class="text-sm font-bold text-gray-700">Reports</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">Analytics &amp; exports</p>
                    </a>
                    <a href="{{ route('admin.rag') }}" class="dh-action p-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl mb-3 shadow-md" style="background:linear-gradient(135deg,#10b981,#34d399);animation:popIn .6s ease .6s both">&#x1F4DA;</div>
                        <p class="text-sm font-bold text-gray-700">RAG Knowledge</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">AI knowledge base</p>
                    </a>
                </div>
            </div>

        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
function adminDashboard() {
    return {
        loading: true, d: {}, report: {},

        async init() {
            const [dash, rep] = await Promise.all([
                api.get('/admin/dashboard'),
                api.get('/admin/reports?period_days=30')
            ]);
            if (dash.success) this.d = dash.data;
            if (rep.success)  this.report = rep.data;
            this.loading = false;
            this.$nextTick(() => {
                this.populateValues();
                this.animateBars();
                this.drawCharts();
                this.runEntranceAnimations();
            });
        },

        populateValues() {
            const high = (this.d.risk_distribution?.high || 0) + (this.d.risk_distribution?.critical || 0);
            const map = {
                total_users:       this.d.total_users       ?? 0,
                new_users_7d:      this.d.new_users_7d      ?? 0,
                avg_risk_score:    this.d.avg_risk_score     ?? 0,
                simulations_today: this.d.simulations_today ?? 0,
                simulations_total: this.d.simulations_total ?? 0,
                simulations_week:  this.d.simulations_week  ?? 0,
                unread_alerts:     this.d.unread_alerts      ?? 0,
                high_risk:         high,
            };
            document.querySelectorAll('[data-val]').forEach(el => {
                const raw = parseFloat(map[el.getAttribute('data-val')]) || 0;
                this.countUp(el, raw);
            });
        },

        countUp(el, target) {
            const isFloat = target % 1 !== 0;
            const duration = 1100;
            const start = performance.now();
            const tick = (now) => {
                const t = Math.min((now - start) / duration, 1);
                const ease = 1 - Math.pow(1 - t, 3);
                el.textContent = isFloat ? (target * ease).toFixed(1) : Math.round(target * ease);
                if (t < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        },

        animateBars() {
            const total = this.d.total_users || 1;
            const high  = (this.d.risk_distribution?.high || 0) + (this.d.risk_distribution?.critical || 0);
            const map = {
                total_users_pct:  Math.min((this.d.total_users       || 0) / 100 * 100, 100),
                avg_risk_pct:     Math.min((parseFloat(this.d.avg_risk_score) || 0) / 10 * 100, 100),
                high_risk_pct:    total ? (high / total * 100) : 0,
                simulations_pct:  Math.min((this.d.simulations_today  || 0) / 50 * 100, 100),
                week_pct:         Math.min((this.d.simulations_week   || 0) / 200 * 100, 100),
                alerts_pct:       Math.min((this.d.unread_alerts       || 0) / 20 * 100, 100),
            };
            requestAnimationFrame(() => {
                document.querySelectorAll('[data-pct-key]').forEach(el => {
                    el.style.width = (map[el.getAttribute('data-pct-key')] || 0) + '%';
                });
            });
        },

        riskTiers() {
            const rd = this.d.risk_distribution || {};
            const total = this.d.total_users || 1;
            const pct = n => Math.round((n / total) * 100);
            return [
                { label:'Low Risk',      count:rd.low      || 0, dot:'bg-emerald-400', bar:'bg-gradient-to-r from-emerald-400 to-teal-400',   pct:pct(rd.low      || 0) },
                { label:'Moderate Risk', count:rd.moderate || 0, dot:'bg-amber-400',   bar:'bg-gradient-to-r from-amber-400  to-yellow-400',   pct:pct(rd.moderate || 0) },
                { label:'High Risk',     count:rd.high     || 0, dot:'bg-orange-500',  bar:'bg-gradient-to-r from-orange-400 to-red-400',      pct:pct(rd.high     || 0) },
                { label:'Critical',      count:rd.critical || 0, dot:'bg-red-600',     bar:'bg-gradient-to-r from-red-500    to-red-800',      pct:pct(rd.critical || 0) },
            ];
        },

        runEntranceAnimations() {
            const io = new IntersectionObserver(entries => {
                entries.forEach(e => {
                    if (e.isIntersecting) {
                        const delay = parseFloat(e.target.style.transitionDelay || '0') * 1000;
                        setTimeout(() => e.target.classList.add('dh-in'), delay);
                        io.unobserve(e.target);
                    }
                });
            }, { threshold: 0.08 });
            document.querySelectorAll('[data-dh]').forEach(el => {
                const r = el.getBoundingClientRect();
                if (r.top < window.innerHeight) {
                    setTimeout(() => el.classList.add('dh-in'), parseFloat(el.style.transitionDelay || '0') * 1000);
                } else {
                    io.observe(el);
                }
            });
        },

        drawCharts() { this.drawRiskChart(); this.drawSimChart(); },

        drawRiskChart() {
            const rd  = this.d.risk_distribution || {};
            const ctx = document.getElementById('riskChart');
            if (!ctx) return;
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Low', 'Moderate', 'High', 'Critical'],
                    datasets: [{
                        data: [rd.low||0, rd.moderate||0, rd.high||0, rd.critical||0],
                        backgroundColor: ['#10b981','#f59e0b','#ef4444','#7f1d1d'],
                        borderWidth: 3, borderColor: 'rgba(255,255,255,.85)',
                        hoverOffset: 14, hoverBorderColor: '#fff',
                    }]
                },
                options: {
                    cutout: '70%',
                    animation: { animateRotate:true, duration:1400, easing:'easeInOutQuart' },
                    plugins: {
                        legend: { position:'bottom', labels:{ padding:16, usePointStyle:true, pointStyleWidth:9, font:{size:10,weight:'700'}, color:'#6b7280' } },
                        tooltip: { backgroundColor:'rgba(255,255,255,.96)', titleColor:'#1f2937', bodyColor:'#6b7280', borderColor:'rgba(0,0,0,.08)', borderWidth:1, cornerRadius:12, padding:12 }
                    }
                }
            });
        },

        drawSimChart() {
            const ds  = this.report.daily_simulations || [];
            const ctx = document.getElementById('simChart');
            if (!ctx || !ds.length) return;
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ds.map(d => { const dt = new Date(d.date); return dt.toLocaleDateString('en',{month:'short',day:'numeric'}); }),
                    datasets: [{
                        label: 'Simulations', data: ds.map(d => d.count),
                        borderColor: '#8b5cf6',
                        backgroundColor: ctx => {
                            const g = ctx.chart.ctx.createLinearGradient(0,0,0,ctx.chart.height);
                            g.addColorStop(0,'rgba(139,92,246,.28)');
                            g.addColorStop(1,'rgba(139,92,246,.0)');
                            return g;
                        },
                        fill:true, tension:0.45, borderWidth:2.5,
                        pointRadius:4, pointBackgroundColor:'#8b5cf6',
                        pointBorderColor:'#fff', pointBorderWidth:2, pointHoverRadius:7,
                    }]
                },
                options: {
                    animation: { duration:1500, easing:'easeInOutQuart' },
                    scales: {
                        x: { grid:{display:false}, border:{display:false}, ticks:{font:{size:9},color:'#9ca3af',maxTicksLimit:9} },
                        y: { beginAtZero:true, grid:{color:'rgba(0,0,0,.04)'}, border:{display:false}, ticks:{font:{size:9},color:'#9ca3af'} }
                    },
                    plugins: {
                        legend: { display:false },
                        tooltip: { backgroundColor:'rgba(255,255,255,.96)', titleColor:'#1f2937', bodyColor:'#6b7280', borderColor:'rgba(0,0,0,.08)', borderWidth:1, cornerRadius:12, padding:12 }
                    }
                }
            });
        }
    };
}
</script>
@endpush
