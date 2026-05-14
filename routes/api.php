<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\AgentConnectionController;
use App\Http\Controllers\AgentController;

Route::post('/agent/verify', [AgentController::class, 'verify']);
Route::post('/agent/connect', [AgentConnectionController::class, 'connect']);
// Route::middleware('throttle:60,1')->post('/agent/heartbeat', [AgentController::class, 'heartbeat']);
Route::post('/agent/heartbeat', [AgentController::class, 'heartbeat']);
Route::post('/agent/audit-log', [AgentController::class, 'auditLog']);
Route::post(
    '/agent/vulnerability/inventory',
    [AgentController::class, 'vulnerabilityInventory']
);
