<div>
    <h1 class="text-2xl font-bold text-brand-900 mb-6">My Bills & Payment History</h1>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-800 border border-green-200">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-800 border border-red-200">
            {{ session('error') }}
        </div>
    @endif

    {{-- ============ EVICTION / DELINQUENT BANNER (SS3) ============ --}}
    @php
        $criticalBills = $bills->filter(fn($b) => in_array($b->status, ['delinquent', 'eviction'], true));
    @endphp
    @if($criticalBills->isNotEmpty())
        <div class="mb-6 rounded-lg border-2 border-red-300 bg-red-50 p-4">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl mt-0.5"></i>
                <div class="flex-1">
                    <h2 class="text-base font-bold text-red-900">
                        Urgent — Your account is in {{ $criticalBills->first()->status }} status
                    </h2>
                    <p class="text-sm text-red-800 mt-1">
                        You have {{ $criticalBills->count() }} bill(s) past the deadline. Settle the balance immediately,
                        or send a request to the General Manager to discuss a payment plan.
                        Failure to act may result in lease termination.
                    </p>
                    <div class="mt-3 space-y-2">
                        @foreach($criticalBills as $cb)
                            <div class="flex flex-wrap items-center gap-2 bg-white border border-red-200 rounded p-2.5">
                                <span class="text-sm font-semibold text-gray-900">
                                    Bill #{{ $cb->id }} &mdash; {{ $cb->billing_period ?? 'Initial Fees' }}
                                </span>
                                <span class="text-xs text-red-700 font-medium">
                                    ₱{{ number_format($cb->total_amount, 2) }}
                                    @if($cb->days_overdue) &bull; {{ $cb->days_overdue }}d overdue @endif
                                </span>
                                <button wire:click="requestPaymentPlan({{ $cb->id }})"
                                    wire:confirm="Send a payment-plan request to the General Manager about this bill?"
                                    class="ml-auto inline-flex items-center gap-1.5 rounded-lg bg-brand-700 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-brand-800">
                                    <i class="fas fa-paper-plane"></i> Request payment plan
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($initialPayment)
        <div class="card border-l-4 border-emerald-500 mb-6">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-emerald-700 font-semibold">Move-In Payments</p>
                    <p class="font-semibold text-gray-900">Initial Payment Receipt</p>
                    <p class="text-xs text-gray-500">
                        Contract #{{ $initialPayment->contract_id }}
                        @if($initialPayment->contract && $initialPayment->contract->room)
                            &bull; Room {{ $initialPayment->contract->room->room_number }}
                        @endif
                        &bull; Received {{ $initialPayment->date_received->format('M d, Y') }}
                    </p>
                </div>
                <span class="badge bg-emerald-100 text-emerald-800">Paid</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                <div><span class="text-gray-500">Security Deposit:</span><br><span class="font-medium">₱{{ number_format($initialPayment->deposit_amount, 2) }}</span></div>
                <div><span class="text-gray-500">First Month Rent:</span><br><span class="font-medium">₱{{ number_format($initialPayment->first_month_rent, 2) }}</span></div>
                <div><span class="text-gray-500">Room Key Fee:</span><br><span class="font-medium">₱{{ number_format($initialPayment->room_key_fee, 2) }}</span></div>
                <div><span class="text-gray-500">Total Paid:</span><br><span class="font-bold text-emerald-700">₱{{ number_format($initialPayment->total_collected, 2) }}</span></div>
            </div>

            @if(!empty($initialPayment->amenities))
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Amenities Breakdown</p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @foreach($initialPayment->amenities as $a)
                            <div class="bg-gray-50 rounded p-2 text-center">
                                <p class="text-xs text-gray-500">{{ $a['name'] }}</p>
                                <p class="font-medium text-gray-900">₱{{ number_format((float) ($a['fee'] ?? 0), 2) }}</p>
                            </div>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Amenities subtotal:
                        <span class="font-medium text-gray-700">₱{{ number_format($initialPayment->amenities_total, 2) }}</span>
                    </p>
                </div>
            @endif

            <div class="mt-3 pt-3 border-t border-gray-100 flex flex-wrap gap-x-6 gap-y-1 text-xs text-gray-600">
                <span>Method: <span class="font-medium text-gray-800">{{ $initialPayment->payment_method_label }}</span></span>
                @if($initialPayment->reference_number)
                    <span>Reference: <span class="font-medium text-gray-800">{{ $initialPayment->reference_number }}</span></span>
                @endif
            </div>
        </div>
    @endif

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
                            <div class="grid grid-cols-3 gap-2">
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