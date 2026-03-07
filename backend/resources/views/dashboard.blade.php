@extends('layouts.app')
@section('title','Simulation Dashboard — HormoneLens')
@section('heading','Predictive Metabolic Simulation')

@push('styles')
<style>
/* ── Dashboard layout overrides so React panel fills viewport ── */
.dash-bg {
    background: linear-gradient(135deg, rgba(95,111,255,0.06) 0%, rgba(194,77,255,0.06) 50%, rgba(255,110,199,0.06) 100%);
    min-height: 100%;
    position: relative;
    overflow: visible !important;
}
#twin-root {
    min-height: calc(100vh - 56px);
}
/* Loading spinner until React mounts */
#twin-root:empty {
    display: flex;
    align-items: center;
    justify-content: center;
}
#twin-root:empty::after {
    content: '';
    width: 40px;
    height: 40px;
    border: 3px solid rgba(124,58,237,.25);
    border-top-color: #7c3aed;
    border-radius: 50%;
    animation: dashSpin .8s linear infinite;
}
@keyframes dashSpin { to { transform: rotate(360deg); } }
</style>
@endpush

@section('content')
<div id="twin-root" class="-m-4 sm:-m-6" style="min-height:calc(100vh - 56px)"></div>
<div id="dashboard-tour-root" data-user-id="{{ Auth::id() }}"></div>

{{-- ── Analytics Charts Section ── --}}
<div x-data="dashboardCharts()" x-init="init()" class="px-4 sm:px-6 py-8 space-y-6" id="analytics-section">

    {{-- Section Heading --}}
    <div class="flex items-center gap-3 mb-2">
        <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-violet-500/20 to-pink-500/20 flex items-center justify-center text-sm">📊</div>
        <div>
            <h2 class="text-lg font-extrabold bg-gradient-to-r from-violet-600 to-pink-500 bg-clip-text text-transparent">Health Analytics</h2>
            <p class="text-xs text-gray-400">Risk profile & historical trends from your Digital Twin</p>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Radar Chart Card --}}
        <div class="bg-white/60 backdrop-blur-xl border border-white/40 rounded-2xl shadow-lg p-5" data-testid="radar-chart-card">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-700">Risk Profile</h3>
                    <p class="text-xs text-gray-400">5-axis health score breakdown</p>
                </div>
                <span x-show="twin" class="text-xs font-bold px-2 py-0.5 rounded-lg"
                      :class="overallBadgeClass()" x-text="twin?.risk_category?.toUpperCase()"></span>
            </div>
            <template x-if="twin">
                <div class="relative" style="max-height:320px">
                    <canvas id="radarChart" height="300"></canvas>
                </div>
            </template>
            <template x-if="!twin && !loading">
                <div class="text-center py-12">
                    <div class="text-3xl mb-2">🧬</div>
                    <p class="text-sm text-gray-500 font-medium">Generate a Digital Twin to see your risk profile</p>
                    <a href="/digital-twin" class="inline-block mt-3 px-4 py-1.5 bg-gradient-to-r from-violet-600 to-pink-500 text-white rounded-lg text-xs font-bold">Generate Twin →</a>
                </div>
            </template>
        </div>

        {{-- Risk History Line Chart Card --}}
        <div class="bg-white/60 backdrop-blur-xl border border-white/40 rounded-2xl shadow-lg p-5" data-testid="history-chart-card">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-700">Risk Score History</h3>
                    <p class="text-xs text-gray-400">Overall risk trend across twin snapshots</p>
                </div>
                <span x-show="history.length" class="text-xs text-gray-400" x-text="history.length + ' snapshots'"></span>
            </div>
            <template x-if="history.length > 0">
                <div class="relative" style="max-height:320px">
                    <canvas id="historyChart" height="300"></canvas>
                </div>
            </template>
            <template x-if="history.length === 0 && !loading">
                <div class="text-center py-12">
                    <div class="text-3xl mb-2">📈</div>
                    <p class="text-sm text-gray-500 font-medium">Run simulations to build your risk history</p>
                    <a href="/simulations" class="inline-block mt-3 px-4 py-1.5 bg-gradient-to-r from-violet-600 to-pink-500 text-white rounded-lg text-xs font-bold">Run Simulation →</a>
                </div>
            </template>
        </div>
    </div>

    {{-- Score Breakdown Cards --}}
    <template x-if="twin">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            <template x-for="s in scores" :key="s.key">
                <div class="bg-white/60 backdrop-blur-xl border border-white/40 rounded-xl p-3 text-center shadow-sm">
                    <div class="text-xl mb-1" x-text="s.icon"></div>
                    <div class="text-lg font-black bg-gradient-to-r from-violet-600 to-pink-500 bg-clip-text text-transparent" x-text="s.value.toFixed(1)"></div>
                    <div class="text-[10px] font-semibold text-gray-500 mt-0.5" x-text="s.label"></div>
                    <div class="mt-1.5 h-1 bg-gray-200/60 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-700"
                             :style="`width:${Math.min(s.value*10,100)}%;background:${s.color}`"></div>
                    </div>
                </div>
            </template>
        </div>
    </template>
</div>
@endsection

@push('scripts')
@viteReactRefresh
@vite('resources/js/dashboard-twin.jsx')

