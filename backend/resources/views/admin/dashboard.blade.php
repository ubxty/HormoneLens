@extends('layouts.admin')
@section('heading','Dashboard')

@php
    $svgRaw = '';
    $svgPath = public_path('images/men.svg');
    if (file_exists($svgPath)) {
        $svgRaw = file_get_contents($svgPath);
        $svgRaw = preg_replace('/<\?xml[^?]*\?>\s*/', '', $svgRaw);
        $svgRaw = preg_replace('/<!--.*?-->/s', '', $svgRaw);
        $svgRaw = preg_replace(
            '/(<svg[^>]*?)\s+width="(\d+(?:\.\d+)?)"\s+height="(\d+(?:\.\d+)?)"/',
            '$1 viewBox="0 0 $2 $3" preserveAspectRatio="xMidYMid meet"',
            $svgRaw
        );
        $svgRaw = preg_replace('/\s+width="\d+(\.\d+)?"/', '', $svgRaw);
        $svgRaw = preg_replace('/\s+height="\d+(\.\d+)?"/', '', $svgRaw);
    }
@endphp

@push('styles')
<style>
/* body SVG purple gradient */
#dash-body-svg svg { width:100%;height:100%;display:block }
#dash-body-svg svg path[fill="#FEFEFE"],
#dash-body-svg svg rect[fill="#FEFEFE"] { display:none!important }
#dash-body-svg svg path,
#dash-body-svg svg rect,
#dash-body-svg svg ellipse,
#dash-body-svg svg circle,
#dash-body-svg svg line {
    fill: url(#dashPurpleGrad)!important;
    stroke: none!important;
}
/* hotspots */
@keyframes hsPulse { 0%{box-shadow:0 0 0 0 rgba(109,40,217,.6)} 70%{box-shadow:0 0 0 10px rgba(109,40,217,0)} 100%{box-shadow:0 0 0 0 rgba(109,40,217,0)} }
.dash-hs {
    position:absolute; width:14px; height:14px; border-radius:50%;
    background:#6d28d9; transform:translate(-50%,-50%);
    animation:hsPulse 2.2s ease-out infinite; cursor:pointer; z-index:12;
}
.dash-hs::before { content:''; position:absolute; inset:-12px; border-radius:50% }
.dash-hs:hover { background:#7c3aed; transform:translate(-50%,-50%) scale(1.4) }
/* floating tags */
@keyframes tagBob { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-5px)} }
.dash-tag {
    position:absolute; font-size:10px; font-weight:700; color:#6d28d9;
    background:rgba(255,255,255,.82); backdrop-filter:blur(6px);
    border:1px solid rgba(109,40,217,.18); border-radius:20px;
    padding:3px 10px; white-space:nowrap; z-index:10; pointer-events:none;
    animation:tagBob 4s ease-in-out infinite;
}
/* kpi card */
.dash-kpi {
    background:rgba(255,255,255,.6); backdrop-filter:blur(14px);
    border:1px solid rgba(255,255,255,.4); border-radius:16px;
    transition:transform .3s, box-shadow .3s; position:relative; overflow:hidden;
}
.dash-kpi:hover { transform:translateY(-4px); box-shadow:0 12px 32px rgba(109,40,217,.12) }
/* progress */
.dash-bar { height:5px; border-radius:99px; background:rgba(0,0,0,.06); overflow:hidden }
.dash-bar-fill { height:100%; border-radius:99px; transition:width 1.2s ease }
</style>
@endpush

@section('content')
{{-- Hidden SVG gradient defs --}}
<svg style="position:absolute;width:0;height:0;overflow:hidden" aria-hidden="true">
    <defs>
        <linearGradient id="dashPurpleGrad" x1="0%" y1="0%" x2="60%" y2="100%">
            <stop offset="0%"   stop-color="#7c3aed" stop-opacity="0.82"/>
            <stop offset="50%"  stop-color="#8b5cf6" stop-opacity="0.75"/>
            <stop offset="100%" stop-color="#a78bfa" stop-opacity="0.65"/>
        </linearGradient>
    </defs>
</svg>

