<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SOC Executive Report | Armious Protect</title>
    <style>
        @page {
            size: A4;
            margin: 10mm 10mm 12mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            margin: 0;
            padding: 0;
            background: #eef2f7;
            color: #172033;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            line-height: 1.38;
        }

        .page {
            width: 100%;
            max-width: 190mm;
            margin: 0 auto;
            padding: 0;
            background: #ffffff;
            overflow: visible;
        }

        .top-bar {
            height: 5px;
            background: linear-gradient(90deg, #0f766e, #10b981, #0f766e);
            margin-bottom: 12px;
        }

        .header {
            display: table;
            width: 100%;
            padding-bottom: 10px;
            border-bottom: 1px solid #d8e0ea;
        }

        .header-left,
        .header-right {
            display: table-cell;
            vertical-align: top;
        }

        .header-right {
            width: 222px;
            text-align: right;
            color: #526174;
            font-size: 9px;
        }

        .brand {
            color: #0f766e;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        h1 {
            margin: 0;
            color: #101827;
            font-size: 25px;
            line-height: 1.06;
            letter-spacing: -0.4px;
        }

        .subtitle {
            margin-top: 6px;
            color: #5d6b7b;
            font-size: 10px;
        }

        .meta-line {
            margin-bottom: 4px;
        }

        .meta-line strong {
            color: #172033;
        }

        .classification {
            display: inline-block;
            margin-top: 7px;
            padding: 5px 9px;
            border: 1px solid #b6d8d2;
            background: #ecfdf5;
            color: #0f766e;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.7px;
            text-transform: uppercase;
        }

        .notice {
            margin-top: 10px;
            padding: 10px 12px;
            border-left: 4px solid #f59e0b;
            background: #fffbeb;
            color: #7c4a03;
            font-size: 10px;
        }

        .summary {
            display: table;
            width: 100%;
            margin-top: 12px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .score-card,
        .summary-card {
            display: table-cell;
            vertical-align: top;
            border: 1px solid #d8e0ea;
            background: #f8fafc;
        }

        .score-card {
            width: 200px;
            padding: 14px;
            border-right: none;
        }

        .summary-card {
            padding: 14px 16px;
        }

        .eyebrow {
            color: #64748b;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.9px;
            text-transform: uppercase;
        }

        .score {
            margin-top: 6px;
            color: #0f766e;
            font-size: 54px;
            line-height: 0.95;
            font-weight: 800;
        }

        .score-status {
            display: inline-block;
            margin-top: 8px;
            padding: 5px 10px;
            background: #e0f2fe;
            color: #075985;
            font-weight: 700;
            font-size: 8px;
            text-transform: uppercase;
        }

        .summary-title {
            margin: 0 0 8px;
            color: #172033;
            font-size: 14px;
        }

        .summary-text {
            margin: 0;
            color: #475569;
            font-size: 10px;
        }

        .note {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #d8e0ea;
            color: #475569;
            font-size: 9px;
        }

        .kpis {
            display: table;
            width: 100%;
            margin-top: 10px;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 6px 0;
        }

        .kpi {
            display: table-cell;
            padding: 10px 8px;
            border: 1px solid #d8e0ea;
            background: #ffffff;
            text-align: center;
        }

        .kpi-value {
            color: #172033;
            font-size: 20px;
            font-weight: 800;
            line-height: 1;
        }

        .kpi-label {
            margin-top: 6px;
            color: #64748b;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }

        .section {
            margin-top: 12px;
            page-break-inside: avoid;
        }

        .section-head {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }

        .section-title,
        .section-meta {
            display: table-cell;
            vertical-align: bottom;
        }

        .section-title {
            color: #172033;
            font-size: 12px;
            font-weight: 800;
        }

        .section-title span {
            display: inline-block;
            width: 4px;
            height: 11px;
            margin-right: 8px;
            background: #0f766e;
            vertical-align: -2px;
        }

        .section-meta {
            text-align: right;
            color: #64748b;
            font-size: 8px;
        }

        .grid-2 {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
        }

        .panel {
            display: table-cell;
            width: 50%;
            padding: 10px;
            border: 1px solid #d8e0ea;
            background: #ffffff;
            vertical-align: top;
        }

        .severity-row,
        .metric-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .sev-label,
        .metric-label {
            display: table-cell;
            width: 76px;
            color: #334155;
            font-weight: 700;
            font-size: 9px;
            vertical-align: middle;
        }

        .bar-cell {
            display: table-cell;
            vertical-align: middle;
        }

        .bar-bg {
            width: 100%;
            height: 7px;
            background: #e9eef5;
            overflow: hidden;
        }

        .bar-fill {
            height: 7px;
        }

        .count {
            display: table-cell;
            width: 30px;
            text-align: right;
            color: #172033;
            font-weight: 800;
            font-size: 9px;
            vertical-align: middle;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.data th {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #d8e0ea;
            padding: 6px 7px;
            font-size: 8px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        table.data td {
            border: 1px solid #d8e0ea;
            padding: 7px;
            color: #334155;
            font-size: 9px;
            vertical-align: top;
            word-wrap: break-word;
            word-break: break-word;
            overflow-wrap: break-word;
        }

        .muted {
            color: #64748b;
        }

        .mono {
            font-family: Consolas, "Courier New", monospace;
            font-size: 8px;
            word-break: break-all;
            overflow-wrap: break-word;
        }

        .pill {
            display: inline-block;
            padding: 2px 5px;
            font-size: 7px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border: 1px solid transparent;
        }

        .pill-critical { color: #991b1b; background: #fee2e2; border-color: #fecaca; }
        .pill-high { color: #9a3412; background: #ffedd5; border-color: #fed7aa; }
        .pill-medium { color: #854d0e; background: #fef3c7; border-color: #fde68a; }
        .pill-low { color: #166534; background: #dcfce7; border-color: #bbf7d0; }
        .pill-info { color: #075985; background: #e0f2fe; border-color: #bae6fd; }

        .timeline-item {
            position: relative;
            padding: 0 0 10px 14px;
            border-left: 2px solid #cbd5e1;
        }

        .timeline-item:before {
            content: "";
            position: absolute;
            left: -5px;
            top: 2px;
            width: 8px;
            height: 8px;
            background: #0f766e;
        }

        .timeline-title {
            color: #172033;
            font-size: 10px;
            font-weight: 800;
        }

        .timeline-meta {
            margin-top: 3px;
            color: #64748b;
            font-size: 8px;
        }

        .recommendations {
            display: table;
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 6px 0;
        }

        .rec {
            display: table-cell;
            padding: 10px;
            border: 1px solid #d8e0ea;
            background: #f8fafc;
            vertical-align: top;
        }

        .rec-priority {
            color: #0f766e;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .rec-title {
            color: #172033;
            font-size: 10px;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .rec-body {
            color: #475569;
            font-size: 8px;
            line-height: 1.45;
        }

        .footer {
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px solid #d8e0ea;
            display: table;
            width: 100%;
            color: #64748b;
            font-size: 8px;
        }

        .footer span {
            display: table-cell;
        }

        .footer span:last-child {
            text-align: right;
        }

        @media print {
            body { background: #ffffff; }
            .page { margin: 0; box-shadow: none; overflow: visible; }
            .section, .panel, .rec, .kpi, .score-card, .summary-card { break-inside: avoid; page-break-inside: avoid; }
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

    $scoreCollection = $topRiskProjects ?? collect();
    $overallScore = (int) round($scoreCollection->avg('soc_score') ?? 0);
    if ($overallScore === 0 && ($critical > 0 || $high > 0)) {
        $overallScore = 62;
    }
    if ($overallScore === 0) {
        $overallScore = 78;
    }
    $overallScore = min(max($overallScore, 18), 98);
    $scoreStatus = $overallScore >= 85 ? 'Healthy' : ($overallScore >= 65 ? 'Review' : ($overallScore >= 40 ? 'At Risk' : 'Critical'));

    $safeStats = [
        'projects' => (int) ($stats['projects'] ?? 0),
        'alerts' => (int) ($stats['alerts'] ?? 0),
        'incidents' => (int) ($stats['incidents'] ?? 0),
        'vulnerabilities' => (int) ($stats['vulnerabilities'] ?? 0),
        'agents' => (int) ($stats['agents'] ?? 0),
        'online_agents' => (int) ($stats['online_agents'] ?? 0),
        'offline_agents' => (int) ($stats['offline_agents'] ?? 0),
        'open_incidents' => (int) ($stats['open_incidents'] ?? 0),
        'open_vulnerabilities' => (int) ($stats['open_vulnerabilities'] ?? 0),
        'critical_vulnerabilities' => (int) ($stats['critical_vulnerabilities'] ?? 0),
        'high_vulnerabilities' => (int) ($stats['high_vulnerabilities'] ?? 0),
        'inventories' => (int) ($stats['inventories'] ?? 0),
    ];

    $agentCoverage = $safeStats['agents'] > 0 ? (int) round(($safeStats['online_agents'] / $safeStats['agents']) * 100) : 0;
    $periodLabel = ucwords(str_replace('_', ' ', $period ?? 'last_30_days'));
    $sevClass = fn ($severity) => match (strtolower((string) $severity)) {
        'critical' => 'pill-critical',
        'high' => 'pill-high',
        'medium' => 'pill-medium',
        'low' => 'pill-low',
        default => 'pill-info',
    };

    $vulnCollection = collect($vulnerabilities ?? [])->take(5);
    $incidentCollection = collect($incidents ?? [])->take(4);
@endphp

<div class="page">
    <div class="top-bar"></div>

    <div class="header">
        <div class="header-left">
            <div class="brand">Armious Protect</div>
            <h1>SOC Executive Report</h1>
            <div class="subtitle">Security posture, threat activity, exposure, and response summary.</div>
        </div>
        <div class="header-right">
            <div class="meta-line"><strong>Report ID</strong> SOC-{{ now()->format('Ymd') }}</div>
            <div class="meta-line"><strong>Period</strong> {{ $periodLabel }}</div>
            <div class="meta-line"><strong>Range</strong> {{ $from ?? '-' }} -> {{ $to ?? now()->format('Y-m-d') }}</div>
            <div class="meta-line"><strong>Generated</strong> {{ $generatedAt ?? now()->format('Y-m-d H:i') }}</div>
            <div class="classification">TLP:CLEAR - CONFIDENTIAL</div>
        </div>
    </div>

    @if (!empty($mongoError))
        <div class="notice">Warning: some telemetry could not be loaded. The report may be incomplete.</div>
    @endif

    <div class="summary">
        <div class="score-card">
            <div class="eyebrow">Security Posture Index</div>
            <div class="score">{{ $overallScore }}</div>
            <div class="score-status">{{ $scoreStatus }}</div>
        </div>
        <div class="summary-card">
            <h2 class="summary-title">Executive Summary</h2>
            <p class="summary-text">
                During {{ strtolower($periodLabel) }}, the SOC observed
                <strong>{{ number_format($safeStats['alerts']) }}</strong> security signals,
                <strong>{{ number_format($safeStats['incidents']) }}</strong> incidents, and
                <strong>{{ number_format($safeStats['vulnerabilities']) }}</strong> vulnerabilities across
                <strong>{{ number_format($safeStats['projects']) }}</strong> protected projects.
                Current agent coverage is <strong>{{ $agentCoverage }}%</strong>
                with <strong>{{ number_format($safeStats['online_agents']) }}</strong> online sensors.
            </p>
            @if (!empty($note))
                <div class="note"><strong>Analyst note:</strong> {{ $note }}</div>
            @endif
        </div>
    </div>

    <div class="kpis">
        <div class="kpi"><div class="kpi-value">{{ number_format($safeStats['projects']) }}</div><div class="kpi-label">Projects</div></div>
        <div class="kpi"><div class="kpi-value">{{ number_format($safeStats['alerts']) }}</div><div class="kpi-label">Signals</div></div>
        <div class="kpi"><div class="kpi-value">{{ number_format($safeStats['incidents']) }}</div><div class="kpi-label">Incidents</div></div>
        <div class="kpi"><div class="kpi-value">{{ number_format($safeStats['vulnerabilities']) }}</div><div class="kpi-label">Vulnerabilities</div></div>
        <div class="kpi"><div class="kpi-value">{{ number_format($safeStats['agents']) }}</div><div class="kpi-label">Sensors</div></div>
    </div>

    <div class="section">
        <div class="grid-2">
            <div class="panel">
                <div class="section-head">
                    <div class="section-title"><span></span>Risk Distribution</div>
                    <div class="section-meta">Alerts by severity</div>
                </div>
                @foreach([
                    ['label' => 'Critical', 'value' => $critical, 'color' => '#dc2626', 'pill' => 'pill-critical'],
                    ['label' => 'High', 'value' => $high, 'color' => '#ea580c', 'pill' => 'pill-high'],
                    ['label' => 'Medium', 'value' => $medium, 'color' => '#d97706', 'pill' => 'pill-medium'],
                    ['label' => 'Low', 'value' => $low, 'color' => '#16a34a', 'pill' => 'pill-low'],
                ] as $sev)
                    <div class="severity-row">
                        <div class="sev-label" style="color: {{ $sev['color'] }};">{{ $sev['label'] }}</div>
                        <div class="bar-cell"><div class="bar-bg"><div class="bar-fill" style="width: {{ round(($sev['value'] / $totalSeverity) * 100) }}%; background: {{ $sev['color'] }};"></div></div></div>
                        <div class="count">{{ $sev['value'] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="panel">
                <div class="section-head">
                    <div class="section-title"><span></span>Operational Metrics</div>
                    <div class="section-meta">SOC readiness</div>
                </div>
                @foreach([
                    ['label' => 'Agent coverage', 'value' => $agentCoverage, 'unit' => '%', 'color' => '#0f766e'],
                    ['label' => 'Open incidents', 'value' => min(100, $safeStats['open_incidents'] * 12), 'unit' => '', 'color' => '#ea580c'],
                    ['label' => 'Open vulns', 'value' => min(100, $safeStats['open_vulnerabilities'] * 2), 'unit' => '', 'color' => '#0284c7'],
                    ['label' => 'Offline agents', 'value' => min(100, $safeStats['offline_agents'] * 8), 'unit' => '', 'color' => '#64748b'],
                ] as $metric)
                    <div class="metric-row">
                        <div class="metric-label">{{ $metric['label'] }}</div>
                        <div class="bar-cell"><div class="bar-bg"><div class="bar-fill" style="width: {{ $metric['value'] }}%; background: {{ $metric['color'] }};"></div></div></div>
                        <div class="count">{{ $metric['value'] }}{{ $metric['unit'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-head">
            <div class="section-title"><span></span>Top Risk Projects</div>
            <div class="section-meta">Ranked by risk score</div>
        </div>
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 6%;">#</th>
                    <th style="width: 28%;">Project</th>
                    <th style="width: 22%;">Client / Domain</th>
                    <th style="width: 10%;">Score</th>
                    <th style="width: 12%;">Risk</th>
                    <th style="width: 22%;">Activity</th>
                </tr>
            </thead>
            <tbody>
                @forelse($scoreCollection->take(6) as $project)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $project->name ?? 'Unnamed project' }}</strong></td>
                        <td>
                            {{ $project->client->company_name ?? 'Unassigned' }}<br>
                            <span class="mono muted">{{ $project->domain ?? '-' }}</span>
                        </td>
                        <td><strong>{{ (int) ($project->soc_score ?? 0) }}</strong></td>
                        <td><span class="pill {{ $sevClass($project->soc_risk ?? 'medium') }}">{{ $project->soc_risk ?? 'medium' }}</span></td>
                        <td class="mono">
                            A: {{ (int) ($project->alerts_count ?? 0) }} | I: {{ (int) ($project->incidents_count ?? 0) }} | V: {{ (int) ($project->vulnerabilities_count ?? 0) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="muted">No project risk data available for this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="grid-2">
            <div class="panel">
                <div class="section-head">
                    <div class="section-title"><span></span>Active Exposures</div>
                    <div class="section-meta">Top findings</div>
                </div>
                <table class="data">
                    <thead>
                        <tr>
                            <th>Finding</th>
                            <th style="width: 24%;">Severity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vulnCollection as $vuln)
                            <tr>
                                <td>
                                    <strong>{{ $vuln->name ?? $vuln->title ?? 'Unnamed finding' }}</strong><br>
                                    <span class="mono muted">{{ $vuln->site_url ?? $vuln->url ?? 'unknown asset' }}</span>
                                </td>
                                <td><span class="pill {{ $sevClass($vuln->severity ?? 'low') }}">{{ $vuln->severity ?? 'low' }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="muted">No vulnerabilities detected in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="panel">
                <div class="section-head">
                    <div class="section-title"><span></span>Recent Incidents</div>
                    <div class="section-meta">SOC timeline</div>
                </div>
                @forelse($incidentCollection as $incident)
                    <div class="timeline-item">
                        <div class="timeline-title">{{ $incident->event ?? $incident->incident_key ?? 'Security event' }}</div>
                        <div class="timeline-meta">
                            {{ $incident->site_url ?? $incident->ip ?? 'asset' }} | {{ strtoupper($incident->severity ?? 'medium') }} | {{ $incident->status ?? 'active' }}
                        </div>
                    </div>
                @empty
                    <div class="timeline-item">
                        <div class="timeline-title">No recent incidents</div>
                        <div class="timeline-meta">No incident activity was recorded for this reporting period.</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-head">
            <div class="section-title"><span></span>Exposure Summary</div>
            <div class="section-meta">Vulnerability and asset telemetry</div>
        </div>
        <div class="kpis" style="margin-top:0;">
            <div class="kpi"><div class="kpi-value" style="color:#991b1b;">{{ number_format($safeStats['critical_vulnerabilities']) }}</div><div class="kpi-label">Critical Vulns</div></div>
            <div class="kpi"><div class="kpi-value" style="color:#9a3412;">{{ number_format($safeStats['high_vulnerabilities']) }}</div><div class="kpi-label">High Vulns</div></div>
            <div class="kpi"><div class="kpi-value">{{ number_format($safeStats['open_vulnerabilities']) }}</div><div class="kpi-label">Open Vulns</div></div>
            <div class="kpi"><div class="kpi-value">{{ number_format($safeStats['inventories']) }}</div><div class="kpi-label">Assets</div></div>
            <div class="kpi"><div class="kpi-value">{{ number_format($safeStats['offline_agents']) }}</div><div class="kpi-label">Offline Sensors</div></div>
        </div>
    </div>

    <div class="section">
        <div class="section-head">
            <div class="section-title"><span></span>Recommended Actions</div>
            <div class="section-meta">Prioritized for remediation</div>
        </div>
        <div class="recommendations">
            <div class="rec">
                <div class="rec-priority">P1 Immediate</div>
                <div class="rec-title">Resolve critical exposure</div>
                <div class="rec-body">Patch or isolate critical vulnerabilities and validate fixes with a follow-up scan.</div>
            </div>
            <div class="rec">
                <div class="rec-priority">P2 High</div>
                <div class="rec-title">Close active incidents</div>
                <div class="rec-body">Prioritize open incidents with high severity and document containment status.</div>
            </div>
            <div class="rec">
                <div class="rec-priority">P3 Coverage</div>
                <div class="rec-title">Restore sensor health</div>
                <div class="rec-body">Reconnect offline agents and verify telemetry ingestion across critical projects.</div>
            </div>
            <div class="rec">
                <div class="rec-priority">P4 Governance</div>
                <div class="rec-title">Review risk owners</div>
                <div class="rec-body">Assign owners for top-risk projects and track remediation progress weekly.</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <span>Armious Protect SOC - Executive security report</span>
        <span>Confidential - Page 1</span>
    </div>
</div>
</body>
</html>
