<?php

namespace Tests\Feature;

use App\Livewire\Admin\Billing\BillingManager;
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
 * SS3 — Billing Management (Black-Box)
 *
 * Tests BillingManager Livewire actions: recordInitial, generateBill, confirmPayment, saveOverride.
 * Technique: Equivalence Partitioning (EP) + Boundary Value Analysis (BVA).
 *
 * IDs: BBT_SS3_BILL_001..010
 */
class SS3_BillingManagementTest extends TestCase
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
            'role' => 'gm', 'status' => 'active', 'activated_at' => now(),
        ]);

        $this->tenant = User::create([
            'full_name' => 'Tenant', 'email' => 't@x.com', 'password' => Hash::make('p'),
            'role' => 'tenant', 'status' => 'active', 'activated_at' => now(),
        ]);

        $this->room = Room::create([
            'room_number' => '101', 'floor_level' => 1, 'room_type' => 'compact',
            'rate' => 5000, 'max_occupants' => 2, 'status' => 'occupied',
        ]);

        $this->contract = Contract::create([
            'tenant_id' => $this->tenant->id, 'room_id' => $this->room->id,
            'base_rent_rate' => 5000, 'deposit' => 5000, 'first_month_rent' => 5000,
            'room_key_fee' => 200, 'start_date' => now(), 'end_date' => now()->addMonths(6),
            'status' => 'active', 'penalty_rate' => 100,
        ]);
    }

    /* ── recordInitial() ─────────────────────────────────── */

    #[Test] // BBT_SS3_BILL_001 — EP: valid recordInitial creates row, totals math correct
    public function record_initial_with_valid_data_creates_payment(): void
    {
        Livewire::actingAs($this->gm)->test(BillingManager::class)
            ->set('initContractId', $this->contract->id)
            ->set('initDateReceived', now()->toDateString())
            ->set('initPaymentMethod', 'cash')
            ->call('recordInitial')
            ->assertHasNoErrors()
            ->assertSet('showInitial', false);

        $ip = InitialPayment::where('contract_id', $this->contract->id)->first();
        $this->assertNotNull($ip);
        $this->assertSame('10200.00', $ip->total_collected); // 5000 + 5000 + 200
    }

    #[Test] // BBT_SS3_BILL_002 — EP: duplicate initial payment is rejected
    public function record_initial_twice_is_rejected(): void
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

        $this->assertSame(1, InitialPayment::count()); // no second insert
    }

    #[Test] // BBT_SS3_BILL_003 — EP: invalid payment method rejected
    public function record_initial_with_invalid_method_fails_validation(): void
    {
        Livewire::actingAs($this->gm)->test(BillingManager::class)
            ->set('initContractId', $this->contract->id)
            ->set('initDateReceived', now()->toDateString())
            ->set('initPaymentMethod', 'crypto')
            ->call('recordInitial')
            ->assertHasErrors(['initPaymentMethod' => 'in']);
    }

    #[Test] // BBT_SS3_BILL_004 — EP: amenities flow into total_collected
    public function record_initial_with_amenities_includes_them_in_total(): void
    {
        Livewire::actingAs($this->gm)->test(BillingManager::class)
            ->set('initContractId', $this->contract->id)
            ->set('initDateReceived', now()->toDateString())
            ->set('initPaymentMethod', 'cash')
            ->set('initAmenities', [
                ['name' => 'Aircon', 'fee' => 500],
                ['name' => 'TV',     'fee' => 300],
            ])
            ->call('recordInitial');

        $ip = InitialPayment::first();
        $this->assertSame('800.00', $ip->amenities_total);
        $this->assertSame('11000.00', $ip->total_collected); // 10200 + 800
        $this->assertCount(2, $ip->amenities);
    }

    /* ── generateBill() ─────────────────────────────────── */

    #[Test] // BBT_SS3_BILL_005 — EP: generate monthly bill stores correct totals
    public function generate_monthly_bill_creates_row_with_utilities_added(): void
    {
        Livewire::actingAs($this->gm)->test(BillingManager::class)
            ->set('genContractId', $this->contract->id)
            ->set('genElectricity', 1500)
            ->set('genWater', 500)
            ->set('genWifi', 300)
            ->call('generateBill')
            ->assertHasNoErrors();

        $bill = Bill::first();
        $this->assertSame('5000.00', $bill->base_rent);
        $this->assertSame('2300.00', $bill->utilities); // 1500+500+300
        $this->assertSame('7300.00', $bill->total_amount); // 5000 + 2300
    }

    #[Test] // BBT_SS3_BILL_006 — BVA: utility = 0 still creates a bill (lower boundary)
    public function generate_monthly_bill_with_zero_utilities_creates_bill_with_base_rent_only(): void
    {
        Livewire::actingAs($this->gm)->test(BillingManager::class)
            ->set('genContractId', $this->contract->id)
            ->set('genElectricity', 0)
            ->set('genWater', 0)
            ->set('genWifi', 0)
            ->call('generateBill')
            ->assertHasNoErrors();

        $this->assertSame('5000.00', Bill::first()->total_amount);
    }

    #[Test] // BBT_SS3_BILL_007 — BVA: negative utility rejected (just below 0)
    public function generate_monthly_bill_with_negative_utility_fails(): void
    {
        Livewire::actingAs($this->gm)->test(BillingManager::class)
            ->set('genContractId', $this->contract->id)
            ->set('genElectricity', -1)
            ->set('genWater', 0)
            ->set('genWifi', 0)
            ->call('generateBill')
            ->assertHasErrors(['genElectricity' => 'min']);
    }

    /* ── confirmPayment() ───────────────────────────────── */

    #[Test] // BBT_SS3_BILL_008 — EP: confirming payment marks bill paid
    public function confirm_payment_marks_bill_paid_and_creates_payment_row(): void
    {
        $bill = Bill::create([
            'tenant_id' => $this->tenant->id, 'contract_id' => $this->contract->id,
            'room_id' => $this->room->id, 'type' => 'monthly',
            'billing_period' => '2026-05', 'base_rent' => 5000, 'utilities' => 0,
            'total_amount' => 5000, 'due_date' => now()->endOfMonth(), 'status' => 'unpaid',
        ]);

        Livewire::actingAs($this->gm)->test(BillingManager::class)
            ->call('openPayment', $bill->id)
            ->set('payAmount', 5000)
            ->set('payMethod', 'cash')
            ->call('confirmPayment');

        $bill->refresh();
        $this->assertSame('paid', $bill->status);
        $this->assertSame(1, $bill->payments()->count());
    }

    /* ── saveOverride() ─────────────────────────────────── */

    #[Test] // BBT_SS3_BILL_009 — EP: override requires reason
    public function save_override_without_reason_fails(): void
    {
        $bill = $this->createUnpaidBillWithPenalty(500);

        Livewire::actingAs($this->gm)->test(BillingManager::class)
            ->call('openOverride', $bill->id)
            ->set('overrideAmount', 0)
            ->set('overrideReason', '')
            ->call('saveOverride')
            ->assertHasErrors(['overrideReason' => 'required']);
    }

    #[Test] // BBT_SS3_BILL_010 — EP: override of penalty=0 waives it (totals recompute)
    public function save_override_to_zero_recomputes_total_without_penalty(): void
    {
        $bill = $this->createUnpaidBillWithPenalty(500);

        Livewire::actingAs($this->gm)->test(BillingManager::class)
            ->call('openOverride', $bill->id)
            ->set('overrideAmount', 0)
            ->set('overrideReason', 'Hardship — waived')
            ->call('saveOverride');

        $bill->refresh();
        $this->assertSame('0.00', $bill->penalty_amount);
        $this->assertSame('5000.00', $bill->total_amount); // base 5000 + utilities 0 + override 0
    }

    private function createUnpaidBillWithPenalty(float $penalty): Bill
    {
        return Bill::create([
            'tenant_id' => $this->tenant->id, 'contract_id' => $this->contract->id,
            'room_id' => $this->room->id, 'type' => 'monthly',
            'billing_period' => '2026-05', 'base_rent' => 5000, 'utilities' => 0,
            'penalty_amount' => $penalty, 'total_amount' => 5000 + $penalty,
            'due_date' => now()->subDays(10), 'status' => 'overdue',
        ]);
    }
}
