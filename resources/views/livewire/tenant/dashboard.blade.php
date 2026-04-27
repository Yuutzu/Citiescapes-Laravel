<div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Welcome, {{ $user->full_name }}</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Contract timer card --}}
        <div class="card lg:col-span-2">
            <h3 class="font-semibold text-gray-900 mb-4">My Contract</h3>
            @if($contract)
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-sm text-gray-500">Room {{ $contract->room->room_number }} &bull; {{ ucfirst($contract->room->room_type) }}</p>
                        <p class="text-sm text-gray-500">{{ $contract->start_date->format('M d, Y') }} — {{ $contract->end_date->format('M d, Y') }}</p>
                    </div>
                    <span class="badge {{ $contract->timer_badge_css }} text-lg px-4 py-1">{{ $contract->days_remaining }} days left</span>
                </div>
                {{-- Progress bar --}}
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="h-3 rounded-full transition-all duration-500 {{ match($contract->timer_badge) { 'green'=>'bg-green-500','amber'=>'bg-amber-500','red'=>'bg-red-500',default=>'bg-gray-400' } }}" style="width: {{ $contract->progress_percent }}%"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ $contract->progress_percent }}% elapsed</p>
                <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500">Monthly Rent:</span> <span class="font-semibold">₱{{ number_format($contract->base_rent_rate, 2) }}</span></div>
                    <div><span class="text-gray-500">Penalty Rate:</span> <span class="font-semibold">₱{{ number_format($contract->penalty_rate, 2) }}/day</span></div>
                </div>
            @else
                <p class="text-sm text-gray-400">No active contract.</p>
            @endif
        </div>

        {{-- Quick stats --}}
        <div class="space-y-4">
            <div class="card">
                <p class="text-sm text-gray-500">Unpaid Bills</p>
                <p class="text-3xl font-bold {{ $unpaidCount > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $unpaidCount }}</p>
            </div>
            @if($latestBill)
            <div class="card">
                <p class="text-sm text-gray-500">Latest Bill</p>
                <p class="text-lg font-bold text-gray-900">₱{{ number_format($latestBill->total_amount, 2) }}</p>
                <span class="badge {{ $latestBill->status_badge }}">{{ ucfirst($latestBill->status) }}</span>
                @if($latestBill->days_overdue > 0 && $latestBill->status !== 'paid')
                    <p class="text-xs text-red-500 mt-1">{{ $latestBill->days_overdue }} days overdue</p>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Notifications --}}
    <div class="card">
        <h3 class="font-semibold text-gray-900 mb-4">Recent Notifications</h3>
        @forelse($notifications as $n)
            <div class="py-2 border-b border-gray-50 last:border-0 {{ $n->is_read ? '' : 'bg-blue-50/50 -mx-2 px-2 rounded' }}">
                <p class="text-sm text-gray-800">{{ $n->message }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $n->created_at->diffForHumans() }} &bull; {{ $n->source }}</p>
            </div>
        @empty
            <p class="text-sm text-gray-400">No notifications.</p>
        @endforelse
    </div>
</div>
