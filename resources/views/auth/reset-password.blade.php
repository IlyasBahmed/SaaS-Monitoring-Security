<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>CyberShield | Secure Credential Activation</title>
    <!-- Google Fonts + Tailwind + Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800;14..32,900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        body {
            background-color: #020617;
        }
        /* Custom animations & utilities */
        .animated-grid {
            background-image: 
                linear-gradient(rgba(34, 211, 238, 0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(34, 211, 238, 0.06) 1px, transparent 1px);
            background-size: 42px 42px;
            animation: driftGrid 22s linear infinite;
        }
        @keyframes driftGrid {
            0% { background-position: 0 0; }
            100% { background-position: 42px 42px; }
        }
        .floating-particle {
            animation: floatParticle 14s ease-in-out infinite;
        }
        @keyframes floatParticle {
            0%, 100% { transform: translateY(0) translateX(0) scale(1); opacity: 0.3; }
            50% { transform: translateY(-25px) translateX(12px) scale(1.1); opacity: 0.6; }
        }
        .stat-tile {
            backdrop-filter: blur(8px);
            transition: all 0.25s ease;
            border: 1px solid rgba(6, 182, 212, 0.18);
        }
        .stat-tile:hover {
            border-color: rgba(6, 182, 212, 0.5);
            background: rgba(6, 182, 212, 0.07);
            transform: translateY(-3px);
        }
        .password-strength-bar {
            transition: width 0.3s ease, background 0.2s;
        }
        .input-dark {
            background-color: rgba(2, 6, 23, 0.85);
            border: 1px solid #1e293b;
            transition: all 0.2s;
        }
        .input-dark:focus {
            border-color: #06b6d4;
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.2);
            background-color: #020617;
        }
        .cyber-submit {
            background: linear-gradient(100deg, #06b6d4, #14b8a6);
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }
        .cyber-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 25px -12px rgba(6, 182, 212, 0.6);
        }
        .cyber-submit:active {
            transform: translateY(1px);
        }
        .glass-card {
            background: rgba(7, 17, 31, 0.88);
            backdrop-filter: blur(18px);
            border: 1px solid rgba(6, 182, 212, 0.2);
            box-shadow: 0 25px 40px -15px rgba(0, 0, 0, 0.5);
        }
        .requirement-badge {
            transition: all 0.2s;
        }
        .requirement-badge.valid {
            color: #2dd4bf;
            border-color: #2dd4bf40;
            background: #2dd4bf10;
        }
        .requirement-badge i {
            font-size: 0.7rem;
        }
        .show-password-btn {
            cursor: pointer;
            transition: color 0.2s;
        }
        .show-password-btn:hover {
            color: #06b6d4;
        }
        .pulse-dot {
            box-shadow: 0 0 0 0 rgba(6, 182, 212, 0.7);
            animation: pulse-ring 1.8s infinite;
        }
        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(6, 182, 212, 0.6); }
            70% { box-shadow: 0 0 0 6px rgba(6, 182, 212, 0); }
            100% { box-shadow: 0 0 0 0 rgba(6, 182, 212, 0); }
        }
        /* custom scroll */
        ::-webkit-scrollbar {
            width: 5px;
        }
        ::-webkit-scrollbar-track {
            background: #0a1220;
        }
        ::-webkit-scrollbar-thumb {
            background: #06b6d4;
            border-radius: 10px;
        }
    </style>
</head>
<body class="bg-[#020617] text-white antialiased">

@php
    $email = old('email', $request->email ?? '');
    $isClientSetup = strtolower((string) ($setupUser->role ?? '')) === 'client';
    $displayName = $setupClient->company_name ?? $setupUser->name ?? 'your account';
@endphp

