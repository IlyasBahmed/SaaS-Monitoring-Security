<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SOC Executive Report | Armious Protect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: A4;
            margin: 0;
        }

        * {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            margin: 0;
            padding: 0;
            background: #f0f4f8;
        }

        /* A4 page container */
        .a4-page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: white;
            padding: 12mm 18mm 10mm 18mm;
            box-sizing: border-box;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        /* Print optimizations */
        @media print {
            body {
                background: white;
            }
            .a4-page {
                margin: 0;
                box-shadow: none;
                padding: 12mm 18mm 10mm 18mm;
            }
            .break-inside-avoid {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }

        /* Custom utilities */
        .tracking-tight {
            letter-spacing: -0.3px;
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
        'critical' => 'bg-red-100 text-red-800 border-red-200',
        'high' => 'bg-orange-100 text-orange-800 border-orange-200',
        'medium' => 'bg-amber-100 text-amber-800 border-amber-200',
        'low' => 'bg-green-100 text-green-800 border-green-200',
        default => 'bg-slate-100 text-slate-700 border-slate-200',
    };

    $vulnCollection = collect($vulnerabilities ?? [])->take(5);
    $incidentCollection = collect($incidents ?? [])->take(4);
@endphp

<div class="a4-page">
    <!-- Top accent bar -->
    <div class="h-1.5 bg-gradient-to-r from-teal-600 via-emerald-500 to-teal-600 mb-5 -mt-[12mm] w-[calc(100%+36mm)] ml-[-18mm]"></div>

    <!-- Header -->
    <div class="flex justify-between items-start mb-5 pb-3 border-b border-slate-200">
        <div class="flex-1">
            <div class="text-teal-700 text-[11px] font-bold tracking-wider mb-1.5">ARMIous PROTECT</div>
            <h1 class="text-[32px] font-black text-slate-900 tracking-tight leading-tight mb-1">SOC Executive Report</h1>
            <p class="text-slate-500 text-[12px]">Security posture, threat activity & response summary</p>
        </div>
        <div class="text-right">
            <div class="text-[10px] text-slate-500 space-y-0.5">
                <div><span class="font-semibold text-slate-700">Report ID</span> SOC-{{ now()->format('Ymd') }}</div>
                <div><span class="font-semibold text-slate-700">Period</span> {{ $periodLabel }}</div>
                <div><span class="font-semibold text-slate-700">Range</span> {{ $from ?? '-' }} → {{ $to ?? now()->format('Y-m-d') }}</div>
                <div><span class="font-semibold text-slate-700">Generated</span> {{ $generatedAt ?? now()->format('Y-m-d H:i') }}</div>
            </div>
            <div class="mt-2 inline-block px-3 py-1 bg-teal-50 border border-teal-200 text-teal-700 text-[9px] font-bold tracking-wide">TLP:CLEAR · CONFIDENTIAL</div>
        </div>
    </div>

    @if (!empty($mongoError))
        <div class="mb-4 p-3 bg-amber-50 border-l-4 border-amber-500 text-amber-800 text-[11px] rounded-r">
            ⚠️ Data source warning: some telemetry could not be loaded. The report may be incomplete.
        </div>
    @endif

    <!-- Executive Summary Cards -->
    <div class="flex gap-4 mb-5 break-inside-avoid">
        <!-- Score Card -->
        <div class="w-[200px] bg-gradient-to-br from-slate-50 to-white border border-slate-200 rounded-xl p-4 flex-shrink-0">
            <div class="text-[10px] font-bold text-slate-400 tracking-wide uppercase mb-2">Security Posture Index</div>
            <div class="text-[62px] font-black text-teal-700 leading-none mb-1">{{ $overallScore }}</div>
            <div class="inline-block px-2.5 py-1 bg-teal-100 text-teal-800 text-[10px] font-bold rounded-full">{{ $scoreStatus }}</div>
        </div>

        <!-- Summary Card -->
        <div class="flex-1 bg-slate-50 border border-slate-200 rounded-xl p-4">
            <h2 class="text-[15px] font-bold text-slate-800 mb-2">Executive Summary</h2>
            <p class="text-[11px] text-slate-600 leading-relaxed">
                During {{ strtolower($periodLabel) }}, the SOC observed
                <strong class="text-slate-800">{{ number_format($safeStats['alerts']) }}</strong> security signals,
                <strong class="text-slate-800">{{ number_format($safeStats['incidents']) }}</strong> incidents, and
                <strong class="text-slate-800">{{ number_format($safeStats['vulnerabilities']) }}</strong> vulnerabilities across
                <strong class="text-slate-800">{{ number_format($safeStats['projects']) }}</strong> protected projects.
                Current agent coverage is <strong class="text-slate-800">{{ $agentCoverage }}%</strong>
                with <strong class="text-slate-800">{{ number_format($safeStats['online_agents']) }}</strong> online sensors.
            </p>
            @if (!empty($note))
                <div class="mt-2 pt-2 border-t border-slate-200 text-[11px] text-slate-500">
                    <span class="font-semibold">Analyst note:</span> {{ $note }}
                </div>
            @endif
        </div>
    </div>

    <!-- KPI Grid -->
    <div class="grid grid-cols-5 gap-3 mb-5 break-inside-avoid">
        <div class="bg-white border border-slate-200 rounded-lg p-3 text-center shadow-sm">
            <div class="text-[26px] font-black text-slate-800">{{ number_format($safeStats['projects']) }}</div>
            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Projects</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-lg p-3 text-center shadow-sm">
            <div class="text-[26px] font-black text-slate-800">{{ number_format($safeStats['alerts']) }}</div>
            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Signals</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-lg p-3 text-center shadow-sm">
            <div class="text-[26px] font-black text-slate-800">{{ number_format($safeStats['incidents']) }}</div>
            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Incidents</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-lg p-3 text-center shadow-sm">
            <div class="text-[26px] font-black text-slate-800">{{ number_format($safeStats['vulnerabilities']) }}</div>
            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Vulns</div>
        </div>
        <div class="bg-white border border-slate-200 rounded-lg p-3 text-center shadow-sm">
            <div class="text-[26px] font-black text-slate-800">{{ number_format($safeStats['agents']) }}</div>
            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Sensors</div>
        </div>
    </div>

    <!-- Two Column Section -->
    <div class="grid grid-cols-2 gap-4 mb-5 break-inside-avoid">
        <!-- Risk Distribution -->
        <div class="bg-white border border-slate-200 rounded-xl p-4">
            <div class="flex justify-between items-center mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-1 h-5 bg-teal-600 rounded"></div>
                    <h3 class="text-[13px] font-bold text-slate-800">Risk Distribution</h3>
                </div>
                <span class="text-[9px] text-slate-400">Alerts by severity</span>
            </div>
            <div class="space-y-3">
                @foreach([['label'=>'Critical','color'=>'red-600','bg'=>'red-100','value'=>$critical],['label'=>'High','color'=>'orange-600','bg'=>'orange-100','value'=>$high],['label'=>'Medium','color'=>'amber-600','bg'=>'amber-100','value'=>$medium],['label'=>'Low','color'=>'green-600','bg'=>'green-100','value'=>$low]] as $sev)
                <div>
                    <div class="flex justify-between text-[10px] font-semibold mb-1">
                        <span class="text-{{ $sev['color'] }}">{{ $sev['label'] }}</span>
                        <span class="text-slate-600">{{ $sev['value'] }}</span>
                    </div>
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-{{ $sev['color'] }} rounded-full" style="width: {{ $totalSeverity > 0 ? round(($sev['value'] / $totalSeverity) * 100) : 0 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Operational Metrics -->
        <div class="bg-white border border-slate-200 rounded-xl p-4">
            <div class="flex justify-between items-center mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-1 h-5 bg-teal-600 rounded"></div>
                    <h3 class="text-[13px] font-bold text-slate-800">Operational Metrics</h3>
                </div>
                <span class="text-[9px] text-slate-400">SOC readiness</span>
            </div>
            <div class="space-y-3">
                @foreach([['label'=>'Agent coverage','value'=>$agentCoverage,'unit'=>'%','color'=>'teal'],['label'=>'Open incidents','value'=>min(100, $safeStats['open_incidents'] * 12),'color'=>'orange'],['label'=>'Open vulns','value'=>min(100, $safeStats['open_vulnerabilities'] * 2),'color'=>'blue'],['label'=>'Offline agents','value'=>min(100, $safeStats['offline_agents'] * 8),'color'=>'slate']] as $metric)
                <div>
                    <div class="flex justify-between text-[10px] font-semibold mb-1">
                        <span class="text-slate-700">{{ $metric['label'] }}</span>
                        <span class="text-slate-600">{{ $metric['value'] }}{{ $metric['unit'] ?? '' }}</span>
                    </div>
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-{{ $metric['color'] }}-500 rounded-full" style="width: {{ $metric['value'] }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Top Risk Projects -->
    <div class="mb-5 break-inside-avoid">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-1 h-5 bg-teal-600 rounded"></div>
            <h3 class="text-[13px] font-bold text-slate-800">Top Risk Projects</h3>
            <span class="text-[9px] text-slate-400 ml-auto">Ranked by risk score</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-[10px] border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="text-left p-2 font-semibold text-slate-500">#</th>
                        <th class="text-left p-2 font-semibold text-slate-500">Project</th>
                        <th class="text-left p-2 font-semibold text-slate-500">Client / Domain</th>
                        <th class="text-left p-2 font-semibold text-slate-500">Score</th>
                        <th class="text-left p-2 font-semibold text-slate-500">Risk</th>
                        <th class="text-left p-2 font-semibold text-slate-500">Activity</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($scoreCollection->take(6) as $project)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="p-2 text-slate-500">{{ $loop->iteration }}</td>
                        <td class="p-2 font-semibold text-slate-800">{{ $project->name ?? 'Unnamed project' }}</td>
                        <td class="p-2">
                            <div>{{ $project->client->company_name ?? 'Unassigned' }}</div>
                            <div class="text-[9px] font-mono text-slate-400">{{ $project->domain ?? '-' }}</div>
                        </td>
                        <td class="p-2 font-bold text-slate-800">{{ (int) ($project->soc_score ?? 0) }}</td>
                        <td class="p-2">
                            <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-bold {{ $sevClass($project->soc_risk ?? 'medium') }}">
                                {{ $project->soc_risk ?? 'medium' }}
                            </span>
                        </td>
                        <td class="p-2 font-mono text-[9px] text-slate-500">
                            A: {{ (int) ($project->alerts_count ?? 0) }} / I: {{ (int) ($project->incidents_count ?? 0) }} / V: {{ (int) ($project->vulnerabilities_count ?? 0) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-3 text-center text-slate-400">No project risk data available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Active Exposures & Recent Incidents -->
    <div class="grid grid-cols-2 gap-4 mb-5 break-inside-avoid">
        <!-- Active Exposures -->
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="flex justify-between items-center p-3 border-b border-slate-200 bg-slate-50">
                <div class="flex items-center gap-2">
                    <div class="w-1 h-5 bg-teal-600 rounded"></div>
                    <h3 class="text-[12px] font-bold text-slate-800">Active Exposures</h3>
                </div>
                <span class="text-[9px] text-slate-400">Top findings</span>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($vulnCollection as $vuln)
                <div class="p-3">
                    <div class="font-semibold text-[11px] text-slate-800">{{ $vuln->name ?? $vuln->title ?? 'Unnamed finding' }}</div>
                    <div class="text-[9px] font-mono text-slate-400 mt-0.5">{{ $vuln->site_url ?? $vuln->url ?? 'unknown asset' }}</div>
                    <div class="mt-1.5">
                        <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-bold {{ $sevClass($vuln->severity ?? 'low') }}">
                            {{ $vuln->severity ?? 'low' }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-slate-400 text-[11px]">No vulnerabilities detected</div>
                @endforelse
            </div>
        </div>

        <!-- Recent Incidents -->
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="flex justify-between items-center p-3 border-b border-slate-200 bg-slate-50">
                <div class="flex items-center gap-2">
                    <div class="w-1 h-5 bg-teal-600 rounded"></div>
                    <h3 class="text-[12px] font-bold text-slate-800">Recent Incidents</h3>
                </div>
                <span class="text-[9px] text-slate-400">SOC timeline</span>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($incidentCollection as $incident)
                <div class="p-3">
                    <div class="font-semibold text-[11px] text-slate-800">{{ $incident->event ?? $incident->incident_key ?? 'Security event' }}</div>
                    <div class="text-[9px] text-slate-500 mt-1">
                        {{ $incident->site_url ?? $incident->ip ?? 'asset' }}
                        <span class="mx-1">·</span>
                        <span class="uppercase">{{ $incident->severity ?? 'medium' }}</span>
                        <span class="mx-1">·</span>
                        <span>{{ $incident->status ?? 'active' }}</span>
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-slate-400 text-[11px]">No recent incidents recorded</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Exposure Summary KPIs -->
    <div class="mb-5 break-inside-avoid">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-1 h-5 bg-teal-600 rounded"></div>
            <h3 class="text-[13px] font-bold text-slate-800">Exposure Summary</h3>
            <span class="text-[9px] text-slate-400 ml-auto">Vulnerability & asset telemetry</span>
        </div>
        <div class="grid grid-cols-5 gap-3">
            <div class="bg-gradient-to-br from-red-50 to-white border border-red-200 rounded-lg p-2.5 text-center">
                <div class="text-[22px] font-black text-red-700">{{ number_format($safeStats['critical_vulnerabilities']) }}</div>
                <div class="text-[8px] font-bold text-red-600 uppercase tracking-wide">Critical Vulns</div>
            </div>
            <div class="bg-gradient-to-br from-orange-50 to-white border border-orange-200 rounded-lg p-2.5 text-center">
                <div class="text-[22px] font-black text-orange-700">{{ number_format($safeStats['high_vulnerabilities']) }}</div>
                <div class="text-[8px] font-bold text-orange-600 uppercase tracking-wide">High Vulns</div>
            </div>
            <div class="bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-center">
                <div class="text-[22px] font-black text-slate-700">{{ number_format($safeStats['open_vulnerabilities']) }}</div>
                <div class="text-[8px] font-bold text-slate-500 uppercase tracking-wide">Open Vulns</div>
            </div>
            <div class="bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-center">
                <div class="text-[22px] font-black text-slate-700">{{ number_format($safeStats['inventories']) }}</div>
                <div class="text-[8px] font-bold text-slate-500 uppercase tracking-wide">Assets</div>
            </div>
            <div class="bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-center">
                <div class="text-[22px] font-black text-slate-700">{{ number_format($safeStats['offline_agents']) }}</div>
                <div class="text-[8px] font-bold text-slate-500 uppercase tracking-wide">Offline Sensors</div>
            </div>
        </div>
    </div>

    <!-- Recommended Actions -->
    <div class="break-inside-avoid">
        <div class="flex items-center gap-2 mb-3">
            <div class="w-1 h-5 bg-teal-600 rounded"></div>
            <h3 class="text-[13px] font-bold text-slate-800">Recommended Actions</h3>
            <span class="text-[9px] text-slate-400 ml-auto">Prioritized for remediation</span>
        </div>
        <div class="grid grid-cols-4 gap-3">
            <div class="bg-red-50 border border-red-200 rounded-xl p-3">
                <div class="text-[10px] font-black text-red-700 uppercase tracking-wider mb-1.5">P1 · Immediate</div>
                <div class="text-[12px] font-bold text-slate-800 mb-1">Resolve critical exposure</div>
                <div class="text-[9px] text-slate-600 leading-relaxed">Patch or isolate critical vulnerabilities and validate fixes with a follow-up scan.</div>
            </div>
            <div class="bg-orange-50 border border-orange-200 rounded-xl p-3">
                <div class="text-[10px] font-black text-orange-700 uppercase tracking-wider mb-1.5">P2 · High</div>
                <div class="text-[12px] font-bold text-slate-800 mb-1">Close active incidents</div>
                <div class="text-[9px] text-slate-600 leading-relaxed">Prioritize open incidents with high severity and document containment status.</div>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-3">
                <div class="text-[10px] font-black text-blue-700 uppercase tracking-wider mb-1.5">P3 · Coverage</div>
                <div class="text-[12px] font-bold text-slate-800 mb-1">Restore sensor health</div>
                <div class="text-[9px] text-slate-600 leading-relaxed">Reconnect offline agents and verify telemetry ingestion across critical projects.</div>
            </div>
            <div class="bg-slate-100 border border-slate-200 rounded-xl p-3">
                <div class="text-[10px] font-black text-slate-600 uppercase tracking-wider mb-1.5">P4 · Governance</div>
                <div class="text-[12px] font-bold text-slate-800 mb-1">Review risk owners</div>
                <div class="text-[9px] text-slate-600 leading-relaxed">Assign owners for top-risk projects and track remediation progress weekly.</div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="mt-5 pt-3 border-t border-slate-200 flex justify-between text-[8px] text-slate-400">
        <span>Armious Protect SOC · Executive security report</span>
        <span>Confidential · Generated {{ now()->format('Y-m-d') }} · Page 1</span>
    </div>
</div>
</body>
</html>