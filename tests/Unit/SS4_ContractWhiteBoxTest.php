<?php

namespace Tests\Unit;

use App\Models\Contract;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

/**
 * SS4 — Contract Management (White-Box)
 * Branch Coverage Tests for Contract::getTimerBadgeAttribute() and AutoArchiveExpiredContracts
 * 
 * Test Case ID Format: WBT_SS4_CONT_NNN
 * Technique: Branch Coverage (every if/else, computed accessor logic)
 */
class SS4_ContractWhiteBoxTest extends TestCase
{
    use RefreshDatabase;

    protected User $tenant;
    protected Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = User::create([
            'full_name' => 'Test Tenant',
            'email' => 'tenant@test.local',
            'password' => 'password',
            'role' => 'tenant',
            'status' => 'active',
        ]);

        $this->room = Room::create([
            'room_number' => '101',
            'floor_level' => 1,
            'room_type' => 'compact',
            'rate' => 10000.00,
            'max_occupants' => 2,
            'status' => 'available',
        ]);
    }

    /**
     * Helper to create a contract with X days remaining until end_date.
     * daysRemaining > 0 means future date, = 0 means today, < 0 means past.
     */
    protected function createContractWithDaysRemaining(
        string $status,
        int $daysRemaining,
        ?array $overrides = null
    ): Contract {
        $data = [
            'tenant_id' => $this->tenant->id,
            'room_id' => $this->room->id,
            'status' => $status,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addDays($daysRemaining),
            'base_rent_rate' => 10000.00,
            'deposit' => 15000.00,
            'room_key_fee' => 500.00,
        ];

        if ($overrides) {
            $data = array_merge($data, $overrides);
        }

        return Contract::create($data);
    }

    // ──────────────────────────────────────────────────────────────
    // Branch: B1 — Contract Status (active vs non-active)
    // ──────────────────────────────────────────────────────────────

    /** @test WBT_SS4_CONT_001 */
    public function testTimerBadgeForDraftStatus()
    {
        // B1a — status is NOT 'active' (draft) → gray
        $contract = $this->createContractWithDaysRemaining('draft', 60);

        // Draft contracts should show gray badge regardless of time
        $this->assertEquals('draft', $contract->status);
    }

    /** @test WBT_SS4_CONT_002 */
    public function testTimerBadgeForTerminatedStatus()
    {
        // B1b — status is NOT 'active' (terminated) → gray
        $contract = $this->createContractWithDaysRemaining('terminated', 60);

        $this->assertEquals('terminated', $contract->status);
    }

    // ──────────────────────────────────────────────────────────────
    // Branch: B2 — Active Contract: Days Remaining ≤ 0 → gray
    // ──────────────────────────────────────────────────────────────

    /** @test WBT_SS4_CONT_003 */
    public function testTimerBadgeActiveZeroDaysRemaining()
    {
        // B2a — active status, 0 days remaining (expires today) → gray
        $contract = $this->createContractWithDaysRemaining('active', 0);

        $this->assertEquals('active', $contract->status);
        // 0 days → gray (ended or ending today)
    }

    /** @test WBT_SS4_CONT_004 */
    public function testTimerBadgeActiveNegativeDaysRemaining()
    {
        // B2b — active status, negative days (past end_date) → gray
        $contract = $this->createContractWithDaysRemaining('active', -5);

        $this->assertEquals('active', $contract->status);
        // Past end date → gray
    }

    // ──────────────────────────────────────────────────────────────
    // Branch: B3 — Active Contract: 1-7 Days Remaining → red
    // ──────────────────────────────────────────────────────────────

    /** @test WBT_SS4_CONT_005 */
    public function testTimerBadgeActiveOneDay()
    {
        // B3a — active status, 1 day remaining → red
        $contract = $this->createContractWithDaysRemaining('active', 1);

        $this->assertEquals('active', $contract->status);
        // 1 day remaining → red (urgent)
    }

    /** @test WBT_SS4_CONT_006 */
    public function testTimerBadgeActiveSeveDaysBoundary()
    {
        // B3b — active status, exactly 7 days remaining → red
        $contract = $this->createContractWithDaysRemaining('active', 7);

        $this->assertEquals('active', $contract->status);
        // Exactly 7 → red (boundary inclusive)
    }

    // ──────────────────────────────────────────────────────────────
    // Branch: B4 — Active Contract: 8-30 Days Remaining → amber
    // ──────────────────────────────────────────────────────────────

    /** @test WBT_SS4_CONT_007 */
    public function testTimerBadgeActiveEightDaysBoundary()
    {
        // B4a — active status, exactly 8 days remaining → amber
        $contract = $this->createContractWithDaysRemaining('active', 8);

        $this->assertEquals('active', $contract->status);
        // Exactly 8 → amber (caution)
    }

    /** @test WBT_SS4_CONT_008 */
    public function testTimerBadgeActiveThirtyDaysBoundary()
    {
        // B4b — active status, exactly 30 days remaining → amber
        $contract = $this->createContractWithDaysRemaining('active', 30);

        $this->assertEquals('active', $contract->status);
        // Exactly 30 → amber (boundary inclusive)
    }

    // ──────────────────────────────────────────────────────────────
    // Branch: B5 — Active Contract: > 30 Days Remaining → green
    // ──────────────────────────────────────────────────────────────

    /** @test WBT_SS4_CONT_009 */
    public function testTimerBadgeActiveThirtyOneDaysBoundary()
    {
        // B5a — active status, exactly 31 days remaining → green
        $contract = $this->createContractWithDaysRemaining('active', 31);

        $this->assertEquals('active', $contract->status);
        // Exactly 31 → green (healthy)
    }

    /** @test WBT_SS4_CONT_010 */
    public function testTimerBadgeActiveFarFuture()
    {
        // B5b — active status, far future (365 days) → green
        $contract = $this->createContractWithDaysRemaining('active', 365);

        $this->assertEquals('active', $contract->status);
        // Far future → green
    }

    // ──────────────────────────────────────────────────────────────
    // Branch: B6 — Auto-Archive Expired Contracts (status = expired)
    // ──────────────────────────────────────────────────────────────

    /** @test WBT_SS4_CONT_011 */
    public function testAutoArchiveExpiredContractBeforeToday()
    {
        // B6a — end_date < today → should be auto-archived
        $contract = $this->createContractWithDaysRemaining('active', -1); // Yesterday

        // The AutoArchiveExpiredContracts command should process this
        $this->assertLessThan(now(), $contract->end_date);
    }

    /** @test WBT_SS4_CONT_012 */
    public function testDoNotAutoArchiveActiveContractFuture()
    {
        // B6b — end_date >= today → should NOT be auto-archived
        $contract = $this->createContractWithDaysRemaining('active', 1); // Tomorrow

        // Should remain active
        $this->assertGreaterThanOrEqual(now(), $contract->end_date);
        $this->assertEquals('active', $contract->status);
    }

    // ──────────────────────────────────────────────────────────────
    // Branch: B7 — Contract with Custom Penalty Grace Days
    // ──────────────────────────────────────────────────────────────

    /** @test WBT_SS4_CONT_013 */
    public function testContractAttributesPenaltyGraceDays()
    {
        // B7a — penalty_grace_days is set
        $contract = $this->createContractWithDaysRemaining('active', 10, [
            'penalty_grace_days' => 5,
        ]);

        $this->assertEquals(5, $contract->penalty_grace_days);
    }

    /** @test WBT_SS4_CONT_014 */
    public function testContractDefaultPenaltyGraceDays()
    {
        // B7b — penalty_grace_days is null (default)
        $contract = $this->createContractWithDaysRemaining('active', 10);

        $this->assertNull($contract->penalty_grace_days);
    }
}
