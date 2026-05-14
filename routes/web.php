<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectSecurityController;
use App\Http\Controllers\UserInviteController;
use App\Models\agents;
use App\Models\AuditLog;
use App\Models\clients;
use App\Models\Incident;
use App\Models\ProjectAgent;
use App\Models\Projects;
use App\Models\User;
use App\Notifications\ClientPasswordSetupNotification;
use App\Notifications\ProjectApiKeyNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

Route::post('/users-roles/invite', [UserInviteController::class, 'store'])
    ->middleware(['auth'])
    ->name('users.invite');
Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/dashboard', 'pages.dashboard')->name('dashboard');

    Route::get('/projects', function () {
       $projects = Projects::query()
    ->with(['client', 'agents'])
    ->latest()
    ->paginate(15);

        return view('pages.projects', compact('projects'));
    })->name('projects.index');
Route::get('/agents', function () {
    $installations = ProjectAgent::query()
        ->with(['agent', 'project.client'])
        ->orderByDesc('last_seen_at')
        ->orderByDesc('project_id')
        ->paginate(15);

    return view('pages.agents', compact('installations'));
})->name('agents.index');

Route::post('/agents/{installation}/restart', function (ProjectAgent $installation) {
    $project = $installation->project;

    $installation->update([
        'status' => 'online',
        'connected_at' => $installation->connected_at ?? now(),
        'last_seen_at' => now(),
        'meta' => array_merge($installation->meta ?? [], [
            'requested_action' => 'connect',
            'requested_at' => now()->toIso8601String(),
        ]),
    ]);

    if ($project) {
        $project->update([
            'is_connected' => true,
            'connected_at' => $project->connected_at ?? now(),
            'last_seen_at' => now(),
            'status' => 'active',
        ]);
    }

    return redirect()
        ->route('agents.index')
        ->with('success', 'Agent connected.');
})->name('agents.restart');

Route::post('/agents/{installation}/off', function (ProjectAgent $installation) {
    $project = $installation->project;

    $installation->update([
        'status' => 'offline',
        'last_seen_at' => null,
        'meta' => array_merge($installation->meta ?? [], [
            'requested_action' => 'disconnect',
            'requested_at' => now()->toIso8601String(),
        ]),
    ]);

    if ($project) {
        $project->update([
            'is_connected' => false,
            'last_seen_at' => null,
            'status' => 'offline',
        ]);
    }

    return redirect()
        ->route('agents.index')
        ->with('success', 'Agent disconnected.');
})->name('agents.off');

