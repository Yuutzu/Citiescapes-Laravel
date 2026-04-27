<?php

namespace App\Livewire\Auth;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Change Password — Citiescapes')]
class ChangePassword extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function save()
    {
        $this->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'Current password is incorrect.');
            return;
        }

        $user->update([
            'password'             => Hash::make($this->password),
            'must_change_password' => false,
        ]);

        AuditLog::record('password_change', $user->id, $user->role, 'SS6');

        return redirect()->route($user->isGm() ? 'admin.dashboard' : 'tenant.dashboard')
            ->with('success', 'Password changed successfully.');
    }

    public function render()
    {
        return view('livewire.auth.change-password');
    }
}
