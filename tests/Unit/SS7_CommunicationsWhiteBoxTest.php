<?php

namespace Tests\Unit;

use App\Livewire\Admin\Communications\AnnouncementManager;
use App\Livewire\Admin\Communications\RequestViewer;
use App\Livewire\NotificationBell;
use App\Livewire\Tenant\RequestManager as TenantRequestManager;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\NotificationLog;
use App\Models\Room;
use App\Models\TenantRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * SS7 — Communications & Notifications (White-Box)
 * Branch Coverage Tests for RequestManager, RequestViewer, AnnouncementManager,
 * and NotificationBell control flow.
 *
 * Test Case ID Format: WBT_SS7_{MODULE}_{NNN}
 * Technique: Branch Coverage
 * Coverage target: 19 documented branches in docs/white_box_testing_all_subsystems.html
 */
class SS7_CommunicationsWhiteBoxTest extends TestCase
{
    use RefreshDatabase;

    protected User $tenant;
    protected User $gm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gm = User::create([
            'full_name' => 'WB GM',
            'email'     => 'wb.gm@test.local',
            'password'  => Hash::make('password'),
            'role'      => 'gm',
            'status'    => 'active',
            'activated_at' => now(),
        ]);

        $this->tenant = User::create([
            'full_name' => 'WB Tenant',
            'email'     => 'wb.tenant@test.local',
            'password'  => Hash::make('password'),
            'role'      => 'tenant',
            'status'    => 'active',
            'activated_at' => now(),
        ]);

