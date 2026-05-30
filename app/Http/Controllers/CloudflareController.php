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

        $cloudflare = $cloudflare->withToken(
            $project->cloudflare_api_token
        );

        switch ($request->action) {

            case 'purge_cache':
                $result = $cloudflare->purgeEverything(
                    $project->cloudflare_zone_id
                );
                break;

            case 'under_attack':
                $result = $cloudflare->enableUnderAttack(
                    $project->cloudflare_zone_id
                );

                $project->update([
                    'under_attack_mode' => true,
                ]);
                break;

            case 'disable_under_attack':
                $result = $cloudflare->disableUnderAttack(
                    $project->cloudflare_zone_id
                );

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
            'success' => true,
            'data' => $result,
        ]);
    }
}