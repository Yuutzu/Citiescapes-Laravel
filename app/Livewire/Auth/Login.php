<?php

namespace App\Livewire\Auth;

use App\Models\AuditLog;
use App\Models\OtpRecord;
use App\Models\User;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('Login — Citiescapes')]
class Login extends Component
{
    public string $email = '';
    public string $password = '';

    public function login()
    {
        $this->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        $user = User::where('email', $this->email)->first();

        if (!$user) {
            AuditLog::record('failed_login', null, null, 'SS6', "Unknown email: {$this->email}");
            $this->addError('email', 'Invalid credentials.');
            return;
        }

        // Auto-unlock if lockout period passed
        $user->autoUnlockIfExpired();

        if ($user->isLocked()) {
            $mins = (int) ceil(now()->diffInSeconds($user->locked_until, true) / 60);
            $mins = max($mins, 1);
            $this->addError('email', "Account locked. Try again in {$mins} minute(s).");
            return;
        }

        if ($user->status === 'archived') {
            $this->addError('email', 'Account archived. Contact the General Manager.');
            return;
        }

        if (!Hash::check($this->password, $user->password)) {
            $user->incrementFailedLogin();
            AuditLog::record('failed_login', $user->id, $user->role, 'SS6', "Attempt #{$user->failed_login_attempts}");

            // If just locked, notify GM
            if ($user->status === 'locked') {
                $gms = User::where('role', 'gm')->where('status', 'active')->get();
                foreach ($gms as $gm) {
                    \App\Models\NotificationLog::create([
                        'user_id' => $gm->id,
                        'type'    => 'account_locked',
                        'source'  => 'SS6',
                        'message' => "Account for {$user->full_name} ({$user->email}) was locked after 5 failed attempts.",
                    ]);
                }
                $this->addError('email', 'Account locked after too many failed attempts. Try again in 15 minutes.');
                return;
            }

            $this->addError('email', 'Invalid credentials.');
            return;
        }

        // Successful authentication
        $user->resetFailedLogin();
        Auth::login($user, true);
        $user->update(['last_login_at' => now()]);
        AuditLog::record('login', $user->id, $user->role, 'SS6');

        // If pending activation, send OTP
        if ($user->status === 'pending_activation') {
            $this->sendOtp($user);
            return redirect()->route('otp.verify');
        }

        // Redirect based on role
        if ($user->must_change_password) {
            return redirect()->route('password.change');
        }

        return redirect()->intended(
            $user->isGm() ? route('admin.dashboard') : route('tenant.dashboard')
        );
    }

    private function sendOtp(User $user): void
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiry = config('citiescapes.auth.otp_expiry_minutes', 10);

        OtpRecord::create([
            'user_id'    => $user->id,
            'code'       => Hash::make($code),
            'expires_at' => now()->addMinutes($expiry),
        ]);

        try {
            Mail::to($user->email)->send(new OtpMail($user->full_name, $code, $expiry));
        } catch (\Throwable $e) {
            \Log::error('OtpMail send failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            session()->flash('error', 'Could not send the OTP email. Please contact the General Manager.');
        }
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
