@extends('layouts.app')
@section('title', $disease->name . ' Data — HormoneLens')
@section('heading', $disease->name . ' Health Indicators')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════
   HormoneLens — Disease Data Glassmorphism Panel
   Matches Health-Profile aesthetic
   ═══════════════════════════════════════════════════════ */

/* ── Page gradient base ── */
.ds-bg {
    background: linear-gradient(135deg,
        rgba(95,111,255,0.06) 0%,
        rgba(194,77,255,0.06) 50%,
        rgba(255,110,199,0.06) 100%);
    min-height: 100%;
    position: relative;
    overflow: hidden;
}

/* ── Ambient floating particles ── */
.ds-particle {
    position: absolute; border-radius: 50%; filter: blur(80px);
    pointer-events: none; opacity: 0.10; will-change: transform;
}
.ds-particle-1 {
    width: 300px; height: 300px;
    background: linear-gradient(135deg, #5f6fff, #c24dff);
    top: -60px; right: -40px;
    animation: dsFloat 18s ease-in-out infinite;
}
.ds-particle-2 {
    width: 240px; height: 240px;
    background: linear-gradient(135deg, #c24dff, #ff6ec7);
    bottom: 5%; left: -30px;
    animation: dsFloat 22s ease-in-out 5s infinite;
}
@keyframes dsFloat {
    0%, 100% { transform: translate(0,0) scale(1); }
    33%      { transform: translate(25px,-18px) scale(1.04); }
    66%      { transform: translate(-18px,12px) scale(0.96); }
}

/* ── Hero banner ── */
.ds-hero {
    background: linear-gradient(135deg, #5f6fff, #c24dff, #ff6ec7);
    border-radius: 22px;
    position: relative;
    overflow: hidden;
}
.ds-hero::after {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(circle at 85% 25%, rgba(255,255,255,0.14) 0%, transparent 55%);
    pointer-events: none;
}

/* ── Glassmorphism field card ── */
.ds-field-card {
    background: rgba(255, 255, 255, 0.55);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.35);
    border-radius: 16px;
    box-shadow: 0 6px 24px rgba(95, 111, 255, 0.07);
    padding: 0.7rem 1rem;
    position: relative;
    overflow: hidden;
    transition: transform 0.4s cubic-bezier(0.4,0,0.2,1),
                box-shadow 0.4s ease,
                border-color 0.4s ease;
}
.ds-field-card::before {
    content: '';
    position: absolute; inset: 0;
    border-radius: 16px;
    padding: 1.5px;
    background: linear-gradient(135deg,
        rgba(95,111,255,0.25),
        rgba(194,77,255,0.2),
        rgba(255,110,199,0.15));
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.4s ease;
}
.ds-field-card:hover::before,
.ds-field-card:focus-within::before { opacity: 1; }
.ds-field-card:hover,
.ds-field-card:focus-within {
    transform: translateY(-3px);
    box-shadow: 0 12px 36px rgba(95, 111, 255, 0.13),
                0 0 18px rgba(194, 77, 255, 0.06);
    border-color: rgba(194, 77, 255, 0.15);
}

/* ── Boolean toggle card ── */
.ds-bool-card {
    background: rgba(255, 255, 255, 0.45);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 14px;
    padding: 0.55rem 0.85rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
    cursor: pointer;
}
.ds-bool-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(95, 111, 255, 0.1);
    border-color: rgba(194, 77, 255, 0.15);
}

/* ── Field icon badge ── */
.ds-field-icon {
    width: 32px; height: 32px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.95rem;
    flex-shrink: 0;
    transition: transform 0.3s ease;
}
.ds-field-card:focus-within .ds-field-icon { transform: scale(1.1); }

/* ── Input styling ── */
.ds-input {
    width: 100%;
    padding: 0.45rem 0.75rem;
    border: 1.5px solid rgba(0,0,0,0.08);
    border-radius: 12px;
    background: rgba(255,255,255,0.7);
    font-size: 0.875rem;
    color: #1f2937;
    outline: none;
    transition: border-color 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
}
.ds-input:focus {
    border-color: rgba(194, 77, 255, 0.45);
    box-shadow: 0 0 0 3px rgba(194, 77, 255, 0.1),
                0 0 12px rgba(95, 111, 255, 0.06);
    background: rgba(255,255,255,0.9);
}
.ds-input::placeholder { color: #b0b0b8; }

/* ── Category header ── */
.ds-category-label {
    background: linear-gradient(135deg, #5f6fff, #c24dff, #ff6ec7);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* ── Staggered entrance ── */
@keyframes dsSlideUp {
    from { opacity: 0; transform: translateY(28px); }
    to   { opacity: 1; transform: translateY(0); }
}
.ds-anim {
    opacity: 0;
    transform: translateY(28px);
}
.ds-anim.ds-visible {
    animation: dsSlideUp 0.65s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}
.ds-d0 { animation-delay: 0s !important; }
.ds-d1 { animation-delay: 0.05s !important; }
.ds-d2 { animation-delay: 0.10s !important; }
.ds-d3 { animation-delay: 0.15s !important; }
.ds-d4 { animation-delay: 0.20s !important; }
.ds-d5 { animation-delay: 0.25s !important; }
.ds-d6 { animation-delay: 0.30s !important; }
.ds-d7 { animation-delay: 0.35s !important; }
.ds-d8 { animation-delay: 0.40s !important; }
.ds-d9 { animation-delay: 0.45s !important; }
.ds-d10 { animation-delay: 0.50s !important; }
.ds-d11 { animation-delay: 0.55s !important; }
.ds-d12 { animation-delay: 0.60s !important; }

/* ── Pulse on value change ── */
@keyframes dsPulse {
    0%   { box-shadow: 0 8px 32px rgba(95,111,255,0.07); }
    40%  { box-shadow: 0 8px 32px rgba(194,77,255,0.18), 0 0 20px rgba(255,110,199,0.08); }
    100% { box-shadow: 0 8px 32px rgba(95,111,255,0.07); }
}
.ds-pulse { animation: dsPulse 0.6s ease-out; }

/* ── Submit button ── */
.ds-submit-btn {
    position: relative;
    padding: 0.6rem 1.75rem;
    border: none;
    border-radius: 14px;
    font-weight: 700;
    font-size: 0.875rem;
    color: #fff;
    background: linear-gradient(135deg, #5f6fff, #c24dff, #ff6ec7);
    background-size: 200% 200%;
    animation: dsBtnGrad 4s ease infinite;
    cursor: pointer;
    overflow: hidden;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    outline: none;
}
.ds-submit-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 28px rgba(194, 77, 255, 0.3),
                0 0 16px rgba(95, 111, 255, 0.15);
}
.ds-submit-btn:active:not(:disabled) { transform: translateY(0); }
.ds-submit-btn:disabled { opacity: 0.55; cursor: not-allowed; }
@keyframes dsBtnGrad {
    0%   { background-position: 0% 50%; }
    50%  { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Ripple effect */
.ds-submit-btn .ds-ripple {
    position: absolute; border-radius: 50%;
    background: rgba(255,255,255,0.35);
    transform: scale(0); pointer-events: none;
}
.ds-submit-btn .ds-ripple.ds-ripple-active {
    animation: dsRippleSpread 0.6s ease-out forwards;
}
@keyframes dsRippleSpread { to { transform: scale(4); opacity: 0; } }

/* ── Status pulse ── */
@keyframes dsStatusPulse {
    0%, 100% { opacity: 0.5; transform: scale(1); }
    50%      { opacity: 1; transform: scale(1.3); }
}
.ds-status-pulse { animation: dsStatusPulse 2s ease-in-out infinite; }

/* ── Error card ── */
.ds-error-card {
    background: rgba(254, 242, 242, 0.75);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(239, 68, 68, 0.15);
    border-radius: 18px;
}

/* ── 2-column field grid ── */
.ds-field-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.65rem;
}
@media (min-width: 640px) {
    .ds-field-grid { grid-template-columns: 1fr 1fr; }
}
.ds-field-full { grid-column: 1 / -1; }

/* ── Custom toggle ── */
.ds-toggle {
    width: 38px; height: 22px;
    background: rgba(0,0,0,0.1);
    border-radius: 11px;
    position: relative;
    transition: background 0.3s ease;
    flex-shrink: 0;
    cursor: pointer;
}
.ds-toggle::after {
    content: '';
    position: absolute;
    top: 3px; left: 3px;
    width: 16px; height: 16px;
    background: white;
    border-radius: 50%;
    box-shadow: 0 1px 4px rgba(0,0,0,0.15);
    transition: transform 0.3s ease;
}
.ds-toggle.active {
    background: linear-gradient(135deg, #5f6fff, #c24dff);
}
.ds-toggle.active::after {
    transform: translateX(16px);
}
</style>
@endpush

@php
    // ── Prepare icon/color map for field types/categories ──
    $categoryMeta = [
        'vitals'   => ['icon' => '🩺', 'color' => 'bg-red-100 text-red-600'],
        'history'  => ['icon' => '📋', 'color' => 'bg-amber-100 text-amber-600'],
        'symptoms' => ['icon' => '🔬', 'color' => 'bg-purple-100 text-purple-600'],
        'cycle'    => ['icon' => '🔄', 'color' => 'bg-pink-100 text-pink-600'],
    ];

    // Per-field icon mapping
    $fieldIcons = [
        'avg_blood_sugar' => ['🩸', 'bg-red-100 text-red-600'],
        'family_history' => ['👨‍👩‍👧', 'bg-amber-100 text-amber-600'],
        'family_history_heart' => ['❤️', 'bg-red-100 text-red-600'],
        'frequent_urination' => ['💧', 'bg-cyan-100 text-cyan-600'],
        'excessive_thirst' => ['🥤', 'bg-blue-100 text-blue-600'],
        'fatigue' => ['😴', 'bg-indigo-100 text-indigo-600'],
        'fatigue_frequency' => ['😴', 'bg-indigo-100 text-indigo-600'],
        'blurred_vision' => ['👁️', 'bg-violet-100 text-violet-600'],
        'numbness_tingling' => ['✋', 'bg-orange-100 text-orange-600'],
        'slow_wound_healing' => ['🩹', 'bg-rose-100 text-rose-600'],
        'unexplained_weight_loss' => ['⚖️', 'bg-emerald-100 text-emerald-600'],
        'sugar_cravings' => ['🍬', 'bg-pink-100 text-pink-600'],
        'energy_crashes_after_meals' => ['⚡', 'bg-yellow-100 text-yellow-600'],
        'cycle_regularity' => ['🔄', 'bg-pink-100 text-pink-600'],
        'avg_cycle_length_days' => ['📅', 'bg-rose-100 text-rose-600'],
        'excess_facial_body_hair' => ['🪒', 'bg-amber-100 text-amber-600'],
        'acne_oily_skin' => ['💆', 'bg-teal-100 text-teal-600'],
        'hair_thinning' => ['💇', 'bg-violet-100 text-violet-600'],
        'weight_gain_difficulty_losing' => ['⚖️', 'bg-orange-100 text-orange-600'],
        'mood_swings_anxiety' => ['🧠', 'bg-purple-100 text-purple-600'],
        'mood_changes' => ['🧠', 'bg-purple-100 text-purple-600'],
        'dark_skin_patches' => ['🔲', 'bg-gray-100 text-gray-600'],
        'sleep_disturbances' => ['🌙', 'bg-blue-100 text-blue-600'],
        'insulin_resistance_diagnosed' => ['💉', 'bg-red-100 text-red-600'],
        'tsh_level' => ['🧪', 'bg-indigo-100 text-indigo-600'],
        't4_level' => ['🧪', 'bg-purple-100 text-purple-600'],
        'thyroid_type' => ['🦋', 'bg-teal-100 text-teal-600'],
        'on_medication' => ['💊', 'bg-emerald-100 text-emerald-600'],
        'cold_intolerance' => ['🥶', 'bg-sky-100 text-sky-600'],
        'dry_skin_hair' => ['🏜️', 'bg-amber-100 text-amber-600'],
        'heart_palpitations' => ['💓', 'bg-red-100 text-red-600'],
        'weight_change' => ['📊', 'bg-orange-100 text-orange-600'],
        'waist_circumference' => ['📐', 'bg-indigo-100 text-indigo-600'],
        'fasting_blood_sugar' => ['🩸', 'bg-red-100 text-red-600'],
        'systolic_bp' => ['❤️', 'bg-rose-100 text-rose-600'],
        'diastolic_bp' => ['❤️', 'bg-rose-100 text-rose-600'],
        'triglycerides' => ['🧬', 'bg-violet-100 text-violet-600'],
        'hdl_cholesterol' => ['🧬', 'bg-emerald-100 text-emerald-600'],
        'on_bp_medication' => ['💊', 'bg-blue-100 text-blue-600'],
        'on_cholesterol_medication' => ['💊', 'bg-teal-100 text-teal-600'],
    ];

    $defaultIcon = ['📋', 'bg-gray-100 text-gray-600'];

    $grouped = $disease->fields->sortBy('sort_order')->groupBy('category');
    $animIndex = 1; // 0 is hero
@endphp

@section('content')
<div x-data="diseasePage()" x-init="init()" class="ds-bg -m-4 sm:-m-6 p-4 sm:p-6">

    {{-- Ambient particles --}}
    <div class="ds-particle ds-particle-1"></div>
    <div class="ds-particle ds-particle-2"></div>

    <div class="max-w-3xl mx-auto relative">

        {{-- ── Hero Banner ── --}}
        <div class="ds-hero px-5 py-4 mb-4 ds-anim ds-d0" data-ds-anim>
            <div class="relative z-10 flex items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <div class="w-2 h-2 rounded-full bg-emerald-400 ds-status-pulse"></div>
                        <span class="text-white/70 text-[11px] font-medium tracking-wide uppercase">Disease Data Panel</span>
                    </div>
                    <h1 class="text-lg font-bold text-white">{{ $disease->icon }} {{ $disease->name }} Health Indicators</h1>
                </div>
            </div>
        </div>

        {{-- Loading --}}
        <div x-show="loading" class="text-center py-10">
            <div class="inline-block w-8 h-8 border-[3px] border-purple-400 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-xs text-gray-400 mt-2">Loading health indicators…</p>
        </div>

        {{-- ── Form ── --}}
        <form x-show="!loading" @submit.prevent="save()" x-cloak>

            @foreach($grouped as $category => $fields)
                @php
                    $catMeta = $categoryMeta[$category] ?? ['icon' => '📁', 'color' => 'bg-gray-100 text-gray-600'];
                    $booleanFields = $fields->filter(fn($f) => $f->field_type === 'boolean');
                    $otherFields   = $fields->filter(fn($f) => $f->field_type !== 'boolean');
                @endphp

                {{-- Category label --}}
                <div class="flex items-center gap-2 mb-2 mt-{{ $loop->first ? '0' : '3' }} ds-anim ds-d{{ min($animIndex++, 12) }}" data-ds-anim>
                    <span class="text-base">{{ $catMeta['icon'] }}</span>
                    <h3 class="text-xs font-bold tracking-widest uppercase ds-category-label">{{ ucfirst($category) }}</h3>
                    <div class="flex-1 h-px bg-gradient-to-r from-purple-200 to-transparent"></div>
                </div>

                {{-- Non-boolean fields in grid --}}
                @if($otherFields->count())
                <div class="ds-field-grid mb-2">
                    @foreach($otherFields as $field)
                        @php $fi = $fieldIcons[$field->slug] ?? $defaultIcon; @endphp
                        <div class="ds-field-card ds-anim ds-d{{ min($animIndex++, 12) }}" data-ds-anim
                             @input="pulseCard($el)" @change="pulseCard($el)">
                            <div class="flex items-center gap-2 mb-1.5">
                                <div class="ds-field-icon {{ $fi[1] }}">{{ $fi[0] }}</div>
                                <label class="text-xs font-semibold text-gray-700">
                                    {{ $field->label }}
                                    @unless($field->is_required)
                                        <span class="font-normal text-gray-400">(optional)</span>
                                    @endunless
                                </label>
                            </div>

                            @if($field->field_type === 'number')
                                <input type="number"
                                       step="{{ ($field->validation['step'] ?? null) ?: 'any' }}"
                                       @if(isset($field->validation['min'])) min="{{ $field->validation['min'] }}" @endif
                                       @if(isset($field->validation['max'])) max="{{ $field->validation['max'] }}" @endif
                                       x-model="form.field_values.{{ $field->slug }}"
                                       {{ $field->is_required ? 'required' : '' }}
                                       class="ds-input"
                                       placeholder="Enter {{ strtolower($field->label) }}…">

                            @elseif($field->field_type === 'select' && is_array($field->options))
                                @php
                                    $opts = $field->options;
                                    if (isset($opts['options']) && is_array($opts['options'])) {
                                        $opts = $opts['options'];
                                    }
                                @endphp
                                <select x-model="form.field_values.{{ $field->slug }}"
                                        {{ $field->is_required ? 'required' : '' }}
                                        class="ds-input bg-transparent">
                                    <option value="">Select…</option>
                                    @foreach($opts as $opt)
                                        @if(is_array($opt) && isset($opt['value']))
                                            <option value="{{ $opt['value'] }}">{{ $opt['label'] ?? ucfirst(str_replace('_', ' ', $opt['value'])) }}</option>
                                        @elseif(!is_array($opt))
                                            <option value="{{ $opt }}">{{ ucfirst(str_replace('_', ' ', $opt)) }}</option>
                                        @endif
                                    @endforeach
                                </select>

                            @elseif($field->field_type === 'text')
                                <input type="text"
                                       x-model="form.field_values.{{ $field->slug }}"
                                       {{ $field->is_required ? 'required' : '' }}
                                       class="ds-input"
                                       placeholder="Enter {{ strtolower($field->label) }}…">
                            @endif
                        </div>
                    @endforeach
                </div>
                @endif

                {{-- Boolean fields as toggle cards --}}
                @if($booleanFields->count())
                <div class="ds-field-grid mb-2">
                    @foreach($booleanFields as $field)
                        @php $fi = $fieldIcons[$field->slug] ?? $defaultIcon; @endphp
                        <label class="ds-bool-card flex items-center gap-3 ds-anim ds-d{{ min($animIndex++, 12) }}" data-ds-anim
                               @click="pulseCard($el)">
                            <div class="ds-field-icon {{ $fi[1] }}" style="width:28px;height:28px;border-radius:8px;font-size:0.85rem;">{{ $fi[0] }}</div>
                            <span class="text-xs font-medium text-gray-700 flex-1">{{ $field->label }}</span>
                            <div class="ds-toggle" :class="{ 'active': form.field_values.{{ $field->slug }} }"
                                 @click.prevent="form.field_values.{{ $field->slug }} = !form.field_values.{{ $field->slug }}"></div>
                            <input type="checkbox" x-model="form.field_values.{{ $field->slug }}" class="hidden">
                        </label>
                    @endforeach
                </div>
                @endif
            @endforeach

            {{-- Validation errors --}}
            <template x-if="errors.length">
                <div class="ds-error-card p-3 mt-3">
                    <p class="text-[10px] font-semibold text-red-500 uppercase tracking-wider mb-1">⚠ Validation Errors</p>
                    <ul class="text-xs text-red-600 space-y-0.5">
                        <template x-for="e in errors"><li x-text="e"></li></template>
                    </ul>
                </div>
            </template>

            {{-- Submit row --}}
            <div class="flex flex-wrap items-center gap-4 mt-4 ds-anim ds-d{{ min($animIndex++, 12) }}" data-ds-anim>
                <button type="submit" :disabled="saving"
                        class="ds-submit-btn"
                        @click="ripple($event)">
                    <span x-show="!saving" x-text="exists ? '✦ Update {{ $disease->name }} Data' : '✦ Save {{ $disease->name }} Data'"></span>
                    <span x-show="saving" class="flex items-center gap-2">
                        <span class="inline-block w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                        Saving…
                    </span>
                </button>
                <span x-show="exists" x-transition class="text-xs font-medium text-emerald-600 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 ds-status-pulse inline-block"></span>
                    Data synced
                </span>
            </div>

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
            try {
                const r = await api.get('/diseases/' + slug);
                if (r.success && r.data) {
                    const saved = r.data.field_values || {};
                    Object.keys(defaults).forEach(k => {
                        if (saved[k] !== undefined && saved[k] !== null) {
                            this.form.field_values[k] = saved[k];
                        }
                    });
                    if (Object.keys(saved).length > 0) this.exists = true;
                }
            } catch (e) {
                console.error('Failed to load disease data:', e);
            }
            this.loading = false;
            this.$nextTick(() => {
                document.querySelectorAll('[data-ds-anim]').forEach(el => {
                    el.classList.add('ds-visible');
                });
            });
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
            } else if (r.errors) {
                this.errors = Object.values(r.errors).flat().map(msg =>
                    msg.replace(/field[_ ]values\./gi, '')
                );
            } else {
                this.errors = [r.message || 'Save failed'];
            }
            this.saving = false;
        },

        pulseCard(el) {
            el.classList.remove('ds-pulse');
            void el.offsetWidth;
            el.classList.add('ds-pulse');
        },

        ripple(event) {
            const btn = event.currentTarget;
            const circle = document.createElement('span');
            const d = Math.max(btn.clientWidth, btn.clientHeight);
            const rect = btn.getBoundingClientRect();
            circle.style.width = circle.style.height = d + 'px';
            circle.style.left = (event.clientX - rect.left - d / 2) + 'px';
            circle.style.top = (event.clientY - rect.top - d / 2) + 'px';
            circle.classList.add('ds-ripple', 'ds-ripple-active');
            btn.appendChild(circle);
            circle.addEventListener('animationend', () => circle.remove());
        }
    };
}
</script>
@endpush
