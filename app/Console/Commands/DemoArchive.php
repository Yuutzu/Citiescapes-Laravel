<?php

namespace App\Console\Commands;

class DemoArchive extends DemoSeedHelper
{
    protected $signature = 'demo:archive';
    protected $description = 'Demo SS5 Archive: archived rooms / tenants / contracts / bills + restored row + CSV/PDF exports';

    protected function scenarios(): array
    {
        return [
            'archive.room',
            'archive.tenant',
            'archive.contract',
            'archive.bill',
            'archive.restored',
        ];
    }

    protected function whatToDemo(): array
    {
        return [
            'Admin → Archive — show 4 record types in one table (room / tenant_account / contract / payment)',
            'Filter by record type → show the dropdown narrows results',
            'Filter by date range → show the range picker working',
            'Click Restore on a tenant_account row → user re-activates (status flips to Active)',
            'Click Restore on a contract row → BLOCKED with the "contracts are immutable snapshots" message',
            'Click Export CSV → download streams; audit row written with row count',
            'Click PDF on a contract archive → download a printable contract record (DomPDF)',
            'Show the restored row hidden from the default view (filter checkbox brings it back)',
        ];
    }
}
