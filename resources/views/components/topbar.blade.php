@php
    $user = Auth::user();
@endphp

<div class="flex items-center justify-between gap-4 mb-6">

    {{-- Search --}}
    <div class="relative w-full max-w-[30rem] group">
        {{-- Focus glow --}}
        <div class="pointer-events-none absolute -inset-[1px] rounded-[1.35rem] bg-gradient-to-r from-cyan-400/25 via-blue-500/15 to-indigo-400/25 opacity-0 blur-sm transition duration-300 group-focus-within:opacity-100"></div>

        {{-- Search Box --}}
        <div class="relative flex items-center gap-3 overflow-hidden rounded-[1.35rem] border border-slate-200
                    bg-white/85 px-3.5 py-2.5 shadow-lg shadow-slate-200/60 backdrop-blur-xl
                    transition duration-300
                    before:absolute before:inset-x-4 before:top-0 before:h-px before:bg-cyan-200/70
                    group-hover:border-cyan-300/50 group-hover:bg-white
                    focus-within:border-cyan-400/60 focus-within:bg-white
                    focus-within:ring-2 focus-within:ring-cyan-400/15
                    dark:border-cyan-400/10 dark:bg-[#020617]/85 dark:shadow-black/20
                    dark:before:bg-cyan-300/15 dark:group-hover:border-cyan-400/25 dark:group-hover:bg-[#07111f]/90
                    dark:focus-within:border-cyan-300/55 dark:focus-within:bg-[#07111f]/95">

            {{-- Icon --}}
            <div class="relative z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-cyan-200/70
                        bg-cyan-50 text-slate-500 transition duration-300
                        group-hover:border-cyan-400/20 group-hover:text-cyan-300
                        group-focus-within:border-cyan-300/50 group-focus-within:bg-cyan-100 group-focus-within:text-cyan-700
                        dark:border-cyan-400/10 dark:bg-cyan-400/[0.04] dark:group-focus-within:border-cyan-300/30
                        dark:group-focus-within:bg-cyan-400/10 dark:group-focus-within:text-cyan-200">
                <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z"/>
                </svg>
            </div>

            {{-- Input --}}
            <input
                type="search"
                aria-label="Search threats, IPs, and domains"
                placeholder="Search threats, IPs, domains..."
                class="relative z-10 min-w-0 flex-1 bg-transparent text-sm font-medium text-slate-800 outline-none
                       placeholder:text-slate-400 selection:bg-cyan-400/20 selection:text-cyan-900
                       dark:text-slate-100 dark:placeholder:text-slate-500 dark:selection:text-cyan-100"
            />

            {{-- Action --}}
            <button type="button"
                    aria-label="Submit search"
                    class="relative z-10 hidden h-8 w-8 shrink-0 items-center justify-center rounded-lg
                           bg-cyan-50 text-cyan-700 transition duration-300
                           hover:bg-cyan-100 hover:text-cyan-900
                           group-focus-within:bg-cyan-100 group-focus-within:shadow-[0_0_16px_rgba(34,211,238,0.14)]
                           dark:bg-cyan-400/10 dark:text-cyan-300 dark:hover:bg-cyan-400/15 dark:hover:text-cyan-100
                           dark:group-focus-within:bg-cyan-400/15 dark:group-focus-within:shadow-[0_0_16px_rgba(34,211,238,0.18)] md:inline-flex">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Right --}}
    <div class="flex items-center gap-3">

        {{-- Status --}}
        <div class="hidden lg:flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700 dark:border-emerald-400/10 dark:bg-emerald-400/5 dark:text-emerald-300">
            <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
            System Active
        </div>

        {{-- Theme --}}
        <button type="button"
                @click="darkMode = !darkMode"
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
        <button class="relative group h-11 w-11 flex items-center justify-center rounded-2xl bg-white border border-cyan-100
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

            <span class="absolute -top-1 -right-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white ring-2 ring-white shadow shadow-red-500/40 dark:ring-[#020617]">
                3
            </span>
        </button>

        {{-- Profile Dropdown --}}
        <div x-data="{ open: false }" class="relative">
            <button type="button"
                    @click="open = !open"
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
                    <a href="#"
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-600 hover:bg-cyan-50 hover:text-cyan-700 transition
                              dark:text-slate-300 dark:hover:bg-white/[0.04] dark:hover:text-cyan-300">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="8" r="4"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 21a8 8 0 0 1 16 0"/>
                        </svg>
                        Profile
                    </a>

                    <a href="#"
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
