<div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="card">
            <p class="text-sm text-gray-500">Available Rooms</p>
            <p class="text-3xl font-bold text-green-600 mt-1"><?php echo e($availableRooms); ?></p>
            <p class="text-xs text-gray-400 mt-1">of <?php echo e($totalRooms); ?> total</p>
        </div>
        <div class="card">
            <p class="text-sm text-gray-500">Occupied</p>
            <p class="text-3xl font-bold text-blue-600 mt-1"><?php echo e($occupiedRooms); ?></p>
            <p class="text-xs text-gray-400 mt-1"><?php echo e($maintenanceRooms); ?> under maintenance</p>
        </div>
        <div class="card">
            <p class="text-sm text-gray-500">Active Tenants</p>
            <p class="text-3xl font-bold text-brand-700 mt-1"><?php echo e($activeTenants); ?></p>
            <p class="text-xs text-gray-400 mt-1"><?php echo e($activeContracts); ?> active contracts</p>
        </div>
        <div class="card">
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-3xl font-bold text-amber-600 mt-1"><?php echo e($pendingInquiries); ?></p>
            <p class="text-xs text-gray-400 mt-1">inquiries &bull; <?php echo e($unpaidBills); ?> unpaid bills</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        
        <div class="card">
            <h3 class="font-semibold text-gray-900 mb-4">Expiring Soon (≤30 days)</h3>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $expiringList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                    <div>
                        <p class="text-sm font-medium text-gray-800"><?php echo e($c->tenant->full_name); ?></p>
                        <p class="text-xs text-gray-500">Room <?php echo e($c->room->room_number); ?></p>
                    </div>
                    <div class="text-right">
                        <span class="badge <?php echo e($c->timer_badge_css); ?>"><?php echo e($c->days_remaining); ?>d left</span>
                        <p class="text-xs text-gray-400 mt-0.5"><?php echo e($c->end_date->format('M d, Y')); ?></p>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-gray-400">No contracts expiring soon.</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="card">
            <h3 class="font-semibold text-gray-900 mb-4">Recent Inquiries</h3>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recentInquiries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                    <div>
                        <p class="text-sm font-medium text-gray-800"><?php echo e($inq->sender_name); ?></p>
                        <p class="text-xs text-gray-500"><?php echo e($inq->preferred_room_type); ?> &bull; <?php echo e($inq->contact_number); ?></p>
                    </div>
                    <span class="badge <?php echo e($inq->status === 'pending' ? 'bg-amber-100 text-amber-800' : ($inq->status === 'responded' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600')); ?>"><?php echo e(ucfirst($inq->status)); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-gray-400">No inquiries yet.</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <div class="card">
        <h3 class="font-semibold text-gray-900 mb-4">Room Occupancy Overview</h3>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [1, 2, 3]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $floor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="mb-4">
                <p class="text-xs font-semibold text-gray-400 uppercase mb-2">Floor <?php echo e($floor); ?></p>
                <div class="grid grid-cols-4 sm:grid-cols-6 lg:grid-cols-8 gap-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $rooms->where('floor_level', $floor); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="rounded-lg p-2 text-center text-xs <?php echo e($room->status === 'occupied' ? 'bg-blue-100 text-blue-800' : ($room->status === 'available' ? 'bg-green-50 text-green-700 ring-1 ring-green-200' : 'bg-amber-50 text-amber-700')); ?>">
                            <p class="font-bold"><?php echo e($room->room_number); ?></p>
                            <p class="truncate"><?php echo e($room->currentTenant?->full_name ?? ($room->status === 'under_maintenance' ? 'Maint.' : 'Open')); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH C:\laragon\www\citiescapes\resources\views/livewire/admin/dashboard.blade.php ENDPATH**/ ?>