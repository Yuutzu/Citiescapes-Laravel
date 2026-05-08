<div>
    <h1 class="text-2xl font-bold text-brand-900 mb-6">Inquiry Log</h1>

    @if(session('success'))
        <div class="mb-4 px-4 py-2 rounded-lg bg-green-50 text-green-700 text-sm border border-green-200">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-2 rounded-lg bg-red-50 text-red-700 text-sm border border-red-200">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex gap-3 mb-6">
        <select wire:model.live="filterStatus" class="form-input w-auto text-sm">
            <option value="">All</option>
            <option value="pending">Pending</option>
            <option value="responded">Responded</option>
            <option value="closed">Closed</option>
        </select>
    </div>

    <div class="space-y-4">
        @foreach($inquiries as $inq)
            <div class="card">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $inq->sender_name }}</p>
                        <p class="text-sm text-gray-500">{{ $inq->contact_number }} @if($inq->email) &bull;
                        {{ $inq->email }} @endif</p>
                        <p class="text-xs text-gray-400 mt-0.5">Preferred: {{ ucfirst($inq->preferred_room_type) }} &bull;
                            {{ $inq->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    <span
                        class="badge {{ $inq->status === 'pending' ? 'bg-amber-100 text-amber-800' : ($inq->status === 'responded' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600') }}">{{ ucfirst($inq->status) }}</span>
                </div>
                <p class="mt-3 text-sm text-gray-700 bg-gray-50 rounded-lg p-3">{{ $inq->message }}</p>

                @if($inq->responded_at)
                    <p class="mt-2 text-xs text-gray-500">
                        Responded by {{ $inq->respondedBy?->full_name ?? '—' }}
                        on {{ $inq->responded_at->format('M d, Y h:i A') }}
                    </p>
                @endif

                <div class="mt-3 flex flex-wrap gap-2">
                    @if($inq->email)
                        @php($alreadyReplied = (bool) $inq->responded_at)
                        <button type="button"
                                @if($alreadyReplied) disabled @else wire:click="compose({{ $inq->id }})" @endif
                                class="text-xs inline-flex items-center gap-1 {{ $alreadyReplied ? 'px-3 py-1.5 rounded-md bg-gray-200 text-gray-500 cursor-not-allowed' : 'btn-primary' }}">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                            {{ $alreadyReplied ? 'Email Sent' : 'Email Reply' }}
                        </button>
                    @endif
                    @if($inq->status === 'pending')
                        <button wire:click="markResponded({{ $inq->id }})" class="btn-success text-xs">
                            Mark Responded
                        </button>
                    @endif
                    @if($inq->status !== 'closed')
                        <button wire:click="close({{ $inq->id }})" class="btn-secondary text-xs">Close</button>
                    @endif
                </div>

                @if($composingId === $inq->id)
                    <div class="mt-4 border-t border-gray-100 pt-4 space-y-3">
                        <p class="text-xs text-gray-500">Sending to <span class="font-medium text-gray-700">{{ $inq->email }}</span></p>
                        <div>
                            <label class="form-label">Subject</label>
                            <input type="text" wire:model="emailSubject" class="form-input text-sm" />
                            @error('emailSubject') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Message</label>
                            <textarea wire:model="emailBody" rows="6" class="form-input text-sm"></textarea>
                            @error('emailBody') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="sendEmail" wire:loading.attr="disabled"
                                    class="btn-primary text-xs inline-flex items-center gap-1">
                                <span wire:loading.remove wire:target="sendEmail">Send Email</span>
                                <span wire:loading wire:target="sendEmail">Sending...</span>
                            </button>
                            <button wire:click="cancelCompose" class="btn-secondary text-xs">Cancel</button>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $inquiries->links() }}</div>
</div>
