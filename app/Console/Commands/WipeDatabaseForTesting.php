<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * WipeDatabaseForTesting Command
 *
 * Safely wipes the database for fresh testing, PRESERVING:
 *   - GM Accounts (users with role='gm')
 *   - Rooms (all rooms and room status history)
 *
 * Usage: php artisan db:wipe-test
 *
 * This command:
 *   1. Deletes all test data in reverse dependency order
 *   2. Resets auto-increment counters
 *   3. Preserves GM accounts and all rooms
 *   4. Confirms before deletion
 */
class WipeDatabaseForTesting extends Command
{
    protected $signature = 'db:wipe-test
                            {--no-confirm : Skip confirmation prompt}
                            {--with-testbed : After wiping, re-seed the 57 testbed:seed scenarios}';

    protected $description = 'Wipe database for testing (preserve GM & Rooms; optionally re-seed testbed)';

    public function handle(): int
    {
        if (!$this->option('no-confirm')) {
            $this->newLine();
            $this->warn('⚠️  WARNING: This will DELETE most data from the database!');
            $this->line('Preserved: GM Accounts, All Rooms');
            $this->line('Deleted: All tenants, contracts, bills, payments, inquiries, etc.');
            $this->newLine();

            if (!$this->confirm('Do you want to continue?')) {
                $this->info('Cancelled.');
                return self::FAILURE;
            }
        }

        try {
            $this->info('🧹 Starting database wipe for testing...');
            $this->newLine();

            // Disable foreign key checks for deletion
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Delete in reverse dependency order
            $this->wipeTableData('audit_logs', 'Audit logs');
            $this->wipeTableData('notifications_log', 'Notifications (bell)');
            $this->wipeTableData('payments', 'Payments');
            $this->wipeTableData('penalty_overrides', 'Penalty overrides');
            $this->wipeTableData('bills', 'Bills');
            $this->wipeTableData('initial_payments', 'Initial payments');
            $this->wipeTableData('contracts', 'Contracts');
            $this->wipeTableData('tenant_requests', 'Tenant requests');
            $this->wipeTableData('inquiries', 'Inquiries');
            $this->wipeTableData('otp_records', 'OTP records');
            $this->wipeTableData('announcements', 'Announcements');
            $this->wipeTableData('archives', 'Archives');

            // Delete users except GM accounts
            $this->wipeTenantsAndAdmins();

            // Delete test rooms (preserve original 22 rooms)
            $this->wipeTestRooms();

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // Reset auto-increment counters
            $this->resetAutoIncrements();

            $this->newLine();
            $this->info('✅ Database wiped successfully for fresh testing!');
            $this->info('GM accounts and original 22 rooms preserved.');

            if ($this->option('with-testbed')) {
                $this->newLine();
                $this->info('🌱 Re-seeding testbed scenarios…');
                $this->call('testbed:seed');
                $this->newLine();
                $this->info('✅ Wipe + testbed re-seed complete.');
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->error('❌ Error during wipe: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Wipe data from a specific table
     */
    private function wipeTableData(string $table, string $label): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $count = DB::table($table)->count();
        if ($count === 0) {
            return;
        }

        DB::table($table)->truncate();
        $this->line("  ✓ Deleted $count $label");
    }

    /**
     * Delete tenants and admins, preserve GM accounts
     */
    private function wipeTenantsAndAdmins(): void
    {
        $gmCount = DB::table('users')->where('role', 'gm')->count();
        $tenantCount = DB::table('users')->where('role', '!=', 'gm')->count();

        if ($tenantCount === 0) {
            return;
        }

        DB::table('users')->where('role', '!=', 'gm')->delete();
        $this->line("  ✓ Deleted $tenantCount tenants and admins (preserved $gmCount GM accounts)");
    }

    /**
     * Delete test rooms (created by seeders), preserve original 22 rooms.
     * Test rooms are identified by BB_, WB_, or TB- prefix in room_number.
     */
    private function wipeTestRooms(): void
    {
        $testRoomCount = DB::table('rooms')
            ->where(function ($q) {
                $q->where('room_number', 'LIKE', 'BB_%')
                  ->orWhere('room_number', 'LIKE', 'WB_%')
                  ->orWhere('room_number', 'LIKE', 'TB-%');
            })
            ->count();

        if ($testRoomCount === 0) {
            return;
        }

        DB::table('rooms')
            ->where(function ($q) {
                $q->where('room_number', 'LIKE', 'BB_%')
                  ->orWhere('room_number', 'LIKE', 'WB_%')
                  ->orWhere('room_number', 'LIKE', 'TB-%');
            })
            ->delete();

        $this->line("  ✓ Deleted $testRoomCount test rooms (BB_/WB_/TB- prefixes; preserved original 22)");
    }

    /**
     * Reset auto-increment counters for all tables.
     *
     * For tables that still have rows after the wipe (users, rooms), we set
     * AUTO_INCREMENT to max(id)+1 so new seeder inserts never collide with
     * the preserved GM account or the original 22 rooms.
     * For fully-truncated tables it is safe to reset to 1.
     */
    private function resetAutoIncrements(): void
    {
        $this->newLine();
        $this->info('🔄 Resetting auto-increment counters...');

        // These tables still have rows — must keep auto-increment above current max
        $preservedTables = ['users', 'rooms'];

        // These tables were fully truncated — safe to reset to 1
        $truncatedTables = [
            'contracts', 'bills', 'payments', 'initial_payments',
            'inquiries', 'tenant_requests', 'announcements', 'otp_records',
            'notifications_log', 'audit_logs', 'penalty_overrides', 'archives',
        ];

        foreach ($preservedTables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            $maxId = DB::table($table)->max('id') ?? 0;
            $nextId = (int) $maxId + 1;
            DB::statement("ALTER TABLE `$table` AUTO_INCREMENT = $nextId;");
        }

        foreach ($truncatedTables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE `$table` AUTO_INCREMENT = 1;");
            }
        }

        $this->line('  ✓ Auto-increment counters reset (preserved tables start above existing max ID)');
    }
}
