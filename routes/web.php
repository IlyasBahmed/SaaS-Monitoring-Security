<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    $sidebarPages = [
        [
            'uri' => '/clients',
            'name' => 'clients.index',
            'title' => 'Clients',
            'description' => 'Manage customer accounts, contacts, and security ownership.',
        ],
        [
            'uri' => '/projects',
            'name' => 'projects.index',
            'title' => 'Projects',
            'description' => 'Track active security projects, scopes, and assigned teams.',
        ],
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
        [
            'uri' => '/users-roles',
            'name' => 'users.roles',
            'title' => 'Users & Roles',
            'description' => 'Manage access, permissions, and role assignments.',
        ],
    ];

    foreach ($sidebarPages as $page) {
        Route::view($page['uri'], 'pages.placeholder', [
            'title' => $page['title'],
            'description' => $page['description'],
        ])->name($page['name']);
    }
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
