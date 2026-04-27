<div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">System Audit Log</h1>

    <div class="flex flex-wrap gap-3 mb-6">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search details or user..." class="form-input w-auto text-sm">
        <select wire:model.live="filterAction" class="form-input w-auto text-sm">
            <option value="">All Actions</option>
            @foreach($actions as $a)<option value="{{ $a }}">{{ $a }}</option>@endforeach
        </select>
        <select wire:model.live="filterSubsystem" class="form-input w-auto text-sm">
            <option value="">All Subsystems</option>
            @foreach(['SS1','SS2','SS3','SS4','SS5','SS6'] as $ss)<option value="{{ $ss }}">{{ $ss }}</option>@endforeach
        </select>
    </div>

    <div class="card overflow-x-auto p-0">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Time</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">User</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Action</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SS</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Details</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $log->created_at->format('M d H:i:s') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $log->user?->full_name ?? 'System' }}</td>
                    <td class="px-4 py-3"><span class="badge bg-brand-50 text-brand-700">{{ $log->action }}</span></td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $log->subsystem ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600 max-w-sm truncate">{{ $log->details ?? '—' }}</td>
                    <td class="px-4 py-3 text-xs text-gray-400 font-mono">{{ $log->ip_address }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $logs->links() }}</div>
    </div>
</div>
