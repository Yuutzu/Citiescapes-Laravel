<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Shared helper for every demo:* command. Each demo command lists the
 * testbed:seed scenarios it wants seeded; this base runs them through the
 * existing SeedTestbed command so we never duplicate seeding logic — the
 * demo commands are thin orchestration wrappers, not new seeders.
 */
abstract class DemoSeedHelper extends Command
{
    /**
     * Return the list of testbed scenario slugs this demo wraps, e.g.
     *   ['bill.grace_day1', 'bill.overdue_day4', 'bill.eviction_day30'].
     */
    abstract protected function scenarios(): array;

    /**
     * A short, friendly summary printed at the end so the operator knows what
     * they can now demo in the browser. Plain text, one line per UI moment.
     */
    abstract protected function whatToDemo(): array;

    public function handle(): int
    {
        $slugs = implode(',', $this->scenarios());

        $this->newLine();
        $this->info('🎬 Seeding demo scenarios: ' . count($this->scenarios()) . ' state(s)');
        $this->line('   ' . $slugs);
        $this->newLine();

        // Hand off to the existing testbed:seed — no duplicate seeding logic.
        $exit = $this->call('testbed:seed', ['--scenario' => $slugs]);

        if ($exit !== self::SUCCESS) {
            $this->error('Demo seeding failed. Run `php artisan db:wipe-test --no-confirm` and try again.');
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('✅ Ready to demo. Open http://citiescapes.test and show:');
        foreach ($this->whatToDemo() as $line) {
            $this->line('   • ' . $line);
        }
        $this->newLine();
        $this->line('  Wipe between demos with: php artisan db:wipe-test --no-confirm');

        return self::SUCCESS;
    }
}
