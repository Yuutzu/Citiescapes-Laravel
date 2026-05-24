<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

/**
 * BBT_SS3_BILL_015 — Apply Penalty (Day 4).
 *
 * From the CS12L BBT/WBT documentation:
 *   Preconditions: Bill 4 days overdue; rate=100; grace=3.
 *   Test Input:    Run billing:apply-penalties cron.
 *   Expected:      penalty = 100; status = Overdue.
 *
 * This command seeds the exact precondition then runs the cron so the
 * resulting state is visible in the Billing Management UI for video capture.
 */
class DemoBbtSs3Bill015 extends Command
{
    protected $signature = 'demo:bbt-ss3-bill-015';
    protected $description = 'BBT_SS3_BILL_015 — seed a bill 4 days overdue (grace=3, rate=100) and run the penalty cron so the demo shows penalty=100 + status=Overdue';

    public function handle(): int
    {
        $this->newLine();
        $this->info('🎬 BBT_SS3_BILL_015 — Apply Penalty (Day 4)');
        $this->line('   Precondition: Bill 4 days overdue, contract penalty_rate=100, grace_days=3');
        $this->newLine();

        // Seed via the testbed's own bill.overdue_day4 scenario (same precondition).
        $this->call('testbed:seed', ['--scenario' => 'bill.overdue_day4']);

        $this->newLine();
        $this->info('⚙ Running billing:apply-penalties so penalty + status populate now…');
        $this->call('billing:apply-penalties');

        $this->newLine();
        $this->info('✅ Ready to record. Open http://citiescapes.test and:');
        $this->line('   • Log in as GM → Billing Management');
        $this->line('   • Find the seeded bill (4 days overdue) → status badge shows "Overdue" (orange)');
        $this->line('   • Penalty Amount column shows ₱100.00 (1 day past 3-day grace × ₱100 rate)');
        $this->line('   • Total Amount = base_rent + utilities + ₱100 penalty');
        $this->line('   • Audit Log → look for billing:apply-penalties cron output');
        $this->newLine();
        $this->line('   Wipe between recordings: php artisan db:wipe-test --no-confirm');

        // Tag this run in the audit log so the recording has evidence the demo was set up explicitly.
        AuditLog::record('demo_bbt_ss3_bill_015_seeded', null, 'system', 'SS3',
            'BBT_SS3_BILL_015 demo precondition seeded for video recording');

        return self::SUCCESS;
    }
}
