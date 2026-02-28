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
</style>
@endpush

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
                            <div class="absolute inset-y-0 left-0 rounded-full transition-all"
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
        loading: true, generating: false, twin: null, history: [],
        scoreCards: [
            {key:'metabolic_health_score',label:'Metabolic Health',from:'#5f6fff',to:'#c24dff'},
            {key:'insulin_resistance_score',label:'Insulin Resist.',from:'#c24dff',to:'#ff6ec7'},
            {key:'sleep_score',label:'Sleep Quality',from:'#3b82f6',to:'#8b5cf6'},
            {key:'stress_score',label:'Stress Level',from:'#f59e0b',to:'#ef4444'},
            {key:'diet_score',label:'Diet Quality',from:'#10b981',to:'#06b6d4'},
        ],
        async init(){
            const [active, all] = await Promise.all([api.get('/digital-twin/active'), api.get('/digital-twin')]);
            if(active.success && active.data) this.twin = active.data;
            if(all.success && all.data) this.history = all.data;
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
