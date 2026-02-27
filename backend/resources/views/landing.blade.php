<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HormoneLens — Metabolic Health Simulation Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: { extend: { colors: {
            brand: { 50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca',800:'#3730a3',900:'#312e81' }
        }}}
    }
    </script>
    <style>
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
        .float-anim { animation: float 6s ease-in-out infinite; }
        .float-anim-delay { animation: float 6s ease-in-out 2s infinite; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(30px)} to{opacity:1;transform:translateY(0)} }
        .fade-up { animation: fadeUp 0.8s ease-out both; }
        .fade-up-d1 { animation-delay: 0.1s; }
        .fade-up-d2 { animation-delay: 0.25s; }
        .fade-up-d3 { animation-delay: 0.4s; }
        .fade-up-d4 { animation-delay: 0.55s; }
    </style>
</head>
<body class="bg-white text-gray-800 antialiased">

{{-- ═══════════ NAVBAR ═══════════ --}}
<nav class="fixed top-0 inset-x-0 z-50 bg-white/80 backdrop-blur-lg border-b border-gray-100">
    <div class="max-w-7xl mx-auto flex items-center justify-between px-6 h-16">
        <a href="/" class="flex items-center gap-2 text-xl font-bold text-brand-600">
            🔬 <span>HormoneLens</span>
        </a>
        <div class="hidden sm:flex items-center gap-6 text-sm font-medium text-gray-600">
            <a href="#features" class="hover:text-brand-600 transition">Features</a>
            <a href="#diseases" class="hover:text-brand-600 transition">Diseases</a>
            <a href="#how" class="hover:text-brand-600 transition">How It Works</a>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('login') }}"
               class="text-sm font-medium text-gray-600 hover:text-brand-600 transition">Log in</a>
            <a href="{{ route('register') }}"
               class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-lg transition shadow-sm shadow-brand-200">
                Get Started</a>
        </div>
    </div>
</nav>

{{-- ═══════════ HERO ═══════════ --}}
<section class="relative pt-32 pb-20 lg:pt-40 lg:pb-28 overflow-hidden">
    {{-- Gradient orbs (background decoration) --}}
    <div class="absolute top-20 -left-40 w-96 h-96 bg-brand-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 float-anim"></div>
    <div class="absolute top-40 -right-40 w-96 h-96 bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 float-anim-delay"></div>

    <div class="relative max-w-7xl mx-auto px-6 grid lg:grid-cols-2 gap-12 items-center">
        <div class="fade-up">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-brand-50 text-brand-700 mb-6">
                🧬 AI-Powered Metabolic Health
            </span>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-tight tracking-tight">
                Your <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-600 to-purple-600">Digital Twin</span>
                for Hormonal Health
            </h1>
            <p class="mt-5 text-lg text-gray-500 max-w-lg leading-relaxed">
                Simulate how food, sleep, and stress impact your metabolic scores. Get personalized risk analysis powered by AI — all in one platform.
            </p>
            <div class="mt-8 flex flex-wrap gap-4">
                <a href="{{ route('register') }}"
                   class="px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl transition shadow-lg shadow-brand-200 text-sm">
                    Start Free Demo →
                </a>
                <a href="#how"
                   class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition text-sm flex items-center gap-2">
                    ▶ See How It Works
                </a>
            </div>
            <p class="mt-5 text-xs text-gray-400">No credit card required • Instant setup with <code class="bg-gray-100 px-1.5 py-0.5 rounded text-brand-600 font-mono">php artisan hormone:install</code></p>
        </div>

        {{-- Hero visual: mock dashboard card --}}
        <div class="fade-up fade-up-d2 relative">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 p-6 max-w-md mx-auto">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <p class="text-sm text-gray-500">Overall Risk Score</p>
                        <p class="text-4xl font-extrabold text-amber-500">68.4</p>
                    </div>
                    <span class="px-3 py-1 bg-amber-100 text-amber-700 text-xs font-bold rounded-full">MEDIUM</span>
                </div>
                <div class="grid grid-cols-3 gap-3 mb-5">
                    <div class="text-center p-3 bg-indigo-50 rounded-xl">
                        <p class="text-lg font-bold text-indigo-600">82.5</p>
                        <p class="text-[10px] text-gray-500 mt-0.5">Metabolic</p>
                    </div>
                    <div class="text-center p-3 bg-purple-50 rounded-xl">
                        <p class="text-lg font-bold text-purple-600">55.0</p>
                        <p class="text-[10px] text-gray-500 mt-0.5">Insulin Res.</p>
                    </div>
                    <div class="text-center p-3 bg-emerald-50 rounded-xl">
                        <p class="text-lg font-bold text-emerald-600">70.0</p>
                        <p class="text-[10px] text-gray-500 mt-0.5">Sleep</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-red-50 rounded-xl text-sm">
                    <span>⛔</span>
                    <div>
                        <p class="font-medium text-red-700">Blood sugar critically high</p>
                        <p class="text-xs text-red-500">210 mg/dL — Immediate attention needed</p>
                    </div>
                </div>
            </div>
            {{-- Floating badge --}}
            <div class="absolute -top-3 -right-3 bg-emerald-500 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg float-anim">
                ✓ Live Simulation
            </div>
        </div>
    </div>
