<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Projects;
use App\Services\CloudflareService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class CloudflareActionController extends Controller
{
    public function store(Request $request, CloudflareService $cloudflare)
    {
        $data = $request->validate([
            'action' => ['required', 'string', Rule::in([
                'purge_cache',
                'purge_url',
                'under_attack',
                'disable_under_attack',
                'block_ip',
                'allow_ip',
                'block_country',
                'challenge_country',
            ])],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'zone_id' => ['nullable', 'string', 'max:255'],
            'target' => ['nullable', 'string', 'max:2048'],
            'note' => ['required', 'string', 'max:1000'],
        ]);

        $project = isset($data['project_id'])
            ? Projects::query()->findOrFail($data['project_id'])
            : null;

        $zoneId = $project?->cloudflare_zone_id ?: ($data['zone_id'] ?? null);

        if (blank($zoneId)) {
            throw ValidationException::withMessages([
                'zone_id' => 'Cloudflare zone ID is missing for this site.',
            ]);
        }

        $target = trim((string) ($data['target'] ?? ''));
        $this->validateTarget($data['action'], $target);

        $token = $project?->cloudflare_api_token ?: config('services.cloudflare.token');

        if (blank($token)) {
            throw ValidationException::withMessages([
                'api_token' => 'Cloudflare API token is missing for this site.',
            ]);
        }

        $cloudflare = $cloudflare->withToken($token);

        try {
            $result = match ($data['action']) {
                'purge_cache' => $cloudflare->purgeEverything($zoneId),
                'purge_url' => $cloudflare->purgeUrl($zoneId, $target),
                'under_attack' => $cloudflare->enableUnderAttack($zoneId),
                'disable_under_attack' => $cloudflare->disableUnderAttack($zoneId),
                'block_ip' => $cloudflare->createAccessRule($zoneId, 'block', 'ip', $target, $data['note']),
                'allow_ip' => $cloudflare->createAccessRule($zoneId, 'whitelist', 'ip', $target, $data['note']),
                'block_country' => $cloudflare->createAccessRule($zoneId, 'block', 'country', $target, $data['note']),
                'challenge_country' => $cloudflare->createAccessRule($zoneId, 'managed_challenge', 'country', $target, $data['note']),
            };

            $this->writeAuditLog($request, $project, $data['action'], $zoneId, $target, $data['note'], $result);
            $this->updateProjectAfterAction($project, $data['action']);

            return response()->json([
                'ok' => true,
                'message' => 'Cloudflare action applied successfully.',
                'result' => $result,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    private function validateTarget(string $action, string $target): void
    {
        if (in_array($action, ['purge_url', 'block_ip', 'allow_ip', 'block_country', 'challenge_country'], true) && $target === '') {
            throw ValidationException::withMessages([
                'target' => 'A target value is required for this Cloudflare action.',
            ]);
        }
    }

    private function writeAuditLog(Request $request, ?Projects $project, string $action, string $zoneId, string $target, string $note, array $result): void
    {
        rescue(function () use ($request, $project, $action, $zoneId, $target, $note, $result) {
            AuditLog::create([
                'project_id' => $project?->id,
                'category' => 'cloudflare',
                'event' => 'cloudflare_'.$action,
                'severity' => in_array($action, ['under_attack', 'block_ip', 'block_country'], true) ? 'high' : 'info',
                'site_url' => $project?->domain,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'actor' => [
                    'id' => $request->user()?->id,
                    'name' => $request->user()?->name,
                    'email' => $request->user()?->email,
                ],
                'target' => [
                    'zone_id' => $zoneId,
                    'value' => $target,
                ],
                'metadata' => [
                    'action' => $action,
                    'note' => $note,
                    'cloudflare_result_id' => $result['id'] ?? null,
                ],
                'event_created_at' => now(),
            ]);
        }, null, false);
    }

    private function updateProjectAfterAction(?Projects $project, string $action): void
    {
        if (!$project || !in_array($action, ['under_attack', 'disable_under_attack'], true)) {
            return;
        }

        $settings = is_array($project->cloudflare_settings)
            ? $project->cloudflare_settings
            : [];

        $settings['under_attack_mode'] = $action === 'under_attack';
        $settings['security_level'] = $action === 'under_attack' ? 'under_attack' : 'medium';

        $project->update([
            'cloudflare_settings' => $settings,
        ]);
    }
}
