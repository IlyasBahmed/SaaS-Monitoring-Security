<?php

namespace App\Http\Controllers;

use App\Models\agents;
use App\Models\Projects;
use App\Models\ProjectAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\AgentLog;
use App\Models\AuditLog;

use Illuminate\Support\Facades\Hash;




class AgentController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'site_url'       => ['required', 'url'],
            'agent_slug'     => ['required', 'string'],
            'wp_version'     => ['nullable', 'string'],
            'php_version'    => ['nullable', 'string'],
            'plugin_version' => ['nullable', 'string'],
        ]);

        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Missing API key',
            ], 401);
        }

        $apiKey = trim(substr($header, 7));

        $project = Projects::where('api_key', $apiKey)->first();

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key',
            ], 401);
        }

        $agent = agents::where('slug', $request->agent_slug)->first();

        if (!$agent) {
            return response()->json([
                'success' => false,
                'message' => 'Agent not found',
            ], 404);
        }

        $agentKey = 'agent_' . Str::random(40);

        $installation = ProjectAgent::updateOrCreate(
            [
                'project_id' => $project->id,
                'agent_id'   => $agent->id,
            ],
            [
                'site_url'     => $request->site_url,
                'wp_version'   => $request->wp_version,
                'php_version'  => $request->php_version,
                'version'      => $request->plugin_version ?? '1.0.0',
                'status'       => 'online',
                'api_key'      => $agentKey,
                'connected_at' => now(),
                'last_seen_at' => now(),
            ]
        );

        $project->update([
            'is_connected' => true,
            'connected_at' => $project->connected_at ?? now(),
            'last_seen_at' => now(),
            'status'       => 'active',
            'domain'       => $project->domain ?: $request->site_url,
            'stack'        => 'wordpress',
        ]);

        return response()->json([
            'success'       => true,
            'project_id'    => $project->id,
            'agent_api_key' => $installation->api_key,
            'message'       => 'Connected successfully',
        ]);
    }
  public function heartbeat(Request $request)
{
    $request->validate([
        'site_url'       => ['required', 'url'],
        'wp_version'     => ['nullable', 'string'],
        'php_version'    => ['nullable', 'string'],
        'plugin_version' => ['nullable', 'string'],
    ]);

    $header = $request->header('Authorization');

    if (!$header || !str_starts_with($header, 'Bearer ')) {
        return response()->json([
            'success' => false,
            'message' => 'Missing agent key',
        ], 401);
    }

    $agentKey = trim(substr($header, 7));

    $installation = ProjectAgent::where('api_key', $agentKey)->first();

    if (!$installation) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid agent key',
        ], 401);
    }

    $installation->update([
        'status'       => 'online',
        'last_seen_at' => now(),
        'site_url'     => $request->site_url,
        'wp_version'   => $request->wp_version,
        'php_version'  => $request->php_version,
        'version'      => $request->plugin_version ?? '1.0.0',
    ]);

    $installation->project->update([
        'is_connected' => true,
        'status'       => 'active',
        'last_seen_at' => now(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Heartbeat received',
    ]);
}
public function storeLog(Request $request)
{
    $request->validate([
        'type'       => ['required', 'string'],
        'event'      => ['required', 'string'],
        'severity'   => ['nullable', 'string'],
        'site_url'   => ['nullable', 'string'],
        'username'   => ['nullable', 'string'],
        'user_id'    => ['nullable'],
        'role'       => ['nullable', 'string'],
        'data'       => ['nullable', 'array'],
    ]);

    $header = $request->header('Authorization');

    if (!$header || !str_starts_with($header, 'Bearer ')) {
        return response()->json([
            'success' => false,
            'message' => 'Missing agent key',
        ], 401);
    }

    $agentKey = trim(substr($header, 7));

    $installation = ProjectAgent::where('api_key', $agentKey)->first();

    if (!$installation) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid agent key',
        ], 401);
    }

    AgentLog::create([
        'project_id' => $installation->project_id,
        'agent_id' => $installation->agent_id,
        'site_url' => $request->site_url,
        'type' => $request->type,
        'event' => $request->event,
        'severity' => $request->severity ?? 'info',
        'ip_address' => $request->ip(),
        'user_agent' => $request->userAgent(),
        'username' => $request->username,
        'user_id' => $request->user_id,
        'role' => $request->role,
        'data' => $request->data ?? [],
        'created_at' => now(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Log stored',
    ]);
}
public function auditLog(Request $request)
{
    $header = $request->header('Authorization');

    if (!$header || !str_starts_with($header, 'Bearer ')) {

        return response()->json([
            'success' => false,
            'message' => 'Missing API key'
        ], 401);
    }

    $apiKey = trim(str_replace('Bearer ', '', $header));

  $project = Projects::where('api_key', $apiKey)->first();

if (!$project) {

    return response()->json([
        'success' => false,
        'message' => 'Invalid API key'
    ], 401);
}

    if (!$project) {

        return response()->json([
            'success' => false,
            'message' => 'Invalid API key'
        ], 401);
    }

    AuditLog::create([

        'project_id' => $project->id,

        'event' => $request->event,

        'severity' => $request->severity ?? 'info',

        'site_url' => $request->site_url,

        'ip' => $request->ip,

        'user_agent' => $request->user_agent,

        'actor' => $request->actor,

        'target' => $request->target,

        'before' => $request->before,

        'after' => $request->after,

        'metadata' => $request->metadata,

        'event_created_at' => $request->created_at,
    ]);

    $project->update([
        'last_seen_at' => now(),
    ]);

    return response()->json([
        'success' => true
    ]);
}

}