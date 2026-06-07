<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureDashboardAccess
{
    /**
     * Routes SOC Analysts can use inside the dashboard area.
     *
     * @var array<int, string>
     */
    private array $socAnalystRoutes = [
        'dashboard',
        'settings.index',
        'clients.index',
        'clients.show',
        'projects.index',
        'projects.show',
        'projects.realtime',
        'projects.logs',
        'projects.vulnerability.scan',
        'incidents.*',
        'alerts.*',
        'cloudflare.*',
        'audit-logs.*',
        'reports.*',
    ];

    /**
     * Routes client accounts can use inside the dashboard area.
     *
     * @var array<int, string>
     */
    private array $clientRoutes = [
        'client.dashboard',
        'client.projects',
        'client.projects.show',
        'client.incidents',
        'client.alerts',
        'client.reports.*',
        'settings.index',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if (strtolower(trim((string) $user->status)) !== 'active') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Your account is not active yet.',
            ]);
        }

        $role = strtolower(trim((string) $user->role));
        $routeName = (string) $request->route()?->getName();

        if ($role === 'client') {
            foreach ($this->clientRoutes as $allowedRoute) {
                if (Str::is($allowedRoute, $routeName)) {
                    return $next($request);
                }
            }

            if ($routeName === 'dashboard') {
                return redirect()->route('client.dashboard');
            }

            abort(403);
        }

        if ($role !== 'soc analyst') {
            return $next($request);
        }

        foreach ($this->socAnalystRoutes as $allowedRoute) {
            if (Str::is($allowedRoute, $routeName)) {
                return $next($request);
            }
        }

        abort(403);
    }
}
