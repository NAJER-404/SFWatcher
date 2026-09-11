<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\Spectral\DashboardController;
use App\Http\Controllers\Spectral\EquipmentController;
use App\Http\Controllers\Spectral\IncidentController;
use App\Http\Controllers\Spectral\ResourceController;
use App\Http\Controllers\Spectral\WardStationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ECTO//NET — Spectral Resource & Spirit Ward Tech GIS Web Routes
| Scenario 3: Fictional Supernatural Incident & Resource Monitoring System
| Primary Hub: San Francisco, Agusan del Sur, Philippines
|--------------------------------------------------------------------------
*/

// ─── Auth Routes (Guest Only) ─────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',        [AuthController::class, 'showLoginForm'])->name('login');
    Route::get('/warden/login', [AuthController::class, 'showAdminLoginForm'])->name('admin.login');
    Route::get('/register',     [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/login',       [AuthController::class, 'login'])->name('login.submit');
    Route::post('/register',    [AuthController::class, 'register'])->name('register.submit');

    // Google OAuth Authentication
    Route::get('/auth/google',          [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ─── Protected Spectral Routes (Auth Required) ───────────────────────────
Route::middleware('auth')->group(function () {

    // ─── Main GIS Dashboard ────────────────────────────────────────────────
    Route::get('/',         [DashboardController::class, 'index'])->name('spectral.dashboard');
    Route::get('/spectral', [DashboardController::class, 'index'])->name('spectral.index');
    Route::get('/ecto',     fn() => redirect()->route('spectral.dashboard'));

    // ─── Spectral Incident Management ──────────────────────────────────────
    Route::prefix('incidents')->name('spectral.incidents.')->group(function () {
        Route::get('/',            [IncidentController::class, 'index'])->name('index');
        Route::get('/create',      [IncidentController::class, 'create'])->name('create');
        Route::post('/',           [IncidentController::class, 'store'])->name('store');
        Route::get('/{id}',        [IncidentController::class, 'show'])->name('show');
        Route::put('/{id}/status', [IncidentController::class, 'updateStatus'])->name('update-status');
    });

    // ─── Spirit Ward Stations ───────────────────────────────────────────────
    Route::get('/wards',     [WardStationController::class, 'index'])->name('spectral.wards.index');

    // ─── Spectral Resources & Ectoplasmic Energy ───────────────────────────
    Route::get('/resources', [ResourceController::class, 'index'])->name('spectral.resources.index');

    // ─── Supernatural Technology & Equipment ───────────────────────────────
    Route::get('/equipment', [EquipmentController::class, 'index'])->name('spectral.equipment.index');

    // ─── Legacy Classroom CRUD (Preserved) ─────────────────────────────────
    Route::get('/library', [LibraryController::class, 'index']);
    Route::resource('librarys', LibraryController::class);

});
