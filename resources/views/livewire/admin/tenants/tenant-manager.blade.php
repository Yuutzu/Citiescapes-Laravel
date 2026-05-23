<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <h1 class="text-2xl font-bold text-brand-900">Tenant Management</h1>
        <button wire:click="create" class="btn-primary">+ Create Tenant</button>
    </div>

    <div class="space-y-3 mb-6">
        <div class="relative max-w-md">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Search name, email, contact, address, status..."
                class="form-input w-full pl-9 text-sm">
        </div>
        @php
            $tBtn = fn($v) => 'inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition ' . ($filterStatus === (string)$v ? 'bg-brand-700 text-white hover:bg-brand-800' : 'bg-white text-brand-700 ring-1 ring-brand-300 hover:bg-brand-50');
        @endphp
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mr-1">Status:</span>
            <button wire:click="$set('filterStatus','')" class="{{ $tBtn('') }}">All</button>
            <button wire:click="$set('filterStatus','active')" class="{{ $tBtn('active') }}">Active</button>
            <button wire:click="$set('filterStatus','pending_activation')" class="{{ $tBtn('pending_activation') }}">Pending</button>
            <button wire:click="$set('filterStatus','archived')" class="{{ $tBtn('archived') }}">Archived</button>
        </div>
    </div>

    <div class="card overflow-x-auto p-0">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-brand-900">
                <tr>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Name</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Email</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Contact</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Address</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Emergency Contact</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Created</th>
                    <th class="px-4 py-3.5 text-right text-xs font-semibold text-brand-200 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($tenants as $t)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $t->full_name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $t->email }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $t->contact_number ?: 'â€”' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600 max-w-[200px] truncate" title="{{ $t->address }}">{{ $t->address ?: 'â€”' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $t->emergency_contact ?: 'â€”' }}</td>
                    <td class="px-4 py-3">
                        <span class="badge {{ match($t->status) { 'active' => 'bg-green-100 text-green-800', 'pending_activation' => 'bg-amber-100 text-amber-800', 'archived' => 'bg-gray-100 text-gray-600', 'locked' => 'bg-red-100 text-red-800', default => 'bg-gray-100 text-gray-600' } }}">
                            {{ str_replace('_', ' ', ucfirst($t->status)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $t->created_at->format('M d, Y') }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if($t->status !== 'archived')
                                <button wire:click="archiveTenant({{ $t->id }})" wire:confirm="Archive this tenant?"
                                    class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-300 hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-amber-400 transition">
                                    <i class="fas fa-box-archive text-[10px]"></i> Archive
                                </button>
                            @endif
                            <button wire:click="deleteTenant({{ $t->id }})" wire:confirm="PERMANENTLY delete this tenant? This cannot be undone."
                                class="inline-flex items-center gap-1 rounded-full bg-red-600 px-3 py-1 text-xs font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-400 transition">
                                <i class="fas fa-trash text-[10px]"></i> Delete
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $tenants->links() }}</div>
    </div>

    {{-- Create Modal --}}
    @if($showCreate)
    <div class="cs-modal">
        <div class="bg-white rounded-xl shadow-xl w-full max-h-[88vh] overflow-y-auto max-w-md mx-4 p-6 my-auto">
            <h3 class="text-lg font-semibold mb-4">Create Tenant Account</h3>
            <p class="text-sm text-gray-500 mb-4">A temporary password will be sent to the tenant's email.</p>
            <form wire:submit="store" class="space-y-4">
                <div>
                    <label class="form-label">Full Name *</label>
                    <input wire:model="full_name" class="form-input" autofocus>
                    @error('full_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Email *</label>
                    <input wire:model="email" type="email" class="form-input">
                    @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="$set('showCreate', false)" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Create & Send Password</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
