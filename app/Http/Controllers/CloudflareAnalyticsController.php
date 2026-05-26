<?php

namespace App\Http\Controllers;

use App\Models\Projects;
use App\Services\CloudflareService;
use Illuminate\Http\Request;

class CloudflareAnalyticsController extends Controller
{
    public function analytics(
        Request $request,
        Projects $project,
        CloudflareService $cloudflare
    ) {
        return app(CloudflareProjectController::class)->analytics($request, $project, $cloudflare);
    }
}
