<?php

namespace Tests\Feature;

use App\Livewire\Admin\Contracts\ContractManager;
use App\Livewire\Tenant\ContractView;
use App\Models\Archive;
use App\Models\AuditLog;
use App\Models\Contract;
use App\Models\NotificationLog;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SS4 — Contract (Extended Black-Box)
 * Covers: ack flows, list filter, edit draft, renew, 30/7-day warnings (cron).
 */
class SS4_ContractExtendedTest extends TestCase
{
    use RefreshDatabase;

    protected User $gm;
    protected User $tenant;
    protected Room $room;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gm = User::create([
            'full_name' => 'GM', 'email' => 'gm@x.com',
            'password' => Hash::make('p'), 'role' => 'gm',
            'status' => 'active', 'activated_at' => now(),
        ]);
        $this->tenant = User::create([
            'full_name' => 'Tenant', 'email' => 't@x.com',
            'password' => Hash::make('p'), 'role' => 'tenant',
            'status' => 'active', 'activated_at' => now(),
        ]);
        $this->room = Room::create([
            'room_number' => '401', 'floor_level' => 4, 'room_type' => 'compact',
            'rate' => 4500, 'max_occupants' => 3, 'status' => 'available',
        ]);
    }

    private function makeContract(array $overrides = []): Contract
    {
        return Contract::create(array_merge([
            'tenant_id'      => $this->tenant->id,
            'room_id'        => $this->room->id,
            'base_rent_rate' => 4500,
            'deposit'        => 4500,
            'room_key_fee'   => 200,
            'start_date'     => now()->subDay(),
            'end_date'       => now()->addYear(),
            'status'         => 'draft',
            'penalty_rate'   => 100,
            'penalty_grace_days' => 3,
            'created_by'     => $this->gm->id,
        ], $overrides));
    }

    /* ── Tenant Ack Step 1 / 2 ─────────────────────── */

    #[Test] // BBT_SS4_ACK1
    public function tenant_can_acknowledge_step_1(): void
    {
        $c = $this->makeContract();

        Livewire::actingAs($this->tenant)->test(ContractView::class)
            ->call('acknowledgeStep1');

        $this->assertNotNull($c->fresh()->step1_acknowledged_at);
        $this->assertDatabaseHas('audit_logs', [
            'action'    => 'contract_step1_ack',
            'subsystem' => 'SS4',
        ]);
    }

    #[Test] // BBT_SS4_ACK1_IDEMPOTENT — re-clicking does not overwrite
    public function acknowledging_step_1_twice_keeps_first_timestamp(): void
    {
        $c = $this->makeContract(['step1_acknowledged_at' => now()->subHour()]);
        $firstTs = $c->fresh()->step1_acknowledged_at;

        Livewire::actingAs($this->tenant)->test(ContractView::class)
            ->call('acknowledgeStep1');

        $this->assertEquals(
            $firstTs->toDateTimeString(),
            $c->fresh()->step1_acknowledged_at->toDateTimeString(),
            'Step 1 timestamp must not be overwritten.'
        );
    }

    #[Test] // BBT_SS4_ACK2_HAPPY — step 1 acked + step 2 activates
    public function tenant_step_2_ack_activates_contract(): void
    {
        $c = $this->makeContract(['step1_acknowledged_at' => now()->subMinute()]);

        Livewire::actingAs($this->tenant)->test(ContractView::class)
            ->call('acknowledgeStep2');

        $fresh = $c->fresh();
        $this->assertNotNull($fresh->step2_acknowledged_at);
        $this->assertSame('active', $fresh->status);
        $this->assertNotNull($fresh->activated_at);
    }

    #[Test] // BBT_SS4_ACK2_BLOCKED — step 1 not acked → no-op
    public function step_2_ack_is_blocked_when_step_1_not_done(): void
    {
        $c = $this->makeContract();

        Livewire::actingAs($this->tenant)->test(ContractView::class)
            ->call('acknowledgeStep2');

        $fresh = $c->fresh();
        $this->assertNull($fresh->step2_acknowledged_at);
        $this->assertSame('draft', $fresh->status);
    }

    /* ── GM list filter ────────────────────────────── */

    #[Test] // BBT_SS4_CONT_FILTER_STATUS
    public function gm_can_filter_contracts_by_status(): void
    {
        $this->makeContract(['status' => 'draft']);
        // active needs a different tenant or room? room_id can be same for testing
        $other = User::create([
            'full_name' => 'Other Tenant', 'email' => 'o@x.com',
            'password' => Hash::make('p'), 'role' => 'tenant', 'status' => 'active',
        ]);
        Contract::create([
            'tenant_id' => $other->id, 'room_id' => $this->room->id,
            'base_rent_rate' => 4500, 'deposit' => 4500, 'room_key_fee' => 200,
            'start_date' => now()->subMonth(), 'end_date' => now()->addYear(),
            'status' => 'active', 'penalty_rate' => 100, 'penalty_grace_days' => 3,
            'created_by' => $this->gm->id,
        ]);

        Livewire::actingAs($this->gm)->test(ContractManager::class)
            ->set('filterStatus', 'active')
            ->assertSee('Other Tenant')
            ->assertDontSee('Tenant Tenant');
    }

    /* ── Renew ─────────────────────────────────────── */

    #[Test] // BBT_SS4_RENEW — old archived, new draft prefilled
    public function renew_archives_old_and_opens_new_draft(): void
    {
        $c = $this->makeContract(['status' => 'active', 'end_date' => now()]);

        Livewire::actingAs($this->gm)->test(ContractManager::class)
            ->call('renew', $c->id);

        $this->assertSame('expired', $c->fresh()->status);
        $this->assertDatabaseHas('archives', [
            'record_type'      => 'contract',
            'source_subsystem' => 'SS4',
            'original_record_id' => $c->id,
        ]);
    }

    /* ── Expiry warnings cron ──────────────────────── */

    #[Test] // BBT_SS4_WARN_30
    public function cron_sends_30_day_warning_once(): void
    {
        $c = Contract::create([
            'tenant_id' => $this->tenant->id, 'room_id' => $this->room->id,
            'base_rent_rate' => 4500, 'deposit' => 4500, 'room_key_fee' => 200,
            'start_date' => now()->subMonths(11), 'end_date' => now()->addDays(20),
            'status' => 'active', 'penalty_rate' => 100, 'penalty_grace_days' => 3,
            'warning_30_sent' => false, 'warning_7_sent' => false,
            'created_by' => $this->gm->id,
        ]);

        $this->artisan('contracts:send-warnings')->assertSuccessful();

        $this->assertTrue($c->fresh()->warning_30_sent);
        $this->assertSame(1, NotificationLog::where('user_id', $this->tenant->id)->where('type', '30_day_warning')->count());

        // Re-run: no duplicate
        $this->artisan('contracts:send-warnings')->assertSuccessful();
        $this->assertSame(1, NotificationLog::where('user_id', $this->tenant->id)->where('type', '30_day_warning')->count());
    }

    #[Test] // BBT_SS4_WARN_7
    public function cron_sends_7_day_warning_once(): void
    {
        $c = Contract::create([
            'tenant_id' => $this->tenant->id, 'room_id' => $this->room->id,
            'base_rent_rate' => 4500, 'deposit' => 4500, 'room_key_fee' => 200,
            'start_date' => now()->subMonths(11), 'end_date' => now()->addDays(5),
            'status' => 'active', 'penalty_rate' => 100, 'penalty_grace_days' => 3,
            'warning_30_sent' => true, 'warning_7_sent' => false,
            'created_by' => $this->gm->id,
        ]);

        $this->artisan('contracts:send-warnings')->assertSuccessful();

        $this->assertTrue($c->fresh()->warning_7_sent);
        $this->assertSame(1, NotificationLog::where('user_id', $this->tenant->id)->where('type', '7_day_warning')->count());
    }
}
