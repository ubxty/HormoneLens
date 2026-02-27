@extends('layouts.admin')
@section('heading','Simulation Logs')

@section('content')
<div x-data="adminSims()" x-init="init()">

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <select x-model="typeFilter" @change="page=1; load()" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 outline-none bg-white text-sm">
            <option value="">All Types</option><option value="meal">🍽️ Meal</option><option value="sleep">😴 Sleep</option><option value="stress">😰 Stress</option>
        </select>
        <input type="text" x-model="userSearch" @input.debounce.400ms="page=1; load()"
               class="flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 outline-none text-sm" placeholder="Filter by user name or email...">
    </div>

    <div x-show="loading" class="text-center py-12"><div class="animate-spin w-8 h-8 border-4 border-brand-500 border-t-transparent rounded-full mx-auto"></div></div>

    <div x-show="!loading" class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">User</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Type</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Description</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Risk Before</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Risk After</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Change</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="s in sims" :key="s.id">
                        <tr class="border-b last:border-0 hover:bg-gray-50 cursor-pointer transition" @click="expanded = expanded === s.id ? null : s.id">
                            <td class="px-4 py-3 text-gray-700" x-text="s.user?.name || 'User #' + s.user_id"></td>
                            <td class="px-4 py-3"><span class="capitalize" x-text="s.type"></span></td>
                            <td class="px-4 py-3 max-w-[200px] truncate text-gray-600" x-text="s.input_data?.description || '—'"></td>
                            <td class="px-4 py-3 font-medium" x-text="s.original_risk_score?.toFixed(2)"></td>
                            <td class="px-4 py-3 font-medium" x-text="s.simulated_risk_score?.toFixed(2)"></td>
                            <td class="px-4 py-3 font-bold" :class="s.risk_change > 0 ? 'text-red-500' : 'text-emerald-500'"
                                x-text="(s.risk_change > 0 ? '+' : '') + s.risk_change?.toFixed(2)"></td>
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap" x-text="new Date(s.created_at).toLocaleDateString()"></td>
                        </tr>
                        <tr x-show="expanded === s.id" class="bg-gray-50">
                            <td colspan="7" class="px-6 py-4 text-sm">
                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <p class="font-medium text-gray-700 mb-1">Category Shift</p>
                                        <p><span class="capitalize px-2 py-0.5 rounded text-xs" :class="catC(s.risk_category_before)" x-text="s.risk_category_before"></span>
                                           → <span class="capitalize px-2 py-0.5 rounded text-xs" :class="catC(s.risk_category_after)" x-text="s.risk_category_after"></span></p>
                                    </div>
                                    <div x-show="s.rag_explanation">
                                        <p class="font-medium text-gray-700 mb-1">💡 RAG Explanation</p>
                                        <p class="text-gray-600" x-text="s.rag_explanation"></p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div x-show="sims.length === 0 && !loading" class="p-8 text-center text-sm text-gray-400">No simulations found.</div>

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
        },
        async init() { await this.load(); }
    };
}
</script>
@endpush
