<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Project Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; line-height: 1.5; }
        .page { padding: 32px; }
        .header { border-bottom: 2px solid #0891b2; padding-bottom: 18px; margin-bottom: 22px; }
        .eyebrow { color: #0891b2; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.6px; }
        h1 { margin: 6px 0 0; font-size: 28px; }
        h2 { margin: 24px 0 10px; font-size: 16px; color: #0f172a; }
        .muted { color: #64748b; }
        .grid { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .grid td { width: 25%; border: 1px solid #e2e8f0; padding: 12px; vertical-align: top; }
        .label { color: #64748b; font-size: 10px; text-transform: uppercase; font-weight: 700; }
        .value { margin-top: 4px; font-size: 18px; font-weight: 800; }
        table.rows { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.rows th, table.rows td { border-bottom: 1px solid #e2e8f0; padding: 8px 6px; text-align: left; }
        table.rows th { color: #64748b; font-size: 10px; text-transform: uppercase; }
        .pill { display: inline-block; padding: 2px 7px; border-radius: 4px; background: #ecfeff; color: #0e7490; font-size: 10px; font-weight: 700; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div class="eyebrow">CyberShield Client Report</div>
            <h1>{{ $project->domain ?: $project->name }}</h1>
            <p class="muted">
                {{ $client?->company_name ?? 'Client' }} /
                {{ $periodFrom->format('Y-m-d') }} to {{ $periodTo->format('Y-m-d') }} /
                Generated {{ now()->format('Y-m-d H:i') }}
            </p>
        </div>

        <table class="grid">
            <tr>
                <td><div class="label">Security Score</div><div class="value">{{ $projectScore['security_score'] ?? 0 }}</div></td>
                <td><div class="label">Risk</div><div class="value">{{ ucfirst($projectScore['risk_label'] ?? 'low') }}</div></td>
                <td><div class="label">Alerts</div><div class="value">{{ $alerts->count() }}</div></td>
                <td><div class="label">Incidents</div><div class="value">{{ $incidents->count() }}</div></td>
            </tr>
        </table>

        <h2>Project Details</h2>
        <table class="rows">
            <tr><th>Name</th><td>{{ $project->name ?: '-' }}</td><th>Domain</th><td>{{ $project->domain ?: '-' }}</td></tr>
            <tr><th>Stack</th><td>{{ \App\Models\Projects::normalizeProjectType($project->stack) }}</td><th>IP Address</th><td>{{ $project->ip_address ?: '-' }}</td></tr>
            <tr><th>Status</th><td>{{ ucfirst($project->status ?: 'offline') }}</td><th>Cloudflare</th><td>{{ $project->cloudflare_enabled ? 'Enabled' : 'Not linked' }}</td></tr>
        </table>

        <h2>Alerts</h2>
        <table class="rows">
            <thead><tr><th>Severity</th><th>Title</th><th>Detected</th></tr></thead>
            <tbody>
            @forelse ($alerts->take(12) as $alert)
                <tr>
                    <td><span class="pill">{{ $alert->severity ?? 'medium' }}</span></td>
                    <td>{{ $alert->title ?: 'Security alert' }}</td>
                    <td>{{ $alert->detected_at ? $alert->detected_at->format('Y-m-d H:i') : '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted">No alerts in this period.</td></tr>
            @endforelse
            </tbody>
        </table>

        <h2>Incidents</h2>
        <table class="rows">
            <thead><tr><th>Severity</th><th>Event</th><th>Status</th><th>Detected</th></tr></thead>
            <tbody>
            @forelse ($incidents->take(12) as $incident)
                <tr>
                    <td><span class="pill">{{ $incident->severity ?? 'medium' }}</span></td>
                    <td>{{ filled($incident->event ?? null) ? ucwords(str_replace('_', ' ', $incident->event)) : 'Security incident' }}</td>
                    <td>{{ ucfirst($incident->status ?? 'open') }}</td>
                    <td>{{ $incident->event_created_at ? $incident->event_created_at->format('Y-m-d H:i') : '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No incidents in this period.</td></tr>
            @endforelse
            </tbody>
        </table>

        <h2>Vulnerabilities</h2>
        <table class="rows">
            <thead><tr><th>Severity</th><th>Title</th><th>Status</th><th>Detected</th></tr></thead>
            <tbody>
            @forelse ($vulnerabilities->take(12) as $vulnerability)
                <tr>
                    <td><span class="pill">{{ $vulnerability->severity ?? 'medium' }}</span></td>
                    <td>{{ $vulnerability->title ?: $vulnerability->name ?: 'Vulnerability' }}</td>
                    <td>{{ ucfirst($vulnerability->status ?? 'open') }}</td>
                    <td>{{ $vulnerability->detected_at ? $vulnerability->detected_at->format('Y-m-d H:i') : '-' }}</td>
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
    </div>
</body>
</html>
