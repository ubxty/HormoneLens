@extends('layouts.admin')
@section('heading','Reports & Analytics')

@section('content')
<div x-data="adminReports()" x-init="init()">

    {{-- Period selector --}}
    <div class="flex items-center gap-3 mb-6">
        <label class="text-sm text-gray-600 font-medium">Period:</label>
        <select x-model="days" @change="load()" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 outline-none bg-white text-sm">
            <option value="7">Last 7 days</option>
            <option value="14">Last 14 days</option>
            <option value="30" selected>Last 30 days</option>
            <option value="90">Last 90 days</option>
        </select>
    </div>

    <div x-show="loading" class="text-center py-16"><div class="animate-spin w-8 h-8 border-4 border-brand-500 border-t-transparent rounded-full mx-auto"></div></div>

    <div x-show="!loading" class="space-y-6">
        {{-- Daily Risk Scores --}}
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <h2 class="font-semibold text-gray-800 mb-4">Average Risk Score Trend</h2>
            <canvas id="riskTrend" height="80"></canvas>
        </div>

        {{-- Daily Simulations --}}
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <h2 class="font-semibold text-gray-800 mb-4">Daily Simulations</h2>
            <canvas id="simTrend" height="80"></canvas>
        </div>

        {{-- Alerts by Severity --}}
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <h2 class="font-semibold text-gray-800 mb-4">Daily Alerts by Severity</h2>
            <canvas id="alertTrend" height="80"></canvas>
        </div>

        {{-- Raw data tables --}}
        <div class="grid lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm border p-5">
                <h3 class="font-semibold text-gray-800 mb-3">Risk Scores</h3>
                <div class="max-h-60 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="border-b"><th class="pb-2 text-left text-gray-500">Date</th><th class="pb-2 text-right text-gray-500">Avg Score</th></tr></thead>
                        <tbody><template x-for="d in data.daily_risk_scores||[]" :key="d.date">
                            <tr class="border-b last:border-0"><td class="py-1.5 text-gray-600" x-text="d.date"></td><td class="py-1.5 text-right font-medium" x-text="parseFloat(d.avg_score || 0).toFixed(2)"></td></tr>
                        </template></tbody>
                    </table>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-5">
                <h3 class="font-semibold text-gray-800 mb-3">Simulation Counts</h3>
                <div class="max-h-60 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="border-b"><th class="pb-2 text-left text-gray-500">Date</th><th class="pb-2 text-right text-gray-500">Count</th></tr></thead>
                        <tbody><template x-for="d in data.daily_simulations||[]" :key="d.date">
                            <tr class="border-b last:border-0"><td class="py-1.5 text-gray-600" x-text="d.date"></td><td class="py-1.5 text-right font-medium" x-text="d.count"></td></tr>
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
            if(r.success) this.data = r.data;
            this.loading = false;
            this.$nextTick(() => this.drawCharts());
        },
        async init() { await this.load(); },
        drawCharts() {
            const rs = this.data.daily_risk_scores || [];
            const ds = this.data.daily_simulations || [];
            const as = this.data.daily_alerts_by_severity || [];

            const ctx1 = document.getElementById('riskTrend');
            if(ctx1) this.charts.risk = new Chart(ctx1, { type:'line', data:{
                labels: rs.map(d=>d.date),
                datasets:[{label:'Avg Risk',data:rs.map(d=>parseFloat(d.avg_score||0)),borderColor:'#ef4444',backgroundColor:'rgba(239,68,68,0.1)',fill:true,tension:0.3}]
            }, options:{scales:{y:{beginAtZero:true,max:10}},plugins:{legend:{display:false}}}});

            const ctx2 = document.getElementById('simTrend');
            if(ctx2) this.charts.sim = new Chart(ctx2, { type:'bar', data:{
                labels: ds.map(d=>d.date),
                datasets:[{label:'Simulations',data:ds.map(d=>d.count),backgroundColor:'rgba(99,102,241,0.6)',borderRadius:4}]
            }, options:{scales:{y:{beginAtZero:true}},plugins:{legend:{display:false}}}});

            const ctx3 = document.getElementById('alertTrend');
            if(ctx3) {
                const dates = [...new Set(as.map(a=>a.date))].sort();
                const bySev = {};
                as.forEach(a => { if(!bySev[a.severity]) bySev[a.severity]=[]; });
                ['critical','warning','info'].forEach(sev => { if(!bySev[sev]) bySev[sev]=[]; });
                const colors = {critical:'#ef4444',warning:'#f59e0b',info:'#3b82f6'};
                const datasets = Object.keys(bySev).map(sev => ({
                    label: sev, data: dates.map(d => { const found = as.find(a=>a.date===d && a.severity===sev); return found ? found.count : 0; }),
                    backgroundColor: colors[sev] || '#9ca3af', borderRadius: 2
                }));
                this.charts.alert = new Chart(ctx3, { type:'bar', data:{ labels:dates, datasets }, options:{ scales:{x:{stacked:true},y:{stacked:true,beginAtZero:true}}, plugins:{legend:{position:'bottom'}} }});
            }
        }
    };
}
</script>
@endpush