</section>

{{-- ═══════════ FEATURES ═══════════ --}}
<section id="features" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-14 fade-up">
            <h2 class="text-3xl font-bold">Everything you need to understand your metabolic health</h2>
            <p class="mt-3 text-gray-500">From data entry to AI-powered insights — all in a unified platform.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
            $features = [
                ['icon' => '🧪', 'title' => 'Digital Twin Engine', 'desc' => 'A virtual model of your metabolic state. Generates risk scores for metabolic health, insulin resistance, hormonal balance, sleep & stress.'],
                ['icon' => '🍽️', 'title' => 'Food Impact Simulator', 'desc' => 'Enter any food item and instantly see how it would affect your risk scores. Get healthier alternatives powered by RAG search.'],
                ['icon' => '😴', 'title' => 'Lifestyle Simulations', 'desc' => 'Simulate changes to sleep, stress, and diet. See the exact impact on your scores before making real changes.'],
                ['icon' => '🧠', 'title' => 'RAG Knowledge Base', 'desc' => 'Ask natural-language questions. Our AI searches a curated medical knowledge base and returns evidence-based answers.'],
                ['icon' => '🔔', 'title' => 'Smart Alerts', 'desc' => 'Automatic risk alerts triggered after simulations. Get notified when scores enter dangerous zones with severity levels.'],
                ['icon' => '📊', 'title' => 'Admin Analytics', 'desc' => 'Superadmin dashboard with user management, simulation logs, alert statistics, and exportable PDF/CSV reports.'],
            ];
            @endphp

            @foreach($features as $i => $f)
            <div class="bg-white rounded-xl border border-gray-100 p-6 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 fade-up fade-up-d{{ $i % 4 + 1 }}">
                <div class="w-12 h-12 flex items-center justify-center bg-brand-50 rounded-xl text-2xl mb-4">{{ $f['icon'] }}</div>
                <h3 class="font-semibold text-gray-800 mb-2">{{ $f['title'] }}</h3>
                <p class="text-sm text-gray-500 leading-relaxed">{{ $f['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════ DISEASES ═══════════ --}}
<section id="diseases" class="py-20">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-14 fade-up">
            <h2 class="text-3xl font-bold">Dynamic Disease Tracking</h2>
            <p class="mt-3 text-gray-500">Extensible to any number of conditions — each with custom fields, validation rules, and risk scoring.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach(\App\Models\Disease::active()->ordered()->with('fields')->get() as $d)
            <div class="bg-white rounded-xl border border-gray-100 p-6 text-center hover:shadow-lg hover:border-brand-200 transition-all duration-300">
                <div class="text-4xl mb-3">{{ $d->icon }}</div>
                <h3 class="font-semibold text-gray-800 mb-1">{{ $d->name }}</h3>
                <p class="text-xs text-gray-400 mb-3">{{ $d->fields->count() }} tracked fields</p>
                <div class="flex flex-wrap justify-center gap-1">
                    @foreach($d->fields->take(4) as $field)
                    <span class="px-2 py-0.5 bg-gray-100 text-[10px] text-gray-500 rounded-full">{{ $field->label }}</span>
                    @endforeach
                    @if($d->fields->count() > 4)
                    <span class="px-2 py-0.5 bg-brand-50 text-[10px] text-brand-600 rounded-full">+{{ $d->fields->count() - 4 }} more</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════ HOW IT WORKS ═══════════ --}}
<section id="how" class="py-20 bg-gray-50">
    <div class="max-w-5xl mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-14 fade-up">
            <h2 class="text-3xl font-bold">How It Works</h2>
            <p class="mt-3 text-gray-500">Four simple steps to a complete metabolic health picture.</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @php
            $steps = [
                ['num' => '01', 'title' => 'Create Profile', 'desc' => 'Enter weight, height, sleep, stress, activity, water intake & primary condition.', 'color' => 'brand'],
                ['num' => '02', 'title' => 'Add Disease Data', 'desc' => 'Fill in disease-specific fields — blood sugar, symptoms, hormonal indicators & more.', 'color' => 'purple'],
                ['num' => '03', 'title' => 'Generate Twin', 'desc' => 'Your digital twin crunches the numbers into 6 health scores with a risk category.', 'color' => 'emerald'],
                ['num' => '04', 'title' => 'Simulate & Learn', 'desc' => 'Run what-if scenarios on food, sleep & stress. Ask the AI knowledge base any health question.', 'color' => 'amber'],
            ];
            @endphp

            @foreach($steps as $s)
            <div class="text-center fade-up fade-up-d{{ $loop->iteration }}">
                <div class="w-14 h-14 mx-auto flex items-center justify-center bg-{{ $s['color'] }}-100 text-{{ $s['color'] }}-600 rounded-2xl text-lg font-extrabold mb-4">
                    {{ $s['num'] }}
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">{{ $s['title'] }}</h3>
                <p class="text-sm text-gray-500 leading-relaxed">{{ $s['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════ TECH STACK ═══════════ --}}
<section class="py-20">
    <div class="max-w-5xl mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-10 fade-up">
            <h2 class="text-3xl font-bold">Built With</h2>
        </div>
        <div class="flex flex-wrap items-center justify-center gap-8 text-gray-400">
            <div class="text-center">
                <p class="text-2xl font-bold text-red-500">Laravel 12</p>
                <p class="text-xs mt-1">Backend API</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-cyan-500">Tailwind</p>
                <p class="text-xs mt-1">Styling</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-blue-500">Alpine.js</p>
                <p class="text-xs mt-1">Reactivity</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-amber-500">Sanctum</p>
                <p class="text-xs mt-1">Auth</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-emerald-500">MySQL</p>
                <p class="text-xs mt-1">Database</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-purple-500">RAG</p>
                <p class="text-xs mt-1">AI Search</p>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════ CTA ═══════════ --}}
<section class="py-20">
    <div class="max-w-4xl mx-auto px-6">
        <div class="bg-gradient-to-br from-brand-600 to-purple-700 rounded-3xl p-10 sm:p-14 text-center text-white shadow-2xl">
            <h2 class="text-3xl sm:text-4xl font-bold mb-4">Ready to explore your metabolic health?</h2>
            <p class="text-brand-200 max-w-lg mx-auto mb-8">Create an account in seconds, fill in your health data, and let your Digital Twin reveal personalized insights.</p>
            <div class="flex flex-wrap items-center justify-center gap-4">
                <a href="{{ route('register') }}"
                   class="px-7 py-3 bg-white text-brand-600 font-semibold rounded-xl hover:bg-brand-50 transition shadow-lg text-sm">
                    Create Free Account
                </a>
                <a href="{{ route('login') }}"
                   class="px-7 py-3 border-2 border-white/30 text-white font-semibold rounded-xl hover:bg-white/10 transition text-sm">
                    Log In
                </a>
            </div>
            <div class="mt-6 pt-6 border-t border-white/20">
                <p class="text-sm text-brand-200">Quick setup for developers:</p>
                <code class="inline-block mt-2 px-4 py-2 bg-black/20 rounded-lg text-sm font-mono text-brand-100">
                    php artisan hormone:install
                </code>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════ FOOTER ═══════════ --}}
<footer class="border-t border-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-400">
        <p>🔬 HormoneLens — Metabolic Health Simulation Platform</p>
        <p>Built with ❤️ using Laravel {{ app()->version() }}</p>
    </div>
</footer>

</body>
</html>
