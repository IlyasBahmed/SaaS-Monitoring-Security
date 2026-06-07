<x-dashboard-layout>
    @php
        $clientName = $client?->company_name ?: (Auth::user()->name ?? 'Client');
        $securityScore = (int) ($stats['security_score'] ?? 0);
        $scoreTone = $securityScore >= 85
            ? 'text-emerald-300'
            : ($securityScore >= 65 ? 'text-amber-300' : 'text-red-300');
        $riskLabel = $securityScore >= 85
            ? 'Low'
            : ($securityScore >= 65 ? 'Moderate' : 'High');
        $severityClass = static function (string $severity): string {
            return [
                'critical' => 'border-red-400/20 bg-red-400/10 text-red-300',
                'high' => 'border-orange-400/20 bg-orange-400/10 text-orange-300',
                'medium' => 'border-amber-400/20 bg-amber-400/10 text-amber-300',
                'low' => 'border-cyan-400/20 bg-cyan-400/10 text-cyan-300',
            ][$severity] ?? 'border-slate-700 bg-slate-900 text-slate-300';
        };

        $statusClass = static function (string $status): string {
            return [
                'active' => 'text-emerald-300',
                'warning' => 'text-amber-300',
                'open' => 'text-red-300',
                'resolved' => 'text-emerald-300',
                'closed' => 'text-emerald-300',
            ][$status] ?? 'text-slate-400';
        };
    @endphp

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-5 py-4 text-sm font-bold text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error') || $errors->any())
            <div class="rounded-xl border border-red-400/20 bg-red-400/10 px-5 py-4 text-sm font-bold text-red-300">
                {{ session('error') ?: $errors->first() }}
            </div>
        @endif

        <section id="dashboard" class="overflow-hidden rounded-xl border border-cyan-400/10 bg-[#07111f]">
            <div class="grid gap-6 p-6 lg:grid-cols-[1.2fr_0.8fr] lg:items-end">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-cyan-300">Client Dashboard</p>
                    <h1 class="mt-3 text-3xl font-black tracking-tight text-white">{{ $clientName }}</h1>
                    <p class="mt-2 max-w-2xl text-sm font-medium leading-6 text-slate-400">
                        A read-onlyy security overview for your protected assets, active signals, and platform coverage.
                    </p>

                    <div class="mt-5 flex flex-wrap gap-2">
                        <span class="inline-flex rounded-lg border border-cyan-400/20 bg-cyan-400/10 px-3 py-1.5 text-xs font-bold text-cyan-200">
                            {{ $client?->email ?: (Auth::user()?->email ?? 'No email on file') }}
                        </span>
                        <span class="inline-flex rounded-lg border border-slate-700 bg-slate-950/40 px-3 py-1.5 text-xs font-bold text-slate-300">
                            {{ $client?->phone ?: 'No phone on file' }}
                        </span>
                    </div>

                    <div class="mt-5 flex flex-wrap gap-3">
                        <a href="{{ route('settings.index') }}"
                           class="inline-flex h-10 items-center rounded-lg border border-slate-700 px-4 text-xs font-black text-slate-300 transition hover:border-cyan-400/30 hover:text-cyan-300">
                            Update Settings
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl border border-emerald-400/10 bg-emerald-400/[0.04] p-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Security Score</p>
                        <p class="mt-3 text-3xl font-black {{ $scoreTone }}">{{ $securityScore }}</p>
                    </div>
                    <div class="rounded-xl border border-cyan-400/10 bg-slate-950/30 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Risk Level</p>
                        <p class="mt-3 text-3xl font-black {{ $scoreTone }}">{{ $riskLabel }}</p>
                    </div>
                </div>
            </div>
        </section>

        @if (! $client)
            <section class="rounded-xl border border-amber-400/20 bg-amber-400/10 p-5">
                <h2 class="text-lg font-black text-amber-200">Client profile not linked</h2>
                <p class="mt-2 text-sm font-medium text-amber-100/80">
                    Your user account is active, but no client record is attached yet. Ask an administrator to link this login to the correct client profile.
                </p>
            </section>
        @endif

        <section id="security-overview" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-xl border border-cyan-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Protected Assets</p>
                <p class="mt-3 text-2xl font-black text-white">{{ $stats['projects'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-emerald-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Active Projects</p>
                <p class="mt-3 text-2xl font-black text-emerald-300">{{ $stats['active_projects'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-red-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Alerts</p>
                <p class="mt-3 text-2xl font-black text-red-300">{{ $stats['open_alerts'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-orange-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Incidents</p>
                <p class="mt-3 text-2xl font-black text-orange-300">{{ $stats['open_incidents'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-blue-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Cloudflare</p>
                <p class="mt-3 text-2xl font-black text-blue-300">{{ $stats['cloudflare_coverage'] ?? 0 }}%</p>
            </div>
        </section>

        <section id="projects-sites" class="rounded-xl border border-cyan-400/10 bg-[#07111f]">
            <div class="flex items-center justify-between gap-4 border-b border-cyan-400/10 px-5 py-4">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-cyan-400">Assets</p>
                    <h2 class="mt-1 text-lg font-black text-white">Project health</h2>
                </div>
                <span class="rounded-lg border border-slate-700 bg-slate-950/40 px-3 py-2 text-xs font-bold text-slate-400">
                    Read only
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[780px]">
                    <thead class="text-left text-[10px] uppercase tracking-[0.18em] text-slate-500">
                        <tr class="border-b border-slate-800">
                            <th class="px-5 py-4">Asset</th>
                            <th class="px-5 py-4">Stack</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Score</th>
                            <th class="px-5 py-4">Signals</th>
                            <th class="px-5 py-4">Cloudflare</th>
                            <th class="px-5 py-4">Last Seen</th>
                            <th class="px-5 py-4">View</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse ($healthRows as $row)
                            <tr class="hover:bg-white/[0.03]">
                                <td class="px-5 py-4 text-sm font-black text-cyan-200">{{ $row['name'] }}</td>
                                <td class="px-5 py-4 text-sm font-semibold text-slate-400">{{ $row['type'] }}</td>
                                <td class="px-5 py-4 text-sm font-black {{ $statusClass($row['status']) }}">{{ ucfirst($row['status']) }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="w-9 text-sm font-black text-white">{{ $row['score'] }}</span>
                                        <div class="h-2 w-28 overflow-hidden rounded-full bg-slate-900">
                                            <div class="h-full rounded-full bg-cyan-400" style="width: {{ $row['score'] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-sm font-semibold text-slate-400">
                                    {{ $row['alerts'] }} alerts / {{ $row['incidents'] }} incidents
                                </td>
                                <td class="px-5 py-4 text-sm font-black {{ $row['cloudflare'] ? 'text-blue-300' : 'text-slate-500' }}">
                                    {{ $row['cloudflare'] ? 'Covered' : 'Not linked' }}
                                </td>
                                <td class="px-5 py-4 text-sm font-semibold text-slate-500">{{ $row['last_seen'] }}</td>
                                <td class="px-5 py-4">
                                    <a href="{{ route('client.projects.show', $row['id']) }}"
                                       class="inline-flex h-9 items-center rounded-lg border border-cyan-400/20 bg-cyan-400/10 px-3 text-xs font-black text-cyan-300 transition hover:bg-cyan-400/20">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center text-sm font-semibold text-slate-500">
                                    No protected assets are attached to this client profile yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-2">
            <div id="alerts" class="rounded-xl border border-cyan-400/10 bg-[#07111f]">
                <div class="border-b border-cyan-400/10 px-5 py-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-cyan-400">Detection</p>
                    <h2 class="mt-1 text-lg font-black text-white">Recent alerts</h2>
                </div>

                <div class="divide-y divide-slate-800">
                    @forelse ($recentAlerts as $alert)
                        <div class="flex items-start justify-between gap-4 px-5 py-4">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-black text-white">{{ $alert['title'] }}</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">{{ $alert['project'] }} / {{ $alert['time'] }}</p>
                            </div>
                            <span class="shrink-0 rounded-md border px-2 py-1 text-[10px] font-black uppercase {{ $severityClass($alert['severity']) }}">
                                {{ $alert['severity'] }}
                            </span>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center text-sm font-semibold text-slate-500">
                            No recent alerts for this client.
                        </div>
                    @endforelse
                </div>
            </div>

            <div id="incidents" class="rounded-xl border border-cyan-400/10 bg-[#07111f]">
                <div class="border-b border-cyan-400/10 px-5 py-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-cyan-400">Response</p>
                    <h2 class="mt-1 text-lg font-black text-white">Recent incidents</h2>
                </div>

                <div class="divide-y divide-slate-800">
                    @forelse ($recentIncidents as $incident)
                        <div class="flex items-start justify-between gap-4 px-5 py-4">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-black text-white">{{ $incident['title'] }}</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">{{ $incident['project'] }} / {{ $incident['time'] }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <span class="rounded-md border px-2 py-1 text-[10px] font-black uppercase {{ $severityClass($incident['severity']) }}">
                                    {{ $incident['severity'] }}
                                </span>
                                <span class="text-[10px] font-black uppercase {{ $statusClass($incident['status']) }}">
                                    {{ $incident['status'] }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center text-sm font-semibold text-slate-500">
                            No recent incidents for this client.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-dashboard-layout>
