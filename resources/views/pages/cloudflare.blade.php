<x-dashboard-layout>
    <div x-data="cloudflarePage()" class="space-y-7">

        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-4xl font-black tracking-tight text-white">
                    Cloudflare
                </h1>

                <p class="mt-3 text-sm font-semibold text-slate-700">
                    <span x-text="stats.wafActive"></span> zones with WAF active
                </p>
            </div>

            <button
                type="button"
                class="inline-flex h-11 items-center gap-2 rounded-xl border border-cyan-400/30 bg-cyan-400/10 px-5 text-sm font-black text-cyan-300"
            >
                <span class="text-xl">+</span>
                Add Zone
            </button>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-800 bg-[#07111f] px-7 py-8">
                <p class="text-5xl font-black text-orange-500" x-text="stats.zones"></p>
                <p class="mt-5 text-sm font-semibold text-slate-600">Zones</p>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-[#07111f] px-7 py-8">
                <p class="text-5xl font-black text-emerald-500" x-text="stats.wafActive"></p>
                <p class="mt-5 text-sm font-semibold text-slate-600">WAF Active</p>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-[#07111f] px-7 py-8">
                <p class="text-5xl font-black text-cyan-400" x-text="stats.ddosEnabled"></p>
                <p class="mt-5 text-sm font-semibold text-slate-600">DDoS Enabled</p>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-[#07111f] px-7 py-8">
                <p class="text-5xl font-black text-violet-400" x-text="stats.proxied"></p>
                <p class="mt-5 text-sm font-semibold text-slate-600">Proxied</p>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-800 bg-[#07111f]">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px]">
                    <thead>
                        <tr class="border-b border-slate-800 text-left text-[11px] uppercase tracking-[0.32em] text-cyan-900">
                            <th class="px-6 py-5">Domain</th>
                            <th class="px-6 py-5">Client</th>
                            <th class="px-6 py-5">WAF</th>
                            <th class="px-6 py-5">DDoS</th>
                            <th class="px-6 py-5">SSL Mode</th>
                            <th class="px-6 py-5">Proxy Status</th>
                            <th class="px-6 py-5">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-800">
                        <template x-for="zone in zones" :key="zone.id">
                            <tr class="hover:bg-white/[0.02]">
                                <td class="px-6 py-6">
                                    <p class="font-mono text-sm font-bold text-cyan-300" x-text="zone.domain"></p>
                                </td>

                                <td class="px-6 py-6">
                                    <p class="text-sm font-bold text-slate-600" x-text="zone.client"></p>
                                </td>

                                <td class="px-6 py-6">
                                    <span
                                        class="text-sm font-black"
                                        :class="zone.waf ? 'text-emerald-400' : 'text-red-400'"
                                        x-text="zone.waf ? 'Active' : 'Off'"
                                    ></span>
                                </td>

                                <td class="px-6 py-6">
                                    <span
                                        class="text-sm font-black"
                                        :class="zone.ddos ? 'text-emerald-400' : 'text-red-400'"
                                        x-text="zone.ddos ? 'Active' : 'Off'"
                                    ></span>
                                </td>

                                <td class="px-6 py-6">
                                    <p class="text-sm font-bold text-slate-500" x-text="zone.ssl"></p>
                                </td>

                                <td class="px-6 py-6">
                                    <span
                                        class="text-sm font-black"
                                        :class="zone.proxy === 'Proxied' ? 'text-emerald-400' : 'text-slate-600'"
                                        x-text="zone.proxy"
                                    ></span>
                                </td>

                                <td class="px-6 py-6">
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            class="inline-flex h-10 items-center gap-2 rounded-lg border border-cyan-400/30 bg-cyan-400/10 px-4 text-sm font-black text-cyan-300"
                                        >
                                            Dashboard
                                        </button>

                                        <button
                                            type="button"
                                            class="inline-flex h-10 items-center rounded-lg border border-slate-800 bg-[#07111f] px-4 text-sm font-black text-slate-600"
                                        >
                                            Purge Cache
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
        function cloudflarePage() {
            return {
                zones: @js($zones ?? [
                    { id: 1, domain: 'nexus.io', client: 'Nexus Corp', waf: true, ddos: true, ssl: 'Full', proxy: 'Proxied' },
                    { id: 2, domain: 'datavault.io', client: 'DataVault Inc', waf: true, ddos: true, ssl: 'Full', proxy: 'Proxied' },
                    { id: 3, domain: 'techprime.io', client: 'TechPrime', waf: false, ddos: false, ssl: 'Full', proxy: 'DNS Only' },
                    { id: 4, domain: 'bank.io', client: 'SecureBank', waf: true, ddos: true, ssl: 'Full', proxy: 'Proxied' },
                    { id: 5, domain: 'cloudmesh.io', client: 'CloudMesh', waf: false, ddos: false, ssl: 'Flexible', proxy: 'DNS Only' },
                    { id: 6, domain: 'agencyhub.io', client: 'AgencyHub', waf: true, ddos: true, ssl: 'Full', proxy: 'Proxied' },
                ]),

                get stats() {
                    return {
                        zones: this.zones.length,
                        wafActive: this.zones.filter(zone => zone.waf).length,
                        ddosEnabled: this.zones.filter(zone => zone.ddos).length,
                        proxied: this.zones.filter(zone => zone.proxy === 'Proxied').length,
                    };
                },
            };
        }
    </script>
</x-dashboard-layout>
