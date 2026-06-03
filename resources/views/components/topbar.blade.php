@php
    $user = Auth::user();
    $isClient = strtolower(trim((string) ($user->role ?? ''))) === 'client';
@endphp

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">

    <button type="button"
            @click="sidebarOpen = true"
            data-sound="click"
            aria-label="Open navigation"
            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-cyan-100 bg-white text-slate-500 shadow-lg shadow-slate-200/50 transition hover:border-cyan-300 hover:bg-cyan-50 hover:text-cyan-700 md:hidden dark:border-cyan-400/10 dark:bg-[#020617] dark:text-slate-400 dark:shadow-none dark:hover:border-cyan-400/30 dark:hover:bg-cyan-400/5 dark:hover:text-cyan-300">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16"/>
        </svg>
    </button>

    <form method="GET" action="{{ route('search') }}" class="hidden min-w-0 flex-1 md:flex md:max-w-xl">
        <label class="relative flex w-full items-center">
            <span class="pointer-events-none absolute left-4 flex h-4 w-4 items-center justify-center text-slate-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z"/>
                </svg>
            </span>
            <input
                type="search"
                name="q"
                value="{{ request('q') }}"
                placeholder="Search alerts, projects, users..."
                aria-label="Search"
                class="h-11 w-full rounded-2xl border border-cyan-100 bg-white pl-11 pr-4 text-sm text-slate-700 shadow-lg shadow-slate-200/40 outline-none transition placeholder:text-slate-400 focus:border-cyan-300 focus:bg-cyan-50/30 focus:ring-2 focus:ring-cyan-200/40 dark:border-cyan-400/10 dark:bg-[#020617] dark:text-slate-100 dark:shadow-none dark:placeholder:text-slate-500 dark:focus:border-cyan-400/30 dark:focus:bg-[#07111f] dark:focus:ring-cyan-400/10"
            />
            <button type="submit" class="absolute right-2 inline-flex h-8 w-8 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-600 transition hover:bg-cyan-500/20 hover:text-cyan-500 dark:text-cyan-300 dark:hover:bg-cyan-400/15" aria-label="Search">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z"/>
                </svg>
            </button>
        </label>
    </form>

    {{-- Right --}}
    <div class="flex shrink-0 items-center gap-2 sm:gap-3">

        {{-- Status --}}
        <div class="hidden lg:flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700 dark:border-emerald-400/10 dark:bg-emerald-400/5 dark:text-emerald-300">
            <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
            System Active
        </div>

        {{-- Sound --}}
        <button type="button"
                data-sound="click"
                @click="soundEnabled = !soundEnabled"
                :aria-pressed="soundEnabled ? 'true' : 'false'"
                :aria-label="soundEnabled ? 'Mute sound' : 'Enable sound'"
                class="group h-11 w-11 flex items-center justify-center rounded-2xl border border-cyan-100 bg-white text-slate-500 shadow-lg shadow-slate-200/50 transition hover:border-cyan-300 hover:bg-cyan-50 hover:text-cyan-700 dark:border-cyan-400/10 dark:bg-[#020617] dark:text-slate-400 dark:shadow-none dark:hover:border-cyan-400/30 dark:hover:bg-cyan-400/5 dark:hover:text-cyan-300">
            <svg x-show="soundEnabled" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5 6.5 9.5H3v5h3.5L11 19V5Z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.5 8.5a4 4 0 0 1 0 7"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.5 6.5a7 7 0 0 1 0 11"/>
            </svg>
            <svg x-show="!soundEnabled" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5 6.5 9.5H3v5h3.5L11 19V5Z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="m16 9 5 5"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 9-5 5"/>
            </svg>
        </button>

        {{-- Theme --}}
        <button type="button"
                @click="darkMode = !darkMode"
                data-sound="click"
                :aria-label="darkMode ? 'Switch to light mode' : 'Switch to dark mode'"
                class="group h-11 w-11 flex items-center justify-center rounded-2xl border border-cyan-100 bg-white text-slate-500
                       shadow-lg shadow-slate-200/50 transition
                       hover:border-cyan-300 hover:bg-cyan-50 hover:text-cyan-700
                       dark:border-cyan-400/10 dark:bg-[#020617] dark:text-slate-400 dark:shadow-none
                       dark:hover:border-cyan-400/30 dark:hover:bg-cyan-400/5 dark:hover:text-cyan-300 dark:hover:shadow-[0_0_22px_rgba(34,211,238,0.18)]">
            <svg x-show="darkMode" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2M4.22 4.22l1.42 1.42m12.72 12.72 1.42 1.42M3 12h2m14 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                <circle cx="12" cy="12" r="4"/>
            </svg>
            <svg x-show="!darkMode" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8A8.5 8.5 0 1 1 11.2 3a6.5 6.5 0 0 0 9.8 9.8z"/>
            </svg>
        </button>

        {{-- Alerts --}}
        <div x-data="{ open: false }" class="relative">
            <button type="button"
                    @click="open = !open"
                    data-sound="click"
                    :aria-expanded="open"
                    aria-label="Open alerts"
                    class="relative group h-11 w-11 flex items-center justify-center rounded-2xl bg-white border border-cyan-100
                           shadow-lg shadow-slate-200/50 transition
                           hover:border-cyan-300 hover:bg-cyan-50 hover:shadow-cyan-100/60
                           dark:bg-[#020617] dark:border-cyan-400/10 dark:shadow-none
                           dark:hover:border-cyan-400/30 dark:hover:bg-cyan-400/5 dark:hover:shadow-[0_0_22px_rgba(34,211,238,0.18)]">
                <svg class="w-5 h-5 text-slate-500 group-hover:text-cyan-700 transition dark:text-slate-400 dark:group-hover:text-cyan-300"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path stroke-linecap="round" d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>

                @if (($openAlertsCount ?? 0) > 0)
                    <span class="absolute -top-1 -right-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white ring-2 ring-white shadow shadow-red-500/40 dark:ring-[#020617]">
                        {{ ($openAlertsCount ?? 0) > 99 ? '99+' : $openAlertsCount }}
                    </span>
                @endif
            </button>

            <div
                x-show="open"
                x-cloak
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                @click.outside="open = false"
                class="absolute right-0 z-50 mt-3 w-[22rem] max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-cyan-100 bg-white/95 shadow-2xl shadow-slate-200/80 backdrop-blur-xl dark:border-cyan-400/10 dark:bg-[#020617]/95 dark:shadow-cyan-950/30"
            >
                <div class="flex items-center justify-between gap-3 border-b border-cyan-100 px-4 py-3 dark:border-cyan-400/10">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-cyan-600 dark:text-cyan-400">Alerts</p>
                        <p class="mt-1 text-sm font-black text-slate-900 dark:text-white">{{ $openAlertsCount ?? 0 }} open alerts</p>
                    </div>
                    <a href="{{ $isClient ? route('client.alerts') : route('alerts.index') }}"
                       class="rounded-lg border border-cyan-200 bg-cyan-50 px-3 py-2 text-xs font-black text-cyan-700 transition hover:bg-cyan-100 dark:border-cyan-400/20 dark:bg-cyan-400/10 dark:text-cyan-300 dark:hover:bg-cyan-400/15">
                        View all
                    </a>
                </div>

                <div class="max-h-96 overflow-y-auto p-2 [scrollbar-width:thin] [scrollbar-color:rgba(34,211,238,0.35)_transparent]">
                    @forelse (($recentAlerts ?? collect()) as $alert)
                        @php
                            $severityClass = [
                                'critical' => 'border-red-400/20 bg-red-400/10 text-red-300',
                                'high' => 'border-orange-400/20 bg-orange-400/10 text-orange-300',
                                'medium' => 'border-amber-400/20 bg-amber-400/10 text-amber-300',
                                'low' => 'border-cyan-400/20 bg-cyan-400/10 text-cyan-300',
                            ][$alert['severity']] ?? 'border-slate-700 bg-slate-900 text-slate-300';
                        @endphp
                        <a href="{{ $isClient ? route('client.alerts') : route('alerts.index') }}"
                           class="block rounded-xl px-3 py-3 transition hover:bg-cyan-50 dark:hover:bg-white/[0.04]"
                           @click="open = false">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black text-slate-900 dark:text-white">{{ $alert['title'] }}</p>
                                    <p class="mt-1 truncate text-xs font-semibold text-slate-500">{{ $alert['project'] }} / {{ $alert['time'] }}</p>
                                </div>
                                <span class="shrink-0 rounded-md border px-2 py-1 text-[10px] font-black uppercase {{ $severityClass }}">
                                    {{ $alert['severity'] }}
                                </span>
                            </div>
                            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-900">
                                <div class="h-full rounded-full bg-cyan-400" style="width: {{ max(6, min(100, (int) $alert['score'])) }}%"></div>
                            </div>
                        </a>
                    @empty
                        <div class="px-4 py-8 text-center">
                            <p class="text-sm font-black text-slate-500 dark:text-slate-400">No open alerts</p>
                            <p class="mt-1 text-xs font-semibold text-slate-400 dark:text-slate-600">New AI findings will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Profile Dropdown --}}
        <div x-data="{ open: false }" class="relative">
            <button type="button"
                    @click="open = !open"
                    data-sound="click"
                    class="group flex items-center gap-3 rounded-2xl bg-white border border-cyan-100 px-3 py-2
                           shadow-lg shadow-slate-200/50 transition
                           hover:border-cyan-300 hover:bg-cyan-50 hover:shadow-cyan-100/60
                           dark:bg-[#020617] dark:border-cyan-400/10 dark:shadow-none
                           dark:hover:border-cyan-400/30 dark:hover:bg-cyan-400/5 dark:hover:shadow-[0_0_22px_rgba(34,211,238,0.15)]">

                <div class="relative">
                    <div class="h-10 w-10 rounded-2xl bg-cyan-50 border border-cyan-200 flex items-center justify-center
                                text-cyan-700 font-bold text-sm shadow-lg shadow-cyan-100/70
                                dark:bg-cyan-400/10 dark:border-cyan-400/30 dark:text-cyan-300 dark:shadow-[0_0_14px_rgba(34,211,238,0.22)]">
                        {{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}
                    </div>
                    <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full bg-emerald-400 ring-2 ring-white dark:ring-[#020617]"></span>
                </div>

                <div class="hidden sm:block text-left leading-tight">
                    <p class="text-sm font-semibold text-slate-900 max-w-32 truncate dark:text-white">
                        {{ $user->name ?? 'Admin' }}
                    </p>
                    <p class="text-[11px] text-slate-500 max-w-32 truncate dark:text-slate-500">
                        {{ $user->role ?? 'User' }}
                    </p>
                </div>

                <svg class="w-4 h-4 text-slate-500 transition duration-200 group-hover:text-cyan-300"
                     :class="open ? 'rotate-180 text-cyan-300' : ''"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/>
                </svg>
            </button>

            {{-- Dropdown --}}
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                 @click.outside="open = false"
                 class="absolute right-0 mt-3 w-64 overflow-hidden rounded-2xl border border-cyan-100 bg-white/95 shadow-2xl shadow-slate-200/80 backdrop-blur-xl z-50
                        dark:border-cyan-400/10 dark:bg-[#020617]/95 dark:shadow-cyan-950/30">

                <div class="p-4 border-b border-cyan-100 dark:border-cyan-400/10">
                    <div class="flex items-center gap-3">
                        <div class="h-11 w-11 rounded-2xl bg-cyan-50 border border-cyan-200 flex items-center justify-center text-cyan-700 font-bold dark:bg-cyan-400/10 dark:border-cyan-400/30 dark:text-cyan-300">
                            {{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-900 truncate dark:text-white">{{ $user->name ?? 'Admin' }}</p>
                            <p class="text-xs text-slate-500 truncate dark:text-slate-500">{{ $user->email ?? '' }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-2">
                    <a href="{{ route('settings.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-600 hover:bg-cyan-50 hover:text-cyan-700 transition
                              dark:text-slate-300 dark:hover:bg-white/[0.04] dark:hover:text-cyan-300">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 1.55V21a2 2 0 1 1-4 0v-.05a1.7 1.7 0 0 0-1-1.55 1.7 1.7 0 0 0-1.88.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-1.55-1H3a2 2 0 1 1 0-4h.05a1.7 1.7 0 0 0 1.55-1 1.7 1.7 0 0 0-.34-1.88l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-1.55V3a2 2 0 1 1 4 0v.05a1.7 1.7 0 0 0 1 1.55 1.7 1.7 0 0 0 1.88-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9c.24.62.84 1 1.55 1H21a2 2 0 1 1 0 4h-.05A1.7 1.7 0 0 0 19.4 15z"/>
                        </svg>
                        Settings
                    </a>

                    <div class="my-2 border-t border-cyan-100 dark:border-cyan-400/10"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-red-400 hover:bg-red-500/10 transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5-5-5"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H3"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 3v18"/>
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
