<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AdministratorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\MonitoringProfileController;
use App\Http\Controllers\MonitoringScheduleController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login')->name('login.store');
});
Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/reports/runs/{run}', [\App\Http\Controllers\RunReportController::class, 'show'])->name('reports.run');
    Route::resource('sites', SiteController::class)->except('show')->middleware('role:administrator,operator');
    Route::resource('devices', DeviceController::class)->middleware('role:administrator,operator');
    Route::resource('monitoring-profiles', MonitoringProfileController::class)->except('show')->middleware('role:administrator');
    Route::resource('administrators', AdministratorController::class)->except('show')->middleware('role:administrator');
    Route::resource('monitoring-schedules', MonitoringScheduleController::class)->except(['show', 'destroy'])->middleware('role:administrator');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
