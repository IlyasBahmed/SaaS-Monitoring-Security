<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserInviteController;
use App\Models\agents;
use App\Models\clients;
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
    Route::get('/dashboard', function () {
        return view('pages.dashboard');
    })->name('dashboard');

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

    return response()->json([
        'status' => $project->status,
        'connected' => (bool) $project->is_connected,
        'last_seen' => $lastSeen
            ? \Carbon\Carbon::parse($lastSeen)->diffForHumans()
            : '-',
        'alerts' => $project->alerts()->count(),
        'incidents' => $project->incidents()->count(),
    ]);
});
    Route::get('/clients', function () {
        $clients = clients::query()
            ->with('user')
            ->withCount('projects')
            ->latest()
            ->paginate(15);

        return view('pages.clients', compact('clients'));
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
        $client->load([
            'projects' => fn ($query) => $query
                ->withCount(['alerts', 'incidents'])
                ->latest(),
        ]);

        return view('pages.clients-show', compact('client'));
    })->name('clients.show');

    Route::get('/agents', function () {
        $installations = ProjectAgent::query()
            ->with(['agent', 'project.client'])
            ->orderByDesc('last_seen_at')
            ->orderByDesc('project_id')
            ->get();

        return view('pages.agents', compact('installations'));
    })->name('agents.index');

    $sidebarPages = [
        [
            'uri' => '/security-center',
            'name' => 'security.index',
            'title' => 'Security Center',
            'description' => 'Review security posture, threat signals, and response priorities.',
        ],
        [
            'uri' => '/incidents',
            'name' => 'incidents.index',
            'title' => 'Incidents',
            'description' => 'Investigate incidents, assign severity, and follow response status.',
        ],
        [
            'uri' => '/alerts',
            'name' => 'alerts.index',
            'title' => 'Alerts',
            'description' => 'Triage real-time alerts and suspicious activity.',
        ],
        [
            'uri' => '/cloudflare',
            'name' => 'cloudflare.index',
            'title' => 'Cloudflare',
            'description' => 'Inspect Cloudflare events, firewall activity, and edge protections.',
        ],
        [
            'uri' => '/reports',
            'name' => 'reports.index',
            'title' => 'Reports',
            'description' => 'Generate executive summaries and operational security reports.',
        ],
        [
            'uri' => '/audit-logs',
            'name' => 'audit-logs.index',
            'title' => 'Audit Logs',
            'description' => 'Review platform activity, changes, and administrative actions.',
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

require __DIR__.'/auth.php';
