<div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Reports & Archives</h1>

    <div class="flex flex-wrap gap-3 mb-6">
        <select wire:model.live="filterType" class="form-input w-auto text-sm">
            <option value="">All Types</option>
            <option value="room">Room</option><option value="tenant_account">Tenant</option>
            <option value="payment">Payment</option><option value="contract">Contract</option>
        </select>
        <select wire:model.live="filterSubsystem" class="form-input w-auto text-sm">
            <option value="">All Sources</option>
            <option value="SS1">SS1 — Rooms</option><option value="SS2">SS2 — Tenants</option>
            <option value="SS3">SS3 — Billing</option><option value="SS4">SS4 — Contracts</option>
        </select>
        <input wire:model.live="dateFrom" type="date" class="form-input w-auto text-sm" placeholder="From">
        <input wire:model.live="dateTo" type="date" class="form-input w-auto text-sm" placeholder="To">
    </div>

    <div class="card overflow-x-auto p-0">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Source</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Reason</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Archived</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Scan</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $archives; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-600">#<?php echo e($a->original_record_id); ?></td>
                    <td class="px-4 py-3"><span class="badge bg-gray-100 text-gray-700"><?php echo e(str_replace('_',' ',ucfirst($a->record_type))); ?></span></td>
                    <td class="px-4 py-3 text-sm text-gray-600"><?php echo e($a->source_subsystem); ?></td>
                    <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate"><?php echo e($a->archive_reason); ?></td>
                    <td class="px-4 py-3 text-xs text-gray-500"><?php echo e($a->created_at->format('M d, Y')); ?></td>
                    <td class="px-4 py-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($a->scan_file_path): ?>
                            <a href="<?php echo e(asset('storage/' . $a->scan_file_path)); ?>" target="_blank" class="text-xs text-brand-600 hover:underline">View</a>
                        <?php else: ?> — <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-right space-x-1">
                        <button wire:click="restore(<?php echo e($a->id); ?>)" wire:confirm="Restore this record?" class="text-xs text-green-600 hover:text-green-800">Restore</button>
                        <button wire:click="permanentDelete(<?php echo e($a->id); ?>)" wire:confirm="PERMANENTLY delete? Cannot be undone." class="text-xs text-red-500 hover:text-red-700">Delete</button>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
        <div class="px-4 py-3"><?php echo e($archives->links()); ?></div>
    </div>
</div>
<?php /**PATH C:\laragon\www\citiescapes\resources\views/livewire/admin/reports/report-manager.blade.php ENDPATH**/ ?>