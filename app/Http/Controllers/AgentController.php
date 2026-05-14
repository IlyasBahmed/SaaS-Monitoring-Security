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

        $agentKey = 'agent_' . Str::random(40);

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
                'connected_at' => now(),
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

        $installation->update([
            'status'       => 'online',
            'last_seen_at' => now(),
            'site_url'     => $request->site_url,
            'wp_version'   => $request->wp_version,
            'php_version'  => $request->php_version,
            'version'      => $request->plugin_version ?? '1.0.0',
        ]);

        $installation->project->update([
            'is_connected' => true,
            'status'       => 'active',
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Heartbeat received',
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
            str_contains($event, 'vulnerab') ||
            str_contains($event, 'plugin') ||
            str_contains($event, 'theme') ||
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
        if ($event === 'ip_banned') {
            $reason = $metadata['reason'] ?? null;

            return in_array($reason, [
                'repeated_attacks',
                'multiple_attacks',
                'critical_attack',
                'sqli_detected',
                'xss_detected',
                'honeypot_trap_detected',
                'login_rate_limit',
                'wp_admin_rate_limit',
                'suspicious_activity',
            ], true);
        }

        if ($event === 'blocking_action') {
            $action = $metadata['action'] ?? null;
            $score = (int) ($metadata['score'] ?? 0);
            $types = $metadata['types'] ?? [];

            if (in_array($action, ['block', 'ban', 'blocked', 'banned'], true)) {
                return true;
            }

            if ($score >= 50 && is_array($types)) {
                return in_array('sql_injection', $types, true)
                    || in_array('sqli', $types, true)
                    || in_array('xss', $types, true)
                    || in_array('command_injection', $types, true)
                    || in_array('path_traversal', $types, true);
            }

            return false;
        }

        if (in_array($event, [
            'file_quarantined',
            'upload_php_detected',
            'suspicious_upload_file_detected',
            'upload_blocked_by_firewall',
        ], true)) {
            return true;
        }

        if (in_array($event, [
            'suspicious_login_detected',
            'admin_login_from_new_device',
        ], true)) {
            return true;
        }

        if ($event === 'rate_limit_exceeded') {
            return true;
        }

        if (in_array($event, [
            'critical_vulnerability_detected',
            'vulnerable_plugin_detected',
            'vulnerable_theme_detected',
        ], true)) {
            return in_array($severity, ['high', 'critical'], true);
        }

        if ($severity === 'critical') {
            return true;
        }

        return false;
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

        $incident = Incident::where('project_id', $project->id)
            ->where('incident_key', $incidentKey)
            ->whereIn('status', ['open', 'investigating'])
            ->first();

        if (!$incident) {
            return Incident::create([
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
                    'related_audit_log_ids' => [$auditLog->id],
                    'events' => [$event],
                    'count' => 1,
                    'first_seen_at' => now(),
                    'last_seen_at' => now(),
                    'action_taken' => $this->incidentActionTaken($event, $metadata),
                    'needs_review' => true,
                    'latest_metadata' => $metadata,
                ],
                'status' => 'open',
                'event_created_at' => $eventCreatedAt ?? now(),
            ]);
        }

        $currentMetadata = is_array($incident->metadata)
            ? $incident->metadata
            : [];

        $logIds = $currentMetadata['related_audit_log_ids'] ?? [];
        $events = $currentMetadata['events'] ?? [];

        $logIds[] = $auditLog->id;
        $events[] = $event;

        $currentMetadata['related_audit_log_ids'] = array_values(array_unique($logIds));
        $currentMetadata['events'] = array_values(array_unique($events));
        $currentMetadata['count'] = (int) ($currentMetadata['count'] ?? 0) + 1;
        $currentMetadata['last_seen_at'] = now();
        $currentMetadata['latest_metadata'] = $metadata;

        $incident->update([
            'severity' => $this->maxSeverity($incident->severity, $severity),
            'metadata' => $currentMetadata,
            'event_created_at' => $eventCreatedAt ?? $incident->event_created_at,
        ]);

        return $incident;
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

        if ($event === 'ip_banned' || $category === 'firewall') {
            $targetIp = $metadata['ip']
                ?? ($targetArray['id'] ?? null)
                ?? ($targetArray['ip'] ?? null)
                ?? $ip
                ?? 'unknown_ip';

            return sha1($projectId . '|firewall|' . $targetIp);
        }

        if ($category === 'file_security') {
            $file = $targetArray['id']
                ?? $metadata['relative_path']
                ?? $metadata['filename']
                ?? 'unknown_file';

            return sha1($projectId . '|file|' . $file);
        }

        if ($category === 'auth') {
            $userId = $targetArray['id']
                ?? $metadata['user_id']
                ?? $metadata['username']
                ?? 'unknown_user';

            return sha1($projectId . '|auth|' . $userId);
        }

        if ($category === 'vulnerability') {
            $plugin = $metadata['slug']
                ?? $metadata['plugin']
                ?? $metadata['name']
                ?? 'site';

            return sha1($projectId . '|vulnerability|' . $plugin);
        }

        return sha1($projectId . '|' . $category . '|' . $event);
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
}