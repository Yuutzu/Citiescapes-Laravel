<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-brand-900">My Requests & Complaints</h1>
            <p class="text-sm text-gray-500 mt-1">Submit issues or requests to the management</p>
        </div>
        <button wire:click="openForm" class="btn-primary flex items-center gap-2">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            New Submission
        </button>
    </div>

    {{-- Submit Form Modal --}}
    @if($showForm)
        <div class="cs-modal">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Submit Request / Complaint</h2>
                    <button wire:click="closeForm" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="form-label">Type *</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" wire:click="$set('type', 'request')"
                                class="flex items-center gap-2 p-3 rounded-lg border-2 text-left transition-colors
                                    {{ $type === 'request' ? 'border-brand-600 bg-brand-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <svg class="h-5 w-5 {{ $type === 'request' ? 'text-brand-600' : 'text-gray-400' }}"
                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l5.654-4.654m5.598-2.22a9 9 0 0 0-7.43-7.43" />
                                </svg>
                                <div>
                                    <p
                                        class="font-medium text-sm {{ $type === 'request' ? 'text-brand-700' : 'text-gray-700' }}">
                                        Request</p>
                                    <p class="text-xs text-gray-500">Maintenance, repairs, etc.</p>
                                </div>
                            </button>
                            <button type="button" wire:click="$set('type', 'complaint')"
                                class="flex items-center gap-2 p-3 rounded-lg border-2 text-left transition-colors
                                    {{ $type === 'complaint' ? 'border-red-500 bg-red-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <svg class="h-5 w-5 {{ $type === 'complaint' ? 'text-red-500' : 'text-gray-400' }}"
                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                                <div>
                                    <p
                                        class="font-medium text-sm {{ $type === 'complaint' ? 'text-red-600' : 'text-gray-700' }}">
                                        Complaint</p>
                                    <p class="text-xs text-gray-500">Noise, issues with neighbors, etc.</p>
                                </div>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Subject *</label>
                        <input wire:model="subject" type="text" class="form-input"
                            placeholder="Brief description of the issue">
                        @error('subject') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Details *</label>
                        <textarea wire:model="body" rows="5" class="form-input"
                            placeholder="Describe the problem or request in detail..."></textarea>
                        @error('body') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-xl">
                    <button wire:click="closeForm" class="btn-secondary">Cancel</button>
                    <button wire:click="submit" wire:loading.attr="disabled" class="btn-primary">
                        <span wire:loading.remove wire:target="submit">Submit</span>
                        <span wire:loading wire:target="submit">Submitting...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Submitted Confirmation Modal --}}
    @if($showSubmittedModal)
        <div class="cs-modal" wire:click.self="closeSubmittedModal">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden">
                <div class="px-6 py-6 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-green-100 mb-4">
                        <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">
                        {{ ucfirst($submittedType ?: 'Request') }} Submitted
                    </h3>
                    <p class="text-sm text-gray-500">
                        Your {{ $submittedType ?: 'submission' }} has been sent to the management.
                        You'll be notified as soon as they respond.
                    </p>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end">
                    <button wire:click="closeSubmittedModal" class="btn-primary">OK</button>
                </div>
            </div>
        </div>
    @endif

    {{-- View Detail Modal --}}
    @if($viewing)
        <div class="cs-modal">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 sticky top-0 bg-white">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ $viewing->subject }}</h2>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="badge {{ $viewing->type_badge }}">{{ ucfirst($viewing->type) }}</span>
                            <span
                                class="badge {{ $viewing->status_badge }}">{{ str_replace('_', ' ', ucfirst($viewing->status)) }}</span>
                        </div>
                    </div>
                    <button wire:click="$set('viewingId', null)" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Your Message</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $viewing->body }}</p>
                    </div>
                    @if($viewing->admin_response)
                        <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-xs text-green-600 font-semibold uppercase tracking-wide mb-1">Management Response</p>
                            <p class="text-sm text-green-900 whitespace-pre-line leading-relaxed">{{ $viewing->admin_response }}
                            </p>
                            @if($viewing->responded_at)
                                <p class="text-xs text-green-600 mt-2">Replied by {{ $viewing->respondedBy?->full_name }} on
                                    {{ $viewing->responded_at->format('M d, Y g:i A') }}</p>
                            @endif
                        </div>
                    @else
                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
                            <p class="text-sm text-amber-700">No response yet. The management will get back to you soon.</p>
                        </div>
                    @endif
                    <p class="text-xs text-gray-400">Submitted {{ $viewing->created_at->diffForHumans() }}</p>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-xl text-right">
                    <button wire:click="$set('viewingId', null)" class="btn-secondary">Close</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Info panel --}}
    <div class="mb-6 p-4 rounded-xl bg-blue-50 border border-blue-200 flex items-start gap-3">
        <svg class="h-5 w-5 text-blue-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
        </svg>
        <p class="text-sm text-blue-800">Use this page to report any issues, maintenance needs, or complaints about your
            room or surroundings. Management will review and respond as soon as possible.</p>
    </div>

    {{-- Submissions list --}}
    <div class="space-y-3">
        @forelse($requests as $req)
            <div class="card hover:shadow-md transition-shadow cursor-pointer"
                wire:click="$set('viewingId', {{ $req->id }})">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <span class="badge {{ $req->type_badge }}">{{ ucfirst($req->type) }}</span>
                            <span
                                class="badge {{ $req->status_badge }}">{{ str_replace('_', ' ', ucfirst($req->status)) }}</span>
                            @if($req->admin_response)
                                <span class="badge bg-green-100 text-green-700">Response received</span>
                            @endif
                        </div>
                        <h3 class="font-semibold text-gray-900">{{ $req->subject }}</h3>
                        <p class="text-sm text-gray-500 mt-0.5 line-clamp-2">{{ $req->body }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-xs text-gray-400">{{ $req->created_at->diffForHumans() }}</p>
                        <svg class="h-4 w-4 text-gray-400 ml-auto mt-2" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </div>
                </div>
            </div>
        @empty
            <div class="card text-center py-12 text-gray-400">
                <svg class="h-10 w-10 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                </svg>
                <p class="font-medium">No submissions yet</p>
                <p class="text-sm mt-1">Click "New Submission" to report an issue or make a request.</p>
            </div>
        @endforelse
    </div>

    @if($requests->hasPages())
        <div class="mt-4">{{ $requests->links() }}</div>
    @endif
</div>