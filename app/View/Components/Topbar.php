<?php

namespace App\View\Components;

use App\Models\Alert;
use App\Models\clients;
use App\Models\Projects;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\View\Component;

class Topbar extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $user = auth()->user();
        $isClient = $user !== null && strtolower(trim((string) $user->role)) === 'client';
        $clientProjectIds = null;

        if ($isClient) {
            $client = clients::query()
                ->where('user_id', $user->id)
                ->first();

            $clientProjectIds = $client
                ? $client->projects()->pluck('id')->map(fn ($id) => (int) $id)->values()
                : collect();
        }

        $alerts = collect(rescue(
            function () use ($clientProjectIds) {
                $query = Alert::query()
                    ->where('resolved', false);

                if ($clientProjectIds !== null) {
                    if ($clientProjectIds->isEmpty()) {
                        return collect();
                    }

                    $query->whereIn('project_id', $clientProjectIds);
                }

                return $query
                    ->orderBy('detected_at', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get();
            },
            collect(),
            false
        ));

        $projectsById = Projects::query()
            ->whereIn('id', $alerts->pluck('project_id')->filter()->map(fn ($id) => (int) $id)->unique())
            ->get(['id', 'name', 'domain'])
            ->keyBy('id');

        $recentAlerts = $alerts->map(function (Alert $alert) use ($projectsById) {
            $project = $projectsById->get((int) ($alert->project_id ?? 0));
            $detectedAt = rescue(
                fn () => filled($alert->detected_at ?? null) ? Carbon::parse($alert->detected_at) : null,
                null,
                false
            ) ?? rescue(
                fn () => filled($alert->created_at ?? null) ? Carbon::parse($alert->created_at) : null,
                null,
                false
            );

            return [
                'id' => (string) $alert->getKey(),
                'title' => $alert->title ?: 'Security alert detected',
                'severity' => strtolower((string) ($alert->severity ?? 'medium')),
                'project' => $project?->domain ?: $project?->name ?: 'Unknown project',
                'score' => (int) ($alert->ai_score ?? 0),
                'time' => $detectedAt ? $detectedAt->diffForHumans() : 'Recently',
            ];
        });

        $openAlertsCount = rescue(
            function () use ($clientProjectIds) {
                $query = Alert::query()->where('resolved', false);

                if ($clientProjectIds !== null) {
                    if ($clientProjectIds->isEmpty()) {
                        return 0;
                    }

                    $query->whereIn('project_id', $clientProjectIds);
                }

                return $query->count();
            },
            $recentAlerts->count(),
            false
        );

        return view('components.topbar', compact('openAlertsCount', 'recentAlerts'));
    }
}
