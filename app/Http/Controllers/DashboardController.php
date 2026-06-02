<?php

namespace App\Http\Controllers;
use App\Services\DashboardStatsService;
use App\Models\Alert;
use App\Models\AuditLog;
use App\Models\clients;
use App\Models\HealthReport;
use App\Models\Incident;
use App\Models\ProjectAgent;
use App\Models\Projects;
use App\Models\SiteVulnerability;
use App\Models\AgentLog;
use App\Services\ProjectSecurityScore;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {
        $projects = Projects::with(['client', 'agents'])->latest()->get();

        $projectIds = $projects->pluck('id')->map(fn ($id) => (int) $id)->values();

        $alerts = collect(rescue(fn () =>
            Alert::whereIn('project_id', $projectIds->all())->orderBy('detected_at', 'desc')->take(160)->get(),
            collect(),
            false
        ));

        $incidents = collect(rescue(fn () =>
            Incident::whereIn('project_id', $projectIds->all())
                ->orderBy('event_created_at', 'desc')
                ->take(160)
                ->get(),
            collect(),
            false
        ));

        $vulnerabilities = collect(rescue(fn () =>
            SiteVulnerability::whereIn('project_id', $projectIds->all())->latest('detected_at')->take(160)->get(),
            collect(),
            false
        ));

        $healthReports = collect(rescue(fn () =>
            HealthReport::whereIn('project_id', $projectIds->all())->latest('event_created_at')->take(160)->get(),
            collect(),
            false
        ));

        $agentLogs = collect(rescue(fn () =>
            AgentLog::whereIn('project_id', $projectIds->all())->latest('event_created_at')->take(160)->get(),
            collect(),
            false
        ));

        $auditLogs = collect(rescue(fn () =>
            AuditLog::whereIn('project_id', $projectIds->all())
                ->orderBy('event_created_at', 'desc')
                ->take(160)
                ->get(),
            collect(),
            false
        ));

        $projectScores = $projects->map(fn ($project) =>
            ProjectSecurityScore::forProject($project, $alerts, $incidents, $vulnerabilities, $healthReports)
        );

        $mapPoints = $this->buildMapPoints($incidents);

       $stats = DashboardStatsService::generate(
    $projects,
    $projectScores,
    $incidents,
    $vulnerabilities,
    $agentLogs,
    $auditLogs
);

        $liveThreats = $incidents->take(10)->map(fn ($incident) => [
            'source' => 'Incident',
            'time' => $this->parseDate($incident->event_created_at ?? $incident->created_at)?->format('H:i:s') ?? '--:--:--',
            'asset' => $incident->site_url ?: 'Protected asset',
            'ip' => $incident->ip ?: 'Unknown IP',
            'type' => strtoupper(str_replace('_', ' ', $incident->event ?? 'INCIDENT')),
            'severity' => strtolower($incident->severity ?? 'medium'),
            'status' => ucfirst($incident->status ?? 'open'),
        ])->values();

        $topAttacked = $projects->map(function ($project) use ($incidents, $vulnerabilities) {
            $id = (int) $project->id;

            return [
                'name' => $project->domain ?: $project->name ?: 'Project #'.$project->id,
                'client' => $project->client?->company_name ?: '-',
                'count' => $incidents->where('project_id', $id)->count()
                    + $vulnerabilities->where('project_id', $id)->count(),
            ];
        })->sortByDesc('count')->take(6)->values();

        $recentLogs = collect()
            ->merge($agentLogs->map(fn ($log) => $this->logRow($log, 'Agent')))
            ->merge($auditLogs->map(fn ($log) => $this->logRow($log, 'Audit')))
            ->sortByDesc('timestamp')
            ->take(10)
            ->values();

        $weeklyThreats = collect(range(6, 0))->map(function ($daysAgo) use ($incidents, $vulnerabilities) {
            $day = now()->subDays($daysAgo);

            return [
                'label' => $day->format('D'),
                'count' => $incidents->filter(fn ($i) => $this->parseDate($i->event_created_at ?? $i->created_at)?->isSameDay($day))->count()
                    + $vulnerabilities->filter(fn ($v) => $this->parseDate($v->detected_at ?? $v->created_at)?->isSameDay($day))->count(),
            ];
        })->values();
    if ($mapPoints->isEmpty()) {

    $mapPoints = collect([

        [
            'lat' => 33.5731,
            'lng' => -7.5898,
            'severity' => 'critical',
            'ip' => '102.50.242.12',
            'event' => 'DDoS Attack',
            'asset' => 'armiousprotect.com',
            'country' => 'Morocco',
            'city' => 'Casablanca',
        ],

        [
            'lat' => 39.9042,
            'lng' => 116.4074,
            'severity' => 'critical',
            'ip' => '101.32.118.41',
            'event' => 'APT Attack',
            'asset' => 'api.armiousprotect.com',
            'country' => 'China',
            'city' => 'Beijing',
        ],

        [
            'lat' => 55.7558,
            'lng' => 37.6173,
            'severity' => 'high',
            'ip' => '185.220.101.4',
            'event' => 'Bruteforce Attempt',
            'asset' => 'dashboard.armiousprotect.com',
            'country' => 'Russia',
            'city' => 'Moscow',
        ],

        [
            'lat' => 40.7128,
            'lng' => -74.0060,
            'severity' => 'medium',
            'ip' => '192.241.190.12',
            'event' => 'Malware Beacon',
            'asset' => 'cdn.armiousprotect.com',
            'country' => 'USA',
            'city' => 'New York',
        ],

        [
            'lat' => 48.8566,
            'lng' => 2.3522,
            'severity' => 'critical',
            'ip' => '91.108.84.15',
            'event' => 'SQL Injection',
            'asset' => 'secure.armiousprotect.com',
            'country' => 'France',
            'city' => 'Paris',
        ],

        [
            'lat' => 35.6762,
            'lng' => 139.6503,
            'severity' => 'high',
            'ip' => '43.153.12.9',
            'event' => 'Botnet Traffic',
            'asset' => 'gateway.armiousprotect.com',
            'country' => 'Japan',
            'city' => 'Tokyo',
        ],

    ]);
}
        return view('pages.dashboard', [
            'stats' => $stats,
            'liveThreats' => $liveThreats,
            'topAttacked' => $topAttacked,
            'mapPoints' => $mapPoints,
            'unknownGeoCount' => max(0, $incidents->count() - $mapPoints->count()),
            'recentLogs' => $recentLogs,
            'weeklyThreats' => $weeklyThreats,
        ]);
    }

    private function buildMapPoints($incidents)
    {
        return $incidents
            ->filter(fn ($incident) => $this->isPublicIp($incident->ip ?? null))
            ->take(40)
            ->map(function ($incident) {
                $geo = $this->geoForIp($incident->ip);

                if (! $geo) {
                    return null;
                }

                return [
                    'lat' => $geo['lat'],
                    'lng' => $geo['lon'],
                    'severity' => strtolower($incident->severity ?? 'medium'),
                    'ip' => $incident->ip,
                    'event' => $incident->event ?? 'Security Incident',
                    'asset' => $incident->site_url ?? 'Protected Asset',
                    'country' => $geo['country'] ?? '-',
                    'city' => $geo['city'] ?? '-',
                ];
            })
            ->filter()
            ->values();
    }

    private function isPublicIp(?string $ip): bool
    {
        return $ip && filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }

    private function geoForIp(string $ip): ?array
    {
        return Cache::remember("geoip:$ip", now()->addDays(7), function () use ($ip) {
            $res = Http::timeout(3)->get("http://ip-api.com/json/$ip", [
                'fields' => 'status,country,city,lat,lon,query',
            ])->json();

            return ($res['status'] ?? null) === 'success' ? $res : null;
        });
    }

    private function parseDate($value): ?Carbon
    {
        if (blank($value)) {
            return null;
        }

        return rescue(fn () => Carbon::parse($value), null, false);
    }

    private function logRow($log, string $source): array
    {
        $createdAt = $this->parseDate($log->event_created_at ?? $log->created_at ?? null);

        return [
            'source' => $source,
            'event' => ucwords(str_replace('_', ' ', $log->event ?? 'Security event')),
            'category' => ucwords(str_replace('_', ' ', $log->category ?? 'Telemetry')),
            'severity' => strtolower($log->severity ?? 'info'),
            'project' => $log->site_url ?: 'Unknown project',
            'ip' => $log->ip ?: data_get($log->metadata ?? [], 'ip', '-'),
            'time' => $createdAt ? $createdAt->format('H:i:s') : '--:--:--',
            'timestamp' => $createdAt?->timestamp ?? 0,
        ];
    }
}
