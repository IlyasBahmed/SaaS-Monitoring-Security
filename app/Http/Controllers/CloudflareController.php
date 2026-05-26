<?php

namespace App\Http\Controllers;

use App\Models\Projects;
use App\Services\CloudflareService;
use Illuminate\Http\Request;

class CloudflareController extends Controller
{
    public function action(
        Request $request,
        Projects $project,
        CloudflareService $cloudflare
    ) {

        $request->validate([
            'action' => 'required|string',
        ]);

        switch ($request->action) {

            case 'purge_cache':
                $result = $cloudflare->purgeCache($project);
                break;

            case 'under_attack':
                $result = $cloudflare->enableUnderAttack($project);

                $project->update([
                    'under_attack_mode' => true,
                ]);
                break;

            case 'disable_under_attack':
                $result = $cloudflare->disableUnderAttack($project);

                $project->update([
                    'under_attack_mode' => false,
                ]);
                break;

            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid action',
                ], 400);
        }

        return response()->json([
            'success' => $result['success'] ?? false,
            'data' => $result,
        ]);
    }
}