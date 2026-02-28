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
    /* ── Admin Glassmorphism System ── */
    .adm-sidebar{background:linear-gradient(180deg,#1e1b4b 0%,#312e81 40%,#3b1f6e 100%);position:relative;overflow:hidden}
    .adm-sidebar::before{content:'';position:absolute;top:-40%;right:-30%;width:200px;height:200px;background:radial-gradient(circle,rgba(194,77,255,.15),transparent 70%);border-radius:50%}
    .adm-sidebar::after{content:'';position:absolute;bottom:-20%;left:-20%;width:160px;height:160px;background:radial-gradient(circle,rgba(95,111,255,.12),transparent 70%);border-radius:50%}
    .adm-nav-item{display:flex;align-items:center;gap:.75rem;padding:.55rem .85rem;font-size:.8125rem;font-weight:500;border-radius:12px;color:rgba(255,255,255,.55);transition:all .25s ease;position:relative;z-index:1}
    .adm-nav-item:hover{color:rgba(255,255,255,.9);background:rgba(255,255,255,.07)}
    .adm-nav-active{color:#fff!important;background:linear-gradient(135deg,rgba(95,111,255,.35),rgba(194,77,255,.25))!important;box-shadow:0 4px 16px rgba(95,111,255,.2)}
    .adm-nav-active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:60%;background:linear-gradient(180deg,#5f6fff,#c24dff);border-radius:0 4px 4px 0}
    .adm-header{background:rgba(255,255,255,.75);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-bottom:1px solid rgba(255,255,255,.5)}
    .adm-bg{background:linear-gradient(135deg,rgba(95,111,255,.04),rgba(194,77,255,.04) 50%,rgba(255,110,199,.04));min-height:100vh}
    .adm-card{background:rgba(255,255,255,.6);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.4);border-radius:16px;box-shadow:0 4px 24px rgba(95,111,255,.06);transition:transform .3s ease,box-shadow .3s ease}
    .adm-card:hover{transform:translateY(-2px);box-shadow:0 8px 32px rgba(95,111,255,.1)}
    .adm-card::before{content:'';position:absolute;inset:0;border-radius:16px;padding:1.5px;background:linear-gradient(135deg,rgba(95,111,255,.2),rgba(194,77,255,.15),rgba(255,110,199,.1));-webkit-mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);-webkit-mask-composite:xor;mask-composite:exclude;pointer-events:none;opacity:0;transition:opacity .3s ease}
    .adm-card:hover::before{opacity:1}
    .adm-grad-text{background:linear-gradient(135deg,#5f6fff,#c24dff,#ff6ec7);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
    .adm-input{background:rgba(255,255,255,.55);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.35);border-radius:12px;padding:.5rem .75rem;font-size:.8125rem;color:#374151;outline:none;transition:border-color .3s,box-shadow .3s;width:100%}
    .adm-input:focus{border-color:rgba(194,77,255,.3);box-shadow:0 0 0 3px rgba(194,77,255,.08)}
    .adm-btn{background:linear-gradient(135deg,#5f6fff,#c24dff);color:#fff;border:none;border-radius:12px;padding:.5rem 1.25rem;font-size:.8125rem;font-weight:700;cursor:pointer;transition:all .3s ease}
    .adm-btn:hover{filter:brightness(1.08);box-shadow:0 4px 20px rgba(95,111,255,.3)}
    .adm-btn:disabled{opacity:.5;cursor:not-allowed}
    .adm-table{width:100%;font-size:.8125rem}
    .adm-table thead{background:rgba(95,111,255,.04)}
    .adm-table th{padding:.65rem .85rem;text-align:left;font-weight:600;color:#6b7280;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid rgba(0,0,0,.05)}
    .adm-table td{padding:.65rem .85rem;border-bottom:1px solid rgba(0,0,0,.03);color:#374151}
    .adm-table tbody tr{transition:background .2s}
    .adm-table tbody tr:hover{background:rgba(194,77,255,.03)}
    @keyframes admUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
    .adm-a{opacity:0;transform:translateY(20px)}.adm-a.adm-v{animation:admUp .5s cubic-bezier(.4,0,.2,1) forwards}
    .adm-d0{animation-delay:0s!important}.adm-d1{animation-delay:.05s!important}.adm-d2{animation-delay:.1s!important}.adm-d3{animation-delay:.15s!important}.adm-d4{animation-delay:.2s!important}.adm-d5{animation-delay:.25s!important}
    @keyframes admCount{from{opacity:0;transform:scale(.8)}to{opacity:1;transform:scale(1)}}
    .adm-count-in{animation:admCount .4s cubic-bezier(.4,0,.2,1) forwards}
    @keyframes admPulse{0%,100%{opacity:.5;transform:scale(1)}50%{opacity:1;transform:scale(1.3)}}
    .adm-pulse{animation:admPulse 2s ease-in-out infinite}
    .adm-badge{display:inline-flex;align-items:center;padding:2px 10px;font-size:10px;font-weight:700;border-radius:20px;text-transform:uppercase;letter-spacing:.03em}
    </style>
    @stack('styles')
</head>
<body class="adm-bg">

<div x-data="{ sidebarOpen: window.innerWidth >= 1024 }" class="flex min-h-screen">

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
                <span class="text-sm">📚</span> RAG Knowledge Base</a>

            <div class="pt-4 mt-4 border-t border-white/10">
                <a href="{{ route('dashboard') }}" class="adm-nav-item text-purple-300/70 hover:text-white">
                    <span class="text-sm">←</span> Back to User Panel</a>
            </div>
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
                <a href="{{ route('admin.alerts') }}" class="relative p-2 rounded-xl text-gray-500 hover:text-purple-600 hover:bg-purple-50 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                    </svg>
                </a>
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
                            <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-xs text-gray-600 hover:bg-purple-50 hover:text-purple-700">User Dashboard</a>
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
</script>
@stack('scripts')
</body>
</html>
