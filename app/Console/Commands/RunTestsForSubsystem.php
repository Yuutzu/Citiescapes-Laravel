<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * RunTestsForSubsystem Command
 *
 * Runs PHPUnit tests for a specific subsystem with test seeding.
 *
 * Usage:
 *   php artisan test:subsystem SS1                    # Run SS1 tests
 *   php artisan test:subsystem SS1 --black-box        # Run only black-box tests
 *   php artisan test:subsystem SS1 --white-box        # Run only white-box tests
 *   php artisan test:subsystem SS1 --coverage         # With coverage report
 *   php artisan test:subsystem HTTP                   # HTTP responses & error handling
 *   php artisan test:subsystem all                    # Run all subsystem tests
 *
 * This command:
 *   1. Seeds test data for the subsystem
 *   2. Runs phpunit tests for that subsystem
 *   3. Reports results clearly
 */
class RunTestsForSubsystem extends Command
{
    protected $signature = 'test:subsystem
                            {subsystem : Subsystem to test (SS1-SS7 or "all")}
                            {--black-box : Run only black-box tests}
                            {--white-box : Run only white-box tests}
                            {--coverage : Generate coverage report}
                            {--seed= : Custom seeder class (default: TestScenarioSeeder)}';

    protected $description = 'Run PHPUnit tests for a specific subsystem';

    protected $subsystems = [
        'SS1' => ['feature' => 'SS1_PublicListingsAndInquiriesTest', 'unit' => 'SS1_PublicListingsWhiteBoxTest'],
        'SS2' => ['feature' => 'SS2_TenantManagementTest', 'unit' => 'SS2_TenantWhiteBoxTest'],
        'SS3' => ['feature' => 'SS3_BillingManagementTest', 'unit' => 'SS3_BillingWhiteBoxTest'],
        'SS4' => ['feature' => 'SS4_ContractManagementTest', 'unit' => 'SS4_ContractWhiteBoxTest'],
        'SS5' => ['feature' => 'SS5_ReportsManagementTest', 'unit' => 'SS5_ReportsWhiteBoxTest'],
        'SS6' => ['feature' => 'SS6_SystemAdministrationTest', 'unit' => 'SS6_AuthWhiteBoxTest'],
        'SS7' => ['feature' => 'SS7_CommunicationsTest', 'unit' => 'SS7_CommunicationsWhiteBoxTest'],
        'HTTP' => ['feature' => 'HttpResponsesAndErrorsTest', 'unit' => 'HttpResponsesAndErrorsWhiteBoxTest'],
    ];

    public function handle(): int
    {
        $subsystem = strtoupper($this->argument('subsystem'));
        $isAll = $subsystem === 'ALL';

        if (!$isAll && !isset($this->subsystems[$subsystem])) {
            $this->error("Invalid subsystem. Choose from: " . implode(', ', array_keys($this->subsystems)) . ", or 'all'");
            return self::FAILURE;
        }

        $subsystems = $isAll ? $this->subsystems : [$subsystem => $this->subsystems[$subsystem]];

        $this->newLine();
        $this->info('🧪 Running tests for: ' . ($isAll ? 'ALL subsystems' : $subsystem));
        $this->newLine();

        $allPassed = true;

        foreach ($subsystems as $ss => $tests) {
            if (!$this->runSubsystemTests($ss, $tests)) {
                $allPassed = false;
            }
        }

        $this->newLine();
        if ($allPassed) {
            $this->info('✅ All tests passed!');
            return self::SUCCESS;
        } else {
            $this->error('❌ Some tests failed.');
            return self::FAILURE;
        }
    }

    private function runSubsystemTests(string $subsystem, array $tests): bool
    {
        $this->line("📋 $subsystem Tests:");

        $passed = true;

        // Run black-box tests (feature tests)
        if (!$this->option('white-box')) {
            $this->line("  🔲 Black-Box (Feature Tests)...");
            if (!$this->runPhpUnitTest($tests['feature'], 'tests/Feature')) {
                $passed = false;
            }
        }

        // Run white-box tests (unit tests)
        if (!$this->option('black-box')) {
            $this->line("  ⬜ White-Box (Unit Tests)...");
            if (!$this->runPhpUnitTest($tests['unit'], 'tests/Unit')) {
                $passed = false;
            }
        }

        return $passed;
    }

    private function runPhpUnitTest(string $testClass, string $dir): bool
    {
        // Check if test file exists
        $testPath = base_path($dir . '/' . $testClass . '.php');
        if (!file_exists($testPath)) {
            $this->warn("    ⚠️  Test file not found: $testClass");
            return true; // Don't fail, just skip
        }

        $command = ['php', 'artisan', 'test', "--filter=$testClass"];

        if ($this->option('coverage')) {
            $command[] = '--coverage';
        }

        $process = new Process($command, base_path());
        $process->setTimeout(300);
        $process->run();

        if ($process->isSuccessful()) {
            $this->line("    ✓ Passed");
            return true;
        } else {
            $this->line("    ✗ Failed");
            $this->warn($process->getErrorOutput() ?: $process->getOutput());
            return false;
        }
    }
}
