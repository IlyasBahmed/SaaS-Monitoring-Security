<x-dashboard-layout>
    @php
        $status = strtolower($project->status ?? 'offline');
        $isActive = $status === 'active';
        $isWarning = $status === 'warning';
        $stack = \App\Models\Projects::normalizeProjectType($project->stack ?? '');
        $stackSource = strtolower((string) $project->stack);
        $cloudflareLinked = str_contains($stackSource, 'cloudflare');
        $sslValid = filled($project->domain);

        $lastSeenValue = $project->agent_last_seen_at ?? $project->last_seen_at ?? null;
        $lastSeenAt = $lastSeenValue ? \Illuminate\Support\Carbon::parse($lastSeenValue) : null;
        $agentOnline = $lastSeenAt && $lastSeenAt->gt(now()->subMinutes(30));
        $connected = (bool) ($project->is_connected ?? false) || $agentOnline;
        $connectedAt = $project->connected_at ? \Illuminate\Support\Carbon::parse($project->connected_at) : null;
        $agent = $project->agents->first();

        $score = 45;
        $score += $isActive ? 20 : 0;
        $score += $agentOnline ? 15 : 0;
        $score += $project->domain ? 10 : 0;
        $score += $cloudflareLinked ? 10 : 0;
        $score -= $isWarning ? 8 : 0;
        $score -= (! $isActive && ! $isWarning) ? 7 : 0;
        $score = max(25, min(99, $score));

        $scoreLabel = $score >= 85 ? 'Healthy' : ($score >= 65 ? 'Review' : 'Risk');
        $scoreText = $score >= 85 ? 'text-emerald-300' : ($score >= 65 ? 'text-amber-300' : 'text-red-300');
        $scoreRing = $score >= 85 ? 'ring-emerald-400/20' : ($score >= 65 ? 'ring-amber-400/20' : 'ring-red-400/20');
        $statusClass = $isActive ? 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300' : ($isWarning ? 'border-yellow-400/20 bg-yellow-400/10 text-yellow-300' : 'border-red-400/20 bg-red-400/10 text-red-300');

        $apiKey = (string) ($project->api_key ?? '');
        $maskedApiKey = $apiKey
            ? substr($apiKey, 0, 5).str_repeat('*', 6).substr($apiKey, -6)
            : 'No API key';
        $agentHeartbeat = $agent?->pivot?->last_seen_at
            ? \Illuminate\Support\Carbon::parse($agent->pivot->last_seen_at)
            : $lastSeenAt;
        $vulnerabilityCount = \App\Models\SiteVulnerability::query()
            ->where('project_id', $project->id)
            ->count();
    @endphp

    <div
        x-data="realtime({{ $project->id }})"
        x-init="start()"
        class="space-y-5 px-2 pb-5"
    >
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-cyan-400">Project Dashboard</p>
                <h1 class="mt-2 text-3xl font-black text-white">{{ $project->domain ?: $project->name }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $project->client->company_name ?? '-' }} / {{ $stack }}</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <form method="POST" action="{{ route('projects.vulnerability.scan', $project) }}" @submit="beginScan()">
                    @csrf
                    <button
                        type="submit"
                        :disabled="scanRunning"
                        class="relative inline-flex h-10 min-w-[112px] items-center justify-center overflow-hidden rounded-lg border border-amber-400/20 bg-amber-400/10 px-4 text-xs font-bold text-amber-300 transition hover:bg-amber-400/20 disabled:cursor-wait disabled:border-cyan-400/30 disabled:bg-cyan-400/10 disabled:text-cyan-200"
                    >
                        <span x-show="scanRunning" x-cloak class="scan-sweep pointer-events-none absolute inset-y-0 left-0 w-1/2 bg-gradient-to-r from-transparent via-cyan-300/20 to-transparent"></span>
                        <span x-show="!scanRunning">Run Scan</span>
                        <span x-show="scanRunning" x-cloak class="inline-flex items-center gap-2">
                            <span class="scan-dot h-2 w-2 rounded-full bg-cyan-300 shadow-[0_0_12px_rgba(103,232,249,0.95)]"></span>
                            Scanning
                        </span>
                    </button>
                </form>
                <a href="{{ route('projects.index') }}"
                   class="inline-flex h-10 items-center rounded-lg border border-slate-700 px-4 text-xs font-bold text-slate-400 transition hover:border-cyan-400/30 hover:text-cyan-300">
                    Back
                </a>
            </div>
        </div>

        <div
            x-show="scanRunning"
            x-cloak
            x-transition.opacity.duration.200ms
            class="relative overflow-hidden rounded-xl border border-cyan-400/20 bg-[#07111f] px-4 py-4 shadow-[0_0_30px_rgba(34,211,238,0.08)]"
            role="status"
            aria-live="polite"
        >
            <div class="scan-grid pointer-events-none absolute inset-0 opacity-40"></div>
            <div class="scan-sweep pointer-events-none absolute inset-y-0 left-0 w-1/3 bg-gradient-to-r from-transparent via-cyan-300/10 to-transparent"></div>

            <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center">
                <div class="relative h-14 w-14 shrink-0 rounded-full border border-cyan-400/20 bg-cyan-400/10">
                    <span class="absolute inset-2 rounded-full border border-cyan-300/20"></span>
                    <span class="absolute inset-0 rounded-full border border-cyan-300/30 animate-ping"></span>
                    <svg class="absolute inset-2 h-10 w-10 animate-spin text-cyan-300" viewBox="0 0 44 44" fill="none" aria-hidden="true">
                        <circle cx="22" cy="22" r="17" stroke="currentColor" stroke-width="2" stroke-dasharray="12 10" opacity="0.65" />
                        <path d="M22 22L35 12" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" />
                        <circle cx="22" cy="22" r="2.5" fill="currentColor" />
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-black text-cyan-200">Scan running</p>
                    <p class="mt-1 text-xs font-medium text-slate-500">Checking headers, SSL, exposed paths, and known signatures...</p>
                    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-900">
                        <span class="scan-progress block h-full w-1/2 rounded-full bg-gradient-to-r from-cyan-400 via-amber-300 to-cyan-400"></span>
                    </div>
                </div>
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

        <div class="grid gap-5 lg:grid-cols-[1.45fr_0.75fr]">
            <section class="rounded-xl border border-slate-800 bg-[#07111f] px-5 py-4">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Project Header</p>
                        <h2 class="mt-1 text-xl font-black text-white">{{ $project->name }}</h2>
                    </div>
                    <span class="inline-flex rounded-md border px-2.5 py-1 text-xs font-bold {{ $statusClass }}">
                        <span x-text="status"></span>
                    </span>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-lg border border-slate-800 bg-slate-950/30 px-4 py-3.5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Domain</p>
                        <p class="mt-1 truncate text-sm font-bold text-cyan-300">{{ $project->domain ?: '-' }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-800 bg-slate-950/30 px-4 py-3.5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Client</p>
                        <p class="mt-1 truncate text-sm font-bold text-slate-300">{{ $project->client->company_name ?? '-' }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-800 bg-slate-950/30 px-4 py-3.5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Stack / Type</p>
                        <p class="mt-1 text-sm font-bold text-slate-300">{{ $stack }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-800 bg-slate-950/30 px-4 py-3.5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">IP Address</p>
                        <p class="mt-1 text-sm font-bold text-slate-300">{{ $project->ip_address ?: '-' }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-slate-800 bg-[#07111f] px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="relative h-16 w-16 shrink-0 rounded-full bg-[#020617] p-1.5 ring-1 {{ $scoreRing }}">
                        <svg class="h-full w-full -rotate-90" viewBox="0 0 40 40" aria-hidden="true">
                            <circle class="stroke-slate-800" cx="20" cy="20" r="16" fill="none" stroke-width="4" />
                            <circle class="stroke-current {{ $scoreText }}" cx="20" cy="20" r="16" fill="none" stroke-width="4" stroke-linecap="round" pathLength="100" stroke-dasharray="100" stroke-dashoffset="{{ 100 - $score }}" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-base font-black {{ $scoreText }}">{{ $score }}</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Security Score</p>
                        <p class="mt-1 text-base font-black {{ $scoreText }}">{{ $scoreLabel }}</p>
                        <p class="text-xs font-medium text-slate-600">Current risk level</p>
                    </div>
                </div>
            </section>
        </div>

        <section class="rounded-xl border border-slate-800 bg-[#07111f] px-5 py-4">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Connection & API</p>
                    <h2 class="mt-1 text-lg font-black text-white">Integration details</h2>
                </div>

                <div class="flex flex-wrap gap-3">
                    <span class="inline-flex h-9 items-center rounded-md border px-2.5 text-xs font-bold {{ $connected ? 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300' : 'border-red-400/20 bg-red-400/10 text-red-300' }}">
                        Connected: {{ $connected ? 'Yes' : 'No' }}
                    </span>
                    <button type="button" class="h-9 rounded-lg border border-cyan-400/20 px-3 text-xs font-bold text-cyan-300 hover:bg-cyan-400/10">
                        Regenerate key
                    </button>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-[1.4fr_0.8fr_0.8fr]">
                <div class="rounded-lg border border-slate-800 bg-slate-950/30 px-4 py-3.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Project API Key</p>
                    <div class="mt-2 flex items-center gap-2" x-data="{ copied: false }">
                        <code class="min-w-0 flex-1 truncate rounded-md border border-slate-800 bg-[#020617] px-3 py-2 text-xs font-bold text-cyan-300">{{ $maskedApiKey }}</code>
                        <button type="button"
                                @click="navigator.clipboard.writeText(@js($apiKey)); copied = true; setTimeout(() => copied = false, 1200)"
                                class="h-9 rounded-lg border border-cyan-400/20 px-3 text-xs font-bold text-cyan-300 hover:bg-cyan-400/10">
                            <span x-show="!copied">Copy</span>
                            <span x-show="copied" x-cloak>Copied</span>
                        </button>
                    </div>
                </div>
                <div class="flex items-center justify-between gap-4 rounded-lg border border-slate-800 bg-slate-950/30 px-4 py-3.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Connected At</p>
                    <p class="text-sm font-bold text-slate-300">{{ $connectedAt ? $connectedAt->format('Y-m-d H:i') : '-' }}</p>
                </div>
                <div class="flex items-center justify-between gap-4 rounded-lg border border-slate-800 bg-slate-950/30 px-4 py-3.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Last Seen</p>
                    <p class="text-sm font-bold text-slate-300"><span x-text="lastSeen"></span></p>
                </div>
            </div>
        </section>

        <section>
            <div class="mb-4">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Security Overview</p>
                <h2 class="mt-1 text-lg font-black text-white">Signals</h2>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-800 bg-[#07111f] px-4 py-3.5">
                    <p class="text-xs font-bold text-slate-500">SSL</p>
                    <p class="text-sm font-black {{ $sslValid ? 'text-emerald-300' : 'text-red-300' }}">{{ $sslValid ? 'Valid' : 'Invalid' }}</p>
                </div>
                <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-800 bg-[#07111f] px-4 py-3.5">
                    <p class="text-xs font-bold text-slate-500">Cloudflare</p>
                    <p class="text-sm font-black {{ $cloudflareLinked ? 'text-emerald-300' : 'text-slate-500' }}">{{ $cloudflareLinked ? 'Linked' : 'Not linked' }}</p>
                </div>
                <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-800 bg-[#07111f] px-4 py-3.5">
                    <p class="text-xs font-bold text-slate-500">Vulnerabilities</p>
                    <p class="text-base font-black text-amber-300">{{ $vulnerabilityCount }}</p>
                </div>
                <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-800 bg-[#07111f] px-4 py-3.5">
                    <p class="text-xs font-bold text-slate-500">Alerts</p>
                    <p class="text-base font-black text-cyan-300">{{ $project->alerts_count ?? 0 }}</p>
                </div>
                <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-800 bg-[#07111f] px-4 py-3.5">
                    <p class="text-xs font-bold text-slate-500">Incidents</p>
                    <p class="text-base font-black text-red-300">{{ $project->incidents_count ?? 0 }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-slate-800 bg-[#07111f] px-5 py-4">
            <div class="mb-4">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Agent / Plugin Status</p>
                <h2 class="mt-1 text-lg font-black text-white">Runtime</h2>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="flex items-center justify-between gap-4 rounded-lg border border-slate-800 bg-slate-950/30 px-4 py-3.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Agent Status</p>
                    <p class="text-sm font-black" :class="agentOnline ? 'text-emerald-300' : 'text-red-300'">
                        <span x-text="agentOnline ? 'Online' : 'Offline'"></span>
                    </p>
                </div>
                <div class="flex items-center justify-between gap-4 rounded-lg border border-slate-800 bg-slate-950/30 px-4 py-3.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Agent Version</p>
                    <p class="text-sm font-bold text-slate-300">{{ $agent?->pivot?->version ?: $agent?->version ?: '-' }}</p>
                </div>
                <div class="flex items-center justify-between gap-4 rounded-lg border border-slate-800 bg-slate-950/30 px-4 py-3.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Last Heartbeat</p>
                    <p class="text-sm font-bold text-slate-300">{{ $agentHeartbeat ? $agentHeartbeat->diffForHumans() : '-' }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-slate-800 bg-[#07111f] px-5 py-4">
            <p class="mb-4 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Actions</p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('projects.edit', $project) }}" class="inline-flex h-10 items-center rounded-lg border border-slate-700 px-4 text-xs font-bold text-slate-300 hover:border-cyan-400/30 hover:text-cyan-300">Edit Project</a>
                <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Delete this project? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="h-10 rounded-lg border border-red-400/20 px-4 text-xs font-bold text-red-300 hover:bg-red-400/10">Delete Project</button>
                </form>
                <button type="button" class="h-10 rounded-lg border border-slate-700 px-4 text-xs font-bold text-slate-300 hover:border-cyan-400/30 hover:text-cyan-300">View Logs</button>
                <a href="{{ route('projects.index') }}" class="inline-flex h-10 items-center rounded-lg border border-cyan-400/20 bg-cyan-400/10 px-4 text-xs font-bold text-cyan-300 hover:bg-cyan-400/20">Back</a>
            </div>
        </section>
    </div>

    <script>
        function realtime(id) {
            return {
                status: '',
                lastSeen: '',
                agentOnline: false,
                scanRunning: false,

                beginScan() {
                    this.scanRunning = true;
                },

                async load() {
                    const res = await fetch(`/projects/${id}/realtime`);
                    const data = await res.json();

                    this.status = data.status;
                    this.lastSeen = data.last_seen;
                    this.agentOnline = data.agent_online;
                },

                start() {
                    this.load();
                    setInterval(() => this.load(), 5000);
                }
            }
        }
    </script>
</x-dashboard-layout>
