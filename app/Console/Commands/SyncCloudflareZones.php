<?php

namespace App\Console\Commands;

use App\Models\Projects;
use App\Services\CloudflareService;
use Illuminate\Console\Command;
use Throwable;

class SyncCloudflareZones extends Command
{
    protected $signature = 'cloudflare:sync';

    protected $description = 'Sync Cloudflare zones status';

    public function handle(CloudflareService $cloudflare): int
    {
        $projects = Projects::query()
            ->whereNotNull('cloudflare_zone_id')
            ->get();

        foreach ($projects as $project) {
            try {
                $zone = $cloudflare
                    ->withToken(config('services.cloudflare.token'))
                    ->getZone($project->cloudflare_zone_id);

                $project->update([
                    'cloudflare_status' => $zone['status'] ?? 'pending',
                    'cloudflare_enabled' => ($zone['status'] ?? null) === 'active',
                    'cloudflare_nameservers' => $zone['name_servers'] ?? [],
                ]);

                $this->info("Synced {$project->domain}: {$project->cloudflare_status}");
            } catch (Throwable $e) {
                $project->update([
                    'cloudflare_status' => 'error',
                ]);

                $this->error("Failed {$project->domain}: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}