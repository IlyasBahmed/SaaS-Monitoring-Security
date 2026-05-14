<x-dashboard-layout>
    <div
        x-data="incidentsPage()"
        x-init="$watch('search', () => resetPage())"
        class="space-y-6"
    >
        {{-- Header --}}
        <section class="rounded-3xl border border-slate-800 bg-[#07111f] p-6">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1">
                        <span class="h-2 w-2 rounded-full bg-cyan-300"></span>

                        <span class="text-[10px] font-black uppercase tracking-[0.24em] text-cyan-300">
                            Security Operations
                        </span>
                    </div>

                    <h1 class="mt-4 text-4xl font-black tracking-tight text-white">
                        Incidents
                    </h1>

                    <p class="mt-2 text-sm font-medium text-slate-400">
                        Monitor and investigate active security incidents.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="rounded-2xl border border-red-400/20 bg-red-400/10 px-5 py-3">
                        <p class="text-[10px] font-black uppercase tracking-wider text-red-300/60">
                            Open
                        </p>

                        <p class="mt-1 text-xl font-black text-red-300">
                            {{ $stats['open'] }}
                        </p>
                    </div>

                    <a
                        href="{{ route('audit-logs.index') }}"
                        class="inline-flex h-11 items-center justify-center rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-5 text-xs font-black text-cyan-300 transition hover:bg-cyan-400/20"
                    >
                        Audit Logs
                    </a>
                </div>
            </div>
        </section>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-400/20 bg-emerald-400/10 px-5 py-4 text-sm font-bold text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-2xl border border-red-400/20 bg-red-400/10 px-5 py-4 text-sm font-bold text-red-300">
                {{ session('error') }}
            </div>
        @endif

       {{-- Stats --}}
<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
    @php
        $cards = [
            ['label' => 'Total', 'value' => $stats['total'], 'color' => 'text-white', 'bg' => 'from-slate-500/10', 'dot' => 'bg-slate-400'],
            ['label' => 'Open', 'value' => $stats['open'], 'color' => 'text-red-300', 'bg' => 'from-red-400/10', 'dot' => 'bg-red-400'],
            ['label' => 'Critical', 'value' => $stats['critical'], 'color' => 'text-red-300', 'bg' => 'from-red-400/10', 'dot' => 'bg-red-400'],
            ['label' => 'High', 'value' => $stats['high'], 'color' => 'text-orange-300', 'bg' => 'from-orange-400/10', 'dot' => 'bg-orange-400'],
            ['label' => 'Assigned', 'value' => $stats['assigned'], 'color' => 'text-cyan-300', 'bg' => 'from-cyan-400/10', 'dot' => 'bg-cyan-400'],
            ['label' => 'Resolved', 'value' => $stats['resolved'], 'color' => 'text-emerald-300', 'bg' => 'from-emerald-400/10', 'dot' => 'bg-emerald-400'],
        ];
    @endphp

    @foreach ($cards as $card)
        <div class="group relative overflow-hidden rounded-2xl border border-slate-800 bg-gradient-to-br {{ $card['bg'] }} to-[#07111f] p-5 shadow-lg shadow-black/10 transition hover:-translate-y-1 hover:border-slate-700">
            <div class="absolute right-0 top-0 h-20 w-20 rounded-full bg-white/5 blur-2xl"></div>

            <div class="relative flex items-start justify-between gap-4">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-500">
                        {{ $card['label'] }}
                    </p>

                    <p class="mt-3 text-3xl font-black tracking-tight {{ $card['color'] }}">
                        {{ $card['value'] }}
                    </p>
                </div>

                <span class="mt-1 h-2.5 w-2.5 rounded-full {{ $card['dot'] }} shadow-[0_0_14px_currentColor]"></span>
            </div>
        </div>
    @endforeach
