@php
    $countryCodes = ['US', 'GB', 'DE', 'FR', 'CA', 'MA', 'ES', 'NL', 'BR', 'IT', 'RU', 'CN'];
    $cloudflareSites = collect($zoneRows ?? [])
        ->values()
        ->map(function (array $zone, int $index) use ($countryCodes) {
            $linked = (bool) ($zone['linked'] ?? false);
            $wafActive = ($zone['waf'] ?? 'Off') === 'Active';

            return [
                'domain' => $zone['domain'] ?: $zone['name'],
                'client' => $zone['client'] ?? '-',
                'site_id' => 'WP-'.str_pad((string) ($zone['id'] ?? $index + 1), 4, '0', STR_PAD_LEFT),
                'project_id' => $zone['id'] ?? null,
                'zone_id' => $zone['cloudflare_zone_id'] ?? '',
                'connect_url' => $zone['connect_url'] ?? route('cloudflare.connect'),
                'sync_url' => $zone['sync_url'] ?? null,
                'analytics_url' => $zone['analytics_url'] ?? null,
                'update_url' => $zone['update_url'] ?? null,
                'disconnect_url' => $zone['disconnect_url'] ?? null,
                'cloudflare_token_saved' => (bool) ($zone['cloudflare_token_saved'] ?? false),
                'cloudflare_account_email' => $zone['cloudflare_account_email'] ?? '',
                'cloudflare_settings' => $zone['cloudflare_settings'] ?? [],
                'country' => $countryCodes[$index % count($countryCodes)],
                'type' => str_contains(strtolower((string) ($zone['stack'] ?? '')), 'woo') ? 'WooCommerce' : 'WordPress',
                'cdn' => $linked && (($zone['proxy'] ?? '') !== 'DNS only'),
                'waf' => $wafActive,
                'ssl' => $zone['ssl'] ?? ($linked ? 'Full Strict' : 'Origin exposed'),
                'login_protection' => $linked,
                'xmlrpc_blocked' => $linked,
                'bot_protection' => $linked && $wafActive,
                'last_update' => $zone['cloudflare_connected_at'] ?? ($linked ? 'Connected' : 'Not connected'),
                'cloudflare_status' => strtolower((string) ($zone['cloudflare_status'] ?? '')) === 'active' ? 'active' : 'pending',
                'name_servers' => $zone['cloudflare_nameservers'] ?? [],
                'analytics_loaded' => false,
                'analytics_error' => '',
                'analytics_warning' => '',
                'analytics_synced_at' => null,
                'traffic_bars' => [],
                'top_countries' => [],
                'top_attacking_ips' => [],
                'security_logs' => [],
                'traffic_24h' => 0,
                'threats_blocked' => 0,
                'waf_events' => 0,
                'cache_hit' => 0,
                'login_attacks' => 0,
                'xmlrpc_hits' => 0,
            ];
        })
        ->values();
@endphp

