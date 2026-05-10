<?php

namespace Tests\Feature\Console;

use App\Models\Bill;
use App\Models\Contract;
use App\Models\NotificationLog;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * White-Box Testing — Module 1: Billing Penalty Cascade
 *
 * Branch coverage of ApplyBillingPenalties::handle()
 * (app/Console/Commands/ApplyBillingPenalties.php:14-91).
 *
 * Each test case maps to a row in docs/WHITE_BOX_TESTING.md (WBT_BILL_001..014).
 */
class ApplyBillingPenaltiesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 5, 8, 12, 0, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /* ── Seeding helpers ───────────────────────── */

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

    private function makeRoom(?int $currentTenantId = null): Room
    {
        return Room::create([
            'room_number'        => '1'.random_int(100, 999),
            'floor_level'        => 1,
            'room_type'          => 'compact',
            'rate'               => 10000,
            'max_occupants'      => 2,
            'status'             => $currentTenantId ? 'occupied' : 'available',
            'current_tenant_id'  => $currentTenantId,
        ]);
    }

    private function makeContract(User $tenant, Room $room, array $overrides = []): Contract
    {
        return Contract::create(array_merge([
            'tenant_id'      => $tenant->id,
            'room_id'        => $room->id,
            'base_rent_rate' => 10000,
            'deposit'        => 10000,
            'start_date'     => '2026-01-01',
            'end_date'       => '2026-12-31',
            'status'         => 'active',
        ], $overrides));
    }

    /**
     * Seed a Bill whose due_date sits exactly $daysOverdue before the frozen "now".
     */
    private function makeBill(int $daysOverdue, array $billOverrides = [], array $contractOverrides = []): Bill
    {
        $tenant   = $this->makeTenant();
        $room     = $this->makeRoom($tenant->id);
        $contract = $this->makeContract($tenant, $room, $contractOverrides);

        return Bill::create(array_merge([
            'tenant_id'      => $tenant->id,
            'contract_id'    => $contract->id,
            'room_id'        => $room->id,
            'type'           => 'monthly',
            'billing_period' => '2026-04',
            'base_rent'      => 10000,
            'utilities'      => 2000,
            'electricity'    => 0,
            'water'          => 0,
            'wifi'           => 0,
            'deposit_amount' => 0,
            'room_key_fee'   => 0,
            'penalty_amount' => 0,
            'total_amount'   => 12000,
            'due_date'       => now()->subDays($daysOverdue)->toDateString(),
            'status'         => 'unpaid',
        ], $billOverrides));
    }

    /* ── B1: grace period (daysOverdue ≤ 3) ────── */

    #[Test]
    public function wbt_bill_001_grace_day_one_logs_reminder(): void
    {
        $bill = $this->makeBill(daysOverdue: 1);

        Artisan::call('billing:apply-penalties');
        $bill->refresh();

        $this->assertSame('grace', $bill->status);
        $this->assertSame('0.00', $bill->penalty_amount);
        $this->assertSame('12000.00', $bill->total_amount);
        $this->assertSame(1, $bill->days_overdue);
        $this->assertSame(1, NotificationLog::where('type', 'grace_reminder')->count());
    }

    #[Test]
    public function wbt_bill_002_grace_day_two_no_reminder(): void
    {
        $bill = $this->makeBill(daysOverdue: 2);

        Artisan::call('billing:apply-penalties');
        $bill->refresh();

        $this->assertSame('grace', $bill->status);
        $this->assertSame('0.00', $bill->penalty_amount);
        $this->assertSame('12000.00', $bill->total_amount);
        $this->assertSame(0, NotificationLog::where('type', 'grace_reminder')->count());
    }

    #[Test]
    public function wbt_bill_003_grace_last_day_logs_reminder(): void
    {
        $bill = $this->makeBill(daysOverdue: 3);

        Artisan::call('billing:apply-penalties');
        $bill->refresh();

        $this->assertSame('grace', $bill->status);
        $this->assertSame('0.00', $bill->penalty_amount);
        $this->assertSame('12000.00', $bill->total_amount);
        $this->assertSame(1, NotificationLog::where('type', 'grace_reminder')->count());
    }

    /* ── B2: overdue (4 ≤ daysOverdue < 14) ─────── */

    #[Test]
    public function wbt_bill_004_overdue_entry_boundary_day_four(): void
    {
        $bill = $this->makeBill(daysOverdue: 4);

        Artisan::call('billing:apply-penalties');
        $bill->refresh();

        $this->assertSame('overdue', $bill->status);
        $this->assertSame('100.00', $bill->penalty_amount);
        $this->assertSame('12100.00', $bill->total_amount);
    }

    #[Test]
    public function wbt_bill_005_overdue_middle_day_ten(): void
    {
        $bill = $this->makeBill(daysOverdue: 10);

        Artisan::call('billing:apply-penalties');
        $bill->refresh();

        $this->assertSame('overdue', $bill->status);
        $this->assertSame('700.00', $bill->penalty_amount);
        $this->assertSame('12700.00', $bill->total_amount);
    }

    #[Test]
    public function wbt_bill_006_overdue_exit_boundary_day_thirteen(): void
    {
        $bill = $this->makeBill(daysOverdue: 13);

        Artisan::call('billing:apply-penalties');
        $bill->refresh();

        $this->assertSame('overdue', $bill->status);
        $this->assertSame('1000.00', $bill->penalty_amount);
        $this->assertSame('13000.00', $bill->total_amount);
    }

    /* ── B3: delinquent (14 ≤ daysOverdue < 30) ── */

    #[Test]
    public function wbt_bill_007_delinquent_entry_logs_notice(): void
    {
        $bill = $this->makeBill(daysOverdue: 14);

        Artisan::call('billing:apply-penalties');
        $bill->refresh();

        $this->assertSame('delinquent', $bill->status);
        $this->assertSame('1100.00', $bill->penalty_amount);
        $this->assertSame('13100.00', $bill->total_amount);
        $this->assertSame(1, NotificationLog::where('type', 'delinquent_notice')->count());
    }

    #[Test]
    public function wbt_bill_008_delinquent_middle_no_extra_notice(): void
    {
        $bill = $this->makeBill(daysOverdue: 20);

        Artisan::call('billing:apply-penalties');
        $bill->refresh();

        $this->assertSame('delinquent', $bill->status);
        $this->assertSame('1700.00', $bill->penalty_amount);
        $this->assertSame('13700.00', $bill->total_amount);
        $this->assertSame(0, NotificationLog::where('type', 'delinquent_notice')->count());
    }

    #[Test]
    public function wbt_bill_009_delinquent_exit_boundary_day_twenty_nine(): void
    {
        $bill = $this->makeBill(daysOverdue: 29);

        Artisan::call('billing:apply-penalties');
        $bill->refresh();

        $this->assertSame('delinquent', $bill->status);
        $this->assertSame('2600.00', $bill->penalty_amount);
        $this->assertSame('14600.00', $bill->total_amount);
    }

    /* ── B4: eviction (daysOverdue ≥ 30) ────────── */

    #[Test]
    public function wbt_bill_010_eviction_entry_logs_urgent_notice(): void
    {
        $bill = $this->makeBill(daysOverdue: 30);

        Artisan::call('billing:apply-penalties');
        $bill->refresh();

        $this->assertSame('eviction', $bill->status);
        $this->assertSame('2700.00', $bill->penalty_amount);
        $this->assertSame('14700.00', $bill->total_amount);
        $this->assertSame(1, NotificationLog::where('type', 'eviction_notice')->count());
    }

    #[Test]
    public function wbt_bill_011_eviction_beyond_no_duplicate_notice(): void
    {
        $bill = $this->makeBill(daysOverdue: 45);

        Artisan::call('billing:apply-penalties');
        $bill->refresh();

        $this->assertSame('eviction', $bill->status);
        $this->assertSame('4200.00', $bill->penalty_amount);
        $this->assertSame('16200.00', $bill->total_amount);
        $this->assertSame(0, NotificationLog::where('type', 'eviction_notice')->count());
    }

    /* ── Edge cases ─────────────────────────────── */

    #[Test]
    public function wbt_bill_012_paid_bill_is_excluded_from_query(): void
    {
        $bill = $this->makeBill(daysOverdue: 10, billOverrides: [
            'status'         => 'paid',
            'paid_at'        => now()->subDays(5),
            'penalty_amount' => 0,
            'total_amount'   => 12000,
        ]);

        Artisan::call('billing:apply-penalties');
        $bill->refresh();

        $this->assertSame('paid', $bill->status);
        $this->assertSame('0.00', $bill->penalty_amount);
        $this->assertSame('12000.00', $bill->total_amount);
        $this->assertSame(0, NotificationLog::count());
    }

    #[Test]
    public function wbt_bill_013_custom_grace_days_extends_grace_window(): void
    {
        $bill = $this->makeBill(
            daysOverdue: 5,
            contractOverrides: ['penalty_grace_days' => 5]
        );

        Artisan::call('billing:apply-penalties');
        $bill->refresh();

        $this->assertSame('grace', $bill->status);
        $this->assertSame('0.00', $bill->penalty_amount);
        $this->assertSame('12000.00', $bill->total_amount);
    }

    #[Test]
    public function wbt_bill_014_default_penalty_rate_path_yields_expected_total(): void
    {
        // Schema disallows literal NULL on contracts.penalty_rate (NOT NULL DEFAULT 100),
        // so this row exercises the default-rate path: contract has no penalty_rate
        // override, the schema's default (100) is used, and penalty math matches.
        $bill = $this->makeBill(daysOverdue: 7);

        Artisan::call('billing:apply-penalties');
        $bill->refresh();

        $this->assertSame('overdue', $bill->status);
        $this->assertSame('400.00', $bill->penalty_amount);
        $this->assertSame('12400.00', $bill->total_amount);
    }
}
