<?php

namespace App\Console\Commands;

use App\Mail\ContractTerminatedMail;
use App\Models\Archive;
use App\Models\AuditLog;
use App\Models\Contract;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class AutoArchiveExpiredContracts extends Command
{
    protected $signature = 'contracts:auto-archive';
    protected $description = 'Auto-archive contracts whose end_date has passed (SS4 → SS5)';

    public function handle(): int
    {
        $contracts = Contract::active()
            ->whereDate('end_date', '<', now()->startOfDay())
            ->with(['tenant', 'room'])
            ->get();

        $count = 0;

        foreach ($contracts as $contract) {
            // Set contract to expired
            $contract->update(['status' => 'expired']);

            // Archive contract record to SS5
            Archive::create([
                'original_record_id' => $contract->id,
                'record_type'        => 'contract',
                'source_subsystem'   => 'SS4',
                'archive_reason'     => 'Contract expired on ' . $contract->end_date->format('Y-m-d'),
                'data'               => $contract->toArray(),
                'scan_file_path'     => $contract->scan_file_path,
                'archived_by'        => null, // system
            ]);

            // Auto-archive the tenant account (SS2)
            if ($contract->tenant && $contract->tenant->status === 'active') {
                $contract->tenant->update([
                    'status'      => 'archived',
                    'archived_at' => now(),
                ]);

                Archive::create([
                    'original_record_id' => $contract->tenant->id,
                    'record_type'        => 'tenant_account',
                    'source_subsystem'   => 'SS2',
                    'archive_reason'     => 'Contract expired — auto-archived',
                    'data'               => $contract->tenant->toArray(),
                ]);
            }

            // Reset room status if still occupied
            if ($contract->room && $contract->room->current_tenant_id === $contract->tenant_id) {
                $contract->room->update([
                    'current_tenant_id'  => null,
                    'status'             => 'available',
                    'last_status_update' => now(),
                ]);
            }

            // Notify the tenant + audit row. Without these, a tenant logs in
            // to find their account archived with no notice and the GM has no
            // record of what fired the auto-archive.
            $this->notifyTenantAndAudit($contract);

            $count++;
        }

        $this->info("Auto-archived {$count} expired contracts.");
        return self::SUCCESS;
    }

    /**
     * Cron-driven termination still deserves the same notification + audit
     * footprint as a manual GM termination: bell (authoritative), email
     * (best-effort), and an audit row tagged actor=system.
     */
    private function notifyTenantAndAudit(Contract $contract): void
    {
        AuditLog::record('contract_auto_archived', null, 'system', 'SS4',
            "Contract #{$contract->id} auto-archived (end_date {$contract->end_date->format('Y-m-d')} passed)");

        if (!$contract->tenant) return;

        $endedOn = $contract->end_date->format('M d, Y');

        NotificationLog::create([
            'user_id' => $contract->tenant_id,
            'type'    => 'contract_auto_archived',
            'source'  => 'SS4',
            'message' => "Your lease for Room {$contract->room?->room_number} ended on {$endedOn} and was auto-archived. Contact the General Manager to renew.",
        ]);

        if ($contract->tenant->email) {
            try {
                Mail::to($contract->tenant->email)->send(
                    new ContractTerminatedMail(
                        $contract->tenant->full_name,
                        (string) ($contract->room?->room_number ?? '—'),
                        $endedOn,
                        "Lease term ended on {$endedOn}. The system auto-archived your contract on its scheduled expiry date.",
                    )
                );
            } catch (\Throwable $e) {
                \Log::error('Auto-archive ContractTerminatedMail send failed', [
                    'contract_id' => $contract->id,
                    'error'       => $e->getMessage(),
                ]);
            }
        }
    }
}
