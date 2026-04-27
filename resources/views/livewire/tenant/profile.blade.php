<div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">My Profile</h1>
    <form wire:submit="save" class="max-w-xl space-y-4">
        <div class="card space-y-4">
            <div><label class="form-label">Full Name</label><input wire:model="full_name" class="form-input">@error('full_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
            <div><label class="form-label">Email</label><input value="{{ $email }}" class="form-input bg-gray-50" disabled><p class="text-xs text-gray-400 mt-1">Contact the GM to change your email.</p></div>
            <div><label class="form-label">Contact Number</label><input wire:model="contact_number" class="form-input"></div>
            <div><label class="form-label">Address</label><input wire:model="address" class="form-input"></div>
            <div><label class="form-label">Emergency Contact</label><input wire:model="emergency_contact" class="form-input"></div>
        </div>
        <button type="submit" class="btn-primary">Save Changes</button>
    </form>
</div>
