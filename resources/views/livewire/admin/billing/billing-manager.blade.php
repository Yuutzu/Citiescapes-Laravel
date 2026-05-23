<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <h1 class="text-2xl font-bold text-brand-900">Billing Management</h1>
        <div class="flex flex-wrap gap-2">
            <button wire:click="openInitial"
                class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-brand-700 ring-1 ring-brand-300 shadow-sm hover:bg-brand-50 transition">
                <i class="fas fa-receipt text-[11px]"></i> Record Initial Fees
            </button>
            <button wire:click="openGenerate"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-800 transition">
                <i class="fas fa-plus text-[11px]"></i> Generate Monthly Bill
            </button>
        </div>
    </div>

    <div class="space-y-3 mb-6">
        <div class="relative max-w-md">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Search tenant, room, status, period, reference..."
                class="form-input w-full pl-9 text-sm">
        </div>
        @php
            $bBtn = fn($v) => 'inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition ' . ($filterStatus === (string)$v ? 'bg-brand-700 text-white hover:bg-brand-800' : 'bg-white text-brand-700 ring-1 ring-brand-300 hover:bg-brand-50');
        @endphp
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mr-1">Status:</span>
            <button wire:click="$set('filterStatus','')" class="{{ $bBtn('') }}">All</button>
            <button wire:click="$set('filterStatus','unpaid')" class="{{ $bBtn('unpaid') }}">Unpaid</button>
            <button wire:click="$set('filterStatus','grace')" class="{{ $bBtn('grace') }}">Grace</button>
            <button wire:click="$set('filterStatus','overdue')" class="{{ $bBtn('overdue') }}">Overdue</button>
            <button wire:click="$set('filterStatus','delinquent')" class="{{ $bBtn('delinquent') }}">Delinquent</button>
            <button wire:click="$set('filterStatus','eviction')" class="{{ $bBtn('eviction') }}">Eviction</button>
            <button wire:click="$set('filterStatus','paid')" class="{{ $bBtn('paid') }}">Paid</button>
        </div>
    </div>

    {{-- ============ INITIAL PAYMENTS (Move-In) ============ --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-base font-semibold text-brand-900">
                <i class="fas fa-receipt text-emerald-600 mr-1"></i> Initial Payments (Move-In)
            </h2>
            <span class="text-xs text-gray-500">{{ $initialPayments->count() }} recorded</span>
        </div>

        @if($initialPayments->isEmpty())
            <div class="card text-sm text-gray-400 italic">No initial payments recorded yet.</div>
        @else
            <div class="card overflow-x-auto p-0">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-emerald-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-100 uppercase tracking-wider">Tenant</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-100 uppercase tracking-wider">Room / Contract</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-100 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-100 uppercase tracking-wider">Breakdown</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-emerald-100 uppercase tracking-wider">Total Collected</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-100 uppercase tracking-wider">Method</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-100 uppercase tracking-wider">Recorded By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($initialPayments as $ip)
                            <tr class="hover:bg-emerald-50/40">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $ip->tenant->full_name ?? 'â€”' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    Room {{ $ip->contract->room->room_number ?? 'â€”' }}
                                    <span class="text-xs text-gray-400">(#{{ $ip->contract_id }})</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $ip->date_received->format('M d, Y') }}</td>
                                <td class="px-4 py-3 text-xs text-gray-600 leading-snug">
                                    <div>Deposit: <span class="font-medium text-gray-800">â‚±{{ number_format($ip->deposit_amount, 2) }}</span></div>
                                    <div>1st Rent: <span class="font-medium text-gray-800">â‚±{{ number_format($ip->first_month_rent, 2) }}</span></div>
                                    <div>Key Fee: <span class="font-medium text-gray-800">â‚±{{ number_format($ip->room_key_fee, 2) }}</span></div>
                                    <div>Amenities: <span class="font-medium text-gray-800">â‚±{{ number_format($ip->amenities_total, 2) }}</span></div>
                                    @if(!empty($ip->amenities))
                                        <div class="mt-1 text-[11px] text-gray-500">
                                            @foreach($ip->amenities as $a)
                                                <span class="inline-block bg-gray-100 rounded px-1.5 py-0.5 mr-1 mb-1">{{ $a['name'] }} â‚±{{ number_format((float) ($a['fee'] ?? 0), 2) }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-bold text-emerald-700">â‚±{{ number_format($ip->total_collected, 2) }}</td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    {{ $ip->payment_method_label }}
                                    @if($ip->reference_number)
                                        <div class="text-[11px] text-gray-400">Ref: {{ $ip->reference_number }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $ip->recordedBy->full_name ?? 'â€”' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ============ MONTHLY BILLS ============ --}}
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-base font-semibold text-brand-900">
            <i class="fas fa-file-invoice text-brand-700 mr-1"></i> Monthly Bills
        </h2>
    </div>
    <div class="card overflow-x-auto p-0">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-brand-900">
                <tr>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider cursor-pointer hover:text-white"
                        wire:click="sortBy('tenant_name')">
                        Tenant @if($sortBy === 'tenant_name') @if($sortDirection === 'asc') â†‘ @else â†“ @endif @endif</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Room
                    </th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">
                        Period</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">
                        Total</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">
                        Penalty</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Due
                    </th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">
                        Status</th>
                    <th class="px-4 py-3.5 text-right text-xs font-semibold text-brand-200 uppercase tracking-wider">
                        Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($bills as $bill)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $bill->tenant->full_name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $bill->room?->room_number ?? 'â€”' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $bill->billing_period }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">â‚±{{ number_format($bill->total_amount, 2) }}
                        </td>
                        <td
                            class="px-4 py-3 text-sm {{ $bill->penalty_amount > 0 ? 'text-red-600 font-medium' : 'text-gray-400' }}">
                            {{ $bill->penalty_amount > 0 ? 'â‚±' . number_format($bill->penalty_amount, 2) : 'â€”' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $bill->due_date->format('M d') }}</td>
                        <td class="px-4 py-3"><span
                                class="badge {{ $bill->status_badge }}">{{ ucfirst($bill->status) }}{{ $bill->days_overdue > 0 && $bill->status !== 'paid' ? " ({$bill->days_overdue}d)" : '' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2 flex-wrap">
                                @if($bill->status !== 'paid' && $bill->status !== 'archived')
                                    <button wire:click="openPayment({{ $bill->id }})"
                                        class="inline-flex items-center gap-1 rounded-full bg-green-600 px-3 py-1 text-xs font-semibold text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-400 transition">
                                        <i class="fas fa-check text-[10px]"></i> Confirm Pay
                                    </button>
                                    @if($bill->penalty_amount > 0)
                                        <button wire:click="openOverride({{ $bill->id }})"
                                            class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-300 hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-amber-400 transition">
                                            <i class="fas fa-gavel text-[10px]"></i> Override
                                        </button>
                                    @endif

                                    {{-- Eviction / delinquent shortcuts: apply deposit credit
                                         or terminate the contract. Only surface when the GM has
                                         crossed into the serious-arrears zone. --}}
                                    @if(in_array($bill->status, ['delinquent', 'eviction'], true))
                                        <button wire:click="applyDepositToArrears({{ $bill->id }})"
                                            wire:confirm="Apply remaining security deposit toward this bill?"
                                            class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-300 hover:bg-indigo-50 transition">
                                            <i class="fas fa-piggy-bank text-[10px]"></i> Apply Deposit
                                        </button>
                                        <button wire:click="terminateForArrears({{ $bill->id }})"
                                            wire:confirm="Terminate the contract for non-payment? This archives the contract and frees the room. Cannot be undone."
                                            class="inline-flex items-center gap-1 rounded-full bg-red-600 px-3 py-1 text-xs font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-400 transition">
                                            <i class="fas fa-ban text-[10px]"></i> Mark for Termination
                                        </button>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-300">&mdash;</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $bills->links() }}</div>
    </div>

    {{-- Generate Monthly Bill Modal --}}
    @if($showGenerate)
        <div class="cs-modal">
            <div class="bg-white rounded-xl shadow-xl w-full max-h-[88vh] overflow-y-auto max-w-md mx-4 p-6 my-auto">
                <h3 class="text-lg font-semibold mb-4">Generate Monthly Bill</h3>
                <form wire:submit="generateBill" class="space-y-4">
                    <div>
                        <label class="form-label">Select Contract *</label>
                        <select wire:model="genContractId" class="form-input">
                            <option value="">â€” Select â€”</option>
                            @foreach($activeContracts as $c)
                                <option value="{{ $c->id }}">{{ $c->tenant->full_name }} â€” Room {{ $c->room->room_number }}
                                    (â‚±{{ number_format($c->base_rent_rate, 2) }}/mo)</option>
                            @endforeach
                        </select>
                        @error('genContractId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Utility Breakdown</p>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="form-label">Electricity (â‚±)</label>
                            <input wire:model="genElectricity" type="number" step="0.01" min="0" class="form-input">
                            @error('genElectricity') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Water (â‚±)</label>
                            <input wire:model="genWater" type="number" step="0.01" min="0" class="form-input">
                            @error('genWater') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">WiFi (â‚±)</label>
                            <input wire:model="genWifi" type="number" step="0.01" min="0" class="form-input">
                            @error('genWifi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showGenerate', false)" class="btn-secondary">Cancel</button>
                        <button type="submit" class="btn-primary">Generate</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Record Initial Fees Modal --}}
    @if($showInitial)
        @php
            $c = $this->initContract;
            $firstRent = $c ? ($c->first_month_rent ?? $c->base_rent_rate) : 0;
            $amenitiesTotal = $this->initAmenitiesTotal;
            $total = $c ? ((float) $c->deposit + (float) $firstRent + (float) $c->room_key_fee + $amenitiesTotal) : 0;
        @endphp
        <div class="cs-modal">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6">
                <h3 class="text-lg font-semibold mb-1">Record Initial Payment</h3>
                <p class="text-xs text-gray-500 mb-4">Move-in amounts come from the signed contract (SS4). Amenities can be adjusted below if the tenant changed their request at move-in.</p>
                <form wire:submit="recordInitial" class="space-y-4">
                    <div>
                        <label class="form-label">Select Contract *</label>
                        <select wire:model.live="initContractId" class="form-input">
                            <option value="">â€” Select â€”</option>
                            @foreach($contractsAwaitingInitial as $opt)
                                <option value="{{ $opt->id }}">
                                    {{ $opt->tenant->full_name }} â€” Room {{ $opt->room->room_number }} (Contract #{{ $opt->id }})
                                </option>
                            @endforeach
                        </select>
                        @if($contractsAwaitingInitial->isEmpty())
                            <p class="text-xs text-amber-700 mt-1">No contracts awaiting initial payment.</p>
                        @endif
                    </div>

                    @if($c)
                        <div class="rounded-md bg-emerald-50 border border-emerald-200 p-3 space-y-2">
                            <p class="text-[11px] uppercase tracking-wide text-emerald-700 font-semibold">From contract â€” read only</p>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div><span class="text-gray-500">Security Deposit</span><div class="font-semibold">â‚±{{ number_format($c->deposit, 2) }}</div></div>
                                <div><span class="text-gray-500">First Month Rent</span><div class="font-semibold">â‚±{{ number_format($firstRent, 2) }}</div></div>
                                <div><span class="text-gray-500">Room Key Fee</span><div class="font-semibold">â‚±{{ number_format($c->room_key_fee, 2) }}</div></div>
                                <div><span class="text-gray-500">Amenities</span><div class="font-semibold">â‚±{{ number_format($amenitiesTotal, 2) }}</div></div>
                            </div>
                            <div class="border-t border-emerald-200 pt-2 flex items-center justify-between text-sm">
                                <span class="text-gray-600 font-medium">Total to collect</span>
                                <span class="font-bold text-emerald-700 text-base">â‚±{{ number_format($total, 2) }}</span>
                            </div>
                        </div>

                        {{-- Amenities â€” prefilled from contract, editable here --}}
                        <div class="rounded-md border border-gray-200 p-3 space-y-2">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold text-gray-700 uppercase tracking-wider">Requested Amenities</p>
                                    <p class="text-[11px] text-gray-500">One-time fees billed only on this move-in payment.</p>
                                </div>
                                <button type="button" wire:click="addInitAmenity" class="text-xs text-brand-600 hover:text-brand-800 font-medium">+ Add</button>
                            </div>

                            @forelse($initAmenities as $i => $a)
                                <div class="grid grid-cols-12 gap-2 items-center" wire:key="init-amenity-{{ $i }}">
                                    <input wire:model.live="initAmenities.{{ $i }}.name" type="text" placeholder="e.g. Aircon"
                                        class="form-input col-span-7 text-sm">
                                    <input wire:model.live="initAmenities.{{ $i }}.fee" type="number" step="0.01" min="0" placeholder="Fee (â‚±)"
                                        class="form-input col-span-4 text-sm">
                                    <button type="button" wire:click="removeInitAmenity({{ $i }})"
                                        class="col-span-1 text-red-500 hover:text-red-700 text-lg leading-none">&times;</button>
                                </div>
                            @empty
                                <p class="text-xs text-gray-400 italic">No amenities to charge.</p>
                            @endforelse
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">Date Received *</label>
                            <input wire:model="initDateReceived" type="date" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Payment Method *</label>
                            <select wire:model.live="initPaymentMethod" class="form-input">
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="e_wallet">E-Wallet</option>
                            </select>
                        </div>
                    </div>

                    @if(in_array($initPaymentMethod, ['bank_transfer', 'e_wallet']))
                        <div class="rounded-lg border border-marigold-300 bg-marigold-50 p-3">
                            <p class="text-xs uppercase tracking-wider text-brand-700 font-semibold mb-2">Accepted channels</p>
                            <div class="flex items-center gap-3">
                                @if($initPaymentMethod === 'bank_transfer')
                                    <img src="/storage/payment-methods/bdo.jpg" alt="BDO" class="h-10 rounded ring-1 ring-brand-200 object-cover cs-anim-zoom-in">
                                    <img src="/storage/payment-methods/bpi.jpg" alt="BPI" class="h-10 rounded ring-1 ring-brand-200 object-cover cs-anim-zoom-in cs-delay-100">
                                @else
                                    <img src="/storage/payment-methods/gcash.jpg" alt="GCash" class="h-10 rounded ring-1 ring-brand-200 object-cover cs-anim-zoom-in">
                                @endif
                                <p class="text-xs text-brand-700/80">Provide the reference number below after payment.</p>
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="form-label">Reference Number <span class="text-brand-400 text-xs">(optional, for transfer / e-wallet)</span></label>
                        <input wire:model="initReferenceNumber" type="text" class="form-input" placeholder="e.g. GCash ref no.">
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showInitial', false)" class="btn-secondary">Cancel</button>
                        <button type="submit" class="btn-primary" @disabled(!$c)>Record Payment</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Confirm Payment Modal --}}
    @if($showPayment)
        <div class="cs-modal">
            <div class="bg-white rounded-xl shadow-xl w-full max-h-[88vh] overflow-y-auto max-w-md mx-4 p-6 my-auto">
                <h3 class="text-lg font-semibold mb-4">Confirm Payment</h3>
                <form wire:submit="confirmPayment" class="space-y-4">
                    <div><label class="form-label">Amount (â‚±)</label><input wire:model="payAmount" type="number" step="0.01"
                            class="form-input"></div>
                    <div>
                        <label class="form-label">Method</label>
                        <select wire:model="payMethod" class="form-input">
                            <option value="cash">Cash</option>
                            <option value="gcash">GCash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div><label class="form-label">Reference # (optional)</label><input wire:model="payReference"
                            class="form-input"></div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showPayment', false)" class="btn-secondary">Cancel</button>
                        <button type="submit" class="btn-success">Confirm Payment</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Override Penalty Modal --}}
    @if($showOverride)
        <div class="cs-modal">
            <div class="bg-white rounded-xl shadow-xl w-full max-h-[88vh] overflow-y-auto max-w-md mx-4 p-6 my-auto">
                <h3 class="text-lg font-semibold mb-4">Override / Waive Penalty</h3>
                <form wire:submit="saveOverride" class="space-y-4">
                    <div><label class="form-label">New Penalty Amount (â‚±)</label><input wire:model="overrideAmount"
                            type="number" step="0.01" class="form-input">
                        <p class="text-xs text-gray-400 mt-1">Set to 0 to fully waive.</p>
                    </div>
                    <div><label class="form-label">Reason (required) *</label><textarea wire:model="overrideReason" rows="2"
                            class="form-input"></textarea>@error('overrideReason')<p class="text-xs text-red-600 mt-1">
                                {{ $message }}
                            </p>@enderror</div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showOverride', false)" class="btn-secondary">Cancel</button>
                        <button type="submit" class="btn-primary">Save Override</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
