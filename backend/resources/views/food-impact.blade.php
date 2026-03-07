@extends('layouts.app')
@section('title','Food Impact — HormoneLens')
@section('heading','Food Impact Analyzer')

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
.gl-d0{animation-delay:0s!important}.gl-d1{animation-delay:.06s!important}.gl-d2{animation-delay:.12s!important}.gl-d3{animation-delay:.18s!important}
@keyframes glPulse{0%,100%{opacity:.5;transform:scale(1)}50%{opacity:1;transform:scale(1.3)}}
.gl-status-pulse{animation:glPulse 2s ease-in-out infinite}
.gl-icon{width:32px;height:32px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0}
.gl-quick{background:rgba(255,255,255,.45);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.3);border-radius:20px;padding:4px 12px;font-size:11px;font-weight:600;color:#6b7280;cursor:pointer;transition:all .3s ease}
.gl-quick:hover{background:rgba(194,77,255,.1);color:#7c3aed;border-color:rgba(194,77,255,.25);transform:translateY(-1px)}
</style>
@endpush

@section('content')
<div x-data="foodImpactPage()" class="gl-bg -m-4 sm:-m-6 p-4 sm:p-6">
    <div class="gl-p gl-p1"></div>
    <div class="gl-p gl-p2"></div>

    <div class="max-w-3xl mx-auto relative">

        {{-- Hero --}}
        <div class="gl-hero px-5 py-4 mb-4 gl-a gl-d0" data-gl>
            <div class="relative z-10 flex items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <div class="w-2 h-2 rounded-full bg-emerald-400 gl-status-pulse"></div>
                        <span class="text-white/70 text-[11px] font-medium tracking-wide uppercase">Nutritional AI</span>
                    </div>
                    <h1 class="text-lg font-bold text-white">🍽️ Food Impact Analyzer</h1>
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            {{-- Input --}}
            <div class="gl-card p-5 gl-a gl-d1" data-gl>
                <div class="flex items-center gap-2 mb-4">
                    <div class="gl-icon bg-orange-100 text-orange-600">🔍</div>
                    <h2 class="text-xs font-bold uppercase tracking-widest gl-grad-text">Analyze a Food</h2>
                </div>
                <form @submit.prevent="analyze()" class="space-y-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Food Item</label>
                        <input type="text" x-model="form.food_item" required maxlength="255"
                               class="gl-input" placeholder="e.g. Gulab Jamun, White Rice, Samosa"
                               id="food-item-input" data-testid="food-item-input">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Quantity <span class="font-normal text-gray-400">(opt.)</span></label>
                            <input type="text" x-model="form.quantity" maxlength="100"
                                   class="gl-input" placeholder="e.g. 1 bowl"
                                   id="food-quantity-input" data-testid="food-quantity-input">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Meal Time <span class="font-normal text-gray-400">(opt.)</span></label>
                            <select x-model="form.meal_time" class="gl-input" id="meal-time-select" data-testid="meal-time-select">
                                <option value="">Auto</option>
                                <option value="morning">🌅 Morning (6–10am)</option>
                                <option value="afternoon">☀️ Afternoon (12–3pm)</option>
                                <option value="evening">🌇 Evening (6–9pm)</option>
                                <option value="night">🌙 Night (10pm–2am)</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" :disabled="analyzing" class="gl-btn" id="analyze-btn" data-testid="analyze-btn">
                        <span x-show="!analyzing">🔍 Analyze Impact</span>
                        <span x-show="analyzing" class="flex items-center justify-center gap-2">
                            <span class="inline-block w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                            Analyzing…
                        </span>
                    </button>
                </form>
                <div class="mt-4 pt-3 border-t border-white/20">
                    <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mb-2">Quick picks</p>
                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="f in quickPicks">
                            <button @click="form.food_item=f; form.quantity='1 serving'; analyze()"
                                    class="gl-quick" x-text="f"></button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Result --}}
            <div class="gl-card p-5 gl-a gl-d2" data-gl>
                <div class="flex items-center gap-2 mb-4">
                    <div class="gl-icon bg-emerald-100 text-emerald-600">📊</div>
                    <h2 class="text-xs font-bold uppercase tracking-widest gl-grad-text">Impact Result</h2>
                </div>
                <div x-show="!result && !analyzing" class="text-center py-10">
                    <div class="text-4xl mb-2">🍽️</div>
                    <p class="text-xs text-gray-400">Enter a food item to see its impact.</p>
                </div>
                <div x-show="analyzing" class="text-center py-10">
                    <div class="inline-block w-8 h-8 border-[3px] border-purple-400 border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-xs text-gray-400 mt-2">Analyzing impact…</p>
                </div>
                <div x-show="result && !analyzing" x-transition class="space-y-3">
                    <div class="p-4 rounded-2xl" :class="result?.risk_change > 0 ? 'bg-red-50/60' : 'bg-emerald-50/60'">
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mb-1">Risk Change</p>
                        <p class="text-3xl font-black gl-grad-text"
                           x-text="(result?.risk_change > 0 ? '+' : '') + result?.risk_change?.toFixed(2)"></p>
                        <div class="flex gap-2 mt-2 text-xs">
                            <span class="px-2 py-0.5 rounded-full capitalize" :class="catColor(result?.risk_category_before)" x-text="result?.risk_category_before"></span>
                            <span class="text-gray-300">→</span>
                            <span class="px-2 py-0.5 rounded-full capitalize" :class="catColor(result?.risk_category_after)" x-text="result?.risk_category_after"></span>
                        </div>
                    </div>

                    {{-- Glycemic Info --}}
                    <div x-show="result?.results?.food_data" class="bg-white/50 rounded-xl p-3">
                        <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">📋 Food Data</p>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div>
                                <p class="text-lg font-black" :class="giColor(result?.results?.food_data?.glycemic_index)" x-text="result?.results?.food_data?.glycemic_index"></p>
                                <p class="text-[9px] text-gray-400 uppercase">GI</p>
                            </div>
                            <div>
                                <p class="text-lg font-black text-purple-600" x-text="result?.results?.peak?.glucose_mg_dl?.toFixed(0)"></p>
                                <p class="text-[9px] text-gray-400 uppercase">Peak mg/dL</p>
                            </div>
                            <div>
                                <p class="text-lg font-black text-blue-600" x-text="result?.results?.peak?.time_minutes + 'min'"></p>
                                <p class="text-[9px] text-gray-400 uppercase">Peak Time</p>
                            </div>
                        </div>
                    </div>

                    {{-- Cross-factor Modifiers --}}
                    <div x-show="result?.results?.modifiers" class="bg-white/50 rounded-xl p-3">
                        <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">⚡ Your Modifiers</p>
                        <div class="space-y-1">
                            <template x-for="(mod, key) in filterModifiers(result?.results?.modifiers)" :key="key">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="text-gray-600" x-text="mod.label"></span>
                                    <span class="font-bold" :class="mod.factor > 1 ? 'text-red-500' : mod.factor < 1 ? 'text-emerald-500' : 'text-gray-400'"
                                          x-text="(mod.factor > 1 ? '+' : '') + ((mod.factor - 1) * 100).toFixed(0) + '%'"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div x-show="result?.rag_explanation" class="bg-white/50 rounded-xl p-3">
                        <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">💡 AI Explanation</p>
                        <p class="text-xs text-gray-600" x-text="result?.rag_explanation"></p>
                        <p x-show="result?.rag_confidence" class="mt-1 text-[10px] text-gray-400" x-text="'Confidence: '+(result?.rag_confidence*100).toFixed(0)+'%'"></p>
                    </div>

                    {{-- Alternatives --}}
                    <div x-show="result?.results?.alternatives?.length" class="bg-white/50 rounded-xl p-3">
                        <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">🥗 Healthier Alternatives</p>
                        <div class="flex flex-wrap gap-1.5">
                            <template x-for="alt in result?.results?.alternatives ?? []" :key="alt">
                                <span class="px-2 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-[11px] font-medium" x-text="alt"></span>
                            </template>
                        </div>
                    </div>

                    <div x-show="result?.alerts?.length" class="space-y-1.5">
                        <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">⚠️ Alerts</p>
                        <template x-for="a in result?.alerts??[]" :key="a.id">
                            <div class="p-2 rounded-lg text-xs" :class="a.severity==='critical'?'bg-red-100/70 text-red-700':'bg-amber-100/70 text-amber-700'">
                                <strong x-text="a.title"></strong>: <span x-text="a.message"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        {{-- Glucose Curve Chart --}}
        <div x-show="result?.results?.glucose_curve" x-transition class="gl-card p-5 mt-4 gl-a gl-d3" data-gl>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="gl-icon bg-purple-100 text-purple-600">📈</div>
                    <h2 class="text-xs font-bold uppercase tracking-widest gl-grad-text">Glucose Response Curve</h2>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] text-gray-400">Predicted glucose over time</span>
                </div>
            </div>
            <div class="relative" style="height:280px">
                <canvas id="glucoseCurveChart" data-testid="glucose-curve-chart"></canvas>
            </div>
            <div class="flex items-center justify-center gap-4 mt-3 text-[10px]">
                <span class="flex items-center gap-1"><span class="w-3 h-1.5 rounded bg-emerald-400 inline-block"></span> Safe Zone (70–140)</span>
                <span class="flex items-center gap-1"><span class="w-3 h-1.5 rounded bg-amber-400 inline-block"></span> Elevated (140–180)</span>
                <span class="flex items-center gap-1"><span class="w-3 h-1.5 rounded bg-red-400 inline-block"></span> Danger (>180)</span>
            </div>
        </div>

        {{-- Food Comparison --}}
        <div class="gl-card p-5 mt-4 gl-a gl-d3" data-gl>
            <div class="flex items-center gap-2 mb-4">
                <div class="gl-icon bg-blue-100 text-blue-600">⚖️</div>
                <h2 class="text-xs font-bold uppercase tracking-widest gl-grad-text">Compare Two Foods</h2>
            </div>
            <form @submit.prevent="compare()" class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Food A</label>
                        <input type="text" x-model="compareForm.food_a" required maxlength="255" class="gl-input" placeholder="e.g. White Rice"
                               id="compare-food-a" data-testid="compare-food-a">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Food B</label>
                        <input type="text" x-model="compareForm.food_b" required maxlength="255" class="gl-input" placeholder="e.g. Brown Rice"
                               id="compare-food-b" data-testid="compare-food-b">
                    </div>
                </div>
                <div class="flex gap-3 items-end">
                    <div class="flex-1">
                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Meal Time</label>
                        <select x-model="compareForm.meal_time" class="gl-input">
                            <option value="">Auto</option>
                            <option value="morning">🌅 Morning</option>
                            <option value="afternoon">☀️ Afternoon</option>
                            <option value="evening">🌇 Evening</option>
                            <option value="night">🌙 Night</option>
                        </select>
                    </div>
                    <button type="submit" :disabled="comparing" class="gl-btn flex-1" id="compare-btn" data-testid="compare-btn">
                        <span x-show="!comparing">⚖️ Compare</span>
                        <span x-show="comparing">Comparing…</span>
                    </button>
                </div>
            </form>

            {{-- Quick Comparisons --}}
            <div class="mt-3 pt-3 border-t border-white/20">
                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mb-2">Quick comparisons</p>
                <div class="flex flex-wrap gap-1.5">
                    <template x-for="c in quickCompares">
                        <button @click="compareForm.food_a=c[0]; compareForm.food_b=c[1]; compare()"
                                class="gl-quick" x-text="c[0] + ' vs ' + c[1]"></button>
                    </template>
                </div>
            </div>

            {{-- Comparison Result --}}
            <div x-show="compareResult" x-transition class="mt-4">
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div class="p-3 rounded-xl" :class="compareResult?.comparison?.better_choice === compareResult?.food_a?.food?.name ? 'bg-emerald-50/70 ring-2 ring-emerald-300' : 'bg-red-50/50'">
                        <p class="text-xs font-bold text-gray-700 mb-1" x-text="compareResult?.food_a?.food?.name"></p>
                        <p class="text-lg font-black" :class="giColor(compareResult?.food_a?.food?.glycemic_index)">
                            GI: <span x-text="compareResult?.food_a?.food?.glycemic_index"></span>
                        </p>
                        <p class="text-[11px] text-gray-500">Peak: <strong x-text="compareResult?.food_a?.peak?.glucose_mg_dl?.toFixed(0)"></strong> mg/dL at <span x-text="compareResult?.food_a?.peak?.time_minutes"></span>min</p>
                    </div>
                    <div class="p-3 rounded-xl" :class="compareResult?.comparison?.better_choice === compareResult?.food_b?.food?.name ? 'bg-emerald-50/70 ring-2 ring-emerald-300' : 'bg-red-50/50'">
                        <p class="text-xs font-bold text-gray-700 mb-1" x-text="compareResult?.food_b?.food?.name"></p>
                        <p class="text-lg font-black" :class="giColor(compareResult?.food_b?.food?.glycemic_index)">
                            GI: <span x-text="compareResult?.food_b?.food?.glycemic_index"></span>
                        </p>
                        <p class="text-[11px] text-gray-500">Peak: <strong x-text="compareResult?.food_b?.peak?.glucose_mg_dl?.toFixed(0)"></strong> mg/dL at <span x-text="compareResult?.food_b?.peak?.time_minutes"></span>min</p>
                    </div>
                </div>
                <div class="p-3 rounded-xl bg-purple-50/50 text-center">
                    <p class="text-[11px] text-gray-500">Better choice:</p>
                    <p class="text-sm font-bold text-purple-700" x-text="'✅ ' + compareResult?.comparison?.better_choice"></p>
                    <p class="text-[10px] text-gray-400 mt-1" x-text="'Spike difference: ' + Math.abs(compareResult?.comparison?.spike_difference).toFixed(0) + ' mg/dL'"></p>
                </div>
                <div class="relative mt-4" style="height:280px">
                    <canvas id="compareChart" data-testid="compare-chart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',()=>document.querySelectorAll('[data-gl]').forEach(el=>el.classList.add('gl-v')));
