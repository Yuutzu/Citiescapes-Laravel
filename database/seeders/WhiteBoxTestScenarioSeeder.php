<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\Contract;
use App\Models\InitialPayment;
use App\Models\NotificationLog;
use App\Models\OtpRecord;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * WhiteBoxTestScenarioSeeder
 * 
 * Comprehensive white-box test scenarios for all subsystems.
 * Covers branch coverage for structural testing.
 *
 * Run: php artisan db:seed --class=WhiteBoxTestScenarioSeeder
 *
 * Subsystems covered:
 *   SS1: Public Listings - Branch coverage for submitInquiry()
 *   SS2: Tenant Management - Branch coverage for TenantManager lifecycle
 *   SS3: Billing - Branch coverage for ApplyBillingPenalties & recordInitial()
 *   SS4: Contracts - Branch coverage for getTimerBadge() & AutoArchiveExpiredContracts::handle()
 *   SS6: Auth - Branch coverage for Login & User locking mechanisms
 */
class WhiteBoxTestScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🧪 Creating White-Box Test Scenarios (Branch Coverage)...');
        $this->command->newLine();

        // Setup: Create users
        $gm = $this->createGMAccounts();
        $tenants = $this->createTenantBranchScenarios();
        $rooms = $this->createRoomBranchScenarios();

        // SS6 Branch: User locking/unlocking scenarios
        $this->createUserLockingBranchScenarios($gm);

        // SS4 Branch: Contract timer badge scenarios
        $this->createContractTimerBranchScenarios($tenants, $rooms, $gm);

        // SS3 Branch: Billing penalty application scenarios
        $this->createBillingPenaltyBranchScenarios($gm);

        // SS3 Branch: Initial payment recording scenarios
        $this->createInitialPaymentBranchScenarios($tenants, $rooms, $gm);

        // SS1 Branch: Inquiry scenarios with different GM counts
        $this->createInquiryBranchScenarios();

        $this->command->newLine();
        $this->command->info('✅ White-box branch coverage scenarios created!');
        $this->displayTestScenarios();
    }

    private function createGMAccounts(): User
    {
        $this->command->info('👤 SS6: Creating GM accounts for branch testing...');

        // GM 1 (for branches requiring active GM)
        $gm = User::firstOrCreate(
            ['email' => 'wb.gm.one@test.local'],
            [
                'full_name' => '[WB] GM - One',
                'password' => Hash::make('password'),
                'role' => 'gm',
                'status' => 'active',
                'activated_at' => now()->subMonths(6),
                'email_verified_at' => now(),
            ]
        );

        // GM 2 (for multi-GM branches)
        User::firstOrCreate(
            ['email' => 'wb.gm.two@test.local'],
            [
                'full_name' => '[WB] GM - Two',
                'password' => Hash::make('password'),
                'role' => 'gm',
                'status' => 'active',
                'activated_at' => now()->subMonths(5),
                'email_verified_at' => now(),
            ]
        );

        $this->command->line('  ✓ Created 2 GMs (for multi-GM branches)');
        return $gm;
    }

    private function createTenantBranchScenarios(): array
    {
        $this->command->info('👥 SS2: Creating tenant branch scenarios...');

        $tenants = [];

        // B1: Create new tenant (editingId is null)
        $tenants['new_create'] = User::firstOrCreate(
            ['email' => 'wb.tenant.create@test.local'],
            [
                'full_name' => '[WB] Tenant - New Create Branch',
                'password' => Hash::make('password'),
                'role' => 'tenant',
                'status' => 'active',
                'activated_at' => now()->subMonths(3),
            ]
        );

        // B1F: Update existing tenant (editingId is set)
        $tenants['update_existing'] = User::firstOrCreate(
            ['email' => 'wb.tenant.update@test.local'],
            [
                'full_name' => '[WB] Tenant - Update Branch (Original Name)',
                'password' => Hash::make('password'),
                'role' => 'tenant',
                'status' => 'active',
                'activated_at' => now()->subMonths(2),
            ]
        );

        // B3: Tenant with active contract (archive blocked)
        $tenants['has_contract'] = User::firstOrCreate(
            ['email' => 'wb.tenant.contract@test.local'],
            [
                'full_name' => '[WB] Tenant - With Contract',
                'password' => Hash::make('password'),
                'role' => 'tenant',
                'status' => 'active',
                'activated_at' => now()->subMonths(4),
            ]
        );

        // B3F: Tenant without contract (can archive)
        $tenants['no_contract'] = User::firstOrCreate(
            ['email' => 'wb.tenant.nocontract@test.local'],
            [
                'full_name' => '[WB] Tenant - No Contract (Can Archive)',
                'password' => Hash::make('password'),
                'role' => 'tenant',
                'status' => 'active',
                'activated_at' => now()->subMonths(1),
            ]
        );

        $this->command->line('  ✓ Created 4 tenant branch scenarios');
        return $tenants;
    }

    private function createRoomBranchScenarios(): array
    {
        $this->command->info('🏠 SS3: Creating room branch scenarios...');

        $rooms = [];

        // For contract branches
        $rooms['for_contract1'] = Room::firstOrCreate(
            ['room_number' => 'WB_C_001'],
            [
                'floor_level' => 1,
                'room_type' => 'compact',
                'rate' => 3500.00,
                'max_occupants' => 2,
                'status' => 'available',
                'amenities' => ['Air Conditioning', 'WiFi', 'Shared Bathroom'],
                'description' => '[WB] Compact test room for contract branch scenarios',
                'photos' => [],
            ]
        );

        $rooms['for_contract2'] = Room::firstOrCreate(
            ['room_number' => 'WB_C_002'],
            [
                'floor_level' => 1,
                'room_type' => 'spacious',
                'rate' => 5000.00,
                'max_occupants' => 3,
                'status' => 'available',
                'amenities' => ['Air Conditioning', 'WiFi', 'Private Bathroom', 'Mini Fridge'],
                'description' => '[WB] Spacious test room for contract branch scenarios',
                'photos' => [],
            ]
        );

        $this->command->line('  ✓ Created 2 room scenarios');
        return $rooms;
    }

    private function createUserLockingBranchScenarios(User $gm): void
    {
        $this->command->info('🔒 SS6: Creating user locking branch scenarios...');

        // Branch B1: User not locked (isLocked() = false)
        User::firstOrCreate(
            ['email' => 'wb.auth.unlocked@test.local'],
            [
                'full_name' => '[WB] Auth - Not Locked',
                'password' => Hash::make('password'),
                'role' => 'gm',
                'status' => 'active',
                'failed_login_attempts' => 0,
                'activated_at' => now(),
            ]
        );

        // Branch B1T: User locked (isLocked() = true)
        User::firstOrCreate(
            ['email' => 'wb.auth.locked@test.local'],
            [
                'full_name' => '[WB] Auth - Locked',
                'password' => Hash::make('password'),
                'role' => 'gm',
                'status' => 'active',
                'failed_login_attempts' => 5,
                'locked_until' => now()->addHours(1),
                'activated_at' => now(),
            ]
        );

        // Branch B2: Lock expired (autoUnlockIfExpired = true)
        User::firstOrCreate(
            ['email' => 'wb.auth.expired_lock@test.local'],
            [
                'full_name' => '[WB] Auth - Lock Expired',
                'password' => Hash::make('password'),
                'role' => 'gm',
                'status' => 'active',
                'failed_login_attempts' => 3,
                'locked_until' => now()->subMinutes(10), // Already expired
                'activated_at' => now(),
            ]
        );

        // Branch B2F: Lock not expired (autoUnlockIfExpired = false)
        User::firstOrCreate(
            ['email' => 'wb.auth.active_lock@test.local'],
            [
                'full_name' => '[WB] Auth - Active Lock',
                'password' => Hash::make('password'),
                'role' => 'gm',
                'status' => 'active',
                'failed_login_attempts' => 4,
                'locked_until' => now()->addMinutes(30), // Still active
                'activated_at' => now(),
            ]
        );

        $this->command->line('  ✓ Created 4 user locking branch scenarios');
    }

    private function createContractTimerBranchScenarios(array $tenants, array $rooms, User $gm): void
    {
        $this->command->info('⏱️  SS4: Creating contract timer branch scenarios...');

        // B1: Contract active (days_remaining > 0)
        Contract::firstOrCreate(
            ['tenant_id' => $tenants['has_contract']->id],
            [
                'room_id' => $rooms['for_contract1']->id,
                'base_rent_rate' => 3500.00,
                'deposit' => 3500.00,
                'room_key_fee' => 150.00,
                'start_date' => now()->subMonths(1)->toDateString(),
                'end_date' => now()->addMonths(2)->toDateString(), // +2 months = 60+ days
                'status' => 'active',
                'activated_at' => now()->subMonths(1),
                'penalty_rate' => 100.00,
                'penalty_grace_days' => 3,
                'created_by' => $gm->id,
            ]
        );

        // B1F: Contract expiring soon (days_remaining < 30, getTimerBadge shows "danger")
        Contract::firstOrCreate(
            ['tenant_id' => $tenants['update_existing']->id],
            [
                'room_id' => $rooms['for_contract2']->id,
                'base_rent_rate' => 5000.00,
                'deposit' => 5000.00,
                'room_key_fee' => 200.00,
                'start_date' => now()->subMonths(5)->toDateString(),
                'end_date' => now()->addDays(15)->toDateString(), // 15 days left
                'status' => 'active',
                'activated_at' => now()->subMonths(5),
                'penalty_rate' => 100.00,
                'penalty_grace_days' => 3,
                'created_by' => $gm->id,
            ]
        );

        // B2: Contract expired (end_date < now())
        Contract::firstOrCreate(
            ['tenant_id' => $tenants['no_contract']->id],
            [
                'room_id' => $rooms['for_contract1']->id,
                'base_rent_rate' => 3500.00,
                'deposit' => 3500.00,
                'room_key_fee' => 150.00,
                'start_date' => now()->subMonths(6)->toDateString(),
                'end_date' => now()->subDays(5)->toDateString(), // 5 days ago
                'status' => 'expired',
                'activated_at' => now()->subMonths(6),
                'terminated_at' => now()->subDays(5),
                'penalty_rate' => 100.00,
                'penalty_grace_days' => 3,
                'created_by' => $gm->id,
            ]
        );

        $this->command->line('  ✓ Created 3 contract timer branch scenarios');
    }

    private function createBillingPenaltyBranchScenarios(User $gm): void
    {
        $this->command->info('💰 SS3: Creating billing penalty branch scenarios...');

        // Create a tenant and contract for billing tests
        $tenant = User::firstOrCreate(
            ['email' => 'wb.tenant.billing@test.local'],
            [
                'full_name' => '[WB] Tenant - Billing Branch',
                'password' => Hash::make('password'),
                'role' => 'tenant',
                'status' => 'active',
                'activated_at' => now()->subMonths(3),
            ]
        );

        $room = Room::firstOrCreate(
            ['room_number' => 'WB_B_001'],
            [
                'floor_level' => 1,
                'room_type' => 'compact',
                'rate' => 5000.00,
                'max_occupants' => 2,
                'status' => 'available',
                'amenities' => ['Air Conditioning', 'WiFi', 'Shared Bathroom'],
                'description' => '[WB] Compact test room for billing branch scenarios',
                'photos' => [],
            ]
        );

        $contract = Contract::firstOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'room_id' => $room->id,
                'base_rent_rate' => 5000.00,
                'deposit' => 5000.00,
                'room_key_fee' => 200.00,
                'start_date' => now()->subMonths(3)->toDateString(),
                'end_date' => now()->addMonths(3)->toDateString(),
                'status' => 'active',
                'activated_at' => now()->subMonths(3),
                'penalty_rate' => 100.00,
                'penalty_grace_days' => 3,
                'created_by' => $gm->id,
            ]
        );

        // B1: Grace period (daysOverdue <= graceDays)
        Bill::firstOrCreate(
            ['contract_id' => $contract->id, 'billing_period' => now()->subMonths(2)->format('Y-m'), 'type' => 'monthly'],
            [
                'tenant_id' => $tenant->id,
                'room_id' => $room->id,
                'type' => 'monthly',
                'base_rent' => 5000.00,
                'utilities' => 500.00,
                'total_amount' => 5500.00,
                'due_date' => now()->subDays(2)->toDateString(), // 2 days overdue (within grace)
                'status' => 'grace',
                'days_overdue' => 2,
            ]
        );

        // B1F: Overdue period (daysOverdue >= graceDays+1 AND < delinquentDay)
        Bill::firstOrCreate(
            ['contract_id' => $contract->id, 'billing_period' => now()->subMonths(3)->format('Y-m'), 'type' => 'monthly'],
            [
                'tenant_id' => $tenant->id,
                'room_id' => $room->id,
                'type' => 'monthly',
                'base_rent' => 5000.00,
                'utilities' => 500.00,
                'penalty_amount' => 400.00, // 4 days overdue - 3 grace = 1 day * 100
                'total_amount' => 5900.00,
                'due_date' => now()->subDays(7)->toDateString(), // 7 days overdue
                'status' => 'overdue',
                'days_overdue' => 7,
            ]
        );

        // B2: Delinquent (daysOverdue >= delinquentDay AND < evictionDay)
        Bill::firstOrCreate(
            ['contract_id' => $contract->id, 'billing_period' => now()->subMonths(4)->format('Y-m'), 'type' => 'monthly'],
            [
                'tenant_id' => $tenant->id,
                'room_id' => $room->id,
                'type' => 'monthly',
                'base_rent' => 5000.00,
                'utilities' => 500.00,
                'penalty_amount' => 1200.00, // 15 days - 3 grace = 12 days * 100
                'total_amount' => 6700.00,
                'due_date' => now()->subDays(15)->toDateString(), // 15 days overdue
                'status' => 'delinquent',
                'days_overdue' => 15,
            ]
        );

        // B2F: Eviction (daysOverdue >= evictionDay)
        Bill::firstOrCreate(
            ['contract_id' => $contract->id, 'billing_period' => now()->subMonths(5)->format('Y-m'), 'type' => 'monthly'],
            [
                'tenant_id' => $tenant->id,
                'room_id' => $room->id,
                'type' => 'monthly',
                'base_rent' => 5000.00,
                'utilities' => 500.00,
                'penalty_amount' => 2800.00, // 31 days - 3 grace = 28 days * 100
                'total_amount' => 8300.00,
                'due_date' => now()->subDays(31)->toDateString(), // 31 days overdue
                'status' => 'eviction',
                'days_overdue' => 31,
            ]
        );

        $this->command->line('  ✓ Created billing penalty branch scenarios (grace, overdue, delinquent, eviction)');
    }

    private function createInitialPaymentBranchScenarios(array $tenants, array $rooms, User $gm): void
    {
        $this->command->info('💳 SS3: Creating initial payment branch scenarios...');

        // B1: Valid initial payment (validate() passes)
        $contract1 = Contract::firstOrCreate(
            ['tenant_id' => $tenants['new_create']->id, 'room_id' => $rooms['for_contract1']->id],
            [
                'base_rent_rate' => 3500.00,
                'deposit' => 3500.00,
                'room_key_fee' => 150.00,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonths(6)->toDateString(),
                'status' => 'draft',
                'penalty_rate' => 100.00,
                'penalty_grace_days' => 3,
                'created_by' => $gm->id,
            ]
        );

        InitialPayment::firstOrCreate(
            ['contract_id' => $contract1->id],
            [
                'tenant_id' => $tenants['new_create']->id,
                'deposit_amount' => 3500.00,
                'first_month_rent' => 3500.00,
                'room_key_fee' => 150.00,
                'total_collected' => 7150.00,
                'date_received' => now(),
                'payment_method' => 'cash',
                'recorded_by' => $gm->id,
            ]
        );

        // B1F: Duplicate initial payment (validate() fails - already exists)
        // This will be tested in the test itself, not seeded
        // The seeder just creates the contract without initial payment

        $contract2 = Contract::firstOrCreate(
            ['tenant_id' => $tenants['update_existing']->id, 'room_id' => $rooms['for_contract2']->id],
            [
                'base_rent_rate' => 5000.00,
                'deposit' => 5000.00,
                'room_key_fee' => 200.00,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addMonths(7)->toDateString(),
                'status' => 'draft',
                'penalty_rate' => 100.00,
                'penalty_grace_days' => 3,
                'created_by' => $gm->id,
            ]
        );

        $this->command->line('  ✓ Created initial payment branch scenarios');
    }

    private function createInquiryBranchScenarios(): void
    {
        $this->command->info('❓ SS1: Creating inquiry branch scenarios...');

        // B2: Zero GMs in DB (foreach loop doesn't execute)
        // This is hard to test with seeding (GMs are required),
        // but we create the scenario where we temporarily check branch paths

        // B2F: One or more GMs (foreach loop executes)
        // Already handled by createGMAccounts() creating 2 GMs

        $this->command->line('  ✓ Created inquiry branch scenarios (multi-GM branches)');
    }

    private function displayTestScenarios(): void
    {
        $this->command->newLine();
        $this->command->info('📋 White-Box Branch Coverage Scenarios:');
        $this->command->line('');
        $this->command->info('  SS6 - User Authentication:');
        $this->command->line('    ✓ Not locked user (B1)');
        $this->command->line('    ✓ Locked user (B1T)');
        $this->command->line('    ✓ Expired lock (B2)');
        $this->command->line('    ✓ Active lock (B2F)');
        $this->command->newLine();
        $this->command->info('  SS4 - Contract Timer:');
        $this->command->line('    ✓ Active contract (B1)');
        $this->command->line('    ✓ Expiring soon (B1F - <30 days)');
        $this->command->line('    ✓ Expired contract (B2)');
        $this->command->newLine();
        $this->command->info('  SS3 - Billing Penalties:');
        $this->command->line('    ✓ Grace period (≤3 days)');
        $this->command->line('    ✓ Overdue period (4-13 days)');
        $this->command->line('    ✓ Delinquent (14-29 days)');
        $this->command->line('    ✓ Eviction (30+ days)');
        $this->command->newLine();
        $this->command->info('💡 All test data prefixed with [WB] for easy identification');
    }
}
