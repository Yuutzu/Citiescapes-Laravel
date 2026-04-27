<div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

    {{-- Stats cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="card">
            <p class="text-sm text-gray-500">Available Rooms</p>
            <p class="text-3xl font-bold text-green-600 mt-1">{{ $availableRooms }}</p>
            <p class="text-xs text-gray-400 mt-1">of {{ $totalRooms }} total</p>
        </div>
        <div class="card">
            <p class="text-sm text-gray-500">Occupied</p>
            <p class="text-3xl font-bold text-blue-600 mt-1">{{ $occupiedRooms }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $maintenanceRooms }} under maintenance</p>
        </div>
        <div class="card">
            <p class="text-sm text-gray-500">Active Tenants</p>
            <p class="text-3xl font-bold text-brand-700 mt-1">{{ $activeTenants }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $activeContracts }} active contracts</p>
        </div>
        <div class="card">
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-3xl font-bold text-amber-600 mt-1">{{ $pendingInquiries }}</p>
            <p class="text-xs text-gray-400 mt-1">inquiries &bull; {{ $unpaidBills }} unpaid bills</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Expiring contracts --}}
        <div class="card">
            <h3 class="font-semibold text-gray-900 mb-4">Expiring Soon (≤30 days)</h3>
            @forelse($expiringList as $c)
                <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
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
            <h3 class="font-semibold text-gray-900 mb-4">Recent Inquiries</h3>
            @forelse($recentInquiries as $inq)
                <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
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

    {{-- Occupancy dashboard (all rooms at a glance) --}}
    <div class="card">
        <h3 class="font-semibold text-gray-900 mb-4">Room Occupancy Overview</h3>
        @foreach([1, 2, 3] as $floor)
            <div class="mb-4">
                <p class="text-xs font-semibold text-gray-400 uppercase mb-2">Floor {{ $floor }}</p>
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
</div>