function foodImpactPage() {
    return {
        analyzing: false, result: null,
        comparing: false, compareResult: null,
        form: { food_item:'', quantity:'', meal_time:'' },
        compareForm: { food_a:'', food_b:'', meal_time:'' },
        glucoseChart: null, compChart: null,
        quickPicks: ['White Rice','Gulab Jamun','Samosa','Paneer Tikka','Dal Khichdi','Green Salad','Roti','Biryani'],
        quickCompares: [['White Rice','Brown Rice'],['Naan','Roti'],['Jalebi','Dates'],['Samosa','Dhokla']],
        catColor(c){ return c==='high'?'bg-red-100 text-red-700':c==='medium'||c==='moderate'?'bg-amber-100 text-amber-700':'bg-emerald-100 text-emerald-700'; },
        giColor(gi){ return gi>=60?'text-red-600':gi>=45?'text-amber-600':'text-emerald-600'; },
        filterModifiers(mods){
            if(!mods) return {};
            const out = {};
            for(const [k,v] of Object.entries(mods)){
                if(k !== 'combined' && v && typeof v === 'object' && v.factor !== undefined) out[k] = v;
            }
            return out;
        },
        async analyze(){
            if(!this.form.food_item) return;
            this.analyzing = true; this.result = null;
            const payload = { food_item: this.form.food_item };
            if(this.form.quantity) payload.quantity = this.form.quantity;
            if(this.form.meal_time) payload.meal_time = this.form.meal_time;
            const r = await api.post('/food-impact', payload);
            if(r.success){ this.result = r.data; this.$nextTick(()=>this.renderGlucoseChart()); }
            else toast(r.message || 'Analysis failed. Generate your Digital Twin first.', 'error');
            this.analyzing = false;
        },
        async compare(){
            if(!this.compareForm.food_a || !this.compareForm.food_b) return;
            this.comparing = true; this.compareResult = null;
            const payload = { food_a: this.compareForm.food_a, food_b: this.compareForm.food_b };
            if(this.compareForm.meal_time) payload.meal_time = this.compareForm.meal_time;
            const r = await api.post('/food-compare', payload);
            if(r.success){ this.compareResult = r.data; this.$nextTick(()=>this.renderCompareChart()); }
            else toast(r.message || 'Comparison failed.', 'error');
            this.comparing = false;
        },
        renderGlucoseChart(){
            const curve = this.result?.results?.glucose_curve;
            if(!curve || !curve.length) return;
            const canvas = document.getElementById('glucoseCurveChart');
            if(!canvas) return;
            if(this.glucoseChart) this.glucoseChart.destroy();

            const labels = curve.map(p => p.time_minutes + 'min');
            const values = curve.map(p => p.glucose_mg_dl);
            const baseline = this.result?.results?.baseline_mg_dl || 100;

            this.glucoseChart = new Chart(canvas, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: this.result?.results?.food_data?.name || 'Glucose',
                        data: values,
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139,92,246,0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointBackgroundColor: '#8b5cf6',
                        borderWidth: 2.5,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        annotation: undefined,
                    },
                    scales: {
                        y: {
                            title: { display: true, text: 'mg/dL', font: { size: 10 } },
                            suggestedMin: Math.max(50, baseline - 20),
                            suggestedMax: Math.max(200, Math.max(...values) + 20),
                            grid: { color: 'rgba(0,0,0,0.04)' },
                        },
                        x: {
                            title: { display: true, text: 'Time after eating', font: { size: 10 } },
                            grid: { display: false },
                        }
                    }
                },
                plugins: [{
                    id: 'zones',
                    beforeDraw(chart){
                        const { ctx, chartArea: { left, right, top, bottom }, scales: { y } } = chart;
                        const drawZone = (min, max, color) => {
                            const yTop = y.getPixelForValue(Math.min(max, y.max));
                            const yBot = y.getPixelForValue(Math.max(min, y.min));
                            if(yTop < yBot){
                                ctx.fillStyle = color;
                                ctx.fillRect(left, yTop, right - left, yBot - yTop);
                            }
                        };
                        drawZone(70, 140, 'rgba(52,211,153,0.08)');
                        drawZone(140, 180, 'rgba(251,191,36,0.08)');
                        drawZone(180, 400, 'rgba(248,113,113,0.08)');
                    }
                }]
            });
        },
        renderCompareChart(){
            const a = this.compareResult?.food_a?.curve;
            const b = this.compareResult?.food_b?.curve;
            if(!a?.length || !b?.length) return;
            const canvas = document.getElementById('compareChart');
            if(!canvas) return;
            if(this.compChart) this.compChart.destroy();

            const maxLen = Math.max(a.length, b.length);
            const allTimes = [...new Set([...a.map(p=>p.time_minutes), ...b.map(p=>p.time_minutes)])].sort((x,y)=>x-y);
            const labels = allTimes.map(t => t + 'min');

            const mapCurve = (curve) => allTimes.map(t => {
                const pt = curve.find(p => p.time_minutes === t);
                return pt ? pt.glucose_mg_dl : null;
            });

            this.compChart = new Chart(canvas, {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: this.compareResult?.food_a?.food?.name || 'Food A',
                            data: mapCurve(a),
                            borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.08)',
                            fill: false, tension: 0.4, borderWidth: 2.5, pointRadius: 3, spanGaps: true,
                        },
                        {
                            label: this.compareResult?.food_b?.food?.name || 'Food B',
                            data: mapCurve(b),
                            borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,0.08)',
                            fill: false, tension: 0.4, borderWidth: 2.5, pointRadius: 3, spanGaps: true,
                        }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } },
                    },
                    scales: {
                        y: { title: { display: true, text: 'mg/dL', font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.04)' } },
                        x: { title: { display: true, text: 'Time after eating', font: { size: 10 } }, grid: { display: false } }
                    }
                },
                plugins: [{
                    id: 'compZones',
                    beforeDraw(chart){
                        const { ctx, chartArea: { left, right }, scales: { y } } = chart;
                        const drawZone = (min, max, color) => {
                            const yTop = y.getPixelForValue(Math.min(max, y.max));
                            const yBot = y.getPixelForValue(Math.max(min, y.min));
                            if(yTop < yBot){ ctx.fillStyle = color; ctx.fillRect(left, yTop, right - left, yBot - yTop); }
                        };
                        drawZone(70, 140, 'rgba(52,211,153,0.06)');
                        drawZone(140, 180, 'rgba(251,191,36,0.06)');
                        drawZone(180, 400, 'rgba(248,113,113,0.06)');
                    }
                }]
            });
        }
    };
}
</script>
@endpush
