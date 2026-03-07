@extends('layouts.app')
@section('title','Knowledge Base — HormoneLens')
@section('heading','Knowledge Base')

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
.gl-d0{animation-delay:0s!important}.gl-d1{animation-delay:.06s!important}.gl-d2{animation-delay:.12s!important}.gl-d3{animation-delay:.18s!important}.gl-d4{animation-delay:.24s!important}
@keyframes glPulse{0%,100%{opacity:.5;transform:scale(1)}50%{opacity:1;transform:scale(1.3)}}
.gl-status-pulse{animation:glPulse 2s ease-in-out infinite}
.gl-input{background:rgba(255,255,255,.5);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.3);border-radius:12px;padding:.55rem .75rem;font-size:13px;color:#374151;outline:none;transition:border-color .3s ease,box-shadow .3s ease;width:100%}
.gl-input:focus{border-color:rgba(194,77,255,.35);box-shadow:0 0 0 3px rgba(194,77,255,.08)}
.gl-input::placeholder{color:#9ca3af}
.gl-btn{background:linear-gradient(135deg,#5f6fff,#c24dff);color:#fff;border:none;border-radius:14px;padding:10px 26px;font-size:13px;font-weight:700;cursor:pointer;transition:all .3s ease;position:relative;overflow:hidden}
.gl-btn:hover{filter:brightness(1.08);box-shadow:0 6px 24px rgba(95,111,255,.3)}
.gl-btn:disabled{opacity:.5;cursor:not-allowed}
.gl-quick{background:rgba(255,255,255,.45);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.3);border-radius:20px;padding:5px 14px;font-size:11px;font-weight:600;color:#6b7280;cursor:pointer;transition:all .3s ease}
.gl-quick:hover{background:rgba(194,77,255,.1);color:#7c3aed;border-color:rgba(194,77,255,.2)}
.gl-src{background:rgba(255,255,255,.35);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.25);border-radius:10px;padding:.6rem .8rem}
.gl-step{background:rgba(95,111,255,.08);border:1px solid rgba(95,111,255,.12);border-radius:8px;padding:3px 10px;font-size:11px;font-weight:600;color:#5f6fff}
.gl-hist-btn{background:rgba(255,255,255,.4);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.25);border-radius:12px;padding:.55rem .75rem;cursor:pointer;transition:all .3s ease;width:100%;text-align:left}
.gl-hist-btn:hover{background:rgba(194,77,255,.06);border-color:rgba(194,77,255,.15)}
</style>
@endpush

@section('content')
<div x-data="ragPage()" class="gl-bg -m-4 sm:-m-6 p-4 sm:p-6">
    <div class="gl-p gl-p1"></div>
    <div class="gl-p gl-p2"></div>

    <div class="max-w-3xl mx-auto relative">

        {{-- Hero --}}
        <div class="gl-hero px-5 py-4 mb-4 gl-a gl-d0" data-gl>
            <div class="relative z-10">
                <div class="flex items-center gap-1.5 mb-1">
                    <div class="w-2 h-2 rounded-full bg-emerald-400 gl-status-pulse"></div>
                    <span class="text-white/70 text-[11px] font-medium tracking-wide uppercase">RAG Engine</span>
                </div>
                <h1 class="text-lg font-bold text-white">📚 Knowledge Base</h1>
            </div>
        </div>

        {{-- Query form --}}
        <div class="gl-card p-5 mb-4 gl-a gl-d1" data-gl>
            <form @submit.prevent="streaming ? askStream() : ask()" class="space-y-3">
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-1">Your Question</label>
                    <textarea x-model="question" rows="2" required maxlength="500"
                              class="gl-input resize-none"
                              placeholder="e.g. What foods should I avoid with insulin resistance?"></textarea>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-1">Disease Context <span class="font-normal text-gray-400">(optional)</span></label>
                    <select x-model="context" class="gl-input">
                        <option value="">Auto-detect</option>
                        <option value="diabetes">Diabetes</option>
                        <option value="pcod">PCOD / PCOS</option>
                    </select>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <button type="submit" :disabled="asking" class="gl-btn">
                        <span x-show="!asking">📚 Ask</span><span x-show="asking">Searching…</span>
                    </button>
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <span class="text-[11px] font-medium text-gray-500">Stream</span>
                        <div class="relative">
                            <input type="checkbox" x-model="streaming" class="sr-only peer">
                            <div class="w-8 h-4 bg-gray-200 rounded-full peer-checked:bg-purple-500 transition-colors"></div>
                            <div class="absolute left-0.5 top-0.5 w-3 h-3 bg-white rounded-full peer-checked:translate-x-4 transition-transform"></div>
                        </div>
                    </label>
                </div>
            </form>

            {{-- Quick questions --}}
            <div class="mt-4 pt-3 border-t border-white/25">
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wide mb-2">Try asking:</p>
                <div class="flex flex-wrap gap-2">
                    <template x-for="q in quickQuestions">
                        <button @click="question=q; streaming ? askStream() : ask()" class="gl-quick" x-text="q"></button>
                    </template>
                </div>
            </div>
        </div>

        {{-- Answer --}}
        <div x-show="answer" x-transition class="gl-card p-5 mb-4 gl-a gl-d2" data-gl>
            <div class="flex items-center gap-2 mb-3">
                <div class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center text-sm">📖</div>
                <h2 class="text-xs font-bold text-gray-800">Answer</h2>
                <span x-show="answer?.confidence" class="ml-auto text-[10px] px-2 py-0.5 rounded-full font-bold"
                      :class="answer?.confidence >= 0.7 ? 'bg-emerald-100 text-emerald-700' : answer?.confidence >= 0.4 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600'"
                      x-text="'Confidence: ' + (answer?.confidence * 100).toFixed(0) + '%'"></span>
            </div>
            <div class="text-sm text-gray-700 leading-relaxed mb-4">
                <p x-text="answer?.answer || streamText"></p>
            </div>

            {{-- Source pages --}}
            <div x-show="answer?.pages?.length" class="border-t border-white/25 pt-3">
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-2">📑 Source Pages (<span x-text="answer?.pages?.length"></span>)</p>
                <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                    <template x-for="(p, i) in answer?.pages ?? []" :key="i">
                        <div class="gl-src">
                            <p class="text-[10px] font-bold text-gray-700 mb-0.5" x-text="'Page ' + (p.page_number || (i+1))"></p>
                            <p class="text-[11px] text-gray-500 line-clamp-3" x-text="p.content"></p>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Reasoning path --}}
            <div x-show="answer?.reasoning_path?.length" class="border-t border-white/25 pt-3 mt-3">
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wide mb-2">🧭 Reasoning Path</p>
                <div class="flex flex-wrap gap-1.5 items-center">
                    <template x-for="(step, i) in answer?.reasoning_path ?? []" :key="i">
                        <div class="flex items-center gap-1.5">
                            <span class="gl-step" x-text="step"></span>
                            <span x-show="i < answer.reasoning_path.length - 1" class="text-gray-300 text-xs">→</span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- History --}}
        <div x-show="history.length > 0" class="gl-card p-5 gl-a gl-d3" data-gl>
            <h2 class="text-xs font-bold text-gray-800 mb-3 flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-purple-100 flex items-center justify-center text-xs">📋</div>
                Previous Queries
            </h2>
            <div class="space-y-2">
                <template x-for="h in history" :key="h.q">
                    <button @click="question=h.q; answer=h.a" class="gl-hist-btn">
                        <p class="text-xs font-medium text-gray-700 truncate" x-text="h.q"></p>
                        <p class="text-[10px] text-gray-400 mt-0.5" x-text="'Confidence: '+(h.a.confidence*100).toFixed(0)+'%'"></p>
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function ragPage() {
    return {
        question: '', context: '', asking: false, answer: null, history: [],
        streaming: true, streamText: '',
        quickQuestions: [
            'What foods spike blood sugar?',
            'How does sleep affect insulin?',
            'Best exercises for PCOS?',
            'How to manage stress with diabetes?',
        ],
        async ask(){
            if(!this.question.trim()) return;
            this.asking = true; this.answer = null; this.streamText = '';
            const payload = { question: this.question };
            if(this.context) payload.disease_context = this.context;
            const r = await api.post('/rag/query', payload);
            if(r.success && r.data){ this.answer = r.data; this.history.unshift({q:this.question, a:r.data}); if(this.history.length > 10) this.history.pop(); }
            else toast(r.message || 'Query failed', 'error');
            this.asking = false;
            this.$nextTick(()=>document.querySelectorAll('[data-gl]').forEach(el=>el.classList.add('gl-v')));
        },
        async askStream(){
            if(!this.question.trim()) return;
            this.asking = true; this.answer = null; this.streamText = '';
            const payload = { question: this.question };
            if(this.context) payload.disease_context = this.context;

            try {
                const token = document.querySelector('meta[name="api-token"]')?.content;
                const res = await fetch('/api/rag/query-stream', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'text/event-stream',
                        ...(token ? {'Authorization': 'Bearer ' + token} : {}),
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify(payload),
                });

                if (!res.ok) { toast('Stream failed', 'error'); this.asking = false; return; }

                const reader = res.body.getReader();
                const decoder = new TextDecoder();
                let meta = {};

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;
                    const lines = decoder.decode(value, { stream: true }).split('\n');
                    for (const line of lines) {
                        if (!line.startsWith('data: ')) continue;
                        const evt = JSON.parse(line.slice(6));
                        if (evt.type === 'meta') {
                            meta = evt;
                            this.answer = { answer: '', confidence: evt.confidence, pages: [], reasoning_path: [] };
                            this.$nextTick(()=>document.querySelectorAll('[data-gl]').forEach(el=>el.classList.add('gl-v')));
                        } else if (evt.type === 'chunk') {
                            this.streamText += evt.text;
                        } else if (evt.type === 'done') {
                            this.answer = { answer: this.streamText, confidence: meta.confidence || 0, pages: [], reasoning_path: [] };
                            this.history.unshift({q: this.question, a: this.answer});
                            if (this.history.length > 10) this.history.pop();
                        }
                    }
                }
            } catch(e) {
                toast('Streaming error: ' + e.message, 'error');
            }
            this.asking = false;
        },
        init(){
            this.$nextTick(()=>document.querySelectorAll('[data-gl]').forEach(el=>el.classList.add('gl-v')));
        }
    };
}
</script>
@endpush
