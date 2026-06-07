<x-dashboard-layout>
@php
    $clientCollection = $clients instanceof \Illuminate\Pagination\AbstractPaginator
        ? $clients->getCollection()
        : collect($clients);

    $totalClients = method_exists($clients, 'total') ? $clients->total() : $clientCollection->count();
    $activeClients = $activeClients ?? $clientCollection->where('status', 'active')->count();
    $clientPaginationPages = method_exists($clients, 'lastPage') ? $clients->lastPage() : 1;
    $clientPaginationCurrent = method_exists($clients, 'currentPage') ? $clients->currentPage() : 1;
    $clientPaginationStart = max(1, $clientPaginationCurrent - 1);
    $clientPaginationEnd = min($clientPaginationPages, $clientPaginationCurrent + 1);
    $canManageClients = ! in_array(strtolower(trim((string) (Auth::user()?->role ?? ''))), ['soc analyst'], true);

    if ($clientPaginationCurrent <= 2) {
        $clientPaginationEnd = min($clientPaginationPages, 3);
    }

    if ($clientPaginationCurrent >= $clientPaginationPages - 1) {
        $clientPaginationStart = max(1, $clientPaginationPages - 2);
    }

    $clientFilterItems = $clientCollection
        ->map(function ($client) {
            $status = strtolower((string) ($client->status ?? 'pending')) === 'active'
                ? 'active'
                : 'pending';

            return [
            'status' => $status,
            'search' => strtolower(trim(implode(' ', [
                $client->company_name ?? '',
                $client->email ?? '',
                $client->phone ?? '',
                $client->address ?? '',
                $status,
            ]))),
        ];
        })
        ->values()
        ->all();
@endphp

<div
    x-data="{
        search: '',
        status: 'all',
        clients: @js($clientFilterItems),
        matchesClient(client) {
            if (!client) return false;

            const query = this.search.toLowerCase().trim();
            const matchesSearch = !query || client.search.includes(query);
            const matchesStatus = this.status === 'all' || client.status === this.status;

            return matchesSearch && matchesStatus;
        },
        get visibleClients() {
            return this.clients.filter((client) => this.matchesClient(client)).length;
        },
        clearFilters() {
            this.search = '';
            this.status = 'all';
        },
        get hasFilters() {
            return this.search.trim() !== '' || this.status !== 'all';
        }
    }"
    class="space-y-6"
