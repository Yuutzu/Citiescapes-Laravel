<?php

namespace App\Livewire\Tenant;

use App\Models\AuditLog;
use App\Models\NotificationLog;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('My Profile — Citiescapes')]
class Profile extends Component
{
    public string $full_name = '';
    public string $email = '';
    public string $contact_number = '';
    public string $address = '';
    public string $emergency_contact = '';

    public function mount()
    {
        $u = auth()->user();
        $this->full_name = $u->full_name;
        $this->email = $u->email;
        $this->contact_number = $u->contact_number ?? '';
        $this->address = $u->address ?? '';
        $this->emergency_contact = $u->emergency_contact ?? '';
    }

    public function save()
    {
        $this->validate([
            'full_name'         => 'required|max:100',
            'contact_number'    => 'nullable|max:20',
            'address'           => 'nullable|max:255',
            'emergency_contact' => 'nullable|max:100',
        ]);

        $tenant = auth()->user();

        $tenant->update([
            'full_name'         => $this->full_name,
            'contact_number'    => $this->contact_number,
            'address'           => $this->address,
            'emergency_contact' => $this->emergency_contact,
        ]);

        AuditLog::record('profile_updated', $tenant->id, 'tenant', 'SS2');

        // Notify the tenant themselves (bell confirmation).
        NotificationLog::create([
            'user_id' => $tenant->id,
            'type'    => 'profile_updated',
            'source'  => 'SS2',
            'message' => 'Your profile has been updated successfully.',
        ]);

        // Notify every active GM so they're aware of tenant-side profile changes.
        $gms = User::where('role', 'gm')->where('status', 'active')->get();
        foreach ($gms as $gm) {
            NotificationLog::create([
                'user_id' => $gm->id,
                'type'    => 'profile_updated',
                'source'  => 'SS2',
                'message' => "{$tenant->full_name} updated their profile.",
            ]);
        }

        session()->flash('success', 'Profile updated.');
    }

    public function render()
    {
        return view('livewire.tenant.profile');
    }
}
