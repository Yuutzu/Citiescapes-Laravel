<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── GM account ─────────────────────────────────
        User::create([
            'full_name'            => 'Florie A. Quibod',
            'email'                => 'gm@citiescapes.test',
            'password'             => Hash::make('password'),
            'role'                 => 'gm',
            'status'               => 'active',
            'must_change_password' => false,
            'activated_at'         => now(),
            'email_verified_at'    => now(),
        ]);

        // ── 22 Rooms: Floor 1 (6), Floor 2 (8), Floor 3 (8) ──
        $floors = config('citiescapes.building.floors', [1 => 6, 2 => 8, 3 => 8]);

        $amenities_compact  = ['Air Conditioning', 'WiFi', 'Shared Bathroom'];
        $amenities_spacious = ['Air Conditioning', 'WiFi', 'Private Bathroom', 'Mini Fridge'];

        foreach ($floors as $floor => $count) {
            for ($i = 1; $i <= $count; $i++) {
                $roomNum = $floor . str_pad($i, 2, '0', STR_PAD_LEFT);
                $type    = ($i <= intdiv($count, 2)) ? 'compact' : 'spacious';

                Room::create([
                    'room_number'  => $roomNum,
                    'floor_level'  => $floor,
                    'room_type'    => $type,
                    'amenities'    => $type === 'compact' ? $amenities_compact : $amenities_spacious,
                    'rate'         => $type === 'compact' ? 3500.00 : 5000.00,
                    'max_occupants'=> $type === 'compact' ? 3 : 4,
                    'status'       => 'available',
                    'description'  => ucfirst($type) . " room on floor {$floor}",
                    'photos'       => [],
                ]);
            }
        }

        $this->command->info('Created GM account (gm@citiescapes.test / password) and 22 rooms.');
    }
}
