<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Mirrors the 10 white-box scenarios from docs/WHITE_BOX_TESTING.md
 * (WBT_CONT_001..010) into the live database, so the same fixtures the
 * PHPUnit suite exercises can be inspected through the admin Contract
 * Manager and the tenant dashboard.
 *
 * Run:
 *   php artisan db:seed --class=WhiteBoxContractTimerScenarioSeeder
 *
 * Unlike the billing module, no follow-up artisan command is needed:
 * Contract::getTimerBadgeAttribute() is a computed accessor — the badge
 * color is derived on read from (status, end_date), so the rows display
 * the expected color the moment they're seeded.
 *
 * Idempotent: drops prior WBT contract-timer data on each run.
 */
class WhiteBoxContractTimerScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $this->cleanupPriorWbtData();

        // [id, status, daysRemaining, expectedBadge, label]
        // daysRemaining is the offset from today applied to end_date.
        $scenarios = [
            ['WBT_CONT_001', 'draft',       60, 'gray',  'non-active (draft) → gray'],
            ['WBT_CONT_002', 'terminated',  60, 'gray',  'non-active (terminated) → gray'],
            ['WBT_CONT_003', 'active',       0, 'gray',  'active, 0 days remaining → gray'],
            ['WBT_CONT_004', 'active',      -5, 'gray',  'active, past end_date → gray'],
            ['WBT_CONT_005', 'active',       1, 'red',   'active, 1 day remaining → red'],
            ['WBT_CONT_006', 'active',       7, 'red',   'active, 7 days (boundary) → red'],
            ['WBT_CONT_007', 'active',       8, 'amber', 'active, 8 days (boundary) → amber'],
            ['WBT_CONT_008', 'active',      30, 'amber', 'active, 30 days (boundary) → amber'],
            ['WBT_CONT_009', 'active',      31, 'green', 'active, 31 days (boundary) → green'],
            ['WBT_CONT_010', 'active',     365, 'green', 'active, far future → green'],
        ];

        $i = 0;
        foreach ($scenarios as [$id, $status, $daysRemaining, $expectedBadge, $label]) {
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
                'room_number'        => 'T'.str_pad((string)$i, 2, '0', STR_PAD_LEFT),
                'floor_level'        => 1,
                'room_type'          => 'compact',
                'rate'               => 10000,
                'max_occupants'      => 2,
                'status'             => 'occupied',
                'current_tenant_id'  => $tenant->id,
                'amenities'          => ['Air Conditioning', 'WiFi'],
                'description'        => "WBT scenario {$id}: {$label}",
            ]);

            $startDate = now()->subMonths(6)->toDateString();
            $endDate   = now()->addDays($daysRemaining)->toDateString();

            Contract::create([
                'tenant_id'      => $tenant->id,
                'room_id'        => $room->id,
                'base_rent_rate' => 10000,
                'deposit'        => 10000,
                'start_date'     => $startDate,
                'end_date'       => $endDate,
                'status'         => $status,
                'activated_at'   => $status === 'active' ? now()->subMonths(6) : null,
                'terminated_at'  => $status === 'terminated' ? now()->subDays(7) : null,
                'termination_reason' => $status === 'terminated' ? 'WBT scenario fixture' : null,
            ]);

            $this->command->info(sprintf('  ✓ %s  [%-10s]  end=%s  badge=%s  %s',
                $id, $status, $endDate, $expectedBadge, $label));
        }

        $this->command->info('');
        $this->command->info('Seeded '.count($scenarios).' WBT contract-timer scenarios.');
        $this->command->warn('Timer badges are computed on read — open the admin Contract Manager to view.');
    }

    /**
     * Wipe prior WBT contract-timer rows so the seeder can be re-run idempotently.
     * Order matters because of foreign keys: contracts → rooms → users.
     */
    private function cleanupPriorWbtData(): void
    {
        $userIds = User::withTrashed()
            ->where('email', 'like', 'wbt+wbt_cont_%@citiescapes.test')
            ->pluck('id');

        if ($userIds->isNotEmpty()) {
            Contract::withTrashed()->whereIn('tenant_id', $userIds)->forceDelete();
            Room::withTrashed()->whereIn('current_tenant_id', $userIds)->update(['current_tenant_id' => null]);
            User::withTrashed()->whereIn('id', $userIds)->forceDelete();
        }

        Room::withTrashed()->where('room_number', 'like', 'T%')->forceDelete();
    }
}
