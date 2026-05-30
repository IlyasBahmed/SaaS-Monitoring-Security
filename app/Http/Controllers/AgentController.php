<?php

namespace App\Http\Controllers;

use App\Models\agents;
use Illuminate\Support\Facades\Cache;
use App\Models\Projects;
use App\Models\ProjectAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\SiteInventory;
use App\Models\AgentLog;
use App\Models\AuditLog;
use App\Models\Incident;
use App\Jobs\AnalyzeLogsWithGroq;

class AgentController extends Controller
{
    /**
     * Verify plugin installation
     */
   public function verify(Request $request)
{
    $request->validate([
        'site_url'       => ['required', 'url'],
        'agent_slug'     => ['required', 'string'],
        'wp_version'     => ['nullable', 'string'],
        'php_version'    => ['nullable', 'string'],
        'plugin_version' => ['nullable', 'string'],
    ]);

    $header = $request->header('Authorization');

    if (!$header || !str_starts_with($header, 'Bearer ')) {
        return response()->json([
            'success' => false,
            'message' => 'Missing API key',
        ], 401);
    }

    $apiKey = trim(substr($header, 7));

    $project = Projects::where('api_key', $apiKey)->first();

    if (!$project) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid API key',
        ], 401);
    }

    $agent = agents::where('slug', $request->agent_slug)->first();

    if (!$agent) {
        return response()->json([
            'success' => false,
            'message' => 'Agent not found',
        ], 404);
    }

    $installation = ProjectAgent::where([
        'project_id' => $project->id,
        'agent_id'   => $agent->id,
    ])->first();

    if ($installation && in_array($installation->status, ['pending', 'offline', 'disabled'], true)) {
        return response()->json([
            'success' => true,
            'project_id' => $project->id,
            'agent_api_key' => $installation->api_key,
            'status' => $installation->status,
            'command' => 'disconnect',
            'message' => 'Agent disabled from dashboard',
        ]);
    }

    $agentKey = $installation?->api_key ?: 'agent_' . Str::random(40);

    $installation = ProjectAgent::updateOrCreate(
        [
            'project_id' => $project->id,
            'agent_id'   => $agent->id,
        ],
        [
            'site_url'     => $request->site_url,
            'wp_version'   => $request->wp_version,
            'php_version'  => $request->php_version,
            'version'      => $request->plugin_version ?? '1.0.0',
            'status'       => 'online',
            'api_key'      => $agentKey,
            'connected_at' => $installation->connected_at ?? now(),
            'last_seen_at' => now(),
        ]
    );

    $project->update([
        'is_connected' => true,
        'connected_at' => $project->connected_at ?? now(),
        'last_seen_at' => now(),
        'status'       => 'active',
        'domain'       => $project->domain ?: $request->site_url,
        'stack'        => 'wordpress',
    ]);

    return response()->json([
        'success'       => true,
        'project_id'    => $project->id,
        'agent_api_key' => $installation->api_key,
        'status'        => $installation->status,
        'command'       => null,
        'message'       => 'Connected successfully',
    ]);
}

    /**
     * Heartbeat
     */
    public function heartbeat(Request $request)
{
    $request->validate([
        'site_url'       => ['required', 'url'],
        'wp_version'     => ['nullable', 'string'],
        'php_version'    => ['nullable', 'string'],
        'plugin_version' => ['nullable', 'string'],
    ]);

    $header = $request->header('Authorization');

    if (!$header || !str_starts_with($header, 'Bearer ')) {
        return response()->json([
            'success' => false,
            'message' => 'Missing agent key',
        ], 401);
    }

    $agentKey = trim(substr($header, 7));

    $installation = ProjectAgent::where('api_key', $agentKey)->first();

    if (!$installation) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid agent key',
        ], 401);
    }

    $meta = $installation->meta ?? [];
    $command = $meta['command'] ?? null;

    if ($command === 'disconnect' || $installation->status === 'pending' || $installation->status === 'offline') {
        $installation->update([
            'status'       => 'offline',
            'last_seen_at' => null,
            'site_url'     => $request->site_url,
            'wp_version'   => $request->wp_version,
            'php_version'  => $request->php_version,
            'version'      => $request->plugin_version ?? '1.0.0',
            'meta'         => array_merge($meta, [
                'last_command' => 'disconnect',
                'command_executed_at' => now()->toIso8601String(),
                'command' => null,
            ]),
        ]);

        if ($installation->project) {
            $installation->project->update([
                'is_connected' => false,
                'status'       => 'offline',
                'last_seen_at' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Disconnect command received',
            'status'  => 'offline',
            'command' => 'disconnect',
        ]);
    }

    $installation->update([
        'status'       => 'online',
        'last_seen_at' => now(),
        'site_url'     => $request->site_url,
        'wp_version'   => $request->wp_version,
        'php_version'  => $request->php_version,
        'version'      => $request->plugin_version ?? '1.0.0',
    ]);

    if ($installation->project) {
        $installation->project->update([
            'is_connected' => true,
            'status'       => 'active',
            'last_seen_at' => now(),
        ]);
    }

    return response()->json([
        'success' => true,
        'message' => 'Heartbeat received',
        'status'  => 'online',
        'command' => null,
    ]);
}
 
