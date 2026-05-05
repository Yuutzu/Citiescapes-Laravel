<div>
    <h1 class="text-2xl font-bold text-brand-900 mb-6">Inquiry Log</h1>

    <div class="flex gap-3 mb-6">
        <select wire:model.live="filterStatus" class="form-input w-auto text-sm">
            <option value="">All</option>
            <option value="pending">Pending</option>
            <option value="responded">Responded</option>
            <option value="closed">Closed</option>
        </select>
    </div>

    <div class="space-y-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $inquiries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="card">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-semibold text-gray-900"><?php echo e($inq->sender_name); ?></p>
                        <p class="text-sm text-gray-500"><?php echo e($inq->contact_number); ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($inq->email): ?> &bull;
                        <?php echo e($inq->email); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></p>
                        <p class="text-xs text-gray-400 mt-0.5">Preferred: <?php echo e(ucfirst($inq->preferred_room_type)); ?> &bull;
                            <?php echo e($inq->created_at->format('M d, Y h:i A')); ?></p>
                    </div>
                    <span
                        class="badge <?php echo e($inq->status === 'pending' ? 'bg-amber-100 text-amber-800' : ($inq->status === 'responded' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600')); ?>"><?php echo e(ucfirst($inq->status)); ?></span>
                </div>
                <p class="mt-3 text-sm text-gray-700 bg-gray-50 rounded-lg p-3"><?php echo e($inq->message); ?></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($inq->gm_notes): ?>
                    <p class="mt-2 text-sm text-brand-700 bg-brand-50 rounded-lg p-3"><span class="font-medium">GM Notes:</span>
                        <?php echo e($inq->gm_notes); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="mt-3 flex gap-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($inq->status === 'pending'): ?>
                        <button wire:click="respond(<?php echo e($inq->id); ?>)" class="btn-primary text-xs">Respond</button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($inq->status !== 'closed'): ?>
                        <button wire:click="close(<?php echo e($inq->id); ?>)" class="btn-secondary text-xs">Close</button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($respondingId === $inq->id): ?>
                    <div class="mt-3 border-t border-gray-100 pt-3">
                        <label class="form-label">Your notes / response</label>
                        <textarea wire:model="gmNotes" rows="2" class="form-input text-sm"></textarea>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['gmNotes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="flex gap-2 mt-2">
                            <button wire:click="saveResponse" class="btn-success text-xs">Save Response</button>
                            <button wire:click="$set('respondingId', null)" class="btn-secondary text-xs">Cancel</button>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <div class="mt-4"><?php echo e($inquiries->links()); ?></div>
</div><?php /**PATH C:\laragon\www\citiescapes\resources\views/livewire/admin/rooms/inquiry-manager.blade.php ENDPATH**/ ?>