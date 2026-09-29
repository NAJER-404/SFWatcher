<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\Spectral\DashboardController;
use App\Http\Controllers\Spectral\EquipmentController;
use App\Http\Controllers\Spectral\IncidentController;
use App\Http\Controllers\Spectral\ResourceController;
use App\Http\Controllers\Spectral\WardStationController;
use App\Http\Controllers\Investigator\InvestigatorDashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Responder\ResponderDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ECTO//NET — Spectral Resource & Spirit Ward Tech GIS Web Routes
| Scenario 3: Fictional Supernatural Incident & Resource Monitoring System
| Primary Hub: San Francisco, Agusan del Sur, Philippines
|--------------------------------------------------------------------------
*/

// ─── Reporter / Public Auth Routes (Guest Only) ───────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',        [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login',       [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register',     [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register',    [AuthController::class, 'register'])->name('register.submit');

    // Google OAuth Authentication
    Route::get('/auth/google',          [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

// Admin Authentication (Separate)
Route::get('/admin/login',  [AuthController::class, 'showAdminLoginForm'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'adminLogin'])->name('admin.login.submit');
Route::get('/warden/login', fn() => redirect()->route('admin.login'));

// Investigator Authentication (Separate — Isolated Session & Cookie)
Route::get('/investigator/login',   [AuthController::class, 'showInvestigatorLoginForm'])->name('investigator.login');
Route::post('/investigator/login',  [AuthController::class, 'investigatorLogin'])->name('investigator.login.submit');
Route::post('/investigator/logout', [AuthController::class, 'investigatorLogout'])->name('investigator.logout');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::put('/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('users.role');
    Route::post('/users/{user}/promote', [UserManagementController::class, 'promote'])->name('users.promote');
});

// ─── Protected Investigator Portal Routes ─────────────────────────────────
Route::middleware(['investigator'])->prefix('investigator')->name('investigator.')->group(function () {
    Route::get('/dashboard',             [InvestigatorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/queue',                 [InvestigatorDashboardController::class, 'queue'])->name('queue');
    Route::get('/incidents',             [InvestigatorDashboardController::class, 'incidents'])->name('incidents.index');
    Route::get('/incidents/{id}/review', [InvestigatorDashboardController::class, 'review'])->name('incidents.review');
    Route::put('/incidents/{id}',        [InvestigatorDashboardController::class, 'updateIncident'])->name('incidents.update');
    Route::post('/incidents/{id}/assign-responder', [InvestigatorDashboardController::class, 'assignResponder'])->name('incidents.assign-responder');
    Route::post('/incidents/{id}/assign-responder-ajax', [InvestigatorDashboardController::class, 'assignResponderAjax'])->name('incidents.assign-responder-ajax');
    Route::get('/incidents/{id}/eligible-responders', [InvestigatorDashboardController::class, 'eligibleResponders'])->name('incidents.eligible-responders');
    Route::delete('/incidents/{id}/reject',         [InvestigatorDashboardController::class, 'rejectIncident'])->name('incidents.reject');

    Route::get('/map',                   [InvestigatorDashboardController::class, 'map'])->name('map');
    Route::get('/safe-zones',            [InvestigatorDashboardController::class, 'safeZones'])->name('safe-zones');
    Route::get('/responders',            [InvestigatorDashboardController::class, 'responders'])->name('responders');
    Route::get('/wards',                 [InvestigatorDashboardController::class, 'wards'])->name('wards');
    Route::get('/resources',             [InvestigatorDashboardController::class, 'resources'])->name('resources');
    Route::get('/profile',               [InvestigatorDashboardController::class, 'profile'])->name('profile');
});

Route::get('/responder/login', [AuthController::class, 'showResponderLoginForm'])->name('responder.login');
Route::post('/responder/login', [AuthController::class, 'responderLogin'])->name('responder.login.submit');
Route::post('/responder/logout', [AuthController::class, 'responderLogout'])->name('responder.logout');

Route::middleware('responder')->prefix('responder')->name('responder.')->group(function () {
    Route::get('/dashboard', [ResponderDashboardController::class, 'index'])->name('dashboard');
    Route::get('/assignments/{assignment}', [ResponderDashboardController::class, 'show'])->name('assignments.show');
    Route::get('/response/{incident}', [ResponderDashboardController::class, 'showByIncident'])->name('response.incident');
    Route::post('/assignments/{assignment}/accept', [ResponderDashboardController::class, 'accept'])->name('assignments.accept');
    Route::post('/assignments/{assignment}/start', [ResponderDashboardController::class, 'start'])->name('assignments.start');
    Route::post('/assignments/{assignment}/complete', [ResponderDashboardController::class, 'complete'])->name('assignments.complete');
    Route::post('/assignments/{assignment}/request-support', [ResponderDashboardController::class, 'requestSupport'])->name('assignments.request-support');
    Route::put('/assignments/{assignment}/progress', [ResponderDashboardController::class, 'progress'])->name('assignments.progress');
    Route::match(['get', 'post'], '/assignments/{assignment}/sync', [ResponderDashboardController::class, 'sync'])->name('assignments.sync');
});

// ─── Protected Spectral Routes (Auth Required) ───────────────────────────
Route::middleware('auth')->group(function () {

    // ─── Main GIS Dashboard ────────────────────────────────────────────────
    Route::get('/',           [DashboardController::class, 'index'])->name('spectral.dashboard');
    Route::get('/spectral',   [DashboardController::class, 'index'])->name('spectral.index');
    Route::get('/ecto',       fn() => redirect()->route('spectral.dashboard'));
    Route::get('/my-reports', [DashboardController::class, 'myReports'])->name('spectral.my-reports');
    Route::get('/profile',    fn() => redirect()->route('spectral.dashboard'))->name('spectral.profile');
    Route::post('/notifications/mark-as-read', function (\Illuminate\Http\Request $request) {
        $read = session('read_notifications', []);
        if ($request->boolean('all')) {
            $userId = \Illuminate\Support\Facades\Auth::id();
            $allIds = \App\Models\Incident::where(function ($query) use ($userId) {
                $query->where('reported_by', $userId)
                      ->orWhereHas('evidence', function ($q) use ($userId) {
                          $q->where('uploaded_by', $userId);
                      });
            })->pluck('id')->toArray();
            $read = array_values(array_unique(array_merge($read, $allIds)));
        } elseif ($request->filled('incident_id')) {
            $read[] = (int) $request->input('incident_id');
            $read = array_values(array_unique($read));
        }
        session(['read_notifications' => $read]);

        return response()->json(['success' => true, 'read_notifications' => $read]);
    })->name('spectral.notifications.read');

    // ─── Spectral Incident Management ──────────────────────────────────────
    Route::prefix('incidents')->name('spectral.incidents.')->group(function () {
        Route::get('/',            [IncidentController::class, 'index'])->name('index');
        Route::get('/create',      [IncidentController::class, 'create'])->name('create');
        Route::post('/',           [IncidentController::class, 'store'])->name('store');
        Route::get('/{id}',        [IncidentController::class, 'show'])->name('show');
        Route::put('/{id}/status', [IncidentController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{id}',     [IncidentController::class, 'destroy'])->name('destroy');
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
