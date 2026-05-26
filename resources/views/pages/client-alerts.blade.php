<x-dashboard-layout>
    @php
        $clientName = $client?->company_name ?: (Auth::user()->name ?? 'Client');
        $severityClass = static function (string $severity): string {
            return [
                'critical' => 'border-red-400/20 bg-red-400/10 text-red-300',
                'high' => 'border-orange-400/20 bg-orange-400/10 text-orange-300',
                'medium' => 'border-amber-400/20 bg-amber-400/10 text-amber-300',
                'low' => 'border-cyan-400/20 bg-cyan-400/10 text-cyan-300',
                'info' => 'border-blue-400/20 bg-blue-400/10 text-blue-300',
            ][$severity] ?? 'border-slate-700 bg-slate-900 text-slate-300';
        };
        $statusClass = static function (string $status): string {
            return [
                'open' => 'border-red-400/20 bg-red-400/10 text-red-300',
                'resolved' => 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300',
            ][$status] ?? 'border-slate-700 bg-slate-900 text-slate-300';
        };
        $scoreTone = static function (int $score): string {
            if ($score >= 85) {
                return 'text-red-300';
            }

            if ($score >= 65) {
                return 'text-orange-300';
            }

            if ($score >= 40) {
                return 'text-amber-300';
            }

            return 'text-cyan-300';
        };
    @endphp

    <div class="space-y-6">
        <section class="rounded-xl border border-cyan-400/10 bg-[#07111f] p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-cyan-300">Alerts</p>
                    <h1 class="mt-3 text-3xl font-black tracking-tight text-white">{{ $clientName }}</h1>
                    <p class="mt-2 max-w-2xl text-sm font-medium leading-6 text-slate-400">
                        Security alerts detected on projects and sites linked to your client profile.
                    </p>
                </div>

                <span class="inline-flex h-10 items-center rounded-lg border border-slate-700 bg-slate-950/40 px-4 text-xs font-black text-slate-400">
                    Consultation only
                </span>
            </div>
        </section>

        @if (! $client)
            <section class="rounded-xl border border-amber-400/20 bg-amber-400/10 p-5">
                <h2 class="text-lg font-black text-amber-200">Client profile not linked</h2>
                <p class="mt-2 text-sm font-medium text-amber-100/80">
                    Your user account is active, but no client record is attached yet.
                </p>
            </section>
        @endif

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            <div class="rounded-xl border border-cyan-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Total Alerts</p>
                <p class="mt-3 text-2xl font-black text-white">{{ $stats['total'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-red-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Open</p>
                <p class="mt-3 text-2xl font-black text-red-300">{{ $stats['open'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-emerald-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Resolved</p>
                <p class="mt-3 text-2xl font-black text-emerald-300">{{ $stats['resolved'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-red-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Critical</p>
                <p class="mt-3 text-2xl font-black text-red-300">{{ $stats['critical'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-orange-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">High</p>
                <p class="mt-3 text-2xl font-black text-orange-300">{{ $stats['high'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-blue-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Avg Score</p>
                <p class="mt-3 text-2xl font-black text-blue-300">{{ $stats['average_score'] ?? 0 }}</p>
            </div>
        </section>

        <section class="grid gap-6 2xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="rounded-xl border border-cyan-400/10 bg-[#07111f]">
                <div class="flex flex-col gap-2 border-b border-cyan-400/10 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-cyan-400">Alert Center</p>
                        <h2 class="mt-1 text-lg font-black text-white">Security alerts</h2>
                    </div>
                    <p class="text-xs font-bold text-slate-500">Latest: {{ $stats['latest_human'] ?? '-' }}</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[980px]">
                        <thead class="text-left text-[10px] uppercase tracking-[0.18em] text-slate-500">
                            <tr class="border-b border-slate-800">
                                <th class="px-5 py-4">Alert</th>
                                <th class="px-5 py-4">Project</th>
                                <th class="px-5 py-4">Severity</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4">Score</th>
                                <th class="px-5 py-4">Evidence</th>
                                <th class="px-5 py-4">SLA</th>
                                <th class="px-5 py-4">Detected</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse ($alerts as $alert)
                                <tr class="hover:bg-white/[0.03]">
                                    <td class="max-w-md px-5 py-4">
                                        <p class="font-black text-white">{{ $alert['title'] }}</p>
                                        <p class="mt-1 line-clamp-2 text-xs font-semibold leading-5 text-slate-500">{{ $alert['summary'] }}</p>
                                        <p class="mt-2 text-[10px] font-black uppercase tracking-[0.16em] text-slate-600">{{ $alert['type_label'] }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="text-sm font-black text-cyan-200">{{ $alert['project'] }}</p>
                                        <p class="mt-1 text-xs font-semibold text-slate-500">{{ $alert['project_name'] }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-md border px-2 py-1 text-[10px] font-black uppercase {{ $severityClass($alert['severity']) }}">
                                            {{ $alert['severity'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-md border px-2 py-1 text-[10px] font-black uppercase {{ $statusClass($alert['status']) }}">
                                            {{ $alert['status'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="w-10 text-sm font-black {{ $scoreTone($alert['score']) }}">{{ $alert['score'] }}</span>
                                            <div class="h-2 w-28 overflow-hidden rounded-full bg-slate-900">
                                                <div class="h-full rounded-full bg-cyan-400" style="width: {{ max(6, min(100, (int) $alert['score'])) }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-sm font-semibold text-slate-400">
                                        {{ $alert['evidence_count'] }} evidence / {{ $alert['recommendation_count'] }} actions
                                    </td>
                                    <td class="px-5 py-4 text-sm font-black text-amber-300">{{ $alert['sla'] }}</td>
                                    <td class="px-5 py-4">
                                        <p class="text-sm font-semibold text-slate-400">{{ $alert['detected_time'] }}</p>
                                        <p class="mt-1 text-xs font-semibold text-slate-600">{{ $alert['detected_human'] }}</p>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-12 text-center text-sm font-semibold text-slate-500">
                                        No alerts are linked to this client profile yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <aside class="rounded-xl border border-cyan-400/10 bg-[#07111f]">
                <div class="border-b border-cyan-400/10 px-5 py-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-cyan-400">Scope</p>
                    <h2 class="mt-1 text-lg font-black text-white">Monitored projects</h2>
                </div>

                <div class="divide-y divide-slate-800">
                    @forelse ($projects->take(10) as $project)
                        <div class="px-5 py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black text-white">{{ $project['name'] }}</p>
                                    <p class="mt-1 truncate text-xs font-semibold text-cyan-200">{{ $project['domain'] }}</p>
                                </div>
                                <span class="shrink-0 rounded-md border border-slate-700 bg-slate-900 px-2 py-1 text-[10px] font-black uppercase text-slate-400">
                                    {{ $project['status'] }}
                                </span>
                            </div>
                            <p class="mt-2 text-xs font-semibold text-slate-500">{{ $project['stack'] }}</p>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center text-sm font-semibold text-slate-500">
                            No projects are linked to this client profile yet.
                        </div>
                    @endforelse
                </div>
            </aside>
        </section>
    </div>
</x-dashboard-layout>
