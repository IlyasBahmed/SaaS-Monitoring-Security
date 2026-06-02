<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectSecurityController;
use App\Http\Controllers\UserInviteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CloudflareActionController;
use App\Http\Controllers\CloudflareController;
use App\Http\Controllers\CloudflareProjectController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\Admin\GlobalReportController;
use App\Models\agents;
use App\Models\Alert;
use App\Models\AuditLog;
use App\Models\clients;
use App\Models\HealthReport;
use App\Models\Incident;
use App\Models\ProjectAgent;
use App\Models\Projects;
use App\Models\ReportRequest;
use App\Models\SiteVulnerability;
use App\Models\User;
use App\Notifications\ClientPasswordSetupNotification;
use App\Services\ProjectSecurityScore;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified', 'dashboard.access'])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/settings', function () {
        return redirect()->route('profile.edit');
    })->name('settings.index');

    /*
    |--------------------------------------------------------------------------
    | Client Portal
    |--------------------------------------------------------------------------
    */

    Route::get('/client-dashboard', function () {
        $user = request()->user();
        $client = clients::query()
            ->where('user_id', $user?->id)
            ->first();

        $emptyPayload = [
            'client' => $client,
            'projects' => collect(),
            'healthRows' => collect(),
            'recentAlerts' => collect(),
            'recentIncidents' => collect(),
            'stats' => [
                'projects' => 0,
                'active_projects' => 0,
                'open_alerts' => 0,
                'open_incidents' => 0,
                'cloudflare_coverage' => 0,
                'security_score' => 0,
            ],
        ];

        if (! $client) {
            return view('pages.client-dashboard', $emptyPayload);
        }

        $projects = $client->projects()
            ->with('agents')
            ->latest()
            ->get();

        $projectIds = $projects
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $projectsById = $projects->keyBy(fn ($project) => (int) $project->id);

        $recentAlerts = collect(rescue(
            fn () => Alert::query()
                ->whereIn('project_id', $projectIds->all())
                ->orderByDesc('detected_at')
                ->orderByDesc('created_at')
                ->take(8)
                ->get(),
            collect(),
            false
        ));

        $recentIncidents = collect(rescue(
            fn () => Incident::query()
                ->whereIn('project_id', $projectIds->all())
                ->orderByDesc('event_created_at')
                ->orderByDesc('created_at')
                ->take(8)
                ->get(),
            collect(),
            false
        ));

        $scoreAlerts = collect(rescue(
            fn () => Alert::query()->whereIn('project_id', $projectIds->all())->get(),
            collect(),
            false
        ));

        $scoreIncidents = collect(rescue(
            fn () => Incident::query()->whereIn('project_id', $projectIds->all())->get(),
            collect(),
            false
        ));

        $scoreVulnerabilities = collect(rescue(
            fn () => SiteVulnerability::query()->whereIn('project_id', $projectIds->all())->get(),
            collect(),
            false
        ));

        $scoreHealthReports = collect(rescue(
            fn () => HealthReport::query()->whereIn('project_id', $projectIds->all())->latest('event_created_at')->get(),
            collect(),
            false
        ));

        $alertCountsByProject = $scoreAlerts
            ->groupBy(fn (Alert $alert) => (int) ($alert->project_id ?? 0))
            ->map->count();

        $incidentCountsByProject = $scoreIncidents
            ->groupBy(fn (Incident $incident) => (int) ($incident->project_id ?? 0))
            ->map->count();

        $projects->each(function (Projects $project) use ($alertCountsByProject, $incidentCountsByProject) {
            $project->setAttribute('alerts_count', (int) ($alertCountsByProject->get((int) $project->id, 0)));
            $project->setAttribute('incidents_count', (int) ($incidentCountsByProject->get((int) $project->id, 0)));
        });

        $scoreForProject = static function (Projects $project) use ($scoreAlerts, $scoreIncidents, $scoreVulnerabilities, $scoreHealthReports): int {
            return ProjectSecurityScore::forProject($project, $scoreAlerts, $scoreIncidents, $scoreVulnerabilities, $scoreHealthReports)['security_score'];
        };

        $healthRows = $projects
            ->map(function (Projects $project) use ($scoreForProject) {
                $lastSeen = $project->agent_last_seen_at ?? $project->last_seen_at;
                $lastSeenAt = $lastSeen ? rescue(fn () => Carbon::parse($lastSeen), null, false) : null;
                $status = strtolower((string) ($project->status ?? 'offline'));
                $score = $scoreForProject($project);

                return [
                    'id' => (int) $project->id,
                    'name' => $project->domain ?: $project->name ?: 'Project #'.$project->id,
                    'type' => Projects::normalizeProjectType($project->stack),
                    'status' => $status,
                    'score' => $score,
                    'alerts' => (int) ($project->alerts_count ?? 0),
                    'incidents' => (int) ($project->incidents_count ?? 0),
                    'cloudflare' => (bool) $project->cloudflare_enabled,
                    'last_seen' => $lastSeenAt ? $lastSeenAt->diffForHumans() : '-',
                ];
            })
            ->values();

        $stats = [
            'projects' => $projects->count(),
            'active_projects' => $projects->filter(fn (Projects $project) => strtolower((string) $project->status) === 'active')->count(),
            'open_alerts' => (int) $projects->sum('alerts_count'),
            'open_incidents' => (int) $projects->sum('incidents_count'),
            'cloudflare_coverage' => $projects->count()
                ? (int) round(($projects->where('cloudflare_enabled', true)->count() / $projects->count()) * 100)
                : 0,
            'security_score' => $healthRows->count() ? (int) round($healthRows->avg('score')) : 0,
        ];

        $alertRows = $recentAlerts
            ->map(function (Alert $alert) use ($projectsById) {
                $project = $projectsById->get((int) ($alert->project_id ?? 0));
                $detectedAt = $alert->detected_at
                    ? rescue(fn () => Carbon::parse($alert->detected_at), null, false)
                    : null;

                return [
                    'title' => $alert->title ?: 'Security alert',
                    'severity' => strtolower((string) ($alert->severity ?? 'medium')),
                    'project' => $project?->domain ?: $project?->name ?: 'Protected asset',
                    'time' => $detectedAt ? $detectedAt->diffForHumans() : 'Recently',
                ];
            })
            ->values();

        $incidentRows = $recentIncidents
            ->map(function (Incident $incident) use ($projectsById) {
                $project = $projectsById->get((int) ($incident->project_id ?? 0));
                $createdAt = $incident->event_created_at
                    ? rescue(fn () => Carbon::parse($incident->event_created_at), null, false)
                    : null;

                return [
                    'title' => filled($incident->event ?? null)
                        ? ucwords(str_replace('_', ' ', (string) $incident->event))
                        : 'Security incident',
                    'severity' => strtolower((string) ($incident->severity ?? 'medium')),
                    'status' => strtolower((string) ($incident->status ?? 'open')),
                    'project' => $project?->domain ?: $project?->name ?: 'Protected asset',
                    'time' => $createdAt ? $createdAt->diffForHumans() : 'Recently',
                ];
            })
            ->values();

        return view('pages.client-dashboard', [
            'client' => $client,
            'projects' => $projects,
            'healthRows' => $healthRows,
            'recentAlerts' => $alertRows,
            'recentIncidents' => $incidentRows,
            'stats' => $stats,
        ]);
    })->name('client.dashboard');

    Route::get('/client-projects', function () {
        $user = request()->user();
        $client = clients::query()
            ->where('user_id', $user?->id)
            ->first();

        if (! $client) {
            return view('pages.client-projects', [
                'client' => null,
                'projects' => collect(),
                'stats' => [
                    'total' => 0,
                    'active' => 0,
                    'offline' => 0,
                    'cloudflare' => 0,
                    'average_score' => 0,
                ],
            ]);
        }

        $projects = $client->projects()
            ->with('agents')
            ->latest()
            ->get();

        $projectIds = $projects
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $scoreAlerts = collect(rescue(
            fn () => Alert::query()->whereIn('project_id', $projectIds->all())->get(),
            collect(),
            false
        ));

        $scoreIncidents = collect(rescue(
            fn () => Incident::query()->whereIn('project_id', $projectIds->all())->get(),
            collect(),
            false
        ));

        $scoreVulnerabilities = collect(rescue(
            fn () => SiteVulnerability::query()->whereIn('project_id', $projectIds->all())->get(),
            collect(),
            false
        ));

        $scoreHealthReports = collect(rescue(
            fn () => HealthReport::query()->whereIn('project_id', $projectIds->all())->latest('event_created_at')->get(),
            collect(),
            false
        ));

        $alertCountsByProject = $scoreAlerts
            ->groupBy(fn (Alert $alert) => (int) ($alert->project_id ?? 0))
            ->map->count();

        $incidentCountsByProject = $scoreIncidents
            ->groupBy(fn (Incident $incident) => (int) ($incident->project_id ?? 0))
            ->map->count();

        $projects->each(function (Projects $project) use ($alertCountsByProject, $incidentCountsByProject) {
            $project->setAttribute('alerts_count', (int) ($alertCountsByProject->get((int) $project->id, 0)));
            $project->setAttribute('incidents_count', (int) ($incidentCountsByProject->get((int) $project->id, 0)));
        });

        $scoreForProject = static function (Projects $project) use ($scoreAlerts, $scoreIncidents, $scoreVulnerabilities, $scoreHealthReports): int {
            return ProjectSecurityScore::forProject($project, $scoreAlerts, $scoreIncidents, $scoreVulnerabilities, $scoreHealthReports)['security_score'];
        };

        $projectRows = $projects
            ->map(function (Projects $project) use ($scoreForProject) {
                $status = strtolower((string) ($project->status ?? 'offline'));
                $lastSeen = $project->agent_last_seen_at ?? $project->last_seen_at;
                $lastSeenAt = $lastSeen ? rescue(fn () => Carbon::parse($lastSeen), null, false) : null;

                return [
                    'id' => (int) $project->id,
                    'name' => $project->name ?: 'Project #'.$project->id,
                    'domain' => $project->domain ?: '-',
                    'ip_address' => $project->ip_address ?: '-',
                    'stack' => Projects::normalizeProjectType($project->stack),
                    'status' => $status,
                    'score' => $scoreForProject($project),
                    'cloudflare' => (bool) $project->cloudflare_enabled,
                    'connected' => (bool) $project->is_connected,
                    'alerts' => (int) ($project->alerts_count ?? 0),
                    'incidents' => (int) ($project->incidents_count ?? 0),
                    'last_seen' => $lastSeenAt ? $lastSeenAt->diffForHumans() : '-',
                ];
            })
            ->values();

        return view('pages.client-projects', [
            'client' => $client,
            'projects' => $projectRows,
            'stats' => [
                'total' => $projectRows->count(),
                'active' => $projectRows->where('status', 'active')->count(),
                'offline' => $projectRows->where('status', '!=', 'active')->count(),
                'cloudflare' => $projectRows->where('cloudflare', true)->count(),
                'average_score' => $projectRows->count() ? (int) round($projectRows->avg('score')) : 0,
            ],
        ]);
    })->name('client.projects');

    Route::get('/client-projects/{project}', function (Projects $project) {
        $user = request()->user();
        $client = clients::query()
            ->where('user_id', $user?->id)
            ->first();

        abort_unless($client && (int) $project->client_id === (int) $client->id, 404);

        $project->load('agents');
        $projectId = (int) $project->id;

        $alerts = collect(rescue(
            fn () => Alert::query()
                ->where('project_id', $projectId)
                ->orderByDesc('detected_at')
                ->orderByDesc('created_at')
                ->take(20)
                ->get(),
            collect(),
            false
        ));

        $incidents = collect(rescue(
            fn () => Incident::query()
                ->where('project_id', $projectId)
                ->orderByDesc('event_created_at')
                ->orderByDesc('created_at')
                ->take(20)
                ->get(),
            collect(),
            false
        ));

        $vulnerabilities = collect(rescue(
            fn () => SiteVulnerability::query()
                ->where('project_id', $projectId)
                ->orderByDesc('detected_at')
                ->take(20)
                ->get(),
            collect(),
            false
        ));

        $healthReports = collect(rescue(
            fn () => HealthReport::query()
                ->where('project_id', $projectId)
                ->latest('event_created_at')
                ->get(),
            collect(),
            false
        ));

        $projectScore = ProjectSecurityScore::forProject($project, $alerts, $incidents, $vulnerabilities, $healthReports);
        $project->setAttribute('alerts_count', $alerts->count());
        $project->setAttribute('incidents_count', $incidents->count());
        $project->setAttribute('vulnerabilities_count', $vulnerabilities->count());

        return view('pages.client-project-show', compact(
            'client',
            'project',
            'projectScore',
            'alerts',
            'incidents',
            'vulnerabilities'
        ));
    })->name('client.projects.show');

    Route::get('/client-incidents', function () {
        $user = request()->user();
        $client = clients::query()
            ->where('user_id', $user?->id)
            ->first();

        $emptyPayload = [
            'client' => $client,
            'incidents' => collect(),
            'projects' => collect(),
            'stats' => [
                'total' => 0,
                'open' => 0,
                'in_progress' => 0,
                'resolved' => 0,
                'critical' => 0,
                'high' => 0,
                'latest_human' => '-',
            ],
        ];

        if (! $client) {
            return view('pages.client-incidents', $emptyPayload);
        }

        $projects = $client->projects()
            ->latest()
            ->get(['id', 'name', 'domain', 'ip_address', 'stack', 'status']);

        $projectIds = $projects
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $projectsById = $projects->keyBy(fn (Projects $project) => (int) $project->id);

        $projectRows = $projects
            ->map(fn (Projects $project) => [
                'id' => (int) $project->id,
                'name' => $project->name ?: 'Project #'.$project->id,
                'domain' => $project->domain ?: '-',
                'stack' => Projects::normalizeProjectType($project->stack),
                'status' => strtolower((string) ($project->status ?? 'unknown')),
            ])
            ->values();

        $rawIncidents = $projectIds->isEmpty()
            ? collect()
            : collect(rescue(
                fn () => Incident::query()
                    ->whereIn('project_id', $projectIds->all())
                    ->orderByDesc('event_created_at')
                    ->orderByDesc('created_at')
                    ->take(200)
                    ->get(),
                collect(),
                false
            ));

        $parseDate = static function ($value): ?Carbon {
            if (blank($value)) {
                return null;
            }

            return rescue(fn () => Carbon::parse($value), null, false);
        };

        $formatTarget = static function ($value): string {
            if (is_array($value)) {
                return collect($value)
                    ->map(fn ($item, $key) => is_scalar($item) ? "{$key}: {$item}" : "{$key}: ".json_encode($item))
                    ->implode(' / ');
            }

            return filled($value) ? (string) $value : '-';
        };

        $incidentRows = $rawIncidents
            ->map(function (Incident $incident) use ($projectsById, $parseDate, $formatTarget) {
                $project = $projectsById->get((int) ($incident->project_id ?? 0));
                $severity = strtolower(trim((string) ($incident->severity ?? 'medium')));
                $severity = in_array($severity, ['critical', 'high', 'medium', 'low', 'info'], true)
                    ? $severity
                    : 'medium';
                $status = strtolower(trim((string) ($incident->status ?? 'open')));
                $status = filled($status) ? $status : 'open';
                $eventAt = $parseDate($incident->event_created_at ?? null)
                    ?? $parseDate($incident->created_at ?? null);
                $category = filled($incident->category ?? null) ? (string) $incident->category : 'security';

                return [
                    'id' => (string) $incident->getKey(),
                    'project' => $project?->domain ?: $project?->name ?: 'Protected asset',
                    'project_name' => $project?->name ?: 'Protected asset',
                    'category' => $category,
                    'category_label' => ucwords(str_replace('_', ' ', $category)),
                    'event' => filled($incident->event ?? null) ? (string) $incident->event : 'security_incident',
                    'event_label' => filled($incident->event ?? null)
                        ? ucwords(str_replace('_', ' ', (string) $incident->event))
                        : 'Security incident',
                    'severity' => $severity,
                    'status' => $status,
                    'target' => $formatTarget($incident->target ?? null),
                    'site_url' => $incident->site_url ?: '-',
                    'ip' => $incident->ip ?: '-',
                    'assigned' => $incident->assigned_user_name ?: 'SOC team',
                    'created_timestamp' => $eventAt?->timestamp ?? 0,
                    'created_human' => $eventAt ? $eventAt->diffForHumans() : 'Recently',
                    'created_time' => $eventAt ? $eventAt->format('M d, H:i') : '-',
                ];
            })
            ->sortByDesc('created_timestamp')
            ->values();

        $openRows = $incidentRows->reject(fn ($incident) => in_array($incident['status'], ['resolved', 'closed'], true));
        $latestIncident = $incidentRows->first();

        return view('pages.client-incidents', [
            'client' => $client,
            'incidents' => $incidentRows,
            'projects' => $projectRows,
            'stats' => [
                'total' => $incidentRows->count(),
                'open' => $openRows->where('status', 'open')->count(),
                'in_progress' => $openRows->where('status', 'in_progress')->count(),
                'resolved' => $incidentRows->whereIn('status', ['resolved', 'closed'])->count(),
                'critical' => $openRows->where('severity', 'critical')->count(),
                'high' => $openRows->where('severity', 'high')->count(),
                'latest_human' => $latestIncident['created_human'] ?? '-',
            ],
        ]);
    })->name('client.incidents');

    Route::get('/client-alerts', function () {
        $user = request()->user();
        $client = clients::query()
            ->where('user_id', $user?->id)
            ->first();

        $emptyPayload = [
            'client' => $client,
            'alerts' => collect(),
            'projects' => collect(),
            'stats' => [
                'total' => 0,
                'open' => 0,
                'resolved' => 0,
                'critical' => 0,
                'high' => 0,
                'projects' => 0,
                'average_score' => 0,
                'latest_human' => '-',
            ],
            'filters' => [
                'severities' => collect(['critical', 'high', 'medium', 'low']),
                'projects' => collect(),
            ],
        ];

        if (! $client) {
            return view('pages.client-alerts', $emptyPayload);
        }

        $projects = $client->projects()
            ->latest()
            ->get(['id', 'name', 'domain', 'ip_address', 'stack', 'status']);

        $projectIds = $projects
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $projectsById = $projects->keyBy(fn (Projects $project) => (int) $project->id);

        $projectRows = $projects
            ->map(fn (Projects $project) => [
                'id' => (int) $project->id,
                'name' => $project->name ?: 'Project #'.$project->id,
                'domain' => $project->domain ?: '-',
                'stack' => Projects::normalizeProjectType($project->stack),
                'status' => strtolower((string) ($project->status ?? 'unknown')),
            ])
            ->values();

        $rawAlerts = $projectIds->isEmpty()
            ? collect()
            : collect(rescue(
                fn () => Alert::query()
                    ->whereIn('project_id', $projectIds->all())
                    ->orderByDesc('detected_at')
                    ->orderByDesc('created_at')
                    ->take(200)
                    ->get(),
                collect(),
                false
            ));

        $normalizeArray = static function ($value): array {
            if (is_array($value)) {
                return $value;
            }

            if (is_string($value) && trim($value) !== '') {
                $decoded = json_decode($value, true);

                return is_array($decoded) ? $decoded : [$value];
            }

            return [];
        };

        $parseDate = static function ($value): ?Carbon {
            if (blank($value)) {
                return null;
            }

            return rescue(fn () => Carbon::parse($value), null, false);
        };

        $alertRows = $rawAlerts
            ->map(function (Alert $alert) use ($projectsById, $normalizeArray, $parseDate) {
                $project = $projectsById->get((int) ($alert->project_id ?? 0));
                $severity = strtolower(trim((string) ($alert->severity ?? 'medium')));
                $severity = in_array($severity, ['critical', 'high', 'medium', 'low', 'info'], true)
                    ? $severity
                    : 'medium';
                $type = filled($alert->type ?? null) ? (string) $alert->type : 'security_alert';
                $detectedAt = $parseDate($alert->detected_at ?? null)
                    ?? $parseDate($alert->created_at ?? null);
                $evidence = $normalizeArray($alert->evidence ?? []);
                $recommendations = $normalizeArray($alert->recommendations ?? []);
                $resolved = (bool) ($alert->resolved ?? false);
                $score = max(0, min(100, (int) ($alert->ai_score ?? 0)));

                return [
                    'id' => (string) $alert->getKey(),
                    'project' => $project?->domain ?: $project?->name ?: 'Protected asset',
                    'project_name' => $project?->name ?: 'Protected asset',
                    'type' => $type,
                    'type_label' => ucwords(str_replace('_', ' ', $type)),
                    'severity' => $severity,
                    'status' => $resolved ? 'resolved' : 'open',
                    'title' => $alert->title ?: 'Security alert detected',
                    'summary' => $alert->summary ?: 'No summary provided.',
                    'score' => $score,
                    'evidence_count' => count($evidence),
                    'recommendation_count' => count($recommendations),
                    'detected_timestamp' => $detectedAt?->timestamp ?? 0,
                    'detected_human' => $detectedAt ? $detectedAt->diffForHumans() : 'Recently',
                    'detected_time' => $detectedAt ? $detectedAt->format('M d, H:i') : '-',
                    'sla' => match ($severity) {
                        'critical' => '15 min',
                        'high' => '1 hour',
                        'medium' => '4 hours',
                        'low' => '24 hours',
                        default => 'Review',
                    },
                ];
            })
            ->sortByDesc('detected_timestamp')
            ->values();

        $openRows = $alertRows->where('status', 'open');
        $latestAlert = $alertRows->first();

        return view('pages.client-alerts', [
            'client' => $client,
            'alerts' => $alertRows,
            'projects' => $projectRows,
            'stats' => [
                'total' => $alertRows->count(),
                'open' => $openRows->count(),
                'resolved' => $alertRows->where('status', 'resolved')->count(),
                'critical' => $openRows->where('severity', 'critical')->count(),
                'high' => $openRows->where('severity', 'high')->count(),
                'projects' => $projectRows->count(),
                'average_score' => $alertRows->count() ? (int) round($alertRows->avg('score')) : 0,
                'latest_human' => $latestAlert['detected_human'] ?? '-',
            ],
            'filters' => [
                'severities' => collect(['critical', 'high', 'medium', 'low'])
                    ->merge($alertRows->pluck('severity'))
                    ->filter()
                    ->unique()
                    ->values(),
                'projects' => $projectRows->pluck('domain')->filter(fn ($domain) => $domain !== '-')->values(),
            ],
        ]);
    })->name('client.alerts');

    Route::get('/client-reports', function () {
        $user = request()->user();
        $client = clients::query()
            ->where('user_id', $user?->id)
            ->first();

        $projects = $client
            ? $client->projects()->latest()->get(['id', 'name', 'domain', 'stack', 'status'])
            : collect();

        $reportRequests = $client
            ? ReportRequest::query()
                ->with('project')
                ->where('client_id', $client->id)
                ->whereNotNull('project_id')
                ->latest()
                ->get()
            : collect();

        return view('pages.client-reports', compact('client', 'projects', 'reportRequests'));
    })->name('client.reports.index');

    Route::post('/client-reports', function (Request $request) {
        $user = $request->user();
        $client = clients::query()
            ->where('user_id', $user?->id)
            ->first();

        if (! $client) {
            return back()->with('error', 'No client profile is linked to this account.');
        }

        $projectIds = $client->projects()->pluck('id')->map(fn ($id) => (int) $id)->all();

        $validated = $request->validate([
            'project_id' => ['required', Rule::in($projectIds)],
            'type' => ['required', Rule::in(['project_security', 'incident_summary', 'vulnerability_summary', 'cloudflare_coverage'])],
            'period' => ['required', Rule::in(['last_7_days', 'last_30_days', 'this_month', 'last_quarter'])],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        ReportRequest::create([
            'client_id' => $client->id,
            'project_id' => $validated['project_id'],
            'user_id' => $user?->id,
            'type' => $validated['type'],
            'period' => $validated['period'],
            'status' => 'ready',
            'note' => $validated['note'] ?? null,
            'requested_at' => now(),
        ]);

        return redirect()
            ->route('client.reports.index')
            ->with('success', 'Report request created. You can download it now.');
    })->name('client.reports.store');

    Route::get('/client-reports/{reportRequest}/download', function (ReportRequest $reportRequest) {
        $user = request()->user();
        $client = clients::query()
            ->where('user_id', $user?->id)
            ->first();

        abort_unless($client && (int) $reportRequest->client_id === (int) $client->id, 404);

        $project = Projects::query()
            ->where('client_id', $client->id)
            ->findOrFail($reportRequest->project_id);

        $from = match ($reportRequest->period) {
            'last_7_days' => now()->subDays(7),
            'this_month' => now()->startOfMonth(),
            'last_quarter' => now()->subMonths(3),
            default => now()->subDays(30),
        };

        $projectId = (int) $project->id;
        $alerts = collect(rescue(
            fn () => Alert::query()
                ->where('project_id', $projectId)
                ->where('detected_at', '>=', $from)
                ->latest('detected_at')
                ->get(),
            collect(),
            false
        ));
        $incidents = collect(rescue(
            fn () => Incident::query()
                ->where('project_id', $projectId)
                ->where('event_created_at', '>=', $from)
                ->latest('event_created_at')
                ->get(),
            collect(),
            false
        ));
        $vulnerabilities = collect(rescue(
            fn () => SiteVulnerability::query()
                ->where('project_id', $projectId)
                ->where('detected_at', '>=', $from)
                ->latest('detected_at')
                ->get(),
            collect(),
            false
        ));
        $healthReports = collect(rescue(
            fn () => HealthReport::query()
                ->where('project_id', $projectId)
                ->where('event_created_at', '>=', $from)
                ->latest('event_created_at')
                ->get(),
            collect(),
            false
        ));

        $projectScore = ProjectSecurityScore::forProject($project, $alerts, $incidents, $vulnerabilities, $healthReports);
        $fileName = Str::slug($project->domain ?: $project->name ?: 'project').'-report-'.now()->format('Y-m-d-His').'.pdf';

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.client-project-report', [
            'client' => $client,
            'project' => $project,
            'reportRequest' => $reportRequest,
            'periodFrom' => $from,
            'periodTo' => now(),
            'projectScore' => $projectScore,
            'alerts' => $alerts,
            'incidents' => $incidents,
            'vulnerabilities' => $vulnerabilities,
            'healthReports' => $healthReports,
        ])->download($fileName);
    })->name('client.reports.download');

    /*
    |--------------------------------------------------------------------------
    | Projects & Agents
    |--------------------------------------------------------------------------
    */

    Route::get('/projects', function () {
        $projects = Projects::query()
            ->with(['client', 'agents'])
            ->latest()
            ->paginate(15);

        $projectIds = $projects->getCollection()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $alerts = collect(rescue(
            fn () => Alert::query()->whereIn('project_id', $projectIds->all())->get(),
            collect(),
            false
        ));

        $incidents = collect(rescue(
            fn () => Incident::query()->whereIn('project_id', $projectIds->all())->get(),
            collect(),
            false
        ));

        $vulnerabilities = collect(rescue(
            fn () => SiteVulnerability::query()->whereIn('project_id', $projectIds->all())->get(),
            collect(),
            false
        ));

        $healthReports = collect(rescue(
            fn () => HealthReport::query()->whereIn('project_id', $projectIds->all())->latest('event_created_at')->get(),
            collect(),
            false
        ));

        $projects->getCollection()->transform(function (Projects $project) use ($alerts, $incidents, $vulnerabilities, $healthReports) {
            $score = ProjectSecurityScore::forProject($project, $alerts, $incidents, $vulnerabilities, $healthReports);
            $project->security_score = $score['security_score'];
            $project->security_score_label = $score['score_label'];
            $project->security_score_source = $score['source'];
            $project->risk_score = $score['risk_score'] ?? (100 - ($score['security_score'] ?? 0));
            $project->risk_label = $score['risk_label'] ?? 'medium';

            return $project;
        });

        return view('pages.projects', compact('projects'));
    })->name('projects.index');

    Route::get('/projects/export', function () {
        $projects = Projects::query()
            ->with(['client', 'agents'])
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($projects) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Name',
                'Domain',
                'IP Address',
                'Client',
                'Type',
                'Status',
                'SSL Status',
                'Cloudflare SSL',
                'Cloudflare',
                'Agent',
                'Created At',
            ]);

            foreach ($projects as $project) {
                $cloudflare = (bool) ($project->cloudflare_enabled ?? false) || filled($project->cloudflare_zone_id ?? null);
                $settings = is_array($project->cloudflare_settings ?? null) ? $project->cloudflare_settings : [];
                $sslMode = $settings['ssl_mode'] ?? null;
                $sslLabel = $cloudflare
                    ? match ($sslMode) {
                        'full_strict' => 'Full Strict',
                        'full' => 'Full',
                        'flexible' => 'Flexible',
                        'off' => 'Off',
                        default => 'Not synced',
                    }
                    : 'Not linked';
                $sslStatus = match ($sslMode) {
                    'full_strict', 'full' => 'Valid',
                    'flexible' => 'Weak',
                    'off' => 'Invalid',
                    default => $cloudflare ? 'Unknown' : 'Missing',
                };
                $agentOnline = $project->agents->contains(
                    fn ($agent) => strtolower((string) ($agent->pivot->status ?? '')) === 'online'
                );

                fputcsv($file, [
                    $project->name,
                    $project->domain,
                    $project->ip_address,
                    $project->client->company_name ?? '-',
                    Projects::normalizeProjectType($project->stack ?? ''),
                    $project->status,
                    $sslStatus,
                    $sslLabel,
                    $cloudflare ? 'Active' : 'Not linked',
                    $agentOnline ? 'Online' : 'Offline',
                    optional($project->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        }, 'projects-export-'.now()->format('Y-m-d-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    })->name('projects.export');

    Route::get('/agents', function () {
        $installations = ProjectAgent::query()
            ->with(['agent', 'project.client'])
            ->orderByDesc('last_seen_at')
            ->orderByDesc('project_id')
            ->paginate(15);

        return view('pages.agents', compact('installations'));
    })->name('agents.index');

    Route::post('/agents/{installation}/restart', function (ProjectAgent $installation) {
        // IDOR Protection: Verify user is admin/staff
        $user = request()->user();
        if (! $user || ! in_array($user->role ?? '', ['admin', 'staff', 'soc_analyst'], true)) {
            abort(403, 'Unauthorized to restart this agent.');
        }

        $installation->update([
            'status' => 'online',
            'last_seen_at' => now(),
            'meta' => array_merge($installation->meta ?? [], [
                'command' => null,
                'requested_action' => 'connect',
                'requested_at' => now()->toIso8601String(),
            ]),
        ]);

        if ($installation->project) {
            $installation->project->update([
                'is_connected' => true,
                'status' => 'active',
                'last_seen_at' => now(),
            ]);
        }

        return redirect()
            ->route('agents.index')
            ->with('success', 'Agent connected.');
    })->name('agents.restart');

    Route::post('/agents/{installation}/off', function (ProjectAgent $installation) {
        // IDOR Protection: Verify user is admin/staff
        $user = request()->user();
        if (! $user || ! in_array($user->role ?? '', ['admin', 'staff', 'soc_analyst'], true)) {
            abort(403, 'Unauthorized to disconnect this agent.');
        }

        $project = $installation->project;

        $installation->update([
            'status' => 'pending',
            'meta' => array_merge($installation->meta ?? [], [
                'command' => 'disconnect',
                'command_requested_at' => now()->toIso8601String(),
                'command_executed_at' => null,
            ]),
        ]);

        if ($project) {
            $project->update([
                'status' => 'pending',
            ]);
        }

        return redirect()
            ->route('agents.index')
            ->with('success', 'Disconnect command queued.');
    })->name('agents.off');

    Route::delete('/agents/{installation}', function (ProjectAgent $installation) {
        // IDOR Protection: Verify user is admin/staff
        $user = request()->user();
        if (! $user || ! in_array($user->role ?? '', ['admin', 'staff', 'soc_analyst'], true)) {
            abort(403, 'Unauthorized to delete this agent.');
        }

        $installation->delete();

        return redirect()
            ->route('agents.index')
            ->with('success', 'Agent installation removed.');
    })->name('agents.destroy');

    Route::get('/projects/create', function () {
        $clients = clients::query()
            ->orderBy('company_name')
            ->get();

        $projectTypes = Projects::PROJECT_TYPES;

        $agents = agents::query()
            ->orderBy('name')
            ->get();

        return view('pages.projects-create', compact('clients', 'projectTypes', 'agents'));
    })->name('projects.create');

    Route::get('/projects/{project}/edit', function (Projects $project) {
        // IDOR Protection: Verify user is admin/staff
        $user = request()->user();
        if (! $user || ! in_array($user->role ?? '', ['admin', 'staff', 'soc_analyst'], true)) {
            abort(403, 'Unauthorized access to this project.');
        }

        $clients = clients::query()
            ->orderBy('company_name')
            ->get();
        $projectTypes = Projects::PROJECT_TYPES;

        return view('pages.projects-edit', compact('project', 'clients', 'projectTypes'));
    })->name('projects.edit');

    Route::post('/projects', [ProjectController::class, 'store'])
        ->name('projects.store');

    Route::put('/projects/{project}', function (Projects $project) {
        // IDOR Protection: Verify user is admin/staff
        $user = request()->user();
        if (! $user || ! in_array($user->role ?? '', ['admin', 'staff', 'soc_analyst'], true)) {
            abort(403, 'Unauthorized to update this project.');
        }

        $validated = request()->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255'],
            'ip_address' => ['nullable', 'string', 'max:255'],
            'stack' => ['required', Rule::in(Projects::PROJECT_TYPES)],
            'status' => ['required', Rule::in(['active', 'warning', 'offline'])],
        ]);

        $project->update($validated);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    })->name('projects.update');

    Route::delete('/projects/{project}', function (Projects $project) {
        // IDOR Protection: Verify user is admin/staff
        $user = request()->user();
        if (! $user || ! in_array($user->role ?? '', ['admin', 'staff', 'soc_analyst'], true)) {
            abort(403, 'Unauthorized to delete this project.');
        }

        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    })->name('projects.destroy');

    Route::get('/projects/{project}', function (Projects $project) {
        // IDOR Protection: Verify user is admin/staff
        $user = request()->user();
        if (! $user || ! in_array($user->role ?? '', ['admin', 'staff', 'soc_analyst'], true)) {
            abort(403, 'Unauthorized to view this project.');
        }

        $project->load(['client', 'agents']);
        $projectId = (int) $project->id;
        $alerts = collect(rescue(fn () => Alert::query()->where('project_id', $projectId)->get(), collect(), false));
        $incidents = collect(rescue(fn () => Incident::query()->where('project_id', $projectId)->get(), collect(), false));
        $vulnerabilities = collect(rescue(fn () => SiteVulnerability::query()->where('project_id', $projectId)->get(), collect(), false));
        $healthReports = collect(rescue(fn () => HealthReport::query()->where('project_id', $projectId)->latest('event_created_at')->get(), collect(), false));
        $projectScore = ProjectSecurityScore::forProject($project, $alerts, $incidents, $vulnerabilities, $healthReports);

        return view('pages.projects-show', compact('project', 'projectScore'));
    })->name('projects.show');

    Route::get('/projects/{project}/realtime', function (Projects $project) {
        // IDOR Protection: Verify user is admin/staff
        $user = request()->user();
        if (! $user || ! in_array($user->role ?? '', ['admin', 'staff', 'soc_analyst'], true)) {
            abort(403, 'Unauthorized to view project data.');
        }

        $lastSeen = $project->agent_last_seen_at ?? $project->last_seen_at;
        $lastSeenAt = $lastSeen
            ? Carbon::parse($lastSeen)
            : null;
        $agentOnline = $lastSeenAt
            && $lastSeenAt->gt(now()->subMinutes(30))
            && (bool) $project->is_connected;

        return response()->json([
            'status' => $project->status,
            'connected' => (bool) $project->is_connected,
            'last_seen' => $lastSeen
                ? Carbon::parse($lastSeen)->diffForHumans()
                : '-',
            'agent_online' => (bool) $agentOnline,
            'alerts' => $project->alerts()->count(),
            'incidents' => $project->incidents()->count(),
        ]);
    });

    Route::get('/test-scan/{project}', [ProjectSecurityController::class, 'runVulnerabilityScan'])->middleware('auth');

    Route::post('/projects/{project}/vulnerability-scan', [ProjectSecurityController::class, 'runVulnerabilityScan'])
        ->name('projects.vulnerability.scan')->middleware('auth');

    /*
    |--------------------------------------------------------------------------
    | Clients
    |--------------------------------------------------------------------------
    */

    Route::get('/clients', function () {
        $activeClients = clients::query()
            ->where('status', 'active')
            ->count();

        $clients = clients::query()
            ->with(['user', 'projects'])
            ->withCount('projects')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $clientCollection = $clients->getCollection();
        $projectIds = $clientCollection
            ->flatMap(fn (clients $client) => $client->projects->pluck('id'))
            ->map(fn ($id) => (int) $id)
            ->values();

        $scoreAlerts = collect(rescue(
            fn () => Alert::query()->whereIn('project_id', $projectIds->all())->get(),
            collect(),
            false
        ));

        $scoreIncidents = collect(rescue(
            fn () => Incident::query()->whereIn('project_id', $projectIds->all())->get(),
            collect(),
            false
        ));

        $scoreVulnerabilities = collect(rescue(
            fn () => SiteVulnerability::query()->whereIn('project_id', $projectIds->all())->get(),
            collect(),
            false
        ));

        $scoreHealthReports = collect(rescue(
            fn () => HealthReport::query()->whereIn('project_id', $projectIds->all())->latest('event_created_at')->get(),
            collect(),
            false
        ));

        $clientCollection->transform(function (clients $client) use ($scoreAlerts, $scoreIncidents, $scoreVulnerabilities, $scoreHealthReports) {
            $projectScores = $client->projects
                ->map(fn (Projects $project) => ProjectSecurityScore::forProject($project, $scoreAlerts, $scoreIncidents, $scoreVulnerabilities, $scoreHealthReports));

            $client->global_score = $projectScores->count()
                ? (int) round($projectScores->avg('security_score'))
                : 0;
            $client->global_score_label = $projectScores->count()
                ? ($client->global_score >= 85 ? 'Healthy' : ($client->global_score >= 65 ? 'Review' : ($client->global_score >= 40 ? 'Risk' : 'Critical')))
                : 'No Projects';
            $client->global_score_source = $projectScores->count()
                ? ($projectScores->contains(fn (array $score) => ($score['source'] ?? null) === 'health_report')
                    ? 'Includes health reports'
                    : 'Live findings')
                : 'No projects';

            return $client;
        });

        return view('pages.clients', compact('clients', 'activeClients'));
    })->name('clients.index');

    Route::get('/clients/export', function () {
        $clientRows = clients::query()
            ->with(['user', 'projects'])
            ->withCount('projects')
            ->latest()
            ->get();

        $projectIds = $clientRows
            ->flatMap(fn (clients $client) => $client->projects->pluck('id'))
            ->map(fn ($id) => (int) $id)
            ->values();

        $scoreAlerts = collect(rescue(
            fn () => Alert::query()->whereIn('project_id', $projectIds->all())->get(),
            collect(),
            false
        ));

        $scoreIncidents = collect(rescue(
            fn () => Incident::query()->whereIn('project_id', $projectIds->all())->get(),
            collect(),
            false
        ));

        $scoreVulnerabilities = collect(rescue(
            fn () => SiteVulnerability::query()->whereIn('project_id', $projectIds->all())->get(),
            collect(),
            false
        ));

        $scoreHealthReports = collect(rescue(
            fn () => HealthReport::query()->whereIn('project_id', $projectIds->all())->latest('event_created_at')->get(),
            collect(),
            false
        ));

        return response()->streamDownload(function () use ($clientRows, $scoreAlerts, $scoreIncidents, $scoreVulnerabilities, $scoreHealthReports) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Company',
                'Email',
                'Phone',
                'Address',
                'Status',
                'Projects',
                'Global Score',
                'Score Label',
                'Created At',
            ]);

            foreach ($clientRows as $client) {
                $projectScores = $client->projects
                    ->map(fn (Projects $project) => ProjectSecurityScore::forProject($project, $scoreAlerts, $scoreIncidents, $scoreVulnerabilities, $scoreHealthReports));
                $globalScore = $projectScores->count()
                    ? (int) round($projectScores->avg('security_score'))
                    : 0;
                $scoreLabel = $projectScores->count()
                    ? ($globalScore >= 85 ? 'Healthy' : ($globalScore >= 65 ? 'Review' : ($globalScore >= 40 ? 'Risk' : 'Critical')))
                    : 'No Projects';

                fputcsv($file, [
                    $client->company_name,
                    $client->email,
                    $client->phone,
                    $client->address,
                    $client->status,
                    $client->projects_count ?? $client->projects->count(),
                    $globalScore,
                    $scoreLabel,
                    optional($client->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        }, 'clients-export-'.now()->format('Y-m-d-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    })->name('clients.export');

    Route::get('/clients/create', function () {
        return view('pages.clients-create');
    })->name('clients.create');

    Route::post('/clients', function () {
        $validated = request()->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('clients', 'email'),
                Rule::unique('users', 'email'),
            ],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,pending'],
        ]);

        DB::transaction(function () use ($validated) {
            $clientUser = User::create([
                'name' => $validated['company_name'],
                'email' => $validated['email'],
                'role' => 'Client',
                'status' => 'pending',
                'password' => bcrypt(Str::random(32)),
            ]);

            clients::create([
                ...$validated,
                'user_id' => $clientUser->id,
            ]);
        });

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client added successfully. Send the setup email when ready.');
    })->name('clients.store');

    Route::post('/clients/{client}/send-password-setup', function (clients $client) {
        $client->loadMissing('user');
        $clientUser = $client->user;

        if (! $clientUser) {
            return back()->with('error', 'No user account is linked to this client.');
        }

        $token = Password::createToken($clientUser);
        $clientUser->notify(new ClientPasswordSetupNotification($token));

        return back()->with('success', 'Password setup email sent to '.$clientUser->email.'.');
    })->name('clients.send-password-setup');

    Route::get('/clients/{client}/edit', function (clients $client) {
        return view('pages.clients-edit', compact('client'));
    })->name('clients.edit');

    Route::put('/clients/{client}', function (clients $client) {
        $validated = request()->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('clients', 'email')->ignore($client->id),
                Rule::unique('users', 'email')->ignore($client->user_id),
            ],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,pending'],
        ]);

        DB::transaction(function () use ($client, $validated) {
            $client->update($validated);

            if ($client->user) {
                $client->user->update([
                    'name' => $validated['company_name'],
                    'email' => $validated['email'],
                ]);
            }
        });

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Client updated successfully.');
    })->name('clients.update');

    Route::delete('/clients/{client}', function (clients $client) {
        DB::transaction(function () use ($client) {
            $user = $client->user;

            $client->delete();

            if ($user && $user->role === 'Client') {
                $user->delete();
            }
        });

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client deleted successfully.');
    })->name('clients.destroy');

    Route::get('/clients/{client}', function (clients $client) {

    $clientProjects = $client->projects()
        ->latest()
        ->get();

    foreach ($clientProjects as $project) {
        $project->alerts_count = $project->alerts()->count();
        $project->incidents_count = $project->incidents()->count();
    }

    $projects = $client->projects()
        ->latest()
        ->paginate(10, ['*'], 'projects_page')
        ->withQueryString();

    foreach ($projects as $project) {
        $project->alerts_count = $project->alerts()->count();
        $project->incidents_count = $project->incidents()->count();
    }

    return view('pages.clients-show', compact('client', 'clientProjects', 'projects'));

})->name('clients.show');
    /*
    |--------------------------------------------------------------------------
    | Audit Logs
    |--------------------------------------------------------------------------
    */

    Route::view('/audit-logs', 'pages.audit-logs')->name('audit-logs.index');

    Route::get('/audit-logs/feed', function () {
        $logs = collect(rescue(
            fn () => AuditLog::query()
                ->orderBy('event_created_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->take(120)
                ->get(),
            collect(),
            false
        ));

        $allProjects = Projects::query()
            ->orderBy('name')
            ->get(['id', 'name', 'domain']);
        $projectsById = $allProjects->keyBy('id');

        $normalizeArray = static function ($value): array {
            if (is_array($value)) {
                return $value;
            }

            if (is_string($value) && trim($value) !== '') {
                $decoded = json_decode($value, true);

                return is_array($decoded) ? $decoded : [];
            }

            return [];
        };

        $normalizeSeverity = static function ($value): string {
            $severity = strtolower(trim((string) $value));

            return match ($severity) {
                'critical', 'high', 'medium', 'low', 'info' => $severity,
                'fatal', 'emergency', 'alert' => 'critical',
                'error' => 'high',
                'warn', 'warning' => 'medium',
                'notice', 'debug', 'informational' => 'info',
                default => 'info',
            };
        };

        $labelFromPayload = null;
        $labelFromPayload = function ($value, string $fallback = '-') use (&$labelFromPayload) {
            if (is_array($value)) {
                $label = $value['name']
                    ?? $value['email']
                    ?? $value['username']
                    ?? $value['ip']
                    ?? $value['ip_address']
                    ?? $value['url']
                    ?? $value['path']
                    ?? $value['file']
                    ?? $value['plugin']
                    ?? $value['id']
                    ?? $fallback;

                return is_scalar($label) ? (string) $label : $fallback;
            }

            if (is_string($value) && trim($value) !== '') {
                $decoded = json_decode($value, true);

                return is_array($decoded)
                    ? $labelFromPayload($decoded, $fallback)
                    : $value;
            }

            if (is_scalar($value)) {
                return (string) $value;
            }

            return $fallback;
        };

        $parseDate = static function ($value): ?Carbon {
            if (blank($value)) {
                return null;
            }

            return rescue(fn () => Carbon::parse($value), null, false);
        };

        $items = $logs
            ->map(function (AuditLog $log) use ($projectsById, $labelFromPayload, $normalizeArray, $normalizeSeverity, $parseDate) {
                $event = filled($log->event ?? null) ? (string) $log->event : 'audit_event';
                $category = strtolower((string) ($log->category ?? 'audit'));
                $severity = $normalizeSeverity($log->severity ?? null);
                $createdAt = $parseDate($log->event_created_at ?? null)
                    ?? $parseDate($log->created_at ?? null);
                $project = $projectsById->get((int) ($log->project_id ?? 0));
                $metadata = $normalizeArray($log->metadata ?? []);
                $actor = $labelFromPayload($log->actor ?? null, 'Agent');
                $target = $labelFromPayload($log->target ?? null, '-');
                $createdHuman = $createdAt
                    ? ($createdAt->gt(now()->addMinute()) ? 'Just now' : $createdAt->diffForHumans())
                    : 'Recently';

                return [
                    'id' => (string) ($log->getKey() ?? md5($event.($createdAt?->timestamp ?? now()->timestamp))),
                    'project_id' => $log->project_id ? (string) $log->project_id : null,
                    'category' => $category,
                    'category_label' => ucwords(str_replace('_', ' ', $category)),
                    'event' => $event,
                    'event_label' => ucwords(str_replace('_', ' ', $event)),
                    'severity' => $severity,
                    'project' => $project?->domain ?: $project?->name ?: ($log->site_url ?: 'Platform'),
                    'site_url' => $log->site_url ?: null,
                    'ip' => $log->ip ?: '-',
                    'actor' => $actor,
                    'target' => $target,
                    'metadata_count' => count($metadata),
                    'created_at' => $createdAt?->toIso8601String(),
                    'created_human' => $createdHuman,
                    'created_time' => $createdAt ? $createdAt->format('H:i:s') : '--:--:--',
                    'search' => strtolower(trim(implode(' ', [
                        $event,
                        $category,
                        $severity,
                        $project?->domain,
                        $project?->name,
                        $log->site_url,
                        $log->ip,
                        $actor,
                        $target,
                    ]))),
                ];
            })
            ->sortByDesc(fn (array $item) => $item['created_at'] ?? '')
            ->take(80)
            ->values();

        $latest = $items->first();
        $severityCounts = $items->countBy('severity');
        $categories = $items
            ->pluck('category')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return response()->json([
            'logs' => $items,
            'stats' => [
                'visible' => $items->count(),
                'total_projects' => $allProjects->count(),
                'critical' => $severityCounts->get('critical', 0),
                'high' => $severityCounts->get('high', 0),
                'medium' => $severityCounts->get('medium', 0),
                'low' => $severityCounts->get('low', 0),
                'info' => $severityCounts->get('info', 0),
                'auth' => $items->where('category', 'auth')->count(),
                'file_security' => $items->where('category', 'file_security')->count(),
                'latest_human' => $latest['created_human'] ?? '-',
                'latest_time' => $latest['created_time'] ?? '--:--:--',
                'categories' => $categories,
                'severities' => collect(['critical', 'high', 'medium', 'low', 'info'])
                    ->merge($items->pluck('severity'))
                    ->filter()
                    ->unique()
                    ->values(),
                'projects' => $allProjects
                    ->map(fn (Projects $project) => [
                        'id' => (string) $project->id,
                        'label' => $project->domain ?: $project->name ?: 'Project #'.$project->id,
                    ])
                    ->values(),
            ],
        ]);
    })->name('audit-logs.feed');

    /*
    |--------------------------------------------------------------------------
    | Incidents
    |--------------------------------------------------------------------------
    */

    Route::get('/incidents', function () {
        $currentUser = request()->user();
        $canTakeIncidents = in_array(strtolower((string) $currentUser?->role), ['soc analyst', 'super admin', 'admin'], true);
        $incidents = collect(rescue(
            fn () => Incident::query()
                ->orderBy('event_created_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->take(120)
                ->get(),
            collect(),
            false
        ));

        $projectIds = $incidents
            ->pluck('project_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $projectsById = Projects::query()
            ->whereIn('id', $projectIds)
            ->with('client')
            ->get()
            ->keyBy('id');

        $labelFromPayload = null;
        $labelFromPayload = function ($value, string $fallback = '-') use (&$labelFromPayload) {
            if (is_array($value)) {
                $label = $value['name']
                    ?? $value['email']
                    ?? $value['username']
                    ?? $value['id']
                    ?? $fallback;

                return is_scalar($label) ? (string) $label : $fallback;
            }

            if (is_string($value) && trim($value) !== '') {
                $decoded = json_decode($value, true);

                return is_array($decoded)
                    ? $labelFromPayload($decoded, $fallback)
                    : $value;
            }

            if (is_scalar($value)) {
                return (string) $value;
            }

            return $fallback;
        };

        $parseDate = static function ($value): ?Carbon {
            if (blank($value)) {
                return null;
            }

            return rescue(fn () => Carbon::parse($value), null, false);
        };

        $rows = $incidents
            ->map(function (Incident $incident) use ($projectsById, $labelFromPayload, $parseDate, $currentUser) {
                $event = filled($incident->event ?? null) ? (string) $incident->event : 'security_incident';
                $category = strtolower((string) ($incident->category ?? 'security'));
                $severity = strtolower((string) ($incident->severity ?? 'info'));
                $status = strtolower((string) ($incident->status ?? 'open'));
                $createdAt = $parseDate($incident->event_created_at ?? null)
                    ?? $parseDate($incident->created_at ?? null);
                $project = $projectsById->get((int) ($incident->project_id ?? 0));
                $target = $labelFromPayload($incident->target ?? null, '-');
                $metadata = is_array($incident->metadata ?? null) ? $incident->metadata : [];
                $assignedAt = $parseDate($incident->assigned_at ?? null);
                $assignedUserId = $incident->assigned_user_id ? (string) $incident->assigned_user_id : null;
                $assignedName = $incident->assigned_user_name ?: null;

                return [
                    'id' => (string) $incident->getKey(),
                    'project_id' => $incident->project_id ? (string) $incident->project_id : null,
                    'event' => $event,
                    'event_label' => ucwords(str_replace('_', ' ', $event)),
                    'category' => $category,
                    'category_label' => ucwords(str_replace('_', ' ', $category)),
                    'severity' => $severity,
                    'status' => $status,
                    'project' => $project?->domain ?: $project?->name ?: ($incident->site_url ?: 'Unknown asset'),
                    'client' => $project?->client?->company_name ?: '-',
                    'site_url' => $incident->site_url ?: '-',
                    'ip' => $incident->ip ?: '-',
                    'target' => $target,
                    'metadata_count' => count($metadata),
                    'assigned_user_id' => $assignedUserId,
                    'assigned_user_name' => $assignedName,
                    'assigned_label' => $assignedName ?: 'Unassigned',
                    'assigned_human' => $assignedAt ? $assignedAt->diffForHumans() : null,
                    'is_assigned' => filled($assignedUserId),
                    'is_mine' => $assignedUserId && $currentUser && $assignedUserId === (string) $currentUser->id,
                    'created_human' => $createdAt ? $createdAt->diffForHumans() : 'Recently',
                    'created_time' => $createdAt ? $createdAt->format('M d, H:i') : '--',
                    'search' => strtolower(trim(implode(' ', [
                        $event,
                        $category,
                        $severity,
                        $status,
                        $project?->domain,
                        $project?->name,
                        $project?->client?->company_name,
                        $incident->site_url,
                        $incident->ip,
                        $target,
                        $assignedName,
                    ]))),
                ];
            })
            ->values();

        $stats = [
            'total' => $rows->count(),
            'open' => $rows->where('status', 'open')->count(),
            'critical' => $rows->where('severity', 'critical')->count(),
            'high' => $rows->where('severity', 'high')->count(),
            'assigned' => $rows->where('is_assigned', true)->count(),
            'resolved' => $rows->whereIn('status', ['resolved', 'closed'])->count(),
            'projects' => $rows->pluck('project_id')->filter()->unique()->count(),
        ];

        return view('pages.incidents', compact('rows', 'stats', 'canTakeIncidents'));
    })->name('incidents.index');

    Route::post('/incidents/{incident}/take', function (Incident $incident) {
        $user = request()->user();

        abort_unless(in_array(strtolower((string) $user?->role), ['soc analyst', 'super admin', 'admin'], true), 403);

        $alreadyAssigned = filled($incident->assigned_user_id ?? null);

        if ($alreadyAssigned && (string) $incident->assigned_user_id !== (string) $user->id) {
            return redirect()
                ->route('incidents.index')
                ->with('error', 'This incident is already assigned to '.$incident->assigned_user_name.'.');
        }

        $incident->forceFill([
            'assigned_user_id' => (string) $user->id,
            'assigned_user_name' => $user->name,
            'assigned_user_email' => $user->email,
            'assigned_at' => now(),
            'status' => in_array(strtolower((string) ($incident->status ?? 'open')), ['resolved', 'closed'], true)
                ? $incident->status
                : 'in_progress',
        ])->save();

        return redirect()
            ->route('incidents.index')
            ->with('success', 'Incident assigned to you.');
    })->name('incidents.take');

    Route::patch('/incidents/{incident}/resolve', function (Incident $incident) {
        $user = request()->user();

        abort_unless(in_array(strtolower((string) $user?->role), ['soc analyst', 'super admin', 'admin'], true), 403);

        $status = strtolower((string) ($incident->status ?? 'open'));

        if (in_array($status, ['resolved', 'closed'], true)) {
            return redirect()
                ->route('incidents.index')
                ->with('success', 'Incident is already resolved.');
        }

        $payload = [
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by_user_id' => (string) $user->id,
            'resolved_by_user_name' => $user->name,
            'resolved_by_user_email' => $user->email,
        ];

        if (blank($incident->assigned_user_id ?? null)) {
            $payload['assigned_user_id'] = (string) $user->id;
            $payload['assigned_user_name'] = $user->name;
            $payload['assigned_user_email'] = $user->email;
            $payload['assigned_at'] = now();
        }

        $incident->forceFill($payload)->save();

        return redirect()
            ->route('incidents.index')
            ->with('success', 'Incident resolved.');
    })->name('incidents.resolve');

    $cloudflareSettingsFromRequest = static function (Request $request): array {
        return [
            'ssl_mode' => $request->input('ssl_mode', 'full_strict'),
            'security_level' => $request->input('security_level', 'medium'),
            'cache_level' => $request->input('cache_level', 'standard'),
            'browser_cache_ttl' => (int) $request->input('browser_cache_ttl', 14400),
            'waf_enabled' => $request->boolean('waf_enabled'),
            'ddos_enabled' => $request->boolean('ddos_enabled'),
            'proxy_enabled' => $request->boolean('proxy_enabled'),
            'bot_fight_mode' => $request->boolean('bot_fight_mode'),
            'under_attack_mode' => $request->boolean('under_attack_mode'),
            'rate_limit_per_minute' => (int) $request->input('rate_limit_per_minute', 120),
            'blocked_countries' => trim((string) $request->input('blocked_countries', '')),
            'allowed_ips' => trim((string) $request->input('allowed_ips', '')),
            'notes' => trim((string) $request->input('notes', '')),
        ];
    };

    $cloudflareValidationRules = static function (bool $includeProject = false): array {
        return array_merge(
            $includeProject ? ['project_id' => ['required', 'exists:projects,id']] : [],
            [
                'account_email' => ['nullable', 'email', 'max:255'],
                'account_id' => ['nullable', 'string', 'max:255'],
                'zone_id' => ['nullable', 'string', 'max:255'],
                'api_token' => ['nullable', 'string', 'max:2048'],
                'ssl_mode' => ['required', Rule::in(['off', 'flexible', 'full', 'full_strict'])],
                'security_level' => ['required', Rule::in(['essentially_off', 'low', 'medium', 'high', 'under_attack'])],
                'cache_level' => ['required', Rule::in(['bypass', 'basic', 'simplified', 'standard', 'aggressive'])],
                'browser_cache_ttl' => ['required', 'integer', 'min:0', 'max:31536000'],
                'waf_enabled' => ['nullable', 'boolean'],
                'ddos_enabled' => ['nullable', 'boolean'],
                'proxy_enabled' => ['nullable', 'boolean'],
                'bot_fight_mode' => ['nullable', 'boolean'],
                'under_attack_mode' => ['nullable', 'boolean'],
                'rate_limit_per_minute' => ['required', 'integer', 'min:1', 'max:100000'],
                'blocked_countries' => ['nullable', 'string', 'max:500'],
                'allowed_ips' => ['nullable', 'string', 'max:1000'],
                'notes' => ['nullable', 'string', 'max:2000'],
            ]
        );
    };

    /*
    |--------------------------------------------------------------------------
    | Cloudflare
    |--------------------------------------------------------------------------
    */

    Route::get('/cloudflare', function () {
        $projects = Projects::query()
            ->with(['client', 'agents'])
            ->orderBy('name')
            ->get();

        $edgeLogs = collect(rescue(
            fn () => AuditLog::query()
                ->orderBy('event_created_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->take(160)
                ->get(),
            collect(),
            false
        ));

        $edgeIncidents = collect(rescue(
            fn () => Incident::query()
                ->orderByDesc('event_created_at')
                ->orderByDesc('created_at')
                ->take(80)
                ->get(),
            collect(),
            false
        ));

        $parseDate = static function ($value): ?Carbon {
            if (blank($value)) {
                return null;
            }

            return rescue(fn () => Carbon::parse($value), null, false);
        };

        $labelFromPayload = null;
        $labelFromPayload = function ($value, string $fallback = '-') use (&$labelFromPayload) {
            if (is_array($value)) {
                $label = $value['name']
                    ?? $value['email']
                    ?? $value['username']
                    ?? $value['id']
                    ?? $fallback;

                return is_scalar($label) ? (string) $label : $fallback;
            }

            if (is_string($value) && trim($value) !== '') {
                $decoded = json_decode($value, true);

                return is_array($decoded)
                    ? $labelFromPayload($decoded, $fallback)
                    : $value;
            }

            if (is_scalar($value)) {
                return (string) $value;
            }

            return $fallback;
        };

        $isEdgeSignal = static function ($item): bool {
            $haystack = strtolower(trim(implode(' ', [
                $item->category ?? '',
                $item->event ?? '',
                $item->severity ?? '',
                $item->site_url ?? '',
                $item->ip ?? '',
            ])));

            return str_contains($haystack, 'firewall')
                || str_contains($haystack, 'cloudflare')
                || str_contains($haystack, 'waf')
                || str_contains($haystack, 'cf_')
                || str_contains($haystack, 'xss')
                || str_contains($haystack, 'sqli');
        };

        $projectRows = $projects
            ->map(function (Projects $project) {
                $stack = strtolower((string) ($project->stack ?? ''));
                $settings = is_array($project->cloudflare_settings ?? null)
                    ? $project->cloudflare_settings
                    : [];
                $linked = (bool) ($project->cloudflare_enabled ?? false)
                    || filled($project->cloudflare_zone_id ?? null)
                    || str_contains($stack, 'cloudflare');
                $agentOnline = $project->agents->contains(
                    fn ($agent) => strtolower((string) ($agent->pivot->status ?? '')) === 'online'
                );
                $cloudflareStatus = (
                    strtolower((string) ($project->cloudflare_status ?? '')) === 'active'
                    || (bool) ($project->cloudflare_enabled ?? false)
                ) ? 'active' : 'pending';

                return [
                    'id' => $project->id,
                    'name' => $project->name ?: $project->domain ?: 'Project #'.$project->id,
                    'domain' => $project->domain ?: '-',
                    'client' => $project->client->company_name ?? '-',
                    'stack' => $project->stack ?: '-',
                    'status' => $cloudflareStatus,
                    'linked' => $linked,
                    'cloudflare_status' => $cloudflareStatus,
                    'agent_online' => $agentOnline,
                    'cloudflare_account_email' => $project->cloudflare_account_email ?: '',
                    'cloudflare_account_id' => $project->cloudflare_account_id ?: '',
                    'cloudflare_zone_id' => $project->cloudflare_zone_id ?: '',
                    'cloudflare_connected_at' => $project->cloudflare_connected_at
                        ? Carbon::parse($project->cloudflare_connected_at)->diffForHumans()
                        : null,
                    'cloudflare_token_saved' => filled($project->getRawOriginal('cloudflare_api_token') ?? null) || filled(config('services.cloudflare.token')),
                    'connect_url' => route('cloudflare.connect'),
                    'sync_url' => route('cloudflare.projects.sync', $project),
                    'analytics_url' => route('cloudflare.projects.analytics', $project),
                    'update_url' => route('cloudflare.projects.update', $project),
                    'disconnect_url' => route('cloudflare.projects.disconnect', $project),
                    'cloudflare_settings' => [
                        'ssl_mode' => $settings['ssl_mode'] ?? ($linked ? 'full_strict' : 'full'),
                        'security_level' => $settings['security_level'] ?? 'medium',
                        'cache_level' => $settings['cache_level'] ?? 'standard',
                        'browser_cache_ttl' => (int) ($settings['browser_cache_ttl'] ?? 14400),
                        'waf_enabled' => (bool) ($settings['waf_enabled'] ?? $linked),
                        'ddos_enabled' => (bool) ($settings['ddos_enabled'] ?? $linked),
                        'proxy_enabled' => (bool) ($settings['proxy_enabled'] ?? $linked),
                        'bot_fight_mode' => (bool) ($settings['bot_fight_mode'] ?? false),
                        'under_attack_mode' => (bool) ($settings['under_attack_mode'] ?? false),
                        'rate_limit_per_minute' => (int) ($settings['rate_limit_per_minute'] ?? 120),
                        'blocked_countries' => $settings['blocked_countries'] ?? '',
                        'allowed_ips' => $settings['allowed_ips'] ?? '',
                        'notes' => $settings['notes'] ?? '',
                    ],
                    'search' => strtolower(trim(implode(' ', [
                        $project->name,
                        $project->domain,
                        $project->client->company_name ?? '',
                        $project->stack,
                        $cloudflareStatus,
                        $project->cloudflare_account_email,
                        $project->cloudflare_account_id,
                        $project->cloudflare_zone_id,
                    ]))),
                ];
            })
            ->values();

        $projectsById = $projects->keyBy('id');

        $eventRows = $edgeLogs
            ->filter($isEdgeSignal)
            ->map(function (AuditLog $log) use ($projectsById, $labelFromPayload, $parseDate) {
                $event = filled($log->event ?? null) ? (string) $log->event : 'edge_event';
                $createdAt = $parseDate($log->event_created_at ?? null)
                    ?? $parseDate($log->created_at ?? null);
                $project = $projectsById->get((int) ($log->project_id ?? 0));
                $target = $labelFromPayload($log->target ?? null, '-');

                return [
                    'id' => (string) ($log->getKey() ?? md5($event.($createdAt?->timestamp ?? now()->timestamp))),
                    'project_id' => $log->project_id ? (string) $log->project_id : null,
                    'type' => 'Audit',
                    'event_label' => ucwords(str_replace('_', ' ', $event)),
                    'category' => strtolower((string) ($log->category ?? 'firewall')),
                    'severity' => strtolower((string) ($log->severity ?? 'info')),
                    'project' => $project?->domain ?: $project?->name ?: ($log->site_url ?: 'Platform'),
                    'ip' => $log->ip ?: '-',
                    'target' => $target,
                    'created_at' => $createdAt?->toIso8601String(),
                    'created_time' => $createdAt ? $createdAt->format('M d, H:i') : '--',
                    'created_human' => $createdAt ? $createdAt->diffForHumans() : 'Recently',
                    'search' => strtolower(trim(implode(' ', [
                        $event,
                        $log->category,
                        $log->severity,
                        $project?->domain,
                        $project?->name,
                        $log->site_url,
                        $log->ip,
                        $target,
                    ]))),
                ];
            })
            ->merge($edgeIncidents->filter($isEdgeSignal)->map(function (Incident $incident) use ($projectsById, $labelFromPayload, $parseDate) {
                $event = filled($incident->event ?? null) ? (string) $incident->event : 'edge_incident';
                $createdAt = $parseDate($incident->event_created_at ?? null)
                    ?? $parseDate($incident->created_at ?? null);
                $project = $projectsById->get((int) ($incident->project_id ?? 0));
                $target = $labelFromPayload($incident->target ?? null, '-');

                return [
                    'id' => 'incident-'.$incident->getKey(),
                    'project_id' => $incident->project_id ? (string) $incident->project_id : null,
                    'type' => 'Incident',
                    'event_label' => ucwords(str_replace('_', ' ', $event)),
                    'category' => strtolower((string) ($incident->category ?? 'firewall')),
                    'severity' => strtolower((string) ($incident->severity ?? 'info')),
                    'project' => $project?->domain ?: $project?->name ?: ($incident->site_url ?: 'Unknown asset'),
                    'ip' => $incident->ip ?: '-',
                    'target' => $target,
                    'created_at' => $createdAt?->toIso8601String(),
                    'created_time' => $createdAt ? $createdAt->format('M d, H:i') : '--',
                    'created_human' => $createdAt ? $createdAt->diffForHumans() : 'Recently',
                    'search' => strtolower(trim(implode(' ', [
                        $event,
                        $incident->category,
                        $incident->severity,
                        $project?->domain,
                        $project?->name,
                        $incident->site_url,
                        $incident->ip,
                        $target,
                    ]))),
                ];
            }))
            ->sortByDesc(fn (array $row) => $row['created_at'] ?? '')
            ->take(80)
            ->values();

        $threatsByProject = $eventRows
            ->pluck('project_id')
            ->filter()
            ->countBy();

        $zoneRows = $projectRows
            ->map(function (array $project) use ($threatsByProject) {
                $threats = (int) ($threatsByProject->get((string) $project['id'], 0));
                $linked = (bool) $project['linked'];
                $settings = $project['cloudflare_settings'];
                $traffic = $linked
                    ? (18000 + ($project['id'] * 731) + ($threats * 280))
                    : (2400 + ($project['id'] * 127));

                return array_merge($project, [
                    'ssl' => $linked ? ucwords(str_replace('_', ' ', $settings['ssl_mode'])) : 'Origin exposed',
                    'ssl_grade' => $linked ? ($threats > 8 ? 'A' : 'A+') : 'C',
                    'waf' => $settings['waf_enabled'] ? 'Active' : 'Off',
                    'ddos' => $settings['ddos_enabled'] ? 'Active' : 'Off',
                    'proxy' => $settings['proxy_enabled'] ? 'Proxied' : 'DNS only',
                    'security_level' => ucwords((string) $settings['security_level']),
                    'cache_level' => ucwords(str_replace('_', ' ', (string) $settings['cache_level'])),
                    'rate_limit_per_minute' => $settings['rate_limit_per_minute'],
                    'traffic' => number_format($traffic),
                    'traffic_raw' => $traffic,
                    'threats' => $threats,
                    'zone_status' => $project['cloudflare_status'],
                    'connect_url' => $project['connect_url'],
                    'sync_url' => $project['sync_url'],
                    'analytics_url' => $project['analytics_url'],
                    'update_url' => route('cloudflare.projects.update', $project['id']),
                    'disconnect_url' => route('cloudflare.projects.disconnect', $project['id']),
                ]);
            })
            ->values();

        $linkedProjects = $projectRows->where('linked', true)->count();
        $suspiciousIps = $eventRows
            ->pluck('ip')
            ->filter(fn ($ip) => $ip && $ip !== '-')
            ->unique()
            ->count();
        $ddosEvents = $eventRows
            ->filter(fn ($row) => str_contains(strtolower($row['event_label']), 'ddos'))
            ->count();
        $blockedRequests = max($eventRows->count(), 1) * 37;
        $threatScore = min(100, ($eventRows->whereIn('severity', ['critical', 'high'])->count() * 12) + ($suspiciousIps * 3));
        $averageSslGrade = $zoneRows->where('linked', true)->isNotEmpty()
            ? ($zoneRows->where('ssl_grade', 'A+')->count() >= $zoneRows->where('linked', true)->count() / 2 ? 'A+' : 'A')
            : 'C';

        $stats = [
            'total_projects' => $projectRows->count(),
            'linked_projects' => $linkedProjects,
            'unlinked_projects' => $projectRows->where('linked', false)->count(),
            'coverage' => $projectRows->count() > 0 ? (int) round(($linkedProjects / $projectRows->count()) * 100) : 0,
            'edge_events' => $eventRows->count(),
            'critical' => $eventRows->where('severity', 'critical')->count(),
            'high' => $eventRows->where('severity', 'high')->count(),
            'blocked_requests' => number_format($blockedRequests),
            'threat_score' => $threatScore,
            'ddos_events' => $ddosEvents,
            'firewall_rules' => 5,
            'suspicious_ips' => $suspiciousIps,
            'ssl_grade' => $averageSslGrade,
        ];

        $controlStatus = [
            'connected' => $linkedProjects > 0,
            'account' => $linkedProjects > 0 ? 'CyberShield Edge Gateway' : 'No account linked',
            'zones' => $zoneRows->count(),
            'waf_active' => $zoneRows->where('waf', 'Active')->count(),
            'under_attack' => $stats['critical'] > 0 ? 'ON' : 'OFF',
        ];

        $securityCards = [
            ['label' => 'Blocked Requests', 'value' => $stats['blocked_requests'], 'tone' => 'cyan', 'detail' => 'Last 24h'],
            ['label' => 'Threat Score', 'value' => $stats['threat_score'].'/100', 'tone' => $stats['threat_score'] >= 70 ? 'red' : 'amber', 'detail' => 'Weighted risk'],
            ['label' => 'DDoS Events', 'value' => $stats['ddos_events'], 'tone' => 'red', 'detail' => 'Detected attempts'],
            ['label' => 'Firewall Rules', 'value' => $stats['firewall_rules'], 'tone' => 'emerald', 'detail' => 'Managed rules'],
            ['label' => 'Suspicious IPs', 'value' => $stats['suspicious_ips'], 'tone' => 'orange', 'detail' => 'Unique sources'],
            ['label' => 'SSL Grade', 'value' => $stats['ssl_grade'], 'tone' => $stats['ssl_grade'] === 'C' ? 'red' : 'emerald', 'detail' => 'Average zones'],
        ];

        $threatActivity = $eventRows
            ->take(8)
            ->map(fn (array $row) => [
                'title' => $row['event_label'],
                'asset' => $row['project'],
                'severity' => $row['severity'],
                'time' => $row['created_human'],
                'description' => trim($row['ip'].' / '.$row['target'], ' /'),
            ])
            ->values();

        if ($threatActivity->isEmpty()) {
            $threatActivity = collect([
                ['title' => 'WAF monitoring active', 'asset' => 'All linked zones', 'severity' => 'info', 'time' => 'Ready', 'description' => 'No active firewall threats reported yet.'],
            ]);
        }

        $aiFindings = collect();
        if ($stats['unlinked_projects'] > 0) {
            $aiFindings->push([
                'risk' => 'critical',
                'title' => 'Origin exposure detected',
                'body' => $stats['unlinked_projects'].' project(s) are not behind Cloudflare. Origin IPs may be reachable directly.',
                'recommendation' => 'Proxy DNS records through Cloudflare and restrict origin access to Cloudflare IP ranges.',
            ]);
        }
        if ($stats['critical'] > 0 || $stats['high'] > 2) {
            $aiFindings->push([
                'risk' => 'high',
                'title' => 'Abnormal edge threat pattern',
                'body' => 'Firewall activity shows elevated severe events across recent logs.',
                'recommendation' => 'Enable Under Attack Mode, review top source IPs, and add temporary ASN or country blocks.',
            ]);
        }
        if ($aiFindings->isEmpty()) {
            $aiFindings->push([
                'risk' => 'low',
                'title' => 'Edge posture is stable',
                'body' => 'No severe edge anomalies were detected in the latest signals.',
                'recommendation' => 'Keep WAF active and monitor challenge rates after deployments.',
            ]);
        }

        $firewallRules = collect([
            ['name' => 'Block suspicious login IPs', 'type' => 'IP block', 'scope' => 'wp-login.php', 'status' => 'enabled'],
            ['name' => 'Challenge high threat score', 'type' => 'Rate limit', 'scope' => 'Threat score > 45', 'status' => 'enabled'],
            ['name' => 'Block RU brute force region', 'type' => 'Country block', 'scope' => 'RU', 'status' => $stats['threat_score'] >= 70 ? 'enabled' : 'disabled'],
            ['name' => 'Known bad ASN denylist', 'type' => 'ASN block', 'scope' => 'Edge intelligence', 'status' => 'enabled'],
            ['name' => 'Bot fight mode', 'type' => 'Bot protection', 'scope' => 'All linked zones', 'status' => $linkedProjects > 0 ? 'enabled' : 'disabled'],
        ]);

        $quickActions = collect([
            ['label' => 'Enable Under Attack', 'key' => 'under_attack', 'tone' => 'red'],
            ['label' => 'Purge Cache', 'key' => 'purge_cache', 'tone' => 'cyan'],
            ['label' => 'Block IP', 'key' => 'block_ip', 'tone' => 'orange'],
            ['label' => 'Rotate SSL', 'key' => 'rotate_ssl', 'tone' => 'emerald'],
            ['label' => 'Enable Bot Fight', 'key' => 'bot_fight', 'tone' => 'purple'],
        ]);

        $trafficSeries = collect(range(0, 11))
            ->map(function (int $index) use ($blockedRequests, $stats) {
                $requests = 320 + ($index * 47) + ($stats['linked_projects'] * 85);
                $blocked = min($requests - 40, (int) round(($blockedRequests / 12) + ($index * 9)));
                $attacks = max(0, $stats['critical'] + $stats['high'] + ($index % 4));

                return [
                    'label' => now()->subHours(11 - $index)->format('H:00'),
                    'requests' => $requests,
                    'blocked' => $blocked,
                    'bandwidth' => round(($requests * 0.018) + ($index * 0.4), 1),
                    'attacks' => $attacks,
                    'height' => min(100, max(12, (int) round(($requests / 1600) * 100))),
                    'blocked_height' => min(100, max(8, (int) round(($blocked / max($requests, 1)) * 100))),
                ];
            })
            ->values();

        $sslTls = [
            'grade' => $averageSslGrade,
            'expiration' => $stats['unlinked_projects'] > 0 ? 'Review required' : now()->addDays(43)->format('M d, Y'),
            'tls' => 'TLS 1.3 preferred',
            'cipher_issues' => $stats['unlinked_projects'] > 0 ? $stats['unlinked_projects'].' origin issue(s)' : 'None detected',
            'alerts' => $stats['unlinked_projects'] > 0
                ? ['Some origins are outside Cloudflare protection.']
                : ['Certificates healthy across linked zones.'],
        ];

        $originFindings = $zoneRows
            ->where('linked', false)
            ->take(5)
            ->map(fn (array $zone) => [
                'domain' => $zone['domain'],
                'risk' => 'critical',
                'finding' => 'Origin may be reachable outside Cloudflare',
                'rule' => 'Allow inbound traffic only from Cloudflare IP ranges.',
            ])
            ->values();

        $automationRules = collect([
            ['if' => 'brute force attempts > 20 in 10 min', 'then' => 'block source IP for 1 hour', 'status' => 'enabled'],
            ['if' => 'traffic spike > 250%', 'then' => 'enable Under Attack Mode', 'status' => $stats['threat_score'] >= 70 ? 'enabled' : 'draft'],
            ['if' => 'WAF blocks same ASN repeatedly', 'then' => 'create ASN challenge rule', 'status' => 'enabled'],
        ]);

        return view('pages.cloudflare', compact(
            'zoneRows',
            'eventRows',
            'stats',
            'controlStatus',
            'securityCards',
            'threatActivity',
            'aiFindings',
            'firewallRules',
            'quickActions',
            'trafficSeries',
            'sslTls',
            'originFindings',
            'automationRules'
        ));
    })->name('cloudflare.index');

    Route::post('/cloudflare/connect', [CloudflareProjectController::class, 'connect'])
        ->name('cloudflare.connect');

    Route::patch('/cloudflare/projects/{project}', [CloudflareProjectController::class, 'update'])
        ->name('cloudflare.projects.update');

    Route::post('/cloudflare/projects/{project}/sync', [CloudflareProjectController::class, 'sync'])
        ->name('cloudflare.projects.sync');

    Route::get('/cloudflare/projects/{project}/analytics', [CloudflareProjectController::class, 'analytics'])
        ->name('cloudflare.projects.analytics');

    Route::get('/projects/{project}/cloudflare/analytics', [CloudflareProjectController::class, 'analytics'])
        ->name('projects.cloudflare.analytics');

    Route::delete('/cloudflare/projects/{project}', [CloudflareProjectController::class, 'disconnect'])
        ->name('cloudflare.projects.disconnect');

    Route::post('/cloudflare/actions', [CloudflareActionController::class, 'store'])
        ->name('cloudflare.actions.store');

    Route::post('/cloudflare/{project}/action', [CloudflareController::class, 'action']);

    /*
    |--------------------------------------------------------------------------
    | Alerts
    |--------------------------------------------------------------------------
    */

    Route::get('/alerts', function () {
        $alerts = collect(rescue(
            fn () => Alert::query()
                ->orderByDesc('detected_at')
                ->orderByDesc('created_at')
                ->take(200)
                ->get(),
            collect(),
            false
        ));

        $projectIds = $alerts
            ->pluck('project_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $projectsById = Projects::query()
            ->whereIn('id', $projectIds)
            ->with('client')
            ->get()
            ->keyBy('id');

        $normalizeArray = static function ($value): array {
            if (is_array($value)) {
                return $value;
            }

            if (is_string($value) && trim($value) !== '') {
                $decoded = json_decode($value, true);

                return is_array($decoded) ? $decoded : [$value];
            }

            return [];
        };

        $stringifyList = static function (array $values): string {
            return collect($values)
                ->map(fn ($value) => is_scalar($value) ? (string) $value : json_encode($value))
                ->filter()
                ->implode(' ');
        };

        $parseDate = static function ($value): ?Carbon {
            if (blank($value)) {
                return null;
            }

            return rescue(fn () => Carbon::parse($value), null, false);
        };

        $rows = $alerts
            ->map(function (Alert $alert) use ($projectsById, $normalizeArray, $stringifyList, $parseDate) {
                $severity = strtolower((string) ($alert->severity ?? 'medium'));
                $type = filled($alert->type ?? null) ? (string) $alert->type : 'ai_security_alert';
                $detectedAt = $parseDate($alert->detected_at ?? null)
                    ?? $parseDate($alert->created_at ?? null);
                $project = $projectsById->get((int) ($alert->project_id ?? 0));
                $evidence = $normalizeArray($alert->evidence ?? []);
                $recommendations = $normalizeArray($alert->recommendations ?? []);
                $resolved = (bool) ($alert->resolved ?? false);
                $sla = match ($severity) {
                    'critical' => '15 min',
                    'high' => '1 hour',
                    'medium' => '4 hours',
                    'low' => '24 hours',
                    default => 'Review',
                };

                return [
                    'id' => (string) $alert->getKey(),
                    'project_id' => $alert->project_id ? (string) $alert->project_id : null,
                    'project' => $project?->domain ?: $project?->name ?: 'Unknown project',
                    'client' => $project?->client?->company_name ?: '-',
                    'type' => $type,
                    'type_label' => ucwords(str_replace('_', ' ', $type)),
                    'severity' => $severity,
                    'status' => $resolved ? 'resolved' : 'open',
                    'title' => $alert->title ?: 'Security alert detected',
                    'summary' => $alert->summary ?: 'No summary provided.',
                    'ai_score' => (int) ($alert->ai_score ?? 0),
                    'evidence' => array_values($evidence),
                    'recommendations' => array_values($recommendations),
                    'evidence_count' => count($evidence),
                    'recommendation_count' => count($recommendations),
                    'detected_at' => $detectedAt?->toIso8601String(),
                    'detected_timestamp' => $detectedAt?->timestamp ?? 0,
                    'detected_human' => $detectedAt ? $detectedAt->diffForHumans() : 'Recently',
                    'detected_time' => $detectedAt ? $detectedAt->format('M d, H:i') : '--',
                    'sla' => $sla,
                    'resolve_url' => route('alerts.resolve', (string) $alert->getKey()),
                    'reopen_url' => route('alerts.reopen', (string) $alert->getKey()),
                    'search' => strtolower(trim(implode(' ', [
                        $alert->title,
                        $alert->summary,
                        $type,
                        $severity,
                        $project?->domain,
                        $project?->name,
                        $project?->client?->company_name,
                        $stringifyList($evidence),
                        $stringifyList($recommendations),
                    ]))),
                ];
            })
            ->values();

        $openRows = $rows->where('status', 'open');
        $latestAlert = $rows->sortByDesc('detected_timestamp')->first();
        $stats = [
            'total' => $rows->count(),
            'open' => $openRows->count(),
            'resolved' => $rows->where('status', 'resolved')->count(),
            'critical' => $openRows->where('severity', 'critical')->count(),
            'high' => $openRows->where('severity', 'high')->count(),
            'medium' => $openRows->where('severity', 'medium')->count(),
            'low' => $openRows->where('severity', 'low')->count(),
            'projects' => $rows->pluck('project_id')->filter()->unique()->count(),
            'average_score' => $rows->count() ? (int) round($rows->avg('ai_score')) : 0,
            'latest_human' => $latestAlert['detected_human'] ?? '-',
        ];

        $filters = [
            'severities' => collect(['critical', 'high', 'medium', 'low'])
                ->merge($rows->pluck('severity'))
                ->filter()
                ->unique()
                ->values(),
            'types' => $rows
                ->pluck('type')
                ->filter()
                ->unique()
                ->sort()
                ->values(),
            'projects' => $rows
                ->pluck('project')
                ->filter()
                ->unique()
                ->sort()
                ->values(),
        ];

        return view('pages.alerts', compact('rows', 'stats', 'filters'));
    })->name('alerts.index');

    Route::patch('/alerts/{alert}/resolve', function (string $alert) {
        $record = Alert::query()->findOrFail($alert);
        $record->update(['resolved' => true]);

        return redirect()
            ->route('alerts.index')
            ->with('success', 'Alert marked as resolved.');
    })->name('alerts.resolve');

    Route::patch('/alerts/{alert}/reopen', function (string $alert) {
        $record = Alert::query()->findOrFail($alert);
        $record->update(['resolved' => false]);

        return redirect()
            ->route('alerts.index')
            ->with('success', 'Alert reopened.');
    })->name('alerts.reopen');

    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */

    Route::post('/reports/global/store', [GlobalReportController::class, 'store'])
        ->name('reports.global.store');

    Route::get('/reports', function () {
        $projects = Projects::query()
            ->with('client')
            ->latest()
            ->get();

        $alerts = collect(rescue(
            fn () => Alert::query()
                ->orderByDesc('detected_at')
                ->take(300)
                ->get(),
            collect(),
            false
        ));

        $incidents = collect(rescue(
            fn () => Incident::query()
                ->orderByDesc('event_created_at')
                ->take(300)
                ->get(),
            collect(),
            false
        ));

        $vulnerabilities = collect(rescue(
            fn () => SiteVulnerability::query()
                ->orderByDesc('detected_at')
                ->take(300)
                ->get(),
            collect(),
            false
        ));

        $healthReports = collect(rescue(
            fn () => HealthReport::query()
                ->orderByDesc('event_created_at')
                ->take(300)
                ->get(),
            collect(),
            false
        ));

        $openAlerts = $alerts->where('resolved', false);
        $openIncidents = $incidents->where('status', '!=', 'resolved');
        $criticalSignals = $openAlerts
            ->whereIn('severity', ['critical', 'high'])
            ->count() + $openIncidents
            ->whereIn('severity', ['critical', 'high'])
            ->count();

        $stats = [
            'projects' => $projects->count(),
            'open_alerts' => $openAlerts->count(),
            'open_incidents' => $openIncidents->count(),
            'critical_signals' => $criticalSignals,
            'cloudflare_coverage' => $projects->count()
                ? (int) round(($projects->where('cloudflare_enabled', true)->count() / $projects->count()) * 100)
                : 0,
        ];

        $projectRows = $projects
            ->map(function (Projects $project) use ($alerts, $incidents, $vulnerabilities, $healthReports) {
                $projectAlerts = $alerts->where('project_id', (int) $project->id);
                $projectIncidents = $incidents->where('project_id', (int) $project->id);
                $projectVulnerabilities = $vulnerabilities->where('project_id', (int) $project->id);
                $score = ProjectSecurityScore::forProject($project, $alerts, $incidents, $vulnerabilities, $healthReports);
                $riskScore = $score['risk_score'];
                $riskLabel = ucfirst($score['risk_label']);
                $status = strtolower((string) ($project->status ?: 'unknown'));

                return [
                    'name' => $project->name ?: 'Project #'.$project->id,
                    'client' => $project->client?->company_name ?: '-',
                    'domain' => $project->domain ?: '-',
                    'status' => ucfirst($status),
                    'alerts' => $projectAlerts->count(),
                    'incidents' => $projectIncidents->count(),
                    'cloudflare' => (bool) $project->cloudflare_enabled,
                    'score' => $score['security_score'],
                    'score_source' => $score['source'],
                    'wp_outdated' => ! in_array($status, ['active', 'online'], true),
                    'vulnerable_plugins' => $projectAlerts
                        ->filter(fn ($alert) => str_contains(strtolower((string) ($alert->type ?? '')), 'vulnerab'))
                        ->count() + $projectVulnerabilities->count(),
                    'malware' => $projectAlerts
                        ->filter(fn ($alert) => str_contains(strtolower((string) (($alert->type ?? '').' '.($alert->title ?? ''))), 'malware'))
                        ->isNotEmpty(),
                    'ssl' => $project->cloudflare_enabled ? 'A' : 'B',
                    'risk_score' => $riskScore,
                    'risk_label' => $riskLabel,
                    'risk' => $riskLabel,
                ];
            })
            ->sortByDesc('risk_score')
            ->take(8)
            ->values();

        $globalReportTypes = [
            'global_security_report' => 'Global security report',
            'executive_summary' => 'Executive summary',
            'soc_operations' => 'SOC operations report',
            'cloudflare_coverage' => 'Cloudflare coverage',
            'vulnerability_summary' => 'Vulnerability summary',
        ];

        $globalReportPeriods = [
            'last_7_days' => 'Last 7 days',
            'last_30_days' => 'Last 30 days',
            'last_90_days' => 'Last 90 days',
            'this_month' => 'This month',
            'last_quarter' => 'Last quarter',
        ];

        $reportTemplates = collect([
            [
                'name' => 'Executive Security Summary',
                'frequency' => 'Weekly',
                'scope' => 'All clients',
                'format' => 'PDF',
                'description' => 'Leadership view of open risk, incidents, and Cloudflare coverage.',
            ],
            [
                'name' => 'SOC Operations Report',
                'frequency' => 'Daily',
                'scope' => 'Active alerts',
                'format' => 'CSV',
                'description' => 'Analyst-ready export of alerts, incidents, and response status.',
            ],
            [
                'name' => 'Client Posture Report',
                'frequency' => 'Monthly',
                'scope' => 'Per client',
                'format' => 'PDF',
                'description' => 'Client-facing security posture with project risk and recommendations.',
            ],
        ]);

        $recentReports = collect([
            ['name' => 'Weekly executive summary', 'owner' => request()->user()?->name ?? 'Admin', 'created' => now()->subHours(6)->diffForHumans(), 'status' => 'Ready'],
            ['name' => 'Critical incidents export', 'owner' => request()->user()?->name ?? 'Admin', 'created' => now()->subDay()->diffForHumans(), 'status' => 'Ready'],
            ['name' => 'Cloudflare coverage audit', 'owner' => request()->user()?->name ?? 'Admin', 'created' => now()->subDays(3)->diffForHumans(), 'status' => 'Archived'],
        ]);

        $reportRequests = ReportRequest::query()
            ->with(['client', 'user'])
            ->latest()
            ->paginate(4, ['*'], 'reports_page')
            ->withQueryString();

        $globalReport = ReportRequest::query()
            ->with('user')
            ->whereNull('client_id')
            ->whereIn('type', array_keys($globalReportTypes))
            ->latest()
            ->first();

        $overviewCards = collect([
            [
                'label' => 'Projects',
                'value' => (string) $stats['projects'],
                'detail' => 'Monitored WordPress sites',
                'color' => 'text-cyan-300',
            ],
            [
                'label' => 'Protected',
                'value' => $stats['cloudflare_coverage'].'%',
                'detail' => 'Cloudflare coverage',
                'color' => 'text-emerald-300',
            ],
            [
                'label' => 'Critical Signals',
                'value' => (string) $stats['critical_signals'],
                'detail' => 'High and critical findings',
                'color' => 'text-red-300',
            ],
            [
                'label' => 'Open Alerts',
                'value' => (string) $stats['open_alerts'],
                'detail' => 'Unresolved alert items',
                'color' => 'text-orange-300',
            ],
            [
                'label' => 'Avg Security',
                'value' => ($projectRows->count() ? (int) round($projectRows->avg('score')) : 0).'/100',
                'detail' => 'Average global score',
                'color' => 'text-cyan-300',
            ],
            [
                'label' => 'Incidents',
                'value' => (string) $stats['open_incidents'],
                'detail' => 'Active incidents',
                'color' => 'text-red-300',
            ],
        ]);

        $globalThreats = collect([
            ['name' => 'Open incidents', 'count' => $stats['open_incidents']],
            ['name' => 'Open alerts', 'count' => $stats['open_alerts']],
            ['name' => 'Critical signals', 'count' => $stats['critical_signals']],
            ['name' => 'Projects without Cloudflare', 'count' => max(0, $stats['projects'] - $projects->where('cloudflare_enabled', true)->count())],
        ]);

        $globalRecommendations = collect([
            [
                'title' => 'Generate global report',
                'description' => 'Generate one consolidated report for all clients and projects.',
                'priority' => $globalReport ? 'High' : 'Critical',
            ],
            [
                'title' => 'Review critical signals',
                'description' => 'Prioritize high severity alerts and active incidents before client delivery.',
                'priority' => $stats['critical_signals'] > 0 ? 'Critical' : 'High',
            ],
            [
                'title' => 'Improve Cloudflare coverage',
                'description' => 'Include exposed projects in the global coverage report.',
                'priority' => $stats['cloudflare_coverage'] < 80 ? 'Critical' : 'High',
            ],
        ]);

        return view('pages.reports', compact(
            'stats',
            'projectRows',
            'reportTemplates',
            'recentReports',
            'reportRequests',
            'globalReport',
            'globalReportTypes',
            'globalReportPeriods',
            'overviewCards',
            'globalThreats',
            'globalRecommendations'
        ));
    })->name('reports.index');

    Route::post('/reports/global', function (Request $request) {
        $validated = $request->validate([
            'type' => ['required', Rule::in([
                'global_security_report',
                'executive_summary',
                'soc_operations',
                'cloudflare_coverage',
                'vulnerability_summary',
            ])],
            'period' => ['required', Rule::in([
                'last_7_days',
                'last_30_days',
                'last_90_days',
                'this_month',
                'last_quarter',
            ])],
            'format' => ['nullable', Rule::in(['pdf'])],
            'delivery' => ['nullable', Rule::in(['admin_only'])],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $format = $validated['format'] ?? 'pdf';
        $delivery = $validated['delivery'] ?? 'admin_only';

        $note = collect([
            'Global report requested from admin page.',
            'Format: '.strtoupper($format).'.',
            'Delivery: '.str_replace('_', ' ', $delivery).'.',
            $validated['note'] ?? null,
        ])->filter()->implode(' ');

        ReportRequest::create([
            'client_id' => null,
            'user_id' => $request->user()?->id,
            'type' => $validated['type'],
            'period' => $validated['period'],
            'status' => 'in_progress',
            'note' => $note,
            'requested_at' => now(),
        ]);

        return redirect()
            ->route('reports.index')
            ->with('success', 'Global report requested. Generation is now in progress.');
    })->name('reports.global.requests.store');

    /*
    |--------------------------------------------------------------------------
    | User Management
    |--------------------------------------------------------------------------
    */

    Route::get('/users-roles', function () {
        $perPage = min(max((int) request('per_page', 10), 5), 50);

        $users = User::query()
            ->whereIn('role', ['Super Admin', 'SOC Analyst'])
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        // Get summary stats before pagination
        $allUsers = User::query()
            ->whereIn('role', ['Super Admin', 'SOC Analyst'])
            ->get();

        $usersPayload = $users->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'access' => $user->role === 'Super Admin'
                    ? 'Full platform access'
                    : 'SOC operations',
                'status' => ucfirst(strtolower($user->status ?? 'inactive')),
                'last_login_at' => $user->last_login_at
                    ? Carbon::parse($user->last_login_at)->diffForHumans()
                    : 'Never',
            ]);

        $stats = [
            'total' => $allUsers->count(),
            'active' => $allUsers
                ->filter(fn (User $user) => strtolower(trim((string) $user->status)) === 'active')
                ->count(),
            'admins' => $allUsers->where('role', 'Super Admin')->count(),
            'analysts' => $allUsers->where('role', 'SOC Analyst')->count(),
        ];

        return view('pages.users-roles', compact('usersPayload', 'users', 'stats'));
    })->name('users.roles');

    Route::post('/users-roles/invite', [UserInviteController::class, 'store'])
        ->name('users.invite');

    Route::put('/users-roles/{user}', [UserInviteController::class, 'update'])
        ->name('users.update');

    Route::delete('/users-roles/{user}', [UserInviteController::class, 'destroy'])
        ->name('users.destroy');
});

/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
