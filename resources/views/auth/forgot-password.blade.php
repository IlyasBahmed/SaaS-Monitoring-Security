<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>CyberShield | Password Recovery</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800;14..32,900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        body { background-color: #020617; }
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
        .glass-card {
            background: rgba(7, 17, 31, 0.88);
            backdrop-filter: blur(18px);
            border: 1px solid rgba(6, 182, 212, 0.2);
            box-shadow: 0 25px 40px -15px rgba(0, 0, 0, 0.5);
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
        .cyber-submit:active { transform: translateY(1px); }
    </style>
</head>
<body class="bg-[#020617] text-white antialiased">

@php
    $email = old('email', request('email', ''));
@endphp

<main class="min-h-screen bg-[#020617]">
    <div class="grid min-h-screen lg:grid-cols-[1fr_560px]">
        <section class="relative hidden overflow-hidden border-r border-cyan-500/20 bg-gradient-to-b from-[#050b14] to-[#030712] p-10 lg:flex lg:flex-col lg:justify-between">
            <div class="absolute inset-0 animated-grid opacity-40"></div>
            <div class="absolute -top-40 -left-40 h-80 w-80 rounded-full bg-cyan-500/20 blur-[100px] floating-particle"></div>
            <div class="absolute bottom-20 right-10 h-64 w-64 rounded-full bg-blue-600/15 blur-[90px] floating-particle" style="animation-delay: -5s;"></div>

            <div class="relative z-10">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-400/20 to-blue-500/10 border border-cyan-400/40 shadow-xl shadow-cyan-500/20">
                        <i class="fas fa-shield-haltered text-3xl text-cyan-300"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-white to-cyan-200 bg-clip-text text-transparent">CyberShield</h1>
                        <p class="text-[11px] font-mono text-cyan-300 tracking-[0.45em] uppercase">PASSWORD RECOVERY</p>
                    </div>
                </div>

                <div class="mt-20 max-w-xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-cyan-400/30 bg-cyan-500/10 backdrop-blur-sm px-4 py-2 text-xs font-bold text-cyan-200">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-cyan-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-cyan-300"></span>
                        </span>
                        <span><i class="fas fa-envelope-open-text mr-1"></i> RESET LINK DELIVERY</span>
                    </div>

                    <h2 class="mt-10 text-5xl xl:text-6xl font-black leading-[1.2] tracking-tighter">
                        Recover access to
                        <span class="block text-transparent bg-clip-text bg-gradient-to-r from-cyan-300 to-teal-300">your secure portal</span>
                    </h2>

                    <p class="mt-6 text-slate-300 text-lg leading-relaxed border-l-2 border-cyan-400/50 pl-5">
                        We will send a password reset link to your email address.
                        <span class="block text-slate-400 text-base mt-2">Use it to create a new credential and return to the dashboard safely.</span>
                    </p>
                </div>
            </div>
        </section>

        <section class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-8 bg-[#020617] relative">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_40%_30%,#06b6d408,transparent_60%)] pointer-events-none"></div>

            <div class="w-full max-w-lg z-10">
                <div class="mb-9 text-center lg:hidden">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-tr from-cyan-500/20 to-blue-600/10 border border-cyan-400/40 shadow-lg">
                        <i class="fas fa-shield-haltered text-3xl text-cyan-300"></i>
                    </div>
                    <h1 class="mt-4 text-2xl font-bold tracking-tight bg-gradient-to-r from-white to-cyan-200 bg-clip-text text-transparent">CyberShield</h1>
                    <p class="text-[11px] text-cyan-300 tracking-[0.2em] uppercase mt-1">Password Recovery</p>
                </div>

                <div class="glass-card rounded-3xl p-7 md:p-9">
                    <div class="mb-7">
                        <div class="inline-flex items-center gap-2 rounded-full bg-cyan-400/10 px-3 py-1 text-[11px] font-mono text-cyan-300 border border-cyan-400/20">
                            <i class="fas fa-paper-plane"></i> STEP 1 / VERIFY EMAIL
                        </div>
                        <h2 class="mt-4 text-3xl md:text-4xl font-extrabold tracking-tight">Forgot your password?</h2>
                        <p class="mt-2 text-slate-400 text-sm leading-6">
                            Enter your email address and we will send you a secure password reset link.
                        </p>
                    </div>

                    <x-auth-session-status class="mb-5" :status="session('status')" />

                    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                        @csrf

                        <div class="space-y-1">
                            <label for="email" class="block text-sm font-semibold text-slate-200 ml-1">
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
                                    value="{{ $email }}"
                                    required
                                    autofocus
                                    autocomplete="email"
                                    placeholder="admin@cybershield.com"
                                    class="w-full rounded-xl pl-10 pr-4 py-3.5 input-dark text-white placeholder-slate-500 outline-none transition-all duration-200"
                                />
                            </div>
                            @error('email')
                                <p class="mt-1 text-xs text-red-400 flex items-center gap-1">
                                    <i class="fas fa-exclamation-triangle"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <button
                            type="submit"
                            class="cyber-submit relative w-full rounded-xl py-3.5 font-bold text-[#0a0f1f] text-base shadow-lg shadow-cyan-500/30 flex items-center justify-center gap-2 group"
                        >
                            <span class="relative z-10 flex items-center gap-2">
                                <i class="fas fa-paper-plane"></i> Send reset link
                            </span>
                            <div class="absolute inset-0 -translate-x-full bg-gradient-to-r from-white/30 to-transparent skew-x-12 transition-transform duration-500 group-hover:translate-x-full"></div>
                        </button>
                    </form>

                    <div class="mt-7 rounded-xl border border-slate-700/70 bg-slate-950/40 p-4">
                        <p class="text-sm font-semibold text-slate-200 flex items-center gap-2">
                            <i class="fas fa-shield-virus text-emerald-400 text-xs"></i> Secure delivery
                        </p>
                        <p class="mt-1 text-xs text-slate-400 leading-relaxed">
                            The reset link expires after a short time and can only be used once.
                        </p>
                    </div>

                    <div class="mt-6 text-center">
                        <a href="{{ route('login') }}" class="text-sm text-cyan-400 hover:text-cyan-300 transition underline-offset-2 hover:underline">
                            Back to sign in
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

</body>
</html>
