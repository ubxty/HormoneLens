@extends('layouts.app')
@section('title','Diabetes Data — HormoneLens')

@section('content')
<div x-data="diabetesPage()" x-init="init()">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-1">Diabetes Data</h1>
        <p class="text-gray-500 mb-6">Enter your diabetes-specific health indicators.</p>

        <div x-show="loading" class="text-center py-12"><div class="animate-spin w-8 h-8 border-4 border-brand-500 border-t-transparent rounded-full mx-auto"></div></div>

        <form x-show="!loading" @submit.prevent="save()" class="bg-white rounded-xl shadow-sm border p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Average Blood Sugar (mg/dL)</label>
                <input type="number" step="1" x-model="form.avg_blood_sugar" min="50" max="500" required
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Family History of Diabetes?</label>
                <div class="flex gap-6">
                    <label class="flex items-center gap-2"><input type="radio" x-model="form.family_history" value="1" class="text-brand-600"> Yes</label>
                    <label class="flex items-center gap-2"><input type="radio" x-model="form.family_history" value="0" class="text-brand-600"> No</label>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Frequent Urination</label>
                    <select x-model="form.frequent_urination" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none bg-white">
                        <option value="">Select...</option>
                        <option value="often">Often</option><option value="occasionally">Occasionally</option><option value="rarely">Rarely</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Excessive Thirst</label>
                    <select x-model="form.excessive_thirst" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none bg-white">
                        <option value="">Select...</option>
                        <option value="often">Often</option><option value="occasionally">Occasionally</option><option value="rarely">Rarely</option>
                    </select>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fatigue Level</label>
                    <select x-model="form.fatigue" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none bg-white">
                        <option value="">Select...</option>
                        <option value="often">Often</option><option value="occasionally">Occasionally</option><option value="rarely">Rarely</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Blurred Vision</label>
                    <select x-model="form.blurred_vision" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none bg-white">
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
                <p class="text-sm font-medium text-gray-700">Check all that apply:</p>
                <label class="flex items-center gap-2"><input type="checkbox" x-model="form.numbness_tingling" class="rounded text-brand-600"> Numbness or tingling in hands/feet</label>
                <label class="flex items-center gap-2"><input type="checkbox" x-model="form.slow_wound_healing" class="rounded text-brand-600"> Slow wound healing</label>
                <label class="flex items-center gap-2"><input type="checkbox" x-model="form.unexplained_weight_loss" class="rounded text-brand-600"> Unexplained weight loss</label>
                <label class="flex items-center gap-2"><input type="checkbox" x-model="form.energy_crashes_after_meals" class="rounded text-brand-600"> Energy crashes after meals</label>
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
function diabetesPage() {
    return {
        loading: true, saving: false, exists: false, errors: [],
        form: {
            avg_blood_sugar:'', family_history:'0', frequent_urination:'', excessive_thirst:'',
            fatigue:'', blurred_vision:'', numbness_tingling:false, slow_wound_healing:false,
            unexplained_weight_loss:false, sugar_cravings:'', energy_crashes_after_meals:false
        },
        async init(){
            const r = await api.get('/disease/diabetes');
            if(r.success && r.data){
                this.exists = true;
                Object.keys(this.form).forEach(k => { if(r.data[k] !== undefined && r.data[k] !== null) this.form[k] = r.data[k]; });
            }
            this.loading = false;
        },
        async save(){
            this.saving = true; this.errors = [];
            const payload = { ...this.form,
                family_history: this.form.family_history == '1' || this.form.family_history === true,
                numbness_tingling: !!this.form.numbness_tingling,
                slow_wound_healing: !!this.form.slow_wound_healing,
                unexplained_weight_loss: !!this.form.unexplained_weight_loss,
                energy_crashes_after_meals: !!this.form.energy_crashes_after_meals,
            };
            const r = await api.post('/disease/diabetes', payload);
            if(r.success) { this.exists = true; toast('Diabetes data saved!'); }
            else { this.errors = r.errors ? Object.values(r.errors).flat() : [r.message||'Save failed']; }
            this.saving = false;
        }
    };
}
</script>
@endpush
