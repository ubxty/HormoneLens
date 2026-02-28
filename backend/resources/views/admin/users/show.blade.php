@extends('layouts.admin')
@section('heading','User Details')

@section('content')
<div x-data="adminUserShow()" x-init="init()">

    <a href="{{ route('admin.users') }}" class="inline-flex items-center gap-1 text-xs font-bold text-purple-400 hover:text-purple-300 mb-4 transition adm-a adm-d0" data-adm>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Users
    </a>

    <div x-show="loading" class="text-center py-16">
        <div class="inline-block w-8 h-8 border-[3px] border-purple-400 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div x-show="!loading && user" class="space-y-4">
        {{-- Header --}}
        <div class="adm-card p-5 flex flex-col sm:flex-row items-start sm:items-center gap-4 adm-a adm-d0" data-adm>
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-400/20 to-pink-400/20 flex items-center justify-center text-2xl font-bold adm-grad-text"
                 x-text="user?.name?.charAt(0).toUpperCase()"></div>
            <div class="flex-1 min-w-0">
                <h2 class="text-lg font-bold text-gray-800 truncate" x-text="user?.name"></h2>
                <p class="text-xs text-gray-400" x-text="user?.email"></p>
                <p class="text-[10px] text-gray-300 mt-0.5" x-text="'Joined: ' + new Date(user?.created_at).toLocaleDateString()"></p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="adm-badge"
                      :class="user?.is_admin ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-500'"
                      x-text="user?.is_admin ? '🛡️ Admin' : '👤 User'"></span>
                <button @click="toggleAdmin()" :disabled="toggling"
                        class="adm-badge cursor-pointer transition disabled:opacity-50"
                        :class="user?.is_admin ? 'bg-red-100 hover:bg-red-200 text-red-700' : 'bg-emerald-100 hover:bg-emerald-200 text-emerald-700'"
                        x-text="user?.is_admin ? 'Remove Admin' : 'Make Admin'"></button>
            </div>
        </div>

        {{-- Health Profile --}}
        <div class="adm-card p-5 adm-a adm-d1" data-adm>
            <h3 class="text-xs font-bold text-gray-700 mb-3 flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-emerald-400/20 to-green-400/20 flex items-center justify-center text-xs">💚</div>
                Health Profile
            </h3>
            <div x-show="!profile" class="text-xs text-gray-400">Not filled yet.</div>
            <div x-show="profile" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <template x-for="item in [
                    {label:'Weight', val:(profile?.weight||'—')+' kg', icon:'⚖️'},
                    {label:'Height', val:(profile?.height||'—')+' cm', icon:'📏'},
                    {label:'Sleep',  val:(profile?.avg_sleep_hours||'—')+' hrs', icon:'😴'},
                    {label:'Water',  val:(profile?.water_intake||'—')+' L', icon:'💧'},
                    {label:'Stress', val:profile?.stress_level||'—', icon:'😰'},
                    {label:'Activity', val:profile?.physical_activity||'—', icon:'🏃'},
                    {label:'Disease', val:profile?.disease_type||'—', icon:'🩺'},
                    {label:'BMI', val:(profile?.weight&&profile?.height?(profile.weight/((profile.height/100)**2)).toFixed(1):'—'), icon:'📊'}
                ]" :key="item.label">
                    <div class="bg-white/40 rounded-xl p-3 text-center">
                        <span class="text-lg" x-text="item.icon"></span>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mt-1" x-text="item.label"></p>
                        <p class="text-sm font-bold text-gray-700 capitalize" x-text="item.val"></p>
                    </div>
                </template>
            </div>
        </div>

        {{-- Digital Twin --}}
        <div class="adm-card p-5 adm-a adm-d2" data-adm>
            <h3 class="text-xs font-bold text-gray-700 mb-3 flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-purple-400/20 to-blue-400/20 flex items-center justify-center text-xs">🧬</div>
                Digital Twin
            </h3>
            <div x-show="!twin" class="text-xs text-gray-400">Not generated yet.</div>
            <div x-show="twin" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <div class="rounded-xl p-3 text-center" :class="twin?.risk_category==='high'?'bg-red-50/80':'bg-white/40'">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Overall Risk</span>
                    <p class="text-xl font-bold adm-grad-text" x-text="twin?.overall_risk_score?.toFixed(1)"></p>
                    <span class="adm-badge mt-1" :class="twin?.risk_category==='high'?'bg-red-100 text-red-700':twin?.risk_category==='medium'?'bg-amber-100 text-amber-700':'bg-emerald-100 text-emerald-700'" x-text="twin?.risk_category"></span>
                </div>
                <template x-for="item in [
                    {label:'Metabolic', key:'metabolic_health_score', color:'text-indigo-600'},
                    {label:'Insulin Res.', key:'insulin_resistance_score', color:'text-purple-600'},
                    {label:'Sleep', key:'sleep_score', color:'text-blue-600'},
                    {label:'Stress', key:'stress_score', color:'text-amber-600'},
                    {label:'Diet', key:'diet_score', color:'text-emerald-600'}
                ]" :key="item.key">
                    <div class="bg-white/40 rounded-xl p-3 text-center">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider" x-text="item.label"></span>
                        <p class="text-xl font-bold" :class="item.color" x-text="twin?.[item.key]?.toFixed(1)"></p>
                    </div>
                </template>
            </div>
        </div>

        {{-- Recent Simulations --}}
        <div class="adm-card p-5 adm-a adm-d3" data-adm>
            <h3 class="text-xs font-bold text-gray-700 mb-3 flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-amber-400/20 to-orange-400/20 flex items-center justify-center text-xs">⚡</div>
                Recent Simulations
            </h3>
            <div x-show="sims.length === 0" class="text-xs text-gray-400">No simulations.</div>
            <div class="space-y-2">
                <template x-for="s in sims" :key="s.id">
                    <div class="flex items-center gap-3 p-3 bg-white/40 rounded-xl text-xs">
                        <span class="text-lg" x-text="s.type==='meal'?'🍽️':s.type==='sleep'?'😴':'😰'"></span>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold truncate text-gray-700" x-text="s.input_data?.description || s.type"></p>
                            <p class="text-[10px] text-gray-400" x-text="new Date(s.created_at).toLocaleString()"></p>
                        </div>
                        <span class="font-bold text-sm" :class="s.risk_change > 0 ? 'text-red-500' : 'text-emerald-500'"
                              x-text="(s.risk_change>0?'+':'')+s.risk_change.toFixed(2)"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <div x-show="!loading && !user" class="adm-card p-10 text-center text-xs text-gray-400">User not found.</div>
</div>
@endsection

@push('scripts')
<script>
function adminUserShow() {
    const userId = @json($id);
    return {
        loading: true, user: null, profile: null, twin: null, sims: [], toggling: false,
        async init() {
            const r = await api.get('/admin/users/' + userId);
            if(r.success && r.data) {
                this.user = r.data;
                this.profile = r.data.health_profile || null;
                this.twin = r.data.active_digital_twin || null;
                this.sims = r.data.simulations || [];
            }
            this.loading = false;
            this.$nextTick(() => admAnimate());
        },
        async toggleAdmin() {
            this.toggling = true;
            const r = await api.patch('/admin/users/' + userId + '/toggle-admin');
            if(r.success) { this.user.is_admin = !this.user.is_admin; toast('Admin status toggled'); }
            else toast(r.message || 'Failed', 'error');
            this.toggling = false;
        }
    };
}
</script>
@endpush
