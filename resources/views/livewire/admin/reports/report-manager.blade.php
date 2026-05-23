<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-brand-900">Archive</h1>
        <div class="flex items-center gap-2">
            <button wire:click="exportCsv" type="button"
                class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition">
                <i class="fas fa-file-csv"></i> Export CSV
            </button>
        </div>
    </div>

    <div class="space-y-3 mb-6">
        @php
            $typeBtn = fn($v) => 'inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition ' . ($filterType === (string)$v ? 'bg-brand-700 text-white hover:bg-brand-800' : 'bg-white text-brand-700 ring-1 ring-brand-300 hover:bg-brand-50');
            $ssBtn   = fn($v) => 'inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition ' . ($filterSubsystem === (string)$v ? 'bg-brand-700 text-white hover:bg-brand-800' : 'bg-white text-brand-700 ring-1 ring-brand-300 hover:bg-brand-50');
        @endphp
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mr-1">Type:</span>
            <button wire:click="$set('filterType','')" class="{{ $typeBtn('') }}">All</button>
            <button wire:click="$set('filterType','room')" class="{{ $typeBtn('room') }}">Room</button>
            <button wire:click="$set('filterType','tenant_account')" class="{{ $typeBtn('tenant_account') }}">Tenant</button>
            <button wire:click="$set('filterType','payment')" class="{{ $typeBtn('payment') }}">Payment</button>
            <button wire:click="$set('filterType','contract')" class="{{ $typeBtn('contract') }}">Contract</button>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mr-1">Source:</span>
            <button wire:click="$set('filterSubsystem','')" class="{{ $ssBtn('') }}">All</button>
            <button wire:click="$set('filterSubsystem','SS1')" class="{{ $ssBtn('SS1') }}">Rooms</button>
            <button wire:click="$set('filterSubsystem','SS2')" class="{{ $ssBtn('SS2') }}">Tenants</button>
            <button wire:click="$set('filterSubsystem','SS3')" class="{{ $ssBtn('SS3') }}">Billing</button>
            <button wire:click="$set('filterSubsystem','SS4')" class="{{ $ssBtn('SS4') }}">Contracts</button>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mr-1">Date range:</span>
            <input wire:model.live="dateFrom" type="date" class="form-input w-auto text-sm" placeholder="From">
            <span class="text-xs text-gray-400">to</span>
            <input wire:model.live="dateTo" type="date" class="form-input w-auto text-sm" placeholder="To">
        </div>
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
                            @if($a->record_type === 'contract')
                                <button wire:click="exportContractPdf({{ $a->id }})"
                                    class="inline-flex items-center gap-1 rounded-full bg-rose-600 px-3 py-1 text-xs font-semibold text-white shadow-sm hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-400 transition">
                                    <i class="fas fa-file-pdf text-[10px]"></i> PDF
                                </button>
                            @endif
                            @if($a->record_type !== 'contract')
                                <button wire:click="restore({{ $a->id }})" wire:confirm="Restore this record?"
                                    class="inline-flex items-center gap-1 rounded-full bg-green-600 px-3 py-1 text-xs font-semibold text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-400 transition">
                                    <i class="fas fa-rotate-left text-[10px]"></i> Restore
                                </button>
                            @endif
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
