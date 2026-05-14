<?php

namespace App\Livewire\Admin\Communications;

use App\Mail\RequestResponseMail;
use App\Models\NotificationLog;
use App\Models\TenantRequest;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Tenant Requests')]
class RequestViewer extends Component
{
    public string $filterStatus = '';
    public string $filterType = '';
    public string $search = '';

    public ?int $viewingId = null;
    public string $adminReply = '';
    public string $newStatus = '';

    public function view(int $id): void
    {
        $this->viewingId = $id;
        $request = TenantRequest::findOrFail($id);
        $this->adminReply = $request->admin_response ?? '';
        $this->newStatus = $request->status;
    }

    public function closeView(): void
    {
        $this->viewingId = null;
        $this->adminReply = '';
        $this->newStatus = '';
    }

    public function respond(): void
    {
        $this->validate([
            'adminReply' => 'nullable|max:2000',
            'newStatus' => 'required|in:pending,in_progress,resolved',
        ]);

        $request = TenantRequest::findOrFail($this->viewingId);

        // Once a request has been saved as Resolved, the response is locked.
        // GM must reopen via a fresh request if anything needs to change.
        if ($request->status === 'resolved') {
            session()->flash('error', 'This request is already marked Resolved and can no longer be edited.');
            $this->closeView();
            return;
        }

        $request->update([
            'status' => $this->newStatus,
            'admin_response' => $this->adminReply ?: null,
            'responded_by' => auth()->id(),
            'responded_at' => now(),
        ]);

        NotificationLog::create([
                'user_id' => $request->tenant_id,
                'type' => 'request_response',
                'source' => 'SS7',
                'message' => "Your {$request->type} \"{$request->subject}\" has been updated: {$this->newStatus}.",
            ]);

            if ($request->tenant?->email) {
                Mail::to($request->tenant->email)->send(
                    new RequestResponseMail(
                        $request->tenant->full_name,
                        $request->type,
                        $request->subject,
                        $this->newStatus,
                        $this->adminReply ?: ''
                    )
                );
            }

        $this->closeView();
        session()->flash('success', 'Response saved.');
    }

    public function render()
    {
        $requests = TenantRequest::with('tenant')
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('subject', 'like', "%{$this->search}%")
                    ->orWhereHas('tenant', fn($q) => $q->where('full_name', 'like', "%{$this->search}%"));
            }))
            ->latest()
            ->paginate(15);

        $viewing = $this->viewingId ? TenantRequest::with('tenant', 'respondedBy')->find($this->viewingId) : null;

        return view('livewire.admin.communications.request-viewer', compact('requests', 'viewing'));
    }
}
