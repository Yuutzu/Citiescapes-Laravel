<?php

namespace App\Livewire\Admin\Communications;

use App\Mail\AnnouncementMail;
use App\Models\Announcement;
use App\Models\AuditLog;
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

        $recipients = $this->resolveRecipients();

        Announcement::create([
            'title'          => $this->title,
            'body'           => $this->body,
            'recipient_type' => $this->recipientType,
            'recipient_id'   => $this->recipientType === 'specific' ? $this->recipientId : null,
            'sent_by'        => auth()->id(),
            'email_sent'     => true,
        ]);

        foreach ($recipients as $tenant) {
            NotificationLog::create([
                'user_id' => $tenant->id,
                'type'    => 'announcement',
                'source'  => 'SS7',
                'message' => "[Announcement] {$this->title}",
            ]);

            if ($tenant->email) {
                Mail::to($tenant->email)->send(
                    new AnnouncementMail($tenant->full_name, $this->title, $this->body)
                );
            }
        }

        $recipientLabel = $this->recipientType === 'all'
            ? 'all active tenants (' . \count($recipients) . ')'
            : ('tenant #' . $this->recipientId);
        AuditLog::record('announcement_sent', auth()->id(), 'gm', 'SS7',
            "Announcement \"{$this->title}\" sent to {$recipientLabel}");

        $this->closeForm();
        session()->flash('success', 'Announcement sent to ' . \count($recipients) . ' tenant(s) (bell + email).');
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
    }

    public function render()
    {
        $announcements = Announcement::with(['sentBy', 'recipient'])
            ->when($this->search, function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($qq) use ($term) {
                    $qq->where('title', 'like', $term)
                       ->orWhere('body', 'like', $term)
                       ->orWhere('recipient_type', 'like', $term)
                       ->orWhereHas('sentBy', fn($u) => $u->where('full_name', 'like', $term))
                       ->orWhereHas('recipient', fn($u) => $u->where('full_name', 'like', $term)->orWhere('email', 'like', $term));
                });
            })
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
