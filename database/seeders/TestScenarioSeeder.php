<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\Contract;
use App\Models\Inquiry;
use App\Models\InitialPayment;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * Comprehensive test seeder for black box and white box testing.
 * Creates realistic test data covering all subsystems:
 *   SS1 (Users/Auth): GM, Admin, Tenants
 *   SS2 (Tenant Lifecycle): Active, Archived tenants
 *   SS3 (Room Management): Available, Occupied, Under Maintenance
 *   SS4 (Contracts): Active, Expired, Pending, Terminated
 *   SS5 (Billing): Paid, Unpaid, Overdue, Delinquent bills
 *   SS6 (Inquiries): Pending, Responded, Closed inquiries
 *
 * Run:  php artisan db:seed --class=TestScenarioSeeder
 * Cleanup:  php artisan db:seed --class=TestScenarioSeeder --cleanup  (NOT IMPLEMENTED - manual delete needed)
 *
 * Test IDs use prefix: TEST_* for easy identification and cleanup
 */
class TestScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🧪 Creating comprehensive test scenarios for all subsystems...');
        $this->command->newLine();

        // Setup: Create users
        $gm = $this->createGMAccount();
        $tenants = $this->createTenants();

        // Setup: Create rooms with different statuses
        $rooms = $this->createRoomsWithVariousStatuses();

        // SS4 & SS3: Create contracts and sync room status
        $this->createContractsAndSyncRooms($tenants, $rooms, $gm->id);

        // SS5: Create bills in various states
        $this->createBillsInVariousStates($tenants, $rooms);

        // SS6: Create inquiries in various states
        $this->createInquiries();

        $this->command->newLine();
        $this->command->info('✅ Test scenarios created successfully!');
        $this->command->info('');
        $this->command->line('Test accounts:');
        $this->command->line('  GM:       test.gm@citiescapes.test / password');
        $this->command->line('  Tenant 1: test.tenant1@citiescapes.test / password');
        $this->command->line('  Tenant 2: test.tenant2@citiescapes.test / password');
        $this->command->line('');
        $this->command->info('💡 All test data prefixed with TEST_ can be identified for cleanup.');
    }

    private function createGMAccount(): User
    {
        $this->command->info('📝 SS1: Creating GM account...');

        return User::firstOrCreate(
            ['email' => 'test.gm@citiescapes.test'],
            [
                'full_name' => 'TEST GM Account',
                'password' => Hash::make('password'),
                'role' => 'gm',
                'status' => 'active',
                'must_change_password' => false,
                'activated_at' => now(),
                'email_verified_at' => now(),
            ]
        );
    }

    private function createTenants(): array
    {
        $this->command->info('👥 SS2: Creating tenant accounts...');

        $tenants = [];

        // Active tenant 1 (will have contract)
        $tenants[] = User::firstOrCreate(
            ['email' => 'test.tenant1@citiescapes.test'],
            [
                'full_name' => 'TEST Tenant - Alice Johnson',
                'password' => Hash::make('password'),
                'role' => 'tenant',
                'status' => 'active',
                'must_change_password' => false,
                'activated_at' => now()->subMonths(3),
                'email_verified_at' => now(),
            ]
        );

        // Active tenant 2 (will have contract)
        $tenants[] = User::firstOrCreate(
            ['email' => 'test.tenant2@citiescapes.test'],
            [
                'full_name' => 'TEST Tenant - Bob Smith',
                'password' => Hash::make('password'),
                'role' => 'tenant',
                'status' => 'active',
                'must_change_password' => false,
                'activated_at' => now()->subMonths(2),
                'email_verified_at' => now(),
            ]
        );

        // Archived tenant (for SS2 lifecycle testing)
        $tenants[] = User::firstOrCreate(
            ['email' => 'test.tenant.archived@citiescapes.test'],
            [
                'full_name' => 'TEST Tenant - Charlie (Archived)',
                'password' => Hash::make('password'),
                'role' => 'tenant',
                'status' => 'archived',
                'must_change_password' => false,
                'activated_at' => now()->subMonths(6),
                'archived_at' => now()->subMonth(),
                'email_verified_at' => now(),
            ]
        );

        $this->command->line("  ✓ Created 3 tenants (2 active, 1 archived)");

        return $tenants;
    }

    private function createRoomsWithVariousStatuses(): array
    {
        $this->command->info('🏠 SS3: Creating rooms with various statuses...');

        $rooms = [];
        $count = 0;

        // Available rooms
        for ($i = 1; $i <= 3; $i++) {
            $rooms[] = Room::firstOrCreate(
                ['room_number' => "TEST_A0{$i}"],
                [
                    'floor_level' => 1,
                    'room_type' => 'compact',
                    'rate' => 3500.00,
                    'max_occupants' => 3,
                    'status' => 'available',
                    'description' => "TEST: Available compact room {$i}",
                    'amenities' => ['Air Conditioning', 'WiFi', 'Shared Bathroom'],
                ]
            );
            $count++;
        }

        // Occupied rooms (will be set by contract activation)
        for ($i = 1; $i <= 2; $i++) {
            $rooms[] = Room::firstOrCreate(
                ['room_number' => "TEST_O0{$i}"],
                [
                    'floor_level' => 2,
                    'room_type' => 'spacious',
                    'rate' => 5000.00,
                    'max_occupants' => 4,
                    'status' => 'available', // Will be marked occupied by contract
                    'description' => "TEST: Spacious room to be occupied {$i}",
                    'amenities' => ['Air Conditioning', 'WiFi', 'Private Bathroom', 'Mini Fridge'],
                ]
            );
            $count++;
        }

        // Under maintenance room
        $rooms[] = Room::firstOrCreate(
            ['room_number' => 'TEST_M01'],
            [
                'floor_level' => 3,
                'room_type' => 'compact',
                'rate' => 3500.00,
                'max_occupants' => 3,
                'status' => 'under_maintenance',
                'description' => 'TEST: Room under maintenance',
                'amenities' => ['WiFi'],
            ]
        );
        $count++;

        $this->command->line("  ✓ Created {$count} test rooms (3 available, 2 for occupancy, 1 maintenance)");

        return $rooms;
    }

    private function createContractsAndSyncRooms(array $tenants, array $rooms, int $gmId): void
    {
        $this->command->info('📋 SS4: Creating contracts and syncing room status...');

        // Active contract: Tenant 1 in Room TEST_O01
        $activeContract1 = Contract::firstOrCreate(
            ['tenant_id' => $tenants[0]->id, 'room_id' => $rooms[3]->id],
            [
                'base_rent_rate' => 5000.00,
                'deposit' => 5000.00,
                'room_key_fee' => 200.00,
                'start_date' => now()->subMonths(2)->toDateString(),
                'end_date' => now()->addMonths(4)->toDateString(),
                'status' => 'active',
                'activated_at' => now()->subMonths(2),
                'penalty_rate' => 100.00,
                'penalty_grace_days' => 3,
                'created_by' => $gmId,
            ]
        );

        // Sync room status
        $rooms[3]->update([
            'status' => 'occupied',
            'current_tenant_id' => $tenants[0]->id,
            'last_status_update' => now(),
        ]);

        // Active contract: Tenant 2 in Room TEST_O02
        $activeContract2 = Contract::firstOrCreate(
            ['tenant_id' => $tenants[1]->id, 'room_id' => $rooms[4]->id],
            [
                'base_rent_rate' => 5000.00,
                'deposit' => 5000.00,
                'room_key_fee' => 200.00,
                'start_date' => now()->subMonths(1)->toDateString(),
                'end_date' => now()->addMonths(5)->toDateString(),
                'status' => 'active',
                'activated_at' => now()->subMonths(1),
                'penalty_rate' => 100.00,
                'penalty_grace_days' => 3,
                'created_by' => $gmId,
            ]
        );

        // Sync room status
        $rooms[4]->update([
            'status' => 'occupied',
            'current_tenant_id' => $tenants[1]->id,
            'last_status_update' => now(),
        ]);

        // Create initial payments for active contracts
        InitialPayment::firstOrCreate(
            ['contract_id' => $activeContract1->id],
            [
                'tenant_id' => $tenants[0]->id,
                'total_collected' => 10400.00, // deposit + 1st month + key
                'payment_method' => 'cash',
                'date_received' => now()->subMonths(2),
                'deposit_amount' => 5000.00,
                'first_month_rent' => 5000.00,
                'room_key_fee' => 200.00,
                'recorded_by' => $gmId,
            ]
        );

        InitialPayment::firstOrCreate(
            ['contract_id' => $activeContract2->id],
            [
                'tenant_id' => $tenants[1]->id,
                'total_collected' => 10400.00,
                'payment_method' => 'bank_transfer',
                'date_received' => now()->subMonths(1),
                'deposit_amount' => 5000.00,
                'first_month_rent' => 5000.00,
                'room_key_fee' => 200.00,
                'recorded_by' => $gmId,
            ]
        );

        $this->command->line("  ✓ Created 2 active contracts with occupied rooms");
    }

    private function createBillsInVariousStates(array $tenants, array $rooms): void
    {
        $this->command->info('💰 SS5: Creating bills in various states...');

        // Get GM user (created as test data)
        $gm = User::where('email', 'test.gm@citiescapes.test')->first();

        // Paid bill
        $contract1 = Contract::where('tenant_id', $tenants[0]->id)->first();
        if ($contract1) {
            $bill = Bill::firstOrCreate(
                ['contract_id' => $contract1->id, 'billing_period' => now()->subMonth()->format('Y-m')],
                [
                    'tenant_id' => $tenants[0]->id,
                    'room_id' => $rooms[3]->id,
                    'type' => 'monthly',
                    'base_rent' => 5000.00,
                    'utilities' => 500.00,
                    'total_amount' => 5500.00,
                    'due_date' => now()->subMonth()->endOfMonth()->toDateString(),
                    'status' => 'paid',
                    'paid_at' => now()->subDays(15),
                ]
            );

            // Record payment
            Payment::firstOrCreate(
                ['bill_id' => $bill->id],
                [
                    'tenant_id' => $tenants[0]->id,
                    'amount' => 5500.00,
                    'confirmed_at' => now()->subDays(14),
                    'confirmed_by' => $gm?->id,
                    'payment_method' => 'bank_transfer',
                ]
            );
        }

        // Unpaid bill (current month)
        if ($contract1) {
            Bill::firstOrCreate(
                ['contract_id' => $contract1->id, 'billing_period' => now()->format('Y-m')],
                [
                    'tenant_id' => $tenants[0]->id,
                    'room_id' => $rooms[3]->id,
                    'type' => 'monthly',
                    'base_rent' => 5000.00,
                    'utilities' => 500.00,
                    'total_amount' => 5500.00,
                    'due_date' => now()->endOfMonth()->toDateString(),
                    'status' => 'unpaid',
                ]
            );
        }

        // Overdue bill
        $contract2 = Contract::where('tenant_id', $tenants[1]->id)->first();
        if ($contract2) {
            Bill::firstOrCreate(
                ['contract_id' => $contract2->id, 'billing_period' => now()->subMonths(2)->format('Y-m')],
                [
                    'tenant_id' => $tenants[1]->id,
                    'room_id' => $rooms[4]->id,
                    'type' => 'monthly',
                    'base_rent' => 5000.00,
                    'utilities' => 500.00,
                    'total_amount' => 5500.00,
                    'due_date' => now()->subMonths(2)->endOfMonth()->toDateString(),
                    'status' => 'overdue',
                ]
            );
        }

        $this->command->line("  ✓ Created bills: paid, unpaid, overdue");
    }

    private function createInquiries(): void
    {
        $this->command->info('❓ SS6: Creating inquiries in various states...');

        // Get GM user for responded_by
        $gm = User::where('email', 'test.gm@citiescapes.test')->first();

        // Pending inquiry
        Inquiry::firstOrCreate(
            ['sender_name' => 'TEST Inquiry 1', 'contact_number' => '09001234567'],
            [
                'sender_name' => 'TEST Inquiry 1',
                'contact_number' => '09001234567',
                'email' => 'test.inquiry1@citiescapes.test',
                'preferred_room_type' => 'compact',
                'status' => 'pending',
                'message' => 'TEST: Interested in compact rooms',
            ]
        );

        // Responded inquiry
        Inquiry::firstOrCreate(
            ['sender_name' => 'TEST Inquiry 2', 'contact_number' => '09007654321'],
            [
                'sender_name' => 'TEST Inquiry 2',
                'contact_number' => '09007654321',
                'email' => 'test.inquiry2@citiescapes.test',
                'preferred_room_type' => 'spacious',
                'status' => 'responded',
                'message' => 'TEST: Asking about spacious rooms',
                'gm_notes' => 'We have available rooms for ₱5000/month',
                'responded_at' => now()->subDays(5),
                'responded_by' => $gm?->id,
            ]
        );

        $this->command->line("  ✓ Created inquiries: pending, responded");
    }
}
