<div>
    <h3 class="text-lg font-semibold text-gray-900 mb-2">Verify your identity</h3>
    <p class="text-sm text-gray-500 mb-6">Enter the 6-digit code sent to your email.</p>

    <form wire:submit="verify" class="space-y-5">
        <div>
            <label for="otp" class="form-label">One-Time Password</label>
            <input wire:model="otp" type="text" id="otp" maxlength="6" class="form-input text-center text-2xl tracking-[0.5em] font-mono" autofocus placeholder="000000">
            @error('otp') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn-primary w-full justify-center">Verify & Activate</button>
    </form>

    <div class="mt-4 text-center">
        <button wire:click="resend" class="text-sm text-brand-600 hover:text-brand-800 font-medium">
            Resend OTP
        </button>
    </div>
</div>
