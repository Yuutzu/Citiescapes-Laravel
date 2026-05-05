<div>
    <h1 class="text-2xl font-bold text-brand-900 mb-6">My Bills & Payment History</h1>

    <div class="space-y-4">
        @forelse($bills as $bill)
            <div class="card">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <p class="font-semibold text-gray-900">
                            {{ $bill->type === 'initial' ? 'Initial Fees' : $bill->billing_period }}</p>
                        <p class="text-xs text-gray-500">Room {{ $bill->room->room_number }} &bull; Due
                            {{ $bill->due_date->format('M d, Y') }}</p>
                    </div>
                    <span
                        class="badge {{ $bill->status_badge }}">{{ ucfirst($bill->status) }}{{ $bill->days_overdue > 0 && $bill->status !== 'paid' ? " ({$bill->days_overdue}d)" : '' }}</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                    @if($bill->type === 'initial')
                        <div><span class="text-gray-500">Deposit:</span><br><span
                                class="font-medium">₱{{ number_format($bill->deposit_amount, 2) }}</span></div>
                        <div><span class="text-gray-500">First Month:</span><br><span
                                class="font-medium">₱{{ number_format($bill->base_rent, 2) }}</span></div>
                        <div><span class="text-gray-500">Key Fee:</span><br><span
                                class="font-medium">₱{{ number_format($bill->room_key_fee, 2) }}</span></div>
                    @else
                        <div><span class="text-gray-500">Rent:</span><br><span
                                class="font-medium">₱{{ number_format($bill->base_rent, 2) }}</span></div>
                        {{-- Itemized utilities --}}
                        <div class="col-span-2 sm:col-span-3">
                            <span class="text-gray-500 block mb-1">Utilities Breakdown:</span>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <div class="bg-gray-50 rounded p-2 text-center">
                                    <p class="text-xs text-gray-400">Electricity</p>
                                    <p class="font-medium text-gray-900">₱{{ number_format($bill->electricity ?? 0, 2) }}</p>
                                </div>
                                <div class="bg-gray-50 rounded p-2 text-center">
                                    <p class="text-xs text-gray-400">Water</p>
                                    <p class="font-medium text-gray-900">₱{{ number_format($bill->water ?? 0, 2) }}</p>
                                </div>
                                <div class="bg-gray-50 rounded p-2 text-center">
                                    <p class="text-xs text-gray-400">WiFi</p>
                                    <p class="font-medium text-gray-900">₱{{ number_format($bill->wifi ?? 0, 2) }}</p>
                                </div>
                                @if(($bill->extras ?? 0) > 0)
                                <div class="bg-gray-50 rounded p-2 text-center">
                                    <p class="text-xs text-gray-400">Extras</p>
                                    <p class="font-medium text-gray-900">₱{{ number_format($bill->extras, 2) }}</p>
                                    @if($bill->extras_note)
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $bill->extras_note }}</p>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                        @if($bill->penalty_amount > 0)
                            <div><span class="text-gray-500">Penalty:</span><br><span
                                    class="font-medium text-red-600">₱{{ number_format($bill->penalty_amount, 2) }}</span></div>
                        @endif
                    @endif
                    <div><span class="text-gray-500">Total:</span><br><span
                            class="font-bold text-lg text-gray-900">₱{{ number_format($bill->total_amount, 2) }}</span>
                    </div>
                </div>

                @if($bill->payments->count())
                    <div class="mt-3 border-t border-gray-100 pt-3">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Payment Records</p>
                        @foreach($bill->payments as $p)
                            <div class="flex items-center justify-between text-sm py-1">
                                <span class="text-gray-600">₱{{ number_format($p->amount, 2) }} via
                                    {{ ucfirst(str_replace('_', ' ', $p->payment_method)) }}</span>
                                <span class="text-xs text-gray-400">{{ $p->confirmed_at?->format('M d, Y') ?? 'Pending' }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($bill->status !== 'paid' && $bill->days_overdue > 0)
                    <div class="mt-3 rounded-lg bg-red-50 p-3 text-sm text-red-700">
                        This bill is {{ $bill->days_overdue }} day(s) overdue. Please settle your payment with the General
                        Manager to avoid further penalties.
                    </div>
                @endif
            </div>
        @empty
            <div class="card">
                <p class="text-sm text-gray-400">No bills yet.</p>
            </div>
        @endforelse
    </div>
</div>