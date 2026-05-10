<div>
    <h3 class="text-xl font-bold text-brand-900 mb-2">Change your password</h3>
    <p class="text-sm text-gray-500 mb-6">
        @if(auth()->user()->must_change_password)
            You must set a new password before continuing.
        @else
            Update your account password.
        @endif
    </p>

    <form wire:submit="save" class="space-y-5">
        <div>
            <label for="current_password" class="form-label">Current Password</label>
            <input wire:model="current_password" type="password" id="current_password" class="form-input">
            @error('current_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password" class="form-label">New Password</label>
            <input wire:model="password" type="password" id="password" class="form-input">
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password_confirmation" class="form-label">Confirm New Password</label>
            <input wire:model="password_confirmation" type="password" id="password_confirmation" class="form-input">
        </div>
        <div class="flex flex-col gap-2 pt-2">
            <button type="submit" class="btn-primary w-full justify-center">Update Password</button>
            @unless(auth()->user()->must_change_password)
                <a href="{{ auth()->user()->isGm() ? route('admin.dashboard') : route('tenant.dashboard') }}"
                    wire:navigate
                    class="w-full inline-flex items-center justify-center gap-1.5 rounded-md px-4 py-2 text-sm font-semibold text-gray-600 hover:text-brand-700 hover:bg-gray-50 transition">
                    <i class="fas fa-arrow-left text-[11px]"></i> Return to {{ auth()->user()->isGm() ? 'Admin Dashboard' : 'My Portal' }}
                </a>
            @endunless
        </div>
    </form>
</div>