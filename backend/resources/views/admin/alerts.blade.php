@extends('layouts.admin')
@section('heading','Alert Oversight')

@section('content')
<div x-data="adminAlerts()" x-init="init()">

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-5 adm-a adm-d0" data-adm>
        <select x-model="sevFilter" @change="page=1;load()" class="adm-input w-auto min-w-[140px]">
            <option value="">All Severities</option>
            <option value="critical">Critical</option>
            <option value="warning">Warning</option>
            <option value="info">Info</option>
        </select>
        <select x-model="readFilter" @change="page=1;load()" class="adm-input w-auto min-w-[140px]">
            <option value="">All Status</option>
            <option value="0">Unread</option>
            <option value="1">Read</option>
        </select>
        <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" x-model.debounce.400ms="search" @input="page=1;load()" placeholder="Search by user name&#8230;" class="adm-input w-full pl-10">
        </div>
    </div>

    {{-- Loading --}}
    <div x-show="loading" class="text-center py-16">
        <div class="inline-block w-8 h-8 border-[3px] border-purple-400 border-t-transparent rounded-full animate-spin"></div>
    </div>

    {{-- Alert Cards (Slide-in) --}}
    <div x-show="!loading" class="space-y-3">
        <template x-for="(a, i) in alerts" :key="a.id">
            <div class="adm-card relative p-4 adm-slide"
                 :class="[
                     a.severity === 'critical' ? 'adm-sev-critical' : a.severity === 'warning' ? 'adm-sev-warning' : 'adm-sev-info',
                     a.is_read ? 'opacity-60' : ''
                 ]"
                 :style="'animation-delay:' + (i * 0.07) + 's'"
                 data-adm>
                <div class="flex items-start gap-4">
                    {{-- Severity Icon --}}
                    <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center text-lg"
                         :class="a.severity === 'critical' ? 'bg-red-100/80 text-red-600' : a.severity === 'warning' ? 'bg-amber-100/80 text-amber-600' : 'bg-blue-100/80 text-blue-600'">
                        <span x-text="a.severity === 'critical' ? '&#128680;' : a.severity === 'warning' ? '&#9888;' : '&#8505;'"></span>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="adm-badge" :class="a.severity === 'critical' ? 'bg-red-100 text-red-700' : a.severity === 'warning' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700'"
                                  x-text="a.severity"></span>
                            <span x-show="!a.is_read" class="w-2 h-2 rounded-full bg-red-500 adm-pulse"></span>
                        </div>
                        <h4 class="text-sm font-bold text-gray-700" x-text="a.title"></h4>
                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-2" x-text="a.message"></p>
                        <div class="flex items-center gap-3 mt-2">
                            <a :href="'/admin/users/' + a.user_id" class="text-[10px] font-bold text-purple-500 hover:text-purple-700 transition flex items-center gap-1">
                                <div class="w-4 h-4 rounded-full bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center text-white text-[7px] font-bold"
                                     x-text="(a.user?.name||'?').charAt(0).toUpperCase()"></div>
                                <span x-text="a.user?.name || 'Unknown'"></span>
                            </a>
                            <span class="text-[10px] text-gray-400" x-text="new Date(a.created_at).toLocaleDateString()"></span>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="shrink-0">
                        <span class="adm-badge" :class="a.is_read ? 'bg-gray-100 text-gray-400' : 'bg-red-50 text-red-500'"
                              x-text="a.is_read ? 'Read' : 'Unread'"></span>
                    </div>
                </div>
            </div>
        </template>

        {{-- Empty State --}}
        <div x-show="alerts.length === 0 && !loading" class="adm-card p-10 text-center adm-a adm-d0" data-adm>
            <div class="text-4xl mb-2">&#128276;</div>
            <p class="text-xs text-gray-400">No alerts found matching your criteria.</p>
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
function adminAlerts() {
    return {
        loading: true, alerts: [], meta: {}, page: 1, sevFilter: '', readFilter: '', search: '',
        async load() {
            this.loading = true;
            let url = '/admin/alerts?page=' + this.page;
            if (this.sevFilter) url += '&severity=' + this.sevFilter;
            if (this.readFilter !== '') url += '&is_read=' + this.readFilter;
            if (this.search) url += '&search=' + encodeURIComponent(this.search);
            const r = await api.get(url);
            if (r.success) { this.alerts = r.data || []; this.meta = r.meta || {}; }
            this.loading = false;
            this.$nextTick(() => admAnimate());
        },
        async init() { await this.load(); }
    };
}
</script>
@endpush
