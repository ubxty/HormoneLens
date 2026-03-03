@extends('layouts.app')
@section('title','Digital Twin — HormoneLens')
@section('heading','Digital Twin Model')

@push('styles')
<style>
.gl-bg{background:linear-gradient(135deg,rgba(95,111,255,.06),rgba(194,77,255,.06) 50%,rgba(255,110,199,.06));min-height:100%;position:relative;overflow:hidden}
.gl-p{position:absolute;border-radius:50%;filter:blur(80px);pointer-events:none;opacity:.10;will-change:transform}
.gl-p1{width:300px;height:300px;background:linear-gradient(135deg,#5f6fff,#c24dff);top:-60px;right:-40px;animation:glF 18s ease-in-out infinite}
.gl-p2{width:240px;height:240px;background:linear-gradient(135deg,#c24dff,#ff6ec7);bottom:5%;left:-30px;animation:glF 22s ease-in-out 5s infinite}
@keyframes glF{0%,100%{transform:translate(0,0) scale(1)}33%{transform:translate(25px,-18px) scale(1.04)}66%{transform:translate(-18px,12px) scale(.96)}}
.gl-hero{background:linear-gradient(135deg,#5f6fff,#c24dff,#ff6ec7);border-radius:22px;position:relative;overflow:hidden}
.gl-hero::after{content:'';position:absolute;inset:0;background:radial-gradient(circle at 85% 25%,rgba(255,255,255,.14) 0%,transparent 55%);pointer-events:none}
.gl-card{background:rgba(255,255,255,.55);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.35);border-radius:16px;box-shadow:0 6px 24px rgba(95,111,255,.07);position:relative;overflow:hidden;transition:transform .4s cubic-bezier(.4,0,.2,1),box-shadow .4s ease,border-color .4s ease}
.gl-card::before{content:'';position:absolute;inset:0;border-radius:16px;padding:1.5px;background:linear-gradient(135deg,rgba(95,111,255,.25),rgba(194,77,255,.2),rgba(255,110,199,.15));-webkit-mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);-webkit-mask-composite:xor;mask-composite:exclude;pointer-events:none;opacity:0;transition:opacity .4s ease}
.gl-card:hover::before{opacity:1}
.gl-card:hover{transform:translateY(-3px);box-shadow:0 12px 36px rgba(95,111,255,.13),0 0 18px rgba(194,77,255,.06);border-color:rgba(194,77,255,.15)}
.gl-grad-text{background:linear-gradient(135deg,#5f6fff,#c24dff,#ff6ec7);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.gl-btn{position:relative;padding:.6rem 1.75rem;border:none;border-radius:14px;font-weight:700;font-size:.875rem;color:#fff;background:linear-gradient(135deg,#5f6fff,#c24dff,#ff6ec7);background-size:200% 200%;animation:glBG 4s ease infinite;cursor:pointer;overflow:hidden;transition:transform .25s ease,box-shadow .25s ease;outline:none}
.gl-btn:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 8px 28px rgba(194,77,255,.3),0 0 16px rgba(95,111,255,.15)}
.gl-btn:disabled{opacity:.55;cursor:not-allowed}
@keyframes glBG{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}
.gl-btn .gl-ripple{position:absolute;border-radius:50%;background:rgba(255,255,255,.35);transform:scale(0);pointer-events:none}
.gl-btn .gl-ripple.active{animation:glRip .6s ease-out forwards}
@keyframes glRip{to{transform:scale(4);opacity:0}}
@keyframes glUp{from{opacity:0;transform:translateY(28px)}to{opacity:1;transform:translateY(0)}}
.gl-a{opacity:0;transform:translateY(28px)}.gl-a.gl-v{animation:glUp .65s cubic-bezier(.4,0,.2,1) forwards}
.gl-d0{animation-delay:0s!important}.gl-d1{animation-delay:.06s!important}.gl-d2{animation-delay:.12s!important}.gl-d3{animation-delay:.18s!important}.gl-d4{animation-delay:.24s!important}.gl-d5{animation-delay:.3s!important}.gl-d6{animation-delay:.36s!important}.gl-d7{animation-delay:.42s!important}
@keyframes glPulse{0%,100%{opacity:.5;transform:scale(1)}50%{opacity:1;transform:scale(1.3)}}
.gl-status-pulse{animation:glPulse 2s ease-in-out infinite}
.gl-ring{width:80px;height:80px;border-radius:50%;position:relative;display:flex;align-items:center;justify-content:center}
.gl-ring svg{position:absolute;inset:0;width:100%;height:100%;transform:rotate(-90deg)}
.gl-table-glass{background:rgba(255,255,255,.45);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.3);border-radius:16px;overflow:hidden}

/* ─── Digital Twin Body Visualization ─── */
@keyframes dtBodyIn{from{opacity:0;transform:translateY(36px) scale(.75)}to{opacity:1;transform:translateY(0) scale(1)}}
@keyframes dtTagFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}
@keyframes dtNodePulse{0%,100%{opacity:.35;box-shadow:0 0 0 0 rgba(91,33,182,.4)}50%{opacity:1;box-shadow:0 0 0 5px rgba(91,33,182,0)}}
@keyframes dtLineDash{to{stroke-dashoffset:-16}}
.dt-body-anim{animation:dtBodyIn 1s cubic-bezier(.4,0,.2,1) both}
.dt-glow{position:absolute;border-radius:50%;filter:blur(22px);pointer-events:none;transform:translateX(-50%);transition:opacity .6s ease}
.dt-node{position:absolute;width:7px;height:7px;border-radius:50%;background:#5b21b6;animation:dtNodePulse 2s ease-in-out infinite;transform:translate(-50%,-50%)}
.dt-tag{position:absolute;background:rgba(255,255,255,.88);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);border:1px solid rgba(139,92,246,.2);border-radius:10px;padding:.3rem .65rem;box-shadow:0 4px 14px rgba(139,92,246,.12);animation:dtTagFloat 3.5s ease-in-out infinite;width:100px;z-index:3}
.dt-conn-line{stroke-dasharray:5 3;animation:dtLineDash 1.5s linear infinite}
</style>
@endpush

@php
$dtBodySvg = '';
$_svgSrc = public_path('images/men.svg');
if (file_exists($_svgSrc)) {
    $_raw = file_get_contents($_svgSrc);
    // Strip white background fill so glassmorphism shows through
    $_raw = preg_replace('/fill="#FEFEFE"/', 'fill="none"', $_raw, 1);
    $dtBodySvg = preg_replace(
        '/<svg\b[^>]*>/',
        '<svg viewBox="183 0 96 350" preserveAspectRatio="xMidYMid meet" style="width:90px;height:auto;display:block;position:relative;z-index:2;pointer-events:none;margin:auto">',
        $_raw,
        1
    );
}
@endphp

@section('content')
<div x-data="digitalTwinPage()" x-init="init()" class="gl-bg -m-4 sm:-m-6 p-4 sm:p-6">
    <div class="gl-p gl-p1"></div>
    <div class="gl-p gl-p2"></div>

    <div class="max-w-4xl mx-auto relative">

        {{-- Hero --}}
        <div class="gl-hero px-5 py-4 mb-4 gl-a gl-d0" data-gl>
            <div class="relative z-10 flex items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <div class="w-2 h-2 rounded-full bg-emerald-400 gl-status-pulse"></div>
                        <span class="text-white/70 text-[11px] font-medium tracking-wide uppercase">Metabolic Model</span>
                    </div>
                    <h1 class="text-lg font-bold text-white">🧬 Digital Twin</h1>
                </div>
                <button @click="generate()" :disabled="generating" class="gl-btn text-sm" @click.self="ripple($event)">
                    <span x-show="!generating" x-text="twin ? '🔄 Regenerate' : '🧬 Generate Twin'"></span>
                    <span x-show="generating" class="flex items-center gap-2">
                        <span class="inline-block w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                        Generating…
                    </span>
                </button>
            </div>
        </div>

        {{-- Loading --}}
        <div x-show="loading" class="text-center py-10">
            <div class="inline-block w-8 h-8 border-[3px] border-purple-400 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-xs text-gray-400 mt-2">Loading digital twin…</p>
        </div>

        {{-- No twin --}}
        <div x-show="!loading && !twin" class="gl-card p-8 text-center gl-a gl-d1" data-gl>
            <div class="text-5xl mb-3">🧬</div>
            <p class="text-sm font-semibold text-gray-700 mb-1">No Digital Twin Found</p>
            <p class="text-xs text-gray-400">Complete your Health Profile & Disease Data, then click Generate.</p>
        </div>

        {{-- Twin display --}}
        <div x-show="!loading && twin" x-cloak>

            {{-- ═══ Body Visualization ═══ --}}
            <div class="gl-card p-5 mb-4 gl-a gl-d1 " data-gl>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xs font-bold uppercase tracking-widest gl-grad-text">🫀 Body Visualization</h2>
                    <span class="text-[11px] text-gray-500 font-medium"
                          x-text="healthProfile ? 'BMI ' + bmiVal().toFixed(1) + ' · ' + bmiLabel() : 'Complete health profile for BMI'"></span>
                </div>

                {{-- Desktop viz frame (hidden on xs) --}}
                <div class="hidden sm:block relative mx-auto " style="height:340px;max-width:750px">

                    {{-- SVG connecting lines --}}
                    <svg class="absolute inset-0 w-full h-full" viewBox="0 0 750 340"
                         preserveAspectRatio="xMidYMid meet" style="z-index:1;pointer-events:none">
                        {{-- Stress → Head --}}
                        <path d="M120,40 C220,40 260,36 330,36"
                              fill="none" stroke="#ef444455" stroke-width="1.5" stroke-linecap="round"
                              class="dt-conn-line"/>
                        {{-- Sleep → Chest --}}
                        <path d="M120,135 C220,135 260,130 330,130"
                              fill="none" stroke="#3b82f655" stroke-width="1.5" stroke-linecap="round"
                              class="dt-conn-line" style="animation-delay:.3s"/>
                        {{-- Metabolic → Abdomen --}}
                        <path d="M120,215 C220,215 260,200 330,200"
                              fill="none" stroke="#a855f755" stroke-width="1.5" stroke-linecap="round"
                              class="dt-conn-line" style="animation-delay:.6s"/>
                        {{-- Insulin → Arm R --}}
                        <path d="M630,118 C530,118 490,128 420,128"
                              fill="none" stroke="#f9731655" stroke-width="1.5" stroke-linecap="round"
                              class="dt-conn-line" style="animation-delay:.15s"/>
                        {{-- Diet → Abdomen R --}}
                        <path d="M630,218 C530,218 490,202 420,202"
                              fill="none" stroke="#10b98155" stroke-width="1.5" stroke-linecap="round"
                              class="dt-conn-line" style="animation-delay:.45s"/>
                    </svg>

                    {{-- Body figure --}}
                    <div class="absolute dt-body-anim w-full"
                         style="left:50%;top:0;transform-origin:top center;overflow:visible"
                         :style="'transform:translateX(-50%) scaleX('+bmiScaleX()+')'">

                        {{-- Risk zone glows --}}
                        <div class="dt-glow" style="width:72px;height:58px;background:rgba(239,68,68,.55);left:50%;top:4px"
                             :style="'opacity:'+(stressRisk()?1:0)"></div>
                        <div class="dt-glow" style="width:82px;height:62px;background:rgba(59,130,246,.55);left:50%;top:94px"
                             :style="'opacity:'+(sleepRisk()?1:0)"></div>
                        <div class="dt-glow" style="width:88px;height:66px;background:rgba(168,85,247,.55);left:50%;top:158px"
                             :style="'opacity:'+(metabolicRisk()?1:0)"></div>
                        <div class="dt-glow" style="width:110px;height:56px;background:rgba(249,115,22,.45);left:50%;top:110px"
                             :style="'opacity:'+(insulinRisk()?1:0)"></div>

                        {{-- Body SVG (cropped to figure) --}}
                        {!! $dtBodySvg !!}

                        {{-- Metabolic nodes --}}
                        {{-- Brain / Stress --}}
                        <div class="dt-node" style="left:50%;top:11%;animation-delay:0s"></div>
                        {{-- Chest / Sleep --}}
                        <div class="dt-node" style="left:50%;top:33%;animation-delay:.45s"></div>
                        {{-- Stomach / Gut --}}
                        <div class="dt-node" style="left:50%;top:47%;animation-delay:.9s"></div>
                        {{-- Left arm / Waist --}}
                        <div class="dt-node" style="left:20%;top:33%;animation-delay:.25s"></div>
                        {{-- Right arm / Waist --}}
                        <div class="dt-node" style="left:80%;top:33%;animation-delay:.7s"></div>
                        {{-- Reproductive / Pelvis --}}
                        <div class="dt-node" style="left:50%;top:62%;animation-delay:1.15s"></div>
                    </div>

                    {{-- Stat tags — left column --}}
                    <div class="dt-tag" style="left:0;top:20px;animation-delay:0s">
                        <p class="text-[9px] text-gray-400 mb-0.5 font-medium">😤 Stress</p>
                        <p class="text-sm font-black gl-grad-text" x-text="(twin?.stress_score||0).toFixed(1)"></p>
                        <div class="h-0.5 rounded-full mt-1.5 transition-all duration-700"
                             style="background:linear-gradient(90deg,#f59e0b,#ef4444)"
                             :style="'width:'+(twin?.stress_score||0)*10+'%'"></div>
                    </div>
                    <div class="dt-tag" style="left:0;top:115px;animation-delay:.7s">
                        <p class="text-[9px] text-gray-400 mb-0.5 font-medium">😴 Sleep</p>
                        <p class="text-sm font-black gl-grad-text" x-text="(twin?.sleep_score||0).toFixed(1)"></p>
                        <div class="h-0.5 rounded-full mt-1.5 transition-all duration-700"
                             style="background:linear-gradient(90deg,#3b82f6,#8b5cf6)"
                             :style="'width:'+(twin?.sleep_score||0)*10+'%'"></div>
                    </div>
                    <div class="dt-tag" style="left:0;top:195px;animation-delay:1.4s">
                        <p class="text-[9px] text-gray-400 mb-0.5 font-medium">⚡ Metabolic</p>
                        <p class="text-sm font-black gl-grad-text" x-text="(twin?.metabolic_health_score||0).toFixed(1)"></p>
                        <div class="h-0.5 rounded-full mt-1.5 transition-all duration-700"
                             style="background:linear-gradient(90deg,#5f6fff,#c24dff)"
                             :style="'width:'+(twin?.metabolic_health_score||0)*10+'%'"></div>
                    </div>

                    {{-- Stat tags — right column --}}
                    <div class="dt-tag" style="right:0;top:99px;animation-delay:.35s">
                        <p class="text-[9px] text-gray-400 mb-0.5 font-medium">🩸 Insulin</p>
                        <p class="text-sm font-black gl-grad-text" x-text="(twin?.insulin_resistance_score||0).toFixed(1)"></p>
                        <div class="h-0.5 rounded-full mt-1.5 transition-all duration-700"
                             style="background:linear-gradient(90deg,#c24dff,#ff6ec7)"
                             :style="'width:'+(twin?.insulin_resistance_score||0)*10+'%'"></div>
                    </div>
                    <div class="dt-tag" style="right:0;top:199px;animation-delay:1.05s">
                        <p class="text-[9px] text-gray-400 mb-0.5 font-medium">🥗 Diet</p>
                        <p class="text-sm font-black gl-grad-text" x-text="(twin?.diet_score||0).toFixed(1)"></p>
                        <div class="h-0.5 rounded-full mt-1.5 transition-all duration-700"
                             style="background:linear-gradient(90deg,#10b981,#06b6d4)"
                             :style="'width:'+(twin?.diet_score||0)*10+'%'"></div>
                    </div>
                </div>

                {{-- Mobile: compact score pills --}}
                <div class="sm:hidden grid grid-cols-2 gap-2 mb-3">
                    <div class="flex items-center gap-2 bg-white/50 rounded-xl p-2.5 border border-white/40">
                        <span class="text-lg">😤</span>
                        <div><p class="text-[9px] text-gray-400">Stress</p><p class="text-sm font-black gl-grad-text" x-text="(twin?.stress_score||0).toFixed(1)"></p></div>
                    </div>
                    <div class="flex items-center gap-2 bg-white/50 rounded-xl p-2.5 border border-white/40">
                        <span class="text-lg">😴</span>
                        <div><p class="text-[9px] text-gray-400">Sleep</p><p class="text-sm font-black gl-grad-text" x-text="(twin?.sleep_score||0).toFixed(1)"></p></div>
                    </div>
                    <div class="flex items-center gap-2 bg-white/50 rounded-xl p-2.5 border border-white/40">
                        <span class="text-lg">⚡</span>
                        <div><p class="text-[9px] text-gray-400">Metabolic</p><p class="text-sm font-black gl-grad-text" x-text="(twin?.metabolic_health_score||0).toFixed(1)"></p></div>
                    </div>
                    <div class="flex items-center gap-2 bg-white/50 rounded-xl p-2.5 border border-white/40">
                        <span class="text-lg">🩸</span>
                        <div><p class="text-[9px] text-gray-400">Insulin</p><p class="text-sm font-black gl-grad-text" x-text="(twin?.insulin_resistance_score||0).toFixed(1)"></p></div>
                    </div>
                </div>

                {{-- BMI Scale bar --}}
                <div class="mt-4" x-show="healthProfile" x-cloak>
                    <div class="flex justify-between text-[9px] text-gray-400 mb-1.5 font-medium uppercase tracking-wide">
                        <span>Underweight</span><span>Normal</span><span>Overweight</span><span>Obese</span>
                    </div>
                    <div class="relative h-2 rounded-full overflow-hidden"
                         style="background:linear-gradient(90deg,#93c5fd 0%,#34d399 28%,#fbbf24 62%,#f87171 100%)">
                        <div class="absolute top-1/2 -translate-y-1/2 w-4 h-4 rounded-full bg-white border-2 border-purple-600 shadow-lg -translate-x-1/2 transition-all duration-700"
                             :style="'left:'+bmiPercent()+'%'"></div>
                    </div>
                    <div class="flex items-center justify-center gap-2 mt-2">
                        <span class="text-xs font-black gl-grad-text" x-text="bmiLabel()"></span>
                        <span class="text-[11px] text-gray-400" x-text="'· BMI ' + bmiVal().toFixed(1)"></span>
                    </div>
                </div>
            </div>
            {{-- ═══ END Body Visualization ═══ --}}

            {{-- Risk ring --}}
            <div class="gl-card p-5 mb-4 gl-a gl-d1" data-gl>
                <div class="flex items-center gap-5">
                    <div class="gl-ring">
                        <svg viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="15.5" fill="none" stroke="rgba(0,0,0,.06)" stroke-width="3"/>
                            <circle cx="18" cy="18" r="15.5" fill="none"
                                    :stroke="twin?.risk_category==='high'?'#ef4444':twin?.risk_category==='medium'?'#f59e0b':'#10b981'"
                                    stroke-width="3" stroke-linecap="round"
                                    :stroke-dasharray="(twin?.overall_risk_score||0)*9.74 + ' 97.4'" />
                        </svg>
                        <span class="text-lg font-black gl-grad-text" x-text="twin?.overall_risk_score?.toFixed(1)"></span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-medium">Overall Risk Score</p>
                        <p class="text-base font-bold capitalize"
                           :class="twin?.risk_category==='high'?'text-red-600':twin?.risk_category==='medium'?'text-amber-600':'text-emerald-600'"
                           x-text="(twin?.risk_category || '') + ' risk'"></p>
                        <p class="text-[11px] text-gray-400 mt-0.5" x-text="'Generated: ' + (twin?.created_at ? new Date(twin.created_at).toLocaleString() : '')"></p>
                    </div>
                </div>
            </div>

            {{-- Score cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-4">
                <template x-for="(s, i) in scoreCards" :key="s.key">
                    <div class="gl-card p-3 gl-a" :class="'gl-d' + (i+2)" data-gl>
                        <p class="text-[11px] text-gray-400 font-medium mb-2" x-text="s.label"></p>
                        <div class="relative h-1.5 rounded-full mb-2" style="background:rgba(0,0,0,.06)">
                            <div class="h-full rounded-full transition-all"
                                 :style="'width:' + (twin?.[s.key]||0)*10 + '%;background:linear-gradient(90deg,' + s.from + ',' + s.to + ')'"></div>
                        </div>
                        <p class="text-lg font-black gl-grad-text" x-text="twin?.[s.key]?.toFixed(1) ?? '—'"></p>
                    </div>
                </template>
            </div>

            {{-- History table --}}
            <div class="gl-table-glass p-5 gl-a gl-d7" data-gl>
                <h2 class="text-xs font-bold uppercase tracking-widest gl-grad-text mb-3">Twin History</h2>
                <div x-show="history.length === 0" class="text-xs text-gray-400 text-center py-4">No previous records.</div>
                <div class="overflow-x-auto">
                    <table x-show="history.length > 0" class="w-full text-xs">
                        <thead><tr class="border-b border-white/30 text-left">
                            <th class="pb-2 font-semibold text-gray-500">Date</th>
                            <th class="pb-2 font-semibold text-gray-500">Overall</th>
                            <th class="pb-2 font-semibold text-gray-500">Metabolic</th>
                            <th class="pb-2 font-semibold text-gray-500">Insulin</th>
                            <th class="pb-2 font-semibold text-gray-500">Sleep</th>
                            <th class="pb-2 font-semibold text-gray-500">Stress</th>
                            <th class="pb-2 font-semibold text-gray-500">Diet</th>
                            <th class="pb-2 font-semibold text-gray-500">Risk</th>
                        </tr></thead>
                        <tbody>
                            <template x-for="h in history" :key="h.id">
                                <tr class="border-b border-white/20 last:border-0" :class="h.is_active ? 'bg-purple-50/40' : ''">
                                    <td class="py-2 text-gray-500" x-text="new Date(h.created_at).toLocaleDateString()"></td>
                                    <td class="py-2 font-bold gl-grad-text" x-text="h.overall_risk_score?.toFixed(1)"></td>
                                    <td class="py-2 text-gray-600" x-text="h.metabolic_health_score?.toFixed(1)"></td>
                                    <td class="py-2 text-gray-600" x-text="h.insulin_resistance_score?.toFixed(1)"></td>
                                    <td class="py-2 text-gray-600" x-text="h.sleep_score?.toFixed(1)"></td>
                                    <td class="py-2 text-gray-600" x-text="h.stress_score?.toFixed(1)"></td>
                                    <td class="py-2 text-gray-600" x-text="h.diet_score?.toFixed(1)"></td>
                                    <td class="py-2"><span class="px-2 py-0.5 text-[10px] rounded-full capitalize font-medium"
                                        :class="h.risk_category==='high'?'bg-red-100 text-red-700':h.risk_category==='medium'?'bg-amber-100 text-amber-700':'bg-emerald-100 text-emerald-700'"
                                        x-text="h.risk_category"></span></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function digitalTwinPage() {
    return {
        loading: true, generating: false, twin: null, history: [], healthProfile: null,
        scoreCards: [
            {key:'metabolic_health_score',label:'Metabolic Health',from:'#5f6fff',to:'#c24dff'},
            {key:'insulin_resistance_score',label:'Insulin Resist.',from:'#c24dff',to:'#ff6ec7'},
            {key:'sleep_score',label:'Sleep Quality',from:'#3b82f6',to:'#8b5cf6'},
            {key:'stress_score',label:'Stress Level',from:'#f59e0b',to:'#ef4444'},
            {key:'diet_score',label:'Diet Quality',from:'#10b981',to:'#06b6d4'},
        ],
        bmiVal(){const hp=this.healthProfile;if(!hp||!hp.height||!hp.weight)return 22;return hp.weight/Math.pow(hp.height/100,2)},
        bmiLabel(){const b=this.bmiVal();return b<18.5?'Underweight':b<25?'Normal':b<30?'Overweight':'Obese'},
        bmiScaleX(){const b=this.bmiVal();return b<18.5?0.82:b<25?1.0:b<30?1.12:1.28},
        bmiPercent(){const b=Math.min(Math.max(this.bmiVal(),14),45);return Math.round((b-14)/31*100)},
        stressRisk(){return(this.twin?.stress_score||0)>=6},
        sleepRisk(){return(this.twin?.sleep_score||0)>=6},
        metabolicRisk(){return(this.twin?.metabolic_health_score||0)>=5},
        insulinRisk(){return(this.twin?.insulin_resistance_score||0)>=5},
        async init(){
            const [active, all, prof] = await Promise.all([
                api.get('/digital-twin/active'),
                api.get('/digital-twin'),
                api.get('/health-profile').catch(()=>({success:false,data:null}))
            ]);
            if(active.success && active.data) this.twin = active.data;
            if(all.success && all.data) this.history = all.data;
            if(prof && prof.success && prof.data) this.healthProfile = prof.data;
            this.loading = false;
            this.$nextTick(()=>document.querySelectorAll('[data-gl]').forEach(el=>el.classList.add('gl-v')));
        },
        async generate(){
            this.generating = true;
            const r = await api.post('/digital-twin/generate');
            if(r.success){ this.twin = r.data; toast('Digital Twin generated!'); await this.refreshHistory(); }
            else toast(r.message || 'Generation failed. Complete your Health Profile & Disease Data first.', 'error');
            this.generating = false;
        },
        async refreshHistory(){ const r = await api.get('/digital-twin'); if(r.success) this.history = r.data; },
        ripple(e){const b=e.currentTarget,c=document.createElement('span'),d=Math.max(b.clientWidth,b.clientHeight),r=b.getBoundingClientRect();c.style.width=c.style.height=d+'px';c.style.left=(e.clientX-r.left-d/2)+'px';c.style.top=(e.clientY-r.top-d/2)+'px';c.classList.add('gl-ripple','active');b.appendChild(c);c.addEventListener('animationend',()=>c.remove())}
    };
}
</script>
@endpush
