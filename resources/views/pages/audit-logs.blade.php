<x-dashboard-layout>
    @push('styles')
        <style>
            .audit-motion {
                --audit-cyan: rgba(34, 211, 238, 0.16);
                --audit-emerald: rgba(52, 211, 153, 0.13);
                --audit-violet: rgba(129, 140, 248, 0.10);
            }

            .audit-motion::before {
                content: "";
                position: fixed;
                inset: 0;
                z-index: -10;
                pointer-events: none;
                background:
                    radial-gradient(circle at 18% 8%, var(--audit-cyan), transparent 28rem),
                    radial-gradient(circle at 88% 20%, var(--audit-emerald), transparent 26rem),
                    radial-gradient(circle at 48% 106%, var(--audit-violet), transparent 30rem),
                    #020617;
            }

            .audit-motion::after {
                content: "";
                position: fixed;
                inset: -30%;
                z-index: -9;
                pointer-events: none;
                background:
                    linear-gradient(112deg, transparent 38%, rgba(103, 232, 249, 0.10) 48%, transparent 60%),
                    linear-gradient(rgba(103, 232, 249, 0.04) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(103, 232, 249, 0.03) 1px, transparent 1px);
                background-size: auto, 42px 42px, 42px 42px;
                mask-image: linear-gradient(to bottom, transparent, black 15%, black 78%, transparent);
                mix-blend-mode: screen;
                opacity: 0.58;
                animation: auditFieldDrift 16s linear infinite;
            }

            .audit-panel {
                position: relative;
                isolation: isolate;
            }

            .audit-panel::before {
                content: "";
                position: absolute;
                inset: 0;
                z-index: -1;
                pointer-events: none;
                background: linear-gradient(120deg, transparent 0%, rgba(103, 232, 249, 0.075) 44%, transparent 66%);
                opacity: 0;
                transform: translateX(-32%);
                transition: opacity 220ms ease, transform 560ms ease;
            }

            .audit-panel:hover::before {
                opacity: 1;
                transform: translateX(32%);
            }

            .audit-metric {
                position: relative;
                overflow: hidden;
            }

            .audit-metric::after {
                content: "";
                position: absolute;
                inset: auto 12% 0;
                height: 1px;
                background: linear-gradient(90deg, transparent, currentColor, transparent);
                opacity: 0.18;
                transition: opacity 200ms ease, transform 260ms ease;
            }

            .audit-metric:hover::after {
                opacity: 0.55;
                transform: translateY(-2px);
            }

            .audit-stream-row {
                position: relative;
            }

            .audit-stream-row::before {
                content: "";
                position: absolute;
                inset: 0 auto 0 0;
                width: 3px;
                background: linear-gradient(to bottom, transparent, rgba(52, 211, 153, 0.8), rgba(34, 211, 238, 0.75), transparent);
                opacity: 0;
                transition: opacity 180ms ease;
            }

            .audit-stream-row:hover::before,
            .audit-stream-row.is-fresh::before {
                opacity: 1;
            }

            .audit-live-pulse {
                animation: auditLivePulse 2.4s ease-in-out infinite;
            }

            @keyframes auditFieldDrift {
                from {
                    transform: translate3d(-24px, -24px, 0);
                }
                to {
                    transform: translate3d(42px, 42px, 0);
                }
            }

            @keyframes auditLivePulse {
                0%, 100% {
                    box-shadow: 0 0 0 rgba(52, 211, 153, 0);
                }
                50% {
                    box-shadow: 0 0 28px rgba(52, 211, 153, 0.16);
                }
            }

            html:not(.dark) .audit-motion {
                --audit-cyan: rgba(8, 145, 178, 0.12);
                --audit-emerald: rgba(5, 150, 105, 0.10);
                --audit-violet: rgba(99, 102, 241, 0.08);
            }

            html:not(.dark) .audit-motion::before {
                background:
                    radial-gradient(circle at 18% 8%, var(--audit-cyan), transparent 28rem),
                    radial-gradient(circle at 88% 20%, var(--audit-emerald), transparent 26rem),
                    linear-gradient(180deg, rgba(248, 250, 252, 0.98), rgba(226, 239, 247, 0.9));
            }

            html:not(.dark) .audit-motion::after {
                background:
                    linear-gradient(112deg, transparent 38%, rgba(8, 145, 178, 0.08) 48%, transparent 60%),
                    linear-gradient(rgba(8, 145, 178, 0.035) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(8, 145, 178, 0.028) 1px, transparent 1px);
                opacity: 0.46;
                mix-blend-mode: multiply;
            }

            html:not(.dark) .audit-panel {
                background: rgba(255, 255, 255, 0.9);
                border-color: rgba(203, 213, 225, 0.92);
                box-shadow: 0 20px 42px -30px rgba(15, 23, 42, 0.38);
            }

            html:not(.dark) .audit-panel::before {
                background: linear-gradient(120deg, transparent 0%, rgba(8, 145, 178, 0.085) 44%, transparent 66%);
            }

            html:not(.dark) .audit-metric {
                background-color: rgba(248, 250, 252, 0.78);
                border-color: rgba(203, 213, 225, 0.9);
            }

            html:not(.dark) .audit-motion :where(input, select) {
                background-color: rgba(255, 255, 255, 0.94);
                border-color: rgba(203, 213, 225, 0.95);
                color: #0f172a;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.86);
            }

            html:not(.dark) .audit-motion :where(input, select):focus {
                background-color: #ffffff;
                border-color: rgba(8, 145, 178, 0.38);
                box-shadow: 0 0 0 4px rgba(8, 145, 178, 0.10);
            }

            html:not(.dark) .audit-motion :where(.bg-\[\#07111f\], .bg-\[\#07111f\]\/95, .bg-\[\#020617\]\/70, .bg-\[\#020617\]\/80, .bg-slate-950\/40, .bg-slate-950\/45, .bg-slate-950\/55) {
                background-color: rgba(248, 250, 252, 0.82);
            }

            html:not(.dark) .audit-motion :where(.border-slate-800, .border-slate-800\/90, .divide-slate-800 > :not([hidden]) ~ :not([hidden])) {
                border-color: rgba(203, 213, 225, 0.9);
            }

            html:not(.dark) .audit-stream-row:hover {
                background-color: rgba(236, 254, 255, 0.72);
            }

            html:not(.dark) .audit-stream-row.is-fresh {
                background-color: rgba(207, 250, 254, 0.62);
                box-shadow: inset 0 0 0 1px rgba(8, 145, 178, 0.16);
            }

            html:not(.dark) .audit-stream-row::before {
                background: linear-gradient(to bottom, transparent, rgba(5, 150, 105, 0.72), rgba(8, 145, 178, 0.68), transparent);
            }

            html:not(.dark) .audit-live-pulse {
                animation-name: auditLivePulseLight;
            }

            @keyframes auditLivePulseLight {
                0%, 100% {
                    box-shadow: 0 18px 34px -30px rgba(15, 23, 42, 0.35);
                }
                50% {
                    box-shadow: 0 18px 34px -30px rgba(15, 23, 42, 0.35), 0 0 30px rgba(5, 150, 105, 0.13);
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .audit-motion::after,
                .audit-live-pulse {
                    animation: none;
                }

                .audit-panel::before,
                .audit-metric::after,
                .audit-stream-row::before {
                    transition: none;
                }
            }
        </style>
    @endpush

    <div
        x-data="auditLogsPage()"
        x-init="start()"
        class="audit-motion relative space-y-5"
    >
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-cyan-400">Live Audit Stream</p>
                <h1 class="mt-2 text-3xl font-black text-white">Audit Logs</h1>
                <p class="mt-1 text-sm font-medium text-slate-500">Real-time stream across all projects associated with this platform.</p>
            </div> 

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex h-10 items-center gap-2 rounded-lg border px-3 text-xs font-black"
                      :class="live ? 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300' : 'border-amber-400/20 bg-amber-400/10 text-amber-300'">
                    <span class="relative flex h-2.5 w-2.5">
                        <span x-show="live" class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-70"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full" :class="live ? 'bg-emerald-400' : 'bg-amber-400'"></span>
                    </span>
                    <span x-text="live ? 'Listening' : 'Paused'"></span>
                </span>

                <span
                    x-show="newEvents > 0"
                    x-cloak
                    class="inline-flex h-10 items-center rounded-lg border border-cyan-400/20 bg-cyan-400/10 px-3 text-xs font-black text-cyan-300"
                >
                    +<span x-text="newEvents"></span> new
                </span>

                <button
                    type="button"
                    @click="toggleLive()"
                    class="h-10 rounded-lg border border-slate-700 px-4 text-xs font-bold text-slate-300 transition hover:border-cyan-400/30 hover:text-cyan-300"
                    x-text="live ? 'Pause' : 'Resume'"
                ></button>
            </div>
        </div>

      <section class="grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.65fr)]">
    <!-- Current Signal -->
    <div class="audit-panel audit-live-pulse relative overflow-hidden rounded-2xl border border-cyan-400/10 bg-[#07111f]/95 shadow-2xl shadow-cyan-950/10">
        <div class="pointer-events-none absolute -right-20 -top-20 h-56 w-56 rounded-full bg-cyan-400/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -left-24 h-56 w-56 rounded-full bg-indigo-500/10 blur-3xl"></div>

        <!-- Header -->
        <div class="relative border-b border-cyan-400/10 bg-cyan-400/[0.035] px-5 py-4">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2.5 w-2.5">
                        <span
                            x-show="live"
                            class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-70"
                        ></span>
                        <span
                            class="relative inline-flex h-2.5 w-2.5 rounded-full"
                            :class="live ? 'bg-emerald-400 shadow-[0_0_14px_rgba(52,211,153,0.9)]' : 'bg-amber-400'"
                        ></span>
                    </span>

                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-cyan-300">
                        Current Signal
                    </p>
                </div>

                <span
                    class="rounded-lg border border-cyan-400/10 bg-cyan-400/10 px-3 py-1 font-mono text-xs font-black text-cyan-200"
                    x-text="latestLog ? latestLog.created_time : '--:--:--'"
                ></span>
            </div>
        </div>

        <div class="relative p-5">
            <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_360px]">
                <!-- Event Details -->
                <div class="min-w-0">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <span
                            class="rounded-lg border px-2.5 py-1 text-[10px] font-black uppercase tracking-wide shadow-sm"
                            :class="latestLog ? severityClass(latestLog.severity) : 'border-slate-700 bg-slate-900 text-slate-400'"
                            x-text="latestLog ? label(latestLog.severity) : 'Idle'"
                        ></span>

                        <span
                            class="rounded-lg border px-2.5 py-1 text-[10px] font-black uppercase tracking-wide shadow-sm"
                            :class="latestLog ? categoryClass(latestLog.category) : 'border-slate-700 bg-slate-900 text-slate-400'"
                            x-text="latestLog ? latestLog.category_label : 'Waiting'"
                        ></span>

                        <span
                            x-show="newEvents > 0"
                            x-cloak
                            class="rounded-lg border border-cyan-400/20 bg-cyan-400/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-cyan-300"
                        >
                            Fresh
                        </span>
                    </div>

                    <h2
                        class="min-w-0 break-words text-3xl font-black leading-tight text-white"
                        x-text="latestLog ? latestLog.event_label : 'Waiting for events'"
                    ></h2>

                    <p
                        class="mt-2 min-w-0 break-words text-sm font-semibold text-slate-400"
                        x-text="latestLog ? latestLog.project : 'The stream updates automatically every 2 seconds.'"
                    ></p>

                    <!-- Meta -->
                    <div class="mt-5 grid gap-3">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="min-w-0 rounded-xl border border-slate-800 bg-slate-950/45 px-4 py-3">
                                <p class="text-[10px] font-black uppercase tracking-wider text-slate-600">
                                    Actor
                                </p>

                                <p
                                    class="mt-1 min-w-0 break-words text-sm font-black text-slate-200"
                                    x-text="latestLog ? latestLog.actor : '-'"
                                ></p>
                            </div>

                            <div class="min-w-0 rounded-xl border border-slate-800 bg-slate-950/45 px-4 py-3">
                                <p class="text-[10px] font-black uppercase tracking-wider text-slate-600">
                                    IP Address
                                </p>

                                <p
                                    class="mt-1 min-w-0 overflow-x-auto whitespace-nowrap font-mono text-sm font-black text-slate-200 [scrollbar-width:thin]"
                                    :title="latestLog ? latestLog.ip : '-'"
                                    x-text="latestLog ? latestLog.ip : '-'"
                                ></p>
                            </div>
                        </div>

                        <div class="min-w-0 rounded-xl border border-cyan-400/10 bg-cyan-400/[0.045] px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">
                                    Target
                                </p>

                                <p
                                    class="shrink-0 text-[10px] font-black uppercase tracking-wider text-slate-700"
                                    x-text="latestLog ? latestLog.created_human : ''"
                                ></p>
                            </div>

                            <p
                                class="mt-2 min-w-0 break-words text-sm font-black leading-relaxed text-cyan-100 [overflow-wrap:anywhere]"
                                :title="latestLog ? latestLog.target : '-'"
                                x-text="latestLog ? latestLog.target : '-'"
                            ></p>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 gap-3 self-start">
                    <div class="audit-metric rounded-xl border border-slate-800 bg-slate-950/55 px-4 py-4 text-center text-cyan-300">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-600">
                            Projects
                        </p>
                        <p class="mt-2 text-2xl font-black text-cyan-300" x-text="stats.total_projects"></p>
                    </div>

                    <div class="audit-metric rounded-xl border border-slate-800 bg-slate-950/55 px-4 py-4 text-center text-white">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-600">
                            Events
                        </p>
                        <p class="mt-2 text-2xl font-black text-white" x-text="filteredLogs.length"></p>
                    </div>

                    <div class="audit-metric rounded-xl border border-red-400/20 bg-red-400/10 px-4 py-4 text-center text-red-300">
                        <p class="text-[10px] font-black uppercase tracking-wider text-red-200/70">
                            Critical
                        </p>
                        <p class="mt-2 text-2xl font-black text-red-300" x-text="stats.critical"></p>
                    </div>

                    <div class="audit-metric rounded-xl border border-orange-400/20 bg-orange-400/10 px-4 py-4 text-center text-orange-300">
                        <p class="text-[10px] font-black uppercase tracking-wider text-orange-200/70">
                            High
                        </p>
                        <p class="mt-2 text-2xl font-black text-orange-300" x-text="stats.high"></p>
                    </div>

                    <div class="audit-metric rounded-xl border border-sky-400/20 bg-sky-400/10 px-4 py-4 text-center text-sky-300 sm:col-span-2">
                        <p class="text-[10px] font-black uppercase tracking-wider text-sky-200/70">
                            Info
                        </p>
                        <p class="mt-2 text-2xl font-black text-sky-300" x-text="stats.info"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stream Health -->
    <div class="audit-panel relative overflow-hidden rounded-2xl border border-slate-800 bg-[#07111f]/95 p-5 shadow-2xl shadow-black/20">
        <div class="pointer-events-none absolute -right-20 -top-20 h-52 w-52 rounded-full bg-emerald-400/10 blur-3xl"></div>

        <div class="relative mb-5 flex items-start justify-between gap-3">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-slate-500">
                    Stream Health
                </p>

                <h2 class="mt-2 text-xl font-black text-white">
                    Realtime status
                </h2>
            </div>

            <span
                class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-black"
                :class="connected
                    ? 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300'
                    : 'border-red-400/20 bg-red-400/10 text-red-300'"
            >
                <span
                    class="h-1.5 w-1.5 rounded-full"
                    :class="connected ? 'bg-emerald-400' : 'bg-red-400'"
                ></span>
                <span x-text="connected ? 'Connected' : 'Disconnected'"></span>
            </span>
        </div>

        <div class="relative grid gap-3 sm:grid-cols-2">
            <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                <p class="text-[10px] font-black uppercase tracking-wider text-slate-600">
                    Refresh
                </p>
                <p class="mt-1 text-sm font-black text-cyan-300">
                    2 seconds
                </p>
            </div>

            <div class="min-w-0 rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                <p class="text-[10px] font-black uppercase tracking-wider text-slate-600">
                    Last Pull
                </p>
                <p
                    class="mt-1 min-w-0 overflow-x-auto whitespace-nowrap font-mono text-sm font-black text-slate-200 [scrollbar-width:thin]"
                    x-text="lastUpdated"
                ></p>
            </div>

            <div class="rounded-xl border border-cyan-400/10 bg-cyan-400/[0.045] px-4 py-3">
                <p class="text-[10px] font-black uppercase tracking-wider text-slate-600">
                    Auth
                </p>
                <p class="mt-1 text-lg font-black text-cyan-300" x-text="stats.auth"></p>
            </div>

            <div class="rounded-xl border border-orange-400/10 bg-orange-400/[0.045] px-4 py-3">
                <p class="text-[10px] font-black uppercase tracking-wider text-slate-600">
                    File Security
                </p>
                <p class="mt-1 text-lg font-black text-orange-300" x-text="stats.file_security"></p>
            </div>

            <div class="rounded-xl border border-amber-400/10 bg-amber-400/[0.045] px-4 py-3">
                <p class="text-[10px] font-black uppercase tracking-wider text-slate-600">
                    Medium
                </p>
                <p class="mt-1 text-lg font-black text-amber-300" x-text="stats.medium"></p>
            </div>

            <div class="rounded-xl border border-cyan-400/10 bg-cyan-400/[0.045] px-4 py-3">
                <p class="text-[10px] font-black uppercase tracking-wider text-slate-600">
                    Low
                </p>
                <p class="mt-1 text-lg font-black text-cyan-300" x-text="stats.low"></p>
            </div>
        </div>
    </div>
</section>

       <section class="audit-panel sticky top-4 z-20 overflow-hidden rounded-2xl border border-slate-800/90 bg-[#07111f]/95 p-3 shadow-xl shadow-black/25 backdrop-blur-xl">
    <div class="flex flex-col gap-3 2xl:flex-row 2xl:items-center">
        <!-- Search -->
        <div class="relative min-w-0 flex-1">
            <svg
                class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-600"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="1.9"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z"/>
            </svg>

            <input
                x-model.debounce.200ms="search"
                type="search"
                placeholder="Search event, actor, target, project, IP..."
                class="h-11 w-full rounded-xl border border-slate-800 bg-[#020617]/80 pl-10 pr-10 text-sm font-semibold text-slate-200 outline-none transition placeholder:text-slate-600 focus:border-cyan-400/40 focus:bg-[#020617] focus:ring-4 focus:ring-cyan-400/5"
            >

            <button
                x-show="search"
                x-cloak
                type="button"
                @click="search = ''"
                class="absolute right-2 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-lg text-slate-500 transition hover:bg-white/5 hover:text-slate-200"
                aria-label="Clear search"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Selects -->
        <div class="grid min-w-0 gap-3 sm:grid-cols-2 2xl:w-[380px]">
            <div class="relative min-w-0">
                <select
                    x-model="project"
                    class="h-11 w-full appearance-none rounded-xl border border-slate-800 bg-[#020617]/80 px-3.5 pr-9 text-xs font-black text-slate-300 outline-none transition focus:border-cyan-400/40 focus:ring-4 focus:ring-cyan-400/5"
                >
                    <option value="all">All projects</option>

                    <template x-for="item in stats.projects" :key="item.id">
                        <option :value="item.id" x-text="item.label"></option>
                    </template>
                </select>

                <svg
                    class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                </svg>
            </div>

            <div class="relative min-w-0">
                <select
                    x-model="category"
                    class="h-11 w-full appearance-none rounded-xl border border-slate-800 bg-[#020617]/80 px-3.5 pr-9 text-xs font-black text-slate-300 outline-none transition focus:border-cyan-400/40 focus:ring-4 focus:ring-cyan-400/5"
                >
                    <option value="all">All categories</option>

                    <template x-for="item in stats.categories" :key="item">
                        <option :value="item" x-text="label(item)"></option>
                    </template>
                </select>

                <svg
                    class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                </svg>
            </div>
        </div>

        <!-- Severity -->
        <div class="min-w-0 overflow-x-auto rounded-xl border border-slate-800 bg-[#020617]/80 p-1 [scrollbar-width:thin] 2xl:max-w-[430px]">
            <div class="flex min-w-max gap-1">
                <template x-for="item in severityOptions" :key="item">
                    <button
                        type="button"
                        @click="severity = item"
                        class="shrink-0 rounded-lg px-3.5 py-2 text-xs font-black uppercase tracking-wide transition"
                        :class="severity === item
                            ? 'bg-cyan-400/10 text-cyan-300 ring-1 ring-cyan-400/30'
                            : 'text-slate-500 hover:bg-white/5 hover:text-slate-200'"
                        x-text="label(item)"
                    ></button>
                </template>
            </div>
        </div>

        <!-- Count + Reset -->
        <div class="flex items-center gap-2 2xl:ml-auto">
            <div class="hidden h-11 items-center rounded-xl border border-slate-800 bg-[#020617]/70 px-3 text-xs font-black text-slate-300 sm:flex">
                <span x-text="filteredLogs.length"></span>
                <span class="ml-1 text-slate-600">results</span>
            </div>

            <button
                type="button"
                @click="search = ''; project = 'all'; category = 'all'; severity = 'all'"
                class="h-11 rounded-xl border border-slate-800 bg-[#020617]/70 px-4 text-xs font-black text-slate-400 transition hover:border-cyan-400/30 hover:bg-cyan-400/10 hover:text-cyan-200"
            >
                Reset
            </button>
        </div>
    </div>
</section>

        <template x-if="errorMessage">
            <section class="audit-panel flex flex-col gap-3 overflow-hidden rounded-xl border border-red-400/20 bg-red-400/10 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-black text-red-200">Audit feed unavailable</p>
                    <p class="mt-1 text-xs font-medium text-red-200/70" x-text="errorMessage"></p>
                </div>

                <button
                    type="button"
                    @click="load()"
                    class="h-9 rounded-lg border border-red-300/20 px-4 text-xs font-black text-red-100 transition hover:border-red-200/40 hover:bg-red-300/10"
                >
                    Retry
                </button>
            </section>
        </template>

        <section class="audit-panel overflow-hidden rounded-xl border border-slate-800 bg-[#07111f]">
            <div class="flex flex-col gap-3 border-b border-slate-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Event Bus</p>
                    <h2 class="mt-1 text-lg font-black text-white">Incoming audit stream</h2>
                </div>
                <p class="text-xs font-bold text-slate-500">
                    <span x-text="filteredLogs.length"></span> visible / <span x-text="logs.length"></span> loaded
                </p>
            </div>

            <div x-ref="stream" class="max-h-[720px] overflow-y-auto [scrollbar-width:thin] [scrollbar-color:rgba(34,211,238,0.35)_transparent]">
                <template x-if="loading">
                    <div class="px-5 py-12 text-center text-sm font-semibold text-slate-500">Connecting to audit stream...</div>
                </template>

                <template x-if="!loading && filteredLogs.length === 0">
                    <div class="px-5 py-12 text-center">
                        <p class="text-sm font-bold text-slate-400">No live events match this view</p>
                        <p class="mt-1 text-xs font-medium text-slate-600">Change filters or wait for the next incoming event.</p>
                    </div>
                </template>

                <div class="divide-y divide-slate-800" x-show="!loading">
                    <template x-for="log in filteredLogs" :key="log.id">
                        <article
                            class="audit-stream-row grid gap-3 px-5 py-4 transition lg:grid-cols-[112px_1fr_180px] lg:items-center"
                            :class="isFresh(log.id) ? 'is-fresh bg-cyan-400/[0.08] ring-1 ring-inset ring-cyan-400/20' : 'hover:bg-white/[0.025]'"
                        >
                            <div class="flex items-center gap-3">
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full" :class="severityDot(log.severity)"></span>
                                <div>
                                    <p class="font-mono text-xs font-black text-cyan-300" x-text="log.created_time"></p>
                                    <p class="mt-1 text-[10px] font-semibold text-slate-600" x-text="log.created_human"></p>
                                </div>
                            </div>

                            <div class="min-w-0">
                                <div class="mb-2 flex flex-wrap items-center gap-2">
                                    <span class="rounded-md border px-2 py-1 text-[10px] font-black uppercase" :class="severityClass(log.severity)" x-text="label(log.severity)"></span>
                                    <span class="rounded-md border px-2 py-1 text-[10px] font-black uppercase" :class="categoryClass(log.category)" x-text="log.category_label"></span>
                                    <span x-show="isFresh(log.id)" x-cloak class="rounded-md border border-cyan-400/20 bg-cyan-400/10 px-2 py-1 text-[10px] font-black uppercase text-cyan-300">new</span>
                                </div>

                                <p class="truncate text-sm font-black text-white" x-text="log.event_label"></p>
                                <p class="mt-1 truncate text-xs font-medium text-slate-500">
                                    <span x-text="log.project"></span>
                                    <span class="mx-1 text-slate-700">/</span>
                                    <span x-text="log.actor"></span>
                                    <span class="mx-1 text-slate-700">/</span>
                                    <span x-text="log.ip"></span>
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-xs lg:block lg:text-right">
                                <div>
                                    <p class="font-bold uppercase tracking-wider text-slate-600">Target</p>
                                    <p class="mt-1 truncate font-semibold text-slate-300" x-text="log.target"></p>
                                </div>
                                <div class="lg:mt-2">
                                    <p class="font-bold uppercase tracking-wider text-slate-600">Metadata</p>
                                    <p class="mt-1 font-semibold text-slate-300"><span x-text="log.metadata_count"></span> fields</p>
                                </div>
                            </div>
                        </article>
                    </template>
                </div>
            </div>
        </section>
    </div>

    <script>
        function auditLogsPage() {
            return {
                feedUrl: @js(route('audit-logs.feed')),
                logs: [],
                stats: {
                    visible: 0,
                    total_projects: 0,
                    critical: 0,
                    high: 0,
                    medium: 0,
                    low: 0,
                    info: 0,
                    auth: 0,
                    file_security: 0,
                    latest_human: '-',
                    latest_time: '--:--:--',
                    categories: [],
                    severities: ['critical', 'high', 'medium', 'low', 'info'],
                    projects: [],
                },
                search: '',
                project: 'all',
                severity: 'all',
                category: 'all',
                live: true,
                connected: true,
                loading: true,
                firstLoad: true,
                errorMessage: '',
                newEvents: 0,
                freshIds: [],
                latestLog: null,
                lastUpdated: '--:--:--',
                timer: null,
                freshTimer: null,

                get filteredLogs() {
                    const q = this.search.toLowerCase().trim();

                    return this.logs.filter((log) => {
                        const searchable = String(log.search || '').toLowerCase();
                        const matchesSearch = !q || searchable.includes(q);
                        const matchesProject = this.project === 'all' || String(log.project_id || '') === String(this.project);
                        const matchesSeverity = this.severity === 'all' || log.severity === this.severity;
                        const matchesCategory = this.category === 'all' || log.category === this.category;

                        return matchesSearch && matchesProject && matchesSeverity && matchesCategory;
                    });
                },

                get severityOptions() {
                    const base = ['all', 'critical', 'high', 'medium', 'low', 'info'];
                    const incoming = Array.isArray(this.stats.severities) ? this.stats.severities : [];

                    return [...new Set([...base, ...incoming].filter(Boolean))];
                },

                async load() {
                    try {
                        const previousIds = new Set(this.logs.map((log) => log.id));
                        const response = await fetch(this.feedUrl, {
                            headers: { Accept: 'application/json' },
                        });
                        if (!response.ok) {
                            throw new Error(`Feed request failed (${response.status})`);
                        }

                        const contentType = response.headers.get('content-type') || '';
                        if (!contentType.includes('application/json')) {
                            throw new Error('Audit feed returned an unexpected response. Please refresh the page.');
                        }

                        const payload = await response.json();
                        const incoming = Array.isArray(payload.logs) ? payload.logs : [];

                        if (!this.firstLoad) {
                            const fresh = incoming
                                .filter((log) => !previousIds.has(log.id))
                                .map((log) => log.id);

                            this.freshIds = fresh;
                            this.newEvents = fresh.length;

                            clearTimeout(this.freshTimer);
                            if (fresh.length > 0) {
                                this.freshTimer = setTimeout(() => {
                                    this.freshIds = [];
                                    this.newEvents = 0;
                                }, 4500);
                            } else {
                                this.freshTimer = null;
                            }
                        }

                        this.logs = incoming;
                        this.latestLog = incoming[0] || null;
                        this.stats = { ...this.stats, ...(payload.stats || {}) };
                        this.lastUpdated = new Date().toLocaleTimeString();
                        this.loading = false;
                        this.connected = true;
                        this.errorMessage = '';
                        this.firstLoad = false;
                        this.$nextTick(() => {
                            if (this.$refs.stream) {
                                this.$refs.stream.scrollTop = 0;
                            }
                        });
                    } catch (error) {
                        this.connected = false;
                        this.loading = false;
                        this.errorMessage = error?.message || 'Unable to refresh the audit stream.';
                    }
                },

                start() {
                    clearInterval(this.timer);
                    this.load();
                    this.timer = setInterval(() => {
                        if (this.live) {
                            this.load();
                        }
                    }, 2000);
                },

                toggleLive() {
                    this.live = !this.live;

                    if (this.live) {
                        this.load();
                    }
                },

                isFresh(id) {
                    return this.freshIds.includes(id);
                },

                label(value) {
                    return String(value || '')
                        .replaceAll('_', ' ')
                        .replace(/\b\w/g, (letter) => letter.toUpperCase());
                },

                severityDot(severity) {
                    return {
                        critical: 'bg-red-400 shadow-[0_0_12px_rgba(248,113,113,0.8)]',
                        high: 'bg-orange-400 shadow-[0_0_12px_rgba(251,146,60,0.75)]',
                        medium: 'bg-amber-400 shadow-[0_0_12px_rgba(251,191,36,0.65)]',
                        low: 'bg-cyan-400 shadow-[0_0_12px_rgba(34,211,238,0.65)]',
                        info: 'bg-sky-400 shadow-[0_0_12px_rgba(56,189,248,0.6)]',
                    }[severity] || 'bg-slate-500';
                },

                severityClass(severity) {
                    return {
                        critical: 'border-red-400/20 bg-red-400/10 text-red-300',
                        high: 'border-orange-400/20 bg-orange-400/10 text-orange-300',
                        medium: 'border-amber-400/20 bg-amber-400/10 text-amber-300',
                        low: 'border-cyan-400/20 bg-cyan-400/10 text-cyan-300',
                        info: 'border-sky-400/20 bg-sky-400/10 text-sky-300',
                    }[severity] || 'border-slate-700 bg-slate-900 text-slate-300';
                },

                categoryClass(category) {
                    return {
                        audit: 'border-slate-500/20 bg-slate-500/10 text-slate-300',
                        auth: 'border-cyan-400/20 bg-cyan-400/10 text-cyan-300',
                        firewall: 'border-red-400/20 bg-red-400/10 text-red-300',
                        blocking: 'border-amber-400/20 bg-amber-400/10 text-amber-300',
                        file_security: 'border-orange-400/20 bg-orange-400/10 text-orange-300',
                        health: 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300',
                    }[category] || 'border-slate-700 bg-slate-900 text-slate-300';
                },
            };
        }
    </script>
</x-dashboard-layout>
