@extends('layouts.admin')
@section('heading','Alert Oversight')

@section('content')
<div x-data="adminAlerts()" x-init="init()">

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <select x-model="sevFilter" @change="page=1; load()" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 outline-none bg-white text-sm">
            <option value="">All Severities</option><option value="critical">⛔ Critical</option><option value="warning">⚠️ Warning</option><option value="info">ℹ️ Info</option>
        </select>
        <select x-model="readFilter" @change="page=1; load()" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 outline-none bg-white text-sm">
            <option value="">All</option><option value="0">Unread</option><option value="1">Read</option>
        </select>
        <input type="text" x-model="userSearch" @input.debounce.400ms="page=1; load()"
               class="flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 outline-none text-sm" placeholder="Filter by user...">
    </div>

    <div x-show="loading" class="text-center py-12"><div class="animate-spin w-8 h-8 border-4 border-brand-500 border-t-transparent rounded-full mx-auto"></div></div>

    <div x-show="!loading" class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Severity</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">User</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Title</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Message</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="a in alerts" :key="a.id">
                        <tr class="border-b last:border-0 hover:bg-gray-50 transition" :class="!a.is_read ? 'bg-amber-50/50' : ''">
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-xs rounded-full capitalize font-medium"
                                      :class="a.severity==='critical'?'bg-red-100 text-red-700':a.severity==='warning'?'bg-amber-100 text-amber-700':'bg-blue-100 text-blue-700'"
                                      x-text="a.severity"></span>
                            </td>
                            <td class="px-4 py-3 text-gray-700" x-text="a.user?.name || 'User #' + a.user_id"></td>
                            <td class="px-4 py-3 font-medium text-gray-800" x-text="a.title"></td>
                            <td class="px-4 py-3 text-gray-600 max-w-[250px] truncate" x-text="a.message"></td>
                            <td class="px-4 py-3">
                                <span class="text-xs" :class="a.is_read ? 'text-emerald-500' : 'text-amber-500'"
                                      x-text="a.is_read ? '✓ Read' : '● Unread'"></span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap" x-text="new Date(a.created_at).toLocaleString()"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div x-show="alerts.length === 0 && !loading" class="p-8 text-center text-sm text-gray-400">No alerts found.</div>

        <div x-show="meta.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t bg-gray-50">
            <p class="text-xs text-gray-500" x-text="'Page ' + page + ' of ' + meta.last_page"></p>
            <div class="flex gap-1">
                <button @click="page--; load()" :disabled="page<=1" class="px-3 py-1 text-xs border rounded disabled:opacity-30">← Prev</button>
                <button @click="page++; load()" :disabled="page>=meta.last_page" class="px-3 py-1 text-xs border rounded disabled:opacity-30">Next →</button>
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
        },
        async init() { await this.load(); }
    };
}
</script>
@endpush
