<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\AgentConnectionController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AutoScanController;
use App\Http\Controllers\CloudflareActionController;
use App\Http\Controllers\CloudflareProjectController;

Route::post('/cloudflare/actions', [CloudflareActionController::class, 'store']);
Route::get('/projects/{project}/cloudflare/analytics', [CloudflareProjectController::class, 'analytics'])
    ->name('projects.cloudflare.analytics');
Route::get('/cloudflare/analytics/{project}', [
    CloudflareProjectController::class,
    'analytics'
]);

Route::post('/agent/verify', [AgentController::class, 'verify']);
Route::post('/agent/connect', [AgentConnectionController::class, 'connect']);
// Route::middleware('throttle:60,1')->post('/agent/heartbeat', [AgentController::class, 'heartbeat']);
Route::post('/agent/heartbeat', [AgentController::class, 'heartbeat']);
Route::post('/agent/audit-log', [AgentController::class, 'auditLog']);
Route::post(
    '/agent/vulnerability/inventory',
    [AgentController::class, 'vulnerabilityInventory']
);  
Route::post('/agent/audit/batch', [AgentController::class, 'auditBatch']);
