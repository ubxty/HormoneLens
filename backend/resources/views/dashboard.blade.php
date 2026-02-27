@extends('layouts.app')
@section('title','Dashboard — HormoneLens')

@section('content')
<div x-data="dashboardPage()" x-init="init()">

    {{-- Welcome --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Welcome back, {{ Auth::user()->name }}! 👋</h1>
        <p class="text-gray-500 mt-1">Here's your metabolic health overview.</p>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <p class="text-sm text-gray-500 mb-1">Overall Risk</p>
            <p class="text-3xl font-bold" :class="riskColor" x-text="twin ? twin.overall_risk_score.toFixed(1) : '—'"></p>
            <span x-show="twin" class="mt-1 inline-block px-2 py-0.5 text-xs font-medium rounded-full"
                  :class="riskBadge" x-text="twin?.risk_category"></span>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <p class="text-sm text-gray-500 mb-1">Metabolic Score</p>
            <p class="text-3xl font-bold text-indigo-600" x-text="twin ? twin.metabolic_health_score.toFixed(1) : '—'"></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <p class="text-sm text-gray-500 mb-1">Insulin Resistance</p>
            <p class="text-3xl font-bold text-purple-600" x-text="twin ? twin.insulin_resistance_score.toFixed(1) : '—'"></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <p class="text-sm text-gray-500 mb-1">Unread Alerts</p>
            <p class="text-3xl font-bold text-amber-600" x-text="alertCount"></p>
        </div>
    </div>

    {{-- No twin prompt --}}
    <div x-show="!loading && !twin" class="bg-amber-50 border border-amber-200 rounded-xl p-6 mb-8 text-center">
        <p class="text-amber-800 font-medium mb-2">You haven't generated your Digital Twin yet.</p>
        <p class="text-amber-600 text-sm mb-4">Complete your Health Profile and Disease Data first, then generate your twin.</p>
        <div class="flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('health-profile') }}" class="px-4 py-2 bg-white border border-amber-300 text-amber-700 rounded-lg text-sm font-medium hover:bg-amber-100">Health Profile</a>
            <a href="{{ route('digital-twin') }}" class="px-4 py-2 bg-amber-500 text-white rounded-lg text-sm font-medium hover:bg-amber-600">Generate Twin →</a>
        </div>
    </div>

    {{-- Twin Scores --}}
    <div x-show="twin" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
        <template x-for="s in scores" :key="s.key">
            <div class="bg-white rounded-xl shadow-sm border p-4 text-center">
                <div class="w-16 h-16 mx-auto mb-2 rounded-full flex items-center justify-center text-lg font-bold text-white"
                     :class="s.bg">
                    <span x-text="twin ? twin[s.key]?.toFixed(0) : '—'"></span>
                </div>
                <p class="text-xs text-gray-600" x-text="s.label"></p>
            </div>
        </template>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- Recent simulations --}}
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-800">Recent Simulations</h2>
                <a href="{{ route('simulations') }}" class="text-sm text-brand-600 hover:underline">Run new →</a>
            </div>
            <div x-show="simulations.length === 0" class="text-sm text-gray-400 text-center py-6">No simulations yet.</div>
            <div class="space-y-3">
                <template x-for="s in simulations" :key="s.id">
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                        <span class="text-lg" x-text="s.type==='meal'?'🍽️':s.type==='sleep'?'😴':'😰'"></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-700 truncate" x-text="s.input_data?.description || s.type"></p>
                            <p class="text-xs text-gray-400" x-text="new Date(s.created_at).toLocaleDateString()"></p>
                        </div>
                        <span class="text-sm font-semibold" :class="s.risk_change > 0 ? 'text-red-500' : 'text-emerald-500'"
                              x-text="(s.risk_change > 0 ? '+' : '') + s.risk_change.toFixed(1)"></span>
                    </div>
                </template>
            </div>
        </div>

        {{-- Recent Alerts --}}
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-800">Recent Alerts</h2>
                <a href="{{ route('alerts') }}" class="text-sm text-brand-600 hover:underline">View all →</a>
            </div>
            <div x-show="alerts.length === 0" class="text-sm text-gray-400 text-center py-6">No alerts.</div>
            <div class="space-y-3">
                <template x-for="a in alerts" :key="a.id">
                    <div class="flex items-start gap-3 p-3 rounded-lg" :class="a.is_read ? 'bg-gray-50' : 'bg-amber-50 border border-amber-100'">
                        <span class="text-sm mt-0.5" :class="{'text-red-500':a.severity==='critical','text-amber-500':a.severity==='warning','text-blue-500':a.severity==='info'}">
                            <template x-if="a.severity==='critical'">⛔</template>
                            <template x-if="a.severity==='warning'">⚠️</template>
                            <template x-if="a.severity==='info'">ℹ️</template>
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-700" x-text="a.title"></p>
                            <p class="text-xs text-gray-500 truncate" x-text="a.message"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="mt-8 grid grid-cols-2 sm:grid-cols-4 gap-4">
        <a href="{{ route('health-profile') }}" class="bg-white border rounded-xl p-4 text-center hover:shadow-md transition group">
            <div class="text-2xl mb-1">👤</div>
            <span class="text-sm font-medium text-gray-700 group-hover:text-brand-600">Health Profile</span>
        </a>
        <a href="{{ route('digital-twin') }}" class="bg-white border rounded-xl p-4 text-center hover:shadow-md transition group">
            <div class="text-2xl mb-1">🧬</div>
            <span class="text-sm font-medium text-gray-700 group-hover:text-brand-600">Digital Twin</span>
        </a>
        <a href="{{ route('simulations') }}" class="bg-white border rounded-xl p-4 text-center hover:shadow-md transition group">
            <div class="text-2xl mb-1">⚡</div>
            <span class="text-sm font-medium text-gray-700 group-hover:text-brand-600">Simulations</span>
        </a>
        <a href="{{ route('knowledge') }}" class="bg-white border rounded-xl p-4 text-center hover:shadow-md transition group">
            <div class="text-2xl mb-1">📚</div>
            <span class="text-sm font-medium text-gray-700 group-hover:text-brand-600">Knowledge Base</span>
        </a>
    </div>

    {{-- Loading overlay --}}
    <div x-show="loading" class="fixed inset-0 bg-white/60 z-50 flex items-center justify-center">
        <div class="text-center"><div class="animate-spin w-8 h-8 border-4 border-brand-500 border-t-transparent rounded-full mx-auto mb-2"></div><p class="text-sm text-gray-500">Loading...</p></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function dashboardPage() {
    return {
        loading: true, twin: null, alertCount: 0, simulations: [], alerts: [],
        scores: [
            { key:'metabolic_health_score', label:'Metabolic', bg:'bg-indigo-500' },
            { key:'insulin_resistance_score', label:'Insulin Res.', bg:'bg-purple-500' },
            { key:'sleep_score', label:'Sleep', bg:'bg-blue-500' },
            { key:'stress_score', label:'Stress', bg:'bg-amber-500' },
            { key:'diet_score', label:'Diet', bg:'bg-emerald-500' },
        ],
        get riskColor(){ if(!this.twin) return ''; const s=this.twin.overall_risk_score; return s>=7?'text-red-600':s>=4?'text-amber-600':'text-emerald-600'; },
        get riskBadge(){ if(!this.twin) return ''; const c=this.twin.risk_category; return c==='high'?'bg-red-100 text-red-700':c==='medium'?'bg-amber-100 text-amber-700':'bg-emerald-100 text-emerald-700'; },
        async init(){
            const [twinR, alertR, simR, alertsR] = await Promise.all([
                api.get('/digital-twin/active'),
                api.get('/alerts/unread-count'),
                api.get('/simulations'),
                api.get('/alerts'),
            ]);
            if(twinR.success && twinR.data) this.twin = twinR.data;
            this.alertCount = alertR.data?.count ?? 0;
            this.simulations = (simR.data || []).slice(0,5);
            this.alerts = (alertsR.data || []).slice(0,5);
            this.loading = false;
        }
    };
}
</script>
@endpush
