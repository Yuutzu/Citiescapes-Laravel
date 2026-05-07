<?php

namespace Tests\Feature\Console;

use App\Models\Archive;
use App\Models\Contract;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * White-Box Testing — Module 5: Auto-Archive Expired Contracts
 *
 * Branch coverage of AutoArchiveExpiredContracts::handle()
 * (app/Console/Commands/AutoArchiveExpiredContracts.php:15-69).
 *
 * Each test case maps to a row in docs/WHITE_BOX_TESTING.md (WBT_ARCH_001..007).
 */
class AutoArchiveExpiredContractsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 5, 7, 12, 0, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function makeTenant(array $overrides = []): User
    {
        return User::create(array_merge([
            'full_name' => 'Tenant '.uniqid(),
            'email'     => 'tenant'.uniqid().'@example.com',
            'password'  => 'secret-pass',
            'role'      => 'tenant',
            'status'    => 'active',
        ], $overrides));
    }

    private function makeRoom(?int $currentTenantId = null, array $overrides = []): Room
    {
        return Room::create(array_merge([
            'room_number'        => '1'.random_int(100, 999),
            'floor_level'        => 1,
            'room_type'          => 'small',
            'rate'               => 10000,
            'max_occupants'      => 2,
            'status'             => $currentTenantId ? 'occupied' : 'available',
            'current_tenant_id'  => $currentTenantId,
        ], $overrides));
    }

    private function makeContract(User $tenant, Room $room, array $overrides = []): Contract
    {
        return Contract::create(array_merge([
            'tenant_id'      => $tenant->id,
            'room_id'        => $room->id,
            'base_rent_rate' => 10000,
            'deposit'        => 10000,
            'start_date'     => '2025-01-01',
            'end_date'       => Carbon::yesterday(),
            'status'         => 'active',
        ], $overrides));
    }

    /* ── WBT_ARCH_001 ─────────────────────────── */

    #[Test]
    public function wbt_arch_001_does_not_archive_active_contract_with_future_end_date(): void
    {
        $tenant = $this->makeTenant();
        $room   = $this->makeRoom($tenant->id);
        $contract = $this->makeContract($tenant, $room, ['end_date' => Carbon::tomorrow()]);

        Artisan::call('contracts:auto-archive');

        $this->assertSame('active', $contract->fresh()->status);
        $this->assertSame(0, Archive::count());
    }

    /* ── WBT_ARCH_002 ─────────────────────────── */

    #[Test]
    public function wbt_arch_002_full_cascade_archives_contract_tenant_and_room(): void
    {
        $tenant = $this->makeTenant();
        $room   = $this->makeRoom($tenant->id);
        $contract = $this->makeContract($tenant, $room);

        Artisan::call('contracts:auto-archive');

        $this->assertSame('expired', $contract->fresh()->status);
        $this->assertSame('archived', $tenant->fresh()->status);
        $this->assertNotNull($tenant->fresh()->archived_at);

        $room->refresh();
        $this->assertNull($room->current_tenant_id);
        $this->assertSame('available', $room->status);

        $this->assertSame(2, Archive::count());
        $this->assertSame(1, Archive::where('record_type', 'contract')->count());
        $this->assertSame(1, Archive::where('record_type', 'tenant_account')->count());
    }

    /* ── WBT_ARCH_003 ─────────────────────────── */

    #[Test]
    public function wbt_arch_003_tenant_block_skipped_when_tenant_orphaned(): void
    {
        $tenant = $this->makeTenant();
        $room   = $this->makeRoom($tenant->id);
        $contract = $this->makeContract($tenant, $room);

        $tenant->delete();

        Artisan::call('contracts:auto-archive');

        $this->assertSame('expired', $contract->fresh()->status);
        $this->assertSame(1, Archive::count());
        $this->assertSame(0, Archive::where('record_type', 'tenant_account')->count());
    }

    /* ── WBT_ARCH_004 ─────────────────────────── */

    #[Test]
    public function wbt_arch_004_tenant_block_skipped_when_tenant_already_archived(): void
    {
        $tenant = $this->makeTenant(['status' => 'archived']);
        $room   = $this->makeRoom();
        $contract = $this->makeContract($tenant, $room);

        Artisan::call('contracts:auto-archive');

        $this->assertSame('expired', $contract->fresh()->status);
        $this->assertSame(1, Archive::count());
        $this->assertSame(0, Archive::where('record_type', 'tenant_account')->count());
    }

    /* ── WBT_ARCH_005 ─────────────────────────── */

    #[Test]
    public function wbt_arch_005_room_block_skipped_when_room_orphaned(): void
    {
        $tenant = $this->makeTenant();
        $room   = $this->makeRoom($tenant->id);
        $contract = $this->makeContract($tenant, $room);

        $room->delete();

        Artisan::call('contracts:auto-archive');

        $this->assertSame('expired', $contract->fresh()->status);
        $this->assertNotNull($room->fresh()->current_tenant_id ?? null);
    }

    /* ── WBT_ARCH_006 ─────────────────────────── */

    #[Test]
    public function wbt_arch_006_room_block_skipped_when_current_tenant_mismatch(): void
    {
        $contractTenant = $this->makeTenant();
        $otherTenant    = $this->makeTenant();
        $room           = $this->makeRoom($otherTenant->id);
        $contract       = $this->makeContract($contractTenant, $room);

        Artisan::call('contracts:auto-archive');

        $this->assertSame('expired', $contract->fresh()->status);
        $room->refresh();
        $this->assertSame($otherTenant->id, $room->current_tenant_id);
        $this->assertSame('occupied', $room->status);
    }

    /* ── WBT_ARCH_007 ─────────────────────────── */

    #[Test]
    public function wbt_arch_007_processes_multiple_contracts_and_outputs_count(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $tenant = $this->makeTenant();
            $room   = $this->makeRoom($tenant->id);
            $this->makeContract($tenant, $room);
        }

        Artisan::call('contracts:auto-archive');
        $output = Artisan::output();

        $this->assertStringContainsString('Auto-archived 3 expired contracts.', $output);
        $this->assertSame(3, Contract::where('status', 'expired')->count());
    }
}
