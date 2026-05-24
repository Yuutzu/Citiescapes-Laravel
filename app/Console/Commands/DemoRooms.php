<?php

namespace App\Console\Commands;

class DemoRooms extends DemoSeedHelper
{
    protected $signature = 'demo:rooms';
    protected $description = 'Demo all room statuses: available / occupied / under maintenance / archived';

    protected function scenarios(): array
    {
        return [
            'room.available',
            'room.occupied',
            'room.under_maintenance',
            'room.archived',
            'inquiry.pending',
            'inquiry.responded',
        ];
    }

    protected function whatToDemo(): array
    {
        return [
            'Admin → Room Management — show the 3 status colors (green / blue / amber) across floors',
            'Try to flip an occupied room → system blocks it ("terminate active contract first")',
            'Admin → Inquiries — show pending vs responded inquiries received from the public form',
            'Public landing page (incognito tab) — show the room cards + working inquiry form',
        ];
    }
}
