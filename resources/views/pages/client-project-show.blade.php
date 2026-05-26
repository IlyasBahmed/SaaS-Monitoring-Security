<x-dashboard-layout>
    @php
        $stack = \App\Models\Projects::normalizeProjectType($project->stack ?? '');
        $status = strtolower((string) ($project->status ?? 'offline'));
        $score = (int) ($projectScore['security_score'] ?? 0);
        $scoreLabel = $projectScore['score_label'] ?? ($score >= 85 ? 'Healthy' : ($score >= 65 ? 'Review' : 'Risk'));
        $riskLabel = ucfirst((string) ($projectScore['risk_label'] ?? 'low'));
        $scoreTone = $score >= 85 ? 'text-emerald-300' : ($score >= 65 ? 'text-amber-300' : 'text-red-300');
        $statusClass = [
            'active' => 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300',
            'warning' => 'border-amber-400/20 bg-amber-400/10 text-amber-300',
            'offline' => 'border-red-400/20 bg-red-400/10 text-red-300',
        ][$status] ?? 'border-slate-700 bg-slate-900 text-slate-300';
        $severityClass = static function (string $severity): string {
            return [
                'critical' => 'border-red-400/20 bg-red-400/10 text-red-300',
                'high' => 'border-orange-400/20 bg-orange-400/10 text-orange-300',
                'medium' => 'border-amber-400/20 bg-amber-400/10 text-amber-300',
                'low' => 'border-cyan-400/20 bg-cyan-400/10 text-cyan-300',
            ][strtolower($severity)] ?? 'border-slate-700 bg-slate-900 text-slate-300';
        };
        $lastSeen = $project->agent_last_seen_at ?? $project->last_seen_at;
        $lastSeenAt = $lastSeen ? rescue(fn () => \Illuminate\Support\Carbon::parse($lastSeen), null, false) : null;
        $agentOnline = $project->agents->contains(fn ($agent) => strtolower((string) ($agent->pivot->status ?? '')) === 'online');
        $cloudflareLinked = (bool) ($project->cloudflare_enabled ?? false) || filled($project->cloudflare_zone_id ?? null);
    @endphp

    <div class="space-y-6">
        <section class="rounded-xl border border-cyan-400/10 bg-[#07111f] p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-cyan-300">Project View</p>
                    <h1 class="mt-3 text-3xl font-black tracking-tight text-white">{{ $project->domain ?: $project->name }}</h1>
                    <p class="mt-2 text-sm font-semibold text-slate-500">{{ $client?->company_name ?? 'Client' }} / {{ $stack }}</p>
                </div>

                <a href="{{ route('client.projects') }}"
                   class="inline-flex h-10 items-center rounded-lg border border-slate-700 px-4 text-xs font-black text-slate-300 transition hover:border-cyan-400/30 hover:text-cyan-300">
                    Back to Projects
                </a>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-xl border border-cyan-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Security Score</p>
                <p class="mt-3 text-2xl font-black {{ $scoreTone }}">{{ $score }}</p>
                <p class="mt-1 text-xs font-bold text-slate-500">{{ $scoreLabel }}</p>
            </div>
            <div class="rounded-xl border border-amber-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Risk</p>
                <p class="mt-3 text-2xl font-black {{ $scoreTone }}">{{ $riskLabel }}</p>
            </div>
            <div class="rounded-xl border border-red-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Alerts</p>
                <p class="mt-3 text-2xl font-black text-red-300">{{ $project->alerts_count ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-orange-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Incidents</p>
                <p class="mt-3 text-2xl font-black text-orange-300">{{ $project->incidents_count ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-blue-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Cloudflare</p>
                <p class="mt-3 text-2xl font-black {{ $cloudflareLinked ? 'text-blue-300' : 'text-slate-500' }}">{{ $cloudflareLinked ? 'On' : 'Off' }}</p>
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="rounded-xl border border-cyan-400/10 bg-[#07111f] p-5">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-cyan-400">Asset</p>
                <h2 class="mt-1 text-lg font-black text-white">Project details</h2>

                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg border border-slate-800 bg-slate-950/30 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Name</p>
                        <p class="mt-1 truncate text-sm font-bold text-white">{{ $project->name ?: '-' }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-800 bg-slate-950/30 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Domain</p>
                        <p class="mt-1 truncate text-sm font-bold text-cyan-300">{{ $project->domain ?: '-' }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-800 bg-slate-950/30 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">IP Address</p>
                        <p class="mt-1 text-sm font-bold text-slate-300">{{ $project->ip_address ?: '-' }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-800 bg-slate-950/30 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Stack</p>
                        <p class="mt-1 text-sm font-bold text-slate-300">{{ $stack }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-cyan-400/10 bg-[#07111f] p-5">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-cyan-400">Runtime</p>
                <h2 class="mt-1 text-lg font-black text-white">Status</h2>

                <div class="mt-5 space-y-3">
                    <div class="flex items-center justify-between gap-4 rounded-lg border border-slate-800 bg-slate-950/30 p-4">
                        <p class="text-xs font-bold text-slate-500">Project status</p>
                        <span class="rounded-md border px-2 py-1 text-[10px] font-black uppercase {{ $statusClass }}">{{ $status }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4 rounded-lg border border-slate-800 bg-slate-950/30 p-4">
                        <p class="text-xs font-bold text-slate-500">Agent</p>
                        <p class="text-sm font-black {{ $agentOnline ? 'text-emerald-300' : 'text-red-300' }}">{{ $agentOnline ? 'Online' : 'Offline' }}</p>
                    </div>
                    <div class="flex items-center justify-between gap-4 rounded-lg border border-slate-800 bg-slate-950/30 p-4">
                        <p class="text-xs font-bold text-slate-500">Last seen</p>
                        <p class="text-sm font-bold text-slate-300">{{ $lastSeenAt ? $lastSeenAt->diffForHumans() : '-' }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-3">
            <div class="rounded-xl border border-cyan-400/10 bg-[#07111f]">
                <div class="border-b border-cyan-400/10 px-5 py-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-cyan-400">Detection</p>
                    <h2 class="mt-1 text-lg font-black text-white">Recent alerts</h2>
                </div>
                <div class="divide-y divide-slate-800">
                    @forelse ($alerts->take(5) as $alert)
                        <div class="px-5 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <p class="min-w-0 truncate text-sm font-black text-white">{{ $alert->title ?: 'Security alert' }}</p>
                                <span class="shrink-0 rounded-md border px-2 py-1 text-[10px] font-black uppercase {{ $severityClass($alert->severity ?? 'medium') }}">{{ $alert->severity ?? 'medium' }}</span>
                            </div>
                            <p class="mt-1 text-xs font-semibold text-slate-500">{{ $alert->detected_at ? $alert->detected_at->diffForHumans() : 'Recently' }}</p>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center text-sm font-semibold text-slate-500">No alerts found.</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl border border-cyan-400/10 bg-[#07111f]">
                <div class="border-b border-cyan-400/10 px-5 py-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-cyan-400">Response</p>
                    <h2 class="mt-1 text-lg font-black text-white">Recent incidents</h2>
                </div>
                <div class="divide-y divide-slate-800">
                    @forelse ($incidents->take(5) as $incident)
                        <div class="px-5 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <p class="min-w-0 truncate text-sm font-black text-white">{{ filled($incident->event ?? null) ? ucwords(str_replace('_', ' ', $incident->event)) : 'Security incident' }}</p>
                                <span class="shrink-0 rounded-md border px-2 py-1 text-[10px] font-black uppercase {{ $severityClass($incident->severity ?? 'medium') }}">{{ $incident->severity ?? 'medium' }}</span>
                            </div>
                            <p class="mt-1 text-xs font-semibold text-slate-500">{{ $incident->event_created_at ? $incident->event_created_at->diffForHumans() : 'Recently' }}</p>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center text-sm font-semibold text-slate-500">No incidents found.</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl border border-cyan-400/10 bg-[#07111f]">
                <div class="border-b border-cyan-400/10 px-5 py-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-cyan-400">Exposure</p>
                    <h2 class="mt-1 text-lg font-black text-white">Vulnerabilities</h2>
                </div>
                <div class="divide-y divide-slate-800">
                    @forelse ($vulnerabilities->take(5) as $vulnerability)
                        <div class="px-5 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <p class="min-w-0 truncate text-sm font-black text-white">{{ $vulnerability->title ?: $vulnerability->name ?: 'Vulnerability' }}</p>
                                <span class="shrink-0 rounded-md border px-2 py-1 text-[10px] font-black uppercase {{ $severityClass($vulnerability->severity ?? 'medium') }}">{{ $vulnerability->severity ?? 'medium' }}</span>
                            </div>
                            <p class="mt-1 text-xs font-semibold text-slate-500">{{ $vulnerability->detected_at ? $vulnerability->detected_at->diffForHumans() : 'Recently' }}</p>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center text-sm font-semibold text-slate-500">No vulnerabilities found.</div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-dashboard-layout>
