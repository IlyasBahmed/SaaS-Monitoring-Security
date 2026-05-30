<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\agents;
use App\Models\Projects;
use App\Models\ProjectAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AgentConnectionController extends Controller
{
    public function connect(Request $request)
    {
        $data = $request->validate([
            'project_api_key' => ['required', 'string'],
            'agent_slug' => ['required', 'string'],
            'version' => ['nullable', 'string'],
        ]);

        $project = Projects::where('api_key', $data['project_api_key'])->first();

        if (! $project) {
            return response()->json([
                'message' => 'Invalid project API key',
            ], 401);
        }

        $agent = agent::where('slug', $data['agent_slug'])
            ->where('status', 'active')
            ->first();

        if (! $agent) {
            return response()->json([
                'message' => 'Invalid agent',
            ], 404);
        }

        $agentKey = 'agent_' . Str::random(40);

        $installation = ProjectAgent::updateOrCreate(
            [
                'project_id' => $project->id,
                'agent_id' => $agent->id,
            ],
            [
                'version' => $data['version'] ?? null,
                'status' => 'online',
                'api_key' => $agentKey,
                'last_seen_at' => now(),
            ]
        );

        $project->update([
            'is_connected' => true,
            'connected_at' => $project->connected_at ?? now(),
            'last_seen_at' => now(),
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'Agent connected successfully',
            'installation_id' => $installation->project_id . '-' . $installation->agent_id,
            'agent_api_key' => $installation->api_key,
            'project_id' => $project->id,
            'agent' => $agent->slug,
            'status' => $installation->status,
        ]);
    }
}