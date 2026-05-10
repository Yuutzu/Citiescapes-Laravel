<?php

namespace Tests\Feature;

use App\Livewire\Admin\Tenants\TenantManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SS2 — Tenant Management (Black-Box)
 *
 * Tests TenantManager Livewire component (create / archive / delete / search / filter).
 * Technique: Equivalence Partitioning (EP) + Boundary Value Analysis (BVA).
 *
 * IDs: BBT_SS2_TEN_001..010
 */
class SS2_TenantManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $gm;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->gm = User::create([
            'full_name'    => 'Test GM',
            'email'        => 'gm@test.local',
            'password'     => Hash::make('password'),
            'role'         => 'gm',
            'status'       => 'active',
            'activated_at' => now(),
        ]);
    }

    /* ── EP: create tenant ───────────────────────────── */

    #[Test] // BBT_SS2_TEN_001 — EP: valid create
    public function create_tenant_with_valid_fields_succeeds(): void
    {
        Livewire::actingAs($this->gm)->test(TenantManager::class)
            ->set('full_name', 'Juan Santos')
            ->set('email', 'juan@example.com')
            ->call('store')
            ->assertHasNoErrors()
            ->assertSet('showCreate', false);

        $this->assertDatabaseHas('users', [
            'full_name' => 'Juan Santos',
            'email'     => 'juan@example.com',
            'role'      => 'tenant',
            'status'    => 'pending_activation',
        ]);
    }

    #[Test] // BBT_SS2_TEN_002 — EP: duplicate email
    public function create_tenant_with_duplicate_email_fails(): void
    {
        User::create([
            'full_name' => 'Existing',
            'email'     => 'dupe@example.com',
            'password'  => Hash::make('p'),
            'role'      => 'tenant',
            'status'    => 'active',
        ]);

        Livewire::actingAs($this->gm)->test(TenantManager::class)
            ->set('full_name', 'New')
            ->set('email', 'dupe@example.com')
            ->call('store')
            ->assertHasErrors(['email' => 'unique']);
    }

    #[Test] // BBT_SS2_TEN_003 — EP: empty full_name
    public function create_tenant_with_empty_full_name_fails(): void
    {
        Livewire::actingAs($this->gm)->test(TenantManager::class)
            ->set('full_name', '')
            ->set('email', 'a@b.com')
            ->call('store')
            ->assertHasErrors(['full_name' => 'required']);
    }

    #[Test] // BBT_SS2_TEN_004 — EP: malformed email
    public function create_tenant_with_malformed_email_fails(): void
    {
        Livewire::actingAs($this->gm)->test(TenantManager::class)
            ->set('full_name', 'John')
            ->set('email', 'not-an-email')
            ->call('store')
            ->assertHasErrors(['email' => 'email']);
    }

    /* ── BVA: full_name length ─────────────────────── */

    #[Test] // BBT_SS2_TEN_005 — BVA: full_name 100 chars passes
    public function full_name_at_100_chars_passes(): void
    {
        $name = str_repeat('a', 100);
        Livewire::actingAs($this->gm)->test(TenantManager::class)
            ->set('full_name', $name)
            ->set('email', 'a@b.com')
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', ['full_name' => $name]);
    }

    #[Test] // BBT_SS2_TEN_006 — BVA: full_name 101 chars fails
    public function full_name_exceeds_100_chars_fails(): void
    {
        Livewire::actingAs($this->gm)->test(TenantManager::class)
            ->set('full_name', str_repeat('a', 101))
            ->set('email', 'a@b.com')
            ->call('store')
            ->assertHasErrors(['full_name' => 'max']);
    }

    /* ── EP: archive / delete ──────────────────────── */

    #[Test] // BBT_SS2_TEN_007 — EP: archive an active tenant
    public function archive_active_tenant_marks_archived_and_creates_archive_row(): void
    {
        $tenant = User::create([
            'full_name' => 'To Archive',
            'email'     => 't@x.com',
            'password'  => Hash::make('p'),
            'role'      => 'tenant',
            'status'    => 'active',
        ]);

        Livewire::actingAs($this->gm)->test(TenantManager::class)
            ->call('archiveTenant', $tenant->id);

        $this->assertSame('archived', $tenant->fresh()->status);
        $this->assertDatabaseHas('archives', [
            'original_record_id' => $tenant->id,
            'record_type'        => 'tenant_account',
        ]);
    }

    #[Test] // BBT_SS2_TEN_008 — EP: permanent delete removes the row
    public function permanent_delete_removes_tenant_row(): void
    {
        $tenant = User::create([
            'full_name' => 'To Delete',
            'email'     => 'd@x.com',
            'password'  => Hash::make('p'),
            'role'      => 'tenant',
            'status'    => 'archived',
        ]);

        Livewire::actingAs($this->gm)->test(TenantManager::class)
            ->call('deleteTenant', $tenant->id);

        $this->assertDatabaseMissing('users', ['id' => $tenant->id]);
    }

    /* ── EP: search / filter ────────────────────────── */

    #[Test] // BBT_SS2_TEN_009 — EP: search by partial name
    public function search_by_partial_name_returns_matches(): void
    {
        User::create(['full_name' => 'Unique Tenant Name', 'email' => 'u@x.com',
            'password' => Hash::make('p'), 'role' => 'tenant', 'status' => 'active']);
        User::create(['full_name' => 'Another Person', 'email' => 'a@x.com',
            'password' => Hash::make('p'), 'role' => 'tenant', 'status' => 'active']);

        Livewire::actingAs($this->gm)->test(TenantManager::class)
            ->set('search', 'Unique')
            ->assertSee('Unique Tenant Name')
            ->assertDontSee('Another Person');
    }

    #[Test] // BBT_SS2_TEN_010 — EP: filter by status
    public function filter_by_archived_status_returns_only_archived(): void
    {
        User::create(['full_name' => 'Active One', 'email' => 'a@x.com',
            'password' => Hash::make('p'), 'role' => 'tenant', 'status' => 'active']);
        User::create(['full_name' => 'Archived One', 'email' => 'b@x.com',
            'password' => Hash::make('p'), 'role' => 'tenant', 'status' => 'archived']);

        Livewire::actingAs($this->gm)->test(TenantManager::class)
            ->set('filterStatus', 'archived')
            ->assertSee('Archived One')
            ->assertDontSee('Active One');
    }
}