>

    {{-- HEADER --}}
    <div class="flex items-start justify-between">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-cyan-400">Management</p>
            <h1 class="mt-2 text-3xl font-black text-white">Clients</h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ $totalClients }} total &middot; {{ $activeClients }} active
            </p>
        </div>

        <div class="flex gap-2">
            @if ($canManageClients)
                <a href="{{ route('clients.export') }}" class="inline-flex h-9 items-center rounded-lg border border-slate-800 bg-[#07111f] px-4 text-xs font-bold text-slate-400 hover:border-cyan-400/30 hover:text-cyan-300 transition">
                    Export
                </a>

                <a href="{{ route('clients.create') }}"
                    class="inline-flex h-9 items-center rounded-lg border border-cyan-400/30 bg-cyan-400/10 px-4 text-xs font-bold text-cyan-300 hover:bg-cyan-400/20 transition">
                    + Add Client
                </a>
            @endif
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
            @foreach(['all' => 'All', 'active' => 'Active', 'pending' => 'Pending'] as $key => $label)
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
                        $clientStatus = strtolower((string) ($client->status ?? 'pending')) === 'active'
                            ? 'active'
                            : 'pending';
                        $score = (int) ($client->global_score ?? 0);

                        $scoreLabel = $client->global_score_label ?? ($score >= 85 ? 'Healthy' : ($score >= 65 ? 'Review' : ($score >= 40 ? 'Risk' : 'Critical')));
                        $scoreSource = $client->global_score_source ?? 'Live findings';
                        $scoreText = $score >= 85 ? 'text-emerald-300' : ($score >= 65 ? 'text-amber-300' : 'text-red-300');
                        $scoreRing = $score >= 85 ? 'ring-emerald-400/20' : ($score >= 65 ? 'ring-amber-400/20' : 'ring-red-400/20');

                        $initials = collect(explode(' ', $client->company_name ?? 'Client'))
                            ->map(fn($p) => substr($p,0,1))
                            ->join('');
                    @endphp

                    <tr
                        x-show="matchesClient(clients[{{ $loop->index }}])"
                        x-cloak
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
                                    <p class="mt-0.5 text-[10px] font-medium text-slate-600">{{ $scoreSource }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- STATUS --}}
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-2 text-sm font-bold
                                {{ $clientStatus === 'active' ? 'text-emerald-400' : 'text-amber-400' }}">
                                <span class="h-2 w-2 rounded-full
                                    {{ $clientStatus === 'active' ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                                {{ ucfirst($clientStatus) }}
                            </span>
                        </td>

                        {{-- ACTIONS --}}
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('clients.show', $client) }}" class="rounded-lg border border-cyan-400/20 bg-cyan-400/10 px-3 py-1.5 text-xs font-bold text-cyan-300 hover:bg-cyan-400/20">
                                    View
                                </a>

                                @if($canManageClients && $client->user)
                                    <form method="POST" action="{{ route('clients.send-password-setup', $client) }}">
                                        @csrf
                                        <button
                                            type="submit"
                                            title="Send setup email"
                                            aria-label="Send setup email to {{ $client->company_name }}"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-800 text-slate-500 transition hover:border-cyan-400/30 hover:text-cyan-300">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5v10.5H3.75z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 7.5 7.5 5.25 7.5-5.25"/>
                                            </svg>
                                        </button>
                                    </form>
                                @elseif($canManageClients)
                                    <button
                                        type="button"
                                        disabled
                                        title="No linked user account"
                                        aria-label="No linked user account"
                                        class="flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-lg border border-slate-800 text-slate-700">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5v10.5H3.75z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 7.5 7.5 5.25 7.5-5.25"/>
                                        </svg>
                                    </button>
                                @endif
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

                @if($clientCollection->isNotEmpty())
                    <tr x-show="visibleClients === 0" x-cloak>
                        <td colspan="5" class="px-5 py-12 text-center">
                            <p class="text-sm font-bold text-slate-400">No clients match your filters.</p>
                            <button
                                type="button"
                                x-show="hasFilters"
                                @click="clearFilters()"
                                class="mt-3 rounded-lg border border-cyan-400/20 bg-cyan-400/10 px-3 py-1.5 text-xs font-bold text-cyan-300 hover:bg-cyan-400/20"
                            >
                                Clear filters
                            </button>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        @if(method_exists($clients, 'total') && $clients->total() > 0)
            <div class="flex flex-col gap-3 border-t border-white/5 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs font-semibold text-slate-500">
                    Showing
                    <span class="font-black text-slate-300">{{ $clients->firstItem() }}</span>
                    to
                    <span class="font-black text-slate-300">{{ $clients->lastItem() }}</span>
                    of
                    <span class="font-black text-slate-300">{{ $clients->total() }}</span>
                    clients
                </p>

                @if($clients->hasPages())
                    <div class="flex flex-wrap items-center gap-2">
                        @if($clients->onFirstPage())
                            <button
                                type="button"
                                disabled
                                class="inline-flex h-9 items-center rounded-lg border border-slate-800 px-3 text-xs font-bold text-slate-700"
                            >
                                Previous
                            </button>
                        @else
                            <a
                                href="{{ $clients->previousPageUrl() }}"
                                class="inline-flex h-9 items-center rounded-lg border border-slate-800 px-3 text-xs font-bold text-slate-400 transition hover:border-cyan-400/30 hover:text-cyan-300"
                            >
                                Previous
                            </a>
                        @endif

                        @if($clientPaginationStart > 1)
                            <a href="{{ $clients->url(1) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-800 text-xs font-bold text-slate-400 transition hover:border-cyan-400/30 hover:text-cyan-300">1</a>
                            @if($clientPaginationStart > 2)
                                <span class="px-1 text-xs font-bold text-slate-600">...</span>
                            @endif
                        @endif

                        @for($page = $clientPaginationStart; $page <= $clientPaginationEnd; $page++)
                            @if($page === $clientPaginationCurrent)
                                <button
                                    type="button"
                                    disabled
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-cyan-400/30 bg-cyan-400/10 text-xs font-black text-cyan-300"
                                >
                                    {{ $page }}
                                </button>
                            @else
                                <a href="{{ $clients->url($page) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-800 text-xs font-bold text-slate-400 transition hover:border-cyan-400/30 hover:text-cyan-300">{{ $page }}</a>
                            @endif
                        @endfor

                        @if($clientPaginationEnd < $clientPaginationPages)
                            @if($clientPaginationEnd < $clientPaginationPages - 1)
                                <span class="px-1 text-xs font-bold text-slate-600">...</span>
                            @endif
                            <a href="{{ $clients->url($clientPaginationPages) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-800 text-xs font-bold text-slate-400 transition hover:border-cyan-400/30 hover:text-cyan-300">{{ $clientPaginationPages }}</a>
                        @endif

                        @if($clients->hasMorePages())
                            <a
                                href="{{ $clients->nextPageUrl() }}"
                                class="inline-flex h-9 items-center rounded-lg border border-slate-800 px-3 text-xs font-bold text-slate-400 transition hover:border-cyan-400/30 hover:text-cyan-300"
                            >
                                Next
                            </a>
                        @else
                            <button
                                type="button"
                                disabled
                                class="inline-flex h-9 items-center rounded-lg border border-slate-800 px-3 text-xs font-bold text-slate-700"
                            >
                                Next
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>

</div>
</x-dashboard-layout>
