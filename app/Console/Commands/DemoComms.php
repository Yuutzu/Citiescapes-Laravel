<?php

namespace App\Console\Commands;

class DemoComms extends DemoSeedHelper
{
    protected $signature = 'demo:comms';
    protected $description = 'Demo SS7 Communications: tenant requests, complaints, announcements, bell notifications';

    protected function scenarios(): array
    {
        return [
            'request.open',
            'request.in_progress',
            'request.resolved',
            'complaint.open',
            'announcement.broadcast',
            'announcement.direct',
            'notification.unread',
            'notification.read',
        ];
    }

    protected function whatToDemo(): array
    {
        return [
            'Admin → Tenant Requests — show pending / in-progress / resolved (locked) states',
            'Click respond on a pending request → status flips, tenant gets bell + email',
            'Admin → Announcements — show one broadcast (all tenants) and one direct (single tenant)',
            'Click Compose & Send Announcement → every active tenant gets bell + email (one failure does not abort batch)',
            'Open the bell icon (top nav) — show unread vs read rows, Mark All Read action',
            'Open as tenant → My Requests → submit new request → GM bell + email fire',
            'Tenant Dashboard → show the Recent Announcements card (with Mark as Read button)',
        ];
    }
}