@push('styles')
    <style>
        html:not(.dark) .cloudflare-shell {
            color: #0f172a;
            background:
                radial-gradient(circle at 12% 10%, rgba(34, 211, 238, 0.10), transparent 24rem),
                radial-gradient(circle at 88% 0%, rgba(16, 185, 129, 0.10), transparent 20rem),
                linear-gradient(180deg, #f8fafc 0%, #eef4fb 100%);
        }

        html:not(.dark) .cloudflare-shell :where(.bg-slate-950, .bg-slate-900, .bg-black\/30, .bg-black\/20, .bg-slate-900\/80, .bg-slate-900\/90, .bg-slate-800\/50) {
            background-color: rgba(255, 255, 255, 0.94) !important;
            background-image: none !important;
        }

        html:not(.dark) .cloudflare-shell :where(.border-slate-800, .border-slate-800\/70, .border-slate-800\/60, .border-slate-800\/50) {
            border-color: rgba(203, 213, 225, 0.96) !important;
        }

        html:not(.dark) .cloudflare-shell :where(.text-slate-200, .text-slate-300, .text-slate-400, .text-slate-500) {
            color: #475569 !important;
        }

        html:not(.dark) .cloudflare-shell :where(.text-white) {
            color: #0f172a !important;
        }

        html:not(.dark) .cloudflare-shell :where(input, select, textarea) {
            background-color: #ffffff !important;
            border-color: rgba(203, 213, 225, 0.95) !important;
            color: #0f172a !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.86);
        }

        html:not(.dark) .cloudflare-shell :where(input, select, textarea)::placeholder {
            color: #94a3b8;
        }

        html:not(.dark) .cloudflare-shell :where(table thead) {
            background: rgba(248, 250, 252, 0.96) !important;
            color: #475569 !important;
        }

        html:not(.dark) .cloudflare-shell :where(th, td) {
            border-color: rgba(226, 232, 240, 1) !important;
        }

        html:not(.dark) .cloudflare-shell :where(.shadow-2xl, .shadow-xl, .shadow-lg) {
            box-shadow: 0 22px 48px -36px rgba(15, 23, 42, 0.30) !important;
        }

        html:not(.dark) .cloudflare-shell :where(.hover\:bg-white\/5:hover, .hover\:bg-white\/10:hover) {
            background-color: rgba(241, 245, 249, 0.95) !important;
        }

        html:not(.dark) .cloudflare-shell .soft-light-panel {
            background: rgba(255, 255, 255, 0.96) !important;
            border-color: rgba(203, 213, 225, 0.96) !important;
        }
    </style>
@endpush

<x-dashboard-layout>
    <div
        x-data="wordpressCloudflareDashboard({
            actionUrl: @js(route('cloudflare.actions.store')),
            csrfToken: @js(csrf_token()),
            sites: @js($cloudflareSites->all()),
        })"
        x-init="init()"
        @keydown.escape.window="closeModal(); closeCloudflareConfig(); openMenu = null"
        class="cloudflare-shell min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-slate-200"
    >
        <main class="mx-auto max-w-[1680px] px-4 py-6 lg:px-6">
            
            {{-- PAGE OVERVIEW (LISTE DES SITES) --}}
            <section x-show="activePage === 'overview'" class="space-y-6" x-cloak x-transition.duration.300ms.opacity>
                {{-- Hero Header avec effet glassmorphism --}}
                <header class="group overflow-hidden rounded-2xl border border-slate-800/60 bg-gradient-to-r from-slate-900/95 to-slate-900/80 backdrop-blur-sm shadow-2xl transition-all duration-300 hover:border-slate-700/60">
                    <div class="relative">
                        {{-- Background accent --}}
                        <div class="absolute inset-0 overflow-hidden">
                            <div class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-cyan-500/5 blur-3xl"></div>
                            <div class="absolute -bottom-32 -left-32 h-80 w-80 rounded-full bg-emerald-500/5 blur-3xl"></div>
                        </div>
                        
                        <div class="relative grid gap-6 p-6 xl:grid-cols-[1fr_520px] xl:items-center">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex h-7 items-center rounded-full border border-cyan-400/30 bg-cyan-400/10 px-3 text-[11px] font-black uppercase tracking-[0.18em] text-cyan-200 shadow-sm">
                                        Internal Admin
                                    </span>
                                </div>
                                <h1 class="mt-5 bg-gradient-to-r from-white via-cyan-100 to-slate-300 bg-clip-text text-3xl font-black tracking-tight text-transparent md:text-4xl">
                                    Cloudflare WordPress Protection
                                </h1>
                                <p class="mt-2 max-w-3xl text-sm font-medium leading-relaxed text-slate-400">
                                    Apply Cloudflare security layers to managed WordPress client websites with one-click protection templates.
                                </p>
                            </div>
                            <div class="space-y-3">
                                <div class="relative">
                                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="11" cy="11" r="7"></circle>
                                            <path d="m20 20-3.5-3.5"></path>
                                        </svg>
                                    </span>
                                    <input 
                                        x-model="search" 
                                        @input="currentPage = 1" 
                                        type="search" 
                                        placeholder="Search by domain, client, type..." 
                                        class="h-11 w-full rounded-xl border border-slate-700/50 bg-slate-800/50 pl-11 pr-4 text-sm font-medium text-slate-200 outline-none transition-all duration-200 placeholder:text-slate-500 focus:border-cyan-400/50 focus:bg-slate-800/80 focus:ring-2 focus:ring-cyan-400/20"
                                    >
                                </div>
                            </div>
                        </div>
                        
                        {{-- Info banner --}}
                        <div class="border-t border-slate-800/50 bg-slate-900/40 p-4 backdrop-blur-sm">
                            <div class="rounded-xl border border-blue-400/15 bg-blue-400/5 px-4 py-3">
                                <div class="flex gap-3">
                                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-blue-400/20 bg-blue-400/10 text-blue-200">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <path d="M12 16v-4"></path>
                                            <path d="M12 8h.01"></path>
                                        </svg>
                                    </span>
                                    <div>
                                        <p class="text-sm font-black text-blue-100">Client dashboard is read-only.</p>
                                        <p class="mt-0.5 text-sm font-medium text-blue-200/70">Cloudflare actions are executed only by internal admins with audit logging.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                {{-- Stats Cards avec animations --}}
                <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <template x-for="(item, idx) in stats" :key="item.label">
                        <div class="group relative overflow-hidden rounded-2xl border border-slate-800/60 bg-gradient-to-br from-slate-900 to-slate-900/80 p-5 shadow-xl transition-all duration-300 hover:border-slate-700/60 hover:shadow-2xl hover:-translate-y-0.5">
                            <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-gradient-to-br from-cyan-500/10 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>
                            <div class="relative flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500" x-text="item.label"></p>
                                    <p class="mt-3 text-4xl font-black tracking-tight" :class="item.color" x-text="item.count.toLocaleString()"></p>
                                </div>
                                <span class="flex h-11 w-11 items-center justify-center rounded-xl border transition-all duration-300 group-hover:scale-110 group-hover:shadow-lg" :class="item.iconClass">
                                    <svg x-show="item.icon === 'sites'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Z"></path><path d="M4 10h16"></path></svg>
                                    <svg x-show="item.icon === 'protected'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 13c0 5-3.5 7.5-8 8-4.5-.5-8-3-8-8V6l8-3 8 3v7Z"></path><path d="m9 12 2 2 4-5"></path></svg>
                                    <svg x-show="item.icon === 'waf'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"></rect><path d="M7 8h10"></path><path d="M7 12h5"></path><path d="M7 16h8"></path></svg>
                                    <svg x-show="item.icon === 'risk'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>
                                </span>
                            </div>
                            <p class="mt-4 text-sm font-medium text-slate-500" x-text="item.description"></p>
                        </div>
                    </template>
                </section>

                {{-- Filters Bar --}}
                <section class="rounded-2xl border border-slate-800/60 bg-gradient-to-r from-slate-900 to-slate-900/80 p-4 backdrop-blur-sm">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div class="flex flex-wrap gap-2">
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-cyan-300">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 13c0 5-3.5 7.5-8 8-4.5-.5-8-3-8-8V6l8-3 8 3v7Z"></path>
                                        <path d="m9 12 2 2 4-5"></path>
                                    </svg>
                                </span>
                                <select x-model="protectionFilter" @change="currentPage = 1" class="h-10 rounded-xl border border-slate-700/50 bg-slate-800/50 py-0 pl-9 pr-3 text-xs font-black text-slate-300 outline-none transition-all duration-200 focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
                                    <option value="">Protection: All</option>
                                    <option value="protected">Protected</option>
                                    <option value="risk">At Risk</option>
                                </select>
                            </div>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-emerald-300">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M4 6h16"></path>
                                        <path d="M4 12h16"></path>
                                        <path d="M4 18h16"></path>
                                        <path d="M8 4v16"></path>
                                        <path d="M16 4v16"></path>
                                    </svg>
                                </span>
                                <select x-model="wafFilter" @change="currentPage = 1" class="h-10 rounded-xl border border-slate-700/50 bg-slate-800/50 py-0 pl-9 pr-3 text-xs font-black text-slate-300 outline-none transition-all duration-200 focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
                                    <option value="">WAF: All</option>
                                    <option value="active">Active</option>
                                    <option value="off">Off</option>
                                </select>
                            </div>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-blue-300">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="9"></circle>
                                        <path d="M3 12h18"></path>
                                        <path d="M12 3c2.5 2.6 3.8 5.6 3.8 9S14.5 18.4 12 21"></path>
                                        <path d="M12 3c-2.5 2.6-3.8 5.6-3.8 9S9.5 18.4 12 21"></path>
                                    </svg>
                                </span>
                                <select x-model="cdnFilter" @change="currentPage = 1" class="h-10 rounded-xl border border-slate-700/50 bg-slate-800/50 py-0 pl-9 pr-3 text-xs font-black text-slate-300 outline-none transition-all duration-200 focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
                                    <option value="">CDN: All</option>
                                    <option value="active">Active</option>
                                    <option value="off">Off</option>
                                </select>
                            </div>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 8 12 3 3 8l9 5 9-5Z"></path>
                                        <path d="m3 8 9 5v8l-9-5V8Z"></path>
                                        <path d="m21 8-9 5v8l9-5V8Z"></path>
                                    </svg>
                                </span>
                                <select x-model="typeFilter" @change="currentPage = 1" class="h-10 rounded-xl border border-slate-700/50 bg-slate-800/50 py-0 pl-9 pr-3 text-xs font-black text-slate-300 outline-none transition-all duration-200 focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
                                    <option value="">Type: All</option>
                                    <option value="WordPress">WordPress</option>
                                    <option value="WooCommerce">WooCommerce</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <select x-model.number="perPage" @change="currentPage = 1" class="h-10 rounded-xl border border-slate-700/50 bg-slate-800/50 px-3 text-xs font-black text-slate-300 outline-none transition-all duration-200 focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
                                <option :value="5">5 rows</option>
                                <option :value="10">10 rows</option>
                                <option :value="15">15 rows</option>
                                <option :value="20">20 rows</option>
                            </select>
                            <button @click="resetFilters" class="group h-10 rounded-xl border border-slate-700/50 px-4 text-xs font-black text-slate-400 transition-all duration-200 hover:border-cyan-400/40 hover:bg-cyan-400/5 hover:text-cyan-200">
                                <span class="flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5 transition-transform duration-200 group-hover:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 12a9 9 0 1 1-3-6.7"></path>
                                        <path d="M21 3v6h-6"></path>
                                    </svg>
                                    Clear filters
                                </span>
                            </button>
                            <span class="inline-flex h-10 items-center rounded-xl border border-slate-700/50 bg-slate-800/50 px-3 text-xs font-black text-slate-400">
                                <span x-text="filteredSites.length"></span>
                                <span class="px-1 text-slate-600">/</span>
                                <span x-text="sites.length"></span> results
                            </span>
                        </div>
                    </div>
                </section>

                {{-- Sites Table --}}
                <section class="overflow-visible rounded-2xl border border-slate-800/60 bg-gradient-to-br from-slate-900 to-slate-900/80 shadow-2xl backdrop-blur-sm">
                    <div class="flex flex-col gap-3 border-b border-slate-800/50 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-sm font-black uppercase tracking-[0.18em] text-slate-300">Managed WordPress sites</h2>
                            <p class="mt-1 text-xs font-medium text-slate-500">Open a site dashboard to apply Cloudflare actions and review security logs and traffic.</p>
                        </div>
                        <p class="text-xs font-black text-slate-500">Selected: <span class="font-mono text-cyan-200" x-text="selectedDomain || 'None'"></span></p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1400px] text-left">
                            <thead class="border-b border-slate-800/50 bg-slate-800/30 text-[10px] uppercase tracking-[0.18em] text-slate-500">
                                <tr>
                                    <th class="px-6 py-4 font-semibold">Domain</th>
                                    <th class="px-6 py-4 font-semibold">Client</th>
                                    <th class="px-6 py-4 font-semibold">
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg class="h-3.5 w-3.5 text-cyan-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M5 22V4"></path>
                                                <path d="M5 4c2.5-1.2 5-.9 7.5.4 2.2 1.2 4.3 1.2 6.5-.1v10.5c-2.2 1.3-4.3 1.3-6.5.1C10 13.6 7.5 13.3 5 14.5"></path>
                                            </svg>
                                            Country
                                        </span>
                                    </th>
                                    <th class="px-6 py-4 font-semibold">Type</th>
                                    <th class="px-6 py-4 font-semibold">Protection</th>
                                    <th class="px-6 py-4 font-semibold">CDN</th>
                                    <th class="px-6 py-4 font-semibold">WAF</th>
                                    <th class="px-6 py-4 font-semibold">SSL</th>
                                    <th class="px-6 py-4 font-semibold">Updated</th>
                                    <th class="px-6 py-4 text-right font-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/40">
                                <template x-for="site in paginatedSites" :key="site.domain">
                                    <tr 
                                        @click="selectedDomain = site.domain" 
                                        class="group cursor-pointer transition-all duration-150 hover:bg-cyan-400/5" 
                                        :class="selectedDomain === site.domain ? 'bg-cyan-400/8 shadow-inner' : ''"
                                    >
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="relative">
                                                    <div class="h-10 w-1 rounded-full transition-all duration-300 group-hover:scale-x-110" :class="isProtected(site) ? 'bg-gradient-to-b from-emerald-400 to-emerald-500' : 'bg-gradient-to-b from-amber-400 to-amber-500'"></div>
                                                    <div class="absolute left-0 top-0 h-10 w-1 rounded-full blur-sm" :class="isProtected(site) ? 'bg-emerald-400/50' : 'bg-amber-400/50'"></div>
                                                </div>
                                                <div>
                                                    <p class="font-mono text-sm font-black text-cyan-200" x-text="site.domain"></p>
                                                    <p class="mt-0.5 text-[11px] font-semibold text-slate-600" x-text="site.site_id"></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-black text-slate-300" x-text="site.client"></td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-700/60 bg-slate-950/50 px-2.5" :title="countryName(site.country)">
                                                <img :src="countryFlagUrl(site.country)" :alt="site.country + ' flag'" class="h-4 w-6 rounded-sm object-cover shadow-sm">
                                                <span class="font-mono text-xs font-black text-slate-300" x-text="site.country"></span>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-black transition-all duration-200" 
                                                :class="site.type === 'WooCommerce' ? 'border-blue-400/30 bg-blue-400/10 text-blue-200' : 'border-slate-700 bg-slate-800/50 text-slate-300'"
                                                x-text="site.type">
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex min-w-24 items-center justify-center gap-1.5 rounded-full border px-3 py-1 text-xs font-black transition-all duration-200" 
                                                :class="isProtected(site) ? 'border-emerald-400/30 bg-emerald-400/10 text-emerald-300 shadow-sm' : 'border-amber-400/30 bg-amber-400/10 text-amber-200'"
                                            >
                                                <svg x-show="isProtected(site)" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M20 13c0 5-3.5 7.5-8 8-4.5-.5-8-3-8-8V6l8-3 8 3v7Z"></path>
                                                    <path d="m9 12 2 2 4-5"></path>
                                                </svg>
                                                <svg x-show="!isProtected(site)" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"></path>
                                                    <path d="M12 9v4"></path>
                                                    <path d="M12 17h.01"></path>
                                                </svg>
                                                <span x-text="isProtected(site) ? 'Protected' : 'At Risk'"></span>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex min-w-20 items-center justify-center gap-1.5 rounded-full border px-3 py-1 text-xs font-black" 
                                                :class="site.cdn ? 'border-cyan-400/30 bg-cyan-400/10 text-cyan-300' : 'border-red-400/30 bg-red-500/10 text-red-300'"
                                            >
                                                <svg x-show="site.cdn" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="9"></circle>
                                                    <path d="M3 12h18"></path>
                                                    <path d="M12 3c2.5 2.6 3.8 5.6 3.8 9S14.5 18.4 12 21"></path>
                                                    <path d="M12 3c-2.5 2.6-3.8 5.6-3.8 9S9.5 18.4 12 21"></path>
                                                </svg>
                                                <svg x-show="!site.cdn" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="9"></circle>
                                                    <path d="m4.9 4.9 14.2 14.2"></path>
                                                </svg>
                                                <span x-text="site.cdn ? 'Active' : 'Off'"></span>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex min-w-20 justify-center rounded-full border px-3 py-1 text-xs font-black" 
                                                :class="site.waf ? 'border-emerald-400/30 bg-emerald-400/10 text-emerald-300' : 'border-red-400/30 bg-red-500/10 text-red-300'"
                                                x-text="site.waf ? 'Active' : 'Off'">
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="rounded-lg border border-slate-700 bg-slate-800/50 px-3 py-1.5 text-xs font-black text-slate-300" x-text="site.ssl"></span>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-semibold text-slate-500" x-text="site.last_update"></td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end gap-2" @click.stop>
                                                <button @click="openDashboard(site)" class="group inline-flex h-9 w-9 items-center justify-center rounded-lg border border-cyan-400/30 bg-cyan-400/5 text-cyan-200 transition-all duration-200 hover:border-cyan-300/50 hover:bg-cyan-400/20 hover:shadow-lg" title="Open site dashboard">
                                                    <svg class="h-4 w-4 transition-transform group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                                                        <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                                                        <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                                                        <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="paginatedSites.length === 0">
                                    <td colspan="10" class="px-6 py-16 text-center">
                                        <div class="mx-auto max-w-md rounded-2xl border border-slate-700 bg-slate-800/30 p-8 backdrop-blur-sm">
                                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl border border-slate-700 bg-slate-800/50 text-slate-500">
                                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="11" cy="11" r="7"></circle>
                                                    <path d="m20 20-3.5-3.5"></path>
                                                </svg>
                                            </div>
                                            <p class="mt-5 text-sm font-black text-slate-200">No sites match your filters</p>
                                            <button @click="resetFilters" class="mt-6 rounded-xl bg-gradient-to-r from-cyan-400 to-cyan-500 px-5 py-2.5 text-sm font-black text-slate-950 transition-all duration-200 hover:shadow-lg hover:shadow-cyan-400/25 hover:scale-[1.02]">
                                                Clear filters
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Pagination --}}
                    <div class="flex flex-col gap-3 border-t border-slate-800/50 px-6 py-5 md:flex-row md:items-center md:justify-between">
                        <span class="text-xs font-black text-slate-500">
                            Showing <span class="text-slate-300" x-text="paginationFrom"></span> to <span class="text-slate-300" x-text="paginationTo"></span> 
                            of <span class="text-slate-300" x-text="filteredSites.length"></span> sites
                        </span>
                        <div class="flex flex-wrap items-center gap-2">
                            <button @click="prevPage" :disabled="currentPage === 1" class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-700/50 px-4 text-xs font-black text-slate-300 transition-all duration-200 hover:border-cyan-400/40 hover:bg-cyan-400/5 hover:text-cyan-200 disabled:cursor-not-allowed disabled:opacity-35">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="m15 18-6-6 6-6"></path>
                                </svg>
                                Previous
                            </button>
                            <template x-for="page in visiblePages" :key="page">
                                <button @click="goToPage(page)" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border text-xs font-black transition-all duration-200 hover:border-cyan-400/40 hover:bg-cyan-400/10 hover:text-cyan-200" 
                                    :class="currentPage === page ? 'border-cyan-400/50 bg-cyan-400/15 text-cyan-200 shadow-sm' : 'border-slate-700/50 text-slate-400'">
                                    <span x-text="page"></span>
                                </button>
                            </template>
                            <button @click="nextPage" :disabled="currentPage === totalPages" class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-700/50 px-4 text-xs font-black text-slate-300 transition-all duration-200 hover:border-cyan-400/40 hover:bg-cyan-400/5 hover:text-cyan-200 disabled:cursor-not-allowed disabled:opacity-35">
                                Next
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="m9 18 6-6-6-6"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </section>
            </section>

            {{-- PAGE DASHBOARD SITE --}}
            <section x-show="activePage === 'dashboard'" x-cloak x-transition.duration.300ms.opacity class="space-y-6">
                <template x-if="selectedSite">
                    <div class="space-y-6">
                        {{-- Header Dashboard --}}
                        <header class="relative overflow-hidden rounded-2xl border border-slate-800/60 bg-gradient-to-r from-slate-900 to-slate-900/80 shadow-2xl backdrop-blur-sm">
                            <div class="absolute top-0 right-0 h-32 w-32 bg-gradient-to-bl from-cyan-500/10 to-transparent rounded-full blur-2xl"></div>
                            <div class="flex flex-col gap-5 border-b border-slate-800/50 p-6 xl:flex-row xl:items-center xl:justify-between">
                                <div class="flex items-start gap-4">
                                    <button @click="backToOverview" class="group inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-700/50 bg-slate-800/50 text-slate-400 transition-all duration-200 hover:border-cyan-400/40 hover:bg-cyan-400/10 hover:text-cyan-200 hover:shadow-lg">
                                        <svg class="h-4 w-4 transition-transform group-hover:-translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <path d="m15 18-6-6 6-6"></path>
                                        </svg>
                                    </button>
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-cyan-400">WordPress Security Dashboard</p>
                                        <h2 class="mt-2 font-mono text-2xl font-black text-white md:text-3xl" x-text="selectedSite.domain"></h2>
                                        <div class="mt-1.5 flex items-center gap-2">
                                            <span class="text-sm font-semibold text-slate-400" x-text="selectedSite.client"></span>
                                            <span class="h-1 w-1 rounded-full bg-slate-600"></span>
                                            <span class="text-sm font-semibold text-slate-400" x-text="selectedSite.type"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <button @click="openModal('purge_cache', selectedSite)" class="group inline-flex h-10 items-center gap-2 rounded-xl border border-emerald-400/30 bg-emerald-400/10 px-4 text-sm font-black text-emerald-200 transition-all duration-200 hover:bg-emerald-400/20 hover:shadow-lg hover:shadow-emerald-400/10">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 13c0 5-3.5 7.5-8 8-4.5-.5-8-3-8-8V6l8-3 8 3v7Z"></path><path d="m9 12 2 2 4-5"></path></svg>
                                        Purge Cache
                                    </button>
                                    <button @click="openModal('under_attack', selectedSite)" class="group inline-flex h-10 items-center gap-2 rounded-xl border border-red-400/30 bg-red-500/10 px-4 text-sm font-black text-red-200 transition-all duration-200 hover:bg-red-500/20 hover:shadow-lg hover:shadow-red-500/10">
                                        <svg class="h-3.5 w-3.5 animate-pulse" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                                        Under Attack
                                    </button>
                                    <button @click="openModal('block_ip', selectedSite)" class="group inline-flex h-10 items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-400 to-cyan-500 px-4 text-sm font-black text-slate-950 transition-all duration-200 hover:shadow-lg hover:shadow-cyan-400/25 hover:scale-[1.02]">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m4.93 4.93 14.14 14.14"/></svg>
                                        Block IP
                                    </button>
                                </div>
                            </div>
                            
                            {{-- Metrics Grid --}}
                            <div class="grid gap-px bg-slate-800/40 md:grid-cols-2 xl:grid-cols-6">
                                <div class="relative overflow-hidden bg-slate-900/60 p-5 transition-all duration-200 hover:bg-slate-900/80">
                                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">Total Requests</p>
                                    <p class="mt-2 text-2xl font-black text-cyan-300" x-text="formatNumber(selectedSite.traffic_24h)"></p>
                                    <p class="mt-1 text-xs font-semibold text-slate-600">Last 24 hours</p>
                                </div>
                                <div class="relative overflow-hidden bg-slate-900/60 p-5 transition-all duration-200 hover:bg-slate-900/80">
                                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">Threats Blocked</p>
                                    <p class="mt-2 text-2xl font-black text-emerald-300" x-text="formatNumber(selectedSite.threats_blocked)"></p>
                                    <p class="mt-1 text-xs font-semibold text-slate-600">By Cloudflare edge</p>
                                </div>
                                <div class="relative overflow-hidden bg-slate-900/60 p-5 transition-all duration-200 hover:bg-slate-900/80">
                                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">WAF Events</p>
                                    <p class="mt-2 text-2xl font-black text-blue-300" x-text="formatNumber(selectedSite.waf_events)"></p>
                                    <p class="mt-1 text-xs font-semibold text-slate-600">Rules matched</p>
                                </div>
                                <div class="relative overflow-hidden bg-slate-900/60 p-5 transition-all duration-200 hover:bg-slate-900/80">
                                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">Cache Hit</p>
                                    <p class="mt-2 text-2xl font-black" :class="selectedSite.cache_hit >= 70 ? 'text-emerald-300' : 'text-amber-300'" x-text="selectedSite.cache_hit + '%'"></p>
                                    <p class="mt-1 text-xs font-semibold text-slate-600">CDN efficiency</p>
                                </div>
                                <div class="relative overflow-hidden bg-slate-900/60 p-5 transition-all duration-200 hover:bg-slate-900/80">
                                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">Login Attacks</p>
                                    <p class="mt-2 text-2xl font-black" :class="selectedSite.login_attacks > 100 ? 'text-red-300' : 'text-amber-300'" x-text="formatNumber(selectedSite.login_attacks)"></p>
                                    <p class="mt-1 text-xs font-semibold text-slate-600">wp-login attempts</p>
                                </div>
                                <div class="relative overflow-hidden bg-slate-900/60 p-5 transition-all duration-200 hover:bg-slate-900/80">
                                    <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500">XML-RPC Hits</p>
                                    <p class="mt-2 text-2xl font-black" :class="selectedSite.xmlrpc_hits > 80 ? 'text-red-300' : 'text-slate-300'" x-text="formatNumber(selectedSite.xmlrpc_hits)"></p>
                                    <p class="mt-1 text-xs font-semibold text-slate-600">Abuse indicators</p>
                                </div>
                            </div>
                        </header>
                        <div class="rounded-2xl border border-slate-800/60 bg-slate-900/80 p-6">
                            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                <div>
                                    <h3 class="text-sm font-black uppercase tracking-[0.18em] text-slate-300">
                                        Cloudflare Status
                                    </h3>
                                    <div class="mt-3 flex flex-wrap items-center gap-2">
                                        <span
                                            class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-black"
                                            :class="selectedSite.cloudflare_status === 'active'
                                                ? 'bg-emerald-500/20 text-emerald-300'
                                                : 'bg-amber-500/20 text-amber-300'"
                                        >
                                            <span class="h-1.5 w-1.5 rounded-full" :class="selectedSite.cloudflare_status === 'active' ? 'bg-emerald-300' : 'bg-amber-300'"></span>
                                            <span x-text="selectedSite.cloudflare_status"></span>
                                        </span>
                                        <span
                                            class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-black"
                                            :class="selectedSite.cloudflare_token_saved ? 'border-emerald-400/25 bg-emerald-400/10 text-emerald-300' : 'border-slate-700/60 bg-slate-800/50 text-slate-400'"
                                        >
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M15 7a3 3 0 1 0-3 3l-7 7v3h3l7-7a3 3 0 0 0 0-6Z"></path>
                                                <path d="M16 7h.01"></path>
                                            </svg>
                                            <span x-text="selectedSite.cloudflare_token_saved ? 'Token saved' : 'Token needed'"></span>
                                        </span>
                                    </div>
                                    <p class="mt-3 text-sm text-slate-400">
                                        Zone ID:
                                        <span class="font-mono font-black text-cyan-300" x-text="selectedSite.zone_id || 'Not connected'"></span>
                                    </p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">
                                        Last update:
                                        <span x-text="selectedSite.last_update || 'Not synced yet'"></span>
                                    </p>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <button
                                        @click="syncCloudflare(selectedSite)"
                                        :disabled="!selectedSite.sync_url || syncingProjectId === selectedSite.project_id"
                                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-cyan-400/25 bg-cyan-400/10 px-4 text-sm font-black text-cyan-200 transition-all duration-200 hover:bg-cyan-400/15 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <svg x-show="syncingProjectId === selectedSite.project_id" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <path d="M21 12a9 9 0 1 1-3-6.7"></path>
                                        </svg>
                                        <svg x-show="syncingProjectId !== selectedSite.project_id" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 12a9 9 0 0 1-15.5 6.3L3 16"></path>
                                            <path d="M3 21v-5h5"></path>
                                            <path d="M3 12A9 9 0 0 1 18.5 5.7L21 8"></path>
                                            <path d="M21 3v5h-5"></path>
                                        </svg>
                                        <span x-text="syncingProjectId === selectedSite.project_id ? 'Syncing...' : 'Sync API'"></span>
                                    </button>
                                    <button
                                        @click="openCloudflareConfig(selectedSite)"
                                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-400 to-cyan-500 px-4 text-sm font-black text-slate-950 transition-all duration-200 hover:shadow-lg hover:shadow-cyan-400/25 hover:scale-[1.02]"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"></path>
                                            <path d="M19.4 15a1.7 1.7 0 0 0 .34 1.87l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21a2 2 0 1 1-4 0v-.09A1.7 1.7 0 0 0 8.6 19.4a1.7 1.7 0 0 0-1.87.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H3a2 2 0 1 1 0-4h.09A1.7 1.7 0 0 0 4.6 8.6a1.7 1.7 0 0 0-.34-1.87l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V3a2 2 0 1 1 4 0v.09A1.7 1.7 0 0 0 15.4 4.6a1.7 1.7 0 0 0 1.87-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9c.2.38.3.8.3 1.2H21a2 2 0 1 1 0 4h-.09a1.7 1.7 0 0 0-1.51.8Z"></path>
                                        </svg>
                                        Configure API
                                    </button>
                                </div>
                            </div>

                            <template x-if="selectedSite.name_servers?.length">
                                <div class="mt-5">
                                    <p class="text-xs font-black uppercase tracking-[0.14em] text-slate-500">
                                        Nameservers
                                    </p>

                                    <div class="mt-3 grid gap-2 md:grid-cols-2">
                                        <template x-for="ns in selectedSite.name_servers" :key="ns">
                                            <div class="rounded-xl border border-slate-700/50 bg-slate-800/40 px-4 py-3 font-mono text-sm text-cyan-200">
                                                <span x-text="ns"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                        {{-- 3 Colonnes: Actions | Traffic & Pays | Top IP & Block --}}
                        <div class="grid gap-6 xl:grid-cols-3">
                            {{-- Actions Column --}}
                            <div class="self-start rounded-2xl border border-slate-800/60 bg-gradient-to-br from-slate-900 to-slate-900/80 p-6 shadow-xl">
                                <div class="flex items-center justify-between gap-4 mb-5">
                                    <div>
                                        <h3 class="text-sm font-black uppercase tracking-[0.18em] text-slate-300">Cloudflare Actions</h3>
                                        <p class="mt-1 text-xs font-medium text-slate-500">Admin-only security actions</p>
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-black transition-all duration-200" 
                                        :class="isProtected(selectedSite) ? 'border-emerald-400/30 bg-emerald-400/10 text-emerald-300' : 'border-amber-400/30 bg-amber-400/10 text-amber-200'"
                                    >
                                        <svg x-show="isProtected(selectedSite)" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M20 13c0 5-3.5 7.5-8 8-4.5-.5-8-3-8-8V6l8-3 8 3v7Z"></path>
                                            <path d="m9 12 2 2 4-5"></path>
                                        </svg>
                                        <svg x-show="!isProtected(selectedSite)" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"></path>
                                            <path d="M12 9v4"></path>
                                            <path d="M12 17h.01"></path>
                                        </svg>
                                        <span x-text="isProtected(selectedSite) ? 'Protected' : 'Needs action'"></span>
                                    </span>
                                </div>
                                <div class="space-y-5">
                                    <template x-for="group in actionGroups" :key="group.label">
                                        <div>
                                            <p class="mb-2.5 text-[10px] font-black uppercase tracking-[0.16em] text-slate-500" x-text="group.label"></p>
                                            <div class="grid gap-2">
                                                <template x-for="action in group.actions" :key="action.key">
                                                    <button @click="openModal(action.key, selectedSite)" class="group rounded-xl border px-3 py-3 text-left transition-all duration-200 hover:shadow-lg" :class="actionButtonClass(action.key)">
                                                        <span class="flex items-center gap-3">
                                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border bg-slate-950/50 transition-all duration-200 group-hover:scale-105" :class="actionIconClass(action.key)">
                                                                <svg x-show="action.key === 'under_attack'" class="h-4 w-4 animate-pulse" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                                                    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path>
                                                                </svg>
                                                                <svg x-show="action.key === 'disable_under_attack'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                                                    <path d="M12 2v10"></path>
                                                                    <path d="M18.4 6.6a9 9 0 1 1-12.8 0"></path>
                                                                </svg>
                                                                <svg x-show="['block_ip', 'block_country'].includes(action.key)" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                                                    <circle cx="12" cy="12" r="9"></circle>
                                                                    <path d="m5.7 5.7 12.6 12.6"></path>
                                                                </svg>
                                                                <svg x-show="action.key === 'allow_ip'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                                                    <path d="M20 6 9 17l-5-5"></path>
                                                                </svg>
                                                                <svg x-show="action.key === 'challenge_country'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                                                    <path d="M20 13c0 5-3.5 7.5-8 8-4.5-.5-8-3-8-8V6l8-3 8 3v7Z"></path>
                                                                    <path d="M12 8v4"></path>
                                                                    <path d="M12 16h.01"></path>
                                                                </svg>
                                                                <svg x-show="['purge_cache', 'purge_url'].includes(action.key)" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                                                    <path d="M21 12a9 9 0 1 1-3-6.7"></path>
                                                                    <path d="M21 3v6h-6"></path>
                                                                </svg>
                                                            </span>
                                                            <span class="min-w-0">
                                                                <span class="block text-xs font-black text-slate-200 transition-colors group-hover:text-white" x-text="action.label"></span>
                                                                <span class="mt-0.5 block text-[11px] font-semibold text-slate-500" x-text="actionHint(action.key)"></span>
                                                            </span>
                                                        </span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Traffic & Countries Column --}}
                            <div class="space-y-6">
                                {{-- Traffic Chart --}}
                                <div class="rounded-2xl border border-slate-800/60 bg-gradient-to-br from-slate-900 to-slate-900/80 p-6 shadow-xl">
                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <h3 class="text-sm font-black uppercase tracking-[0.18em] text-slate-300">Traffic Overview (24h)</h3>
                                            <p class="mt-1 text-xs font-medium text-slate-500">
                                                <span x-text="selectedSite.analytics_synced_at ? 'Updated ' + selectedSite.analytics_synced_at : 'Real Cloudflare analytics'"></span>
                                            </p>
                                        </div>
                                        <button @click="loadCloudflareAnalytics(selectedSite, true)" :disabled="!selectedSite.analytics_url || analyticsLoadingProjectId === selectedSite.project_id" class="inline-flex h-9 items-center gap-2 rounded-xl border border-cyan-400/25 bg-cyan-400/10 px-3 text-xs font-black text-cyan-200 transition hover:bg-cyan-400/15 disabled:cursor-not-allowed disabled:opacity-50">
                                            <svg class="h-3.5 w-3.5" :class="analyticsLoadingProjectId === selectedSite.project_id ? 'animate-spin' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                                <path d="M21 12a9 9 0 0 1-15.5 6.3L3 16"></path>
                                                <path d="M3 21v-5h5"></path>
                                                <path d="M3 12A9 9 0 0 1 18.5 5.7L21 8"></path>
                                                <path d="M21 3v5h-5"></path>
                                            </svg>
                                            <span x-text="analyticsLoadingProjectId === selectedSite.project_id ? 'Loading' : 'Refresh'"></span>
                                        </button>
                                    </div>
                                    <p x-show="selectedSite.analytics_error" x-text="selectedSite.analytics_error" class="mt-4 rounded-xl border border-red-400/25 bg-red-500/10 px-4 py-3 text-xs font-black text-red-200"></p>
                                    <p x-show="selectedSite.analytics_warning && !selectedSite.analytics_error" x-text="selectedSite.analytics_warning" class="mt-4 rounded-xl border border-amber-400/25 bg-amber-400/10 px-4 py-3 text-xs font-black text-amber-100"></p>
                                    <div class="mt-6 flex h-64 items-end gap-2 rounded-2xl border border-slate-700/50 bg-slate-800/30 px-4 py-4">
                                        <template x-for="bar in trafficBars(selectedSite)" :key="bar.hour">
                                            <div class="group flex min-w-0 flex-1 flex-col items-center gap-2">
                                                <div class="flex h-52 w-full items-end">
                                                    <div class="relative w-full overflow-hidden rounded-t-lg bg-cyan-400/60 transition-all duration-300 group-hover:bg-cyan-300 group-hover:shadow-lg" :style="`height: ${bar.value}%`">
                                                        <div class="absolute inset-0 w-full bg-gradient-to-t from-cyan-500/0 to-cyan-300/20"></div>
                                                    </div>
                                                </div>
                                                <span class="text-[10px] font-black text-slate-600" x-text="bar.hour"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                {{-- Traffic by Country --}}
                                <div class="overflow-hidden rounded-2xl border border-slate-800/70 bg-slate-900/90 shadow-xl">
                                    <div class="flex items-center justify-between gap-4 border-b border-slate-800/60 px-5 py-4">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-cyan-400/20 bg-cyan-400/10 text-cyan-200">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="9"></circle>
                                                    <path d="M3 12h18"></path>
                                                    <path d="M12 3c2.5 2.6 3.8 5.6 3.8 9S14.5 18.4 12 21"></path>
                                                    <path d="M12 3c-2.5 2.6-3.8 5.6-3.8 9S9.5 18.4 12 21"></path>
                                                </svg>
                                            </span>
                                            <div class="min-w-0">
                                                <h3 class="text-sm font-black uppercase tracking-[0.18em] text-slate-200">Traffic by Country</h3>
                                                <p class="mt-1 text-xs font-medium text-slate-500">Last 24 hours by region.</p>
                                            </div>
                                        </div>
                                        <span class="rounded-lg border border-slate-700/60 bg-slate-950/40 px-3 py-1.5 text-xs font-black text-slate-400">
                                            <span x-text="topCountries.length"></span>
                                            regions
                                        </span>
                                    </div>

                                    <div class="max-h-[340px] divide-y divide-slate-800/60 overflow-y-auto custom-scrollbar">
                                        <template x-for="country in topCountries" :key="country.code || country.name">
                                            <div class="px-5 py-4 transition hover:bg-slate-800/35">
                                                <div class="flex items-center justify-between gap-4">
                                                    <div class="flex min-w-0 items-center gap-3">
                                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-700/60 bg-slate-950/50" :title="country.name">
                                                            <img :src="countryFlagUrl(country.code)" :alt="country.name + ' flag'" class="h-5 w-7 rounded-sm object-cover shadow-sm">
                                                        </span>
                                                        <div class="min-w-0">
                                                            <p class="truncate text-sm font-black text-slate-200" x-text="country.name"></p>
                                                            <p class="mt-1 text-xs font-medium text-slate-500">
                                                                <span x-text="formatNumber(country.requests)"></span>
                                                                requests
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <div class="flex shrink-0 items-center gap-2">
                                                        <span class="w-10 text-right font-mono text-xs font-black text-cyan-200" x-text="country.percentage + '%'"></span>
                                                        <button @click="openModal('challenge_country', selectedSite, country.code)" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-amber-400/25 bg-amber-400/10 text-amber-200 transition hover:bg-amber-400/15" title="Challenge country" aria-label="Challenge country">
                                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                                                <path d="M20 13c0 5-3.5 7.5-8 8-4.5-.5-8-3-8-8V6l8-3 8 3v7Z"></path>
                                                                <path d="M12 8v4"></path>
                                                                <path d="M12 16h.01"></path>
                                                            </svg>
                                                        </button>
                                                        <button @click="openModal('block_country', selectedSite, country.code)" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-400/25 bg-red-500/10 text-red-200 transition hover:bg-red-500/15" title="Block country" aria-label="Block country">
                                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                                                <circle cx="12" cy="12" r="9"></circle>
                                                                <path d="m5.7 5.7 12.6 12.6"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-800">
                                                    <div class="h-full rounded-full bg-cyan-400 transition-all duration-500" :style="{ width: country.percentage + '%' }"></div>
                                                </div>
                                            </div>
                                        </template>
                                        <p x-show="topCountries.length === 0" class="px-5 py-8 text-center text-xs font-medium text-slate-500">
                                            No country traffic returned by Cloudflare for this window.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- IP & Countries Blocked Column --}}
                            <div class="space-y-6">
                                {{-- Top Attacking IPs --}}
                                <div class="rounded-2xl border border-slate-800/60 bg-gradient-to-br from-slate-900 to-slate-900/80 p-6 shadow-xl">
                                    <h3 class="text-sm font-black uppercase tracking-[0.18em] text-slate-300">Top Attacking IPs</h3>
                                    <div class="mt-5 space-y-3">
                                        <template x-for="ip in topAttackingIPs" :key="ip.address">
                                            <div class="group flex items-center justify-between border-b border-slate-700/50 pb-3 transition-all duration-200 hover:bg-slate-800/30 hover:-translate-x-1">
                                                <div>
                                                    <span class="font-mono text-sm font-black text-cyan-200" x-text="ip.address"></span>
                                                    <span class="ml-2 inline-flex align-middle" :title="ip.country">
                                                        <img :src="countryFlagUrl(ip.country)" :alt="ip.country + ' flag'" class="h-3.5 w-5 rounded-sm object-cover shadow-sm">
                                                    </span>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <span class="inline-flex items-center gap-1.5 text-xs font-black text-red-300" title="Attempts">
                                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"></path>
                                                            <path d="M12 9v4"></path>
                                                            <path d="M12 17h.01"></path>
                                                        </svg>
                                                        <span x-text="ip.attempts.toLocaleString()"></span>
                                                    </span>
                                                    <button @click="openModal('block_ip', selectedSite, ip.address)" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-red-500/10 text-red-300 transition-all duration-200 hover:bg-red-500/20 hover:scale-105" title="Block IP" aria-label="Block IP">
                                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                                            <circle cx="12" cy="12" r="9"></circle>
                                                            <path d="m5.7 5.7 12.6 12.6"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                        <p x-show="topAttackingIPs.length === 0" class="py-6 text-center text-xs font-medium text-slate-500">
                                            No Security Events with source IPs in the last 24 hours.
                                        </p>
                                    </div>
                                </div>

                                {{-- Blocked IPs --}}
                                <div class="rounded-2xl border border-slate-800/60 bg-gradient-to-br from-slate-900 to-slate-900/80 p-6 shadow-xl">
                                    <h3 class="text-sm font-black uppercase tracking-[0.18em] text-slate-300">Recently Blocked IPs</h3>
                                    <div class="mt-5 space-y-3">
                                        <template x-for="ip in blockedIPs" :key="ip">
                                            <div class="group flex items-center justify-between border-b border-slate-700/50 pb-3 transition-all duration-200 hover:bg-slate-800/30">
                                                <div class="flex items-center gap-2">
                                                    <span class="h-2 w-2 rounded-full bg-red-500 animate-pulse"></span>
                                                    <span class="font-mono text-sm font-black text-red-300" x-text="ip"></span>
                                                </div>
                                                <button @click="openModal('allow_ip', selectedSite, ip)" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-400/10 text-emerald-400 transition-all duration-200 hover:bg-emerald-400/20 hover:text-emerald-300 hover:scale-105" title="Unblock IP" aria-label="Unblock IP">
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                                        <path d="M20 6 9 17l-5-5"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                        <p x-show="blockedIPs.length === 0" class="py-6 text-center text-xs font-medium text-slate-500">
                                            No recently blocked IPs
                                        </p>
                                    </div>
                                </div>

                                {{-- Blocked Countries --}}
                                <div class="rounded-2xl border border-slate-800/60 bg-gradient-to-br from-slate-900 to-slate-900/80 p-6 shadow-xl">
                                    <h3 class="text-sm font-black uppercase tracking-[0.18em] text-slate-300">Blocked Countries Seen</h3>
                                    <div class="mt-5 flex flex-wrap gap-2">
                                        <template x-for="country in blockedCountries" :key="country">
                                            <div class="group flex items-center gap-2 rounded-full border border-red-400/30 bg-red-500/10 px-3 py-1.5 transition-all duration-200 hover:scale-105">
                                                <span class="inline-flex" :title="country">
                                                    <img :src="countryFlagUrl(country)" :alt="country + ' flag'" class="h-3.5 w-5 rounded-sm object-cover shadow-sm">
                                                </span>
                                                <button @click="openModal('challenge_country', selectedSite, country)" class="text-[10px] font-black text-slate-400 transition-colors hover:text-white">
                                                    x
                                                </button>
                                            </div>
                                        </template>
                                        <p x-show="blockedCountries.length === 0" class="py-4 text-center text-xs font-medium text-slate-500 w-full">
                                            No blocked country events
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Security Logs --}}
                        <div class="overflow-hidden rounded-2xl border border-slate-800/60 bg-gradient-to-br from-slate-900 to-slate-900/80 shadow-xl">
                            <div class="border-b border-slate-800/50 px-6 py-5">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <h3 class="text-sm font-black uppercase tracking-[0.18em] text-slate-300">Security Logs</h3>
                                        <p class="mt-1 text-xs font-medium text-slate-500">Firewall, login, XML-RPC, bot, and cache events for this site.</p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <span class="inline-flex h-9 items-center rounded-xl border border-slate-700/60 bg-slate-950/50 px-3 text-xs font-black text-slate-300">
                                            <span x-text="siteLogs(selectedSite).length"></span>
                                            <span class="ml-1">events</span>
                                        </span>
                                        <span class="inline-flex h-9 items-center rounded-xl border border-red-400/25 bg-red-500/10 px-3 text-xs font-black text-red-200">
                                            <span x-text="siteLogs(selectedSite).filter(log => log.severity === 'High').length"></span>
                                            <span class="ml-1">high risk</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="max-h-[320px] overflow-auto custom-scrollbar">
                                <table class="w-full min-w-[1040px] text-left">
                                    <thead class="sticky top-0 z-10 border-b border-slate-800/50 bg-slate-800/95 text-[10px] uppercase tracking-[0.16em] text-slate-500 backdrop-blur">
                                        <tr>
                                            <th class="px-6 py-3 font-semibold">Time</th>
                                            <th class="px-6 py-3 font-semibold">Event</th>
                                            <th class="px-6 py-3 font-semibold">Source IP</th>
                                            <th class="px-6 py-3 font-semibold">Path</th>
                                            <th class="px-6 py-3 font-semibold">Cloudflare action</th>
                                            <th class="px-6 py-3 font-semibold">Severity</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-800/40">
                                        <template x-for="log in siteLogs(selectedSite)" :key="log.id">
                                            <tr class="transition-all duration-200 hover:bg-cyan-400/5">
                                                <td class="px-6 py-4 text-xs font-black text-slate-500" x-text="log.time"></td>
                                                <td class="px-6 py-4">
                                                    <p class="text-sm font-black text-slate-200" x-text="log.event"></p>
                                                    <p class="mt-1 text-xs font-medium text-slate-600" x-text="log.detail"></p>
                                                </td>
                                                <td class="px-6 py-4 font-mono text-xs font-black text-cyan-200" x-text="log.ip"></td>
                                                <td class="px-6 py-4 font-mono text-xs font-semibold text-slate-400" x-text="log.path"></td>
                                                <td class="px-6 py-4">
                                                    <span class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-xs font-black" :class="logActionClass(log.action)">
                                                        <svg x-show="log.action === 'Blocked'" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                                                            <circle cx="12" cy="12" r="9"></circle>
                                                            <path d="m5.7 5.7 12.6 12.6"></path>
                                                        </svg>
                                                        <svg x-show="['Managed Challenge', 'Challenge'].includes(log.action)" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                                                            <path d="M20 13c0 5-3.5 7.5-8 8-4.5-.5-8-3-8-8V6l8-3 8 3v7Z"></path>
                                                            <path d="M12 8v4"></path>
                                                            <path d="M12 16h.01"></path>
                                                        </svg>
                                                        <svg x-show="log.action === 'Monitor'" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                                                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                        <span x-text="log.action"></span>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-black transition-all duration-200" 
                                                        :class="severityClass(log.severity)">
                                                        <span class="h-1.5 w-1.5 rounded-full" :class="severityDotClass(log.severity)"></span>
                                                        <span x-text="log.severity"></span>
                                                    </span>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="siteLogs(selectedSite).length === 0">
                                            <td colspan="6" class="px-6 py-10 text-center text-xs font-medium text-slate-500">
                                                No Cloudflare Security Events returned for this site in the last 24 hours.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </template>
            </section>
        </main>

        {{-- MODAL CLOUDFLARE API --}}
        <div x-show="cloudflareConfig.open" x-cloak x-transition.opacity.duration.200ms class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/90 p-4 backdrop-blur-md">
            <form @submit.prevent="saveCloudflareConfig" @click.outside="closeCloudflareConfig" x-transition.scale.origin.center.duration.200ms class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl border border-slate-700/60 bg-gradient-to-br from-slate-900 to-slate-950 shadow-2xl">
                <div class="border-b border-slate-800/50 bg-slate-800/30 px-6 py-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-cyan-400">Cloudflare API</p>
                            <h2 class="mt-2 text-xl font-black text-white">Connect Cloudflare Zone</h2>
                            <p class="mt-1 font-mono text-sm font-semibold text-slate-500" x-text="cloudflareConfig.domain"></p>
                        </div>
                        <button type="button" @click="closeCloudflareConfig" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-700/50 bg-slate-800/50 text-slate-500 transition-all duration-200 hover:border-red-400/40 hover:bg-red-500/10 hover:text-red-300">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 6 6 18"></path>
                                <path d="m6 6 12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="space-y-5 p-6">
                    <div class="rounded-xl border border-cyan-400/20 bg-cyan-400/5 p-4">
                        <p class="text-xs font-black uppercase tracking-[0.16em] text-cyan-300">Connection</p>
                        <p class="mt-1 text-sm font-medium leading-relaxed text-slate-300">
                            Paste a Cloudflare API token with Zone Read and Zone Settings Edit permissions. Leave it empty when a token is already saved or configured in the server environment.
                        </p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">API Token</label>
                            <input
                                x-model="cloudflareConfig.api_token"
                                type="password"
                                autocomplete="off"
                                placeholder="Cloudflare API token"
                                class="mt-2 h-11 w-full rounded-xl border border-slate-700/50 bg-slate-800/50 px-4 text-sm text-slate-200 outline-none transition-all duration-200 placeholder:text-slate-600 focus:border-cyan-400/50 focus:bg-slate-800/80 focus:ring-2 focus:ring-cyan-400/20"
                            >
                        </div>
                        <div>
                            <label class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">Zone ID</label>
                            <input
                                x-model="cloudflareConfig.zone_id"
                                type="text"
                                placeholder="Optional if domain is in the account"
                                class="mt-2 h-11 w-full rounded-xl border border-slate-700/50 bg-slate-800/50 px-4 font-mono text-sm text-slate-200 outline-none transition-all duration-200 placeholder:text-slate-600 focus:border-cyan-400/50 focus:bg-slate-800/80 focus:ring-2 focus:ring-cyan-400/20"
                            >
                        </div>
                        <div>
                            <label class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">Account Email</label>
                            <input
                                x-model="cloudflareConfig.account_email"
                                type="email"
                                placeholder="admin@example.com"
                                class="mt-2 h-11 w-full rounded-xl border border-slate-700/50 bg-slate-800/50 px-4 text-sm text-slate-200 outline-none transition-all duration-200 placeholder:text-slate-600 focus:border-cyan-400/50 focus:bg-slate-800/80 focus:ring-2 focus:ring-cyan-400/20"
                            >
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">SSL Mode</label>
                            <select x-model="cloudflareConfig.ssl_mode" class="mt-2 h-11 w-full rounded-xl border border-slate-700/50 bg-slate-800/50 px-4 text-sm font-semibold text-slate-200 outline-none transition-all duration-200 focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
                                <option value="full_strict">Full Strict</option>
                                <option value="full">Full</option>
                                <option value="flexible">Flexible</option>
                                <option value="off">Off</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">Security Level</label>
                            <select x-model="cloudflareConfig.security_level" class="mt-2 h-11 w-full rounded-xl border border-slate-700/50 bg-slate-800/50 px-4 text-sm font-semibold text-slate-200 outline-none transition-all duration-200 focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
                                <option value="essentially_off">Essentially Off</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="under_attack">Under Attack</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">Cache Level</label>
                            <select x-model="cloudflareConfig.cache_level" class="mt-2 h-11 w-full rounded-xl border border-slate-700/50 bg-slate-800/50 px-4 text-sm font-semibold text-slate-200 outline-none transition-all duration-200 focus:border-cyan-400/50 focus:ring-2 focus:ring-cyan-400/20">
                                <option value="standard">Standard</option>
                                <option value="simplified">Simplified</option>
                                <option value="aggressive">Aggressive</option>
                                <option value="cache_everything">Cache Everything</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <label class="flex items-center gap-3 rounded-xl border border-slate-700/50 bg-slate-800/40 px-4 py-3 text-sm font-black text-slate-300">
                            <input x-model="cloudflareConfig.proxy_enabled" type="checkbox" class="h-4 w-4 rounded border-slate-600 bg-slate-900 text-cyan-400 focus:ring-cyan-400">
                            Proxy enabled
                        </label>
                        <label class="flex items-center gap-3 rounded-xl border border-slate-700/50 bg-slate-800/40 px-4 py-3 text-sm font-black text-slate-300">
                            <input x-model="cloudflareConfig.waf_enabled" type="checkbox" class="h-4 w-4 rounded border-slate-600 bg-slate-900 text-cyan-400 focus:ring-cyan-400">
                            WAF active
                        </label>
                        <label class="flex items-center gap-3 rounded-xl border border-slate-700/50 bg-slate-800/40 px-4 py-3 text-sm font-black text-slate-300">
                            <input x-model="cloudflareConfig.bot_fight_mode" type="checkbox" class="h-4 w-4 rounded border-slate-600 bg-slate-900 text-cyan-400 focus:ring-cyan-400">
                            Bot fight mode
                        </label>
                        <label class="flex items-center gap-3 rounded-xl border border-slate-700/50 bg-slate-800/40 px-4 py-3 text-sm font-black text-slate-300">
                            <input x-model="cloudflareConfig.under_attack_mode" type="checkbox" class="h-4 w-4 rounded border-slate-600 bg-slate-900 text-red-400 focus:ring-red-400">
                            Under attack
                        </label>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">Browser Cache TTL</label>
                            <input
                                x-model.number="cloudflareConfig.browser_cache_ttl"
                                type="number"
                                min="30"
                                step="1"
                                class="mt-2 h-11 w-full rounded-xl border border-slate-700/50 bg-slate-800/50 px-4 text-sm text-slate-200 outline-none transition-all duration-200 focus:border-cyan-400/50 focus:bg-slate-800/80 focus:ring-2 focus:ring-cyan-400/20"
                            >
                        </div>
                        <div>
                            <label class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">Rate Limit Per Minute</label>
                            <input
                                x-model.number="cloudflareConfig.rate_limit_per_minute"
                                type="number"
                                min="1"
                                step="1"
                                class="mt-2 h-11 w-full rounded-xl border border-slate-700/50 bg-slate-800/50 px-4 text-sm text-slate-200 outline-none transition-all duration-200 focus:border-cyan-400/50 focus:bg-slate-800/80 focus:ring-2 focus:ring-cyan-400/20"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">Notes</label>
                        <textarea
                            x-model="cloudflareConfig.notes"
                            rows="3"
                            placeholder="Internal note for this Cloudflare setup..."
                            class="mt-2 w-full resize-none rounded-xl border border-slate-700/50 bg-slate-800/50 px-4 py-3 text-sm text-slate-200 outline-none transition-all duration-200 placeholder:text-slate-600 focus:border-cyan-400/50 focus:bg-slate-800/80 focus:ring-2 focus:ring-cyan-400/20"
                        ></textarea>
                    </div>

                    <p x-show="cloudflareConfig.error" x-text="cloudflareConfig.error" class="rounded-xl border border-red-400/30 bg-red-500/10 px-4 py-3 text-xs font-black text-red-200"></p>
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-slate-800/50 bg-slate-800/30 px-6 py-5 sm:flex-row sm:justify-end">
                    <button type="button" @click="closeCloudflareConfig" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-700/50 px-5 text-sm font-black text-slate-400 transition-all duration-200 hover:border-slate-600 hover:text-white">
                        Cancel
                    </button>
                    <button type="submit" :disabled="cloudflareConfig.submitting" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-400 to-cyan-500 px-5 text-sm font-black text-slate-950 transition-all duration-200 hover:shadow-lg hover:shadow-cyan-400/25 hover:scale-[1.02] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:scale-100">
                        <svg x-show="cloudflareConfig.submitting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M21 12a9 9 0 1 1-3-6.7"></path>
                        </svg>
                        <span x-text="cloudflareConfig.submitting ? 'Saving...' : 'Save Cloudflare Settings'"></span>
                    </button>
                </div>
            </form>
        </div>

        {{-- MODAL DE CONFIRMATION --}}
        <div x-show="modal.open" x-cloak x-transition.opacity.duration.200ms class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/90 p-4 backdrop-blur-md">
            <div @click.outside="closeModal" x-transition.scale.origin.center.duration.200ms class="w-full max-w-xl overflow-hidden rounded-2xl border border-slate-700/60 bg-gradient-to-br from-slate-900 to-slate-950 shadow-2xl">
                <div class="border-b border-slate-800/50 bg-slate-800/30 px-6 py-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.22em] text-cyan-400">Cloudflare admin action</p>
                            <h2 class="mt-2 text-xl font-black text-white" x-text="modal.title"></h2>
                            <p class="mt-1 font-mono text-sm font-semibold text-slate-500" x-text="modal.domain"></p>
                        </div>
                        <button @click="closeModal" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-700/50 bg-slate-800/50 text-slate-500 transition-all duration-200 hover:border-red-400/40 hover:bg-red-500/10 hover:text-red-300">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 6 6 18"></path>
                                <path d="m6 6 12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="space-y-5 p-6">
                    <div class="rounded-xl border border-cyan-400/20 bg-cyan-400/5 p-4">
                        <p class="text-xs font-black uppercase tracking-[0.16em] text-cyan-300">Action description</p>
                        <p class="mt-1 text-sm font-medium leading-relaxed text-slate-300" x-text="modal.description"></p>
                    </div>
                    <template x-if="modal.inputType">
                        <div>
                            <label class="text-xs font-black uppercase tracking-[0.12em] text-slate-500" x-text="modal.inputLabel"></label>
                            <input 
                                x-model="modal.target" 
                                type="text" 
                                :placeholder="modal.placeholder" 
                                class="mt-2 h-11 w-full rounded-xl border border-slate-700/50 bg-slate-800/50 px-4 text-sm text-slate-200 outline-none transition-all duration-200 placeholder:text-slate-600 focus:border-cyan-400/50 focus:bg-slate-800/80 focus:ring-2 focus:ring-cyan-400/20" 
                                :class="modal.inputType === 'ip' ? 'font-mono' : ''"
                            >
                        </div>
                    </template>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.12em] text-slate-500">Analyst note</label>
                        <textarea 
                            x-model="modal.note" 
                            rows="4" 
                            placeholder="Reason for this action..." 
                            class="mt-2 w-full resize-none rounded-xl border border-slate-700/50 bg-slate-800/50 px-4 py-3 text-sm text-slate-200 outline-none transition-all duration-200 placeholder:text-slate-600 focus:border-cyan-400/50 focus:bg-slate-800/80 focus:ring-2 focus:ring-cyan-400/20"
                        ></textarea>
                    </div>
                    <p x-show="modal.error" x-text="modal.error" class="rounded-xl border border-red-400/30 bg-red-500/10 px-4 py-3 text-xs font-black text-red-200"></p>
                </div>
                <div class="flex flex-col-reverse gap-2 border-t border-slate-800/50 bg-slate-800/30 px-6 py-5 sm:flex-row sm:justify-end">
                    <button @click="closeModal" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-700/50 px-5 text-sm font-black text-slate-400 transition-all duration-200 hover:border-slate-600 hover:text-white">
                        Cancel
                    </button>
                    <button @click="confirmAction" :disabled="modal.submitting" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-400 to-cyan-500 px-5 text-sm font-black text-slate-950 transition-all duration-200 hover:shadow-lg hover:shadow-cyan-400/25 hover:scale-[1.02] disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:scale-100">
                        <svg x-show="modal.submitting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M21 12a9 9 0 1 1-3-6.7"></path>
                        </svg>
                        <span x-text="modal.submitting ? 'Applying...' : 'Confirm Action'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- TOAST NOTIFICATION --}}
        <div x-show="toast.open" x-cloak x-transition.duration.300ms class="fixed bottom-6 right-6 z-50 max-w-sm rounded-2xl border border-emerald-400/30 bg-gradient-to-r from-slate-900 to-slate-950 p-4 shadow-2xl backdrop-blur-md">
            <div class="flex items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-400/10">
                    <svg class="h-4 w-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M20 6L9 17l-5-5"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-black text-emerald-200" x-text="toast.title || 'Action confirmed'"></p>
                    <p class="mt-0.5 text-xs font-medium text-slate-500" x-text="toast.message"></p>
                </div>
            </div>
        </div>

        <style>
            [x-cloak] { display: none !important; }
            .custom-scrollbar::-webkit-scrollbar {
                width: 6px;
                height: 6px;
            }
            .custom-scrollbar::-webkit-scrollbar-track {
                background: rgba(51, 65, 85, 0.3);
                border-radius: 10px;
            }
            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: rgba(6, 182, 212, 0.4);
                border-radius: 10px;
            }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: rgba(6, 182, 212, 0.6);
            }
        </style>
    </div>

    <script>
        function wordpressCloudflareDashboard(config = {}) {
            return {
                cloudflareActionUrl: config.actionUrl || '',
                csrfToken: config.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '',
                serverSites: Array.isArray(config.sites) ? config.sites : [],
                activePage: 'overview',
                search: '',
                protectionFilter: '',
                wafFilter: '',
                cdnFilter: '',
                typeFilter: '',
                sslFilter: '',
                selectedDomain: 'nexus.io',
                openMenu: null,
                currentPage: 1,
                perPage: 5,
                syncingProjectId: null,
                analyticsLoadingProjectId: null,
                modal: { open: false, action: '', title: '', domain: '', description: '', inputType: '', inputLabel: '', placeholder: '', target: '', note: '', error: '', submitting: false },
                cloudflareConfig: {
                    open: false,
                    submitting: false,
                    error: '',
                    project_id: null,
                    domain: '',
                    url: '',
                    method: 'POST',
                    api_token: '',
                    zone_id: '',
                    account_email: '',
                    ssl_mode: 'full_strict',
                    security_level: 'medium',
                    cache_level: 'standard',
                    browser_cache_ttl: 14400,
                    waf_enabled: true,
                    ddos_enabled: true,
                    proxy_enabled: true,
                    bot_fight_mode: false,
                    under_attack_mode: false,
                    rate_limit_per_minute: 120,
                    notes: '',
                },
                toast: { open: false, title: 'Action confirmed', message: '' },

                sites: [],

                actionGroups: [
                    { label: 'Incident Response', actions: [{ key: 'under_attack', label: 'Enable Under Attack Mode' }, { key: 'disable_under_attack', label: 'Disable Under Attack Mode' }, { key: 'block_ip', label: 'Block IP' }, { key: 'allow_ip', label: 'Allow IP' }, { key: 'block_country', label: 'Block Country' }, { key: 'challenge_country', label: 'Challenge Country' }] },
                    { label: 'Cache', actions: [{ key: 'purge_cache', label: 'Purge Full Cache' }, { key: 'purge_url', label: 'Purge Specific URL' }] },
                ],

                actions: {
                    under_attack: { title: 'Enable Under Attack Mode', description: 'Enables Under Attack Mode for emergency mitigation.' },
                    disable_under_attack: { title: 'Disable Under Attack Mode', description: 'Disables Under Attack Mode after the incident is contained.' },
                    block_ip: { title: 'Block IP', description: 'Blocks a malicious IP address.', inputType: 'ip', inputLabel: 'IP Address', placeholder: '185.220.101.44' },
                    allow_ip: { title: 'Allow IP', description: 'Allows a trusted admin IP address.', inputType: 'ip', inputLabel: 'IP Address', placeholder: '203.0.113.10' },
                    block_country: { title: 'Block Country', description: 'Blocks traffic from a country on wp-login/wp-admin paths.', inputType: 'country', inputLabel: 'Country Code', placeholder: 'MA, FR, US' },
                    challenge_country: { title: 'Challenge Country', description: 'Challenges traffic from a country using Managed Challenge.', inputType: 'country', inputLabel: 'Country Code', placeholder: 'MA, FR, US' },
                    purge_cache: { title: 'Purge Full Cache', description: 'Purges the full Cloudflare cache for this site.' },
                    purge_url: { title: 'Purge Specific URL', description: 'Purges one specific URL from Cloudflare cache.', inputType: 'url', inputLabel: 'URL', placeholder: 'https://example.com/page' },
                },

                get selectedSite() { return this.sites.find(s => s.domain === this.selectedDomain) || this.sites[0]; },
                get filteredSites() { const q = this.search.toLowerCase(); return this.sites.filter(s => { return (!q || `${s.domain} ${s.client}`.toLowerCase().includes(q)) && (!this.protectionFilter || (this.protectionFilter === 'protected' && this.isProtected(s)) || (this.protectionFilter === 'risk' && !this.isProtected(s))) && (!this.wafFilter || (this.wafFilter === 'active' && s.waf) || (this.wafFilter === 'off' && !s.waf)) && (!this.cdnFilter || (this.cdnFilter === 'active' && s.cdn) || (this.cdnFilter === 'off' && !s.cdn)) && (!this.typeFilter || s.type === this.typeFilter); }); },
                get paginatedSites() { const start = (this.currentPage - 1) * this.perPage; return this.filteredSites.slice(start, start + this.perPage); },
                get totalPages() { return Math.max(1, Math.ceil(this.filteredSites.length / this.perPage)); },
                get visiblePages() { const pages = []; let start = Math.max(1, this.currentPage - 2); let end = Math.min(this.totalPages, this.currentPage + 2); if (end - start < 4) { if (start === 1) end = Math.min(this.totalPages, start + 4); if (end === this.totalPages) start = Math.max(1, end - 4); } for (let p = start; p <= end; p++) pages.push(p); return pages; },
                get paginationFrom() { return this.filteredSites.length ? (this.currentPage - 1) * this.perPage + 1 : 0; },
                get paginationTo() { return Math.min(this.currentPage * this.perPage, this.filteredSites.length); },
                get protectedSites() { return this.sites.filter(s => this.isProtected(s)).length; },
                get wafActive() { return this.sites.filter(s => s.waf).length; },
                get atRiskSites() { return this.sites.filter(s => !this.isProtected(s)).length; },
                get stats() { return [{ label: 'Total Sites', count: this.sites.length, description: 'Managed WordPress websites.', icon: 'sites', color: 'text-cyan-300', iconClass: 'border-cyan-400/20 bg-cyan-400/10 text-cyan-300' }, { label: 'Protected Sites', count: this.protectedSites, description: 'CDN + WAF enabled.', icon: 'protected', color: 'text-emerald-300', iconClass: 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300' }, { label: 'WAF Active', count: this.wafActive, description: 'Protected by Cloudflare WAF.', icon: 'waf', color: 'text-blue-300', iconClass: 'border-blue-400/20 bg-blue-400/10 text-blue-300' }, { label: 'At Risk', count: this.atRiskSites, description: 'Missing critical protection.', icon: 'risk', color: 'text-amber-300', iconClass: 'border-amber-400/20 bg-amber-400/10 text-amber-300' }]; },
                get topCountries() { return this.selectedSite?.top_countries || []; },
                get topAttackingIPs() { return this.selectedSite?.top_attacking_ips || []; },
                get blockedIPs() {
                    return [...new Set(this.siteLogs(this.selectedSite).filter(log => log.action === 'Blocked' && log.ip && log.ip !== '-').map(log => log.ip))].slice(0, 8);
                },
                get blockedCountries() {
                    return [...new Set(this.siteLogs(this.selectedSite).filter(log => log.action === 'Blocked' && log.country).map(log => log.country))].slice(0, 8);
                },

                isProtected(s) { return s.cdn && s.waf && s.login_protection && s.xmlrpc_blocked && s.bot_protection; },
                countryFlagUrl(code) {
                    return code ? `https://flagcdn.com/w40/${code.toLowerCase()}.png` : '';
                },
                countryName(code) {
                    const names = {
                        US: 'United States',
                        GB: 'United Kingdom',
                        DE: 'Germany',
                        FR: 'France',
                        CA: 'Canada',
                        MA: 'Morocco',
                        ES: 'Spain',
                        NL: 'Netherlands',
                        BR: 'Brazil',
                        IT: 'Italy',
                        RU: 'Russia',
                        CN: 'China',
                    };
                    return names[code] || code || '';
                },
                formatNumber(v) { return new Intl.NumberFormat('en-US').format(v); },
                trafficBars(s) {
                    if (Array.isArray(s?.traffic_bars) && s.traffic_bars.length) {
                        return s.traffic_bars;
                    }

                    return ['00:00', '03:00', '06:00', '09:00', '12:00', '15:00', '18:00', '21:00'].map(hour => ({ hour, requests: 0, value: 4 }));
                },
                siteLogs(s) { return Array.isArray(s?.security_logs) ? s.security_logs : []; },
                actionHint(key) {
                    const hints = {
                        under_attack: 'Emergency edge mitigation',
                        disable_under_attack: 'Return traffic to normal mode',
                        block_ip: 'Deny a malicious source IP',
                        allow_ip: 'Trust an admin source IP',
                        block_country: 'Deny country traffic',
                        challenge_country: 'Managed challenge by country',
                        purge_cache: 'Clear all cached assets',
                        purge_url: 'Clear one cached URL',
                    };
                    return hints[key] || 'Cloudflare action';
                },
                actionButtonClass(key) {
                    if (['under_attack', 'block_ip', 'block_country'].includes(key)) return 'border-red-400/25 bg-red-500/10 hover:border-red-400/40 hover:bg-red-500/15';
                    if (key === 'challenge_country') return 'border-amber-400/25 bg-amber-400/10 hover:border-amber-400/40 hover:bg-amber-400/15';
                    if (key === 'allow_ip') return 'border-emerald-400/25 bg-emerald-400/10 hover:border-emerald-400/40 hover:bg-emerald-400/15';
                    return 'border-cyan-400/20 bg-cyan-400/5 hover:border-cyan-400/40 hover:bg-cyan-400/10';
                },
                actionIconClass(key) {
                    if (['under_attack', 'block_ip', 'block_country'].includes(key)) return 'border-red-400/25 text-red-300';
                    if (key === 'challenge_country') return 'border-amber-400/25 text-amber-300';
                    if (key === 'allow_ip') return 'border-emerald-400/25 text-emerald-300';
                    return 'border-cyan-400/20 text-cyan-300';
                },
                logActionClass(action) {
                    const classes = {
                        Blocked: 'border-red-400/30 bg-red-500/10 text-red-300',
                        'Managed Challenge': 'border-amber-400/30 bg-amber-400/10 text-amber-300',
                        Challenge: 'border-amber-400/30 bg-amber-400/10 text-amber-300',
                        Monitor: 'border-slate-600/60 bg-slate-800/50 text-slate-300',
                    };
                    return classes[action] || 'border-cyan-400/25 bg-cyan-400/10 text-cyan-300';
                },
                severityClass(s) { 
                    const classes = {
                        High: 'border-red-400/30 bg-red-500/10 text-red-300',
                        Medium: 'border-amber-400/30 bg-amber-400/10 text-amber-300',
                        Low: 'border-emerald-400/30 bg-emerald-400/10 text-emerald-300'
                    };
                    return classes[s] || classes.Medium;
                },
                severityDotClass(s) {
                    const classes = {
                        High: 'bg-red-400',
                        Medium: 'bg-amber-300',
                        Low: 'bg-emerald-300',
                    };
                    return classes[s] || classes.Medium;
                },
                resetFilters() { this.search = ''; this.protectionFilter = ''; this.wafFilter = ''; this.cdnFilter = ''; this.typeFilter = ''; this.currentPage = 1; },
                openDashboard(s) {
                    this.selectedDomain = s.domain;
                    this.activePage = 'dashboard';
                    this.loadCloudflareAnalytics(s);
                },
                backToOverview() { this.activePage = 'overview'; },
                openCloudflareConfig(site) {
                    if (!site) return;

                    const settings = site.cloudflare_settings || {};
                    const hasApiConnection = Boolean(site.zone_id || site.cloudflare_token_saved);

                    this.cloudflareConfig = {
                        open: true,
                        submitting: false,
                        error: '',
                        project_id: site.project_id || null,
                        domain: site.domain || '',
                        url: hasApiConnection && site.update_url ? site.update_url : (site.connect_url || site.update_url || ''),
                        method: hasApiConnection && site.update_url ? 'PATCH' : 'POST',
                        api_token: '',
                        zone_id: site.zone_id || '',
                        account_email: site.cloudflare_account_email || '',
                        ssl_mode: settings.ssl_mode || this.sslValue(site.ssl) || 'full_strict',
                        security_level: settings.security_level || 'medium',
                        cache_level: settings.cache_level || 'standard',
                        browser_cache_ttl: Number(settings.browser_cache_ttl || 14400),
                        waf_enabled: settings.waf_enabled ?? Boolean(site.waf),
                        ddos_enabled: settings.ddos_enabled ?? true,
                        proxy_enabled: settings.proxy_enabled ?? Boolean(site.cdn),
                        bot_fight_mode: settings.bot_fight_mode ?? Boolean(site.bot_protection),
                        under_attack_mode: settings.under_attack_mode ?? false,
                        rate_limit_per_minute: Number(settings.rate_limit_per_minute || 120),
                        notes: settings.notes || '',
                    };
                },
                closeCloudflareConfig() {
                    this.cloudflareConfig.open = false;
                    this.cloudflareConfig.error = '';
                },
                sslValue(label) {
                    const value = String(label || '').toLowerCase().replace(/\s+/g, '_');
                    return value === 'full_strict' || value === 'full' || value === 'flexible' || value === 'off' ? value : '';
                },
                async saveCloudflareConfig() {
                    if (this.cloudflareConfig.submitting) return;

                    if (!this.cloudflareConfig.project_id) {
                        this.cloudflareConfig.error = 'Select a project before connecting Cloudflare.';
                        return;
                    }

                    if (!this.cloudflareConfig.url) {
                        this.cloudflareConfig.error = 'Cloudflare route is missing for this project.';
                        return;
                    }

                    this.cloudflareConfig.submitting = true;
                    this.cloudflareConfig.error = '';

                    try {
                        const response = await fetch(this.cloudflareConfig.url, {
                            method: this.cloudflareConfig.method,
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                            },
                            body: JSON.stringify({
                                project_id: this.cloudflareConfig.project_id,
                                api_token: this.cloudflareConfig.api_token,
                                zone_id: this.cloudflareConfig.zone_id || null,
                                account_email: this.cloudflareConfig.account_email || null,
                                ssl_mode: this.cloudflareConfig.ssl_mode,
                                security_level: this.cloudflareConfig.security_level,
                                cache_level: this.cloudflareConfig.cache_level,
                                browser_cache_ttl: Number(this.cloudflareConfig.browser_cache_ttl || 14400),
                                waf_enabled: Boolean(this.cloudflareConfig.waf_enabled),
                                ddos_enabled: Boolean(this.cloudflareConfig.ddos_enabled),
                                proxy_enabled: Boolean(this.cloudflareConfig.proxy_enabled),
                                bot_fight_mode: Boolean(this.cloudflareConfig.bot_fight_mode),
                                under_attack_mode: Boolean(this.cloudflareConfig.under_attack_mode),
                                rate_limit_per_minute: Number(this.cloudflareConfig.rate_limit_per_minute || 120),
                                notes: this.cloudflareConfig.notes || '',
                            }),
                        });
                        const payload = await response.json().catch(() => ({}));

                        if (!response.ok || payload.ok === false || payload.success === false) {
                            this.cloudflareConfig.error = payload.message || Object.values(payload.errors || {}).flat()[0] || 'Cloudflare API request failed.';
                            return;
                        }

                        if (payload.site) {
                            this.mergeSitePayload(payload.site);
                        }

                        if (payload.warnings?.length) {
                            this.cloudflareConfig.error = payload.warnings.join(' ');
                            return;
                        }

                        this.closeCloudflareConfig();
                        this.showToast(payload.message || 'Cloudflare API connected.', 'Cloudflare updated');
                        this.loadCloudflareAnalytics(this.selectedSite, true);
                    } catch (error) {
                        this.cloudflareConfig.error = error?.message || 'Cloudflare API request failed.';
                    } finally {
                        this.cloudflareConfig.submitting = false;
                    }
                },
                async loadCloudflareAnalytics(site = this.selectedSite, force = false) {
                    if (!site || !site.analytics_url) return;
                    if (!force && site.analytics_loaded) return;

                    this.analyticsLoadingProjectId = site.project_id;
                    this.mergeSitePayload({
                        project_id: site.project_id,
                        analytics_error: '',
                        analytics_warning: '',
                    });

                    try {
                        const response = await fetch(`${site.analytics_url}?hours=24`, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                            },
                        });
                        const payload = await response.json().catch(() => ({}));

                        if (!response.ok || payload.ok === false || payload.success === false) {
                            throw new Error(payload.message || Object.values(payload.errors || {}).flat()[0] || 'Cloudflare analytics request failed.');
                        }

                        this.mergeSitePayload({
                            project_id: site.project_id,
                            ...(payload.analytics || {}),
                            analytics_loaded: true,
                            analytics_error: '',
                            analytics_warning: Array.isArray(payload.warnings) && payload.warnings.length ? payload.warnings.join(' ') : '',
                        });
                    } catch (error) {
                        this.mergeSitePayload({
                            project_id: site.project_id,
                            analytics_loaded: true,
                            analytics_error: error?.message || 'Cloudflare analytics request failed.',
                            analytics_warning: '',
                        });
                    } finally {
                        this.analyticsLoadingProjectId = null;
                    }
                },
                async syncCloudflare(site) {
                    if (!site) return;

                    if (!site.sync_url) {
                        this.openCloudflareConfig(site);
                        this.cloudflareConfig.error = 'Connect Cloudflare before syncing this project.';
                        return;
                    }

                    this.syncingProjectId = site.project_id;

                    try {
                        const response = await fetch(site.sync_url, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                            },
                            body: JSON.stringify({}),
                        });
                        const payload = await response.json().catch(() => ({}));

                        if (!response.ok || payload.ok === false || payload.success === false) {
                            throw new Error(payload.message || Object.values(payload.errors || {}).flat()[0] || 'Cloudflare sync failed.');
                        }

                        if (payload.site) {
                            this.mergeSitePayload(payload.site);
                        }

                        this.showToast(payload.message || 'Cloudflare zone synced.', 'Cloudflare synced');
                        this.loadCloudflareAnalytics(this.selectedSite, true);
                    } catch (error) {
                        this.openCloudflareConfig(site);
                        this.cloudflareConfig.error = error?.message || 'Cloudflare sync failed.';
                    } finally {
                        this.syncingProjectId = null;
                    }
                },
                mergeSitePayload(patch = {}) {
                    const projectId = patch.project_id || this.cloudflareConfig.project_id;
                    const domain = patch.domain || this.cloudflareConfig.domain;
                    const index = this.sites.findIndex(site => (projectId && site.project_id === projectId) || (domain && site.domain === domain));

                    if (index === -1) return;

                    this.sites.splice(index, 1, { ...this.sites[index], ...patch });
                    this.selectedDomain = this.sites[index].domain;
                },
                showToast(message, title = 'Action confirmed') {
                    this.toast = { open: true, title, message };
                    setTimeout(() => { this.toast.open = false; }, 3500);
                },
                openModal(action, site, presetTarget = null) { 
                    const a = this.actions[action]; 
                    if (!a || !site) return; 
                    this.modal = { 
                        open: true, 
                        action, 
                        title: a.title, 
                        domain: site.domain, 
                        description: a.description, 
                        inputType: a.inputType || '', 
                        inputLabel: a.inputLabel || '', 
                        placeholder: a.placeholder || '', 
                        target: presetTarget || a.defaultTarget || '', 
                        note: '', 
                        error: '',
                        submitting: false,
                    }; 
                },
                closeModal() { this.modal.open = false; this.modal.error = ''; },
                async confirmAction() { 
                    if (this.modal.submitting) return;

                    if (this.modal.inputType && !this.modal.target.trim()) { 
                        this.modal.error = 'Please complete the target field.'; 
                        return; 
                    } 
                    if (!this.modal.note.trim()) { 
                        this.modal.error = 'Please add an analyst note.'; 
                        return; 
                    } 

                    const site = this.sites.find(item => item.domain === this.modal.domain) || this.selectedSite;

                    if (!site?.project_id && !site?.zone_id) {
                        this.modal.error = 'Connect this site to Cloudflare first, or add a zone ID.';
                        return;
                    }

                    this.modal.submitting = true;
                    this.modal.error = '';

                    try {
                        const response = await fetch(this.cloudflareActionUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                            },
                            body: JSON.stringify({
                                action: this.modal.action,
                                project_id: site.project_id || null,
                                zone_id: site.zone_id || null,
                                target: this.modal.target,
                                note: this.modal.note,
                            }),
                        });
                        const payload = await response.json().catch(() => ({}));

                        if (!response.ok || payload.ok === false) {
                            this.modal.error = payload.message || Object.values(payload.errors || {}).flat()[0] || 'Cloudflare API request failed.';
                            return;
                        }

                        if (payload.site) {
                            this.mergeSitePayload(payload.site);
                        }

                        this.showToast(payload.message || `${this.modal.title} confirmed for ${this.modal.domain}.`);
                        this.closeModal();
                    } catch (error) {
                        this.modal.error = error?.message || 'Cloudflare API request failed.';
                    } finally {
                        this.modal.submitting = false;
                    }
                },
                nextPage() { if (this.currentPage < this.totalPages) this.currentPage++; },
                prevPage() { if (this.currentPage > 1) this.currentPage--; },
                goToPage(p) { if (p >= 1 && p <= this.totalPages) this.currentPage = p; },
                init() {
                    if (this.serverSites.length) {
                        this.sites = this.serverSites;
                        this.selectedDomain = this.sites[0]?.domain || '';
                    }
                }
            };
        }
    </script>
</x-dashboard-layout>
