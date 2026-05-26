<?php

namespace App\Http\Controllers;
use App\Services\CloudflareService;
use Illuminate\Support\Str;
use Throwable;
use App\Models\Projects;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function store(Request $request, CloudflareService $cloudflare)
{
    $data = $request->validate([
        'client_id' => 'required|exists:clients,id',
        'name' => 'required|string|max:255',
        'domain' => 'required|string|max:255',
        'ip_address' => 'nullable|string|max:255',
        'stack' => 'nullable|string|max:255',
        'agent_id' => 'nullable|exists:agents,id',
    ]);

    $apiKey = 'proj_' . Str::random(40);

    while (Projects::where('api_key', $apiKey)->exists()) {
        $apiKey = 'proj_' . Str::random(40);
    }

    $data['api_key'] = $apiKey;
    $data['api_key_hash'] = hash('sha256', $apiKey);
    $data['status'] = 'offline';

    $project = Projects::create($data);

    try {
        $zone = $cloudflare
            ->withToken(config('services.cloudflare.token'))
            ->createZone($project->domain);

      $project->update([
    'cloudflare_enabled' => false,
    'cloudflare_zone_id' => $zone['id'] ?? null,
    'cloudflare_account_id' => $zone['account']['id'] ?? null,
    'cloudflare_nameservers' => $zone['name_servers'] ?? [],
    'cloudflare_status' => $zone['status'] ?? 'pending',
    'cloudflare_settings' => [
        'zone_name' => $zone['name'] ?? null,
    ],
]);
    } catch (Throwable $e) {
        $project->update([
            'cloudflare_settings' => [
                'error' => $e->getMessage(),
            ],
        ]);
    }

    return redirect()
        ->route('projects.index')
        ->with('success', 'Project created. Cloudflare setup started.');
}
}
