<?php

namespace App\Livewire\Public;

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
        $smallRoom = Room::where('room_type', 'small')->where('status', 'available')->first()
            ?? Room::where('room_type', 'small')->first();
        $bigRoom = Room::where('room_type', 'big')->where('status', 'available')->first()
            ?? Room::where('room_type', 'big')->first();

        $smallPhotos = $smallRoom?->photos ?: [
            'images/rooms/small-1.jpg',
            'images/rooms/small-2.jpg',
            'images/rooms/small-3.jpg',
        ];
        $bigPhotos = $bigRoom?->photos ?: [
            'images/rooms/big-1.jpg',
            'images/rooms/big-2.jpg',
            'images/rooms/big-3.jpg',
        ];

        $smallCount = Room::where('room_type', 'small')->where('status', 'available')->count();
        $bigCount = Room::where('room_type', 'big')->where('status', 'available')->count();

        return view('livewire.public.room-listings', compact(
            'smallRoom',
            'bigRoom',
            'smallPhotos',
            'bigPhotos',
            'smallCount',
            'bigCount'
        ));
    }
}
