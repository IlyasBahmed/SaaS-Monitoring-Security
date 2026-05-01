
    
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberShield Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen bg-[#020617] text-white grid lg:grid-cols-2 overflow-hidden">
        {{-- Left Branding Panel --}}
        <div class="hidden lg:flex relative flex-col justify-between p-12 bg-[#050b14] border-r border-cyan-500/10">
            <div class="absolute inset-0 opacity-40 bg-[radial-gradient(circle_at_20%_20%,#06b6d433,transparent_30%),radial-gradient(circle_at_80%_70%,#2563eb33,transparent_35%)]"></div>
            <div class="absolute inset-0 bg-[linear-gradient(rgba(34,211,238,.04)_1px,transparent_1px),linear-gradient(90deg,rgba(34,211,238,.04)_1px,transparent_1px)] bg-[size:36px_36px]"></div>

            <div class="relative">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-cyan-400/15 border border-cyan-300/30 flex items-center justify-center shadow-lg shadow-cyan-500/20">
                        <svg class="w-7 h-7 text-cyan-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3l7 4v5c0 5-3.5 8-7 9-3.5-1-7-4-7-9V7l7-4z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-wide">CyberShield</h1>
                        <p class="text-xs text-cyan-300 tracking-[0.35em] uppercase">Enterprise SOC</p>
                    </div>
                </div>

                <div class="mt-24 max-w-xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-cyan-400/20 bg-cyan-400/10 px-4 py-2 text-xs text-cyan-200">
                        <span class="h-2 w-2 rounded-full bg-cyan-300 animate-pulse"></span>
                        Real-time Threat Intelligence
                    </div>

                    <h2 class="mt-8 text-5xl font-black leading-tight tracking-tight">
                        Secure access to your
                        <span class="text-cyan-300">cyber defense</span>
                        command center.
                    </h2>

                    <p class="mt-6 text-slate-400 text-lg leading-8">
                        Monitor active threats, analyze incidents, manage protected assets,
                        and respond faster from one unified security platform.
                    </p>
                </div>
            </div>

            <div class="relative grid grid-cols-3 gap-4">
                <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-5">
                    <p class="text-3xl font-bold text-cyan-300">24/7</p>
                    <p class="mt-1 text-xs text-slate-400 uppercase tracking-wider">Monitoring</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-5">
                    <p class="text-3xl font-bold text-red-300">0</p>
                    <p class="mt-1 text-xs text-slate-400 uppercase tracking-wider">Trust Access</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-5">
                    <p class="text-3xl font-bold text-emerald-300">SSL</p>
                    <p class="mt-1 text-xs text-slate-400 uppercase tracking-wider">Validated</p>
                </div>
            </div>
        </div>

        {{-- Login Panel --}}
        <div class="relative flex items-center justify-center p-6 lg:p-12 bg-[#020617]">
            <div class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_top,#06b6d433,transparent_35%)]"></div>

            <div class="relative w-full max-w-md">
                <div class="lg:hidden mb-10 text-center">
                    <div class="mx-auto h-14 w-14 rounded-2xl bg-cyan-400/15 border border-cyan-300/30 flex items-center justify-center">
                        <svg class="w-8 h-8 text-cyan-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3l7 4v5c0 5-3.5 8-7 9-3.5-1-7-4-7-9V7l7-4z" />
                        </svg>
                    </div>
                    <h1 class="mt-4 text-2xl font-bold">CyberShield</h1>
                    <p class="text-xs text-cyan-300 tracking-[0.3em] uppercase">Enterprise SOC</p>
                </div>

                <div class="rounded-3xl border border-white/10 bg-[#07111f]/90 shadow-2xl shadow-cyan-950/40 backdrop-blur-xl p-8">
                    <div class="mb-8">
                        <p class="text-xs uppercase tracking-[0.3em] text-cyan-300">Secure Portal</p>
                        <h2 class="mt-3 text-3xl font-bold">Sign in</h2>
                        <p class="mt-2 text-sm text-slate-400">
                            Authenticate to continue to the SOC dashboard.
                        </p>
                    </div>

                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-300 mb-2">
                                Email address
                            </label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="admin@cybershield.com"
                                class="w-full rounded-2xl border border-slate-700/80 bg-[#020617] px-4 py-3.5 text-white placeholder-slate-600 outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-400/10"
                            />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label for="password" class="block text-sm font-medium text-slate-300">
                                    Password
                                </label>
                            </div>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••••"
                                class="w-full rounded-2xl border border-slate-700/80 bg-[#020617] px-4 py-3.5 text-white placeholder-slate-600 outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-400/10"
                            />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="inline-flex items-center gap-3 text-sm text-slate-400">
                                <input
                                    type="checkbox"
                                    name="remember"
                                    class="rounded border-slate-700 bg-[#020617] text-cyan-400 focus:ring-cyan-400"
                                >
                                Remember this device
                            </label>
                        </div>

                        <button
                            type="submit"
                            class="group relative w-full overflow-hidden rounded-2xl bg-cyan-400 px-5 py-3.5 font-bold text-slate-950 shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-300"
                        >
                            <span class="relative z-10">Access Dashboard</span>
                            <span class="absolute inset-0 -translate-x-full bg-white/30 transition group-hover:translate-x-full"></span>
                        </button>
                    </form>

                    <div class="mt-8 rounded-2xl border border-cyan-400/10 bg-cyan-400/[0.04] p-4">
                        <div class="flex items-start gap-3">
                            <div class="mt-1 h-2.5 w-2.5 rounded-full bg-emerald-400"></div>
                            <div>
                                <p class="text-sm font-semibold text-slate-200">Protected session</p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    Unauthorized access is monitored and logged by the security platform.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="mt-6 text-center text-xs text-slate-600">
                    © {{ date('Y') }} CyberShield Security Platform
                </p>
            </div>
        </div>
    </div>
</body>
</html>