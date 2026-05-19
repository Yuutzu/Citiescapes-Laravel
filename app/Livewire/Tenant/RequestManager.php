<?php

namespace App\Livewire\Tenant;

use App\Models\AuditLog;
use App\Models\TenantRequest;
use App\Models\NotificationLog;
use App\Models\User;
use App\Mail\TenantRequestMail;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('My Requests')]
class RequestManager extends Component
{
    // Form fields
    public string $type = 'request';
    public string $subject = '';
    public string $body = '';

    // UI state
    public bool $showForm = false;
    public ?int $viewingId = null;
    public bool $showSubmittedModal = false;
    public string $submittedType = '';

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

    public function submit(): void
    {
        $this->validate([
            'type' => 'required|in:request,complaint',
            'subject' => 'required|max:150',
            'body' => 'required|max:2000',
        ]);

        $tr = TenantRequest::create([
            'tenant_id' => auth()->id(),
            'type' => $this->type,
            'subject' => $this->subject,
            'body' => $this->body,
        ]);

        AuditLog::record('tenant_request_submitted', auth()->id(), 'tenant', 'SS7',
            ucfirst($this->type) . " #{$tr->id} \"{$this->subject}\" submitted");

        // Notify all GMs
        $gms = User::where('role', 'gm')->where('status', 'active')->get();
        foreach ($gms as $gm) {
            NotificationLog::create([
                'user_id' => $gm->id,
                'type' => 'tenant_request',
                'source' => 'SS7',
                'message' => "New {$this->type} from " . auth()->user()->full_name . ": {$this->subject}",
            ]);

            if ($gm->email) {
                Mail::to($gm->email)->send(
                    new TenantRequestMail(auth()->user()->full_name, $this->type, $this->subject, $this->body),
                );
            }
        }

        $this->submittedType = $this->type;
        $this->closeForm();
        $this->showSubmittedModal = true;
    }

    public function closeSubmittedModal(): void
    {
        $this->showSubmittedModal = false;
        $this->submittedType = '';
    }

    private function resetForm(): void
    {
        $this->type = 'request';
        $this->subject = '';
        $this->body = '';
    }

    public function render()
    {
        $requests = TenantRequest::where('tenant_id', auth()->id())
            ->with('respondedBy')
            ->latest()
            ->paginate(10);

        $viewing = $this->viewingId
            ? TenantRequest::where('tenant_id', auth()->id())->with('respondedBy')->find($this->viewingId)
            : null;

        return view('livewire.tenant.request-manager', compact('requests', 'viewing'));
    }
}
