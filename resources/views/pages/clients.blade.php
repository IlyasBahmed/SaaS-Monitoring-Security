<x-dashboard-layout>
<div x-data="{ search: '', status: 'all' }" class="space-y-6">

    {{-- HEADER --}}
    <div class="flex items-start justify-between">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-cyan-400">Management</p>
            <h1 class="mt-2 text-3xl font-black text-white">Clients</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ $clients->count() }} total · {{ $clients->where('status','active')->count() }} active
            </p>
        </div>

        <div class="flex gap-2">
            <button class="h-9 rounded-lg border border-slate-800 bg-[#07111f] px-4 text-xs font-bold text-slate-400 hover:border-cyan-400/30 hover:text-cyan-300 transition">
                Export
            </button>

            <a href="{{ route('clients.create') }}"
                class="inline-flex h-9 items-center rounded-lg border border-cyan-400/30 bg-cyan-400/10 px-4 text-xs font-bold text-cyan-300 hover:bg-cyan-400/20 transition">
                + Add Client
            </a>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="flex items-center justify-between gap-3">
        <div class="relative w-80">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-600"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <circle cx="10.5" cy="10.5" r="7.5"/>
                <path d="m21 21-4.35-4.35"/>
            </svg>

            <input
                x-model="search"
                type="text"
                placeholder="Search clients..."
                class="h-10 w-full rounded-xl border border-slate-800 bg-[#07111f] pl-9 pr-3 text-sm text-slate-300 placeholder-slate-600 outline-none focus:border-cyan-400/40"
            >
        </div>

        <div class="flex rounded-xl border border-slate-800 bg-[#07111f] p-1">
            @foreach(['all' => 'All', 'active' => 'Active', 'warning' => 'Warning', 'critical' => 'Critical'] as $key => $label)
                <button
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

    {{-- TABLE --}}
    <div class="overflow-hidden rounded-2xl border border-cyan-400/10 bg-[#07111f] shadow-2xl shadow-black/20">
        <table class="w-full">
            <thead class="border-b border-white/5 bg-white/[0.02]">
                <tr class="text-left text-[10px] uppercase tracking-[0.2em] text-cyan-500/50">
                    <th class="px-5 py-4">Client</th>
                    <th class="px-5 py-4">Projects</th>
                    <th class="px-5 py-4">Score</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4 text-right">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-white/5">
                @forelse($clients as $client)

                    @php
                        $clientStatus = strtolower($client->status ?? 'active');
                        $score = $clientStatus === 'active' ? 94 : ($clientStatus === 'warning' ? 71 : 58);

                        $scoreLabel = $score >= 90 ? 'Healthy' : ($score >= 70 ? 'Review' : 'Risk');
                        $scoreText = $score >= 90 ? 'text-emerald-300' : ($score >= 70 ? 'text-amber-300' : 'text-red-300');
                        $scoreRing = $score >= 90 ? 'ring-emerald-400/20' : ($score >= 70 ? 'ring-amber-400/20' : 'ring-red-400/20');

                        $initials = collect(explode(' ', $client->company_name ?? 'Client'))
                            ->map(fn($p) => substr($p,0,1))
                            ->join('');
                    @endphp

                    <tr
                        x-show="
                            (status === 'all' || status === '{{ $clientStatus }}') &&
                            ('{{ strtolower(($client->company_name ?? '').' '.($client->email ?? '')) }}'.includes(search.toLowerCase()))
                        "
                        class="hover:bg-white/[0.025] transition">

                        {{-- CLIENT --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-400/80 text-sm font-black text-slate-950">
                                    {{ strtoupper(substr($initials, 0, 2)) }}
                                </div>

                                <div>
                                    <p class="font-bold text-white">{{ $client->company_name }}</p>
                                    <p class="text-xs text-slate-600">{{ $client->email ?? 'No email' }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- PROJECTS --}}
                        <td class="px-5 py-4 text-sm font-semibold text-slate-300">
                            {{ $client->projects_count ?? ($client->projects->count() ?? 0) }}
                        </td>

                        {{-- SCORE --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="relative h-14 w-14 shrink-0 rounded-full bg-[#020617] p-1 ring-1 {{ $scoreRing }}" title="{{ $scoreLabel }}">
                                    <svg class="h-full w-full -rotate-90" viewBox="0 0 40 40" aria-hidden="true">
                                        <circle
                                            class="stroke-slate-800"
                                            cx="20"
                                            cy="20"
                                            r="16"
                                            fill="none"
                                            stroke-width="4"
                                        />
                                        <circle
                                            class="stroke-current {{ $scoreText }}"
                                            cx="20"
                                            cy="20"
                                            r="16"
                                            fill="none"
                                            stroke-width="4"
                                            stroke-linecap="round"
                                            pathLength="100"
                                            stroke-dasharray="100"
                                            stroke-dashoffset="{{ 100 - $score }}"
                                        />
                                    </svg>

                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <span class="text-sm font-black {{ $scoreText }}">{{ $score }}</span>
                                    </div>
                                </div>

                                <div>
                                    <p class="text-xs font-black {{ $scoreText }}">{{ $scoreLabel }}</p>
                                    <p class="mt-0.5 text-[10px] font-medium text-slate-600">Score</p>
                                </div>
                            </div>
                        </td>

                        {{-- STATUS --}}
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-2 text-sm font-bold
                                {{ $clientStatus === 'active' ? 'text-emerald-400' : ($clientStatus === 'warning' ? 'text-amber-400' : 'text-red-400') }}">
                                <span class="h-2 w-2 rounded-full
                                    {{ $clientStatus === 'active' ? 'bg-emerald-400' : ($clientStatus === 'warning' ? 'bg-amber-400' : 'bg-red-400') }}"></span>
                                {{ ucfirst($clientStatus) }}
                            </span>
                        </td>

                        {{-- ACTIONS --}}
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('clients.show', $client) }}" class="rounded-lg border border-cyan-400/20 bg-cyan-400/10 px-3 py-1.5 text-xs font-bold text-cyan-300 hover:bg-cyan-400/20">
                                    View
                                </a>

                                <button class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-800 text-slate-500 hover:border-cyan-400/30 hover:text-cyan-300">
                                    ⧉
                                </button>
                            </div>
                        </td>

                    </tr>

                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-slate-500">
                            No clients found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    <x-pagination :paginator="$clients" />

</div>
</x-dashboard-layout>
