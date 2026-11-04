<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\MonitoringProfileController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login')->name('login.store');
});
Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::resource('sites', SiteController::class)->except('show')->middleware('role:administrator,operator');
    Route::resource('devices', DeviceController::class)->middleware('role:administrator,operator');
    Route::resource('monitoring-profiles', MonitoringProfileController::class)->except('show')->middleware('role:administrator');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
