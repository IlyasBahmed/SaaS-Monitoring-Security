<x-dashboard-layout>
    @php
        $projectTypeClasses = [
            'WordPress' => 'bg-blue-400/10 text-blue-300 border-blue-400/20',
            'Node.js' => 'bg-emerald-400/10 text-emerald-300 border-emerald-400/20',
            'Laravel' => 'bg-red-400/10 text-red-300 border-red-400/20',
        ];

        $resolveProjectType = function ($project) use ($projectTypeClasses) {
            $typeLabel = \App\Models\Projects::normalizeProjectType($project->stack ?? '');

            return [
                'label' => $typeLabel,
                'class' => $projectTypeClasses[$typeLabel] ?? 'bg-slate-400/10 text-slate-300 border-slate-400/20',
            ];
        };

        $projectPaginationPages = method_exists($projects, 'lastPage') ? $projects->lastPage() : 1;
        $projectPaginationCurrent = method_exists($projects, 'currentPage') ? $projects->currentPage() : 1;
        $projectPaginationStart = max(1, $projectPaginationCurrent - 1);
        $projectPaginationEnd = min($projectPaginationPages, $projectPaginationCurrent + 1);

        if ($projectPaginationCurrent <= 2) {
            $projectPaginationEnd = min($projectPaginationPages, 3);
        }

        if ($projectPaginationCurrent >= $projectPaginationPages - 1) {
            $projectPaginationStart = max(1, $projectPaginationPages - 2);
        }

        $currentRole = strtolower(trim((string) (Auth::user()?->role ?? '')));
        $canManageProjects = ! in_array($currentRole, ['soc analyst'], true);
        $canScanProjects = in_array($currentRole, ['super admin', 'admin', 'staff', 'soc analyst'], true);

        $projectFilterItems = $projects
            ->map(function ($project) use ($resolveProjectType) {
                $type = $resolveProjectType($project)['label'];
                $client = $project->client->company_name ?? '-';

                $agentOnline = $project->agents->contains(function ($agent) {
                    return strtolower($agent->pivot->status ?? '') === 'online';
                });

                $status = $agentOnline
                    ? 'active'
                    : (strtolower($project->status ?? '') === 'warning' ? 'warning' : 'offline');

                return [
                    'id' => $project->id,
                    'status' => $status,
                    'type' => $type,
                    'client' => $client,
                    'search' => strtolower(trim(implode(' ', [
                        $project->name,
                        $project->domain,
                        $project->ip_address,
                        $project->stack,
                        $client,
                        $type,
                        $status,
                    ]))),
                ];
            })
            ->values()
            ->all();
    @endphp

    <div
        x-data="{
            search: '',
            status: 'all',
            scanningProject: null,
            projects: @js($projectFilterItems),
            startScan(id) {
                this.scanningProject = id;
            },
            matchesProject(project) {
                if (!project) return false;

                const query = this.search.toLowerCase().trim();
                const matchesSearch = !query || project.search.includes(query);
                const matchesStatus = this.status === 'all' || project.status === this.status;

                return matchesSearch && matchesStatus;
            },
            get visibleProjects() {
                return this.projects.filter((project) => this.matchesProject(project)).length;
            },
            clearFilters() {
                this.search = '';
                this.status = 'all';
            },
            get hasFilters() {
                return this.search.trim() !== '' || this.status !== 'all';
            }
        }"
        class="space-y-6"
    >
        {{-- HEADER --}}
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[10px] tracking-[0.2em] text-cyan-400 uppercase">Protected Assets</p>
                <h1 class="text-2xl font-bold text-white mt-1">Projects</h1>
                <p class="text-xs text-slate-500 mt-1">
                    {{ $projects->count() }} projects /
                    {{ collect($projectFilterItems)->where('status', 'active')->count() }} online
                </p>
            </div>

            <div class="flex gap-2">
                @if ($canManageProjects)
                    <a href="{{ route('projects.export') }}" class="inline-flex h-9 items-center px-4 text-xs border border-slate-700 text-slate-400 rounded-lg hover:text-cyan-300 hover:border-cyan-400/30 transition">
                        Export
                    </a>

                    <a href="{{ route('projects.create') }}" class="h-9 px-4 inline-flex items-center text-xs border border-cyan-400/30 bg-cyan-400/10 text-cyan-300 rounded-lg hover:bg-cyan-400/20 transition">
                        + Add Project
                    </a>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm font-bold text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-xl border border-red-400/20 bg-red-400/10 px-4 py-3 text-sm font-bold text-red-300">
                {{ session('error') }}
            </div>
        @endif

        @if (session('scan_errors'))
            <div class="rounded-xl border border-amber-400/20 bg-amber-400/10 px-4 py-3 text-sm text-amber-200">
                <p class="font-bold">Some scan checks failed:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ((array) session('scan_errors') as $scanError)
                        <li>{{ $scanError }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- FILTERS --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <input
                    x-model.debounce.200ms="search"
                    type="text"
                    placeholder="Search projects..."
                    class="h-9 w-64 rounded-lg bg-[#0a1628] border border-slate-800 px-3 text-xs text-slate-300 placeholder-slate-600 outline-none focus:border-cyan-400/40"
                >

                <div class="flex items-center gap-2 text-xs">
                    <button @click="status='all'" :class="status==='all' ? 'text-white' : 'text-slate-500'" class="font-semibold">
                        All
                    </button>

                    <button @click="status='active'" :class="status==='active' ? 'text-emerald-400' : 'text-slate-500'" class="font-semibold">
                        Online
                    </button>

                    <button @click="status='warning'" :class="status==='warning' ? 'text-yellow-400' : 'text-slate-500'" class="font-semibold">
                        Warning
                    </button>

                    <button @click="status='offline'" :class="status==='offline' ? 'text-red-400' : 'text-slate-500'" class="font-semibold">
                        Offline
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500">
                    <span x-text="visibleProjects"></span> results
                </span>

                <button
                    x-show="hasFilters"
                    x-cloak
                    @click="clearFilters()"
                    class="text-xs text-slate-500 hover:text-cyan-300">
                    Reset
                </button>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="rounded-xl border border-slate-800 bg-[#07111f] overflow-hidden">
            <table class="w-full">
                <thead class="bg-white/[0.02] text-[10px] uppercase text-slate-500 tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Domain</th>
                        <th class="px-4 py-3 text-left">Client</th>
                        <th class="px-4 py-3 text-left">Type</th>
                        <th class="px-4 py-3 text-left">Score</th>
                        <th class="px-4 py-3 text-left">SSL</th>
                        <th class="px-4 py-3 text-left">Cloudflare</th>
                        <th class="px-4 py-3 text-left">Agent</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-800">
                    @forelse($projects as $project)
                        @php
                            $agentOnline = $project->agents->contains(function ($agent) {
                                return strtolower($agent->pivot->status ?? '') === 'online';
                            });

                            $warning = strtolower($project->status ?? '') === 'warning';
                            $cloudflare = (bool) ($project->cloudflare_enabled ?? false) || filled($project->cloudflare_zone_id ?? null);
                            $cloudflareSettings = is_array($project->cloudflare_settings ?? null) ? $project->cloudflare_settings : [];
                            $sslMode = $cloudflareSettings['ssl_mode'] ?? null;
                            $sslLabel = $cloudflare
                                ? match ($sslMode) {
                                    'full_strict' => 'Full Strict',
                                    'full' => 'Full',
                                    'flexible' => 'Flexible',
                                    'off' => 'Off',
                                    default => 'Not synced',
                                }
                                : 'Not linked';
                            $sslState = match ($sslMode) {
                                'full_strict', 'full' => 'Valid',
                                'flexible' => 'Weak',
                                'off' => 'Invalid',
                                default => $cloudflare ? 'Unknown' : 'Missing',
                            };
                            $sslClass = match ($sslState) {
                                'Valid' => 'border-emerald-400/25 bg-emerald-400/10 text-emerald-300',
                                'Weak' => 'border-amber-400/25 bg-amber-400/10 text-amber-300',
                                default => 'border-red-400/25 bg-red-400/10 text-red-300',
                            };
                            $score = (int) ($project->security_score ?? 0);
                            $scoreLabel = $project->security_score_label ?? ($score >= 85 ? 'Healthy' : ($score >= 65 ? 'Review' : 'Risk'));
                            $scoreSource = ($project->security_score_source ?? 'live_findings') === 'health_report'
                                ? 'Health report'
                                : 'Live findings';
                            $scoreText = $score >= 85 ? 'text-emerald-300' : ($score >= 65 ? 'text-amber-300' : 'text-red-300');
                            $scoreRing = $score >= 85 ? 'ring-emerald-400/20' : ($score >= 65 ? 'ring-amber-400/20' : 'ring-red-400/20');

                            $typeMeta = $resolveProjectType($project);
                            $typeLabel = $typeMeta['label'];
                            $typeClass = $typeMeta['class'];
                        @endphp

                        <tr
                            x-show="matchesProject(projects.find((project) => project.id === {{ $project->id }}))"
                            class="hover:bg-white/[0.03] transition"
                        >
                            {{-- DOMAIN --}}
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 flex items-center justify-center rounded-lg bg-cyan-400/10 border border-cyan-400/20 text-cyan-300">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                            <circle cx="12" cy="12" r="9"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18M12 3c2.2 2.4 3.3 5.4 3.3 9s-1.1 6.6-3.3 9M12 3c-2.2 2.4-3.3 5.4-3.3 9s1.1 6.6 3.3 9"/>
                                        </svg>
                                    </div>

                                    <div>
                                        <p class="text-sm font-semibold text-cyan-300">
                                            {{ $project->domain ?? $project->name }}
                                        </p>
                                        <p class="text-xs text-slate-600">
                                            {{ $project->ip_address ?? 'No IP' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- CLIENT --}}
                            <td class="px-4 py-4 text-sm text-slate-400">
                                {{ $project->client->company_name ?? '-' }}
                            </td>

                            {{-- TYPE --}}
                            <td class="px-4 py-4">
                                <span class="inline-flex px-2.5 py-1 rounded-md border text-xs font-bold {{ $typeClass }}">
                                    {{ $typeLabel }}
                                </span>
                            </td>

                            {{-- SCORE --}}
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="relative h-14 w-14 shrink-0 rounded-full bg-[#020617] p-1 ring-1 {{ $scoreRing }}">
                                        <svg class="h-full w-full -rotate-90" viewBox="0 0 40 40" aria-hidden="true">
                                            <circle class="stroke-slate-800" cx="20" cy="20" r="16" fill="none" stroke-width="4" />
                                            <circle
                                                class="stroke-current {{ $scoreText }}"
                                                cx="20"
                                                cy="20"
                                                r="16"
                                                fill="none"
                                                stroke-width="4"
                                                stroke-linecap="round"
                                                pathLength="100"
                                                stroke-dasharray="100"
                                                stroke-dashoffset="{{ 100 - $score }}"
                                            />
                                        </svg>

                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <span class="text-sm font-black {{ $scoreText }}">{{ $score }}</span>
                                        </div>
                                    </div>

                                    <div>
                                        <p class="text-xs font-black {{ $scoreText }}">{{ $scoreLabel }}</p>
                                        <p class="mt-0.5 text-[10px] font-medium text-slate-600">{{ $scoreSource }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- SSL --}}
                            <td class="px-4 py-4">
                                <span class="inline-flex flex-col rounded-lg border px-2.5 py-1 {{ $sslClass }}">
                                    <span class="text-xs font-black">{{ $sslState }}</span>
                                    <span class="text-[10px] font-semibold opacity-75">{{ $sslLabel }}</span>
                                </span>
                            </td>

                            {{-- CLOUDFLARE --}}
                            <td class="px-4 py-4">
                                <span class="text-xs font-bold {{ $cloudflare ? 'text-emerald-400' : 'text-slate-600' }}">
                                    {{ $cloudflare ? 'Active' : 'Not linked' }}
                                </span>
                            </td>

                            {{-- AGENT --}}
                            <td class="px-4 py-4">
                                @if($agentOnline)
                                    <span class="flex items-center gap-2 text-xs font-bold text-emerald-400">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,0.9)]"></span>
                                        Online
                                    </span>
                                @else
                                    <span class="flex items-center gap-2 text-xs font-bold text-red-400">
                                        <span class="w-2 h-2 rounded-full bg-red-400"></span>
                                        Offline
                                    </span>
                                @endif
                            </td>

                            {{-- ACTIONS --}}
                            <td class="px-4 py-4">
                                <div class="flex justify-start gap-2">
                                    <a href="{{ route('projects.show', $project) }}"
                                       title="View project"
                                       aria-label="View project"
                                       class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-cyan-400/20 bg-cyan-400/10 text-cyan-300 hover:bg-cyan-400/20 transition">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z"/>
                                            <circle cx="12" cy="12" r="2.8"/>
                                        </svg>
                                    </a>

                                    @if ($canScanProjects)
                                        <form method="POST" action="{{ route('projects.vulnerability.scan', $project) }}" @submit="startScan({{ $project->id }})">
                                            @csrf
                                            <button type="submit"
                                                    title="Run scan"
                                                    aria-label="Run scan"
                                                    :disabled="scanningProject === {{ $project->id }}"
                                                    class="relative inline-flex h-9 w-9 items-center justify-center overflow-hidden rounded-lg border border-amber-400/20 text-amber-300 transition hover:bg-amber-400/10 disabled:cursor-wait disabled:border-cyan-400/30 disabled:bg-cyan-400/10 disabled:text-cyan-200">
                                                <span x-show="scanningProject === {{ $project->id }}" x-cloak class="scan-sweep pointer-events-none absolute inset-y-0 left-0 w-1/2 bg-gradient-to-r from-transparent via-cyan-300/25 to-transparent"></span>
                                                <svg x-show="scanningProject !== {{ $project->id }}" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 2 4 14h7l-1 8 10-13h-7l1-7z"/>
                                                </svg>
                                                <svg x-show="scanningProject === {{ $project->id }}" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                                    <circle cx="12" cy="12" r="8" stroke-dasharray="10 8"/>
                                                    <path stroke-linecap="round" d="M12 12l5-4"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif

                                
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-sm text-slate-500">
                                No projects found. Add your first protected asset to fill this table.
                            </td>
                        </tr>
                    @endforelse

                    @if($projects->isNotEmpty())
                        <tr x-show="visibleProjects === 0" x-cloak>
                            <td colspan="8" class="px-4 py-10 text-center text-sm text-slate-500">
                                No projects match your filters.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>

            @if(method_exists($projects, 'total') && $projects->total() > 0)
                <div class="flex flex-col gap-3 border-t border-slate-800 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs font-semibold text-slate-500">
                        Showing
                        <span class="font-black text-slate-300">{{ $projects->firstItem() }}</span>
                        to
                        <span class="font-black text-slate-300">{{ $projects->lastItem() }}</span>
                        of
                        <span class="font-black text-slate-300">{{ $projects->total() }}</span>
                        projects
                    </p>

                    @if($projects->hasPages())
                        <div class="flex flex-wrap items-center gap-2">
                            @if($projects->onFirstPage())
                                <button
                                    type="button"
                                    disabled
                                    class="inline-flex h-9 items-center rounded-lg border border-slate-800 px-3 text-xs font-bold text-slate-700"
                                >
                                    Previous
                                </button>
                            @else
                                <a
                                    href="{{ $projects->previousPageUrl() }}"
                                    class="inline-flex h-9 items-center rounded-lg border border-slate-800 px-3 text-xs font-bold text-slate-400 transition hover:border-cyan-400/30 hover:text-cyan-300"
                                >
                                    Previous
                                </a>
                            @endif

                            @if($projectPaginationStart > 1)
                                <a href="{{ $projects->url(1) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-800 text-xs font-bold text-slate-400 transition hover:border-cyan-400/30 hover:text-cyan-300">1</a>
                                @if($projectPaginationStart > 2)
                                    <span class="px-1 text-xs font-bold text-slate-600">...</span>
                                @endif
                            @endif

                            @for($page = $projectPaginationStart; $page <= $projectPaginationEnd; $page++)
                                @if($page === $projectPaginationCurrent)
                                    <button
                                        type="button"
                                        disabled
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-cyan-400/30 bg-cyan-400/10 text-xs font-black text-cyan-300"
                                    >
                                        {{ $page }}
                                    </button>
                                @else
                                    <a href="{{ $projects->url($page) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-800 text-xs font-bold text-slate-400 transition hover:border-cyan-400/30 hover:text-cyan-300">{{ $page }}</a>
                                @endif
                            @endfor

                            @if($projectPaginationEnd < $projectPaginationPages)
                                @if($projectPaginationEnd < $projectPaginationPages - 1)
                                    <span class="px-1 text-xs font-bold text-slate-600">...</span>
                                @endif
                                <a href="{{ $projects->url($projectPaginationPages) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-800 text-xs font-bold text-slate-400 transition hover:border-cyan-400/30 hover:text-cyan-300">{{ $projectPaginationPages }}</a>
                            @endif

                            @if($projects->hasMorePages())
                                <a
                                    href="{{ $projects->nextPageUrl() }}"
                                    class="inline-flex h-9 items-center rounded-lg border border-slate-800 px-3 text-xs font-bold text-slate-400 transition hover:border-cyan-400/30 hover:text-cyan-300"
                                >
                                    Next
                                </a>
                            @else
                                <button
                                    type="button"
                                    disabled
                                    class="inline-flex h-9 items-center rounded-lg border border-slate-800 px-3 text-xs font-bold text-slate-700"
                                >
                                    Next
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-dashboard-layout>
