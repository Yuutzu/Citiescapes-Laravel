<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\OtpVerify;
use App\Livewire\Auth\ChangePassword;
use App\Livewire\Public\RoomListings;
// Admin (GM)
use App\Livewire\Admin\Rooms\RoomManager;
use App\Livewire\Admin\Rooms\InquiryManager;
use App\Livewire\Admin\Tenants\TenantManager;
use App\Livewire\Admin\Billing\BillingManager;
use App\Livewire\Admin\Contracts\ContractManager;
use App\Livewire\Admin\Reports\ReportManager;
use App\Livewire\Admin\Settings\AuditLogViewer;
use App\Livewire\Admin\Settings\SystemSettings;
use App\Livewire\Admin\Communications\AnnouncementManager;
use App\Livewire\Admin\Communications\RequestViewer;
use App\Livewire\Admin\Dashboard as AdminDashboard;
// Tenant
use App\Livewire\Tenant\Dashboard as TenantDashboard;
use App\Livewire\Tenant\Profile as TenantProfile;
use App\Livewire\Tenant\ContractView;
use App\Livewire\Tenant\BillingView;
use App\Livewire\Tenant\RequestManager as TenantRequestManager;
use App\Http\Controllers\PatchedLivewireUploadController;

// Override Livewire's upload endpoint with a Windows/Laragon-safe version
// (stops "Path must not be empty" / "failed to upload" on this machine).
Route::post('/livewire/upload-file', [PatchedLivewireUploadController::class, 'handle'])
    ->middleware('web')
    ->name('livewire.upload-file');

/*
|--------------------------------------------------------------------------
| Public Routes (No Auth Required) — SS1 public listing
|--------------------------------------------------------------------------
*/
Route::get('/', RoomListings::class)->name('home');

/*
|--------------------------------------------------------------------------
| Auth Routes — SS6
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

Route::post('/logout', function () {
    $user = auth()->user();
    \App\Models\AuditLog::record('logout', $user?->id, $user?->role, 'SS6', null, request()->ip());
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// OTP verification (authenticated but pending activation)
Route::middleware('auth')->group(function () {
    Route::get('/otp/verify', OtpVerify::class)->name('otp.verify');
    Route::get('/password/change', ChangePassword::class)->name('password.change');
});

/*
|--------------------------------------------------------------------------
| GM Routes — SS1 through SS6
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'activated', 'role:gm'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboard::class)->name('dashboard');

    // SS1 — Room Management
    Route::get('/rooms', RoomManager::class)->name('rooms.index');
    Route::get('/inquiries', InquiryManager::class)->name('inquiries.index');

    // SS2 — Tenant Management
    Route::get('/tenants', TenantManager::class)->name('tenants.index');

    // SS3 — Billing Management
    Route::get('/billing', BillingManager::class)->name('billing.index');

    // SS4 — Contract Management
    Route::get('/contracts', ContractManager::class)->name('contracts.index');

    // SS5 — Reports & Archives
    Route::get('/reports', ReportManager::class)->name('reports.index');

    // SS6 — System Administration
    Route::get('/audit-log', AuditLogViewer::class)->name('audit-log');
    Route::get('/settings', SystemSettings::class)->name('settings');

    // SS7 — Communications
    Route::get('/announcements', AnnouncementManager::class)->name('announcements.index');
    Route::get('/requests', RequestViewer::class)->name('requests.index');
});

/*
|--------------------------------------------------------------------------
| Tenant Routes — Portal views
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'activated', 'role:tenant'])->prefix('tenant')->name('tenant.')->group(function () {
    Route::get('/dashboard', TenantDashboard::class)->name('dashboard');
    Route::get('/profile', TenantProfile::class)->name('profile');
    Route::get('/contract', ContractView::class)->name('contract');
    Route::get('/billing', BillingView::class)->name('billing');
    Route::get('/requests', TenantRequestManager::class)->name('requests');
});
