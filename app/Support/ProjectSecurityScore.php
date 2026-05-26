<?php

namespace App\Support;

use App\Models\Projects;
use Illuminate\Support\Collection;

class ProjectSecurityScore
{
    public static function forProject(
        Projects $project,
        ?Collection $alerts = null,
        ?Collection $incidents = null,
        ?Collection $vulnerabilities = null,
        ?Collection $healthReports = null
    ): array {
        $projectId = (int) $project->id;
        $projectAlerts = self::forProjectId($alerts ?? collect(), $projectId);
        $projectIncidents = self::forProjectId($incidents ?? collect(), $projectId);
        $projectVulnerabilities = self::forProjectId($vulnerabilities ?? collect(), $projectId);

        $healthScore = self::latestHealthScore(self::forProjectId($healthReports ?? collect(), $projectId));

        if ($healthScore !== null) {
            $securityScore = $healthScore;
            $riskScore = 100 - $securityScore;
            $source = 'health_report';
        } else {
            $riskScore = min(100, self::riskScore($project, $projectAlerts, $projectIncidents, $projectVulnerabilities));
            $securityScore = max(0, 100 - $riskScore);
            $source = 'live_findings';
        }

        return [
            'security_score' => $securityScore,
            'risk_score' => $riskScore,
            'score_label' => self::securityLabel($securityScore),
            'risk_label' => self::riskLabel($riskScore),
            'source' => $source,
        ];
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
            return self::clampScore((int) round((float) $value));
        }

        if (! is_array($value)) {
            return null;
        }

        foreach (['security_score', 'overall_score', 'overall', 'total', 'score', 'value'] as $key) {
            if (array_key_exists($key, $value) && is_numeric($value[$key])) {
                return self::clampScore((int) round((float) $value[$key]));
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

    private static function riskScore(
        Projects $project,
        Collection $alerts,
        Collection $incidents,
        Collection $vulnerabilities
    ): int {
        $score = 0;
        $score += self::weightedSeverity($alerts->filter(fn ($alert) => ! (bool) ($alert->resolved ?? false)), [
            'critical' => 18,
            'high' => 10,
            'medium' => 5,
            'low' => 2,
            'info' => 1,
        ]);
        $score += self::weightedSeverity($incidents->filter(fn ($incident) => ! in_array(strtolower((string) ($incident->status ?? 'open')), ['resolved', 'closed'], true)), [
            'critical' => 22,
            'high' => 14,
            'medium' => 7,
            'low' => 3,
            'info' => 1,
        ]);
        $score += self::weightedSeverity($vulnerabilities->filter(fn ($vuln) => ! in_array(strtolower((string) ($vuln->status ?? 'open')), ['fixed', 'ignored', 'closed'], true)), [
            'critical' => 20,
            'high' => 12,
            'medium' => 6,
            'low' => 2,
            'info' => 1,
        ]);

        if (! self::agentOnline($project)) {
            $score += 8;
        }

        if (! (bool) ($project->cloudflare_enabled ?? false)) {
            $score += 6;
        }

        if (blank($project->domain ?? null)) {
            $score += 4;
        }

        return max(0, min(100, $score));
    }

    private static function weightedSeverity(Collection $rows, array $weights): int
    {
        return (int) $rows->sum(function ($row) use ($weights) {
            $severity = strtolower((string) ($row->severity ?? 'medium'));

            return $weights[$severity] ?? $weights['medium'];
        });
    }

    private static function agentOnline(Projects $project): bool
    {
        if ($project->relationLoaded('agents') && $project->agents->contains(fn ($agent) => strtolower((string) ($agent->pivot->status ?? '')) === 'online')) {
            return true;
        }

        $lastSeen = $project->agent_last_seen_at ?? $project->last_seen_at ?? null;
        $lastSeenAt = $lastSeen ? rescue(fn () => \Illuminate\Support\Carbon::parse($lastSeen), null, false) : null;

        return (bool) ($project->is_connected ?? false)
            && $lastSeenAt
            && $lastSeenAt->gt(now()->subMinutes(30));
    }

    private static function securityLabel(int $score): string
    {
        return $score >= 85 ? 'Healthy' : ($score >= 65 ? 'Review' : 'Risk');
    }

    private static function riskLabel(int $score): string
    {
        return $score >= 75 ? 'critical' : ($score >= 50 ? 'high' : ($score >= 25 ? 'medium' : 'low'));
    }

    private static function clampScore(int $score): int
    {
        return max(0, min(100, $score));
    }
}
