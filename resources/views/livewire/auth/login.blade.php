<div class="min-h-screen flex flex-col justify-center items-center">
    <div class="w-full max-w-md">
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

            <div class="flex justify-center items-center gap-4 mt-4">
                <a href="/"
                    class="flex items-center justify-center h-12 w-12 rounded-lg bg-gray-200 hover:bg-gray-300 transition text-gray-700 font-semibold text-base"
                    title="Return">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="h-6 w-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </a>
            </div>
        </form>
    </div>
</div>