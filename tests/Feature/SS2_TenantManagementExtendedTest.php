<?php

namespace Tests\Feature;

use App\Livewire\Tenant\Dashboard as TenantDashboard;
use App\Livewire\Tenant\Profile;
use App\Models\AuditLog;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SS2 — Tenant Management (Extended Black-Box)
 *
 * Fills BBT_SS2_TEN_007, 008, 010, 011, 012 + View Tenant Dashboard.
 */
class SS2_TenantManagementExtendedTest extends TestCase
{
    use RefreshDatabase;

    protected User $gm;
    protected User $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gm = User::create([
            'full_name'    => 'Test GM',
            'email'        => 'gm@test.local',
            'password'     => Hash::make('password'),
            'role'         => 'gm',
            'status'       => 'active',
            'activated_at' => now(),
        ]);
        $this->tenant = User::create([
            'full_name'    => 'Tenant One',
            'email'        => 't@test.local',
            'password'     => Hash::make('password'),
            'role'         => 'tenant',
            'status'       => 'active',
            'activated_at' => now(),
        ]);
    }

    /* ── Profile self-service ───────────────────────── */

    #[Test] // BBT_SS2_PRO_HAPPY — tenant updates own profile
    public function tenant_can_update_own_profile_and_audit_is_written(): void
    {
        Livewire::actingAs($this->tenant)->test(Profile::class)
            ->set('full_name', 'Tenant One Renamed')
            ->set('contact_number', '09181112233')
            ->set('address', 'Davao City')
            ->set('emergency_contact', 'Mom 09177779999')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id'             => $this->tenant->id,
            'full_name'      => 'Tenant One Renamed',
            'contact_number' => '09181112233',
            'address'        => 'Davao City',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id'   => $this->tenant->id,
            'action'    => 'profile_updated',
            'subsystem' => 'SS2',
        ]);
    }

    #[Test] // BBT_SS2_PRO_FAIL_EMPTY — full_name required
    public function profile_save_rejects_empty_full_name(): void
    {
        Livewire::actingAs($this->tenant)->test(Profile::class)
            ->set('full_name', '')
            ->call('save')
            ->assertHasErrors(['full_name']);
    }

    #[Test] // BBT_SS2_PRO_BVA_ADDR_256 — address above max 255
    public function profile_save_rejects_address_over_255_chars(): void
    {
        Livewire::actingAs($this->tenant)->test(Profile::class)
            ->set('address', str_repeat('a', 256))
            ->call('save')
            ->assertHasErrors(['address']);
    }

    #[Test] // BBT_SS2_TEN_011 — bell fan-out: self + GMs notified on profile update
    public function profile_update_notifies_self_and_every_active_gm(): void
    {
        // Add 1 more GM (total 2 active)
        User::create([
            'full_name'    => 'Second GM',
            'email'        => 'gm2@test.local',
            'password'     => Hash::make('password'),
            'role'         => 'gm',
            'status'       => 'active',
            'activated_at' => now(),
        ]);

        Livewire::actingAs($this->tenant)->test(Profile::class)
            ->set('full_name', 'Tenant One')
            ->call('save')
            ->assertHasNoErrors();

        // 1 self confirmation + 2 GM notifications = 3 NotificationLog rows of type profile_updated
        $this->assertSame(3, NotificationLog::where('type', 'profile_updated')->count());
        $this->assertSame(1, NotificationLog::where('user_id', $this->tenant->id)->where('type', 'profile_updated')->count());
        $this->assertSame(2, NotificationLog::whereIn('user_id', User::where('role', 'gm')->pluck('id'))->where('type', 'profile_updated')->count());
    }

    #[Test] // BBT_SS2_TEN_012 — fan-out when 0 active GMs: only self bell
    public function profile_update_with_no_active_gms_only_writes_self_bell(): void
    {
        // Archive the only GM
        $this->gm->update(['status' => 'archived']);

        Livewire::actingAs($this->tenant)->test(Profile::class)
            ->set('full_name', 'Tenant One')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(1, NotificationLog::where('type', 'profile_updated')->count());
        $this->assertSame(1, NotificationLog::where('user_id', $this->tenant->id)->where('type', 'profile_updated')->count());
    }

    /* ── Tenant List — pending_activation filter ────── */

    #[Test] // BBT_SS2_TEN_FILTER_PENDING
    public function filter_by_pending_activation_returns_only_pending(): void
    {
        User::create([
            'full_name' => 'Pending Tenant', 'email' => 'pa@x.com',
            'password' => Hash::make('p'), 'role' => 'tenant', 'status' => 'pending_activation',
        ]);

        Livewire::actingAs($this->gm)->test(\App\Livewire\Admin\Tenants\TenantManager::class)
            ->set('filterStatus', 'pending_activation')
            ->assertSee('Pending Tenant')
            ->assertDontSee('Tenant One'); // active tenant in setUp
    }

    /* ── View Tenant Dashboard (new use case) ───────── */

    #[Test] // BBT_SS2_DASH — tenant dashboard renders without errors
    public function tenant_dashboard_renders_for_logged_in_tenant(): void
    {
        Livewire::actingAs($this->tenant)->test(TenantDashboard::class)
            ->assertOk();
    }

    /* ── Audit cross-check ──────────────────────────── */

    #[Test] // BBT_SS2_AUD — multiple SS2 events recorded
    public function multiple_tenant_operations_each_write_one_ss2_audit_row(): void
    {
        // Profile update by tenant
        Livewire::actingAs($this->tenant)->test(Profile::class)
            ->set('full_name', 'Tenant One')
            ->call('save');

        // Archive by GM
        Livewire::actingAs($this->gm)->test(\App\Livewire\Admin\Tenants\TenantManager::class)
            ->call('archiveTenant', $this->tenant->id);

        $rows = AuditLog::where('subsystem', 'SS2')->get();
        $this->assertGreaterThanOrEqual(2, $rows->count(), 'Expected profile_updated and tenant_archived audit rows.');
        $this->assertTrue($rows->contains('action', 'profile_updated'));
        $this->assertTrue($rows->contains('action', 'tenant_archived'));
    }
}
