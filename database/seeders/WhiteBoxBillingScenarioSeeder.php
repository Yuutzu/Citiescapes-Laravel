<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\Contract;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Mirrors the 14 white-box scenarios from docs/WHITE_BOX_TESTING.md
 * (WBT_BILL_001..014) into the live database, so the same fixtures
 * the PHPUnit suite exercises can be inspected in MySQL / the app UI.
 *
 * Run:
 *   php artisan db:seed --class=WhiteBoxBillingScenarioSeeder
 *   php artisan billing:apply-penalties     # to apply the penalty cascade
 *
 * Idempotent: drops prior WBT data on each run.
 */
class WhiteBoxBillingScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $this->cleanupPriorWbtData();

        // [id, daysOverdue, contractOverrides, billOverrides, label]
        $scenarios = [
            ['WBT_BILL_001',  1, [], [], 'grace day 1 (reminder)'],
            ['WBT_BILL_002',  2, [], [], 'grace day 2 (no reminder)'],
            ['WBT_BILL_003',  3, [], [], 'grace day 3 (reminder, last day)'],
            ['WBT_BILL_004',  4, [], [], 'overdue entry (day 4)'],
            ['WBT_BILL_005', 10, [], [], 'overdue middle (day 10)'],
            ['WBT_BILL_006', 13, [], [], 'overdue exit (day 13)'],
            ['WBT_BILL_007', 14, [], [], 'delinquent entry (day 14, notice)'],
            ['WBT_BILL_008', 20, [], [], 'delinquent middle (day 20)'],
            ['WBT_BILL_009', 29, [], [], 'delinquent exit (day 29)'],
            ['WBT_BILL_010', 30, [], [], 'eviction entry (day 30, notice)'],
            ['WBT_BILL_011', 45, [], [], 'eviction beyond (day 45)'],
            ['WBT_BILL_012', 10, [], ['status' => 'paid', 'paid_at' => now()->subDays(5)], 'paid (skipped by query)'],
            ['WBT_BILL_013',  5, ['penalty_grace_days' => 5], [], 'custom grace days = 5'],
            ['WBT_BILL_014',  7, [], [], 'default penalty rate path'],
        ];

        $i = 0;
        foreach ($scenarios as [$id, $daysOverdue, $contractOverrides, $billOverrides, $label]) {
            $i++;

            $tenant = User::create([
                'full_name'            => "WBT Tenant {$id}",
                'email'                => 'wbt+'.strtolower($id).'@citiescapes.test',
                'password'             => Hash::make('password'),
                'role'                 => 'tenant',
                'status'               => 'active',
                'must_change_password' => false,
                'activated_at'         => now(),
                'email_verified_at'    => now(),
            ]);

            $room = Room::create([
                'room_number'        => 'W'.str_pad((string)$i, 2, '0', STR_PAD_LEFT),
                'floor_level'        => 1,
                'room_type'          => 'compact',
                'rate'               => 10000,
                'max_occupants'      => 2,
                'status'             => 'occupied',
                'current_tenant_id'  => $tenant->id,
                'amenities'          => ['Air Conditioning', 'WiFi'],
                'photos'             => [],
                'description'        => "WBT scenario {$id}: {$label}",
            ]);

            $contract = Contract::create(array_merge([
                'tenant_id'      => $tenant->id,
                'room_id'        => $room->id,
                'base_rent_rate' => 10000,
                'deposit'        => 10000,
                'start_date'     => now()->subMonths(2)->toDateString(),
                'end_date'       => now()->addMonths(10)->toDateString(),
                'status'         => 'active',
                'activated_at'   => now()->subMonths(2),
            ], $contractOverrides));

            Bill::create(array_merge([
                'tenant_id'      => $tenant->id,
                'contract_id'    => $contract->id,
                'room_id'        => $room->id,
                'type'           => 'monthly',
                'billing_period' => now()->subMonth()->format('Y-m'),
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

            $this->command->info(sprintf('  ✓ %s  (%2dd overdue)  %s', $id, $daysOverdue, $label));
        }

        $this->command->info('');
        $this->command->info('Seeded '.count($scenarios).' WBT billing scenarios.');
        $this->command->warn('Next: run  php artisan billing:apply-penalties  to apply the cascade.');
    }

    /**
     * Wipe prior WBT scenario rows so the seeder can be re-run idempotently.
     * Order matters because of foreign keys: bills → contracts → rooms → users.
     */
    private function cleanupPriorWbtData(): void
    {
        $userIds = User::withTrashed()
            ->where('email', 'like', 'wbt+%@citiescapes.test')
            ->pluck('id');

        if ($userIds->isNotEmpty()) {
            Bill::withTrashed()->whereIn('tenant_id', $userIds)->forceDelete();
            Contract::withTrashed()->whereIn('tenant_id', $userIds)->forceDelete();
            // Detach rooms before deleting users (current_tenant_id has nullOnDelete,
            // but we also drop the WBT-numbered rooms next).
            Room::withTrashed()->whereIn('current_tenant_id', $userIds)->update(['current_tenant_id' => null]);
            User::withTrashed()->whereIn('id', $userIds)->forceDelete();
        }

        Room::withTrashed()->where('room_number', 'like', 'W%')->forceDelete();
    }
}
