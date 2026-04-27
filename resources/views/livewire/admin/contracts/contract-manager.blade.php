<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Contract Management</h1>
        <button wire:click="create" class="btn-primary">+ Create Draft</button>
    </div>

    <div class="flex flex-wrap gap-3 mb-6">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search tenant..." class="form-input w-auto text-sm">
        <select wire:model.live="filterStatus" class="form-input w-auto text-sm">
            <option value="">All Status</option>
            <option value="draft">Draft</option><option value="active">Active</option>
            <option value="expired">Expired</option><option value="terminated">Terminated</option>
        </select>
    </div>

    <div class="card overflow-x-auto p-0">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tenant</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Room</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Rate</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Period</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Timer</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Scan</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($contracts as $c)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $c->tenant->full_name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $c->room->room_number }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">₱{{ number_format($c->base_rent_rate, 2) }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $c->start_date->format('M d, Y') }} — {{ $c->end_date->format('M d, Y') }}</td>
                    <td class="px-4 py-3">
                        @if($c->status === 'active')
                            <span class="badge {{ $c->timer_badge_css }}">{{ $c->days_remaining }}d</span>
                            <div class="w-16 bg-gray-200 rounded-full h-1.5 mt-1"><div class="bg-brand-600 h-1.5 rounded-full" style="width: {{ $c->progress_percent }}%"></div></div>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3"><span class="badge {{ match($c->status){ 'draft'=>'bg-gray-100 text-gray-600','active'=>'bg-green-100 text-green-800','expired'=>'bg-gray-100 text-gray-600','terminated'=>'bg-red-100 text-red-800',default=>'bg-gray-100 text-gray-600' } }}">{{ ucfirst($c->status) }}</span></td>
                    <td class="px-4 py-3">
                        @if($c->scan_file_path)
                            <a href="{{ asset('storage/' . $c->scan_file_path) }}" target="_blank" class="text-xs text-brand-600 hover:underline">View</a>
                        @else <span class="text-xs text-gray-400">—</span> @endif
                    </td>
                    <td class="px-4 py-3 text-right space-x-1">
                        <button wire:click="edit({{ $c->id }})" class="text-xs text-brand-600 hover:text-brand-800">Edit</button>
                        @if($c->status === 'active')
                            <button wire:click="renew({{ $c->id }})" class="text-xs text-green-600 hover:text-green-800">Renew</button>
                            <button wire:click="openTerminate({{ $c->id }})" class="text-xs text-red-500 hover:text-red-700">Terminate</button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $contracts->links() }}</div>
    </div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto p-6">
            <h3 class="text-lg font-semibold mb-4">{{ $editing ? 'Edit Contract' : 'Create Contract Draft' }}</h3>
            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Tenant *</label>
                        <select wire:model="tenant_id" class="form-input">
                            <option value="">— Select —</option>
                            @foreach($tenants as $t)<option value="{{ $t->id }}">{{ $t->full_name }}</option>@endforeach
                        </select>
                        @error('tenant_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Room *</label>
                        <select wire:model="room_id" class="form-input">
                            <option value="">— Select —</option>
                            @foreach($rooms as $r)<option value="{{ $r->id }}">{{ $r->room_number }} ({{ ucfirst($r->room_type) }})</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div><label class="form-label">Rent (₱/mo) *</label><input wire:model="base_rent_rate" type="number" step="0.01" class="form-input"></div>
                    <div><label class="form-label">Deposit (₱)</label><input wire:model="deposit" type="number" step="0.01" class="form-input"></div>
                    <div><label class="form-label">Key Fee (₱)</label><input wire:model="room_key_fee" type="number" step="0.01" class="form-input"></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="form-label">Start Date *</label><input wire:model="start_date" type="date" class="form-input"></div>
                    <div><label class="form-label">End Date *</label><input wire:model="end_date" type="date" class="form-input"></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="form-label">Penalty (₱/day)</label><input wire:model="penalty_rate" type="number" step="0.01" class="form-input"></div>
                    <div><label class="form-label">Grace Days</label><input wire:model="penalty_grace_days" type="number" min="0" class="form-input"></div>
                </div>
                <div><label class="form-label">House Rules</label><textarea wire:model="house_rules" rows="3" class="form-input"></textarea></div>
                <div><label class="form-label">Upload Signed Contract (scan)</label><input wire:model="scanFile" type="file" accept=".pdf,.jpg,.jpeg,.png" class="form-input text-sm"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="$set('showModal', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">{{ $editing ? 'Update' : 'Save Draft' }}</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Terminate Modal --}}
    @if($showTerminate)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-semibold mb-4 text-red-700">Terminate Contract</h3>
            <form wire:submit="terminate" class="space-y-4">
                <div><label class="form-label">Reason *</label><textarea wire:model="terminateReason" rows="3" class="form-input"></textarea>@error('terminateReason')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="$set('showTerminate', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-danger">Terminate</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
