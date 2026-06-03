<x-dashboard-layout>
    @php
        $term = trim((string) ($query ?? ''));
        $hasQuery = $term !== '';
        $projectCount = $projects?->count() ?? 0;
        $alertCount = $alerts?->count() ?? 0;
        $incidentCount = $incidents?->count() ?? 0;
        $clientCount = $clientsResults?->count() ?? 0;
        $userCount = $usersResults?->count() ?? 0;
    @endphp

    <div class="space-y-6">
        <section class="rounded-xl border border-cyan-400/10 bg-[#07111f] p-6">
            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-cyan-300">Search</p>
            <h1 class="mt-3 text-3xl font-black tracking-tight text-white">
                {{ $hasQuery ? $term : 'Search' }}
            </h1>
            <p class="mt-2 max-w-2xl text-sm font-medium leading-6 text-slate-400">
                Find projects, alerts, incidents, clients, and users across your allowed scope.
            </p>
        </section>

        @unless($hasQuery)
            <section class="rounded-xl border border-slate-800 bg-[#07111f] p-6 text-sm text-slate-400">
                Type a term in the top bar and press Enter.
            </section>
        @else
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-xl border border-cyan-400/10 bg-[#07111f] p-5">
                    <p class="text-xs font-bold text-slate-500">Projects</p>
                    <p class="mt-3 text-2xl font-black text-white">{{ $projectCount }}</p>
                </div>
                <div class="rounded-xl border border-red-400/10 bg-[#07111f] p-5">
                    <p class="text-xs font-bold text-slate-500">Alerts</p>
                    <p class="mt-3 text-2xl font-black text-red-300">{{ $alertCount }}</p>
                </div>
                <div class="rounded-xl border border-orange-400/10 bg-[#07111f] p-5">
                    <p class="text-xs font-bold text-slate-500">Incidents</p>
                    <p class="mt-3 text-2xl font-black text-orange-300">{{ $incidentCount }}</p>
                </div>
                <div class="rounded-xl border border-cyan-400/10 bg-[#07111f] p-5">
                    <p class="text-xs font-bold text-slate-500">Clients</p>
                    <p class="mt-3 text-2xl font-black text-cyan-300">{{ $clientCount }}</p>
                </div>
                <div class="rounded-xl border border-emerald-400/10 bg-[#07111f] p-5">
                    <p class="text-xs font-bold text-slate-500">Users</p>
                    <p class="mt-3 text-2xl font-black text-emerald-300">{{ $userCount }}</p>
                </div>
            </section>

            <section class="grid gap-6 2xl:grid-cols-2">
                <div class="rounded-xl border border-cyan-400/10 bg-[#07111f]">
                    <div class="border-b border-cyan-400/10 px-5 py-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-cyan-400">Projects</p>
                        <h2 class="mt-1 text-lg font-black text-white">Matched projects</h2>
                    </div>
                    <div class="divide-y divide-slate-800">
                        @forelse ($projects as $project)
                            <a href="{{ route('projects.show', $project) }}" class="block px-5 py-4 transition hover:bg-white/[0.03]">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-black text-white">{{ $project->name ?? 'Unnamed project' }}</p>
                                        <p class="mt-1 truncate text-xs font-semibold text-cyan-200">{{ $project->domain ?? '-' }}</p>
                                        <p class="mt-2 text-xs font-semibold text-slate-500">{{ $project->client?->company_name ?? '-' }}</p>
                                    </div>
                                    <span class="rounded-md border border-slate-700 bg-slate-900 px-2 py-1 text-[10px] font-black uppercase text-slate-400">
                                        {{ $project->status ?? 'unknown' }}
                                    </span>
                                </div>
                            </a>
                        @empty
                            <div class="px-5 py-10 text-center text-sm font-semibold text-slate-500">No projects matched.</div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-xl border border-cyan-400/10 bg-[#07111f]">
                    <div class="border-b border-cyan-400/10 px-5 py-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-cyan-400">Alerts</p>
                        <h2 class="mt-1 text-lg font-black text-white">Matched alerts</h2>
                    </div>
                    <div class="divide-y divide-slate-800">
                        @forelse ($alerts as $alert)
                            <div class="flex items-start justify-between gap-4 px-5 py-4">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black text-white">{{ $alert->title ?? 'Security alert' }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">{{ $alert->project?->domain ?? $alert->project?->name ?? '-' }}</p>
                                </div>
                                <span class="rounded-md border px-2 py-1 text-[10px] font-black uppercase text-slate-300">
                                    {{ $alert->severity ?? 'medium' }}
                                </span>
                            </div>
                        @empty
                            <div class="px-5 py-10 text-center text-sm font-semibold text-slate-500">No alerts matched.</div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-xl border border-cyan-400/10 bg-[#07111f]">
                    <div class="border-b border-cyan-400/10 px-5 py-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-cyan-400">Incidents</p>
                        <h2 class="mt-1 text-lg font-black text-white">Matched incidents</h2>
                    </div>
                    <div class="divide-y divide-slate-800">
                        @forelse ($incidents as $incident)
                            <div class="flex items-start justify-between gap-4 px-5 py-4">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black text-white">{{ $incident->event ?? 'Security incident' }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">{{ $incident->site_url ?? $incident->ip ?? '-' }}</p>
                                </div>
                                <span class="rounded-md border px-2 py-1 text-[10px] font-black uppercase text-slate-300">
                                    {{ $incident->severity ?? 'medium' }}
                                </span>
                            </div>
                        @empty
                            <div class="px-5 py-10 text-center text-sm font-semibold text-slate-500">No incidents matched.</div>
                        @endforelse
                    </div>
                </div>

                @if (!($client?->id))
                    <div class="grid gap-6">
                        <div class="rounded-xl border border-cyan-400/10 bg-[#07111f]">
                            <div class="border-b border-cyan-400/10 px-5 py-4">
                                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-cyan-400">Clients</p>
                                <h2 class="mt-1 text-lg font-black text-white">Matched clients</h2>
                            </div>
                            <div class="divide-y divide-slate-800">
                                @forelse ($clientsResults as $clientRow)
                                    <a href="{{ route('clients.show', $clientRow) }}" class="block px-5 py-4 transition hover:bg-white/[0.03]">
                                        <p class="text-sm font-black text-white">{{ $clientRow->company_name ?? '-' }}</p>
                                        <p class="mt-1 text-xs font-semibold text-slate-500">{{ $clientRow->email ?? '-' }}</p>
                                    </a>
                                @empty
                                    <div class="px-5 py-10 text-center text-sm font-semibold text-slate-500">No clients matched.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="rounded-xl border border-cyan-400/10 bg-[#07111f]">
                            <div class="border-b border-cyan-400/10 px-5 py-4">
                                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-cyan-400">Users</p>
                                <h2 class="mt-1 text-lg font-black text-white">Matched users</h2>
                            </div>
                            <div class="divide-y divide-slate-800">
                                @forelse ($usersResults as $userRow)
                                    <div class="px-5 py-4">
                                        <p class="text-sm font-black text-white">{{ $userRow->name ?? '-' }}</p>
                                        <p class="mt-1 text-xs font-semibold text-slate-500">{{ $userRow->email ?? '-' }}</p>
                                    </div>
                                @empty
                                    <div class="px-5 py-10 text-center text-sm font-semibold text-slate-500">No users matched.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endif
            </section>
        @endunless
    </div>
</x-dashboard-layout>
