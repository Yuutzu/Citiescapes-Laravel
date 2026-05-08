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

    public function open(int $id)
    {
        $notif = NotificationLog::where('user_id', auth()->id())->find($id);
        if (! $notif) {
            return;
        }

        if (! $notif->is_read) {
            $notif->update(['is_read' => true]);
        }

        $url = $notif->actionUrl(auth()->user()?->role);
        if ($url) {
            return $this->redirect($url, navigate: true);
        }
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
