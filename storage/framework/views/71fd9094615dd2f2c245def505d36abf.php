<div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">My Contract</h1>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($contract): ?>
        <div class="max-w-2xl space-y-6">
            
            <div class="card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900">Contract Summary</h3>
                    <span class="badge <?php echo e(match($contract->status){ 'draft'=>'bg-gray-100 text-gray-600','active'=>'bg-green-100 text-green-800', default=>'bg-gray-100 text-gray-600' }); ?>"><?php echo e(ucfirst($contract->status)); ?></span>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500">Room:</span> <span class="font-medium"><?php echo e($contract->room->room_number); ?> (<?php echo e(ucfirst($contract->room->room_type)); ?>)</span></div>
                    <div><span class="text-gray-500">Floor:</span> <span class="font-medium"><?php echo e($contract->room->floor_level); ?></span></div>
                    <div><span class="text-gray-500">Monthly Rent:</span> <span class="font-medium">₱<?php echo e(number_format($contract->base_rent_rate, 2)); ?></span></div>
                    <div><span class="text-gray-500">Deposit:</span> <span class="font-medium">₱<?php echo e(number_format($contract->deposit, 2)); ?></span></div>
                    <div><span class="text-gray-500">Key Fee:</span> <span class="font-medium">₱<?php echo e(number_format($contract->room_key_fee, 2)); ?></span></div>
                    <div><span class="text-gray-500">Penalty Rate:</span> <span class="font-medium text-red-600">₱<?php echo e(number_format($contract->penalty_rate, 2)); ?>/day</span></div>
                    <div><span class="text-gray-500">Grace Period:</span> <span class="font-medium"><?php echo e($contract->penalty_grace_days); ?> days</span></div>
                    <div><span class="text-gray-500">Period:</span> <span class="font-medium"><?php echo e($contract->start_date->format('M d, Y')); ?> — <?php echo e($contract->end_date->format('M d, Y')); ?></span></div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($contract->house_rules): ?>
                    <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">House Rules</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line"><?php echo e($contract->house_rules); ?></p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($contract->scan_file_path): ?>
                    <div class="mt-4">
                        <a href="<?php echo e(asset('storage/' . $contract->scan_file_path)); ?>" target="_blank" class="btn-secondary text-sm">View Signed Contract (Scan)</a>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($contract->status === 'active'): ?>
                <div class="card">
                    <h3 class="font-semibold text-gray-900 mb-3">Lease Timer</h3>
                    <div class="flex items-center gap-4 mb-2">
                        <span class="badge <?php echo e($contract->timer_badge_css); ?> text-lg px-4 py-1"><?php echo e($contract->days_remaining); ?> days remaining</span>
                        <span class="text-sm text-gray-500">Ends <?php echo e($contract->end_date->format('M d, Y')); ?></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="h-3 rounded-full <?php echo e(match($contract->timer_badge) { 'green'=>'bg-green-500','amber'=>'bg-amber-500','red'=>'bg-red-500',default=>'bg-gray-400' }); ?>" style="width: <?php echo e($contract->progress_percent); ?>%"></div>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($contract->status === 'draft'): ?>
                <div class="card border-2 border-amber-200 bg-amber-50/50">
                    <h3 class="font-semibold text-gray-900 mb-4">Contract Acknowledgment</h3>

                    
                    <div class="mb-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($contract->step1_acknowledged_at): ?>
                            <div class="flex items-center gap-2 text-green-700">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                                <span class="text-sm font-medium">Step 1 completed — <?php echo e($contract->step1_acknowledged_at->format('M d, Y h:i A')); ?></span>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-gray-700 mb-2"><strong>Step 1:</strong> Please confirm you have read and understood all terms above.</p>
                            <button wire:click="acknowledgeStep1" wire:confirm="I confirm I have read and understood this contract." class="btn-primary text-sm">I have read and understood this contract</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    
                    <div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($contract->step2_acknowledged_at): ?>
                            <div class="flex items-center gap-2 text-green-700">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                                <span class="text-sm font-medium">Step 2 completed — <?php echo e($contract->step2_acknowledged_at->format('M d, Y h:i A')); ?></span>
                            </div>
                        <?php elseif($contract->step1_acknowledged_at): ?>
                            <p class="text-sm text-gray-700 mb-2"><strong>Step 2:</strong> Specifically accept the penalty clause (₱<?php echo e(number_format($contract->penalty_rate, 2)); ?>/day after <?php echo e($contract->penalty_grace_days); ?>-day grace period).</p>
                            <button wire:click="acknowledgeStep2" wire:confirm="I specifically acknowledge and accept the penalty clause." class="btn-danger text-sm">I accept the penalty clause</button>
                        <?php else: ?>
                            <p class="text-sm text-gray-400">Complete Step 1 first to unlock Step 2.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php else: ?>
        <div class="card">
            <p class="text-sm text-gray-400">No active or pending contract found. Please contact the General Manager.</p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\citiescapes\resources\views/livewire/tenant/contract-view.blade.php ENDPATH**/ ?>