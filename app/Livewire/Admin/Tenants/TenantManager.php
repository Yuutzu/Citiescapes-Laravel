<?php

namespace App\Livewire\Admin\Tenants;

use App\Mail\TempPasswordMail;
use App\Models\Archive;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Tenant Management — Citiescapes')]
class TenantManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public bool $showCreate = false;

    public string $full_name = '';
    public string $email = '';

    public function create()
    {
        $this->reset(['full_name', 'email']);
        $this->showCreate = true;
    }

    public function store()
    {
        $this->validate([
            'full_name' => 'required|max:100',
            'email'     => 'required|email|unique:users,email',
        ]);

        $tempPassword = Str::random(10);

        $tenant = User::create([
            'full_name'            => $this->full_name,
            'email'                => $this->email,
            'password'             => Hash::make($tempPassword),
            'role'                 => 'tenant',
            'status'               => 'pending_activation',
            'must_change_password' => true,
        ]);

        $mailOk = true;
        try {
            Mail::to($tenant->email)->send(new TempPasswordMail($tenant->full_name, $tenant->email, $tempPassword));
        } catch (\Throwable $e) {
            $mailOk = false;
            \Log::error('TempPasswordMail send failed', ['tenant_id' => $tenant->id, 'error' => $e->getMessage()]);
        }

        AuditLog::record('tenant_created', auth()->id(), 'gm', 'SS2',
            "Created tenant: {$tenant->full_name} ({$tenant->email})" . ($mailOk ? '' : ' — EMAIL FAILED, password not delivered'));

        $this->showCreate = false;
        if ($mailOk) {
            session()->flash('success', "Tenant account created. Temporary password sent to {$tenant->email}.");
        } else {
            session()->flash('error', "Tenant account created, but the temporary-password email failed to send. Reset the password via the user record so the tenant can log in.");
        }
    }

    public function archiveTenant(int $id)
    {
        $tenant = User::findOrFail($id);
        Archive::create([
            'original_record_id' => $tenant->id,
            'record_type'        => 'tenant_account',
            'source_subsystem'   => 'SS2',
            'archive_reason'     => 'Tenant record archived by management',
            'data'               => $tenant->toArray(),
            'archived_by'        => auth()->id(),
        ]);
        $tenant->update(['status' => 'archived', 'archived_at' => now(), 'archived_by' => auth()->id()]);
        AuditLog::record('tenant_archived', auth()->id(), 'gm', 'SS2', "Archived: {$tenant->full_name}");
        session()->flash('success', 'Tenant archived.');
    }

    public function deleteTenant(int $id)
    {
        $tenant = User::findOrFail($id);
        $name = $tenant->full_name;
        $tenant->forceDelete();
        AuditLog::record('tenant_deleted', auth()->id(), 'gm', 'SS2', "Deleted: {$name}");
        session()->flash('success', 'Tenant permanently deleted.');
    }

    public function render()
    {
        $tenants = User::where('role', 'tenant')
            ->when($this->search, function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($qq) use ($term) {
                    $qq->where('full_name', 'like', $term)
                       ->orWhere('email', 'like', $term)
                       ->orWhere('contact_number', 'like', $term)
                       ->orWhere('address', 'like', $term)
                       ->orWhere('emergency_contact', 'like', $term)
                       ->orWhere('status', 'like', $term);
                });
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.tenants.tenant-manager', compact('tenants'));
    }
}
