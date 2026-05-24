<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DemoEverything extends Command
{
    protected $signature = 'demo:everything {--no-wipe : Skip the db:wipe-test step before seeding}';
    protected $description = 'Run every demo:* command in one shot — wipes the DB first then seeds every status branch across all subsystems';

    public function handle(): int
    {
        if (!$this->option('no-wipe')) {
            $this->info('🧹 Wiping database first…');
            $this->call('db:wipe-test', ['--no-confirm' => true]);
            $this->newLine();
        }

        $commands = [
            'demo:rooms',
            'demo:tenant-lifecycle',
            'demo:contract-lifecycle',
            'demo:billing-statuses',
            'demo:comms',
            'demo:auth',
            'demo:archive',
        ];

        foreach ($commands as $cmd) {
            $this->newLine();
            $this->info("▶ Running {$cmd}");
            $exit = $this->call($cmd);
            if ($exit !== self::SUCCESS) {
                $this->error("✗ {$cmd} failed — aborting.");
                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->info('✅ Comprehensive demo state seeded across all 7 subsystems.');
        $this->line('   Open http://citiescapes.test and walk through each section.');
        $this->newLine();
        $this->line('  Wipe between demos with: php artisan db:wipe-test --no-confirm');

        return self::SUCCESS;
    }
}
