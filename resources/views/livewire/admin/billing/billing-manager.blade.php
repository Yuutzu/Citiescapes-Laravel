<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-brand-900">Billing Management</h1>
        <div class="flex gap-2">
            <button wire:click="openInitial"
                class="inline-flex items-center gap-1.5 rounded-full bg-white px-4 py-1.5 text-xs font-semibold text-brand-700 ring-1 ring-inset ring-brand-300 hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-brand-400 transition">
                <i class="fas fa-receipt text-[11px]"></i> Record Initial Fees
            </button>
            <button wire:click="openGenerate"
                class="inline-flex items-center gap-1.5 rounded-full bg-brand-600 px-4 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-400 transition">
                <i class="fas fa-plus text-[11px]"></i> Generate Monthly Bill
            </button>
        </div>
    </div>

    <div class="flex flex-wrap gap-3 mb-6">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search tenant..."
            class="form-input w-auto text-sm">
        <select wire:model.live="filterStatus" class="form-input w-auto text-sm">
            <option value="">All Status</option>
            <option value="unpaid">Unpaid</option>
            <option value="grace">Grace</option>
            <option value="overdue">Overdue</option>
            <option value="delinquent">Delinquent</option>
            <option value="eviction">Eviction</option>
            <option value="paid">Paid</option>
        </select>
    </div>

    <div class="card overflow-x-auto p-0">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-brand-900">
                <tr>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider cursor-pointer hover:text-white"
                        wire:click="sortBy('tenant_name')">
                        Tenant @if($sortBy === 'tenant_name') @if($sortDirection === 'asc') ↑ @else ↓ @endif @endif</th>
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
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $bill->room->room_number }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $bill->billing_period }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">₱{{ number_format($bill->total_amount, 2) }}
                        </td>
                        <td
                            class="px-4 py-3 text-sm {{ $bill->penalty_amount > 0 ? 'text-red-600 font-medium' : 'text-gray-400' }}">
                            {{ $bill->penalty_amount > 0 ? '₱' . number_format($bill->penalty_amount, 2) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $bill->due_date->format('M d') }}</td>
                        <td class="px-4 py-3"><span
                                class="badge {{ $bill->status_badge }}">{{ ucfirst($bill->status) }}{{ $bill->days_overdue > 0 && $bill->status !== 'paid' ? " ({$bill->days_overdue}d)" : '' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
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
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
                <h3 class="text-lg font-semibold mb-4">Generate Monthly Bill</h3>
                <form wire:submit="generateBill" class="space-y-4">
                    <div>
                        <label class="form-label">Select Contract *</label>
                        <select wire:model="genContractId" class="form-input">
                            <option value="">— Select —</option>
                            @foreach($activeContracts as $c)
                                <option value="{{ $c->id }}">{{ $c->tenant->full_name }} — Room {{ $c->room->room_number }}
                                    (₱{{ number_format($c->base_rent_rate, 2) }}/mo)</option>
                            @endforeach
                        </select>
                        @error('genContractId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Utility Breakdown</p>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="form-label">Electricity (₱)</label>
                            <input wire:model="genElectricity" type="number" step="0.01" min="0" class="form-input">
                            @error('genElectricity') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Water (₱)</label>
                            <input wire:model="genWater" type="number" step="0.01" min="0" class="form-input">
                            @error('genWater') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">WiFi (₱)</label>
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
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto p-6">
                <h3 class="text-lg font-semibold mb-1">Record Initial Payment</h3>
                <p class="text-xs text-gray-500 mb-4">Move-in amounts come from the signed contract (SS4). Amenities can be adjusted below if the tenant changed their request at move-in.</p>
                <form wire:submit="recordInitial" class="space-y-4">
                    <div>
                        <label class="form-label">Select Contract *</label>
                        <select wire:model.live="initContractId" class="form-input">
                            <option value="">— Select —</option>
                            @foreach($contractsAwaitingInitial as $opt)
                                <option value="{{ $opt->id }}">
                                    {{ $opt->tenant->full_name }} — Room {{ $opt->room->room_number }} (Contract #{{ $opt->id }})
                                </option>
                            @endforeach
                        </select>
                        @if($contractsAwaitingInitial->isEmpty())
                            <p class="text-xs text-amber-700 mt-1">No contracts awaiting initial payment.</p>
                        @endif
                    </div>

                    @if($c)
                        <div class="rounded-md bg-emerald-50 border border-emerald-200 p-3 space-y-2">
                            <p class="text-[11px] uppercase tracking-wide text-emerald-700 font-semibold">From contract — read only</p>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div><span class="text-gray-500">Security Deposit</span><div class="font-semibold">₱{{ number_format($c->deposit, 2) }}</div></div>
                                <div><span class="text-gray-500">First Month Rent</span><div class="font-semibold">₱{{ number_format($firstRent, 2) }}</div></div>
                                <div><span class="text-gray-500">Room Key Fee</span><div class="font-semibold">₱{{ number_format($c->room_key_fee, 2) }}</div></div>
                                <div><span class="text-gray-500">Amenities</span><div class="font-semibold">₱{{ number_format($amenitiesTotal, 2) }}</div></div>
                            </div>
                            <div class="border-t border-emerald-200 pt-2 flex items-center justify-between text-sm">
                                <span class="text-gray-600 font-medium">Total to collect</span>
                                <span class="font-bold text-emerald-700 text-base">₱{{ number_format($total, 2) }}</span>
                            </div>
                        </div>

                        {{-- Amenities — prefilled from contract, editable here --}}
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
                                    <input wire:model.live="initAmenities.{{ $i }}.fee" type="number" step="0.01" min="0" placeholder="Fee (₱)"
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
                            <select wire:model="initPaymentMethod" class="form-input">
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="e_wallet">E-Wallet</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Reference Number <span class="text-gray-400 text-xs">(optional, for transfer / e-wallet)</span></label>
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
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
                <h3 class="text-lg font-semibold mb-4">Confirm Payment</h3>
                <form wire:submit="confirmPayment" class="space-y-4">
                    <div><label class="form-label">Amount (₱)</label><input wire:model="payAmount" type="number" step="0.01"
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
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
                <h3 class="text-lg font-semibold mb-4">Override / Waive Penalty</h3>
                <form wire:submit="saveOverride" class="space-y-4">
                    <div><label class="form-label">New Penalty Amount (₱)</label><input wire:model="overrideAmount"
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