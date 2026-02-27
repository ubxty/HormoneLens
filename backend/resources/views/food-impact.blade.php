@extends('layouts.app')
@section('title','Food Impact — HormoneLens')

@section('content')
<div x-data="foodImpactPage()" class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-1">Food Impact Analyzer</h1>
    <p class="text-gray-500 mb-6">See how specific foods affect your metabolic health.</p>

    <div class="grid md:grid-cols-2 gap-6">
        {{-- Input --}}
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <h2 class="font-semibold text-gray-800 mb-4">Analyze a Food</h2>
            <form @submit.prevent="analyze()" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Food Item</label>
                    <input type="text" x-model="form.food_item" required maxlength="255"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                           placeholder="e.g. Gulab Jamun, White Rice, Samosa">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity (optional)</label>
                    <input type="text" x-model="form.quantity" maxlength="100"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                           placeholder="e.g. 2 pieces, 1 bowl, 200g">
                </div>
                <button type="submit" :disabled="analyzing"
                        class="w-full py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-lg transition disabled:opacity-50">
                    <span x-show="!analyzing">🔍 Analyze Impact</span>
                    <span x-show="analyzing">Analyzing...</span>
                </button>
            </form>

            {{-- Quick picks --}}
            <div class="mt-4 pt-4 border-t">
                <p class="text-xs text-gray-400 mb-2">Quick picks:</p>
                <div class="flex flex-wrap gap-2">
                    <template x-for="f in quickPicks">
                        <button @click="form.food_item=f; form.quantity='1 serving'; analyze()"
                                class="px-3 py-1 text-xs bg-gray-100 hover:bg-brand-50 text-gray-600 rounded-full transition" x-text="f"></button>
                    </template>
                </div>
            </div>
        </div>

        {{-- Result --}}
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <h2 class="font-semibold text-gray-800 mb-4">Impact Result</h2>
            <div x-show="!result && !analyzing" class="text-center py-12 text-sm text-gray-400">
                <div class="text-4xl mb-2">🍽️</div>
                Enter a food item to see its impact.
            </div>
            <div x-show="analyzing" class="text-center py-12"><div class="animate-spin w-6 h-6 border-4 border-brand-500 border-t-transparent rounded-full mx-auto"></div></div>
            <div x-show="result && !analyzing" class="space-y-4">
                <div class="p-4 rounded-lg" :class="result?.risk_change > 0 ? 'bg-red-50' : 'bg-emerald-50'">
                    <p class="text-sm text-gray-500 mb-1">Risk Change</p>
                    <p class="text-3xl font-bold" :class="result?.risk_change > 0 ? 'text-red-600' : 'text-emerald-600'"
                       x-text="(result?.risk_change > 0 ? '+' : '') + result?.risk_change?.toFixed(2)"></p>
                    <div class="flex gap-2 mt-2 text-xs">
                        <span class="px-2 py-0.5 rounded-full capitalize" :class="catColor(result?.risk_category_before)" x-text="result?.risk_category_before"></span>
                        <span class="text-gray-400">→</span>
                        <span class="px-2 py-0.5 rounded-full capitalize" :class="catColor(result?.risk_category_after)" x-text="result?.risk_category_after"></span>
                    </div>
                </div>

                <div x-show="result?.rag_explanation" class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-700 mb-1">💡 AI Explanation</p>
                    <p class="text-sm text-gray-600" x-text="result?.rag_explanation"></p>
                    <p x-show="result?.rag_confidence" class="mt-1 text-xs text-gray-400" x-text="'Confidence: '+(result?.rag_confidence*100).toFixed(0)+'%'"></p>
                </div>

                <div x-show="result?.alerts?.length" class="space-y-2">
                    <p class="text-sm font-medium text-gray-700">⚠️ Alerts</p>
                    <template x-for="a in result?.alerts??[]" :key="a.id">
                        <div class="p-2 rounded text-xs" :class="a.severity==='critical'?'bg-red-100 text-red-700':'bg-amber-100 text-amber-700'">
                            <strong x-text="a.title"></strong>: <span x-text="a.message"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function foodImpactPage() {
    return {
        analyzing: false, result: null,
        form: { food_item:'', quantity:'' },
        quickPicks: ['White Rice','Gulab Jamun','Samosa','Paneer Tikka','Dal Khichdi','Green Salad','Roti','Biryani'],
        catColor(c){ return c==='high'?'bg-red-100 text-red-700':c==='medium'?'bg-amber-100 text-amber-700':'bg-emerald-100 text-emerald-700'; },
        async analyze(){
            if(!this.form.food_item) return;
            this.analyzing = true; this.result = null;
            const r = await api.post('/food-impact', this.form);
            if(r.success) this.result = r.data;
            else toast(r.message || 'Analysis failed. Generate your Digital Twin first.', 'error');
            this.analyzing = false;
        }
    };
}
</script>
@endpush
