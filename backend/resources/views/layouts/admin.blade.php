<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin — HormoneLens')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: { extend: { colors: {
            brand: { 50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca',800:'#3730a3',900:'#312e81' }
        }}}
    }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <style>
    [x-cloak] { display: none !important; }
    /* ═══════════════════════════════════════════════
       HormoneLens — Admin Glassmorphism System v2
       Pastel gradient healthcare AI theme
       ═══════════════════════════════════════════════ */

    /* ── Floating Ambient Particles ── */
    .adm-particle{position:fixed;border-radius:50%;filter:blur(80px);pointer-events:none;opacity:.10;will-change:transform;z-index:0}
    .adm-particle-1{width:300px;height:300px;background:linear-gradient(135deg,#5f6fff,#c24dff);top:-60px;right:-40px;animation:admFloat 16s ease-in-out infinite}
    .adm-particle-2{width:240px;height:240px;background:linear-gradient(135deg,#c24dff,#ff6ec7);bottom:10%;left:-30px;animation:admFloat 20s ease-in-out 4s infinite}
    .adm-particle-3{width:180px;height:180px;background:linear-gradient(135deg,#ff6ec7,#5f6fff);top:45%;right:20%;animation:admFloat 18s ease-in-out 8s infinite}
    @keyframes admFloat{0%,100%{transform:translate(0,0) scale(1)}33%{transform:translate(25px,-18px) scale(1.04)}66%{transform:translate(-18px,12px) scale(.96)}}

    /* ── Sidebar ── */
    .adm-sidebar{background:linear-gradient(180deg,#1e1b4b 0%,#312e81 40%,#3b1f6e 100%);position:relative;overflow:hidden}
    .adm-sidebar::before{content:'';position:absolute;top:-40%;right:-30%;width:200px;height:200px;background:radial-gradient(circle,rgba(194,77,255,.15),transparent 70%);border-radius:50%}
    .adm-sidebar::after{content:'';position:absolute;bottom:-20%;left:-20%;width:160px;height:160px;background:radial-gradient(circle,rgba(95,111,255,.12),transparent 70%);border-radius:50%}
    .adm-nav-item{display:flex;align-items:center;gap:.75rem;padding:.55rem .85rem;font-size:.8125rem;font-weight:500;border-radius:12px;color:rgba(255,255,255,.55);transition:all .25s ease;position:relative;z-index:1}
    .adm-nav-item:hover{color:rgba(255,255,255,.9);background:rgba(255,255,255,.07)}
    .adm-nav-active{color:#fff!important;background:linear-gradient(135deg,rgba(95,111,255,.35),rgba(194,77,255,.25))!important;box-shadow:0 4px 16px rgba(95,111,255,.2)}
    .adm-nav-active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:60%;background:linear-gradient(180deg,#5f6fff,#c24dff);border-radius:0 4px 4px 0}

    /* ── Header ── */
    .adm-header{background:rgba(255,255,255,.75);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-bottom:1px solid rgba(255,255,255,.5)}

    /* ── Background ── */
    .adm-bg{background:linear-gradient(135deg,rgba(95,111,255,.04),rgba(194,77,255,.04) 50%,rgba(255,110,199,.04));min-height:100vh}

    /* ── Glass Card — Enhanced ── */
    .adm-card{background:rgba(255,255,255,.55);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.35);border-radius:20px;box-shadow:0 8px 32px rgba(95,111,255,.08);transition:transform .4s cubic-bezier(.4,0,.2,1),box-shadow .4s ease,border-color .4s ease;position:relative;overflow:hidden}
    .adm-card::before{content:'';position:absolute;inset:0;border-radius:20px;padding:1px;background:linear-gradient(135deg,rgba(95,111,255,.2),rgba(194,77,255,.15),rgba(255,110,199,.1));-webkit-mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);-webkit-mask-composite:xor;mask-composite:exclude;pointer-events:none;opacity:0;transition:opacity .4s ease}
    .adm-card:hover::before{opacity:1}
    .adm-card:hover{transform:translateY(-4px);box-shadow:0 12px 40px rgba(95,111,255,.14),0 0 20px rgba(194,77,255,.06)}

    /* ── Gradient Text ── */
    .adm-grad-text{background:linear-gradient(135deg,#5f6fff,#c24dff,#ff6ec7);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}

    /* ── Inputs ── */
    .adm-input{background:rgba(255,255,255,.55);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.35);border-radius:12px;padding:.5rem .75rem;font-size:.8125rem;color:#374151;outline:none;transition:border-color .3s,box-shadow .3s;width:100%}
    .adm-input:focus{border-color:rgba(194,77,255,.3);box-shadow:0 0 0 3px rgba(194,77,255,.08)}

    /* ── Button ── */
    .adm-btn{background:linear-gradient(135deg,#5f6fff,#c24dff);color:#fff;border:none;border-radius:12px;padding:.5rem 1.25rem;font-size:.8125rem;font-weight:700;cursor:pointer;transition:all .3s ease}
    .adm-btn:hover{filter:brightness(1.08);box-shadow:0 4px 20px rgba(95,111,255,.3)}
    .adm-btn:disabled{opacity:.5;cursor:not-allowed}

    /* ── Table — Enhanced ── */
    .adm-table{width:100%;font-size:.8125rem;border-collapse:separate;border-spacing:0}
    .adm-table thead{background:rgba(95,111,255,.04)}
    .adm-table th{padding:.65rem .85rem;text-align:left;font-weight:600;color:#6b7280;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid rgba(0,0,0,.05)}
    .adm-table td{padding:.65rem .85rem;border-bottom:1px solid rgba(0,0,0,.03);color:#374151}
    .adm-table tbody tr{transition:all .25s ease}
    .adm-table tbody tr:hover{background:rgba(194,77,255,.04);box-shadow:inset 3px 0 0 rgba(194,77,255,.3)}
    .adm-row-risk{background:rgba(239,68,68,.03)!important}
    .adm-row-risk:hover{background:rgba(239,68,68,.07)!important;box-shadow:inset 3px 0 0 rgba(239,68,68,.4)!important}

    /* ── Badge ── */
    .adm-badge{display:inline-flex;align-items:center;padding:2px 10px;font-size:10px;font-weight:700;border-radius:20px;text-transform:uppercase;letter-spacing:.03em}

    /* ── KPI Card Accents ── */
    .adm-kpi-accent{position:absolute;left:0;top:0;bottom:0;width:5px;border-radius:0 6px 6px 0}
    .adm-kpi-icon{width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.25rem;transition:transform .3s,box-shadow .3s}
    .adm-card:hover .adm-kpi-icon{transform:scale(1.1)}

    /* ── Floating Cards ── */
    .adm-float{animation:admCardFloat 6s ease-in-out infinite}
    .adm-float:nth-child(2){animation-delay:.8s}.adm-float:nth-child(3){animation-delay:1.6s}
    .adm-float:nth-child(4){animation-delay:2.4s}.adm-float:nth-child(5){animation-delay:3.2s}.adm-float:nth-child(6){animation-delay:4s}
    @keyframes admCardFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}

    /* ── Chart Glass Container ── */
    .adm-chart-glass{background:rgba(255,255,255,.65);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.4);border-radius:22px;box-shadow:0 8px 32px rgba(95,111,255,.08);position:relative;overflow:hidden;transition:transform .4s,box-shadow .4s}
    .adm-chart-glass::before{content:'';position:absolute;inset:0;border-radius:22px;padding:1px;background:linear-gradient(135deg,rgba(95,111,255,.15),rgba(194,77,255,.1),rgba(255,110,199,.08));-webkit-mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);-webkit-mask-composite:xor;mask-composite:exclude;pointer-events:none;opacity:0;transition:opacity .4s}
    .adm-chart-glass:hover::before{opacity:1}
    .adm-chart-glass:hover{transform:translateY(-2px);box-shadow:0 12px 40px rgba(95,111,255,.12)}

    /* ── Action Cards ── */
    .adm-action{border-radius:18px;background:rgba(255,255,255,.55);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.35);transition:transform .3s,box-shadow .3s;position:relative;overflow:hidden;display:block}
    .adm-action::before{content:'';position:absolute;inset:0;border-radius:18px;padding:1px;background:linear-gradient(135deg,rgba(95,111,255,.15),rgba(194,77,255,.1));-webkit-mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);-webkit-mask-composite:xor;mask-composite:exclude;pointer-events:none;opacity:0;transition:opacity .3s}
    .adm-action:hover::before{opacity:1}
    .adm-action:hover{transform:translateY(-6px);box-shadow:0 16px 48px rgba(95,111,255,.12)}
    .adm-action-icon{width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#5f6fff,#c24dff);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem;box-shadow:0 6px 18px rgba(139,92,246,.15);transition:transform .3s}
    .adm-action:hover .adm-action-icon{transform:scale(1.1)}
    .adm-action-pulse{animation:admPulse 1.8s infinite}

    /* ── Progress Bar ── */
    .adm-progress{height:6px;border-radius:99px;background:rgba(0,0,0,.06);overflow:hidden}
    .adm-progress-fill{height:100%;border-radius:99px;background:linear-gradient(90deg,#5f6fff,#c24dff,#ff6ec7);box-shadow:0 4px 12px rgba(124,58,237,.15);width:0%;transition:width 1.2s cubic-bezier(.4,0,.2,1)}

    /* ── Alert Severity Cards ── */
    .adm-sev-critical{border-left:4px solid #ef4444}
    .adm-sev-warning{border-left:4px solid #f59e0b}
    .adm-sev-info{border-left:4px solid #3b82f6}

    /* ── Slide-in Animation ── */
    @keyframes admSlideIn{from{opacity:0;transform:translateX(-30px)}to{opacity:1;transform:translateX(0)}}
    .adm-slide{opacity:0;transform:translateX(-30px)}.adm-slide.adm-v{animation:admSlideIn .5s cubic-bezier(.4,0,.2,1) forwards}

    /* ── Entrance Animations ── */
    @keyframes admUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
    .adm-a{opacity:0;transform:translateY(20px)}.adm-a.adm-v{animation:admUp .5s cubic-bezier(.4,0,.2,1) forwards}
    .adm-d0{animation-delay:0s!important}.adm-d1{animation-delay:.07s!important}.adm-d2{animation-delay:.14s!important}
    .adm-d3{animation-delay:.21s!important}.adm-d4{animation-delay:.28s!important}.adm-d5{animation-delay:.35s!important}

    /* ── Count Animations ── */
    @keyframes admCount{from{opacity:0;transform:scale(.8)}to{opacity:1;transform:scale(1)}}
    .adm-count-in{animation:admCount .4s cubic-bezier(.4,0,.2,1) forwards}
    .adm-count-num{font-variant-numeric:tabular-nums}

    /* ── Status Pulse ── */
    @keyframes admPulse{0%,100%{opacity:.5;transform:scale(1)}50%{opacity:1;transform:scale(1.3)}}
    .adm-pulse{animation:admPulse 2s ease-in-out infinite}
    @keyframes admStatusPulse{0%,100%{box-shadow:0 0 0 0 rgba(194,77,255,.4)}70%{box-shadow:0 0 0 8px rgba(194,77,255,0)}}
    .adm-status-ring{animation:admStatusPulse 2s ease-in-out infinite}

    /* ── Welcome Banner ── */
    .adm-banner{background:linear-gradient(135deg,#5f6fff,#c24dff,#ff6ec7);border-radius:22px;position:relative;overflow:hidden}
    .adm-banner::after{content:'';position:absolute;inset:0;background:radial-gradient(circle at 80% 20%,rgba(255,255,255,.15) 0%,transparent 60%);pointer-events:none}

    /* ── Scrollbar ── */
    ::-webkit-scrollbar{width:6px;height:6px}
    ::-webkit-scrollbar-track{background:transparent}
    ::-webkit-scrollbar-thumb{background:rgba(194,77,255,.15);border-radius:99px}
    ::-webkit-scrollbar-thumb:hover{background:rgba(194,77,255,.25)}
    </style>
    @stack('styles')
</head>
<body class="adm-bg">

<!-- Ambient floating particles -->
<div class="adm-particle adm-particle-1"></div>
<div class="adm-particle adm-particle-2"></div>
<div class="adm-particle adm-particle-3"></div>


<div x-data="{
        sidebarOpen: window.innerWidth >= 1024,
        unreadAlerts: 0,
        bellOpen: false,
        alertItems: [],
        alertsLoading: false,
        async fetchAlertCount() {
            try {
                const r = await fetch('/api/alerts/unread-count',{headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},credentials:'same-origin'});
                const d = await r.json();
                if(d.success) this.unreadAlerts = d.data?.unread_count ?? d.data?.count ?? 0;
            } catch(e){}
        },
        async fetchAlerts() {
            this.alertsLoading = true;
            try {
                const r = await fetch('/api/alerts',{headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},credentials:'same-origin'});
                const d = await r.json();
                if(d.success) this.alertItems = (d.data || []).slice(0, 20);
            } catch(e){}
            this.alertsLoading = false;
        },
        async markAlertRead(a) {
            try {
                const r = await fetch('/api/alerts/'+a.id+'/read',{method:'PATCH',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},credentials:'same-origin'});
                const d = await r.json();
                if(d.success){ a.is_read = true; this.unreadAlerts = Math.max(0, this.unreadAlerts - 1); }
            } catch(e){}
        },
        async markAllRead() {
            try {
                const r = await fetch('/api/alerts/read-all',{method:'PATCH',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},credentials:'same-origin'});
                const d = await r.json();
                if(d.success){
                    this.alertItems.forEach(a => a.is_read = true);
                    this.unreadAlerts = 0;
                }
            } catch(e){}
        },
        openBell() {
            this.bellOpen = !this.bellOpen;
            if(this.bellOpen) this.fetchAlerts();
        },
        sevIcon(s){ return s==='critical'?'⛔':s==='warning'?'⚠️':'ℹ️'; },
        sevColor(s){ return s==='critical'?'bg-red-100 text-red-600':s==='warning'?'bg-amber-100 text-amber-600':'bg-blue-100 text-blue-600'; },
        sevBorder(s){ return s==='critical'?'border-l-red-400':s==='warning'?'border-l-amber-400':'border-l-blue-400'; },
        timeAgo(d){
            const s=Math.floor((Date.now()-new Date(d))/1000);
            if(s<60)return 'just now'; if(s<3600)return Math.floor(s/60)+'m ago';
            if(s<86400)return Math.floor(s/3600)+'h ago'; return Math.floor(s/86400)+'d ago';
        }
    }"
     x-init="fetchAlertCount(); setInterval(()=>fetchAlertCount(), 5000)"
     class="flex min-h-screen">

    {{-- Overlay --}}
    <div x-show="sidebarOpen" @click="sidebarOpen=false"
         class="fixed inset-0 z-20 bg-black/40 backdrop-blur-sm lg:hidden" x-transition.opacity x-cloak></div>

    {{-- ── Admin Sidebar ── --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="adm-sidebar fixed inset-y-0 left-0 z-30 w-64 transform transition-transform duration-200 lg:translate-x-0 lg:static lg:inset-auto flex flex-col">

        {{-- Logo --}}
        <div class="flex items-center h-14 px-5 border-b border-white/10 shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white text-sm font-bold shadow-lg">🛡️</div>
                <span class="text-base font-bold text-white">Admin Panel</span>
            </a>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 p-3 space-y-1 overflow-y-auto relative z-10">
            @php $cur = request()->route()?->getName(); @endphp

            <p class="px-3 pt-2 pb-1 text-[10px] font-bold text-white/30 uppercase tracking-widest">Overview</p>
            <a href="{{ route('admin.dashboard') }}" class="adm-nav-item {{ str_starts_with($cur,'admin.dashboard') ? 'adm-nav-active' : '' }}">
                <span class="text-sm">📊</span> Dashboard</a>
            <a href="{{ route('admin.users') }}" class="adm-nav-item {{ str_starts_with($cur,'admin.users') ? 'adm-nav-active' : '' }}">
                <span class="text-sm">👥</span> User Monitoring</a>

            <p class="px-3 pt-4 pb-1 text-[10px] font-bold text-white/30 uppercase tracking-widest">Analytics</p>
            <a href="{{ route('admin.risk-analysis') }}" class="adm-nav-item {{ str_starts_with($cur,'admin.risk') ? 'adm-nav-active' : '' }}">
                <span class="text-sm">📈</span> Risk Analysis</a>
            <a href="{{ route('admin.simulations') }}" class="adm-nav-item {{ str_starts_with($cur,'admin.simulations') ? 'adm-nav-active' : '' }}">
                <span class="text-sm">⚡</span> Simulation Logs</a>
            <a href="{{ route('admin.reports') }}" class="adm-nav-item {{ str_starts_with($cur,'admin.reports') ? 'adm-nav-active' : '' }}">
                <span class="text-sm">📑</span> Reports</a>

            <p class="px-3 pt-4 pb-1 text-[10px] font-bold text-white/30 uppercase tracking-widest">System</p>
            <a href="{{ route('admin.alerts') }}" class="adm-nav-item {{ str_starts_with($cur,'admin.alerts') ? 'adm-nav-active' : '' }}">
                <span class="text-sm">🚨</span> Alert Oversight</a>
            <a href="{{ route('admin.rag') }}" class="adm-nav-item {{ str_starts_with($cur,'admin.rag') ? 'adm-nav-active' : '' }}">
                <span class="text-sm">📚</span> Knowledge Base</a>
        </nav>

        {{-- Admin user --}}
        <div class="p-4 border-t border-white/10 shrink-0 relative z-10">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center text-white text-xs font-bold">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-white/40 truncate">{{ Auth::user()->email }}</p>
                </div>
            </div>
        </div>
    </aside>

    {{-- ── Main ── --}}
    <div class="flex-1 flex flex-col min-w-0">
        {{-- Header --}}
        <header class="adm-header sticky top-0 z-10 flex items-center h-14 px-4 sm:px-6 shrink-0">
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 mr-2 rounded-lg text-gray-500 hover:bg-purple-50 hover:text-purple-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="text-sm font-bold text-gray-700">@yield('heading', 'Admin Dashboard')</h1>
            <div class="flex-1"></div>
            <div class="flex items-center gap-3">
                {{-- Bell icon with live alerts dropdown --}}
                <div class="relative">
                    <button @click="openBell()" @click.outside="bellOpen = false" class="relative p-2 rounded-xl text-gray-500 hover:text-purple-600 hover:bg-purple-50 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                        </svg>
                        <span x-show="unreadAlerts > 0" x-cloak
                              class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full shadow-sm animate-pulse"
                              x-text="unreadAlerts"></span>
                    </button>

                    <div x-show="bellOpen" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                         class="absolute right-0 mt-2 w-80 sm:w-96 bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-gray-200/60 overflow-hidden z-50">

                        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-purple-50/80 to-pink-50/80">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white text-xs">🔔</div>
                                <div>
                                    <p class="text-xs font-bold text-gray-800">Notifications</p>
                                    <p class="text-[10px] text-gray-400" x-text="unreadAlerts + ' unread'"></p>
                                </div>
                            </div>
                            <button x-show="unreadAlerts > 0" @click="markAllRead()" class="text-[10px] font-bold text-purple-600 hover:text-purple-800 uppercase tracking-wide transition-colors">Mark all read</button>
                        </div>

                        <div class="max-h-80 overflow-y-auto overscroll-contain" style="scrollbar-width:thin;scrollbar-color:#e5e7eb transparent">
                            <div x-show="alertsLoading" class="py-8 text-center">
                                <div class="inline-block w-5 h-5 border-2 border-purple-400 border-t-transparent rounded-full animate-spin"></div>
                                <p class="text-[10px] text-gray-400 mt-1.5">Loading…</p>
                            </div>
                            <div x-show="!alertsLoading && alertItems.length === 0" class="py-10 text-center">
                                <div class="text-3xl mb-1.5">🔕</div>
                                <p class="text-xs text-gray-400">No alerts yet</p>
                            </div>
                            <template x-for="a in alertItems" :key="a.id">
                                <div class="px-4 py-3 border-b border-gray-50 hover:bg-purple-50/40 transition-colors cursor-pointer border-l-[3px]"
                                     :class="!a.is_read ? sevBorder(a.severity) + ' bg-purple-50/20' : 'border-l-transparent'"
                                     @click="if(!a.is_read) markAlertRead(a)">
                                    <div class="flex items-start gap-2.5">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs flex-shrink-0 mt-0.5" :class="sevColor(a.severity)">
                                            <span x-text="sevIcon(a.severity)"></span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-0.5">
                                                <p class="text-xs text-gray-800 truncate" :class="!a.is_read ? 'font-bold' : 'font-medium'" x-text="a.title"></p>
                                                <span x-show="!a.is_read" class="w-2 h-2 rounded-full bg-purple-500 flex-shrink-0"></span>
                                            </div>
                                            <p class="text-[11px] text-gray-500 line-clamp-2" x-text="a.message"></p>
                                            <p class="text-[10px] text-gray-400 mt-1" x-text="timeAgo(a.created_at)"></p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open=!open" @click.outside="open=false" class="flex items-center gap-2 pl-1.5 pr-3 py-1.5 rounded-xl hover:bg-gray-100 transition">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white text-[10px] font-bold">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <span class="text-xs font-medium text-gray-600 hidden sm:inline">{{ Auth::user()->name }}</span>
                    </button>
                    <div x-show="open" x-cloak x-transition class="absolute right-0 mt-2 w-48 bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl border border-gray-200/60 overflow-hidden z-50">
                        <div class="px-4 py-2.5 border-b border-gray-100">
                            <p class="text-xs font-bold text-gray-800">{{ Auth::user()->name }}</p>
                            <p class="text-[10px] text-gray-400">{{ Auth::user()->email }}</p>
                        </div>
                        <div class="py-1">
                            <a href="{{ route('health-profile') }}" class="block px-4 py-2 text-xs text-gray-600 hover:bg-purple-50 hover:text-purple-700">Settings</a>
                        </div>
                        <div class="border-t border-gray-100 py-1">
                            <form method="POST" action="{{ route('logout') }}">@csrf
                                <button class="w-full text-left px-4 py-2 text-xs text-red-500 hover:bg-red-50">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="flex-1 p-4 sm:p-6 overflow-auto">
            @yield('content')
        </main>
    </div>
</div>

{{-- Toast --}}
<div x-data="toastManager()" @toast.window="add($event.detail)"
     class="fixed bottom-4 right-4 z-50 space-y-2 max-w-sm w-full pointer-events-none">
    <template x-for="t in toasts" :key="t.id">
        <div class="pointer-events-auto px-4 py-3 rounded-xl text-white shadow-lg flex items-center gap-2 backdrop-blur-md"
             :class="{'bg-emerald-500/90':t.type==='success','bg-red-500/90':t.type==='error','bg-amber-500/90':t.type==='warning','bg-blue-500/90':t.type==='info'}">
            <span class="flex-1 text-sm" x-text="t.message"></span>
            <button @click="remove(t.id)" class="opacity-70 hover:opacity-100">✕</button>
        </div>
    </template>
</div>

<script>
const api = {
    _headers() {
        return { 'Content-Type':'application/json','Accept':'application/json',
                 'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content,
                 'X-Requested-With':'XMLHttpRequest' };
    },
    async request(method, url, data = null) {
        const opts = { method, headers: this._headers(), credentials: 'same-origin' };
        if (data && method !== 'GET') opts.body = JSON.stringify(data);
        try {
            const r = await fetch('/api' + url, opts);
            const json = await r.json();
            if (r.status === 401 && !window.__authRedirecting) { window.__authRedirecting = true; window.location.href = '/login'; }
            return { ok: r.ok, status: r.status, ...json };
        } catch (e) { return { ok:false, success:false, message:'Network error' }; }
    },
    get:    (u)    => api.request('GET', u),
    post:   (u, d) => api.request('POST', u, d),
    put:    (u, d) => api.request('PUT', u, d),
    patch:  (u, d) => api.request('PATCH', u, d),
    delete: (u)    => api.request('DELETE', u),
};
function toastManager() {
    return {
        toasts: [], _id: 0,
        add(d) { const id=++this._id; this.toasts.push({id,message:d.message,type:d.type||'success'}); setTimeout(()=>this.remove(id),4000); },
        remove(id) { this.toasts=this.toasts.filter(t=>t.id!==id); }
    };
}
function toast(m,t='success'){window.dispatchEvent(new CustomEvent('toast',{detail:{message:m,type:t}}));}

/* Animate elements */
function admAnimate(){ document.querySelectorAll('[data-adm]').forEach(el=>el.classList.add('adm-v')); }
function admCountUp(el,target,dur){dur=dur||900;let s=null;const n=parseFloat(target)||0;const step=t=>{if(!s)s=t;const p=Math.min((t-s)/dur,1);el.textContent=Math.floor(p*n);if(p<1)requestAnimationFrame(step);else el.textContent=n;};requestAnimationFrame(step);}
</script>
@stack('scripts')
</body>
</html>
