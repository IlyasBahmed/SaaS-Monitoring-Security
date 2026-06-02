<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Project Security Report</title>
    <style>
        @page { margin: 24px; }
        body { margin: 0; background: #f8fafc; color: #0f172a; font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.45; }
        .page { background: #ffffff; border: 1px solid #e2e8f0; padding: 24px; }
        .cover { background: #07111f; color: #e2e8f0; padding: 26px; border-bottom: 5px solid #06b6d4; }
        .brand { color: #67e8f9; font-size: 10px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; }
        h1 { margin: 10px 0 6px; color: #ffffff; font-size: 28px; line-height: 1.12; }
        h2 { margin: 22px 0 10px; color: #0f172a; font-size: 15px; }
        h3 { margin: 0 0 6px; font-size: 12px; }
        .muted { color: #64748b; }
        .cover .muted { color: #94a3b8; }
        .meta { margin-top: 14px; width: 100%; border-collapse: collapse; }
        .meta td { padding: 6px 0; color: #cbd5e1; }
        .meta b { color: #ffffff; }
        .summary { margin-top: 18px; padding: 14px; background: #ecfeff; border-left: 4px solid #06b6d4; color: #164e63; }
        .kpis { width: 100%; border-collapse: separate; border-spacing: 8px; margin: 14px -8px 6px; }
        .kpis td { width: 25%; padding: 14px; border: 1px solid #e2e8f0; background: #f8fafc; vertical-align: top; }
        .label { color: #64748b; font-size: 9px; text-transform: uppercase; font-weight: 800; letter-spacing: .7px; }
        .value { margin-top: 5px; font-size: 22px; font-weight: 900; color: #0f172a; }
        .subvalue { margin-top: 3px; color: #64748b; font-size: 10px; }
        .score-box { width: 100%; border-collapse: collapse; margin-top: 14px; }
        .score-box td { padding: 14px; border: 1px solid #e2e8f0; vertical-align: top; }
        .score { font-size: 34px; font-weight: 900; }
        .score-good { color: #059669; }
        .score-warn { color: #d97706; }
        .score-risk { color: #dc2626; }
        .bar { height: 8px; background: #e2e8f0; margin-top: 8px; }
        .bar span { display: block; height: 8px; background: #06b6d4; }
        table.rows { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.rows th, table.rows td { border-bottom: 1px solid #e2e8f0; padding: 8px 6px; text-align: left; vertical-align: top; }
        table.rows th { color: #64748b; font-size: 9px; text-transform: uppercase; letter-spacing: .6px; background: #f8fafc; }
        .pill { display: inline-block; padding: 2px 7px; border-radius: 4px; font-size: 9px; font-weight: 800; text-transform: uppercase; border: 1px solid #cbd5e1; color: #334155; }
        .critical { color: #be123c; border-color: #fecdd3; background: #fff1f2; }
        .high { color: #c2410c; border-color: #fed7aa; background: #fff7ed; }
        .medium { color: #b45309; border-color: #fde68a; background: #fffbeb; }
        .low, .info { color: #047857; border-color: #bbf7d0; background: #f0fdf4; }
        .actions { width: 100%; border-collapse: separate; border-spacing: 8px; margin: 10px -8px 0; }
        .actions td { width: 33%; padding: 12px; border: 1px solid #e2e8f0; background: #f8fafc; vertical-align: top; }
        .footer { margin-top: 22px; padding-top: 10px; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 9px; }
    </style>
</head>
<body>
@php
    $score = (int) ($projectScore['security_score'] ?? 0);
    $riskLabel = ucfirst((string) ($projectScore['risk_label'] ?? 'low'));
    $scoreClass = $score >= 85 ? 'score-good' : ($score >= 65 ? 'score-warn' : 'score-risk');
    $openAlerts = $alerts->filter(fn ($alert) => ! (bool) ($alert->resolved ?? false));
    $openIncidents = $incidents->filter(fn ($incident) => strtolower((string) ($incident->status ?? 'open')) !== 'resolved');
    $openVulnerabilities = $vulnerabilities->filter(fn ($vulnerability) => ! in_array(strtolower((string) ($vulnerability->status ?? 'open')), ['fixed', 'ignored', 'closed'], true));
    $criticalFindings = $openAlerts->where('severity', 'critical')->count()
        + $openIncidents->where('severity', 'critical')->count()
        + $openVulnerabilities->where('severity', 'critical')->count();
    $highFindings = $openAlerts->where('severity', 'high')->count()
        + $openIncidents->where('severity', 'high')->count()
        + $openVulnerabilities->where('severity', 'high')->count();
    $formatDate = static fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->format('Y-m-d H:i') : '-';
    $severityClass = static function ($value): string {
        $severity = strtolower((string) ($value ?: 'medium'));
        return in_array($severity, ['critical', 'high', 'medium', 'low', 'info'], true) ? $severity : 'medium';
    };
@endphp

<div class="page">
    <section class="cover">
        <div class="brand">CyberShield Client Security Report</div>
        <h1>{{ $project->domain ?: $project->name ?: 'Protected Project' }}</h1>
        <p class="muted">Executive security posture, active findings, and remediation guidance.</p>

        <table class="meta">
            <tr>
                <td><b>Client:</b> {{ $client?->company_name ?? 'Client' }}</td>
                <td><b>Period:</b> {{ $periodFrom->format('Y-m-d') }} to {{ $periodTo->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <td><b>Project:</b> {{ $project->name ?: '-' }}</td>
                <td><b>Generated:</b> {{ now()->format('Y-m-d H:i') }}</td>
            </tr>
        </table>
    </section>

    <table class="score-box">
        <tr>
            <td style="width: 32%;">
                <div class="label">Security Score</div>
                <div class="score {{ $scoreClass }}">{{ $score }}/100</div>
                <div class="bar"><span style="width: {{ max(4, min(100, $score)) }}%;"></span></div>
            </td>
            <td>
                <div class="label">Executive Summary</div>
                <div class="summary">
                    Risk is currently <b>{{ $riskLabel }}</b>. This report contains
                    {{ $alerts->count() }} alerts, {{ $incidents->count() }} incidents, and
                    {{ $vulnerabilities->count() }} vulnerabilities for the selected period.
                    @if ($criticalFindings + $highFindings > 0)
                        Priority attention is required for {{ $criticalFindings }} critical and {{ $highFindings }} high severity open findings.
                    @else
                        No critical or high severity open findings were detected in this report window.
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table class="kpis">
        <tr>
            <td><div class="label">Open Alerts</div><div class="value">{{ $openAlerts->count() }}</div><div class="subvalue">{{ $alerts->count() }} total</div></td>
            <td><div class="label">Open Incidents</div><div class="value">{{ $openIncidents->count() }}</div><div class="subvalue">{{ $incidents->count() }} total</div></td>
            <td><div class="label">Open Vulnerabilities</div><div class="value">{{ $openVulnerabilities->count() }}</div><div class="subvalue">{{ $vulnerabilities->count() }} total</div></td>
            <td><div class="label">Critical / High</div><div class="value">{{ $criticalFindings + $highFindings }}</div><div class="subvalue">open priority findings</div></td>
        </tr>
    </table>

    <h2>Project Details</h2>
    <table class="rows">
        <tr><th>Name</th><td>{{ $project->name ?: '-' }}</td><th>Domain</th><td>{{ $project->domain ?: '-' }}</td></tr>
        <tr><th>Stack</th><td>{{ \App\Models\Projects::normalizeProjectType($project->stack) }}</td><th>IP Address</th><td>{{ $project->ip_address ?: '-' }}</td></tr>
        <tr><th>Status</th><td>{{ ucfirst($project->status ?: 'offline') }}</td><th>Cloudflare</th><td>{{ $project->cloudflare_enabled ? 'Enabled' : 'Not linked' }}</td></tr>
    </table>

    <h2>Recommended Actions</h2>
    <table class="actions">
        <tr>
            <td><h3>1. Prioritize Exposure</h3><p class="muted">Resolve critical and high findings first, especially public-facing plugin or service vulnerabilities.</p></td>
            <td><h3>2. Validate Incidents</h3><p class="muted">Review open incidents, confirm impact, assign ownership, and close only after evidence is verified.</p></td>
            <td><h3>3. Re-scan After Fixes</h3><p class="muted">Run a follow-up scan after remediation to confirm risk reduction and update the project score.</p></td>
        </tr>
    </table>

    <h2>Alerts</h2>
    <table class="rows">
        <thead><tr><th>Severity</th><th>Title</th><th>Status</th><th>Detected</th></tr></thead>
        <tbody>
        @forelse ($alerts->take(12) as $alert)
            <tr>
                <td><span class="pill {{ $severityClass($alert->severity ?? 'medium') }}">{{ $alert->severity ?? 'medium' }}</span></td>
                <td>{{ $alert->title ?: 'Security alert' }}<br><span class="muted">{{ \Illuminate\Support\Str::limit((string) ($alert->summary ?? ''), 110) }}</span></td>
                <td>{{ (bool) ($alert->resolved ?? false) ? 'Resolved' : 'Open' }}</td>
                <td>{{ $formatDate($alert->detected_at ?? $alert->created_at ?? null) }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="muted">No alerts in this period.</td></tr>
        @endforelse
        </tbody>
    </table>

    <h2>Incidents</h2>
    <table class="rows">
        <thead><tr><th>Severity</th><th>Event</th><th>Status</th><th>Detected</th></tr></thead>
        <tbody>
        @forelse ($incidents->take(12) as $incident)
            <tr>
                <td><span class="pill {{ $severityClass($incident->severity ?? 'medium') }}">{{ $incident->severity ?? 'medium' }}</span></td>
                <td>{{ filled($incident->event ?? null) ? ucwords(str_replace('_', ' ', $incident->event)) : 'Security incident' }}<br><span class="muted">{{ $incident->site_url ?: $incident->ip ?: '-' }}</span></td>
                <td>{{ ucfirst($incident->status ?? 'open') }}</td>
                <td>{{ $formatDate($incident->event_created_at ?? $incident->created_at ?? null) }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="muted">No incidents in this period.</td></tr>
        @endforelse
        </tbody>
    </table>

    <h2>Vulnerabilities</h2>
    <table class="rows">
        <thead><tr><th>Severity</th><th>Finding</th><th>Status</th><th>Detected</th></tr></thead>
        <tbody>
        @forelse ($vulnerabilities->take(12) as $vulnerability)
            <tr>
                <td><span class="pill {{ $severityClass($vulnerability->severity ?? 'medium') }}">{{ $vulnerability->severity ?? 'medium' }}</span></td>
                <td>{{ $vulnerability->title ?: $vulnerability->name ?: 'Vulnerability' }}<br><span class="muted">{{ $vulnerability->cve ?: $vulnerability->slug ?: 'No CVE reference' }}</span></td>
                <td>{{ ucfirst($vulnerability->status ?? 'open') }}</td>
                <td>{{ $formatDate($vulnerability->detected_at ?? $vulnerability->created_at ?? null) }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="muted">No vulnerabilities in this period.</td></tr>
        @endforelse
        </tbody>
    </table>

    @if (filled($reportRequest->note))
        <h2>Request Note</h2>
        <p>{{ $reportRequest->note }}</p>
    @endif

    <div class="footer">
        CyberShield report generated for {{ $client?->company_name ?? 'Client' }}. Treat as confidential operational security information.
    </div>
</div>
</body>
</html>
