<div>
    <h1 class="text-2xl font-bold text-brand-900 mb-6">My Contract</h1>

    @if($contract)
        <div class="space-y-6">
            {{-- Contract summary + scan viewer side-by-side --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Left: Summary --}}
                <div class="card">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-semibold text-gray-900">Contract Summary</h3>
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
                </div>

                {{-- Right: Scanned contract viewer (gated by GM approval) --}}
                <div class="card p-0 overflow-hidden flex flex-col">
                    <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 border-b border-gray-200 bg-paper-50">
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-semibold text-brand-900">Signed Contract</h3>
                            @if($contract->scan_view_status === 'approved' && $contract->scan_view_decided_at)
                                @php
                                    $window = \App\Livewire\Tenant\ContractView::SCAN_VIEW_WINDOW_DAYS;
                                    $expiresAt = $contract->scan_view_decided_at->copy()->addDays($window);
                                    $hoursLeft = now()->diffInHours($expiresAt, false);
                                    $isUrgent = $hoursLeft < 24;
                                @endphp
                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $isUrgent ? 'bg-amber-100 text-amber-800 ring-1 ring-amber-300' : 'bg-green-100 text-green-800 ring-1 ring-green-300' }}">
                                    <i class="fas {{ $isUrgent ? 'fa-clock' : 'fa-shield-check' }} text-[10px]"></i>
                                    Access expires {{ $expiresAt->diffForHumans() }}
                                </span>
                            @elseif($contract->scan_view_status === 'revoked')
                                <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 text-gray-700 ring-1 ring-gray-300 px-2.5 py-0.5 text-[11px] font-semibold">
                                    <i class="fas fa-lock text-[10px]"></i> Access expired
                                </span>
                            @endif
                        </div>
                        @if($contract->scan_file_path && $contract->scan_view_status === 'approved')
                            <a href="{{ asset('storage/' . $contract->scan_file_path) }}" target="_blank"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:text-brand-900">
                                <i class="fas fa-arrow-up-right-from-square text-[10px]"></i> Open in new tab
                            </a>
                        @endif
                    </div>
                    <div class="bg-gray-100" style="height: 520px; overflow-y: auto;">
                        @if(!$contract->scan_file_path)
                            <div class="flex items-center justify-center h-full text-sm text-gray-400 italic px-6 text-center">
                                Scanned contract not yet uploaded.
                            </div>
                        @elseif($contract->scan_view_status === 'approved')
                            @php
                                $tenantScanUrl = asset('storage/' . $contract->scan_file_path);
                                $tenantExt = strtolower(pathinfo($contract->scan_file_path, PATHINFO_EXTENSION));
                            @endphp
                            @if($tenantExt === 'pdf')
                                <iframe src="{{ $tenantScanUrl }}" class="w-full h-full bg-white" style="border:0;"></iframe>
                            @elseif(in_array($tenantExt, ['jpg','jpeg','png','webp','gif']))
                                <img src="{{ $tenantScanUrl }}" alt="Signed contract" class="w-full block">
                            @else
                                <div class="flex flex-col items-center justify-center h-full text-sm text-gray-600 gap-3">
                                    <i class="fas fa-file-word text-4xl text-brand-700"></i>
                                    <p>This file type cannot be previewed in the browser.</p>
                                    <a href="{{ $tenantScanUrl }}" target="_blank" class="btn-primary text-xs">
                                        <i class="fas fa-download mr-1"></i> Download {{ strtoupper($tenantExt) }}
                                    </a>
                                </div>
                            @endif
                        @elseif($contract->scan_view_status === 'pending')
                            <div class="flex flex-col items-center justify-center h-full text-center px-6 gap-3">
                                <div class="h-16 w-16 rounded-full bg-amber-100 ring-4 ring-amber-200 flex items-center justify-center">
                                    <i class="fas fa-hourglass-half text-3xl text-amber-600 cs-anim-pulse-soft"></i>
                                </div>
                                <p class="text-sm font-semibold text-brand-900">Request pending</p>
                                <p class="text-xs text-gray-600 max-w-xs">
                                    Submitted on {{ $contract->scan_view_requested_at?->format('M d, Y h:i A') }}.
                                    Awaiting General Manager approval.
                                </p>
                            </div>
                        @elseif($contract->scan_view_status === 'denied')
                            <div class="flex flex-col items-center justify-center h-full text-center px-6 gap-3">
                                <div class="h-16 w-16 rounded-full bg-red-100 ring-4 ring-red-200 flex items-center justify-center">
                                    <i class="fas fa-ban text-3xl text-red-600"></i>
                                </div>
                                <p class="text-sm font-semibold text-brand-900">Request denied</p>
                                @if($contract->scan_view_decision_note)
                                    <p class="text-xs text-gray-600 italic max-w-xs">
                                        "{{ $contract->scan_view_decision_note }}"
                                    </p>
                                @endif
                                <button wire:click="requestScanAccess" wire:confirm="Submit a new request to view the signed contract?"
                                    class="inline-flex items-center gap-2 rounded-lg bg-white text-brand-700 ring-1 ring-brand-300 hover:bg-brand-50 text-xs font-semibold px-3 py-1.5 mt-2 transition">
                                    <i class="fas fa-rotate-right text-[10px]"></i> Request again
                                </button>
                            </div>
                        @elseif($contract->scan_view_status === 'revoked')
                            <div class="flex flex-col items-center justify-center h-full text-center px-6 gap-3">
                                <div class="h-16 w-16 rounded-full bg-marigold-100 ring-4 ring-marigold-200 flex items-center justify-center">
                                    <i class="fas fa-clock-rotate-left text-3xl text-marigold-700"></i>
                                </div>
                                <p class="text-sm font-semibold text-brand-900">Access window expired</p>
                                <p class="text-xs text-gray-600 max-w-xs">
                                    Your previous approval has timed out (7-day window). For your privacy, the system
                                    automatically locks the scan again. Request access if you still need to view it.
                                </p>
                                <button wire:click="requestScanAccess" wire:confirm="Submit a new request to view the signed contract?"
                                    class="inline-flex items-center gap-2 rounded-lg bg-brand-700 hover:bg-brand-800 text-white text-xs font-semibold px-3 py-1.5 mt-2 shadow-sm transition">
                                    <i class="fas fa-paper-plane text-[10px]"></i> Request again
                                </button>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center h-full text-center px-6 gap-3">
                                <div class="h-16 w-16 rounded-full bg-brand-100 ring-4 ring-brand-200 flex items-center justify-center">
                                    <i class="fas fa-shield-halved text-3xl text-brand-700"></i>
                                </div>
                                <p class="text-sm font-semibold text-brand-900">Approval required</p>
                                <p class="text-xs text-gray-600 max-w-xs">
                                    For your security, viewing the signed contract requires General Manager approval.
                                    Once approved, you'll have 7 days of access before the link locks again.
                                </p>
                                <button wire:click="requestScanAccess" wire:confirm="Send a request to view your signed contract?"
                                    class="inline-flex items-center gap-2 rounded-lg bg-brand-700 hover:bg-brand-800 text-white text-xs font-semibold px-3 py-1.5 mt-2 shadow-sm transition">
                                    <i class="fas fa-paper-plane text-[10px]"></i> Request access
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Timer (active contracts) --}}
            @if($contract->status === 'active')
                <div class="card">
                    <h3 class="text-base font-semibold text-gray-900 mb-3">Lease Timer</h3>
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
