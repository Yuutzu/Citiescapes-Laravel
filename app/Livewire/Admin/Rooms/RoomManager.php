<?php

namespace App\Livewire\Admin\Rooms;

use App\Models\Archive;
use App\Models\AuditLog;
use App\Models\Room;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Room Management — Citiescapes')]
class RoomManager extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public string $filterFloor = '';
    public string $filterStatus = '';

    // Modal state
    public bool $showModal = false;
    public bool $editing = false;
    public ?int $editId = null;

    // Form fields
    public string $room_number = '';
    public int $floor_level = 1;
    public string $room_type = 'compact';
    public float $rate = 3500;
    public int $max_occupants = 3;
    public string $status = 'available';
    public string $description = '';
    public $newPhotos = [];
    public array $amenitiesInput = [];

    protected $availableAmenities = ['Air Conditioning', 'WiFi', 'Private Bathroom', 'Shared Bathroom', 'Mini Fridge', 'Study Desk', 'Wardrobe', 'Hot Shower'];

    public function getAvailableAmenitiesProperty() { return $this->availableAmenities; }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->editing = false;
    }

    public function edit(int $id)
    {
        $room = Room::findOrFail($id);
        $this->editId = $id;
        $this->room_number = $room->room_number;
        $this->floor_level = $room->floor_level;
        $this->room_type = $room->room_type;
        $this->rate = (float) $room->rate;
        $this->max_occupants = $room->max_occupants;
        $this->status = $room->status;
        $this->description = $room->description ?? '';
        $this->amenitiesInput = $room->amenities ?? [];
        $this->showModal = true;
        $this->editing = true;
    }

    public function save()
    {
        $this->validate([
            'room_number'  => 'required|max:10|unique:rooms,room_number,' . $this->editId,
            'floor_level'  => 'required|in:1,2,3',
            'room_type'    => 'required|in:compact,spacious',
            'rate'         => 'required|numeric|min:0',
            'max_occupants'=> 'required|integer|min:1|max:10',
            'status'       => 'required|in:available,occupied,under_maintenance',
        ]);

        $data = [
            'room_number'       => $this->room_number,
            'floor_level'       => $this->floor_level,
            'room_type'         => $this->room_type,
            'rate'              => $this->rate,
            'max_occupants'     => $this->max_occupants,
            'status'            => $this->status,
            'description'       => $this->description,
            'amenities'         => $this->amenitiesInput,
            'last_updated_by'   => auth()->id(),
            'last_status_update'=> now(),
        ];

        // Handle photo uploads
        if (!empty($this->newPhotos)) {
            $photos = [];
            foreach ($this->newPhotos as $photo) {
                $photos[] = $photo->store('rooms', 'public');
            }
            $existing = $this->editing ? (Room::find($this->editId)?->photos ?? []) : [];
            $data['photos'] = array_merge($existing, $photos);
        }

        if ($this->editing) {
            Room::findOrFail($this->editId)->update($data);
            AuditLog::record('room_updated', auth()->id(), 'gm', 'SS1', "Room {$this->room_number} updated");
        } else {
            Room::create($data);
            AuditLog::record('room_created', auth()->id(), 'gm', 'SS1', "Room {$this->room_number} created");
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', $this->editing ? 'Room updated.' : 'Room created.');
    }

    public function updateStatus(int $id, string $newStatus)
    {
        $room = Room::findOrFail($id);
        $old = $room->status;
        $room->update([
            'status'             => $newStatus,
            'last_updated_by'    => auth()->id(),
            'last_status_update' => now(),
            'current_tenant_id'  => $newStatus !== 'occupied' ? null : $room->current_tenant_id,
        ]);
        AuditLog::record('room_status_change', auth()->id(), 'gm', 'SS1', "Room {$room->room_number}: {$old} → {$newStatus}");
    }

    public function archiveRoom(int $id)
    {
        $room = Room::findOrFail($id);
        Archive::create([
            'original_record_id' => $room->id,
            'record_type'        => 'room',
            'source_subsystem'   => 'SS1',
            'archive_reason'     => 'Manual archive by GM',
            'data'               => $room->toArray(),
            'archived_by'        => auth()->id(),
        ]);
        $room->delete();
        AuditLog::record('room_archived', auth()->id(), 'gm', 'SS1', "Room {$room->room_number} archived");
        session()->flash('success', 'Room archived.');
    }

    public function deleteRoom(int $id)
    {
        $room = Room::findOrFail($id);
        $num = $room->room_number;
        $room->forceDelete();
        AuditLog::record('room_deleted', auth()->id(), 'gm', 'SS1', "Room {$num} permanently deleted");
        session()->flash('success', 'Room permanently deleted.');
    }

    private function resetForm()
    {
        $this->editId = null;
        $this->room_number = '';
        $this->floor_level = 1;
        $this->room_type = 'compact';
        $this->rate = 3500;
        $this->max_occupants = 3;
        $this->status = 'available';
        $this->description = '';
        $this->amenitiesInput = [];
        $this->newPhotos = [];
    }

    public function render()
    {
        $rooms = Room::query()
            ->when($this->search, fn($q) => $q->where('room_number', 'like', "%{$this->search}%"))
            ->when($this->filterFloor, fn($q) => $q->where('floor_level', $this->filterFloor))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->with('currentTenant')
            ->orderBy('floor_level')->orderBy('room_number')
            ->paginate(20);

        return view('livewire.admin.rooms.room-manager', compact('rooms'));
    }
}
