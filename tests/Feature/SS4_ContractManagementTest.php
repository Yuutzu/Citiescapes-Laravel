<?php

namespace Tests\Feature;

use App\Livewire\Admin\Contracts\ContractManager;
use App\Models\Contract;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SS4 — Contract Management (Black-Box)
 *
 * Tests ContractManager Livewire actions: save (create/edit), activate, terminate, renew.
 * Technique: Equivalence Partitioning (EP) + Boundary Value Analysis (BVA).
 *
 * IDs: BBT_SS4_CONT_001..009
 */
class SS4_ContractManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $gm;
    protected User $tenant;
    protected Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::create([
            'full_name' => 'GM', 'email' => 'gm@x.com', 'password' => Hash::make('p'),
            'role' => 'gm', 'status' => 'active', 'activated_at' => now(),
        ]);

        $this->tenant = User::create([
            'full_name' => 'Tenant', 'email' => 't@x.com', 'password' => Hash::make('p'),
            'role' => 'tenant', 'status' => 'active', 'activated_at' => now(),
        ]);

        $this->room = Room::create([
            'room_number' => '101', 'floor_level' => 1, 'room_type' => 'compact',
            'rate' => 5000, 'max_occupants' => 2, 'status' => 'available',
        ]);
    }

    /* ── EP: create / save ─────────────────────────── */

    #[Test] // BBT_SS4_CONT_001 — EP: valid create stores draft
    public function valid_create_stores_draft_contract(): void
    {
        Livewire::actingAs($this->gm)->test(ContractManager::class)
            ->set('tenant_id', $this->tenant->id)
            ->set('room_id', $this->room->id)
            ->set('base_rent_rate', 5000)
            ->set('deposit', 5000)
            ->set('room_key_fee', 200)
            ->set('start_date', now()->toDateString())
            ->set('end_date', now()->addYear()->toDateString())
            ->set('penalty_rate', 100)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contracts', [
            'tenant_id' => $this->tenant->id, 'room_id' => $this->room->id, 'status' => 'draft',
        ]);
    }

    #[Test] // BBT_SS4_CONT_002 — EP: missing tenant fails
    public function missing_tenant_fails_validation(): void
    {
        Livewire::actingAs($this->gm)->test(ContractManager::class)
            ->set('room_id', $this->room->id)
            ->set('base_rent_rate', 5000)
            ->set('start_date', now()->toDateString())
            ->set('end_date', now()->addYear()->toDateString())
            ->set('penalty_rate', 100)
            ->call('save')
            ->assertHasErrors(['tenant_id' => 'required']);
    }

    /* ── BVA: end_date relative to start_date ──────── */

    #[Test] // BBT_SS4_CONT_003 — BVA: end_date == start_date fails (must be after)
    public function end_date_equal_start_date_fails(): void
    {
        $today = now()->toDateString();
        Livewire::actingAs($this->gm)->test(ContractManager::class)
            ->set('tenant_id', $this->tenant->id)
            ->set('room_id', $this->room->id)
            ->set('base_rent_rate', 5000)
            ->set('start_date', $today)
            ->set('end_date', $today)
            ->set('penalty_rate', 100)
            ->call('save')
            ->assertHasErrors(['end_date' => 'after']);
    }

    #[Test] // BBT_SS4_CONT_004 — BVA: end_date = start_date + 1 day passes
    public function end_date_one_day_after_start_passes(): void
    {
        Livewire::actingAs($this->gm)->test(ContractManager::class)
            ->set('tenant_id', $this->tenant->id)
            ->set('room_id', $this->room->id)
            ->set('base_rent_rate', 5000)
            ->set('start_date', now()->toDateString())
            ->set('end_date', now()->addDay()->toDateString())
            ->set('penalty_rate', 100)
            ->call('save')
            ->assertHasNoErrors();
    }

    /* ── EP: amount validations ────────────────────── */

    #[Test] // BBT_SS4_CONT_005 — EP: negative base_rent fails
    public function negative_base_rent_fails(): void
    {
        Livewire::actingAs($this->gm)->test(ContractManager::class)
            ->set('tenant_id', $this->tenant->id)
            ->set('room_id', $this->room->id)
            ->set('base_rent_rate', -1)
            ->set('start_date', now()->toDateString())
            ->set('end_date', now()->addYear()->toDateString())
            ->set('penalty_rate', 100)
            ->call('save')
            ->assertHasErrors(['base_rent_rate' => 'min']);
    }

    /* ── EP: state transitions ─────────────────────── */

    #[Test] // BBT_SS4_CONT_006 — EP: activate moves draft→active and occupies the room
    public function activate_moves_draft_to_active_and_room_to_occupied(): void
    {
        $c = $this->makeDraftContract();

        Livewire::actingAs($this->gm)->test(ContractManager::class)
            ->call('activate', $c->id);

        $this->assertSame('active', $c->fresh()->status);
        $this->assertSame('occupied', $this->room->fresh()->status);
        $this->assertSame($this->tenant->id, $this->room->fresh()->current_tenant_id);
    }

    #[Test] // BBT_SS4_CONT_007 — EP: terminate requires reason
    public function terminate_requires_reason(): void
    {
        $c = $this->makeDraftContract();

        Livewire::actingAs($this->gm)->test(ContractManager::class)
            ->call('openTerminate', $c->id)
            ->set('terminateReason', '')
            ->call('terminate')
            ->assertHasErrors(['terminateReason' => 'required']);
    }

    #[Test] // BBT_SS4_CONT_008 — EP: terminate with reason archives + frees room
    public function terminate_with_reason_marks_terminated_and_frees_room(): void
    {
        $c = $this->makeDraftContract(['status' => 'active']);
        $this->room->update(['status' => 'occupied', 'current_tenant_id' => $this->tenant->id]);

        Livewire::actingAs($this->gm)->test(ContractManager::class)
            ->call('openTerminate', $c->id)
            ->set('terminateReason', 'Tenant moved out early')
            ->call('terminate');

        $c->refresh();
        $this->assertSame('terminated', $c->status);
        $this->assertNotNull($c->terminated_at);
        $this->assertSame('available', $this->room->fresh()->status);
        $this->assertNull($this->room->fresh()->current_tenant_id);
    }

    #[Test] // BBT_SS4_CONT_009 — EP: amenity rows with empty names dropped on save
    public function save_drops_amenity_rows_with_blank_names(): void
    {
        Livewire::actingAs($this->gm)->test(ContractManager::class)
            ->set('tenant_id', $this->tenant->id)
            ->set('room_id', $this->room->id)
            ->set('base_rent_rate', 5000)
            ->set('start_date', now()->toDateString())
            ->set('end_date', now()->addYear()->toDateString())
            ->set('penalty_rate', 100)
            ->set('amenities', [
                ['name' => 'Aircon', 'fee' => 500],
                ['name' => '',       'fee' => 999],   // dropped
            ])
            ->call('save');

        $c = Contract::first();
        $this->assertCount(1, $c->requested_amenities);
        $this->assertSame('Aircon', $c->requested_amenities[0]['name']);
    }

    private function makeDraftContract(array $overrides = []): Contract
    {
        return Contract::create(array_merge([
            'tenant_id' => $this->tenant->id, 'room_id' => $this->room->id,
            'base_rent_rate' => 5000, 'deposit' => 5000, 'room_key_fee' => 200,
            'start_date' => now(), 'end_date' => now()->addYear(),
            'status' => 'draft', 'penalty_rate' => 100,
        ], $overrides));
    }
}
