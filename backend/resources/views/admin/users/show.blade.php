@extends('layouts.admin')
@section('heading','User Detail')

@section('content')
<div x-data="adminUserShow()" x-init="init()">

    {{-- Back link --}}
    <a href="{{ route('admin.users') }}" class="inline-flex items-center gap-1 text-xs font-bold text-purple-500 hover:text-purple-700 mb-4 transition adm-a adm-d0" data-adm>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Users
    </a>

    {{-- Loading --}}
    <div x-show="loading" class="text-center py-16">
        <div class="inline-block w-8 h-8 border-[3px] border-purple-400 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div x-show="!loading" class="space-y-5">

        {{-- User Header Card --}}
        <div class="adm-card relative p-6 adm-a adm-d0" data-adm>
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white text-xl font-bold shadow-lg"
                     x-text="(user.name||'?').charAt(0).toUpperCase()"></div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg font-bold text-gray-800" x-text="user.name"></h2>
                    <p class="text-xs text-gray-400" x-text="user.email"></p>
                    <div class="flex items-center gap-2 mt-1.5">
                        <span class="adm-badge" :class="user.is_admin ? 'bg-gradient-to-r from-purple-100 to-pink-100 text-purple-700' : 'bg-gray-100 text-gray-500'"
                              x-text="user.is_admin ? 'Admin' : 'User'"></span>
                        <span class="text-[10px] text-gray-400" x-text="'Joined ' + new Date(user.created_at).toLocaleDateString()"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Health Profile + Digital Twin --}}
        <div class="grid lg:grid-cols-2 gap-5">

            {{-- Health Profile --}}
            <div class="adm-card relative p-5 adm-a adm-d1" data-adm>
                <h3 class="text-xs font-bold text-gray-700 mb-4 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-emerald-400/20 to-teal-400/20 flex items-center justify-center text-xs">&#128154;</div>
                    Health Profile
                </h3>
                <div x-show="!profile" class="text-xs text-gray-400 text-center py-6">No health profile recorded yet.</div>
                <div x-show="profile" class="grid grid-cols-2 gap-3">
                    <div class="bg-white/40 rounded-xl p-3">
                        <p class="text-[10px] font-bold text-gray-400 uppercase">Weight</p>
                        <p class="text-sm font-bold text-gray-700" x-text="(profile?.weight || '&#8212;') + ' kg'"></p>
                    </div>
                    <div class="bg-white/40 rounded-xl p-3">
                        <p class="text-[10px] font-bold text-gray-400 uppercase">Height</p>
                        <p class="text-sm font-bold text-gray-700" x-text="(profile?.height || '&#8212;') + ' cm'"></p>
                    </div>
                    <div class="bg-white/40 rounded-xl p-3">
                        <p class="text-[10px] font-bold text-gray-400 uppercase">Avg Sleep</p>
                        <p class="text-sm font-bold text-gray-700" x-text="(profile?.avg_sleep_hours || '&#8212;') + ' hrs'"></p>
                    </div>
                    <div class="bg-white/40 rounded-xl p-3">
                        <p class="text-[10px] font-bold text-gray-400 uppercase">Water Intake</p>
                        <p class="text-sm font-bold text-gray-700" x-text="(profile?.water_intake || '&#8212;') + ' L'"></p>
                    </div>
                    <div class="bg-white/40 rounded-xl p-3">
                        <p class="text-[10px] font-bold text-gray-400 uppercase">Stress</p>
                        <p class="text-sm font-bold text-gray-700" x-text="profile?.stress_level || '&#8212;'"></p>
                    </div>
                    <div class="bg-white/40 rounded-xl p-3">
                        <p class="text-[10px] font-bold text-gray-400 uppercase">Activity</p>
                        <p class="text-sm font-bold text-gray-700" x-text="profile?.physical_activity || '&#8212;'"></p>
                    </div>
                    <div class="col-span-2 bg-white/40 rounded-xl p-3">
                        <p class="text-[10px] font-bold text-gray-400 uppercase">Disease Type</p>
                        <p class="text-sm font-bold text-gray-700" x-text="profile?.disease_type || '&#8212;'"></p>
                    </div>
                </div>
            </div>

            {{-- Digital Twin --}}
            <div class="adm-card relative p-5 adm-a adm-d2" data-adm>
                <h3 class="text-xs font-bold text-gray-700 mb-4 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-purple-400/20 to-pink-400/20 flex items-center justify-center text-xs">&#129302;</div>
                    Digital Twin Scores
                </h3>
                <div x-show="!twin" class="text-xs text-gray-400 text-center py-6">No digital twin data available.</div>
                <div x-show="twin" class="space-y-3">
                    <div class="flex items-center justify-between bg-white/40 rounded-xl p-3">
                        <span class="text-xs font-bold text-gray-600">Overall Risk</span>
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-extrabold" :class="parseFloat(twin?.overall_risk_score)>=7?'text-red-600':parseFloat(twin?.overall_risk_score)>=4?'text-amber-600':'text-emerald-600'"
                                  x-text="twin?.overall_risk_score || '&#8212;'"></span>
                            <span class="adm-badge" :class="{'bg-red-100 text-red-700': twin?.risk_category==='high' || twin?.risk_category==='critical', 'bg-amber-100 text-amber-700': twin?.risk_category==='moderate', 'bg-emerald-100 text-emerald-700': twin?.risk_category==='low'}"
                                  x-text="twin?.risk_category || '?'"></span>
                        </div>
                    </div>
                    <template x-for="[label, key, icon] in [['Metabolic Health','metabolic_health_score','&#128170;'],['Insulin Resistance','insulin_resistance_score','&#128137;'],['Sleep Score','sleep_score','&#128164;'],['Stress Score','stress_score','&#129504;'],['Diet Score','diet_score','&#127822;']]" :key="key">
                        <div class="bg-white/40 rounded-xl p-3">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[10px] font-bold text-gray-500 flex items-center gap-1"><span x-html="icon"></span> <span x-text="label"></span></span>
                                <span class="text-xs font-bold text-gray-700" x-text="twin?.[key] ?? '&#8212;'"></span>
                            </div>
                            <div class="adm-progress"><div class="adm-progress-fill" :style="'width:' + Math.min((parseFloat(twin?.[key])||0)*10, 100) + '%'"></div></div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Recent Simulations --}}
        <div class="adm-card relative p-5 adm-a adm-d3" data-adm>
            <h3 class="text-xs font-bold text-gray-700 mb-4 flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-blue-400/20 to-purple-400/20 flex items-center justify-center text-xs">&#9889;</div>
                Recent Simulations
            </h3>
            <div x-show="sims.length === 0" class="text-xs text-gray-400 text-center py-6">No simulations yet.</div>
            <div x-show="sims.length > 0" class="space-y-2">
                <template x-for="s in sims" :key="s.id">
                    <div class="flex items-center gap-3 bg-white/40 rounded-xl p-3 transition hover:bg-white/60">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-400/20 to-pink-400/20 flex items-center justify-center text-xs shrink-0">&#9889;</div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-gray-700 capitalize" x-text="s.type?.replace('_',' ') || 'Simulation'"></p>
                            <p class="text-[10px] text-gray-400 truncate" x-text="s.input_data?.description || 'No description'"></p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-xs font-bold" :class="parseFloat(s.risk_change) > 0 ? 'text-red-500' : 'text-emerald-500'" x-text="(parseFloat(s.risk_change) > 0 ? '+' : '') + s.risk_change"></span>
                            <p class="text-[10px] text-gray-400" x-text="new Date(s.created_at).toLocaleDateString()"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function adminUserShow() {
    const userId = @json($id);
    return {
        loading: true, user: {}, profile: null, twin: null, sims: [],
        async init() {
            const r = await api.get('/admin/users/' + userId);
            if (r.success) {
                this.user = r.data.user || r.data;
                this.profile = r.data.health_profile || r.data.user?.health_profile || null;
                this.twin = r.data.active_digital_twin || r.data.user?.active_digital_twin || null;
                this.sims = r.data.simulations || r.data.user?.simulations || [];
            }
            this.loading = false;
            this.$nextTick(() => admAnimate());
        }
    };
}
</script>
@endpush
