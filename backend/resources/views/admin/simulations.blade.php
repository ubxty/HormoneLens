@extends('layouts.admin')
@section('heading','Simulation Logs')

@section('content')
<div x-data="adminSims()" x-init="init()">

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-5 adm-a adm-d0 items-center" data-adm>
        <select x-model="typeFilter" @change="page=1;load()" class="adm-input w-auto min-w-[160px]">
            <option value="">All Types</option>
            <option value="lifestyle_change">Lifestyle Change</option>
            <option value="medication_change">Medication Change</option>
            <option value="diet_change">Diet Change</option>
            <option value="exercise_change">Exercise Change</option>
            <option value="food_impact">Food Impact</option>
        </select>

        <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" x-model.debounce.400ms="search" @input="page=1;load()" placeholder="Search by user name&#8230;" class="adm-input w-full pl-10 pr-28">
        </div>

        <div class="flex items-center">
            <button @click="page=1;load()" class="adm-btn px-4 py-2.5 flex items-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18M3 12h18M3 20h18" />
                </svg>
                <span>Filter</span>
            </button>
        </div>
    </div>

    {{-- Loading --}}
    <div x-show="loading" class="text-center py-16">
        <div class="inline-block w-8 h-8 border-[3px] border-purple-400 border-t-transparent rounded-full animate-spin"></div>
    </div>

    {{-- Simulation Table --}}
    <div x-show="!loading" class="adm-card relative p-0 overflow-hidden adm-a adm-d1" data-adm>
        <div class="overflow-x-auto">
            <table class="adm-table w-full">
                <thead>
                    <tr>
                        <th class="pl-5">User</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th class="text-center">Before</th>
                        <th class="text-center">After</th>
                        <th class="text-center">Change</th>
                        <th>Date</th>
                        <th class="w-10"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(s, idx) in sims" :key="s.id">
                        <tr class="group cursor-pointer" @click="s._open = !s._open">
                            <td class="pl-5">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center text-white text-[9px] font-bold shrink-0"
                                         x-text="(s.user?.name||'?').charAt(0).toUpperCase()"></div>
                                    <span class="text-xs font-semibold text-gray-700 truncate max-w-[100px]" x-text="s.user?.name || 'Unknown'"></span>
                                </div>
                            </td>
                            <td><span class="adm-badge bg-purple-50 text-purple-600 capitalize" x-text="(s.type||'').replace('_',' ')"></span></td>
                            <td class="text-xs text-gray-500 max-w-[160px] truncate" x-text="s.input_data?.description || '&#8212;'"></td>
                            <td class="text-center text-xs font-bold text-gray-600" x-text="s.original_risk_score ?? '&#8212;'"></td>
                            <td class="text-center text-xs font-bold text-gray-600" x-text="s.simulated_risk_score ?? '&#8212;'"></td>
                            <td class="text-center">
                                <span class="text-xs font-bold" :class="parseFloat(s.risk_change) > 0 ? 'text-red-500' : parseFloat(s.risk_change) < 0 ? 'text-emerald-500' : 'text-gray-400'"
                                      x-text="s.risk_change != null ? ((parseFloat(s.risk_change) > 0 ? '+' : '') + s.risk_change) : '&#8212;'"></span>
                            </td>
                            <td class="text-xs text-gray-400" x-text="new Date(s.created_at).toLocaleDateString()"></td>
                            <td class="text-center">
                                <span class="text-gray-400 text-[10px] transition-transform inline-block" :class="s._open ? 'rotate-90' : ''">&#9654;</span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Expanded Detail (shown via separate render) --}}
        <template x-for="s in sims.filter(s => s._open)" :key="'detail-' + s.id">
            <div class="mx-4 mb-3 bg-gradient-to-r from-purple-50/60 to-pink-50/60 backdrop-blur-sm rounded-xl border border-purple-100/40 p-4 text-xs space-y-2" x-transition>
                <div class="flex gap-4">
                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase">Risk Before</span>
                        <p class="font-bold text-gray-700" x-text="s.risk_category_before || '&#8212;'"></p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase">Risk After</span>
                        <p class="font-bold" :class="catC(s.risk_category_after)" x-text="s.risk_category_after || '&#8212;'"></p>
                    </div>
                </div>
                <div x-show="s.rag_explanation">
                    <span class="text-[10px] font-bold text-gray-400 uppercase">AI Explanation</span>
                    <p class="text-gray-600 leading-relaxed mt-0.5" x-text="s.rag_explanation"></p>
                </div>
            </div>
        </template>

        {{-- Empty State --}}
        <div x-show="sims.length === 0 && !loading" class="p-10 text-center">
            <div class="text-4xl mb-2">&#9889;</div>
            <p class="text-xs text-gray-400">No simulations found.</p>
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
function adminSims() {
    return {
        loading: true, sims: [], meta: {}, page: 1, typeFilter: '', search: '',
        async load() {
            this.loading = true;
            let url = '/admin/simulations?page=' + this.page;
            if (this.typeFilter) url += '&type=' + this.typeFilter;
            if (this.search) url += '&search=' + encodeURIComponent(this.search);
            const r = await api.get(url);
            if (r.success) { this.sims = (r.data || []).map(s => ({...s, _open: false})); this.meta = r.meta || {}; }
            this.loading = false;
            this.$nextTick(() => admAnimate());
        },
        async init() { await this.load(); },
        catC(c) {
            if (!c) return 'text-gray-500';
            c = c.toLowerCase();
            if (c === 'low') return 'text-emerald-600';
            if (c === 'moderate') return 'text-amber-600';
            if (c === 'high') return 'text-red-500';
            if (c === 'critical') return 'text-red-800';
            return 'text-gray-500';
        }
    };
}
</script>
@endpush
