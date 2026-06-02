<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;
use App\Models\Projects;
use App\Models\ProjectAgent;
use App\Models\ReportRequest;
use App\Models\Alert;
use App\Models\Incident;
use App\Models\SiteVulnerability;
use App\Models\SiteInventory;
use App\Models\AgentLog;
use App\Models\HealthReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use App\Support\ProjectSecurityScore;
use Throwable;

class GlobalReportController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'type' => ['required', 'in:global_security_report'],
            'period' => ['required', 'in:last_7_days,last_30_days,last_90_days,this_month,last_quarter'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $from = match ($request->period) {
            'last_7_days' => now()->subDays(7),
            'last_30_days' => now()->subDays(30),
            'last_90_days' => now()->subDays(90),
            'this_month' => now()->startOfMonth(),
            'last_quarter' => now()->subMonths(3),
            default => now()->subDays(30),
        };

        $projects = Projects::with('client')->latest()->get();
        $projectMap = $projects->keyBy(fn ($project) => (int) $project->id);
        $projectAgents = ProjectAgent::with(['project', 'agent'])->latest()->get();

        try {
            $alerts = Alert::where('detected_at', '>=', $from)->orderBy('detected_at', 'desc')->get();
            $incidents = Incident::where('event_created_at', '>=', $from)->orderBy('event_created_at', 'desc')->get();
            $vulnerabilities = SiteVulnerability::where('detected_at', '>=', $from)->orderBy('detected_at', 'desc')->get();
            $inventories = SiteInventory::orderBy('collected_at', 'desc')->get();
            $agentLogs = AgentLog::where('event_created_at', '>=', $from)->orderBy('event_created_at', 'desc')->limit(50)->get();
            $healthReports = HealthReport::where('event_created_at', '>=', $from)->orderBy('event_created_at', 'desc')->limit(30)->get();
            $mongoError = null;
        } catch (Throwable $e) {
            $alerts = collect();
            $incidents = collect();
            $vulnerabilities = collect();
            $inventories = collect();
            $agentLogs = collect();
            $healthReports = collect();
            $mongoError = $e->getMessage();
        }

        $stats = [
            'projects' => $projects->count(),
            'connected_projects' => $projects->where('is_connected', true)->count(),
            'offline_projects' => $projects->where('is_connected', false)->count(),

            'alerts' => $alerts->count(),
            'critical_alerts' => $alerts->where('severity', 'critical')->count(),
            'high_alerts' => $alerts->where('severity', 'high')->count(),
            'medium_alerts' => $alerts->where('severity', 'medium')->count(),
            'low_alerts' => $alerts->where('severity', 'low')->count(),

            'incidents' => $incidents->count(),
            'open_incidents' => $incidents->where('status', 'open')->count(),

            'vulnerabilities' => $vulnerabilities->count(),
            'open_vulnerabilities' => $vulnerabilities->where('status', 'open')->count(),
            'critical_vulnerabilities' => $vulnerabilities->where('severity', 'critical')->count(),
            'high_vulnerabilities' => $vulnerabilities->where('severity', 'high')->count(),

            'inventories' => $inventories->count(),
            'agents' => $projectAgents->count(),
            'online_agents' => $projectAgents->where('status', 'online')->count(),
            'offline_agents' => $projectAgents->where('status', 'offline')->count(),
            'health_reports' => $healthReports->count(),
        ];

        $topRiskProjects = $projects->map(function ($project) use ($alerts, $incidents, $vulnerabilities, $healthReports) {
            $projectId = (int) $project->id;

            $projectAlerts = $alerts->where('project_id', $projectId);
            $projectIncidents = $incidents->where('project_id', $projectId);
            $projectVulns = $vulnerabilities->where('project_id', $projectId);
            $score = ProjectSecurityScore::forProject($project, $alerts, $incidents, $vulnerabilities, $healthReports);

            $project->soc_score = $score['security_score'];
            $project->soc_risk_score = $score['risk_score'];
            $project->soc_risk = $score['risk_label'];
            $project->soc_score_source = $score['source'];
            $project->alerts_count = $projectAlerts->count();
            $project->incidents_count = $projectIncidents->count();
            $project->vulnerabilities_count = $projectVulns->count();

            return $project;
        })->sortByDesc('soc_risk_score')->take(10)->values();

        $html = view('pdf.soc-report', [
            'period' => $request->period,
            'from' => $from->format('Y-m-d'),
            'to' => now()->format('Y-m-d'),
            'generatedAt' => now()->format('Y-m-d H:i'),
            'note' => $request->note,
            'mongoError' => $mongoError,

            'stats' => $stats,
            'projects' => $projects,
            'projectMap' => $projectMap,
            'topRiskProjects' => $topRiskProjects,
            'alerts' => $alerts,
            'incidents' => $incidents,
            'vulnerabilities' => $vulnerabilities,
            'inventories' => $inventories,
            'agentLogs' => $agentLogs,
            'healthReports' => $healthReports,
            'projectAgents' => $projectAgents,
        ])->render();

        $fileName = 'global-soc-report-' . now()->format('Y-m-d-His') . '.pdf';
        $pdfPath = storage_path('app/public/' . $fileName);

        $reportRequest = ReportRequest::create([
            'client_id' => null,
            'user_id' => $request->user()?->id,
            'type' => 'global_security_report',
            'period' => $request->period,
            'status' => 'in_progress',
            'note' => $request->note,
            'requested_at' => now(),
        ]);

        try {
            File::ensureDirectoryExists(dirname($pdfPath));
            $this->savePdf($html, $pdfPath);
            $reportRequest->update(['status' => 'ready']);
        } catch (Throwable) {
            $reportRequest->update(['status' => 'failed']);

            return back()->withErrors([
                'report' => 'Unable to generate the SOC PDF report. Check that Chrome, Node, or DomPDF is available.',
            ]);
        }

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    private function savePdf(string $html, string $pdfPath): void
    {
        try {
            $chromePath = config('services.browsershot.chrome_path', 'C:/Program Files/Google/Chrome/Application/chrome.exe');
            $browserShot = Browsershot::html($html)
                ->showBackground()
                ->format('A4')
                ->margins(0, 0, 0, 0)
                ->waitUntilNetworkIdle();

            if (filled($chromePath) && file_exists($chromePath)) {
                $browserShot->setChromePath($chromePath);
            }

            $browserShot->savePdf($pdfPath);
        } catch (Throwable) {
            Pdf::loadHTML($html)
                ->setPaper('a4')
                ->save($pdfPath);
        }
    }
}