public function auditBatch(Request $request)
{
    $header = $request->header('Authorization');

    if (!$header || !str_starts_with($header, 'Bearer ')) {
        return response()->json([
            'success' => false,
            'message' => 'Missing API key'
        ], 401);
    }

    $apiKey = trim(str_replace('Bearer ', '', $header));

    $project = Projects::where('api_key', $apiKey)->first();

    if (!$project) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid API key'
        ], 401);
    }

    $events = $request->input('events', []);

    if (!is_array($events)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid events payload'
        ], 422);
    }

    $processed = 0;
    $incidents = 0;

    foreach ($events as $eventData) {
        if (!is_array($eventData)) {
            continue;
        }

        $event = (string) ($eventData['event'] ?? 'unknown');
        $severity = $eventData['severity'] ?? 'info';

        $actor = $this->jsonPayload($eventData['actor'] ?? []);
        $target = $this->jsonPayload($eventData['target'] ?? []);
        $before = $this->jsonPayload($eventData['before'] ?? []);
        $after = $this->jsonPayload($eventData['after'] ?? []);
        $metadata = $this->jsonPayload($eventData['metadata'] ?? []);

        if (!is_array($metadata)) {
            $metadata = [];
        }

        $auditLog = AuditLog::create([
            'project_id' => $project->id,
            'category' => $this->auditCategory($event, $metadata),
            'event' => $event,
            'severity' => $severity,
            'site_url' => $eventData['site_url'] ?? null,
            'ip' => $eventData['ip'] ?? null,
            'user_agent' => $eventData['user_agent'] ?? null,
            'actor' => is_array($actor) ? $actor : [],
            'target' => is_array($target) ? $target : [],
            'before' => is_array($before) ? $before : [],
            'after' => is_array($after) ? $after : [],
            'metadata' => $metadata,
            'event_created_at' => $eventData['created_at'] ?? now(),
        ]);

        if ($this->shouldCreateIncident($event, $severity, $metadata)) {
           \Log::info('INCIDENT SHOULD BE CREATED', [
    'event' => $event,
    'severity' => $severity,
]);
        $this->createOrUpdateIncident(
                $project,
                $auditLog,
                $event,
                $severity,
                $eventData['site_url'] ?? null,
                $eventData['ip'] ?? null,
                $eventData['user_agent'] ?? null,
                is_array($target) ? $target : [],
                $metadata,
                $eventData['created_at'] ?? now()
            );

            $incidents++;
        }

        $processed++;
    }

    $project->update([
        'last_seen_at' => now(),
    ]);
    $this->triggerAiAnalysis($project->id);

    return response()->json([
        'success' => true,
        'processed' => $processed,
        'incidents' => $incidents,
    ]);
}



    /**
     * Legacy logs
     */
    public function storeLog(Request $request)
    {
        $request->validate([
            'type'       => ['required', 'string'],
            'event'      => ['required', 'string'],
            'severity'   => ['nullable', 'string'],
            'site_url'   => ['nullable', 'string'],
            'username'   => ['nullable', 'string'],
            'user_id'    => ['nullable'],
            'role'       => ['nullable', 'string'],
            'data'       => ['nullable'],
        ]);

        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Missing agent key',
            ], 401);
        }

        $agentKey = trim(substr($header, 7));

        $installation = ProjectAgent::where('api_key', $agentKey)->first();

        if (!$installation) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid agent key',
            ], 401);
        }

        AgentLog::create([
            'project_id' => $installation->project_id,
            'agent_id' => $installation->agent_id,
            'site_url' => $request->site_url,
            'type' => $request->type,
            'event' => $request->event,
            'severity' => $request->severity ?? 'info',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'username' => $request->username,
            'user_id' => $request->user_id,
            'role' => $request->role,
            'data' => $this->arrayPayload($request->data),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Log stored',
        ]);
    }

    /**
     * Main audit logs
     */
    public function auditLog(Request $request)
    {
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Missing API key',
            ], 401);
        }

        $apiKey = trim(substr($header, 7));

        $project = Projects::where('api_key', $apiKey)->first();

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key',
            ], 401);
        }

        $request->validate([
            'event' => ['required', 'string'],
            'severity' => ['nullable', 'string'],
            'site_url' => ['nullable', 'string'],
            'user_agent' => ['nullable', 'string'],
            'actor' => ['nullable'],
            'target' => ['nullable'],
            'before' => ['nullable'],
            'after' => ['nullable'],
            'metadata' => ['nullable'],
            'created_at' => ['nullable'],
        ]);

        $event = (string) $request->event;
        $severity = $request->severity ?? 'info';

        $actor = $this->jsonPayload($request->actor);
        $target = $this->jsonPayload($request->target);
        $before = $this->jsonPayload($request->before);
        $after = $this->jsonPayload($request->after);

        $metadata = $this->jsonPayload($request->metadata);

        if (!is_array($metadata)) {
            $metadata = [];
        }

        \Log::info('AUDIT DEBUG RECEIVED', [
            'raw_event' => $request->event,
            'normalized_event' => $event,
            'severity' => $severity,
            'metadata' => $metadata,
        ]);

        $auditLog = AuditLog::create([
            'project_id' => $project->id,
            'category' => $this->auditCategory($event, $metadata),
            'event' => $event,
            'severity' => $severity,
            'site_url' => $request->site_url,
            'ip' => $request->ip(),
            'user_agent' => $request->user_agent ?? $request->userAgent(),
            'actor' => $actor,
            'target' => $target,
            'before' => $before,
            'after' => $after,
            'metadata' => $metadata,
            'event_created_at' => $request->created_at ?? now(),
        ]);

        $incidentCreated = false;

        if ($this->shouldCreateIncident($event, $severity, $metadata)) {
            \Log::info('SSA INCIDENT DEBUG BEFORE', [
                'event' => $event,
                'severity' => $severity,
                'metadata' => $metadata,
                'metadata_action' => $metadata['action'] ?? null,
                'should_create' => true,
            ]);

            $this->createOrUpdateIncident(
                $project,
                $auditLog,
                $event,
                $severity,
                $request->site_url,
                $request->ip(),
                $request->user_agent ?? $request->userAgent(),
                $target,
                $metadata,
                $request->created_at ?? now()
            );

            $incidentCreated = true;
        }

        $project->update([
            'last_seen_at' => now(),
        ]);
