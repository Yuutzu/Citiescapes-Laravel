<?php

namespace App\Livewire\Public;

use App\Livewire\Admin\Rooms\RoomManager;
use App\Models\Inquiry;
use App\Models\Room;
use App\Models\NotificationLog;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.public')]
#[Title('Citiescapes — Available Rooms')]
class RoomListings extends Component
{
    // Inquiry form fields
    public string $sender_name = '';
    public string $contact_number = '';
    public string $inquiryEmail = '';
    public string $preferred_room_type = 'any';
    public string $message = '';
    public bool $inquirySent = false;

    public function inquireAbout(string $type): void
    {
        if (\in_array($type, ['compact', 'spacious', 'any'], true)) {
            $this->preferred_room_type = $type;
            $this->inquirySent = false;
            $this->dispatch('scroll-to-inquiry');
        }
    }

    public function submitInquiry()
    {
        $this->validate([
            'sender_name' => 'required|max:100',
            'contact_number' => 'required|max:20',
            'inquiryEmail' => 'nullable|email|max:100',
            'message' => 'required|max:500',
        ]);

        Inquiry::create([
            'sender_name' => $this->sender_name,
            'contact_number' => $this->contact_number,
            'email' => $this->inquiryEmail,
            'preferred_room_type' => $this->preferred_room_type,
            'message' => $this->message,
        ]);

        // Notify GM
        $gms = User::where('role', 'gm')->where('status', 'active')->get();
        foreach ($gms as $gm) {
            NotificationLog::create([
                'user_id' => $gm->id,
                'type' => 'new_inquiry',
                'source' => 'SS1',
                'message' => "New room inquiry from {$this->sender_name} ({$this->contact_number})",
            ]);
        }

        $this->reset(['sender_name', 'contact_number', 'inquiryEmail', 'preferred_room_type', 'message']);
        $this->inquirySent = true;
    }

    public function render()
    {
        $compactRoom = Room::where('room_type', 'compact')->where('status', 'available')->first()
            ?? Room::where('room_type', 'compact')->first();
        $spaciousRoom = Room::where('room_type', 'spacious')->where('status', 'available')->first()
            ?? Room::where('room_type', 'spacious')->first();

        // Admin-configurable card definitions (managed in SS1 Room Management).
        $cards = RoomManager::loadRoomCards();

        $toUrl = fn(string $p) => str_starts_with($p, 'http') ? $p : asset('storage/' . ltrim($p, '/'));
        $compactPhotos = array_map($toUrl, $cards['compact']['photos'] ?? []);
        $spaciousPhotos = array_map($toUrl, $cards['spacious']['photos'] ?? []);

        $compactCount = Room::where('room_type', 'compact')->where('status', 'available')->count();
        $spaciousCount = Room::where('room_type', 'spacious')->where('status', 'available')->count();

        return view('livewire.public.room-listings', compact(
            'compactRoom',
            'spaciousRoom',
            'compactPhotos',
            'spaciousPhotos',
            'compactCount',
            'spaciousCount',
            'cards'
        ));
    }
}
