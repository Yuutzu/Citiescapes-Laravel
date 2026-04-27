<div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">My Bills & Payment History</h1>

    <div class="space-y-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $bills; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="card">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <p class="font-semibold text-gray-900"><?php echo e($bill->type === 'initial' ? 'Initial Fees' : $bill->billing_period); ?></p>
                        <p class="text-xs text-gray-500">Room <?php echo e($bill->room->room_number); ?> &bull; Due <?php echo e($bill->due_date->format('M d, Y')); ?></p>
                    </div>
                    <span class="badge <?php echo e($bill->status_badge); ?>"><?php echo e(ucfirst($bill->status)); ?><?php echo e($bill->days_overdue > 0 && $bill->status !== 'paid' ? " ({$bill->days_overdue}d)" : ''); ?></span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bill->type === 'initial'): ?>
                        <div><span class="text-gray-500">Deposit:</span><br><span class="font-medium">₱<?php echo e(number_format($bill->deposit_amount, 2)); ?></span></div>
                        <div><span class="text-gray-500">First Month:</span><br><span class="font-medium">₱<?php echo e(number_format($bill->base_rent, 2)); ?></span></div>
                        <div><span class="text-gray-500">Key Fee:</span><br><span class="font-medium">₱<?php echo e(number_format($bill->room_key_fee, 2)); ?></span></div>
                    <?php else: ?>
                        <div><span class="text-gray-500">Rent:</span><br><span class="font-medium">₱<?php echo e(number_format($bill->base_rent, 2)); ?></span></div>
                        <div><span class="text-gray-500">Utilities:</span><br><span class="font-medium">₱<?php echo e(number_format($bill->utilities, 2)); ?></span></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bill->penalty_amount > 0): ?>
                            <div><span class="text-gray-500">Penalty:</span><br><span class="font-medium text-red-600">₱<?php echo e(number_format($bill->penalty_amount, 2)); ?></span></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div><span class="text-gray-500">Total:</span><br><span class="font-bold text-lg text-gray-900">₱<?php echo e(number_format($bill->total_amount, 2)); ?></span></div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bill->payments->count()): ?>
                    <div class="mt-3 border-t border-gray-100 pt-3">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Payment Records</p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $bill->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center justify-between text-sm py-1">
                                <span class="text-gray-600">₱<?php echo e(number_format($p->amount, 2)); ?> via <?php echo e(ucfirst(str_replace('_',' ',$p->payment_method))); ?></span>
                                <span class="text-xs text-gray-400"><?php echo e($p->confirmed_at?->format('M d, Y') ?? 'Pending'); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bill->status !== 'paid' && $bill->days_overdue > 0): ?>
                    <div class="mt-3 rounded-lg bg-red-50 p-3 text-sm text-red-700">
                        This bill is <?php echo e($bill->days_overdue); ?> day(s) overdue. Please settle your payment with the General Manager to avoid further penalties.
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="card"><p class="text-sm text-gray-400">No bills yet.</p></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH C:\laragon\www\citiescapes\resources\views/livewire/tenant/billing-view.blade.php ENDPATH**/ ?>