$this->triggerAiAnalysis($project->id);
        return response()->json([
            'success' => true,
            'category' => $this->auditCategory($event, $metadata),
            'incident_created' => $incidentCreated,
        ]);
    }

    /**
     * Vulnerability inventory
     */
    public function vulnerabilityInventory(Request $request)
    {
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Missing API key',
            ], 401);
        }

        $apiKey = trim(substr($header, 7));

        $project = Projects::where('api_key', $apiKey)->first();

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key',
            ], 401);
        }

        $plugins = $this->arrayPayload($request->input('plugins', []));
        $themes = $this->arrayPayload($request->input('themes', []));

        SiteInventory::create([
            'project_id' => $project->id,
            'site_url' => $request->input('site_url'),
            'wp_version' => $request->input('wp_version'),
            'php_version' => $request->input('php_version'),
            'plugins' => $plugins,
            'themes' => $themes,
            'collected_at' => $request->input('collected_at') ?: now(),
        ]);

        return response()->json([
            'success' => true,
            'plugins_count' => count($plugins),
            'themes_count' => count($themes),
        ]);
    }

    /**
     * Normalize JSON payload.
     */
    private function jsonPayload(mixed $value): mixed
    {
        if (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);

            return json_last_error() === JSON_ERROR_NONE
                ? $decoded
                : $value;
        }

        return $value;
    }

    /**
     * Normalize array payload.
     */
    private function arrayPayload(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * Audit category.
     */
private function auditCategory(string $event, array $metadata = []): string
{
    if (
        str_contains($event, 'blocking') ||
        str_contains($event, 'firewall') ||
        str_contains($event, 'sqli') ||
        str_contains($event, 'sql') ||
        str_contains($event, 'xss') ||
        str_contains($event, 'honeypot') ||
        str_contains($event, 'rate_limit') ||
        str_contains($event, 'ip_banned')
    ) {
        return 'firewall';
    }

    if (
        str_contains($event, 'login') ||
        str_contains($event, 'auth') ||
        str_contains($event, 'user')
    ) {
        return 'auth';
    }

    if (
        str_contains($event, 'file') ||
        str_contains($event, 'upload') ||
        str_contains($event, 'quarantine')
    ) {
        return 'file_security';
    }

    if (
        str_contains($event, 'plugin') ||
        str_contains($event, 'theme')
    ) {
        return 'plugin_theme';
    }

    if (
        str_contains($event, 'post') ||
        str_contains($event, 'page') ||
        str_contains($event, 'content')
    ) {
        return 'content';
    }

    if (
        str_contains($event, 'setting') ||
        str_contains($event, 'option')
    ) {
        return 'settings';
    }

    if (
        str_contains($event, 'vulnerab') ||
        str_contains($event, 'health')
    ) {
        return 'vulnerability';
    }

    return 'audit';
}
    /**
     * Decide if audit log should create/update incident.
     */
private function shouldCreateIncident(
    string $event,
    string $severity,
    array $metadata = []
): bool {
    $event = strtolower(trim($event));
    $severity = strtolower(trim($severity));

    // SQLi / XSS = incident مباشرة
    if (in_array($event, [
        'sqli_detected',
        'sql_injection_detected',
        'xss_detected',
        'xss_attempt_detected',
    ], true)) {
        return true;
    }

    // uploads / malware / critical security actions = incident
    if (in_array($event, [
        'command_injection_detected',
        'path_traversal_detected',
        'xmlrpc_blocked',
        'honeypot_triggered',
        'rate_limit_exceeded',
        'ip_banned',
        'file_quarantined',
        'upload_php_detected',
        'suspicious_upload_file_detected',
        'upload_blocked_by_firewall',
        'suspicious_login_detected',
        'admin_login_from_new_device',
        'critical_vulnerability_detected',
        'vulnerable_plugin_detected',
        'vulnerable_theme_detected',
    ], true)) {
        return in_array($severity, ['medium', 'high', 'critical'], true);
    }

    // blocking_action: action=log ماشي incident، block/ban ولا score >= 50 incident
    if ($event === 'blocking_action') {
        $action = strtolower((string) ($metadata['action'] ?? ''));
        $score = (int) ($metadata['score'] ?? 0);

        return in_array($action, ['block', 'blocked', 'ban', 'banned'], true)
            || $score >= 50;
    }

    return $severity === 'critical';
}

    /**
     * Create one incident or update existing open incident.
     */
   private function createOrUpdateIncident(
    Projects $project,
    AuditLog $auditLog,
    string $event,
    string $severity,
    ?string $siteUrl,
    ?string $ip,
    ?string $userAgent,
    mixed $target,
    array $metadata,
    mixed $eventCreatedAt = null
): Incident {
    $incidentKey = $this->incidentKey(
        $project->id,
        $event,
        $target,
        $metadata,
        $ip
    );

    \Log::info('INCIDENT CREATE/UPDATE START', [
        'project_id' => $project->id,
        'event' => $event,
        'severity' => $severity,
        'incident_key' => $incidentKey,
        'ip' => $ip,
    ]);

    $incident = Incident::where('project_id', $project->id)
        ->where('incident_key', $incidentKey)
        ->whereIn('status', ['open', 'investigating'])
        ->first();

    if (!$incident) {
        try {
            $incident = Incident::create([
                'project_id' => $project->id,
                'incident_key' => $incidentKey,
                'category' => $this->incidentCategory($event, $metadata),
                'event' => $event,
                'severity' => $severity,
                'site_url' => $siteUrl,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'target' => is_array($target) ? $target : [],
                'metadata' => [
                    'related_audit_log_ids' => [(string) $auditLog->id],
                    'events' => [$event],
                    'count' => 1,
                    'first_seen_at' => now()->toDateTimeString(),
                    'last_seen_at' => now()->toDateTimeString(),
                    'action_taken' => $this->incidentActionTaken($event, $metadata),
                    'needs_review' => true,
                    'latest_metadata' => $metadata,
                ],
                'status' => 'open',
                'event_created_at' => $eventCreatedAt ?: now(),
            ]);

            \Log::info('INCIDENT CREATED SUCCESSFULLY', [
                'incident_id' => $incident->id ?? null,
                'event' => $event,
            ]);

            return $incident;

        } catch (\Throwable $e) {
            \Log::error('INCIDENT CREATE FAILED', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw $e;
        }
    }

    try {
        $currentMetadata = is_array($incident->metadata)
            ? $incident->metadata
            : [];

        $logIds = $currentMetadata['related_audit_log_ids'] ?? [];
        $events = $currentMetadata['events'] ?? [];

        $logIds[] = (string) $auditLog->id;
        $events[] = $event;

        $currentMetadata['related_audit_log_ids'] = array_values(array_unique($logIds));
        $currentMetadata['events'] = array_values(array_unique($events));
        $currentMetadata['count'] = (int) ($currentMetadata['count'] ?? 0) + 1;
        $currentMetadata['last_seen_at'] = now()->toDateTimeString();
        $currentMetadata['latest_metadata'] = $metadata;

        $incident->update([
            'severity' => $this->maxSeverity($incident->severity, $severity),
            'metadata' => $currentMetadata,
            'event_created_at' => $eventCreatedAt ?: $incident->event_created_at,
        ]);

        \Log::info('INCIDENT UPDATED SUCCESSFULLY', [
            'incident_id' => $incident->id ?? null,
            'event' => $event,
            'count' => $currentMetadata['count'],
        ]);

        return $incident;

    } catch (\Throwable $e) {
        \Log::error('INCIDENT UPDATE FAILED', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        throw $e;
    }
}

    /**
     * Build incident key to group related logs into one incident.
     */
    private function incidentKey(
    mixed $projectId,
    string $event,
    mixed $target,
    array $metadata,
    ?string $ip
): string {
    $category = $this->incidentCategory($event, $metadata);
    $targetArray = is_array($target) ? $target : [];

    $targetIp = $metadata['ip']
        ?? ($targetArray['id'] ?? null)
        ?? ($targetArray['ip'] ?? null)
        ?? $ip
        ?? 'unknown_ip';

    // group incidents by 30-minute window
    $window = now()->format('Y-m-d-H') . '-' . floor((int) now()->format('i') / 30);

    return sha1(
        $projectId . '|' .
        $category . '|' .
        $targetIp . '|' .
        $window
    );
}
    /**
     * Incident category from event.
     */
    private function incidentCategory(string $event, array $metadata = []): string
    {
        if (
            str_contains($event, 'upload') ||
            str_contains($event, 'file') ||
            str_contains($event, 'quarantine')
        ) {
            return 'file_security';
        }

        if (
            str_contains($event, 'login') ||
            str_contains($event, 'auth')
        ) {
            return 'auth';
        }

        if (
            str_contains($event, 'vulnerab') ||
            str_contains($event, 'health')
        ) {
            return 'vulnerability';
        }

        if (
            str_contains($event, 'rate_limit') ||
            str_contains($event, 'ip_banned') ||
            str_contains($event, 'blocking') ||
            str_contains($event, 'firewall') ||
            str_contains($event, 'sqli') ||
            str_contains($event, 'sql') ||
            str_contains($event, 'xss') ||
            str_contains($event, 'honeypot')
        ) {
            return 'firewall';
        }

        return 'security';
    }

    /**
     * What action was already taken by the agent.
     */
    private function incidentActionTaken(string $event, array $metadata = []): ?string
    {
        if ($event === 'ip_banned') {
            return 'ip_banned';
        }

        if ($event === 'rate_limit_exceeded') {
            return 'rate_limited';
        }

        if ($event === 'file_quarantined') {
            return 'file_quarantined';
        }

        if ($event === 'upload_blocked_by_firewall') {
            return 'upload_blocked';
        }

        if ($event === 'blocking_action') {
            return $metadata['action'] ?? null;
        }

        if (($metadata['blocked'] ?? false) === true) {
            return 'blocked';
        }

        if (($metadata['action'] ?? null)) {
            return $metadata['action'];
        }

        return null;
    }

    /**
     * Keep highest severity.
     */
    private function maxSeverity(?string $old, ?string $new): string
    {
        $rank = [
            'info' => 1,
            'low' => 2,
            'medium' => 3,
            'warning' => 3,
            'high' => 4,
            'critical' => 5,
        ];

        $old = $old ?: 'info';
        $new = $new ?: 'info';

        return ($rank[$new] ?? 1) > ($rank[$old] ?? 1)
            ? $new
            : $old;
    }
   private function triggerAiAnalysis(int $projectId): void
{
    $cacheKey = 'ai_analysis_running_' . $projectId;

    // prevent spam AI jobs
    if (Cache::has($cacheKey)) {
        return;
    }

    // lock 30 seconds
    Cache::put(
        $cacheKey,
        true,
        now()->addSeconds(30)
    );

    \Log::info('AI ANALYSIS DISPATCHED', [
        'project_id' => $projectId,
    ]);

    AnalyzeLogsWithGroq::dispatch($projectId);
}
}