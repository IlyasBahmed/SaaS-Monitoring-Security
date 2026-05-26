<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page not found | CyberShield</title>

    <script>
        (() => {
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            if (savedTheme === 'light' || (!savedTheme && !prefersDark)) {
                document.documentElement.classList.remove('dark');
            } else {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen overflow-x-hidden bg-slate-50 text-slate-950 antialiased transition-colors duration-300 dark:bg-[#061014] dark:text-white">
<div class="fixed inset-0 -z-10 overflow-hidden">
    <canvas id="hero3d" class="h-full w-full"></canvas>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_14%_16%,rgba(20,184,166,0.20),transparent_28%),radial-gradient(circle_at_82%_22%,rgba(244,63,94,0.16),transparent_24%),linear-gradient(135deg,rgba(248,250,252,0.92),rgba(224,242,254,0.76)_46%,rgba(255,247,237,0.72))] dark:bg-[radial-gradient(circle_at_14%_16%,rgba(45,212,191,0.18),transparent_28%),radial-gradient(circle_at_82%_22%,rgba(251,113,133,0.13),transparent_24%),linear-gradient(135deg,rgba(6,16,20,0.90),rgba(15,23,42,0.78)_46%,rgba(25,18,32,0.78))]"></div>
    <div class="absolute inset-0 bg-[linear-gradient(rgba(15,23,42,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(15,23,42,0.05)_1px,transparent_1px)] bg-[length:46px_46px] dark:bg-[linear-gradient(rgba(45,212,191,0.06)_1px,transparent_1px),linear-gradient(90deg,rgba(45,212,191,0.06)_1px,transparent_1px)]"></div>
</div>

<header class="fixed left-0 top-0 z-50 w-full px-4 py-4 sm:px-6">
    <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 rounded-lg border border-slate-200/80 bg-white/[0.82] px-4 py-3 shadow-xl shadow-slate-900/10 backdrop-blur-2xl transition-colors dark:border-white/10 dark:bg-[#071217]/[0.82] dark:shadow-black/25 sm:px-5">
        <a href="{{ url('/') }}" class="flex min-w-0 items-center gap-3">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-lg border border-teal-500/20 bg-teal-500/10 text-teal-700 dark:border-teal-300/30 dark:bg-teal-300/10 dark:text-teal-200">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 3 5 6v5c0 4.7 3 8.4 7 10 4-1.6 7-5.3 7-10V6l-7-3Z"/>
                    <path d="m9 12 2 2 4-5"/>
                </svg>
            </span>
            <span class="min-w-0">
                <span class="block truncate text-lg font-black tracking-tight">CyberShield</span>
                <span class="block truncate text-xs font-bold uppercase tracking-[0.18em] text-teal-700 dark:text-teal-200">Security Platform</span>
            </span>
        </a>

        <div class="flex shrink-0 items-center gap-2">
            <button id="themeToggle" type="button" aria-label="Toggle theme" class="group grid h-11 w-11 place-items-center rounded-md border border-slate-200 bg-slate-100 text-slate-700 transition hover:border-teal-500/40 hover:text-teal-700 dark:border-white/10 dark:bg-white/[0.06] dark:text-slate-200 dark:hover:border-teal-300/40 dark:hover:text-teal-200">
                <svg class="h-5 w-5 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 3v2"/>
                    <path d="M12 19v2"/>
                    <path d="m4.22 4.22 1.42 1.42"/>
                    <path d="m18.36 18.36 1.42 1.42"/>
                    <path d="M3 12h2"/>
                    <path d="M19 12h2"/>
                    <path d="m4.22 19.78 1.42-1.42"/>
                    <path d="m18.36 5.64 1.42-1.42"/>
                    <circle cx="12" cy="12" r="4"/>
                </svg>
                <svg class="hidden h-5 w-5 dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20.5 14.5A8.5 8.5 0 0 1 9.5 3.5 7.5 7.5 0 1 0 20.5 14.5Z"/>
                </svg>
            </button>

            @auth
                <a href="{{ route('dashboard') }}" class="hidden items-center gap-2 rounded-md bg-teal-500 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-teal-900/20 transition hover:bg-teal-400 dark:bg-teal-300 dark:text-[#061014] dark:hover:bg-teal-200 sm:inline-flex">
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="hidden items-center gap-2 rounded-md bg-teal-500 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-teal-900/20 transition hover:bg-teal-400 dark:bg-teal-300 dark:text-[#061014] dark:hover:bg-teal-200 sm:inline-flex">
                    Login
                </a>
            @endauth
        </div>
    </nav>
</header>

<main class="flex min-h-screen items-center px-5 py-28">
    <section class="mx-auto grid max-w-7xl items-center gap-10 lg:grid-cols-[0.95fr_1.05fr]">
        <div class="order-2 rounded-lg border border-slate-200 bg-white/[0.86] p-5 shadow-2xl shadow-slate-900/10 backdrop-blur-2xl dark:border-white/10 dark:bg-[#071217]/[0.82] dark:shadow-black/30 lg:order-1">
            <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-5 dark:border-white/10">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-rose-700 dark:text-rose-200">Route monitor</p>
                    <h2 class="mt-1 text-2xl font-black">Request not resolved</h2>
                </div>
                <span class="inline-flex items-center gap-2 rounded-md border border-rose-500/20 bg-rose-500/10 px-3 py-2 text-sm font-bold text-rose-700 dark:border-rose-300/25 dark:bg-rose-300/10 dark:text-rose-200">
                    404
                </span>
            </div>

            <div class="mt-5 grid gap-3">
                <div class="rounded-md border border-slate-200 bg-slate-50/80 p-4 dark:border-white/10 dark:bg-black/20">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Signal</p>
                    <p class="mt-2 font-black">The page address does not match an active endpoint.</p>
                </div>
                <div class="rounded-md border border-slate-200 bg-slate-50/80 p-4 dark:border-white/10 dark:bg-black/20">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Recommended action</p>
                    <p class="mt-2 font-black">Return to a trusted route and continue from the command center.</p>
                </div>
                <div class="rounded-md border border-slate-200 bg-slate-50/80 p-4 dark:border-white/10 dark:bg-black/20">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Status</p>
                    <p class="mt-2 font-black text-emerald-700 dark:text-emerald-200">Application is online</p>
                </div>
            </div>
        </div>

        <div class="order-1 lg:order-2">
            <div class="inline-flex items-center gap-2 rounded-md border border-rose-500/20 bg-rose-500/10 px-3 py-2 text-xs font-black uppercase tracking-[0.2em] text-rose-700 dark:border-rose-300/25 dark:bg-rose-300/10 dark:text-rose-200">
                <span class="grid h-5 w-5 place-items-center rounded bg-rose-500/[0.12] dark:bg-rose-300/15">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 8v4"/>
                        <path d="M12 16h.01"/>
                    </svg>
                </span>
                Page not found
            </div>

            <h1 class="mt-7 text-7xl font-black leading-none tracking-tight text-slate-950 dark:text-white sm:text-8xl lg:text-9xl">
                404
            </h1>

            <p class="mt-6 max-w-2xl text-2xl font-black leading-tight text-slate-900 dark:text-white sm:text-4xl">
                This route is outside the protected map.
            </p>

            <p class="mt-5 max-w-xl text-base leading-8 text-slate-600 dark:text-slate-300 sm:text-lg">
                The page may have moved, been removed, or the link may be incorrect.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 rounded-md bg-slate-950 px-6 py-4 text-sm font-black text-white shadow-xl shadow-slate-900/20 transition hover:bg-slate-800 dark:bg-teal-300 dark:text-[#061014] dark:hover:bg-teal-200">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m3 12 9-9 9 9"/>
                        <path d="M5 10v10h14V10"/>
                    </svg>
                    Back home
                </a>

                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-slate-200 bg-white/75 px-6 py-4 text-sm font-black text-slate-800 shadow-lg shadow-slate-900/5 transition hover:border-teal-500/45 hover:bg-teal-50 dark:border-white/10 dark:bg-white/[0.06] dark:text-white dark:hover:border-teal-300/45 dark:hover:bg-teal-300/10">
                        <svg class="h-5 w-5 text-teal-700 dark:text-teal-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect width="7" height="9" x="3" y="3" rx="1"/>
                            <rect width="7" height="5" x="14" y="3" rx="1"/>
                            <rect width="7" height="9" x="14" y="12" rx="1"/>
                            <rect width="7" height="5" x="3" y="16" rx="1"/>
                        </svg>
                        Open dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-slate-200 bg-white/75 px-6 py-4 text-sm font-black text-slate-800 shadow-lg shadow-slate-900/5 transition hover:border-teal-500/45 hover:bg-teal-50 dark:border-white/10 dark:bg-white/[0.06] dark:text-white dark:hover:border-teal-300/45 dark:hover:bg-teal-300/10">
                        <svg class="h-5 w-5 text-teal-700 dark:text-teal-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                            <path d="m10 17 5-5-5-5"/>
                            <path d="M15 12H3"/>
                        </svg>
                        Login
                    </a>
                @endauth
            </div>
        </div>
    </section>
</main>
</body>
</html>
