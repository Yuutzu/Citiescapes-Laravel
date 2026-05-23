<?php

namespace App\Livewire\Tenant;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Bill;
use App\Models\Contract;
use App\Models\InitialPayment;
use App\Models\NotificationLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('My Dashboard — Citiescapes')]
class Dashboard extends Component
{
    /**
     * Mark a single announcement as read for the current tenant and write a
     * business-activity audit row. Idempotent — re-marking is a no-op.
     */
    public function markAnnouncementRead(int $id): void
    {
        $tenantId = auth()->id();
        $announcement = Announcement::forTenant($tenantId)->find($id);
        if (!$announcement) return;

        $log = NotificationLog::where('user_id', $tenantId)
            ->where('type', 'announcement')
            ->where('message', "[Announcement] {$announcement->title}")
            ->where('is_read', false)
            ->first();

        if (!$log) return; // already read or never had a bell row

        $log->update(['is_read' => true]);
        AuditLog::record('tenant_announcement_viewed', $tenantId, 'tenant', 'SS7',
            "Announcement #{$announcement->id} \"{$announcement->title}\" marked read");
    }

    public function render()
    {
        $user = auth()->user();
        $contract = Contract::with('room')->where('tenant_id', $user->id)->active()->first();

        // Draft contract awaiting tenant acknowledgement. Surfaced as a banner so
        // a newly-onboarded tenant doesn't miss it; without this prompt the
        // dashboard would just say "No active contract" and the tenant might
        // never click into "My Contract" to complete Step 1/Step 2.
        $draftContract = Contract::with('room')
            ->where('tenant_id', $user->id)->where('status', 'draft')
            ->latest()->first();

        $latestBill = Bill::with(['room', 'payments'])
            ->where('tenant_id', $user->id)->latest()->first();
        $unpaidCount = Bill::where('tenant_id', $user->id)->unpaid()->count();
        $notifications = NotificationLog::where('user_id', $user->id)->latest()->take(5)->get();

        // Initial payment (move-in collection) — shown on dashboard
        $initialPayment = InitialPayment::with('contract.room')
            ->where('tenant_id', $user->id)->first();

        // Recent announcements (3 most recent reaching this tenant) and a set of
        // IDs the tenant has already marked read, so the view can render the badge.
        $announcements = Announcement::forTenant($user->id)
            ->latest('created_at')->take(3)->get();
        $readAnnouncementTitles = NotificationLog::where('user_id', $user->id)
            ->where('type', 'announcement')->where('is_read', true)
            ->pluck('message')->map(fn($m) => str_replace('[Announcement] ', '', $m))
            ->all();

        return view('livewire.tenant.dashboard', compact(
            'user',
            'contract',
            'draftContract',
            'latestBill',
            'unpaidCount',
            'notifications',
            'initialPayment',
            'announcements',
            'readAnnouncementTitles'
        ));
    }
}
