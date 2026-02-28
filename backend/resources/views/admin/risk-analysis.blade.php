@extends('layouts.admin')
@section('heading','Risk Analysis')

@section('content')
<div x-data="riskAnalysis()" x-init="init()">

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-5 adm-a adm-d0" data-adm>
        <select x-model="period" @change="load()" class="adm-input w-auto min-w-[140px]">
            <option value="7">Last 7 Days</option>
            <option value="14">Last 14 Days</option>
            <option value="30" selected>Last 30 Days</option>
            <option value="90">Last 90 Days</option>
        </select>
        <div class="flex items-center gap-1.5 text-[10px] text-gray-400">
            <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 adm-pulse"></div> Live Data
        </div>
    </div>

    <div x-show="loading" class="text-center py-16">
        <div class="inline-block w-8 h-8 border-[3px] border-purple-400 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div x-show="!loading">
        {{-- Risk Distribution Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="adm-card relative p-5 adm-float adm-a adm-d0" data-adm>
                <div class="adm-kpi-accent bg-emerald-500"></div>
                <div class="pl-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100/60 flex items-center justify-center text-lg mb-3">&#128994;</div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Low Risk</p>
                    <p class="text-2xl font-bold text-emerald-600 adm-count-in" x-text="dist.low || 0"></p>
                    <p class="text-[10px] text-gray-400 mt-1" x-text="pct('low') + '% of users'"></p>
                </div>
            </div>
            <div class="adm-card relative p-5 adm-float adm-a adm-d1" data-adm>
                <div class="adm-kpi-accent bg-amber-500"></div>
                <div class="pl-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100/60 flex items-center justify-center text-lg mb-3">&#128993;</div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Moderate Risk</p>
                    <p class="text-2xl font-bold text-amber-600 adm-count-in" x-text="dist.moderate || 0"></p>
                    <p class="text-[10px] text-gray-400 mt-1" x-text="pct('moderate') + '% of users'"></p>
                </div>
            </div>
            <div class="adm-card relative p-5 adm-float adm-a adm-d2" data-adm>
                <div class="adm-kpi-accent bg-red-500"></div>
                <div class="pl-3">
                    <div class="w-10 h-10 rounded-xl bg-red-100/60 flex items-center justify-center text-lg mb-3">&#128308;</div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">High Risk</p>
                    <p class="text-2xl font-bold text-red-600 adm-count-in" x-text="dist.high || 0"></p>
                    <p class="text-[10px] text-gray-400 mt-1" x-text="pct('high') + '% of users'"></p>
                </div>
            </div>
            <div class="adm-card relative p-5 adm-float adm-a adm-d3" data-adm>
                <div class="adm-kpi-accent bg-red-900"></div>
                <div class="pl-3">
                    <div class="w-10 h-10 rounded-xl bg-red-200/60 flex items-center justify-center text-lg mb-3">&#9899;</div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Critical Risk</p>
                    <p class="text-2xl font-bold text-red-800 adm-count-in" x-text="dist.critical || 0"></p>
                    <p class="text-[10px] text-gray-400 mt-1" x-text="pct('critical') + '% of users'"></p>
                </div>
            </div>
        </div>

        {{-- Charts --}}
        <div class="grid lg:grid-cols-2 gap-5 mb-6">
            <div class="adm-chart-glass p-5 adm-a adm-d4" data-adm>
                <h2 class="text-sm font-bold adm-grad-text mb-4 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-purple-400/20 to-pink-400/20 flex items-center justify-center text-xs">&#127849;</div>
                    Distribution Breakdown
                </h2>
                <div class="max-w-[220px] mx-auto"><canvas id="riskDoughnut"></canvas></div>
            </div>
            <div class="adm-chart-glass p-5 adm-a adm-d5" data-adm>
                <h2 class="text-sm font-bold adm-grad-text mb-4 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-blue-400/20 to-purple-400/20 flex items-center justify-center text-xs">&#128200;</div>
                    Risk Trend Over Period
                </h2>
                <canvas id="riskTrend"></canvas>
            </div>
        </div>

        {{-- Risk Score Bar Chart --}}
        <div class="adm-chart-glass p-5 adm-a adm-d5" data-adm>
            <h2 class="text-sm font-bold adm-grad-text mb-4 flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-amber-400/20 to-red-400/20 flex items-center justify-center text-xs">&#128202;</div>
                Risk Category Comparison
            </h2>
            <canvas id="riskBar" style="max-height:260px"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function riskAnalysis() {
    let charts = {};
    return {
        loading: true, period: 30, dist: {}, report: {},
        async load() {
            this.loading = true;
            const [dash, rep] = await Promise.all([
                api.get('/admin/dashboard'),
                api.get('/admin/reports?period_days=' + this.period)
            ]);
            if (dash.success) this.dist = dash.data.risk_distribution || {};
            if (rep.success) this.report = rep.data;
            this.loading = false;
            this.$nextTick(() => { this.drawDoughnut(); this.drawTrend(); this.drawBar(); admAnimate(); });
        },
        async init() { await this.load(); },
        total() { return (this.dist.low || 0) + (this.dist.moderate || 0) + (this.dist.high || 0) + (this.dist.critical || 0); },
        pct(key) { const t = this.total(); return t ? ((this.dist[key] || 0) / t * 100).toFixed(1) : '0'; },
        drawDoughnut() {
            if (charts.doughnut) charts.doughnut.destroy();
            const ctx = document.getElementById('riskDoughnut');
            if (!ctx) return;
            charts.doughnut = new Chart(ctx, { type: 'doughnut', data: {
                labels: ['Low', 'Moderate', 'High', 'Critical'],
                datasets: [{ data: [this.dist.low || 0, this.dist.moderate || 0, this.dist.high || 0, this.dist.critical || 0],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#991b1b'],
                    borderWidth: 0, hoverOffset: 8 }]
            }, options: { cutout: '60%', plugins: { legend: { position: 'bottom', labels: { padding: 14, usePointStyle: true, pointStyleWidth: 8, font: { size: 10, weight: 'bold' } } } } } });
        },
        drawTrend() {
            if (charts.trend) charts.trend.destroy();
            const ctx = document.getElementById('riskTrend');
            if (!ctx) return;
            const rd = this.report.risk_trend || this.report.daily_simulations || [];
            charts.trend = new Chart(ctx, { type: 'line', data: {
                labels: rd.map(d => d.date),
                datasets: [{
                    label: 'Avg Risk', data: rd.map(d => d.avg_risk || d.count || 0),
                    borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.08)',
                    fill: true, tension: 0.4, borderWidth: 2, pointRadius: 3,
                    pointBackgroundColor: '#8b5cf6', pointBorderColor: '#fff', pointBorderWidth: 2
                }]
            }, options: { scales: { x: { grid: { display: false }, ticks: { font: { size: 9 } } }, y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' }, ticks: { font: { size: 9 } } } }, plugins: { legend: { display: false } } } });
        },
        drawBar() {
            if (charts.bar) charts.bar.destroy();
            const ctx = document.getElementById('riskBar');
            if (!ctx) return;
            charts.bar = new Chart(ctx, { type: 'bar', data: {
                labels: ['Low', 'Moderate', 'High', 'Critical'],
                datasets: [{ data: [this.dist.low || 0, this.dist.moderate || 0, this.dist.high || 0, this.dist.critical || 0],
                    backgroundColor: ['rgba(16,185,129,0.7)', 'rgba(245,158,11,0.7)', 'rgba(239,68,68,0.7)', 'rgba(153,27,27,0.7)'],
                    borderRadius: 8, borderSkipped: false }]
            }, options: { scales: { x: { grid: { display: false }, ticks: { font: { size: 10, weight: 'bold' } } }, y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' }, ticks: { font: { size: 9 } } } }, plugins: { legend: { display: false } } } });
        }
    };
}
</script>
@endpush
