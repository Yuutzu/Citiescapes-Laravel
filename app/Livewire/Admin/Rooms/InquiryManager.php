<?php

namespace App\Livewire\Admin\Rooms;

use App\Models\Inquiry;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Inquiry Log — Citiescapes')]
class InquiryManager extends Component
{
    use WithPagination;

    public string $filterStatus = '';
    public ?int $respondingId = null;
    public string $gmNotes = '';

    public function respond(int $id)
    {
        $this->respondingId = $id;
        $this->gmNotes = Inquiry::find($id)?->gm_notes ?? '';
    }

    public function saveResponse()
    {
        $this->validate(['gmNotes' => 'required|max:500']);
        Inquiry::findOrFail($this->respondingId)->update([
            'status'       => 'responded',
            'gm_notes'     => $this->gmNotes,
            'responded_at' => now(),
            'responded_by' => auth()->id(),
        ]);
        $this->respondingId = null;
        $this->gmNotes = '';
        session()->flash('success', 'Inquiry marked as responded.');
    }

    public function close(int $id)
    {
        Inquiry::findOrFail($id)->update(['status' => 'closed']);
    }

    public function render()
    {
        $inquiries = Inquiry::query()
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.rooms.inquiry-manager', compact('inquiries'));
    }
}
