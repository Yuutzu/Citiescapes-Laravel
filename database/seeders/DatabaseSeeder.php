<?php

namespace Database\Seeders;

use App\Livewire\Admin\Rooms\RoomManager;
use App\Models\Room;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── GM account ─────────────────────────────────
        $gm = User::create([
            'full_name' => 'Florie A. Quibod',
            'email' => 'citiescapes2017@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'gm',
            'status' => 'active',
            'must_change_password' => false,
            'activated_at' => now(),
            'email_verified_at' => now(),
        ]);

        // ── Demo tenant (active, no contract) ──────────
        // For first-deploy login testing. The GM can issue a contract through
        // the admin UI to walk through the full move-in flow.
        User::create([
            'full_name'            => 'Demo Tenant',
            'email'                => 'demo.tenant@citiescapes.test',
            'contact_number'       => '09171234567',
            'address'              => 'Bajada, Davao City',
            'password'             => Hash::make('password'),
            'role'                 => 'tenant',
            'status'               => 'active',
            'must_change_password' => false,
            'activated_at'         => now(),
            'email_verified_at'    => now(),
        ]);

        // ── Public room-type cards (SS1) ───────────────
        // Seed system_settings.room_type_cards with the original photo set
        // (compact-cover.jpg / compact-1.jpg / spacious-cover.jpg / spacious-1.jpg
        // under storage/app/public/room-types/). Wipe any prior value so this
        // seeder is idempotent — re-seeding always resets cards to defaults.
        SystemSetting::where('key', 'room_type_cards')->delete();
        SystemSetting::setValue(
            'room_type_cards',
            json_encode(RoomManager::defaultRoomCards()),
            $gm->id
        );

        // ── 22 Rooms: F1 (6 compact), F2 (8 compact), F3 (8 spacious) ──
        $layout = [
            1 => ['count' => 6, 'type' => 'compact'],
            2 => ['count' => 8, 'type' => 'compact'],
            3 => ['count' => 8, 'type' => 'spacious'],
        ];

        $amenities_compact = ['Air Conditioning', 'WiFi', 'Shared Bathroom'];
        $amenities_spacious = ['Air Conditioning', 'WiFi', 'Private Bathroom', 'Mini Fridge'];

        foreach ($layout as $floor => $spec) {
            $type  = $spec['type'];
            $count = $spec['count'];

            for ($i = 1; $i <= $count; $i++) {
                $roomNum = $floor . str_pad($i, 2, '0', STR_PAD_LEFT);

                Room::create([
                    'room_number' => $roomNum,
                    'floor_level' => $floor,
                    'room_type' => $type,
                    'amenities' => $type === 'compact' ? $amenities_compact : $amenities_spacious,
                    'rate' => $type === 'compact' ? 3500.00 : 5000.00,
                    'max_occupants' => $type === 'compact' ? 3 : 4,
                    'status' => 'available',
                    'description' => ucfirst($type) . " room on floor {$floor}",
                ]);
            }
        }

        $this->command->info('────────────────────────────────────────────────');
        $this->command->info('  GM:     citiescapes2017@gmail.com / password');
        $this->command->info('  Tenant: demo.tenant@citiescapes.test / password');
        $this->command->info('  Rooms seeded: 22 (6 + 8 + 8 across floors 1-3)');
        $this->command->warn('  → Change both passwords immediately after first login.');
        $this->command->info('────────────────────────────────────────────────');
    }
}
