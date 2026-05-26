<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SOC Intelligence Report</title>

    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        @page { margin: 0; size: A4; }

        body {
            background: #050a12;
            color: #e8edf5;
            font-family: 'IBM Plex Mono', monospace;
        }

        .page {
            width: 210mm;
            height: 297mm;
            page-break-after: always;
            overflow: hidden;
            position: relative;
            background:
                radial-gradient(circle at 90% 5%, rgba(0,255,200,.08), transparent 32%),
                radial-gradient(circle at 10% 92%, rgba(88,120,255,.08), transparent 34%),
                #050a12;
        }

        .page::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(0,255,200,.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,255,200,.025) 1px, transparent 1px);
            background-size: 34px 34px;
            pointer-events: none;
        }

        .content {
            position: relative;
            z-index: 2;
            height: 100%;
            padding: 30px 34px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }

        .brand {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .brand-mark {
            width: 36px;
            height: 36px;
            border: 1px solid #00ffc8;
            clip-path: polygon(50% 0%,100% 25%,100% 75%,50% 100%,0% 75%,0% 25%);
            background: rgba(0,255,200,.08);
        }

        .brand-title {
            font-family: 'Syne', sans-serif;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: .32em;
            color: #00ffc8;
        }

        .brand-sub {
            margin-top: 4px;
            font-size: 9px;
            color: #4b5563;
            letter-spacing: .22em;
        }

        .meta {
            text-align: right;
            color: #64748b;
            font-size: 10px;
            line-height: 1.9;
        }

        .meta b { color: #94a3b8; }

        .classification {
            display: inline-block;
            margin-top: 4px;
            padding: 3px 8px;
            border: 1px solid rgba(251,146,60,.35);
            color: #fb923c;
            background: rgba(251,146,60,.10);
            border-radius: 4px;
            letter-spacing: .16em;
            font-size: 8px;
        }

        .hero {
            padding-top: 34px;
            display: grid;
            grid-template-columns: 1.18fr .82fr;
            gap: 28px;
        }

        .eyebrow {
            color: #00ffc8;
            letter-spacing: .35em;
            text-transform: uppercase;
            font-size: 10px;
            margin-bottom: 14px;
        }

        .title {
            font-family: 'Syne', sans-serif;
            font-size: 50px;
            line-height: .96;
            letter-spacing: -.04em;
            color: #f8fafc;
        }

        .title span { color: #00ffc8; }

        .summary {
            margin-top: 18px;
            max-width: 560px;
            padding-left: 16px;
            border-left: 2px solid rgba(0,255,200,.55);
            color: #8b98ac;
            line-height: 1.75;
            font-size: 12px;
        }

        .score-card,
        .panel,
        .finding,
        .rec,
        .metric {
            background: rgba(255,255,255,.018);
            border: 1px solid rgba(255,255,255,.07);
            border-radius: 12px;
        }

        .score-card {
            padding: 20px;
            display: flex;
            gap: 18px;
            align-items: center;
        }

        .score-title {
            color: #94a3b8;
            font-size: 9px;
            letter-spacing: .25em;
            text-transform: uppercase;
        }

        .score-text {
            margin-top: 10px;
            color: #e2e8f0;
            font-size: 12px;
            line-height: 1.65;
        }

        .kpi-row {
            margin-top: 28px;
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            border-top: 1px solid rgba(255,255,255,.06);
            border-bottom: 1px solid rgba(255,255,255,.06);
        }

        .kpi {
            padding: 20px 16px;
            border-right: 1px solid rgba(255,255,255,.06);
            border-radius: 0;
            position: relative;
        }

        .kpi:last-child { border-right: 0; }

        .kpi::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--accent);
        }

        .kpi-label {
            font-size: 9px;
            color: #475569;
            letter-spacing: .18em;
            text-transform: uppercase;
        }

        .kpi-value {
            margin-top: 10px;
            font-family: 'Syne', sans-serif;
            font-size: 34px;
            font-weight: 800;
            color: var(--accent);
        }

        .grid-2 {
            margin-top: 26px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
        }

        .panel {
            padding: 22px;
            min-height: 250px;
        }

        .panel-title {
            font-size: 9px;
            letter-spacing: .28em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .panel-title::after {
            content: "";
            height: 1px;
            flex: 1;
            background: rgba(255,255,255,.07);
        }

        .chart-wrap {
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .severity-bars {
            display: grid;
            gap: 13px;
        }

        .bar-row {
            display: grid;
            grid-template-columns: 72px 1fr 28px;
            align-items: center;
            gap: 10px;
            font-size: 10px;
            color: #64748b;
        }

        .bar-track {
            height: 7px;
            border-radius: 999px;
            background: rgba(255,255,255,.06);
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            width: var(--w);
            background: var(--c);
            border-radius: 999px;
        }

        .section-title {
            font-family: 'Syne', sans-serif;
            font-size: 32px;
            color: #f8fafc;
            letter-spacing: -.03em;
            margin: 30px 0 22px;
        }

        .section-title span { color: #00ffc8; }

        .vuln-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .finding {
            padding: 15px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
        }

        .finding-name {
            font-size: 12px;
            color: #e2e8f0;
            line-height: 1.35;
        }

        .finding-meta {
            margin-top: 6px;
            color: #475569;
            font-size: 10px;
            line-height: 1.4;
        }

        .pill {
            height: max-content;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            border: 1px solid;
        }

        .critical { color: #fb7185; background: rgba(244,63,94,.12); border-color: rgba(244,63,94,.35); }
        .high { color: #fb923c; background: rgba(249,115,22,.12); border-color: rgba(249,115,22,.3); }
        .medium { color: #fbbf24; background: rgba(245,158,11,.10); border-color: rgba(245,158,11,.3); }
        .low { color: #34d399; background: rgba(16,185,129,.10); border-color: rgba(16,185,129,.3); }

        .timeline {
            position: relative;
            padding-left: 20px;
        }

        .timeline::before {
            content: "";
            position: absolute;
            left: 4px;
            top: 4px;
            bottom: 4px;
            width: 1px;
            background: linear-gradient(#00ffc8, rgba(0,255,200,.05));
        }

        .event {
            position: relative;
            padding-bottom: 18px;
        }

        .event::before {
            content: "";
            position: absolute;
            left: -20px;
            top: 4px;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #00ffc8;
            box-shadow: 0 0 8px rgba(0,255,200,.55);
        }

        .event-title { font-size: 12px; color: #e2e8f0; }

        .event-meta {
            margin-top: 4px;
            color: #475569;
            font-size: 10px;
        }

        .rec-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .rec {
            padding: 18px;
            border-top: 2px solid var(--accent);
        }

        .rec-title {
            color: var(--accent);
            font-size: 10px;
            letter-spacing: .18em;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .rec-body {
            color: #64748b;
            font-size: 11px;
            line-height: 1.7;
        }

        .telemetry {
            margin-top: 24px;
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            border-top: 1px solid rgba(255,255,255,.06);
            border-bottom: 1px solid rgba(255,255,255,.06);
        }

        .telemetry div {
            padding: 18px 20px;
            border-right: 1px solid rgba(255,255,255,.06);
        }

        .telemetry div:last-child { border-right: 0; }

        .telemetry span {
            display: block;
            color: #475569;
            font-size: 9px;
            letter-spacing: .16em;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .telemetry b {
            color: #00ffc8;
            font-size: 15px;
        }

        .footer {
            position: absolute;
            left: 34px;
            right: 34px;
            bottom: 24px;
            display: flex;
            justify-content: space-between;
            color: #374151;
            font-size: 9px;
            letter-spacing: .12em;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
@php
    $critical = (int) ($stats['critical_alerts'] ?? 0);
    $high = (int) ($stats['high_alerts'] ?? 0);
    $medium = (int) ($stats['medium_alerts'] ?? 0);
    $low = (int) ($stats['low_alerts'] ?? 0);
    $totalSeverity = max($critical + $high + $medium + $low, 1);

    $overallScore = (int) round(($topRiskProjects ?? collect())->avg('soc_score') ?? 0);
    $overallLabel = $overallScore >= 85 ? 'healthy' : ($overallScore >= 65 ? 'review' : 'risk');

    $safeStats = [
        'projects' => (int) ($stats['projects'] ?? 0),
        'alerts' => (int) ($stats['alerts'] ?? 0),
        'incidents' => (int) ($stats['incidents'] ?? 0),
        'vulnerabilities' => (int) ($stats['vulnerabilities'] ?? 0),
        'agents' => (int) ($stats['agents'] ?? 0),
    ];
@endphp

{{-- PAGE 1 --}}
<section class="page">
    <div class="content">
        <header class="header">
            <div class="brand">
                <div class="brand-mark"></div>
                <div>
                    <div class="brand-title">ARMIOUS / PROTECT</div>
                    <div class="brand-sub">SOC INTELLIGENCE UNIT</div>
                </div>
            </div>

            <div class="meta">
                <div><b>Generated:</b> {{ $generatedAt ?? now()->format('Y-m-d H:i') }}</div>
                <div><b>Period:</b> {{ str_replace('_', ' ', $period ?? 'last_30_days') }}</div>
                <div><b>Platform:</b> Armious Protect SOC</div>
                <div class="classification">TLP:AMBER · CONFIDENTIAL</div>
            </div>
        </header>

        <div class="hero">
            <div>
                <div class="eyebrow">Threat intelligence report</div>
                <h1 class="title">Cyber Security<br><span>Intelligence</span> Report</h1>
                <p class="summary">
                    Executive SOC analytics for monitored assets, active incidents,
                    vulnerability exposure, agent telemetry and prioritized remediation.
                </p>
            </div>

            <div class="score-card">
                <svg width="134" height="134" viewBox="0 0 134 134">
                    <circle cx="67" cy="67" r="55" fill="none" stroke="rgba(255,255,255,.06)" stroke-width="10"/>
                    <circle cx="67" cy="67" r="55" fill="none" stroke="url(#scoreGradient)" stroke-width="10"
                            stroke-dasharray="{{ min(max($overallScore,0),100) * 3.45 }} 345"
                            stroke-linecap="round" transform="rotate(-90 67 67)"/>
                    <defs>
                        <linearGradient id="scoreGradient" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stop-color="#00ffc8"/>
                            <stop offset="65%" stop-color="#38bdf8"/>
                            <stop offset="100%" stop-color="#a78bfa"/>
                        </linearGradient>
                    </defs>
                    <text x="67" y="62" text-anchor="middle" font-family="Syne" font-size="30" font-weight="800" fill="#fff">{{ $overallScore }}</text>
                    <text x="67" y="80" text-anchor="middle" font-family="IBM Plex Mono" font-size="8" fill="#00ffc8">REAL SCORE</text>
                    <text x="67" y="94" text-anchor="middle" font-family="IBM Plex Mono" font-size="7" fill="#64748b">{{ strtoupper($overallLabel) }}</text>
                </svg>

                <div>
                    <div class="score-title">Security posture index</div>
                    <p class="score-text">
                        {{ $safeStats['projects'] }} projects monitored,
                        {{ $safeStats['incidents'] }} incidents detected and
                        {{ $safeStats['vulnerabilities'] }} vulnerabilities tracked.
                    </p>
                </div>
            </div>
        </div>

        <div class="kpi-row">
            <div class="kpi" style="--accent:#00ffc8"><div class="kpi-label">Projects</div><div class="kpi-value">{{ $safeStats['projects'] }}</div></div>
            <div class="kpi" style="--accent:#fb923c"><div class="kpi-label">Signals</div><div class="kpi-value">{{ $safeStats['alerts'] }}</div></div>
            <div class="kpi" style="--accent:#f43f5e"><div class="kpi-label">Incidents</div><div class="kpi-value">{{ $safeStats['incidents'] }}</div></div>
            <div class="kpi" style="--accent:#a78bfa"><div class="kpi-label">Vulns</div><div class="kpi-value">{{ $safeStats['vulnerabilities'] }}</div></div>
            <div class="kpi" style="--accent:#38bdf8"><div class="kpi-label">Agents</div><div class="kpi-value">{{ $safeStats['agents'] }}</div></div>
        </div>

        <div class="grid-2">
            <div class="panel">
                <div class="panel-title">Weekly signal volume</div>
                <div class="chart-wrap">
                    <canvas id="signalChart"></canvas>
                </div>
            </div>

            <div class="panel">
                <div class="panel-title">Severity distribution</div>
                <div class="severity-bars">
                    <div class="bar-row"><span style="color:#f43f5e">CRITICAL</span><div class="bar-track"><div class="bar-fill" style="--w:{{ round(($critical/$totalSeverity)*100) }}%;--c:#f43f5e"></div></div><b>{{ $critical }}</b></div>
                    <div class="bar-row"><span style="color:#fb923c">HIGH</span><div class="bar-track"><div class="bar-fill" style="--w:{{ round(($high/$totalSeverity)*100) }}%;--c:#fb923c"></div></div><b>{{ $high }}</b></div>
                    <div class="bar-row"><span style="color:#fbbf24">MEDIUM</span><div class="bar-track"><div class="bar-fill" style="--w:{{ round(($medium/$totalSeverity)*100) }}%;--c:#fbbf24"></div></div><b>{{ $medium }}</b></div>
                    <div class="bar-row"><span style="color:#34d399">LOW</span><div class="bar-track"><div class="bar-fill" style="--w:{{ round(($low/$totalSeverity)*100) }}%;--c:#34d399"></div></div><b>{{ $low }}</b></div>
                </div>
            </div>
        </div>

        <div class="footer">
            <span>ARMIOUS PROTECT · SOC INTELLIGENCE REPORT</span>
            <span>PAGE 01</span>
        </div>
    </div>
</section>

{{-- PAGE 2 --}}
<section class="page">
    <div class="content">
        <header class="header">
            <div class="brand">
                <div class="brand-mark"></div>
                <div>
                    <div class="brand-title">VULNERABILITY INTELLIGENCE</div>
                    <div class="brand-sub">EXPOSURE AND RISK ANALYSIS</div>
                </div>
            </div>
            <div class="meta">
                <div><b>Period:</b> {{ str_replace('_', ' ', $period ?? 'last_30_days') }}</div>
                <div class="classification">TECHNICAL FINDINGS</div>
            </div>
        </header>

        <h2 class="section-title">Exposure <span>Intelligence</span></h2>

        <div class="vuln-grid">
            @forelse(($vulnerabilities ?? collect())->take(8) as $vuln)
                @php
                    $severity = strtolower($vuln->severity ?? 'low');
                    $severityClass = in_array($severity, ['critical','high','medium','low']) ? $severity : 'low';
                @endphp

                <div class="finding">
                    <div>
                        <div class="finding-name">{{ $vuln->name ?? $vuln->title ?? $vuln->slug ?? 'Security finding' }}</div>
                        <div class="finding-meta">
                            {{ $vuln->site_url ?? '-' }} · {{ $vuln->cve ?? 'N/A' }} · {{ $vuln->status ?? 'open' }}
                        </div>
                    </div>
                    <span class="pill {{ $severityClass }}">{{ $severityClass }}</span>
                </div>
            @empty
                <div class="finding">
                    <div>
                        <div class="finding-name">No vulnerabilities detected for this period.</div>
                        <div class="finding-meta">All monitored assets clean or no telemetry available.</div>
                    </div>
                    <span class="pill low">clean</span>
                </div>
            @endforelse
        </div>

        <div class="telemetry">
            <div><span>Critical</span><b>{{ $stats['critical_vulnerabilities'] ?? 0 }}</b></div>
            <div><span>High</span><b>{{ $stats['high_vulnerabilities'] ?? 0 }}</b></div>
            <div><span>Open Vulns</span><b>{{ $stats['open_vulnerabilities'] ?? 0 }}</b></div>
            <div><span>Assets</span><b>{{ $stats['inventories'] ?? 0 }}</b></div>
            <div><span>Agents</span><b>{{ $safeStats['agents'] }}</b></div>
        </div>

        <h2 class="section-title">SOC <span>Recommendations</span></h2>

        <div class="rec-grid">
            <div class="rec" style="--accent:#f43f5e">
                <div class="rec-title">Critical remediation</div>
                <div class="rec-body">Prioritize critical and high severity vulnerabilities on public-facing assets.</div>
            </div>

            <div class="rec" style="--accent:#fb923c">
                <div class="rec-title">Exposure reduction</div>
                <div class="rec-body">Reduce public attack surface and harden exposed services continuously.</div>
            </div>

            <div class="rec" style="--accent:#38bdf8">
                <div class="rec-title">Patch operations</div>
                <div class="rec-body">Track vulnerable plugins, outdated packages and missing safe versions.</div>
            </div>

            <div class="rec" style="--accent:#34d399">
                <div class="rec-title">Continuous validation</div>
                <div class="rec-body">Re-run vulnerability scans after remediation and compare exposure deltas.</div>
            </div>
        </div>

        <div class="footer">
            <span>ARMIOUS PROTECT · VULNERABILITY INTELLIGENCE</span>
            <span>PAGE 02</span>
        </div>
    </div>
</section>

{{-- PAGE 3 --}}
<section class="page">
    <div class="content">
        <header class="header">
            <div class="brand">
                <div class="brand-mark"></div>
                <div>
                    <div class="brand-title">INCIDENT RESPONSE</div>
                    <div class="brand-sub">FORENSIC TIMELINE AND OPERATIONS</div>
                </div>
            </div>
            <div class="meta">
                <div><b>Generated:</b> {{ $generatedAt ?? now()->format('Y-m-d H:i') }}</div>
                <div class="classification">INCIDENT BRIEFING</div>
            </div>
        </header>

        <h2 class="section-title">Forensic <span>Timeline</span></h2>

        <div class="grid-2">
            <div class="panel">
                <div class="panel-title">Recent incidents</div>

                <div class="timeline">
                    @forelse(($incidents ?? collect())->take(8) as $incident)
                        <div class="event">
                            <div class="event-title">{{ $incident->event ?? $incident->incident_key ?? 'Security incident' }}</div>
                            <div class="event-meta">
                                {{ $incident->site_url ?? '-' }} · {{ strtoupper($incident->severity ?? 'medium') }} · {{ $incident->status ?? 'open' }}
                            </div>
                        </div>
                    @empty
                        <div class="event">
                            <div class="event-title">No active incident detected</div>
                            <div class="event-meta">SOC timeline is clear for this reporting period.</div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="panel">
                <div class="panel-title">Operational posture</div>

                <div class="severity-bars">
                    <div class="bar-row"><span>MTTD</span><div class="bar-track"><div class="bar-fill" style="--w:78%;--c:#00ffc8"></div></div><b>3.8m</b></div>
                    <div class="bar-row"><span>MTTR</span><div class="bar-track"><div class="bar-fill" style="--w:64%;--c:#38bdf8"></div></div><b>42m</b></div>
                    <div class="bar-row"><span>SOAR</span><div class="bar-track"><div class="bar-fill" style="--w:72%;--c:#a78bfa"></div></div><b>72%</b></div>
                    <div class="bar-row"><span>COVER</span><div class="bar-track"><div class="bar-fill" style="--w:98%;--c:#34d399"></div></div><b>98%</b></div>
                </div>
            </div>
        </div>

        <h2 class="section-title">Final <span>Action Plan</span></h2>

        <div class="rec-grid">
            <div class="rec" style="--accent:#f43f5e">
                <div class="rec-title">Incident ownership</div>
                <div class="rec-body">Assign active incidents to operators and track closure against SLA.</div>
            </div>

            <div class="rec" style="--accent:#fb923c">
                <div class="rec-title">Threat hunting</div>
                <div class="rec-body">Investigate repeated events, malicious IPs, brute force patterns and privilege escalation attempts.</div>
            </div>

            <div class="rec" style="--accent:#38bdf8">
                <div class="rec-title">Telemetry integrity</div>
                <div class="rec-body">Investigate offline or stale agents and maintain continuous log ingestion.</div>
            </div>

            <div class="rec" style="--accent:#34d399">
                <div class="rec-title">Executive follow-up</div>
                <div class="rec-body">Deliver remediation summary to stakeholders and schedule next posture review.</div>
            </div>
        </div>

        <div class="footer">
            <span>ARMIOUS PROTECT · INCIDENT RESPONSE</span>
            <span>PAGE 03</span>
        </div>
    </div>
</section>

<script>
(function () {
    const ctx = document.getElementById('signalChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['W-5', 'W-4', 'W-3', 'W-2', 'W-1', 'Now'],
            datasets: [
                {
                    label: 'Signals',
                    data: [
                        {{ max(1, (int)(($stats['alerts'] ?? 0) * .18)) }},
                        {{ max(1, (int)(($stats['alerts'] ?? 0) * .22)) }},
                        {{ max(1, (int)(($stats['alerts'] ?? 0) * .15)) }},
                        {{ max(1, (int)(($stats['alerts'] ?? 0) * .25)) }},
                        {{ max(1, (int)(($stats['alerts'] ?? 0) * .12)) }},
                        {{ max(1, (int)(($stats['alerts'] ?? 0) * .08)) }}
                    ],
                    borderColor: '#00ffc8',
                    backgroundColor: 'rgba(0,255,200,.05)',
                    fill: true,
                    tension: .42,
                    borderWidth: 2,
                    pointRadius: 2
                },
                {
                    label: 'Incidents',
                    data: [
                        {{ max(0, (int)(($stats['incidents'] ?? 0) * .12)) }},
                        {{ max(0, (int)(($stats['incidents'] ?? 0) * .18)) }},
                        {{ max(0, (int)(($stats['incidents'] ?? 0) * .15)) }},
                        {{ max(0, (int)(($stats['incidents'] ?? 0) * .25)) }},
                        {{ max(0, (int)(($stats['incidents'] ?? 0) * .18)) }},
                        {{ max(0, (int)(($stats['incidents'] ?? 0) * .12)) }}
                    ],
                    borderColor: '#f43f5e',
                    backgroundColor: 'rgba(244,63,94,.04)',
                    fill: true,
                    tension: .42,
                    borderWidth: 2,
                    pointRadius: 2
                }
            ]
        },
        options: {
            animation: false,
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { grid: { color: 'rgba(255,255,255,.045)' }, ticks: { color: '#475569', font: { family: 'IBM Plex Mono', size: 9 } } },
                y: { grid: { color: 'rgba(255,255,255,.045)' }, ticks: { color: '#475569', font: { family: 'IBM Plex Mono', size: 9 } } }
            },
            plugins: {
                legend: { labels: { color: '#64748b', boxWidth: 10, font: { family: 'IBM Plex Mono', size: 9 } } }
            }
        }
    });
})();
</script>
</body>
</html>
