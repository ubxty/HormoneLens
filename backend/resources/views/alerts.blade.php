@extends('layouts.app')
@section('title','Alerts — HormoneLens')

@section('content')
<div x-data="alertsPage()" x-init="init()">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Alerts</h1>
                <p class="text-gray-500">Health warnings from your simulations.</p>
            </div>
            <span x-show="unread > 0" class="px-3 py-1 bg-red-100 text-red-700 text-sm font-medium rounded-full" x-text="unread + ' unread'"></span>
        </div>

        {{-- Filters --}}
        <div class="flex gap-2 mb-4 flex-wrap">
            <button @click="filter='all'" :class="filter==='all'?'bg-brand-600 text-white':'bg-white text-gray-700 border'" class="px-4 py-1.5 text-sm rounded-lg font-medium transition">All</button>
            <button @click="filter='critical'" :class="filter==='critical'?'bg-red-600 text-white':'bg-white text-gray-700 border'" class="px-4 py-1.5 text-sm rounded-lg font-medium transition">Critical</button>
            <button @click="filter='warning'" :class="filter==='warning'?'bg-amber-600 text-white':'bg-white text-gray-700 border'" class="px-4 py-1.5 text-sm rounded-lg font-medium transition">Warning</button>
            <button @click="filter='info'" :class="filter==='info'?'bg-blue-600 text-white':'bg-white text-gray-700 border'" class="px-4 py-1.5 text-sm rounded-lg font-medium transition">Info</button>
            <button @click="filter='unread'" :class="filter==='unread'?'bg-gray-800 text-white':'bg-white text-gray-700 border'" class="px-4 py-1.5 text-sm rounded-lg font-medium transition">Unread</button>
        </div>

        <div x-show="loading" class="text-center py-12"><div class="animate-spin w-8 h-8 border-4 border-brand-500 border-t-transparent rounded-full mx-auto"></div></div>

        <div x-show="!loading && filtered.length === 0" class="bg-white rounded-xl border p-8 text-center text-sm text-gray-400">
            <div class="text-4xl mb-2">🔔</div>No alerts to show.
        </div>

        <div class="space-y-3">
            <template x-for="a in filtered" :key="a.id">
                <div class="bg-white rounded-xl shadow-sm border p-4 flex items-start gap-3 transition"
                     :class="!a.is_read ? 'border-l-4' : ''"
                     :style="!a.is_read ? 'border-left-color:' + (a.severity==='critical'?'#ef4444':a.severity==='warning'?'#f59e0b':'#3b82f6') : ''">
                    <div class="text-lg mt-0.5">
                        <span x-show="a.severity==='critical'">⛔</span>
                        <span x-show="a.severity==='warning'">⚠️</span>
                        <span x-show="a.severity==='info'">ℹ️</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-sm font-semibold text-gray-800" x-text="a.title"></h3>
                            <span class="px-2 py-0.5 text-[10px] rounded-full capitalize"
                                  :class="a.severity==='critical'?'bg-red-100 text-red-700':a.severity==='warning'?'bg-amber-100 text-amber-700':'bg-blue-100 text-blue-700'"
                                  x-text="a.severity"></span>
                        </div>
                        <p class="text-sm text-gray-600" x-text="a.message"></p>
                        <p class="text-xs text-gray-400 mt-1" x-text="new Date(a.created_at).toLocaleString()"></p>
                    </div>
                    <button x-show="!a.is_read" @click="markRead(a)"
                            class="shrink-0 px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition">
                        Mark read
                    </button>
                    <span x-show="a.is_read" class="shrink-0 text-xs text-emerald-500">✓ Read</span>
                </div>
            </template>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function alertsPage() {
    return {
        loading: true, alerts: [], filter: 'all', unread: 0,
        get filtered(){
            return this.alerts.filter(a => {
                if(this.filter==='all') return true;
                if(this.filter==='unread') return !a.is_read;
                return a.severity === this.filter;
            });
        },
        async init(){
            const [r, c] = await Promise.all([api.get('/alerts'), api.get('/alerts/unread-count')]);
            if(r.success) this.alerts = r.data || [];
            this.unread = c.data?.count ?? 0;
            this.loading = false;
        },
        async markRead(a){
            const r = await api.patch('/alerts/'+a.id+'/read');
            if(r.success){ a.is_read = true; this.unread = Math.max(0, this.unread-1); toast('Alert marked as read'); }
        }
    };
}
</script>
@endpush
