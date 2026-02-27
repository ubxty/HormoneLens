@extends('layouts.app')
@section('title', $disease->name . ' Data — HormoneLens')

@section('content')
<div x-data="diseasePage()" x-init="init()">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800 mb-1">{{ $disease->icon }} {{ $disease->name }} Data</h1>
        <p class="text-gray-500 mb-6">{{ $disease->description ?? 'Enter your health indicators for ' . $disease->name . '.' }}</p>

        <div x-show="loading" class="text-center py-12"><div class="animate-spin w-8 h-8 border-4 border-brand-500 border-t-transparent rounded-full mx-auto"></div></div>

        <form x-show="!loading" @submit.prevent="save()" class="bg-white rounded-xl shadow-sm border p-6 space-y-6">

            @php
                $grouped = $disease->fields->groupBy('category');
            @endphp

            @foreach($grouped as $category => $fields)
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 border-b pb-1">{{ ucfirst($category) }}</h3>

                    @php
                        // Split into pairs for grid layout
                        $fieldArr = $fields->values();
                        $booleanFields = $fieldArr->filter(fn($f) => $f->field_type === 'boolean');
                        $otherFields = $fieldArr->filter(fn($f) => $f->field_type !== 'boolean');
                    @endphp

                    {{-- Non-boolean fields in 2-column grid --}}
                    @if($otherFields->count())
                    <div class="grid sm:grid-cols-2 gap-5 mb-4">
                        @foreach($otherFields as $field)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $field->label }}</label>

                                @if($field->field_type === 'number')
                                    <input type="number"
                                           step="{{ ($field->validation['step'] ?? null) ?: 'any' }}"
                                           @if(isset($field->validation['min'])) min="{{ $field->validation['min'] }}" @endif
                                           @if(isset($field->validation['max'])) max="{{ $field->validation['max'] }}" @endif
                                           x-model="form.field_values.{{ $field->slug }}"
                                           {{ $field->is_required ? 'required' : '' }}
                                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">

                                @elseif($field->field_type === 'select' && is_array($field->options))
                                    <select x-model="form.field_values.{{ $field->slug }}"
                                            {{ $field->is_required ? 'required' : '' }}
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none bg-white">
                                        <option value="">Select...</option>
                                        @foreach($field->options as $opt)
                                            <option value="{{ $opt }}">{{ ucfirst(str_replace('_', ' ', $opt)) }}</option>
                                        @endforeach
                                    </select>

                                @elseif($field->field_type === 'text')
                                    <input type="text"
                                           x-model="form.field_values.{{ $field->slug }}"
                                           {{ $field->is_required ? 'required' : '' }}
                                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @endif

                    {{-- Boolean fields as checkboxes --}}
                    @if($booleanFields->count())
                    <div class="space-y-3">
                        @foreach($booleanFields as $field)
                            <label class="flex items-center gap-2">
                                <input type="checkbox"
                                       x-model="form.field_values.{{ $field->slug }}"
                                       class="rounded text-brand-600">
                                {{ $field->label }}
                            </label>
                        @endforeach
                    </div>
                    @endif
                </div>
            @endforeach

            <template x-if="errors.length">
                <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                    <ul class="text-sm text-red-600 space-y-1">
                        <template x-for="e in errors"><li x-text="e"></li></template>
                    </ul>
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
function diseasePage() {
    const slug = @json($disease->slug);
    const fieldDefs = @json($disease->fields->keyBy('slug')->map(fn($f) => ['type' => $f->field_type, 'slug' => $f->slug]));

    // Build default form values from field definitions
    const defaults = {};
    @foreach($disease->fields as $field)
        @if($field->field_type === 'boolean')
            defaults['{{ $field->slug }}'] = false;
        @elseif($field->field_type === 'number')
            defaults['{{ $field->slug }}'] = '';
        @else
            defaults['{{ $field->slug }}'] = '';
        @endif
    @endforeach

    return {
        loading: true, saving: false, exists: false, errors: [],
        form: { field_values: { ...defaults } },

        async init() {
            const r = await api.get('/diseases/' + slug);
            if (r.success && r.data) {
                // r.data.disease has field definitions, r.data.user_data has saved values
                const saved = r.data.user_data?.field_values || r.data.user_data || {};
                Object.keys(defaults).forEach(k => {
                    if (saved[k] !== undefined && saved[k] !== null) {
                        this.form.field_values[k] = saved[k];
                    }
                });
                if (Object.keys(saved).length > 0) this.exists = true;
            }
            this.loading = false;
        },

        async save() {
            this.saving = true;
            this.errors = [];

            // Cast values based on field type
            const payload = { field_values: {} };
            Object.entries(this.form.field_values).forEach(([key, val]) => {
                const def = fieldDefs[key];
                if (!def) { payload.field_values[key] = val; return; }
                if (def.type === 'boolean') {
                    payload.field_values[key] = val === true || val === 'true' || val == 1;
                } else if (def.type === 'number') {
                    payload.field_values[key] = val !== '' ? parseFloat(val) : null;
                } else {
                    payload.field_values[key] = val;
                }
            });

            const r = await api.post('/diseases/' + slug, payload);
            if (r.success) {
                this.exists = true;
                toast('{{ $disease->name }} data saved!');
            } else {
                this.errors = r.errors ? Object.values(r.errors).flat() : [r.message || 'Save failed'];
            }
            this.saving = false;
        }
    };
}
</script>
@endpush
