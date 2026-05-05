<div>
    <h1 class="text-2xl font-bold text-brand-900 mb-6">My Profile</h1>
    <form wire:submit="save" class="max-w-xl space-y-4">
        <div class="card space-y-4">
            <h3 class="text-base font-semibold text-gray-900 pb-3 border-b border-gray-100">Personal Information</h3>
            <div><label class="form-label">Full Name</label><input wire:model="full_name"
                    class="form-input"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['full_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div>
            <div><label class="form-label">Email</label><input value="<?php echo e($email); ?>" class="form-input bg-gray-50"
                    disabled>
                <p class="text-xs text-gray-400 mt-1">Contact the GM to change your email.</p>
            </div>
            <div><label class="form-label">Contact Number</label><input wire:model="contact_number" class="form-input">
            </div>
            <div><label class="form-label">Address</label><input wire:model="address" class="form-input"></div>
            <div><label class="form-label">Emergency Contact</label><input wire:model="emergency_contact"
                    class="form-input"></div>
        </div>
        <button type="submit" class="btn-primary">Save Changes</button>
    </form>
</div><?php /**PATH C:\laragon\www\citiescapes\resources\views/livewire/tenant/profile.blade.php ENDPATH**/ ?>