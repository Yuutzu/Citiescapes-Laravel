<?php

namespace App\Console\Commands;

class DemoAuth extends DemoSeedHelper
{
    protected $signature = 'demo:auth';
    protected $description = 'Demo SS6 Authentication: account lockout, auto-unlock, forced password change, audit log';

    protected function scenarios(): array
    {
        return [
            'user.locked_active',
            'user.locked_expired',
            'user.must_change_password',
            'audit.login',
            'audit.failed_login',
            'audit.password_change',
            'audit.settings_updated',
        ];
    }

    protected function whatToDemo(): array
    {
        return [
            'Live lockout demo: log out, enter the GM email + WRONG password 5 times',
            '5th attempt locks the account; GM bell increments with "Account for X was locked"',
            'Show the locked_active user → "Try again in N minutes" error on login',
            'Show the locked_expired user → next login attempt auto-unlocks them',
            'Log in as the must_change_password user → forced to /password/change before any other page',
            'Admin → System → Audit Log — show login / failed_login / password_change / settings_updated rows',
            'Filter the audit log by subsystem (SS6) and by date range',
        ];
    }
}
