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

.sim-character-panel{background:linear-gradient(180deg,rgba(88,28,135,.04) 0%,rgba(194,77,255,.06) 100%);border-radius:16px;position:relative;overflow:hidden;min-height:460px}
.sim-character-panel::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 50% 80%,rgba(139,92,246,.08) 0%,transparent 60%);pointer-events:none}

.result-card{background:rgba(255,255,255,.7);border:1px solid rgba(255,255,255,.4);border-radius:12px;padding:12px;margin-bottom:10px;transition:all .3s ease}
.result-card:hover{background:rgba(255,255,255,.9);transform:scale(1.02)}
.result-header{display:flex;align-items:center;gap:8px;margin-bottom:8px}
.result-icon{width:24px;height:24px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:.8rem}

.gl-sim-item{background:rgba(255,255,255,.45);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.3);border-radius:14px;transition:transform .3s ease,box-shadow .3s ease}
.gl-sim-item:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(95,111,255,.1)}

.food-chip{display:inline-flex;align-items:center;gap:4px;background:rgba(255,255,255,.45);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.3);border-radius:20px;padding:4px 12px;font-size:11px;font-weight:600;color:#6b7280;cursor:pointer;transition:all .3s ease}
.food-chip:hover,.food-chip.active{background:rgba(194,77,255,.12);color:#7c3aed;border-color:rgba(194,77,255,.3);transform:translateY(-1px)}

.timing-option{display:flex;flex-direction:column;align-items:center;gap:4px;padding:8px 12px;border-radius:12px;border:1.5px solid rgba(0,0,0,.06);background:rgba(255,255,255,.5);cursor:pointer;transition:all .3s ease;font-size:10px;font-weight:600;color:#9ca3af}
.timing-option:hover,.timing-option.active{border-color:rgba(194,77,255,.4);background:rgba(194,77,255,.08);color:#7c3aed}
.timing-option .timing-icon{font-size:18px}
</style>
@endpush

@section('content')
<div x-data="simulationsPage()" x-init="init()" class="gl-bg -m-4 sm:-m-6 p-4 sm:p-6">
    <div class="gl-p gl-p1"></div>
    <div class="gl-p gl-p2"></div>

    <div class="max-w-6xl mx-auto relative">

        {{-- Hero --}}
        <div class="gl-hero px-5 py-4 mb-4 gl-a gl-d0" data-gl>
            <div class="relative z-10 flex items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <div class="w-2 h-2 rounded-full bg-emerald-400 gl-status-pulse"></div>
                        <span class="text-white/70 text-[11px] font-medium tracking-wide uppercase">Prediction Engine</span>
                    </div>
                    <h1 class="text-lg font-bold text-white">⚡ What If — Simulate Your Choices</h1>
                    <p class="text-white/60 text-xs mt-0.5">Pick a food, choose timing, and see the hormonal impact on your 3D body</p>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-12 gap-4">

            {{-- Left Column: Form --}}
            <div class="lg:col-span-4">
                <div class="gl-card p-5 sticky top-20 gl-a gl-d1" data-gl>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="gl-icon bg-purple-100 text-purple-600">🎯</div>
                        <h2 class="text-xs font-bold uppercase tracking-widest gl-grad-text">Simulate</h2>
                    </div>

                    <form @submit.prevent="run()" class="space-y-4">

                        {{-- Simulation Type --}}
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1.5">What are you simulating?</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" @click="form.type='meal'"
                                        :class="form.type==='meal' ? 'border-purple-400 bg-purple-50 text-purple-700' : 'border-gray-100 text-gray-500 hover:border-purple-200'"
                                        class="flex flex-col items-center gap-1 p-2.5 rounded-xl border-2 transition-all text-center">
                                    <span class="text-lg">🍽️</span>
                                    <span class="text-[10px] font-bold uppercase">Meal</span>
                                </button>
                                <button type="button" @click="form.type='sleep'"
                                        :class="form.type==='sleep' ? 'border-blue-400 bg-blue-50 text-blue-700' : 'border-gray-100 text-gray-500 hover:border-blue-200'"
                                        class="flex flex-col items-center gap-1 p-2.5 rounded-xl border-2 transition-all text-center">
                                    <span class="text-lg">😴</span>
                                    <span class="text-[10px] font-bold uppercase">Sleep</span>
                                </button>
                                <button type="button" @click="form.type='stress'"
                                        :class="form.type==='stress' ? 'border-red-400 bg-red-50 text-red-700' : 'border-gray-100 text-gray-500 hover:border-red-200'"
                                        class="flex flex-col items-center gap-1 p-2.5 rounded-xl border-2 transition-all text-center">
                                    <span class="text-lg">😰</span>
                                    <span class="text-[10px] font-bold uppercase">Stress</span>
                                </button>
                            </div>
                        </div>

                        {{-- Favourite Food (optional dropdown — shown for meal type) --}}
                        <div x-show="form.type==='meal'" x-transition>
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">
                                Favourite Food <span class="font-normal text-gray-400">(optional)</span>
                            </label>
                            <select x-model="form.favourite_food" class="gl-input bg-transparent" @change="onFoodSelect()">
                                <option value="">— Choose a food —</option>
                                <optgroup label="🍚 Rice & Grains">
                                    <option value="White Rice">White Rice</option>
                                    <option value="Brown Rice">Brown Rice</option>
                                    <option value="Biryani">Biryani</option>
                                    <option value="Quinoa">Quinoa</option>
                                    <option value="Oats">Oats</option>
                                </optgroup>
                                <optgroup label="🍞 Breads">
                                    <option value="Roti">Roti</option>
                                    <option value="Naan">Naan</option>
                                    <option value="White Bread">White Bread</option>
                                    <option value="Paratha">Paratha</option>
                                </optgroup>
                                <optgroup label="🍛 Indian Dishes">
                                    <option value="Dal Khichdi">Dal Khichdi</option>
                                    <option value="Paneer Tikka">Paneer Tikka</option>
                                    <option value="Samosa">Samosa</option>
                                    <option value="Chole Bhature">Chole Bhature</option>
                                    <option value="Dosa">Dosa</option>
                                    <option value="Idli">Idli</option>
                                    <option value="Rajma Chawal">Rajma Chawal</option>
                                    <option value="Aloo Gobi">Aloo Gobi</option>
                                </optgroup>
                                <optgroup label="🍕 Fast Food">
                                    <option value="Pizza">Pizza</option>
                                    <option value="Burger">Burger</option>
                                    <option value="French Fries">French Fries</option>
                                    <option value="Pasta">Pasta</option>
                                </optgroup>
                                <optgroup label="🥗 Healthy">
                                    <option value="Green Salad">Green Salad</option>
                                    <option value="Sprouts">Sprouts</option>
                                    <option value="Yogurt">Yogurt</option>
                                    <option value="Nuts & Seeds">Nuts & Seeds</option>
                                    <option value="Dal & Vegetables">Dal & Vegetables</option>
                                </optgroup>
                                <optgroup label="🍰 Sweets">
                                    <option value="Gulab Jamun">Gulab Jamun</option>
                                    <option value="Jalebi">Jalebi</option>
                                    <option value="Cake">Cake</option>
                                    <option value="Ice Cream">Ice Cream</option>
                                    <option value="Rasgulla">Rasgulla</option>
                                </optgroup>
                                <optgroup label="🥤 Beverages">
                                    <option value="Soda / Cola">Soda / Cola</option>
                                    <option value="Chai with Sugar">Chai with Sugar</option>
                                    <option value="Green Tea">Green Tea</option>
                                    <option value="Buttermilk">Buttermilk</option>
                                    <option value="Fresh Juice">Fresh Juice</option>
                                </optgroup>
                            </select>

                            {{-- Quick food chips --}}
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                <template x-for="f in quickFoods" :key="f">
                                    <button type="button" @click="form.favourite_food=f; onFoodSelect()"
                                            :class="form.favourite_food===f ? 'active' : ''"
                                            class="food-chip" x-text="f"></button>
                                </template>
                            </div>
                        </div>

                        {{-- Meal Timing (for meal type) --}}
                        <div x-show="form.type==='meal'" x-transition>
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1.5">When did you eat?</label>
                            <div class="grid grid-cols-4 gap-2">
                                <template x-for="t in timings" :key="t.value">
                                    <button type="button" @click="form.meal_timing=t.value"
                                            :class="form.meal_timing===t.value ? 'active' : ''"
                                            class="timing-option">
                                        <span class="timing-icon" x-text="t.icon"></span>
                                        <span x-text="t.label"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Describe the scenario</label>
                            <textarea x-model="form.description" rows="2" required maxlength="500"
                                      class="gl-input" style="resize:vertical;min-height:52px"
                                      :placeholder="form.type==='meal' ? 'e.g. Ate 2 samosas and a large cola at lunch' : form.type==='sleep' ? 'e.g. Slept only 4 hours last night' : 'e.g. Had a very stressful day at work'"></textarea>
                        </div>

                        {{-- Meal Detail --}}
                        <div x-show="form.type==='meal' && !form.favourite_food">
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Meal Detail</label>
                            <input type="text" x-model="form.parameters.meal_description" class="gl-input" placeholder="Fried snacks, sugary drink">
                        </div>

                        {{-- Sleep Hours --}}
                        <div x-show="form.type==='sleep'">
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Sleep Hours</label>
                            <input type="number" step="0.5" min="0" max="24" x-model="form.parameters.sleep_hours" class="gl-input" placeholder="e.g. 4">
                        </div>

                        {{-- Stress Level --}}
                        <div x-show="form.type==='stress'">
                            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Stress Level</label>
                            <select x-model="form.parameters.stress_level" class="gl-input bg-transparent">
                                <option value="">Select…</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
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
                </div>
            </div>

            {{-- Center Column: 3D Character --}}
            <div class="lg:col-span-5">
                <div class="gl-card sim-character-panel gl-a gl-d2" data-gl>
                    <div class="p-3 border-b border-white/20 flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-500">3D Digital Twin</span>
                        <div class="flex items-center gap-2">
                            <span x-show="running" class="text-[10px] font-bold text-purple-500 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-500 animate-pulse"></span> LIVE
                            </span>
                            <div class="flex gap-1">
                                <div class="w-1.5 h-1.5 rounded-full bg-purple-400"></div>
                                <div class="w-1.5 h-1.5 rounded-full bg-purple-300"></div>
                                <div class="w-1.5 h-1.5 rounded-full bg-purple-200"></div>
                            </div>
                        </div>
                    </div>
                    <div id="simulation-character-root" style="height:420px">
                        <div class="flex items-center justify-center py-10">
                            <div class="w-6 h-6 border-2 border-purple-200 border-t-purple-600 rounded-full animate-spin"></div>
                        </div>
                    </div>
                </div>

                {{-- Result cards (appear after simulation) --}}
                <div x-show="result" x-transition class="mt-4 space-y-3">
                    {{-- Risk card --}}
                    <div class="result-card" :class="result?.risk_change > 0 ? 'border-red-200' : 'border-emerald-200'">
                        <div class="result-header">
                            <div class="result-icon font-bold" :class="result?.risk_change > 0 ? 'bg-red-100 text-red-600' : 'bg-emerald-100 text-emerald-600'">📈</div>
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Risk Assessment</span>
                        </div>
                        <div class="flex items-end gap-2">
                            <span class="text-3xl font-black" :class="result?.risk_change > 0 ? 'text-red-500' : 'text-emerald-500'"
                                  x-text="(result?.risk_change > 0 ? '+' : '') + result?.risk_change?.toFixed(2)"></span>
                            <span class="text-[10px] text-gray-400 mb-1.5 font-bold uppercase">Total Risk Delta</span>
                        </div>
                        <div class="mt-2 flex items-center gap-2 text-[11px]">
                            <span class="px-2 py-0.5 rounded-full capitalize font-bold"
                                  :class="catColor(result?.risk_category_before)" x-text="result?.risk_category_before"></span>
                            <span class="text-gray-300 font-bold">→</span>
                            <span class="px-2 py-0.5 rounded-full capitalize font-bold"
                                  :class="catColor(result?.risk_category_after)" x-text="result?.risk_category_after"></span>
                        </div>
                    </div>

                    {{-- Analysis card --}}
                    <div x-show="result?.rag_explanation" class="result-card border-indigo-200">
                        <div class="result-header">
                            <div class="result-icon bg-indigo-100 text-indigo-600">💡</div>
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Analysis</span>
                        </div>
                        <p class="text-xs text-gray-600 leading-relaxed" x-text="result?.rag_explanation"></p>
                        <div class="mt-2 flex items-center justify-between">
                            <span class="text-[9px] font-bold text-gray-400 uppercase">AI Insights</span>
                            <span x-show="result?.rag_confidence" class="text-[9px] text-gray-400" x-text="'Confidence: '+(result?.rag_confidence*100).toFixed(0)+'%'"></span>
                        </div>
                    </div>

                    {{-- Alerts card --}}
                    <div x-show="result?.alerts?.length" class="result-card border-amber-200">
                        <div class="result-header">
                            <div class="result-icon bg-amber-100 text-amber-600">⚠️</div>
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Predicted Effects</span>
                        </div>
                        <div class="space-y-1.5 mt-2">
                            <template x-for="a in result?.alerts??[]" :key="a.id">
                                <div class="text-[11px] p-2 rounded-lg bg-gray-50/80 border border-gray-100">
                                    <div class="flex gap-2">
                                        <div class="w-1 h-3 self-center rounded-full" :class="a.severity==='critical'?'bg-red-500':'bg-amber-500'"></div>
                                        <div>
                                            <span class="block font-bold text-gray-700" x-text="a.title"></span>
                                            <span class="text-gray-500" x-text="a.message"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: History --}}
            <div class="lg:col-span-3">
                <div class="gl-card p-4 gl-a gl-d3" data-gl>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="gl-icon bg-indigo-100 text-indigo-600" style="width:26px;height:26px;font-size:.8rem">📋</div>
                        <h2 class="text-[10px] font-bold uppercase tracking-widest gl-grad-text">History</h2>
                    </div>
                    <div x-show="loading" class="text-center py-6"><div class="inline-block w-5 h-5 border-2 border-purple-400 border-t-transparent rounded-full animate-spin"></div></div>
                    <div x-show="!loading && sims.length===0" class="text-center py-6">
                        <div class="text-3xl mb-1">⚡</div>
                        <p class="text-[10px] text-gray-400">No simulations yet</p>
                    </div>
                    <div class="space-y-2 max-h-[500px] overflow-y-auto" style="scrollbar-width:thin">
                        <template x-for="s in sims" :key="s.id">
                            <div class="gl-sim-item p-2.5 cursor-pointer" @click="s._open = !s._open">
                                <div class="flex items-center gap-2">
                                    <div class="gl-icon" style="width:24px;height:24px;font-size:.75rem;border-radius:8px"
                                         :class="s.type==='meal'?'bg-orange-100 text-orange-600':s.type==='sleep'?'bg-blue-100 text-blue-600':s.type==='food_impact'?'bg-lime-100 text-lime-600':'bg-red-100 text-red-600'"
                                         x-text="s.type==='meal'?'🍽️':s.type==='sleep'?'😴':s.type==='food_impact'?'🍛':'😰'"></div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[11px] font-semibold text-gray-700 truncate" x-text="s.input_data?.description || s.input_data?.food_item || s.type"></p>
                                        <p class="text-[9px] text-gray-400" x-text="new Date(s.created_at).toLocaleString()"></p>
                                    </div>
                                    <span class="text-xs font-black" :class="s.risk_change>0?'text-red-500':'text-emerald-500'"
                                          x-text="(s.risk_change>0?'+':'')+s.risk_change.toFixed(2)"></span>
                                </div>
                                <div x-show="s._open" x-collapse class="mt-2 text-[10px] border-t border-white/30 pt-2 space-y-1.5">
                                    <p class="text-gray-500"><strong class="text-gray-700">Before:</strong> <span x-text="s.original_risk_score?.toFixed(2)"></span>
                                       (<span class="capitalize" x-text="s.risk_category_before"></span>)
                                       → <strong class="text-gray-700">After:</strong> <span x-text="s.simulated_risk_score?.toFixed(2)"></span>
                                       (<span class="capitalize" x-text="s.risk_category_after"></span>)</p>
                                    <div x-show="s.rag_explanation" class="bg-white/50 p-1.5 rounded-lg text-gray-500 text-[9px]">
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
@viteReactRefresh
@vite('resources/js/dashboard-twin.jsx')
<script>
function simulationsPage() {
    return {
        loading: true, running: false, result: null, sims: [],
        quickFoods: ['Samosa', 'White Rice', 'Biryani', 'Gulab Jamun', 'Green Salad', 'Oats'],
        timings: [
            { value: 'morning', label: 'Morning', icon: '🌅' },
            { value: 'afternoon', label: 'Lunch', icon: '☀️' },
            { value: 'evening', label: 'Evening', icon: '🌇' },
            { value: 'night', label: 'Night', icon: '🌙' },
        ],
        form: {
            type: 'meal',
            description: '',
            favourite_food: '',
            meal_timing: 'afternoon',
            parameters: { meal_description: '', sleep_hours: '', stress_level: '' },
        },
        catColor(c) {
            return c === 'high' ? 'bg-red-100 text-red-700' : c === 'medium' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700';
        },
        onFoodSelect() {
            if (this.form.favourite_food) {
                this.form.parameters.meal_description = this.form.favourite_food;
                if (!this.form.description) {
                    this.form.description = `Ate ${this.form.favourite_food} for ${this.form.meal_timing || 'a meal'}`;
                }
            }
        },
        async init() {
            const r = await api.get('/simulations');
            if (r.success) this.sims = (r.data || []).map(s => ({ ...s, _open: false }));
            this.loading = false;
            this.$nextTick(() => document.querySelectorAll('[data-gl]').forEach(el => el.classList.add('gl-v')));
        },
        async run() {
            this.running = true;
            this.result = null;
            window.dispatchEvent(new CustomEvent('sim:start'));

            // If a favourite food is selected and type is meal, run food-impact API
            if (this.form.type === 'meal' && this.form.favourite_food) {
                const payload = {
                    food_item: this.form.favourite_food,
                    quantity: '1 serving',
                };
                const r = await api.post('/food-impact', payload);
                if (r.success) {
                    this.result = r.data;
                    window.dispatchEvent(new CustomEvent('sim:result', { detail: r.data }));
                    toast('Food impact simulation complete!');
                    this.sims.unshift({ ...r.data, _open: false });
                } else {
                    window.dispatchEvent(new CustomEvent('sim:reset'));
                    toast(r.message || 'Simulation failed. Make sure your Digital Twin is generated.', 'error');
                }
            } else {
                // Standard lifestyle simulation
                const payload = { type: this.form.type, description: this.form.description, parameters: {} };
                if (this.form.type === 'meal' && this.form.parameters.meal_description) payload.parameters.meal_description = this.form.parameters.meal_description;
                if (this.form.type === 'sleep' && this.form.parameters.sleep_hours) payload.parameters.sleep_hours = parseFloat(this.form.parameters.sleep_hours);
                if (this.form.type === 'stress' && this.form.parameters.stress_level) payload.parameters.stress_level = this.form.parameters.stress_level;

                // Include timing in description if available
                if (this.form.type === 'meal' && this.form.meal_timing && !this.form.description.includes(this.form.meal_timing)) {
                    payload.description = this.form.description + ` (${this.form.meal_timing})`;
                }

                const r = await api.post('/simulations/run', payload);
                if (r.success) {
                    this.result = r.data;
                    window.dispatchEvent(new CustomEvent('sim:result', { detail: r.data }));
                    toast('Simulation complete!');
                    this.sims.unshift({ ...r.data, _open: false });
                } else {
                    window.dispatchEvent(new CustomEvent('sim:reset'));
                    toast(r.message || 'Simulation failed. Make sure your Digital Twin is generated.', 'error');
                }
            }
            this.running = false;
        }
    };
}
</script>
@endpush
