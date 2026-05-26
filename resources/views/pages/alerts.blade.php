<x-dashboard-layout>
    @php
        $viewErrors = $errors ?? new \Illuminate\Support\ViewErrorBag;
        $metricCards = [
            ['label' => 'Open', 'value' => $stats['open'], 'tone' => 'text-red-400', 'ring' => 'border-red-500/20 bg-red-500/10'],
            ['label' => 'Critical', 'value' => $stats['critical'], 'tone' => 'text-red-400', 'ring' => 'border-red-500/20 bg-red-500/10'],
            ['label' => 'High', 'value' => $stats['high'], 'tone' => 'text-orange-400', 'ring' => 'border-orange-500/20 bg-orange-500/10'],
            ['label' => 'Medium', 'value' => $stats['medium'] ?? 0, 'tone' => 'text-amber-400', 'ring' => 'border-amber-500/20 bg-amber-500/10'],
            ['label' => 'AI Score', 'value' => $stats['average_score'].'/100', 'tone' => 'text-sky-400', 'ring' => 'border-sky-500/20 bg-sky-500/10'],
            ['label' => 'Projects', 'value' => $stats['projects'], 'tone' => 'text-emerald-400', 'ring' => 'border-emerald-500/20 bg-emerald-500/10'],
        ];
    @endphp

    <div x-data="alertsPage()" x-init="init()" class="space-y-6">
        {{-- Header --}}
        <header class="overflow-hidden rounded-2xl border border-white/5 bg-gradient-to-br from-[#0a0f1a] to-[#05080f] shadow-2xl shadow-black/30">
            <div class="grid gap-0 xl:grid-cols-[1fr_600px]">
                <div class="border-b border-white/5 p-6 xl:border-b-0 xl:border-r">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex h-8 items-center rounded-full border border-amber-500/20 bg-amber-500/10 px-3.5 text-[11px] font-black uppercase tracking-[0.18em] text-amber-400 shadow-sm">
                            AI Security Center
                        </span>
                        <span class="inline-flex h-8 items-center rounded-full border border-white/5 bg-white/5 px-3.5 text-xs font-bold text-slate-400 backdrop-blur-sm">
                            {{ $stats['total'] }} total alerts
                        </span>
                    </div>

                    <div class="mt-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <h1 class="text-4xl font-black tracking-tight text-white">Security Alerts</h1>
                            <p class="mt-1.5 max-w-2xl text-sm font-medium text-slate-500">
                                AI-powered vulnerability detection with actionable intelligence.
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <a
                                href="{{ route('audit-logs.index') }}"
                                class="inline-flex h-10 items-center justify-center rounded-xl border border-sky-500/20 bg-sky-500/10 px-4 text-xs font-black text-sky-400 transition-all hover:bg-sky-500/20 hover:text-sky-300 hover:shadow-lg hover:shadow-sky-500/10"
                            >
                                <svg class="mr-1.5 h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Audit Logs
                            </a>
                            <button class="inline-flex h-10 items-center justify-center rounded-xl border border-white/10 bg-white/5 px-4 text-xs font-black text-slate-400 transition-all hover:bg-white/10 hover:text-white">
                                <svg class="mr-1.5 h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Export
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-px bg-white/5 sm:grid-cols-6 xl:grid-cols-1 xl:grid-flow-col">
                    @foreach ($metricCards as $metric)
                        <div class="bg-gradient-to-br from-[#0a0f1a] to-[#05080f] px-5 py-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-[11px] font-black uppercase tracking-wider text-slate-500">{{ $metric['label'] }}</p>
                                <div class="h-1.5 w-1.5 rounded-full shadow-sm {{ $metric['ring'] }}"></div>
                            </div>
                            <p class="mt-1.5 text-2xl font-black tracking-tight {{ $metric['tone'] }}">{{ $metric['value'] }}</p>
                            @if($metric['label'] === 'AI Score')
                                <div class="mt-2 h-0.5 w-full overflow-hidden rounded-full bg-white/5">
                                    <div class="h-full rounded-full bg-gradient-to-r from-sky-500 to-cyan-400" style="width: {{ $stats['average_score'] }}%"></div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </header>

        {{-- Notifications --}}
        @if (session('success'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-5 py-3.5 text-sm font-bold text-emerald-200 backdrop-blur-sm">
                <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if ($viewErrors->any())
            <div class="flex items-center gap-3 rounded-2xl border border-red-500/20 bg-red-500/10 px-5 py-3.5 text-sm font-bold text-red-200">
                <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $viewErrors->first() }}
            </div>
        @endif

        {{-- Main Content --}}
        <section class="grid gap-6 2xl:grid-cols-[minmax(0,1fr)_480px]">
            {{-- Alerts Table --}}
            <div class="min-w-0 overflow-hidden rounded-2xl border border-white/5 bg-gradient-to-br from-[#0a0f1a] to-[#05080f] shadow-2xl shadow-black/30">
                {{-- Filters Bar --}}
                <div class="border-b border-white/5 p-5">
                    <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z"/>
                            </svg>
                            <input
                                x-model.debounce.200ms="search"
                                type="search"
                                placeholder="Search alerts, projects, evidence..."
                                class="h-11 w-full rounded-xl border border-white/10 bg-black/30 pl-10 pr-4 text-sm font-medium text-white placeholder:text-slate-500 outline-none transition-all focus:border-sky-500/40 focus:bg-black/50 focus:shadow-lg focus:shadow-sky-500/10"
                            >
                        </div>

                        <div class="grid grid-cols-3 rounded-xl border border-white/10 bg-black/30 p-1">
                            <template x-for="item in ['open', 'all', 'resolved']" :key="item">
                                <button
                                    type="button"
                                    @click="status = item"
                                    class="rounded-lg px-4 py-2 text-xs font-black uppercase tracking-wider transition-all"
                                    :class="status === item ? 'bg-sky-500/15 text-sky-400 shadow-lg shadow-sky-500/10' : 'text-slate-500 hover:bg-white/5 hover:text-white'"
                                    x-text="label(item)"
                                ></button>
                            </template>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                        <select x-model="severity" class="h-11 rounded-xl border border-white/10 bg-black/30 px-3 text-xs font-bold text-white outline-none transition-all focus:border-sky-500/40 focus:shadow-lg focus:shadow-sky-500/10">
                            <option value="all" class="bg-[#0a0f1a]">All severity</option>
                            <template x-for="item in filters.severities" :key="item">
                                <option :value="item" x-text="label(item)" class="bg-[#0a0f1a]"></option>
                            </template>
                        </select>

                        <select x-model="type" class="h-11 rounded-xl border border-white/10 bg-black/30 px-3 text-xs font-bold text-white outline-none transition-all focus:border-sky-500/40 focus:shadow-lg focus:shadow-sky-500/10">
                            <option value="all" class="bg-[#0a0f1a]">All types</option>
                            <template x-for="item in filters.types" :key="item">
                                <option :value="item" x-text="label(item)" class="bg-[#0a0f1a]"></option>
                            </template>
                        </select>

                        <select x-model="project" class="h-11 rounded-xl border border-white/10 bg-black/30 px-3 text-xs font-bold text-white outline-none transition-all focus:border-sky-500/40 focus:shadow-lg focus:shadow-sky-500/10">
                            <option value="all" class="bg-[#0a0f1a]">All projects</option>
                            <template x-for="item in filters.projects" :key="item">
                                <option :value="item" x-text="item" class="bg-[#0a0f1a]"></option>
                            </template>
                        </select>

                        <select x-model="sortBy" class="h-11 rounded-xl border border-white/10 bg-black/30 px-3 text-xs font-bold text-white outline-none transition-all focus:border-sky-500/40 focus:shadow-lg focus:shadow-sky-500/10">
                            <option value="priority" class="bg-[#0a0f1a]">Priority first</option>
                            <option value="score" class="bg-[#0a0f1a]">AI score</option>
                            <option value="newest" class="bg-[#0a0f1a]">Newest</option>
                            <option value="evidence" class="bg-[#0a0f1a]">Evidence count</option>
                        </select>

                        <button
                            type="button"
                            @click="resetFilters()"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-white/10 bg-black/30 px-3 text-xs font-black text-slate-400 transition-all hover:border-white/20 hover:bg-white/5 hover:text-white"
                            :class="hasActiveFilters ? 'border-sky-500/30 text-sky-400 shadow-sm' : ''"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Clear Filters
                        </button>
                    </div>

                    <div class="mt-5 flex flex-wrap gap-2">
                        <template x-for="item in filters.severities" :key="`chip-${item}`">
                            <button
                                type="button"
                                @click="severity = item"
                                class="inline-flex h-8 items-center gap-2 rounded-full border px-3 text-[11px] font-black uppercase tracking-wide transition-all"
                                :class="severity === item ? severityClass(item) : 'border-white/10 bg-black/30 text-slate-500 hover:border-white/20 hover:text-white'"
                            >
                                <span x-text="label(item)"></span>
                                <span class="rounded-full bg-black/40 px-1.5 py-0.5 text-[10px]" x-text="severityCount(item)"></span>
                            </button>
                        </template>
                    </div>

                    <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-white/5 pt-4">
                        <p class="text-xs font-bold text-slate-500">
                            <span x-text="filteredRows.length" class="text-white"></span> visible from {{ $stats['total'] }} alerts
                        </p>
                        <p class="text-xs font-bold text-slate-600">
                            Latest alert: {{ $stats['latest_human'] ?? '-' }}
                        </p>
                    </div>
                </div>

                {{-- Empty State --}}
                <template x-if="filteredRows.length === 0">
                    <div class="flex flex-col items-center justify-center px-6 py-20 text-center">
                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full border border-white/10 bg-black/30">
                            <svg class="h-8 w-8 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-base font-black text-white">No alerts match this view</p>
                        <p class="mt-1 text-sm font-medium text-slate-500">Adjust the filters or wait for the next AI analysis run.</p>
                    </div>
                </template>

                {{-- Alerts List --}}
                <div class="divide-y divide-white/5">
                    <template x-for="alert in filteredRows" :key="alert.id">
                        <article
                            class="group relative transition-all duration-200"
                            :class="selectedId === alert.id ? 'bg-gradient-to-r from-sky-500/5 to-transparent' : 'hover:bg-white/[0.02]'"
                        >
                            <div class="absolute inset-y-0 left-0 w-1" :class="severityRail(alert.severity)"></div>

                            <div class="grid grid-cols-[1fr_auto] gap-4 px-6 py-5 lg:grid-cols-[minmax(0,1fr)_180px_140px]">
                                {{-- Main Content --}}
                                <div class="min-w-0">
                                    <button type="button" @click="select(alert.id)" class="w-full text-left">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-full border px-2.5 py-1 text-[10px] font-black uppercase tracking-wider" :class="severityClass(alert.severity)" x-text="label(alert.severity)"></span>
                                            <span class="rounded-full border px-2.5 py-1 text-[10px] font-black uppercase tracking-wider" :class="statusClass(alert.status)" x-text="label(alert.status)"></span>
                                            <span class="rounded-full border border-white/10 bg-white/5 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-slate-300" x-text="alert.type_label"></span>
                                            <span class="text-[10px] font-bold text-slate-600" x-text="alert.detected_time"></span>
                                        </div>

                                        <h3 class="mt-3 text-base font-black tracking-tight text-white group-hover:text-sky-400 transition-colors" x-text="alert.title"></h3>
                                        <p class="mt-1 text-sm font-medium text-slate-500 line-clamp-2" x-text="alert.summary"></p>

                                        <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs font-bold">
                                            <span class="inline-flex items-center gap-1.5 text-sky-400">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                </svg>
                                                <span x-text="alert.project"></span>
                                            </span>
                                            <span class="inline-flex items-center gap-1.5 text-slate-500" x-text="alert.client"></span>
                                            <span class="inline-flex items-center gap-1.5 text-amber-400">
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span x-text="`SLA ${alert.sla}`"></span>
                                            </span>
                                        </div>
                                    </button>
                                </div>

                                {{-- AI Score Column --}}
                                <div class="hidden lg:block">
                                    <div class="flex items-center justify-between">
                                        <p class="text-[11px] font-black uppercase tracking-wider text-slate-500">AI Risk Score</p>
                                        <p class="text-sm font-black" :class="scoreClass(alert.ai_score)" x-text="`${alert.ai_score}/100`"></p>
                                    </div>
                                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-white/5">
                                        <div class="h-full rounded-full transition-all duration-300" :class="scoreBar(alert.ai_score)" :style="`width: ${scoreWidth(alert.ai_score)}%`"></div>
                                    </div>
                                    <p class="mt-2 text-xs font-bold text-slate-500" x-text="`${alert.evidence_count} evidence · ${alert.recommendation_count} actions`"></p>
                                </div>

                                {{-- Action Column --}}
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        type="button"
                                        @click="select(alert.id)"
                                        class="inline-flex h-9 items-center justify-center rounded-xl border border-white/10 bg-white/5 px-4 text-xs font-black uppercase tracking-wide text-white transition-all hover:border-sky-500/30 hover:bg-sky-500/10 hover:text-sky-400"
                                    >
                                        Inspect
                                        <svg class="ml-1.5 h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </article>
                    </template>
                </div>
            </div>

            {{-- Details Panel --}}
            <aside class="min-w-0 rounded-2xl border border-white/5 bg-gradient-to-br from-[#0a0f1a] to-[#05080f] shadow-2xl shadow-black/30 2xl:sticky 2xl:top-6 2xl:self-start">
                <template x-if="selectedAlert">
                    <div>
                        {{-- Header --}}
                        <div class="border-b border-white/5 p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-500">Active Investigation</p>
                                    <h2 class="mt-2 text-xl font-black leading-tight text-white" x-text="selectedAlert.title"></h2>
                                </div>
                                <span class="shrink-0 rounded-full border px-3 py-1.5 text-xs font-black uppercase tracking-wide" :class="severityClass(selectedAlert.severity)" x-text="label(selectedAlert.severity)"></span>
                            </div>

                            <div class="mt-6 grid grid-cols-2 gap-3">
                                <div class="rounded-xl border border-white/10 bg-black/30 p-3.5">
                                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-500">Project</p>
                                    <p class="mt-1 text-sm font-black text-sky-400" x-text="selectedAlert.project"></p>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-3.5">
                                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-500">Status</p>
                                    <p class="mt-1 text-sm font-black" :class="selectedAlert.status === 'resolved' ? 'text-emerald-400' : 'text-red-400'" x-text="label(selectedAlert.status)"></p>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-3.5">
                                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-500">Type</p>
                                    <p class="mt-1 truncate text-sm font-black text-white" x-text="selectedAlert.type_label"></p>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-black/30 p-3.5">
                                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-500">SLA Target</p>
                                    <p class="mt-1 text-sm font-black text-amber-400" x-text="selectedAlert.sla"></p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6 p-6">
                            {{-- AI Confidence --}}
                            <div>
                                <div class="flex items-center justify-between">
                                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-500">AI Confidence Score</p>
                                    <p class="text-base font-black" :class="scoreClass(selectedAlert.ai_score)" x-text="`${selectedAlert.ai_score}/100`"></p>
                                </div>
                                <div class="mt-2 h-2 overflow-hidden rounded-full bg-white/5">
                                    <div class="h-full rounded-full transition-all duration-500" :class="scoreBar(selectedAlert.ai_score)" :style="`width: ${scoreWidth(selectedAlert.ai_score)}%`"></div>
                                </div>
                                <p class="mt-2 text-xs font-medium text-slate-500">Confidence level based on pattern matching and historical data.</p>
                            </div>

                            {{-- Summary --}}
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-wider text-slate-500">Summary</p>
                                <p class="mt-2 text-sm font-medium leading-relaxed text-slate-300" x-text="selectedAlert.summary"></p>
                            </div>

                            {{-- Priority Checklist --}}
                            <div class="rounded-xl border border-white/5 bg-black/20 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-[11px] font-black uppercase tracking-wider text-slate-500">Response Protocol</p>
                                        <p class="mt-0.5 text-xs font-medium text-slate-500" x-text="`Detected ${selectedAlert.detected_time} · ${selectedAlert.client}`"></p>
                                    </div>
                                    <span class="rounded-full border px-2.5 py-1 text-[10px] font-black uppercase" :class="scoreClass(selectedAlert.ai_score)" x-text="priorityLabel(selectedAlert)"></span>
                                </div>
                                <div class="mt-4 space-y-2">
                                    <template x-for="(item, idx) in responseChecklist(selectedAlert)" :key="idx">
                                        <div class="flex items-start gap-2.5 text-sm font-medium text-slate-400">
                                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                            <span x-text="item"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Evidence --}}
                            <div>
                                <div class="mb-3 flex items-center justify-between">
                                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-500">Evidence Collected</p>
                                    <span class="rounded-full bg-white/5 px-2 py-0.5 text-xs font-bold text-slate-400" x-text="selectedAlert.evidence_count"></span>
                                </div>
                                <div class="space-y-2">
                                    <template x-for="(item, index) in selectedAlert.evidence" :key="index">
                                        <div class="rounded-xl border border-white/5 bg-black/30 p-3.5">
                                            <p class="text-xs font-mono font-medium leading-relaxed text-slate-300 break-all" x-text="formatItem(item)"></p>
                                        </div>
                                    </template>
                                    <p x-show="selectedAlert.evidence.length === 0" class="rounded-xl border border-white/5 bg-black/30 p-3.5 text-center text-sm font-medium text-slate-500">No evidence attached.</p>
                                </div>
                            </div>

                            {{-- Recommendations --}}
                            <div>
                                <div class="mb-3 flex items-center justify-between">
                                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-500">Recommended Actions</p>
                                    <span class="rounded-full bg-white/5 px-2 py-0.5 text-xs font-bold text-slate-400" x-text="selectedAlert.recommendation_count"></span>
                                </div>
                                <div class="space-y-2">
                                    <template x-for="(item, index) in selectedAlert.recommendations" :key="index">
                                        <div class="rounded-xl border border-sky-500/20 bg-sky-500/5 p-3.5">
                                            <div class="flex gap-2">
                                                <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                                </svg>
                                                <p class="text-xs font-medium leading-relaxed text-sky-100" x-text="formatItem(item)"></p>
                                            </div>
                                        </div>
                                    </template>
                                    <p x-show="selectedAlert.recommendations.length === 0" class="rounded-xl border border-white/5 bg-black/30 p-3.5 text-center text-sm font-medium text-slate-500">No recommendations attached.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex flex-wrap items-center gap-3 border-t border-white/5 p-6">
                            <form method="POST" action="#" :action="selectedAlert.resolve_url" x-show="selectedAlert.status !== 'resolved'">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-5 text-xs font-black uppercase tracking-wide text-emerald-200 transition-all hover:bg-emerald-500/20 hover:shadow-lg hover:shadow-emerald-500/10">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Resolve Alert
                                </button>
                            </form>

                            <form method="POST" action="#" :action="selectedAlert.reopen_url" x-show="selectedAlert.status === 'resolved'">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-amber-500/30 bg-amber-500/10 px-5 text-xs font-black uppercase tracking-wide text-amber-200 transition-all hover:bg-amber-500/20 hover:shadow-lg hover:shadow-amber-500/10">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Reopen
                                </button>
                            </form>

                            <a href="{{ route('incidents.index') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/5 px-5 text-xs font-black uppercase tracking-wide text-slate-400 transition-all hover:border-white/20 hover:bg-white/10 hover:text-white">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                View Incidents
                            </a>
                        </div>
                    </div>
                </template>

                <template x-if="!selectedAlert">
                    <div class="flex flex-col items-center justify-center p-12 text-center">
                        <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-full border border-white/10 bg-black/30">
                            <svg class="h-10 w-10 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-base font-black text-white">No alert selected</p>
                        <p class="mt-2 text-sm font-medium text-slate-500">Select an alert from the list to inspect evidence and recommendations.</p>
                    </div>
                </template>
            </aside>
        </section>
    </div>

    <script>
        function alertsPage() {
            return {
                rows: @js($rows),
                filters: { severities: [], types: [], projects: [], ...@js($filters) },
                search: '',
                status: 'open',
                severity: 'all',
                type: 'all',
                project: 'all',
                sortBy: 'priority',
                selectedId: null,

                init() {
                    this.selectedId = this.filteredRows[0]?.id || this.rows[0]?.id || null;
                },

                get filteredRows() {
                    const q = this.search.toLowerCase().trim();
                    let filtered = this.rows.filter((row) => {
                        const matchesSearch = !q || String(row.search || '').toLowerCase().includes(q);
                        const matchesStatus = this.status === 'all' || row.status === this.status;
                        const matchesSeverity = this.severity === 'all' || row.severity === this.severity;
                        const matchesType = this.type === 'all' || row.type === this.type;
                        const matchesProject = this.project === 'all' || row.project === this.project;
                        return matchesSearch && matchesStatus && matchesSeverity && matchesType && matchesProject;
                    });
                    return this.sortAlerts(filtered);
                },

                get hasActiveFilters() {
                    return this.search.trim() !== '' || this.status !== 'open' || this.severity !== 'all' || this.type !== 'all' || this.project !== 'all' || this.sortBy !== 'priority';
                },

                get selectedAlert() {
                    const current = this.filteredRows.find((row) => row.id === this.selectedId);
                    return current || this.filteredRows[0] || null;
                },

                select(id) {
                    this.selectedId = id;
                },

                resetFilters() {
                    this.search = '';
                    this.status = 'open';
                    this.severity = 'all';
                    this.type = 'all';
                    this.project = 'all';
                    this.sortBy = 'priority';
                    this.selectedId = this.filteredRows[0]?.id || this.rows[0]?.id || null;
                },

                severityCount(severity) {
                    return this.rows.filter((row) => {
                        const matchesStatus = this.status === 'all' || row.status === this.status;
                        const matchesType = this.type === 'all' || row.type === this.type;
                        const matchesProject = this.project === 'all' || row.project === this.project;
                        return row.severity === severity && matchesStatus && matchesType && matchesProject;
                    }).length;
                },

                sortAlerts(alerts) {
                    return alerts.sort((a, b) => {
                        if (this.sortBy === 'score') return Number(b.ai_score || 0) - Number(a.ai_score || 0);
                        if (this.sortBy === 'newest') return Number(b.detected_timestamp || 0) - Number(a.detected_timestamp || 0);
                        if (this.sortBy === 'evidence') return Number(b.evidence_count || 0) - Number(a.evidence_count || 0);
                        const priority = this.priorityRank(b) - this.priorityRank(a);
                        return priority || Number(b.ai_score || 0) - Number(a.ai_score || 0);
                    });
                },

                priorityRank(alert) {
                    const severityRank = { critical: 5, high: 4, medium: 3, low: 2, info: 1 }[alert.severity] || 0;
                    return severityRank + (alert.status === 'open' ? 10 : 0);
                },

                priorityLabel(alert) {
                    if (alert.status === 'resolved') return 'Closed';
                    if (alert.severity === 'critical' || alert.ai_score >= 85) return 'Immediate';
                    if (alert.severity === 'high' || alert.ai_score >= 65) return 'Priority';
                    return 'Review';
                },

                responseChecklist(alert) {
                    if (alert.status === 'resolved') {
                        return ['Confirm the remediation is still deployed.', 'Check for repeated signals on the same project.', 'Keep the evidence attached for audit history.'];
                    }
                    if (['critical', 'high'].includes(alert.severity)) {
                        return ['Validate the evidence against recent project activity.', 'Contain the affected route, IP, user, or service if impact is confirmed.', 'Create or update an incident before resolving the alert.'];
                    }
                    return ['Review the evidence and project context.', 'Apply the recommended action when it matches the finding.', 'Resolve only after confirming the signal is no longer active.'];
                },

                label(value) {
                    return String(value || '').replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
                },

                formatItem(value) {
                    if (value === null || value === undefined) return '-';
                    if (typeof value === 'object') return JSON.stringify(value);
                    return String(value);
                },

                scoreWidth(score) {
                    return Math.max(6, Math.min(100, Number(score) || 0));
                },

                severityRail(severity) {
                    return { critical: 'bg-gradient-to-b from-red-500 to-red-600', high: 'bg-gradient-to-b from-orange-500 to-orange-600', medium: 'bg-gradient-to-b from-amber-500 to-amber-600', low: 'bg-gradient-to-b from-sky-500 to-sky-600', info: 'bg-gradient-to-b from-indigo-500 to-indigo-600' }[severity] || 'bg-slate-600';
                },

                severityClass(severity) {
                    return { critical: 'border-red-500/30 bg-red-500/10 text-red-400', high: 'border-orange-500/30 bg-orange-500/10 text-orange-400', medium: 'border-amber-500/30 bg-amber-500/10 text-amber-400', low: 'border-sky-500/30 bg-sky-500/10 text-sky-400', info: 'border-indigo-500/30 bg-indigo-500/10 text-indigo-400' }[severity] || 'border-white/10 bg-white/5 text-white';
                },

                statusClass(status) {
                    return { open: 'border-red-500/30 bg-red-500/10 text-red-400', resolved: 'border-emerald-500/30 bg-emerald-500/10 text-emerald-400' }[status] || 'border-white/10 bg-white/5 text-white';
                },

                scoreClass(score) {
                    if (score >= 85) return 'text-red-400';
                    if (score >= 65) return 'text-orange-400';
                    if (score >= 40) return 'text-amber-400';
                    return 'text-sky-400';
                },

                scoreBar(score) {
                    if (score >= 85) return 'bg-gradient-to-r from-red-500 to-rose-500';
                    if (score >= 65) return 'bg-gradient-to-r from-orange-500 to-amber-500';
                    if (score >= 40) return 'bg-gradient-to-r from-amber-500 to-yellow-500';
                    return 'bg-gradient-to-r from-sky-500 to-cyan-400';
                },
            };
        }
    </script>
</x-dashboard-layout>