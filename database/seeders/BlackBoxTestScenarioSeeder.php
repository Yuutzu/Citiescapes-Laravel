<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Archive;
use App\Models\Bill;
use App\Models\Contract;
use App\Models\InitialPayment;
use App\Models\Inquiry;
use App\Models\NotificationLog;
use App\Models\OtpRecord;
use App\Models\Payment;
use App\Models\Room;
use App\Models\TenantRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * BlackBoxTestScenarioSeeder
 * 
 * Comprehensive black-box test scenarios for all subsystems.
 * Covers equivalence partitioning and boundary value analysis.
 *
 * Run: php artisan db:seed --class=BlackBoxTestScenarioSeeder
 *
 * Subsystems covered:
 *   SS1: Public Listings & Inquiries
 *   SS2: Tenant Management
 *   SS3: Billing Management
 *   SS4: Contract Management
 *   SS5: Reports & Archives
 *   SS6: System Administration (Auth)
 */
class BlackBoxTestScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🧪 Creating Black-Box Test Scenarios...');
        $this->command->newLine();

        // Setup: Create users
        $gm = $this->createGMAccounts();
        $tenants = $this->createTenantScenarios();
        $rooms = $this->createRoomScenarios();

        // SS4: Create contracts with various scenarios
        $this->createContractScenarios($tenants, $rooms, $gm);

        // SS5: Create bills in various states (paid, unpaid, overdue, grace, delinquent)
        $this->createBillingScenarios($tenants, $gm);

        // SS6: Create inquiries in various states
        $this->createInquiryScenarios($gm);

        // SS2 Extended: Create tenant requests
        $this->createTenantRequestScenarios($tenants, $gm);

        // SS5 Extended: Create announcements
        $this->createAnnouncementScenarios($gm);

        $this->command->newLine();
        $this->command->info('✅ Black-box test scenarios created successfully!');
        $this->displayTestAccounts();
    }

    private function createGMAccounts(): User
    {
        $this->command->info('👤 SS6: Creating GM accounts...');

        // Primary GM
        $gm = User::firstOrCreate(
            ['email' => 'bb.gm.primary@test.local'],
            [
                'full_name' => '[BB] GM - Primary',
                'password' => Hash::make('password'),
                'role' => 'gm',
                'status' => 'active',
                'activated_at' => now()->subMonths(6),
                'email_verified_at' => now(),
                'must_change_password' => false,
            ]
        );

        // Secondary GM (for multiple GMs notification testing)
        User::firstOrCreate(
            ['email' => 'bb.gm.secondary@test.local'],
            [
                'full_name' => '[BB] GM - Secondary',
                'password' => Hash::make('password'),
                'role' => 'gm',
                'status' => 'active',
                'activated_at' => now()->subMonths(5),
                'email_verified_at' => now(),
                'must_change_password' => false,
            ]
        );

        $this->command->line('  ✓ Created 2 GM accounts');
        return $gm;
    }

    private function createTenantScenarios(): array
    {
        $this->command->info('👥 SS2: Creating tenant scenarios...');

        $tenants = [];

        // EP: Valid active tenant with contract
        $tenants['active_with_contract'] = User::firstOrCreate(
            ['email' => 'bb.tenant.active1@test.local'],
            [
                'full_name' => '[BB] Tenant - Active (Alice)',
                'password' => Hash::make('password'),
                'role' => 'tenant',
                'status' => 'active',
                'activated_at' => now()->subMonths(4),
                'email_verified_at' => now(),
            ]
        );

        // EP: Valid active tenant without contract (just created)
        $tenants['active_no_contract'] = User::firstOrCreate(
            ['email' => 'bb.tenant.active2@test.local'],
            [
                'full_name' => '[BB] Tenant - Active No Contract (Bob)',
                'password' => Hash::make('password'),
                'role' => 'tenant',
                'status' => 'active',
                'activated_at' => now()->subDays(10),
                'email_verified_at' => now(),
            ]
        );

        // EP: Pending activation tenant
        $tenants['pending'] = User::firstOrCreate(
            ['email' => 'bb.tenant.pending@test.local'],
            [
                'full_name' => '[BB] Tenant - Pending Activation (Charlie)',
                'password' => Hash::make('password'),
                'role' => 'tenant',
                'status' => 'pending_activation',
            ]
        );

        // EP: Archived tenant
        $tenants['archived'] = User::firstOrCreate(
            ['email' => 'bb.tenant.archived@test.local'],
            [
                'full_name' => '[BB] Tenant - Archived (David)',
                'password' => Hash::make('password'),
                'role' => 'tenant',
                'status' => 'archived',
                'activated_at' => now()->subMonths(12),
                'archived_at' => now()->subMonths(2),
                'email_verified_at' => now(),
            ]
        );

        // BVA: Name with boundary length (max 100 chars)
        $tenants['max_name_length'] = User::firstOrCreate(
            ['email' => 'bb.tenant.maxname@test.local'],
            [
                'full_name' => '[BB] ' . str_repeat('A', 95), // 100 chars total
                'password' => Hash::make('password'),
                'role' => 'tenant',
                'status' => 'active',
                'activated_at' => now()->subMonths(1),
                'email_verified_at' => now(),
            ]
        );

        $this->command->line('  ✓ Created 5 tenant scenarios');
        return $tenants;
    }

    private function createRoomScenarios(): array
    {
        $this->command->info('🏠 SS3: Creating room scenarios...');

        $rooms = [];

        // Available rooms
        $rooms['available_compact1'] = Room::firstOrCreate(
            ['room_number' => 'BB_A_001'],
            [
                'floor_level' => 1,
                'room_type' => 'compact',
                'rate' => 3500.00,
                'max_occupants' => 2,
                'status' => 'available',
                'amenities' => ['WiFi', 'AC'],
            ]
        );

        $rooms['available_compact2'] = Room::firstOrCreate(
            ['room_number' => 'BB_A_002'],
            [
                'floor_level' => 1,
                'room_type' => 'compact',
                'rate' => 3500.00,
                'max_occupants' => 2,
                'status' => 'available',
                'amenities' => ['WiFi'],
            ]
        );

        $rooms['available_spacious'] = Room::firstOrCreate(
            ['room_number' => 'BB_A_003'],
            [
                'floor_level' => 2,
                'room_type' => 'spacious',
                'rate' => 5000.00,
                'max_occupants' => 4,
                'status' => 'available',
                'amenities' => ['WiFi', 'AC', 'Private Bath'],
            ]
        );

        // Occupied room
        $rooms['occupied'] = Room::firstOrCreate(
            ['room_number' => 'BB_O_001'],
            [
                'floor_level' => 2,
                'room_type' => 'spacious',
                'rate' => 5000.00,
                'max_occupants' => 3,
                'status' => 'occupied',
                'amenities' => ['Air Conditioning', 'WiFi', 'Private Bathroom'],
                'description' => '[BB] Spacious test room (occupied) for black-box scenarios',
                'photos' => [],
            ]
        );

        // Under maintenance
        $rooms['maintenance'] = Room::firstOrCreate(
            ['room_number' => 'BB_M_001'],
            [
                'floor_level' => 3,
                'room_type' => 'compact',
                'rate' => 3500.00,
                'max_occupants' => 2,
                'status' => 'under_maintenance',
                'amenities' => ['WiFi'],
                'description' => '[BB] Compact test room (maintenance) for black-box scenarios',
                'photos' => [],
            ]
        );

        $this->command->line('  ✓ Created 5 room scenarios');
        return $rooms;
    }

    private function createContractScenarios(array $tenants, array $rooms, User $gm): void
    {
        $this->command->info('📋 SS4: Creating contract scenarios...');

        // Active contract (ongoing)
        Contract::firstOrCreate(
            ['tenant_id' => $tenants['active_with_contract']->id],
            [
                'room_id' => $rooms['occupied']->id,
                'base_rent_rate' => 5000.00,
                'deposit' => 5000.00,
                'room_key_fee' => 200.00,
                'start_date' => now()->subMonths(2)->toDateString(),
                'end_date' => now()->addMonths(4)->toDateString(),
                'status' => 'active',
                'activated_at' => now()->subMonths(2),
                'penalty_rate' => 100.00,
                'penalty_grace_days' => 3,
                'created_by' => $gm->id,
            ]
        );

        // Create initial payment for active contract
        InitialPayment::firstOrCreate(
            ['contract_id' => Contract::where('tenant_id', $tenants['active_with_contract']->id)->first()->id ?? 1],
            [
                'tenant_id' => $tenants['active_with_contract']->id,
                'deposit_amount' => 5000.00,
                'first_month_rent' => 5000.00,
                'room_key_fee' => 200.00,
                'total_collected' => 10200.00,
                'date_received' => now()->subMonths(2),
                'payment_method' => 'cash',
                'recorded_by' => $gm->id,
            ]
        );

        // Expired contract
        Contract::firstOrCreate(
            ['tenant_id' => $tenants['archived']->id],
            [
                'room_id' => $rooms['available_compact1']->id,
                'base_rent_rate' => 3500.00,
                'deposit' => 3500.00,
                'room_key_fee' => 150.00,
                'start_date' => now()->subMonths(13)->toDateString(),
                'end_date' => now()->subMonths(1)->toDateString(),
                'status' => 'expired',
                'activated_at' => now()->subMonths(13),
                'terminated_at' => now()->subMonths(1),
                'penalty_rate' => 100.00,
                'penalty_grace_days' => 3,
                'created_by' => $gm->id,
            ]
        );

        $this->command->line('  ✓ Created contract scenarios (active, expired)');
    }

    private function createBillingScenarios(array $tenants, User $gm): void
    {
        $this->command->info('💰 SS5: Creating billing scenarios...');

        $contract = Contract::where('tenant_id', $tenants['active_with_contract']->id)->first();
        if (!$contract)
            return;

        // Paid bill (BVA: at boundary, fully paid)
        $paidBill = Bill::firstOrCreate(
            ['contract_id' => $contract->id, 'billing_period' => now()->subMonths(2)->format('Y-m')],
            [
                'tenant_id' => $tenants['active_with_contract']->id,
                'room_id' => $contract->room_id,
                'type' => 'monthly',
                'base_rent' => 5000.00,
                'utilities' => 500.00,
                'total_amount' => 5500.00,
                'due_date' => now()->subMonths(2)->endOfMonth()->toDateString(),
                'status' => 'paid',
                'paid_at' => now()->subMonths(2)->endOfMonth()->addDays(1),
            ]
        );

        Payment::firstOrCreate(
            ['bill_id' => $paidBill->id],
            [
                'tenant_id' => $tenants['active_with_contract']->id,
                'amount' => 5500.00,
                'payment_method' => 'bank_transfer',
                'confirmed_at' => now()->subMonths(2)->endOfMonth()->addDays(1),
                'confirmed_by' => $gm->id,
            ]
        );

        // Unpaid bill (current month)
        Bill::firstOrCreate(
            ['contract_id' => $contract->id, 'billing_period' => now()->format('Y-m')],
            [
                'tenant_id' => $tenants['active_with_contract']->id,
                'room_id' => $contract->room_id,
                'type' => 'monthly',
                'base_rent' => 5000.00,
                'utilities' => 500.00,
                'total_amount' => 5500.00,
                'due_date' => now()->endOfMonth()->toDateString(),
                'status' => 'unpaid',
            ]
        );

        // Grace period bill (1-3 days overdue)
        Bill::firstOrCreate(
            ['contract_id' => $contract->id, 'billing_period' => now()->subMonths(1)->format('Y-m')],
            [
                'tenant_id' => $tenants['active_with_contract']->id,
                'room_id' => $contract->room_id,
                'type' => 'monthly',
                'base_rent' => 5000.00,
                'utilities' => 500.00,
                'total_amount' => 5500.00,
                'due_date' => now()->subMonths(1)->endOfMonth()->toDateString(),
                'status' => 'grace',
                'days_overdue' => 2,
            ]
        );

        // Overdue bill (4-13 days)
        Bill::firstOrCreate(
            ['contract_id' => $contract->id, 'billing_period' => now()->subMonths(2)->startOfMonth()->format('Y-m'), 'type' => 'monthly'],
            [
                'tenant_id' => $tenants['active_with_contract']->id,
                'room_id' => $contract->room_id,
                'type' => 'monthly',
                'base_rent' => 5000.00,
                'utilities' => 500.00,
                'penalty_amount' => 400.00, // 4 days * 100
                'total_amount' => 5900.00,
                'due_date' => now()->subMonths(2)->startOfMonth()->toDateString(),
                'status' => 'overdue',
                'days_overdue' => 8,
            ]
        );

        // Delinquent bill (14+ days)
        Bill::firstOrCreate(
            ['contract_id' => $contract->id, 'billing_period' => now()->subMonths(3)->format('Y-m')],
            [
                'tenant_id' => $tenants['active_with_contract']->id,
                'room_id' => $contract->room_id,
                'type' => 'monthly',
                'base_rent' => 5000.00,
                'utilities' => 500.00,
                'penalty_amount' => 1200.00, // 12 days * 100
                'total_amount' => 6700.00,
                'due_date' => now()->subMonths(3)->startOfMonth()->toDateString(),
                'status' => 'delinquent',
                'days_overdue' => 20,
            ]
        );

        $this->command->line('  ✓ Created billing scenarios (paid, unpaid, grace, overdue, delinquent)');
    }

    private function createInquiryScenarios(User $gm): void
    {
        $this->command->info('❓ SS1: Creating inquiry scenarios...');

        // Pending inquiry
        Inquiry::firstOrCreate(
            ['sender_name' => '[BB] Inquiry - Pending', 'contact_number' => '09001111111'],
            [
                'contact_number' => '09001111111',
                'email' => 'bb.inquiry.pending@test.local',
                'preferred_room_type' => 'compact',
                'status' => 'pending',
                'message' => '[BB] Interested in compact rooms',
            ]
        );

        // Responded inquiry
        Inquiry::firstOrCreate(
            ['sender_name' => '[BB] Inquiry - Responded', 'contact_number' => '09002222222'],
            [
                'contact_number' => '09002222222',
                'email' => 'bb.inquiry.responded@test.local',
                'preferred_room_type' => 'spacious',
                'status' => 'responded',
                'message' => '[BB] Asking about spacious rooms',
                'gm_notes' => '[BB] Available for ₱5000/month',
                'responded_by' => $gm->id,
                'responded_at' => now()->subDays(5),
            ]
        );

        // Max length inquiry
        Inquiry::firstOrCreate(
            ['sender_name' => '[BB] Inquiry - Max Length', 'contact_number' => '09003333333'],
            [
                'contact_number' => '09003333333',
                'email' => 'bb.inquiry.maxlength@test.local',
                'preferred_room_type' => 'any',
                'status' => 'pending',
                'message' => '[BB] ' . str_repeat('A', 495), // 500 chars
            ]
        );

        $this->command->line('  ✓ Created inquiry scenarios (pending, responded, max-length)');
    }

    private function createTenantRequestScenarios(array $tenants, User $gm): void
    {
        $this->command->info('📞 SS2: Creating tenant request scenarios...');

        if (isset($tenants['active_with_contract'])) {
            // Pending request
            TenantRequest::firstOrCreate(
                ['tenant_id' => $tenants['active_with_contract']->id, 'type' => 'request'],
                [
                    'type' => 'request',
                    'status' => 'pending',
                    'subject' => '[BB] Air conditioning repair needed',
                    'body' => 'The AC in my room stopped working yesterday',
                ]
            );

            // Resolved request
            TenantRequest::firstOrCreate(
                ['tenant_id' => $tenants['active_with_contract']->id, 'type' => 'complaint'],
                [
                    'type' => 'complaint',
                    'status' => 'resolved',
                    'subject' => '[BB] Noise complaint - resolved',
                    'body' => 'Noise from neighbors',
                    'admin_response' => '[BB] Spoke with neighbors, issue resolved',
                    'responded_by' => $gm->id,
                    'responded_at' => now()->subDays(3),
                ]
            );
        }

        $this->command->line('  ✓ Created tenant request scenarios');
    }

    private function createAnnouncementScenarios(User $gm): void
    {
        $this->command->info('📢 SS5: Creating announcement scenarios...');

        Announcement::firstOrCreate(
            ['title' => '[BB] Announcement - Active'],
            [
                'title' => '[BB] Announcement - Active',
                'body' => 'This is an active announcement',
                'recipient_type' => 'all',
                'sent_by' => $gm->id,
                'email_sent' => true,
            ]
        );

        // Note: archived announcements would need a soft_deletes or separate status field
        // For now, we'll just create one active announcement

        $this->command->line('  ✓ Created announcement scenarios');
    }

    private function displayTestAccounts(): void
    {
        $this->command->newLine();
        $this->command->info('📋 Test Accounts Created:');
        $this->command->line('');
        $this->command->line('  GM (Primary):');
        $this->command->line('    Email: bb.gm.primary@test.local');
        $this->command->line('    Password: password');
        $this->command->line('');
        $this->command->line('  Tenant (Active with Contract):');
        $this->command->line('    Email: bb.tenant.active1@test.local');
        $this->command->line('    Password: password');
        $this->command->line('');
        $this->command->info('💡 All test data prefixed with [BB] for easy identification');
    }
}
