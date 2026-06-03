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
        // IDOR Protection: Verify user is admin/staff
        $user = request()->user();
        $role = str_replace('_', ' ', strtolower(trim((string) ($user?->role ?? ''))));
        if (! $user || ! in_array($role, ['super admin', 'admin', 'staff', 'soc analyst'], true)) {
            abort(403, 'Unauthorized to scan this project.');
        }

        try {

            $result = $scanner->scan($project);

            if (! ($result['success'] ?? false)) {
                return back()->with(
                    'error',
                    $result['message'] ?? 'Scan failed.'
                );
            }

            $response = back()->with(
                'success',
                'Scan completed: ' . ($result['count'] ?? 0) . ' vulnerabilities found across ' . ($result['searched'] ?? 0) . ' active searches.'
            );

            if (! empty($result['errors'])) {
                $response->with('scan_errors', $result['errors']);
            }

            return $response;

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
