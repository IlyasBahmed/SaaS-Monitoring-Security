<?php

namespace App\Services;

use Illuminate\Support\Collection;

class ProjectSecurityScore
{
    public static function forProject($project, Collection $alerts, Collection $incidents, Collection $vulnerabilities, Collection $healthReports): array
    {
        $projectId = (int) ($project->id ?? 0);
        $projectAlerts = self::forProjectId($alerts, $projectId);
        $projectIncidents = self::forProjectId($incidents, $projectId);
        $projectVulnerabilities = self::forProjectId($vulnerabilities, $projectId);
        $healthScore = self::latestHealthScore(self::forProjectId($healthReports, $projectId));

        if ($healthScore !== null) {
            $riskScore = 100 - $healthScore;

            return self::result($healthScore, $riskScore, 'health_report');
        }

        $riskScore = 0;

        $open = fn ($item) => in_array(strtolower((string) ($item->status ?? 'open')), ['open', 'active', 'in_progress'], true);
        $sev = fn ($item) => strtolower((string) ($item->severity ?? 'low'));

        foreach ($projectVulnerabilities as $vuln) {
            if (! $open($vuln)) continue;

            $riskScore += match ($sev($vuln)) {
                'critical' => 20,
                'high' => 12,
                'medium' => 6,
                'low' => 2,
                default => 4,
            };
        }

        foreach ($projectIncidents as $incident) {
            if (! $open($incident)) continue;

            $riskScore += match ($sev($incident)) {
                'critical' => 22,
                'high' => 14,
                'medium' => 7,
                'low' => 3,
                default => 6,
            };
        }

        foreach ($projectAlerts as $alert) {
            if ((bool) ($alert->resolved ?? false)) continue;

            $riskScore += match ($sev($alert)) {
                'critical' => 18,
                'high' => 10,
                'medium' => 5,
                'low' => 2,
                default => 3,
            };
        }

        if (! ($project->is_connected ?? false)) {
            $riskScore += 8;
        }

        if (! ($project->cloudflare_enabled ?? false)) {
            $riskScore += 6;
        }

        if (empty($project->domain)) {
            $riskScore += 4;
        }

        if ($project->last_seen_at && now()->diffInMinutes($project->last_seen_at) > 30) {
            $riskScore += 8;
        }

        $riskScore = max(0, min(100, (int) round($riskScore)));
        $score = max(0, 100 - $riskScore);

        return self::result($score, $riskScore, 'live_findings');
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
