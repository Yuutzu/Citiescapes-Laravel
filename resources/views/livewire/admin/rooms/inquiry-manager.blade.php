<div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Inquiry Log</h1>

    <div class="flex gap-3 mb-6">
        <select wire:model.live="filterStatus" class="form-input w-auto text-sm">
            <option value="">All</option>
            <option value="pending">Pending</option><option value="responded">Responded</option><option value="closed">Closed</option>
        </select>
    </div>

    <div class="space-y-4">
        @foreach($inquiries as $inq)
        <div class="card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="font-semibold text-gray-900">{{ $inq->sender_name }}</p>
                    <p class="text-sm text-gray-500">{{ $inq->contact_number }} @if($inq->email) &bull; {{ $inq->email }} @endif</p>
                    <p class="text-xs text-gray-400 mt-0.5">Preferred: {{ ucfirst($inq->preferred_room_type) }} &bull; {{ $inq->created_at->format('M d, Y h:i A') }}</p>
                </div>
                <span class="badge {{ $inq->status === 'pending' ? 'bg-amber-100 text-amber-800' : ($inq->status === 'responded' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600') }}">{{ ucfirst($inq->status) }}</span>
            </div>
            <p class="mt-3 text-sm text-gray-700 bg-gray-50 rounded-lg p-3">{{ $inq->message }}</p>
            @if($inq->gm_notes)
                <p class="mt-2 text-sm text-brand-700 bg-brand-50 rounded-lg p-3"><span class="font-medium">GM Notes:</span> {{ $inq->gm_notes }}</p>
            @endif
            <div class="mt-3 flex gap-2">
                @if($inq->status === 'pending')
                    <button wire:click="respond({{ $inq->id }})" class="btn-primary text-xs">Respond</button>
                @endif
                @if($inq->status !== 'closed')
                    <button wire:click="close({{ $inq->id }})" class="btn-secondary text-xs">Close</button>
                @endif
            </div>

            @if($respondingId === $inq->id)
                <div class="mt-3 border-t border-gray-100 pt-3">
                    <label class="form-label">Your notes / response</label>
                    <textarea wire:model="gmNotes" rows="2" class="form-input text-sm"></textarea>
                    @error('gmNotes') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    <div class="flex gap-2 mt-2">
                        <button wire:click="saveResponse" class="btn-success text-xs">Save Response</button>
                        <button wire:click="$set('respondingId', null)" class="btn-secondary text-xs">Cancel</button>
                    </div>
                </div>
            @endif
        </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $inquiries->links() }}</div>
</div>
