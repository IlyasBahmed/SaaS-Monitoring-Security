<x-dashboard-layout>
    @php
        $resolveProjectType = function ($project) {
            $typeSource = strtolower(trim(($project->name ?? '').' '.($project->domain ?? '').' '.($project->stack ?? '')));

            if (str_contains($typeSource, 'api')) {
                return ['label' => 'API', 'class' => 'bg-violet-400/10 text-violet-300 border-violet-400/20'];
            }

            if (str_contains($typeSource, 'admin') || str_contains($typeSource, 'console') || str_contains($typeSource, 'panel')) {
                return ['label' => 'Admin', 'class' => 'bg-purple-400/10 text-purple-300 border-purple-400/20'];
            }

            if (str_contains($typeSource, 'portal')) {
                return ['label' => 'Portal', 'class' => 'bg-cyan-400/10 text-cyan-300 border-cyan-400/20'];
            }

            if (str_contains($typeSource, 'wordpress')) {
                return ['label' => 'WordPress', 'class' => 'bg-blue-400/10 text-blue-300 border-blue-400/20'];
            }

            if (str_contains($typeSource, 'server') || str_contains($typeSource, 'nginx') || str_contains($typeSource, 'apache')) {
                return ['label' => 'Server', 'class' => 'bg-sky-400/10 text-sky-300 border-sky-400/20'];
            }

            if (str_contains($typeSource, 'website') || str_contains($typeSource, 'www.')) {
                return ['label' => 'Website', 'class' => 'bg-emerald-400/10 text-emerald-300 border-emerald-400/20'];
            }

            return ['label' => 'Web App', 'class' => 'bg-cyan-400/10 text-cyan-300 border-cyan-400/20'];
        };

        $projectFilterItems = $projects
            ->map(function ($project) use ($resolveProjectType) {
                $type = $resolveProjectType($project)['label'];
                $client = $project->client->company_name ?? '-';
                $status = strtolower($project->status ?? 'offline');
                $status = in_array($status, ['active', 'warning'], true) ? $status : 'offline';

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
                        $project->status,
                    ]))),
                ];
            })
            ->values()
            ->all();

        $typeOptions = collect($projectFilterItems)->pluck('type')->unique()->sort()->values();
        $clientOptions = collect($projectFilterItems)->pluck('client')->filter(fn ($client) => $client !== '-')->unique()->sort()->values();
    @endphp

    <div
        x-data="{
            search: '',
            status: 'all',
            type: 'all',
            client: 'all',
            projects: @js($projectFilterItems),
            matchesProject(project) {
                if (!project) {
                    return false;
                }

                const query = this.search.toLowerCase().trim();
                const matchesSearch = !query || project.search.includes(query);
                const matchesStatus = this.status === 'all' || project.status === this.status;
                const matchesType = this.type === 'all' || project.type === this.type;
                const matchesClient = this.client === 'all' || project.client === this.client;

                return matchesSearch && matchesStatus && matchesType && matchesClient;
            },
            get visibleProjects() {
                return this.projects.filter((project) => this.matchesProject(project)).length;
            },
            countByStatus(status) {
                return status === 'all'
                    ? this.projects.length
                    : this.projects.filter((project) => project.status === status).length;
            },
            clearFilters() {
                this.search = '';
                this.status = 'all';
                this.type = 'all';
                this.client = 'all';
            },
            get hasFilters() {
                return this.search.trim() !== '' || this.status !== 'all' || this.type !== 'all' || this.client !== 'all';
            },
            get statusLabel() {
                if (this.status === 'all') {
                    return 'All';
                }

                if (this.status === 'offline') {
                    return 'Offline';
                }

                return this.status.charAt(0).toUpperCase() + this.status.slice(1);
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
                    {{ $projects->filter(fn ($project) => strtolower($project->status ?? '') === 'active')->count() }} online
                </p>
            </div>

            <div class="flex gap-2">
                <button type="button" class="h-9 px-4 text-xs border border-slate-700 text-slate-400 rounded-lg hover:text-cyan-300 hover:border-cyan-400/30 transition">
                    Export
                </button>

                <a href="{{ route('projects.create') }}" class="h-9 px-4 inline-flex items-center text-xs border border-cyan-400/30 bg-cyan-400/10 text-cyan-300 rounded-lg hover:bg-cyan-400/20 transition">
                    + Add Project
                </a>
            </div>
        </div>

       <div class="flex items-center justify-between">

    {{-- LEFT --}}
    <div class="flex items-center gap-3">

        {{-- SEARCH --}}
        <input
            x-model.debounce.200ms="search"
            type="text"
            placeholder="Search projects..."
            class="h-9 w-64 rounded-lg bg-[#0a1628] border border-slate-800 px-3 text-xs text-slate-300 placeholder-slate-600 outline-none focus:border-cyan-400/40"
        >

        {{-- STATUS --}}
        <div class="flex items-center gap-2 text-xs">

            <button @click="status='all'"
                :class="status==='all' ? 'text-white' : 'text-slate-500'"
                class="font-semibold">
                All
            </button>

            <button @click="status='active'"
                :class="status==='active' ? 'text-emerald-400' : 'text-slate-500'"
                class="font-semibold">
                Online
            </button>

            <button @click="status='warning'"
                :class="status==='warning' ? 'text-yellow-400' : 'text-slate-500'"
                class="font-semibold">
                Warning
            </button>

            <button @click="status='offline'"
                :class="status==='offline' ? 'text-red-400' : 'text-slate-500'"
                class="font-semibold">
                Offline
            </button>

        </div>

    </div>

    {{-- RIGHT --}}
    <div class="flex items-center gap-2">

        {{-- RESULTS --}}
        <span class="text-xs text-slate-500">
            <span x-text="visibleProjects"></span> results
        </span>

        {{-- RESET --}}
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
                            $status = strtolower($project->status ?? 'offline');
                            $online = $status === 'active';
                            $warning = $status === 'warning';
                            $stack = strtolower($project->stack ?? '');
                            $cloudflare = str_contains($stack, 'cloudflare');
                            $lastSeenAt = $project->agent_last_seen_at
                                ? \Illuminate\Support\Carbon::parse($project->agent_last_seen_at)
                                : null;
                            $agentOnline = $lastSeenAt && $lastSeenAt->gt(now()->subMinutes(30));

                            $score = 45;
                            $score += $online ? 20 : 0;
                            $score += $agentOnline ? 15 : 0;
                            $score += $project->domain ? 10 : 0;
                            $score += $cloudflare ? 10 : 0;
                            $score -= $warning ? 8 : 0;
                            $score -= (!$online && !$warning) ? 7 : 0;
                            $score = max(25, min(99, $score));

                            $scoreLabel = $score >= 85 ? 'Healthy' : ($score >= 65 ? 'Review' : 'Risk');
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
                                <div class="space-y-1">
                                    <span class="inline-flex px-2.5 py-1 rounded-md border text-xs font-bold {{ $typeClass }}">
                                        {{ $typeLabel }}
                                    </span>
                                    <p class="max-w-36 truncate text-[10px] font-medium text-slate-600">
                                        {{ $project->stack ?: 'No stack' }}
                                    </p>
                                </div>
                            </td>

                            {{-- SCORE --}}
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="relative h-14 w-14 shrink-0 rounded-full bg-[#020617] p-1 ring-1 {{ $scoreRing }}">
                                        <svg class="h-full w-full -rotate-90" viewBox="0 0 40 40" aria-hidden="true">
                                            <circle
                                                class="stroke-slate-800"
                                                cx="20"
                                                cy="20"
                                                r="16"
                                                fill="none"
                                                stroke-width="4"
                                            />
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
                                        <p class="mt-0.5 text-[10px] font-medium text-slate-600">Security score</p>
                                    </div>
                                </div>
                            </td>

                            {{-- SSL --}}
                            <td class="px-4 py-4">
                                <span class="text-xs font-bold {{ $project->domain ? 'text-emerald-400' : 'text-red-400' }}">
                                    {{ $project->domain ? 'Valid' : 'Missing' }}
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
                                <span class="flex items-center gap-2 text-xs font-bold
                                    {{ $agentOnline ? 'text-emerald-400' : ($warning ? 'text-yellow-400' : 'text-red-400') }}">
                                    <span class="w-2 h-2 rounded-full
                                        {{ $agentOnline ? 'bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,0.9)]' : ($warning ? 'bg-yellow-400' : 'bg-red-400') }}"></span>
                                    {{ $agentOnline ? 'Online' : ($warning ? 'Warning' : 'Offline') }}
                                </span>
                            </td>

                            {{-- ACTIONS --}}
                            <td class="px-4 py-4">
                                <div class="flex justify-start gap-2">
                                    <button type="button"
                                            title="View project"
                                            aria-label="View project"
                                            class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-cyan-400/20 bg-cyan-400/10 px-2.5 text-xs font-bold text-cyan-300 hover:bg-cyan-400/20 transition">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z"/>
                                            <circle cx="12" cy="12" r="2.8"/>
                                        </svg>
                                        <span class="hidden xl:inline">View</span>
                                    </button>

                                    <button type="button"
                                            title="Run scan"
                                            aria-label="Run scan"
                                            class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-amber-400/20 px-2.5 text-xs font-bold text-amber-300 hover:bg-amber-400/10 transition">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 2 4 14h7l-1 8 10-13h-7l1-7z"/>
                                        </svg>
                                        <span class="hidden xl:inline">Scan</span>
                                    </button>

                                    <button type="button"
                                            title="Security actions"
                                            aria-label="Security actions"
                                            class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-700 px-2.5 text-xs font-bold text-slate-400 hover:border-cyan-400/30 hover:text-cyan-300 transition">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-5"/>
                                        </svg>
                                        <span class="hidden xl:inline">Secure</span>
                                    </button>
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
        </div>
    </div>
</x-dashboard-layout>
