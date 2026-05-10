<?php

namespace Tests\Unit;

use App\Livewire\Admin\Billing\BillingManager;
use App\Models\Bill;
use App\Models\Contract;
use App\Models\InitialPayment;
use App\Models\PenaltyOverride;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SS3 — Billing Management (White-Box)
 *
 * Branch coverage of BillingManager component logic:
 *   - recordInitial(): duplicate guard, amenities filter (empty-name skip), total math
 *   - saveOverride(): total recompute, PenaltyOverride row insert
 *   - openInitial → updatedInitContractId(): hydrates initAmenities from contract defaults
 *
 * The penalty CASCADE branches (grace/overdue/delinquent/eviction) are already
 * fully covered by tests/Feature/Console/ApplyBillingPenaltiesTest.php.
 *
 * IDs: WBT_SS3_BILL_001..008
 */
class SS3_BillingWhiteBoxTest extends TestCase
{
    use RefreshDatabase;

    protected User $gm;
    protected User $tenant;
    protected Room $room;
    protected Contract $contract;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::create([
            'full_name' => 'GM', 'email' => 'gm@x.com', 'password' => Hash::make('p'),
            'role' => 'gm', 'status' => 'active',
        ]);

        $this->tenant = User::create([
            'full_name' => 'Tenant', 'email' => 't@x.com', 'password' => Hash::make('p'),
            'role' => 'tenant', 'status' => 'active',
        ]);

        $this->room = Room::create([
            'room_number' => '101', 'floor_level' => 1, 'room_type' => 'compact',
            'rate' => 5000, 'max_occupants' => 2, 'status' => 'occupied',
        ]);

