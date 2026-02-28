@extends('layouts.app')
@section('title','Alerts — HormoneLens')
@section('heading','Health Alerts')

@push('styles')
<style>
.gl-bg{background:linear-gradient(135deg,rgba(95,111,255,.06),rgba(194,77,255,.06) 50%,rgba(255,110,199,.06));min-height:100%;position:relative;overflow:hidden}
.gl-p{position:absolute;border-radius:50%;filter:blur(80px);pointer-events:none;opacity:.10;will-change:transform}
.gl-p1{width:300px;height:300px;background:linear-gradient(135deg,#5f6fff,#c24dff);top:-60px;right:-40px;animation:glF 18s ease-in-out infinite}
.gl-p2{width:240px;height:240px;background:linear-gradient(135deg,#c24dff,#ff6ec7);bottom:5%;left:-30px;animation:glF 22s ease-in-out 5s infinite}
@keyframes glF{0%,100%{transform:translate(0,0) scale(1)}33%{transform:translate(25px,-18px) scale(1.04)}66%{transform:translate(-18px,12px) scale(.96)}}
.gl-hero{background:linear-gradient(135deg,#5f6fff,#c24dff,#ff6ec7);border-radius:22px;position:relative;overflow:hidden}
.gl-hero::after{content:'';position:absolute;inset:0;background:radial-gradient(circle at 85% 25%,rgba(255,255,255,.14) 0%,transparent 55%);pointer-events:none}
.gl-card{background:rgba(255,255,255,.55);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.35);border-radius:16px;box-shadow:0 6px 24px rgba(95,111,255,.07);position:relative;overflow:hidden;transition:transform .4s cubic-bezier(.4,0,.2,1),box-shadow .4s ease,border-color .4s ease}
.gl-card::before{content:'';position:absolute;inset:0;border-radius:16px;padding:1.5px;background:linear-gradient(135deg,rgba(95,111,255,.25),rgba(194,77,255,.2),rgba(255,110,199,.15));-webkit-mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);-webkit-mask-composite:xor;mask-composite:exclude;pointer-events:none;opacity:0;transition:opacity .4s ease}
.gl-card:hover::before{opacity:1}
.gl-card:hover{transform:translateY(-3px);box-shadow:0 12px 36px rgba(95,111,255,.13),0 0 18px rgba(194,77,255,.06);border-color:rgba(194,77,255,.15)}
.gl-grad-text{background:linear-gradient(135deg,#5f6fff,#c24dff,#ff6ec7);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
@keyframes glUp{from{opacity:0;transform:translateY(28px)}to{opacity:1;transform:translateY(0)}}
.gl-a{opacity:0;transform:translateY(28px)}.gl-a.gl-v{animation:glUp .65s cubic-bezier(.4,0,.2,1) forwards}
.gl-d0{animation-delay:0s!important}.gl-d1{animation-delay:.06s!important}.gl-d2{animation-delay:.12s!important}.gl-d3{animation-delay:.18s!important}
@keyframes glPulse{0%,100%{opacity:.5;transform:scale(1)}50%{opacity:1;transform:scale(1.3)}}
.gl-status-pulse{animation:glPulse 2s ease-in-out infinite}
.gl-filter{background:rgba(255,255,255,.45);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.3);border-radius:12px;padding:5px 14px;font-size:11px;font-weight:700;color:#6b7280;cursor:pointer;transition:all .3s ease;text-transform:uppercase;letter-spacing:.05em}
.gl-filter:hover{background:rgba(194,77,255,.08);color:#7c3aed}
.gl-filter.active-all{background:linear-gradient(135deg,#5f6fff,#c24dff);color:#fff;border-color:transparent}
.gl-filter.active-critical{background:#ef4444;color:#fff;border-color:transparent}
.gl-filter.active-warning{background:#f59e0b;color:#fff;border-color:transparent}
.gl-filter.active-info{background:#3b82f6;color:#fff;border-color:transparent}
.gl-filter.active-unread{background:#1f2937;color:#fff;border-color:transparent}
.gl-alert-card{background:rgba(255,255,255,.5);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.3);border-radius:14px;padding:.85rem 1rem;transition:transform .3s ease,box-shadow .3s ease}
.gl-alert-card:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(95,111,255,.1)}
.gl-alert-unread{border-left:3px solid}
.gl-mark-btn{background:rgba(255,255,255,.6);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.3);border-radius:10px;padding:4px 12px;font-size:10px;font-weight:700;color:#6b7280;cursor:pointer;transition:all .3s ease;text-transform:uppercase;letter-spacing:.03em}
.gl-mark-btn:hover{background:rgba(194,77,255,.1);color:#7c3aed;border-color:rgba(194,77,255,.2)}
</style>
@endpush

@section('content')
<div x-data="alertsPage()" x-init="init()" class="gl-bg -m-4 sm:-m-6 p-4 sm:p-6">
    <div class="gl-p gl-p1"></div>
    <div class="gl-p gl-p2"></div>

    <div class="max-w-3xl mx-auto relative">

        {{-- Hero --}}
        <div class="gl-hero px-5 py-4 mb-4 gl-a gl-d0" data-gl>
            <div class="relative z-10 flex items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <div class="w-2 h-2 rounded-full bg-emerald-400 gl-status-pulse"></div>
                        <span class="text-white/70 text-[11px] font-medium tracking-wide uppercase">Alert System</span>
                    </div>
                    <h1 class="text-lg font-bold text-white">🔔 Health Alerts</h1>
                </div>
                <span x-show="unread > 0" x-transition class="px-3 py-1 bg-white/20 backdrop-blur-sm text-white text-xs font-bold rounded-full" x-text="unread + ' unread'"></span>
            </div>
        </div>

        {{-- Filters --}}
        <div class="flex gap-2 mb-4 flex-wrap gl-a gl-d1" data-gl>
            <button @click="filter='all'" class="gl-filter" :class="filter==='all'?'active-all':''">All</button>
            <button @click="filter='critical'" class="gl-filter" :class="filter==='critical'?'active-critical':''">⛔ Critical</button>
            <button @click="filter='warning'" class="gl-filter" :class="filter==='warning'?'active-warning':''">⚠️ Warning</button>
            <button @click="filter='info'" class="gl-filter" :class="filter==='info'?'active-info':''">ℹ️ Info</button>
            <button @click="filter='unread'" class="gl-filter" :class="filter==='unread'?'active-unread':''">Unread</button>
        </div>

        {{-- Loading --}}
        <div x-show="loading" class="text-center py-10">
            <div class="inline-block w-8 h-8 border-[3px] border-purple-400 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-xs text-gray-400 mt-2">Loading alerts…</p>
        </div>

        {{-- Empty --}}
        <div x-show="!loading && filtered.length === 0" class="gl-card p-8 text-center gl-a gl-d2" data-gl>
            <div class="text-4xl mb-2">🔔</div>
            <p class="text-xs text-gray-400">No alerts to show.</p>
        </div>

        {{-- Alert list --}}
        <div class="space-y-2.5">
            <template x-for="a in filtered" :key="a.id">
                <div class="gl-alert-card flex items-start gap-3"
                     :class="!a.is_read ? 'gl-alert-unread' : ''"
                     :style="!a.is_read ? 'border-left-color:' + (a.severity==='critical'?'#ef4444':a.severity==='warning'?'#f59e0b':'#3b82f6') : ''">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm flex-shrink-0"
                         :class="a.severity==='critical'?'bg-red-100 text-red-600':a.severity==='warning'?'bg-amber-100 text-amber-600':'bg-blue-100 text-blue-600'">
                        <span x-show="a.severity==='critical'">⛔</span>
                        <span x-show="a.severity==='warning'">⚠️</span>
                        <span x-show="a.severity==='info'">ℹ️</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <h3 class="text-xs font-bold text-gray-800" x-text="a.title"></h3>
                            <span class="px-2 py-0.5 text-[9px] rounded-full capitalize font-bold"
                                  :class="a.severity==='critical'?'bg-red-100 text-red-700':a.severity==='warning'?'bg-amber-100 text-amber-700':'bg-blue-100 text-blue-700'"
                                  x-text="a.severity"></span>
                        </div>
                        <p class="text-xs text-gray-500" x-text="a.message"></p>
                        <p class="text-[10px] text-gray-400 mt-1" x-text="new Date(a.created_at).toLocaleString()"></p>
                    </div>
                    <button x-show="!a.is_read" @click="markRead(a)" class="gl-mark-btn shrink-0">Mark read</button>
                    <span x-show="a.is_read" class="shrink-0 text-[10px] text-emerald-500 font-bold">✓ Read</span>
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
            this.$nextTick(()=>document.querySelectorAll('[data-gl]').forEach(el=>el.classList.add('gl-v')));
        },
        async markRead(a){
            const r = await api.patch('/alerts/'+a.id+'/read');
            if(r.success){ a.is_read = true; this.unread = Math.max(0, this.unread-1); toast('Alert marked as read'); }
        }
    };
}
</script>
@endpush
