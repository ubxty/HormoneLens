@extends('layouts.admin')
@section('heading','Usage & Costs')

@section('content')
<div x-data="bedrockUsage()" x-init="init()" id="bedrock-usage">

    <div x-show="loading" class="text-center py-16">
        <div class="inline-block w-8 h-8 border-[3px] border-purple-400 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div x-show="!loading" class="space-y-5">
        {{-- Back link --}}
        <div class="flex items-center justify-between adm-a adm-d0" data-adm>
            <p class="text-xs font-bold text-gray-400">Cost tracking & usage metrics</p>
            <a href="{{ route('admin.bedrock') }}" class="adm-badge bg-white/60 hover:bg-white/80 text-gray-600 cursor-pointer transition px-3 py-1.5">← Back</a>
        </div>

        {{-- Usage error --}}
        <div x-show="usageError" class="adm-card p-4 border-l-4 border-amber-400 adm-a adm-d0" data-adm data-testid="usage-error">
            <p class="text-xs font-bold text-amber-700">⚠ Usage data unavailable</p>
            <p class="text-[10px] text-amber-600 mt-0.5" x-text="usageError"></p>
        </div>

        {{-- Usage summary cards --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="adm-card relative p-5 adm-a adm-d0" data-adm>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Requests</p>
                <p class="text-2xl font-bold adm-grad-text" x-text="usage.total_requests ?? 0" data-testid="total-requests"></p>
            </div>
            <div class="adm-card relative p-5 adm-a adm-d1" data-adm>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Tokens</p>
                <p class="text-2xl font-bold adm-grad-text" x-text="(usage.total_tokens ?? 0).toLocaleString()" data-testid="total-tokens"></p>
            </div>
            <div class="adm-card relative p-5 adm-a adm-d2" data-adm>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Daily Cost</p>
                <p class="text-2xl font-bold" :class="(usage.daily_cost ?? 0) > 8 ? 'text-red-500' : 'adm-grad-text'" data-testid="daily-cost">
                    $<span x-text="(usage.daily_cost ?? 0).toFixed(4)"></span>
                </p>
            </div>
            <div class="adm-card relative p-5 adm-a adm-d3" data-adm>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Monthly Cost</p>
                <p class="text-2xl font-bold" :class="(usage.monthly_cost ?? 0) > 80 ? 'text-red-500' : 'adm-grad-text'" data-testid="monthly-cost">
                    $<span x-text="(usage.monthly_cost ?? 0).toFixed(4)"></span>
                </p>
            </div>
        </div>

        {{-- Pricing error --}}
        <div x-show="pricingError" class="adm-card p-4 border-l-4 border-amber-400 adm-a adm-d0" data-adm data-testid="pricing-error">
            <p class="text-xs font-bold text-amber-700">⚠ Pricing data unavailable</p>
            <p class="text-[10px] text-amber-600 mt-0.5" x-text="pricingError"></p>
        </div>

        {{-- Pricing info --}}
        <div class="adm-card relative p-5 adm-a adm-d4" data-adm>
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Model Pricing (per 1K tokens)</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="text-left text-gray-400 border-b border-gray-100">
                            <th class="pb-2 font-bold">Model</th>
                            <th class="pb-2 font-bold">Input</th>
                            <th class="pb-2 font-bold">Output</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(p, model) in pricing" :key="model">
                            <tr class="border-b border-gray-50">
                                <td class="py-2 text-gray-700 font-medium break-all" x-text="model"></td>
                                <td class="py-2 text-gray-500">$<span x-text="((p.input ?? p.input_cost ?? 0) * 1000).toFixed(4)"></span></td>
                                <td class="py-2 text-gray-500">$<span x-text="((p.output ?? p.output_cost ?? 0) * 1000).toFixed(4)"></span></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div x-show="Object.keys(pricing).length === 0" class="text-center py-4 text-gray-400 text-xs">
                No pricing data available
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function bedrockUsage() {
    return {
        loading: true,
        usage: {},
        pricing: {},
        usageError: '',
        pricingError: '',

        async init() {
            const results = await Promise.allSettled([
                api.get('/admin/bedrock/usage'),
                api.get('/admin/bedrock/pricing'),
            ]);

            // Usage
            if (results[0].status === 'fulfilled') {
                const res = results[0].value;
                if (res.success === false) {
                    this.usageError = res.error || 'Failed to load usage data';
                } else {
                    this.usage = res.data || res || {};
                }
            } else {
                this.usageError = 'Network error loading usage data';
            }

            // Pricing
            if (results[1].status === 'fulfilled') {
                const res = results[1].value;
                if (res.success === false) {
                    this.pricingError = res.error || 'Failed to load pricing data';
                } else {
                    this.pricing = res.data || res || {};
                }
            } else {
                this.pricingError = 'Network error loading pricing data';
            }

            this.loading = false;
            this.$nextTick(() => admAnimate());
        }
    }
}
</script>
@endpush