        $this->contract = Contract::create([
            'tenant_id' => $this->tenant->id, 'room_id' => $this->room->id,
            'base_rent_rate' => 5000, 'deposit' => 5000, 'first_month_rent' => 5000,
            'room_key_fee' => 200, 'requested_amenities' => [
                ['name' => 'Aircon', 'fee' => 500],
            ],
            'start_date' => now(), 'end_date' => now()->addMonths(6),
            'status' => 'active', 'penalty_rate' => 100,
        ]);
    }

    /* ── recordInitial(): branch coverage ─────────────────── */

    #[Test] // WBT_SS3_BILL_001 — B1: duplicate-payment guard returns early (no second insert)
    public function duplicate_initial_payment_guard_prevents_second_insert(): void
    {
        InitialPayment::create([
            'tenant_id' => $this->tenant->id, 'contract_id' => $this->contract->id,
            'deposit_amount' => 5000, 'first_month_rent' => 5000, 'room_key_fee' => 200,
            'amenities_total' => 0, 'total_collected' => 10200,
            'date_received' => now(), 'payment_method' => 'cash', 'recorded_by' => $this->gm->id,
        ]);

        Livewire::actingAs($this->gm)->test(BillingManager::class)
            ->set('initContractId', $this->contract->id)
            ->set('initDateReceived', now()->toDateString())
            ->set('initPaymentMethod', 'cash')
            ->call('recordInitial');

        $this->assertSame(1, InitialPayment::count());
    }

    #[Test] // WBT_SS3_BILL_002 — B2: amenity rows with empty name are filtered out
    public function amenity_rows_with_blank_name_are_dropped_before_save(): void
    {
        Livewire::actingAs($this->gm)->test(BillingManager::class)
            ->set('initContractId', $this->contract->id)
            ->set('initDateReceived', now()->toDateString())
            ->set('initPaymentMethod', 'cash')
            ->set('initAmenities', [
                ['name' => 'Aircon', 'fee' => 500],
                ['name' => '',       'fee' => 999],   // <- should be dropped
                ['name' => 'TV',     'fee' => 300],
            ])
            ->call('recordInitial');

        $ip = InitialPayment::first();
        $this->assertCount(2, $ip->amenities);                    // not 3
        $this->assertSame('800.00', $ip->amenities_total);        // 500 + 300, not 1799
    }

    #[Test] // WBT_SS3_BILL_003 — B3: total = deposit + first_month + key + amenities (math branch)
    public function total_collected_equals_sum_of_all_components(): void
    {
        Livewire::actingAs($this->gm)->test(BillingManager::class)
            ->set('initContractId', $this->contract->id)
            ->set('initDateReceived', now()->toDateString())
            ->set('initPaymentMethod', 'cash')
            ->set('initAmenities', [['name' => 'Heater', 'fee' => 100]])
            ->call('recordInitial');

        $ip = InitialPayment::first();
        // 5000 (deposit) + 5000 (first month) + 200 (key) + 100 (amenities)
        $this->assertSame('10300.00', $ip->total_collected);
    }

    #[Test] // WBT_SS3_BILL_004 — B4: first_month_rent fallback to base_rent_rate when null
    public function first_month_rent_falls_back_to_base_rent_when_null(): void
    {
        $this->contract->update(['first_month_rent' => null]);

        Livewire::actingAs($this->gm)->test(BillingManager::class)
            ->set('initContractId', $this->contract->id)
            ->set('initDateReceived', now()->toDateString())
            ->set('initPaymentMethod', 'cash')
            ->call('recordInitial');

        $ip = InitialPayment::first();
        $this->assertSame('5000.00', $ip->first_month_rent); // pulled from base_rent_rate
    }

    /* ── updatedInitContractId(): hydrate hook ─────────────── */

    #[Test] // WBT_SS3_BILL_005 — B5: selecting a contract pre-fills initAmenities from its defaults
    public function selecting_contract_prefills_amenities_from_contract(): void
    {
        $component = Livewire::actingAs($this->gm)->test(BillingManager::class)
            ->set('initContractId', $this->contract->id);

        $amenities = $component->get('initAmenities');
        $this->assertCount(1, $amenities);
        $this->assertSame('Aircon', $amenities[0]['name']);
    }

    #[Test] // WBT_SS3_BILL_006 — B6: contract with NULL requested_amenities → empty initAmenities
    public function contract_without_amenities_yields_empty_initAmenities(): void
    {
        $this->contract->update(['requested_amenities' => null]);

        $component = Livewire::actingAs($this->gm)->test(BillingManager::class)
            ->set('initContractId', $this->contract->id);

        $this->assertSame([], $component->get('initAmenities'));
    }

    /* ── saveOverride(): branch coverage ──────────────────── */

    #[Test] // WBT_SS3_BILL_007 — B7: override creates a PenaltyOverride row with correct FK
    public function save_override_creates_penalty_override_row(): void
    {
        $bill = Bill::create([
            'tenant_id' => $this->tenant->id, 'contract_id' => $this->contract->id,
            'room_id' => $this->room->id, 'type' => 'monthly',
            'billing_period' => '2026-05', 'base_rent' => 5000, 'utilities' => 0,
            'penalty_amount' => 1500, 'total_amount' => 6500,
            'due_date' => now()->subDays(10), 'status' => 'overdue',
        ]);

        Livewire::actingAs($this->gm)->test(BillingManager::class)
            ->call('openOverride', $bill->id)
            ->set('overrideAmount', 500)
            ->set('overrideReason', 'Hardship case')
            ->call('saveOverride');

        $override = PenaltyOverride::where('bill_id', $bill->id)->first();
        $this->assertNotNull($override);
        $this->assertSame('1500.00', $override->original_penalty);
        $this->assertSame('500.00',  $override->adjusted_penalty);
        $this->assertSame($this->gm->id, $override->overridden_by);
    }

    #[Test] // WBT_SS3_BILL_008 — B8: override recomputes bill.total_amount
    public function save_override_recomputes_bill_total(): void
    {
        $bill = Bill::create([
            'tenant_id' => $this->tenant->id, 'contract_id' => $this->contract->id,
            'room_id' => $this->room->id, 'type' => 'monthly',
            'billing_period' => '2026-05', 'base_rent' => 5000, 'utilities' => 1000,
            'penalty_amount' => 2000, 'total_amount' => 8000,
            'due_date' => now()->subDays(10), 'status' => 'overdue',
        ]);

        Livewire::actingAs($this->gm)->test(BillingManager::class)
            ->call('openOverride', $bill->id)
            ->set('overrideAmount', 200)
            ->set('overrideReason', 'Reduced')
            ->call('saveOverride');

        // base 5000 + utilities 1000 + new penalty 200 = 6200
        $this->assertSame('6200.00', $bill->fresh()->total_amount);
    }
}
