@extends('layouts.admin')
@section('heading','Dashboard')

@section('content')
<div x-data="adminDashboard()" x-init="init()">

    <div x-show="loading" class="text-center py-16"><div class="animate-spin w-8 h-8 border-4 border-brand-500 border-t-transparent rounded-full mx-auto"></div></div>

    <div x-show="!loading">
        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-sm border p-5">
                <p class="text-sm text-gray-500 mb-1">Total Users</p>
                <p class="text-3xl font-bold text-brand-600" x-text="d.total_users"></p>
                <p class="text-xs text-emerald-500 mt-1" x-text="'+' + d.new_users_7d + ' this week'"></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-5">
                <p class="text-sm text-gray-500 mb-1">Simulations Today</p>
                <p class="text-3xl font-bold text-purple-600" x-text="d.simulations_today"></p>
                <p class="text-xs text-gray-400 mt-1" x-text="d.simulations_total + ' total'"></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-5">
                <p class="text-sm text-gray-500 mb-1">Avg Risk Score</p>
                <p class="text-3xl font-bold" :class="d.avg_risk_score>=7?'text-red-600':d.avg_risk_score>=4?'text-amber-600':'text-emerald-600'" x-text="d.avg_risk_score"></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-5">
                <p class="text-sm text-gray-500 mb-1">Unread Alerts</p>
                <p class="text-3xl font-bold text-red-600" x-text="d.unread_alerts"></p>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6 mb-8">
            {{-- Risk Distribution --}}
            <div class="bg-white rounded-xl shadow-sm border p-5">
                <h2 class="font-semibold text-gray-800 mb-4">Risk Distribution</h2>
                <div class="max-w-xs mx-auto"><canvas id="riskChart"></canvas></div>
            </div>

            {{-- Simulation Trend --}}
            <div class="bg-white rounded-xl shadow-sm border p-5">
                <h2 class="font-semibold text-gray-800 mb-4">Simulations (Last 30 Days)</h2>
                <canvas id="simChart"></canvas>
            </div>
        </div>

        {{-- Quick Links --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <a href="{{ route('admin.users') }}" class="bg-white border rounded-xl p-4 text-center hover:shadow-md transition group">
                <div class="text-2xl mb-1">👥</div><span class="text-sm font-medium text-gray-700 group-hover:text-brand-600">Users</span>
            </a>
            <a href="{{ route('admin.simulations') }}" class="bg-white border rounded-xl p-4 text-center hover:shadow-md transition group">
                <div class="text-2xl mb-1">⚡</div><span class="text-sm font-medium text-gray-700 group-hover:text-brand-600">Simulations</span>
            </a>
            <a href="{{ route('admin.alerts') }}" class="bg-white border rounded-xl p-4 text-center hover:shadow-md transition group">
                <div class="text-2xl mb-1">🔔</div><span class="text-sm font-medium text-gray-700 group-hover:text-brand-600">Alerts</span>
            </a>
            <a href="{{ route('admin.rag') }}" class="bg-white border rounded-xl p-4 text-center hover:shadow-md transition group">
                <div class="text-2xl mb-1">📚</div><span class="text-sm font-medium text-gray-700 group-hover:text-brand-600">RAG KB</span>
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
            this.$nextTick(() => { this.drawRiskChart(); this.drawSimChart(); });
        },
        drawRiskChart() {
            const rd = this.d.risk_distribution || {};
            const ctx = document.getElementById('riskChart');
            if(!ctx) return;
            new Chart(ctx, { type:'doughnut', data: {
                labels: ['Low','Moderate','High','Critical'],
                datasets: [{ data: [rd.low||0, rd.moderate||0, rd.high||0, rd.critical||0], backgroundColor: ['#10b981','#f59e0b','#ef4444','#991b1b'] }]
            }, options: { plugins: { legend: { position:'bottom' } } } });
        },
        drawSimChart() {
            const ds = this.report.daily_simulations || [];
            const ctx = document.getElementById('simChart');
            if(!ctx) return;
            new Chart(ctx, { type:'line', data: {
                labels: ds.map(d => d.date),
                datasets: [{ label:'Simulations', data: ds.map(d => d.count), borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.1)', fill: true, tension: 0.3 }]
            }, options: { scales: { x: { display: true }, y: { beginAtZero: true } }, plugins: { legend: { display: false } } } });
        }
    };
}
</script>
@endpush
