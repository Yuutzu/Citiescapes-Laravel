<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" class="relative p-2 text-gray-500 hover:text-gray-700">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
        @if($unreadCount > 0)
            <span class="absolute top-1 right-1 h-4 w-4 rounded-full bg-red-500 text-[10px] font-bold text-white flex items-center justify-center">{{ min($unreadCount, 9) }}{{ $unreadCount > 9 ? '+' : '' }}</span>
        @endif
    </button>

    <div x-show="open" @click.away="open = false" x-transition
         class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg ring-1 ring-gray-200 z-50 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
            <h4 class="text-sm font-semibold text-gray-900">Notifications</h4>
            @if($unreadCount > 0)
                <button wire:click="markAllRead" class="text-xs text-brand-600 hover:text-brand-800">Mark all read</button>
            @endif
        </div>
        <div class="max-h-72 overflow-y-auto divide-y divide-gray-50">
            @forelse($recent as $notif)
                <div class="px-4 py-3 {{ $notif->is_read ? 'bg-white' : 'bg-blue-50/50' }}">
                    <p class="text-sm text-gray-800">{{ $notif->message }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }} &bull; {{ $notif->source }}</p>
                </div>
            @empty
                <div class="px-4 py-6 text-center text-sm text-gray-400">No notifications yet.</div>
            @endforelse
        </div>
    </div>
</div>
