@extends('layouts.app')
@section('title','Health Profile — HormoneLens')

@section('content')
<div x-data="healthProfilePage()" x-init="init()">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-1">Health Profile</h1>
        <p class="text-gray-500 mb-6">Your baseline metabolic health information.</p>

        <div x-show="loading" class="text-center py-12"><div class="animate-spin w-8 h-8 border-4 border-brand-500 border-t-transparent rounded-full mx-auto"></div></div>

        <form x-show="!loading" @submit.prevent="save()" class="bg-white rounded-xl shadow-sm border p-6 space-y-5">
            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                    <input type="number" step="0.1" x-model="form.weight" min="20" max="300" required
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Height (cm)</label>
                    <input type="number" step="0.1" x-model="form.height" min="50" max="250" required
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Avg. Sleep (hours/day)</label>
                    <input type="number" step="0.5" x-model="form.avg_sleep_hours" min="0" max="24" required
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Water Intake (liters/day)</label>
                    <input type="number" step="0.1" x-model="form.water_intake" min="0" max="20" required
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stress Level</label>
                    <select x-model="form.stress_level" required
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none bg-white">
                        <option value="">Select...</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Physical Activity</label>
                    <select x-model="form.physical_activity" required
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none bg-white">
                        <option value="">Select...</option>
                        <option value="sedentary">Sedentary</option>
                        <option value="moderate">Moderate</option>
                        <option value="active">Active</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Primary Condition</label>
                <select x-model="form.disease_type" required
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none bg-white">
                    <option value="">Select...</option>
                    @foreach(\App\Models\Disease::active()->ordered()->get() as $d)
                    <option value="{{ $d->slug }}">{{ $d->icon }} {{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Eating Habits (optional)</label>
                <textarea x-model="form.eating_habits" rows="3" maxlength="1000"
                          class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none"
                          placeholder="Describe your typical diet, meal timings, preferences..."></textarea>
            </div>

            {{-- Validation errors --}}
            <template x-if="errors.length">
                <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                    <ul class="text-sm text-red-600 space-y-1">
                        <template x-for="e in errors">
                            <li x-text="e"></li>
                        </template>
                    </ul>
                </div>
            </template>

            <div class="flex items-center gap-3">
                <button type="submit" :disabled="saving"
                        class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-lg transition disabled:opacity-50">
                    <span x-show="!saving" x-text="exists ? 'Update Profile' : 'Save Profile'"></span>
                    <span x-show="saving">Saving...</span>
                </button>
                <span x-show="exists" class="text-sm text-emerald-600">✓ Profile exists</span>
            </div>

            {{-- BMI --}}
            <div x-show="form.weight && form.height" class="p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600">
                    <strong>BMI:</strong>
                    <span x-text="(form.weight / ((form.height/100)**2)).toFixed(1)"></span>
                    <span class="ml-2 text-xs px-2 py-0.5 rounded-full"
                          :class="bmiCategory.color" x-text="bmiCategory.label"></span>
                </p>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function healthProfilePage() {
    return {
        loading: true, saving: false, exists: false, errors: [],
        form: { weight:'', height:'', avg_sleep_hours:'', stress_level:'', physical_activity:'', eating_habits:'', water_intake:'', disease_type:'' },
        get bmiCategory(){
            if(!this.form.weight||!this.form.height) return {label:'',color:''};
            const b=this.form.weight/((this.form.height/100)**2);
            if(b<18.5) return {label:'Underweight',color:'bg-blue-100 text-blue-700'};
            if(b<25) return {label:'Normal',color:'bg-emerald-100 text-emerald-700'};
            if(b<30) return {label:'Overweight',color:'bg-amber-100 text-amber-700'};
            return {label:'Obese',color:'bg-red-100 text-red-700'};
        },
        async init(){
            const r = await api.get('/health-profile');
            if(r.success && r.data){
                this.exists = true;
                Object.keys(this.form).forEach(k => { if(r.data[k] !== undefined && r.data[k] !== null) this.form[k] = r.data[k]; });
            }
            this.loading = false;
        },
        async save(){
            this.saving = true; this.errors = [];
            const method = this.exists ? 'put' : 'post';
            const r = await api[method]('/health-profile', this.form);
            if(r.success) { this.exists = true; toast('Health profile saved!'); }
            else {
                if(r.errors) this.errors = Object.values(r.errors).flat();
                else this.errors = [r.message || 'Save failed'];
            }
            this.saving = false;
        }
    };
}
</script>
@endpush
