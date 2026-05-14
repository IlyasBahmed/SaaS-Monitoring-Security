<?php

namespace App\Http\Controllers;

use App\Models\agents;
use App\Models\Projects;
use App\Models\ProjectAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\SiteInventory;
use App\Models\AgentLog;
use App\Models\AuditLog;
use App\Models\Incident;
use App\Models\HealthReport;
use App\Models\FileScanReport;

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
            'data'       => ['nullable', 'array'],
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
            'data' => $request->data ?? [],
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

       $event = (string) $request->event;

$severity = $request->severity ?? 'info';

$actor = is_string($request->actor)
    ? json_decode($request->actor, true)
    : $request->actor;

$target = is_string($request->target)
    ? json_decode($request->target, true)
    : $request->target;

$before = is_string($request->before)
    ? json_decode($request->before, true)
    : $request->before;

$after = is_string($request->after)
    ? json_decode($request->after, true)
    : $request->after;

$metadata = is_string($request->metadata)
    ? json_decode($request->metadata, true)
    : ($request->metadata ?? []);

$category = $this->auditCategory($event);

        AuditLog::create([
            'project_id' => $project->id,
            'category' => $category,
            'event' => $event,
            'severity' => $severity,
            'site_url' => $request->site_url,
            'ip' => $request->ip(),
            'user_agent' => $request->user_agent,
          'actor' => $actor,
'target' => $target,
'before' => $before,
'after' => $after,
'metadata' => $metadata,
            'event_created_at' => $request->created_at,
        ]);

        /**
         * Health reports
         */
        if ($category === 'health' && $event === 'health_scan_completed') {

            HealthReport::create([
                'project_id' => $project->id,
                'site_url' => $request->site_url,
                'score' => $metadata['score'] ?? [],
                'risk_level' => $metadata['score']['risk_level'] ?? null,
                'issues' => $metadata['issues'] ?? [],
                'reports' => $metadata['reports'] ?? [],
                'metadata' => $metadata,
                'event_created_at' => $request->created_at,
            ]);
        }

        /**
         * File security reports
         */
        if ($category === 'file_security') {

            FileScanReport::create([
                'project_id' => $project->id,
                'site_url' => $request->site_url,
                'event' => $event,
                'severity' => $severity,
                'target' => $request->target,
                'metadata' => $metadata,
                'event_created_at' => $request->created_at,
            ]);
        }

        /**
         * Security incidents
         */
        if ($this->shouldCreateIncident($event, $severity, $metadata)) {

            Incident::create([
                'project_id' => $project->id,
                'category' => $category,
                'event' => $event,
                'severity' => $severity,
                'site_url' => $request->site_url,
                'ip' => $request->ip(),
                'user_agent' => $request->user_agent,
                'target' => $target,
'metadata' => $metadata,
                'status' => 'open',
                'event_created_at' => $request->created_at,
            ]);
        }

        $project->update([
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'category' => $category,
        ]);
    }

    /**
     * Detect audit category
     */
    private function auditCategory(string $event): string
    {
        if (
            str_contains($event, 'login') ||
            str_contains($event, 'password')
        ) {
            return 'auth';
        }

        if (
            str_starts_with($event, 'firewall_') ||
            str_contains($event, 'xss') ||
            str_contains($event, 'sqli')
        ) {
            return 'firewall';
        }

        if (
            str_contains($event, 'blocking') ||
            str_contains($event, 'banned') ||
            str_contains($event, 'unbanned')
        ) {
            return 'blocking';
        }

        if (
            str_contains($event, 'file_') ||
            str_contains($event, 'upload_') ||
            str_contains($event, 'quarantine') ||
            str_contains($event, 'suspicious_file')
        ) {
            return 'file_security';
        }

        if (
            str_contains($event, 'health') ||
            str_contains($event, 'site_health') ||
            str_contains($event, 'resource')
        ) {
            return 'health';
        }

        return 'audit';
    }

    /**
     * Should create incident
     */
    private function shouldCreateIncident(
        string $event,
        string $severity,
        array $metadata = []
    ): bool {

        if (in_array($severity, ['critical', 'high'], true)) {
            return true;
        }

        if (in_array($event, [
            'ip_banned',
            'file_quarantined',
            'upload_php_detected',
            'suspicious_file_detected',
            'firewall_threat_detected',
        ], true)) {
            return true;
        }

        if (($metadata['action'] ?? null) === 'ban') {
            return true;
        }

        return false;
    }

    public function vulnerabilityInventory(Request $request)
    {
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Missing API key'
            ], 401);
        }

        $apiKey = trim(substr($header, 7));

        $project = Projects::where('api_key', $apiKey)->first();

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key'
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
}
