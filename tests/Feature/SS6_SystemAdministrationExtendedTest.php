<?php

namespace Tests\Feature;

use App\Livewire\Admin\Settings\AuditLogViewer;
use App\Livewire\Admin\Settings\SystemSettings;
use App\Livewire\Auth\ChangePassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\OtpVerify;
use App\Models\AuditLog;
use App\Models\OtpRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SS6 — System Administration (Extended Black-Box)
 *
 * Covers: logout, OTP resend, change-password flows, lockout notifications,
 * audit log export, system-settings save and validation.
 */
class SS6_SystemAdministrationExtendedTest extends TestCase
{
    use RefreshDatabase;

    protected User $gm;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gm = User::create([
            'full_name' => 'GM', 'email' => 'gm@x.com',
            'password' => Hash::make('correct'), 'role' => 'gm',
            'status' => 'active', 'activated_at' => now(),
        ]);
    }

    /* ── Logout audit ──────────────────────────────── */

    #[Test] // BBT_SS6_LOGOUT
    public function logout_writes_audit_and_clears_session(): void
    {
        $this->actingAs($this->gm)
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertDatabaseHas('audit_logs', [
            'user_id'   => $this->gm->id,
            'action'    => 'logout',
            'subsystem' => 'SS6',
        ]);
        $this->assertGuest();
    }

    /* ── OTP resend ────────────────────────────────── */

    #[Test] // BBT_SS6_OTP_RESEND
    public function resend_otp_creates_new_record_and_flashes_success(): void
    {
        $tenant = User::create([
            'full_name' => 'Pending', 'email' => 'p@x.com',
            'password' => Hash::make('temp'), 'role' => 'tenant', 'status' => 'pending_activation',
        ]);

        Livewire::actingAs($tenant)->test(OtpVerify::class)
            ->call('resend');

        $this->assertSame(1, OtpRecord::where('user_id', $tenant->id)->count());
    }

    /* ── Change password ───────────────────────────── */

    #[Test] // BBT_SS6_PW_HAPPY
    public function change_password_with_correct_current_succeeds(): void
    {
        Livewire::actingAs($this->gm)->test(ChangePassword::class)
            ->set('current_password', 'correct')
            ->set('password', 'NewStrongPass1')
            ->set('password_confirmation', 'NewStrongPass1')
            ->call('save');

        $this->assertTrue(Hash::check('NewStrongPass1', $this->gm->fresh()->password));
        $this->assertFalse((bool) $this->gm->fresh()->must_change_password);
        $this->assertDatabaseHas('audit_logs', [
            'user_id'   => $this->gm->id,
            'action'    => 'password_change',
            'subsystem' => 'SS6',
        ]);
    }

    #[Test] // BBT_SS6_PW_WRONG_CURRENT
    public function change_password_with_wrong_current_fails(): void
    {
        Livewire::actingAs($this->gm)->test(ChangePassword::class)
            ->set('current_password', 'WRONG')
            ->set('password', 'NewStrongPass1')
            ->set('password_confirmation', 'NewStrongPass1')
            ->call('save')
            ->assertHasErrors(['current_password']);

        // Password unchanged
        $this->assertTrue(Hash::check('correct', $this->gm->fresh()->password));
    }

    #[Test] // BBT_SS6_PW_TOO_SHORT — BVA: 7 chars below min 8
    public function change_password_rejects_password_below_8_chars(): void
    {
        Livewire::actingAs($this->gm)->test(ChangePassword::class)
            ->set('current_password', 'correct')
            ->set('password', 'abcdef1') // 7 chars
            ->set('password_confirmation', 'abcdef1')
            ->call('save')
            ->assertHasErrors(['password']);
    }

    /* ── Lockout flow + GM notification ────────────── */

    #[Test] // BBT_SS6_LOCK_NOTIFY — 5 failed attempts locks AND notifies GMs
    public function five_failed_logins_lock_account_and_notify_other_gms(): void
    {
        // Add a second GM to be notified
        $otherGm = User::create([
            'full_name' => 'Other GM', 'email' => 'gm2@x.com',
            'password' => Hash::make('p'), 'role' => 'gm', 'status' => 'active',
        ]);

        $tenant = User::create([
            'full_name' => 'Victim', 'email' => 'v@x.com',
            'password' => Hash::make('correct'), 'role' => 'tenant', 'status' => 'active',
        ]);

        // Drive ALL 5 wrong-password attempts through Livewire::test(Login::class) so the
        // 5th call hits the GM-notification branch (which only fires on the active→locked
        // transition inside Login::login itself).
        for ($i = 0; $i < 5; $i++) {
            Livewire::test(Login::class)
                ->set('email', 'v@x.com')
                ->set('password', 'wrong-password')
                ->call('login');
        }

        $tenant->refresh();
        $this->assertSame('locked', $tenant->status);
        $this->assertNotNull($tenant->locked_until);
        $this->assertTrue($tenant->locked_until->isFuture());

        // Both GMs notified (gm@x.com and gm2@x.com)
        $this->assertSame(2, \App\Models\NotificationLog::where('type', 'account_locked')->count());
    }

    /* ── Auto-unlock on next login attempt ─────────── */

    #[Test] // BBT_SS6_AUTO_UNLOCK
    public function auto_unlock_lets_login_succeed_once_lock_period_passes(): void
    {
        $tenant = User::create([
            'full_name' => 'Unlocked', 'email' => 'u@x.com',
            'password' => Hash::make('correct'), 'role' => 'tenant',
            'status' => 'locked', 'locked_until' => now()->subMinute(),
            'failed_login_attempts' => 5, 'activated_at' => now(),
        ]);

        Livewire::test(Login::class)
            ->set('email', 'u@x.com')
            ->set('password', 'correct')
            ->call('login');

        $fresh = $tenant->fresh();
        $this->assertSame('active', $fresh->status);
        $this->assertNull($fresh->locked_until);
        $this->assertSame(0, $fresh->failed_login_attempts);
    }

    /* ── Audit Log CSV export ──────────────────────── */

    #[Test] // BBT_SS6_AUDIT_CSV_EXPORT
    public function audit_log_csv_export_audits_itself(): void
    {
        AuditLog::record('test_seed', $this->gm->id, 'gm', 'SS1', 'seeded');

        Livewire::actingAs($this->gm)->test(AuditLogViewer::class)
            ->call('exportCsv');

        $this->assertDatabaseHas('audit_logs', [
            'action'    => 'audit_log_exported',
            'subsystem' => 'SS6',
        ]);
    }

    /* ── System Settings save ──────────────────────── */

    #[Test] // BBT_SS6_SETTINGS_HAPPY
    public function valid_system_settings_persist_and_audit(): void
    {
        Livewire::actingAs($this->gm)->test(SystemSettings::class)
            ->set('session_timeout', 60)
            ->set('lockout_threshold', 10)
            ->set('lockout_minutes', 30)
            ->set('otp_expiry', 15)
            ->set('penalty_grace_days', 5)
            ->set('default_penalty_rate', 150)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('audit_logs', [
            'action'    => 'settings_updated',
            'subsystem' => 'SS6',
        ]);
    }

    #[Test] // BBT_SS6_SETTINGS_BVA_THRESHOLD_2
    public function settings_rejects_lockout_threshold_below_3(): void
    {
        Livewire::actingAs($this->gm)->test(SystemSettings::class)
            ->set('lockout_threshold', 2)
            ->call('save')
            ->assertHasErrors(['lockout_threshold']);
    }

    #[Test] // BBT_SS6_SETTINGS_BVA_OTP_120
    public function settings_rejects_otp_expiry_above_60(): void
    {
        Livewire::actingAs($this->gm)->test(SystemSettings::class)
            ->set('otp_expiry', 120)
            ->call('save')
            ->assertHasErrors(['otp_expiry']);
    }
}
