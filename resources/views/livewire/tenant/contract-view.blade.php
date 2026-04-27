<div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">My Contract</h1>

    @if($contract)
        <div class="max-w-2xl space-y-6">
            {{-- Contract summary --}}
            <div class="card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900">Contract Summary</h3>
                    <span class="badge {{ match($contract->status){ 'draft'=>'bg-gray-100 text-gray-600','active'=>'bg-green-100 text-green-800', default=>'bg-gray-100 text-gray-600' } }}">{{ ucfirst($contract->status) }}</span>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500">Room:</span> <span class="font-medium">{{ $contract->room->room_number }} ({{ ucfirst($contract->room->room_type) }})</span></div>
                    <div><span class="text-gray-500">Floor:</span> <span class="font-medium">{{ $contract->room->floor_level }}</span></div>
                    <div><span class="text-gray-500">Monthly Rent:</span> <span class="font-medium">₱{{ number_format($contract->base_rent_rate, 2) }}</span></div>
                    <div><span class="text-gray-500">Deposit:</span> <span class="font-medium">₱{{ number_format($contract->deposit, 2) }}</span></div>
                    <div><span class="text-gray-500">Key Fee:</span> <span class="font-medium">₱{{ number_format($contract->room_key_fee, 2) }}</span></div>
                    <div><span class="text-gray-500">Penalty Rate:</span> <span class="font-medium text-red-600">₱{{ number_format($contract->penalty_rate, 2) }}/day</span></div>
                    <div><span class="text-gray-500">Grace Period:</span> <span class="font-medium">{{ $contract->penalty_grace_days }} days</span></div>
                    <div><span class="text-gray-500">Period:</span> <span class="font-medium">{{ $contract->start_date->format('M d, Y') }} — {{ $contract->end_date->format('M d, Y') }}</span></div>
                </div>

                @if($contract->house_rules)
                    <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">House Rules</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $contract->house_rules }}</p>
                    </div>
                @endif

                @if($contract->scan_file_path)
                    <div class="mt-4">
                        <a href="{{ asset('storage/' . $contract->scan_file_path) }}" target="_blank" class="btn-secondary text-sm">View Signed Contract (Scan)</a>
                    </div>
                @endif
            </div>

            {{-- Timer (active contracts) --}}
            @if($contract->status === 'active')
                <div class="card">
                    <h3 class="font-semibold text-gray-900 mb-3">Lease Timer</h3>
                    <div class="flex items-center gap-4 mb-2">
                        <span class="badge {{ $contract->timer_badge_css }} text-lg px-4 py-1">{{ $contract->days_remaining }} days remaining</span>
                        <span class="text-sm text-gray-500">Ends {{ $contract->end_date->format('M d, Y') }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="h-3 rounded-full {{ match($contract->timer_badge) { 'green'=>'bg-green-500','amber'=>'bg-amber-500','red'=>'bg-red-500',default=>'bg-gray-400' } }}" style="width: {{ $contract->progress_percent }}%"></div>
                    </div>
                </div>
            @endif

            {{-- Acknowledgment steps (draft contracts) --}}
            @if($contract->status === 'draft')
                <div class="card border-2 border-amber-200 bg-amber-50/50">
                    <h3 class="font-semibold text-gray-900 mb-4">Contract Acknowledgment</h3>

                    {{-- Step 1 --}}
                    <div class="mb-4">
                        @if($contract->step1_acknowledged_at)
                            <div class="flex items-center gap-2 text-green-700">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                                <span class="text-sm font-medium">Step 1 completed — {{ $contract->step1_acknowledged_at->format('M d, Y h:i A') }}</span>
                            </div>
                        @else
                            <p class="text-sm text-gray-700 mb-2"><strong>Step 1:</strong> Please confirm you have read and understood all terms above.</p>
                            <button wire:click="acknowledgeStep1" wire:confirm="I confirm I have read and understood this contract." class="btn-primary text-sm">I have read and understood this contract</button>
                        @endif
                    </div>

                    {{-- Step 2 --}}
                    <div>
                        @if($contract->step2_acknowledged_at)
                            <div class="flex items-center gap-2 text-green-700">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                                <span class="text-sm font-medium">Step 2 completed — {{ $contract->step2_acknowledged_at->format('M d, Y h:i A') }}</span>
                            </div>
                        @elseif($contract->step1_acknowledged_at)
                            <p class="text-sm text-gray-700 mb-2"><strong>Step 2:</strong> Specifically accept the penalty clause (₱{{ number_format($contract->penalty_rate, 2) }}/day after {{ $contract->penalty_grace_days }}-day grace period).</p>
                            <button wire:click="acknowledgeStep2" wire:confirm="I specifically acknowledge and accept the penalty clause." class="btn-danger text-sm">I accept the penalty clause</button>
                        @else
                            <p class="text-sm text-gray-400">Complete Step 1 first to unlock Step 2.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="card">
            <p class="text-sm text-gray-400">No active or pending contract found. Please contact the General Manager.</p>
        </div>
    @endif
</div>
