<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Tenant Management</h1>
        <button wire:click="create" class="btn-primary">+ Create Tenant</button>
    </div>

    <div class="flex flex-wrap gap-3 mb-6">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search name or email..." class="form-input w-auto text-sm">
        <select wire:model.live="filterStatus" class="form-input w-auto text-sm">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="pending_activation">Pending</option>
            <option value="archived">Archived</option>
        </select>
    </div>

    <div class="card overflow-x-auto p-0">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Created</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($tenants as $t)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $t->full_name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $t->email }}</td>
                    <td class="px-4 py-3">
                        <span class="badge {{ match($t->status) { 'active' => 'bg-green-100 text-green-800', 'pending_activation' => 'bg-amber-100 text-amber-800', 'archived' => 'bg-gray-100 text-gray-600', 'locked' => 'bg-red-100 text-red-800', default => 'bg-gray-100 text-gray-600' } }}">
                            {{ str_replace('_', ' ', ucfirst($t->status)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $t->created_at->format('M d, Y') }}</td>
                    <td class="px-4 py-3 text-right space-x-1">
                        @if($t->status !== 'archived')
                            <button wire:click="archiveTenant({{ $t->id }})" wire:confirm="Archive this tenant?" class="text-xs text-gray-500 hover:text-red-600">Archive</button>
                        @endif
                        <button wire:click="deleteTenant({{ $t->id }})" wire:confirm="PERMANENTLY delete this tenant? This cannot be undone." class="text-xs text-red-500 hover:text-red-700">Delete</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $tenants->links() }}</div>
    </div>

    {{-- Create Modal --}}
    @if($showCreate)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
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
