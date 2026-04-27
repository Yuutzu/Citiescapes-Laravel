<div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">System Audit Log</h1>

    <div class="flex flex-wrap gap-3 mb-6">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search details or user..." class="form-input w-auto text-sm">
        <select wire:model.live="filterAction" class="form-input w-auto text-sm">
            <option value="">All Actions</option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($a); ?>"><?php echo e($a); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </select>
        <select wire:model.live="filterSubsystem" class="form-input w-auto text-sm">
            <option value="">All Subsystems</option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['SS1','SS2','SS3','SS4','SS5','SS6']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ss): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($ss); ?>"><?php echo e($ss); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </select>
    </div>

    <div class="card overflow-x-auto p-0">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Time</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">User</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Action</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SS</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Details</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap"><?php echo e($log->created_at->format('M d H:i:s')); ?></td>
                    <td class="px-4 py-3 text-sm text-gray-700"><?php echo e($log->user?->full_name ?? 'System'); ?></td>
                    <td class="px-4 py-3"><span class="badge bg-brand-50 text-brand-700"><?php echo e($log->action); ?></span></td>
                    <td class="px-4 py-3 text-xs text-gray-500"><?php echo e($log->subsystem ?? '—'); ?></td>
                    <td class="px-4 py-3 text-sm text-gray-600 max-w-sm truncate"><?php echo e($log->details ?? '—'); ?></td>
                    <td class="px-4 py-3 text-xs text-gray-400 font-mono"><?php echo e($log->ip_address); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
        <div class="px-4 py-3"><?php echo e($logs->links()); ?></div>
    </div>
</div>
<?php /**PATH C:\laragon\www\citiescapes\resources\views/livewire/admin/settings/audit-log.blade.php ENDPATH**/ ?>