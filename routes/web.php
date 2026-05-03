<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserInviteController;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Projects;
use App\Models\clients;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

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
            ->with('client')
            ->latest()
            ->get();

        return view('pages.projects', compact('projects'));
    })->name('projects.index');

    Route::get('/projects/create', function () {
        $clients = clients::query()
            ->orderBy('company_name')
            ->get();

        return view('pages.projects-create', compact('clients'));
    })->name('projects.create');

    Route::post('/projects', function () {
        $validated = request()->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255'],
            'ip_address' => ['nullable', 'string', 'max:255'],
            'stack' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:Active,Inactive'],
        ]);

        Projects::create($validated);

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project added successfully.');
    })->name('projects.store');

    Route::get('/clients', function () {
        $clients = clients::query()
            ->with('user')
            ->withCount('projects')
            ->latest()
            ->get();

        return view('pages.clients', compact('clients'));
    })->name('clients.index');

    Route::get('/clients/create', function () {
        return view('pages.clients-create');
    })->name('clients.create');

    Route::post('/clients', function () {
        $validated = request()->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,warning,critical'],
        ]);

        clients::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client added successfully.');
    })->name('clients.store');

    $sidebarPages = [
        [
            'uri' => '/agents',
            'name' => 'agents.index',
            'title' => 'Agents',
            'description' => 'Monitor deployed agents, service health, and endpoint coverage.',
        ],
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
        $usersPayload = User::query()
            ->whereIn('role', ['Super Admin', 'SOC Analyst'])
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
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
            ])
            ->values()
            ->all();

        return view('pages.users-roles', compact('usersPayload'));
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
