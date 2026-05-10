<div>
    <h1 class="text-2xl font-bold text-brand-900 mb-6">Dashboard</h1>

    {{-- Stats cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="card border-t-4 border-green-500">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Available Rooms</p>
            <p class="text-3xl font-bold text-green-600 mt-2">{{ $availableRooms }}</p>
            <p class="text-xs text-gray-400 mt-1.5">of {{ $totalRooms }} total</p>
        </div>
        <div class="card border-t-4 border-blue-500">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Occupied</p>
            <p class="text-3xl font-bold text-blue-600 mt-2">{{ $occupiedRooms }}</p>
            <p class="text-xs text-gray-400 mt-1.5">{{ $maintenanceRooms }} under maintenance</p>
        </div>
        <div class="card border-t-4 border-brand-500">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Active Tenants</p>
            <p class="text-3xl font-bold text-brand-700 mt-2">{{ $activeTenants }}</p>
            <p class="text-xs text-gray-400 mt-1.5">{{ $activeContracts }} active contracts</p>
        </div>
        <div class="card border-t-4 border-amber-500">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Pending</p>
            <p class="text-3xl font-bold text-amber-600 mt-2">{{ $pendingInquiries }}</p>
            <p class="text-xs text-gray-400 mt-1.5">inquiries &bull; {{ $unpaidBills }} unpaid bills</p>
        </div>
    </div>

    {{-- Revenue summary + charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        {{-- Total Revenue Card --}}
        <div class="card border-t-4 border-emerald-500 flex flex-col justify-center">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Revenue Collected</p>
            <p class="text-4xl font-bold text-emerald-600 mt-3">₱{{ number_format($totalRevenue, 2) }}</p>
            <p class="text-xs text-gray-400 mt-2">All confirmed payments</p>
            @if($overdueCount > 0)
                <div class="mt-4 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700 font-medium">
                    ⚠ {{ $overdueCount }} overdue / delinquent bill(s)
                </div>
            @endif
        </div>

        {{-- Monthly Revenue Bar Chart --}}
        <div class="card lg:col-span-2">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Monthly Revenue (Last 6 Months)</h3>
            <div style="position:relative; height:200px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Bill Status + other info --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        {{-- Bill Status Doughnut --}}
        <div class="card">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Bill Status Breakdown</h3>
            <div style="position:relative; height:200px;">
                <canvas id="billStatusChart"></canvas>
            </div>
        </div>

        {{-- Expiring contracts --}}
        <div class="card">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Expiring Soon (≤30 days)</h3>
            @forelse($expiringList as $c)
                <div class="flex items-center justify-between py-2.5 border-b border-gray-100 last:border-0">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $c->tenant->full_name }}</p>
                        <p class="text-xs text-gray-500">Room {{ $c->room->room_number }}</p>
                    </div>
                    <div class="text-right">
                        <span class="badge {{ $c->timer_badge_css }}">{{ $c->days_remaining }}d left</span>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $c->end_date->format('M d, Y') }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400">No contracts expiring soon.</p>
            @endforelse
        </div>

        {{-- Recent inquiries --}}
        <div class="card">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Recent Inquiries</h3>
            @forelse($recentInquiries as $inq)
                <div class="flex items-center justify-between py-2.5 border-b border-gray-100 last:border-0">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $inq->sender_name }}</p>
                        <p class="text-xs text-gray-500">{{ $inq->preferred_room_type }} &bull; {{ $inq->contact_number }}</p>
                    </div>
                    <span class="badge {{ $inq->status === 'pending' ? 'bg-amber-100 text-amber-800' : ($inq->status === 'responded' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600') }}">{{ ucfirst($inq->status) }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-400">No inquiries yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Recent Initial Payments (move-in collections) --}}
    <div class="card mb-8">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-semibold text-gray-900">Recent Initial Payments</h3>
                <p class="text-xs text-gray-500 mt-0.5">Total collected to date:
                    <span class="font-semibold text-emerald-700">₱{{ number_format($totalInitialCollected, 2) }}</span>
                </p>
            </div>
            <a href="{{ route('admin.billing.index') }}" wire:navigate
                class="inline-flex items-center gap-1 rounded-full bg-brand-600 px-3 py-1 text-xs font-semibold text-white hover:bg-brand-700 transition">
                Manage Billing
            </a>
        </div>
        @forelse($recentInitialPayments as $ip)
            <div class="flex items-center justify-between py-2.5 border-b border-gray-100 last:border-0">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $ip->tenant?->full_name ?? '—' }}</p>
                    <p class="text-xs text-gray-500">
                        Contract #{{ $ip->contract_id }}
                        @if($ip->contract?->room)
                            &bull; Room {{ $ip->contract->room->room_number }}
                        @endif
                        &bull; {{ ucfirst(str_replace('_', ' ', $ip->payment_method)) }}
                        &bull; {{ $ip->date_received->format('M d, Y') }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-emerald-700">₱{{ number_format($ip->total_collected, 2) }}</p>
                    <p class="text-[11px] text-gray-400">deposit + 1st mo + key
                        @if($ip->amenities_total > 0) + amenities @endif
                    </p>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400">No initial payments recorded yet.</p>
        @endforelse
    </div>

    {{-- Occupancy dashboard (all rooms at a glance) --}}
    <div class="card">
        <h3 class="text-base font-semibold text-gray-900 mb-4">Room Occupancy Overview</h3>
        @foreach([1, 2, 3] as $floor)
            <div class="mb-4 last:mb-0">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Floor {{ $floor }}</p>
                <div class="grid grid-cols-4 sm:grid-cols-6 lg:grid-cols-8 gap-2">
                    @foreach($rooms->where('floor_level', $floor) as $room)
                        <div class="rounded-lg p-2 text-center text-xs {{ $room->status === 'occupied' ? 'bg-blue-100 text-blue-800' : ($room->status === 'available' ? 'bg-green-50 text-green-700 ring-1 ring-green-200' : 'bg-amber-50 text-amber-700') }}">
                            <p class="font-bold">{{ $room->room_number }}</p>
                            <p class="truncate">{{ $room->currentTenant?->full_name ?? ($room->status === 'under_maintenance' ? 'Maint.' : 'Open') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- Chart.js initialisation --}}
    @script
    <script>
        (function () {
            function initCharts() {
                // Monthly Revenue Bar Chart
                const revenueCtx = document.getElementById('revenueChart');
                if (revenueCtx && !revenueCtx._chartInstance) {
                    revenueCtx._chartInstance = new Chart(revenueCtx, {
                        type: 'bar',
                        data: {
                            labels: @json($revenueLabels),
                            datasets: [{
                                label: 'Revenue (₱)',
                                data: @json($revenueValues),
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
                    const statusData = @json($billStatusCounts);
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
    @endscript
</div>
