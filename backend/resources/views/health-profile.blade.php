@extends('layouts.app')
@section('title','Health Profile — HormoneLens')
@section('heading','Lifestyle Simulation Input')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════
   HormoneLens — Animated Vertical Lifestyle Simulation
   Input Panel — Glassmorphism Healthcare AI Theme
   ═══════════════════════════════════════════════════════ */

/* ── Page gradient base ── */
.hp-bg {
    background: linear-gradient(135deg,
        rgba(95,111,255,0.06) 0%,
        rgba(194,77,255,0.06) 50%,
        rgba(255,110,199,0.06) 100%);
    min-height: 100%;
    position: relative;
    overflow: hidden;
}

/* ── Ambient floating particles ── */
.hp-particle {
    position: absolute; border-radius: 50%; filter: blur(80px);
    pointer-events: none; opacity: 0.10; will-change: transform;
}
.hp-particle-1 {
    width: 300px; height: 300px;
    background: linear-gradient(135deg, #5f6fff, #c24dff);
    top: -60px; right: -40px;
    animation: hpFloat 18s ease-in-out infinite;
}
.hp-particle-2 {
    width: 240px; height: 240px;
    background: linear-gradient(135deg, #c24dff, #ff6ec7);
    bottom: 5%; left: -30px;
    animation: hpFloat 22s ease-in-out 5s infinite;
}
@keyframes hpFloat {
    0%, 100% { transform: translate(0,0) scale(1); }
    33%      { transform: translate(25px,-18px) scale(1.04); }
    66%      { transform: translate(-18px,12px) scale(0.96); }
}

/* ── Hero banner ── */
.hp-hero {
    background: linear-gradient(135deg, #5f6fff, #c24dff, #ff6ec7);
    border-radius: 22px;
    position: relative;
    overflow: hidden;
}
.hp-hero::after {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(circle at 85% 25%, rgba(255,255,255,0.14) 0%, transparent 55%);
    pointer-events: none;
}

/* ── Glassmorphism field card ── */
.field-card {
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

/* Gradient glow border on hover / focus-within */
.field-card::before {
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
.field-card:hover::before,
.field-card:focus-within::before {
    opacity: 1;
}
.field-card:hover,
.field-card:focus-within {
    transform: translateY(-3px);
    box-shadow: 0 12px 36px rgba(95, 111, 255, 0.13),
                0 0 18px rgba(194, 77, 255, 0.06);
    border-color: rgba(194, 77, 255, 0.15);
}

/* ── Field icon badge ── */
.field-icon {
    width: 32px; height: 32px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.95rem;
    flex-shrink: 0;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.field-card:focus-within .field-icon {
    transform: scale(1.1);
}

/* ── Input styling ── */
.hp-input {
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
.hp-input:focus {
    border-color: rgba(194, 77, 255, 0.45);
    box-shadow: 0 0 0 3px rgba(194, 77, 255, 0.1),
                0 0 12px rgba(95, 111, 255, 0.06);
    background: rgba(255,255,255,0.9);
}
.hp-input::placeholder {
    color: #b0b0b8;
}

/* ── Staggered entrance ── */
@keyframes hpSlideUp {
    from { opacity: 0; transform: translateY(28px); }
    to   { opacity: 1; transform: translateY(0); }
}
.hp-anim {
    opacity: 0;
    transform: translateY(28px);
}
.hp-anim.hp-visible {
    animation: hpSlideUp 0.65s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}
.hp-d0 { animation-delay: 0s !important; }
.hp-d1 { animation-delay: 0.07s !important; }
.hp-d2 { animation-delay: 0.14s !important; }
.hp-d3 { animation-delay: 0.21s !important; }
.hp-d4 { animation-delay: 0.28s !important; }
.hp-d5 { animation-delay: 0.35s !important; }
.hp-d6 { animation-delay: 0.42s !important; }
.hp-d7 { animation-delay: 0.49s !important; }
.hp-d8 { animation-delay: 0.56s !important; }
.hp-d9 { animation-delay: 0.63s !important; }

/* ── Pulse on value change ── */
@keyframes fieldPulse {
    0%   { box-shadow: 0 8px 32px rgba(95,111,255,0.07); }
    40%  { box-shadow: 0 8px 32px rgba(194,77,255,0.18), 0 0 20px rgba(255,110,199,0.08); }
    100% { box-shadow: 0 8px 32px rgba(95,111,255,0.07); }
}
.field-pulse {
    animation: fieldPulse 0.6s ease-out;
}

/* ── Submit button ── */
.hp-submit-btn {
    position: relative;
    padding: 0.6rem 1.75rem;
    border: none;
    border-radius: 14px;
    font-weight: 700;
    font-size: 0.875rem;
    color: #fff;
    background: linear-gradient(135deg, #5f6fff, #c24dff, #ff6ec7);
    background-size: 200% 200%;
    animation: btnGradient 4s ease infinite;
    cursor: pointer;
    overflow: hidden;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    outline: none;
}
.hp-submit-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 28px rgba(194, 77, 255, 0.3),
                0 0 16px rgba(95, 111, 255, 0.15);
}
.hp-submit-btn:active:not(:disabled) {
    transform: translateY(0);
}
.hp-submit-btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}
@keyframes btnGradient {
    0%   { background-position: 0% 50%; }
    50%  { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Ripple effect */
.hp-submit-btn .hp-ripple {
    position: absolute;
    border-radius: 50%;
    background: rgba(255,255,255,0.35);
    transform: scale(0);
    pointer-events: none;
}
.hp-submit-btn .hp-ripple.hp-ripple-active {
    animation: rippleSpread 0.6s ease-out forwards;
}
@keyframes rippleSpread {
    to { transform: scale(4); opacity: 0; }
}

/* ── Gradient text helper ── */
.hp-gradient-text {
    background: linear-gradient(135deg, #5f6fff, #c24dff, #ff6ec7);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* ── Status pulse ── */
@keyframes hpStatusPulse {
    0%, 100% { opacity: 0.5; transform: scale(1); }
    50%      { opacity: 1; transform: scale(1.3); }
}
.hp-status-pulse {
    animation: hpStatusPulse 2s ease-in-out infinite;
}

/* ── BMI glass card ── */
.bmi-card {
    background: rgba(255,255,255,0.6);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border: 1px solid rgba(255,255,255,0.35);
    border-radius: 14px;
    box-shadow: 0 6px 24px rgba(95, 111, 255, 0.06);
}

/* ── 2-column field grid ── */
.hp-field-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.65rem;
}
@media (min-width: 640px) {
    .hp-field-grid {
        grid-template-columns: 1fr 1fr;
    }
}
.hp-field-full {
    grid-column: 1 / -1;
}

/* ── Error card ── */
.hp-error-card {
    background: rgba(254, 242, 242, 0.75);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(239, 68, 68, 0.15);
    border-radius: 18px;
}
</style>
@endpush

@section('content')
<div x-data="healthProfilePage()" x-init="init()" class="hp-bg -m-4 sm:-m-6 p-4 sm:p-6">

    {{-- Ambient particles --}}
    <div class="hp-particle hp-particle-1"></div>
    <div class="hp-particle hp-particle-2"></div>

    <div class="max-w-3xl mx-auto relative">

        {{-- ── Hero Banner ── --}}
        <div class="hp-hero px-5 py-4 mb-4 hp-anim hp-d0" data-hp-anim>
            <div class="relative z-10 flex items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <div class="w-2 h-2 rounded-full bg-emerald-400 hp-status-pulse"></div>
                        <span class="text-white/70 text-[11px] font-medium tracking-wide uppercase">Simulation Input Panel</span>
                    </div>
                    <h1 class="text-lg font-bold text-white">🧬 Lifestyle Configuration</h1>
                </div>
            </div>
        </div>

        {{-- Loading --}}
        <div x-show="loading" class="text-center py-10">
            <div class="inline-block w-8 h-8 border-[3px] border-purple-400 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-xs text-gray-400 mt-2">Loading simulation parameters…</p>
        </div>

        {{-- ── Form ── --}}
        <form x-show="!loading" @submit.prevent="save()" x-cloak>

            <div class="hp-field-grid">

                {{-- Weight --}}
                <div class="field-card hp-anim hp-d1" data-hp-anim
                     @input="pulseCard($el)">
                    <div class="flex items-center gap-2 mb-1.5">
                        <div class="field-icon bg-indigo-100 text-indigo-600">⚖️</div>
                        <label class="text-xs font-semibold text-gray-700">Weight (kg)</label>
                    </div>
                    <input type="number" step="0.1" x-model="form.weight" min="20" max="300" required
                           class="hp-input" placeholder="Enter weight…">
                </div>

                {{-- Height --}}
                <div class="field-card hp-anim hp-d2" data-hp-anim
                     @input="pulseCard($el)">
                    <div class="flex items-center gap-2 mb-1.5">
                        <div class="field-icon bg-purple-100 text-purple-600">📏</div>
                        <label class="text-xs font-semibold text-gray-700">Height (cm)</label>
                    </div>
                    <input type="number" step="0.1" x-model="form.height" min="50" max="250" required
                           class="hp-input" placeholder="Enter height…">
                </div>

                {{-- Avg Sleep --}}
                <div class="field-card hp-anim hp-d3" data-hp-anim
                     @input="pulseCard($el)">
                    <div class="flex items-center gap-2 mb-1.5">
                        <div class="field-icon bg-blue-100 text-blue-600">🌙</div>
                        <label class="text-xs font-semibold text-gray-700">Avg. Sleep (hours/day)</label>
                    </div>
                    <input type="number" step="0.5" x-model="form.avg_sleep_hours" min="0" max="24" required
                           class="hp-input" placeholder="Enter average sleep…">
                </div>

                {{-- Water Intake --}}
                <div class="field-card hp-anim hp-d4" data-hp-anim
                     @input="pulseCard($el)">
                    <div class="flex items-center gap-2 mb-1.5">
                        <div class="field-icon bg-cyan-100 text-cyan-600">💧</div>
                        <label class="text-xs font-semibold text-gray-700">Water Intake (liters/day)</label>
                    </div>
                    <input type="number" step="0.1" x-model="form.water_intake" min="0" max="20" required
                           class="hp-input" placeholder="Enter daily water intake…">
                </div>

                {{-- Stress Level --}}
                <div class="field-card hp-anim hp-d5" data-hp-anim
                     @change="pulseCard($el)">
                    <div class="flex items-center gap-2 mb-1.5">
                        <div class="field-icon bg-amber-100 text-amber-600">🧠</div>
                        <label class="text-xs font-semibold text-gray-700">Stress Level</label>
                    </div>
                    <select x-model="form.stress_level" required class="hp-input bg-transparent">
                        <option value="">Select…</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>

                {{-- Physical Activity --}}
                <div class="field-card hp-anim hp-d6" data-hp-anim
                     @change="pulseCard($el)">
                    <div class="flex items-center gap-2 mb-1.5">
                        <div class="field-icon bg-emerald-100 text-emerald-600">🏃</div>
                        <label class="text-xs font-semibold text-gray-700">Physical Activity</label>
                    </div>
                    <select x-model="form.physical_activity" required class="hp-input bg-transparent">
                        <option value="">Select…</option>
                        <option value="sedentary">Sedentary</option>
                        <option value="moderate">Moderate</option>
                        <option value="active">Active</option>
                    </select>
                </div>

                {{-- Primary Condition --}}
                <div class="field-card hp-field-full hp-anim hp-d7" data-hp-anim
                     @change="pulseCard($el)">
                    <div class="flex items-center gap-2 mb-1.5">
                        <div class="field-icon bg-red-100 text-red-600">🩺</div>
                        <label class="text-xs font-semibold text-gray-700">Primary Condition</label>
                    </div>
                    <select x-model="form.disease_type" required class="hp-input bg-transparent">
                        <option value="">Select…</option>
                        @foreach(\App\Models\Disease::active()->ordered()->get() as $d)
                        <option value="{{ $d->slug }}">{{ $d->icon }} {{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Eating Habits --}}
                <div class="field-card hp-field-full hp-anim hp-d8" data-hp-anim
                     @input="pulseCard($el)">
                    <div class="flex items-center gap-2 mb-1.5">
                        <div class="field-icon bg-orange-100 text-orange-600">🍽️</div>
                        <label class="text-xs font-semibold text-gray-700">Eating Habits <span class="font-normal text-gray-400">(optional)</span></label>
                    </div>
                    <textarea x-model="form.eating_habits" rows="2" maxlength="1000"
                              class="hp-input" style="resize:vertical; min-height:52px;"
                              placeholder="Describe your typical diet, meal timings, preferences…"></textarea>
                </div>

            </div>

            {{-- Validation errors --}}
            <template x-if="errors.length">
                <div class="hp-error-card p-3 mt-3">
                    <p class="text-[10px] font-semibold text-red-500 uppercase tracking-wider mb-1">⚠ Validation Errors</p>
                    <ul class="text-xs text-red-600 space-y-0.5">
                        <template x-for="e in errors">
                            <li x-text="e"></li>
                        </template>
                    </ul>
                </div>
            </template>

            {{-- Submit + Status + BMI row --}}
            <div class="flex flex-wrap items-center gap-4 mt-4 hp-anim hp-d9" data-hp-anim>
                <button type="submit" :disabled="saving"
                        class="hp-submit-btn"
                        @click="ripple($event)">
                    <span x-show="!saving" x-text="exists ? '✦ Update Profile' : '✦ Save Profile'"></span>
                    <span x-show="saving" class="flex items-center gap-2">
                        <span class="inline-block w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                        Saving…
                    </span>
                </button>
                <span x-show="exists" x-transition class="text-xs font-medium text-emerald-600 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 hp-status-pulse inline-block"></span>
                    Profile synced
                </span>
                <div x-show="form.weight && form.height" x-transition class="bmi-card px-3 py-2 ml-auto flex items-center gap-2">
                    <div class="field-icon bg-violet-100 text-violet-600 text-xs font-black"
                         style="width:32px;height:32px;border-radius:10px;">
                        <span x-text="(form.weight / ((form.height/100)**2)).toFixed(1)" class="text-xs font-bold"></span>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-700">BMI</p>
                        <p class="text-[11px] text-gray-500">
                            <span x-text="(form.weight / ((form.height/100)**2)).toFixed(1)" class="font-medium"></span>
                            <span class="ml-1 inline-block px-1.5 py-0.5 text-[10px] font-medium rounded-full"
                                  :class="bmiCategory.color" x-text="bmiCategory.label"></span>
                        </p>
                    </div>
                </div>
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
            /* Stagger-animate field cards once loaded */
            this.$nextTick(() => {
                document.querySelectorAll('[data-hp-anim]').forEach(el => {
                    el.classList.add('hp-visible');
                });
            });
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
        },
        /* Pulse animation on field value change */
        pulseCard(el) {
            el.classList.remove('field-pulse');
            void el.offsetWidth; /* reflow */
            el.classList.add('field-pulse');
        },
        /* Ripple effect on submit button */
        ripple(event) {
            const btn = event.currentTarget;
            const circle = document.createElement('span');
            const d = Math.max(btn.clientWidth, btn.clientHeight);
            const rect = btn.getBoundingClientRect();
            circle.style.width = circle.style.height = d + 'px';
            circle.style.left = (event.clientX - rect.left - d / 2) + 'px';
            circle.style.top = (event.clientY - rect.top - d / 2) + 'px';
            circle.classList.add('hp-ripple', 'hp-ripple-active');
            btn.appendChild(circle);
            circle.addEventListener('animationend', () => circle.remove());
        }
    };
}
</script>
@endpush
