@extends('layouts.admin')
@section('heading','User Monitoring')

@section('content')
<div x-data="adminUsers()" x-init="init()">

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-5 adm-a adm-d0" data-adm>
        <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" x-model.debounce.400ms="search" @input="page=1;load()" placeholder="Search users by name or email&#8230;" class="adm-input w-full pl-10">
        </div>
        
        <div class="relative flex items-center gap-2">
            <div class="flex items-center gap-2 adm-input py-0 px-3 min-w-[160px]">
                <svg class="w-3.5 h-3.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4.5h18m-18 5h18m-18 5h18m-18 5h18" />
                </svg>
                <select x-model="roleFilter" @change="page=1;load()" class="bg-transparent border-none focus:ring-0 text-sm font-medium text-gray-600 cursor-pointer w-full py-2.5 outline-none">
                    <option value="">All Roles</option>
                    <option value="1">Admins Only</option>
                    <option value="0">Standard Users</option>
                </select>
            </div>
            
            <button @click="load()" class="adm-btn px-4 py-2.5 flex items-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span>Filter</span>
            </button>
        </div>
    </div>

    {{-- Loading --}}
    <div x-show="loading" class="text-center py-16">
        <div class="inline-block w-8 h-8 border-[3px] border-purple-400 border-t-transparent rounded-full animate-spin"></div>
    </div>

    {{-- User Table --}}
    <div x-show="!loading" class="adm-card relative p-0 overflow-hidden adm-a adm-d1" data-adm>
        <div class="overflow-x-auto">
            <table class="adm-table w-full">
                <thead>
                    <tr>
                        <th class="pl-5">User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th class="text-right pr-5">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="u in users" :key="u.id">
                        <tr class="group">
                            <td class="pl-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center text-white text-[10px] font-bold shrink-0"
                                         x-text="(u.name||'?').charAt(0).toUpperCase()"></div>
                                    <span class="font-semibold text-gray-700" x-text="u.name"></span>
                                </div>
                            </td>
                            <td class="text-gray-500" x-text="u.email"></td>
                            <td>
                                <span class="adm-badge" :class="u.is_admin ? 'bg-gradient-to-r from-purple-100 to-pink-100 text-purple-700' : 'bg-gray-100 text-gray-500'"
                                      x-text="u.is_admin ? 'Admin' : 'User'"></span>
                            </td>
                            <td class="text-gray-400 text-xs" x-text="new Date(u.created_at).toLocaleDateString()"></td>
                            <td class="text-right pr-5">
                                <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition">
                                    <a :href="'/admin/users/' + u.id" class="adm-badge bg-gradient-to-r from-purple-100 to-pink-100 text-purple-700 hover:from-purple-200 hover:to-pink-200 cursor-pointer transition">View</a>
                                    <button @click="toggleAdmin(u)" :disabled="u._toggling"
                                            class="adm-badge cursor-pointer transition"
                                            :class="u.is_admin ? 'bg-red-50 text-red-500 hover:bg-red-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100'"
                                            x-text="u._toggling ? '...' : (u.is_admin ? 'Remove Admin' : 'Make Admin')"></button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Empty State --}}
        <div x-show="users.length === 0 && !loading" class="p-10 text-center">
            <div class="text-4xl mb-2">&#128100;</div>
            <p class="text-xs text-gray-400">No users found matching your criteria.</p>
        </div>
    </div>

    {{-- Pagination --}}
    <div x-show="meta.last_page > 1" class="flex items-center justify-between mt-4 adm-a adm-d2" data-adm>
        <p class="text-[10px] text-gray-400">Page <span x-text="meta.current_page"></span> of <span x-text="meta.last_page"></span></p>
        <div class="flex gap-2">
            <button @click="page--;load()" :disabled="page<=1" class="adm-btn text-xs disabled:opacity-30">&larr; Prev</button>
            <button @click="page++;load()" :disabled="page>=meta.last_page" class="adm-btn text-xs disabled:opacity-30">Next &rarr;</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function adminUsers() {
    return {
        loading: true, users: [], meta: {}, page: 1, search: '', roleFilter: '',
        async load() {
            this.loading = true;
            let url = '/admin/users?page=' + this.page;
            if (this.search) url += '&search=' + encodeURIComponent(this.search);
            if (this.roleFilter !== '') url += '&is_admin=' + this.roleFilter;
            const r = await api.get(url);
            if (r.success) { this.users = r.data; this.meta = r.meta || {}; }
            this.loading = false;
            this.$nextTick(() => admAnimate());
        },
        async init() { await this.load(); },
        async toggleAdmin(u) {
            u._toggling = true;
            const r = await api.patch('/admin/users/' + u.id + '/toggle-admin');
            if (r.success) { u.is_admin = !u.is_admin; toast(u.name + ' role updated!'); }
            else toast(r.message || 'Failed', 'error');
            u._toggling = false;
        }
    };
}
</script>
@endpush
