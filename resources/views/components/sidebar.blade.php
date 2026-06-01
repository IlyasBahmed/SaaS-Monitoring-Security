@php
$user = Auth::user();
$role = strtolower(trim((string) ($user->role ?? '')));
$isClient = $role === 'client';
$isSocAnalyst = $role === 'soc analyst';
$client = $isClient
    ? \App\Models\clients::query()->where('user_id', $user?->id)->first()
    : null;
$clientProjectIds = $client
    ? $client->projects()->pluck('id')->map(fn ($id) => (int) $id)->all()
    : [];

$openAlertsCount = rescue(function () use ($isClient, $clientProjectIds) {
    return \App\Models\Alert::query()
        ->when($isClient, fn ($query) => $query->whereIn('project_id', $clientProjectIds))
        ->where('resolved', false)
        ->count();
}, 0, false);

$openIncidentsCount = rescue(function () use ($isClient, $clientProjectIds) {
    return \App\Models\Incident::query()
        ->when($isClient, fn ($query) => $query->whereIn('project_id', $clientProjectIds))
        ->where(function ($query) {
            $query->whereNull('status')
                ->orWhereNotIn('status', ['resolved', 'closed']);
        })
        ->count();
}, 0, false);

$items = [
    ['section' => 'OVERVIEW'],
    ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'grid', 'badge' => null],

    ['section' => 'MANAGEMENT'],
    ['label' => 'Clients', 'route' => 'clients.index', 'icon' => 'users', 'badge' => null],
    ['label' => 'Projects', 'route' => 'projects.index', 'icon' => 'folder', 'badge' => null],
    ['label' => 'Agents', 'route' => 'agents.index', 'icon' => 'cpu', 'badge' => null],

    ['section' => 'SECURITY'],
    ['label' => 'Incidents', 'route' => 'incidents.index', 'icon' => 'alert', 'badge' => $openIncidentsCount, 'badgeColor' => 'red'],
    ['label' => 'Alerts', 'route' => 'alerts.index', 'icon' => 'bell', 'badge' => $openAlertsCount, 'badgeColor' => 'yellow'],
    ['label' => 'Cloudflare', 'route' => 'cloudflare.index', 'icon' => 'cloud', 'badge' => null],

    ['section' => 'PLATFORM'],
    ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'file', 'badge' => null],
    ['label' => 'Audit Logs', 'route' => 'audit-logs.index', 'icon' => 'edit', 'badge' => null],
    ['label' => 'Users & Roles', 'route' => 'users.roles', 'icon' => 'user', 'badge' => null],
];

$socAnalystRoutes = ['dashboard', 'incidents.index', 'alerts.index', 'cloudflare.index', 'audit-logs.index', 'reports.index'];
$clientDashboardUrl = route('client.dashboard');
$clientItems = [
    ['label' => 'Dashboard', 'route' => 'client.dashboard', 'icon' => 'grid', 'badge' => null],
    ['label' => 'Projects / Sites', 'route' => 'client.projects', 'icon' => 'folder', 'badge' => null],
    ['label' => 'Incidents', 'route' => 'client.incidents', 'icon' => 'alert', 'badge' => $openIncidentsCount, 'badgeColor' => 'red'],
    ['label' => 'Alerts', 'route' => 'client.alerts', 'icon' => 'bell', 'badge' => $openAlertsCount, 'badgeColor' => 'yellow'],
    ['label' => 'Reports', 'route' => 'client.reports.index', 'icon' => 'file', 'badge' => null],
    ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'user', 'badge' => null],
];

$filteredItems = $isClient ? $clientItems : [];
$pendingSection = null;

foreach ($isClient ? [] : $items as $item) {
    if (isset($item['section'])) {
        $pendingSection = $item;
        continue;
    }

    if ($isSocAnalyst && ! in_array($item['route'], $socAnalystRoutes, true)) {
        continue;
    }

    if ($pendingSection) {
        $filteredItems[] = $pendingSection;
        $pendingSection = null;
    }

    $filteredItems[] = $item;
}

$items = $filteredItems;
@endphp

<aside
    class="fixed inset-y-0 left-0 z-50 flex h-screen w-72 max-w-[calc(100vw-2rem)] shrink-0 flex-col overflow-hidden border-r border-cyan-100 bg-white text-slate-700 shadow-xl shadow-slate-200/50 transition-transform duration-200 ease-out [height:100dvh] lg:sticky lg:top-0 lg:z-auto lg:h-screen lg:w-64 lg:translate-x-0 dark:border-cyan-500/10 dark:bg-[#020617] dark:text-slate-300 dark:shadow-none"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
