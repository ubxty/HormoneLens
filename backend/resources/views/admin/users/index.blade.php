@extends('layouts.admin')
@section('heading','User Monitoring')

@section('content')
<div x-data="adminUsers()" x-init="init()">

    {{-- Search & Filter --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-5 adm-a adm-d0" data-adm>
        <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" x-model="search" @input.debounce.400ms="loadUsers()"
                   class="adm-input w-full pl-10" placeholder="Search users by name or email…">
        </div>
        <select x-model="adminFilter" @change="loadUsers()" class="adm-input w-auto min-w-[120px]">
            <option value="">All Roles</option><option value="1">Admins</option><option value="0">Regular</option>
        </select>
    </div>

    <div x-show="loading" class="text-center py-16">
        <div class="inline-block w-8 h-8 border-[3px] border-purple-400 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div x-show="!loading" class="adm-card overflow-hidden adm-a adm-d1" data-adm>
        <div class="overflow-x-auto">
            <table class="adm-table w-full text-sm">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">User</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Email</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Role</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Joined</th>
                        <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="u in users" :key="u.id">
                        <tr class="border-b border-white/40 last:border-0 hover:bg-white/30 transition">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-400/20 to-pink-400/20 flex items-center justify-center text-purple-600 font-bold text-xs"
                                         x-text="u.name?.charAt(0).toUpperCase()"></div>
                                    <span class="font-bold text-gray-700 text-xs" x-text="u.name"></span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs" x-text="u.email"></td>
                            <td class="px-4 py-3">
                                <span class="adm-badge"
                                      :class="u.is_admin ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-500'"
                                      x-text="u.is_admin ? '🛡️ Admin' : '👤 User'"></span>
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-xs" x-text="new Date(u.created_at).toLocaleDateString()"></td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a :href="'/admin/users/' + u.id"
                                   class="adm-badge bg-white/60 text-gray-600 hover:bg-white/80 cursor-pointer transition">View</a>
                                <button @click="toggleAdmin(u)" :disabled="u._toggling"
                                        class="adm-badge cursor-pointer transition disabled:opacity-50"
                                        :class="u.is_admin ? 'bg-red-100 hover:bg-red-200 text-red-700' : 'bg-emerald-100 hover:bg-emerald-200 text-emerald-700'"
                                        x-text="u.is_admin ? 'Remove Admin' : 'Make Admin'"></button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div x-show="users.length === 0 && !loading" class="p-10 text-center text-xs text-gray-400">No users found.</div>

        {{-- Pagination --}}
        <div x-show="meta.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t border-white/40 bg-white/20">
            <p class="text-[10px] font-bold text-gray-400" x-text="'Showing ' + meta.from + '–' + meta.to + ' of ' + meta.total"></p>
            <div class="flex gap-1">
                <button @click="page--; loadUsers()" :disabled="page<=1"
                        class="adm-badge bg-white/60 hover:bg-white/80 text-gray-600 disabled:opacity-30 cursor-pointer transition">← Prev</button>
                <button @click="page++; loadUsers()" :disabled="page>=meta.last_page"
                        class="adm-badge bg-white/60 hover:bg-white/80 text-gray-600 disabled:opacity-30 cursor-pointer transition">Next →</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function adminUsers() {
    return {
        loading: true, users: [], search: '', adminFilter: '', page: 1, meta: {},
        async loadUsers() {
            this.loading = true;
            let url = '/admin/users?page=' + this.page;
            if(this.search) url += '&search=' + encodeURIComponent(this.search);
            if(this.adminFilter !== '') url += '&is_admin=' + this.adminFilter;
            const r = await api.get(url);
            if(r.success) {
                this.users = (r.data || []).map(u => ({...u, _toggling: false}));
                this.meta = r.meta || {};
            }
            this.loading = false;
            this.$nextTick(() => admAnimate());
        },
        async init() { await this.loadUsers(); },
        async toggleAdmin(u) {
            u._toggling = true;
            const r = await api.patch('/admin/users/' + u.id + '/toggle-admin');
            if(r.success){ u.is_admin = !u.is_admin; toast(u.name + (u.is_admin ? ' is now an admin' : ' is no longer an admin')); }
            else toast(r.message || 'Failed', 'error');
            u._toggling = false;
        }
    };
}
</script>
@endpush
