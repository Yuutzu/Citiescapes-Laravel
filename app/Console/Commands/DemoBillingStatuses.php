<?php

namespace App\Console\Commands;

class DemoBillingStatuses extends DemoSeedHelper
{
    protected $signature = 'demo:billing-statuses';
    protected $description = 'Demo every bill status: unpaid → grace → overdue → delinquent → eviction → paid + override + initial payment';

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
            'Admin → Billing Management — show all 6 status colors in one table',
            'Click filter tabs (Unpaid / Grace / Overdue / Delinquent / Eviction / Paid) to walk through statuses',
            'Run `php artisan billing:apply-penalties` — bills auto-escalate, tenants get bell + email per stage',
            'On an eviction-status bill, click Apply Deposit → deposit credited as a Payment row',
            'On an eviction-status bill, click Mark for Termination → contract ends + tenant gets termination email',
            'Show the penalty-override row (GM-adjusted penalty with reason on the audit trail)',
            'Show the initial-payment receipt row (deposit + first month + key fee breakdown)',
        ];
    }
}
