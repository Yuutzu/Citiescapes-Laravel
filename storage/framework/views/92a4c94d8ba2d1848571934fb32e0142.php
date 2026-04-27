<div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">System Settings</h1>

    <form wire:submit="save" class="max-w-xl space-y-6">
        <div class="card">
            <h3 class="font-semibold text-gray-900 mb-4">Authentication & Security</h3>
            <div class="space-y-4">
                <div>
                    <label class="form-label">Session Timeout (minutes)</label>
                    <input wire:model="session_timeout" type="number" class="form-input">
                    <p class="text-xs text-gray-400 mt-1">Inactive sessions end after this duration.</p>
                </div>
                <div>
                    <label class="form-label">Lockout After (failed attempts)</label>
                    <input wire:model="lockout_threshold" type="number" class="form-input">
                </div>
                <div>
                    <label class="form-label">Lockout Duration (minutes)</label>
                    <input wire:model="lockout_minutes" type="number" class="form-input">
                </div>
                <div>
                    <label class="form-label">OTP Expiry (minutes)</label>
                    <input wire:model="otp_expiry" type="number" class="form-input">
                </div>
            </div>
        </div>

        <div class="card">
            <h3 class="font-semibold text-gray-900 mb-4">Billing Defaults</h3>
            <div class="space-y-4">
                <div>
                    <label class="form-label">Penalty Grace Period (days)</label>
                    <input wire:model="penalty_grace_days" type="number" class="form-input">
                    <p class="text-xs text-gray-400 mt-1">Days after due date before penalty starts.</p>
                </div>
                <div>
                    <label class="form-label">Default Daily Penalty Rate (₱)</label>
                    <input wire:model="default_penalty_rate" type="number" step="0.01" class="form-input">
                </div>
            </div>
        </div>

        <button type="submit" class="btn-primary">Save Settings</button>
    </form>
</div>
<?php /**PATH C:\laragon\www\citiescapes\resources\views/livewire/admin/settings/system-settings.blade.php ENDPATH**/ ?>