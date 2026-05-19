# Citiescapes test runner — uses Laragon's PHP 8.3.30 explicitly so vendor's
# `php >= 8.3` requirement is satisfied (the system `php` is 8.2.12).
#
# Usage:
#   .\test.ps1            # all tests
#   .\test.ps1 bbt        # all Black-Box (Feature) tests
#   .\test.ps1 wbt        # all White-Box (Unit) tests
#   .\test.ps1 ss1        # all SS1 tests (BBT + WBT)
#   .\test.ps1 ss2 ... ss7
#   .\test.ps1 -h         # show this help

$php = "C:/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe"

if (-not (Test-Path $php)) {
    Write-Host "ERROR: PHP 8.3.30 not found at $php" -ForegroundColor Red
    Write-Host "Edit the `$php variable at the top of this script to your PHP path." -ForegroundColor Red
    exit 1
}

$arg = if ($args.Count -gt 0) { $args[0].ToString().ToLower() } else { "all" }

switch ($arg) {
    "all"  { & $php artisan test }
    "bbt"  { & $php artisan test --testsuite=Feature }
    "wbt"  { & $php artisan test --testsuite=Unit }
    "ss1"  { & $php artisan test --filter=SS1 }
    "ss2"  { & $php artisan test --filter=SS2 }
    "ss3"  { & $php artisan test --filter=SS3 }
    "ss4"  { & $php artisan test --filter=SS4 }
    "ss5"  { & $php artisan test --filter=SS5 }
    "ss6"  { & $php artisan test --filter=SS6 }
    "ss7"  { & $php artisan test --filter=SS7 }
    { $_ -in @("-h", "--help", "help") } {
        Write-Host ""
        Write-Host "Citiescapes test runner" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "  .\test.ps1            run all 155 tests"
        Write-Host "  .\test.ps1 bbt        run Black-Box (Feature) tests only"
        Write-Host "  .\test.ps1 wbt        run White-Box (Unit) tests only"
        Write-Host "  .\test.ps1 ss1..ss7   run a single subsystem (BBT + WBT)"
        Write-Host ""
    }
    default {
        Write-Host "Unknown option: $arg" -ForegroundColor Yellow
        Write-Host "Run '.\test.ps1 help' for usage." -ForegroundColor Yellow
        exit 1
    }
}
