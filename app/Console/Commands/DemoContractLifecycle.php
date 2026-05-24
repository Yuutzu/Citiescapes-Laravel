<?php

namespace App\Console\Commands;

class DemoContractLifecycle extends DemoSeedHelper
{
    protected $signature = 'demo:contract-lifecycle';
    protected $description = 'Demo the full contract lifecycle: draft → step1-ack → active (green/amber/red) → expired → terminated';

    protected function scenarios(): array
    {
        return [
            'contract.draft',
            'contract.step1_ack_only',
            'contract.active_green',
            'contract.active_amber_30',
            'contract.active_red_7',
            'contract.expired_unarchived',
            'contract.terminated',
        ];
    }

    protected function whatToDemo(): array
    {
        return [
            'Admin → Contract Management — show all statuses side by side with timer badges',
            'Open a draft contract in a tenant browser → walk through Step 1 + Step 2 acknowledgement',
            'After Step 2: switch to admin Dashboard → the room turns BLUE in the occupancy donut',
            'Show the lease timer card: green (60+ days), amber (30 days), red (7 days)',
            'Run `php artisan contracts:send-warnings` → 30-day and 7-day tenants get bell + email',
            'Run `php artisan contracts:auto-archive` → expired contract moves to SS5 archive with audit row',
            'Click Terminate on an active contract → tenant gets bell + email (ContractTerminatedMail)',
        ];
    }
}