<script>
function dashboardCharts() {
    return {
        twin: null,
        history: [],
        loading: true,
        radarChart: null,
        historyChart: null,

        get scores() {
            if (!this.twin) return [];
            return [
                { key: 'metabolic_health_score', label: 'Metabolic', icon: '⚡', value: this.twin.metabolic_health_score, color: '#7c3aed' },
                { key: 'insulin_resistance_score', label: 'Insulin', icon: '🩸', value: this.twin.insulin_resistance_score, color: '#f97316' },
                { key: 'sleep_score', label: 'Sleep', icon: '😴', value: this.twin.sleep_score, color: '#3b82f6' },
                { key: 'stress_score', label: 'Stress', icon: '🧠', value: this.twin.stress_score, color: '#ef4444' },
                { key: 'diet_score', label: 'Diet', icon: '🥗', value: this.twin.diet_score, color: '#10b981' },
            ];
        },

        overallBadgeClass() {
            const cat = this.twin?.risk_category;
            if (cat === 'critical') return 'bg-red-100 text-red-600';
            if (cat === 'high') return 'bg-orange-100 text-orange-600';
            if (cat === 'moderate') return 'bg-amber-100 text-amber-600';
            return 'bg-green-100 text-green-600';
        },

        async init() {
            const csrf = document.querySelector('meta[name=csrf-token]')?.content ?? '';
            const opts = { headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf }, credentials: 'same-origin' };

            const [activeRes, allRes] = await Promise.all([
                fetch('/api/digital-twin/active', opts).then(r => r.json()).catch(() => ({})),
                fetch('/api/digital-twin', opts).then(r => r.json()).catch(() => ({})),
            ]);

            if (activeRes?.success && activeRes.data) this.twin = activeRes.data;
            if (allRes?.success && allRes.data) this.history = allRes.data;

            this.loading = false;

            this.$nextTick(() => {
                if (this.twin) this.renderRadar();
                if (this.history.length) this.renderHistory();
            });
        },

        renderRadar() {
            const el = document.getElementById('radarChart');
            if (!el) return;
            if (this.radarChart) this.radarChart.destroy();

            this.radarChart = new Chart(el, {
                type: 'radar',
                data: {
                    labels: ['Metabolic', 'Insulin', 'Sleep', 'Stress', 'Diet'],
                    datasets: [{
                        label: 'Health Scores',
                        data: [
                            this.twin.metabolic_health_score,
                            this.twin.insulin_resistance_score,
                            this.twin.sleep_score,
                            this.twin.stress_score,
                            this.twin.diet_score,
                        ],
                        backgroundColor: 'rgba(124,58,237,0.15)',
                        borderColor: '#7c3aed',
                        borderWidth: 2,
                        pointBackgroundColor: '#7c3aed',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 1,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        r: {
                            beginAtZero: true,
                            max: 10,
                            ticks: { stepSize: 2, font: { size: 10 }, backdropColor: 'transparent', color: '#9ca3af' },
                            grid: { color: 'rgba(124,58,237,0.08)' },
                            angleLines: { color: 'rgba(124,58,237,0.08)' },
                            pointLabels: { font: { size: 11, weight: 'bold' }, color: '#374151' },
                        },
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(255,255,255,0.95)',
                            titleColor: '#374151',
                            bodyColor: '#6b7280',
                            borderColor: 'rgba(124,58,237,0.2)',
                            borderWidth: 1,
                            cornerRadius: 8,
                            padding: 10,
                        },
                    },
                },
            });
        },

        renderHistory() {
            const el = document.getElementById('historyChart');
            if (!el) return;
            if (this.historyChart) this.historyChart.destroy();

            const sorted = [...this.history].sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
            const labels = sorted.map(t => {
                const d = new Date(t.created_at);
                return d.toLocaleDateString('en-IN', { day: 'numeric', month: 'short' });
            });

            this.historyChart = new Chart(el, {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Overall Risk',
                            data: sorted.map(t => t.overall_risk_score),
                            borderColor: '#7c3aed',
                            backgroundColor: 'rgba(124,58,237,0.08)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2.5,
                            pointRadius: 4,
                            pointBackgroundColor: '#7c3aed',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 1.5,
                        },
                        {
                            label: 'Metabolic',
                            data: sorted.map(t => t.metabolic_health_score),
                            borderColor: '#6366f1',
                            borderWidth: 1.5,
                            borderDash: [4, 3],
                            tension: 0.4,
                            pointRadius: 2,
                            fill: false,
                        },
                        {
                            label: 'Insulin',
                            data: sorted.map(t => t.insulin_resistance_score),
                            borderColor: '#f97316',
                            borderWidth: 1.5,
                            borderDash: [4, 3],
                            tension: 0.4,
                            pointRadius: 2,
                            fill: false,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            grid: { color: 'rgba(0,0,0,0.04)' },
                            ticks: { font: { size: 10 }, color: '#9ca3af' },
                            title: { display: true, text: 'Score', font: { size: 10 }, color: '#9ca3af' },
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 10 }, color: '#9ca3af' },
                        },
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 10, boxHeight: 10, borderRadius: 2, font: { size: 10 }, padding: 12 },
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255,255,255,0.95)',
                            titleColor: '#374151',
                            bodyColor: '#6b7280',
                            borderColor: 'rgba(124,58,237,0.2)',
                            borderWidth: 1,
                            cornerRadius: 8,
                            padding: 10,
                        },
                    },
                },
            });
        },
    };
}
</script>
@endpush
