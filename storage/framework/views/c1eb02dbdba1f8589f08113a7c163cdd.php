<div>
    <h3 class="text-xl font-bold text-brand-900 mb-2">Verify your identity</h3>
    <p class="text-sm text-gray-500 mb-6">Enter the 6-digit code sent to your email.</p>

    <form wire:submit="verify" class="space-y-5">
        <div>
            <label for="otp" class="form-label">One-Time Password</label>
            <input wire:model="otp" type="text" id="otp" maxlength="6"
                class="form-input text-center text-2xl tracking-[0.5em] font-mono" autofocus placeholder="000000">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['otp'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <button type="submit" class="btn-primary w-full justify-center">Verify & Activate</button>
    </form>

    <div class="mt-4 text-center">
        <button wire:click="resend" class="text-sm text-brand-600 hover:text-brand-800 font-medium">
            Resend OTP
        </button>
    </div>
</div><?php /**PATH C:\laragon\www\citiescapes\resources\views/livewire/auth/otp-verify.blade.php ENDPATH**/ ?>