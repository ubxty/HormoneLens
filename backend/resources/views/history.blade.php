@extends('layouts.app')
@section('title','History — HormoneLens')

@section('content')
<div x-data="historyPage()" x-init="init()">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-1">Simulation History</h1>
        <p class="text-gray-500 mb-6">Review your past simulations and rerun them.</p>

        {{-- Filters --}}
        <div class="flex gap-2 mb-4 flex-wrap">
            <button @click="typeFilter='all'" :class="typeFilter==='all'?'bg-brand-600 text-white':'bg-white text-gray-700 border'" class="px-4 py-1.5 text-sm rounded-lg font-medium transition">All</button>
            <button @click="typeFilter='meal'" :class="typeFilter==='meal'?'bg-brand-600 text-white':'bg-white text-gray-700 border'" class="px-4 py-1.5 text-sm rounded-lg font-medium transition">🍽️ Meal</button>
            <button @click="typeFilter='sleep'" :class="typeFilter==='sleep'?'bg-brand-600 text-white':'bg-white text-gray-700 border'" class="px-4 py-1.5 text-sm rounded-lg font-medium transition">😴 Sleep</button>
            <button @click="typeFilter='stress'" :class="typeFilter==='stress'?'bg-brand-600 text-white':'bg-white text-gray-700 border'" class="px-4 py-1.5 text-sm rounded-lg font-medium transition">😰 Stress</button>
        </div>

        <div x-show="loading" class="text-center py-12"><div class="animate-spin w-8 h-8 border-4 border-brand-500 border-t-transparent rounded-full mx-auto"></div></div>
        <div x-show="!loading && filtered.length===0" class="bg-white rounded-xl border p-8 text-center text-sm text-gray-400">No simulations found.</div>

        <div class="space-y-3">
            <template x-for="s in filtered" :key="s.id">
                <div class="bg-white rounded-xl shadow-sm border p-4 transition">
                    <div class="flex items-center gap-3 cursor-pointer" @click="s._open = !s._open">
                        <span class="text-lg" x-text="s.type==='meal'?'🍽️':s.type==='sleep'?'😴':'😰'"></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-700 truncate" x-text="s.input_data?.description || s.type"></p>
                            <p class="text-xs text-gray-400" x-text="new Date(s.created_at).toLocaleString()"></p>
                        </div>
                        <span class="text-sm font-bold" :class="s.risk_change > 0 ? 'text-red-500' : 'text-emerald-500'"
                              x-text="(s.risk_change > 0 ? '+' : '') + s.risk_change.toFixed(2)"></span>
                        <span class="text-gray-400 transition-transform" :class="s._open ? 'rotate-180' : ''">▾</span>
                    </div>

                    <div x-show="s._open" x-collapse class="mt-4 pt-4 border-t space-y-3 text-sm">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-xs text-gray-500 mb-1">Risk Before</p>
                                <p class="font-semibold" x-text="s.original_risk_score?.toFixed(2)"></p>
                                <span class="text-xs capitalize px-1.5 py-0.5 rounded-full" :class="catColor(s.risk_category_before)" x-text="s.risk_category_before"></span>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-xs text-gray-500 mb-1">Risk After</p>
                                <p class="font-semibold" x-text="s.simulated_risk_score?.toFixed(2)"></p>
                                <span class="text-xs capitalize px-1.5 py-0.5 rounded-full" :class="catColor(s.risk_category_after)" x-text="s.risk_category_after"></span>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-xs text-gray-500 mb-1">Change</p>
                                <p class="font-semibold" :class="s.risk_change > 0 ? 'text-red-600' : 'text-emerald-600'"
                                   x-text="(s.risk_change>0?'+':'')+s.risk_change.toFixed(2)"></p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-xs text-gray-500 mb-1">Type</p>
                                <p class="font-semibold capitalize" x-text="s.type"></p>
                            </div>
                        </div>

                        <div x-show="s.rag_explanation" class="bg-blue-50 rounded-lg p-3">
                            <p class="font-medium text-gray-700 mb-1">💡 AI Insight</p>
                            <p class="text-gray-600" x-text="s.rag_explanation"></p>
                            <p x-show="s.rag_confidence" class="text-xs text-gray-400 mt-1" x-text="'Confidence: '+(s.rag_confidence*100).toFixed(0)+'%'"></p>
                        </div>

                        <div class="flex gap-2">
                            <button @click="rerun(s)" :disabled="s._rerunning"
                                    class="px-4 py-1.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-medium rounded-lg transition disabled:opacity-50">
                                <span x-show="!s._rerunning">🔄 Rerun</span><span x-show="s._rerunning">Running...</span>
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
