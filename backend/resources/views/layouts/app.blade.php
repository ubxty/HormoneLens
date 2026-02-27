<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HormoneLens')</title>
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
<body class="bg-gray-50 min-h-screen">

<div x-data="{ sidebarOpen: window.innerWidth >= 1024, unreadAlerts: 0 }"
     x-init="fetch('/api/alerts/unread-count',{headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},credentials:'same-origin'}).then(r=>r.json()).then(d=>{ if(d.success) unreadAlerts=d.data.unread_count }).catch(()=>{})"
     class="flex min-h-screen">

    {{-- ── Sidebar Overlay (mobile) ── --}}
    <div x-show="sidebarOpen" @click="sidebarOpen=false"
         class="fixed inset-0 z-20 bg-black/40 lg:hidden" x-transition.opacity x-cloak></div>

    {{-- ── Sidebar ── --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-30 w-64 bg-white border-r border-gray-200 transform transition-transform duration-200 lg:translate-x-0 lg:static lg:inset-auto flex flex-col">
        <div class="flex items-center h-16 px-6 border-b border-gray-100 shrink-0">
            <a href="{{ route('dashboard') }}" class="text-xl font-bold text-brand-600">🔬 HormoneLens</a>
        </div>
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
            @php $cur = request()->route()?->getName(); @endphp

            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg {{ $cur==='dashboard' ? 'bg-brand-50 text-brand-700':'text-gray-600 hover:bg-gray-100' }}">
                📊 Dashboard</a>

            <a href="{{ route('health-profile') }}"
               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg {{ $cur==='health-profile' ? 'bg-brand-50 text-brand-700':'text-gray-600 hover:bg-gray-100' }}">
                👤 Health Profile</a>

            <p class="pt-3 pb-1 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Disease Data</p>
            @php $diseases = \App\Models\Disease::active()->ordered()->get(); @endphp
            @foreach($diseases as $d)
            <a href="{{ route('disease.show', $d->slug) }}"
               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg {{ request()->is('disease/'.$d->slug) ? 'bg-brand-50 text-brand-700':'text-gray-600 hover:bg-gray-100' }}">
                {{ $d->icon }} {{ $d->name }}</a>
            @endforeach

            <p class="pt-3 pb-1 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Analytics</p>
            <a href="{{ route('digital-twin') }}"
               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg {{ $cur==='digital-twin' ? 'bg-brand-50 text-brand-700':'text-gray-600 hover:bg-gray-100' }}">
                🧪 Digital Twin</a>
            <a href="{{ route('simulations') }}"
               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg {{ $cur==='simulations' ? 'bg-brand-50 text-brand-700':'text-gray-600 hover:bg-gray-100' }}">
                ⚡ Simulations</a>
            <a href="{{ route('food-impact') }}"
               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg {{ $cur==='food-impact' ? 'bg-brand-50 text-brand-700':'text-gray-600 hover:bg-gray-100' }}">
                🍛 Food Impact</a>

            <p class="pt-3 pb-1 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Activity</p>
            <a href="{{ route('alerts') }}"
               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg {{ $cur==='alerts' ? 'bg-brand-50 text-brand-700':'text-gray-600 hover:bg-gray-100' }}">
                🔔 Alerts
                <span x-show="unreadAlerts>0" x-text="unreadAlerts"
                      class="ml-auto inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full"></span>
            </a>
            <a href="{{ route('history') }}"
               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg {{ $cur==='history' ? 'bg-brand-50 text-brand-700':'text-gray-600 hover:bg-gray-100' }}">
                🕐 History</a>
            <a href="{{ route('knowledge') }}"
               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg {{ $cur==='knowledge' ? 'bg-brand-50 text-brand-700':'text-gray-600 hover:bg-gray-100' }}">
                📚 Knowledge Base</a>

            @if(Auth::user()->is_admin)
            <p class="pt-3 pb-1 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Admin</p>
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg text-brand-600 hover:bg-brand-50">
                🛡️ Admin Panel</a>
            @endif
        </nav>
    </aside>

    {{-- ── Main Content ── --}}
    <div class="flex-1 flex flex-col min-w-0">
        {{-- Top Bar --}}
        <header class="sticky top-0 z-10 flex items-center h-16 px-4 sm:px-6 bg-white border-b border-gray-200 shrink-0">
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 mr-2 rounded-md text-gray-500 hover:bg-gray-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="text-lg font-semibold text-gray-800">@yield('heading', 'Dashboard')</h1>
            <div class="flex-1"></div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600 hidden sm:inline">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-red-600 font-medium">Logout</button>
                </form>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="flex-1 p-4 sm:p-6 overflow-auto">
            @yield('content')
        </main>
    </div>
</div>

{{-- ── Toast Notifications ── --}}
<div x-data="toastManager()" @toast.window="add($event.detail)"
     class="fixed bottom-4 right-4 z-50 space-y-2 max-w-sm w-full pointer-events-none">
    <template x-for="t in toasts" :key="t.id">
        <div class="pointer-events-auto px-4 py-3 rounded-lg text-white shadow-lg flex items-center gap-2 transition-all"
             :class="{'bg-emerald-500':t.type==='success','bg-red-500':t.type==='error','bg-amber-500':t.type==='warning','bg-blue-500':t.type==='info'}">
            <span class="flex-1 text-sm" x-text="t.message"></span>
            <button @click="remove(t.id)" class="opacity-70 hover:opacity-100 shrink-0">✕</button>
        </div>
    </template>
</div>

<script>
/* ── API Helper ────────────────────── */
const api = {
    _headers() {
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            'X-Requested-With': 'XMLHttpRequest',
        };
    },
    async request(method, url, data = null) {
        const opts = { method, headers: this._headers(), credentials: 'same-origin' };
        if (data && method !== 'GET') opts.body = JSON.stringify(data);
        try {
            const r = await fetch('/api' + url, opts);
            const json = await r.json();
            if (r.status === 401 && !window.__authRedirecting) { window.__authRedirecting = true; window.location.href = '/login'; }
            return { ok: r.ok, status: r.status, ...json };
        } catch (e) {
            return { ok: false, success: false, message: 'Network error' };
        }
    },
    get:    (u)    => api.request('GET', u),
    post:   (u, d) => api.request('POST', u, d),
    put:    (u, d) => api.request('PUT', u, d),
    patch:  (u, d) => api.request('PATCH', u, d),
    delete: (u)    => api.request('DELETE', u),
};

/* ── Toast Manager ─────────────────── */
function toastManager() {
    return {
        toasts: [],
        _id: 0,
        add(detail) {
            const id = ++this._id;
            this.toasts.push({ id, message: detail.message, type: detail.type || 'success' });
            setTimeout(() => this.remove(id), 4000);
        },
        remove(id) { this.toasts = this.toasts.filter(t => t.id !== id); }
    };
}
function toast(message, type = 'success') {
    window.dispatchEvent(new CustomEvent('toast', { detail: { message, type } }));
}
</script>
@stack('scripts')

</body>
</html>
