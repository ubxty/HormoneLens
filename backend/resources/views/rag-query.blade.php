@extends('layouts.app')
@section('title','Knowledge Base — HormoneLens')

@section('content')
<div x-data="ragPage()" class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-1">Knowledge Base</h1>
    <p class="text-gray-500 mb-6">Ask health questions powered by our RAG engine.</p>

    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <form @submit.prevent="ask()" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Your Question</label>
                <textarea x-model="question" rows="3" required maxlength="500"
                          class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                          placeholder="e.g. What foods should I avoid with insulin resistance?"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Disease Context (optional)</label>
                <select x-model="context" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 outline-none bg-white">
                    <option value="">Auto-detect</option>
                    <option value="diabetes">Diabetes</option>
                    <option value="pcod">PCOD / PCOS</option>
                </select>
            </div>
            <button type="submit" :disabled="asking"
                    class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-lg transition disabled:opacity-50">
                <span x-show="!asking">📚 Ask</span><span x-show="asking">Searching...</span>
            </button>
        </form>

        {{-- Quick questions --}}
        <div class="mt-4 pt-4 border-t">
            <p class="text-xs text-gray-400 mb-2">Try asking:</p>
            <div class="flex flex-wrap gap-2">
                <template x-for="q in quickQuestions">
                    <button @click="question=q; ask()"
                            class="px-3 py-1 text-xs bg-gray-100 hover:bg-brand-50 text-gray-600 rounded-full transition" x-text="q"></button>
                </template>
            </div>
        </div>
    </div>

    {{-- Answer --}}
    <div x-show="answer" class="bg-white rounded-xl shadow-sm border p-6 mb-6" x-transition>
        <div class="flex items-center gap-2 mb-3">
            <span class="text-lg">📖</span>
            <h2 class="font-semibold text-gray-800">Answer</h2>
            <span x-show="answer?.confidence" class="ml-auto text-xs px-2 py-0.5 rounded-full"
                  :class="answer?.confidence >= 0.7 ? 'bg-emerald-100 text-emerald-700' : answer?.confidence >= 0.4 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600'"
                  x-text="'Confidence: ' + (answer?.confidence * 100).toFixed(0) + '%'"></span>
        </div>
        <div class="prose prose-sm max-w-none text-gray-700 mb-4">
            <p x-text="answer?.answer"></p>
        </div>

        {{-- Source pages --}}
        <div x-show="answer?.pages?.length" class="border-t pt-4">
            <p class="text-xs font-medium text-gray-500 mb-2">📑 Source Pages (<span x-text="answer?.pages?.length"></span>)</p>
            <div class="space-y-2 max-h-60 overflow-y-auto">
                <template x-for="(p, i) in answer?.pages ?? []" :key="i">
                    <div class="p-3 bg-gray-50 rounded-lg text-xs">
                        <p class="font-medium text-gray-700 mb-1" x-text="'Page ' + (p.page_number || (i+1))"></p>
                        <p class="text-gray-500 line-clamp-3" x-text="p.content"></p>
                    </div>
                </template>
            </div>
        </div>

        {{-- Reasoning path --}}
        <div x-show="answer?.reasoning_path?.length" class="border-t pt-4 mt-4">
            <p class="text-xs font-medium text-gray-500 mb-2">🧭 Reasoning Path</p>
            <div class="flex flex-wrap gap-1 items-center">
                <template x-for="(step, i) in answer?.reasoning_path ?? []" :key="i">
                    <div class="flex items-center gap-1">
                        <span class="px-2 py-0.5 bg-brand-50 text-brand-700 text-xs rounded" x-text="step"></span>
                        <span x-show="i < answer.reasoning_path.length - 1" class="text-gray-300 text-xs">→</span>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- History --}}
    <div x-show="history.length > 0" class="bg-white rounded-xl shadow-sm border p-5">
        <h2 class="font-semibold text-gray-800 mb-4">Previous Queries</h2>
        <div class="space-y-2">
            <template x-for="h in history" :key="h.q">
                <button @click="question=h.q; answer=h.a" class="w-full text-left p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                    <p class="text-sm text-gray-700 truncate" x-text="h.q"></p>
                    <p class="text-xs text-gray-400 mt-0.5" x-text="'Confidence: '+(h.a.confidence*100).toFixed(0)+'%'"></p>
                </button>
            </template>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function ragPage() {
    return {
        question: '', context: '', asking: false, answer: null, history: [],
        quickQuestions: [
            'What foods spike blood sugar?',
            'How does sleep affect insulin?',
            'Best exercises for PCOS?',
            'How to manage stress with diabetes?',
        ],
        async ask(){
            if(!this.question.trim()) return;
            this.asking = true; this.answer = null;
            const payload = { question: this.question };
            if(this.context) payload.disease_context = this.context;
            const r = await api.post('/rag/query', payload);
            if(r.success && r.data){ this.answer = r.data; this.history.unshift({q:this.question, a:r.data}); if(this.history.length > 10) this.history.pop(); }
            else toast(r.message || 'Query failed', 'error');
            this.asking = false;
        }
    };
}
</script>
@endpush
