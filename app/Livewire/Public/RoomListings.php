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
    public string $filterFloor = '';
    public string $filterType  = '';

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
            'sender_name'    => 'required|max:100',
            'contact_number' => 'required|max:20',
            'inquiryEmail'   => 'nullable|email|max:100',
            'message'        => 'required|max:500',
        ]);

        Inquiry::create([
            'sender_name'         => $this->sender_name,
            'contact_number'      => $this->contact_number,
            'email'               => $this->inquiryEmail,
            'preferred_room_type' => $this->preferred_room_type,
            'message'             => $this->message,
        ]);

        // Notify GM
        $gms = User::where('role', 'gm')->where('status', 'active')->get();
        foreach ($gms as $gm) {
            NotificationLog::create([
                'user_id' => $gm->id,
                'type'    => 'new_inquiry',
                'source'  => 'SS1',
                'message' => "New room inquiry from {$this->sender_name} ({$this->contact_number})",
            ]);
        }

        $this->reset(['sender_name', 'contact_number', 'inquiryEmail', 'preferred_room_type', 'message']);
        $this->inquirySent = true;
    }

    public function render()
    {
        $rooms = Room::query()
            ->when($this->filterFloor, fn($q) => $q->where('floor_level', $this->filterFloor))
            ->when($this->filterType, fn($q) => $q->where('room_type', $this->filterType))
            ->orderBy('floor_level')
            ->orderBy('room_number')
            ->get();

        return view('livewire.public.room-listings', compact('rooms'));
    }
}
