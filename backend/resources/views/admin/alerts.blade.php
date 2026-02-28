@extends('layouts.admin')
@section('heading','Alert Oversight')

@section('content')
<div x-data="adminAlerts()" x-init="init()">

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-5 adm-a adm-d0" data-adm>
        <select x-model="sevFilter" @change="page=1; load()" class="adm-input w-auto min-w-[150px]">
            <option value="">All Severities</option><option value="critical">⛔ Critical</option><option value="warning">⚠️ Warning</option><option value="info">ℹ️ Info</option>
        </select>
        <select x-model="readFilter" @change="page=1; load()" class="adm-input w-auto min-w-[120px]">
            <option value="">All</option><option value="0">Unread</option><option value="1">Read</option>
        </select>
        <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" x-model="userSearch" @input.debounce.400ms="page=1; load()"
                   class="adm-input w-full pl-10" placeholder="Filter by user…">
        </div>
    </div>

    <div x-show="loading" class="text-center py-16">
        <div class="inline-block w-8 h-8 border-[3px] border-purple-400 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div x-show="!loading" class="adm-card overflow-hidden adm-a adm-d1" data-adm>
        <div class="overflow-x-auto">
            <table class="adm-table w-full text-sm">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Severity</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">User</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Title</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Message</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="a in alerts" :key="a.id">
                        <tr class="border-b border-white/40 last:border-0 hover:bg-white/30 transition" :class="!a.is_read ? 'bg-amber-50/30' : ''">
                            <td class="px-4 py-3">
                                <span class="adm-badge capitalize"
                                      :class="a.severity==='critical'?'bg-red-100 text-red-700':a.severity==='warning'?'bg-amber-100 text-amber-700':'bg-blue-100 text-blue-700'"
                                      x-text="a.severity"></span>
                            </td>
                            <td class="px-4 py-3 text-xs font-bold text-gray-700" x-text="a.user?.name || 'User #' + a.user_id"></td>
                            <td class="px-4 py-3 text-xs font-bold text-gray-700" x-text="a.title"></td>
                            <td class="px-4 py-3 text-xs text-gray-500 max-w-[220px] truncate" x-text="a.message"></td>
                            <td class="px-4 py-3">
                                <span class="text-xs font-bold" :class="a.is_read ? 'text-emerald-500' : 'text-amber-500'"
                                      x-text="a.is_read ? '✓ Read' : '● Unread'"></span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap" x-text="new Date(a.created_at).toLocaleString()"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div x-show="alerts.length === 0 && !loading" class="p-10 text-center text-xs text-gray-400">No alerts found.</div>

        <div x-show="meta.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t border-white/40 bg-white/20">
            <p class="text-[10px] font-bold text-gray-400" x-text="'Page ' + page + ' of ' + meta.last_page"></p>
            <div class="flex gap-1">
                <button @click="page--; load()" :disabled="page<=1" class="adm-badge bg-white/60 hover:bg-white/80 text-gray-600 disabled:opacity-30 cursor-pointer transition">← Prev</button>
                <button @click="page++; load()" :disabled="page>=meta.last_page" class="adm-badge bg-white/60 hover:bg-white/80 text-gray-600 disabled:opacity-30 cursor-pointer transition">Next →</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function adminAlerts() {
    return {
        loading: true, alerts: [], page: 1, meta: {}, sevFilter: '', readFilter: '', userSearch: '',
        async load() {
            this.loading = true;
            let url = '/admin/alerts?page=' + this.page;
            if(this.sevFilter) url += '&severity=' + this.sevFilter;
            if(this.readFilter !== '') url += '&is_read=' + this.readFilter;
            if(this.userSearch) url += '&search=' + encodeURIComponent(this.userSearch);
            const r = await api.get(url);
            if(r.success) { this.alerts = r.data || []; this.meta = r.meta || {}; }
            this.loading = false;
            this.$nextTick(() => admAnimate());
        },
        async init() { await this.load(); }
    };
}
</script>
@endpush
