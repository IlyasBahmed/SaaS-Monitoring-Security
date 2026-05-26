<x-dashboard-layout>
    @php
        $clientName = $client?->company_name ?: (Auth::user()->name ?? 'Client');
        $averageScore = (int) ($stats['average_score'] ?? 0);
        $scoreTone = $averageScore >= 85
            ? 'text-emerald-300'
            : ($averageScore >= 65 ? 'text-amber-300' : 'text-red-300');
        $statusClass = static function (string $status): string {
            return [
                'active' => 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300',
                'warning' => 'border-amber-400/20 bg-amber-400/10 text-amber-300',
                'offline' => 'border-red-400/20 bg-red-400/10 text-red-300',
                'inactive' => 'border-slate-700 bg-slate-900 text-slate-300',
            ][$status] ?? 'border-slate-700 bg-slate-900 text-slate-300';
        };
    @endphp

    <div class="space-y-6">
        <section class="rounded-xl border border-cyan-400/10 bg-[#07111f] p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-cyan-300">Projects / Sites</p>
                    <h1 class="mt-3 text-3xl font-black tracking-tight text-white">{{ $clientName }}</h1>
                    <p class="mt-2 max-w-2xl text-sm font-medium leading-6 text-slate-400">
                        Read-only inventory of protected projects and monitored sites linked to your account.
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

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-xl border border-cyan-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Total Sites</p>
                <p class="mt-3 text-2xl font-black text-white">{{ $stats['total'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-emerald-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Active</p>
                <p class="mt-3 text-2xl font-black text-emerald-300">{{ $stats['active'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-red-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Needs Review</p>
                <p class="mt-3 text-2xl font-black text-red-300">{{ $stats['offline'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-blue-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Cloudflare</p>
                <p class="mt-3 text-2xl font-black text-blue-300">{{ $stats['cloudflare'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-amber-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Avg Score</p>
                <p class="mt-3 text-2xl font-black {{ $scoreTone }}">{{ $averageScore }}</p>
            </div>
        </section>

        <section class="rounded-xl border border-cyan-400/10 bg-[#07111f]">
            <div class="flex items-center justify-between gap-4 border-b border-cyan-400/10 px-5 py-4">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-cyan-400">Inventory</p>
                    <h2 class="mt-1 text-lg font-black text-white">Monitored sites</h2>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px]">
                    <thead class="text-left text-[10px] uppercase tracking-[0.18em] text-slate-500">
                        <tr class="border-b border-slate-800">
                            <th class="px-5 py-4">Project</th>
                            <th class="px-5 py-4">Domain</th>
                            <th class="px-5 py-4">Stack</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Score</th>
                            <th class="px-5 py-4">Signals</th>
                            <th class="px-5 py-4">Protection</th>
                            <th class="px-5 py-4">Last Seen</th>
                            <th class="px-5 py-4">View</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse ($projects as $project)
                            <tr class="hover:bg-white/[0.03]">
                                <td class="px-5 py-4">
                                    <p class="font-black text-white">{{ $project['name'] }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">{{ $project['ip_address'] }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm font-bold text-cyan-200">{{ $project['domain'] }}</td>
                                <td class="px-5 py-4 text-sm font-semibold text-slate-400">{{ $project['stack'] }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-md border px-2 py-1 text-[10px] font-black uppercase {{ $statusClass($project['status']) }}">
                                        {{ $project['status'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="w-9 text-sm font-black text-white">{{ $project['score'] }}</span>
                                        <div class="h-2 w-28 overflow-hidden rounded-full bg-slate-900">
                                            <div class="h-full rounded-full bg-cyan-400" style="width: {{ $project['score'] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-sm font-semibold text-slate-400">
                                    {{ $project['alerts'] }} alerts / {{ $project['incidents'] }} incidents
                                </td>
                                <td class="px-5 py-4 text-sm font-black {{ $project['cloudflare'] ? 'text-blue-300' : 'text-slate-500' }}">
                                    {{ $project['cloudflare'] ? 'Cloudflare' : 'Standard' }}
                                </td>
                                <td class="px-5 py-4 text-sm font-semibold text-slate-500">{{ $project['last_seen'] }}</td>
                                <td class="px-5 py-4">
                                    <a href="{{ route('client.projects.show', $project['id']) }}"
                                       class="inline-flex h-9 items-center rounded-lg border border-cyan-400/20 bg-cyan-400/10 px-3 text-xs font-black text-cyan-300 transition hover:bg-cyan-400/20">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-12 text-center text-sm font-semibold text-slate-500">
                                    No projects or sites are linked to this client profile yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-dashboard-layout>
