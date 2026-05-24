<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

/**
 * BBT_SS5_REP_012 — Restore Archived Record (room type, decision-table branch).
 *
 * From the CS12L BBT/WBT documentation:
 *   Preconditions: Archived record_type=room.
 *   Test Input:    restore(id) called on the archived room row.
 *   Expected:      restored=true; no user re-activation branch entered.
 *
 * Note: the latest code change to SS5 now refuses restore for record_type=contract
 * (see ReportManager::restore guard, commit 19e7b71). For record_type=room the
 * happy path still applies — that's exactly what this test case exercises.
 */
class DemoBbtSs5Rep012 extends Command
{
    protected $signature = 'demo:bbt-ss5-rep-012';
    protected $description = 'BBT_SS5_REP_012 — seed an archived room row in SS5 so the demo can click Restore and verify the decision-table branch (room type → restored flag flipped, no user re-activation)';

    public function handle(): int
    {
        $this->newLine();
        $this->info('🎬 BBT_SS5_REP_012 — Restore Archived Record (room type branch)');
        $this->line('   Precondition: An archive row with record_type=room, source_subsystem=SS1');
        $this->newLine();

        // Seed via the testbed scenario that creates exactly this archive row.
        $this->call('testbed:seed', ['--scenario' => 'archive.room']);

        $this->newLine();
        $this->info('✅ Ready to record. Open http://citiescapes.test and:');
        $this->line('   • Log in as GM → Archive (SS5 in the sidebar)');
        $this->line('   • Filter by record_type = "room" if needed');
        $this->line('   • Find the seeded archive row (record_type=room, source=SS1)');
        $this->line('   • Click the green "Restore" button');
        $this->line('   • Confirm the prompt → flash "Record restored." appears');
        $this->line('   • The archive row disappears from the default view (restored=true)');
        $this->line('   • Toggle "Show restored" to verify the row exists with restored badge');
        $this->line('   • Audit Log → look for record_restored action tagged SS5');
        $this->newLine();
        $this->line('   Demo note: this exercises the "other type" branch of ReportManager::restore() —');
        $this->line('   the tenant_account branch is skipped (no User::find call). For contrast,');
        $this->line('   clicking Restore on a contract-type archive should be BLOCKED with a clear');
        $this->line('   error (matches the "contracts are immutable" rule).');
        $this->newLine();
        $this->line('   Wipe between recordings: php artisan db:wipe-test --no-confirm');

        AuditLog::record('demo_bbt_ss5_rep_012_seeded', null, 'system', 'SS5',
            'BBT_SS5_REP_012 demo precondition seeded (archived room row) for video recording');

        return self::SUCCESS;
    }
}
