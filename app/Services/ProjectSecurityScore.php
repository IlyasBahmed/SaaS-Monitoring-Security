<?php

namespace App\Services;

use App\Models\Projects;
use App\Support\ProjectSecurityScore as CanonicalProjectSecurityScore;
use Illuminate\Support\Collection;

class ProjectSecurityScore
{
    public static function forProject(
        Projects $project,
        ?Collection $alerts = null,
        ?Collection $incidents = null,
        ?Collection $vulnerabilities = null,
        ?Collection $healthReports = null
    ): array
    {
        return CanonicalProjectSecurityScore::forProject(
            $project,
            $alerts,
            $incidents,
            $vulnerabilities,
            $healthReports
        );
    }
}
