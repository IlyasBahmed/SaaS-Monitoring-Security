<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberShield - Security Monitoring</title>

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

<body class="overflow-x-hidden bg-slate-50 text-slate-950 antialiased transition-colors duration-300 dark:bg-[#061014] dark:text-white">
<div class="fixed inset-0 -z-10 overflow-hidden">
    <canvas id="hero3d" class="h-full w-full"></canvas>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_12%_12%,rgba(20,184,166,0.20),transparent_28%),radial-gradient(circle_at_84%_18%,rgba(245,158,11,0.18),transparent_24%),linear-gradient(135deg,rgba(248,250,252,0.88),rgba(224,242,254,0.76)_46%,rgba(255,247,237,0.72))] dark:bg-[radial-gradient(circle_at_12%_12%,rgba(45,212,191,0.18),transparent_28%),radial-gradient(circle_at_84%_18%,rgba(251,191,36,0.13),transparent_24%),linear-gradient(135deg,rgba(6,16,20,0.88),rgba(15,23,42,0.74)_46%,rgba(25,18,32,0.76))]"></div>
    <div class="absolute inset-0 bg-[linear-gradient(rgba(15,23,42,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(15,23,42,0.05)_1px,transparent_1px)] bg-[length:46px_46px] dark:bg-[linear-gradient(rgba(45,212,191,0.06)_1px,transparent_1px),linear-gradient(90deg,rgba(45,212,191,0.06)_1px,transparent_1px)]"></div>
</div>

