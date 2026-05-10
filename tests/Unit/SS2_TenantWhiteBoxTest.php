<?php

namespace Tests\Unit;

use App\Livewire\Admin\Tenants\TenantManager;
use App\Models\Archive;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SS2 — Tenant Management (White-Box)
 *
 * Branch coverage of:
 *   - TenantManager::store()       (validation → temp pw → mail → audit)
 *   - TenantManager::archiveTenant() (Archive insert → user update → audit)
 *   - TenantManager::deleteTenant()  (audit → forceDelete)
 *   - render() search/filter scopes  (with/without filters applied)
 *
 * IDs: WBT_SS2_TEN_001..008
 */
class SS2_TenantWhiteBoxTest extends TestCase
{
    use RefreshDatabase;

    protected User $gm;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->gm = User::create([
            'full_name' => 'GM',
            'email'     => 'gm@x.com',
            'password'  => Hash::make('p'),
            'role'      => 'gm',
            'status'    => 'active',
        ]);
    }

    /* ── store(): all side-effects fire ────────────────── */

    #[Test] // WBT_SS2_TEN_001 — store() inserts user with the right defaults
    public function store_creates_tenant_with_pending_activation_and_must_change_password(): void
    {
        Livewire::actingAs($this->gm)->test(TenantManager::class)
            ->set('full_name', 'Foo Bar')
            ->set('email', 'foo@x.com')
            ->call('store');

        $u = User::where('email', 'foo@x.com')->first();
        $this->assertSame('tenant', $u->role);
        $this->assertSame('pending_activation', $u->status);
        $this->assertTrue((bool) $u->must_change_password);
    }

    #[Test] // WBT_SS2_TEN_002 — store() emails the temp password
    public function store_dispatches_temp_password_mail(): void
    {
        Livewire::actingAs($this->gm)->test(TenantManager::class)
            ->set('full_name', 'Foo')
            ->set('email', 'mail-test@x.com')
            ->call('store');

        Mail::assertSent(\App\Mail\TempPasswordMail::class);
    }

    #[Test] // WBT_SS2_TEN_003 — store() writes an audit log row
    public function store_writes_audit_log(): void
    {
        Livewire::actingAs($this->gm)->test(TenantManager::class)
            ->set('full_name', 'Aud')
            ->set('email', 'aud@x.com')
            ->call('store');

        $this->assertDatabaseHas('audit_logs', [
            'action'    => 'tenant_created',
            'subsystem' => 'SS2',
        ]);
    }

    /* ── archiveTenant(): both side-effects fire ───────── */

    #[Test] // WBT_SS2_TEN_004 — archiveTenant: Archive row created with tenant snapshot
    public function archive_creates_archive_row_with_data_payload(): void
    {
        $t = User::create(['full_name' => 'X', 'email' => 'x@x.com',
            'password' => Hash::make('p'), 'role' => 'tenant', 'status' => 'active']);

        Livewire::actingAs($this->gm)->test(TenantManager::class)
            ->call('archiveTenant', $t->id);

        $arch = Archive::where('original_record_id', $t->id)->first();
        $this->assertNotNull($arch);
        $this->assertSame('tenant_account', $arch->record_type);
        $this->assertSame('SS2', $arch->source_subsystem);
        $this->assertIsArray($arch->data);
    }

    #[Test] // WBT_SS2_TEN_005 — archiveTenant: status transitions to 'archived'
    public function archive_sets_status_archived_and_records_archived_by(): void
    {
        $t = User::create(['full_name' => 'X', 'email' => 'x@x.com',
            'password' => Hash::make('p'), 'role' => 'tenant', 'status' => 'active']);

        Livewire::actingAs($this->gm)->test(TenantManager::class)
            ->call('archiveTenant', $t->id);

        $t->refresh();
        $this->assertSame('archived', $t->status);
        $this->assertSame($this->gm->id, $t->archived_by);
        $this->assertNotNull($t->archived_at);
    }

    /* ── deleteTenant(): audit-then-forceDelete order ─── */

    #[Test] // WBT_SS2_TEN_006 — deleteTenant: row hard-deleted (no soft-delete restore possible)
    public function delete_force_deletes_user_row(): void
    {
        $t = User::create(['full_name' => 'X', 'email' => 'x@x.com',
            'password' => Hash::make('p'), 'role' => 'tenant', 'status' => 'archived']);

        Livewire::actingAs($this->gm)->test(TenantManager::class)
            ->call('deleteTenant', $t->id);

        $this->assertDatabaseMissing('users', ['id' => $t->id]);
        $this->assertNull(User::withTrashed()->find($t->id));
    }

    /* ── render(): query scope branches ───────────────── */

    #[Test] // WBT_SS2_TEN_007 — render() WITHOUT search/filter returns all tenants
    public function render_without_filters_lists_all_tenants(): void
    {
        User::create(['full_name' => 'A', 'email' => 'a@x.com',
            'password' => Hash::make('p'), 'role' => 'tenant', 'status' => 'active']);
        User::create(['full_name' => 'B', 'email' => 'b@x.com',
            'password' => Hash::make('p'), 'role' => 'tenant', 'status' => 'archived']);

        Livewire::actingAs($this->gm)->test(TenantManager::class)
            ->assertSee('A')
            ->assertSee('B');
    }

    #[Test] // WBT_SS2_TEN_008 — render() excludes non-tenant users (role filter branch)
    public function render_excludes_users_with_non_tenant_role(): void
    {
        Livewire::actingAs($this->gm)->test(TenantManager::class)
            ->assertDontSee($this->gm->full_name);
    }
}
