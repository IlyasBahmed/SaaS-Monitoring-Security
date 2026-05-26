<?php

namespace App\Http\Controllers;

use App\Models\Projects;
use App\Services\CloudflareService;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class CloudflareProjectController extends Controller
{
    public function connect(Request $request, CloudflareService $cloudflare)
    {
        $data = $request->validate($this->rules(true));
        $project = Projects::query()->findOrFail($data['project_id']);

        return $this->connectProject($request, $project, $cloudflare, $data, true);
    }

    public function update(Request $request, Projects $project, CloudflareService $cloudflare)
    {
        $data = $request->validate($this->rules(false));

        return $this->connectProject($request, $project, $cloudflare, $data, false);
    }

    public function sync(Request $request, Projects $project, CloudflareService $cloudflare)
    {
        $zoneId = $project->cloudflare_zone_id;

        if (blank($zoneId)) {
            throw ValidationException::withMessages([
                'zone_id' => 'Cloudflare zone ID is missing for this project.',
            ]);
        }

        $token = $this->tokenForProject($project);

        try {
            $client = $cloudflare->withToken($token);
            $zone = $client->getZone($zoneId);
            $settings = is_array($project->cloudflare_settings) ? $project->cloudflare_settings : [];
            $sslMode = $this->cloudflareSslMode($client, $zoneId);
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'cloudflare' => $e->getMessage(),
            ]);
        }

        $project->update($this->zonePayload($zone, [
            'cloudflare_enabled' => ($zone['status'] ?? null) === 'active',
            'cloudflare_settings' => array_merge($settings, array_filter([
                'ssl_mode' => $sslMode,
            ])),
        ]));

        return $this->respond($request, $project, 'Cloudflare zone synced.');
    }

    public function analytics(Request $request, Projects $project, CloudflareService $cloudflare)
    {
        $zoneId = $project->cloudflare_zone_id;

        if (blank($zoneId)) {
            throw ValidationException::withMessages([
                'zone_id' => 'Cloudflare zone ID is missing for this project.',
            ]);
        }

        $hours = min(max((int) $request->query('hours', 24), 1), 24);
        $end = Carbon::now('UTC');
        $start = $end->copy()->subHours($hours);
        $token = $this->tokenForProject($project);

        $client = $cloudflare->withToken($token);
        $raw = [];
        $dashboard = [];
        $errors = [];
        $warnings = [];
        $dashboardError = null;

        try {
            $raw = $client->zoneAnalytics(
                $zoneId,
                $start->toIso8601ZuluString(),
                $end->toIso8601ZuluString()
            );
        } catch (Throwable $e) {
            $errors[] = 'GraphQL: '.$e->getMessage();
            $warnings[] = 'Cloudflare GraphQL analytics failed, using dashboard analytics when available.';
        }

        $graphqlRequests = (int) collect($raw['hourly'] ?? [])
            ->sum(fn (array $row) => (int) ($row['count'] ?? 0));
        $needsDashboardFallback = empty($raw)
            || $graphqlRequests === 0
            || empty($raw['countries'] ?? []);

        if ($needsDashboardFallback) {
            try {
                $dashboard = $client->zoneDashboardAnalytics(
                    $zoneId,
                    $start->toIso8601ZuluString(),
                    $end->toIso8601ZuluString()
                );
            } catch (Throwable $e) {
                $dashboardError = $e->getMessage();
                $errors[] = 'Dashboard: '.$e->getMessage();
            }
        }

        if (empty($raw) && empty($dashboard) && $errors !== []) {
            throw ValidationException::withMessages([
                'cloudflare' => implode(' ', $errors),
            ]);
        }

        if ($dashboardError && $graphqlRequests === 0) {
            $warnings[] = 'Cloudflare dashboard analytics fallback failed: '.$dashboardError;
        }

        return response()->json([
            'ok' => true,
            'message' => 'Cloudflare analytics loaded.',
            'warnings' => array_values(array_unique($warnings)),
            'analytics' => $this->analyticsPayload($raw, $start, $end, $dashboard),
        ]);
    }

    public function disconnect(Request $request, Projects $project)
    {
        $project->update([
            'cloudflare_enabled' => false,
            'cloudflare_account_email' => null,
            'cloudflare_account_id' => null,
            'cloudflare_zone_id' => null,
            'cloudflare_api_token' => null,
            'cloudflare_settings' => null,
            'cloudflare_connected_at' => null,
            'cloudflare_nameservers' => null,
            'cloudflare_status' => null,
        ]);

        return $this->respond($request, $project, 'Cloudflare disconnected from '.$project->name.'.');
    }

    private function connectProject(
        Request $request,
        Projects $project,
        CloudflareService $cloudflare,
        array $data,
        bool $isNewConnection
    ) {
        $token = trim((string) ($data['api_token'] ?? ''))
            ?: $this->tokenForProject($project, false);

        if (blank($token)) {
            throw ValidationException::withMessages([
                'api_token' => 'Cloudflare API token is required.',
            ]);
        }

        try {
            $client = $cloudflare->withToken($token);
            $zone = $this->resolveZone($client, $project, $data['zone_id'] ?? null);
            $settings = $this->settingsFromRequest($request, $project);
            $settingErrors = $this->applySettings($client, (string) ($zone['id'] ?? ''), $settings);
            $sslMode = $this->cloudflareSslMode($client, (string) ($zone['id'] ?? ''));
        } catch (Throwable $e) {
            if ($e instanceof ValidationException) {
                throw $e;
            }

            throw ValidationException::withMessages([
                'cloudflare' => $e->getMessage(),
            ]);
        }

        $payload = $this->zonePayload($zone, [
            'cloudflare_enabled' => ($zone['status'] ?? null) === 'active',
            'cloudflare_account_email' => $data['account_email'] ?? $project->cloudflare_account_email,
            'cloudflare_settings' => array_merge($settings, [
                'ssl_mode' => $sslMode ?? $settings['ssl_mode'],
                'zone_name' => $zone['name'] ?? $project->domain,
                'api_setting_errors' => $settingErrors,
            ]),
            'cloudflare_connected_at' => $project->cloudflare_connected_at ?? now(),
        ]);

        if (filled($data['api_token'] ?? null)) {
            $payload['cloudflare_api_token'] = $data['api_token'];
        }

        $project->update($payload);

        return $this->respond(
            $request,
            $project,
            $isNewConnection ? 'Cloudflare API connected.' : 'Cloudflare API settings updated.',
            $settingErrors
        );
    }

    private function resolveZone(CloudflareService $cloudflare, Projects $project, ?string $zoneId): array
    {
        if (filled($zoneId)) {
            return $cloudflare->getZone($zoneId);
        }

        $domain = $this->normalizeDomain((string) $project->domain);

        if ($domain === '') {
            throw ValidationException::withMessages([
                'domain' => 'Project domain is required before connecting Cloudflare.',
            ]);
        }

        $zones = $cloudflare->listZones($domain);
        $existing = collect($zones)->firstWhere('name', $domain);

        return $existing ?: $cloudflare->createZone($domain);
    }

    private function applySettings(CloudflareService $cloudflare, string $zoneId, array $settings): array
    {
        if ($zoneId === '') {
            return ['Missing zone ID, settings were not applied.'];
        }

        $errors = [];

        foreach ([
            'ssl_mode' => fn () => $cloudflare->setSslMode($zoneId, $settings['ssl_mode']),
            'security_level' => fn () => $cloudflare->setSecurityLevel($zoneId, $settings['security_level']),
            'cache_level' => fn () => $cloudflare->setCacheLevel($zoneId, $settings['cache_level']),
            'browser_cache_ttl' => fn () => $cloudflare->setBrowserCacheTtl($zoneId, (int) $settings['browser_cache_ttl']),
            'bot_fight_mode' => fn () => $cloudflare->setBotFightMode($zoneId, (bool) $settings['bot_fight_mode']),
        ] as $setting => $callback) {
            try {
                $callback();
            } catch (Throwable $e) {
                $errors[] = $setting.': '.$e->getMessage();
            }
        }

        return $errors;
    }

    private function rules(bool $includeProject): array
    {
        return array_merge(
            $includeProject ? ['project_id' => ['required', 'exists:projects,id']] : [],
            [
                'account_email' => ['nullable', 'email', 'max:255'],
                'zone_id' => ['nullable', 'string', 'max:255'],
                'api_token' => ['nullable', 'string', 'max:2048'],
                'ssl_mode' => ['nullable', Rule::in(['off', 'flexible', 'full', 'full_strict'])],
                'security_level' => ['nullable', Rule::in(['essentially_off', 'low', 'medium', 'high', 'under_attack'])],
                'cache_level' => ['nullable', Rule::in(['basic', 'simplified', 'standard', 'aggressive', 'cache_everything'])],
                'browser_cache_ttl' => ['nullable', 'integer', 'min:0', 'max:31536000'],
                'waf_enabled' => ['nullable', 'boolean'],
                'ddos_enabled' => ['nullable', 'boolean'],
                'proxy_enabled' => ['nullable', 'boolean'],
                'bot_fight_mode' => ['nullable', 'boolean'],
                'under_attack_mode' => ['nullable', 'boolean'],
                'rate_limit_per_minute' => ['nullable', 'integer', 'min:1', 'max:100000'],
                'blocked_countries' => ['nullable', 'string', 'max:500'],
                'allowed_ips' => ['nullable', 'string', 'max:1000'],
                'notes' => ['nullable', 'string', 'max:2000'],
            ]
        );
    }

    private function settingsFromRequest(Request $request, Projects $project): array
    {
        $existing = is_array($project->cloudflare_settings) ? $project->cloudflare_settings : [];

        return [
            'ssl_mode' => $request->input('ssl_mode', $existing['ssl_mode'] ?? 'full_strict'),
            'security_level' => $request->input('security_level', $existing['security_level'] ?? 'medium'),
            'cache_level' => $request->input('cache_level', $existing['cache_level'] ?? 'standard'),
            'browser_cache_ttl' => (int) $request->input('browser_cache_ttl', $existing['browser_cache_ttl'] ?? 14400),
            'waf_enabled' => $request->has('waf_enabled') ? $request->boolean('waf_enabled') : (bool) ($existing['waf_enabled'] ?? true),
            'ddos_enabled' => $request->has('ddos_enabled') ? $request->boolean('ddos_enabled') : (bool) ($existing['ddos_enabled'] ?? true),
            'proxy_enabled' => $request->has('proxy_enabled') ? $request->boolean('proxy_enabled') : (bool) ($existing['proxy_enabled'] ?? true),
            'bot_fight_mode' => $request->has('bot_fight_mode') ? $request->boolean('bot_fight_mode') : (bool) ($existing['bot_fight_mode'] ?? false),
            'under_attack_mode' => $request->has('under_attack_mode') ? $request->boolean('under_attack_mode') : (bool) ($existing['under_attack_mode'] ?? false),
            'rate_limit_per_minute' => (int) $request->input('rate_limit_per_minute', $existing['rate_limit_per_minute'] ?? 120),
            'blocked_countries' => trim((string) $request->input('blocked_countries', $existing['blocked_countries'] ?? '')),
            'allowed_ips' => trim((string) $request->input('allowed_ips', $existing['allowed_ips'] ?? '')),
            'notes' => trim((string) $request->input('notes', $existing['notes'] ?? '')),
        ];
    }

    private function zonePayload(array $zone, array $overrides = []): array
    {
        return array_merge([
            'cloudflare_account_id' => data_get($zone, 'account.id'),
            'cloudflare_zone_id' => $zone['id'] ?? null,
            'cloudflare_nameservers' => $zone['name_servers'] ?? [],
            'cloudflare_status' => $this->clientCloudflareStatus($zone['status'] ?? null),
        ], $overrides);
    }

    private function tokenForProject(Projects $project, bool $throw = true): ?string
    {
        $token = $project->cloudflare_api_token ?: config('services.cloudflare.token');

        if ($throw && blank($token)) {
            throw ValidationException::withMessages([
                'api_token' => 'Cloudflare API token is missing for this project.',
            ]);
        }

        return $token;
    }

    private function normalizeDomain(string $domain): string
    {
        $domain = trim($domain);
        $domain = preg_replace('#^https?://#i', '', $domain);
        $domain = preg_replace('#/.*$#', '', $domain);

        return strtolower($domain ?: '');
    }

    private function cloudflareSslMode(CloudflareService $cloudflare, string $zoneId): ?string
    {
        if ($zoneId === '') {
            return null;
        }

        $mode = rescue(fn () => $cloudflare->getSslMode($zoneId), null, false);

        return match ($mode) {
            'strict' => 'full_strict',
            'off', 'flexible', 'full', 'full_strict' => $mode,
            default => null,
        };
    }

    private function respond(Request $request, Projects $project, string $message, array $warnings = [])
    {
        $project->refresh();

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'warnings' => $warnings,
                'site' => $this->sitePayload($project),
            ]);
        }

        return redirect()
            ->route('cloudflare.index')
            ->with('success', $message);
    }

    private function sitePayload(Projects $project): array
    {
        $settings = is_array($project->cloudflare_settings) ? $project->cloudflare_settings : [];
        $linked = filled($project->cloudflare_zone_id);

        return [
            'project_id' => $project->id,
            'domain' => $project->domain ?: $project->name,
            'zone_id' => $project->cloudflare_zone_id ?: '',
            'cloudflare_status' => $this->clientCloudflareStatus($project->cloudflare_status),
            'name_servers' => $project->cloudflare_nameservers ?: [],
            'last_update' => $project->cloudflare_connected_at
                ? $project->cloudflare_connected_at->diffForHumans()
                : ($linked ? 'Connected' : 'Not connected'),
            'cdn' => $linked && (bool) ($settings['proxy_enabled'] ?? true),
            'waf' => $linked && (bool) ($settings['waf_enabled'] ?? true),
            'ssl' => $linked ? ucwords(str_replace('_', ' ', $settings['ssl_mode'] ?? 'full_strict')) : 'Origin exposed',
            'login_protection' => $linked,
            'xmlrpc_blocked' => $linked,
            'bot_protection' => $linked && (bool) ($settings['bot_fight_mode'] ?? false),
            'cloudflare_token_saved' => filled($project->getRawOriginal('cloudflare_api_token') ?? null) || filled(config('services.cloudflare.token')),
            'cloudflare_account_email' => $project->cloudflare_account_email ?: '',
            'cloudflare_settings' => $settings,
            'connect_url' => route('cloudflare.connect'),
            'sync_url' => route('cloudflare.projects.sync', $project),
            'analytics_url' => route('cloudflare.projects.analytics', $project),
            'update_url' => route('cloudflare.projects.update', $project),
            'disconnect_url' => route('cloudflare.projects.disconnect', $project),
        ];
    }

    private function analyticsPayload(array $raw, Carbon $start, Carbon $end, array $dashboard = []): array
    {
        $dashboardHourly = $this->dashboardHourly($dashboard, $start);
        $usingDashboardTraffic = false;

        $hourly = collect($raw['hourly'] ?? [])
            ->map(function (array $row) {
                $hour = data_get($row, 'dimensions.datetimeHour');
                $requests = (int) ($row['count'] ?? 0);

                return [
                    'hour' => $hour ? Carbon::parse($hour)->format('H:i') : '--',
                    'requests' => $requests,
                ];
            })
            ->values();

        if ((int) $hourly->sum('requests') === 0 && (int) $dashboardHourly->sum('requests') > 0) {
            $hourly = $dashboardHourly;
            $usingDashboardTraffic = true;
        }

        $maxHourly = max((int) $hourly->max('requests'), 1);
        $trafficBars = $hourly
            ->map(fn (array $row) => array_merge($row, [
                'value' => max(4, (int) round(($row['requests'] / $maxHourly) * 100)),
            ]))
            ->values();

        if ($trafficBars->isEmpty()) {
            $trafficBars = collect(range(0, 7))
                ->map(fn (int $index) => [
                    'hour' => $start->copy()->addHours($index * 3)->format('H:i'),
                    'requests' => 0,
                    'value' => 4,
                ]);
        }

        $totalRequests = (int) $hourly->sum('requests');
        $dashboardTotalRequests = $this->dashboardTotalRequests($dashboard);

        if ($totalRequests === 0 && $dashboardTotalRequests > 0) {
            $totalRequests = $dashboardTotalRequests;
            $usingDashboardTraffic = true;
        }

        $countriesTotal = max((int) collect($raw['countries'] ?? [])->sum('count'), 1);

        $countries = collect($raw['countries'] ?? [])
            ->map(function (array $row) use ($countriesTotal) {
                $country = (string) data_get($row, 'dimensions.clientCountryName', 'Unknown');
                $code = strlen($country) === 2 ? strtoupper($country) : '';
                $requests = (int) ($row['count'] ?? 0);

                return [
                    'code' => $code,
                    'name' => $this->countryName($code ?: $country),
                    'requests' => $requests,
                    'percentage' => (int) round(($requests / $countriesTotal) * 100),
                ];
            })
            ->filter(fn (array $row) => $row['requests'] > 0)
            ->values();

        if ($countries->isEmpty()) {
            $countries = $this->dashboardCountries($dashboard);
        }

        $securityLogs = collect($raw['events'] ?? [])
            ->map(function (array $event, int $index) {
                $action = $this->cloudflareActionLabel((string) ($event['action'] ?? 'log'));
                $path = (string) ($event['clientRequestPath'] ?? '/');
                $query = trim((string) ($event['clientRequestQuery'] ?? ''));
                $datetime = filled($event['datetime'] ?? null)
                    ? Carbon::parse($event['datetime'])
                    : null;

                if ($query !== '') {
                    $path .= '?'.$query;
                }

                return [
                    'id' => sha1(json_encode($event).$index),
                    'time' => $datetime ? $datetime->format('M d H:i') : '--',
                    'event' => ucwords(str_replace('_', ' ', (string) ($event['source'] ?? 'security event'))),
                    'detail' => trim(implode(' ', array_filter([
                        $event['source'] ?? null,
                        filled($event['ruleId'] ?? null) ? 'Rule '.$event['ruleId'] : null,
                        filled($event['clientRequestHTTPHost'] ?? null) ? 'Host '.$event['clientRequestHTTPHost'] : null,
                    ]))) ?: 'Cloudflare security event.',
                    'ip' => $event['clientIP'] ?: '-',
                    'country' => strlen((string) ($event['clientCountryName'] ?? '')) === 2
                        ? strtoupper((string) $event['clientCountryName'])
                        : '',
                    'path' => $path,
                    'action' => $action,
                    'severity' => $this->cloudflareSeverity($action),
                    'user_agent' => $event['userAgent'] ?? '',
                ];
            })
            ->values();

        $topIps = $securityLogs
            ->where('ip', '!=', '-')
            ->groupBy('ip')
            ->map(function ($rows, string $ip) {
                $first = $rows->first();

                return [
                    'address' => $ip,
                    'country' => $first['country'] ?? '',
                    'attempts' => $rows->count(),
                ];
            })
            ->sortByDesc('attempts')
            ->take(8)
            ->values();

        $blockedEvents = $securityLogs->filter(
            fn (array $log) => in_array($log['action'], ['Blocked', 'Managed Challenge', 'Challenge'], true)
        );

        return [
            'traffic_bars' => $trafficBars->values(),
            'top_countries' => $countries,
            'top_attacking_ips' => $topIps,
            'security_logs' => $securityLogs,
            'traffic_24h' => $totalRequests,
            'waf_events' => $securityLogs->count(),
            'threats_blocked' => $blockedEvents->count(),
            'cache_hit' => $this->dashboardCacheHit($dashboard),
            'login_attacks' => $securityLogs
                ->filter(fn (array $log) => str_contains(strtolower($log['path']), 'wp-login'))
                ->count(),
            'xmlrpc_hits' => $securityLogs
                ->filter(fn (array $log) => str_contains(strtolower($log['path']), 'xmlrpc'))
                ->count(),
            'analytics_window' => [
                'start' => $start->toIso8601ZuluString(),
                'end' => $end->toIso8601ZuluString(),
            ],
            'analytics_source' => $usingDashboardTraffic ? 'cloudflare_dashboard_rest' : 'cloudflare_graphql',
            'analytics_synced_at' => Carbon::now()->diffForHumans(),
        ];
    }

    private function dashboardHourly(array $dashboard, Carbon $start)
    {
        return collect($dashboard['timeseries'] ?? [])
            ->map(function (array $point, int $index) use ($start) {
                $time = data_get($point, 'since') ?: data_get($point, 'dimensions.datetimeHour');

                return [
                    'hour' => $time ? Carbon::parse($time)->format('H:i') : $start->copy()->addHours($index)->format('H:i'),
                    'requests' => (int) data_get($point, 'requests.all', 0),
                ];
            })
            ->values();
    }

    private function dashboardCountries(array $dashboard)
    {
        $totals = [];
        $addCountries = function ($countries) use (&$totals): void {
            if (!is_array($countries)) {
                return;
            }

            foreach ($countries as $country => $requests) {
                $country = strtoupper((string) $country);
                $totals[$country] = ($totals[$country] ?? 0) + (int) $requests;
            }
        };

        $addCountries(data_get($dashboard, 'totals.requests.country', []));

        foreach ($dashboard['timeseries'] ?? [] as $point) {
            $addCountries(data_get($point, 'requests.country', []));
        }

        if ($totals === []) {
            return collect();
        }

        $total = max(array_sum($totals), 1);

        return collect($totals)
            ->map(function (int $requests, string $country) use ($total) {
                $code = strlen($country) === 2 ? $country : '';

                return [
                    'code' => $code,
                    'name' => $this->countryName($code ?: $country),
                    'requests' => $requests,
                    'percentage' => (int) round(($requests / $total) * 100),
                ];
            })
            ->sortByDesc('requests')
            ->take(10)
            ->values();
    }

    private function dashboardTotalRequests(array $dashboard): int
    {
        $total = (int) data_get($dashboard, 'totals.requests.all', 0);

        if ($total > 0) {
            return $total;
        }

        return (int) collect($dashboard['timeseries'] ?? [])
            ->sum(fn (array $point) => (int) data_get($point, 'requests.all', 0));
    }

    private function dashboardCacheHit(array $dashboard): int
    {
        $total = $this->dashboardTotalRequests($dashboard);
        $cached = (int) data_get($dashboard, 'totals.requests.cached', 0);

        if ($cached <= 0) {
            $cached = (int) collect($dashboard['timeseries'] ?? [])
                ->sum(fn (array $point) => (int) data_get($point, 'requests.cached', 0));
        }

        return $total > 0 ? (int) round(($cached / $total) * 100) : 0;
    }

    private function cloudflareActionLabel(string $action): string
    {
        return match (strtolower($action)) {
            'block', 'blocked' => 'Blocked',
            'managed_challenge' => 'Managed Challenge',
            'challenge', 'js_challenge' => 'Challenge',
            'allow', 'skip', 'bypass' => 'Allowed',
            'log', 'simulate' => 'Monitor',
            default => ucwords(str_replace('_', ' ', $action ?: 'Monitor')),
        };
    }

    private function cloudflareSeverity(string $action): string
    {
        return match ($action) {
            'Blocked' => 'High',
            'Managed Challenge', 'Challenge' => 'Medium',
            default => 'Low',
        };
    }

    private function countryName(string $country): string
    {
        $code = strtoupper(trim($country));

        return [
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'DE' => 'Germany',
            'FR' => 'France',
            'CA' => 'Canada',
            'MA' => 'Morocco',
            'ES' => 'Spain',
            'NL' => 'Netherlands',
            'BR' => 'Brazil',
            'IT' => 'Italy',
            'RU' => 'Russia',
            'CN' => 'China',
            'IN' => 'India',
            'JP' => 'Japan',
            'AU' => 'Australia',
            'TR' => 'Turkey',
            'DZ' => 'Algeria',
            'TN' => 'Tunisia',
            'EG' => 'Egypt',
        ][$code] ?? ($country ?: 'Unknown');
    }

    private function clientCloudflareStatus(?string $status): string
    {
        return strtolower((string) $status) === 'active'
            ? 'active'
            : 'pending';
    }
}
