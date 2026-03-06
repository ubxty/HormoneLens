@extends('layouts.app')
@section('title','Simulation Dashboard — HormoneLens')
@section('heading','Predictive Metabolic Simulation')

@push('styles')
<style>
/* ── Dashboard layout overrides so React panel fills viewport ── */
.dash-bg {
    background: linear-gradient(135deg, rgba(95,111,255,0.06) 0%, rgba(194,77,255,0.06) 50%, rgba(255,110,199,0.06) 100%);
    min-height: 100%;
    position: relative;
    overflow: visible !important;
}
#twin-root {
    min-height: calc(100vh - 56px);
}
/* Loading spinner until React mounts */
#twin-root:empty {
    display: flex;
    align-items: center;
    justify-content: center;
}
#twin-root:empty::after {
    content: '';
    width: 40px;
    height: 40px;
    border: 3px solid rgba(124,58,237,.25);
    border-top-color: #7c3aed;
    border-radius: 50%;
    animation: dashSpin .8s linear infinite;
}
@keyframes dashSpin { to { transform: rotate(360deg); } }
</style>
@endpush

@section('content')
<div id="twin-root" class="-m-4 sm:-m-6" style="min-height:calc(100vh - 56px)"></div>
<div id="dashboard-tour-root" data-user-id="{{ Auth::id() }}"></div>
@endsection

@push('scripts')
@viteReactRefresh
@vite('resources/js/dashboard-twin.jsx')
@endpush
