@extends('layouts.app')
@section('title','Dashboard — HormoneLens')
@section('heading','Health Dashboard')

@push('styles')
<style>
/* ── Dashboard layout ── */
.dash-bg {
    background: linear-gradient(135deg, rgba(95,111,255,0.06) 0%, rgba(194,77,255,0.06) 50%, rgba(255,110,199,0.06) 100%);
    min-height: 100%;
}

/* Twin card height */
#twin-root {
    height: calc(100vh - 180px);
    min-height: 600px;
}
@media (max-width: 899px) {
    #twin-root {
        height: auto;
        min-height: 580px;
    }
}

/* Loading spinner until React mounts */
#twin-root:empty {
    display: flex;
    align-items: center;
    justify-content: center;
}
#twin-root:empty::after {
    content: '';
    width: 36px;
    height: 36px;
    border: 3px solid rgba(124,58,237,.18);
    border-top-color: #7c3aed;
    border-radius: 50%;
    animation: dashSpin .8s linear infinite;
}
@keyframes dashSpin { to { transform: rotate(360deg); } }
</style>
@endpush

@section('content')

{{-- ══════════════════════════════════════════════
     Page Header
══════════════════════════════════════════════ --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span class="text-[10px] font-bold uppercase tracking-widest text-purple-500">Live Dashboard</span>
        </div>
        <h1 class="text-xl font-extrabold bg-gradient-to-r from-violet-600 to-pink-500 bg-clip-text text-transparent leading-tight">
            My Health Overview
        </h1>
    </div>
    <div class="flex items-center gap-2">
        <a href="/simulations"
           class="flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-xl text-xs font-bold hover:-translate-y-0.5 transition-transform shadow-md shadow-violet-500/20">
            🔬 <span class="hidden sm:inline">Simulate</span>
        </a>
        <a href="/health-profile"
           class="flex items-center gap-1.5 px-4 py-2 bg-white/60 backdrop-blur-xl border border-white/40 text-violet-700 rounded-xl text-xs font-bold hover:-translate-y-0.5 transition-transform">
            ✏️ <span class="hidden sm:inline">Profile</span>
        </a>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     Full-Width Digital Twin
══════════════════════════════════════════════ --}}
<div class="space-y-3">

        {{-- Twin Card --}}
        <div style="
            background: linear-gradient(135deg, rgba(109,40,217,.07) 0%, rgba(139,92,246,.05) 50%, rgba(236,72,153,.05) 100%);
            border-radius: 20px;
            border: 1px solid rgba(139,92,246,.18);
            overflow: hidden;
            box-shadow: 0 8px 40px rgba(109,40,217,.10), 0 2px 8px rgba(0,0,0,.04);
        ">
            <div id="twin-root"></div>
        </div>

</div>

<div id="dashboard-tour-root" data-user-id="{{ Auth::id() }}"></div>

@endsection

@push('scripts')
@viteReactRefresh
@vite('resources/js/dashboard-twin.jsx')


@endpush
