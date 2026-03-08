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

<div x-data="{
        sidebarOpen: window.innerWidth >= 1024,
        unreadAlerts: 0,
        bellOpen: false,
        alertItems: [],
        alertsLoading: false,
        alertsLoaded: false,
        async fetchAlertCount() {
            try {
                const r = await fetch('/api/alerts/unread-count',{headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},credentials:'same-origin'});
                const d = await r.json();
                if(d.success) this.unreadAlerts = d.data?.unread_count ?? d.data?.count ?? 0;
            } catch(e){}
        },
        async fetchAlerts() {
            if(this.alertsLoaded) return;
            this.alertsLoading = true;
            try {
                const r = await fetch('/api/alerts',{headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},credentials:'same-origin'});
                const d = await r.json();
                if(d.success) this.alertItems = (d.data || []).slice(0, 20);
                this.alertsLoaded = true;
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
            if(this.bellOpen) { this.alertsLoaded = false; this.fetchAlerts(); }
        },
        sevIcon(s){ return s==='critical'?'⛔':s==='warning'?'⚠️':'ℹ️'; },
        sevColor(s){ return s==='critical'?'bg-red-100 text-red-600':s==='warning'?'bg-amber-100 text-amber-600':'bg-blue-100 text-blue-600'; },
        sevBorder(s){ return s==='critical'?'border-l-red-400':s==='warning'?'border-l-amber-400':'border-l-blue-400'; },
        timeAgo(d){
            const s = Math.floor((Date.now() - new Date(d))/1000);
            if(s < 60) return 'just now';
            if(s < 3600) return Math.floor(s/60)+'m ago';
            if(s < 86400) return Math.floor(s/3600)+'h ago';
            return Math.floor(s/86400)+'d ago';
        }
     }"
     x-init="fetchAlertCount(); setInterval(()=>fetchAlertCount(), 60000)"
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

            <a href="{{ route('health-profile') }}" data-tour-id="nav-health-profile"
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
            <a href="{{ route('simulations') }}" data-tour-id="nav-simulations"
               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg {{ $cur==='simulations' ? 'bg-brand-50 text-brand-700':'text-gray-600 hover:bg-gray-100' }}">
                ⚡ Simulations</a>
            <a href="{{ route('food-impact') }}"
               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg {{ $cur==='food-impact' ? 'bg-brand-50 text-brand-700':'text-gray-600 hover:bg-gray-100' }}">
                🍛 Food Impact</a>

            <p class="pt-3 pb-1 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Activity</p>
            <a href="{{ route('history') }}"
               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg {{ $cur==='history' ? 'bg-brand-50 text-brand-700':'text-gray-600 hover:bg-gray-100' }}">
                🕐 History</a>
            <a href="{{ route('knowledge') }}" data-tour-id="nav-knowledge"
               class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg {{ $cur==='knowledge' ? 'bg-brand-50 text-brand-700':'text-gray-600 hover:bg-gray-100' }}">
                📚 Knowledge Base</a>
        </nav>

        {{-- ── User section at bottom of sidebar ── --}}
        <div x-data="{ userMenuOpen: false }" class="border-t border-gray-100 p-3 shrink-0">
            <button @click="userMenuOpen = !userMenuOpen" @click.outside="userMenuOpen = false"
                    class="flex items-center gap-3 w-full px-3 py-2 rounded-xl hover:bg-gray-100 transition-all duration-200 text-left">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white text-xs font-bold shadow-sm shrink-0">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-700 truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[11px] text-gray-400 truncate">{{ Auth::user()->email }}</p>
                </div>
                <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200" :class="userMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                </svg>
            </button>

            {{-- Dropdown (opens upward) --}}
            <div x-show="userMenuOpen" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 class="mb-1 rounded-xl border border-gray-200/60 bg-white shadow-lg overflow-hidden">
                <a href="{{ route('health-profile') }}"
                   class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-purple-50 hover:text-purple-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                    Settings
                </a>
                <div class="border-t border-gray-100">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 hover:text-red-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/>
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    {{-- ── Main Content ── --}}
    <div class="flex-1 flex flex-col min-w-0">
        {{-- Top Bar --}}
        <header class="sticky top-0 z-10 flex items-center h-14 px-4 sm:px-6 bg-white/80 backdrop-blur-md border-b border-gray-200/60 shrink-0">
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 mr-2 rounded-md text-gray-500 hover:bg-gray-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="text-sm font-semibold text-gray-700">@yield('heading', 'Dashboard')</h1>
            <div class="flex-1"></div>

            <div class="flex items-center gap-3">
                {{-- Bell icon with live alerts dropdown --}}
                <div class="relative">
                    <button @click="openBell()" @click.outside="bellOpen = false" class="relative p-2 rounded-xl text-gray-500 hover:text-purple-600 hover:bg-purple-50 transition-all duration-200">
                        <svg class="w-5 h-5" :class="unreadAlerts > 0 ? 'animate-[bellShake_.6s_ease-in-out]' : ''" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                        </svg>
                        <span x-show="unreadAlerts > 0" x-cloak
                              class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full shadow-sm animate-pulse"
                              x-text="unreadAlerts"></span>
                    </button>

                    {{-- Alerts dropdown panel --}}
                    <div x-show="bellOpen" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                         class="absolute right-0 mt-2 w-80 sm:w-96 bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-gray-200/60 overflow-hidden z-50">

                        {{-- Header --}}
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

                        {{-- Alert list --}}
                        <div class="max-h-80 overflow-y-auto overscroll-contain" style="scrollbar-width:thin;scrollbar-color:#e5e7eb transparent">
                            {{-- Loading --}}
                            <div x-show="alertsLoading" class="py-8 text-center">
                                <div class="inline-block w-5 h-5 border-2 border-purple-400 border-t-transparent rounded-full animate-spin"></div>
                                <p class="text-[10px] text-gray-400 mt-1.5">Loading alerts…</p>
                            </div>

                            {{-- Empty --}}
                            <div x-show="!alertsLoading && alertItems.length === 0" class="py-10 text-center">
                                <div class="text-3xl mb-1.5">🔕</div>
                                <p class="text-xs text-gray-400">No alerts yet</p>
                            </div>

                            {{-- Items --}}
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
                                                <p class="text-xs font-semibold text-gray-800 truncate" :class="!a.is_read ? 'font-bold' : 'font-medium'" x-text="a.title"></p>
                                                <span x-show="!a.is_read" class="w-2 h-2 rounded-full bg-purple-500 flex-shrink-0"></span>
                                            </div>
                                            <p class="text-[11px] text-gray-500 line-clamp-2" x-text="a.message"></p>
                                            <p class="text-[10px] text-gray-400 mt-1" x-text="timeAgo(a.created_at)"></p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Footer --}}
                        <div class="px-4 py-2.5 border-t border-gray-100 bg-gray-50/50">
                            <a href="{{ route('alerts') }}" class="flex items-center justify-center gap-1.5 text-[11px] font-bold text-purple-600 hover:text-purple-800 transition-colors uppercase tracking-wide">
                                View all alerts
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Profile dropdown --}}
                <div x-data="{ profileOpen: false }" class="relative">
                    <button @click="profileOpen = !profileOpen" @click.outside="profileOpen = false"
                            class="flex items-center gap-2.5 pl-1.5 pr-3 py-1.5 rounded-xl hover:bg-gray-100 transition-all duration-200">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <span class="text-sm font-medium text-gray-700 hidden sm:inline">{{ Auth::user()->name }}</span>
                        <svg class="w-3.5 h-3.5 text-gray-400 hidden sm:block transition-transform duration-200" :class="profileOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                    </button>

                    <div x-show="profileOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95 -translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                         x-cloak class="absolute right-0 mt-2 w-52 bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl border border-gray-200/60 overflow-hidden z-50">
                        {{-- User info header --}}
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-xs font-bold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-[11px] text-gray-400 truncate">{{ Auth::user()->email }}</p>
                        </div>
                        {{-- Menu items --}}
                        <div class="py-1.5">
                            <a href="{{ route('health-profile') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-600 hover:bg-purple-50 hover:text-purple-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                Settings
                            </a>
                        </div>
                        {{-- Logout --}}
                        <div class="border-t border-gray-100 py-1.5">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-3 w-full px-4 py-2 text-sm text-red-500 hover:bg-red-50 hover:text-red-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                                    Logout
                                </button>
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
