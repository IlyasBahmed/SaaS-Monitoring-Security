<?php

namespace App\Http\Controllers;

use App\Models\Projects;
use App\Services\VulnerabilityScanner;
use Throwable;

class ProjectSecurityController extends Controller
{
    public function runVulnerabilityScan(
        Projects $project,
        VulnerabilityScanner $scanner
    ) {
        try {

            $result = $scanner->scan($project);

            return back()->with(
                'success',
                'Scan completed: ' . $result['count'] . ' vulnerabilities found.'
            );

        } catch (Throwable $e) {

            logger()->error('SCAN ERROR', [
                'message' => $e->getMessage(),
            ]);

            return back()->with(
                'error',
                'Scan failed: ' . $e->getMessage()
            );
        }
    }
}