<?php

namespace Tests\Feature;

use App\Livewire\Admin\Communications\AnnouncementManager;
use App\Livewire\Admin\Communications\RequestViewer;
use App\Livewire\NotificationBell;
use App\Livewire\Tenant\RequestManager;
use App\Mail\AnnouncementMail;
use App\Mail\RequestResponseMail;
use App\Mail\TenantRequestMail;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\NotificationLog;
use App\Models\TenantRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SS7 — Communications & Notifications (Black-Box)
 *
 * Tests tenant request submission, GM response with Resolved-lock,
 * announcements (broadcast + specific), notification bell, and audit-log fan-out.
 * Technique: Equivalence Partitioning, Boundary Value Analysis, State Transition.
 *
 * IDs: BBT_SS7_*_001..014
 */
class SS7_CommunicationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $gm;
    protected User $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->gm = User::create([
            'full_name'    => 'Test GM',
            'email'        => 'gm@test.local',
            'password'     => Hash::make('password'),
            'role'         => 'gm',
            'status'       => 'active',
            'activated_at' => now(),
        ]);

        $this->tenant = User::create([
            'full_name'    => 'Tenant One',
            'email'        => 'tenant@test.local',
            'password'     => Hash::make('password'),
            'role'         => 'tenant',
            'status'       => 'active',
            'activated_at' => now(),
        ]);
    }

    /* ── Tenant submissions (UC 7.1 / 7.2) ─────────────── */

    #[Test] // BBT_SS7_REQ_001 — EP: valid maintenance request
    public function tenant_can_submit_valid_maintenance_request(): void
    {
        Livewire::actingAs($this->tenant)->test(RequestManager::class)
            ->set('type', 'request')
            ->set('subject', 'Aircon broken')
            ->set('body', 'AC unit not cooling.')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tenant_requests', [
            'tenant_id' => $this->tenant->id,
            'type'      => 'request',
            'subject'   => 'Aircon broken',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action'    => 'tenant_request_submitted',
            'subsystem' => 'SS7',
        ]);
    }

    #[Test] // BBT_SS7_REQ_004 — EP: valid complaint
    public function tenant_can_submit_valid_complaint(): void
    {
        Livewire::actingAs($this->tenant)->test(RequestManager::class)
            ->set('type', 'complaint')
            ->set('subject', 'Noisy neighbor')
            ->set('body', 'Loud music after 10pm.')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tenant_requests', [
            'type'    => 'complaint',
            'subject' => 'Noisy neighbor',
        ]);
    }

    #[Test] // BBT_SS7_REQ_002 — BVA: subject above max 150
    public function submit_rejects_subject_over_150_chars(): void
    {
        Livewire::actingAs($this->tenant)->test(RequestManager::class)
            ->set('type', 'request')
            ->set('subject', str_repeat('x', 151))
            ->set('body', 'body')
            ->call('submit')
            ->assertHasErrors(['subject']);

        $this->assertSame(0, TenantRequest::count());
    }

    #[Test] // BBT_SS7_REQ_003 — EP: empty body rejected
    public function submit_rejects_empty_body(): void
    {
        Livewire::actingAs($this->tenant)->test(RequestManager::class)
            ->set('type', 'request')
            ->set('subject', 'Subject ok')
            ->set('body', '')
            ->call('submit')
            ->assertHasErrors(['body']);
    }

    /* ── Notify GMs on new tenant request (UC 7.3) ─────── */

    #[Test] // BBT_SS7_REQ_005 — fan-out to all active GMs
    public function submit_notifies_every_active_gm(): void
    {
        // Add 2 more active GMs (total 3 active)
        $extra = collect(['gm2@test.local', 'gm3@test.local'])->map(fn($e) =>
            User::create([
                'full_name' => 'GM ' . $e,
                'email'     => $e,
                'password'  => Hash::make('password'),
                'role'      => 'gm',
                'status'    => 'active',
            ])
        );

        Livewire::actingAs($this->tenant)->test(RequestManager::class)
            ->set('type', 'request')
            ->set('subject', 'Fan out test')
            ->set('body', 'test body')
            ->call('submit');

        $this->assertSame(3, NotificationLog::where('type', 'tenant_request')->count());
        Mail::assertSent(TenantRequestMail::class, 3);
    }

    #[Test] // BBT_SS7_REQ_006 — ALT: archived GMs are skipped
    public function submit_skips_archived_gms(): void
    {
        User::create([
            'full_name' => 'Archived GM',
            'email'     => 'ar@test.local',
            'password'  => Hash::make('password'),
            'role'      => 'gm',
            'status'    => 'archived',
        ]);

        Livewire::actingAs($this->tenant)->test(RequestManager::class)
            ->set('type', 'request')
            ->set('subject', 'Skip archived')
            ->set('body', 'test')
            ->call('submit');

        // Only the 1 active GM should be notified
        $this->assertSame(1, NotificationLog::where('type', 'tenant_request')->count());
        Mail::assertSent(TenantRequestMail::class, 1);
    }

    /* ── Respond to request & update status (UC 7.5) ───── */

    #[Test] // BBT_SS7_REQ_010 — HAPPY: GM responds, status flips
    public function gm_can_respond_to_pending_request(): void
    {
        $req = TenantRequest::create([
            'tenant_id' => $this->tenant->id,
            'type'      => 'request',
            'subject'   => 'Pending one',
            'body'      => 'body',
            'status'    => 'pending',
        ]);

        Livewire::actingAs($this->gm)->test(RequestViewer::class)
            ->call('view', $req->id)
            ->set('adminReply', 'Scheduled tomorrow')
            ->set('newStatus', 'in_progress')
            ->call('respond')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tenant_requests', [
            'id'             => $req->id,
            'status'         => 'in_progress',
            'admin_response' => 'Scheduled tomorrow',
            'responded_by'   => $this->gm->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action'    => 'tenant_request_responded',
            'subsystem' => 'SS7',
        ]);
        Mail::assertSent(RequestResponseMail::class, 1);
    }

    #[Test] // BBT_SS7_REQ_011 — ALT: resolve marks final
    public function gm_can_set_request_to_resolved(): void
    {
        $req = TenantRequest::create([
            'tenant_id' => $this->tenant->id,
            'type'      => 'request',
            'subject'   => 'In progress one',
            'body'      => 'body',
            'status'    => 'in_progress',
        ]);

        Livewire::actingAs($this->gm)->test(RequestViewer::class)
            ->call('view', $req->id)
            ->set('adminReply', 'Done')
            ->set('newStatus', 'resolved')
            ->call('respond')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tenant_requests', [
            'id'     => $req->id,
            'status' => 'resolved',
        ]);
    }

    #[Test] // BBT_SS7_REQ_012 — ERROR: resolved is locked
    public function resolved_request_cannot_be_edited_again(): void
    {
        $req = TenantRequest::create([
            'tenant_id'      => $this->tenant->id,
            'type'           => 'request',
            'subject'        => 'Already done',
            'body'           => 'body',
            'status'         => 'resolved',
            'admin_response' => 'Fixed already',
            'responded_by'   => $this->gm->id,
            'responded_at'   => now(),
        ]);

        Livewire::actingAs($this->gm)->test(RequestViewer::class)
            ->call('view', $req->id)
            ->set('adminReply', 'second edit')
            ->set('newStatus', 'in_progress')
            ->call('respond');

        // Status NOT reverted; admin_response NOT overwritten
        $this->assertDatabaseHas('tenant_requests', [
            'id'             => $req->id,
            'status'         => 'resolved',
            'admin_response' => 'Fixed already',
        ]);
    }

    /* ── Announcements (UC 7.10 / 7.11) ────────────────── */

    #[Test] // BBT_SS7_ANN_020 — HAPPY: broadcast to all active tenants
    public function gm_can_broadcast_announcement_to_all_tenants(): void
    {
        // Add 2 more active tenants (total 3 active)
        collect(['t2@test.local', 't3@test.local'])->each(fn($e) =>
            User::create([
                'full_name' => 'Tenant ' . $e,
                'email'     => $e,
                'password'  => Hash::make('password'),
                'role'      => 'tenant',
                'status'    => 'active',
            ])
        );

        Livewire::actingAs($this->gm)->test(AnnouncementManager::class)
            ->set('title', 'Water maintenance')
            ->set('body', 'Saturday 6-9pm.')
            ->set('recipientType', 'all')
            ->call('send')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('announcements', [
            'title'          => 'Water maintenance',
            'recipient_type' => 'all',
            'sent_by'        => $this->gm->id,
        ]);
        $this->assertSame(3, NotificationLog::where('type', 'announcement')->count());
        Mail::assertSent(AnnouncementMail::class, 3);
        $this->assertDatabaseHas('audit_logs', [
            'action'    => 'announcement_sent',
            'subsystem' => 'SS7',
        ]);
    }

    #[Test] // BBT_SS7_ANN_021 — ALT: direct to one tenant
    public function gm_can_send_announcement_to_specific_tenant(): void
    {
        Livewire::actingAs($this->gm)->test(AnnouncementManager::class)
            ->set('title', 'Personal note')
            ->set('body', 'Please update your contact.')
            ->set('recipientType', 'specific')
            ->set('recipientId', $this->tenant->id)
            ->call('send')
            ->assertHasNoErrors();

        $this->assertSame(1, NotificationLog::where('type', 'announcement')->count());
        Mail::assertSent(AnnouncementMail::class, 1);
        $this->assertDatabaseHas('announcements', [
            'recipient_type' => 'specific',
            'recipient_id'   => $this->tenant->id,
        ]);
    }

    #[Test] // BBT_SS7_ANN_023 — ERROR: empty title/body rejected
    public function announcement_rejects_empty_title_and_body(): void
    {
        Livewire::actingAs($this->gm)->test(AnnouncementManager::class)
            ->set('title', '')
            ->set('body', '')
            ->set('recipientType', 'all')
            ->call('send')
            ->assertHasErrors(['title', 'body']);

        $this->assertSame(0, Announcement::count());
    }

    /* ── Notification Bell (UC 7.7 / 7.9) ──────────────── */

    #[Test] // BBT_SS7_BELL_019 — markAllRead clears unread badge
    public function mark_all_read_clears_every_unread_for_user(): void
    {
        // Seed 3 unread + 1 belonging to another user
        $other = User::create([
            'full_name' => 'Other',
            'email'     => 'other@test.local',
            'password'  => Hash::make('password'),
            'role'      => 'tenant',
            'status'    => 'active',
        ]);

        for ($i = 0; $i < 3; $i++) {
            NotificationLog::create([
                'user_id' => $this->tenant->id,
                'type'    => 'announcement',
                'source'  => 'SS7',
                'message' => "msg $i",
                'is_read' => false,
            ]);
        }
        NotificationLog::create([
            'user_id' => $other->id,
            'type'    => 'announcement',
            'source'  => 'SS7',
            'message' => 'other user msg',
            'is_read' => false,
        ]);

        Livewire::actingAs($this->tenant)->test(NotificationBell::class)
            ->call('markAllRead');

        $this->assertSame(0, NotificationLog::where('user_id', $this->tenant->id)->where('is_read', false)->count());
        // Other user's unread untouched
        $this->assertSame(1, NotificationLog::where('user_id', $other->id)->where('is_read', false)->count());
    }
}
