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
        $installationKey = $agent?->pivot?->api_key ?: $apiKey;
        $maskedInstallationKey = $installationKey
            ? substr($installationKey, 0, 5).str_repeat('*', 6).substr($installationKey, -6)
            : 'No installation key';
        $agentHeartbeat = $agent?->pivot?->last_seen_at
            ? \Illuminate\Support\Carbon::parse($agent->pivot->last_seen_at)
            : $lastSeenAt;
    @endphp

    <div
    x-data="realtime({{ $project->id }})"
    x-init="start()"
    class="space-y-6"
>
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-cyan-400">Project Dashboard</p>
                <h1 class="mt-2 text-3xl font-black text-white">{{ $project->domain ?: $project->name }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $project->client->company_name ?? '-' }} / {{ $stack }}</p>
            </div>

            <a href="{{ route('projects.index') }}"
               class="inline-flex h-10 items-center rounded-lg border border-slate-700 px-4 text-xs font-bold text-slate-400 transition hover:border-cyan-400/30 hover:text-cyan-300">
                Back
            </a>
        </div>

        <div class="grid gap-4 lg:grid-cols-[1.45fr_0.75fr]">
            <section class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Project Header</p>
                        <h2 class="mt-1 text-xl font-black text-white">{{ $project->name }}</h2>
                    </div>
                    <span class="inline-flex rounded-md border px-2.5 py-1 text-xs font-bold {{ $statusClass }}">
                        <span x-text="status"></span>
                    </span>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-lg border border-slate-800 bg-slate-950/30 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Domain</p>
                        <p class="mt-2 truncate text-sm font-bold text-cyan-300">{{ $project->domain ?: '-' }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-800 bg-slate-950/30 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Client</p>
                        <p class="mt-2 truncate text-sm font-bold text-slate-300">{{ $project->client->company_name ?? '-' }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-800 bg-slate-950/30 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Stack / Type</p>
                        <p class="mt-2 text-sm font-bold text-slate-300">{{ $stack }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-800 bg-slate-950/30 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">IP Address</p>
                        <p class="mt-2 text-sm font-bold text-slate-300">{{ $project->ip_address ?: '-' }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Security Score</p>
                <div class="mt-4 flex items-center gap-4">
                    <div class="relative h-20 w-20 shrink-0 rounded-full bg-[#020617] p-1.5 ring-1 {{ $scoreRing }}">
                        <svg class="h-full w-full -rotate-90" viewBox="0 0 40 40" aria-hidden="true">
                            <circle class="stroke-slate-800" cx="20" cy="20" r="16" fill="none" stroke-width="4" />
                            <circle class="stroke-current {{ $scoreText }}" cx="20" cy="20" r="16" fill="none" stroke-width="4" stroke-linecap="round" pathLength="100" stroke-dasharray="100" stroke-dashoffset="{{ 100 - $score }}" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-lg font-black {{ $scoreText }}">{{ $score }}</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-lg font-black {{ $scoreText }}">{{ $scoreLabel }}</p>
                        <p class="mt-1 text-xs font-medium text-slate-600">Current project risk level</p>
                    </div>
                </div>
            </section>
        </div>

        <section class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Connection & API</p>
                    <h2 class="mt-1 text-lg font-black text-white">Integration details</h2>
                </div>
                <span class="inline-flex rounded-md border px-2.5 py-1 text-xs font-bold {{ $connected ? 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300' : 'border-red-400/20 bg-red-400/10 text-red-300' }}">
                    Connected: {{ $connected ? 'Yes' : 'No' }}
                </span>
            </div>

            <div class="grid gap-4 lg:grid-cols-[1.4fr_1fr_1fr]">
                <div class="rounded-lg border border-slate-800 bg-slate-950/30 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Project API Key</p>
                    <div class="mt-3 flex items-center gap-2" x-data="{ copied: false }">
                        <code class="min-w-0 flex-1 truncate rounded-md border border-slate-800 bg-[#020617] px-3 py-2 text-xs font-bold text-cyan-300">{{ $maskedApiKey }}</code>
                        <button type="button"
                                @click="navigator.clipboard.writeText(@js($apiKey)); copied = true; setTimeout(() => copied = false, 1200)"
                                class="h-9 rounded-lg border border-cyan-400/20 px-3 text-xs font-bold text-cyan-300 hover:bg-cyan-400/10">
                            <span x-show="!copied">Copy</span>
                            <span x-show="copied" x-cloak>Copied</span>
                        </button>
                    </div>
                </div>
                <div class="rounded-lg border border-slate-800 bg-slate-950/30 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Connected At</p>
                    <p class="mt-3 text-sm font-bold text-slate-300">{{ $connectedAt ? $connectedAt->format('Y-m-d H:i') : '-' }}</p>
                </div>
                <div class="rounded-lg border border-slate-800 bg-slate-950/30 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Last Seen</p>
                    <p class="mt-3 text-sm font-bold text-slate-300"><span x-text="lastSeen"></span></p>
                </div>
            </div>
        </section>

        <section>
            <div class="mb-4">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Security Overview</p>
                <h2 class="mt-1 text-lg font-black text-white">Signals</h2>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
                    <p class="text-xs font-bold text-slate-500">SSL</p>
                    <p class="mt-3 text-lg font-black {{ $sslValid ? 'text-emerald-300' : 'text-red-300' }}">{{ $sslValid ? 'Valid' : 'Invalid' }}</p>
                </div>
                <div class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
                    <p class="text-xs font-bold text-slate-500">Cloudflare</p>
                    <p class="mt-3 text-lg font-black {{ $cloudflareLinked ? 'text-emerald-300' : 'text-slate-500' }}">{{ $cloudflareLinked ? 'Linked' : 'Not linked' }}</p>
                </div>
                <div class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
                    <p class="text-xs font-bold text-slate-500">Vulnerabilities</p>
                    <p class="mt-3 text-lg font-black text-amber-300">0</p>
                </div>
                <div class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
                    <p class="text-xs font-bold text-slate-500">Alerts</p>
                    <p class="mt-3 text-lg font-black text-cyan-300">{{ $project->alerts_count ?? 0 }}</p>
                </div>
                <div class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
                    <p class="text-xs font-bold text-slate-500">Incidents</p>
                    <p class="mt-3 text-lg font-black text-red-300">{{ $project->incidents_count ?? 0 }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
            <div class="mb-4">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Agent / Plugin Status</p>
                <h2 class="mt-1 text-lg font-black text-white">Runtime</h2>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-lg border border-slate-800 bg-slate-950/30 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Agent Status</p>
                   <p
    class="mt-2 text-sm font-black"
    :class="agentOnline ? 'text-emerald-300' : 'text-red-300'"
>
    <span x-text="agentOnline ? 'Online' : 'Offline'"></span>
</p>
                </div>
                <div class="rounded-lg border border-slate-800 bg-slate-950/30 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Agent Version</p>
                    <p class="mt-2 text-sm font-bold text-slate-300">{{ $agent?->pivot?->version ?: $agent?->version ?: '-' }}</p>
                </div>
                <div class="rounded-lg border border-slate-800 bg-slate-950/30 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Last Heartbeat</p>
                    <p class="mt-2 text-sm font-bold text-slate-300">{{ $agentHeartbeat ? $agentHeartbeat->diffForHumans() : '-' }}</p>
                </div>
                <div class="rounded-lg border border-slate-800 bg-slate-950/30 p-4 xl:col-span-2">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Installation Key</p>
                    <p class="mt-2 truncate text-sm font-bold text-cyan-300">{{ $maskedInstallationKey }}</p>
                </div>
            </div>

            <div class="mt-4">
                <button type="button" class="h-10 rounded-lg border border-cyan-400/20 px-4 text-xs font-bold text-cyan-300 hover:bg-cyan-400/10">
                    Regenerate key
                </button>
            </div>
        </section>

        <section class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
            <p class="mb-4 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Actions</p>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('projects.edit', $project) }}" class="inline-flex h-10 items-center rounded-lg border border-slate-700 px-4 text-xs font-bold text-slate-300 hover:border-cyan-400/30 hover:text-cyan-300">Edit Project</a>
                <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Delete this project? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="h-10 rounded-lg border border-red-400/20 px-4 text-xs font-bold text-red-300 hover:bg-red-400/10">Delete Project</button>
                </form>
                <button type="button" class="h-10 rounded-lg border border-amber-400/20 px-4 text-xs font-bold text-amber-300 hover:bg-amber-400/10">Run Scan</button>
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
