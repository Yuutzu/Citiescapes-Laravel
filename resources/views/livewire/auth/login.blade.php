<div>
    <h3 class="cs-anim-fade-down font-serif text-2xl font-semibold text-brand-900 mb-1">Sign in</h3>
    <p class="cs-anim-fade-down cs-delay-100 text-sm text-brand-700/70 mb-6">Welcome back. Use your registered credentials.</p>

    <form wire:submit="login" class="space-y-5">
        <div class="cs-anim-fade-up cs-delay-200">
            <label for="email" class="form-label">Email address</label>
            <input wire:model="email" type="email" id="email" class="form-input" autofocus>
            @error('email') <p class="mt-1 text-sm text-red-600 cs-anim-fade-down">{{ $message }}</p> @enderror
        </div>

        <div class="cs-anim-fade-up cs-delay-300">
            <label for="password" class="form-label">Password</label>
            <input wire:model="password" type="password" id="password" class="form-input">
            @error('password') <p class="mt-1 text-sm text-red-600 cs-anim-fade-down">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="cs-anim-fade-up cs-delay-400 btn-primary w-full justify-center" wire:loading.attr="disabled">
            <span wire:loading.remove>Sign in</span>
            <span wire:loading>Signing in...</span>
        </button>
    </form>

    <a href="{{ route('home') }}" class="cs-anim-fade cs-delay-500 btn-secondary w-full justify-center mt-3 inline-flex">
        Return
    </a>
</div>