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
                <button @click="testConnection()" :disabled="testing" class="adm-btn disabled:opacity-50" id="test-connection-btn">
                    <span x-show="!testing">Test Connection</span>
                    <span x-show="testing">Testing…</span>
                </button>
            </div>
            <div class="flex gap-6">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full" :class="status.available ? 'bg-green-400 shadow-lg shadow-green-400/50' : 'bg-red-400 shadow-lg shadow-red-400/50'"></div>
                    <span class="text-xs font-bold" :class="status.available ? 'text-green-600' : 'text-red-600'" x-text="status.available ? 'Connected' : 'Disconnected'" id="connection-status"></span>
                </div>
                <div x-show="testResult" class="text-xs text-gray-500" x-text="testResult" id="test-result"></div>
            </div>
        </div>

        {{-- AWS Credentials --}}
        <div class="adm-card relative p-5 adm-a adm-d1" data-adm>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-purple-400/20 to-pink-400/20 flex items-center justify-center text-xs">🔑</div>
                    AWS Credentials
                </h3>
                <button @click="showCredentials = !showCredentials" class="adm-badge bg-white/60 hover:bg-white/80 text-gray-600 cursor-pointer transition px-3 py-1.5" id="toggle-credentials-btn">
                    <span x-text="showCredentials ? 'Cancel' : (creds.has_keys ? 'Update' : 'Configure')"></span>
                </button>
            </div>

            {{-- Current status --}}
            <div x-show="!showCredentials" class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500">AWS Access Key</span>
                    <span class="text-xs font-mono" :class="creds.has_keys ? 'text-gray-700' : 'text-red-400'" x-text="creds.has_keys ? creds.aws_key : 'Not configured'" data-testid="aws-key-display"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500">AWS Secret Key</span>
                    <span class="text-xs font-mono" :class="creds.has_keys ? 'text-gray-700' : 'text-red-400'" x-text="creds.has_keys ? creds.aws_secret : 'Not configured'" data-testid="aws-secret-display"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500">Region</span>
                    <span class="adm-badge bg-purple-50 text-purple-700 text-[10px]" x-text="creds.region || 'us-east-1'" data-testid="aws-region-display"></span>
                </div>
            </div>

            {{-- Edit form --}}
            <form x-show="showCredentials" x-transition @submit.prevent="saveCredentials()" class="space-y-3">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">AWS Access Key ID</label>
                    <input type="text" x-model="credForm.aws_key" required minlength="16" maxlength="128" class="adm-input w-full" placeholder="AKIA..." id="aws-key-input" data-testid="aws-key-input">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">AWS Secret Access Key</label>
                    <input type="password" x-model="credForm.aws_secret" required minlength="16" maxlength="128" class="adm-input w-full" placeholder="••••••••" id="aws-secret-input" data-testid="aws-secret-input">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">AWS Region</label>
                    <select x-model="credForm.region" class="adm-input w-full" id="aws-region-input" data-testid="aws-region-input">
                        <option value="us-east-1">US East (N. Virginia)</option>
                        <option value="us-west-2">US West (Oregon)</option>
                        <option value="eu-west-1">Europe (Ireland)</option>
                        <option value="eu-central-1">Europe (Frankfurt)</option>
                        <option value="ap-southeast-1">Asia Pacific (Singapore)</option>
                        <option value="ap-northeast-1">Asia Pacific (Tokyo)</option>
                        <option value="ap-south-1">Asia Pacific (Mumbai)</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" :disabled="savingCreds" class="adm-btn disabled:opacity-50" id="save-credentials-btn" data-testid="save-credentials-btn">
                        <span x-show="!savingCreds">Save Credentials</span>
                        <span x-show="savingCreds">Saving…</span>
                    </button>
                    <button type="button" @click="showCredentials = false" class="adm-badge bg-white/60 hover:bg-white/80 text-gray-600 cursor-pointer transition px-4 py-2">Cancel</button>
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

        {{-- Quick links --}}
        <div class="flex gap-3 adm-a adm-d2" data-adm>
            <a href="{{ route('admin.bedrock.models') }}" class="adm-btn">🧠 View Models</a>
            <a href="{{ route('admin.bedrock.usage') }}" class="adm-btn">💰 Usage & Costs</a>
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
        status: { available: false, settings: {} },
        testResult: '',
        showCredentials: false,
        savingCreds: false,
        creds: { aws_key: '', aws_secret: '', region: 'us-east-1', has_keys: false },
        credForm: { aws_key: '', aws_secret: '', region: 'us-east-1' },

        async init() {
            try {
                const [statusRes, credsRes] = await Promise.all([
                    api.get('/admin/bedrock/status'),
                    api.get('/admin/bedrock/credentials'),
                ]);
                this.status = statusRes;
                this.creds = credsRes;
                this.credForm.region = credsRes.region || 'us-east-1';
            } catch (e) {
                toast('Failed to load Bedrock status', 'error');
            }
            this.loading = false;
            this.$nextTick(() => admAnimate());
        },

        async testConnection() {
            this.testing = true;
            this.testResult = '';
            try {
                const res = await api.post('/admin/bedrock/test');
                this.testResult = res.success ? `✓ Connected (${res.latency_ms}ms)` : '✗ Connection failed';
                this.status.available = res.success;
                toast(res.success ? 'Connection successful' : 'Connection failed', res.success ? 'success' : 'error');
            } catch (e) {
                this.testResult = '✗ Error testing connection';
                toast('Connection test failed', 'error');
            }
            this.testing = false;
        },

        async saveCredentials() {
            this.savingCreds = true;
            try {
                await api.put('/admin/bedrock/credentials', this.credForm);
                toast('Credentials saved successfully');
                this.showCredentials = false;
                // Refresh credentials display
                const credsRes = await api.get('/admin/bedrock/credentials');
                this.creds = credsRes;
            } catch (e) {
                const msg = e.response?.data?.message || 'Failed to save credentials';
                toast(msg, 'error');
            }
            this.savingCreds = false;
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
