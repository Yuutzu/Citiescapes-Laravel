<?php

namespace App\Console\Commands;

class DemoBillingStatuses extends DemoSeedHelper
{
    protected $signature = 'demo:billing-statuses {--no-penalties : Skip running the penalty cron after seeding (leaves bills with zero penalty for status-only demos)}';
    protected $description = 'Demo every bill status: unpaid → grace → overdue → delinquent → eviction → paid + override + initial payment (auto-runs the penalty cron so amounts populate immediately)';

    protected function scenarios(): array
    {
        return [
            'bill.unpaid_future',
            'bill.grace_day1',
            'bill.grace_day3',
            'bill.overdue_day4',
            'bill.overdue_day13',
            'bill.delinquent_day14',
            'bill.delinquent_day29',
            'bill.eviction_day30',
            'bill.eviction_day45',
            'bill.paid',
            'bill.penalty_override',
            'bill.initial_payment',
        ];
    }

    protected function whatToDemo(): array
    {
        return [
            'Admin → Billing Management — show all 6 status colors in one table WITH penalties populated',
            'Click filter tabs (Unpaid / Grace / Overdue / Delinquent / Eviction / Paid) to walk through statuses',
            'Hover an overdue bill — penalty_amount = ₱100/day × (days_overdue - 3 grace days)',
            'On an eviction-status bill, click Apply Deposit → deposit credited as a Payment row',
            'On an eviction-status bill, click Mark for Termination → contract ends + tenant gets termination email',
            'Show the penalty-override row (GM-adjusted penalty with reason on the audit trail)',
            'Show the initial-payment receipt row (deposit + first month + key fee breakdown)',
        ];
    }

    /**
     * Override the base handle to run the penalty cron after seeding. Without
     * this, the seeded overdue/delinquent/eviction bills carry the correct
     * status but penalty_amount=0 and total_amount=base+utilities only —
     * confusing in a live client demo because "where are the penalties?"
     */
    public function handle(): int
    {
        $base = parent::handle();
        if ($base !== self::SUCCESS) return $base;

        if (!$this->option('no-penalties')) {
            $this->newLine();
            $this->info('⚙ Running billing:apply-penalties so penalty amounts populate now…');
            $this->call('billing:apply-penalties');
        }

        return self::SUCCESS;
    }
}
