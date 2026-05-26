<x-dashboard-layout>
    @php
        $total = $installations->count();
        $online = $installations->where('status', 'online')->count();
        $offline = $installations->where('status', 'offline')->count();
        $pending = $installations->where('status', 'pending')->count();

        $rows = $installations->map(function ($installation) {
            $lastSeen = $installation->last_seen_at
                ? \Illuminate\Support\Carbon::parse($installation->last_seen_at)
                : null;

            return [
                'id' => $installation->id,
                'agent_name' => $installation->agent->name ?? 'Unknown Agent',
                'agent_slug' => $installation->agent->slug ?? 'unknown',
                'category' => $installation->agent->category ?? 'General',
                'project' => $installation->project->domain ?? $installation->project->name ?? '-',
                'client' => $installation->project->client->company_name ?? '-',
                'version' => $installation->version ?? '-',
                'status' => strtolower($installation->status ?? 'offline'),
                'last_seen' => $lastSeen ? $lastSeen->diffForHumans() : 'No heartbeat',
                'search' => strtolower(trim(implode(' ', [
                    $installation->agent->name ?? '',
                    $installation->agent->slug ?? '',
                    $installation->agent->category ?? '',
                    $installation->project->domain ?? '',
                    $installation->project->name ?? '',
                    $installation->project->client->company_name ?? '',
                    $installation->version ?? '',
                    $installation->status ?? '',
                ]))),
            ];
        })->values();
    @endphp

    <div
        x-data="{
            search: '',
            status: 'all',
            rows: @js($rows),
            matches(row) {
                const q = this.search.toLowerCase().trim();
                const okSearch = !q || row.search.includes(q);
                const okStatus = this.status === 'all' || row.status === this.status;
                return okSearch && okStatus;
            },
            get visibleRows() {
                return this.rows.filter((row) => this.matches(row)).length;
            }
        }"
        class="space-y-6"
    >
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-cyan-400">Management</p>
                <h1 class="mt-2 text-3xl font-black text-white">Agents</h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $total }} installed agents
                </p>
            </div>

            <button type="button"
                class="h-9 rounded-lg border border-cyan-400/30 bg-cyan-400/10 px-4 text-xs font-bold text-cyan-300 hover:bg-cyan-400/20 transition">
                Register Agent
            </button>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm font-bold text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Total Installations</p>
                <p class="mt-3 text-2xl font-black text-white">{{ $total }}</p>
            </div>

            <div class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Online</p>
                <p class="mt-3 text-2xl font-black text-emerald-300">{{ $online }}</p>
            </div>

            <div class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Pending</p>
                <p class="mt-3 text-2xl font-black text-amber-300">{{ $pending }}</p>
            </div>

            <div class="rounded-xl border border-slate-800 bg-[#07111f] p-5">
                <p class="text-xs font-bold text-slate-500">Offline</p>
                <p class="mt-3 text-2xl font-black text-red-300">{{ $offline }}</p>
            </div>
        </div>

        <div class="flex items-center justify-between gap-3">
            <div class="relative w-80">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-600"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <circle cx="10.5" cy="10.5" r="7.5"/>
                    <path d="m21 21-4.35-4.35"/>
                </svg>

                <input
                    x-model.debounce.200ms="search"
                    type="text"
                    placeholder="Search installations..."
                    class="h-10 w-full rounded-xl border border-slate-800 bg-[#07111f] pl-9 pr-3 text-sm text-slate-300 placeholder-slate-600 outline-none focus:border-cyan-400/40"
                >
            </div>

            <div class="flex rounded-xl border border-slate-800 bg-[#07111f] p-1">
                @foreach(['all' => 'All', 'online' => 'Online', 'pending' => 'Pending', 'offline' => 'Offline'] as $key => $label)
                    <button
                        type="button"
                        @click="status='{{ $key }}'"
                        class="rounded-lg px-4 py-2 text-xs font-bold transition"
                        :class="status === '{{ $key }}'
                            ? 'bg-cyan-400/10 text-cyan-300 ring-1 ring-cyan-400/30'
                            : 'text-slate-500 hover:bg-white/5 hover:text-cyan-300'">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-cyan-400/10 bg-[#07111f] shadow-2xl shadow-black/20">
            <table class="w-full">
                <thead class="border-b border-white/5 bg-white/[0.02]">
                    <tr class="text-left text-[10px] uppercase tracking-[0.2em] text-cyan-500/50">
                        <th class="px-5 py-4">Agent</th>
                        <th class="px-5 py-4">Project</th>
                        <th class="px-5 py-4">Client</th>
                        <th class="px-5 py-4">Version</th>
                        <th class="px-5 py-4">Last Seen</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-white/5">
                    @forelse($installations as $installation)
                        @php
                            $status = strtolower($installation->status ?? 'offline');

                            $statusClass = $status === 'online'
                                ? 'text-emerald-300'
                                : ($status === 'pending' ? 'text-amber-300' : 'text-red-300');

                            $dotClass = $status === 'online'
                                ? 'bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,0.9)]'
                                : ($status === 'pending' ? 'bg-amber-400' : 'bg-red-400');

                            $lastSeen = $installation->last_seen_at
                                ? \Illuminate\Support\Carbon::parse($installation->last_seen_at)->diffForHumans()
                                : 'No heartbeat';
                        @endphp

                        <tr
                            x-show="matches(rows.find((row) => row.id === {{ $installation->id }}))"
                            class="hover:bg-white/[0.025] transition"
                        >
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-cyan-400/20 bg-cyan-400/10 text-cyan-300">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                            <rect x="5" y="5" width="14" height="14" rx="2"/>
                                            <rect x="9" y="9" width="6" height="6" rx="1"/>
                                            <path stroke-linecap="round" d="M9 2v3M15 2v3M9 19v3M15 19v3M2 9h3M2 15h3M19 9h3M19 15h3"/>
                                        </svg>
                                    </div>

                                    <div>
                                        <p class="font-bold text-white">
                                            {{ $installation->agent->name ?? 'Unknown Agent' }}
                                        </p>
                                        <p class="text-xs text-slate-600">
                                            {{ $installation->agent->slug ?? 'unknown' }} / {{ $installation->agent->category ?? 'General' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <a href="{{ $installation->project ? route('projects.show', $installation->project) : '#' }}"
                                   class="text-sm font-bold text-cyan-300 hover:text-cyan-200">
                                    {{ $installation->project->domain ?? $installation->project->name ?? '-' }}
                                </a>
                            </td>

                            <td class="px-5 py-4 text-sm font-semibold text-slate-300">
                                {{ $installation->project->client->company_name ?? '-' }}
                            </td>

                            <td class="px-5 py-4 text-sm font-semibold text-slate-300">
                                {{ $installation->version ?? '-' }}
                            </td>

                            <td class="px-5 py-4 text-sm font-semibold text-slate-400">
                                {{ $lastSeen }}
                            </td>

                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-2 text-sm font-bold {{ $statusClass }}">
                                    <span class="h-2 w-2 rounded-full {{ $dotClass }}"></span>
                                    {{ ucfirst($status) }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <form method="POST" action="{{ route('agents.restart', $installation) }}">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-cyan-400/20 bg-cyan-400/10 text-cyan-300 transition hover:bg-cyan-400/20"
                                                title="Connect agent"
                                                aria-label="Connect agent">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-2.64-6.36"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 3v6h-6"/>
                                            </svg>
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('agents.off', $installation) }}">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-amber-400/20 bg-amber-400/10 text-amber-300 transition hover:bg-amber-400/20"
                                                title="Turn off agent"
                                                aria-label="Turn off agent">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v10"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.36 5.64a9 9 0 1 1-12.72 0"/>
                                            </svg>
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('agents.destroy', $installation) }}" onsubmit="return confirm('Remove this agent installation?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-400/20 bg-red-400/10 text-red-300 transition hover:bg-red-400/20"
                                                title="Remove agent"
                                                aria-label="Remove agent">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 11v6M14 11v6"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 7l1 14h10l1-14"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V4h6v3"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-500">
                                No agent installations found.
                            </td>
                        </tr>
                    @endforelse

                    @if($installations->isNotEmpty())
                        <tr x-show="visibleRows === 0" x-cloak>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-500">
                                No installations match your filters.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <x-pagination :paginator="$installations" />
    </div>
</x-dashboard-layout>
