<div>
    <h1 class="text-2xl font-bold text-brand-900 mb-6">Welcome, {{ $user->full_name }}</h1>

    {{-- Pending-draft prompt — surfaces a contract the GM created so the tenant
         can complete acknowledgement Step 1 / Step 2 from the dashboard
         without first navigating to "My Contract" in the sidebar. --}}
    @if($draftContract)
        <div class="mb-6 rounded-xl border-2 border-marigold-400 bg-marigold-50/60 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex items-center gap-3 flex-1">
                <div class="h-10 w-10 rounded-full bg-marigold-400 text-brand-950 flex items-center justify-center shrink-0">
                    <i class="fas fa-file-signature"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-brand-900">Contract awaiting your acknowledgement</p>
                    <p class="text-xs text-brand-700 mt-0.5">
                        Room {{ $draftContract->room->room_number }} &bull; {{ ucfirst($draftContract->room->room_type) }}
                        &bull; ₱{{ number_format($draftContract->base_rent_rate, 2) }}/mo
                        &mdash; review and sign to activate.
                    </p>
                </div>
            </div>
            <a href="{{ route('tenant.contract') }}" wire:navigate
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-4 py-2 shadow-sm transition shrink-0">
                <i class="fas fa-arrow-right text-xs"></i> Review & Sign
            </a>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Contract timer card --}}
        <div class="card lg:col-span-2">
            <h3 class="text-base font-semibold text-gray-900 mb-4">My Contract</h3>
            @if($contract)
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Room {{ $contract->room->room_number }} &bull;
                            {{ ucfirst($contract->room->room_type) }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">{{ $contract->start_date->format('M d, Y') }} —
                            {{ $contract->end_date->format('M d, Y') }}</p>
                    </div>
                    <span class="badge {{ $contract->timer_badge_css }} text-sm px-3 py-1">{{ $contract->days_remaining }}
                        days left</span>
                </div>
                {{-- Progress bar --}}
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="h-2.5 rounded-full transition-all duration-500 {{ match ($contract->timer_badge) { 'green' => 'bg-green-500', 'amber' => 'bg-amber-500', 'red' => 'bg-red-500', default => 'bg-gray-400'} }}"
                        style="width: {{ $contract->progress_percent }}%"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1.5">{{ $contract->progress_percent }}% elapsed</p>
                <div class="mt-4 grid grid-cols-2 gap-4 text-sm border-t border-gray-100 pt-4">
                    <div>
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Monthly Rent</span>
                        <p class="font-semibold text-gray-900 mt-0.5">₱{{ number_format($contract->base_rent_rate, 2) }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Penalty Rate</span>
                        <p class="font-semibold text-gray-900 mt-0.5">₱{{ number_format($contract->penalty_rate, 2) }}/day
                        </p>
                    </div>
                </div>
            @elseif($draftContract)
                <div class="flex items-start gap-3">
                    <i class="fas fa-hourglass-half text-marigold-500 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-semibold text-brand-900">Draft contract awaiting your signature</p>
                        <p class="text-xs text-gray-500 mt-1">
                            The General Manager has prepared your contract for Room {{ $draftContract->room->room_number }}.
                            Acknowledge Step&nbsp;1 and Step&nbsp;2 in <strong>My Contract</strong> to activate it and start your billing cycle.
                        </p>
                    </div>
                </div>
            @else
                <p class="text-sm text-gray-400">No active contract.</p>
            @endif
        </div>

        {{-- Quick stats --}}
        <div class="space-y-4">
            <div class="card border-t-4 {{ $unpaidCount > 0 ? 'border-red-500' : 'border-green-500' }}">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Unpaid Bills</p>
                <p class="text-3xl font-bold {{ $unpaidCount > 0 ? 'text-red-600' : 'text-green-600' }} mt-2">
                    {{ $unpaidCount }}</p>
                <a href="{{ route('tenant.billing') }}" wire:navigate
                    class="mt-3 inline-flex items-center gap-1 rounded-full bg-brand-600 px-3 py-1 text-xs font-semibold text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-400 transition">
                    <i class="fas fa-receipt text-[10px]"></i> View My Bills
                </a>
            </div>
        </div>
    </div>

    {{-- Latest Bill --}}
    <div class="card border-l-4 {{ $latestBill ? ($latestBill->status === 'paid' ? 'border-emerald-500' : ($latestBill->days_overdue > 0 ? 'border-red-500' : 'border-amber-500')) : 'border-gray-300' }} mb-6">
        @if($latestBill)
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="text-[11px] uppercase tracking-wide font-semibold {{ $latestBill->status === 'paid' ? 'text-emerald-700' : ($latestBill->days_overdue > 0 ? 'text-red-700' : 'text-amber-700') }}">
                        Latest Bill
                    </p>
                    <p class="font-semibold text-gray-900">
                        {{ $latestBill->type === 'initial' ? 'Initial Fees' : ($latestBill->billing_period ?? 'Monthly') }}
                    </p>
                    <p class="text-xs text-gray-500">
                        Room {{ $latestBill->room?->room_number ?? '—' }}
                        &bull; Due {{ $latestBill->due_date->format('M d, Y') }}
                        @if($latestBill->days_overdue > 0 && $latestBill->status !== 'paid')
                            <span class="text-red-600 font-medium">&bull; {{ $latestBill->days_overdue }}d overdue</span>
                        @endif
                    </p>
                </div>
                <span class="badge {{ $latestBill->status_badge }}">{{ ucfirst($latestBill->status) }}</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                @if($latestBill->type === 'initial')
                    <div><span class="text-gray-500">Deposit</span><br>
                        <span class="font-medium">₱{{ number_format($latestBill->deposit_amount, 2) }}</span></div>
                    <div><span class="text-gray-500">First Month</span><br>
                        <span class="font-medium">₱{{ number_format($latestBill->base_rent, 2) }}</span></div>
                    <div><span class="text-gray-500">Key Fee</span><br>
                        <span class="font-medium">₱{{ number_format($latestBill->room_key_fee, 2) }}</span></div>
                @else
                    <div><span class="text-gray-500">Base Rent</span><br>
                        <span class="font-medium">₱{{ number_format($latestBill->base_rent, 2) }}</span></div>
                    <div><span class="text-gray-500">Electricity</span><br>
                        <span class="font-medium">₱{{ number_format($latestBill->electricity ?? 0, 2) }}</span></div>
                    <div><span class="text-gray-500">Water</span><br>
                        <span class="font-medium">₱{{ number_format($latestBill->water ?? 0, 2) }}</span></div>
                    <div><span class="text-gray-500">WiFi</span><br>
                        <span class="font-medium">₱{{ number_format($latestBill->wifi ?? 0, 2) }}</span></div>
                @endif
            </div>

            @if($latestBill->penalty_amount > 0)
                <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between text-sm">
                    <span class="text-gray-500">Penalty</span>
                    <span class="font-medium text-red-600">₱{{ number_format($latestBill->penalty_amount, 2) }}</span>
                </div>
            @endif

            <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                <span class="text-sm text-gray-500">Total</span>
                <span class="text-lg font-bold text-gray-900">₱{{ number_format($latestBill->total_amount, 2) }}</span>
            </div>

            @if($latestBill->payments->count())
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-2">Payment Records</p>
                    @foreach($latestBill->payments as $p)
                        <div class="flex items-center justify-between text-xs py-0.5">
                            <span class="text-gray-600">₱{{ number_format($p->amount, 2) }} via
                                {{ ucfirst(str_replace('_', ' ', $p->payment_method)) }}</span>
                            <span class="text-gray-400">{{ $p->confirmed_at?->format('M d, Y') ?? 'Pending' }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($latestBill->status !== 'paid' && $latestBill->days_overdue > 0)
                <div class="mt-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">
                    This bill is {{ $latestBill->days_overdue }} day(s) overdue. Please settle with the General Manager.
                </div>
            @endif

            <div class="mt-3 pt-3 border-t border-gray-100">
                <a href="{{ route('tenant.billing') }}" wire:navigate
                    class="inline-flex items-center gap-1 rounded-full bg-brand-600 px-3 py-1 text-xs font-semibold text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-400 transition">
                    <i class="fas fa-receipt text-[10px]"></i> View My Bills
                </a>
            </div>
        @else
            <p class="text-[11px] uppercase tracking-wide text-gray-500 font-semibold">Latest Bill</p>
            <p class="font-semibold text-gray-700 mt-1">No Bill Yet</p>
            <p class="text-sm text-gray-400 mt-2">You don't have any bills yet. Once the General Manager generates one, it will appear here.</p>
        @endif
    </div>

    {{-- Announcements (recent, from management) --}}
    <div class="card mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-gray-900">
                <i class="fas fa-bullhorn text-brand-600 mr-1.5"></i> Recent Announcements
            </h3>
            <span class="text-xs text-gray-400">Last {{ count($announcements) }}</span>
        </div>
        @forelse($announcements as $a)
            @php $isRead = in_array($a->title, $readAnnouncementTitles, true); @endphp
            <div wire:key="ann-{{ $a->id }}"
                class="py-3 border-b border-gray-100 last:border-0 {{ $isRead ? '' : 'bg-amber-50/40 -mx-2 px-2 rounded' }}">
                <div class="flex items-start justify-between gap-3 mb-1">
                    <p class="text-sm font-semibold text-brand-900">
                        @if(!$isRead)
                            <span class="inline-block h-2 w-2 rounded-full bg-amber-500 mr-1 align-middle"></span>
                        @endif
                        {{ $a->title }}
                    </p>
                    <p class="text-[11px] text-gray-400 shrink-0">{{ $a->created_at->format('M d, Y') }}</p>
                </div>
                <p class="text-xs text-gray-600 whitespace-pre-line leading-relaxed">{{ $a->body }}</p>
                @if(!$isRead)
                    <button type="button" wire:click="markAnnouncementRead({{ $a->id }})"
                        class="mt-2 inline-flex items-center gap-1 text-[11px] font-semibold text-brand-700 hover:text-brand-900">
                        <i class="fas fa-check text-[10px]"></i> Mark as read
                    </button>
                @endif
            </div>
        @empty
            <p class="text-sm text-gray-400">No announcements yet.</p>
        @endforelse
    </div>

    {{-- Notifications --}}
    <div class="card">
        <h3 class="text-base font-semibold text-gray-900 mb-4">Recent Notifications</h3>
        @forelse($notifications as $n)
            <div
                class="py-2.5 border-b border-gray-100 last:border-0 {{ $n->is_read ? '' : 'bg-blue-50/50 -mx-2 px-2 rounded' }}">
                <p class="text-sm text-gray-800">{{ $n->message }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $n->created_at->diffForHumans() }} &bull; {{ $n->source }}</p>
            </div>
        @empty
            <p class="text-sm text-gray-400">No notifications.</p>
        @endforelse
    </div>
</div>