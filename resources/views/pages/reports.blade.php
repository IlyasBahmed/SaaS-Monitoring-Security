<x-dashboard-layout>
    @php
        $projectRows = collect($projectRows ?? []);
        $reportPaginator = $reportRequests ?? collect();
        $reportRequests = $reportPaginator instanceof \Illuminate\Pagination\AbstractPaginator
            ? $reportPaginator->getCollection()
            : collect($reportPaginator);
        $reportTotal = method_exists($reportPaginator, 'total') ? $reportPaginator->total() : $reportRequests->count();
        $reportPaginationPages = method_exists($reportPaginator, 'lastPage') ? $reportPaginator->lastPage() : 1;
        $reportPaginationCurrent = method_exists($reportPaginator, 'currentPage') ? $reportPaginator->currentPage() : 1;
        $reportPaginationStart = max(1, $reportPaginationCurrent - 1);
        $reportPaginationEnd = min($reportPaginationPages, $reportPaginationCurrent + 1);

        if ($reportPaginationCurrent <= 2) {
            $reportPaginationEnd = min($reportPaginationPages, 3);
        }

        if ($reportPaginationCurrent >= $reportPaginationPages - 1) {
            $reportPaginationStart = max(1, $reportPaginationPages - 2);
        }

        $overviewCards = collect($overviewCards ?? []);
        $globalThreats = collect($globalThreats ?? []);
        $globalRecommendations = collect($globalRecommendations ?? []);
        $stats = $stats ?? [];
        $globalReport = $globalReport ?? null;
        $globalReportPeriods = $globalReportPeriods ?? [
            'last_7_days' => 'Last 7 days',
            'last_30_days' => 'Last 30 days',
            'last_90_days' => 'Last 90 days',
            'this_month' => 'This month',
            'last_quarter' => 'Last quarter',
        ];
        $reportTypeLabels = [
            'global_security_report' => 'Global security report',
            'executive_summary' => 'Executive summary',
            'soc_operations' => 'SOC operations report',
            'cloudflare_coverage' => 'Cloudflare coverage',
            'vulnerability_summary' => 'Vulnerability summary',
            'client_posture' => 'Client posture',
            'incident_summary' => 'Incident summary',
        ];
        $statusClass = static fn (string $status): string => [
            'pending' => 'border-amber-400/25 bg-amber-400/10 text-amber-200',
            'in_progress' => 'border-cyan-400/25 bg-cyan-400/10 text-cyan-200',
            'ready' => 'border-emerald-400/25 bg-emerald-400/10 text-emerald-200',
            'failed' => 'border-red-400/25 bg-red-400/10 text-red-200',
            'closed' => 'border-slate-700 bg-slate-800/70 text-slate-300',
        ][strtolower($status)] ?? 'border-slate-700 bg-slate-800/70 text-slate-300';
        $priorityClass = static fn (string $priority): string => [
            'Critical' => 'border-red-400/25 bg-red-400/10 text-red-200',
            'High' => 'border-amber-400/25 bg-amber-400/10 text-amber-200',
            'Medium' => 'border-cyan-400/25 bg-cyan-400/10 text-cyan-200',
            'Low' => 'border-emerald-400/25 bg-emerald-400/10 text-emerald-200',
        ][$priority] ?? 'border-slate-700 bg-slate-800/60 text-slate-300';
        $riskRowsAll = $projectRows->sortByDesc('risk_score')->values();
        $riskPerPage = 3;
        $riskPaginationPages = max(1, (int) ceil($riskRowsAll->count() / $riskPerPage));
        $riskPaginationCurrent = min(
            $riskPaginationPages,
            max(1, (int) request('risk_page', 1))
        );
        $riskRows = $riskRowsAll
            ->slice(($riskPaginationCurrent - 1) * $riskPerPage, $riskPerPage)
            ->values();
        $riskPaginationStart = max(1, $riskPaginationCurrent - 1);
        $riskPaginationEnd = min($riskPaginationPages, $riskPaginationCurrent + 1);
        $latestStatus = (string) ($globalReport?->status ?? 'pending');
        $latestTitle = $globalReport
            ? ($reportTypeLabels[$globalReport->type] ?? ucwords(str_replace('_', ' ', (string) $globalReport->type)))
            : 'No report generated';
        $averageScore = $projectRows->count() ? (int) round($projectRows->avg('score')) : 0;
        $coverage = (int) ($stats['cloudflare_coverage'] ?? 0);
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-cyan-400">Reporting</p>
                <h1 class="mt-2 text-3xl font-black text-white">Rapports SOC</h1>
                <p class="mt-1 max-w-2xl text-sm font-medium text-slate-500">
                    Generate executive PDF reports and monitor global security posture across projects, alerts, incidents, and Cloudflare coverage.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="#generate-pdf-report" class="inline-flex h-10 items-center rounded-lg border border-cyan-400/30 bg-cyan-400/10 px-4 text-xs font-black text-cyan-300 transition hover:bg-cyan-400/20">
                    Generate PDF
                </a>
                <span class="inline-flex h-10 items-center rounded-lg border border-slate-800 bg-[#07111f] px-4 text-xs font-bold text-slate-400">
                    {{ $reportTotal }} report jobs
                </span>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm font-bold text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-400/20 bg-red-400/10 px-4 py-3 text-sm font-bold text-red-300">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            @forelse($overviewCards as $card)
                <div class="rounded-xl border border-slate-800 bg-[#07111f] px-4 py-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-600">{{ $card['label'] ?? '-' }}</p>
                    <p class="mt-2 text-2xl font-black {{ $card['color'] ?? 'text-white' }}">{{ $card['value'] ?? 0 }}</p>
                    <p class="mt-1 text-xs font-semibold text-slate-500">{{ $card['detail'] ?? '' }}</p>
                </div>
            @empty
                <div class="rounded-xl border border-slate-800 bg-[#07111f] px-4 py-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-600">Projects</p>
                    <p class="mt-2 text-2xl font-black text-cyan-300">{{ $stats['projects'] ?? 0 }}</p>
                    <p class="mt-1 text-xs font-semibold text-slate-500">Monitored assets</p>
                </div>
            @endforelse
        </div>

        <div class="grid gap-6 xl:grid-cols-[0.86fr_1.14fr]">
            <form
                id="generate-pdf-report"
                method="POST"
                action="{{ route('reports.global.store') }}"
                class="rounded-xl border border-cyan-400/10 bg-[#07111f] p-5"
            >
                @csrf
                <input type="hidden" name="type" value="global_security_report">

                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-cyan-400">New Report</p>
                        <h2 class="mt-2 text-xl font-black text-white">Generate SOC PDF</h2>
                        <p class="mt-1 text-xs font-semibold text-slate-500">Includes posture score, risk projects, incidents, vulnerabilities, and recommendations.</p>
                    </div>
                    <span class="rounded-lg border border-slate-800 bg-slate-950/40 px-3 py-1 text-[10px] font-black uppercase tracking-wide text-slate-400">PDF</span>
                </div>

                <div class="mt-5 space-y-4">
                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">Period</span>
                        <select
                            name="period"
                            required
                            class="mt-2 h-11 w-full rounded-lg border border-slate-800 bg-slate-950/50 px-3 text-sm font-bold text-slate-200 outline-none transition focus:border-cyan-400/40"
                        >
                            @foreach($globalReportPeriods as $value => $label)
                                <option value="{{ $value }}" @selected($value === 'last_30_days')>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">SOC note</span>
                        <textarea
                            name="note"
                            rows="4"
                            maxlength="1000"
                            placeholder="Optional analyst note..."
                            class="mt-2 w-full resize-none rounded-lg border border-slate-800 bg-slate-950/50 px-3 py-3 text-sm font-semibold text-slate-200 outline-none transition placeholder:text-slate-600 focus:border-cyan-400/40"
                        ></textarea>
                    </label>

                    <button
                        type="submit"
                        class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-cyan-400/30 bg-cyan-400/10 px-4 text-sm font-black text-cyan-300 transition hover:bg-cyan-400/20"
                    >
                        Generate PDF Report
                    </button>
                </div>
            </form>

            <div class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-500">Latest Report</p>
                        <h2 class="mt-2 text-xl font-black text-white">{{ $latestTitle }}</h2>
                        <p class="mt-1 text-sm font-semibold text-slate-500">
                            {{ $globalReport?->requested_at?->diffForHumans() ?? 'Generate a report to start the history.' }}
                        </p>
                    </div>
                    <span class="inline-flex w-fit rounded-lg border px-3 py-1.5 text-[10px] font-black uppercase tracking-wide {{ $statusClass($latestStatus) }}">
                        {{ $globalReport ? str_replace('_', ' ', (string) $globalReport->status) : 'not generated' }}
                    </span>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-lg border border-slate-800 bg-slate-950/30 px-4 py-3">
                        <p class="text-[10px] font-black uppercase tracking-wide text-slate-600">Avg Score</p>
                        <p class="mt-2 text-2xl font-black {{ $averageScore >= 85 ? 'text-emerald-300' : ($averageScore >= 65 ? 'text-amber-300' : 'text-red-300') }}">{{ $averageScore }}/100</p>
                    </div>
                    <div class="rounded-lg border border-slate-800 bg-slate-950/30 px-4 py-3">
                        <p class="text-[10px] font-black uppercase tracking-wide text-slate-600">Cloudflare</p>
                        <p class="mt-2 text-2xl font-black text-blue-300">{{ $coverage }}%</p>
                    </div>
                    <div class="rounded-lg border border-slate-800 bg-slate-950/30 px-4 py-3">
                        <p class="text-[10px] font-black uppercase tracking-wide text-slate-600">Critical</p>
                        <p class="mt-2 text-2xl font-black text-red-300">{{ $stats['critical_signals'] ?? 0 }}</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-2 sm:grid-cols-2">
                    @foreach(['Executive overview', 'Global metrics', 'Critical signals', 'Cloudflare coverage', 'Risk projects', 'Recommendations'] as $section)
                        <div class="flex items-center gap-2 rounded-lg border border-slate-800 bg-slate-950/20 px-3 py-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-cyan-300"></span>
                            <span class="text-xs font-bold text-slate-300">{{ $section }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <div class="overflow-hidden rounded-xl border border-slate-800 bg-[#07111f]">
                <div class="flex flex-col gap-3 border-b border-slate-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-500">Risk Summary</p>
                        <h2 class="mt-1 text-lg font-black text-white">Top risky projects</h2>
                    </div>
                    <span class="text-xs font-bold text-slate-500">{{ $riskRowsAll->count() }} total</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[680px]">
                        <thead class="border-b border-slate-800 bg-white/[0.02]">
                            <tr class="text-left text-[10px] uppercase tracking-[0.18em] text-slate-500">
                                <th class="px-5 py-3">Project</th>
                                <th class="px-5 py-3">Client</th>
                                <th class="px-5 py-3">Score</th>
                                <th class="px-5 py-3">Signals</th>
                                <th class="px-5 py-3">Risk</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse($riskRows as $project)
                                @php
                                    $risk = $project['risk'] ?? $project['risk_label'] ?? 'Medium';
                                    $riskTone = [
                                        'Critical' => 'border-red-400/25 bg-red-400/10 text-red-300',
                                        'High' => 'border-orange-400/25 bg-orange-400/10 text-orange-300',
                                        'Medium' => 'border-amber-400/25 bg-amber-400/10 text-amber-300',
                                        'Low' => 'border-emerald-400/25 bg-emerald-400/10 text-emerald-300',
                                    ][$risk] ?? 'border-slate-700 bg-slate-800 text-slate-300';
                                    $score = (int) ($project['score'] ?? 0);
                                @endphp
                                <tr class="transition hover:bg-white/[0.025]">
                                    <td class="px-5 py-4">
                                        <p class="text-sm font-black text-white">{{ $project['domain'] ?? $project['name'] ?? '-' }}</p>
                                        <p class="mt-0.5 text-xs font-semibold text-slate-600">{{ $project['name'] ?? '' }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-sm font-semibold text-slate-400">{{ $project['client'] ?? '-' }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="w-12 text-sm font-black {{ $score >= 85 ? 'text-emerald-300' : ($score >= 65 ? 'text-amber-300' : 'text-red-300') }}">{{ $score }}</span>
                                            <div class="h-1.5 w-28 overflow-hidden rounded-full bg-slate-800">
                                                <div class="h-full rounded-full bg-cyan-400" style="width: {{ max(0, min(100, $score)) }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-sm font-black text-slate-300">{{ (int) ($project['alerts'] ?? 0) + (int) ($project['incidents'] ?? 0) }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-lg border px-2.5 py-1 text-[10px] font-black uppercase tracking-wide {{ $riskTone }}">{{ $risk }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-12 text-center text-sm font-semibold text-slate-500">No project data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($riskRowsAll->count() > 0)
                    <div class="flex flex-col gap-3 border-t border-slate-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs font-semibold text-slate-500">
                            Showing
                            <span class="font-black text-slate-300">{{ (($riskPaginationCurrent - 1) * $riskPerPage) + 1 }}</span>
                            to
                            <span class="font-black text-slate-300">{{ min($riskRowsAll->count(), $riskPaginationCurrent * $riskPerPage) }}</span>
                            of
                            <span class="font-black text-slate-300">{{ $riskRowsAll->count() }}</span>
                            projects
                        </p>

                        @if($riskPaginationPages > 1)
                            <div class="flex flex-wrap items-center gap-2">
                                @if($riskPaginationCurrent === 1)
                                    <button type="button" disabled class="inline-flex h-9 items-center rounded-lg border border-slate-800 px-3 text-xs font-bold text-slate-700">Previous</button>
                                @else
                                    <a href="{{ request()->fullUrlWithQuery(['risk_page' => $riskPaginationCurrent - 1]) }}" class="inline-flex h-9 items-center rounded-lg border border-slate-800 px-3 text-xs font-bold text-slate-400 transition hover:border-cyan-400/30 hover:text-cyan-300">Previous</a>
                                @endif

                                @for($page = $riskPaginationStart; $page <= $riskPaginationEnd; $page++)
                                    @if($page === $riskPaginationCurrent)
                                        <button type="button" disabled class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-cyan-400/30 bg-cyan-400/10 text-xs font-black text-cyan-300">{{ $page }}</button>
                                    @else
                                        <a href="{{ request()->fullUrlWithQuery(['risk_page' => $page]) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-800 text-xs font-bold text-slate-400 transition hover:border-cyan-400/30 hover:text-cyan-300">{{ $page }}</a>
                                    @endif
                                @endfor

                                @if($riskPaginationCurrent < $riskPaginationPages)
                                    <a href="{{ request()->fullUrlWithQuery(['risk_page' => $riskPaginationCurrent + 1]) }}" class="inline-flex h-9 items-center rounded-lg border border-slate-800 px-3 text-xs font-bold text-slate-400 transition hover:border-cyan-400/30 hover:text-cyan-300">Next</a>
                                @else
                                    <button type="button" disabled class="inline-flex h-9 items-center rounded-lg border border-slate-800 px-3 text-xs font-bold text-slate-700">Next</button>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                <div class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-500">Threat Focus</p>
                            <h2 class="mt-1 text-lg font-black text-white">Global signals</h2>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse($globalThreats as $threat)
                            <div class="flex items-center justify-between rounded-lg border border-slate-800 bg-slate-950/25 px-4 py-3">
                                <span class="text-sm font-bold text-slate-300">{{ $threat['name'] ?? '-' }}</span>
                                <span class="text-sm font-black text-cyan-300">{{ $threat['count'] ?? 0 }}</span>
                            </div>
                        @empty
                            <p class="rounded-lg border border-dashed border-slate-800 px-4 py-6 text-center text-sm font-semibold text-slate-500">No signal data yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-800 bg-[#07111f]">
            <div class="flex flex-col gap-3 border-b border-slate-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-500">Report Queue</p>
                    <h2 class="mt-1 text-lg font-black text-white">Recent report jobs</h2>
                </div>
                <span class="text-xs font-bold text-slate-500">{{ $reportTotal }} total</span>
            </div>

            <div class="divide-y divide-slate-800">
                @forelse($reportRequests as $request)
                    @php
                        $requestStatus = (string) ($request->status ?? 'pending');
                        $requestTitle = $reportTypeLabels[$request->type] ?? ucwords(str_replace('_', ' ', (string) $request->type));
                    @endphp
                    <div class="grid gap-3 px-5 py-4 md:grid-cols-[1fr_160px_150px_120px] md:items-center">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-black text-slate-100">{{ $requestTitle }}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                {{ str_replace('_', ' ', (string) $request->period) }} / {{ $request->client?->company_name ?? 'Global' }}
                            </p>
                        </div>
                        <p class="text-xs font-bold text-slate-500">{{ $request->user?->name ?? 'System' }}</p>
                        <p class="text-xs font-bold text-slate-500">{{ $request->requested_at?->diffForHumans() ?? $request->created_at?->diffForHumans() ?? '-' }}</p>
                        <span class="inline-flex w-fit rounded-lg border px-2.5 py-1 text-[10px] font-black uppercase tracking-wide {{ $statusClass($requestStatus) }}">
                            {{ str_replace('_', ' ', $requestStatus) }}
                        </span>
                    </div>
                @empty
                    <div class="px-5 py-12 text-center text-sm font-semibold text-slate-500">No report jobs have been created yet.</div>
                @endforelse
            </div>

            @if(method_exists($reportPaginator, 'total') && $reportPaginator->total() > 0)
                <div class="flex flex-col gap-3 border-t border-slate-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs font-semibold text-slate-500">
                        Showing
                        <span class="font-black text-slate-300">{{ $reportPaginator->firstItem() }}</span>
                        to
                        <span class="font-black text-slate-300">{{ $reportPaginator->lastItem() }}</span>
                        of
                        <span class="font-black text-slate-300">{{ $reportPaginator->total() }}</span>
                        jobs
                    </p>

                    @if($reportPaginator->hasPages())
                        <div class="flex flex-wrap items-center gap-2">
                            @if($reportPaginator->onFirstPage())
                                <button type="button" disabled class="inline-flex h-9 items-center rounded-lg border border-slate-800 px-3 text-xs font-bold text-slate-700">Previous</button>
                            @else
                                <a href="{{ $reportPaginator->previousPageUrl() }}" class="inline-flex h-9 items-center rounded-lg border border-slate-800 px-3 text-xs font-bold text-slate-400 transition hover:border-cyan-400/30 hover:text-cyan-300">Previous</a>
                            @endif

                            @if($reportPaginationStart > 1)
                                <a href="{{ $reportPaginator->url(1) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-800 text-xs font-bold text-slate-400 transition hover:border-cyan-400/30 hover:text-cyan-300">1</a>
                                @if($reportPaginationStart > 2)
                                    <span class="px-1 text-xs font-bold text-slate-600">...</span>
                                @endif
                            @endif

                            @for($page = $reportPaginationStart; $page <= $reportPaginationEnd; $page++)
                                @if($page === $reportPaginationCurrent)
                                    <button type="button" disabled class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-cyan-400/30 bg-cyan-400/10 text-xs font-black text-cyan-300">{{ $page }}</button>
                                @else
                                    <a href="{{ $reportPaginator->url($page) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-800 text-xs font-bold text-slate-400 transition hover:border-cyan-400/30 hover:text-cyan-300">{{ $page }}</a>
                                @endif
                            @endfor

                            @if($reportPaginationEnd < $reportPaginationPages)
                                @if($reportPaginationEnd < $reportPaginationPages - 1)
                                    <span class="px-1 text-xs font-bold text-slate-600">...</span>
                                @endif
                                <a href="{{ $reportPaginator->url($reportPaginationPages) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-800 text-xs font-bold text-slate-400 transition hover:border-cyan-400/30 hover:text-cyan-300">{{ $reportPaginationPages }}</a>
                            @endif

                            @if($reportPaginator->hasMorePages())
                                <a href="{{ $reportPaginator->nextPageUrl() }}" class="inline-flex h-9 items-center rounded-lg border border-slate-800 px-3 text-xs font-bold text-slate-400 transition hover:border-cyan-400/30 hover:text-cyan-300">Next</a>
                            @else
                                <button type="button" disabled class="inline-flex h-9 items-center rounded-lg border border-slate-800 px-3 text-xs font-bold text-slate-700">Next</button>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-dashboard-layout>
