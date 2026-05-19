<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-brand-900">Tenant Requests & Complaints</h1>
            <p class="text-sm text-gray-500 mt-1">Manage and respond to tenant-submitted issues</p>
        </div>
    </div>

    {{-- View / Respond Modal --}}
    @if($viewing)
        <div class="cs-modal">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 sticky top-0 bg-white">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ $viewing->subject }}</h2>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="badge {{ $viewing->type_badge }}">{{ ucfirst($viewing->type) }}</span>
                            <span
                                class="badge {{ $viewing->status_badge }}">{{ str_replace('_', ' ', ucfirst($viewing->status)) }}</span>
                        </div>
                    </div>
                    <button wire:click="closeView" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-5">
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                        <div class="h-9 w-9 rounded-full bg-brand-100 flex items-center justify-center">
                            <span
                                class="text-brand-700 font-semibold text-sm">{{ substr($viewing->tenant?->full_name ?? '?', 0, 1) }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $viewing->tenant?->full_name }}</p>
                            <p class="text-xs text-gray-500">{{ $viewing->tenant?->email }} &bull; Submitted
                                {{ $viewing->created_at->diffForHumans() }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Message</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $viewing->body }}</p>
                    </div>

                    <hr class="border-gray-200">

                    @if($viewing->status === 'resolved')
                        {{-- Resolved → locked, read-only view --}}
                        <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <p class="text-sm font-semibold text-emerald-800">This request is Resolved &mdash; locked from further changes.</p>
                            </div>
                            <div>
                                <p class="text-xs text-emerald-700 font-semibold uppercase tracking-wide mb-1">Final Response</p>
                                <p class="text-sm text-emerald-900 whitespace-pre-line leading-relaxed">{{ $viewing->admin_response ?: '— No written response —' }}</p>
                            </div>
                            @if($viewing->responded_at)
                                <p class="text-xs text-emerald-700 mt-3">Responded by <span class="font-medium">{{ $viewing->respondedBy?->full_name }}</span> on {{ $viewing->responded_at->format('M d, Y g:i A') }}</p>
                            @endif
                        </div>
                    @else
                        <div class="space-y-3">
                            <div>
                                <label class="form-label">Update Status</label>
                                <select wire:model="newStatus" class="form-input">
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="resolved">Resolved</option>
                                </select>
                                <p class="text-[11px] text-amber-700 mt-1">Setting to <strong>Resolved</strong> and saving will permanently lock this request from further response.</p>
                            </div>
                            <div>
                                <label class="form-label">Response / Notes</label>
                                <textarea wire:model="adminReply" rows="4" class="form-input"
                                    placeholder="Write a response to the tenant..."></textarea>
                                @error('adminReply') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            @if($viewing->responded_at)
                                <p class="text-xs text-gray-400">Last responded by <span
                                        class="font-medium text-gray-600">{{ $viewing->respondedBy?->full_name }}</span> on
                                    {{ $viewing->responded_at->format('M d, Y g:i A') }}</p>
                            @endif
                        </div>
                    @endif
                </div>
                <div
                    class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-xl sticky bottom-0">
                    <button wire:click="closeView" class="btn-secondary">{{ $viewing->status === 'resolved' ? 'Close' : 'Cancel' }}</button>
                    @if($viewing->status !== 'resolved')
                        <button wire:click="respond" wire:loading.attr="disabled" class="btn-primary">
                            <span wire:loading.remove wire:target="respond">Save Response</span>
                            <span wire:loading wire:target="respond">Saving...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Filters --}}
    <div class="space-y-3 mb-4">
        <div class="relative max-w-md">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Search tenant, subject, message, type, status, response..."
                class="form-input w-full pl-9 text-sm">
        </div>
        @php
            $rBtn = fn($v, $field) => 'inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition ' . (($field === 'type' ? $filterType : $filterStatus) === (string)$v ? 'bg-brand-700 text-white hover:bg-brand-800' : 'bg-white text-brand-700 ring-1 ring-brand-300 hover:bg-brand-50');
        @endphp
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mr-1">Type:</span>
            <button wire:click="$set('filterType','')" class="{{ $rBtn('', 'type') }}">All</button>
            <button wire:click="$set('filterType','request')" class="{{ $rBtn('request', 'type') }}">Request</button>
            <button wire:click="$set('filterType','complaint')" class="{{ $rBtn('complaint', 'type') }}">Complaint</button>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mr-1">Status:</span>
            <button wire:click="$set('filterStatus','')" class="{{ $rBtn('', 'status') }}">All</button>
            <button wire:click="$set('filterStatus','pending')" class="{{ $rBtn('pending', 'status') }}">Pending</button>
            <button wire:click="$set('filterStatus','in_progress')" class="{{ $rBtn('in_progress', 'status') }}">In Progress</button>
            <button wire:click="$set('filterStatus','resolved')" class="{{ $rBtn('resolved', 'status') }}">Resolved</button>
        </div>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden p-0">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tenant
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Subject
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Type
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Submitted</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($requests as $req)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $req->tenant?->full_name }}</td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-900">{{ $req->subject }}</p>
                            <p class="text-xs text-gray-400 mt-0.5 line-clamp-1">{{ $req->body }}</p>
                        </td>
                        <td class="px-6 py-4"><span class="badge {{ $req->type_badge }}">{{ ucfirst($req->type) }}</span>
                        </td>
                        <td class="px-6 py-4"><span
                                class="badge {{ $req->status_badge }}">{{ str_replace('_', ' ', ucfirst($req->status)) }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $req->created_at->diffForHumans() }}</td>
                        <td class="px-6 py-4 text-right">
                            <button wire:click="view({{ $req->id }})"
                                class="inline-flex items-center gap-1 rounded-full bg-brand-600 px-3 py-1 text-xs font-semibold text-white shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-400 transition">
                                <i class="fas fa-reply text-[10px]"></i> Respond
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">No requests or complaints submitted
                            yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($requests->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">{{ $requests->links() }}</div>
        @endif
    </div>
</div>