</section>

        {{-- Filters --}}
        <section class="rounded-2xl border border-slate-800 bg-[#07111f] p-4">
            <div class="grid gap-4 lg:grid-cols-[1fr_auto_auto] lg:items-center">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z"/>
                    </svg>

                    <input
                        x-model.debounce.250ms="search"
                        type="search"
                        placeholder="Search incident, asset, target, client, IP..."
                        class="h-11 w-full rounded-xl border border-slate-800 bg-[#020617] pl-11 pr-4 text-sm font-medium text-slate-300 outline-none placeholder:text-slate-600 focus:border-cyan-400/40"
                    >
                </div>

                <select
                    x-model="status"
                    @change="resetPage()"
                    class="h-11 rounded-xl border border-slate-800 bg-[#020617] px-4 text-xs font-bold text-slate-300 outline-none focus:border-cyan-400/40"
                >
                    <option value="all">All Status</option>
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                </select>

                <select
                    x-model="severity"
                    @change="resetPage()"
                    class="h-11 rounded-xl border border-slate-800 bg-[#020617] px-4 text-xs font-bold text-slate-300 outline-none focus:border-cyan-400/40"
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
        <section class="overflow-hidden rounded-3xl border border-slate-800 bg-[#07111f]">
            <div class="flex items-center justify-between border-b border-slate-800 px-6 py-5">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-cyan-400/60">
                        Response Queue
                    </p>

                    <h2 class="mt-1 text-xl font-black text-white">
                        Active Incidents
                    </h2>
                </div>

                <div class="rounded-full border border-slate-800 bg-[#020617] px-3 py-1.5 text-xs font-black text-slate-500">
                    <span x-text="filteredRows.length"></span>
                    /
                    <span x-text="rows.length"></span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1180px]">
                    <thead class="bg-[#020617] text-left text-[10px] uppercase tracking-[0.18em] text-slate-500">
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
                                <td colspan="8" class="px-6 py-16 text-center">
                                    <p class="text-sm font-black text-slate-300">
                                        No incidents found
                                    </p>

                                    <p class="mt-1 text-xs font-semibold text-slate-600">
                                        Try another filter or search.
                                    </p>
                                </td>
                            </tr>
                        </template>

                        <template x-for="row in paginatedRows" :key="row.id">
                            <tr class="transition hover:bg-white/[0.03]">
                                <td class="px-6 py-5 align-top">
                                    <div class="mb-3 flex flex-wrap items-center gap-2">
                                        <span
                                            class="rounded-lg border px-2.5 py-1 text-[10px] font-black uppercase"
                                            :class="severityClass(row.severity)"
                                            x-text="row.severity"
                                        ></span>

                                        <span
                                            class="rounded-lg border px-2.5 py-1 text-[10px] font-black uppercase"
                                            :class="categoryClass(row.category)"
                                            x-text="row.category_label"
                                        ></span>
                                    </div>

                                    <p
                                        class="max-w-sm break-words text-sm font-black leading-5 text-white"
                                        x-text="row.event_label"
                                    ></p>

                                    <p class="mt-2 text-xs font-semibold text-slate-600">
                                        <span x-text="row.metadata_count"></span>
                                        metadata fields
                                    </p>
                                </td>

                                <td class="px-6 py-5 align-top">
                                    <p
                                        class="max-w-[220px] break-words text-sm font-black text-cyan-300"
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
                                        class="inline-flex rounded-xl border border-slate-800 bg-[#020617] px-3 py-1.5 font-mono text-xs font-black text-slate-300"
                                        x-text="row.ip"
                                    ></p>
                                </td>

                                <td class="px-6 py-5 align-top">
                                    <div class="max-w-[180px]">
                                        <span
                                            x-show="row.is_mine"
                                            class="inline-flex rounded-lg border border-cyan-400/20 bg-cyan-400/10 px-2.5 py-1 text-[10px] font-black uppercase text-cyan-300"
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
                                        class="inline-flex rounded-lg border px-2.5 py-1 text-[10px] font-black uppercase"
                                        :class="statusClass(row.status)"
                                        x-text="label(row.status)"
                                    ></span>
                                </td>

                                <td class="px-6 py-5 text-right align-top">
                                    @if ($canTakeIncidents)
                                        <form
                                            method="POST"
                                            :action="takeUrl(row.id)"
                                            x-show="!row.is_assigned && !['resolved', 'closed'].includes(row.status)"
                                        >
                                            @csrf

                                            <button
                                                type="submit"
                                                class="inline-flex h-10 items-center rounded-xl border border-cyan-400/20 bg-cyan-400/10 px-4 text-xs font-black text-cyan-300 transition hover:bg-cyan-400/20"
                                            >
                                                Take
                                            </button>
                                        </form>

                                        <span
                                            x-show="row.is_mine"
                                            class="inline-flex h-10 items-center rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 text-xs font-black text-emerald-300"
                                        >
                                            Working
                                        </span>

                                        <span
                                            x-show="row.is_assigned && !row.is_mine"
                                            class="inline-flex h-10 items-center rounded-xl border border-slate-700 bg-slate-900/30 px-4 text-xs font-black text-slate-500"
                                        >
                                            Assigned
                                        </span>
                                    @else
                                        <span class="inline-flex h-10 items-center rounded-xl border border-slate-700 bg-slate-900/30 px-4 text-xs font-black text-slate-500">
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
            <div class="flex flex-col gap-4 border-t border-slate-800 bg-[#020617]/50 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-3">
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
                        class="h-10 rounded-xl border border-slate-800 bg-[#07111f] px-3 text-xs font-bold text-slate-300 outline-none"
                    >
                        <template x-for="option in perPageOptions" :key="option">
                            <option :value="option" x-text="`${option} / page`"></option>
                        </template>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        @click="page = Math.max(1, page - 1)"
                        :disabled="page === 1"
                        class="h-10 rounded-xl border border-slate-800 bg-[#07111f] px-4 text-xs font-black text-slate-400 transition hover:border-cyan-400/30 hover:text-cyan-300 disabled:opacity-30"
                    >
                        Prev
                    </button>

                    <template x-for="number in visiblePages" :key="number">
                        <button
                            @click="page = number"
                            class="h-10 min-w-10 rounded-xl border px-3 text-xs font-black transition"
                            :class="page === number
                                ? 'border-cyan-400/40 bg-cyan-400/10 text-cyan-300'
                                : 'border-slate-800 bg-[#07111f] text-slate-500 hover:text-cyan-300'"
                            x-text="number"
                        ></button>
                    </template>

                    <button
                        @click="page = Math.min(totalPages, page + 1)"
                        :disabled="page === totalPages"
                        class="h-10 rounded-xl border border-slate-800 bg-[#07111f] px-4 text-xs font-black text-slate-400 transition hover:border-cyan-400/30 hover:text-cyan-300 disabled:opacity-30"
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
                perPage: 10,
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

                severityClass(severity) {
                    return {
                        critical: 'border-red-400/30 bg-red-400/10 text-red-300',
                        high: 'border-orange-400/30 bg-orange-400/10 text-orange-300',
                        medium: 'border-amber-400/30 bg-amber-400/10 text-amber-300',
                        low: 'border-cyan-400/30 bg-cyan-400/10 text-cyan-300',
                    }[severity] || 'border-slate-700 bg-slate-900 text-slate-300';
                },

                categoryClass(category) {
                    return {
                        auth: 'border-cyan-400/30 bg-cyan-400/10 text-cyan-300',
                        firewall: 'border-red-400/30 bg-red-400/10 text-red-300',
                        blocking: 'border-amber-400/30 bg-amber-400/10 text-amber-300',
                        file_security: 'border-orange-400/30 bg-orange-400/10 text-orange-300',
                        health: 'border-emerald-400/30 bg-emerald-400/10 text-emerald-300',
                    }[category] || 'border-slate-700 bg-slate-900 text-slate-300';
                },

                statusClass(status) {
                    return {
                        in_progress: 'border-cyan-400/30 bg-cyan-400/10 text-cyan-300',
                        resolved: 'border-emerald-400/30 bg-emerald-400/10 text-emerald-300',
                        closed: 'border-emerald-400/30 bg-emerald-400/10 text-emerald-300',
                        open: 'border-red-400/30 bg-red-400/10 text-red-300',
                    }[status] || 'border-slate-700 bg-slate-900 text-slate-300';
                },
            };
        }
    </script>
</x-dashboard-layout>