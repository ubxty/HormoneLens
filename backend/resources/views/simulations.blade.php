@extends('layouts.app')
@section('title','Simulations — HormoneLens')

@section('content')
<div x-data="simulationsPage()" x-init="init()">
    <div class="max-w-4xl mx-auto">

        <h1 class="text-2xl font-bold text-gray-800 mb-1">Simulations</h1>
        <p class="text-gray-500 mb-6">Predict how meals, sleep, and stress affect your health.</p>

        <div class="grid lg:grid-cols-5 gap-6">
            {{-- Run form --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border p-5 sticky top-20">
                    <h2 class="font-semibold text-gray-800 mb-4">Run Simulation</h2>
                    <form @submit.prevent="run()" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                            <select x-model="form.type" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 outline-none bg-white">
                                <option value="meal">🍽️ Meal</option>
                                <option value="sleep">😴 Sleep</option>
                                <option value="stress">😰 Stress</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea x-model="form.description" rows="2" required maxlength="500"
                                      class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 outline-none text-sm"
                                      placeholder="e.g. Ate 2 samosas and a large cola at lunch"></textarea>
                        </div>

                        {{-- Meal params --}}
                        <div x-show="form.type==='meal'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Meal Description (detail)</label>
                            <input type="text" x-model="form.parameters.meal_description"
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 outline-none text-sm"
                                   placeholder="Fried snacks, sugary drink">
                        </div>
                        {{-- Sleep params --}}
                        <div x-show="form.type==='sleep'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sleep Hours</label>
                            <input type="number" step="0.5" min="0" max="24" x-model="form.parameters.sleep_hours"
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 outline-none text-sm"
                                   placeholder="e.g. 4">
                        </div>
                        {{-- Stress params --}}
                        <div x-show="form.type==='stress'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stress Level</label>
                            <select x-model="form.parameters.stress_level" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 outline-none bg-white text-sm">
                                <option value="">Select...</option>
                                <option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option>
                            </select>
                        </div>

                        <button type="submit" :disabled="running"
                                class="w-full py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-lg transition disabled:opacity-50 text-sm">
                            <span x-show="!running">⚡ Run Simulation</span>
                            <span x-show="running">Running...</span>
                        </button>
                    </form>

                    {{-- Result --}}
                    <div x-show="result" class="mt-5 p-4 rounded-lg border" :class="result?.risk_change > 0 ? 'bg-red-50 border-red-200' : 'bg-emerald-50 border-emerald-200'">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-2xl font-bold" :class="result?.risk_change > 0 ? 'text-red-600' : 'text-emerald-600'"
                                  x-text="(result?.risk_change > 0 ? '+' : '') + result?.risk_change?.toFixed(2)"></span>
                            <span class="text-sm text-gray-500">risk change</span>
                        </div>
                        <div class="flex gap-2 text-xs mb-3">
                            <span class="px-2 py-0.5 rounded-full capitalize"
                                  :class="catColor(result?.risk_category_before)" x-text="result?.risk_category_before"></span>
                            <span class="text-gray-400">→</span>
                            <span class="px-2 py-0.5 rounded-full capitalize"
                                  :class="catColor(result?.risk_category_after)" x-text="result?.risk_category_after"></span>
                        </div>
                        <div x-show="result?.rag_explanation" class="text-sm text-gray-700 bg-white/70 rounded p-3 mb-2">
                            <p class="font-medium text-gray-800 mb-1">💡 AI Explanation</p>
                            <p x-text="result?.rag_explanation"></p>
                            <p x-show="result?.rag_confidence" class="text-xs text-gray-400 mt-1" x-text="'Confidence: '+(result?.rag_confidence*100).toFixed(0)+'%'"></p>
                        </div>
                        <div x-show="result?.alerts?.length" class="space-y-1">
                            <template x-for="a in result?.alerts??[]" :key="a.id">
                                <div class="text-xs p-2 rounded" :class="a.severity==='critical'?'bg-red-100 text-red-700':'bg-amber-100 text-amber-700'">
                                    <strong x-text="a.title"></strong>: <span x-text="a.message"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- History --}}
            <div class="lg:col-span-3">
                <div class="bg-white rounded-xl shadow-sm border p-5">
                    <h2 class="font-semibold text-gray-800 mb-4">Recent Simulations</h2>
                    <div x-show="loading" class="text-center py-8"><div class="animate-spin w-6 h-6 border-4 border-brand-500 border-t-transparent rounded-full mx-auto"></div></div>
                    <div x-show="!loading && sims.length===0" class="text-center py-8 text-sm text-gray-400">No simulations yet. Run your first one!</div>
                    <div class="space-y-3">
                        <template x-for="s in sims" :key="s.id">
                            <div class="p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition"
                                 @click="s._open = !s._open">
                                <div class="flex items-center gap-3">
                                    <span class="text-lg" x-text="s.type==='meal'?'🍽️':s.type==='sleep'?'😴':'😰'"></span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-700 truncate" x-text="s.input_data?.description || s.type"></p>
                                        <p class="text-xs text-gray-400" x-text="new Date(s.created_at).toLocaleString()"></p>
                                    </div>
                                    <span class="text-sm font-bold" :class="s.risk_change>0?'text-red-500':'text-emerald-500'"
                                          x-text="(s.risk_change>0?'+':'')+s.risk_change.toFixed(2)"></span>
                                </div>
                                <div x-show="s._open" x-collapse class="mt-3 text-sm border-t pt-3 space-y-2">
                                    <p><strong>Before:</strong> <span x-text="s.original_risk_score?.toFixed(2)"></span>
                                       (<span class="capitalize" x-text="s.risk_category_before"></span>)
                                       → <strong>After:</strong> <span x-text="s.simulated_risk_score?.toFixed(2)"></span>
                                       (<span class="capitalize" x-text="s.risk_category_after"></span>)</p>
                                    <div x-show="s.rag_explanation" class="bg-white p-2 rounded border text-xs text-gray-600">
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
