<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-brand-900">Announcements</h1>
            <p class="text-sm text-gray-500 mt-1">Send notices and announcements to tenants</p>
        </div>
        <button wire:click="openForm" class="btn-primary flex items-center gap-2">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 1 8.835-2.535m0 0A23.74 23.74 0 0 1 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m-1.395 5.43a24.015 24.015 0 0 1-11.4 0"/>
            </svg>
            New Announcement
        </button>
    </div>

    {{-- Compose Modal --}}
    @if($showForm)
        <div class="cs-modal">
            <div class="bg-white rounded-xl shadow-xl w-full max-h-[88vh] overflow-y-auto max-w-2xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">New Announcement</h2>
                    <button wire:click="closeForm" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    {{-- Title --}}
                    <div>
                        <label class="form-label">Title *</label>
                        <input wire:model="title" type="text" class="form-input" placeholder="e.g. Water Interruption Notice">
                        @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Recipient --}}
                    <div>
                        <label class="form-label">Send To *</label>
                        <select wire:model.live="recipientType" class="form-input">
                            <option value="all">All Active Tenants</option>
                            <option value="specific">Specific Tenant</option>
                        </select>
                    </div>

                    @if($recipientType === 'specific')
                        <div>
                            <label class="form-label">Select Tenant *</label>
                            <select wire:model="recipientId" class="form-input">
                                <option value="">â€” choose tenant â€”</option>
                                @foreach($tenants as $tenant)
                                    <option value="{{ $tenant->id }}">{{ $tenant->full_name }} ({{ $tenant->email }})</option>
                                @endforeach
                            </select>
                            @error('recipientId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    {{-- Message --}}
                    <div>
                        <label class="form-label">Message *</label>
                        <textarea wire:model="body" rows="5" class="form-input" placeholder="Write your announcement here..."></textarea>
                        @error('body') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Delivery summary (always sent via bell + email) --}}
                    <div class="flex items-start gap-3 p-3 rounded-lg bg-blue-50 border border-blue-200">
                        <svg class="h-5 w-5 text-blue-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                        <p class="text-sm text-blue-800 leading-snug">
                            Every announcement is delivered automatically via the in-app bell <em>and</em> email to the chosen recipient(s).
                        </p>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-xl">
                    <button wire:click="closeForm" class="btn-secondary">Cancel</button>
                    <button wire:click="send" wire:loading.attr="disabled" class="btn-primary flex items-center gap-2">
                        <span wire:loading.remove wire:target="send">Send Announcement</span>
                        <span wire:loading wire:target="send">Sending...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- View Detail Modal --}}
    @if($viewing)
        <div class="cs-modal">
            <div class="bg-white rounded-xl shadow-xl w-full max-h-[88vh] overflow-y-auto max-w-xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Announcement Details</h2>
                    <button wire:click="$set('viewingId', null)" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Title</p>
                        <p class="font-semibold text-gray-900 text-lg">{{ $viewing->title }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="badge {{ $viewing->recipient_type === 'all' ? 'bg-indigo-100 text-indigo-800' : 'bg-purple-100 text-purple-800' }}">
                            {{ $viewing->recipient_label }}
                        </span>
                        @if($viewing->email_sent)
                            <span class="badge bg-green-100 text-green-800">Email Sent</span>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Message</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $viewing->body }}</p>
                    </div>
                    <div class="pt-2 border-t border-gray-100 text-xs text-gray-400">
                        Sent by <span class="font-medium text-gray-600">{{ $viewing->sentBy?->full_name }}</span>
                        on {{ $viewing->created_at->format('M d, Y g:i A') }}
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-xl text-right">
                    <button wire:click="$set('viewingId', null)" class="btn-secondary">Close</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Search --}}
    <div class="mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" class="form-input w-full sm:w-80" placeholder="Search announcements...">
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden p-0">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-brand-900">
                <tr>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Title</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Recipient</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Sent</th>
                    <th class="px-6 py-3.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($announcements as $ann)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-900 text-sm">{{ $ann->title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5 line-clamp-1">{{ $ann->body }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="badge {{ $ann->recipient_type === 'all' ? 'bg-indigo-100 text-indigo-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ $ann->recipient_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($ann->email_sent)
                                <span class="badge bg-green-100 text-green-800">Sent</span>
                            @else
                                <span class="badge bg-gray-100 text-gray-500">No</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $ann->created_at->diffForHumans() }}</td>
                        <td class="px-6 py-4 text-right">
                            <button wire:click="$set('viewingId', {{ $ann->id }})" class="text-sm text-brand-700 hover:text-brand-900 font-medium">View</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">No announcements yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($announcements->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">{{ $announcements->links() }}</div>
        @endif
    </div>
</div>
