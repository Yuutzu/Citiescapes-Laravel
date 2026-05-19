<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\Room;
use Illuminate\Console\Command;

class SyncRoomContractStatus extends Command
{
    protected $signature = 'room:sync-status {--cleanup : Remove occupied flag from rooms with no active contract}';
    protected $description = 'Sync room status with active contracts for data integrity';

    public function handle(): int
    {
        $this->info('🔄 Syncing room occupancy status with active contracts...');

        // Step 1: Mark rooms as occupied if they have active contracts
        $activeContracts = Contract::active()->with('room')->get();
        $syncedCount = 0;

        foreach ($activeContracts as $contract) {
            if ($contract->room && $contract->room->status !== 'occupied') {
                $contract->room->update([
                    'status' => 'occupied',
                    'current_tenant_id' => $contract->tenant_id,
                    'last_status_update' => now(),
                ]);
                $syncedCount++;
                $this->line("  ✓ Room {$contract->room->room_number} marked as occupied (Contract #{$contract->id})");
            }
        }

        // Step 2: Optional cleanup - mark occupied rooms as available if no active contract
        if ($this->option('cleanup')) {
            $occupiedRooms = Room::where('status', 'occupied')->get();
            $cleanupCount = 0;

            foreach ($occupiedRooms as $room) {
                $hasActiveContract = Contract::active()
                    ->where('room_id', $room->id)
                    ->exists();

                if (!$hasActiveContract) {
                    $room->update([
                        'status' => 'available',
                        'current_tenant_id' => null,
                        'last_status_update' => now(),
                    ]);
                    $cleanupCount++;
                    $this->line("  🧹 Room {$room->room_number} marked as available (no active contract)");
                }
            }

            $this->info("\nCleaned up {$cleanupCount} orphaned occupied rooms.");
        }

        // Summary stats
        $total = Room::count();
        $occupied = Room::occupied()->count();
        $maintenance = Room::underMaintenance()->count();
        $available = $total - $occupied - $maintenance;

        $this->newLine();
        $this->info('Summary:');
        $this->line("  Total rooms:       {$total}");
        $this->line("  Occupied:          {$occupied}");
        $this->line("  Under maintenance: {$maintenance}");
        $this->line("  Available:         {$available}");
        $this->newLine();
        $this->info("✅ Synced {$syncedCount} rooms. All data is now consistent.");

        return Command::SUCCESS;
    }
}
