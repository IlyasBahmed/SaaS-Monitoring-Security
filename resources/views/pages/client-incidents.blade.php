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
                'in_progress' => 'border-cyan-400/20 bg-cyan-400/10 text-cyan-300',
                'resolved' => 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300',
                'closed' => 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300',
            ][$status] ?? 'border-slate-700 bg-slate-900 text-slate-300';
        };
    @endphp

    <div class="space-y-6">
        <section class="rounded-xl border border-cyan-400/10 bg-[#07111f] p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-cyan-300">Incidents</p>
                    <h1 class="mt-3 text-3xl font-black tracking-tight text-white">{{ $clientName }}</h1>
                    <p class="mt-2 max-w-2xl text-sm font-medium leading-6 text-slate-400">
                        Security incidents detected on projects and sites linked to your client profile.
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
                <p class="text-xs font-bold text-slate-500">Total Incidents</p>
                <p class="mt-3 text-2xl font-black text-white">{{ $stats['total'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-red-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Open</p>
                <p class="mt-3 text-2xl font-black text-red-300">{{ $stats['open'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-cyan-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">In Progress</p>
                <p class="mt-3 text-2xl font-black text-cyan-300">{{ $stats['in_progress'] ?? 0 }}</p>
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
        </section>

        <section class="grid gap-6 2xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="rounded-xl border border-cyan-400/10 bg-[#07111f]">
                <div class="flex flex-col gap-2 border-b border-cyan-400/10 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-cyan-400">Response Center</p>
                        <h2 class="mt-1 text-lg font-black text-white">Security incidents</h2>
                    </div>
                    <p class="text-xs font-bold text-slate-500">Latest: {{ $stats['latest_human'] ?? '-' }}</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1040px]">
                        <thead class="text-left text-[10px] uppercase tracking-[0.18em] text-slate-500">
                            <tr class="border-b border-slate-800">
                                <th class="px-5 py-4">Incident</th>
                                <th class="px-5 py-4">Project</th>
                                <th class="px-5 py-4">Severity</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4">Target</th>
                                <th class="px-5 py-4">IP</th>
                                <th class="px-5 py-4">Assigned</th>
                                <th class="px-5 py-4">Detected</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse ($incidents as $incident)
                                <tr class="hover:bg-white/[0.03]">
                                    <td class="max-w-md px-5 py-4">
                                        <p class="font-black text-white">{{ $incident['event_label'] }}</p>
                                        <p class="mt-1 text-xs font-semibold text-slate-500">{{ $incident['category_label'] }}</p>
                                        <p class="mt-2 text-[10px] font-black uppercase tracking-[0.16em] text-slate-600">{{ $incident['site_url'] }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="text-sm font-black text-cyan-200">{{ $incident['project'] }}</p>
                                        <p class="mt-1 text-xs font-semibold text-slate-500">{{ $incident['project_name'] }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-md border px-2 py-1 text-[10px] font-black uppercase {{ $severityClass($incident['severity']) }}">
                                            {{ $incident['severity'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-md border px-2 py-1 text-[10px] font-black uppercase {{ $statusClass($incident['status']) }}">
                                            {{ str_replace('_', ' ', $incident['status']) }}
                                        </span>
                                    </td>
                                    <td class="max-w-xs px-5 py-4 text-sm font-semibold text-slate-400">
                                        <span class="line-clamp-2">{{ $incident['target'] }}</span>
                                    </td>
                                    <td class="px-5 py-4 font-mono text-sm font-semibold text-slate-400">{{ $incident['ip'] }}</td>
                                    <td class="px-5 py-4 text-sm font-semibold text-slate-400">{{ $incident['assigned'] }}</td>
                                    <td class="px-5 py-4">
                                        <p class="text-sm font-semibold text-slate-400">{{ $incident['created_time'] }}</p>
                                        <p class="mt-1 text-xs font-semibold text-slate-600">{{ $incident['created_human'] }}</p>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-12 text-center text-sm font-semibold text-slate-500">
                                        No incidents are linked to this client profile yet.
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
