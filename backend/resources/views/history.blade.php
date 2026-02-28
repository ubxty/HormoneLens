@extends('layouts.app')
@section('title','History — HormoneLens')
@section('heading','Simulation History')

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
@keyframes glUp{from{opacity:0;transform:translateY(28px)}to{opacity:1;transform:translateY(0)}}
.gl-a{opacity:0;transform:translateY(28px)}.gl-a.gl-v{animation:glUp .65s cubic-bezier(.4,0,.2,1) forwards}
.gl-d0{animation-delay:0s!important}.gl-d1{animation-delay:.06s!important}.gl-d2{animation-delay:.12s!important}.gl-d3{animation-delay:.18s!important}
@keyframes glPulse{0%,100%{opacity:.5;transform:scale(1)}50%{opacity:1;transform:scale(1.3)}}
.gl-status-pulse{animation:glPulse 2s ease-in-out infinite}
.gl-filter{background:rgba(255,255,255,.45);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.3);border-radius:12px;padding:5px 14px;font-size:11px;font-weight:700;color:#6b7280;cursor:pointer;transition:all .3s ease;text-transform:uppercase;letter-spacing:.05em}
.gl-filter:hover{background:rgba(194,77,255,.08);color:#7c3aed}
.gl-filter-active{background:linear-gradient(135deg,#5f6fff,#c24dff);color:#fff;border-color:transparent}
.gl-stat{background:rgba(255,255,255,.35);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.25);border-radius:12px;padding:.65rem .8rem}
.gl-btn{background:linear-gradient(135deg,#5f6fff,#c24dff);color:#fff;border:none;border-radius:12px;padding:5px 16px;font-size:11px;font-weight:700;cursor:pointer;transition:all .3s ease;text-transform:uppercase;letter-spacing:.03em}
.gl-btn:hover{filter:brightness(1.1);box-shadow:0 4px 20px rgba(95,111,255,.3)}
.gl-btn:disabled{opacity:.5;cursor:not-allowed}
.gl-insight{background:rgba(95,111,255,.08);backdrop-filter:blur(8px);border:1px solid rgba(95,111,255,.12);border-radius:12px;padding:.75rem .9rem}
.gl-chevron{transition:transform .25s ease}
.gl-chevron-open{transform:rotate(180deg)}
</style>
@endpush

@section('content')
<div x-data="historyPage()" x-init="init()" class="gl-bg -m-4 sm:-m-6 p-4 sm:p-6">
    <div class="gl-p gl-p1"></div>
    <div class="gl-p gl-p2"></div>

    <div class="max-w-4xl mx-auto relative">

        {{-- Hero --}}
        <div class="gl-hero px-5 py-4 mb-4 gl-a gl-d0" data-gl>
            <div class="relative z-10">
                <div class="flex items-center gap-1.5 mb-1">
                    <div class="w-2 h-2 rounded-full bg-emerald-400 gl-status-pulse"></div>
                    <span class="text-white/70 text-[11px] font-medium tracking-wide uppercase">History Log</span>
                </div>
                <h1 class="text-lg font-bold text-white">📊 Simulation History</h1>
            </div>
        </div>

        {{-- Filters --}}
        <div class="flex gap-2 mb-4 flex-wrap gl-a gl-d1" data-gl>
            <button @click="typeFilter='all'" class="gl-filter" :class="typeFilter==='all'?'gl-filter-active':''">All</button>
            <button @click="typeFilter='meal'" class="gl-filter" :class="typeFilter==='meal'?'gl-filter-active':''">🍽️ Meal</button>
            <button @click="typeFilter='sleep'" class="gl-filter" :class="typeFilter==='sleep'?'gl-filter-active':''">😴 Sleep</button>
            <button @click="typeFilter='stress'" class="gl-filter" :class="typeFilter==='stress'?'gl-filter-active':''">😰 Stress</button>
        </div>

        {{-- Loading --}}
        <div x-show="loading" class="text-center py-10">
            <div class="inline-block w-8 h-8 border-[3px] border-purple-400 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-xs text-gray-400 mt-2">Loading history…</p>
        </div>

        {{-- Empty --}}
        <div x-show="!loading && filtered.length===0" class="gl-card p-8 text-center gl-a gl-d2" data-gl>
            <div class="text-4xl mb-2">📊</div>
            <p class="text-xs text-gray-400">No simulations found.</p>
        </div>

        {{-- History list --}}
        <div class="space-y-2.5">
            <template x-for="s in filtered" :key="s.id">
                <div class="gl-card p-0">
                    {{-- Header row --}}
                    <div class="flex items-center gap-3 px-4 py-3 cursor-pointer" @click="s._open = !s._open">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm flex-shrink-0"
                             :class="s.type==='meal'?'bg-orange-100 text-orange-600':s.type==='sleep'?'bg-indigo-100 text-indigo-600':'bg-rose-100 text-rose-600'">
                            <span x-text="s.type==='meal'?'🍽️':s.type==='sleep'?'😴':'😰'"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-gray-700 truncate" x-text="s.input_data?.description || s.type"></p>
                            <p class="text-[10px] text-gray-400" x-text="new Date(s.created_at).toLocaleString()"></p>
                        </div>
                        <span class="text-xs font-bold" :class="s.risk_change > 0 ? 'text-red-500' : 'text-emerald-500'"
                              x-text="(s.risk_change > 0 ? '+' : '') + s.risk_change.toFixed(2)"></span>
                        <span class="text-gray-400 text-xs gl-chevron" :class="s._open ? 'gl-chevron-open' : ''">▾</span>
                    </div>

                    {{-- Expanded detail --}}
                    <div x-show="s._open" x-collapse class="px-4 pb-3 pt-0 space-y-2.5 border-t border-white/30">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-2.5">
                            <div class="gl-stat">
                                <p class="text-[10px] text-gray-500 mb-0.5">Risk Before</p>
                                <p class="text-xs font-bold text-gray-800" x-text="s.original_risk_score?.toFixed(2)"></p>
                                <span class="text-[9px] capitalize px-1.5 py-0.5 rounded-full font-bold" :class="catColor(s.risk_category_before)" x-text="s.risk_category_before"></span>
                            </div>
                            <div class="gl-stat">
                                <p class="text-[10px] text-gray-500 mb-0.5">Risk After</p>
                                <p class="text-xs font-bold text-gray-800" x-text="s.simulated_risk_score?.toFixed(2)"></p>
                                <span class="text-[9px] capitalize px-1.5 py-0.5 rounded-full font-bold" :class="catColor(s.risk_category_after)" x-text="s.risk_category_after"></span>
                            </div>
                            <div class="gl-stat">
                                <p class="text-[10px] text-gray-500 mb-0.5">Change</p>
                                <p class="text-xs font-bold" :class="s.risk_change > 0 ? 'text-red-600' : 'text-emerald-600'"
                                   x-text="(s.risk_change>0?'+':'')+s.risk_change.toFixed(2)"></p>
                            </div>
                            <div class="gl-stat">
                                <p class="text-[10px] text-gray-500 mb-0.5">Type</p>
                                <p class="text-xs font-bold capitalize text-gray-800" x-text="s.type"></p>
                            </div>
                        </div>

                        <div x-show="s.rag_explanation" class="gl-insight">
                            <p class="font-bold text-xs text-gray-700 mb-0.5">💡 AI Insight</p>
                            <p class="text-xs text-gray-600" x-text="s.rag_explanation"></p>
                            <p x-show="s.rag_confidence" class="text-[10px] text-gray-400 mt-1" x-text="'Confidence: '+(s.rag_confidence*100).toFixed(0)+'%'"></p>
                        </div>

                        <div>
                            <button @click="rerun(s)" :disabled="s._rerunning" class="gl-btn">
                                <span x-show="!s._rerunning">🔄 Rerun</span><span x-show="s._rerunning">Running…</span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function historyPage() {
    return {
        loading: true, history: [], typeFilter: 'all',
        get filtered(){
            return this.history.filter(s => this.typeFilter==='all' || s.type===this.typeFilter);
        },
        catColor(c){ return c==='high'?'bg-red-100 text-red-700':c==='medium'?'bg-amber-100 text-amber-700':'bg-emerald-100 text-emerald-700'; },
        async init(){
            const r = await api.get('/history');
            if(r.success) this.history = (r.data || []).map(s => ({...s, _open:false, _rerunning:false}));
            this.loading = false;
            this.$nextTick(()=>document.querySelectorAll('[data-gl]').forEach(el=>el.classList.add('gl-v')));
        },
        async rerun(s){
            s._rerunning = true;
            const r = await api.post('/history/' + s.id + '/rerun');
            if(r.success){ toast('Simulation rerun complete!'); this.history.unshift({...r.data, _open:true, _rerunning:false}); }
            else toast(r.message || 'Rerun failed', 'error');
            s._rerunning = false;
        }
    };
}
</script>
@endpush
