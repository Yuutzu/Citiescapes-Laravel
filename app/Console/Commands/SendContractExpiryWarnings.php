<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Console\Command;

class SendContractExpiryWarnings extends Command
{
    protected $signature = 'contracts:send-warnings';
    protected $description = 'Send 30-day and 7-day contract expiry warnings (SS4)';

    public function handle(): int
    {
        $sent = 0;

        // 30-day warning
        $contracts30 = Contract::active()
            ->where('warning_30_sent', false)
            ->whereDate('end_date', '<=', now()->addDays(30))
            ->whereDate('end_date', '>', now()->addDays(7))
            ->with('tenant')
            ->get();

        foreach ($contracts30 as $contract) {
            $this->sendWarning($contract, 30);
            $contract->update(['warning_30_sent' => true]);
            $sent++;
        }

        // 7-day warning
        $contracts7 = Contract::active()
            ->where('warning_7_sent', false)
            ->whereDate('end_date', '<=', now()->addDays(7))
            ->whereDate('end_date', '>', now())
            ->with('tenant')
            ->get();

        foreach ($contracts7 as $contract) {
            $this->sendWarning($contract, 7);
            $contract->update(['warning_7_sent' => true]);
            $sent++;
        }

        $this->info("Sent {$sent} expiry warnings.");
        return self::SUCCESS;
    }

    private function sendWarning(Contract $contract, int $days): void
    {
        $msg = $days === 30
            ? "Your lease contract (Room {$contract->room->room_number}) will expire in approximately 30 days on {$contract->end_date->format('M d, Y')}."
            : "URGENT: Your lease contract (Room {$contract->room->room_number}) will expire in 7 days on {$contract->end_date->format('M d, Y')}. Please contact management.";

        // Notify tenant
        NotificationLog::create([
            'user_id' => $contract->tenant_id,
            'type'    => "{$days}_day_warning",
            'source'  => 'SS4',
            'message' => $msg,
        ]);

        // Notify GM(s)
        $gms = User::where('role', 'gm')->where('status', 'active')->get();
        foreach ($gms as $gm) {
            NotificationLog::create([
                'user_id' => $gm->id,
                'type'    => "{$days}_day_warning",
                'source'  => 'SS4',
                'message' => "Contract for tenant {$contract->tenant->full_name} (Room {$contract->room->room_number}) expires in {$days} days.",
            ]);
        }
    }
}
