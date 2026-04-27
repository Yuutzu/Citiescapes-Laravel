<?php

namespace App\Console\Commands;

use App\Models\Archive;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Console\Command;

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

            $count++;
        }

        $this->info("Auto-archived {$count} expired contracts.");
        return self::SUCCESS;
    }
}
