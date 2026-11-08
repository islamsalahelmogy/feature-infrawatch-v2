<?php

use App\Http\Controllers\Api\WorkerIngestionController;
use Illuminate\Support\Facades\Route;

Route::middleware('worker.token')->prefix('worker')->group(function (): void {
    Route::get('/devices', [WorkerIngestionController::class, 'devices']);
    Route::post('/heartbeats', [WorkerIngestionController::class, 'heartbeat']);
    Route::post('/icmp-results', [WorkerIngestionController::class, 'icmpResult']);
    Route::get('/runs/next', [WorkerIngestionController::class, 'nextRun']);
});
