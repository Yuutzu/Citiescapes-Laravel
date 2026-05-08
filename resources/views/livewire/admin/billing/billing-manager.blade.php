<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-brand-900">Billing Management</h1>
        <div class="flex gap-2">
            <button wire:click="openInitial" class="btn-secondary text-sm">Record Initial Fees</button>
            <button wire:click="openGenerate" class="btn-primary text-sm">Generate Monthly Bill</button>
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
                        <td class="px-4 py-3 text-right space-x-1">
                            @if($bill->status !== 'paid' && $bill->status !== 'archived')
                                <button wire:click="openPayment({{ $bill->id }})"
                                    class="text-xs text-green-600 hover:text-green-800 font-medium">Confirm Pay</button>
                                @if($bill->penalty_amount > 0)
                                    <button wire:click="openOverride({{ $bill->id }})"
                                        class="text-xs text-amber-600 hover:text-amber-800 font-medium">Override</button>
                                @endif
                            @endif
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

                    <div class="grid grid-cols-2 gap-3">
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
                        <div>
                            <label class="form-label">Extras (₱)</label>
                            <input wire:model="genExtras" type="number" step="0.01" min="0" class="form-input">
                            @error('genExtras') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Extras Note <span class="text-gray-400 font-normal">(optional — e.g. extra
                                mattress fee)</span></label>
                        <input wire:model="genExtrasNote" type="text" class="form-input"
                            placeholder="Describe what the extras charge is for">
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
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
                <h3 class="text-lg font-semibold mb-4">Record Initial Fees</h3>
                <form wire:submit="recordInitial" class="space-y-4">
                    <div>
                        <label class="form-label">Select Contract *</label>
                        <select wire:model="initContractId" class="form-input">
                            <option value="">— Select —</option>
                            @foreach($activeContracts as $c)
                                <option value="{{ $c->id }}">{{ $c->tenant->full_name }} — Room {{ $c->room->room_number }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div><label class="form-label">Security Deposit (₱)</label><input wire:model="initDeposit" type="number"
                            step="0.01" class="form-input"></div>
                    <div><label class="form-label">First Month Rent (₱)</label><input wire:model="initFirstMonth"
                            type="number" step="0.01" class="form-input"></div>
                    <div><label class="form-label">Room Key Fee (₱)</label><input wire:model="initKeyFee" type="number"
                            step="0.01" class="form-input"></div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showInitial', false)" class="btn-secondary">Cancel</button>
                        <button type="submit" class="btn-primary">Record</button>
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