<div id="dash-app">

    {{-- LOADING --}}
    <div id="dash-loading" class="flex items-center justify-center" style="min-height:60vh">
        <div class="text-center">
            <div class="inline-block w-10 h-10 border-4 border-purple-400 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-sm text-gray-400 mt-3">Loading dashboard...</p>
        </div>
    </div>

    {{-- MAIN CONTENT  --}}
    <div id="dash-content" style="display:none">
        <div class="grid lg:grid-cols-5 gap-5" style="min-height:80vh">

            {{-- LEFT COLUMN — Stats (3/5) --}}
            <div class="lg:col-span-3 space-y-4">

                {{-- Header --}}
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-purple-500">Live Dashboard</span>
                        </div>
                        <h1 class="text-lg font-black" style="background:linear-gradient(135deg,#5f6fff,#c24dff,#ff6ec7);-webkit-background-clip:text;-webkit-text-fill-color:transparent">
                            AI Metabolic Command Center
                        </h1>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] text-gray-400">Total users</p>
                        <p id="kpi-total-users-hero" class="text-2xl font-black" style="background:linear-gradient(135deg,#5f6fff,#c24dff);-webkit-background-clip:text;-webkit-text-fill-color:transparent">&mdash;</p>
                    </div>
                </div>

                {{-- KPI GRID --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" id="kpi-grid">
                    {{-- 1. Total Users --}}
                    <div class="dash-kpi p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center text-base">&#x1F465;</div>
                            <span class="text-[9px] font-bold uppercase text-indigo-500 bg-indigo-50 px-1.5 py-0.5 rounded">Users</span>
                        </div>
                        <p class="text-[9px] font-bold text-gray-400 uppercase">Total Users</p>
                        <p class="text-xl font-black text-gray-800" id="kpi-total-users">&mdash;</p>
                        <p class="text-[9px] text-gray-400">+<span id="kpi-new-users">0</span> this week</p>
                        <div class="dash-bar mt-2"><div class="dash-bar-fill bg-gradient-to-r from-indigo-500 to-purple-500" id="bar-users" style="width:0%"></div></div>
                    </div>
                    {{-- 2. Avg Risk --}}
                    <div class="dash-kpi p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-amber-100 to-orange-100 flex items-center justify-center text-base">&#x1F4C8;</div>
                            <span class="text-[9px] font-bold uppercase bg-amber-50 px-1.5 py-0.5 rounded" id="kpi-risk-badge">&mdash;</span>
                        </div>
                        <p class="text-[9px] font-bold text-gray-400 uppercase">Avg Risk Score</p>
                        <p class="text-xl font-black" id="kpi-avg-risk">&mdash;</p>
                        <p class="text-[9px] text-gray-400">Population avg / 10</p>
                        <div class="dash-bar mt-2"><div class="dash-bar-fill" id="bar-risk" style="width:0%;background:linear-gradient(90deg,#f59e0b,#ef4444)"></div></div>
                    </div>
                    {{-- 3. High Risk --}}
                    <div class="dash-kpi p-4 col-span-2 sm:col-span-1">
                        <div class="flex items-start justify-between mb-2">
                            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-red-100 to-rose-100 flex items-center justify-center text-base">&#x1F6A8;</div>
                            <span class="text-[9px] font-bold uppercase text-red-400 bg-red-50 px-1.5 py-0.5 rounded animate-pulse">Alert</span>
                        </div>
                        <p class="text-[9px] font-bold text-gray-400 uppercase">High Risk Users</p>
                        <p class="text-xl font-black text-red-600" id="kpi-high-risk">&mdash;</p>
                        <p class="text-[9px] text-gray-400">High + critical tier</p>
                        <div class="dash-bar mt-2"><div class="dash-bar-fill" id="bar-high" style="width:0%;background:linear-gradient(90deg,#ef4444,#991b1b)"></div></div>
                    </div>
                    {{-- 4. Simulations Today --}}
                    <div class="dash-kpi p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-purple-100 to-violet-100 flex items-center justify-center text-base">&#x26A1;</div>
                            <span class="text-[9px] font-bold uppercase text-purple-500 bg-purple-50 px-1.5 py-0.5 rounded">Today</span>
                        </div>
                        <p class="text-[9px] font-bold text-gray-400 uppercase">Simulations</p>
                        <p class="text-xl font-black text-purple-700" id="kpi-sims-today">&mdash;</p>
                        <p class="text-[9px] text-gray-400">Total: <span id="kpi-sims-total">&mdash;</span></p>
                        <div class="dash-bar mt-2"><div class="dash-bar-fill bg-gradient-to-r from-purple-500 to-violet-500" id="bar-sims" style="width:0%"></div></div>
                    </div>
                    {{-- 5. Week --}}
                    <div class="dash-kpi p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-pink-100 to-rose-100 flex items-center justify-center text-base">&#x1F4C5;</div>
                            <span class="text-[9px] font-bold uppercase text-pink-500 bg-pink-50 px-1.5 py-0.5 rounded">Week</span>
                        </div>
                        <p class="text-[9px] font-bold text-gray-400 uppercase">This Week</p>
                        <p class="text-xl font-black text-pink-600" id="kpi-sims-week">&mdash;</p>
                        <p class="text-[9px] text-gray-400">Last 7 days</p>
                        <div class="dash-bar mt-2"><div class="dash-bar-fill bg-gradient-to-r from-pink-500 to-rose-500" id="bar-week" style="width:0%"></div></div>
                    </div>
                    {{-- 6. Alerts --}}
                    <div class="dash-kpi p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-red-100 to-orange-100 flex items-center justify-center text-base">&#x1F514;</div>
                            <span class="text-[9px] font-bold uppercase text-red-400 bg-red-50 px-1.5 py-0.5 rounded" id="kpi-alert-badge" style="display:none">Unread</span>
                        </div>
                        <p class="text-[9px] font-bold text-gray-400 uppercase">Active Alerts</p>
                        <p class="text-xl font-black text-red-500" id="kpi-alerts">&mdash;</p>
                        <p class="text-[9px] text-gray-400">Unresolved incidents</p>
                        <div class="dash-bar mt-2"><div class="dash-bar-fill" id="bar-alerts" style="width:0%;background:linear-gradient(90deg,#ef4444,#dc2626)"></div></div>
                    </div>
                </div>

                {{-- CHARTS ROW --}}
                <div class="grid sm:grid-cols-2 gap-4">
                    {{-- Risk Distribution --}}
                    <div class="adm-chart-glass p-4">
                        <h4 class="text-sm font-bold mb-3" style="background:linear-gradient(135deg,#5f6fff,#c24dff);-webkit-background-clip:text;-webkit-text-fill-color:transparent">Risk Distribution</h4>
                        <div style="position:relative;max-height:180px;display:flex;align-items:center;justify-content:center">
                            <canvas id="riskChart"></canvas>
                        </div>
                        <div class="mt-3 space-y-2" id="risk-tiers"></div>
                    </div>
                    {{-- Simulation Trend --}}
                    <div class="adm-chart-glass p-4">
                        <h4 class="text-sm font-bold mb-1" style="background:linear-gradient(135deg,#5f6fff,#c24dff);-webkit-background-clip:text;-webkit-text-fill-color:transparent">Simulation Activity</h4>
                        <p class="text-[10px] text-gray-400 mb-3">Last 30 days</p>
                        <div style="height:200px"><canvas id="simChart"></canvas></div>
                    </div>
                </div>

                {{-- QUICK ACTIONS --}}
                <div>
                    <h4 class="text-sm font-bold mb-3" style="background:linear-gradient(135deg,#5f6fff,#c24dff);-webkit-background-clip:text;-webkit-text-fill-color:transparent">Quick Actions</h4>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                        <a href="{{ route('admin.users') }}" class="adm-action p-3 text-center">
                            <div class="w-8 h-8 rounded-lg mx-auto bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-sm mb-1.5">&#x1F465;</div>
                            <p class="text-[10px] font-bold text-gray-700">Users</p>
                        </a>
                        <a href="{{ route('admin.risk-analysis') }}" class="adm-action p-3 text-center">
                            <div class="w-8 h-8 rounded-lg mx-auto bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center text-white text-sm mb-1.5">&#x1F4C8;</div>
                            <p class="text-[10px] font-bold text-gray-700">Risk</p>
                        </a>
                        <a href="{{ route('admin.simulations') }}" class="adm-action p-3 text-center">
                            <div class="w-8 h-8 rounded-lg mx-auto bg-gradient-to-br from-purple-500 to-violet-600 flex items-center justify-center text-white text-sm mb-1.5">&#x26A1;</div>
                            <p class="text-[10px] font-bold text-gray-700">Sims</p>
                        </a>
                        <a href="{{ route('admin.alerts') }}" class="adm-action p-3 text-center">
                            <div class="w-8 h-8 rounded-lg mx-auto bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center text-white text-sm mb-1.5">&#x1F6A8;</div>
                            <p class="text-[10px] font-bold text-gray-700">Alerts</p>
                        </a>
                        <a href="{{ route('admin.reports') }}" class="adm-action p-3 text-center">
                            <div class="w-8 h-8 rounded-lg mx-auto bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center text-white text-sm mb-1.5">&#x1F4CB;</div>
                            <p class="text-[10px] font-bold text-gray-700">Reports</p>
                        </a>
                        <a href="{{ route('admin.rag') }}" class="adm-action p-3 text-center">
                            <div class="w-8 h-8 rounded-lg mx-auto bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white text-sm mb-1.5">&#x1F4DA;</div>
                            <p class="text-[10px] font-bold text-gray-700">RAG</p>
                        </a>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN — Body SVG (2/5) --}}
            <div class="lg:col-span-2">
                <div class="sticky top-4" style="height:calc(100vh - 7rem)">
                    <div class="adm-chart-glass h-full flex flex-col items-center justify-center relative overflow-hidden">

                        {{-- Glow blobs --}}
                        <div style="position:absolute;width:180px;height:180px;border-radius:50%;background:radial-gradient(circle,rgba(109,40,217,.15),transparent 70%);top:-30px;right:-20px;filter:blur(40px);pointer-events:none"></div>
                        <div style="position:absolute;width:140px;height:140px;border-radius:50%;background:radial-gradient(circle,rgba(76,29,149,.12),transparent 70%);bottom:30px;left:-15px;filter:blur(40px);pointer-events:none"></div>

                        {{-- Label --}}
                        <div class="absolute top-4 left-0 right-0 text-center z-10">
                            <span class="text-[10px] font-bold uppercase tracking-widest" style="background:linear-gradient(135deg,#5f6fff,#c24dff);-webkit-background-clip:text;-webkit-text-fill-color:transparent">Metabolic Risk Map</span>
                        </div>

                        {{-- Floating tags --}}
                        <div class="dash-tag" style="top:10%;left:4%;animation-delay:0s">&#x1F9E0; HPA Axis</div>
                        <div class="dash-tag" style="top:20%;right:3%;animation-delay:1.2s">&#x1F98B; Thyroid</div>
                        <div class="dash-tag" style="top:38%;left:3%;animation-delay:2.4s">&#x1F4AA; Insulin</div>
                        <div class="dash-tag" style="top:52%;right:3%;animation-delay:3.6s">&#x1F9A0; Gut Axis</div>
                        <div class="dash-tag" style="top:68%;left:4%;animation-delay:4.8s">&#x1F338; Hormones</div>
                        <div class="dash-tag" style="bottom:10%;right:4%;animation-delay:6s">&#x26A1; Glucose</div>

                        {{-- SVG container --}}
                        <div class="relative w-full h-full flex items-center justify-center px-8" style="padding-top:2rem;padding-bottom:5rem">
                            <div id="dash-body-svg" style="width:100%;height:100%">{!! $svgRaw !!}</div>

                            {{-- Pulsing hotspots --}}
                            <div class="dash-hs" style="top:17%;left:51%" title="Brain & HPA Axis"></div>
                            <div class="dash-hs" style="top:25%;left:51%" title="Thyroid Gland"></div>
                            <div class="dash-hs" style="top:33%;left:47%" title="Heart & Adrenals"></div>
                            <div class="dash-hs" style="top:38%;left:41%" title="Muscle & Insulin"></div>
                            <div class="dash-hs" style="top:38%;left:57%" title="Blood Panel"></div>
                            <div class="dash-hs" style="top:46%;left:51%" title="Gut-Hormone Axis"></div>
                            <div class="dash-hs" style="top:56%;left:51%" title="Reproductive Hormones"></div>
                            <div class="dash-hs" style="top:71%;left:51%" title="Leg Muscles & Glucose"></div>
                        </div>

                        {{-- Bottom stat pills --}}
                        <div class="absolute bottom-4 left-4 right-4 z-10">
                            <div class="grid grid-cols-3 gap-2">
                                <div class="bg-white/60 backdrop-blur rounded-xl p-2 text-center border border-white/50">
                                    <p class="text-[9px] text-gray-400 font-medium">Avg Risk</p>
                                    <p class="text-sm font-black leading-tight" id="pill-risk" style="background:linear-gradient(135deg,#5f6fff,#c24dff);-webkit-background-clip:text;-webkit-text-fill-color:transparent">&mdash;</p>
                                </div>
                                <div class="bg-white/60 backdrop-blur rounded-xl p-2 text-center border border-white/50">
                                    <p class="text-[9px] text-gray-400 font-medium">High Risk</p>
                                    <p class="text-sm font-black text-red-600 leading-tight" id="pill-high">&mdash;</p>
                                </div>
                                <div class="bg-white/60 backdrop-blur rounded-xl p-2 text-center border border-white/50">
                                    <p class="text-[9px] text-gray-400 font-medium">Alerts</p>
                                    <p class="text-sm font-black text-amber-600 leading-tight" id="pill-alerts">&mdash;</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function() {
    var d = {};
    var report = {};

    async function load() {
        try {
            var results = await Promise.all([
                api.get('/admin/dashboard'),
                api.get('/admin/reports?period_days=30')
            ]);
            if (results[0].success) d = results[0].data;
            if (results[1].success) report = results[1].data;
        } catch(e) {
            console.error('Dashboard load error:', e);
        }
        document.getElementById('dash-loading').style.display = 'none';
        document.getElementById('dash-content').style.display = '';
        render();
    }

    function render() {
        var total = d.total_users || 0;
        var avg = parseFloat(d.avg_risk_score) || 0;
        var rd = d.risk_distribution || {};
        var high = (rd.high || 0) + (rd.critical || 0);

        setText('kpi-total-users-hero', total);
        setText('kpi-total-users', total);
        setText('kpi-new-users', d.new_users_7d || 0);
        setText('kpi-avg-risk', avg.toFixed(1));
        setText('kpi-high-risk', high);
        setText('kpi-sims-today', d.simulations_today || 0);
        setText('kpi-sims-total', d.simulations_total || 0);
        setText('kpi-sims-week', d.simulations_week || 0);
        setText('kpi-alerts', d.unread_alerts || 0);

        setText('pill-risk', avg.toFixed(1));
        setText('pill-high', high);
        setText('pill-alerts', d.unread_alerts || 0);

        var rb = document.getElementById('kpi-risk-badge');
        if (rb) {
            if (avg >= 7) { rb.textContent = 'Critical'; rb.className = 'text-[9px] font-bold uppercase bg-red-50 text-red-500 px-1.5 py-0.5 rounded'; }
            else if (avg >= 4) { rb.textContent = 'Warning'; rb.className = 'text-[9px] font-bold uppercase bg-amber-50 text-amber-500 px-1.5 py-0.5 rounded'; }
            else { rb.textContent = 'Healthy'; rb.className = 'text-[9px] font-bold uppercase bg-emerald-50 text-emerald-500 px-1.5 py-0.5 rounded'; }
        }
        var riskEl = document.getElementById('kpi-avg-risk');
        if (riskEl) riskEl.className = 'text-xl font-black ' + (avg >= 7 ? 'text-red-600' : avg >= 4 ? 'text-amber-600' : 'text-emerald-600');

        if ((d.unread_alerts || 0) > 0) {
            var ab = document.getElementById('kpi-alert-badge');
            if (ab) ab.style.display = '';
        }

        setWidth('bar-users', Math.min(total / 100 * 100, 100));
        setWidth('bar-risk', Math.min(avg / 10 * 100, 100));
        setWidth('bar-high', total ? (high / total * 100) : 0);
        setWidth('bar-sims', Math.min((d.simulations_today || 0) / 50 * 100, 100));
        setWidth('bar-week', Math.min((d.simulations_week || 0) / 200 * 100, 100));
        setWidth('bar-alerts', Math.min((d.unread_alerts || 0) / 20 * 100, 100));

        buildTiers(rd, total);
        drawRiskChart(rd);
        drawSimChart(report.daily_simulations || []);
    }

    function setText(id, val) {
        var el = document.getElementById(id);
        if (el) el.textContent = val;
    }
    function setWidth(id, pct) {
        setTimeout(function() {
            var el = document.getElementById(id);
            if (el) el.style.width = pct + '%';
        }, 100);
    }

    function buildTiers(rd, total) {
        var container = document.getElementById('risk-tiers');
        if (!container) return;
        var tiers = [
            { label:'Low', count:rd.low||0, color:'#10b981' },
            { label:'Moderate', count:rd.moderate||0, color:'#f59e0b' },
            { label:'High', count:rd.high||0, color:'#ef4444' },
            { label:'Critical', count:rd.critical||0, color:'#7f1d1d' },
        ];
        var html = '';
        tiers.forEach(function(t) {
            var pct = total ? Math.round(t.count / total * 100) : 0;
            html += '<div>'
                + '<div class="flex items-center justify-between mb-1">'
                + '<div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full inline-block" style="background:'+t.color+'"></span><span class="text-[11px] font-semibold text-gray-600">'+t.label+'</span></div>'
                + '<span class="text-[11px] font-black text-gray-800">'+t.count+' <span class="text-gray-400 font-normal">('+pct+'%)</span></span>'
                + '</div>'
                + '<div class="dash-bar"><div class="dash-bar-fill" style="width:'+pct+'%;background:'+t.color+'"></div></div>'
                + '</div>';
        });
        container.innerHTML = html;
    }

    function drawRiskChart(rd) {
        var ctx = document.getElementById('riskChart');
        if (!ctx) return;
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Low','Moderate','High','Critical'],
                datasets: [{ data:[rd.low||0, rd.moderate||0, rd.high||0, rd.critical||0],
                    backgroundColor:['#10b981','#f59e0b','#ef4444','#7f1d1d'],
                    borderWidth:3, borderColor:'rgba(255,255,255,.85)', hoverOffset:10 }]
            },
            options: {
                cutout:'68%',
                animation:{ animateRotate:true, duration:1200 },
                plugins:{ legend:{ position:'bottom', labels:{ padding:12, usePointStyle:true, pointStyleWidth:8, font:{size:10,weight:'700'} } } }
            }
        });
    }

    function drawSimChart(ds) {
        var ctx = document.getElementById('simChart');
        if (!ctx || !ds.length) return;
        new Chart(ctx, {
            type:'line',
            data:{
                labels: ds.map(function(r){ return new Date(r.date).toLocaleDateString('en',{month:'short',day:'numeric'}); }),
                datasets:[{
                    label:'Simulations', data:ds.map(function(r){return r.count}),
                    borderColor:'#8b5cf6', backgroundColor:'rgba(139,92,246,.1)',
                    fill:true, tension:.4, borderWidth:2.5,
                    pointRadius:2, pointBackgroundColor:'#8b5cf6'
                }]
            },
            options:{
                responsive:true, maintainAspectRatio:false,
                scales:{
                    y:{beginAtZero:true, grid:{color:'rgba(0,0,0,.04)'}, ticks:{font:{size:9}}},
                    x:{grid:{display:false}, ticks:{font:{size:9}, maxTicksLimit:8}}
                },
                plugins:{ legend:{display:false} }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', load);
    } else {
        load();
    }
})();
</script>
@endpush
