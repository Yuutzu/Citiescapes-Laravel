<?php

namespace App\Livewire\Admin\Settings;

use App\Models\AuditLog;
use App\Models\SystemSetting;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('System Settings — Citiescapes')]
class SystemSettings extends Component
{
    public int $session_timeout = 120;
    public int $lockout_threshold = 5;
    public int $lockout_minutes = 15;
    public int $otp_expiry = 10;
    public int $penalty_grace_days = 3;
    public float $default_penalty_rate = 100;

    public function mount()
    {
        $this->session_timeout    = (int) SystemSetting::getValue('session_timeout', 120);
        $this->lockout_threshold  = (int) SystemSetting::getValue('lockout_threshold', 5);
        $this->lockout_minutes    = (int) SystemSetting::getValue('lockout_minutes', 15);
        $this->otp_expiry         = (int) SystemSetting::getValue('otp_expiry', 10);
        $this->penalty_grace_days = (int) SystemSetting::getValue('penalty_grace_days', 3);
        $this->default_penalty_rate = (float) SystemSetting::getValue('default_penalty_rate', 100);
    }

    public function save()
    {
        $this->validate([
            'session_timeout'      => 'required|integer|min:5|max:1440',
            'lockout_threshold'    => 'required|integer|min:3|max:20',
            'lockout_minutes'      => 'required|integer|min:5|max:120',
            'otp_expiry'           => 'required|integer|min:5|max:60',
            'penalty_grace_days'   => 'required|integer|min:0|max:30',
            'default_penalty_rate' => 'required|numeric|min:0',
        ]);

        $uid = auth()->id();
        SystemSetting::setValue('session_timeout', $this->session_timeout, $uid);
        SystemSetting::setValue('lockout_threshold', $this->lockout_threshold, $uid);
        SystemSetting::setValue('lockout_minutes', $this->lockout_minutes, $uid);
        SystemSetting::setValue('otp_expiry', $this->otp_expiry, $uid);
        SystemSetting::setValue('penalty_grace_days', $this->penalty_grace_days, $uid);
        SystemSetting::setValue('default_penalty_rate', $this->default_penalty_rate, $uid);

        AuditLog::record('settings_updated', $uid, 'gm', 'SS6', 'System settings updated');
        session()->flash('success', 'Settings saved.');
    }

    public function render()
    {
        return view('livewire.admin.settings.system-settings');
    }
}
