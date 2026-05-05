<?php

namespace App\Livewire\Admin\Communications;

use App\Mail\AnnouncementMail;
use App\Models\Announcement;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Announcements')]
class AnnouncementManager extends Component
{
    // Form fields
    public string $title          = '';
    public string $body           = '';
    public string $recipientType  = 'all';
    public string $recipientId    = '';
    public bool   $sendEmail      = false;

    // UI state
    public bool   $showForm       = false;
    public string $search         = '';
    public ?int   $viewingId      = null;

    public function openForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function send(): void
    {
        $this->validate([
            'title'       => 'required|max:150',
            'body'        => 'required|max:2000',
            'recipientType' => 'required|in:all,specific',
            'recipientId' => 'required_if:recipientType,specific|nullable|exists:users,id',
        ]);

        $announcement = Announcement::create([
            'title'          => $this->title,
            'body'           => $this->body,
            'recipient_type' => $this->recipientType,
            'recipient_id'   => $this->recipientType === 'specific' ? $this->recipientId : null,
            'sent_by'        => auth()->id(),
            'email_sent'     => $this->sendEmail,
        ]);

        $recipients = $this->resolveRecipients();

        foreach ($recipients as $tenant) {
            NotificationLog::create([
                'user_id' => $tenant->id,
                'type'    => 'announcement',
                'source'  => 'SS7',
                'message' => "[Announcement] {$this->title}",
            ]);

            if ($this->sendEmail && $tenant->email) {
                Mail::to($tenant->email)->send(
                    new AnnouncementMail($tenant->full_name, $this->title, $this->body)
                );
            }
        }

        $this->closeForm();
        session()->flash('success', 'Announcement sent to ' . count($recipients) . ' tenant(s).');
    }

    private function resolveRecipients()
    {
        if ($this->recipientType === 'all') {
            return User::where('role', 'tenant')->where('status', 'active')->get();
        }

        return User::where('id', $this->recipientId)->where('role', 'tenant')->get();
    }

    private function resetForm(): void
    {
        $this->title         = '';
        $this->body          = '';
        $this->recipientType = 'all';
        $this->recipientId   = '';
        $this->sendEmail     = false;
    }

    public function render()
    {
        $announcements = Announcement::with(['sentBy', 'recipient'])
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('body', 'like', "%{$this->search}%");
            }))
            ->latest()
            ->paginate(15);

        $tenants = User::where('role', 'tenant')
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'email']);

        $viewing = $this->viewingId ? Announcement::with('sentBy', 'recipient')->find($this->viewingId) : null;

        return view('livewire.admin.communications.announcement-manager', compact('announcements', 'tenants', 'viewing'));
    }
}
