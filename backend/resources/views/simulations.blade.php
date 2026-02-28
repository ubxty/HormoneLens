@extends('layouts.app')
@section('title','Simulations — HormoneLens')
@section('heading','Health Simulations')

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
.gl-input{width:100%;padding:.45rem .75rem;border:1.5px solid rgba(0,0,0,.08);border-radius:12px;background:rgba(255,255,255,.7);font-size:.875rem;color:#1f2937;outline:none;transition:border-color .3s,box-shadow .3s,background .3s}
.gl-input:focus{border-color:rgba(194,77,255,.45);box-shadow:0 0 0 3px rgba(194,77,255,.1),0 0 12px rgba(95,111,255,.06);background:rgba(255,255,255,.9)}
.gl-input::placeholder{color:#b0b0b8}
.gl-btn{position:relative;padding:.6rem 1.75rem;border:none;border-radius:14px;font-weight:700;font-size:.875rem;color:#fff;background:linear-gradient(135deg,#5f6fff,#c24dff,#ff6ec7);background-size:200% 200%;animation:glBG 4s ease infinite;cursor:pointer;overflow:hidden;transition:transform .25s ease,box-shadow .25s ease;outline:none;width:100%}
.gl-btn:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 8px 28px rgba(194,77,255,.3),0 0 16px rgba(95,111,255,.15)}
.gl-btn:disabled{opacity:.55;cursor:not-allowed}
@keyframes glBG{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}
@keyframes glUp{from{opacity:0;transform:translateY(28px)}to{opacity:1;transform:translateY(0)}}
.gl-a{opacity:0;transform:translateY(28px)}.gl-a.gl-v{animation:glUp .65s cubic-bezier(.4,0,.2,1) forwards}
.gl-d0{animation-delay:0s!important}.gl-d1{animation-delay:.06s!important}.gl-d2{animation-delay:.12s!important}.gl-d3{animation-delay:.18s!important}.gl-d4{animation-delay:.24s!important}
@keyframes glPulse{0%,100%{opacity:.5;transform:scale(1)}50%{opacity:1;transform:scale(1.3)}}
.gl-status-pulse{animation:glPulse 2s ease-in-out infinite}
.gl-icon{width:32px;height:32px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0}
.gl-result-positive{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.15);border-radius:16px}
.gl-result-negative{background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.15);border-radius:16px}
.gl-sim-item{background:rgba(255,255,255,.45);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.3);border-radius:14px;transition:transform .3s ease,box-shadow .3s ease}
.gl-sim-item:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(95,111,255,.1)}
</style>
@endpush

