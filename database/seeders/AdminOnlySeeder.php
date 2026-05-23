<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Creates the GM account, a demo tenant, and the fixed 22-room building
 * inventory (rooms are treated as constant in this app — never added/removed).
 *
 * Run:  php artisan db:seed --class=AdminOnlySeeder
 *
 * Demo logins:
 *   GM     — citiescapes2017@gmail.com  /  password
 *   Tenant — demo.tenant@citiescapes.test  /  password
 *
 * The demo tenant starts with no contract assigned; the GM can issue one
 * through the admin UI to exercise the full move-in flow.
 */
class AdminOnlySeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'citiescapes2017@gmail.com'],
            [
                'full_name'            => 'Florie A. Quibod',
                'password'             => Hash::make('password'),
                'role'                 => 'gm',
                'status'               => 'active',
                'must_change_password' => false,
                'activated_at'         => now(),
                'email_verified_at'    => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'demo.tenant@citiescapes.test'],
            [
                'full_name'            => 'Demo Tenant',
                'contact_number'       => '09171234567',
                'address'              => 'Bajada, Davao City',
                'password'             => Hash::make('password'),
                'role'                 => 'tenant',
                'status'               => 'active',
                'must_change_password' => false,
                'activated_at'         => now(),
                'email_verified_at'    => now(),
            ]
        );

        // ── Fixed building inventory: Floor 1 (6), Floor 2 (8), Floor 3 (8) = 22 rooms
        $floors = config('citiescapes.building.floors', [1 => 6, 2 => 8, 3 => 8]);

        $amenities_compact  = ['Air Conditioning', 'WiFi', 'Shared Bathroom'];
        $amenities_spacious = ['Air Conditioning', 'WiFi', 'Private Bathroom', 'Mini Fridge'];

        foreach ($floors as $floor => $count) {
            for ($i = 1; $i <= $count; $i++) {
                $roomNum = $floor . str_pad($i, 2, '0', STR_PAD_LEFT);
                $type    = ($i <= intdiv($count, 2)) ? 'compact' : 'spacious';

                Room::firstOrCreate(
                    ['room_number' => $roomNum],
                    [
                        'floor_level'   => $floor,
                        'room_type'     => $type,
                        'amenities'     => $type === 'compact' ? $amenities_compact : $amenities_spacious,
                        'rate'          => $type === 'compact' ? 3500.00 : 5000.00,
                        'max_occupants' => $type === 'compact' ? 3 : 4,
                        'status'        => 'available',
                        'description'   => ucfirst($type) . " room on floor {$floor}",
                        'photos'        => [],
                    ]
                );
            }
        }

        $this->command->info('────────────────────────────────────────────────');
        $this->command->info('  GM:     citiescapes2017@gmail.com / password');
        $this->command->info('  Tenant: demo.tenant@citiescapes.test / password');
        $this->command->info('  Rooms seeded: ' . Room::count());
        $this->command->warn('  → Change both passwords immediately after first login.');
        $this->command->info('────────────────────────────────────────────────');
    }
}
