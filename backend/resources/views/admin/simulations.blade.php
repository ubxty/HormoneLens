@extends('layouts.admin')
@section('heading','Simulation Logs')

@section('content')
<div x-data="adminSims()" x-init="init()">

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-5 adm-a adm-d0" data-adm>
        <select x-model="typeFilter" @change="page=1; load()" class="adm-input w-auto min-w-[140px]">
            <option value="">All Types</option><option value="meal">🍽️ Meal</option><option value="sleep">😴 Sleep</option><option value="stress">😰 Stress</option>
        </select>
        <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" x-model="userSearch" @input.debounce.400ms="page=1; load()"
                   class="adm-input w-full pl-10" placeholder="Filter by user name or email…">
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
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">User</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Description</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Before</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">After</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Change</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="s in sims" :key="s.id">
                        <tr class="border-b border-white/40 last:border-0 hover:bg-white/30 cursor-pointer transition" @click="expanded = expanded === s.id ? null : s.id">
                            <td class="px-4 py-3 text-xs font-bold text-gray-700" x-text="s.user?.name || 'User #' + s.user_id"></td>
                            <td class="px-4 py-3">
                                <span class="adm-badge capitalize" :class="s.type==='meal'?'bg-orange-100 text-orange-700':s.type==='sleep'?'bg-blue-100 text-blue-700':'bg-amber-100 text-amber-700'" x-text="s.type"></span>
                            </td>
                            <td class="px-4 py-3 max-w-[180px] truncate text-xs text-gray-500" x-text="s.input_data?.description || '—'"></td>
                            <td class="px-4 py-3 text-xs font-bold text-gray-600" x-text="s.original_risk_score?.toFixed(2)"></td>
                            <td class="px-4 py-3 text-xs font-bold text-gray-600" x-text="s.simulated_risk_score?.toFixed(2)"></td>
                            <td class="px-4 py-3 text-xs font-bold" :class="s.risk_change > 0 ? 'text-red-500' : 'text-emerald-500'"
                                x-text="(s.risk_change > 0 ? '+' : '') + s.risk_change?.toFixed(2)"></td>
                            <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap" x-text="new Date(s.created_at).toLocaleDateString()"></td>
                        </tr>
                        {{-- Expanded detail --}}
                        <tr x-show="expanded === s.id" x-transition class="bg-white/20">
                            <td colspan="7" class="px-6 py-4">
                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Category Shift</p>
                                        <div class="flex items-center gap-2">
                                            <span class="adm-badge capitalize" :class="catC(s.risk_category_before)" x-text="s.risk_category_before"></span>
                                            <span class="text-gray-300">→</span>
                                            <span class="adm-badge capitalize" :class="catC(s.risk_category_after)" x-text="s.risk_category_after"></span>
                                        </div>
                                    </div>
                                    <div x-show="s.rag_explanation">
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">💡 RAG Explanation</p>
                                        <p class="text-xs text-gray-600 leading-relaxed" x-text="s.rag_explanation"></p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div x-show="sims.length === 0 && !loading" class="p-10 text-center text-xs text-gray-400">No simulations found.</div>

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
function adminSims() {
    return {
        loading: true, sims: [], page: 1, meta: {}, typeFilter: '', userSearch: '', expanded: null,
        catC(c) { return c==='high'?'bg-red-100 text-red-700':c==='medium'?'bg-amber-100 text-amber-700':'bg-emerald-100 text-emerald-700'; },
        async load() {
            this.loading = true;
            let url = '/admin/simulations?page=' + this.page;
            if(this.typeFilter) url += '&type=' + this.typeFilter;
            if(this.userSearch) url += '&search=' + encodeURIComponent(this.userSearch);
            const r = await api.get(url);
            if(r.success) { this.sims = r.data || []; this.meta = r.meta || {}; }
            this.loading = false;
            this.$nextTick(() => admAnimate());
        },
        async init() { await this.load(); }
    };
}
</script>
@endpush
