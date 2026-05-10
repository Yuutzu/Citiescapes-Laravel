<?php

namespace App\Livewire\Public;

use App\Models\Room;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class RoomTypeDetail extends Component
{
    public string $type = 'spacious';

    public function mount(string $type): void
    {
        abort_unless(in_array($type, ['spacious', 'compact']), 404);
        $this->type = $type;
    }

    public function render()
    {
        $rooms = Room::where('room_type', $this->type)
            ->orderBy('floor_level')
            ->orderBy('room_number')
            ->get();

        $label      = $this->type === 'spacious' ? 'Spacious' : 'Compact';
        $available  = $rooms->where('status', 'available')->count();
        $allPhotos  = $rooms->pluck('photos')->filter()->flatten()->values()->toArray();
        $amenities  = $rooms->pluck('amenities')->filter()->flatten()->unique()->values()->toArray();
        $minRate    = $rooms->min('rate');
        $maxRate    = $rooms->max('rate');

        return view('livewire.public.room-type-detail', compact(
            'rooms', 'label', 'available', 'allPhotos', 'amenities', 'minRate', 'maxRate'
        ))->title("Citiescapes — {$label} Rooms");
    }
}
