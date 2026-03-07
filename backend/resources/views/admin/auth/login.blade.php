<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — HormoneLens</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: { extend: { colors: {
            brand: { 50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',
                     400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca',
                     800:'#3730a3',900:'#312e81' }
        }}}
    }
    </script>
    <style>
        * { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; }

        .admin-bg {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f0a1e 0%, #1a1040 40%, #0d1b3e 70%, #0a0e1a 100%);
            position: relative;
            overflow: hidden;
        }

        /* Ambient glow particles */
        .glow-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            pointer-events: none;
            opacity: 0.12;
        }
        .glow-1 { width: 400px; height: 400px; background: #7c3aed; top: -80px; left: -60px; }
        .glow-2 { width: 300px; height: 300px; background: #6366f1; bottom: -50px; right: -40px; }
        .glow-3 { width: 200px; height: 200px; background: #a78bfa; top: 40%; right: 15%; }

        /* Grid lines overlay */
        .grid-overlay {
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(124,58,237,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(124,58,237,0.04) 1px, transparent 1px);
            background-size: 60px 60px;
        }

        /* Login card */
        @keyframes cardSlideIn {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .admin-card {
            width: 100%;
            max-width: 440px;
            background: rgba(15, 10, 30, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(124, 58, 237, 0.2);
            border-radius: 20px;
            padding: 48px 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4), 0 0 40px rgba(124,58,237,0.08);
            animation: cardSlideIn 0.5s ease-out both;
            position: relative;
            z-index: 10;
        }

        /* Inputs */
        .admin-input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid rgba(124,58,237,0.25);
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            background: rgba(255,255,255,0.04);
            color: #e2e8f0;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .admin-input::placeholder { color: #64748b; }
        .admin-input:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124,58,237,0.15);
        }

        /* Submit button */
        @keyframes btnPulse {
            0%,100% { box-shadow: 0 4px 20px rgba(124,58,237,0.3); }
            50%     { box-shadow: 0 4px 30px rgba(124,58,237,0.5); }
        }
        .admin-btn {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            color: #fff;
            font-weight: 700;
            font-size: 15px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            animation: btnPulse 2.5s ease-in-out infinite;
        }
        .admin-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(124,58,237,0.45);
        }
    </style>
</head>
<body>

<div class="admin-bg">
    <div class="glow-orb glow-1"></div>
    <div class="glow-orb glow-2"></div>
    <div class="glow-orb glow-3"></div>
    <div class="grid-overlay"></div>

    <div class="admin-card">
        {{-- Shield icon + Badge --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4"
                 style="background: linear-gradient(135deg, rgba(124,58,237,0.2), rgba(99,102,241,0.2)); border: 1px solid rgba(124,58,237,0.3);">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#a78bfa" class="w-8 h-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
            </div>
            <div class="inline-flex items-center gap-2 mb-1">
                <span class="text-xl font-extrabold" style="background:linear-gradient(90deg,#a78bfa,#818cf8);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                    HormoneLens
                </span>
            </div>
            <p class="text-xs font-semibold tracking-widest uppercase" style="color: #64748b;">Super Admin Console</p>
        </div>

        <h2 class="text-lg font-bold text-white mb-6">Admin Sign In</h2>

        {{-- Errors --}}
        @if($errors->any())
        <div class="mb-4 p-3 rounded-xl" style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3);">
            <ul class="text-sm space-y-1" style="color: #fca5a5;">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}" class="space-y-5" data-testid="admin-login-form">
            @csrf
            <div>
                <label for="admin-email" class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color: #94a3b8;">Email address</label>
                <input id="admin-email" data-testid="admin-email-input" name="email" type="email" value="{{ old('email') }}" required autofocus
                       class="admin-input" placeholder="admin@hormonelens.com">
            </div>
            <div>
                <label for="admin-password" class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color: #94a3b8;">Password</label>
                <div class="relative">
                    <input id="admin-password" data-testid="admin-password-input" name="password" type="password" required
                           class="admin-input" placeholder="••••••••" style="padding-right:44px;">
                    <button type="button" id="toggleAdminPassword"
                            onclick="(function(){
                                var inp = document.getElementById('admin-password');
                                inp.type = inp.type === 'password' ? 'text' : 'password';
                            })()"
                            aria-label="Toggle password visibility"
                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#7c3aed;padding:4px;display:flex;align-items:center;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="remember" id="admin-remember" data-testid="admin-remember-checkbox"
                       class="w-4 h-4 rounded border-gray-600 text-violet-600 focus:ring-violet-500" style="background: rgba(255,255,255,0.05);">
                <label for="admin-remember" class="text-sm" style="color: #94a3b8;">Remember me</label>
            </div>
            <button type="submit" class="admin-btn" data-testid="admin-login-button">
                Access Admin Console
            </button>
        </form>

        <p class="mt-6 text-center text-xs" style="color: #475569;">
            This is a restricted area. Authorized personnel only.
        </p>
    </div>
</div>

</body>
</html>
