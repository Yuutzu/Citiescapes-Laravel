<?php

namespace App\Livewire;

use App\Models\NotificationLog;
use Livewire\Component;

class NotificationBell extends Component
{
    public function markAllRead()
    {
        NotificationLog::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    public function render()
    {
        $unreadCount = NotificationLog::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();

        $recent = NotificationLog::where('user_id', auth()->id())
            ->latest()
            ->take(10)
            ->get();

        return view('livewire.notification-bell', compact('unreadCount', 'recent'));
    }
}
