@extends('layouts.admin')
@section('heading','AI Dashboard')

@section('content')
<div x-data="bedrockDashboard()" x-init="init()" id="bedrock-dashboard">

    <div x-show="loading" class="text-center py-16">
        <div class="inline-block w-8 h-8 border-[3px] border-purple-400 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div x-show="!loading" class="space-y-5">

        {{-- Status card --}}
        <div class="adm-card relative p-5 adm-a adm-d0" data-adm>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-purple-400/20 to-pink-400/20 flex items-center justify-center text-xs">🤖</div>
                    Bedrock Status
                </h3>
                <button @click="testConnection()" :disabled="testing" class="adm-btn disabled:opacity-50" id="test-connection-btn" data-testid="test-connection-btn">
                    <span x-show="!testing">🔌 Test Connection</span>
                    <span x-show="testing" class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        Testing…
                    </span>
                </button>
            </div>
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full" :class="status.configured ? 'bg-blue-400 shadow-lg shadow-blue-400/50' : 'bg-gray-300'"></div>
                    <span class="text-xs font-bold" :class="status.configured ? 'text-blue-600' : 'text-gray-400'" x-text="status.configured ? 'Configured' : 'Not configured'" id="config-status"></span>

                    <template x-if="connectionTested">
                        <div class="flex items-center gap-2 ml-3">
                            <div class="w-3 h-3 rounded-full" :class="connectionOk ? 'bg-green-400 shadow-lg shadow-green-400/50' : 'bg-red-400 shadow-lg shadow-red-400/50'"></div>
                            <span class="text-xs font-bold" :class="connectionOk ? 'text-green-600' : 'text-red-600'" x-text="connectionOk ? 'Connected' : 'Disconnected'" id="connection-status"></span>
                        </div>
                    </template>
                </div>

                {{-- Test result details --}}
                <div x-show="testResult" class="mt-1 p-3 rounded-lg text-xs" :class="connectionOk ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'" id="test-result-box" data-testid="test-result-box">
                    <p class="font-bold" x-text="connectionOk ? '✓ Connection Successful' : '✗ Connection Failed'"></p>
                    <p class="mt-0.5" x-text="testResult"></p>
                    <p x-show="testResponseTime" class="mt-0.5 text-[10px] opacity-70" x-text="'Response time: ' + testResponseTime + 'ms'"></p>
                </div>
            </div>
        </div>

        {{-- AWS Credentials — inline edit --}}
        <div class="adm-card relative p-5 adm-a adm-d1" data-adm>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-purple-400/20 to-pink-400/20 flex items-center justify-center text-xs">🔑</div>
                    AWS Credentials
                </h3>
                {{-- Saved indicator --}}
                <span x-show="creds.has_keys" class="flex items-center gap-1.5 text-[10px] font-semibold text-green-600">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Saved
                </span>
            </div>

            <form @submit.prevent="saveCredentials()" class="space-y-4" id="credentials-form">

                {{-- Auth mode tab --}}
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Auth Method</label>
                    <div class="flex rounded-lg border border-gray-200 bg-gray-50 p-0.5 gap-0.5" id="auth-mode-toggle">
                        <button type="button"
                            @click="credForm.auth_mode = 'bearer'"
                            :class="credForm.auth_mode === 'bearer' ? 'bg-white shadow text-violet-600 font-bold' : 'text-gray-400 hover:text-gray-600'"
                            class="flex-1 py-1.5 text-xs rounded-md transition-all"
                            data-testid="mode-bearer">
                            🔐 Bearer Token
                        </button>
                        <button type="button"
                            @click="credForm.auth_mode = 'keys'"
                            :class="credForm.auth_mode === 'keys' ? 'bg-white shadow text-blue-600 font-bold' : 'text-gray-400 hover:text-gray-600'"
                            class="flex-1 py-1.5 text-xs rounded-md transition-all"
                            data-testid="mode-keys">
                            🗝 Access Keys
                        </button>
                    </div>
                </div>

                {{-- Bearer Token fields --}}
                <div x-show="credForm.auth_mode === 'bearer'" x-transition class="space-y-3">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Bearer Token</label>
                        <div class="relative">
                            <input :type="showBearer ? 'text' : 'password'"
                                x-model="credForm.bearer_token"
                                class="adm-input w-full font-mono pr-10"
                                placeholder="ABSKYmV..."
                                id="bearer-token-input"
                                data-testid="bearer-token-input">
                            <button type="button" @click="showBearer = !showBearer"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs">
                                <span x-text="showBearer ? '🙈' : '👁'"></span>
                            </button>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">
                            Copy from AWS Bedrock Console:
                            <code class="bg-gray-100 px-1 rounded">export AWS_BEARER_TOKEN_BEDROCK=ABSK...</code>
                        </p>
                    </div>
                </div>

                {{-- Access Key fields --}}
                <div x-show="credForm.auth_mode === 'keys'" x-transition class="space-y-3">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">AWS Access Key ID</label>
                        <input type="text" x-model="credForm.aws_key"
                            class="adm-input w-full font-mono" placeholder="AKIA..."
                            id="aws-key-input" data-testid="aws-key-input">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">AWS Secret Access Key</label>
                        <div class="relative">
                            <input :type="showSecret ? 'text' : 'password'"
                                x-model="credForm.aws_secret"
                                class="adm-input w-full font-mono pr-10"
                                placeholder="wJalrXUtnFEMI..."
                                id="aws-secret-input" data-testid="aws-secret-input">
                            <button type="button" @click="showSecret = !showSecret"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs">
                                <span x-text="showSecret ? '🙈' : '👁'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Region --}}
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">AWS Region</label>
                    <select x-model="credForm.region" class="adm-input w-full" id="aws-region-input" data-testid="aws-region-input">
                        <option value="us-east-1">US East (N. Virginia) — us-east-1</option>
                        <option value="us-west-2">US West (Oregon) — us-west-2</option>
                        <option value="eu-west-1">Europe (Ireland) — eu-west-1</option>
                        <option value="eu-central-1">Europe (Frankfurt) — eu-central-1</option>
                        <option value="ap-southeast-1">Asia Pacific (Singapore) — ap-southeast-1</option>
                        <option value="ap-northeast-1">Asia Pacific (Tokyo) — ap-northeast-1</option>
                        <option value="ap-south-1">Asia Pacific (Mumbai) — ap-south-1</option>
                    </select>
                </div>

                {{-- Save button + status --}}
                <div class="flex items-center gap-3 pt-1">
                    <button type="submit" :disabled="savingCreds" class="adm-btn disabled:opacity-50" id="save-credentials-btn" data-testid="save-credentials-btn">
                        <span x-show="!savingCreds">💾 Save Credentials</span>
                        <span x-show="savingCreds" class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                            Saving…
                        </span>
                    </button>
                    <span x-show="savedAt" class="text-[10px] text-green-600 font-semibold" x-text="'Saved at ' + savedAt"></span>
                </div>
            </form>
        </div>

        {{-- Settings grid --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="(groupSettings, group) in status.settings" :key="group">
                <div class="adm-card relative p-5 adm-a adm-d1" data-adm>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3" x-text="group"></h3>
                    <div class="space-y-2">
                        <template x-for="s in groupSettings" :key="s.key">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-600" x-text="s.description || s.key"></span>
                                <template x-if="s.type === 'boolean'">
                                    <button @click="toggleSetting(s)"
                                        class="w-8 h-5 rounded-full transition-colors relative"
                                        :class="s.value === 'true' || s.value === true ? 'bg-purple-400' : 'bg-gray-300'"
                                        :data-testid="'toggle-' + s.key">
                                        <div class="w-3.5 h-3.5 bg-white rounded-full absolute top-[3px] transition-transform"
                                             :class="s.value === 'true' || s.value === true ? 'translate-x-[14px]' : 'translate-x-[3px]'"></div>
                                    </button>
                                </template>
                                <template x-if="s.type !== 'boolean'">
                                    <span class="adm-badge bg-purple-50 text-purple-700 text-[10px]" x-text="s.value"></span>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        {{-- Model Aliases --}}
        <div class="adm-card relative p-5 adm-a adm-d2" data-adm>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-purple-400/20 to-pink-400/20 flex items-center justify-center text-xs">🧬</div>
                    Model Configuration
                </h3>
                <button @click="saveModelAliases()" :disabled="savingAliases" class="adm-btn disabled:opacity-50" id="save-aliases-btn" data-testid="save-aliases-btn">
                    <span x-show="!savingAliases">💾 Save</span>
                    <span x-show="savingAliases">Saving…</span>
                </button>
            </div>

            <div x-show="loadingModels" class="text-center py-6">
                <div class="inline-block w-5 h-5 border-2 border-purple-400 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-[10px] text-gray-400 mt-1">Loading models…</p>
            </div>

            <div x-show="!loadingModels" class="space-y-3">
                <div x-show="modelError" class="p-3 rounded-lg bg-red-50 border border-red-200 text-xs text-red-700" data-testid="model-error">
                    <p class="font-bold">⚠ Could not load models</p>
                    <p x-text="modelError" class="mt-0.5"></p>
                    <p class="mt-1 text-[10px] opacity-70">You can still type model IDs manually below.</p>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Default Model (general AI tasks)</label>
                    <div class="flex gap-2">
                        <input type="text" x-model="aliases.default" list="model-list"
                            class="adm-input w-full font-mono text-xs" placeholder="e.g. anthropic.claude-3-5-sonnet-20241022-v2:0"
                            data-testid="alias-default">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Smart Model (complex reasoning, RAG)</label>
                    <input type="text" x-model="aliases.smart" list="model-list"
                        class="adm-input w-full font-mono text-xs" placeholder="e.g. anthropic.claude-3-5-sonnet-20241022-v2:0"
                        data-testid="alias-smart">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Fast Model (quick tasks, low latency)</label>
                    <input type="text" x-model="aliases.fast" list="model-list"
                        class="adm-input w-full font-mono text-xs" placeholder="e.g. anthropic.claude-3-haiku-20240307-v1:0"
                        data-testid="alias-fast">
                </div>

                {{-- Datalist with fetched models --}}
                <datalist id="model-list">
                    <template x-for="m in availableModels" :key="m.model_id">
                        <option :value="m.model_id" x-text="m.name + ' (' + m.provider + ')'"></option>
                    </template>
                </datalist>

                <p class="text-[10px] text-gray-400">Type a model ID or select from fetched models. <a href="{{ route('admin.bedrock.models') }}" class="text-purple-500 hover:underline">View all models →</a></p>
            </div>
        </div>

        {{-- Quick links --}}
        <div class="flex gap-3 adm-a adm-d3" data-adm>
            <a href="{{ route('admin.bedrock.models') }}" class="adm-btn" data-testid="link-models">🧠 View Models</a>
            <a href="{{ route('admin.bedrock.usage') }}" class="adm-btn" data-testid="link-usage">💰 Usage & Costs</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function bedrockDashboard() {
    return {
        loading: true,
        testing: false,
        status: { configured: false, settings: {} },
        testResult: '',
        testResponseTime: null,
        connectionTested: false,
        connectionOk: false,
        savingCreds: false,
        savedAt: '',
        showBearer: false,
        showSecret: false,
        creds: { auth_mode: 'bearer', has_keys: false },
        credForm: { auth_mode: 'bearer', bearer_token: '', aws_key: '', aws_secret: '', region: 'us-east-1' },

        // Model aliases
        aliases: { default: '', smart: '', fast: '' },
        availableModels: [],
        loadingModels: false,
        modelError: '',
        savingAliases: false,

        async init() {
            try {
                const [statusRes, credsRes, aliasRes] = await Promise.all([
                    api.get('/admin/bedrock/status'),
                    api.get('/admin/bedrock/credentials'),
                    api.get('/admin/bedrock/model-aliases'),
                ]);
                this.status = statusRes;
                this.creds  = credsRes;
                this.aliases = aliasRes;
                // Pre-populate form with current region and mode so user sees what's saved
                this.credForm.auth_mode = credsRes.auth_mode || 'bearer';
                this.credForm.region    = credsRes.region    || 'us-east-1';
            } catch (e) {
                toast('Failed to load Bedrock status', 'error');
            }
            this.loading = false;
            this.$nextTick(() => admAnimate());

            // Load models in background for selection dropdowns
            if (this.status.configured) {
                this.fetchAvailableModels();
            }
        },

        async fetchAvailableModels() {
            this.loadingModels = true;
            this.modelError = '';
            try {
                const res = await api.get('/admin/bedrock/models');
                if (res.success) {
                    this.availableModels = res.models || [];
                } else {
                    this.modelError = res.error || 'Failed to fetch models';
                    this.availableModels = [];
                }
            } catch (e) {
                this.modelError = e.response?.data?.error || 'Network error fetching models';
                this.availableModels = [];
            }
            this.loadingModels = false;
        },

        async testConnection() {
            this.testing = true;
            this.testResult = '';
            this.testResponseTime = null;
            this.connectionTested = false;
            try {
                const res = await api.post('/admin/bedrock/test');
                this.connectionTested = true;
                this.connectionOk = res.success;
                this.testResult = res.message || (res.success ? 'Connected' : 'Connection failed');
                this.testResponseTime = res.response_time;
                this.status.configured = true;
                toast(res.success ? 'Connection successful' : 'Connection failed — see details below', res.success ? 'success' : 'error');

                // If test passes, refresh model list
                if (res.success && this.availableModels.length === 0) {
                    this.fetchAvailableModels();
                }
            } catch (e) {
                this.connectionTested = true;
                this.connectionOk = false;
                this.testResult = e.response?.data?.message || 'Network error — could not reach the server';
                toast('Connection test failed', 'error');
            }
            this.testing = false;
        },

        async saveCredentials() {
            this.savingCreds = true;
            try {
                const payload = { auth_mode: this.credForm.auth_mode, region: this.credForm.region };
                if (this.credForm.auth_mode === 'bearer') {
                    if (!this.credForm.bearer_token) { toast('Bearer token is required', 'error'); this.savingCreds = false; return; }
                    payload.bearer_token = this.credForm.bearer_token;
                } else {
                    if (!this.credForm.aws_key || !this.credForm.aws_secret) { toast('Access Key and Secret are required', 'error'); this.savingCreds = false; return; }
                    payload.aws_key    = this.credForm.aws_key;
                    payload.aws_secret = this.credForm.aws_secret;
                }
                await api.put('/admin/bedrock/credentials', payload);
                toast('Credentials saved successfully');
                // Clear sensitive fields after save
                this.credForm.bearer_token = '';
                this.credForm.aws_secret   = '';
                this.savedAt = new Date().toLocaleTimeString();
                // Refresh display
                this.creds = await api.get('/admin/bedrock/credentials');
                this.credForm.auth_mode = this.creds.auth_mode;
                this.credForm.region    = this.creds.region;
                this.status.configured = true;
                // Reset test state — user should re-test
                this.connectionTested = false;
                this.testResult = '';
            } catch (e) {
                const msg = e.response?.data?.message || 'Failed to save credentials';
                toast(msg, 'error');
            }
            this.savingCreds = false;
        },

        async saveModelAliases() {
            this.savingAliases = true;
            try {
                await api.put('/admin/bedrock/model-aliases', this.aliases);
                toast('Model aliases updated');
            } catch (e) {
                toast(e.response?.data?.message || 'Failed to update model aliases', 'error');
            }
            this.savingAliases = false;
        },

        async toggleSetting(s) {
            const newVal = (s.value === 'true' || s.value === true) ? 'false' : 'true';
            try {
                await api.put('/admin/bedrock/settings', {
                    settings: [{ key: s.key, value: newVal }]
                });
                s.value = newVal;
                toast('Setting updated');
            } catch (e) {
                toast('Failed to update setting', 'error');
            }
        }
    }
}
</script>
@endpush
