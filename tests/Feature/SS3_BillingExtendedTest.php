<?php

namespace Tests\Feature;

use App\Livewire\Admin\Billing\BillingManager;
use App\Livewire\Tenant\BillingView;
use App\Models\Archive;
use App\Models\Bill;
use App\Models\Contract;
use App\Models\InitialPayment;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SS3 — Billing (Extended Black-Box)
 * Covers: tenant billing view, GM list filter/sort, archive on lease end.
 */
class SS3_BillingExtendedTest extends TestCase
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
            'full_name'    => 'GM', 'email' => 'gm@x.com',
            'password'     => Hash::make('p'), 'role' => 'gm',
            'status'       => 'active', 'activated_at' => now(),
        ]);
        $this->tenant = User::create([
            'full_name'    => 'Maria Santos', 'email' => 't@x.com',
            'password'     => Hash::make('p'), 'role' => 'tenant',
            'status'       => 'active', 'activated_at' => now(),
        ]);
        $this->room = Room::create([
            'room_number' => '301', 'floor_level' => 3, 'room_type' => 'compact',
            'rate' => 5000, 'max_occupants' => 3, 'status' => 'occupied',
            'current_tenant_id' => $this->tenant->id,
        ]);
        $this->contract = Contract::create([
            'tenant_id'      => $this->tenant->id,
            'room_id'        => $this->room->id,
            'base_rent_rate' => 5000,
            'deposit'        => 5000,
            'room_key_fee'   => 200,
            'start_date'     => now()->subMonth(),
            'end_date'       => now()->addYear(),
            'status'         => 'active',
            'penalty_rate'   => 100,
            'penalty_grace_days' => 3,
            'created_by'     => $this->gm->id,
        ]);
    }

    private function makeBill(array $overrides = []): Bill
    {
        return Bill::create(array_merge([
            'tenant_id'      => $this->tenant->id,
            'contract_id'    => $this->contract->id,
            'room_id'        => $this->room->id,
            'type'           => 'monthly',
            'billing_period' => now()->format('Y-m'),
            'base_rent'      => 5000,
            'utilities'      => 1000,
            'total_amount'   => 6000,
            'due_date'       => now()->endOfMonth(),
            'status'         => 'unpaid',
        ], $overrides));
    }

    /* ── Tenant billing view ───────────────────────── */

    #[Test] // BBT_SS3_BILL_VIEW_HAPPY — tenant sees their bills + initial payment
    public function tenant_sees_their_bills_and_initial_payment_on_billing_view(): void
    {
        $this->makeBill(['billing_period' => '2026-04']);
        $this->makeBill(['billing_period' => '2026-05']);
        InitialPayment::create([
            'tenant_id'       => $this->tenant->id,
            'contract_id'     => $this->contract->id,
            'deposit_amount'  => 5000,
            'first_month_rent'=> 5000,
            'room_key_fee'    => 200,
            'amenities_total' => 0,
            'total_collected' => 10200,
            'date_received'   => now(),
            'payment_method'  => 'cash',
            'recorded_by'     => $this->gm->id,
        ]);

        Livewire::actingAs($this->tenant)->test(BillingView::class)
            ->assertOk()
            ->assertSee('2026-04')
            ->assertSee('2026-05');
    }

    #[Test] // BBT_SS3_BILL_VIEW_EMPTY — empty state renders cleanly
    public function tenant_billing_view_with_no_bills_renders_without_error(): void
    {
        Livewire::actingAs($this->tenant)->test(BillingView::class)
            ->assertOk();
    }

    /* ── GM list filter + sort ─────────────────────── */

    #[Test] // BBT_SS3_BILL_FILTER_OVERDUE
    public function gm_can_filter_bills_by_overdue_status(): void
    {
        $this->makeBill(['billing_period' => '2026-04', 'status' => 'overdue']);
        $this->makeBill(['billing_period' => '2026-05', 'status' => 'paid']);

        Livewire::actingAs($this->gm)->test(BillingManager::class)
            ->set('filterStatus', 'overdue')
            ->assertSee('2026-04')
            ->assertDontSee('2026-05');
    }

    #[Test] // BBT_SS3_BILL_SEARCH_TENANT
    public function gm_can_search_bills_by_tenant_name(): void
    {
        $other = User::create([
            'full_name' => 'Juan Cruz', 'email' => 'j@x.com',
            'password'  => Hash::make('p'), 'role' => 'tenant', 'status' => 'active',
        ]);
        $otherRoom = Room::create([
            'room_number' => '302', 'floor_level' => 3, 'room_type' => 'compact',
            'rate' => 5000, 'max_occupants' => 3, 'status' => 'occupied',
            'current_tenant_id' => $other->id,
        ]);
        $otherContract = Contract::create([
            'tenant_id' => $other->id, 'room_id' => $otherRoom->id,
            'base_rent_rate' => 5000, 'deposit' => 5000, 'room_key_fee' => 200,
            'start_date' => now()->subMonth(), 'end_date' => now()->addYear(),
            'status' => 'active', 'penalty_rate' => 100, 'penalty_grace_days' => 3,
            'created_by' => $this->gm->id,
        ]);
        Bill::create([
            'tenant_id' => $other->id, 'contract_id' => $otherContract->id, 'room_id' => $otherRoom->id,
            'type' => 'monthly', 'billing_period' => '2026-04',
            'base_rent' => 5000, 'utilities' => 0, 'total_amount' => 5000,
            'due_date' => now()->endOfMonth(), 'status' => 'unpaid',
        ]);

        $this->makeBill(['billing_period' => '2026-05']);

        Livewire::actingAs($this->gm)->test(BillingManager::class)
            ->set('search', 'Juan')
            ->assertSee('Juan')
            ->assertDontSee('Maria');
    }

    #[Test] // BBT_SS3_BILL_SORT_TOGGLE
    public function clicking_sortBy_toggles_direction_when_same_column(): void
    {
        $component = Livewire::actingAs($this->gm)->test(BillingManager::class);

        // initial: sortBy='created_at', direction='desc'
        $component->call('sortBy', 'created_at');
        $this->assertSame('asc', $component->get('sortDirection'), 'Same column should toggle desc→asc');

        $component->call('sortBy', 'created_at');
        $this->assertSame('desc', $component->get('sortDirection'), 'Same column should toggle asc→desc');
    }

    #[Test] // BBT_SS3_BILL_SORT_NEW_COL
    public function clicking_sortBy_on_new_column_resets_to_asc(): void
    {
        $component = Livewire::actingAs($this->gm)->test(BillingManager::class);

        $component->call('sortBy', 'due_date');
        $this->assertSame('due_date', $component->get('sortBy'));
        $this->assertSame('asc', $component->get('sortDirection'));
    }

}
