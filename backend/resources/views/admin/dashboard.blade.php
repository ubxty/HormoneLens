@extends('layouts.admin')
@section('heading','Dashboard')

@section('content')
<div x-data="adminDashboard()" x-init="init()">

    <div x-show="loading" class="text-center py-16">
        <div class="inline-block w-8 h-8 border-[3px] border-purple-400 border-t-transparent rounded-full animate-spin"></div>
        <p class="text-xs text-gray-400 mt-2">Loading dashboard…</p>
    </div>

    <div x-show="!loading">
        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="adm-card relative p-5 adm-a adm-d0" data-adm>
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-400/20 to-blue-500/20 flex items-center justify-center text-lg">👥</div>
                    <span class="adm-badge bg-emerald-100 text-emerald-700" x-text="'+' + d.new_users_7d + ' this week'"></span>
                </div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Users</p>
                <p class="text-2xl font-bold adm-grad-text adm-count-in" x-text="d.total_users"></p>
            </div>
            <div class="adm-card relative p-5 adm-a adm-d1" data-adm>
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-400/20 to-purple-500/20 flex items-center justify-center text-lg">⚡</div>
                    <span class="adm-badge bg-purple-100 text-purple-700" x-text="d.simulations_total + ' total'"></span>
                </div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Simulations Today</p>
                <p class="text-2xl font-bold adm-grad-text adm-count-in" x-text="d.simulations_today"></p>
            </div>
            <div class="adm-card relative p-5 adm-a adm-d2" data-adm>
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400/20 to-red-400/20 flex items-center justify-center text-lg">📈</div>
                </div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Avg Risk Score</p>
                <p class="text-2xl font-bold" :class="d.avg_risk_score>=7?'text-red-500':d.avg_risk_score>=4?'text-amber-500':'text-emerald-500'" x-text="d.avg_risk_score"></p>
            </div>
            <div class="adm-card relative p-5 adm-a adm-d3" data-adm>
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-400/20 to-red-500/20 flex items-center justify-center text-lg">🚨</div>
                    <div class="w-2 h-2 rounded-full bg-red-500 adm-pulse" x-show="d.unread_alerts > 0"></div>
                </div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Unread Alerts</p>
                <p class="text-2xl font-bold text-red-500 adm-count-in" x-text="d.unread_alerts"></p>
            </div>
        </div>

        {{-- Charts --}}
        <div class="grid lg:grid-cols-2 gap-4 mb-6">
            <div class="adm-card relative p-5 adm-a adm-d4" data-adm>
                <h2 class="text-xs font-bold text-gray-700 mb-4 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-purple-400/20 to-pink-400/20 flex items-center justify-center text-xs">🍩</div>
                    Risk Distribution
                </h2>
                <div class="max-w-[200px] mx-auto"><canvas id="riskChart"></canvas></div>
            </div>
            <div class="adm-card relative p-5 adm-a adm-d5" data-adm>
                <h2 class="text-xs font-bold text-gray-700 mb-4 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-blue-400/20 to-purple-400/20 flex items-center justify-center text-xs">📊</div>
                    Simulations (Last 30 Days)
                </h2>
                <canvas id="simChart"></canvas>
            </div>
        </div>

        {{-- Quick Links --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <a href="{{ route('admin.users') }}" class="adm-card relative p-4 text-center group">
                <div class="text-2xl mb-1.5">👥</div>
                <span class="text-xs font-bold text-gray-600 group-hover:adm-grad-text transition">User Monitoring</span>
            </a>
            <a href="{{ route('admin.simulations') }}" class="adm-card relative p-4 text-center group">
                <div class="text-2xl mb-1.5">⚡</div>
                <span class="text-xs font-bold text-gray-600 group-hover:adm-grad-text transition">Simulation Logs</span>
            </a>
            <a href="{{ route('admin.alerts') }}" class="adm-card relative p-4 text-center group">
                <div class="text-2xl mb-1.5">🚨</div>
                <span class="text-xs font-bold text-gray-600 group-hover:adm-grad-text transition">Alert Oversight</span>
            </a>
            <a href="{{ route('admin.rag') }}" class="adm-card relative p-4 text-center group">
                <div class="text-2xl mb-1.5">📚</div>
                <span class="text-xs font-bold text-gray-600 group-hover:adm-grad-text transition">RAG Knowledge</span>
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function adminDashboard() {
    return {
        loading: true, d: {}, report: {},
        async init() {
            const [dash, rep] = await Promise.all([api.get('/admin/dashboard'), api.get('/admin/reports?period_days=30')]);
            if(dash.success) this.d = dash.data;
            if(rep.success) this.report = rep.data;
            this.loading = false;
            this.$nextTick(() => { this.drawRiskChart(); this.drawSimChart(); admAnimate(); });
        },
        drawRiskChart() {
            const rd = this.d.risk_distribution || {};
            const ctx = document.getElementById('riskChart');
            if(!ctx) return;
            new Chart(ctx, { type:'doughnut', data: {
                labels: ['Low','Moderate','High','Critical'],
                datasets: [{ data: [rd.low||0, rd.moderate||0, rd.high||0, rd.critical||0],
                    backgroundColor: ['#10b981','#f59e0b','#ef4444','#991b1b'],
                    borderWidth: 0, hoverOffset: 6 }]
            }, options: { cutout: '65%', plugins: { legend: { position:'bottom', labels: { padding: 12, usePointStyle: true, pointStyleWidth: 8, font: { size: 10, weight: 'bold' } } } } } });
        },
        drawSimChart() {
            const ds = this.report.daily_simulations || [];
            const ctx = document.getElementById('simChart');
            if(!ctx) return;
            new Chart(ctx, { type:'line', data: {
                labels: ds.map(d => d.date),
                datasets: [{
                    label:'Simulations', data: ds.map(d => d.count),
                    borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.08)',
                    fill: true, tension: 0.4, borderWidth: 2, pointRadius: 3,
                    pointBackgroundColor: '#8b5cf6', pointBorderColor: '#fff', pointBorderWidth: 2
                }]
            }, options: { scales: { x: { grid: { display: false }, ticks: { font: { size: 9 } } }, y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' }, ticks: { font: { size: 9 } } } }, plugins: { legend: { display: false } } } });
        }
    };
}
</script>
@endpush
