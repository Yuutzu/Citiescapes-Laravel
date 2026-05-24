<?php

namespace App\Console\Commands;

use App\Livewire\Admin\Tenants\TenantManager;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Console\Command;
use Livewire\Livewire;

/**
 * WBT_SS2_TEN_004 — TenantManager::archiveTenant (404 branch).
 *
 * From the CS12L BBT/WBT documentation:
 *   Preconditions: id missing in tenants table.
 *   Test Input:    archiveTenant(99999).
 *   Expected:      findOrFail throws 404; no Archive row created.
 *
 * This branch can't be reproduced through normal UI clicks (the Tenant
 * Management table never renders a row for a nonexistent ID). So this demo
 * command exercises the branch in headless mode and prints the before/after
 * proof for video capture. The operator can then read the output on-screen
 * during the recording.
 */
class DemoWbtSs2Ten004 extends Command
{
    protected $signature = 'demo:wbt-ss2-ten-004';
    protected $description = 'WBT_SS2_TEN_004 — invoke TenantManager::archiveTenant on a nonexistent ID and prove the 404 branch (no Archive row written, controller does not crash)';

    public function handle(): int
    {
        $this->newLine();
        $this->info('🎬 WBT_SS2_TEN_004 — archiveTenant 404 branch');
        $this->line('   Precondition: tenant id 99999 does not exist in the users table');
        $this->line('   Expected:     findOrFail throws ModelNotFoundException; no Archive row written');
        $this->newLine();

        $tenantId = 99999;
        if (User::find($tenantId)) {
            $this->error("User #{$tenantId} unexpectedly exists. Run `php artisan db:wipe-test --no-confirm` first.");
            return self::FAILURE;
        }

        // Anchor a GM user to act as (TenantManager requires auth).
        $gm = User::where('role', 'gm')->where('status', 'active')->first();
        if (!$gm) {
            $this->error('No active GM found. Run `php artisan db:seed --class=AdminOnlySeeder` first.');
            return self::FAILURE;
        }

        $archiveCountBefore = \App\Models\Archive::count();
        $this->line("Before:");
        $this->line("  • Users with id={$tenantId}: " . User::where('id', $tenantId)->count());
        $this->line("  • Archive rows: {$archiveCountBefore}");
        $this->newLine();

        $this->info("Invoking TenantManager::archiveTenant({$tenantId}) as GM…");
        $caught = null;
        try {
            Livewire::actingAs($gm)->test(TenantManager::class)
                ->call('archiveTenant', $tenantId);
        } catch (\Throwable $e) {
            $caught = $e;
        }

        $archiveCountAfter = \App\Models\Archive::count();
        $this->newLine();
        $this->line("After:");
        $this->line("  • Exception raised:    " . ($caught ? get_class($caught) : 'NONE'));
        $this->line("  • Exception message:   " . ($caught?->getMessage() ?: '-'));
        $this->line("  • Archive rows now:    {$archiveCountAfter}  (was {$archiveCountBefore})");
        $this->newLine();

        $is404Branch = $caught instanceof \Illuminate\Database\Eloquent\ModelNotFoundException
            && $archiveCountAfter === $archiveCountBefore;

        if ($is404Branch) {
            $this->info('✅ Branch verified: findOrFail threw ModelNotFoundException and NO Archive row was created.');
        } else {
            $this->error('❌ Branch not exercised as expected. Inspect the output above.');
            return self::FAILURE;
        }

        $this->newLine();
        $this->line('For video recording, you have two options:');
        $this->line('   (a) Record this terminal output as the proof (simplest, most honest).');
        $this->line('   (b) In a browser DevTools console while logged in as GM on /admin/tenants,');
        $this->line('       call Livewire.find(componentId).call("archiveTenant", 99999) — the');
        $this->line('       server responds with a 404 page or graceful Livewire error.');
        $this->newLine();
        $this->line('   Wipe between recordings: php artisan db:wipe-test --no-confirm');

        AuditLog::record('demo_wbt_ss2_ten_004_verified', null, 'system', 'SS2',
            "WBT_SS2_TEN_004 branch verified: archiveTenant({$tenantId}) threw ModelNotFoundException, archive count unchanged ({$archiveCountAfter})");

        return self::SUCCESS;
    }
}
