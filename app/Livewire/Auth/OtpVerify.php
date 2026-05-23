<?php

namespace App\Livewire\Auth;

use App\Models\AuditLog;
use App\Models\OtpRecord;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Verify OTP — Citiescapes')]
class OtpVerify extends Component
{
    public string $otp = '';

    public function verify()
    {
        $this->validate(['otp' => 'required|digits:6']);

        $user = auth()->user();
        $record = OtpRecord::where('user_id', $user->id)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$record || !Hash::check($this->otp, $record->code)) {
            $this->addError('otp', 'Invalid or expired OTP. Please try again.');
            return;
        }

        $record->update(['used' => true]);
        $user->update([
            'status'       => 'active',
            'activated_at' => now(),
        ]);

        AuditLog::record('account_activated', $user->id, $user->role, 'SS6');

        return redirect()->route('password.change')
            ->with('success', 'Account activated! Please set your new password.');
    }

    public function resend()
    {
        $user = auth()->user();
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiry = config('citiescapes.auth.otp_expiry_minutes', 10);

        OtpRecord::create([
            'user_id'    => $user->id,
            'code'       => Hash::make($code),
            'expires_at' => now()->addMinutes($expiry),
        ]);

        try {
            Mail::to($user->email)->send(new OtpMail($user->full_name, $code, $expiry));
            session()->flash('success', 'A new OTP has been sent to your email.');
        } catch (\Throwable $e) {
            \Log::error('OtpMail resend failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            session()->flash('error', 'Could not send the OTP email. Please contact the General Manager.');
        }
    }

    public function render()
    {
        return view('livewire.auth.otp-verify');
    }
}
