<x-dashboard-layout>
    @push('styles')
        <style>
            .incident-motion {
                --incident-cyan: rgba(34, 211, 238, 0.18);
                --incident-red: rgba(248, 113, 113, 0.14);
                --incident-amber: rgba(251, 191, 36, 0.10);
            }

            .incident-light-field {
                background:
                    radial-gradient(circle at 22% 14%, var(--incident-cyan), transparent 28rem),
                    radial-gradient(circle at 88% 22%, var(--incident-red), transparent 24rem),
                    radial-gradient(circle at 48% 110%, var(--incident-amber), transparent 30rem),
                    linear-gradient(180deg, rgba(2, 6, 23, 0.96), rgba(2, 6, 23, 0.86));
            }

            .incident-light-field::before,
            .incident-light-field::after {
                content: "";
                position: absolute;
                inset: -35%;
                pointer-events: none;
                opacity: 0.58;
                mix-blend-mode: screen;
            }

            .incident-light-field::before {
                background: linear-gradient(115deg, transparent 36%, rgba(103, 232, 249, 0.12) 47%, transparent 58%);
                animation: incidentLightSweep 11s ease-in-out infinite;
            }

            .incident-light-field::after {
                background:
                    linear-gradient(rgba(103, 232, 249, 0.045) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(103, 232, 249, 0.035) 1px, transparent 1px);
                background-size: 44px 44px;
                mask-image: linear-gradient(to bottom, transparent, black 18%, black 72%, transparent);
                animation: incidentGridDrift 18s linear infinite;
            }

            .incident-panel {
                position: relative;
            }

            .incident-panel::before {
                content: "";
                position: absolute;
                inset: 0;
                pointer-events: none;
                background: linear-gradient(120deg, transparent 0%, rgba(103, 232, 249, 0.08) 42%, transparent 64%);
                opacity: 0;
                transform: translateX(-35%);
                transition: opacity 220ms ease, transform 520ms ease;
            }

            .incident-panel:hover::before {
                opacity: 1;
                transform: translateX(35%);
            }

            .incident-card-light {
                isolation: isolate;
            }

            .incident-card-light::after {
                content: "";
                position: absolute;
                inset: -1px;
                z-index: -1;
                border-radius: inherit;
                background: conic-gradient(from 180deg, transparent, rgba(103, 232, 249, 0.20), transparent, rgba(248, 113, 113, 0.16), transparent);
                opacity: 0;
                filter: blur(12px);
                transition: opacity 240ms ease;
            }

            .incident-card-light:hover::after {
                opacity: 0.65;
                animation: incidentGlowSpin 4.8s linear infinite;
            }

            .incident-row {
                position: relative;
            }

            .incident-row::before {
                content: "";
                position: absolute;
                inset: 0 auto 0 0;
                width: 3px;
                background: linear-gradient(to bottom, transparent, rgba(103, 232, 249, 0.75), transparent);
                opacity: 0;
                transition: opacity 180ms ease;
            }

            .incident-row:hover::before {
                opacity: 1;
            }

            @keyframes incidentLightSweep {
                0%, 100% {
                    transform: translate3d(-18%, -8%, 0) rotate(0deg);
                }
                50% {
                    transform: translate3d(18%, 8%, 0) rotate(6deg);
                }
            }

            @keyframes incidentGridDrift {
                from {
                    transform: translate3d(0, 0, 0);
                }
                to {
                    transform: translate3d(44px, 44px, 0);
                }
            }

            @keyframes incidentGlowSpin {
                to {
                    transform: rotate(1turn);
                }
            }

            html:not(.dark) .incident-motion {
                --incident-cyan: rgba(8, 145, 178, 0.14);
                --incident-red: rgba(225, 29, 72, 0.10);
                --incident-amber: rgba(217, 119, 6, 0.10);
            }

            html:not(.dark) .incident-light-field {
                background:
                    radial-gradient(circle at 22% 14%, var(--incident-cyan), transparent 28rem),
                    radial-gradient(circle at 88% 22%, var(--incident-red), transparent 24rem),
                    linear-gradient(180deg, rgba(248, 250, 252, 0.98), rgba(226, 239, 247, 0.88));
            }

            html:not(.dark) .incident-panel {
                background: rgba(255, 255, 255, 0.88);
                border-color: rgba(203, 213, 225, 0.9);
                box-shadow: 0 20px 42px -30px rgba(15, 23, 42, 0.38);
            }

            html:not(.dark) .incident-panel::before {
                background: linear-gradient(120deg, transparent 0%, rgba(8, 145, 178, 0.09) 42%, transparent 64%);
            }

            html:not(.dark) .incident-card-light {
                box-shadow: 0 16px 32px -28px rgba(15, 23, 42, 0.45);
            }

            html:not(.dark) .incident-card-light::after {
                background: conic-gradient(from 180deg, transparent, rgba(8, 145, 178, 0.18), transparent, rgba(225, 29, 72, 0.12), transparent);
            }

            html:not(.dark) .incident-motion :where(input, select) {
                background-color: rgba(255, 255, 255, 0.92);
                border-color: rgba(203, 213, 225, 0.95);
                color: #0f172a;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.86);
            }

            html:not(.dark) .incident-motion :where(input, select):focus {
                background-color: #ffffff;
                border-color: rgba(8, 145, 178, 0.38);
                box-shadow: 0 0 0 4px rgba(8, 145, 178, 0.10);
            }

            html:not(.dark) .incident-motion :where(thead, .bg-slate-950\/70, .bg-slate-950\/80, .bg-slate-950\/90, .bg-slate-950) {
                background-color: rgba(248, 250, 252, 0.88);
            }

            html:not(.dark) .incident-motion :where(.border-white\/10, .divide-white\/5 > :not([hidden]) ~ :not([hidden])) {
                border-color: rgba(203, 213, 225, 0.86);
            }

            html:not(.dark) .incident-row:hover {
                background-color: rgba(236, 254, 255, 0.72);
            }

            html:not(.dark) .incident-row::before {
                background: linear-gradient(to bottom, transparent, rgba(8, 145, 178, 0.72), transparent);
            }

            html:not(.dark) .incident-motion .shadow-black\/30,
            html:not(.dark) .incident-motion .shadow-black\/20 {
                --tw-shadow-color: rgba(148, 163, 184, 0.24);
            }

            @media (prefers-reduced-motion: reduce) {
                .incident-light-field::before,
                .incident-light-field::after,
                .incident-card-light:hover::after {
                    animation: none;
                }

                .incident-panel::before {
                    transition: none;
                }
            }
        </style>
    @endpush

    <div
        x-data="incidentsPage()"
        x-init="$watch('search', () => resetPage())"
        class="incident-motion relative space-y-6 overflow-hidden"
    >
        {{-- Ambient background --}}
        <div class="incident-light-field pointer-events-none fixed inset-0 -z-10 overflow-hidden"></div>

        {{-- Header --}}
        <section class="incident-panel relative overflow-hidden rounded-[2rem] border border-white/10 bg-slate-950/80 p-6 shadow-2xl shadow-black/30 backdrop-blur xl:p-8">
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-cyan-300/50 to-transparent"></div>
            <div class="absolute -right-24 -top-24 h-56 w-56 rounded-full bg-cyan-400/10 blur-3xl"></div>
            <div class="absolute -bottom-24 left-10 h-56 w-56 rounded-full bg-red-400/10 blur-3xl"></div>

            <div class="relative flex flex-col gap-8 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-cyan-300/10 px-3 py-1.5 shadow-lg shadow-cyan-950/30">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-cyan-300 opacity-60"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-cyan-300"></span>
                        </span>

                        <span class="text-[10px] font-black uppercase tracking-[0.28em] text-cyan-200">
                            Security Operations Center
                        </span>
                    </div>

                    <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between xl:block">
                        <div>
                            <h1 class="text-4xl font-black tracking-tight text-white sm:text-5xl">
                                Incidents
                            </h1>

                            <p class="mt-3 max-w-2xl text-sm font-medium leading-6 text-slate-400">
                                Monitor, triage and resolve security incidents from one operational queue.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:min-w-[420px]">
                    <div class="incident-card-light relative rounded-3xl border border-red-300/20 bg-red-400/10 p-5 shadow-lg shadow-red-950/20">
                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-red-200/70">
                            Open incidents
                        </p>

                        <div class="mt-3 flex items-end justify-between gap-4">
                            <p class="text-4xl font-black tracking-tight text-red-200">
                                {{ $stats['open'] }}
                            </p>
                            <span class="rounded-full border border-red-300/20 bg-red-300/10 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-red-200">
                                Live
                            </span>
                        </div>
                    </div>

                    <a
                        href="{{ route('audit-logs.index') }}"
                        class="incident-card-light group relative flex items-center justify-between rounded-3xl border border-cyan-300/20 bg-cyan-300/10 p-5 shadow-lg shadow-cyan-950/20 transition hover:-translate-y-0.5 hover:bg-cyan-300/15"
                    >
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-cyan-200/70">
                                Investigation
                            </p>
                            <p class="mt-3 text-sm font-black text-cyan-100">
                                View audit logs
                            </p>
                        </div>

                        <span class="grid h-11 w-11 place-items-center rounded-2xl border border-cyan-300/20 bg-cyan-300/10 text-cyan-100 transition group-hover:translate-x-1">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </span>
                    </a>
                </div>
            </div>
        </section>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="flex items-start gap-3 rounded-2xl border border-emerald-300/20 bg-emerald-400/10 px-5 py-4 text-sm font-bold text-emerald-200 shadow-lg shadow-emerald-950/20">
                <span class="mt-1 h-2 w-2 rounded-full bg-emerald-300 shadow-[0_0_18px_rgba(110,231,183,0.8)]"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-start gap-3 rounded-2xl border border-red-300/20 bg-red-400/10 px-5 py-4 text-sm font-bold text-red-200 shadow-lg shadow-red-950/20">
                <span class="mt-1 h-2 w-2 rounded-full bg-red-300 shadow-[0_0_18px_rgba(252,165,165,0.8)]"></span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- Stats --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            @php
                $cards = [
                    ['label' => 'Total', 'value' => $stats['total'], 'color' => 'text-white', 'ring' => 'group-hover:border-white/20', 'bg' => 'from-white/10', 'dot' => 'bg-slate-300'],
                    ['label' => 'Open', 'value' => $stats['open'], 'color' => 'text-red-200', 'ring' => 'group-hover:border-red-300/30', 'bg' => 'from-red-400/15', 'dot' => 'bg-red-300'],
                    ['label' => 'Critical', 'value' => $stats['critical'], 'color' => 'text-red-200', 'ring' => 'group-hover:border-red-300/30', 'bg' => 'from-red-400/15', 'dot' => 'bg-red-300'],
                    ['label' => 'High', 'value' => $stats['high'], 'color' => 'text-orange-200', 'ring' => 'group-hover:border-orange-300/30', 'bg' => 'from-orange-400/15', 'dot' => 'bg-orange-300'],
                    ['label' => 'Assigned', 'value' => $stats['assigned'], 'color' => 'text-cyan-200', 'ring' => 'group-hover:border-cyan-300/30', 'bg' => 'from-cyan-400/15', 'dot' => 'bg-cyan-300'],
                    ['label' => 'Resolved', 'value' => $stats['resolved'], 'color' => 'text-emerald-200', 'ring' => 'group-hover:border-emerald-300/30', 'bg' => 'from-emerald-400/15', 'dot' => 'bg-emerald-300'],
                ];
            @endphp

            @foreach ($cards as $card)
                <div class="incident-card-light group relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br {{ $card['bg'] }} to-slate-950/90 p-5 shadow-xl shadow-black/20 backdrop-blur transition duration-300 hover:-translate-y-1 {{ $card['ring'] }}">
                    <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-white/5 blur-2xl transition group-hover:bg-white/10"></div>

                    <div class="relative flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">
                                {{ $card['label'] }}
                            </p>

                            <p class="mt-3 text-3xl font-black tracking-tight {{ $card['color'] }}">
                                {{ $card['value'] }}
                            </p>
                        </div>

                        <span class="mt-1 h-2.5 w-2.5 rounded-full {{ $card['dot'] }} shadow-[0_0_16px_currentColor]"></span>
                    </div>
                </div>
            @endforeach
        </section>

        {{-- Filters --}}
        <section class="incident-panel overflow-hidden rounded-3xl border border-white/10 bg-slate-950/75 p-4 shadow-xl shadow-black/20 backdrop-blur">
            <div class="grid gap-3 lg:grid-cols-[1fr_180px_180px] lg:items-center">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z"/>
                    </svg>

                    <input
                        x-model.debounce.250ms="search"
                        type="search"
                        placeholder="Search incident, asset, target, client, IP..."
                        class="h-12 w-full rounded-2xl border border-white/10 bg-slate-950/80 pl-11 pr-4 text-sm font-semibold text-slate-200 outline-none ring-0 placeholder:text-slate-600 transition focus:border-cyan-300/40 focus:bg-slate-950 focus:shadow-[0_0_0_4px_rgba(103,232,249,0.08)]"
                    >
                </div>

                <select
                    x-model="status"
                    @change="resetPage()"
                    class="h-12 rounded-2xl border border-white/10 bg-slate-950/80 px-4 text-xs font-black uppercase tracking-wider text-slate-300 outline-none transition focus:border-cyan-300/40 focus:shadow-[0_0_0_4px_rgba(103,232,249,0.08)]"
                >
                    <option value="all">All Status</option>
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                </select>

                <select
                    x-model="severity"
                    @change="resetPage()"
                    class="h-12 rounded-2xl border border-white/10 bg-slate-950/80 px-4 text-xs font-black uppercase tracking-wider text-slate-300 outline-none transition focus:border-cyan-300/40 focus:shadow-[0_0_0_4px_rgba(103,232,249,0.08)]"
                >
                    <option value="all">All Severity</option>
                    <option value="critical">Critical</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
        </section>

        {{-- Table --}}
        <section class="incident-panel overflow-hidden rounded-[2rem] border border-white/10 bg-slate-950/80 shadow-2xl shadow-black/30 backdrop-blur">
            <div class="flex flex-col gap-4 border-b border-white/10 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-cyan-300/60">
                        Response Queue
                    </p>

                    <h2 class="mt-1 text-xl font-black text-white">
                        Active Incidents
                    </h2>
                </div>

                <div class="inline-flex w-fit items-center gap-2 rounded-full border border-white/10 bg-slate-950 px-3 py-1.5 text-xs font-black text-slate-500">
                    <span class="h-1.5 w-1.5 rounded-full bg-cyan-300 shadow-[0_0_14px_rgba(103,232,249,0.8)]"></span>
                    <span x-text="filteredRows.length"></span>
                    <span>/</span>
                    <span x-text="rows.length"></span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1180px]">
                    <thead class="bg-slate-950/90 text-left text-[10px] uppercase tracking-[0.2em] text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Incident</th>
                            <th class="px-6 py-4">Asset</th>
                            <th class="px-6 py-4">Target</th>
                            <th class="px-6 py-4">IP</th>
                            <th class="px-6 py-4">Analyst</th>
                            <th class="px-6 py-4">Time</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-white/5">
                        <template x-if="filteredRows.length === 0">
                            <tr>
                                <td colspan="8" class="px-6 py-20 text-center">
                                    <div class="mx-auto grid h-14 w-14 place-items-center rounded-3xl border border-white/10 bg-white/5 text-slate-500">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007v.008H12v-.008ZM10.29 3.86 1.82 18a1.5 1.5 0 0 0 1.29 2.25h17.78A1.5 1.5 0 0 0 22.18 18L13.71 3.86a1.5 1.5 0 0 0-2.42 0Z" />
                                        </svg>
                                    </div>

                                    <p class="mt-4 text-sm font-black text-slate-200">
                                        No incidents found
                                    </p>

                                    <p class="mt-1 text-xs font-semibold text-slate-600">
                                        Try another filter or search keyword.
                                    </p>
                                </td>
                            </tr>
                        </template>

                        <template x-for="row in paginatedRows" :key="row.id">
                            <tr class="incident-row group transition hover:bg-white/[0.035]">
                                <td class="px-6 py-5 align-top">
                                    <div class="mb-3 flex flex-wrap items-center gap-2">
                                        <span
                                            class="rounded-xl border px-2.5 py-1 text-[10px] font-black uppercase tracking-wider"
                                            :class="severityClass(row.severity)"
                                            x-text="row.severity"
                                        ></span>

                                        <span
                                            class="rounded-xl border px-2.5 py-1 text-[10px] font-black uppercase tracking-wider"
                                            :class="categoryClass(row.category)"
                                            x-text="row.category_label"
                                        ></span>
                                    </div>

                                    <p
                                        class="max-w-sm break-words text-sm font-black leading-5 text-white transition group-hover:text-cyan-50"
                                        x-text="row.event_label"
                                    ></p>

                                    <p class="mt-2 text-xs font-semibold text-slate-600">
                                        <span x-text="row.metadata_count"></span>
                                        metadata fields
                                    </p>
                                </td>

                                <td class="px-6 py-5 align-top">
                                    <p
                                        class="max-w-[220px] break-words text-sm font-black text-cyan-200"
                                        x-text="row.project"
                                    ></p>

                                    <p
                                        class="mt-1 max-w-[220px] break-words text-xs font-semibold text-slate-600"
                                        x-text="row.client"
                                    ></p>
                                </td>

                                <td class="px-6 py-5 align-top">
                                    <p
                                        class="max-w-[240px] break-all text-xs font-bold text-slate-300"
                                        x-text="row.target"
                                    ></p>

                                    <p
                                        class="mt-1 max-w-[240px] break-all text-[11px] font-semibold text-slate-600"
                                        x-text="row.site_url"
                                    ></p>
                                </td>

                                <td class="px-6 py-5 align-top">
                                    <p
                                        class="inline-flex rounded-2xl border border-white/10 bg-slate-950 px-3 py-1.5 font-mono text-xs font-black text-slate-300 shadow-inner shadow-black/30"
                                        x-text="row.ip"
                                    ></p>
                                </td>

                                <td class="px-6 py-5 align-top">
                                    <div class="max-w-[180px]">
                                        <span
                                            x-show="row.is_mine"
                                            class="inline-flex rounded-xl border border-cyan-300/20 bg-cyan-300/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-cyan-200"
                                        >
                                            You
                                        </span>

                                        <p
                                            class="mt-1 break-words text-sm font-black"
                                            :class="row.is_assigned ? 'text-slate-200' : 'text-slate-600'"
                                            x-text="row.assigned_label"
                                        ></p>

                                        <p
                                            class="mt-1 text-[10px] font-semibold text-slate-600"
                                            x-text="row.assigned_human || 'No analyst assigned'"
                                        ></p>
                                    </div>
                                </td>

                                <td class="px-6 py-5 align-top">
                                    <p
                                        class="font-mono text-xs font-black text-slate-300"
                                        x-text="row.created_time"
                                    ></p>

                                    <p
                                        class="mt-1 text-[10px] font-semibold text-slate-600"
                                        x-text="row.created_human"
                                    ></p>
                                </td>

                                <td class="px-6 py-5 align-top">
                                    <span
                                        class="inline-flex rounded-xl border px-2.5 py-1 text-[10px] font-black uppercase tracking-wider"
                                        :class="statusClass(row.status)"
                                        x-text="label(row.status)"
                                    ></span>
                                </td>

                                <td class="px-6 py-5 text-right align-top">
                                    @if ($canTakeIncidents)
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <form
                                                method="POST"
                                                :action="takeUrl(row.id)"
                                                x-show="!row.is_assigned && row.status === 'open'"
                                            >
                                                @csrf

                                                <button
                                                    type="submit"
                                                    class="inline-flex h-10 items-center rounded-2xl border border-cyan-300/20 bg-cyan-300/10 px-4 text-xs font-black text-cyan-200 transition hover:-translate-y-0.5 hover:bg-cyan-300/20"
                                                >
                                                    Take
                                                </button>
                                            </form>

                                            <form
                                                method="POST"
                                                :action="resolveUrl(row.id)"
                                                x-show="row.status !== 'open' && !['resolved', 'closed'].includes(row.status)"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    type="submit"
                                                    class="inline-flex h-10 items-center rounded-2xl border border-emerald-300/20 bg-emerald-300/10 px-4 text-xs font-black text-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-300/20"
                                                >
                                                    Resolve
                                                </button>
                                            </form>

                                            <span
                                                x-show="row.is_mine && row.status === 'open'"
                                                class="inline-flex h-10 items-center rounded-2xl border border-emerald-300/20 bg-emerald-300/10 px-4 text-xs font-black text-emerald-200"
                                            >
                                                Working
                                            </span>

                                            <span
                                                x-show="row.is_assigned && !row.is_mine && !['resolved', 'closed'].includes(row.status)"
                                                class="inline-flex h-10 items-center rounded-2xl border border-white/10 bg-white/5 px-4 text-xs font-black text-slate-500"
                                            >
                                                Assigned
                                            </span>
                                        </div>
                                    @else
                                        <span class="inline-flex h-10 items-center rounded-2xl border border-white/10 bg-white/5 px-4 text-xs font-black text-slate-500">
                                            SOC only
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="flex flex-col gap-4 border-t border-white/10 bg-slate-950/70 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    <p class="text-xs font-bold text-slate-500">
                        Showing
                        <span class="text-slate-300" x-text="fromRow"></span>
                        -
                        <span class="text-slate-300" x-text="toRow"></span>
                        of
                        <span class="text-slate-300" x-text="filteredRows.length"></span>
                    </p>

                    <select
                        x-model.number="perPage"
                        @change="resetPage()"
                        class="h-10 rounded-2xl border border-white/10 bg-slate-950 px-3 text-xs font-bold text-slate-300 outline-none transition focus:border-cyan-300/40"
                    >
                        <template x-for="option in perPageOptions" :key="option">
                            <option :value="option" x-text="`${option} / page`"></option>
                        </template>
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        @click="page = Math.max(1, page - 1)"
                        :disabled="page === 1"
                        class="h-10 rounded-2xl border border-white/10 bg-slate-950 px-4 text-xs font-black text-slate-400 transition hover:border-cyan-300/30 hover:text-cyan-200 disabled:cursor-not-allowed disabled:opacity-30"
                    >
                        Prev
                    </button>

                    <template x-for="number in visiblePages" :key="number">
                        <button
                            @click="page = number"
                            class="h-10 min-w-10 rounded-2xl border px-3 text-xs font-black transition"
                            :class="page === number
                                ? 'border-cyan-300/40 bg-cyan-300/10 text-cyan-200 shadow-[0_0_0_4px_rgba(103,232,249,0.08)]'
                                : 'border-white/10 bg-slate-950 text-slate-500 hover:border-cyan-300/30 hover:text-cyan-200'"
                            x-text="number"
                        ></button>
                    </template>

                    <button
                        @click="page = Math.min(totalPages, page + 1)"
                        :disabled="page === totalPages"
                        class="h-10 rounded-2xl border border-white/10 bg-slate-950 px-4 text-xs font-black text-slate-400 transition hover:border-cyan-300/30 hover:text-cyan-200 disabled:cursor-not-allowed disabled:opacity-30"
                    >
                        Next
                    </button>
                </div>
            </div>
        </section>
    </div>

    <script>
        function incidentsPage() {
            return {
                rows: @js($rows),
                takeBaseUrl: @js(url('/incidents')),

                search: '',
                status: 'all',
                severity: 'all',

                page: 1,
                perPage: 3,
                perPageOptions: [10, 25, 50],

                get filteredRows() {
                    const q = this.search.toLowerCase().trim();

                    return this.rows.filter((row) => {
                        const matchesSearch = !q || String(row.search || '').toLowerCase().includes(q);

                        const matchesStatus = this.status === 'all'
                            || row.status === this.status
                            || (this.status === 'resolved' && ['resolved', 'closed'].includes(row.status));

                        const matchesSeverity = this.severity === 'all'
                            || row.severity === this.severity;

                        return matchesSearch && matchesStatus && matchesSeverity;
                    });
                },

                get totalPages() {
                    return Math.max(1, Math.ceil(this.filteredRows.length / this.perPage));
                },

                get paginatedRows() {
                    const start = (this.page - 1) * this.perPage;

                    return this.filteredRows.slice(
                        start,
                        start + this.perPage
                    );
                },

                get visiblePages() {
                    const pages = [];

                    let start = Math.max(1, this.page - 2);
                    let end = Math.min(this.totalPages, this.page + 2);

                    for (let i = start; i <= end; i++) {
                        pages.push(i);
                    }

                    return pages;
                },

                get fromRow() {
                    if (!this.filteredRows.length) return 0;

                    return ((this.page - 1) * this.perPage) + 1;
                },

                get toRow() {
                    return Math.min(
                        this.page * this.perPage,
                        this.filteredRows.length
                    );
                },

                resetPage() {
                    this.page = 1;
                },

                label(value) {
                    return String(value || '')
                        .replaceAll('_', ' ')
                        .replace(/\b\w/g, (letter) => letter.toUpperCase());
                },

                takeUrl(id) {
                    return `${this.takeBaseUrl}/${encodeURIComponent(id)}/take`;
                },

                resolveUrl(id) {
                    return `${this.takeBaseUrl}/${encodeURIComponent(id)}/resolve`;
                },

                severityClass(severity) {
                    return {
                        critical: 'border-red-300/30 bg-red-300/10 text-red-200 shadow-[0_0_18px_rgba(248,113,113,0.08)]',
                        high: 'border-orange-300/30 bg-orange-300/10 text-orange-200 shadow-[0_0_18px_rgba(251,146,60,0.08)]',
                        medium: 'border-amber-300/30 bg-amber-300/10 text-amber-200 shadow-[0_0_18px_rgba(251,191,36,0.08)]',
                        low: 'border-cyan-300/30 bg-cyan-300/10 text-cyan-200 shadow-[0_0_18px_rgba(103,232,249,0.08)]',
                    }[severity] || 'border-white/10 bg-white/5 text-slate-300';
                },

                categoryClass(category) {
                    return {
                        auth: 'border-cyan-300/30 bg-cyan-300/10 text-cyan-200',
                        firewall: 'border-red-300/30 bg-red-300/10 text-red-200',
                        blocking: 'border-amber-300/30 bg-amber-300/10 text-amber-200',
                        file_security: 'border-orange-300/30 bg-orange-300/10 text-orange-200',
                        health: 'border-emerald-300/30 bg-emerald-300/10 text-emerald-200',
                    }[category] || 'border-white/10 bg-white/5 text-slate-300';
                },

                statusClass(status) {
                    return {
                        in_progress: 'border-cyan-300/30 bg-cyan-300/10 text-cyan-200',
                        resolved: 'border-emerald-300/30 bg-emerald-300/10 text-emerald-200',
                        closed: 'border-emerald-300/30 bg-emerald-300/10 text-emerald-200',
                        open: 'border-red-300/30 bg-red-300/10 text-red-200',
                    }[status] || 'border-white/10 bg-white/5 text-slate-300';
                },
            };
        }
    </script>
</x-dashboard-layout>
