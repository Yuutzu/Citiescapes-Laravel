<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-brand-900">System Audit Log</h1>
        <button wire:click="exportCsv" type="button"
            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition">
            <i class="fas fa-file-csv"></i> Export CSV
        </button>
    </div>

    <div class="space-y-3 mb-6">
        <div class="relative max-w-md">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Search action, details, user, IP, subsystem..."
                class="form-input w-full pl-9 text-sm">
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mr-1">Action:</span>
            <select wire:model.live="filterAction" class="form-input w-auto text-sm">
                <option value="">All Actions</option>
                @foreach($actions as $a)<option value="{{ $a }}">{{ $a }}</option>@endforeach
            </select>
        </div>
        @php
            $subsystemLabels = [
                'SS1' => 'Room',
                'SS2' => 'Tenant',
                'SS3' => 'Billing',
                'SS4' => 'Contract',
                'SS5' => 'Archive',
                'SS6' => 'Settings',
                'SS7' => 'Communications',
            ];
        @endphp
        @php
            $ssPill = fn($v) => 'inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition ' . ($filterSubsystem === (string)$v ? 'bg-brand-700 text-white hover:bg-brand-800' : 'bg-white text-brand-700 ring-1 ring-brand-300 hover:bg-brand-50');
        @endphp
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mr-1">Subsystem:</span>
            <button wire:click="$set('filterSubsystem','')" class="{{ $ssPill('') }}">All</button>
            @foreach($subsystemLabels as $ss => $label)
                <button wire:click="$set('filterSubsystem','{{ $ss }}')" class="{{ $ssPill($ss) }}">{{ $label }}</button>
            @endforeach
        </div>
    </div>

    <div class="card overflow-x-auto p-0">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-brand-900">
                <tr>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Time</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">User</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Action</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Subsystem</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Details</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $log->created_at->format('M d H:i:s') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $log->user?->full_name ?? 'System' }}</td>
                    <td class="px-4 py-3"><span class="badge bg-brand-50 text-brand-700">{{ $log->action }}</span></td>
                    <td class="px-4 py-3 text-xs text-gray-600">{{ $subsystemLabels[$log->subsystem] ?? ($log->subsystem ?? '—') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600 max-w-sm truncate">{{ $log->details ?? '—' }}</td>
                    <td class="px-4 py-3 text-xs text-gray-400 font-mono">{{ $log->ip_address }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $logs->links() }}</div>
    </div>
</div>
