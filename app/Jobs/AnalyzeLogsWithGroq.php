<?php

namespace App\Jobs;

use App\Models\Alert;
use App\Models\AuditLog;
use App\Models\Projects;
use App\Services\GroqSecurityAnalyzer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AnalyzeLogsWithGroq implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $projectId
    ) {}

    public function handle(GroqSecurityAnalyzer $analyzer): void
    {
        $project = Projects::find($this->projectId);

        if (!$project) {
            return;
        }

        $logsQuery = AuditLog::where('project_id', $this->projectId);

        if ($project->last_ai_analyzed_at) {
            $logsQuery->where('created_at', '>', $project->last_ai_analyzed_at);
        }

        $logsCollection = $logsQuery
            ->orderBy('created_at', 'asc')
            ->limit(20)
            ->get();

        if ($logsCollection->isEmpty()) {
            return;
        }

        $lastLogTime = $logsCollection->max('created_at');

        $logs = $logsCollection
            ->map(function ($log) {
                return [
                    'event' => $log->event,
                    'category' => $log->category,
                    'severity' => $log->severity,
                    'ip' => $log->ip,
                    'metadata' => [
                        'action' => $log->metadata['action'] ?? null,
                        'reason' => $log->metadata['reason'] ?? null,
                        'score' => $log->metadata['score'] ?? null,
                        'types' => $log->metadata['types'] ?? null,
                        'rule' => $log->metadata['rule'] ?? null,
                        'blocked' => $log->metadata['blocked'] ?? null,
                    ],
                    'created_at' => $log->event_created_at,
                ];
            })
            ->toArray();

        \Log::info('AI LOGS COUNT', [
            'count' => count($logs),
        ]);

        $result = $analyzer->analyze($logs);

        \Log::info('AI RESULT', [
            'result' => $result,
        ]);

        if (!$result || empty($result['alert'])) {
            return;
        }

        $type = $result['type'] ?? 'ai_security_alert';

        $exists = Alert::where('project_id', $this->projectId)
            ->where('type', $type)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->exists();

        if ($exists) {
            return;
        }

        Alert::create([
            'project_id' => $this->projectId,
            'type' => $type,
            'severity' => $result['severity'] ?? 'medium',
            'title' => $result['title'] ?? 'Security alert detected',
            'summary' => $result['summary'] ?? null,
            'ai_score' => (int) ($result['ai_score'] ?? 50),
            'evidence' => $result['evidence'] ?? [],
            'recommendations' => $result['recommendations'] ?? [],
            'resolved' => false,
            'detected_at' => now(),
        ]);

        $project->update([
            'last_ai_analyzed_at' => $lastLogTime,
        ]);
    }
}