@section('content')
<div x-data="simulationsPage()" x-init="init()" class="gl-bg -m-4 sm:-m-6 p-4 sm:p-6">
    <div class="gl-p gl-p1"></div>
    <div class="gl-p gl-p2"></div>

    <div class="max-w-4xl mx-auto relative">

        {{-- Hero --}}
        <div class="gl-hero px-5 py-4 mb-4 gl-a gl-d0" data-gl>
            <div class="relative z-10 flex items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <div class="w-2 h-2 rounded-full bg-emerald-400 gl-status-pulse"></div>
                        <span class="text-white/70 text-[11px] font-medium tracking-wide uppercase">Prediction Engine</span>
                    </div>
                    <h1 class="text-lg font-bold text-white">⚡ Health Simulations</h1>
                </div>
                <p class="text-white/60 text-xs hidden sm:block max-w-[220px]">Predict how meals, sleep & stress affect your metabolic health.</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-5 gap-4">
            {{-- Run form --}}
            <div class="lg:col-span-2">
                <div class="gl-card p-5 sticky top-20 gl-a gl-d1" data-gl>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="gl-icon bg-purple-100 text-purple-600">🎯</div>
                        <h2 class="text-xs font-bold uppercase tracking-widest gl-grad-text">Run Simulation</h2>
                    </div>
                    <form @submit.prevent="run()" class="space-y-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Type</label>
                            <select x-model="form.type" required class="gl-input bg-transparent">
                                <option value="meal">🍽️ Meal</option>
                                <option value="sleep">😴 Sleep</option>
                                <option value="stress">😰 Stress</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Description</label>
                            <textarea x-model="form.description" rows="2" required maxlength="500"
                                      class="gl-input" style="resize:vertical;min-height:52px"
                                      placeholder="e.g. Ate 2 samosas and a large cola at lunch"></textarea>
                        </div>
                        <div x-show="form.type==='meal'">
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Meal Detail</label>
                            <input type="text" x-model="form.parameters.meal_description" class="gl-input" placeholder="Fried snacks, sugary drink">
                        </div>
                        <div x-show="form.type==='sleep'">
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Sleep Hours</label>
                            <input type="number" step="0.5" min="0" max="24" x-model="form.parameters.sleep_hours" class="gl-input" placeholder="e.g. 4">
                        </div>
                        <div x-show="form.type==='stress'">
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Stress Level</label>
                            <select x-model="form.parameters.stress_level" class="gl-input bg-transparent">
                                <option value="">Select…</option>
                                <option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option>
                            </select>
                        </div>
                        <button type="submit" :disabled="running" class="gl-btn">
                            <span x-show="!running">⚡ Run Simulation</span>
                            <span x-show="running" class="flex items-center justify-center gap-2">
                                <span class="inline-block w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                                Running…
                            </span>
                        </button>
                    </form>

                    {{-- Result --}}
                    <div x-show="result" x-transition class="mt-4 p-4 rounded-2xl" :class="result?.risk_change > 0 ? 'gl-result-positive' : 'gl-result-negative'">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-2xl font-black gl-grad-text"
                                  x-text="(result?.risk_change > 0 ? '+' : '') + result?.risk_change?.toFixed(2)"></span>
                            <span class="text-xs text-gray-400">risk change</span>
                        </div>
                        <div class="flex gap-2 text-xs mb-3">
                            <span class="px-2 py-0.5 rounded-full capitalize"
                                  :class="catColor(result?.risk_category_before)" x-text="result?.risk_category_before"></span>
                            <span class="text-gray-300">→</span>
                            <span class="px-2 py-0.5 rounded-full capitalize"
                                  :class="catColor(result?.risk_category_after)" x-text="result?.risk_category_after"></span>
                        </div>
                        <div x-show="result?.rag_explanation" class="gl-card p-3 mb-2" style="transform:none">
                            <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">💡 AI Explanation</p>
                            <p class="text-xs text-gray-600" x-text="result?.rag_explanation"></p>
                            <p x-show="result?.rag_confidence" class="text-[10px] text-gray-400 mt-1" x-text="'Confidence: '+(result?.rag_confidence*100).toFixed(0)+'%'"></p>
                        </div>
                        <div x-show="result?.alerts?.length" class="space-y-1">
                            <template x-for="a in result?.alerts??[]" :key="a.id">
                                <div class="text-xs p-2 rounded-lg" :class="a.severity==='critical'?'bg-red-100/70 text-red-700':'bg-amber-100/70 text-amber-700'">
                                    <strong x-text="a.title"></strong>: <span x-text="a.message"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- History --}}
            <div class="lg:col-span-3">
                <div class="gl-card p-5 gl-a gl-d2" data-gl>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="gl-icon bg-indigo-100 text-indigo-600">📋</div>
                        <h2 class="text-xs font-bold uppercase tracking-widest gl-grad-text">Recent Simulations</h2>
                    </div>
                    <div x-show="loading" class="text-center py-8"><div class="inline-block w-6 h-6 border-[3px] border-purple-400 border-t-transparent rounded-full animate-spin"></div></div>
                    <div x-show="!loading && sims.length===0" class="text-center py-8">
                        <div class="text-4xl mb-2">⚡</div>
                        <p class="text-xs text-gray-400">No simulations yet. Run your first one!</p>
                    </div>
                    <div class="space-y-2.5">
                        <template x-for="s in sims" :key="s.id">
                            <div class="gl-sim-item p-3 cursor-pointer" @click="s._open = !s._open">
                                <div class="flex items-center gap-3">
                                    <div class="gl-icon" :class="s.type==='meal'?'bg-orange-100 text-orange-600':s.type==='sleep'?'bg-blue-100 text-blue-600':'bg-red-100 text-red-600'"
                                         x-text="s.type==='meal'?'🍽️':s.type==='sleep'?'😴':'😰'"></div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-semibold text-gray-700 truncate" x-text="s.input_data?.description || s.type"></p>
                                        <p class="text-[10px] text-gray-400" x-text="new Date(s.created_at).toLocaleString()"></p>
                                    </div>
                                    <span class="text-sm font-black" :class="s.risk_change>0?'text-red-500':'text-emerald-500'"
                                          x-text="(s.risk_change>0?'+':'')+s.risk_change.toFixed(2)"></span>
                                </div>
                                <div x-show="s._open" x-collapse class="mt-3 text-xs border-t border-white/30 pt-3 space-y-2">
                                    <p class="text-gray-500"><strong class="text-gray-700">Before:</strong> <span x-text="s.original_risk_score?.toFixed(2)"></span>
                                       (<span class="capitalize" x-text="s.risk_category_before"></span>)
                                       → <strong class="text-gray-700">After:</strong> <span x-text="s.simulated_risk_score?.toFixed(2)"></span>
                                       (<span class="capitalize" x-text="s.risk_category_after"></span>)</p>
                                    <div x-show="s.rag_explanation" class="bg-white/50 p-2 rounded-lg text-gray-500">
                                        💡 <span x-text="s.rag_explanation"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function simulationsPage() {
    return {
        loading: true, running: false, result: null, sims: [],
        form: { type:'meal', description:'', parameters: { meal_description:'', sleep_hours:'', stress_level:'' } },
        catColor(c){ return c==='high'?'bg-red-100 text-red-700':c==='medium'?'bg-amber-100 text-amber-700':'bg-emerald-100 text-emerald-700'; },
        async init(){
            const r = await api.get('/simulations');
            if(r.success) this.sims = (r.data||[]).map(s=>({...s,_open:false}));
            this.loading = false;
            this.$nextTick(()=>document.querySelectorAll('[data-gl]').forEach(el=>el.classList.add('gl-v')));
        },
        async run(){
            this.running = true; this.result = null;
            const payload = { type: this.form.type, description: this.form.description, parameters: {} };
            if(this.form.type==='meal' && this.form.parameters.meal_description) payload.parameters.meal_description = this.form.parameters.meal_description;
            if(this.form.type==='sleep' && this.form.parameters.sleep_hours) payload.parameters.sleep_hours = parseFloat(this.form.parameters.sleep_hours);
            if(this.form.type==='stress' && this.form.parameters.stress_level) payload.parameters.stress_level = this.form.parameters.stress_level;
            const r = await api.post('/simulations/run', payload);
            if(r.success){ this.result = r.data; toast('Simulation complete!'); this.sims.unshift({...r.data, _open:false}); }
            else toast(r.message || 'Simulation failed. Make sure your Digital Twin is generated.', 'error');
            this.running = false;
        }
    };
}
</script>
@endpush