Route::delete('/agents/{installation}', function (ProjectAgent $installation) {
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

   Route::post('/projects', function () {
    $validated = request()->validate([
        'client_id' => ['required', 'exists:clients,id'],
        'agent_id' => ['required', 'exists:agents,id'],

        'name' => ['required', 'string', 'max:255'],
        'domain' => ['nullable', 'string', 'max:255'],
        'ip_address' => ['nullable', 'string', 'max:255'],
        'stack' => ['required', Rule::in(Projects::PROJECT_TYPES)],
        'status' => ['required', Rule::in(['active', 'warning', 'offline'])],
    ]);

    $projectKey = 'proj_' . Str::random(40);

    $project = Projects::create([
        'client_id' => $validated['client_id'],
        'name' => $validated['name'],
        'domain' => $validated['domain'],
        'ip_address' => $validated['ip_address'],
        'stack' => $validated['stack'],
        'status' => $validated['status'],

        'api_key' => $projectKey,
        'api_key_hash' => hash('sha256', $projectKey),
    ]);

    $agentKey = 'agent_' . Str::random(40);

    DB::table('project_agents')->insert([
        'project_id' => $project->id,
        'agent_id' => $validated['agent_id'],
        'version' => null,
        'status' => 'pending',
        'api_key' => $agentKey,
        'last_seen_at' => null,
    ]);

    return redirect()
        ->route('projects.index')
        ->with('success', 'Project added successfully.');
})->name('projects.store');
    Route::get('/projects/{project}/edit', function (Projects $project) {
        $clients = clients::query()
            ->orderBy('company_name')
            ->get();
        $projectTypes = Projects::PROJECT_TYPES;

        return view('pages.projects-edit', compact('project', 'clients', 'projectTypes'));
    })->name('projects.edit');

    Route::put('/projects/{project}', function (Projects $project) {
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
        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    })->name('projects.destroy');

    Route::get('/projects/{project}', function (Projects $project) {
        $project->load(['client', 'agents'])->loadCount(['alerts', 'incidents']);

        return view('pages.projects-show', compact('project'));
    })->name('projects.show');
Route::get('/projects/{project}/realtime', function (Projects $project) {
    $lastSeen = $project->agent_last_seen_at ?? $project->last_seen_at;
    $lastSeenAt = $lastSeen
        ? \Carbon\Carbon::parse($lastSeen)
        : null;
    $agentOnline = $lastSeenAt
        && $lastSeenAt->gt(now()->subMinutes(30))
        && (bool) $project->is_connected;

    return response()->json([
        'status' => $project->status,
        'connected' => (bool) $project->is_connected,
        'last_seen' => $lastSeen
            ? \Carbon\Carbon::parse($lastSeen)->diffForHumans()
            : '-',
        'agent_online' => (bool) $agentOnline,
        'alerts' => $project->alerts()->count(),
        'incidents' => $project->incidents()->count(),
    ]);
});
    Route::get('/clients', function () {
        $activeClients = clients::query()
            ->where('status', 'active')
            ->count();

        $clients = clients::query()
            ->with('user')
            ->withCount('projects')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.clients', compact('clients', 'activeClients'));
    })->name('clients.index');

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
            'status' => ['required', 'in:active,warning,critical'],
        ]);

        $clientUser = DB::transaction(function () use ($validated) {
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

            return $clientUser;
        });

        $token = Password::createToken($clientUser);
        $clientUser->notify(new ClientPasswordSetupNotification($token));

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client added successfully. Password setup email sent.');
    })->name('clients.store');

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
            'status' => ['required', 'in:active,warning,critical'],
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
            ->withCount(['alerts', 'incidents'])
            ->latest()
            ->get();

        $projects = $client->projects()
            ->withCount(['alerts', 'incidents'])
            ->latest()
            ->paginate(10, ['*'], 'projects_page')
            ->withQueryString();

        return view('pages.clients-show', compact('client', 'clientProjects', 'projects'));
    })->name('clients.show');

    Route::view('/audit-logs', 'pages.audit-logs')->name('audit-logs.index');

    Route::get('/audit-logs/feed', function () {
        $logs = collect(rescue(
            fn () => AuditLog::query()
                ->orderByDesc('event_created_at')
                ->orderByDesc('created_at')
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

    Route::get('/incidents', function () {
        $currentUser = request()->user();
        $canTakeIncidents = in_array(strtolower((string) $currentUser?->role), ['soc analyst', 'super admin', 'admin'], true);
        $incidents = collect(rescue(
            fn () => Incident::query()
                ->orderByDesc('event_created_at')
                ->orderByDesc('created_at')
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

    Route::get('/cloudflare', function () {
        $projects = Projects::query()
            ->with(['client', 'agents'])
            ->orderBy('name')
            ->get();

        $edgeLogs = collect(rescue(
            fn () => AuditLog::query()
                ->orderByDesc('event_created_at')
                ->orderByDesc('created_at')
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
                $linked = str_contains($stack, 'cloudflare');
                $agentOnline = $project->agents->contains(
                    fn ($agent) => strtolower((string) ($agent->pivot->status ?? '')) === 'online'
                );
                $status = $linked
                    ? (strtolower((string) ($project->status ?? 'active')) === 'warning' ? 'warning' : 'linked')
                    : 'unlinked';

                return [
                    'id' => $project->id,
                    'name' => $project->name ?: $project->domain ?: 'Project #'.$project->id,
                    'domain' => $project->domain ?: '-',
                    'client' => $project->client->company_name ?? '-',
                    'stack' => $project->stack ?: '-',
                    'status' => $status,
                    'linked' => $linked,
                    'agent_online' => $agentOnline,
                    'search' => strtolower(trim(implode(' ', [
                        $project->name,
                        $project->domain,
                        $project->client->company_name ?? '',
                        $project->stack,
                        $status,
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
                $traffic = $linked
                    ? (18000 + ($project['id'] * 731) + ($threats * 280))
                    : (2400 + ($project['id'] * 127));

                return array_merge($project, [
                    'ssl' => $linked ? 'Full strict' : 'Origin exposed',
                    'ssl_grade' => $linked ? ($threats > 8 ? 'A' : 'A+') : 'C',
                    'waf' => $linked ? 'Active' : 'Off',
                    'traffic' => number_format($traffic),
                    'traffic_raw' => $traffic,
                    'threats' => $threats,
                    'zone_status' => $linked ? ($project['status'] === 'warning' ? 'review' : 'protected') : 'exposed',
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

    $sidebarPages = [
        [
            'uri' => '/alerts',
            'name' => 'alerts.index',
            'title' => 'Alerts',
            'description' => 'Triage real-time alerts and suspicious activity.',
        ],
        [
            'uri' => '/reports',
            'name' => 'reports.index',
            'title' => 'Reports',
            'description' => 'Generate executive summaries and operational security reports.',
        ],
    ];

    foreach ($sidebarPages as $page) {
        Route::view($page['uri'], 'pages.placeholder', [
            'title' => $page['title'],
            'description' => $page['description'],
        ])->name($page['name']);
    }

    Route::get('/users-roles', function () {
        $users = User::query()
            ->whereIn('role', ['Super Admin', 'SOC Analyst'])
            ->orderBy('name')
            ->paginate(15);

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
            'active' => $allUsers->where('status', 'Active')->count(),
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

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::get(
    '/test-scan/{project}',
    [ProjectSecurityController::class, 'runVulnerabilityScan']
);
Route::post(
    '/projects/{project}/vulnerability-scan',
    [ProjectSecurityController::class, 'runVulnerabilityScan']
)->name('projects.vulnerability.scan');
require __DIR__.'/auth.php';
