@extends('layouts.admin')
@section('heading','Reports & Analytics')

@section('content')
<div x-data="adminReports()" x-init="init()">

    {{-- Period selector --}}
    <div class="flex items-center gap-3 mb-5 adm-a adm-d0" data-adm>
        <select x-model="days" @change="load()" class="adm-input w-auto min-w-[160px]">
            <option value="7">Last 7 days</option>
            <option value="14">Last 14 days</option>
            <option value="30" selected>Last 30 days</option>
            <option value="90">Last 90 days</option>
        </select>
        <div class="flex items-center gap-1.5 text-[10px] text-gray-400">
            <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 adm-pulse"></div> Auto-refresh
        </div>
    </div>

    <div x-show="loading" class="text-center py-16">
        <div class="inline-block w-8 h-8 border-[3px] border-purple-400 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div x-show="!loading" class="space-y-5">
        {{-- Daily Simulations --}}
        <div class="adm-chart-glass p-5 adm-a adm-d2" data-adm>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold adm-grad-text flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-blue-400/20 to-purple-400/20 flex items-center justify-center text-xs">&#9889;</div>
                    Daily Simulations
                </h2>
                <span class="text-[10px] text-gray-400" x-text="(data.daily_simulations||[]).reduce((a,b) => a + b.count, 0) + ' total'"></span>
            </div>
            <canvas id="simTrendR" height="80"></canvas>
        </div>

        {{-- Alerts by Severity --}}
        <div class="adm-chart-glass p-5 adm-a adm-d3" data-adm>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold adm-grad-text flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-amber-400/20 to-red-400/20 flex items-center justify-center text-xs">&#128680;</div>
                    Daily Alerts by Severity
                </h2>
            </div>
            <canvas id="alertTrendR" height="80"></canvas>
        </div>

        {{-- Raw data tables --}}
        <div class="grid lg:grid-cols-2 gap-5">
            <div class="adm-card relative p-5 adm-a adm-d4" data-adm>
                <h3 class="text-xs font-bold text-gray-700 mb-3 flex items-center gap-2">
                    <div class="w-5 h-5 rounded bg-gradient-to-br from-red-400/20 to-orange-400/20 flex items-center justify-center text-[10px]">&#128203;</div>
                    Risk Scores
                </h3>
                <div class="max-h-56 overflow-y-auto">
                    <table class="adm-table w-full text-xs">
                        <thead><tr><th class="pb-2 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Date</th><th class="pb-2 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Avg Score</th></tr></thead>
                        <tbody><template x-for="d in data.daily_risk_scores||[]" :key="d.date">
                            <tr class="border-b border-white/40 last:border-0"><td class="py-1.5 text-gray-500" x-text="d.date"></td><td class="py-1.5 text-right font-bold text-gray-700" x-text="parseFloat(d.avg_score || 0).toFixed(2)"></td></tr>
                        </template></tbody>
                    </table>
                </div>
            </div>
            <div class="adm-card relative p-5 adm-a adm-d5" data-adm>
                <h3 class="text-xs font-bold text-gray-700 mb-3 flex items-center gap-2">
                    <div class="w-5 h-5 rounded bg-gradient-to-br from-blue-400/20 to-purple-400/20 flex items-center justify-center text-[10px]">&#128203;</div>
                    Simulation Counts
                </h3>
                <div class="max-h-56 overflow-y-auto">
                    <table class="adm-table w-full text-xs">
                        <thead><tr><th class="pb-2 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Date</th><th class="pb-2 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Count</th></tr></thead>
                        <tbody><template x-for="d in data.daily_simulations||[]" :key="d.date">
                            <tr class="border-b border-white/40 last:border-0"><td class="py-1.5 text-gray-500" x-text="d.date"></td><td class="py-1.5 text-right font-bold text-gray-700" x-text="d.count"></td></tr>
                        </template></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function adminReports() {
    return {
        loading: true, days: 30, data: {}, charts: {},
        async load() {
            this.loading = true;
            Object.values(this.charts).forEach(c => c.destroy());
            this.charts = {};
            const r = await api.get('/admin/reports?period_days=' + this.days);
            if (r.success) this.data = r.data;
            this.loading = false;
            this.$nextTick(() => { this.drawCharts(); admAnimate(); });
        },
        async init() { await this.load(); },
        drawCharts() {
            const rs = this.data.daily_risk_scores || [];
            const ds = this.data.daily_simulations || [];
            const as = this.data.daily_alerts_by_severity || [];

            const ctx2 = document.getElementById('simTrendR');
            if (ctx2) this.charts.sim = new Chart(ctx2, { type: 'bar', data: {
                labels: ds.map(d => d.date),
                datasets: [{ label: 'Simulations', data: ds.map(d => d.count), backgroundColor: 'rgba(139,92,246,0.6)', borderRadius: 6, borderSkipped: false }]
            }, options: { scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' }, ticks: { font: { size: 9 } } }, x: { grid: { display: false }, ticks: { font: { size: 9 } } } }, plugins: { legend: { display: false } } } });

            const ctx3 = document.getElementById('alertTrendR');
            if (ctx3) {
                const dates = [...new Set(as.map(a => a.date))].sort();
                const bySev = {};
                as.forEach(a => { if (!bySev[a.severity]) bySev[a.severity] = []; });
                ['critical', 'warning', 'info'].forEach(sev => { if (!bySev[sev]) bySev[sev] = []; });
                const colors = { critical: 'rgba(239,68,68,0.7)', warning: 'rgba(245,158,11,0.7)', info: 'rgba(59,130,246,0.7)' };
                const datasets = Object.keys(bySev).map(sev => ({
                    label: sev, data: dates.map(d => { const found = as.find(a => a.date === d && a.severity === sev); return found ? found.count : 0; }),
                    backgroundColor: colors[sev] || '#9ca3af', borderRadius: 3, borderSkipped: false
                }));
                this.charts.alert = new Chart(ctx3, { type: 'bar', data: { labels: dates, datasets }, options: { scales: { x: { stacked: true, grid: { display: false }, ticks: { font: { size: 9 } } }, y: { stacked: true, beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' }, ticks: { font: { size: 9 } } } }, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, pointStyleWidth: 8, padding: 14, font: { size: 10, weight: 'bold' } } } } } });
            }
        }
    };
}
</script>
@endpush
