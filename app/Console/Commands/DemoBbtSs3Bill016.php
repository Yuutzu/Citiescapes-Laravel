<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Bill;
use App\Models\Contract;
use App\Models\Room;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * BBT_SS3_BILL_016 — Apply Penalty (Day 10).
 *
 * From the CS12L BBT/WBT documentation:
 *   Preconditions: Bill 10 days overdue; rate=100; grace=3.
 *   Test Input:    Run billing:apply-penalties cron.
 *   Expected:      penalty = (10 − 3) × 100 = 700; status = Overdue.
 *
 * The testbed ships day-4 and day-13 scenarios but not day-10 specifically,
 * so this command seeds the exact day-10 row inline before running the cron.
 */
class DemoBbtSs3Bill016 extends Command
{
    protected $signature = 'demo:bbt-ss3-bill-016';
    protected $description = 'BBT_SS3_BILL_016 — seed a bill 10 days overdue (grace=3, rate=100) and run the penalty cron so the demo shows penalty=700 + status=Overdue';

    public function handle(): int
    {
        $this->newLine();
        $this->info('🎬 BBT_SS3_BILL_016 — Apply Penalty (Day 10)');
        $this->line('   Precondition: Bill 10 days overdue, contract penalty_rate=100, grace_days=3');
        $this->line('   Expected:     penalty = (10−3) × 100 = ₱700, status = Overdue');
        $this->newLine();

        // Anchor records — pick or create the demo tenant + first available room.
        $tenant = User::firstOrCreate(
            ['email' => 'bbt015.tenant@demo.local'],
            [
                'full_name'    => 'Demo Tenant (BBT_SS3_BILL_016)',
                'password'     => Hash::make('password'),
                'role'         => 'tenant',
                'status'       => 'active',
                'activated_at' => now(),
            ]
        );
        $room = Room::where('status', 'available')->orderBy('id')->first();
        if (!$room) {
            $this->error('No available room to anchor the contract on. Run `php artisan db:wipe-test --no-confirm` first.');
            return self::FAILURE;
        }

        // Active contract with the BBT-mandated penalty terms.
        $contract = Contract::create([
            'tenant_id'           => $tenant->id,
            'room_id'             => $room->id,
            'base_rent_rate'      => 4500,
            'deposit'             => 4500,
            'room_key_fee'        => 200,
            'start_date'          => now()->subMonths(2)->toDateString(),
            'end_date'            => now()->addMonths(10)->toDateString(),
            'status'              => 'active',
            'penalty_rate'        => 100,    // BBT precondition
            'penalty_grace_days'  => 3,      // BBT precondition
            'activated_at'        => now()->subMonths(2),
            'created_by'          => null,
        ]);

        // Bill 10 days overdue, seeded with status=unpaid so the cron makes the visible transition.
        Bill::create([
            'tenant_id'      => $tenant->id,
            'contract_id'    => $contract->id,
            'room_id'        => $room->id,
            'type'           => 'monthly',
            'billing_period' => now()->subDays(10)->format('Y-m'),
            'base_rent'      => $contract->base_rent_rate,
            'utilities'      => 1000,
            'electricity'    => 500,
            'water'          => 200,
            'wifi'           => 300,
            'total_amount'   => $contract->base_rent_rate + 1000,
            'due_date'       => now()->subDays(10)->toDateString(),
            'status'         => 'unpaid',
        ]);

        $this->info('⚙ Running billing:apply-penalties so penalty + status populate now…');
        $this->call('billing:apply-penalties');

        $this->newLine();
        $this->info('✅ Ready to record. Open http://citiescapes.test and:');
        $this->line('   • Log in as GM → Billing Management');
        $this->line('   • Find the bill for "Demo Tenant (BBT_SS3_BILL_016)" → status = "Overdue" (orange badge)');
        $this->line('   • Penalty Amount = ₱700.00  ((10 − 3 grace) × ₱100 rate)');
        $this->line('   • Total Amount = base + utilities + ₱700 penalty');
        $this->newLine();
        $this->line('   Wipe between recordings: php artisan db:wipe-test --no-confirm');

        AuditLog::record('demo_bbt_ss3_bill_016_seeded', null, 'system', 'SS3',
            'BBT_SS3_BILL_016 demo precondition seeded (10-day-overdue bill) for video recording');

        return self::SUCCESS;
    }
}
