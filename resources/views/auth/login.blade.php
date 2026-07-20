<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberShield | Secure Access</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        body { background: #020617; }
        .field:focus {
            border-color: #22d3ee;
            box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.14);
        }
        .access-button {
            background: linear-gradient(135deg, #22d3ee, #2dd4bf);
            box-shadow: 0 18px 42px -22px rgba(34, 211, 238, 0.85);
        }
        .grid-plane {
            background-image:
                linear-gradient(rgba(148, 163, 184, 0.08) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148, 163, 184, 0.08) 1px, transparent 1px);
            background-size: 36px 36px;
        }
    </style>
</head>
<body class="antialiased text-white">
    <main class="min-h-screen bg-[#020617] lg:grid lg:grid-cols-[minmax(0,1.08fr)_minmax(420px,0.92fr)]">
        <section class="relative hidden overflow-hidden border-r border-white/10 bg-[#050b14] lg:flex">
            <div class="absolute inset-0 grid-plane"></div>
            <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(8,145,178,0.18),transparent_42%),linear-gradient(0deg,rgba(2,6,23,0.1),rgba(2,6,23,0.72))]"></div>

            <div class="relative z-10 flex min-h-screen w-full flex-col justify-between p-10 xl:p-12">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg border border-cyan-300/35 bg-cyan-300/10">
                        <svg class="h-6 w-6 text-cyan-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 5.5 5.4v5.4c0 4.2 2.6 8 6.5 9.2 3.9-1.2 6.5-5 6.5-9.2V5.4L12 3Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9.4 12.1 1.8 1.8 3.8-4" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black tracking-tight">CyberShield</h1>
                        <p class="mt-1 text-xs font-bold uppercase tracking-[0.28em] text-cyan-300">Defense Console</p>
                    </div>
                </div>

                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-md border border-cyan-300/25 bg-cyan-300/10 px-3 py-2 text-xs font-bold uppercase tracking-[0.18em] text-cyan-200">
                        <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                        Protected session gateway
                    </div>
                    <h2 class="mt-8 max-w-xl text-5xl font-black leading-tight tracking-tight xl:text-6xl">
                        Access your security operations center.
                    </h2>
                    <p class="mt-5 max-w-xl text-base leading-7 text-slate-300">
                        Review incidents, monitor protected projects, and respond to active signals from one controlled workspace.
                    </p>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div class="rounded-lg border border-white/10 bg-white/[0.04] p-4">
                        <p class="text-2xl font-black text-cyan-200">24/7</p>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Monitoring</p>
                    </div>
                    <div class="rounded-lg border border-white/10 bg-white/[0.04] p-4">
                        <p class="text-2xl font-black text-emerald-200">MFA</p>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Ready</p>
                    </div>
                    <div class="rounded-lg border border-white/10 bg-white/[0.04] p-4">
                        <p class="text-2xl font-black text-amber-200">SOC</p>
                        <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Workflow</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="flex min-h-screen items-center justify-center px-5 py-8 sm:px-8 lg:px-10">
            <div class="w-full max-w-md">
                <div class="mb-8 text-center lg:hidden">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg border border-cyan-300/35 bg-cyan-300/10">
                        <svg class="h-6 w-6 text-cyan-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 5.5 5.4v5.4c0 4.2 2.6 8 6.5 9.2 3.9-1.2 6.5-5 6.5-9.2V5.4L12 3Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9.4 12.1 1.8 1.8 3.8-4" />
                        </svg>
                    </div>
                    <h1 class="mt-3 text-2xl font-black">CyberShield</h1>
                    <p class="mt-1 text-xs font-bold uppercase tracking-[0.24em] text-cyan-300">Defense Console</p>
                </div>

                <div class="rounded-lg border border-white/10 bg-[#07111f] p-6 shadow-2xl shadow-black/40 sm:p-8">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-cyan-300">Secure login</p>
                        <h2 class="mt-3 text-3xl font-black tracking-tight">Welcome back</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-400">Sign in with your authorized account to continue.</p>
                    </div>

                    @if(session('status'))
                        <div class="mt-6 rounded-lg border border-emerald-400/25 bg-emerald-400/10 px-4 py-3 text-sm font-semibold text-emerald-200">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="block text-sm font-bold text-slate-200">Email address</label>
                            <div class="relative mt-2">
                                <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6.75A2.75 2.75 0 0 1 6.75 4h10.5A2.75 2.75 0 0 1 20 6.75v10.5A2.75 2.75 0 0 1 17.25 20H6.75A2.75 2.75 0 0 1 4 17.25V6.75Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 7 7 5 7-5" />
                                </svg>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@company.com" class="field h-12 w-full rounded-lg border border-slate-700 bg-[#020617] pl-10 pr-4 text-sm font-semibold text-white outline-none transition placeholder:text-slate-600">
                            </div>
                            @error('email')
                                <p class="mt-2 text-xs font-semibold text-red-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <label for="password" class="block text-sm font-bold text-slate-200">Password</label>
                                <a href="{{ route('password.request') }}" class="text-xs font-bold text-cyan-300 transition hover:text-cyan-200">Forgot password?</a>
                            </div>
                            <div class="relative mt-2">
                                <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10V8a4 4 0 1 1 8 0v2" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 10h10.5A1.75 1.75 0 0 1 19 11.75v6.5A1.75 1.75 0 0 1 17.25 20H6.75A1.75 1.75 0 0 1 5 18.25v-6.5A1.75 1.75 0 0 1 6.75 10Z" />
                                </svg>
                                <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="Enter your password" class="field h-12 w-full rounded-lg border border-slate-700 bg-[#020617] pl-10 pr-12 text-sm font-semibold text-white outline-none transition placeholder:text-slate-600">
                                <button type="button" id="togglePassword" class="absolute right-3 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-md text-slate-500 transition hover:bg-white/5 hover:text-cyan-200" aria-label="Show password">
                                    <svg id="eyeOpen" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14.75A2.75 2.75 0 1 0 12 9.25a2.75 2.75 0 0 0 0 5.5Z" />
                                    </svg>
                                    <svg id="eyeClosed" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.6 10.7a2.75 2.75 0 0 0 3.7 3.7" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.1 7.6C4.2 9.1 2.5 12 2.5 12s3.5 6 9.5 6c1.4 0 2.7-.3 3.8-.8M19.1 15.3c1.5-1.3 2.4-3.3 2.4-3.3s-3.5-6-9.5-6c-.8 0-1.6.1-2.3.3" />
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-2 text-xs font-semibold text-red-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-300">
                                <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-700 bg-[#020617] text-cyan-400 focus:ring-cyan-300 focus:ring-offset-0">
                                Remember this device
                            </label>
                            <span class="rounded-md border border-white/10 bg-white/[0.04] px-2.5 py-1 text-xs font-bold text-slate-400">Encrypted</span>
                        </div>

                        <button type="submit" class="access-button flex h-12 w-full items-center justify-center gap-2 rounded-lg text-sm font-black text-slate-950 transition hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-cyan-200 focus:ring-offset-2 focus:ring-offset-[#07111f]">
                            Access dashboard
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                            </svg>
                        </button>
                    </form>
                </div>

                <p class="mt-6 text-center text-xs font-semibold text-slate-600">
                    Copyright {{ date('Y') }} CyberShield Security Platform
                </p>
            </div>
        </section>
    </main>

    <script>
        (function () {
            const toggle = document.getElementById('togglePassword');
            const input = document.getElementById('password');
            const eyeOpen = document.getElementById('eyeOpen');
            const eyeClosed = document.getElementById('eyeClosed');

            if (!toggle || !input || !eyeOpen || !eyeClosed) {
                return;
            }

            toggle.addEventListener('click', function () {
                const visible = input.getAttribute('type') === 'text';
                input.setAttribute('type', visible ? 'password' : 'text');
                toggle.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
                eyeOpen.classList.toggle('hidden', visible);
                eyeClosed.classList.toggle('hidden', !visible);
            });
        })();
    </script>
</body>
</html>
