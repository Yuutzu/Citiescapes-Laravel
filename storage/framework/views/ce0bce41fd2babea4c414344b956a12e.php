<div>
    <h1 class="text-2xl font-bold text-brand-900 mb-6">Dashboard</h1>

    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="card border-t-4 border-green-500">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Available Rooms</p>
            <p class="text-3xl font-bold text-green-600 mt-2"><?php echo e($availableRooms); ?></p>
            <p class="text-xs text-gray-400 mt-1.5">of <?php echo e($totalRooms); ?> total</p>
        </div>
        <div class="card border-t-4 border-blue-500">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Occupied</p>
            <p class="text-3xl font-bold text-blue-600 mt-2"><?php echo e($occupiedRooms); ?></p>
            <p class="text-xs text-gray-400 mt-1.5"><?php echo e($maintenanceRooms); ?> under maintenance</p>
        </div>
        <div class="card border-t-4 border-brand-500">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Active Tenants</p>
            <p class="text-3xl font-bold text-brand-700 mt-2"><?php echo e($activeTenants); ?></p>
            <p class="text-xs text-gray-400 mt-1.5"><?php echo e($activeContracts); ?> active contracts</p>
        </div>
        <div class="card border-t-4 border-amber-500">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Pending</p>
            <p class="text-3xl font-bold text-amber-600 mt-2"><?php echo e($pendingInquiries); ?></p>
            <p class="text-xs text-gray-400 mt-1.5">inquiries &bull; <?php echo e($unpaidBills); ?> unpaid bills</p>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        
        <div class="card border-t-4 border-emerald-500 flex flex-col justify-center">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Revenue Collected</p>
            <p class="text-4xl font-bold text-emerald-600 mt-3">₱<?php echo e(number_format($totalRevenue, 2)); ?></p>
            <p class="text-xs text-gray-400 mt-2">All confirmed payments</p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($overdueCount > 0): ?>
                <div class="mt-4 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700 font-medium">
                    ⚠ <?php echo e($overdueCount); ?> overdue / delinquent bill(s)
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="card lg:col-span-2">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Monthly Revenue (Last 6 Months)</h3>
            <div style="position:relative; height:200px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        
        <div class="card">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Bill Status Breakdown</h3>
            <div style="position:relative; height:200px;">
                <canvas id="billStatusChart"></canvas>
            </div>
        </div>

        
        <div class="card">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Expiring Soon (≤30 days)</h3>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $expiringList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex items-center justify-between py-2.5 border-b border-gray-100 last:border-0">
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
            <h3 class="text-base font-semibold text-gray-900 mb-4">Recent Inquiries</h3>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recentInquiries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex items-center justify-between py-2.5 border-b border-gray-100 last:border-0">
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
        <h3 class="text-base font-semibold text-gray-900 mb-4">Room Occupancy Overview</h3>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [1, 2, 3]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $floor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="mb-4 last:mb-0">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Floor <?php echo e($floor); ?></p>
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

    
        <?php
        $__scriptKey = '2623383065-0';
        ob_start();
    ?>
    <script>
        (function () {
            function initCharts() {
                // Monthly Revenue Bar Chart
                const revenueCtx = document.getElementById('revenueChart');
                if (revenueCtx && !revenueCtx._chartInstance) {
                    revenueCtx._chartInstance = new Chart(revenueCtx, {
                        type: 'bar',
                        data: {
                            labels: <?php echo json_encode($revenueLabels, 15, 512) ?>,
                            datasets: [{
                                label: 'Revenue (₱)',
                                data: <?php echo json_encode($revenueValues, 15, 512) ?>,
                                backgroundColor: 'rgba(40, 50, 70, 0.75)',
                                borderColor: 'rgba(40, 50, 70, 1)',
                                borderWidth: 1,
                                borderRadius: 4,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: ctx => '₱' + Number(ctx.raw).toLocaleString('en-PH', { minimumFractionDigits: 2 })
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: val => '₱' + Number(val).toLocaleString('en-PH')
                                    }
                                }
                            }
                        }
                    });
                }

                // Bill Status Doughnut Chart
                const billCtx = document.getElementById('billStatusChart');
                if (billCtx && !billCtx._chartInstance) {
                    const statusData = <?php echo json_encode($billStatusCounts, 15, 512) ?>;
                    const labelMap = {
                        unpaid: 'Unpaid', grace: 'Grace Period', overdue: 'Overdue',
                        delinquent: 'Delinquent', eviction: 'Eviction Notice', paid: 'Paid'
                    };
                    const colorMap = {
                        unpaid: '#9ca3af', grace: '#f59e0b', overdue: '#f97316',
                        delinquent: '#ef4444', eviction: '#991b1b', paid: '#22c55e'
                    };
                    const keys = Object.keys(statusData);
                    billCtx._chartInstance = new Chart(billCtx, {
                        type: 'doughnut',
                        data: {
                            labels: keys.map(k => labelMap[k] ?? k),
                            datasets: [{
                                data: keys.map(k => statusData[k]),
                                backgroundColor: keys.map(k => colorMap[k] ?? '#e5e7eb'),
                                borderWidth: 2,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 10 } }
                            }
                        }
                    });
                }
            }

            initCharts();
        })();
    </script>
        <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>
</div>
<?php /**PATH C:\laragon\www\citiescapes\resources\views/livewire/admin/dashboard.blade.php ENDPATH**/ ?>