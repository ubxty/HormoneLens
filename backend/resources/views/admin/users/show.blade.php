@extends('layouts.admin')
@section('heading','User Details')

@section('content')
<div x-data="adminUserShow()" x-init="init()">

    <a href="{{ route('admin.users') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-brand-600 mb-4">← Back to Users</a>

    <div x-show="loading" class="text-center py-16"><div class="animate-spin w-8 h-8 border-4 border-brand-500 border-t-transparent rounded-full mx-auto"></div></div>

    <div x-show="!loading && user" class="space-y-6">
        {{-- Header --}}
        <div class="bg-white rounded-xl shadow-sm border p-6 flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-brand-100 flex items-center justify-center text-brand-600 font-bold text-2xl"
                 x-text="user?.name?.charAt(0).toUpperCase()"></div>
            <div class="flex-1">
                <h2 class="text-xl font-bold text-gray-800" x-text="user?.name"></h2>
                <p class="text-sm text-gray-500" x-text="user?.email"></p>
                <p class="text-xs text-gray-400 mt-1" x-text="'Joined: ' + new Date(user?.created_at).toLocaleDateString()"></p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 text-sm rounded-full font-medium"
                      :class="user?.is_admin ? 'bg-brand-100 text-brand-700' : 'bg-gray-100 text-gray-600'"
                      x-text="user?.is_admin ? '🛡️ Admin' : '👤 User'"></span>
                <button @click="toggleAdmin()" :disabled="toggling"
                        class="px-4 py-1.5 text-sm rounded-lg font-medium transition disabled:opacity-50"
                        :class="user?.is_admin ? 'bg-red-100 hover:bg-red-200 text-red-700' : 'bg-emerald-100 hover:bg-emerald-200 text-emerald-700'"
                        x-text="user?.is_admin ? 'Remove Admin' : 'Make Admin'"></button>
            </div>
        </div>

        {{-- Health Profile --}}
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h3 class="font-semibold text-gray-800 mb-3">Health Profile</h3>
            <div x-show="!profile" class="text-sm text-gray-400">Not filled yet.</div>
            <div x-show="profile" class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div><span class="text-gray-500 block">Weight</span><span class="font-medium" x-text="(profile?.weight||'—')+' kg'"></span></div>
                <div><span class="text-gray-500 block">Height</span><span class="font-medium" x-text="(profile?.height||'—')+' cm'"></span></div>
                <div><span class="text-gray-500 block">Sleep</span><span class="font-medium" x-text="(profile?.avg_sleep_hours||'—')+' hrs'"></span></div>
                <div><span class="text-gray-500 block">Water</span><span class="font-medium" x-text="(profile?.water_intake||'—')+' L'"></span></div>
                <div><span class="text-gray-500 block">Stress</span><span class="font-medium capitalize" x-text="profile?.stress_level||'—'"></span></div>
                <div><span class="text-gray-500 block">Activity</span><span class="font-medium capitalize" x-text="profile?.physical_activity||'—'"></span></div>
                <div><span class="text-gray-500 block">Disease</span><span class="font-medium capitalize" x-text="profile?.disease_type||'—'"></span></div>
                <div><span class="text-gray-500 block">BMI</span><span class="font-medium" x-text="profile?.weight && profile?.height ? (profile.weight / ((profile.height/100)**2)).toFixed(1) : '—'"></span></div>
            </div>
        </div>

        {{-- Digital Twin --}}
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h3 class="font-semibold text-gray-800 mb-3">Digital Twin</h3>
            <div x-show="!twin" class="text-sm text-gray-400">Not generated yet.</div>
            <div x-show="twin" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 text-sm text-center">
                <div class="p-3 rounded-lg" :class="twin?.risk_category==='high'?'bg-red-50':twin?.risk_category==='medium'?'bg-amber-50':'bg-emerald-50'">
                    <span class="text-xs text-gray-500">Overall Risk</span>
                    <p class="text-lg font-bold" x-text="twin?.overall_risk_score?.toFixed(1)"></p>
                    <span class="text-xs capitalize font-medium" x-text="twin?.risk_category"></span>
                </div>
                <div class="p-3 rounded-lg bg-gray-50"><span class="text-xs text-gray-500">Metabolic</span><p class="text-lg font-bold text-indigo-600" x-text="twin?.metabolic_health_score?.toFixed(1)"></p></div>
                <div class="p-3 rounded-lg bg-gray-50"><span class="text-xs text-gray-500">Insulin Res.</span><p class="text-lg font-bold text-purple-600" x-text="twin?.insulin_resistance_score?.toFixed(1)"></p></div>
                <div class="p-3 rounded-lg bg-gray-50"><span class="text-xs text-gray-500">Sleep</span><p class="text-lg font-bold text-blue-600" x-text="twin?.sleep_score?.toFixed(1)"></p></div>
                <div class="p-3 rounded-lg bg-gray-50"><span class="text-xs text-gray-500">Stress</span><p class="text-lg font-bold text-amber-600" x-text="twin?.stress_score?.toFixed(1)"></p></div>
                <div class="p-3 rounded-lg bg-gray-50"><span class="text-xs text-gray-500">Diet</span><p class="text-lg font-bold text-emerald-600" x-text="twin?.diet_score?.toFixed(1)"></p></div>
            </div>
        </div>

        {{-- Recent Simulations --}}
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h3 class="font-semibold text-gray-800 mb-3">Recent Simulations</h3>
            <div x-show="sims.length === 0" class="text-sm text-gray-400">No simulations.</div>
            <div class="space-y-2">
                <template x-for="s in sims" :key="s.id">
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg text-sm">
                        <span x-text="s.type==='meal'?'🍽️':s.type==='sleep'?'😴':'😰'"></span>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium truncate text-gray-700" x-text="s.input_data?.description || s.type"></p>
                            <p class="text-xs text-gray-400" x-text="new Date(s.created_at).toLocaleString()"></p>
                        </div>
                        <span class="font-bold" :class="s.risk_change > 0 ? 'text-red-500' : 'text-emerald-500'"
                              x-text="(s.risk_change>0?'+':'')+s.risk_change.toFixed(2)"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <div x-show="!loading && !user" class="bg-white rounded-xl border p-8 text-center text-gray-400">User not found.</div>
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
