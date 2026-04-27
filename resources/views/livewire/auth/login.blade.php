<div>
    <h3 class="text-lg font-semibold text-gray-900 mb-6">Sign in to your account</h3>

    <form wire:submit="login" class="space-y-5">
        <div>
            <label for="email" class="form-label">Email address</label>
            <input wire:model="email" type="email" id="email" class="form-input" autofocus>
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="form-label">Password</label>
            <input wire:model="password" type="password" id="password" class="form-input">
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn-primary w-full justify-center" wire:loading.attr="disabled">
            <span wire:loading.remove>Sign in</span>
            <span wire:loading>Signing in...</span>
        </button>
    </form>
</div>
