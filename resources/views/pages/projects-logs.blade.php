<x-dashboard-layout>
    @php
        $severityClass = static function (string $severity): string {
            return [
                'critical' => 'border-red-400/20 bg-red-400/10 text-red-300',
                'high' => 'border-orange-400/20 bg-orange-400/10 text-orange-300',
                'medium' => 'border-amber-400/20 bg-amber-400/10 text-amber-300',
                'low' => 'border-cyan-400/20 bg-cyan-400/10 text-cyan-300',
                'info' => 'border-blue-400/20 bg-blue-400/10 text-blue-300',
            ][$severity] ?? 'border-slate-700 bg-slate-900 text-slate-300';
        };

        $sourceClass = static function (string $source): string {
            return $source === 'Agent'
                ? 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300'
                : 'border-cyan-400/20 bg-cyan-400/10 text-cyan-300';
        };
    @endphp

    <div class="space-y-6">
        <section class="rounded-xl border border-cyan-400/10 bg-[#07111f] p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-cyan-300">Project Logs</p>
                    <h1 class="mt-3 text-3xl font-black tracking-tight text-white">
                        {{ $project->domain ?: $project->name }}
                    </h1>
                    <p class="mt-2 text-sm font-medium text-slate-400">
                        {{ $project->client->company_name ?? 'No client' }} / latest activity from audit and agent logs.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('projects.show', $project) }}"
                       class="inline-flex h-10 items-center rounded-lg border border-slate-700 px-4 text-xs font-bold text-slate-400 transition hover:border-cyan-400/30 hover:text-cyan-300">
                        Back to Project
                    </a>
                    <a href="{{ route('projects.index') }}"
                       class="inline-flex h-10 items-center rounded-lg border border-cyan-400/20 bg-cyan-400/10 px-4 text-xs font-bold text-cyan-300 transition hover:bg-cyan-400/20">
                        Projects
                    </a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-xl border border-cyan-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Total Logs</p>
                <p class="mt-3 text-2xl font-black text-white">{{ $stats['total'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-emerald-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Agent Logs</p>
                <p class="mt-3 text-2xl font-black text-emerald-300">{{ $stats['agent'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-cyan-400/10 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Audit Logs</p>
                <p class="mt-3 text-2xl font-black text-cyan-300">{{ $stats['audit'] ?? 0 }}</p>
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

        <section class="rounded-xl border border-cyan-400/10 bg-[#07111f]">
            <div class="flex flex-col gap-2 border-b border-cyan-400/10 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-cyan-400">Activity</p>
                    <h2 class="mt-1 text-lg font-black text-white">Project log stream</h2>
                </div>
                <p class="text-xs font-bold text-slate-500">Latest: {{ $stats['latest_human'] ?? '-' }}</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1040px]">
                    <thead class="text-left text-[10px] uppercase tracking-[0.18em] text-slate-500">
                        <tr class="border-b border-slate-800">
                            <th class="px-5 py-4">Event</th>
                            <th class="px-5 py-4">Source</th>
                            <th class="px-5 py-4">Severity</th>
                            <th class="px-5 py-4">Actor</th>
                            <th class="px-5 py-4">Target</th>
                            <th class="px-5 py-4">IP</th>
                            <th class="px-5 py-4">Metadata</th>
                            <th class="px-5 py-4">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-white/[0.03]">
                                <td class="max-w-md px-5 py-4">
                                    <p class="font-black text-white">{{ $log['event_label'] }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">{{ $log['category_label'] }}</p>
                                    <p class="mt-2 truncate text-[10px] font-black uppercase tracking-[0.16em] text-slate-600">{{ $log['site_url'] }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-md border px-2 py-1 text-[10px] font-black uppercase {{ $sourceClass($log['source']) }}">
                                        {{ $log['source'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-md border px-2 py-1 text-[10px] font-black uppercase {{ $severityClass($log['severity']) }}">
                                        {{ $log['severity'] }}
                                    </span>
                                </td>
                                <td class="max-w-xs px-5 py-4 text-sm font-semibold text-slate-400">
                                    <span class="line-clamp-2">{{ $log['actor'] }}</span>
                                </td>
                                <td class="max-w-xs px-5 py-4 text-sm font-semibold text-slate-400">
                                    <span class="line-clamp-2">{{ $log['target'] }}</span>
                                </td>
                                <td class="px-5 py-4 font-mono text-sm font-semibold text-slate-400">{{ $log['ip'] }}</td>
                                <td class="px-5 py-4 text-sm font-semibold text-slate-500">
                                    {{ $log['metadata_count'] }} fields
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-sm font-semibold text-slate-400">{{ $log['created_time'] }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-600">{{ $log['created_human'] }}</p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center text-sm font-semibold text-slate-500">
                                    No logs are linked to this project yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-dashboard-layout>