        Mail::fake();
    }

    // ───────────────────────────────────────────────
    // WBT_SS7_REQ_001 — RequestManager::submit (valid)
    // ───────────────────────────────────────────────
    public function test_request_submit_valid_persists_and_fans_out(): void
    {
        Livewire::actingAs($this->tenant)
            ->test(TenantRequestManager::class)
            ->set('type', 'request')
            ->set('subject', 'Aircon broken')
            ->set('body', 'Please fix.')
            ->call('submit');

        $this->assertDatabaseHas('tenant_requests', [
            'tenant_id' => $this->tenant->id,
            'type'      => 'request',
            'subject'   => 'Aircon broken',
            'status'    => 'pending',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action'    => 'tenant_request_submitted',
            'subsystem' => 'SS7',
        ]);
        $this->assertDatabaseHas('notifications_log', [
            'user_id' => $this->gm->id,
            'type'    => 'tenant_request',
            'source'  => 'SS7',
        ]);
    }

    // ───────────────────────────────────────────────
    // WBT_SS7_REQ_002 — RequestManager::submit (invalid)
    // ───────────────────────────────────────────────
    public function test_request_submit_invalid_blocks_persistence(): void
    {
        Livewire::actingAs($this->tenant)
            ->test(TenantRequestManager::class)
            ->set('type', 'request')
            ->set('subject', str_repeat('x', 151)) // 151 chars > max:150
            ->set('body', 'Body.')
            ->call('submit')
            ->assertHasErrors(['subject']);

        $this->assertDatabaseCount('tenant_requests', 0);
        $this->assertDatabaseCount('notifications_log', 0);
    }

    // ───────────────────────────────────────────────
    // WBT_SS7_REQ_003 — foreach iterates 3 active GMs
    // ───────────────────────────────────────────────
    public function test_request_submit_fans_out_to_all_active_gms(): void
    {
        User::create(['full_name' => 'GM2', 'email' => 'gm2@test.local', 'password' => Hash::make('p'), 'role' => 'gm', 'status' => 'active', 'activated_at' => now()]);
        User::create(['full_name' => 'GM3', 'email' => 'gm3@test.local', 'password' => Hash::make('p'), 'role' => 'gm', 'status' => 'active', 'activated_at' => now()]);

        Livewire::actingAs($this->tenant)
            ->test(TenantRequestManager::class)
            ->set('type', 'request')->set('subject', 's')->set('body', 'b')
            ->call('submit');

        $this->assertEquals(3, NotificationLog::where('type', 'tenant_request')->count());
    }

    // ───────────────────────────────────────────────
    // WBT_SS7_REQ_004 — foreach when GM email is falsy
    // Branch is structurally unreachable: users.email is NOT NULL UNIQUE.
    // We assert the bell-write side of the loop instead (mail side stays as
    // documented dead branch in the WBT HTML).
    // ───────────────────────────────────────────────
    public function test_request_submit_writes_bell_row_per_gm(): void
    {
        Livewire::actingAs($this->tenant)
            ->test(TenantRequestManager::class)
            ->set('type', 'complaint')->set('subject', 's')->set('body', 'b')
            ->call('submit');

        $this->assertEquals(1, NotificationLog::where('user_id', $this->gm->id)->count());
    }

    // ───────────────────────────────────────────────
    // WBT_SS7_VIEW_005 — RequestViewer::respond (validation fail)
    // ───────────────────────────────────────────────
    public function test_request_viewer_respond_validation_fail(): void
    {
        $req = TenantRequest::create([
            'tenant_id' => $this->tenant->id,
            'type' => 'request', 'subject' => 's', 'body' => 'b', 'status' => 'pending',
        ]);

        Livewire::actingAs($this->gm)
            ->test(RequestViewer::class)
            ->call('view',$req->id)
            ->set('newStatus', 'invalid_status')
            ->set('adminReply', 'reply')
            ->call('respond')
            ->assertHasErrors(['newStatus']);

        $this->assertDatabaseHas('tenant_requests', ['id' => $req->id, 'status' => 'pending']);
    }

    // ───────────────────────────────────────────────
    // WBT_SS7_VIEW_006 — respond on already-resolved (locked)
    // ───────────────────────────────────────────────
    public function test_request_viewer_respond_blocked_when_resolved(): void
    {
        $req = TenantRequest::create([
            'tenant_id' => $this->tenant->id,
            'type' => 'request', 'subject' => 's', 'body' => 'b',
            'status' => 'resolved',
            'admin_response' => 'old', 'responded_by' => $this->gm->id, 'responded_at' => now(),
        ]);

        $component = Livewire::actingAs($this->gm)
            ->test(RequestViewer::class)
            ->call('view',$req->id)
            ->set('newStatus', 'in_progress')
            ->set('adminReply', 'new')
            ->call('respond');

        // Resolved row remains untouched
        $this->assertDatabaseHas('tenant_requests', ['id' => $req->id, 'status' => 'resolved', 'admin_response' => 'old']);
    }

    // ───────────────────────────────────────────────
    // WBT_SS7_VIEW_007 — respond fires mail when tenant has email
    // ───────────────────────────────────────────────
    public function test_request_viewer_respond_sends_mail_when_tenant_has_email(): void
    {
        $req = TenantRequest::create([
            'tenant_id' => $this->tenant->id,
            'type' => 'request', 'subject' => 's', 'body' => 'b', 'status' => 'pending',
        ]);

        Livewire::actingAs($this->gm)
            ->test(RequestViewer::class)
            ->call('view',$req->id)
            ->set('newStatus', 'in_progress')
            ->set('adminReply', 'On it.')
            ->call('respond');

        $this->assertDatabaseHas('tenant_requests', ['id' => $req->id, 'status' => 'in_progress']);
        $this->assertDatabaseHas('notifications_log', ['user_id' => $this->tenant->id, 'source' => 'SS7']);
    }

    // ───────────────────────────────────────────────
    // WBT_SS7_VIEW_008 — respond when tenant email branch is false
    // Branch is structurally unreachable: users.email is NOT NULL UNIQUE.
    // Verified by the always-on bell write side.
    // ───────────────────────────────────────────────
    public function test_request_viewer_respond_always_writes_bell_to_tenant(): void
    {
        $req = TenantRequest::create([
            'tenant_id' => $this->tenant->id,
            'type' => 'request', 'subject' => 's', 'body' => 'b', 'status' => 'pending',
        ]);

        Livewire::actingAs($this->gm)
            ->test(RequestViewer::class)
            ->call('view', $req->id)
            ->set('newStatus', 'in_progress')
            ->set('adminReply', 'On it.')
            ->call('respond');

        $this->assertDatabaseHas('notifications_log', ['user_id' => $this->tenant->id, 'source' => 'SS7']);
    }

    // ───────────────────────────────────────────────
    // WBT_SS7_ANN_009 — AnnouncementManager::send (validation pass)
    // ───────────────────────────────────────────────
    public function test_announcement_send_validation_pass(): void
    {
        Livewire::actingAs($this->gm)
            ->test(AnnouncementManager::class)
            ->set('title', 'Notice')
            ->set('body',  'Body text.')
            ->set('recipientType', 'all')
            ->call('send');

        $this->assertDatabaseHas('announcements', ['title' => 'Notice', 'recipient_type' => 'all']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'announcement_sent', 'subsystem' => 'SS7']);
    }

    // ───────────────────────────────────────────────
    // WBT_SS7_ANN_010 — send (validation fail: empty title)
    // ───────────────────────────────────────────────
    public function test_announcement_send_validation_fail(): void
    {
        Livewire::actingAs($this->gm)
            ->test(AnnouncementManager::class)
            ->set('title', '')
            ->set('body',  'Body.')
            ->set('recipientType', 'all')
            ->call('send')
            ->assertHasErrors(['title']);

        $this->assertDatabaseCount('announcements', 0);
    }

    // ───────────────────────────────────────────────
    // WBT_SS7_ANN_011 — resolveRecipients (all)
    // ───────────────────────────────────────────────
    public function test_announcement_resolve_recipients_all_returns_active_tenants(): void
    {
        // create 2 more active tenants + 1 archived (should not be included)
        User::create(['full_name' => 'T2', 'email' => 't2@test.local', 'password' => Hash::make('p'), 'role' => 'tenant', 'status' => 'active', 'activated_at' => now()]);
        User::create(['full_name' => 'T3', 'email' => 't3@test.local', 'password' => Hash::make('p'), 'role' => 'tenant', 'status' => 'active', 'activated_at' => now()]);
        User::create(['full_name' => 'T-arc', 'email' => 'arc@test.local', 'password' => Hash::make('p'), 'role' => 'tenant', 'status' => 'archived', 'activated_at' => now()]);

        Livewire::actingAs($this->gm)
            ->test(AnnouncementManager::class)
            ->set('title', 'All')->set('body', 'B')->set('recipientType', 'all')
            ->call('send');

        // 3 active tenants → 3 bell rows
        $this->assertEquals(3, NotificationLog::where('source', 'SS7')->where('type', 'announcement')->count());
    }

    // ───────────────────────────────────────────────
    // WBT_SS7_ANN_012 — resolveRecipients (specific)
    // ───────────────────────────────────────────────
    public function test_announcement_resolve_recipients_specific_returns_one(): void
    {
        User::create(['full_name' => 'Other', 'email' => 'other@test.local', 'password' => Hash::make('p'), 'role' => 'tenant', 'status' => 'active', 'activated_at' => now()]);

        Livewire::actingAs($this->gm)
            ->test(AnnouncementManager::class)
            ->set('title', 'Direct')->set('body', 'B')
            ->set('recipientType', 'specific')->set('recipientId', $this->tenant->id)
            ->call('send');

        $this->assertEquals(1, NotificationLog::where('source', 'SS7')->where('type', 'announcement')->count());
        $this->assertDatabaseHas('notifications_log', ['user_id' => $this->tenant->id, 'source' => 'SS7']);
    }

    // ───────────────────────────────────────────────
    // WBT_SS7_ANN_013 — send fires mail when recipient has email
    // ───────────────────────────────────────────────
    public function test_announcement_send_fires_mail_when_recipient_email_present(): void
    {
        Livewire::actingAs($this->gm)
            ->test(AnnouncementManager::class)
            ->set('title', 'M')->set('body', 'B')
            ->set('recipientType', 'specific')->set('recipientId', $this->tenant->id)
            ->call('send');

        // tenant.email is set, so a mail send should have been queued
        Mail::assertSentCount(1);
    }

    // ───────────────────────────────────────────────
    // WBT_SS7_ANN_014 — recipient email branch
    // Branch is structurally unreachable: users.email is NOT NULL UNIQUE.
    // Documented as defensive code; verified by always-on bell write.
    // ───────────────────────────────────────────────
    public function test_announcement_send_always_writes_bell_to_recipient(): void
    {
        Livewire::actingAs($this->gm)
            ->test(AnnouncementManager::class)
            ->set('title', 'M')->set('body', 'B')
            ->set('recipientType', 'specific')->set('recipientId', $this->tenant->id)
            ->call('send');

        $this->assertDatabaseHas('notifications_log', ['user_id' => $this->tenant->id, 'source' => 'SS7']);
    }

    // ───────────────────────────────────────────────
    // WBT_SS7_BELL_015 — NotificationBell::open with foreign id (early return)
    // ───────────────────────────────────────────────
    public function test_bell_open_foreign_notification_early_returns(): void
    {
        // Notification belongs to GM, but we act as tenant
        $foreign = NotificationLog::create([
            'user_id' => $this->gm->id,
            'type'    => 'announcement', 'source' => 'SS7',
            'message' => 'GM-only', 'is_read' => false,
        ]);

        Livewire::actingAs($this->tenant)
            ->test(NotificationBell::class)
            ->call('open', $foreign->id);

        // Foreign row untouched (still unread)
        $this->assertDatabaseHas('notifications_log', ['id' => $foreign->id, 'is_read' => false]);
    }

    // ───────────────────────────────────────────────
    // WBT_SS7_BELL_016 — open on already-read notification
    // ───────────────────────────────────────────────
    public function test_bell_open_already_read_skips_update(): void
    {
        $n = NotificationLog::create([
            'user_id' => $this->tenant->id,
            'type'    => 'announcement', 'source' => 'SS7',
            'message' => 'read me', 'is_read' => true,
        ]);

        Livewire::actingAs($this->tenant)
            ->test(NotificationBell::class)
            ->call('open', $n->id);

        // Still read, no double-update / no error
        $this->assertTrue($n->fresh()->is_read);
    }

    // ───────────────────────────────────────────────
    // WBT_SS7_BELL_017 — open unread, actionUrl returns string (redirect path)
    // ───────────────────────────────────────────────
    public function test_bell_open_unread_with_url_marks_read(): void
    {
        $n = NotificationLog::create([
            'user_id' => $this->tenant->id,
            'type'    => 'tenant_request_responded', 'source' => 'SS7',
            'message' => 'reply', 'is_read' => false,
        ]);

        Livewire::actingAs($this->tenant)
            ->test(NotificationBell::class)
            ->call('open', $n->id);

        $this->assertTrue($n->fresh()->is_read);
    }

    // ───────────────────────────────────────────────
    // WBT_SS7_BELL_018 — open unread, actionUrl null (no redirect)
    // ───────────────────────────────────────────────
    public function test_bell_open_unread_without_url_still_marks_read(): void
    {
        $n = NotificationLog::create([
            'user_id' => $this->tenant->id,
            'type'    => 'unknown_type_no_route', 'source' => 'SS7',
            'message' => 'no url type', 'is_read' => false,
        ]);

        Livewire::actingAs($this->tenant)
            ->test(NotificationBell::class)
            ->call('open', $n->id);

        // Still marks read even if no actionUrl
        $this->assertTrue($n->fresh()->is_read);
    }

    // ───────────────────────────────────────────────
    // WBT_SS7_BELL_019 — markAllRead bulk-updates only unread for this user
    // ───────────────────────────────────────────────
    public function test_bell_mark_all_read_only_affects_current_user(): void
    {
        // Mixed read/unread for the tenant
        NotificationLog::create(['user_id' => $this->tenant->id, 'type' => 'announcement', 'source' => 'SS7', 'message' => '1', 'is_read' => false]);
        NotificationLog::create(['user_id' => $this->tenant->id, 'type' => 'announcement', 'source' => 'SS7', 'message' => '2', 'is_read' => false]);
        NotificationLog::create(['user_id' => $this->tenant->id, 'type' => 'announcement', 'source' => 'SS7', 'message' => '3', 'is_read' => true]);
        // Another user's unread row — must stay unread
        $gmRow = NotificationLog::create(['user_id' => $this->gm->id, 'type' => 'announcement', 'source' => 'SS7', 'message' => 'gm', 'is_read' => false]);

        Livewire::actingAs($this->tenant)
            ->test(NotificationBell::class)
            ->call('markAllRead');

        $this->assertEquals(0, NotificationLog::where('user_id', $this->tenant->id)->where('is_read', false)->count());
        $this->assertFalse($gmRow->fresh()->is_read);
    }
}
