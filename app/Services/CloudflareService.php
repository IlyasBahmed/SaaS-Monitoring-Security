<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloudflareService
{
    protected ?string $token = null;

    public function withToken(?string $token): self
    {
        $clone = clone $this;
        $clone->token = $token;

        return $clone;
    }

    protected function client()
{
    if (!$this->token) {
        throw new RuntimeException('Missing Cloudflare API token.');
    }

    return Http::withToken($this->token)
        ->acceptJson()
        ->timeout(30)
        ->connectTimeout(10)
        ->retry(3, 1000)
        ->baseUrl('https://api.cloudflare.com/client/v4');
}

    protected function handle($response): array
    {
        $json = $response->json();

        if (!$response->successful() || !($json['success'] ?? false)) {
            $message = $json['errors'][0]['message']
                ?? $json['messages'][0]['message']
                ?? 'Cloudflare API request failed.';

            throw new RuntimeException($message);
        }

        return $json['result'] ?? $json;
    }

    protected function handleGraphql($response): array
    {
        $json = $response->json();

        if (!$response->successful() || !empty($json['errors'])) {
            $message = $json['errors'][0]['message']
                ?? 'Cloudflare GraphQL request failed.';

            throw new RuntimeException($message);
        }

        return $json['data'] ?? [];
    }

    public function graphql(string $query, array $variables = []): array
    {
        return $this->handleGraphql(
            $this->client()->post('/graphql', [
                'query' => $query,
                'variables' => $variables,
            ])
        );
    }

    public function zoneAnalytics(string $zoneId, string $start, string $end): array
    {
        $query = <<<'GRAPHQL'
query ZoneAnalytics($zoneTag: string, $start: Time, $end: Time) {
  viewer {
    zones(filter: { zoneTag: $zoneTag }) {
      hourly: httpRequestsAdaptiveGroups(
        limit: 24
        filter: { datetime_geq: $start, datetime_lt: $end }
        orderBy: [datetimeHour_ASC]
      ) {
        count
        dimensions {
          datetimeHour
        }
      }
      countries: httpRequestsAdaptiveGroups(
        limit: 10
        filter: { datetime_geq: $start, datetime_lt: $end }
        orderBy: [count_DESC]
      ) {
        count
        dimensions {
          clientCountryName
        }
      }
      events: firewallEventsAdaptive(
        filter: { datetime_geq: $start, datetime_lt: $end }
        limit: 100
        orderBy: [datetime_DESC]
      ) {
        action
        clientAsn
        clientCountryName
        clientIP
        clientRequestHTTPHost
        clientRequestPath
        clientRequestQuery
        datetime
        source
        userAgent
        ruleId
      }
    }
  }
}
GRAPHQL;

        $data = $this->graphql($query, [
            'zoneTag' => $zoneId,
            'start' => $start,
            'end' => $end,
        ]);

        return data_get($data, 'viewer.zones.0', []);
    }

    public function zoneDashboardAnalytics(string $zoneId, string $start, string $end): array
{
    try {

        return $this->handle(
            $this->client()->get("/zones/{$zoneId}/analytics/dashboard", [
                'since' => $start,
                'until' => $end,
                'continuous' => 'true',
            ])
        );

    } catch (\Throwable $e) {

        return [
            'totals' => [
                'requests' => [
                    'all' => 0,
                ],
                'threats' => [
                    'all' => 0,
                ],
                'pageviews' => [
                    'all' => 0,
                ],
                'uniques' => [
                    'all' => 0,
                ],
            ],

            'timeseries' => [],

            'error' => $e->getMessage(),
        ];
    }
}
     public function createZone(string $domain): array
     {
    return $this->handle(
        $this->client()->post('/zones', [
            'name' => $domain,
            'jump_start' => true,
        ])
    );
      }
    public function listZones(?string $name = null): array
    {
        $query = [];

        if ($name) {
            $query['name'] = $name;
        }

        return $this->handle(
            $this->client()->get('/zones', $query)
        );
    }

    public function purgeEverything(string $zoneId): array
    {
        return $this->handle(
            $this->client()->post("/zones/{$zoneId}/purge_cache", [
                'purge_everything' => true,
            ])
        );
    }
public function getZone(string $zoneId): array
{
    return $this->handle(
        $this->client()->get("/zones/{$zoneId}")
    );
}

    public function getZoneSetting(string $zoneId, string $setting): array
    {
        return $this->handle(
            $this->client()->get("/zones/{$zoneId}/settings/{$setting}")
        );
    }

    public function getSslMode(string $zoneId): ?string
    {
        $setting = $this->getZoneSetting($zoneId, 'ssl');
        $value = $setting['value'] ?? null;

        return is_string($value) ? $value : null;
    }

    public function purgeUrl(string $zoneId, string $url): array
    {
        return $this->handle(
            $this->client()->post("/zones/{$zoneId}/purge_cache", [
                'files' => [$url],
            ])
        );
    }

    public function enableUnderAttack(string $zoneId): array
    {
        return $this->setSecurityLevel($zoneId, 'under_attack');
    }

    public function disableUnderAttack(string $zoneId): array
    {
        return $this->setSecurityLevel($zoneId, 'medium');
    }

    public function setSecurityLevel(string $zoneId, string $level): array
    {
        return $this->handle(
            $this->client()->patch("/zones/{$zoneId}/settings/security_level", [
                'value' => $level,
            ])
        );
    }

    public function setSslMode(string $zoneId, string $mode): array
    {
        $mode = $mode === 'full_strict' ? 'strict' : $mode;

        return $this->handle(
            $this->client()->patch("/zones/{$zoneId}/settings/ssl", [
                'value' => $mode,
            ])
        );
    }

    public function setCacheLevel(string $zoneId, string $level): array
    {
        if ($level === 'standard') {
            $level = 'basic';
        }

        return $this->handle(
            $this->client()->patch("/zones/{$zoneId}/settings/cache_level", [
                'value' => $level,
            ])
        );
    }

    public function setBrowserCacheTtl(string $zoneId, int $ttl): array
    {
        return $this->handle(
            $this->client()->patch("/zones/{$zoneId}/settings/browser_cache_ttl", [
                'value' => $ttl,
            ])
        );
    }

    public function setBotFightMode(string $zoneId, bool $enabled): array
    {
        return $this->handle(
            $this->client()->patch("/zones/{$zoneId}/settings/bot_fight_mode", [
                'value' => $enabled ? 'on' : 'off',
            ])
        );
    }

    public function createAccessRule(
        string $zoneId,
        string $mode,
        string $targetType,
        string $targetValue,
        string $note
    ): array {
        return $this->handle(
            $this->client()->post("/zones/{$zoneId}/firewall/access_rules/rules", [
                'mode' => $mode,
                'configuration' => [
                    'target' => $targetType,
                    'value' => strtoupper($targetType) === 'COUNTRY'
                        ? strtoupper($targetValue)
                        : $targetValue,
                ],
                'notes' => $note,
            ])
        );
    }
}