<header class="fixed left-0 top-0 z-50 w-full px-4 py-4 sm:px-6">
    <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 rounded-lg border border-slate-200/80 bg-white/[0.82] px-4 py-3 shadow-xl shadow-slate-900/10 backdrop-blur-2xl transition-colors dark:border-white/10 dark:bg-[#071217]/[0.82] dark:shadow-black/25 sm:px-5">
        <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
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

        <div class="hidden items-center gap-7 text-sm font-bold text-slate-600 dark:text-slate-300 md:flex">
            <a href="#services" class="transition hover:text-teal-700 dark:hover:text-teal-200">Services</a>
            <a href="#security" class="transition hover:text-amber-700 dark:hover:text-amber-200">Security</a>
            <a href="#contact" class="transition hover:text-rose-700 dark:hover:text-rose-200">Contact</a>
        </div>

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
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-md bg-teal-500 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-teal-900/20 transition hover:bg-teal-400 dark:bg-teal-300 dark:text-[#061014] dark:hover:bg-teal-200">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect width="7" height="9" x="3" y="3" rx="1"/>
                        <rect width="7" height="5" x="14" y="3" rx="1"/>
                        <rect width="7" height="9" x="14" y="12" rx="1"/>
                        <rect width="7" height="5" x="3" y="16" rx="1"/>
                    </svg>
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-md bg-teal-500 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-teal-900/20 transition hover:bg-teal-400 dark:bg-teal-300 dark:text-[#061014] dark:hover:bg-teal-200">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                        <path d="m10 17 5-5-5-5"/>
                        <path d="M15 12H3"/>
                    </svg>
                    Login
                </a>
            @endauth
        </div>
    </nav>
</header>

<main>
    <section class="relative min-h-[92vh] px-5 pb-14 pt-32 sm:pt-36">
        <div class="mx-auto grid max-w-7xl items-center gap-10 lg:grid-cols-[1.03fr_0.97fr]">
            <div>
                <div class="inline-flex items-center gap-2 rounded-md border border-emerald-500/20 bg-emerald-500/10 px-3 py-2 text-xs font-black uppercase tracking-[0.2em] text-emerald-700 dark:border-emerald-300/25 dark:bg-emerald-300/10 dark:text-emerald-200">
                    <span class="grid h-5 w-5 place-items-center rounded bg-emerald-500/[0.12] dark:bg-emerald-300/15">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M22 12h-4l-3 8L9 4l-3 8H2"/>
                        </svg>
                    </span>
                    Active monitoring
                </div>

                <h1 class="mt-7 max-w-3xl text-5xl font-black leading-[0.96] tracking-tight text-slate-950 dark:text-white sm:text-6xl lg:text-7xl">
                    Protect websites, APIs and servers.
                </h1>

                <p class="mt-6 max-w-2xl text-base leading-8 text-slate-600 dark:text-slate-300 sm:text-lg">
                    CyberShield combines uptime monitoring, security scanning, AI log analysis and automated response in one focused command center.
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-md bg-slate-950 px-6 py-4 text-sm font-black text-white shadow-xl shadow-slate-900/20 transition hover:bg-slate-800 dark:bg-teal-300 dark:text-[#061014] dark:hover:bg-teal-200">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 3v18h18"/>
                                <path d="m19 9-5 5-4-4-3 3"/>
                            </svg>
                            Open dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 rounded-md bg-slate-950 px-6 py-4 text-sm font-black text-white shadow-xl shadow-slate-900/20 transition hover:bg-slate-800 dark:bg-teal-300 dark:text-[#061014] dark:hover:bg-teal-200">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                                <path d="m10 17 5-5-5-5"/>
                                <path d="M15 12H3"/>
                            </svg>
                            Se connecter
                        </a>
                    @endauth
                    <a href="#services" class="inline-flex items-center justify-center gap-2 rounded-md border border-slate-200 bg-white/75 px-6 py-4 text-sm font-black text-slate-800 shadow-lg shadow-slate-900/5 transition hover:border-amber-500/45 hover:bg-amber-50 dark:border-white/10 dark:bg-white/[0.06] dark:text-white dark:hover:border-amber-300/45 dark:hover:bg-amber-300/10">
                        <svg class="h-5 w-5 text-amber-600 dark:text-amber-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m7 11 2-2-2-2"/>
                            <path d="M11 13h4"/>
                            <rect width="18" height="18" x="3" y="3" rx="2"/>
                        </svg>
                        View services
                    </a>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white/[0.86] p-5 shadow-2xl shadow-slate-900/10 backdrop-blur-2xl transition-colors dark:border-white/10 dark:bg-[#071217]/[0.82] dark:shadow-black/30">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-5 dark:border-white/10">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-teal-700 dark:text-teal-200">Security posture</p>
                        <h2 class="mt-1 text-3xl font-black">Defense active</h2>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-md border border-emerald-500/20 bg-emerald-500/10 px-3 py-2 text-sm font-bold text-emerald-700 dark:border-emerald-300/25 dark:bg-emerald-300/10 dark:text-emerald-200">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 6 9 17l-5-5"/>
                        </svg>
                        Operational
                    </span>
                </div>

                <div class="grid grid-cols-3 divide-x divide-slate-200 border-b border-slate-200 py-5 dark:divide-white/10 dark:border-white/10">
                    <div class="px-3">
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Score</p>
                        <h3 class="mt-2 text-3xl font-black text-teal-700 dark:text-teal-200">92</h3>
                        <p class="text-xs text-slate-500">/100</p>
                    </div>
                    <div class="px-3">
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Incidents</p>
                        <h3 class="mt-2 text-3xl font-black text-amber-600 dark:text-amber-200">2</h3>
                        <p class="text-xs text-slate-500">active</p>
                    </div>
                    <div class="px-3">
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Agents</p>
                        <h3 class="mt-2 text-3xl font-black text-emerald-700 dark:text-emerald-200">6</h3>
                        <p class="text-xs text-slate-500">online</p>
                    </div>
                </div>

                <div class="mt-5 rounded-lg border border-slate-200 bg-slate-50/80 p-4 dark:border-white/10 dark:bg-black/20">
                    <canvas id="securityChart" height="130"></canvas>
                </div>

                <div id="security" class="mt-2 divide-y divide-slate-200 dark:divide-white/10">
                    @foreach([
                        ['shield', 'Cloudflare WAF', 'Blocking active', 'text-emerald-700 dark:text-emerald-200'],
                        ['scan', 'AI analysis', 'Permanent scan', 'text-teal-700 dark:text-teal-200'],
                        ['report', 'Reports', 'Automated', 'text-rose-700 dark:text-rose-200'],
                    ] as $row)
                        <div class="flex items-center justify-between gap-4 py-4">
                            <span class="flex items-center gap-3 text-sm font-bold text-slate-700 dark:text-slate-200">
                                <span class="grid h-9 w-9 place-items-center rounded-md border border-slate-200 bg-white {{ $row[3] }} dark:border-white/10 dark:bg-white/[0.06]">
                                    @if($row[0] === 'shield')
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M12 3 5 6v5c0 4.7 3 8.4 7 10 4-1.6 7-5.3 7-10V6l-7-3Z"/>
                                        </svg>
                                    @elseif($row[0] === 'scan')
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M12 2v4"/>
                                            <path d="M12 18v4"/>
                                            <path d="M2 12h4"/>
                                            <path d="M18 12h4"/>
                                            <path d="m4.93 4.93 2.83 2.83"/>
                                            <path d="m16.24 16.24 2.83 2.83"/>
                                        </svg>
                                    @else
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/>
                                            <path d="M14 2v6h6"/>
                                            <path d="M8 13h8"/>
                                        </svg>
                                    @endif
                                </span>
                                {{ $row[1] }}
                            </span>
                            <span class="text-right text-sm font-black text-slate-900 dark:text-white">{{ $row[2] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section id="services" class="px-5 py-20">
        <div class="mx-auto max-w-7xl">
            <div class="max-w-2xl">
                <p class="text-xs font-black uppercase tracking-[0.24em] text-amber-700 dark:text-amber-200">Services</p>
                <h2 class="mt-3 text-4xl font-black tracking-tight sm:text-5xl">Complete coverage for critical assets.</h2>
            </div>

            <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach([
                    ['server', 'Uptime Monitoring', 'HTTP/HTTPS, API latency and availability tracking.', 'text-emerald-700 dark:text-emerald-200'],
                    ['shield', 'Security Scanning', 'Ports, SSL, blacklists, headers and suspicious files.', 'text-teal-700 dark:text-teal-200'],
                    ['brain', 'AI Log Analysis', 'Clear incident explanations and smart recommendations.', 'text-amber-700 dark:text-amber-200'],
                    ['cloud', 'Cloudflare Protection', 'IP blocking, firewall rules and Under Attack mode.', 'text-sky-700 dark:text-sky-200'],
                    ['report', 'PDF Reports', 'Weekly, monthly and white-label security reports.', 'text-rose-700 dark:text-rose-200'],
                    ['automation', 'Automation Rules', 'Automatic actions for incidents and anomalies.', 'text-lime-700 dark:text-lime-200'],
                ] as $item)
                    <article class="group rounded-lg border border-slate-200 bg-white/[0.78] p-6 shadow-lg shadow-slate-900/5 transition hover:-translate-y-1 hover:border-teal-500/35 hover:bg-white dark:border-white/10 dark:bg-[#071217]/70 dark:shadow-black/20 dark:hover:border-teal-300/35">
                        <div class="grid h-12 w-12 place-items-center rounded-md border border-slate-200 bg-slate-50 {{ $item[3] }} transition group-hover:border-teal-500/30 dark:border-white/10 dark:bg-white/[0.06]">
                            @if($item[0] === 'server')
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16v5H4z"/><path d="M4 13h16v5H4z"/><path d="M7 8h.01"/><path d="M7 15h.01"/></svg>
                            @elseif($item[0] === 'shield')
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></svg>
                            @elseif($item[0] === 'brain')
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 3a3 3 0 0 0-3 3v1a3 3 0 0 0 0 6v1a3 3 0 0 0 3 3"/><path d="M15 3a3 3 0 0 1 3 3v1a3 3 0 0 1 0 6v1a3 3 0 0 1-3 3"/><path d="M12 3v18"/></svg>
                            @elseif($item[0] === 'cloud')
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.5 19H8a5 5 0 1 1 1.7-9.7A6 6 0 0 1 21 12a4 4 0 0 1-3.5 7Z"/></svg>
                            @elseif($item[0] === 'report')
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2h9l5 5v15H6Z"/><path d="M14 2v6h6"/><path d="M9 13h6"/><path d="M9 17h6"/></svg>
                            @else
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 8v8"/><path d="M8 12h8"/><circle cx="12" cy="12" r="9"/></svg>
                            @endif
                        </div>

                        <h3 class="mt-5 text-xl font-black">{{ $item[1] }}</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-400">{{ $item[2] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="px-5 py-20">
        <div class="mx-auto grid max-w-7xl gap-8 rounded-lg border border-slate-200 bg-white/[0.78] p-6 shadow-xl shadow-slate-900/10 backdrop-blur-2xl dark:border-white/10 dark:bg-[#071217]/[0.76] dark:shadow-black/25 md:p-10 lg:grid-cols-[0.92fr_1.08fr]">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.24em] text-teal-700 dark:text-teal-200">Security Engine</p>
                <h2 class="mt-3 text-4xl font-black tracking-tight sm:text-5xl">Detect, explain and act automatically.</h2>
                <p class="mt-5 text-base leading-8 text-slate-600 dark:text-slate-400">
                    Security signals are grouped, explained and routed into response actions so teams can move faster without losing context.
                </p>
            </div>

            <div class="grid gap-3">
                @foreach([
                    ['Critical port opened', 'Instant alert and remediation task'],
                    ['Brute force detected', 'IP blocked through Cloudflare'],
                    ['SSL expiring soon', 'Advance alerts before expiration'],
                    ['Security score dropped', 'Detailed report generated'],
                ] as $rule)
                    <div class="rounded-md border border-slate-200 bg-slate-50/80 p-4 dark:border-white/10 dark:bg-black/20">
                        <p class="font-black text-slate-950 dark:text-white">{{ $rule[0] }}</p>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $rule[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="contact" class="px-5 py-20">
        <div class="mx-auto grid max-w-7xl gap-8 border-t border-slate-200 pt-14 dark:border-white/10 lg:grid-cols-[0.8fr_1.2fr]">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.24em] text-rose-700 dark:text-rose-200">Contact</p>
                <h2 class="mt-3 text-4xl font-black tracking-tight">Talk security with us.</h2>
                <p class="mt-5 max-w-md text-sm leading-7 text-slate-600 dark:text-slate-400">
                    Request a demo or secure a project. The form is ready for your backend action when you want to wire it.
                </p>
            </div>

            <form class="grid gap-4">
                <input class="rounded-md border border-slate-200 bg-white/80 px-5 py-4 text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-teal-500 dark:border-white/10 dark:bg-black/20 dark:text-white dark:placeholder:text-slate-600 dark:focus:border-teal-300" placeholder="Name">
                <input class="rounded-md border border-slate-200 bg-white/80 px-5 py-4 text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-teal-500 dark:border-white/10 dark:bg-black/20 dark:text-white dark:placeholder:text-slate-600 dark:focus:border-teal-300" placeholder="Email">
                <textarea class="h-36 rounded-md border border-slate-200 bg-white/80 px-5 py-4 text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-teal-500 dark:border-white/10 dark:bg-black/20 dark:text-white dark:placeholder:text-slate-600 dark:focus:border-teal-300" placeholder="Message"></textarea>
                <button class="inline-flex items-center justify-center gap-2 rounded-md bg-amber-400 px-5 py-4 font-black text-slate-950 transition hover:bg-amber-300 dark:bg-amber-300 dark:hover:bg-amber-200">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m22 2-7 20-4-9-9-4Z"/>
                        <path d="M22 2 11 13"/>
                    </svg>
                    Send message
                </button>
            </form>
        </div>
    </section>
</main>

<footer class="border-t border-slate-200 px-5 py-8 text-center text-sm font-semibold text-slate-500 dark:border-white/10 dark:text-slate-500">
    &copy; {{ date('Y') }} CyberShield. All rights reserved.
</footer>
</body>
</html>
