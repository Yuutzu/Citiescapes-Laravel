<?php

namespace Tests\Unit\Models;

use App\Models\Contract;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * White-Box Testing — Module 3: Contract Timer Badge
 *
 * Branch coverage of Contract::getTimerBadgeAttribute()
 * (app/Models/Contract.php:63-71).
 *
 * Each test case maps to a row in docs/WHITE_BOX_TESTING.md (WBT_CONT_001..010).
 */
class ContractTest extends TestCase
{
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

    private function makeContract(string $status, Carbon $endDate): Contract
    {
        $contract = new Contract();
        $contract->status = $status;
        $contract->end_date = $endDate;
        return $contract;
    }

    #[Test]
    public function wbt_cont_001_non_active_status_draft_returns_gray(): void
    {
        $contract = $this->makeContract('draft', Carbon::today()->addDays(60));
        $this->assertSame('gray', $contract->timer_badge);
    }

    #[Test]
    public function wbt_cont_002_non_active_status_terminated_returns_gray(): void
    {
        $contract = $this->makeContract('terminated', Carbon::today()->addDays(60));
        $this->assertSame('gray', $contract->timer_badge);
    }

    #[Test]
    public function wbt_cont_003_active_with_zero_days_remaining_returns_gray(): void
    {
        $contract = $this->makeContract('active', Carbon::today());
        $this->assertSame(0, $contract->days_remaining);
        $this->assertSame('gray', $contract->timer_badge);
    }

    #[Test]
    public function wbt_cont_004_active_with_past_end_date_returns_gray(): void
    {
        $contract = $this->makeContract('active', Carbon::today()->subDays(5));
        $this->assertSame(0, $contract->days_remaining);
        $this->assertSame('gray', $contract->timer_badge);
    }

    #[Test]
    public function wbt_cont_005_active_one_day_remaining_returns_red(): void
    {
        $contract = $this->makeContract('active', Carbon::today()->addDays(1));
        $this->assertSame('red', $contract->timer_badge);
    }

    #[Test]
    public function wbt_cont_006_active_seven_days_remaining_returns_red(): void
    {
        $contract = $this->makeContract('active', Carbon::today()->addDays(7));
        $this->assertSame('red', $contract->timer_badge);
    }

    #[Test]
    public function wbt_cont_007_active_eight_days_remaining_returns_amber(): void
    {
        $contract = $this->makeContract('active', Carbon::today()->addDays(8));
        $this->assertSame('amber', $contract->timer_badge);
    }

    #[Test]
    public function wbt_cont_008_active_thirty_days_remaining_returns_amber(): void
    {
        $contract = $this->makeContract('active', Carbon::today()->addDays(30));
        $this->assertSame('amber', $contract->timer_badge);
    }

    #[Test]
    public function wbt_cont_009_active_thirty_one_days_remaining_returns_green(): void
    {
        $contract = $this->makeContract('active', Carbon::today()->addDays(31));
        $this->assertSame('green', $contract->timer_badge);
    }

    #[Test]
    public function wbt_cont_010_active_far_future_end_date_returns_green(): void
    {
        $contract = $this->makeContract('active', Carbon::today()->addDays(365));
        $this->assertSame('green', $contract->timer_badge);
    }
}
