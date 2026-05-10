<?php

namespace Tests\Feature;

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
 * SS1 — Public Listings & Inquiries (Black-Box)
 *
 * Tests the public RoomListings::submitInquiry() Livewire action.
 * Technique: Equivalence Partitioning (EP) + Boundary Value Analysis (BVA).
 *
 * IDs: BBT_SS1_INQ_001..012
 */
class SS1_PublicListingsAndInquiriesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // GM exists so the notification loop in submitInquiry() has a recipient
        User::create([
            'full_name' => 'Test GM',
            'email'     => 'gm@test.local',
            'password'  => Hash::make('password'),
            'role'      => 'gm',
            'status'    => 'active',
        ]);
    }

    #[Test] // BBT_SS1_INQ_001 — EP: valid inquiry with email
    public function valid_inquiry_with_email_is_stored_and_notifies_gm(): void
    {
        Livewire::test(RoomListings::class)
            ->set('sender_name', 'Maria Santos')
            ->set('contact_number', '09171234567')
            ->set('inquiryEmail', 'maria@example.com')
            ->set('preferred_room_type', 'compact')
            ->set('message', 'Is this room available?')
            ->call('submitInquiry')
            ->assertHasNoErrors()
            ->assertSet('inquirySent', true);

        $this->assertDatabaseHas('inquiries', [
            'sender_name'         => 'Maria Santos',
            'email'               => 'maria@example.com',
            'preferred_room_type' => 'compact',
        ]);
        $this->assertSame(1, NotificationLog::where('type', 'new_inquiry')->count());
    }

    #[Test] // BBT_SS1_INQ_002 — EP: valid inquiry with no email
    public function valid_inquiry_without_email_is_stored(): void
    {
        Livewire::test(RoomListings::class)
            ->set('sender_name', 'Juan Cruz')
            ->set('contact_number', '09221234567')
            ->set('inquiryEmail', '')
            ->set('preferred_room_type', 'spacious')
            ->set('message', 'Asking about spacious room')
            ->call('submitInquiry')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('inquiries', ['contact_number' => '09221234567']);
    }

    #[Test] // BBT_SS1_INQ_003 — EP: empty sender_name fails
    public function empty_sender_name_fails(): void
    {
        Livewire::test(RoomListings::class)
            ->set('sender_name', '')
            ->set('contact_number', '09171234567')
            ->set('message', 'Hi')
            ->call('submitInquiry')
            ->assertHasErrors(['sender_name' => 'required']);
    }

    #[Test] // BBT_SS1_INQ_004 — EP: empty contact_number fails
    public function empty_contact_number_fails(): void
    {
        Livewire::test(RoomListings::class)
            ->set('sender_name', 'John')
            ->set('contact_number', '')
            ->set('message', 'Hi')
            ->call('submitInquiry')
            ->assertHasErrors(['contact_number' => 'required']);
    }

    #[Test] // BBT_SS1_INQ_005 — EP: empty message fails
    public function empty_message_fails(): void
    {
        Livewire::test(RoomListings::class)
            ->set('sender_name', 'John')
            ->set('contact_number', '09171234567')
            ->set('message', '')
            ->call('submitInquiry')
            ->assertHasErrors(['message' => 'required']);
    }

    #[Test] // BBT_SS1_INQ_006 — EP: malformed email fails
    public function malformed_email_fails(): void
    {
        Livewire::test(RoomListings::class)
            ->set('sender_name', 'John')
            ->set('contact_number', '09171234567')
            ->set('inquiryEmail', 'not-an-email')
            ->set('message', 'Hi')
            ->call('submitInquiry')
            ->assertHasErrors(['inquiryEmail' => 'email']);
    }

    #[Test] // BBT_SS1_INQ_007 — BVA: sender_name 100 chars (boundary on)
    public function sender_name_at_100_chars_passes(): void
    {
        $name = str_repeat('a', 100);
        Livewire::test(RoomListings::class)
            ->set('sender_name', $name)
            ->set('contact_number', '09171234567')
            ->set('message', 'Hi')
            ->call('submitInquiry')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('inquiries', ['sender_name' => $name]);
    }

    #[Test] // BBT_SS1_INQ_008 — BVA: sender_name 101 chars (boundary +1) fails
    public function sender_name_exceeds_100_chars_fails(): void
    {
        Livewire::test(RoomListings::class)
            ->set('sender_name', str_repeat('a', 101))
            ->set('contact_number', '09171234567')
            ->set('message', 'Hi')
            ->call('submitInquiry')
            ->assertHasErrors(['sender_name' => 'max']);
    }

    #[Test] // BBT_SS1_INQ_009 — BVA: contact_number 20 chars (boundary on)
    public function contact_number_at_20_chars_passes(): void
    {
        $num = str_repeat('1', 20);
        Livewire::test(RoomListings::class)
            ->set('sender_name', 'John')
            ->set('contact_number', $num)
            ->set('message', 'Hi')
            ->call('submitInquiry')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('inquiries', ['contact_number' => $num]);
    }

    #[Test] // BBT_SS1_INQ_010 — BVA: contact_number 21 chars fails
    public function contact_number_exceeds_20_chars_fails(): void
    {
        Livewire::test(RoomListings::class)
            ->set('sender_name', 'John')
            ->set('contact_number', str_repeat('1', 21))
            ->set('message', 'Hi')
            ->call('submitInquiry')
            ->assertHasErrors(['contact_number' => 'max']);
    }

    #[Test] // BBT_SS1_INQ_011 — BVA: message 500 chars passes
    public function message_at_500_chars_passes(): void
    {
        $msg = str_repeat('a', 500);
        Livewire::test(RoomListings::class)
            ->set('sender_name', 'John')
            ->set('contact_number', '09171234567')
            ->set('message', $msg)
            ->call('submitInquiry')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('inquiries', ['message' => $msg]);
    }

    #[Test] // BBT_SS1_INQ_012 — BVA: message 501 chars fails
    public function message_exceeds_500_chars_fails(): void
    {
        Livewire::test(RoomListings::class)
            ->set('sender_name', 'John')
            ->set('contact_number', '09171234567')
            ->set('message', str_repeat('a', 501))
            ->call('submitInquiry')
            ->assertHasErrors(['message' => 'max']);
    }
}
