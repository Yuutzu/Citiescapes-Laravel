<?php

namespace Tests\Unit;

use App\Livewire\Public\RoomListings;
use App\Models\Inquiry;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SS1 — Public Listings & Inquiries (White-Box)
 *
 * Branch coverage of RoomListings::submitInquiry() (app/Livewire/Public/RoomListings.php:25-55):
 *   - B1: validation (true → throw / false → continue)
 *   - B2: notification loop over $gms (0 GMs / 1 GM / many GMs)
 *   - B3: form reset + inquirySent flag set true
 *
 * IDs: WBT_SS1_INQ_001..006
 */
class SS1_PublicListingsWhiteBoxTest extends TestCase
{
    use RefreshDatabase;

    /* ── B1: validation branch ─────────────────────── */

    #[Test] // WBT_SS1_INQ_001 — B1a: validation FAILS, no row inserted
    public function failed_validation_does_not_create_inquiry(): void
    {
        Livewire::test(RoomListings::class)
            ->set('sender_name', '')
            ->call('submitInquiry');

        $this->assertSame(0, Inquiry::count());
    }

    #[Test] // WBT_SS1_INQ_002 — B1b: validation PASSES, row inserted with all fields
    public function valid_payload_inserts_row_with_all_fields(): void
    {
        Livewire::test(RoomListings::class)
            ->set('sender_name', 'Anna')
            ->set('contact_number', '09175551234')
            ->set('inquiryEmail', 'anna@x.com')
            ->set('preferred_room_type', 'spacious')
            ->set('message', 'Hello')
            ->call('submitInquiry');

        $row = Inquiry::first();
        $this->assertSame('Anna', $row->sender_name);
        $this->assertSame('anna@x.com', $row->email);
        $this->assertSame('spacious', $row->preferred_room_type);
    }

    /* ── B2: notification loop ──────────────────────── */

    #[Test] // WBT_SS1_INQ_003 — B2a: zero GMs → loop body never executes
    public function loop_body_skipped_when_no_gms(): void
    {
        Livewire::test(RoomListings::class)
            ->set('sender_name', 'X')
            ->set('contact_number', '0917')
            ->set('message', 'M')
            ->call('submitInquiry');

        $this->assertSame(0, NotificationLog::count());
    }

    #[Test] // WBT_SS1_INQ_004 — B2b: one GM → loop runs once
    public function loop_runs_once_for_single_gm(): void
    {
        $this->makeGm('gm1@x.com');

        Livewire::test(RoomListings::class)
            ->set('sender_name', 'X')
            ->set('contact_number', '0917')
            ->set('message', 'M')
            ->call('submitInquiry');

        $this->assertSame(1, NotificationLog::where('type', 'new_inquiry')->count());
    }

    #[Test] // WBT_SS1_INQ_005 — B2c: many GMs → loop runs N times
    public function loop_runs_n_times_for_n_active_gms(): void
    {
        $this->makeGm('gm1@x.com');
        $this->makeGm('gm2@x.com');
        $this->makeGm('gm3@x.com');

        Livewire::test(RoomListings::class)
            ->set('sender_name', 'X')
            ->set('contact_number', '0917')
            ->set('message', 'M')
            ->call('submitInquiry');

        $this->assertSame(3, NotificationLog::where('type', 'new_inquiry')->count());
    }

    /* ── B3: form reset / flag flip ─────────────────── */

    #[Test] // WBT_SS1_INQ_006 — B3: form fields are reset and inquirySent set to true on success
    public function on_success_form_is_reset_and_flag_is_set(): void
    {
        Livewire::test(RoomListings::class)
            ->set('sender_name', 'X')
            ->set('contact_number', '0917')
            ->set('message', 'M')
            ->call('submitInquiry')
            ->assertSet('sender_name', '')
            ->assertSet('contact_number', '')
            ->assertSet('message', '')
            ->assertSet('preferred_room_type', 'any')
            ->assertSet('inquirySent', true);
    }

    private function makeGm(string $email): void
    {
        User::create([
            'full_name' => 'GM ' . $email,
            'email'     => $email,
            'password'  => Hash::make('password'),
            'role'      => 'gm',
            'status'    => 'active',
        ]);
    }
}
