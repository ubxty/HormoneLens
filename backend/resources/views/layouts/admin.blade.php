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
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen">

<div x-data="{ sidebarOpen: window.innerWidth >= 1024 }" class="flex min-h-screen">

    {{-- Overlay --}}
    <div x-show="sidebarOpen" @click="sidebarOpen=false"
         class="fixed inset-0 z-20 bg-black/50 lg:hidden" x-transition.opacity x-cloak></div>

    {{-- ── Admin Sidebar ── --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-30 w-64 bg-brand-900 transform transition-transform duration-200 lg:translate-x-0 lg:static lg:inset-auto flex flex-col">
        <div class="flex items-center h-16 px-6 border-b border-brand-800 shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold text-white">🛡️ Admin Panel</a>
        </div>
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
            @php $cur = request()->route()?->getName(); @endphp

            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg {{ str_starts_with($cur,'admin.dashboard') ? 'bg-brand-800 text-white':'text-brand-200 hover:bg-brand-800 hover:text-white' }}">
                📊 Dashboard</a>
            <a href="{{ route('admin.users') }}"
               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg {{ str_starts_with($cur,'admin.users') ? 'bg-brand-800 text-white':'text-brand-200 hover:bg-brand-800 hover:text-white' }}">
                👥 User Management</a>
            <a href="{{ route('admin.simulations') }}"
               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg {{ str_starts_with($cur,'admin.simulations') ? 'bg-brand-800 text-white':'text-brand-200 hover:bg-brand-800 hover:text-white' }}">
                ⚡ Simulation Logs</a>
            <a href="{{ route('admin.alerts') }}"
               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg {{ str_starts_with($cur,'admin.alerts') ? 'bg-brand-800 text-white':'text-brand-200 hover:bg-brand-800 hover:text-white' }}">
                🔔 Alert Oversight</a>
            <a href="{{ route('admin.reports') }}"
               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg {{ str_starts_with($cur,'admin.reports') ? 'bg-brand-800 text-white':'text-brand-200 hover:bg-brand-800 hover:text-white' }}">
                📈 Reports & Analytics</a>
            <a href="{{ route('admin.rag') }}"
               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg {{ str_starts_with($cur,'admin.rag') ? 'bg-brand-800 text-white':'text-brand-200 hover:bg-brand-800 hover:text-white' }}">
                📚 RAG Knowledge Base</a>

            <div class="pt-4 mt-4 border-t border-brand-800">
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg text-brand-300 hover:bg-brand-800 hover:text-white">
                    ← Back to User Panel</a>
            </div>
        </nav>
        <div class="p-4 border-t border-brand-800 shrink-0">
            <p class="text-xs text-brand-400">Logged in as</p>
            <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
        </div>
    </aside>

    {{-- ── Main ── --}}
    <div class="flex-1 flex flex-col min-w-0">
        <header class="sticky top-0 z-10 flex items-center h-16 px-4 sm:px-6 bg-white border-b border-gray-200 shrink-0">
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 mr-2 rounded-md text-gray-500 hover:bg-gray-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="text-lg font-semibold text-gray-800">@yield('heading', 'Admin Dashboard')</h1>
            <div class="flex-1"></div>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="text-sm text-gray-500 hover:text-red-600 font-medium">Logout</button>
            </form>
        </header>
        <main class="flex-1 p-4 sm:p-6 overflow-auto">
            @yield('content')
        </main>
    </div>
</div>

{{-- Toast --}}
<div x-data="toastManager()" @toast.window="add($event.detail)"
     class="fixed bottom-4 right-4 z-50 space-y-2 max-w-sm w-full pointer-events-none">
    <template x-for="t in toasts" :key="t.id">
        <div class="pointer-events-auto px-4 py-3 rounded-lg text-white shadow-lg flex items-center gap-2"
             :class="{'bg-emerald-500':t.type==='success','bg-red-500':t.type==='error','bg-amber-500':t.type==='warning','bg-blue-500':t.type==='info'}">
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
            if (r.status === 401) { window.location.href = '/login'; return json; }
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
</script>
@stack('scripts')
</body>
</html>
