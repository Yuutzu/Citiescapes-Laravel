<?php

namespace App\Console\Commands;

class DemoTenantLifecycle extends DemoSeedHelper
{
    protected $signature = 'demo:tenant-lifecycle';
    protected $description = 'Demo the tenant account lifecycle: pending → OTP → active → archived';

    protected function scenarios(): array
    {
        return [
            'tenant.pending_activation',
            'tenant.with_valid_otp',
            'tenant.with_expired_otp',
            'tenant.active',
            'tenant.archived_manual',
        ];
    }

    protected function whatToDemo(): array
    {
        return [
            'Admin → Tenant Management — show the 4 status badges (pending / active / archived)',
            'Click Create Tenant — show the temp-password email flow (check storage/logs/laravel.log)',
            'Open an incognito tab → log in as a pending-activation user → forced to OTP screen',
            'Show the expired-OTP branch → "Resend OTP" generates a fresh code',
            'Click Archive on an active tenant → flips to Archived; show the row moves to that filter',
            'Click Permanently Delete on an archived row → hard delete with confirmation',
        ];
    }
}
