@extends('layouts.admin')
@section('heading','AI Metabolic Command Center')

@section('content')
<div x-data="adminDashboard()" x-init="init()" class="space-y-6">

    {{-- Loading --}}
    <div x-show="loading" class="text-center py-20">
        <div class="inline-block w-10 h-10 border-4 border-purple-300/40 border-t-purple-500 rounded-full animate-spin"></div>
        <p class="text-sm text-gray-400 mt-3">Initializing AI Metabolic Command Center&#8230;</p>
    </div>

    <div x-show="!loading" x-cloak>

        {{-- Welcome Banner --}}
        <div class="adm-banner p-6 mb-6 text-white adm-a adm-d0" data-adm>
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold">Metabolic Risk Monitoring Panel</h2>
                    <p class="text-sm text-white/70 mt-1">Real-time population health overview &bull; AI-powered analytics</p>
                </div>
                <div class="hidden sm:flex items-center gap-2 text-xs text-white/50">
                    <div class="w-2 h-2 rounded-full bg-emerald-400 adm-pulse"></div> Live
                </div>
            </div>
        </div>

        {{-- KPI Cards --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-6">

            {{-- Total Users --}}
            <div class="adm-card adm-float p-5 adm-a adm-d0" data-adm>
                <div class="adm-kpi-accent bg-gradient-to-b from-[#5f6fff] to-[#818cf8]"></div>
                <div class="flex items-start justify-between pl-3">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Users</p>
                        <h3 class="text-3xl font-extrabold text-gray-800 adm-count-num" x-text="d.total_users"></h3>
                        <p class="text-[10px] text-gray-400 mt-1.5">+<span x-text="d.new_users_7d"></span> this week</p>
                    </div>
                    <div class="adm-kpi-icon bg-gradient-to-br from-[#5f6fff]/15 to-[#818cf8]/15">&#128101;</div>
                </div>
                <div class="adm-progress mt-4"><div class="adm-progress-fill" :style="'width:' + Math.min((d.total_users||0)/100*100, 100) + '%'"></div></div>
            </div>

            {{-- Avg Risk Score --}}
            <div class="adm-card adm-float p-5 adm-a adm-d1" data-adm>
                <div class="adm-kpi-accent bg-gradient-to-b from-[#f59e0b] to-[#fbbf24]"></div>
                <div class="flex items-start justify-between pl-3">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Avg Risk Score</p>
                        <h3 class="text-3xl font-extrabold adm-count-num" :class="parseFloat(d.avg_risk_score)>=7?'text-red-600':parseFloat(d.avg_risk_score)>=4?'text-amber-600':'text-emerald-600'" x-text="d.avg_risk_score"></h3>
                        <p class="text-[10px] text-gray-400 mt-1.5">Population average</p>
                    </div>
                    <div class="adm-kpi-icon bg-gradient-to-br from-[#f59e0b]/15 to-[#fbbf24]/15">&#128200;</div>
                </div>
                <div class="adm-progress mt-4"><div class="adm-progress-fill" :style="'width:' + Math.min((parseFloat(d.avg_risk_score)||0)/10*100, 100) + '%'" style="background:linear-gradient(90deg,#f59e0b,#ef4444)"></div></div>
            </div>

            {{-- High Risk Users --}}
            <div class="adm-card adm-float p-5 adm-a adm-d2" data-adm>
                <div class="adm-kpi-accent bg-gradient-to-b from-[#ef4444] to-[#f87171]"></div>
                <div class="flex items-start justify-between pl-3">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">High Risk Users</p>
                        <h3 class="text-3xl font-extrabold text-red-600 adm-count-num" x-text="(d.risk_distribution?.high||0) + (d.risk_distribution?.critical||0)"></h3>
                        <p class="text-[10px] text-gray-400 mt-1.5">Need immediate attention</p>
                    </div>
                    <div class="adm-kpi-icon bg-gradient-to-br from-[#ef4444]/15 to-[#f87171]/15">&#128308;</div>
                </div>
                <div class="adm-progress mt-4"><div class="adm-progress-fill" style="background:linear-gradient(90deg,#ef4444,#991b1b)" :style="'width:' + (d.total_users ? (((d.risk_distribution?.high||0)+(d.risk_distribution?.critical||0))/d.total_users*100) : 0) + '%'"></div></div>
            </div>

            {{-- Simulations Today --}}
            <div class="adm-card adm-float p-5 adm-a adm-d3" data-adm>
                <div class="adm-kpi-accent bg-gradient-to-b from-[#c24dff] to-[#a855f7]"></div>
                <div class="flex items-start justify-between pl-3">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Simulations Today</p>
                        <h3 class="text-3xl font-extrabold text-purple-700 adm-count-num" x-text="d.simulations_today"></h3>
                        <p class="text-[10px] text-gray-400 mt-1.5"><span x-text="d.simulations_total"></span> total</p>
                    </div>
                    <div class="adm-kpi-icon bg-gradient-to-br from-[#c24dff]/15 to-[#a855f7]/15">&#9889;</div>
                </div>
                <div class="adm-progress mt-4"><div class="adm-progress-fill" style="background:linear-gradient(90deg,#c24dff,#a855f7)" :style="'width:' + Math.min((d.simulations_today||0)/50*100, 100) + '%'"></div></div>
            </div>

            {{-- Simulations This Week --}}
            <div class="adm-card adm-float p-5 adm-a adm-d4" data-adm>
                <div class="adm-kpi-accent bg-gradient-to-b from-[#ff6ec7] to-[#f472b6]"></div>
                <div class="flex items-start justify-between pl-3">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">This Week</p>
                        <h3 class="text-3xl font-extrabold text-pink-600 adm-count-num" x-text="d.simulations_week"></h3>
                        <p class="text-[10px] text-gray-400 mt-1.5">Last 7 days</p>
                    </div>
                    <div class="adm-kpi-icon bg-gradient-to-br from-[#ff6ec7]/15 to-[#f472b6]/15">&#128202;</div>
                </div>
                <div class="adm-progress mt-4"><div class="adm-progress-fill" style="background:linear-gradient(90deg,#ff6ec7,#f472b6)" :style="'width:' + Math.min((d.simulations_week||0)/200*100, 100) + '%'"></div></div>
            </div>

            {{-- Unread Alerts --}}
            <div class="adm-card adm-float p-5 adm-a adm-d5" data-adm>
                <div class="adm-kpi-accent bg-gradient-to-b from-[#ef4444] to-[#dc2626]"></div>
                <div class="flex items-start justify-between pl-3">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Active Alerts</p>
                        <h3 class="text-3xl font-extrabold text-red-500 adm-count-num" x-text="d.unread_alerts"></h3>
                        <p class="text-[10px] text-gray-400 mt-1.5">Unresolved incidents</p>
                    </div>
                    <div class="adm-kpi-icon bg-gradient-to-br from-[#ef4444]/15 to-[#dc2626]/15">&#128680;</div>
                </div>
                <div class="adm-progress mt-4"><div class="adm-progress-fill" style="background:linear-gradient(90deg,#ef4444,#dc2626)" :style="'width:' + Math.min((d.unread_alerts||0)/20*100, 100) + '%'"></div></div>
            </div>

        </section>

        {{-- Charts Row --}}
        <section class="grid lg:grid-cols-2 gap-5 mb-6">
            <div class="adm-chart-glass p-5 adm-a adm-d4" data-adm>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-sm font-bold adm-grad-text">Risk Distribution</h4>
                        <p class="text-[10px] text-gray-400 mt-0.5">Current population snapshot</p>
                    </div>
                    <div class="flex items-center gap-1.5 text-[10px] text-gray-400">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 adm-pulse"></div> Realtime
                    </div>
                </div>
                <div class="h-64 flex items-center justify-center">
                    <canvas id="riskChart"></canvas>
                </div>
            </div>

            <div class="adm-chart-glass p-5 adm-a adm-d5" data-adm>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="text-sm font-bold adm-grad-text">Simulation Trend</h4>
                        <p class="text-[10px] text-gray-400 mt-0.5">Last 30 days activity</p>
                    </div>
                    <div class="text-[10px] text-gray-400">Smoothed</div>
                </div>
                <div class="h-64"><canvas id="simChart"></canvas></div>
            </div>
        </section>

        {{-- Quick Actions --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('admin.users') }}" class="adm-action p-4 adm-a adm-d0" data-adm>
                <div class="flex items-center gap-3">
                    <div class="adm-action-icon adm-action-pulse">&#128101;</div>
                    <div><div class="text-sm font-bold text-gray-700">User Monitoring</div><div class="text-[10px] text-gray-400">Live profiles &amp; vitals</div></div>
                </div>
            </a>
            <a href="{{ route('admin.simulations') }}" class="adm-action p-4 adm-a adm-d1" data-adm>
                <div class="flex items-center gap-3">
                    <div class="adm-action-icon">&#9889;</div>
                    <div><div class="text-sm font-bold text-gray-700">Simulation Logs</div><div class="text-[10px] text-gray-400">Run history &amp; analytics</div></div>
                </div>
            </a>
            <a href="{{ route('admin.alerts') }}" class="adm-action p-4 adm-a adm-d2" data-adm>
                <div class="flex items-center gap-3">
                    <div class="adm-action-icon">&#128680;</div>
                    <div><div class="text-sm font-bold text-gray-700">Alert Oversight</div><div class="text-[10px] text-gray-400">Investigate incidents</div></div>
                </div>
            </a>
            <a href="{{ route('admin.rag') }}" class="adm-action p-4 adm-a adm-d3" data-adm>
                <div class="flex items-center gap-3">
                    <div class="adm-action-icon">&#128218;</div>
                    <div><div class="text-sm font-bold text-gray-700">RAG Knowledge</div><div class="text-[10px] text-gray-400">AI knowledge base</div></div>
                </div>
            </a>
        </section>

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
            if (dash.success) this.d = dash.data;
            if (rep.success) this.report = rep.data;
            this.loading = false;
            this.$nextTick(() => {
                this.drawRiskChart();
                this.drawSimChart();
                admAnimate();
                this.animateCountUp();
            });
        },
        animateCountUp() {
            document.querySelectorAll('.adm-count-num').forEach(el => {
                const target = parseFloat(el.textContent) || 0;
                admCountUp(el, target, 900);
            });
        },
        drawRiskChart() {
            const rd = this.d.risk_distribution || {};
            const ctx = document.getElementById('riskChart');
            if (!ctx) return;
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Low', 'Moderate', 'High', 'Critical'],
                    datasets: [{
                        data: [rd.low || 0, rd.moderate || 0, rd.high || 0, rd.critical || 0],
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#991b1b'],
                        borderWidth: 0, hoverOffset: 8
                    }]
                },
                options: {
                    cutout: '65%',
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 14, usePointStyle: true, pointStyleWidth: 8, font: { size: 10, weight: 'bold' } } }
                    }
                }
            });
        },
        drawSimChart() {
            const ds = this.report.daily_simulations || [];
            const ctx = document.getElementById('simChart');
            if (!ctx) return;
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ds.map(d => d.date),
                    datasets: [{
                        label: 'Simulations', data: ds.map(d => d.count),
                        borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.08)',
                        fill: true, tension: 0.4, borderWidth: 2, pointRadius: 3,
                        pointBackgroundColor: '#8b5cf6', pointBorderColor: '#fff', pointBorderWidth: 2
                    }]
                },
                options: {
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 9 } } },
                        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' }, ticks: { font: { size: 9 } } }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        }
    };
}
</script>
@endpush
