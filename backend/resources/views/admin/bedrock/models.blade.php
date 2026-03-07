@extends('layouts.admin')
@section('heading','AI Models')

@section('content')
<div x-data="bedrockModels()" x-init="init()" id="bedrock-models">

    <div x-show="loading" class="text-center py-16">
        <div class="inline-block w-8 h-8 border-[3px] border-purple-400 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div x-show="!loading" class="space-y-5">
        {{-- Header --}}
        <div class="flex items-center justify-between adm-a adm-d0" data-adm>
            <p class="text-xs font-bold text-gray-400" x-text="models.length + ' models available'"></p>
            <a href="{{ route('admin.bedrock') }}" class="adm-badge bg-white/60 hover:bg-white/80 text-gray-600 cursor-pointer transition px-3 py-1.5">← Back</a>
        </div>

        {{-- Model cards --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="(m, i) in models" :key="m.model_id || i">
                <div class="adm-card relative p-5 adm-a" :class="'adm-d' + Math.min(i, 5)" data-adm :data-testid="'model-card-' + i">
                    <div class="flex items-start justify-between mb-3">
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-gray-700 truncate" x-text="m.model_name || m.model_id || 'Unknown'"></h3>
                            <p class="text-[10px] text-gray-400 mt-0.5" x-text="m.provider || m.provider_name || ''"></p>
                        </div>
                        <span class="adm-badge text-[10px]"
                              :class="m.model_status === 'ACTIVE' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                              x-text="m.model_status || 'N/A'"></span>
                    </div>
                    <div class="space-y-1 text-[10px] text-gray-500">
                        <p x-show="m.model_id"><span class="font-bold">ID:</span> <span x-text="m.model_id" class="break-all"></span></p>
                        <p x-show="m.input_modalities"><span class="font-bold">Input:</span> <span x-text="Array.isArray(m.input_modalities) ? m.input_modalities.join(', ') : m.input_modalities"></span></p>
                        <p x-show="m.output_modalities"><span class="font-bold">Output:</span> <span x-text="Array.isArray(m.output_modalities) ? m.output_modalities.join(', ') : m.output_modalities"></span></p>
                    </div>
                </div>
            </template>
        </div>

        <div x-show="models.length === 0" class="adm-card p-10 text-center adm-a adm-d0" data-adm>
            <p class="text-sm text-gray-400">No models found. Check your AWS credentials and region.</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function bedrockModels() {
    return {
        loading: true,
        models: [],

        async init() {
            try {
                const res = await api.get('/admin/bedrock/models');
                this.models = Array.isArray(res) ? res : (res.models || res.data || []);
            } catch (e) {
                toast('Failed to load models', 'error');
            }
            this.loading = false;
            this.$nextTick(() => admAnimate());
        }
    }
}
</script>
@endpush
