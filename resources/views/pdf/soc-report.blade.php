<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SOC Executive Report | Armious Protect</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #eef2f7;
            font-family: 'Inter', 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            font-size: 11px;
            line-height: 1.45;
            color: #0a2540;
            padding: 20px 0;
        }

        /* Premium A4 container */
        .report {
            max-width: 210mm;
            width: 100%;
            margin: 0 auto;
            background: #ffffff;
            box-shadow: 0 20px 35px -12px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .page {
            padding: 16mm 18mm 14mm;
            position: relative;
            background: white;
        }

        /* modern hero accent */
        .hero-strip {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #0f766e 0%, #2c9f8f 50%, #6ed4c2 100%);
        }

        /* header with refined hierarchy */
        .header-grid {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            margin-bottom: 24px;
            padding-bottom: 18px;
            border-bottom: 1px solid #e9edf2;
        }

        .brand-area .brand {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #0f766e;
            margin-bottom: 8px;
        }

        h1 {
            font-size: 32px;
            font-weight: 700;
            letter-spacing: -0.8px;
            color: #0b2b3b;
            margin: 0 0 4px;
            line-height: 1.1;
        }

        .tagline {
            color: #5d6f88;
            font-size: 11px;
            font-weight: 400;
        }

        .meta-panel {
            background: #f8fafd;
            padding: 12px 18px;
            border-radius: 18px;
            text-align: right;
            border: 1px solid #eef2f9;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }

        .meta-line {
            font-size: 10px;
            color: #2c3e66;
            margin-bottom: 4px;
        }

        .meta-line strong {
            font-weight: 700;
            color: #0f172a;
        }

        .classification {
            display: inline-block;
            margin-top: 8px;
            background: #ecfdf5;
            border: 1px solid #bdd9d2;
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 8px;
            font-weight: 800;
            letter-spacing: 0.8px;
            color: #0f766e;
        }

        /* alert banner */
        .data-warning {
            margin: 8px 0 16px;
            padding: 12px 16px;
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            border-radius: 12px;
            color: #b45309;
            font-size: 10px;
            font-weight: 500;
        }

        /* executive card duo (score + summary) - elevated */
        .executive-duo {
            display: flex;
            gap: 20px;
            margin-bottom: 28px;
        }

        .score-card-modern {
            flex: 0 0 220px;
            background: linear-gradient(145deg, #fefefc 0%, #f9fbfe 100%);
            border-radius: 28px;
            padding: 22px 18px;
            border: 1px solid #eef2f8;
            box-shadow: 0 8px 18px -8px rgba(0,0,0,0.05);
        }

        .score-eyebrow {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #4a6b8f;
        }

        .score-number {
            font-size: 68px;
            font-weight: 800;
            line-height: 1;
            color: #0f766e;
            margin: 12px 0 8px;
            letter-spacing: -2px;
        }

        .score-badge {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 10px;
            font-weight: 800;
        }

        .summary-card-modern {
            flex: 1;
            background: #ffffff;
            border-radius: 28px;
            padding: 18px 24px;
            border: 1px solid #eef2f8;
            box-shadow: 0 8px 18px -8px rgba(0,0,0,0.05);
        }

        .summary-title {
            font-size: 15px;
            font-weight: 800;
            color: #0b2b3b;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .summary-text {
            color: #2c3e50;
            font-size: 11px;
            line-height: 1.5;
        }

        .analyst-note {
            margin-top: 16px;
            padding-top: 12px;
            border-top: 1px dashed #e2e8f0;
            font-size: 10px;
            color: #4b5563;
        }

        /* KPI grid - modern cards */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 14px;
            margin-bottom: 32px;
        }

        .kpi-tile {
            background: #ffffff;
            border: 1px solid #edf2f7;
            border-radius: 24px;
            padding: 16px 8px;
            text-align: center;
            transition: all 0.2s;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }

        .kpi-value {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
        }

        .kpi-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #5e7a9c;
            margin-top: 8px;
        }

        /* two column section */
        .two-columns {
            display: flex;
            gap: 20px;
            margin-bottom: 32px;
        }

        .card-panel {
            flex: 1;
            background: #ffffff;
            border: 1px solid #ecf1f7;
            border-radius: 24px;
            padding: 18px 16px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 18px;
            align-items: baseline;
        }

        .panel-title {
            font-weight: 800;
            font-size: 13px;
            color: #1e2f44;
            letter-spacing: -0.2px;
        }

        .panel-sub {
            font-size: 9px;
            color: #6c86a3;
            font-weight: 500;
        }

        .severity-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .severity-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .severity-label {
            width: 70px;
            font-weight: 700;
            font-size: 10px;
        }

        .progress-wrapper {
            flex: 1;
        }

        .progress-bar-bg {
            height: 8px;
            background: #e4e9f0;
            border-radius: 20px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 20px;
            width: 0%;
        }

        .severity-count {
            width: 36px;
            text-align: right;
            font-weight: 800;
            font-size: 11px;
        }

        .metric-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .metric-row {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .metric-label {
            width: 100px;
            font-weight: 600;
            font-size: 10px;
            color: #334155;
        }

        /* refined tables */
        .risk-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        .risk-table th {
            text-align: left;
            padding: 12px 10px;
            background: #f8fafd;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #3b5d8c;
            border-bottom: 1px solid #e6edf4;
        }

        .risk-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #f0f3f9;
            font-size: 10px;
            color: #1f2d48;
        }

        .pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 40px;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .pill-critical { background: #fee9e6; color: #b91c1c; border: 1px solid #ffcdc7; }
        .pill-high { background: #fff0e6; color: #b45309; border: 1px solid #ffdecb; }
        .pill-medium { background: #fef7e0; color: #a16207; border: 1px solid #fdebb3; }
        .pill-low { background: #e4f4ea; color: #166534; border: 1px solid #c0e0cb; }
        .pill-info { background: #e6f4fe; color: #0369a1; }

        .mono {
            font-family: 'SF Mono', 'Fira Code', monospace;
            font-size: 9px;
            color: #5e6f8d;
        }

        .timeline-item {
            position: relative;
            padding-left: 18px;
            margin-bottom: 16px;
            border-left: 2px solid #cbdde9;
        }
        .timeline-item:last-child { margin-bottom: 0; }
        .timeline-title {
            font-weight: 800;
            font-size: 11px;
            color: #0f2b3b;
        }
        .timeline-meta {
            font-size: 9px;
            color: #617e9e;
            margin-top: 4px;
        }

        /* exposure summary as mini grid */
        .exposure-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 12px;
        }
        .exposure-badge {
            background: #f9fbfe;
            border-radius: 36px;
            padding: 8px 14px;
            flex: 1;
            text-align: center;
            border: 1px solid #eef2f8;
        }
        .exposure-number {
            font-size: 22px;
            font-weight: 800;
            color: #1e2f44;
        }
        .exposure-label {
            font-size: 8px;
            text-transform: uppercase;
            font-weight: 700;
            color: #667f9e;
        }

        /* recommendations row */
        .rec-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-top: 4px;
        }
        .rec-card {
            background: #fafcff;
            border-radius: 20px;
            padding: 14px;
            border: 1px solid #eef2fa;
        }
        .rec-priority {
            font-size: 8px;
            font-weight: 800;
            letter-spacing: 0.8px;
            color: #0f766e;
            margin-bottom: 10px;
        }
        .rec-title {
            font-weight: 800;
            font-size: 11px;
            margin-bottom: 6px;
        }
        .rec-body {
            font-size: 9px;
            color: #4a617c;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: 28px;
            padding-top: 14px;
            border-top: 1px solid #eef2f8;
            font-size: 8px;
            color: #8196b0;
        }

        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            .report {
                box-shadow: none;
                margin: 0;
            }
            .card-panel, .kpi-tile, .score-card-modern, .summary-card-modern, .rec-card {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
<div class="report">
    <div class="page">
        <div class="hero-strip"></div>

        @php
            // sanitize dynamic input
            $critical = (int) ($stats['critical_alerts'] ?? 0);
            $high = (int) ($stats['high_alerts'] ?? 0);
            $medium = (int) ($stats['medium_alerts'] ?? 0);
            $low = (int) ($stats['low_alerts'] ?? 0);
            $totalSeverity = max($critical + $high + $medium + $low, 1);

            $scoreCollection = $topRiskProjects ?? collect();
            $overallScore = (int) round($scoreCollection->avg('soc_score') ?? 0);
            if ($overallScore === 0 && ($critical > 0 || $high > 0)) $overallScore = 62;
            if ($overallScore === 0) $overallScore = 78;
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

        <!-- Header -->
        <div class="header-grid">
            <div class="brand-area">
                <div class="brand">ARMIous Protect · SOC</div>
                <h1>Executive Report</h1>
                <div class="tagline">Security posture · Threat exposure · Incident response</div>
            </div>
            <div class="meta-panel">
                <div class="meta-line"><strong>Report ID</strong> SOC-{{ now()->format('Ymd') }}</div>
                <div class="meta-line"><strong>Period</strong> {{ $periodLabel }} ({{ $from ?? '-' }} → {{ $to ?? now()->format('Y-m-d') }})</div>
                <div class="meta-line"><strong>Generated</strong> {{ $generatedAt ?? now()->format('Y-m-d H:i') }}</div>
                <div class="classification">TLP:CLEAR · CONFIDENTIAL</div>
            </div>
        </div>

        @if (!empty($mongoError))
            <div class="data-warning">⚠️ Data source warning: some telemetry could not be loaded. The report may be incomplete.</div>
        @endif

        <!-- Executive score + summary -->
        <div class="executive-duo">
            <div class="score-card-modern">
                <div class="score-eyebrow">Security Posture Index</div>
                <div class="score-number">{{ $overallScore }}</div>
                <div class="score-badge">{{ $scoreStatus }}</div>
            </div>
            <div class="summary-card-modern">
                <div class="summary-title">
                    <span>📊 Executive Summary</span>
                </div>
                <div class="summary-text">
                    During {{ strtolower($periodLabel) }}, SOC processed <strong>{{ number_format($safeStats['alerts']) }}</strong> signals,
                    <strong>{{ number_format($safeStats['incidents']) }}</strong> incidents and
                    <strong>{{ number_format($safeStats['vulnerabilities']) }}</strong> flaws across
                    <strong>{{ number_format($safeStats['projects']) }}</strong> projects. Agent telemetry shows <strong>{{ $agentCoverage }}%</strong> coverage
                    (<strong>{{ number_format($safeStats['online_agents']) }}</strong> online sensors).
                </div>
                @if (!empty($note))
                    <div class="analyst-note"><strong>📌 Analyst note:</strong> {{ $note }}</div>
                @endif
            </div>
        </div>

        <!-- KPI row -->
        <div class="kpi-grid">
            <div class="kpi-tile"><div class="kpi-value">{{ number_format($safeStats['projects']) }}</div><div class="kpi-label">Projects</div></div>
            <div class="kpi-tile"><div class="kpi-value">{{ number_format($safeStats['alerts']) }}</div><div class="kpi-label">Signals</div></div>
            <div class="kpi-tile"><div class="kpi-value">{{ number_format($safeStats['incidents']) }}</div><div class="kpi-label">Incidents</div></div>
            <div class="kpi-tile"><div class="kpi-value">{{ number_format($safeStats['vulnerabilities']) }}</div><div class="kpi-label">Vulnerabilities</div></div>
            <div class="kpi-tile"><div class="kpi-value">{{ number_format($safeStats['agents']) }}</div><div class="kpi-label">Sensors</div></div>
        </div>

        <!-- Risk + Ops metrics -->
        <div class="two-columns">
            <div class="card-panel">
                <div class="panel-header">
                    <div class="panel-title">⚠️ Risk Distribution</div>
                    <div class="panel-sub">Alerts by severity</div>
                </div>
                <div class="severity-list">
                    <div class="severity-item"><div class="severity-label" style="color:#b91c1c;">Critical</div><div class="progress-wrapper"><div class="progress-bar-bg"><div class="progress-fill" style="width: {{ round(($critical / $totalSeverity) * 100) }}%; background:#dc2626;"></div></div></div><div class="severity-count">{{ $critical }}</div></div>
                    <div class="severity-item"><div class="severity-label" style="color:#b45309;">High</div><div class="progress-wrapper"><div class="progress-bar-bg"><div class="progress-fill" style="width: {{ round(($high / $totalSeverity) * 100) }}%; background:#f97316;"></div></div></div><div class="severity-count">{{ $high }}</div></div>
                    <div class="severity-item"><div class="severity-label" style="color:#a16207;">Medium</div><div class="progress-wrapper"><div class="progress-bar-bg"><div class="progress-fill" style="width: {{ round(($medium / $totalSeverity) * 100) }}%; background:#facc15;"></div></div></div><div class="severity-count">{{ $medium }}</div></div>
                    <div class="severity-item"><div class="severity-label" style="color:#166534;">Low</div><div class="progress-wrapper"><div class="progress-bar-bg"><div class="progress-fill" style="width: {{ round(($low / $totalSeverity) * 100) }}%; background:#22c55e;"></div></div></div><div class="severity-count">{{ $low }}</div></div>
                </div>
            </div>
            <div class="card-panel">
                <div class="panel-header">
                    <div class="panel-title">📡 Operational Metrics</div>
                    <div class="panel-sub">SOC readiness</div>
                </div>
                <div class="metric-list">
                    <div class="metric-row"><div class="metric-label">Agent coverage</div><div class="progress-wrapper"><div class="progress-bar-bg"><div class="progress-fill" style="width: {{ $agentCoverage }}%; background:#0f766e;"></div></div></div><div class="severity-count">{{ $agentCoverage }}%</div></div>
                    <div class="metric-row"><div class="metric-label">Open incidents</div><div class="progress-wrapper"><div class="progress-bar-bg"><div class="progress-fill" style="width: {{ min(100, $safeStats['open_incidents'] * 12) }}%; background:#ea580c;"></div></div></div><div class="severity-count">{{ $safeStats['open_incidents'] }}</div></div>
                    <div class="metric-row"><div class="metric-label">Open vulns</div><div class="progress-wrapper"><div class="progress-bar-bg"><div class="progress-fill" style="width: {{ min(100, $safeStats['open_vulnerabilities'] * 2) }}%; background:#0284c7;"></div></div></div><div class="severity-count">{{ $safeStats['open_vulnerabilities'] }}</div></div>
                    <div class="metric-row"><div class="metric-label">Offline sensors</div><div class="progress-wrapper"><div class="progress-bar-bg"><div class="progress-fill" style="width: {{ min(100, $safeStats['offline_agents'] * 8) }}%; background:#94a3b8;"></div></div></div><div class="severity-count">{{ $safeStats['offline_agents'] }}</div></div>
                </div>
            </div>
        </div>

        <!-- Top Risk Projects table -->
        <div class="card-panel" style="margin-bottom: 28px; padding: 12px 0 6px;">
            <div class="panel-header" style="padding: 0 16px;">
                <div class="panel-title">🏆 Top Risk Projects</div>
                <div class="panel-sub">Ranked by SOC risk score</div>
            </div>
            <table class="risk-table">
                <thead>
                <tr><th>#</th><th>Project / Client</th><th>Score</th><th>Risk</th><th>Activity (A/I/V)</th></tr>
                </thead>
                <tbody>
                @forelse($scoreCollection->take(6) as $project)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $project->name ?? 'Unnamed' }}</strong><br><span class="mono">{{ $project->client->company_name ?? 'Unassigned' }} · {{ $project->domain ?? '-' }}</span></td>
                        <td><strong>{{ (int) ($project->soc_score ?? 0) }}</strong></td>
                        <td><span class="pill {{ $sevClass($project->soc_risk ?? 'medium') }}">{{ $project->soc_risk ?? 'medium' }}</span></td>
                        <td class="mono">{{ (int) ($project->alerts_count ?? 0) }} / {{ (int) ($project->incidents_count ?? 0) }} / {{ (int) ($project->vulnerabilities_count ?? 0) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="padding: 24px; text-align: center; color: #7a8b9f;">No project risk data available</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <!-- Active exposures + recent incidents -->
        <div class="two-columns">
            <div class="card-panel">
                <div class="panel-header"><div class="panel-title">🔍 Active Exposures</div><div class="panel-sub">Top findings</div></div>
                <table class="risk-table" style="margin-top: 0;">
                    <thead><tr><th>Finding</th><th>Severity</th></tr></thead>
                    <tbody>
                    @forelse($vulnCollection as $vuln)
                        <tr><td><strong>{{ $vuln->name ?? $vuln->title ?? 'Unnamed' }}</strong><br><span class="mono">{{ $vuln->site_url ?? $vuln->url ?? 'unknown asset' }}</span></td><td><span class="pill {{ $sevClass($vuln->severity ?? 'low') }}">{{ $vuln->severity ?? 'low' }}</span></td></tr>
                    @empty
                        <tr><td colspan="2" class="mono" style="padding: 16px;">No vulnerabilities detected in period.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-panel">
                <div class="panel-header"><div class="panel-title">⏱️ Recent Incidents</div><div class="panel-sub">SOC timeline</div></div>
                @forelse($incidentCollection as $incident)
                    <div class="timeline-item">
                        <div class="timeline-title">{{ $incident->event ?? $incident->incident_key ?? 'Security event' }}</div>
                        <div class="timeline-meta">{{ $incident->site_url ?? $incident->ip ?? 'asset' }} · severity {{ strtoupper($incident->severity ?? 'medium') }} · status {{ $incident->status ?? 'active' }}</div>
                    </div>
                @empty
                    <div class="timeline-item"><div class="timeline-title">No recent incidents</div><div class="timeline-meta">No incident activity recorded this period.</div></div>
                @endforelse
            </div>
        </div>

        <!-- Exposure summary -->
        <div class="card-panel" style="margin-bottom: 28px;">
            <div class="panel-header"><div class="panel-title">📦 Exposure & Asset Telemetry</div><div class="panel-sub">Vulnerability density</div></div>
            <div class="exposure-stats">
                <div class="exposure-badge"><div class="exposure-number" style="color:#b91c1c;">{{ number_format($safeStats['critical_vulnerabilities']) }}</div><div class="exposure-label">Critical Vulns</div></div>
                <div class="exposure-badge"><div class="exposure-number" style="color:#b45309;">{{ number_format($safeStats['high_vulnerabilities']) }}</div><div class="exposure-label">High Vulns</div></div>
                <div class="exposure-badge"><div class="exposure-number">{{ number_format($safeStats['open_vulnerabilities']) }}</div><div class="exposure-label">Open Vulns</div></div>
                <div class="exposure-badge"><div class="exposure-number">{{ number_format($safeStats['inventories']) }}</div><div class="exposure-label">Assets tracked</div></div>
                <div class="exposure-badge"><div class="exposure-number">{{ number_format($safeStats['offline_agents']) }}</div><div class="exposure-label">Offline sensors</div></div>
            </div>
        </div>

        <!-- Recommendations -->
        <div class="card-panel" style="padding: 18px;">
            <div class="panel-header"><div class="panel-title">📌 Recommended Actions</div><div class="panel-sub">Prioritized roadmap</div></div>
            <div class="rec-grid">
                <div class="rec-card"><div class="rec-priority">P1 · Immediate</div><div class="rec-title">Critical exposure closure</div><div class="rec-body">Patch or isolate critical CVEs, re-scan within 48h.</div></div>
                <div class="rec-card"><div class="rec-priority">P2 · High</div><div class="rec-title">Incident remediation</div><div class="rec-body">Resolve high-severity open incidents, update containment timeline.</div></div>
                <div class="rec-card"><div class="rec-priority">P3 · Coverage</div><div class="rec-title">Sensor health recovery</div><div class="rec-body">Reconnect offline agents, verify log ingestion for top projects.</div></div>
                <div class="rec-card"><div class="rec-priority">P4 · Governance</div><div class="rec-title">Risk ownership review</div><div class="rec-body">Assign owners for high-risk projects, weekly tracking.</div></div>
            </div>
        </div>

        <div class="footer">
            <span>Armious Protect SOC · Executive Security Report — Confidential</span>
            <span>Generated for leadership · Page 1 / 1</span>
        </div>
    </div>
</div>
</body>
</html>