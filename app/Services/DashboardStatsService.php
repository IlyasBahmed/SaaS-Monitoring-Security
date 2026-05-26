<?php

namespace App\Services;

class DashboardStatsService
{
    public static function generate(
        $projects,
        $projectScores,
        $incidents,
        $vulnerabilities,
        $agentLogs,
        $auditLogs
    ): array {

        return [

            'clients' => \App\Models\clients::count(),

            'projects' => $projects->count(),

            'protected_projects' =>
                $projects->where('cloudflare_enabled', true)->count(),

            'online_agents' =>
                \App\Models\ProjectAgent::where('status', 'online')->count(),

            'offline_agents' =>
                \App\Models\ProjectAgent::where('status', 'offline')->count(),

            'threats_today' =>
                $incidents->count()
                + $vulnerabilities->where('status', 'open')->count(),

            'open_incidents' =>
                $incidents->where('status', 'open')->count(),

            'critical_signals' =>
                $incidents->whereIn('severity', ['critical', 'high'])->count()
                + $vulnerabilities->whereIn('severity', ['critical', 'high'])->count(),

            'logs_today' =>
                $agentLogs->count() + $auditLogs->count(),

            'avg_score' =>
                $projectScores->count()
                    ? (int) round($projectScores->avg('security_score'))
                    : 0,
        ];
    }
}