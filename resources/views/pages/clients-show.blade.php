<x-dashboard-layout>
    @php
        $allProjects = $clientProjects ?? collect($client->projects ?? []);
        $projects = $projects ?? $allProjects;
        $clientStatus = strtolower($client->status ?? 'active');
        $activeProjects = $allProjects->filter(fn ($project) => strtolower($project->status ?? '') === 'active')->count();
        $offlineProjects = $allProjects->filter(fn ($project) => strtolower($project->status ?? 'offline') !== 'active')->count();

        $scoreForProject = function ($project) {
            $status = strtolower($project->status ?? 'offline');
            $stack = strtolower($project->stack ?? '');
            $lastSeenAt = $project->agent_last_seen_at
                ? \Illuminate\Support\Carbon::parse($project->agent_last_seen_at)
                : null;
            $agentOnline = $lastSeenAt && $lastSeenAt->gt(now()->subMinutes(30));

            $score = 45;
            $score += $status === 'active' ? 20 : 0;
            $score += $agentOnline ? 15 : 0;
            $score += $project->domain ? 10 : 0;
            $score += str_contains($stack, 'cloudflare') ? 10 : 0;
            $score -= $status === 'warning' ? 8 : 0;
            $score -= ! in_array($status, ['active', 'warning'], true) ? 7 : 0;

            return max(25, min(99, $score));
        };

        $averageScore = $allProjects->isNotEmpty()
            ? (int) round($allProjects->avg(fn ($project) => $scoreForProject($project)))
            : 0;
        $totalAlerts = $allProjects->sum(fn ($project) => $project->alerts_count ?? 0);
        $totalIncidents = $allProjects->sum(fn ($project) => $project->incidents_count ?? 0);
        $riskLevel = $averageScore >= 85 ? 'Low' : ($averageScore >= 65 ? 'Medium' : 'High');
        $riskClass = $averageScore >= 85 ? 'text-emerald-300' : ($averageScore >= 65 ? 'text-amber-300' : 'text-red-300');
        $lastActivity = $allProjects
            ->map(fn ($project) => $project->agent_last_seen_at ?? $project->updated_at)
            ->filter()
            ->map(fn ($date) => \Illuminate\Support\Carbon::parse($date))
            ->sortDesc()
            ->first();
        $statusClass = $clientStatus === 'active'
            ? 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300'
            : ($clientStatus === 'warning'
                ? 'border-amber-400/20 bg-amber-400/10 text-amber-300'
                : 'border-red-400/20 bg-red-400/10 text-red-300');
    @endphp

    <div class="space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-cyan-400">Client Dashboard</p>
                <h1 class="mt-2 text-3xl font-black text-white">{{ $client->company_name }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $client->email }} / {{ $client->phone ?: 'No phone' }}</p>
            </div>

            <a href="{{ route('clients.index') }}"
               class="inline-flex h-10 items-center rounded-lg border border-slate-700 px-4 text-xs font-bold text-slate-400 transition hover:border-cyan-400/30 hover:text-cyan-300">
                Back
            </a>
        </div>

        <section class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div class="xl:col-span-2">
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Client Header</p>
                    <h2 class="mt-2 text-xl font-black text-white">{{ $client->company_name }}</h2>
                    <p class="mt-2 text-sm font-medium text-slate-500">{{ $client->address ?: 'No address' }}</p>
                </div>
                <div class="rounded-lg border border-slate-800 bg-slate-950/30 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Email</p>
                    <p class="mt-2 truncate text-sm font-bold text-cyan-300">{{ $client->email }}</p>
                </div>
                <div class="rounded-lg border border-slate-800 bg-slate-950/30 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Phone</p>
                    <p class="mt-2 text-sm font-bold text-slate-300">{{ $client->phone ?: '-' }}</p>
                </div>
                <div class="rounded-lg border border-slate-800 bg-slate-950/30 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Created</p>
                    <p class="mt-2 text-sm font-bold text-slate-300">{{ $client->created_at ? $client->created_at->format('M Y') : '-' }}</p>
                </div>
            </div>

            <div class="mt-4">
                <span class="inline-flex rounded-md border px-2.5 py-1 text-xs font-bold {{ $statusClass }}">
                    Status: {{ ucfirst($clientStatus) }}
                </span>
            </div>
        </section>

        <section>
            <p class="mb-4 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Overview</p>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
                    <p class="text-xs font-bold text-slate-500">Total Projects</p>
                    <p class="mt-3 text-2xl font-black text-white">{{ $allProjects->count() }}</p>
                </div>
                <div class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
                    <p class="text-xs font-bold text-slate-500">Active Projects</p>
                    <p class="mt-3 text-2xl font-black text-emerald-300">{{ $activeProjects }}</p>
                </div>
                <div class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
                    <p class="text-xs font-bold text-slate-500">Offline Projects</p>
                    <p class="mt-3 text-2xl font-black text-red-300">{{ $offlineProjects }}</p>
                </div>
                <div class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
                    <p class="text-xs font-bold text-slate-500">Average Security Score</p>
                    <p class="mt-3 text-2xl font-black {{ $riskClass }}">{{ $averageScore }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Projects Table</p>
                    <h2 class="mt-1 text-lg font-black text-white">Protected assets</h2>
                </div>
                <a href="{{ route('projects.create', ['client_id' => $client->id]) }}" class="inline-flex h-9 items-center rounded-lg border border-cyan-400/20 bg-cyan-400/10 px-3 text-xs font-bold text-cyan-300 hover:bg-cyan-400/20">
                    Add Project
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[880px]">
                    <thead class="text-left text-[10px] uppercase tracking-[0.18em] text-slate-500">
                        <tr class="border-b border-slate-800">
                            <th class="px-3 py-3">Domain</th>
                            <th class="px-3 py-3">IP</th>
                            <th class="px-3 py-3">Stack</th>
                            <th class="px-3 py-3">Status</th>
                            <th class="px-3 py-3">Security Score</th>
                            <th class="px-3 py-3">Last Seen</th>
                            <th class="px-3 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($projects as $project)
                            @php
                                $projectStatus = strtolower($project->status ?? 'offline');
                                $projectScore = $scoreForProject($project);
                                $projectScoreClass = $projectScore >= 85 ? 'text-emerald-300' : ($projectScore >= 65 ? 'text-amber-300' : 'text-red-300');
                                $lastSeen = $project->agent_last_seen_at
                                    ? \Illuminate\Support\Carbon::parse($project->agent_last_seen_at)
                                    : null;
                            @endphp
                            <tr class="hover:bg-white/[0.03]">
                                <td class="px-3 py-4 text-sm font-bold text-cyan-300">{{ $project->domain ?: $project->name }}</td>
                                <td class="px-3 py-4 text-sm text-slate-400">{{ $project->ip_address ?: '-' }}</td>
                                <td class="px-3 py-4 text-sm text-slate-400">{{ \App\Models\Projects::normalizeProjectType($project->stack) }}</td>
                                <td class="px-3 py-4">
                                    <span class="text-xs font-bold {{ $projectStatus === 'active' ? 'text-emerald-300' : ($projectStatus === 'warning' ? 'text-amber-300' : 'text-red-300') }}">
                                        {{ ucfirst($projectStatus) }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 text-sm font-black {{ $projectScoreClass }}">{{ $projectScore }}</td>
                                <td class="px-3 py-4 text-sm text-slate-500">{{ $lastSeen ? $lastSeen->diffForHumans() : '-' }}</td>
                                <td class="px-3 py-4 text-right">
                                    <a href="{{ route('projects.show', $project) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-cyan-400/20 bg-cyan-400/10 text-cyan-300 hover:bg-cyan-400/20" title="View project" aria-label="View project">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z"/>
                                            <circle cx="12" cy="12" r="2.8"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-10 text-center text-sm text-slate-500">No projects for this client yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if ($projects instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            <x-pagination :paginator="$projects" />
        @endif

        <section>
            <p class="mb-4 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Security Summary</p>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
                    <p class="text-xs font-bold text-slate-500">Total Alerts</p>
                    <p class="mt-3 text-2xl font-black text-cyan-300">{{ $totalAlerts }}</p>
                </div>
                <div class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
                    <p class="text-xs font-bold text-slate-500">Total Incidents</p>
                    <p class="mt-3 text-2xl font-black text-red-300">{{ $totalIncidents }}</p>
                </div>
                <div class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
                    <p class="text-xs font-bold text-slate-500">Risk Level</p>
                    <p class="mt-3 text-2xl font-black {{ $riskClass }}">{{ $riskLevel }}</p>
                </div>
                <div class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
                    <p class="text-xs font-bold text-slate-500">Last Activity</p>
                    <p class="mt-3 text-sm font-black text-slate-300">{{ $lastActivity ? $lastActivity->diffForHumans() : '-' }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
            <p class="mb-4 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Actions</p>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('projects.create', ['client_id' => $client->id]) }}" class="inline-flex h-10 items-center rounded-lg border border-cyan-400/20 bg-cyan-400/10 px-4 text-xs font-bold text-cyan-300 hover:bg-cyan-400/20">Add Project</a>
                <a href="{{ route('clients.edit', $client) }}" class="inline-flex h-10 items-center rounded-lg border border-slate-700 px-4 text-xs font-bold text-slate-300 hover:border-cyan-400/30 hover:text-cyan-300">Edit Client</a>
                <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Delete this client? All client projects will also be deleted.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="h-10 rounded-lg border border-red-400/20 px-4 text-xs font-bold text-red-300 hover:bg-red-400/10">Delete Client</button>
                </form>
            </div>
        </section>
    </div>
</x-dashboard-layout>
