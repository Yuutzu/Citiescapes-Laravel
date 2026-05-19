<?php

namespace Tests\Feature;

use App\Livewire\Admin\Communications\AnnouncementManager;
use App\Livewire\Admin\Communications\RequestViewer;
use App\Livewire\NotificationBell;
use App\Livewire\Tenant\RequestManager;
use App\Models\Announcement;
use App\Models\NotificationLog;
use App\Models\TenantRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SS7 — Communications (Extended Black-Box)
 *
 * Covers: list/filter/search of tenant requests, notification bell open + URL routing,
 * tenant-email-null branch, view announcement history filter, audit cross-checks.
 */
class SS7_CommunicationsExtendedTest extends TestCase
{
    use RefreshDatabase;

    protected User $gm;
    protected User $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gm = User::create([
            'full_name' => 'GM', 'email' => 'gm@x.com',
            'password' => Hash::make('p'), 'role' => 'gm',
            'status' => 'active', 'activated_at' => now(),
        ]);
        $this->tenant = User::create([
            'full_name' => 'Maria Santos', 'email' => 't@x.com',
            'password' => Hash::make('p'), 'role' => 'tenant',
            'status' => 'active', 'activated_at' => now(),
        ]);
    }

    /* ── View / Filter / Search tenant requests ────── */

    #[Test] // BBT_SS7_REQ_FILTER_TYPE
    public function gm_can_filter_requests_by_type_complaint(): void
    {
        TenantRequest::create([
            'tenant_id' => $this->tenant->id, 'type' => 'request',
            'subject' => 'Aircon Maintenance', 'body' => 'b', 'status' => 'pending',
        ]);
        TenantRequest::create([
            'tenant_id' => $this->tenant->id, 'type' => 'complaint',
            'subject' => 'Noisy Neighbor', 'body' => 'b', 'status' => 'pending',
        ]);

        Livewire::actingAs($this->gm)->test(RequestViewer::class)
            ->set('filterType', 'complaint')
            ->assertSee('Noisy Neighbor')
            ->assertDontSee('Aircon Maintenance');
    }

    #[Test] // BBT_SS7_REQ_SEARCH_TENANT
    public function gm_can_search_requests_by_tenant_name(): void
    {
        $other = User::create([
            'full_name' => 'Juan Cruz', 'email' => 'j@x.com',
            'password' => Hash::make('p'), 'role' => 'tenant', 'status' => 'active',
        ]);

        TenantRequest::create([
            'tenant_id' => $this->tenant->id, 'type' => 'request',
            'subject' => 'Maria thing', 'body' => 'b', 'status' => 'pending',
        ]);
        TenantRequest::create([
            'tenant_id' => $other->id, 'type' => 'request',
            'subject' => 'Juan thing', 'body' => 'b', 'status' => 'pending',
        ]);

        Livewire::actingAs($this->gm)->test(RequestViewer::class)
            ->set('search', 'Juan')
            ->assertSee('Juan')
            ->assertDontSee('Maria');
    }

    /* ── Notification bell ─────────────────────────── */

    #[Test] // BBT_SS7_BELL_VIEW_UNREAD — render exposes unreadCount to the view
    public function bell_renders_with_correct_unread_count(): void
    {
        for ($i = 0; $i < 3; $i++) {
            NotificationLog::create([
                'user_id' => $this->tenant->id,
                'type'    => 'announcement',
                'source'  => 'SS7',
                'message' => "msg $i",
                'is_read' => false,
            ]);
        }

        Livewire::actingAs($this->tenant)->test(NotificationBell::class)
            ->assertViewHas('unreadCount', 3);
    }

    #[Test] // BBT_SS7_BELL_OPEN_MARKS_READ
    public function opening_a_notification_marks_it_read(): void
    {
        $n = NotificationLog::create([
            'user_id' => $this->tenant->id,
            'type'    => 'announcement',
            'source'  => 'SS7',
            'message' => 'hi',
            'is_read' => false,
        ]);

        Livewire::actingAs($this->tenant)->test(NotificationBell::class)
            ->call('open', $n->id);

        $this->assertTrue((bool) $n->fresh()->is_read);
    }

    #[Test] // BBT_SS7_BELL_OPEN_FOREIGN — notification for another user is a no-op
    public function opening_someone_elses_notification_is_a_noop(): void
    {
        $other = User::create([
            'full_name' => 'Other', 'email' => 'o@x.com',
            'password' => Hash::make('p'), 'role' => 'tenant', 'status' => 'active',
        ]);
        $foreign = NotificationLog::create([
            'user_id' => $other->id,
            'type'    => 'announcement',
            'source'  => 'SS7',
            'message' => 'their msg',
            'is_read' => false,
        ]);

        Livewire::actingAs($this->tenant)->test(NotificationBell::class)
            ->call('open', $foreign->id);

        $this->assertFalse((bool) $foreign->fresh()->is_read, 'Foreign notification should not be marked read.');
    }

    /* ── Announcement history search ───────────────── */

    #[Test] // BBT_SS7_ANN_HISTORY_SEARCH
    public function announcement_history_search_narrows_by_title(): void
    {
        Announcement::create([
            'title' => 'Power Outage', 'body' => 'b1',
            'recipient_type' => 'all', 'sent_by' => $this->gm->id, 'email_sent' => true,
        ]);
        Announcement::create([
            'title' => 'New Year Greeting', 'body' => 'b2',
            'recipient_type' => 'all', 'sent_by' => $this->gm->id, 'email_sent' => true,
        ]);

        Livewire::actingAs($this->gm)->test(AnnouncementManager::class)
            ->set('search', 'outage')
            ->assertSee('Power Outage')
            ->assertDontSee('New Year');
    }

    /* ── Announcement to invalid recipient ─────────── */

    #[Test] // BBT_SS7_ANN_INVALID_RECIPIENT
    public function announcement_with_nonexistent_specific_recipient_fails_validation(): void
    {
        Livewire::actingAs($this->gm)->test(AnnouncementManager::class)
            ->set('title', 'Hi')
            ->set('body', 'Body')
            ->set('recipientType', 'specific')
            ->set('recipientId', 999999)
            ->call('send')
            ->assertHasErrors(['recipientId']);

        $this->assertSame(0, Announcement::count());
    }

    /* ── Audit cross-check ─────────────────────────── */

    #[Test] // BBT_SS7_AUD_027
    public function ss7_events_are_audit_logged(): void
    {
        // Submit a request as tenant
        Livewire::actingAs($this->tenant)->test(RequestManager::class)
            ->set('type', 'request')
            ->set('subject', 'Audit cross-check')
            ->set('body', 'b')
            ->call('submit');

        // Send an announcement as GM
        Livewire::actingAs($this->gm)->test(AnnouncementManager::class)
            ->set('title', 'Audit test')
            ->set('body', 'b')
            ->set('recipientType', 'all')
            ->call('send');

        $actions = \App\Models\AuditLog::where('subsystem', 'SS7')->pluck('action')->toArray();
        $this->assertContains('tenant_request_submitted', $actions);
        $this->assertContains('announcement_sent', $actions);
    }
}
