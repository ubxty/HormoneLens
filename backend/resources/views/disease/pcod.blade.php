@extends('layouts.app')
@section('title','PCOD / PCOS Data — HormoneLens')

@section('content')
<div x-data="pcodPage()" x-init="init()">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-1">PCOD / PCOS Data</h1>
        <p class="text-gray-500 mb-6">Enter your PCOD-specific health indicators.</p>

        <div x-show="loading" class="text-center py-12"><div class="animate-spin w-8 h-8 border-4 border-brand-500 border-t-transparent rounded-full mx-auto"></div></div>

        <form x-show="!loading" @submit.prevent="save()" class="bg-white rounded-xl shadow-sm border p-6 space-y-5">

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cycle Regularity</label>
                    <select x-model="form.cycle_regularity" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none bg-white">
                        <option value="">Select...</option>
                        <option value="regular">Regular</option><option value="irregular">Irregular</option><option value="missed">Missed periods</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Avg Cycle Length (days)</label>
                    <input type="number" x-model="form.avg_cycle_length_days" min="15" max="90"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none"
                           placeholder="e.g. 28">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fatigue Frequency</label>
                    <select x-model="form.fatigue_frequency" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none bg-white">
                        <option value="">Select...</option>
                        <option value="often">Often</option><option value="occasionally">Occasionally</option><option value="rarely">Rarely</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sleep Disturbances</label>
                    <select x-model="form.sleep_disturbances" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none bg-white">
                        <option value="">Select...</option>
                        <option value="often">Often</option><option value="occasionally">Occasionally</option><option value="rarely">Rarely</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sugar Cravings</label>
                <select x-model="form.sugar_cravings" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none bg-white">
                    <option value="">Select...</option>
                    <option value="frequent">Frequent</option><option value="occasional">Occasional</option><option value="rare">Rare</option>
                </select>
            </div>

            <div class="space-y-3">
                <p class="text-sm font-medium text-gray-700">Symptoms — check all that apply:</p>
                <label class="flex items-center gap-2"><input type="checkbox" x-model="form.excess_facial_body_hair" class="rounded text-brand-600"> Excess facial / body hair (hirsutism)</label>
                <label class="flex items-center gap-2"><input type="checkbox" x-model="form.acne_oily_skin" class="rounded text-brand-600"> Acne or oily skin</label>
                <label class="flex items-center gap-2"><input type="checkbox" x-model="form.hair_thinning" class="rounded text-brand-600"> Hair thinning or loss</label>
                <label class="flex items-center gap-2"><input type="checkbox" x-model="form.weight_gain_difficulty_losing" class="rounded text-brand-600"> Weight gain / difficulty losing weight</label>
                <label class="flex items-center gap-2"><input type="checkbox" x-model="form.mood_swings_anxiety" class="rounded text-brand-600"> Mood swings / anxiety</label>
                <label class="flex items-center gap-2"><input type="checkbox" x-model="form.dark_skin_patches" class="rounded text-brand-600"> Dark skin patches (acanthosis nigricans)</label>
                <label class="flex items-center gap-2"><input type="checkbox" x-model="form.insulin_resistance_diagnosed" class="rounded text-brand-600"> Insulin resistance diagnosed</label>
            </div>

            <template x-if="errors.length">
                <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                    <ul class="text-sm text-red-600 space-y-1"><template x-for="e in errors"><li x-text="e"></li></template></ul>
                </div>
            </template>

            <button type="submit" :disabled="saving"
                    class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-lg transition disabled:opacity-50">
                <span x-show="!saving" x-text="exists ? 'Update Data' : 'Save Data'"></span>
                <span x-show="saving">Saving...</span>
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function pcodPage() {
    return {
        loading: true, saving: false, exists: false, errors: [],
        form: {
            cycle_regularity:'', avg_cycle_length_days:'', excess_facial_body_hair:false,
            acne_oily_skin:false, hair_thinning:false, weight_gain_difficulty_losing:false,
            mood_swings_anxiety:false, dark_skin_patches:false, fatigue_frequency:'',
            sleep_disturbances:'', sugar_cravings:'', insulin_resistance_diagnosed:false
        },
        async init(){
            const r = await api.get('/disease/pcod');
            if(r.success && r.data){
                this.exists = true;
                Object.keys(this.form).forEach(k => { if(r.data[k] !== undefined && r.data[k] !== null) this.form[k] = r.data[k]; });
            }
            this.loading = false;
        },
        async save(){
            this.saving = true; this.errors = [];
            const bools = ['excess_facial_body_hair','acne_oily_skin','hair_thinning','weight_gain_difficulty_losing','mood_swings_anxiety','dark_skin_patches','insulin_resistance_diagnosed'];
            const payload = { ...this.form };
            bools.forEach(k => payload[k] = !!payload[k]);
            if(!payload.avg_cycle_length_days) delete payload.avg_cycle_length_days;
            const r = await api.post('/disease/pcod', payload);
            if(r.success) { this.exists = true; toast('PCOD data saved!'); }
            else { this.errors = r.errors ? Object.values(r.errors).flat() : [r.message||'Save failed']; }
            this.saving = false;
        }
    };
}
</script>
@endpush
