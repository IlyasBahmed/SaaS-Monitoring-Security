<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Projects;
use App\Services\VulnerabilityScanner;
use Illuminate\Http\Request;

class AutoScanController extends Controller
{
    public function scan(
        Request $request,
        VulnerabilityScanner $scanner
    ) {

        $apiKey = $request->header('X-API-KEY');

        $project = Projects::where(
            'api_key',
            $apiKey
        )->first();

        if (!$project) {

            return response()->json([
                'success' => false,
                'message' => 'Invalid API key',
            ], 401);
        }

        $result = $scanner->scan($project);

        return response()->json($result);
    }
}