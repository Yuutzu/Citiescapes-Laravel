<?php

namespace App\Livewire\Admin\Rooms;

use App\Mail\InquiryReplyMail;
use App\Models\Inquiry;
use Illuminate\Support\Facades\Mail;
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
    public string $search = '';

    public ?int $composingId = null;
    public string $emailSubject = '';
    public string $emailBody = '';

    public function compose(int $id): void
    {
        $inq = Inquiry::find($id);
        if (! $inq || ! $inq->email) {
            session()->flash('error', 'This inquiry has no email address on file.');
            return;
        }

        $this->composingId = $id;
        $this->emailSubject = 'Re: Your room inquiry at Citiescapes';
        $this->emailBody = "Hello {$inq->sender_name},\n\nThank you for your interest in Citiescapes. ";
    }

    public function cancelCompose(): void
    {
        $this->composingId = null;
        $this->emailSubject = '';
        $this->emailBody = '';
    }

    public function sendEmail(): void
    {
        $this->validate([
            'emailSubject' => 'required|max:150',
            'emailBody'    => 'required|max:3000',
        ]);

        $inq = Inquiry::findOrFail($this->composingId);
        if (! $inq->email) {
            session()->flash('error', 'This inquiry has no email address on file.');
            return;
        }

        try {
            Mail::to($inq->email)->send(new InquiryReplyMail(
                $inq->sender_name,
                $this->emailSubject,
                $this->emailBody,
                auth()->user()->full_name,
            ));
        } catch (\Throwable $e) {
            \Log::error('InquiryReplyMail send failed', ['inquiry_id' => $inq->id, 'error' => $e->getMessage()]);
            session()->flash('error', 'Could not send the reply email. The inquiry status was NOT updated. Try again or check SMTP settings.');
            return;
        }

        $inq->update([
            'status'       => 'responded',
            'gm_notes'     => $this->emailBody,
            'responded_at' => now(),
            'responded_by' => auth()->id(),
        ]);

        $this->cancelCompose();
        session()->flash('success', 'Email reply sent to ' . $inq->email . '.');
    }

    public function markResponded(int $id): void
    {
        Inquiry::findOrFail($id)->update([
            'status'       => 'responded',
            'responded_at' => now(),
            'responded_by' => auth()->id(),
        ]);
        session()->flash('success', 'Inquiry marked as responded.');
    }

    public function close(int $id): void
    {
        Inquiry::findOrFail($id)->update(['status' => 'closed']);
    }

    public function render()
    {
        $inquiries = Inquiry::query()
            ->with('respondedBy')
            ->when($this->search, function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($qq) use ($term) {
                    $qq->where('sender_name', 'like', $term)
                       ->orWhere('email', 'like', $term)
                       ->orWhere('contact_number', 'like', $term)
                       ->orWhere('message', 'like', $term)
                       ->orWhere('preferred_room_type', 'like', $term)
                       ->orWhere('status', 'like', $term);
                });
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.rooms.inquiry-manager', compact('inquiries'));
    }
}
