<div>
    <h1 class="text-2xl font-bold text-brand-900 mb-6">Archive</h1>

    <div class="flex flex-wrap gap-3 mb-6">
        <select wire:model.live="filterType" class="form-input w-auto text-sm">
            <option value="">All Types</option>
            <option value="room">Room</option><option value="tenant_account">Tenant</option>
            <option value="payment">Payment</option><option value="contract">Contract</option>
        </select>
        <select wire:model.live="filterSubsystem" class="form-input w-auto text-sm">
            <option value="">All Sources</option>
            <option value="SS1">SS1 — Rooms</option><option value="SS2">SS2 — Tenants</option>
            <option value="SS3">SS3 — Billing</option><option value="SS4">SS4 — Contracts</option>
        </select>
        <input wire:model.live="dateFrom" type="date" class="form-input w-auto text-sm" placeholder="From">
        <input wire:model.live="dateTo" type="date" class="form-input w-auto text-sm" placeholder="To">
    </div>

    <div class="card overflow-x-auto p-0">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-brand-900">
                <tr>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">ID</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Type</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Source</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Reason</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Archived</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Scan</th>
                    <th class="px-4 py-3.5 text-right text-xs font-semibold text-brand-200 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($archives as $a)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-600">#{{ $a->original_record_id }}</td>
                    <td class="px-4 py-3"><span class="badge bg-gray-100 text-gray-700">{{ str_replace('_',' ',ucfirst($a->record_type)) }}</span></td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $a->source_subsystem }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate">{{ $a->archive_reason }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $a->created_at->format('M d, Y') }}</td>
                    <td class="px-4 py-3">
                        @if($a->scan_file_path)
                            <a href="{{ asset('storage/' . $a->scan_file_path) }}" target="_blank" class="text-xs text-brand-600 hover:underline">View</a>
                        @else — @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button wire:click="restore({{ $a->id }})" wire:confirm="Restore this record?"
                                class="inline-flex items-center gap-1 rounded-full bg-green-600 px-3 py-1 text-xs font-semibold text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-400 transition">
                                <i class="fas fa-rotate-left text-[10px]"></i> Restore
                            </button>
                            <button wire:click="permanentDelete({{ $a->id }})" wire:confirm="PERMANENTLY delete? Cannot be undone."
                                class="inline-flex items-center gap-1 rounded-full bg-red-600 px-3 py-1 text-xs font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-400 transition">
                                <i class="fas fa-trash text-[10px]"></i> Delete
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $archives->links() }}</div>
    </div>
</div>
