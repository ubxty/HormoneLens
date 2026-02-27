@extends('layouts.app')
@section('title','Digital Twin — HormoneLens')

@section('content')
<div x-data="digitalTwinPage()" x-init="init()">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Digital Twin</h1>
                <p class="text-gray-500">Your personalized metabolic health model.</p>
            </div>
            <button @click="generate()" :disabled="generating"
                    class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-lg transition disabled:opacity-50 text-sm">
                <span x-show="!generating" x-text="twin ? '🔄 Regenerate' : '🧬 Generate Twin'"></span>
                <span x-show="generating">Generating...</span>
            </button>
        </div>

        <div x-show="loading" class="text-center py-16"><div class="animate-spin w-8 h-8 border-4 border-brand-500 border-t-transparent rounded-full mx-auto"></div></div>

        {{-- No twin --}}
        <div x-show="!loading && !twin" class="bg-white rounded-xl shadow-sm border p-8 text-center">
            <div class="text-5xl mb-4">🧬</div>
            <p class="text-gray-600 font-medium mb-2">No Digital Twin found.</p>
            <p class="text-sm text-gray-400">Make sure your Health Profile and Disease Data are filled, then click Generate.</p>
        </div>

        {{-- Twin display --}}
        <div x-show="!loading && twin">
            {{-- Risk badge --}}
            <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 rounded-full flex items-center justify-center text-2xl font-bold text-white"
                         :class="twin?.risk_category==='high'?'bg-red-500':twin?.risk_category==='medium'?'bg-amber-500':'bg-emerald-500'">
                        <span x-text="twin?.overall_risk_score?.toFixed(1)"></span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Overall Risk Score</p>
                        <p class="text-lg font-bold capitalize" :class="twin?.risk_category==='high'?'text-red-600':twin?.risk_category==='medium'?'text-amber-600':'text-emerald-600'" x-text="twin?.risk_category + ' risk'"></p>
                        <p class="text-xs text-gray-400" x-text="'Generated: ' + (twin?.created_at ? new Date(twin.created_at).toLocaleString() : '')"></p>
                    </div>
                </div>
            </div>

            {{-- Score cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
                <template x-for="s in scoreCards" :key="s.key">
                    <div class="bg-white rounded-xl shadow-sm border p-4">
                        <p class="text-xs text-gray-500 mb-2" x-text="s.label"></p>
                        <div class="relative h-2 bg-gray-200 rounded-full mb-2">
                            <div class="absolute inset-y-0 left-0 rounded-full transition-all" :class="s.bg"
                                 :style="'width:' + (twin?.[s.key] || 0) * 10 + '%'"></div>
                        </div>
                        <p class="text-lg font-bold" :class="s.text" x-text="twin?.[s.key]?.toFixed(1) ?? '—'"></p>
                    </div>
                </template>
            </div>

            {{-- Previous twins --}}
            <div class="bg-white rounded-xl shadow-sm border p-5">
                <h2 class="font-semibold text-gray-800 mb-4">Twin History</h2>
                <div x-show="history.length === 0" class="text-sm text-gray-400 text-center py-4">No previous records.</div>
                <div class="overflow-x-auto">
                    <table x-show="history.length > 0" class="w-full text-sm">
                        <thead><tr class="border-b text-left">
                            <th class="pb-2 font-medium text-gray-500">Date</th>
                            <th class="pb-2 font-medium text-gray-500">Overall</th>
                            <th class="pb-2 font-medium text-gray-500">Metabolic</th>
                            <th class="pb-2 font-medium text-gray-500">Insulin</th>
                            <th class="pb-2 font-medium text-gray-500">Sleep</th>
                            <th class="pb-2 font-medium text-gray-500">Stress</th>
                            <th class="pb-2 font-medium text-gray-500">Diet</th>
                            <th class="pb-2 font-medium text-gray-500">Risk</th>
                        </tr></thead>
                        <tbody>
                            <template x-for="h in history" :key="h.id">
                                <tr class="border-b last:border-0" :class="h.is_active ? 'bg-brand-50' : ''">
                                    <td class="py-2 text-gray-600" x-text="new Date(h.created_at).toLocaleDateString()"></td>
                                    <td class="py-2 font-semibold" x-text="h.overall_risk_score?.toFixed(1)"></td>
                                    <td class="py-2" x-text="h.metabolic_health_score?.toFixed(1)"></td>
                                    <td class="py-2" x-text="h.insulin_resistance_score?.toFixed(1)"></td>
                                    <td class="py-2" x-text="h.sleep_score?.toFixed(1)"></td>
                                    <td class="py-2" x-text="h.stress_score?.toFixed(1)"></td>
                                    <td class="py-2" x-text="h.diet_score?.toFixed(1)"></td>
                                    <td class="py-2"><span class="px-2 py-0.5 text-xs rounded-full capitalize"
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
            {key:'metabolic_health_score',label:'Metabolic Health',bg:'bg-indigo-500',text:'text-indigo-600'},
            {key:'insulin_resistance_score',label:'Insulin Resist.',bg:'bg-purple-500',text:'text-purple-600'},
            {key:'sleep_score',label:'Sleep Quality',bg:'bg-blue-500',text:'text-blue-600'},
            {key:'stress_score',label:'Stress Level',bg:'bg-amber-500',text:'text-amber-600'},
            {key:'diet_score',label:'Diet Quality',bg:'bg-emerald-500',text:'text-emerald-600'},
        ],
        async init(){
            const [active, all] = await Promise.all([api.get('/digital-twin/active'), api.get('/digital-twin')]);
            if(active.success && active.data) this.twin = active.data;
            if(all.success && all.data) this.history = all.data;
            this.loading = false;
        },
        async generate(){
            this.generating = true;
            const r = await api.post('/digital-twin/generate');
            if(r.success) { this.twin = r.data; toast('Digital Twin generated!'); await this.refreshHistory(); }
            else toast(r.message || 'Generation failed. Complete your Health Profile & Disease Data first.', 'error');
            this.generating = false;
        },
        async refreshHistory(){ const r = await api.get('/digital-twin'); if(r.success) this.history = r.data; }
    };
}
</script>
@endpush
