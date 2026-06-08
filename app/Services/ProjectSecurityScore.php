<?php

namespace App\Services;

use Illuminate\Support\Collection;

class ProjectSecurityScore
{
   public static function forProject(
    $project,
    Collection $alerts,
    Collection $incidents,
    Collection $vulnerabilities,
    Collection $healthReports
): array {
    $projectId = (int) ($project->id ?? 0);

    $projectAlerts = self::forProjectId($alerts, $projectId);
    $projectIncidents = self::forProjectId($incidents, $projectId);
    $projectVulnerabilities = self::forProjectId($vulnerabilities, $projectId);

    $healthScore = self::latestHealthScore(
        self::forProjectId($healthReports, $projectId)
    );

    $open = fn ($item) => in_array(
        strtolower((string) ($item->status ?? 'open')),
        ['open', 'active', 'in_progress'],
        true
    );

    $sev = fn ($item) => strtolower((string) ($item->severity ?? 'low'));

    /*
    |--------------------------------------------------------------------------
    | Vulnerabilities (Max 40)
    |--------------------------------------------------------------------------
    */
    $vulnerabilityPenalty = 0;

    foreach ($projectVulnerabilities as $vuln) {
        if (! $open($vuln)) {
            continue;
        }

        $vulnerabilityPenalty += match ($sev($vuln)) {
            'critical' => 8,
            'high' => 5,
            'medium' => 2,
            'low' => 1,
            default => 2,
        };
    }

    $vulnerabilityPenalty = min(40, $vulnerabilityPenalty);

    /*
    |--------------------------------------------------------------------------
    | Incidents (Max 35)
    |--------------------------------------------------------------------------
    */
    $incidentPenalty = 0;

    foreach ($projectIncidents as $incident) {
        if (! $open($incident)) {
            continue;
        }

        $incidentPenalty += match ($sev($incident)) {
            'critical' => 10,
            'high' => 6,
            'medium' => 3,
            'low' => 1,
            default => 3,
        };
    }

    $incidentPenalty = min(35, $incidentPenalty);

    /*
    |--------------------------------------------------------------------------
    | Alerts (Max 15)
    |--------------------------------------------------------------------------
    */
    $alertPenalty = 0;

    foreach ($projectAlerts as $alert) {
        if ((bool) ($alert->resolved ?? false)) {
            continue;
        }

        $alertPenalty += match ($sev($alert)) {
            'critical' => 4,
            'high' => 3,
            'medium' => 2,
            'low' => 1,
            default => 1,
        };
    }

    $alertPenalty = min(15, $alertPenalty);

    /*
    |--------------------------------------------------------------------------
    | Project Health (Max 10)
    |--------------------------------------------------------------------------
    */
    $healthPenalty = 0;

    if (! ($project->is_connected ?? false)) {
        $healthPenalty += 4;
    }

    if (! ($project->cloudflare_enabled ?? false)) {
        $healthPenalty += 3;
    }

    if (empty($project->domain)) {
        $healthPenalty += 1;
    }

    if (
        $project->last_seen_at &&
        now()->diffInHours($project->last_seen_at) > 1
    ) {
        $healthPenalty += 2;
    }

    $healthPenalty = min(10, $healthPenalty);

    /*
    |--------------------------------------------------------------------------
    | Live Score
    |--------------------------------------------------------------------------
    */
    $totalPenalty =
        $vulnerabilityPenalty +
        $incidentPenalty +
        $alertPenalty +
        $healthPenalty;

    $liveScore = max(0, 100 - $totalPenalty);

    /*
    |--------------------------------------------------------------------------
    | Final Score
    |--------------------------------------------------------------------------
    */
    if ($healthScore !== null) {
        $score = (int) round(
            ($healthScore * 0.7) +
            ($liveScore * 0.3)
        );

        return self::result(
            $score,
            100 - $score,
            'health_report+live_findings'
        );
    }

    return self::result(
        $liveScore,
        100 - $liveScore,
        'live_findings'
    );
}

    private static function forProjectId(Collection $rows, int $projectId): Collection
    {
        return $rows->filter(fn ($row) => (int) ($row->project_id ?? 0) === $projectId);
    }

    private static function latestHealthScore(Collection $healthReports): ?int
    {
        $report = $healthReports
            ->sortByDesc(fn ($row) => (string) ($row->event_created_at ?? $row->created_at ?? ''))
            ->first();

        return $report ? self::extractScore($report->score ?? null) : null;
    }

    private static function extractScore(mixed $value): ?int
    {
        if (is_numeric($value)) {
            return max(0, min(100, (int) round((float) $value)));
        }

        if (! is_array($value)) {
            return null;
        }

        foreach (['security_score', 'overall_score', 'overall', 'total', 'score', 'value'] as $key) {
            if (array_key_exists($key, $value) && is_numeric($value[$key])) {
                return max(0, min(100, (int) round((float) $value[$key])));
            }
        }

        foreach ($value as $item) {
            $score = self::extractScore($item);

            if ($score !== null) {
                return $score;
            }
        }

        return null;
    }

    private static function result(int $score, int $riskScore, string $source): array
    {
        return [
            'security_score' => $score,
            'score_label' => match (true) {
                $score >= 85 => 'Healthy',
                $score >= 65 => 'Review',
                $score >= 40 => 'Risk',
                default => 'Critical',
            },
            'risk_score' => $riskScore,
            'risk_label' => match (true) {
                $riskScore >= 70 => 'critical',
                $riskScore >= 45 => 'high',
                $riskScore >= 25 => 'medium',
                default => 'low',
            },
            'source' => $source,
        ];
    }
}