<main class="min-h-screen bg-[#020617]">
    <div class="grid min-h-screen lg:grid-cols-[1fr_580px]">
        <!-- LEFT PANEL - Premium Brand & Security Narrative (Enhanced) -->
        <section class="relative hidden overflow-hidden border-r border-cyan-500/20 bg-gradient-to-b from-[#050b14] to-[#030712] p-10 lg:flex lg:flex-col lg:justify-between">
            <!-- deep cyber textures -->
            <div class="absolute inset-0 animated-grid opacity-40"></div>
            <div class="absolute -top-40 -left-40 h-80 w-80 rounded-full bg-cyan-500/20 blur-[100px] floating-particle"></div>
            <div class="absolute bottom-20 right-10 h-64 w-64 rounded-full bg-blue-600/15 blur-[90px] floating-particle" style="animation-delay: -5s;"></div>
            
            <!-- brand area -->
            <div class="relative z-10">
                <div class="flex items-center gap-4 group">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-400/20 to-blue-500/10 border border-cyan-400/40 shadow-xl shadow-cyan-500/20 transition duration-300 group-hover:border-cyan-400/70 group-hover:scale-105">
                        <i class="fas fa-shield-haltered text-3xl text-cyan-300 filter drop-shadow-md"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-white to-cyan-200 bg-clip-text text-transparent">CyberShield</h1>
                        <p class="text-[11px] font-mono text-cyan-300 tracking-[0.45em] uppercase">ZERO TRUST VAULT</p>
                    </div>
                </div>

                <div class="mt-20 max-w-xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-cyan-400/30 bg-cyan-500/10 backdrop-blur-sm px-4 py-2 text-xs font-bold text-cyan-200">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-cyan-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-cyan-300"></span>
                        </span>
                        <span><i class="fas fa-fingerprint mr-1"></i> PASSWORD INITIALIZATION</span>
                    </div>

                    <h2 class="mt-10 text-5xl xl:text-6xl font-black leading-[1.2] tracking-tighter">
                        Activate access for
                        <span class="relative inline-block">
                            <span class="relative z-10 text-transparent bg-clip-text bg-gradient-to-r from-cyan-300 to-teal-300">{{ $displayName }}</span>
                            <svg class="absolute bottom-1 left-0 w-full h-2 -z-0" viewBox="0 0 200 6" preserveAspectRatio="none">
                                <path d="M2,4 L198,4" stroke="#06b6d4" stroke-width="2" stroke-dasharray="4 4" fill="none" opacity="0.6"/>
                            </svg>
                        </span>
                    </h2>

                    <p class="mt-6 text-slate-300 text-lg leading-relaxed border-l-2 border-cyan-400/50 pl-5">
                        Define a strong, encrypted credential to unlock the CyberShield command portal. 
                        <span class="block text-slate-400 text-base mt-2">End-to-end protection & real-time threat monitoring enabled.</span>
                    </p>

                    <!-- dynamic trust metrics -->
                    <div class="mt-12 flex flex-wrap gap-4">
                        <div class="flex items-center gap-2 bg-white/5 rounded-xl px-4 py-2 border border-white/10">
                            <i class="fas fa-key text-cyan-300 text-sm"></i>
                            <span class="text-xs font-mono text-slate-300">AES-256/GCM</span>
                        </div>
                        <div class="flex items-center gap-2 bg-white/5 rounded-xl px-4 py-2 border border-white/10">
                            <i class="fas fa-shield-alt text-emerald-300 text-sm"></i>
                            <span class="text-xs font-mono text-slate-300">SOC2 Type II</span>
                        </div>
                        <div class="flex items-center gap-2 bg-white/5 rounded-xl px-4 py-2 border border-white/10">
                            <i class="fas fa-clock text-sky-300 text-sm"></i>
                            <span class="text-xs font-mono text-slate-300">Session TTL: 15min</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- bottom status cards (modernized KPIs) -->
            <div class="relative z-10 grid grid-cols-3 gap-5 mt-14">
                <div class="stat-tile rounded-2xl bg-black/30 p-5 backdrop-blur-sm">
                    <i class="fas fa-laptop-code text-cyan-300 text-xl mb-2"></i>
                    <p class="text-2xl font-black text-cyan-300">FIPS</p>
                    <p class="mt-1 text-[11px] text-slate-300 uppercase tracking-wider font-semibold">140-3 Validated</p>
                </div>
                <div class="stat-tile rounded-2xl bg-black/30 p-5 backdrop-blur-sm">
                    <i class="fas fa-chart-line text-emerald-300 text-xl mb-2"></i>
                    <p class="text-2xl font-black text-emerald-300">99.99%</p>
                    <p class="mt-1 text-[11px] text-slate-300 uppercase tracking-wider font-semibold">SLA Uptime</p>
                </div>
                <div class="stat-tile rounded-2xl bg-black/30 p-5 backdrop-blur-sm">
                    <i class="fas fa-user-secret text-purple-300 text-xl mb-2"></i>
                    <p class="text-2xl font-black text-purple-300">MFA</p>
                    <p class="mt-1 text-[11px] text-slate-300 uppercase tracking-wider font-semibold">Ready</p>
                </div>
            </div>
        </section>

        <!-- RIGHT PANEL: PROFESSIONAL PASSWORD SETUP (Elevated) -->
        <section class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-8 bg-[#020617] relative">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_40%_30%,#06b6d408,transparent_60%)] pointer-events-none"></div>
            
            <div class="w-full max-w-lg z-10">
                <!-- Mobile brand -->
                <div class="mb-9 text-center lg:hidden">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-tr from-cyan-500/20 to-blue-600/10 border border-cyan-400/40 shadow-lg">
                        <i class="fas fa-shield-haltered text-3xl text-cyan-300"></i>
                    </div>
                    <h1 class="mt-4 text-2xl font-bold tracking-tight bg-gradient-to-r from-white to-cyan-200 bg-clip-text text-transparent">CyberShield</h1>
                    <p class="text-[11px] text-cyan-300 tracking-[0.2em] uppercase mt-1">Secure Credential Setup</p>
                </div>

                <!-- glassmorphism card -->
                <div class="glass-card rounded-3xl p-7 md:p-9 transition-all duration-300">
                    <div class="mb-7">
                        <div class="inline-flex items-center gap-2 rounded-full bg-cyan-400/10 px-3 py-1 text-[11px] font-mono text-cyan-300 border border-cyan-400/20">
                            <i class="fas fa-database"></i> STEP 2 / SECURE VAULT
                        </div>
                        <h2 class="mt-4 text-3xl md:text-4xl font-extrabold tracking-tight">Define password</h2>
                        <p class="mt-2 text-slate-400 text-sm flex items-center gap-1">
                            <i class="fas fa-lock text-[10px] text-cyan-300"></i> 
                            Create a strong credential for <span class="font-medium text-white">{{ $isClientSetup ? 'client portal' : 'enterprise access' }}</span>
                        </p>
                    </div>

                    <form method="POST" action="{{ route('password.store') }}" class="space-y-6" id="passwordSetupForm">
                        @csrf
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <!-- Email (readonly style but enhanced) -->
                        <div class="space-y-1">
                            <label class="block text-sm font-semibold text-slate-200 ml-1">
                                <i class="fas fa-envelope text-cyan-300 mr-1 text-xs"></i> Verified email
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-check-circle text-emerald-400 text-sm"></i>
                                </div>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    value="{{ $email }}"
                                    required
                                    autocomplete="username"
                                    @readonly(filled($email))
                                    class="w-full rounded-xl pl-12 pr-4 py-3.5 input-dark text-white placeholder-slate-500 outline-none transition {{ filled($email) ? 'bg-slate-900/80 border-slate-600 text-slate-300' : '' }}"
                                >
                            </div>
                            @error('email')
                                <p class="text-xs text-red-400 mt-1"><i class="fas fa-exclamation-triangle mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- New Password Field with strength meter -->
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-200 ml-1">
                                <i class="fas fa-key text-cyan-300 mr-1 text-xs"></i> New password
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-slate-400 text-sm"></i>
                                </div>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    autofocus
                                    autocomplete="new-password"
                                    placeholder="Enter a strong passphrase"
                                    class="w-full rounded-xl pl-10 pr-12 py-3.5 input-dark text-white placeholder-slate-500 outline-none transition"
                                >
                                <button type="button" class="show-password-btn absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400" data-target="password">
                                    <i class="fas fa-eye-slash text-sm"></i>
                                </button>
                            </div>
                            
                            <!-- password strength indicator & requirements -->
                            <div class="mt-2 space-y-2">
                                <div class="h-1.5 w-full bg-slate-800 rounded-full overflow-hidden">
                                    <div id="strengthBar" class="password-strength-bar h-full w-0 rounded-full bg-red-500 transition-all duration-300"></div>
                                </div>
                                <div class="flex flex-wrap gap-2 text-[11px] font-mono">
                                    <span id="lengthReq" class="requirement-badge flex items-center gap-1 px-2 py-0.5 rounded-full border border-slate-700 text-slate-400"><i class="far fa-circle"></i> 8+ chars</span>
                                    <span id="upperReq" class="requirement-badge flex items-center gap-1 px-2 py-0.5 rounded-full border border-slate-700 text-slate-400"><i class="far fa-circle"></i> Uppercase</span>
                                    <span id="numberReq" class="requirement-badge flex items-center gap-1 px-2 py-0.5 rounded-full border border-slate-700 text-slate-400"><i class="far fa-circle"></i> Number</span>
                                    <span id="specialReq" class="requirement-badge flex items-center gap-1 px-2 py-0.5 rounded-full border border-slate-700 text-slate-400"><i class="far fa-circle"></i> Special</span>
                                </div>
                            </div>
                            @error('password')
                                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="space-y-1">
                            <label class="block text-sm font-semibold text-slate-200 ml-1">
                                <i class="fas fa-check-double text-cyan-300 mr-1 text-xs"></i> Confirm password
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-redo-alt text-slate-400 text-sm"></i>
                                </div>
                                <input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Re-enter your password"
                                    class="w-full rounded-xl pl-10 pr-12 py-3.5 input-dark text-white outline-none transition"
                                >
                                <button type="button" class="show-password-btn absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400" data-target="password_confirmation">
                                    <i class="fas fa-eye-slash text-sm"></i>
                                </button>
                            </div>
                            <div id="matchFeedback" class="text-xs mt-1 hidden"></div>
                            @error('password_confirmation')
                                <p class="text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Advanced security notice and submit -->
                        <button
                            type="submit"
                            id="submitBtn"
                            class="cyber-submit relative w-full rounded-xl py-3.5 font-bold text-[#0a0f1f] text-base shadow-lg shadow-cyan-500/30 flex items-center justify-center gap-3 group mt-4"
                        >
                            <i class="fas fa-shield-alt text-slate-900 group-hover:scale-110 transition"></i>
                            <span>Initialize secure access</span>
                            <i class="fas fa-arrow-right text-slate-900 text-sm group-hover:translate-x-1 transition"></span>
                        </button>
                    </form>

                    <!-- compliance & trusted badge -->
                    <div class="mt-7 rounded-xl border border-emerald-400/15 bg-emerald-500/[0.03] p-4 flex items-start gap-3">
                        <div class="mt-0.5 h-7 w-7 rounded-full bg-emerald-500/20 flex items-center justify-center">
                            <i class="fas fa-shield-virus text-emerald-300 text-xs"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-200 flex items-center gap-1"><i class="fas fa-check-circle text-emerald-400 text-xs"></i> Zero-knowledge encryption</p>
                            <p class="text-xs text-slate-400 mt-1 leading-relaxed">Your password is never stored in plaintext. Credentials are hashed using bcrypt + salted peppers.</p>
                        </div>
                    </div>
                </div>
                <div class="mt-8 text-center text-[11px] text-slate-600 flex flex-wrap justify-center gap-4">
                    <span><i class="fas fa-shield-alt"></i> ISO 27001:2022</span>
                    <span>•</span>
                    <span><i class="fas fa-globe"></i> GDPR ready</span>
                    <span>•</span>
                    <span><i class="fas fa-database"></i> Encrypted at rest</span>
                </div>
            </div>
        </section>
    </div>
</main>

<script>
    (function(){
        // Password visibility toggle
        const toggleBtns = document.querySelectorAll('.show-password-btn');
        toggleBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                if(input) {
                    const type = input.type === 'password' ? 'text' : 'password';
                    input.type = type;
                    const icon = this.querySelector('i');
                    icon.classList.toggle('fa-eye-slash');
                    icon.classList.toggle('fa-eye');
                }
            });
        });

        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('password_confirmation');
        const strengthBar = document.getElementById('strengthBar');
        const lengthReq = document.getElementById('lengthReq');
        const upperReq = document.getElementById('upperReq');
        const numberReq = document.getElementById('numberReq');
        const specialReq = document.getElementById('specialReq');
        const matchFeedback = document.getElementById('matchFeedback');

        function evaluateStrength(pwd) {
            let score = 0;
            const checks = {
                length: pwd.length >= 8,
                upper: /[A-Z]/.test(pwd),
                lower: /[a-z]/.test(pwd),
                number: /[0-9]/.test(pwd),
                special: /[^A-Za-z0-9]/.test(pwd)
            };
            
            // update badges
            updateReqBadge(lengthReq, checks.length);
            updateReqBadge(upperReq, checks.upper);
            updateReqBadge(numberReq, checks.number);
            updateReqBadge(specialReq, checks.special);
            
            if(checks.length) score++;
            if(checks.upper) score++;
            if(checks.lower) score++;
            if(checks.number) score++;
            if(checks.special) score++;
            
            let width = 0;
            let color = '#ef4444';
            if(score === 0) { width = 0; color = '#ef4444'; }
            else if(score <= 2) { width = 25; color = '#f97316'; }
            else if(score === 3) { width = 50; color = '#eab308'; }
            else if(score === 4) { width = 75; color = '#2dd4bf'; }
            else { width = 100; color = '#10b981'; }
            
            strengthBar.style.width = width + '%';
            strengthBar.style.backgroundColor = color;
            return checks;
        }
        
        function updateReqBadge(element, isValid) {
            if(isValid) {
                element.classList.add('valid');
                element.classList.remove('text-slate-400', 'border-slate-700');
                element.classList.add('text-emerald-300', 'border-emerald-500/50', 'bg-emerald-500/10');
                element.innerHTML = '<i class="fas fa-check-circle"></i> ' + element.innerText.replace(/[✓]/, '').trim();
            } else {
                element.classList.remove('valid', 'text-emerald-300', 'border-emerald-500/50', 'bg-emerald-500/10');
                element.classList.add('text-slate-400', 'border-slate-700');
                element.innerHTML = '<i class="far fa-circle"></i> ' + element.innerText.replace(/[✓]/, '').trim();
            }
        }
        
        function checkMatch() {
            if(confirmInput.value.length === 0) {
                matchFeedback.classList.add('hidden');
                return;
            }
            if(passwordInput.value === confirmInput.value && passwordInput.value.length > 0) {
                matchFeedback.innerHTML = '<i class="fas fa-check-circle text-emerald-400 mr-1"></i> Passwords match';
                matchFeedback.classList.remove('hidden', 'text-red-400');
                matchFeedback.classList.add('text-emerald-400');
            } else {
                matchFeedback.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i> Passwords do not match';
                matchFeedback.classList.remove('hidden', 'text-emerald-400');
                matchFeedback.classList.add('text-red-400');
            }
        }
        
        passwordInput.addEventListener('input', function() {
            evaluateStrength(passwordInput.value);
            if(confirmInput.value.length) checkMatch();
        });
        confirmInput.addEventListener('input', checkMatch);
        
        const form = document.getElementById('passwordSetupForm');
        form.addEventListener('submit', function(e) {
            const pwd = passwordInput.value;
            const confirm = confirmInput.value;
            const checks = evaluateStrength(pwd);
            if(pwd !== confirm) {
                e.preventDefault();
                matchFeedback.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i> Passwords must match';
                matchFeedback.classList.remove('hidden');
                matchFeedback.classList.add('text-red-400');
                matchFeedback.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            if(pwd.length < 8 || !checks.upper || !checks.number || !checks.special) {
                e.preventDefault();
                alert('Security requirement: password must contain at least 8 characters, one uppercase, one number and one special character.');
            }
        });
        
        // initial check if any default values exist
        if(passwordInput.value) evaluateStrength(passwordInput.value);
    })();
</script>
</body>
</html>