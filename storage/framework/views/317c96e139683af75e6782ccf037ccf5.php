<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Billing Management</h1>
        <div class="flex gap-2">
            <button wire:click="openInitial" class="btn-secondary text-sm">Record Initial Fees</button>
            <button wire:click="openGenerate" class="btn-primary text-sm">Generate Monthly Bill</button>
        </div>
    </div>

    <div class="flex flex-wrap gap-3 mb-6">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search tenant..." class="form-input w-auto text-sm">
        <select wire:model.live="filterStatus" class="form-input w-auto text-sm">
            <option value="">All Status</option>
            <option value="unpaid">Unpaid</option><option value="grace">Grace</option>
            <option value="overdue">Overdue</option><option value="delinquent">Delinquent</option>
            <option value="eviction">Eviction</option><option value="paid">Paid</option>
        </select>
    </div>

    <div class="card overflow-x-auto p-0">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tenant</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Room</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Period</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Penalty</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Due</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $bills; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-900"><?php echo e($bill->tenant->full_name); ?></td>
                    <td class="px-4 py-3 text-sm text-gray-600"><?php echo e($bill->room->room_number); ?></td>
                    <td class="px-4 py-3 text-sm text-gray-600"><?php echo e($bill->billing_period); ?></td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">₱<?php echo e(number_format($bill->total_amount, 2)); ?></td>
                    <td class="px-4 py-3 text-sm <?php echo e($bill->penalty_amount > 0 ? 'text-red-600 font-medium' : 'text-gray-400'); ?>">
                        <?php echo e($bill->penalty_amount > 0 ? '₱'.number_format($bill->penalty_amount, 2) : '—'); ?>

                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600"><?php echo e($bill->due_date->format('M d')); ?></td>
                    <td class="px-4 py-3"><span class="badge <?php echo e($bill->status_badge); ?>"><?php echo e(ucfirst($bill->status)); ?><?php echo e($bill->days_overdue > 0 && $bill->status !== 'paid' ? " ({$bill->days_overdue}d)" : ''); ?></span></td>
                    <td class="px-4 py-3 text-right space-x-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bill->status !== 'paid' && $bill->status !== 'archived'): ?>
                            <button wire:click="openPayment(<?php echo e($bill->id); ?>)" class="text-xs text-green-600 hover:text-green-800 font-medium">Confirm Pay</button>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bill->penalty_amount > 0): ?>
                                <button wire:click="openOverride(<?php echo e($bill->id); ?>)" class="text-xs text-amber-600 hover:text-amber-800 font-medium">Override</button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
        <div class="px-4 py-3"><?php echo e($bills->links()); ?></div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showGenerate): ?>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-semibold mb-4">Generate Monthly Bill</h3>
            <form wire:submit="generateBill" class="space-y-4">
                <div>
                    <label class="form-label">Select Contract *</label>
                    <select wire:model="genContractId" class="form-input">
                        <option value="">— Select —</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $activeContracts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($c->id); ?>"><?php echo e($c->tenant->full_name); ?> — Room <?php echo e($c->room->room_number); ?> (₱<?php echo e(number_format($c->base_rent_rate,2)); ?>/mo)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['genContractId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <label class="form-label">Utilities (₱)</label>
                    <input wire:model="genUtilities" type="number" step="0.01" class="form-input">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="$set('showGenerate', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Generate</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showInitial): ?>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-semibold mb-4">Record Initial Fees</h3>
            <form wire:submit="recordInitial" class="space-y-4">
                <div>
                    <label class="form-label">Select Contract *</label>
                    <select wire:model="initContractId" class="form-input">
                        <option value="">— Select —</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $activeContracts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($c->id); ?>"><?php echo e($c->tenant->full_name); ?> — Room <?php echo e($c->room->room_number); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
                <div><label class="form-label">Security Deposit (₱)</label><input wire:model="initDeposit" type="number" step="0.01" class="form-input"></div>
                <div><label class="form-label">First Month Rent (₱)</label><input wire:model="initFirstMonth" type="number" step="0.01" class="form-input"></div>
                <div><label class="form-label">Room Key Fee (₱)</label><input wire:model="initKeyFee" type="number" step="0.01" class="form-input"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="$set('showInitial', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Record</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPayment): ?>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-semibold mb-4">Confirm Payment</h3>
            <form wire:submit="confirmPayment" class="space-y-4">
                <div><label class="form-label">Amount (₱)</label><input wire:model="payAmount" type="number" step="0.01" class="form-input"></div>
                <div>
                    <label class="form-label">Method</label>
                    <select wire:model="payMethod" class="form-input">
                        <option value="cash">Cash</option><option value="gcash">GCash</option><option value="bank_transfer">Bank Transfer</option><option value="other">Other</option>
                    </select>
                </div>
                <div><label class="form-label">Reference # (optional)</label><input wire:model="payReference" class="form-input"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="$set('showPayment', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-success">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showOverride): ?>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-semibold mb-4">Override / Waive Penalty</h3>
            <form wire:submit="saveOverride" class="space-y-4">
                <div><label class="form-label">New Penalty Amount (₱)</label><input wire:model="overrideAmount" type="number" step="0.01" class="form-input"><p class="text-xs text-gray-400 mt-1">Set to 0 to fully waive.</p></div>
                <div><label class="form-label">Reason (required) *</label><textarea wire:model="overrideReason" rows="2" class="form-input"></textarea><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['overrideReason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="$set('showOverride', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Save Override</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\citiescapes\resources\views/livewire/admin/billing/billing-manager.blade.php ENDPATH**/ ?>