>

    <div class="p-6">
        <div class="flex items-center gap-3">
            <div class="h-11 w-11 rounded-xl bg-cyan-50 border border-cyan-200 flex items-center justify-center shadow-lg shadow-cyan-100/70 dark:bg-cyan-400/15 dark:border-cyan-400/30 dark:shadow-cyan-500/20">
                <svg class="w-6 h-6 text-cyan-700 dark:text-cyan-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>

            <div>
                <h1 class="text-sm font-black text-slate-950 tracking-wide dark:text-white">
                    Cyber<span class="text-cyan-600 dark:text-cyan-300">Shield</span>
                </h1>
                <p class="text-[9px] text-cyan-600 tracking-[0.25em] uppercase dark:text-cyan-400">
                    Enterprise SOC
                </p>
            </div>
        </div>
    </div>

    <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto px-4 py-2 pr-2
                [scrollbar-width:thin] [scrollbar-color:rgba(34,211,238,0.35)_transparent]
                [&::-webkit-scrollbar]:w-1.5
                [&::-webkit-scrollbar-track]:bg-transparent
                [&::-webkit-scrollbar-thumb]:rounded-full
                [&::-webkit-scrollbar-thumb]:bg-cyan-400/30
                hover:[&::-webkit-scrollbar-thumb]:bg-cyan-400/50">

        @foreach ($items as $item)
            @if (isset($item['section']))
                <p class="px-3 pt-6 pb-2 text-[10px] font-bold tracking-[0.22em] text-slate-400 uppercase dark:text-slate-600">
                    {{ $item['section'] }}
                </p>
            @else
                @php
                    $routeName = $item['route'] ?? null;
                    $routePattern = $routeName ? $routeName . '*' : null;
                    $isActive = $routePattern ? request()->routeIs($routePattern) : false;
                    $href = $item['href'] ?? ($routeName && Route::has($routeName) ? route($routeName) : '#');
                @endphp

                <a href="{{ $href }}"
                   @click="sidebarOpen = false"
                   class="group flex items-center justify-between rounded-xl px-3 py-2.5 text-sm transition-all duration-200
                   {{ $isActive
                        ? 'bg-cyan-50 text-cyan-800 border border-cyan-200 shadow-lg shadow-cyan-100/70 dark:bg-cyan-400/10 dark:text-cyan-100 dark:border-cyan-400/20 dark:shadow-cyan-500/5'
                        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-400 dark:hover:bg-slate-800/60 dark:hover:text-white'
                   }}">

                    <span class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg transition-all duration-200
                            {{ $isActive
                                ? 'bg-cyan-100 text-cyan-700 shadow-lg shadow-cyan-100/70 dark:bg-cyan-400/15 dark:text-cyan-300 dark:shadow-[0_0_18px_rgba(34,211,238,0.25)]'
                                : 'bg-slate-100 text-slate-500 group-hover:bg-cyan-50 group-hover:text-cyan-700 group-hover:shadow-lg group-hover:shadow-cyan-100/60 dark:bg-slate-900/60 dark:text-slate-500 dark:group-hover:bg-cyan-400/10 dark:group-hover:text-cyan-300 dark:group-hover:shadow-[0_0_14px_rgba(34,211,238,0.18)]'
                            }}">

                            @switch($item['icon'])
                                @case('grid')
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                        <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                                        <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                                        <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                                        <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                                    </svg>
                                    @break

                                @case('users')
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                                    </svg>
                                    @break

                                @case('folder')
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h7l2 2h9v9.5A2.5 2.5 0 0 1 18.5 21h-13A2.5 2.5 0 0 1 3 18.5V7z"/>
                                    </svg>
                                    @break

                                @case('cpu')
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                        <rect x="5" y="5" width="14" height="14" rx="2"/>
                                        <rect x="9" y="9" width="6" height="6" rx="1"/>
                                        <path stroke-linecap="round" d="M9 2v3M15 2v3M9 19v3M15 19v3M2 9h3M2 15h3M19 9h3M19 15h3"/>
                                    </svg>
                                    @break

                                @case('shield')
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                    </svg>
                                    @break

                                @case('alert')
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                                        <path stroke-linecap="round" d="M12 9v4"/>
                                        <path stroke-linecap="round" d="M12 17h.01"/>
                                    </svg>
                                    @break

                                @case('bell')
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
                                        <path stroke-linecap="round" d="M13.73 21a2 2 0 0 1-3.46 0"/>
                                    </svg>
                                    @break

                                @case('cloud')
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.5 19H7a4 4 0 0 1-.7-7.94A6 6 0 0 1 18 9.5a4.75 4.75 0 0 1-.5 9.5z"/>
                                    </svg>
                                    @break

                                @case('file')
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 2v6h6M8 13h8M8 17h5"/>
                                    </svg>
                                    @break

                                @case('edit')
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>
                                    </svg>
                                    @break

                                @case('user')
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                                        <circle cx="12" cy="8" r="4"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 21a8 8 0 0 1 16 0"/>
                                    </svg>
                                    @break
                            @endswitch
                        </span>

                        <span class="font-semibold tracking-tight">
                            {{ $item['label'] }}
                        </span>
                    </span>

                    @if(array_key_exists('badge', $item) && $item['badge'] !== null)
                        <span class="flex h-5 min-w-[20px] items-center justify-center rounded-full px-1.5 text-[10px] font-bold ring-1 ring-inset
                            {{ ($item['badgeColor'] ?? '') === 'red'
                                ? 'bg-red-500/10 text-red-400 ring-red-500/30'
                                : 'bg-amber-500/10 text-amber-400 ring-amber-500/30'
                            }}">
                            {{ $item['badge'] > 99 ? '99+' : $item['badge'] }}
                        </span>
                    @endif
                </a>
            @endif
        @endforeach
    </nav>

    <div class="m-4 shrink-0 rounded-xl bg-slate-50 border border-cyan-100 p-4 dark:bg-slate-900/50 dark:border-cyan-400/10">
        <div class="flex items-center gap-3">
            <div class="h-8 w-8 rounded-full bg-cyan-50 border border-cyan-200 flex items-center justify-center text-[10px] font-bold text-cyan-700 dark:bg-cyan-500/20 dark:border-cyan-500/40 dark:text-cyan-300">
                {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="text-xs font-bold text-slate-900 dark:text-white">
                    {{ Auth::user()->name ?? 'Admin' }}
                </p>
                <p class="text-[10px] text-slate-500 dark:text-slate-500">
                    {{ Auth::user()->role ?? 'User' }}
                </p>
            </div>
        </div>
    </div>
</aside>
