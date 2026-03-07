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
            <div class="flex gap-2">
                <button @click="refresh()" :disabled="refreshing" class="adm-btn text-xs disabled:opacity-50" data-testid="refresh-models-btn">
                    <span x-show="!refreshing">🔄 Refresh</span>
                    <span x-show="refreshing">Loading…</span>
                </button>
                <a href="{{ route('admin.bedrock') }}" class="adm-badge bg-white/60 hover:bg-white/80 text-gray-600 cursor-pointer transition px-3 py-1.5">← Back</a>
            </div>
        </div>

        {{-- Error display --}}
        <div x-show="error" class="adm-card p-5 border-l-4 border-red-400 adm-a adm-d0" data-adm data-testid="models-error">
            <div class="flex items-start gap-3">
                <div class="text-red-500 text-lg mt-0.5">⚠</div>
                <div>
                    <h4 class="text-sm font-bold text-red-700">Failed to load models</h4>
                    <p class="text-xs text-red-600 mt-1" x-text="error"></p>
                    <p class="text-[10px] text-gray-400 mt-2">Troubleshooting tips:</p>
                    <ul class="text-[10px] text-gray-500 mt-1 space-y-0.5 list-disc pl-4">
                        <li>Verify your API credentials on the <a href="{{ route('admin.bedrock') }}" class="text-purple-500 hover:underline">AI Dashboard</a></li>
                        <li>Use the <strong>Test Connection</strong> button to check connectivity</li>
                        <li>Make sure the selected AWS region has Bedrock access enabled</li>
                        <li>If using a bearer token, ensure it hasn't expired</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Search/filter --}}
        <div x-show="models.length > 0" class="adm-a adm-d0" data-adm>
            <input type="text" x-model="search" placeholder="Filter models…" class="adm-input w-full text-xs" data-testid="model-search">
        </div>

        {{-- Model cards --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="(m, i) in filteredModels" :key="m.model_id || i">
                <div class="adm-card relative p-5 adm-a" :class="'adm-d' + Math.min(i, 5)" data-adm :data-testid="'model-card-' + i">
                    <div class="flex items-start justify-between mb-3">
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-gray-700 truncate" x-text="m.name || m.model_name || m.model_id || 'Unknown'"></h3>
                            <p class="text-[10px] text-gray-400 mt-0.5" x-text="m.provider || m.provider_name || ''"></p>
                        </div>
                        <span class="adm-badge text-[10px]"
                              :class="m.is_active !== false && (m.model_status === 'ACTIVE' || !m.model_status) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                              x-text="m.is_active !== false ? 'ACTIVE' : 'INACTIVE'"></span>
                    </div>
                    <div class="space-y-1 text-[10px] text-gray-500">
                        <p x-show="m.model_id"><span class="font-bold">ID:</span> <span x-text="m.model_id" class="break-all font-mono"></span></p>
                        <p x-show="m.context_window"><span class="font-bold">Context:</span> <span x-text="(m.context_window || 0).toLocaleString() + ' tokens'"></span></p>
                        <p x-show="m.max_tokens"><span class="font-bold">Max output:</span> <span x-text="(m.max_tokens || 0).toLocaleString() + ' tokens'"></span></p>
                        <p x-show="m.capabilities"><span class="font-bold">Capabilities:</span> <span x-text="Array.isArray(m.capabilities) ? m.capabilities.join(', ') : (m.capabilities || '')"></span></p>
                        <p x-show="m.input_modalities"><span class="font-bold">Input:</span> <span x-text="Array.isArray(m.input_modalities) ? m.input_modalities.join(', ') : m.input_modalities"></span></p>
                        <p x-show="m.output_modalities"><span class="font-bold">Output:</span> <span x-text="Array.isArray(m.output_modalities) ? m.output_modalities.join(', ') : m.output_modalities"></span></p>
                    </div>

                    {{-- Quick-set alias buttons --}}
                    <div class="flex gap-1 mt-3 pt-2 border-t border-gray-100">
                        <button @click="setAlias('default', m.model_id)" class="text-[9px] px-2 py-1 rounded bg-purple-50 text-purple-600 hover:bg-purple-100 transition" :data-testid="'set-default-' + i">Set Default</button>
                        <button @click="setAlias('smart', m.model_id)" class="text-[9px] px-2 py-1 rounded bg-blue-50 text-blue-600 hover:bg-blue-100 transition" :data-testid="'set-smart-' + i">Set Smart</button>
                        <button @click="setAlias('fast', m.model_id)" class="text-[9px] px-2 py-1 rounded bg-green-50 text-green-600 hover:bg-green-100 transition" :data-testid="'set-fast-' + i">Set Fast</button>
                    </div>
                </div>
            </template>
        </div>

        <div x-show="models.length > 0 && filteredModels.length === 0" class="adm-card p-10 text-center adm-a adm-d0" data-adm>
            <p class="text-sm text-gray-400">No models match your search.</p>
        </div>

        <div x-show="models.length === 0 && !error" class="adm-card p-10 text-center adm-a adm-d0" data-adm>
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
        refreshing: false,
        models: [],
        search: '',
        error: '',

        get filteredModels() {
            if (!this.search) return this.models;
            const q = this.search.toLowerCase();
            return this.models.filter(m =>
                (m.model_id || '').toLowerCase().includes(q) ||
                (m.name || m.model_name || '').toLowerCase().includes(q) ||
                (m.provider || m.provider_name || '').toLowerCase().includes(q)
            );
        },

        async init() {
            await this.loadModels();
            this.loading = false;
            this.$nextTick(() => admAnimate());
        },

        async loadModels() {
            this.error = '';
            try {
                const res = await api.get('/admin/bedrock/models');
                if (res.success) {
                    this.models = res.models || [];
                } else {
                    this.error = res.error || 'Failed to load models';
                    this.models = [];
                }
            } catch (e) {
                this.error = e.response?.data?.error || e.response?.data?.message || 'Network error — could not reach the server';
                this.models = [];
            }
        },

        async refresh() {
            this.refreshing = true;
            await this.loadModels();
            this.refreshing = false;
            if (!this.error) toast('Models refreshed');
        },

        async setAlias(alias, modelId) {
            try {
                const payload = {};
                payload[alias] = modelId;
                await api.put('/admin/bedrock/model-aliases', payload);
                toast(`${alias.charAt(0).toUpperCase() + alias.slice(1)} model set to ${modelId}`);
            } catch (e) {
                toast('Failed to set model alias', 'error');
            }
        }
    }
}
</script>
@endpush
