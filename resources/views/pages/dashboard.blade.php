<x-dashboard-layout>
    @php
        $stats = array_merge([
            'clients' => 0,
            'projects' => 0,
            'protected_projects' => 0,
            'online_agents' => 0,
            'offline_agents' => 0,
            'threats_today' => 0,
            'open_incidents' => 0,
            'critical_signals' => 0,
            'logs_today' => 0,
            'avg_score' => 0,
        ], $stats ?? []);

        $liveThreats = collect($liveThreats ?? []);
        $topAttacked = collect($topAttacked ?? []);
        $mapPoints = collect($mapPoints ?? []);
        $recentLogs = collect($recentLogs ?? []);
        $weeklyThreats = collect($weeklyThreats ?? []);
        $unknownGeoCount = (int) ($unknownGeoCount ?? 0);
        
        // FIX: Handle empty weekly threats with fallback data
        if ($weeklyThreats->isEmpty()) {
            $weeklyThreats = collect([
                ['label' => 'MON', 'count' => 0],
                ['label' => 'TUE', 'count' => 0],
                ['label' => 'WED', 'count' => 0],
                ['label' => 'THU', 'count' => 0],
                ['label' => 'FRI', 'count' => 0],
                ['label' => 'SAT', 'count' => 0],
                ['label' => 'SUN', 'count' => 0],
            ]);
        }
        
        $maxWeeklyThreats = max(1, (int) ($weeklyThreats->max('count') ?? 0));
        $agentTotal = (int) $stats['online_agents'] + (int) $stats['offline_agents'];

        $coverage = $stats['projects'] > 0
            ? (int) round(($stats['protected_projects'] / $stats['projects']) * 100)
            : 0;

        $agentHealth = $agentTotal > 0
            ? (int) round(($stats['online_agents'] / $agentTotal) * 100)
            : 0;

        $criticalShare = $stats['threats_today'] > 0
            ? (int) round(($stats['critical_signals'] / $stats['threats_today']) * 100)
            : 0;

        $scoreLabel = $stats['avg_score'] >= 85
            ? 'Strong'
            : ($stats['avg_score'] >= 65 ? 'Watch' : 'Risk');

        $severityClass = static function (string $severity): string {
            return [
                'critical' => 'border-red-500/40 bg-red-500/10 text-red-200 shadow-[0_0_8px_rgba(244,63,94,0.3)]',
                'high' => 'border-orange-500/40 bg-orange-500/10 text-orange-200 shadow-[0_0_6px_rgba(251,146,60,0.2)]',
                'medium' => 'border-amber-500/40 bg-amber-500/10 text-amber-200',
                'low' => 'border-emerald-500/40 bg-emerald-500/10 text-emerald-200',
                'info' => 'border-cyan-500/40 bg-cyan-500/10 text-cyan-200',
            ][$severity] ?? 'border-slate-700 bg-slate-800/50 text-slate-300';
        };

        $scoreTone = $stats['avg_score'] >= 85
            ? 'text-emerald-400 drop-shadow-[0_0_6px_rgba(52,211,153,0.3)]'
            : ($stats['avg_score'] >= 65 ? 'text-amber-400' : 'text-red-400');

        $cards = [
            ['label' => 'Clients', 'value' => $stats['clients'], 'detail' => 'Managed accounts', 'tone' => 'text-sky-300', 'ring' => 'border-sky-500/25 bg-sky-500/5', 'icon' => 'users', 'status' => $stats['projects'].' projects'],
            ['label' => 'Protected', 'value' => $stats['protected_projects'].'/'.$stats['projects'], 'detail' => 'Cloudflare coverage', 'tone' => 'text-cyan-300', 'ring' => 'border-cyan-500/25 bg-cyan-500/5', 'icon' => 'sites', 'status' => $coverage.'% covered'],
            ['label' => 'Agents Online', 'value' => $stats['online_agents'], 'detail' => $stats['offline_agents'].' offline / '.$agentTotal.' total', 'tone' => 'text-emerald-300', 'ring' => 'border-emerald-500/25 bg-emerald-500/5', 'icon' => 'agent', 'status' => $agentHealth.'% online'],
            ['label' => 'Threats 24h', 'value' => $stats['threats_today'], 'detail' => $stats['critical_signals'].' critical signals', 'tone' => 'text-orange-300', 'ring' => 'border-orange-500/25 bg-orange-500/5', 'icon' => 'bolt', 'status' => $criticalShare.'% severe'],
            ['label' => 'Open Incidents', 'value' => $stats['open_incidents'], 'detail' => 'Active response queue', 'tone' => 'text-red-300', 'ring' => 'border-red-500/25 bg-red-500/5', 'icon' => 'incident', 'status' => $stats['open_incidents'] ? 'Action needed' : 'Clear'],
            ['label' => 'Score', 'value' => $stats['avg_score'], 'detail' => 'Average project posture', 'tone' => $scoreTone, 'ring' => 'border-violet-500/25 bg-violet-500/5', 'icon' => 'shield', 'status' => $scoreLabel],
        ];

        $mapPayload = $mapPoints
            ->map(function ($point) {
                return [
                    'lat' => (float) ($point['lat'] ?? 0),
                    'lng' => (float) ($point['lng'] ?? 0),
                    'severity' => strtolower((string) ($point['severity'] ?? 'medium')),
                    'ip' => $point['ip'] ?? 'Unknown IP',
                    'event' => $point['event'] ?? $point['type'] ?? 'Security signal',
                    'asset' => $point['asset'] ?? $point['project'] ?? 'Protected asset',
                    'country' => $point['country'] ?? '-',
                    'city' => $point['city'] ?? '-',
                ];
            })
            ->filter(fn ($point) => $point['lat'] !== 0.0 || $point['lng'] !== 0.0)
            ->values();
    @endphp

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
        <style>
            :root {
                color-scheme: dark;
                --soc-glow-cyan: rgba(34, 211, 238, 0.15);
                --soc-glow-red: rgba(244, 63, 94, 0.12);
                --soc-panel: rgba(8, 14, 26, 0.92);
                --soc-border: rgba(71, 85, 105, 0.35);
                --soc-border-strong: rgba(34, 211, 238, 0.4);
            }

            body {
                background: #030712;
            }

            #worldMap {
                background: #050b14;
            }
            .leaflet-container {
                background: #050b14;
                font-family: 'Inter', 'Segoe UI', monospace;
            }
            .leaflet-control-attribution {
                display: none !important;
            }
            .leaflet-popup-content-wrapper, .leaflet-popup-tip {
                background: rgba(3, 7, 18, 0.96);
                backdrop-filter: blur(16px);
                border: 1px solid rgba(34, 211, 238, 0.35);
                border-radius: 14px;
                box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.9);
            }
            .leaflet-popup-content {
                margin: 12px 16px;
                min-width: 210px;
            }

            .attack-pulse {
                width: 10px;
                height: 10px;
                border-radius: 999px;
                position: relative;
                transform: translate(-5px, -5px);
                box-shadow: 0 0 10px currentColor;
                transition: all 0.2s ease;
            }
            .attack-pulse:hover {
                transform: translate(-5px, -5px) scale(1.3);
            }
            .attack-pulse::before {
                content: "";
                position: absolute;
                inset: -12px;
                border-radius: 999px;
                background: currentColor;
                opacity: 0.3;
                animation: socPulse 1.8s ease-out infinite;
            }
            .attack-pulse::after {
                content: "";
                position: absolute;
                inset: -5px;
                border-radius: 999px;
                background: currentColor;
                opacity: 0.7;
                animation: socPulseRing 1.8s ease-out infinite;
            }
            @keyframes socPulse {
                0% { transform: scale(0.3); opacity: 0.5; }
                100% { transform: scale(1.8); opacity: 0; }
            }
            @keyframes socPulseRing {
                0% { transform: scale(0.5); opacity: 0.8; }
                100% { transform: scale(2.2); opacity: 0; }
            }

            .custom-scroll::-webkit-scrollbar {
                width: 3px;
                height: 3px;
            }
            .custom-scroll::-webkit-scrollbar-track {
                background: #0f172a;
                border-radius: 10px;
            }
            .custom-scroll::-webkit-scrollbar-thumb {
                background: #2d3a5e;
                border-radius: 10px;
            }
            .custom-scroll::-webkit-scrollbar-thumb:hover {
                background: #22d3ee;
            }

            .glow-card {
                transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
                position: relative;
                overflow: hidden;
            }
            .glow-card::after {
                content: '';
                position: absolute;
                inset: 0;
                background: radial-gradient(circle at 30% 20%, rgba(255,255,255,0.03), transparent 70%);
                opacity: 0;
                transition: opacity 0.3s ease;
                pointer-events: none;
            }
            .glow-card:hover::after {
                opacity: 1;
            }
            .glow-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 20px 30px -18px rgba(0, 0, 0, 0.8);
                border-color: var(--soc-border-strong);
            }

            .threat-item {
                transition: all 0.18s ease;
                position: relative;
            }
            .threat-item:hover {
                background: linear-gradient(90deg, rgba(34, 211, 238, 0.04), transparent);
                border-left-color: #22d3ee !important;
            }

            .soc-shell {
                position: relative;
                isolation: isolate;
            }
            .soc-shell::before {
                content: '';
                position: fixed;
                inset: 0;
                pointer-events: none;
                background: 
                    radial-gradient(circle at 20% 30%, rgba(14, 165, 233, 0.03), transparent 45%),
                    linear-gradient(rgba(148, 163, 184, 0.02) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(148, 163, 184, 0.02) 1px, transparent 1px);
                background-size: auto, 50px 50px, 50px 50px;
                mask-image: linear-gradient(to bottom, black, transparent 85%);
                z-index: -1;
            }
            .soc-panel {
                background: linear-gradient(145deg, rgba(12, 18, 34, 0.88), rgba(6, 10, 20, 0.92));
                backdrop-filter: blur(12px);
                border: 1px solid var(--soc-border);
                border-radius: 20px;
                box-shadow: 0 8px 20px -12px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.02);
            }
            .soc-panel:hover {
                border-color: var(--soc-border-strong);
                transition: border-color 0.2s ease;
            }
            .metric-card {
                min-height: 170px;
                background: linear-gradient(135deg, rgba(5, 10, 20, 0.7), rgba(2, 6, 18, 0.8));
            }
            .stat-divider {
                height: 1px;
                background: linear-gradient(90deg, transparent, rgba(34, 211, 238, 0.4), transparent);
            }
            .dashboard-action {
                transition: all 0.2s cubic-bezier(0.2, 0.9, 0.4, 1.1);
                letter-spacing: 0.03em;
            }
            .dashboard-action:active {
                transform: translateY(0px);
            }
            @media (max-width: 640px) {
                .dashboard-action {
                    flex: 1 1 100%;
                    justify-content: center;
                }
                .leaflet-top.leaflet-left {
                    transform: scale(0.85);
                    transform-origin: top left;
                }
            }
            @keyframes fadeSlideUp {
                from { opacity: 0; transform: translateY(8px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .animate-fade-up {
                animation: fadeSlideUp 0.4s ease-out forwards;
            }
            .stat-number {
                font-feature-settings: "tnum";
                font-variant-numeric: tabular-nums;
            }
            .hover-lift {
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }
            .hover-lift:hover {
                transform: translateY(-2px);
                box-shadow: 0 12px 20px -15px rgba(0,0,0,0.5);
            }
            .glow-text {
                text-shadow: 0 0 8px currentColor;
            }

            /* Weekly Chart Enhancements */
            .weekly-chart-container {
                background: linear-gradient(180deg, rgba(6, 12, 24, 0.6), rgba(2, 6, 18, 0.4));
                border-radius: 16px;
                padding: 4px;
            }
            .weekly-bar {
                transition: all 0.4s cubic-bezier(0.34, 1.2, 0.64, 1);
                position: relative;
                overflow: hidden;
            }
            .weekly-bar::after {
                content: '';
                position: absolute;
                inset: 0;
                background: linear-gradient(180deg, rgba(255,255,255,0.1), transparent);
                opacity: 0;
                transition: opacity 0.3s;
            }
            .weekly-bar:hover::after {
                opacity: 1;
            }
            .weekly-bar:hover {
                filter: brightness(1.1);
                transform: scaleX(1.02);
            }
            .weekly-value {
                font-feature-settings: "tnum";
                transition: all 0.2s;
            }
            .trend-indicator {
                background: rgba(0,0,0,0.5);
                backdrop-filter: blur(4px);
                border-radius: 20px;
                padding: 2px 8px;
            }

            html:not(.dark) {
                color-scheme: light;
                --soc-glow-cyan: rgba(8, 145, 178, 0.14);
                --soc-glow-red: rgba(225, 29, 72, 0.10);
                --soc-panel: rgba(255, 255, 255, 0.92);
                --soc-border: rgba(203, 213, 225, 0.85);
                --soc-border-strong: rgba(8, 145, 178, 0.35);
            }

            html:not(.dark) body {
                background: #f8fafc;
            }

            html:not(.dark) .soc-shell {
                background: linear-gradient(180deg, #f8fafc 0%, #eef6fb 100%);
                color: #0f172a;
            }

            html:not(.dark) .soc-shell::before {
                background:
                    radial-gradient(circle at 18% 18%, rgba(8, 145, 178, 0.08), transparent 34%),
                    radial-gradient(circle at 82% 12%, rgba(249, 115, 22, 0.07), transparent 28%),
                    linear-gradient(rgba(15, 23, 42, 0.035) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(15, 23, 42, 0.035) 1px, transparent 1px);
                background-size: auto, auto, 50px 50px, 50px 50px;
            }

            html:not(.dark) .soc-panel,
            html:not(.dark) .metric-card {
                background: linear-gradient(145deg, rgba(255, 255, 255, 0.94), rgba(241, 245, 249, 0.88));
                border-color: var(--soc-border);
                box-shadow: 0 18px 35px -24px rgba(15, 23, 42, 0.32), inset 0 1px 0 rgba(255, 255, 255, 0.8);
            }

            html:not(.dark) .glow-card:hover {
                box-shadow: 0 22px 36px -24px rgba(8, 145, 178, 0.38);
            }

            html:not(.dark) .weekly-chart-container {
                background: linear-gradient(180deg, rgba(226, 232, 240, 0.72), rgba(248, 250, 252, 0.58));
            }

            html:not(.dark) .trend-indicator,
            html:not(.dark) .soc-shell [class*="bg-slate-900"],
            html:not(.dark) .soc-shell [class*="bg-slate-950"],
            html:not(.dark) .soc-shell [class*="bg-slate-800"] {
                background-color: rgba(241, 245, 249, 0.78);
            }

            html:not(.dark) .soc-shell [class*="border-slate-700"],
            html:not(.dark) .soc-shell [class*="border-slate-800"] {
                border-color: rgba(203, 213, 225, 0.9);
            }

            html:not(.dark) .soc-shell .text-white,
            html:not(.dark) .soc-shell .text-slate-200,
            html:not(.dark) .soc-shell .text-slate-300 {
                color: #0f172a;
            }

            html:not(.dark) .soc-shell .text-slate-400 {
                color: #475569;
            }

            html:not(.dark) .soc-shell .text-slate-500,
            html:not(.dark) .soc-shell .text-slate-600 {
                color: #64748b;
            }

            html:not(.dark) .soc-shell .text-cyan-200,
            html:not(.dark) .soc-shell .text-cyan-300,
            html:not(.dark) .soc-shell .text-cyan-400 {
                color: #0891b2;
            }

            html:not(.dark) .soc-shell .text-emerald-300,
            html:not(.dark) .soc-shell .text-emerald-400 {
                color: #059669;
            }

            html:not(.dark) .soc-shell .text-orange-300,
            html:not(.dark) .soc-shell .text-orange-400 {
                color: #ea580c;
            }

            html:not(.dark) .soc-shell .text-amber-200,
            html:not(.dark) .soc-shell .text-amber-300,
            html:not(.dark) .soc-shell .text-amber-400 {
                color: #d97706;
            }

            html:not(.dark) .soc-shell .text-red-200,
            html:not(.dark) .soc-shell .text-red-300,
            html:not(.dark) .soc-shell .text-red-400 {
                color: #dc2626;
            }

            html:not(.dark) .soc-shell .bg-gradient-to-r.from-white,
            html:not(.dark) .soc-shell .bg-gradient-to-r.from-white.to-slate-300 {
                background-image: linear-gradient(to right, #0f172a, #334155);
            }

            html:not(.dark) .stat-divider {
                background: linear-gradient(90deg, transparent, rgba(8, 145, 178, 0.28), transparent);
            }

            html:not(.dark) .threat-item:hover {
                background: linear-gradient(90deg, rgba(8, 145, 178, 0.08), transparent);
            }

            html:not(.dark) #worldMap,
            html:not(.dark) .leaflet-container {
                background: #e2e8f0;
            }

            html:not(.dark) .leaflet-popup-content-wrapper,
            html:not(.dark) .leaflet-popup-tip {
                background: rgba(255, 255, 255, 0.96);
                border-color: rgba(8, 145, 178, 0.25);
                box-shadow: 0 18px 28px -18px rgba(15, 23, 42, 0.35);
            }

            html:not(.dark) .custom-scroll::-webkit-scrollbar-track {
                background: #e2e8f0;
            }

            html:not(.dark) .custom-scroll::-webkit-scrollbar-thumb {
                background: #94a3b8;
            }

            html:not(.dark) .custom-scroll::-webkit-scrollbar-thumb:hover {
                background: #0891b2;
            }

            @media (prefers-reduced-motion: reduce) {
                *, *::before, *::after {
                    animation-duration: 0.01ms !important;
                    animation-iteration-count: 1 !important;
                    transition-duration: 0.01ms !important;
                }
            }
        </style>
    @endpush

    <div class="soc-shell min-h-screen space-y-6 overflow-hidden bg-[#030712] px-4 py-5 text-white sm:px-6 lg:px-8">
        <!-- Header -->
        <header class="soc-panel relative overflow-hidden rounded-2xl px-6 py-6 sm:px-8 flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between animate-fade-up">
            <div class="relative">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-1.5 rounded-full bg-gradient-to-b from-cyan-400 to-cyan-600"></div>
                    <p class="text-[11px] font-black tracking-[0.2em] uppercase text-cyan-300">SOC v2.0 | Zero-Trust Defense Grid</p>
                </div>
                <h1 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl lg:text-5xl bg-gradient-to-r from-white to-slate-300 bg-clip-text text-transparent">Security Operations Center</h1>
                <p class="mt-2 flex max-w-2xl items-center gap-2 text-sm font-semibold text-slate-400">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500 shadow-[0_0_8px_#10b981]"></span>
                    </span>
                    Live telemetry / Proactive hunting / Incident ready
                </p>
            </div>
            <div class="flex w-full flex-wrap gap-3 xl:w-auto">
                <a href="{{ route('alerts.index') }}" class="dashboard-action group relative inline-flex h-11 items-center rounded-xl border border-slate-700/60 bg-slate-900/60 px-5 text-xs font-black uppercase tracking-wide text-slate-300 transition-all hover:border-cyan-500/60 hover:bg-cyan-950/30 hover:text-cyan-300 hover:shadow-[0_0_12px_rgba(34,211,238,0.2)]">
                    <svg class="mr-2 h-3.5 w-3.5 transition-transform group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Alert Queue
                </a>
                <a href="{{ route('reports.index') }}" class="dashboard-action inline-flex h-11 items-center rounded-xl border border-cyan-500/30 bg-cyan-500/5 px-5 text-xs font-black uppercase tracking-wide text-cyan-200 transition-all hover:-translate-y-0.5 hover:border-cyan-400/60 hover:bg-cyan-500/15 hover:shadow-[0_0_12px_rgba(34,211,238,0.2)]">
                    <svg class="mr-2 h-3.5 w-3.5 transition-transform group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Generate Report
                </a>
            </div>
        </header>

        <!-- Operations summary -->
        <section class="grid gap-3 md:grid-cols-2 xl:grid-cols-4 animate-fade-up" style="animation-delay: 0.03s">
            <div class="soc-panel flex items-center justify-between gap-4 rounded-xl px-5 py-4">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-500">Coverage</p>
                    <p class="mt-1 text-sm font-bold text-slate-300">Protected projects</p>
                </div>
                <p class="text-2xl font-black text-cyan-300 stat-number">{{ $coverage }}%</p>
            </div>
            <div class="soc-panel flex items-center justify-between gap-4 rounded-xl px-5 py-4">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-500">Agent Health</p>
                    <p class="mt-1 text-sm font-bold text-slate-300">{{ $stats['online_agents'] }} online / {{ $agentTotal }} total</p>
                </div>
                <p class="text-2xl font-black text-emerald-300 stat-number">{{ $agentHealth }}%</p>
            </div>
            <div class="soc-panel flex items-center justify-between gap-4 rounded-xl px-5 py-4">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-500">Critical Share</p>
                    <p class="mt-1 text-sm font-bold text-slate-300">{{ $stats['critical_signals'] }} critical signals</p>
                </div>
                <p class="text-2xl font-black text-orange-300 stat-number">{{ $criticalShare }}%</p>
            </div>
            <div class="soc-panel flex items-center justify-between gap-4 rounded-xl px-5 py-4">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-500">Posture</p>
                    <p class="mt-1 text-sm font-bold text-slate-300">{{ $scoreLabel }}</p>
                </div>
                <p class="text-2xl font-black {{ $scoreTone }} stat-number">{{ $stats['avg_score'] }}</p>
            </div>
        </section>

        <!-- Metric cards -->
        <section class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6 animate-fade-up" style="animation-delay: 0.05s">
            @foreach ($cards as $card)
                <div class="metric-card glow-card relative overflow-hidden rounded-xl border {{ $card['ring'] }} bg-slate-950/40 p-5 backdrop-blur-sm transition-all duration-200 group">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-700/60 bg-slate-900/80 {{ $card['tone'] }} shadow-lg">
                            @if ($card['icon'] === 'users')
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.8"/><path d="M16 3.2a4 4 0 0 1 0 7.6"/></svg>
                            @elseif ($card['icon'] === 'sites')
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3c2.3 2.5 3.4 5.5 3.4 9S14.3 18.5 12 21"/><path d="M12 3c-2.3 2.5-3.4 5.5-3.4 9S9.7 18.5 12 21"/></svg>
                            @elseif ($card['icon'] === 'agent')
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2v4"/><path d="M12 18v4"/><path d="M4.9 4.9 7.8 7.8"/><path d="m16.2 16.2 2.9 2.9"/><path d="M2 12h4"/><path d="M18 12h4"/><circle cx="12" cy="12" r="5"/></svg>
                            @elseif ($card['icon'] === 'bolt')
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m13 2-9 13h7l-1 7 10-14h-7z"/></svg>
                            @elseif ($card['icon'] === 'incident')
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                            @else
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 13c0 5-3.5 7.5-7.7 8.8a1 1 0 0 1-.6 0C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.2-2.5a1.3 1.3 0 0 1 1.6 0C14.5 3.8 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                            @endif
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="rounded-full border border-slate-700/50 bg-slate-800/60 px-2.5 py-0.5 text-[9px] font-black uppercase tracking-wider text-slate-300 backdrop-blur-sm">{{ $card['status'] }}</span>
                        </div>
                    </div>
                    <p class="mt-5 text-3xl font-black tracking-tight stat-number sm:text-4xl {{ $card['tone'] }}">{{ $card['value'] }}</p>
                    <p class="mt-1 text-sm font-bold text-slate-200">{{ $card['label'] }}</p>
                    <p class="mt-2 text-[11px] font-semibold text-slate-500">{{ $card['detail'] }}</p>
                    <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-gradient-to-br from-white/5 to-transparent blur-2xl"></div>
                </div>
            @endforeach
        </section>

        <!-- MAIN GRID: THREAT MAP & LIVE STREAM -->
        <section class="grid gap-6 xl:grid-cols-[1.6fr_1fr] animate-fade-up" style="animation-delay: 0.1s">
            <!-- World Threat Map -->
            <div class="relative overflow-hidden soc-panel rounded-2xl backdrop-blur-sm transition-all duration-300 hover:shadow-xl">
                <div class="flex flex-col gap-3 border-b border-slate-800/40 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div class="flex items-center gap-3">
                        <div class="h-7 w-1 rounded-full bg-gradient-to-b from-cyan-400 to-cyan-600"></div>
                        <div>
                            <p class="text-[10px] font-black tracking-[0.2em] uppercase text-cyan-300">Global Telemetry</p>
                            <h2 class="mt-1 text-xl font-black tracking-tight text-white">Live Attack Surface</h2>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-4 text-[10px] font-black uppercase tracking-wider text-slate-300">
                        <span class="flex items-center gap-1.5"><span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-500 opacity-60"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-red-500 shadow-[0_0_6px_#ef4444]"></span></span> Critical</span>
                        <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-orange-500"></span> High</span>
                        <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-amber-400"></span> Medium</span>
                        <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Low</span>
                    </div>
                </div>
                <div class="relative h-[380px] w-full sm:h-[480px] lg:h-[540px]">
                    <div id="worldMap" class="h-full w-full z-10"></div>
                    <div class="absolute bottom-4 left-4 right-4 flex flex-wrap justify-between gap-3 pointer-events-none">
                        <div class="rounded-xl border border-slate-700/50 bg-black/60 px-4 py-2.5 shadow-2xl backdrop-blur-md pointer-events-auto">
                            <p class="text-[11px] font-black text-cyan-300">{{ $mapPayload->count() }} geo-tagged events</p>
                            <p class="text-[9px] font-semibold text-slate-500">{{ $unknownGeoCount }} sources unlocated</p>
                        </div>
                        @if ($mapPayload->isEmpty())
                            <div class="rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-2.5 text-[11px] font-bold text-amber-300 backdrop-blur-sm pointer-events-auto">⏳ Awaiting geospatial threat data</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Live Threat Stream -->
            <div class="soc-panel rounded-2xl overflow-hidden backdrop-blur-sm flex flex-col transition-all duration-300 hover:shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-800/40 bg-red-950/5 px-5 py-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <span class="flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-500 opacity-80"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-600 shadow-[0_0_10px_#ef4444]"></span>
                            </span>
                        </div>
                        <div>
                            <p class="text-[10px] font-black tracking-[0.2em] uppercase text-red-300">Incident Stream</p>
                            <h2 class="mt-1 text-xl font-black tracking-tight text-white">Real-time Threat Feed</h2>
                        </div>
                    </div>
                    <span class="rounded-full border border-red-500/40 bg-red-500/10 px-3 py-1 text-[9px] font-black uppercase tracking-wider text-red-300 shadow-sm backdrop-blur-sm">LIVE · {{ $liveThreats->count() }} alerts</span>
                </div>
                <div class="flex-1 max-h-[480px] overflow-y-auto custom-scroll divide-y divide-slate-800/30">
                    @forelse ($liveThreats as $threat)
                        @php $severity = strtolower((string) ($threat['severity'] ?? 'medium')); @endphp
                        <div class="threat-item px-5 py-4 transition-all duration-150 border-l-3 border-l-transparent">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-mono text-[10px] font-black text-cyan-400 bg-cyan-950/40 px-2 py-0.5 rounded-md">{{ $threat['time'] ?? '--:--' }}</span>
                                        <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wide">{{ $threat['source'] ?? 'Sensor' }}</span>
                                    </div>
                                    <p class="mt-2 text-sm font-black text-white tracking-tight">{{ $threat['type'] ?? 'Generic Threat' }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500 truncate">{{ $threat['asset'] ?? 'Protected Asset' }}</p>
                                </div>
                                <div class="shrink-0">
                                    <span class="inline-flex items-center rounded-md border px-2.5 py-1 text-[9px] font-black uppercase tracking-wide {{ $severityClass($severity) }}">{{ $severity }}</span>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center justify-between gap-3 text-[10px] font-bold text-slate-500">
                                <span class="font-mono truncate">{{ $threat['ip'] ?? '0.0.0.0' }}</span>
                                <span class="text-red-300/80 flex items-center gap-1"><span class="h-1 w-1 rounded-full bg-red-400"></span>{{ $threat['status'] ?? 'Investigating' }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-16 text-center">
                            <div class="h-14 w-14 rounded-full bg-slate-800/50 flex items-center justify-center mb-4">
                                <svg class="h-7 w-7 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <p class="text-sm font-bold text-slate-500">No active threats detected</p>
                            <p class="text-xs text-slate-600 mt-1">All systems nominal</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <!-- SECONDARY METRICS - FIXED WEEKLY CHART -->
        <section class="grid gap-6 xl:grid-cols-[0.9fr_1.2fr_0.9fr] animate-fade-up" style="animation-delay: 0.15s">
            <!-- Weekly Threats Chart - COMPLETELY FIXED -->
            <div class="soc-panel rounded-2xl p-5 backdrop-blur-sm transition-all duration-300 hover:shadow-xl hover:border-cyan-500/20 overflow-hidden">
                <div class="flex items-center justify-between gap-4 mb-4">
                    <div>
                        <p class="text-[10px] font-black tracking-[0.2em] uppercase text-cyan-400 flex items-center gap-2">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                            Threat Intelligence
                        </p>
                        <h2 class="mt-1 text-xl font-black tracking-tight text-white">Weekly Attack Volume</h2>
                    </div>
                    <div class="text-right bg-slate-900/40 px-4 py-2 rounded-xl">
                        <span class="text-2xl font-black text-cyan-300 stat-number">{{ $weeklyThreats->sum('count') }}</span>
                        <p class="text-[9px] font-bold text-slate-500">total events</p>
                    </div>
                </div>
                
                <div class="weekly-chart-container">
                    <div class="flex items-end justify-between gap-2 h-44 pt-2 px-1">
                        @php
                            $hasAnyData = $weeklyThreats->sum('count') > 0;
                        @endphp
                        @foreach ($weeklyThreats as $day)
                            @php 
                                $count = (int) ($day['count'] ?? 0);
                                $height = $hasAnyData ? max(8, (int) round(($count / $maxWeeklyThreats) * 100)) : 8;
                                
                                // Color based on threat level (only if data exists)
                                if (!$hasAnyData || $count == 0) {
                                    $barGradient = 'from-slate-700 to-slate-600';
                                } elseif ($height > 75) {
                                    $barGradient = 'from-red-600 to-red-500';
                                } elseif ($height > 50) {
                                    $barGradient = 'from-orange-600 to-orange-500';
                                } elseif ($height > 25) {
                                    $barGradient = 'from-amber-600 to-amber-500';
                                } else {
                                    $barGradient = 'from-emerald-600 to-emerald-500';
                                }
                            @endphp
                            <div class="flex flex-col items-center gap-2 group flex-1">
                                <div class="relative w-full max-w-[60px] mx-auto bg-slate-800/60 rounded-lg overflow-hidden transition-all duration-200 group-hover:bg-slate-800/80" style="height: 140px;">
                                    <div class="absolute bottom-0 w-full bg-gradient-to-t {{ $barGradient }} weekly-bar transition-all duration-500 ease-out origin-bottom group-hover:brightness-110" style="height: {{ $height }}%;"></div>
                                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-black/50 backdrop-blur-sm">
                                        <span class="text-xs font-black text-white stat-number">{{ $count }}</span>
                                    </div>
                                </div>
                                <span class="text-[11px] font-black uppercase text-slate-400 tracking-wide">{{ $day['label'] }}</span>
                                <span class="text-[10px] font-bold stat-number {{ $hasAnyData && $height > 50 ? 'text-orange-400' : 'text-slate-500' }}">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Trend Summary -->
                @php
                    $weeklyArray = $weeklyThreats->values()->toArray();
                    $lastWeekCount = count($weeklyArray) > 0 ? ($weeklyArray[count($weeklyArray)-1]['count'] ?? 0) : 0;
                    $firstWeekCount = count($weeklyArray) > 0 ? ($weeklyArray[0]['count'] ?? 0) : 0;
                    $trend = $lastWeekCount - $firstWeekCount;
                    $trendPercent = $firstWeekCount > 0 ? round(($trend / $firstWeekCount) * 100) : 0;
                    $isUp = $trend > 0;
                    $hasTrendData = $weeklyThreats->sum('count') > 0;
                @endphp
                <div class="mt-4 pt-3 border-t border-slate-800/50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="trend-indicator">
                            <span class="text-[10px] font-bold uppercase tracking-wide text-slate-400">7d trend</span>
                        </div>
                        <div class="flex items-center gap-1">
                            @if(!$hasTrendData)
                                <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"></path></svg>
                                <span class="text-xs font-black text-slate-500">—</span>
                            @elseif($isUp)
                                <svg class="w-3 h-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                <span class="text-xs font-black text-red-400">+{{ abs($trendPercent) }}%</span>
                            @elseif($trend < 0)
                                <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path></svg>
                                <span class="text-xs font-black text-emerald-400">{{ $trendPercent }}%</span>
                            @else
                                <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"></path></svg>
                                <span class="text-xs font-black text-slate-500">0%</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-[9px] font-medium text-slate-500">
                        Peak: <span class="text-cyan-400 font-black">{{ $maxWeeklyThreats }}</span> events
                    </div>
                </div>
                
                <!-- No data message (only shown when absolutely no data) -->
                @if(!$hasTrendData)
                <div class="mt-3 text-center">
                    <p class="text-[10px] text-amber-400/70 bg-amber-500/5 py-1.5 px-2 rounded-lg inline-block w-full">
                        <svg class="inline w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Waiting for weekly threat data
                    </p>
                </div>
                @endif
            </div>

            <!-- Audit Logs -->
            <div class="soc-panel rounded-2xl overflow-hidden backdrop-blur-sm flex flex-col transition-all duration-300 hover:shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-800/40 px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="h-7 w-1 rounded-full bg-emerald-400"></div>
                        <div>
                            <p class="text-[10px] font-black tracking-[0.2em] uppercase text-emerald-400">Audit Trail</p>
                            <h2 class="mt-1 text-xl font-black tracking-tight text-white">Live Forensics Log</h2>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 bg-slate-800/30 px-3 py-1.5 rounded-xl">
                        <svg class="h-3 w-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span class="text-[9px] font-black text-emerald-300">{{ $stats['logs_today'] }} events today</span>
                    </div>
                </div>
                <div class="max-h-[380px] overflow-y-auto custom-scroll divide-y divide-slate-800/30">
                    @forelse ($recentLogs as $log)
                        @php $severity = strtolower((string) ($log['severity'] ?? 'info')); @endphp
                        <div class="grid gap-3 px-5 py-4 hover:bg-slate-800/10 transition-all duration-150 sm:grid-cols-[1fr_auto] sm:items-center">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <span class="rounded-md bg-slate-800/60 px-2 py-0.5 text-[9px] font-black uppercase text-slate-400">{{ $log['source'] }}</span>
                                    <span class="rounded-md border px-2 py-0.5 text-[9px] font-black uppercase tracking-wide {{ $severityClass($severity) }}">{{ $severity }}</span>
                                </div>
                                <p class="text-sm font-black text-white truncate">{{ $log['event'] }}</p>
                                <p class="mt-1 text-[11px] font-semibold text-slate-500">{{ $log['project'] }} / {{ $log['category'] }}</p>
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="font-mono text-[11px] font-black text-cyan-300">{{ $log['time'] }}</p>
                                <p class="mt-1 text-[9px] font-mono text-slate-600">{{ $log['ip'] }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-16 text-center text-sm font-bold text-slate-500">No audit logs available</div>
                    @endforelse
                </div>
            </div>

            <!-- Top Attacked Assets -->
            <div class="soc-panel rounded-2xl p-5 backdrop-blur-sm transition-all duration-300 hover:shadow-xl">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black tracking-[0.2em] uppercase text-orange-400">High Value Targets</p>
                        <h2 class="mt-1 text-xl font-black tracking-tight text-white">Most Attacked Assets</h2>
                    </div>
                    <div class="text-right">
                        <span class="text-2xl font-black text-orange-300 stat-number">{{ $topAttacked->sum('count') }}</span>
                        <p class="text-[10px] font-bold text-slate-500">total attacks</p>
                    </div>
                </div>
                <div class="mt-5 space-y-5">
                    @forelse ($topAttacked as $index => $item)
                        @php 
                            $maxCount = max(1, (int) $topAttacked->max('count'));
                            $width = max(8, (int) round(((int) $item['count'] / $maxCount) * 100));
                            $barColor = $index === 0 ? 'from-red-500 to-red-400' : ($index === 1 ? 'from-orange-500 to-orange-400' : 'from-amber-500 to-yellow-600');
                        @endphp
                        <div class="group">
                            <div class="flex items-center justify-between gap-3 mb-1.5">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="text-[11px] font-black text-slate-500 w-5">{{ $index+1 }}</span>
                                    <p class="truncate text-sm font-black text-slate-200">{{ $item['name'] }}</p>
                                </div>
                                <span class="font-mono text-sm font-black text-orange-400 stat-number">{{ $item['count'] }} hits</span>
                            </div>
                            <div class="relative h-2 w-full overflow-hidden rounded-full bg-slate-800/80">
                                <div class="absolute top-0 left-0 h-full rounded-full bg-gradient-to-r {{ $barColor }} transition-all duration-700 ease-out group-hover:brightness-110 group-hover:shadow-[0_0_6px_rgba(249,115,22,0.4)]" style="width: {{ $width }}%;"></div>
                            </div>
                            <p class="mt-1.5 text-[10px] font-semibold text-slate-600 truncate">{{ $item['client'] ?? 'Multiple clients' }}</p>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-10 border border-dashed border-slate-700/50 rounded-xl">
                            <svg class="h-8 w-8 text-slate-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <p class="text-xs font-bold text-slate-600">No attack data recorded</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const points = @json($mapPayload);
                if (!points.length) return;

                const map = L.map('worldMap', {
                    zoomControl: true,
                    attributionControl: false,
                    fadeAnimation: true,
                    worldCopyJump: false
                }).setView([20, 0], 1.8);
                const isDarkMode = document.documentElement.classList.contains('dark');
                const tileTheme = isDarkMode ? 'dark_all' : 'light_all';

                L.tileLayer(`https://{s}.basemaps.cartocdn.com/${tileTheme}/{z}/{x}/{y}{r}.png`, {
                    maxZoom: 6,
                    minZoom: 1.5,
                    subdomains: 'abcd',
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; CartoDB'
                }).addTo(map);

                const severityColors = {
                    critical: '#f43f5e',
                    high: '#fb923c',
                    medium: '#fbbf24',
                    low: '#34d399',
                    info: '#22d3ee'
                };

                const markers = [];
                const popupText = {
                    title: isDarkMode ? '#ffffff' : '#0f172a',
                    meta: isDarkMode ? '#94a3b8' : '#475569',
                    place: isDarkMode ? '#cbd5e1' : '#334155',
                    asset: '#64748b'
                };
                points.forEach(point => {
                    const color = severityColors[point.severity] || '#22d3ee';
                    const customIcon = L.divIcon({
                        className: '',
                        html: `<div class="attack-pulse" style="color:${color};"></div>`,
                        iconSize: [14, 14],
                        iconAnchor: [7, 7]
                    });
                    const marker = L.marker([point.lat, point.lng], { icon: customIcon }).addTo(map);
                    marker.bindPopup(`
                        <div class="space-y-2" style="font-family: 'Inter', monospace">
                            <div style="font-weight:900;color:${popupText.title};font-size:13px;display:flex;align-items:center;gap:6px"><span>Event:</span> ${point.event}</div>
                            <div style="color:${popupText.meta};font-size:11px;font-family:monospace">IP: ${point.ip}</div>
                            <div style="color:${popupText.place};font-size:10px">Location: ${point.city}, ${point.country}</div>
                            <div class="inline-block px-2 py-0.5 rounded-md text-[9px] font-black uppercase" style="background:${color}20; color:${color}; border-left:3px solid ${color}">${point.severity.toUpperCase()}</div>
                            <div style="color:${popupText.asset};font-size:10px;margin-top:4px">Asset: ${point.asset}</div>
                        </div>
                    `, { minWidth: 210, maxWidth: 270, className: 'custom-popup' });
                    markers.push(marker);
                });

                if (markers.length > 1) {
                    const group = L.featureGroup(markers);
                    map.fitBounds(group.getBounds().pad(0.2), { maxZoom: 4 });
                } else if (markers.length === 1) {
                    map.setZoom(3);
                }

                setTimeout(() => map.invalidateSize(), 200);
                window.addEventListener('resize', () => map.invalidateSize());
            });
        </script>
    @endpush
</x-dashboard-layout>
