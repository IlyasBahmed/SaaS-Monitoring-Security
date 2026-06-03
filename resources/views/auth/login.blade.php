<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>CyberShield | Next-Gen Security Access</title>
    <!-- Google Fonts + Tailwind + Font Awesome Icons (for richer icons) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800;14..32,900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        body {
            background-color: #020617;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0a1220;
        }
        ::-webkit-scrollbar-thumb {
            background: #06b6d4;
            border-radius: 8px;
        }
        /* animated glow for input focus */
        .input-glow:focus {
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.3), 0 0 0 1px #06b6d4;
            border-color: #06b6d4;
        }
        /* cyber button effect */
        .cyber-button {
            background: linear-gradient(95deg, #06b6d4, #2dd4bf);
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }
        .cyber-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px -8px rgba(6, 182, 212, 0.5);
        }
        .cyber-button:active {
            transform: translateY(1px);
        }
        /* grid animation for left panel (enhanced) */
        .animated-grid-bg {
            background-image: 
                linear-gradient(rgba(34, 211, 238, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(34, 211, 238, 0.05) 1px, transparent 1px);
            background-size: 40px 40px;
            animation: shiftGrid 20s linear infinite;
        }
        @keyframes shiftGrid {
            0% {
                background-position: 0 0;
            }
            100% {
                background-position: 40px 40px;
            }
        }
        .floating-glow {
            animation: floatGlow 6s ease-in-out infinite;
        }
        @keyframes floatGlow {
            0% { transform: translateY(0px) scale(1); opacity: 0.5; }
            50% { transform: translateY(-15px) scale(1.05); opacity: 0.8; }
            100% { transform: translateY(0px) scale(1); opacity: 0.5; }
        }
        .stat-card {
            backdrop-filter: blur(4px);
            transition: all 0.2s ease;
            border: 1px solid rgba(6, 182, 212, 0.15);
        }
        .stat-card:hover {
            border-color: rgba(6, 182, 212, 0.4);
            background: rgba(6, 182, 212, 0.05);
            transform: translateY(-2px);
        }
        .login-panel {
            background: rgba(2, 6, 23, 0.75);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(6, 182, 212, 0.18);
            box-shadow: 0 25px 45px -12px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(6, 182, 212, 0.05);
        }
        .input-dark {
            background-color: rgba(2, 6, 23, 0.7);
            border: 1px solid #1e293b;
            transition: all 0.2s;
        }
        .input-dark:focus {
            background-color: #020617;
        }
        .remember-checkbox {
            accent-color: #06b6d4;
            width: 1rem;
            height: 1rem;
        }
        .shield-icon {
            filter: drop-shadow(0 0 6px rgba(6, 182, 212, 0.6));
        }
        .glow-text {
            text-shadow: 0 0 8px rgba(6, 182, 212, 0.3);
        }
    </style>
</head>
<body class="antialiased">

    <div class="min-h-screen bg-[#020617] text-white grid lg:grid-cols-2 overflow-hidden relative">
        
        <!-- LEFT: Immersive brand & command center (redesigned with more depth) -->
        <div class="hidden lg:flex relative flex-col justify-between p-10 xl:p-12 bg-[#050b14] overflow-hidden border-r border-cyan-500/20">
            
            <!-- Advanced animated background effects -->
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_20%_30%,#0a4c5e20,transparent_60%),radial-gradient(circle_at_85%_80%,#2563eb25,transparent_55%)]"></div>
            <div class="absolute inset-0 animated-grid-bg"></div>
            
            <!-- floating orbs -->
            <div class="absolute top-20 left-10 w-72 h-72 bg-cyan-500 rounded-full blur-[100px] opacity-20 floating-glow"></div>
            <div class="absolute bottom-10 right-5 w-64 h-64 bg-blue-600 rounded-full blur-[100px] opacity-15 floating-glow" style="animation-delay: -3s;"></div>

            <!-- Main content left -->
            <div class="relative z-10">
                <!-- Logo brand section (more polished) -->
                <div class="flex items-center gap-4 group">
                    <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-cyan-400/20 to-blue-500/10 border border-cyan-400/40 flex items-center justify-center shadow-xl shadow-cyan-500/20 transition duration-300 group-hover:border-cyan-400/70">
                        <i class="fas fa-shield-haltered text-3xl text-cyan-300 shield-icon"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-white to-cyan-200 bg-clip-text text-transparent">CyberShield</h1>
                        <p class="text-xs font-mono text-cyan-300 tracking-[0.4em] uppercase mt-0.5">✦ ZERO TRUST PLATFORM ✦</p>
                    </div>
                </div>

                <!-- Hero headline & threat indicator -->
                <div class="mt-20 max-w-2xl">
                    <div class="inline-flex items-center gap-3 rounded-full border border-cyan-400/30 bg-cyan-500/5 backdrop-blur-sm px-4 py-2 text-xs font-medium text-cyan-200">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-cyan-300"></span>
                        </span>
                        <span><i class="fas fa-chart-line mr-1"></i> LIVE THREAT INTELLIGENCE — ACTIVE DEFENSE</span>
                    </div>

                    <h2 class="mt-10 text-6xl xl:text-7xl font-black leading-[1.2] tracking-tighter">
                        Secure access to
                        <span class="relative inline-block">
                            <span class="relative z-10 text-transparent bg-clip-text bg-gradient-to-r from-cyan-300 to-teal-300">cyber defense</span>
                            <svg class="absolute bottom-2 left-0 w-full h-3 -z-0" viewBox="0 0 200 8" preserveAspectRatio="none">
                                <path d="M2,6 L198,6" stroke="#06b6d4" stroke-width="2" stroke-dasharray="6 6" fill="none" opacity="0.5"/>
                            </svg>
                        </span>
                        command center.
                    </h2>

                    <p class="mt-6 text-slate-300 text-lg leading-relaxed border-l-2 border-cyan-400/50 pl-5">
                        AI-driven detection, real-time incident correlation, and automated response.
                        <span class="block text-slate-400 text-base mt-2">Monitor active threats, analyze patterns, respond faster — all in one unified interface.</span>
                    </p>
                    
                    <!-- Dynamic stats row (modern) -->
                    <div class="mt-12 flex flex-wrap gap-5">
                        <div class="flex items-center gap-3 bg-white/5 rounded-2xl px-4 py-2 border border-white/10">
                            <i class="fas fa-shield-alt text-cyan-300 text-xl"></i>
                            <div><span class="text-2xl font-black">99.97%</span><span class="text-slate-400 text-sm"> uptime</span></div>
                        </div>
                        <div class="flex items-center gap-3 bg-white/5 rounded-2xl px-4 py-2 border border-white/10">
                            <i class="fas fa-bolt text-amber-300 text-xl"></i>
                            <div><span class="text-2xl font-black">&lt;120ms</span><span class="text-slate-400 text-sm"> response</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom KPI cards (elevated) -->
            <div class="relative z-10 grid grid-cols-3 gap-5 mt-12">
                <div class="stat-card rounded-2xl bg-black/30 p-5 backdrop-blur-sm">
                    <i class="fas fa-eye text-cyan-300 text-xl mb-2"></i>
                    <p class="text-3xl font-black text-cyan-300">24/7</p>
                    <p class="mt-1 text-xs text-slate-300 uppercase tracking-wider font-semibold">Active Monitoring</p>
                </div>
                <div class="stat-card rounded-2xl bg-black/30 p-5 backdrop-blur-sm">
                    <i class="fas fa-fingerprint text-red-300 text-xl mb-2"></i>
                    <p class="text-3xl font-black text-red-300">ZERO</p>
                    <p class="mt-1 text-xs text-slate-300 uppercase tracking-wider font-semibold">Trust Assumption</p>
                </div>
                <div class="stat-card rounded-2xl bg-black/30 p-5 backdrop-blur-sm">
                    <i class="fas fa-lock text-emerald-300 text-xl mb-2"></i>
                    <p class="text-3xl font-black text-emerald-300">AES-256</p>
                    <p class="mt-1 text-xs text-slate-300 uppercase tracking-wider font-semibold">End-to-End</p>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL: LOGIN ENHANCED (superior design and micro-interactions) -->
        <div class="relative flex items-center justify-center p-6 lg:p-10 bg-[#020617] overflow-y-auto">
            <!-- subtle radial overlay -->
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_30%_40%,#06b6d408,transparent_70%)] pointer-events-none"></div>
            
            <div class="relative w-full max-w-md z-10">
                <!-- Mobile brand (only shows on small screens) -->
                <div class="lg:hidden text-center mb-8">
                    <div class="mx-auto w-16 h-16 rounded-2xl bg-gradient-to-tr from-cyan-500/20 to-blue-600/10 border border-cyan-400/40 flex items-center justify-center shadow-lg shadow-cyan-500/20">
                        <i class="fas fa-shield-haltered text-3xl text-cyan-300"></i>
                    </div>
                    <h1 class="mt-4 text-2xl font-bold tracking-tight bg-gradient-to-r from-white to-cyan-200 bg-clip-text text-transparent">CyberShield</h1>
                    <p class="text-[11px] text-cyan-300 tracking-[0.25em] uppercase mt-1">Secure Access Gateway</p>
                </div>

                <!-- Main Login Card -->
                <div class="login-panel rounded-3xl p-7 md:p-9 transition-all duration-300">
                    <div class="mb-7 text-center lg:text-left">
                        <div class="inline-block rounded-full bg-cyan-400/10 px-3 py-1 text-[11px] font-mono text-cyan-300 border border-cyan-400/20 mb-3">
                            <i class="fas fa-key mr-1"></i> RESTRICTED ACCESS
                        </div>
                        <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight">Welcome back</h2>
                        <p class="mt-2 text-slate-400 text-sm">Sign in to manage security operations & incidents</p>
                    </div>

                    <!-- Session status (if any) - can be dynamic -->
                    @if(session('status'))
                        <div class="mb-5 p-3 rounded-xl bg-emerald-500/10 border border-emerald-400/30 text-emerald-300 text-sm flex items-center gap-2">
                            <i class="fas fa-check-circle"></i> {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-6">
                        @csrf

                        <!-- Email field with modern icon -->
                        <div class="space-y-1">
                            <label class="block text-sm font-semibold text-slate-200 ml-1">
                                <i class="fas fa-envelope text-cyan-300 mr-1 text-xs"></i> Email address
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-user-shield text-slate-500 text-sm"></i>
                                </div>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    value="{{ old('email') }}"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="admin@cybershield.com"
                                    class="w-full rounded-xl pl-10 pr-4 py-3.5 input-dark text-white placeholder-slate-500 outline-none transition-all duration-200 input-glow"
                                />
                            </div>
                            @error('email')
                                <p class="mt-1 text-xs text-red-400 flex items-center gap-1"><i class="fas fa-exclamation-triangle"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password field with show/hide toggle (interactive design) -->
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <label class="block text-sm font-semibold text-slate-200 ml-1">
                                    <i class="fas fa-lock text-cyan-300 mr-1 text-xs"></i> Password
                                </label>
                                <a href="{{ route('password.request') }}" class="text-xs text-cyan-400 hover:text-cyan-300 transition underline-offset-2 hover:underline">Forgot?</a>
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-key text-slate-500 text-sm"></i>
                                </div>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Enter your passkey"
                                    class="w-full rounded-xl pl-10 pr-12 py-3.5 input-dark text-white placeholder-slate-500 outline-none transition-all duration-200 input-glow"
                                />
                                <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-cyan-300 transition">
                                    <i class="fas fa-eye-slash" id="eyeIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1 text-xs text-red-400 flex items-center gap-1"><i class="fas fa-exclamation-triangle"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Remember me + extra micro copy -->
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <label class="inline-flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" name="remember" class="remember-checkbox rounded border-slate-600 bg-slate-800 focus:ring-cyan-400 focus:ring-offset-0">
                                <span class="text-sm text-slate-300 group-hover:text-slate-200 transition">Remember this device</span>
                            </label>
                            <div class="text-xs text-slate-500 bg-white/5 px-2 py-1 rounded-full">
                                <i class="fas fa-fingerprint mr-1"></i> Zero-trust ready
                            </div>
                        </div>

                        <!-- Submit button with modern shine effect -->
                        <button
                            type="submit"
                            class="cyber-button relative w-full rounded-xl py-3.5 font-bold text-[#0a0f1f] text-base shadow-lg shadow-cyan-500/30 flex items-center justify-center gap-2 group"
                        >
                            <span class="relative z-10 flex items-center gap-2"><i class="fas fa-arrow-right-to-bracket"></i> Access Dashboard</span>
                            <div class="absolute inset-0 -translate-x-full bg-gradient-to-r from-white/30 to-transparent skew-x-12 transition-transform duration-500 group-hover:translate-x-full"></div>
                        </button>
                    </form>

                    <!-- additional security verification mock: authenticator hint -->
                    <div class="mt-8 pt-4 border-t border-white/10">
                        <div class="flex items-start gap-3 p-3 rounded-xl bg-gradient-to-r from-cyan-400/5 to-transparent">
                            <div class="mt-0.5 h-8 w-8 rounded-full bg-cyan-500/20 flex items-center justify-center">
                                <i class="fas fa-shield-virus text-cyan-300 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-200 flex items-center gap-1"><i class="fas fa-check-circle text-emerald-400 text-xs"></i> Identity protection active</p>
                                <p class="text-xs text-slate-400 mt-1">Multi-factor authentication recommended for privileged roles.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer micro copy & trust badge -->
                <div class="mt-8 text-center flex flex-col items-center gap-3">
                    <div class="flex gap-3 text-[11px] text-slate-500 font-mono">
                        <span><i class="fas fa-shield-alt"></i> SOC2 Type II</span>
                        <span>•</span>
                        <span><i class="fas fa-lock"></i> ISO 27001</span>
                        <span>•</span>
                        <span><i class="fas fa-globe"></i> GDPR Ready</span>
                    </div>
                    <p class="text-xs text-slate-600">
                        © {{ date('Y') }} CyberShield Security Platform — Unified Defense
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- tiny JS for toggling password visibility + additional micro-interaction -->
    <script>
        (function() {
            const toggleBtn = document.getElementById('togglePassword');
            if(toggleBtn) {
                const passwordInput = document.getElementById('password');
                const eyeIcon = document.getElementById('eyeIcon');
                toggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    eyeIcon.classList.toggle('fa-eye-slash');
                    eyeIcon.classList.toggle('fa-eye');
                });
            }

            // Optional: Add a nice console greeting and dynamic copyright year ( already done )
            console.log("%c🛡️ CyberShield Enterprise SOC — Securing the digital frontier", "color: #06b6d4; font-size: 14px; font-weight: bold;");
            
            // Simulate some futuristic protection pulse (just for style)
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('focus', () => {
                    input.parentElement?.classList.add('ring-1', 'ring-cyan-400/30');
                });
                input.addEventListener('blur', () => {
                    input.parentElement?.classList.remove('ring-1', 'ring-cyan-400/30');
                });
            });
        })();
    </script>

    <!-- note: if any blade directives (x-auth-session-status) exist, they will be processed server-side. For pure frontend demo, it's safe. -->
</body>
</html>
