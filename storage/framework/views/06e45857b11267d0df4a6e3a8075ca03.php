<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-brand-900">Contract Management</h1>
        <button wire:click="create" class="btn-primary">+ Create Draft</button>
    </div>

    <div class="flex flex-wrap gap-3 mb-6">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search tenant..." class="form-input w-auto text-sm">
        <select wire:model.live="filterStatus" class="form-input w-auto text-sm">
            <option value="">All Status</option>
            <option value="draft">Draft</option><option value="active">Active</option>
            <option value="expired">Expired</option><option value="terminated">Terminated</option>
        </select>
    </div>

    <div class="card overflow-x-auto p-0">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-brand-900">
                <tr>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Tenant</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Room</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Rate</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Period</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Timer</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Scan</th>
                    <th class="px-4 py-3.5 text-right text-xs font-semibold text-brand-200 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $contracts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900"><?php echo e($c->tenant->full_name); ?></td>
                    <td class="px-4 py-3 text-sm text-gray-600"><?php echo e($c->room->room_number); ?></td>
                    <td class="px-4 py-3 text-sm text-gray-600">₱<?php echo e(number_format($c->base_rent_rate, 2)); ?></td>
                    <td class="px-4 py-3 text-xs text-gray-500"><?php echo e($c->start_date->format('M d, Y')); ?> — <?php echo e($c->end_date->format('M d, Y')); ?></td>
                    <td class="px-4 py-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($c->status === 'active'): ?>
                            <span class="badge <?php echo e($c->timer_badge_css); ?>"><?php echo e($c->days_remaining); ?>d</span>
                            <div class="w-16 bg-gray-200 rounded-full h-1.5 mt-1"><div class="bg-brand-600 h-1.5 rounded-full" style="width: <?php echo e($c->progress_percent); ?>%"></div></div>
                        <?php else: ?>
                            <span class="text-xs text-gray-400">—</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="px-4 py-3"><span class="badge <?php echo e(match($c->status){ 'draft'=>'bg-gray-100 text-gray-600','active'=>'bg-green-100 text-green-800','expired'=>'bg-gray-100 text-gray-600','terminated'=>'bg-red-100 text-red-800',default=>'bg-gray-100 text-gray-600' }); ?>"><?php echo e(ucfirst($c->status)); ?></span></td>
                    <td class="px-4 py-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($c->scan_file_path): ?>
                            <a href="<?php echo e(asset('storage/' . $c->scan_file_path)); ?>" target="_blank" class="text-xs text-brand-600 hover:underline">View</a>
                        <?php else: ?> <span class="text-xs text-gray-400">—</span> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-right space-x-1">
                        <button wire:click="edit(<?php echo e($c->id); ?>)" class="text-xs text-brand-600 hover:text-brand-800">Edit</button>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($c->status === 'active'): ?>
                            <button wire:click="renew(<?php echo e($c->id); ?>)" class="text-xs text-green-600 hover:text-green-800">Renew</button>
                            <button wire:click="openTerminate(<?php echo e($c->id); ?>)" class="text-xs text-red-500 hover:text-red-700">Terminate</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
        <div class="px-4 py-3"><?php echo e($contracts->links()); ?></div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal): ?>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto p-6">
            <h3 class="text-lg font-semibold mb-4"><?php echo e($editing ? 'Edit Contract' : 'Create Contract Draft'); ?></h3>
            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Tenant *</label>
                        <select wire:model="tenant_id" class="form-input">
                            <option value="">— Select —</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($t->id); ?>"><?php echo e($t->full_name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['tenant_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <label class="form-label">Room *</label>
                        <select wire:model="room_id" class="form-input">
                            <option value="">— Select —</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($r->id); ?>"><?php echo e($r->room_number); ?> (<?php echo e(ucfirst($r->room_type)); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div><label class="form-label">Rent (₱/mo) *</label><input wire:model="base_rent_rate" type="number" step="0.01" class="form-input"></div>
                    <div><label class="form-label">Deposit (₱)</label><input wire:model="deposit" type="number" step="0.01" class="form-input"></div>
                    <div><label class="form-label">Key Fee (₱)</label><input wire:model="room_key_fee" type="number" step="0.01" class="form-input"></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="form-label">Start Date *</label><input wire:model="start_date" type="date" class="form-input"></div>
                    <div><label class="form-label">End Date *</label><input wire:model="end_date" type="date" class="form-input"></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="form-label">Penalty (₱/day)</label><input wire:model="penalty_rate" type="number" step="0.01" class="form-input"></div>
                    <div><label class="form-label">Grace Days</label><input wire:model="penalty_grace_days" type="number" min="0" class="form-input"></div>
                </div>
                <div><label class="form-label">House Rules</label><textarea wire:model="house_rules" rows="3" class="form-input"></textarea></div>
                <div><label class="form-label">Upload Signed Contract (scan)</label><input wire:model="scanFile" type="file" accept=".pdf,.jpg,.jpeg,.png" class="form-input text-sm"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="$set('showModal', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary"><?php echo e($editing ? 'Update' : 'Save Draft'); ?></button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showTerminate): ?>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-semibold mb-4 text-red-700">Terminate Contract</h3>
            <form wire:submit="terminate" class="space-y-4">
                <div><label class="form-label">Reason *</label><textarea wire:model="terminateReason" rows="3" class="form-input"></textarea><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['terminateReason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="$set('showTerminate', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-danger">Terminate</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\citiescapes\resources\views/livewire/admin/contracts/contract-manager.blade.php ENDPATH**/ ?>