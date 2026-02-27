@extends('layouts.admin')
@section('heading','User Management')

@section('content')
<div x-data="adminUsers()" x-init="init()">

    {{-- Search --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <input type="text" x-model="search" @input.debounce.400ms="loadUsers()"
               class="flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 outline-none text-sm"
               placeholder="Search users by name or email...">
        <select x-model="adminFilter" @change="loadUsers()"
                class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 outline-none bg-white text-sm">
            <option value="">All</option><option value="1">Admins</option><option value="0">Regular</option>
        </select>
    </div>

    <div x-show="loading" class="text-center py-12"><div class="animate-spin w-8 h-8 border-4 border-brand-500 border-t-transparent rounded-full mx-auto"></div></div>

    <div x-show="!loading" class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">User</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Email</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Role</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Joined</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="u in users" :key="u.id">
                        <tr class="border-b last:border-0 hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center text-brand-600 font-bold text-sm"
                                         x-text="u.name?.charAt(0).toUpperCase()"></div>
                                    <span class="font-medium text-gray-800" x-text="u.name"></span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600" x-text="u.email"></td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-xs rounded-full font-medium"
                                      :class="u.is_admin ? 'bg-brand-100 text-brand-700' : 'bg-gray-100 text-gray-600'"
                                      x-text="u.is_admin ? 'Admin' : 'User'"></span>
                            </td>
                            <td class="px-4 py-3 text-gray-500" x-text="new Date(u.created_at).toLocaleDateString()"></td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a :href="'/admin/users/' + u.id"
                                   class="inline-block px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium">View</a>
                                <button @click="toggleAdmin(u)" :disabled="u._toggling"
                                        class="px-3 py-1 text-xs rounded-lg font-medium transition disabled:opacity-50"
                                        :class="u.is_admin ? 'bg-red-100 hover:bg-red-200 text-red-700' : 'bg-emerald-100 hover:bg-emerald-200 text-emerald-700'"
                                        x-text="u.is_admin ? 'Remove Admin' : 'Make Admin'"></button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div x-show="users.length === 0 && !loading" class="p-8 text-center text-sm text-gray-400">No users found.</div>

        {{-- Pagination --}}
        <div x-show="meta.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t bg-gray-50">
            <p class="text-xs text-gray-500" x-text="'Showing ' + meta.from + '-' + meta.to + ' of ' + meta.total"></p>
            <div class="flex gap-1">
                <button @click="page--; loadUsers()" :disabled="page<=1"
                        class="px-3 py-1 text-xs border rounded disabled:opacity-30">← Prev</button>
                <button @click="page++; loadUsers()" :disabled="page>=meta.last_page"
                        class="px-3 py-1 text-xs border rounded disabled:opacity-30">Next →</button>
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
