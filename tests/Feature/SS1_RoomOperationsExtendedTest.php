<?php

namespace Tests\Feature;

use App\Livewire\Admin\Rooms\InquiryManager;
use App\Livewire\Admin\Rooms\RoomManager;
use App\Livewire\Public\RoomListings;
use App\Livewire\Public\RoomTypeDetail;
use App\Models\Archive;
use App\Models\AuditLog;
use App\Models\Inquiry;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SS1 — Room Operations (Extended Black-Box)
 *
 * Fills the gaps from BBT_SS1_ROOM_*, _CARD_*, _LIST_*, _INQ_013/014, _DASH_*, _AUD_*.
 */
class SS1_RoomOperationsExtendedTest extends TestCase
{
    use RefreshDatabase;

    protected User $gm;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gm = User::create([
            'full_name'    => 'Test GM',
            'email'        => 'gm@test.local',
            'password'     => Hash::make('password'),
            'role'         => 'gm',
            'status'       => 'active',
            'activated_at' => now(),
        ]);
    }

    private function makeRoom(array $overrides = []): Room
    {
        return Room::create(array_merge([
            'room_number'   => '201',
            'floor_level'   => 2,
            'room_type'     => 'compact',
            'rate'          => 3500,
            'max_occupants' => 3,
            'status'        => 'available',
        ], $overrides));
    }

    /* ── Edit Room ─────────────────────────────────────── */

    #[Test] // BBT_SS1_ROOM_EDIT — valid edit updates row + audit
    public function editing_a_room_updates_row_and_audits(): void
    {
        $r = $this->makeRoom();

        Livewire::actingAs($this->gm)->test(RoomManager::class)
            ->call('edit', $r->id)
            ->set('rate', 4200)
            ->set('max_occupants', 4)
            ->set('amenitiesInput', ['WiFi', 'Aircon'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('rooms', [
            'id'   => $r->id,
            'rate' => 4200,
            'max_occupants' => 4,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action'    => 'room_updated',
            'subsystem' => 'SS1',
        ]);
    }

    #[Test] // BBT_SS1_ROOM_EDIT_BVA — max_occupants 11 fails (BVA above max 10)
    public function edit_rejects_max_occupants_above_10(): void
    {
        $r = $this->makeRoom();

        Livewire::actingAs($this->gm)->test(RoomManager::class)
            ->call('edit', $r->id)
            ->set('max_occupants', 11)
            ->call('save')
            ->assertHasErrors(['max_occupants']);
    }

    /* ── Update Room Status ────────────────────────────── */

    #[Test] // BBT_SS1_STATUS_AVAIL_TO_MAINT — state transition
    public function update_status_available_to_maintenance(): void
    {
        $r = $this->makeRoom(['status' => 'available']);

        Livewire::actingAs($this->gm)->test(RoomManager::class)
            ->call('updateStatus', $r->id, 'under_maintenance');

        $this->assertDatabaseHas('rooms', [
            'id'     => $r->id,
            'status' => 'under_maintenance',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action'    => 'room_status_change',
            'subsystem' => 'SS1',
        ]);
    }

    #[Test] // BBT_SS1_STATUS_OCCUPIED_BLOCKED — GM cannot flip an occupied room
    // until the active tenant is moved out / contract terminated. Behaviour added
    // per SS1 review notes; status stays 'occupied' and an error flash is set.
    public function changing_status_of_occupied_room_is_blocked(): void
    {
        $tenant = User::create([
            'full_name' => 't', 'email' => 't@x.com',
            'password'  => Hash::make('p'), 'role' => 'tenant', 'status' => 'active',
        ]);
        $r = $this->makeRoom([
            'status'            => 'occupied',
            'current_tenant_id' => $tenant->id,
        ]);

        Livewire::actingAs($this->gm)->test(RoomManager::class)
            ->call('updateStatus', $r->id, 'available');

        // Status must NOT have changed.
        $this->assertDatabaseHas('rooms', [
            'id'                => $r->id,
            'status'            => 'occupied',
            'current_tenant_id' => $tenant->id,
        ]);
    }

    #[Test] // BBT_SS1_STATUS_KEEP_TENANT_WHEN_OCCUPIED
    public function setting_to_occupied_preserves_current_tenant_id(): void
    {
        $tenant = User::create([
            'full_name' => 't', 'email' => 't2@x.com',
            'password'  => Hash::make('p'), 'role' => 'tenant', 'status' => 'active',
        ]);
        $r = $this->makeRoom([
            'status'            => 'occupied',
            'current_tenant_id' => $tenant->id,
        ]);

        Livewire::actingAs($this->gm)->test(RoomManager::class)
            ->call('updateStatus', $r->id, 'occupied');

        $this->assertDatabaseHas('rooms', [
            'id'                => $r->id,
            'current_tenant_id' => $tenant->id,
        ]);
    }

    /* ── Public Listings & Type Detail ─────────────────── */

    #[Test] // BBT_SS1_LIST_RENDERS — public landing page renders both cards
    public function public_landing_page_renders_both_room_type_cards(): void
    {
        $this->makeRoom(['room_number' => 'C-101', 'room_type' => 'compact', 'status' => 'available']);
        $this->makeRoom(['room_number' => 'C-102', 'room_type' => 'compact', 'status' => 'available']);
        $this->makeRoom(['room_number' => 'S-101', 'room_type' => 'spacious', 'status' => 'available']);

        $this->get('/')
            ->assertOk()
            ->assertSeeText('Compact')
            ->assertSeeText('Spacious');
    }

    #[Test] // BBT_SS1_TYPE_DETAIL — per-type detail mount accepts valid type
    public function room_type_detail_mount_accepts_valid_type(): void
    {
        $this->makeRoom(['room_number' => 'C-101', 'room_type' => 'compact', 'rate' => 3000]);

        Livewire::test(RoomTypeDetail::class, ['type' => 'compact'])
            ->assertOk();
    }

    /* ── Inquiry Mark Responded / Close ────────────────── */

    #[Test] // BBT_SS1_INQ_MARK_RESPONDED — manual mark without email
    public function gm_can_mark_inquiry_responded_without_sending_email(): void
    {
        $inq = Inquiry::create([
            'sender_name'         => 'Maria',
            'contact_number'      => '0917',
            'email'               => null,
            'preferred_room_type' => 'compact',
            'message'             => 'Hi',
            'status'              => 'pending',
        ]);

        Livewire::actingAs($this->gm)->test(InquiryManager::class)
            ->call('markResponded', $inq->id);

        $this->assertDatabaseHas('inquiries', [
            'id'           => $inq->id,
            'status'       => 'responded',
            'responded_by' => $this->gm->id,
        ]);
    }

    #[Test] // BBT_SS1_INQ_CLOSE — responded → closed
    public function gm_can_close_inquiry(): void
    {
        $inq = Inquiry::create([
            'sender_name'    => 'Maria',
            'contact_number' => '0917',
            'message'        => 'Hi',
            'status'         => 'responded',
        ]);

        Livewire::actingAs($this->gm)->test(InquiryManager::class)
            ->call('close', $inq->id);

        $this->assertDatabaseHas('inquiries', [
            'id'     => $inq->id,
            'status' => 'closed',
        ]);
    }

    /* ── Inquiry filter ────────────────────────────────── */

    #[Test] // BBT_SS1_INQ_FILTER — filterStatus narrows the list
    public function inquiry_filter_by_status_narrows_results(): void
    {
        Inquiry::create(['sender_name' => 'AlphaSender', 'contact_number' => '01', 'message' => 'a', 'status' => 'pending']);
        Inquiry::create(['sender_name' => 'BetaSender',  'contact_number' => '02', 'message' => 'b', 'status' => 'responded']);
        Inquiry::create(['sender_name' => 'GammaSender', 'contact_number' => '03', 'message' => 'c', 'status' => 'closed']);

        Livewire::actingAs($this->gm)->test(InquiryManager::class)
            ->set('filterStatus', 'responded')
            ->assertSee('BetaSender')
            ->assertDontSee('AlphaSender')
            ->assertDontSee('GammaSender');
    }

    /* ── Search rooms ──────────────────────────────────── */

    #[Test] // BBT_SS1_ROOM_SEARCH
    public function room_search_filters_by_room_number(): void
    {
        $this->makeRoom(['room_number' => '201']);
        $this->makeRoom(['room_number' => '305']);

        Livewire::actingAs($this->gm)->test(RoomManager::class)
            ->set('search', '20')
            ->assertSee('201')
            ->assertDontSee('305');
    }

    #[Test] // BBT_SS1_ROOM_FILTER_FLOOR
    public function room_filter_by_floor(): void
    {
        $this->makeRoom(['room_number' => '101', 'floor_level' => 1]);
        $this->makeRoom(['room_number' => '201', 'floor_level' => 2]);

        Livewire::actingAs($this->gm)->test(RoomManager::class)
            ->set('filterFloor', '2')
            ->assertSee('201')
            ->assertDontSee('101');
    }

    #[Test] // BBT_SS1_ROOM_FILTER_STATUS
    public function room_filter_by_status(): void
    {
        $this->makeRoom(['room_number' => 'AVAIL1', 'status' => 'available']);
        $this->makeRoom(['room_number' => 'MAINT1', 'status' => 'under_maintenance']);

        Livewire::actingAs($this->gm)->test(RoomManager::class)
            ->set('filterStatus', 'under_maintenance')
            ->assertSee('MAINT1')
            ->assertDontSee('AVAIL1');
    }

    /* ── Archive Room / Permanently Delete ─────────────── */

    #[Test] // BBT_SS1_ROOM_ARCHIVE
    public function gm_can_archive_a_room(): void
    {
        $r = $this->makeRoom();

        Livewire::actingAs($this->gm)->test(RoomManager::class)
            ->call('archiveRoom', $r->id);

        $this->assertDatabaseHas('archives', [
            'record_type'      => 'room',
            'source_subsystem' => 'SS1',
        ]);
        $this->assertSoftDeleted('rooms', ['id' => $r->id]);
        $this->assertDatabaseHas('audit_logs', [
            'action'    => 'room_archived',
            'subsystem' => 'SS1',
        ]);
    }

    #[Test] // BBT_SS1_ROOM_DELETE
    public function gm_can_permanently_delete_a_room(): void
    {
        $r = $this->makeRoom();

        Livewire::actingAs($this->gm)->test(RoomManager::class)
            ->call('deleteRoom', $r->id);

        $this->assertDatabaseMissing('rooms', ['id' => $r->id]);
        $this->assertDatabaseHas('audit_logs', [
            'action'    => 'room_deleted',
            'subsystem' => 'SS1',
        ]);
    }

    /* ── Audit log cross-check ─────────────────────────── */

    #[Test] // BBT_SS1_AUD — multiple SS1 events recorded
    public function multiple_room_operations_audit_to_ss1(): void
    {
        $r = $this->makeRoom();

        Livewire::actingAs($this->gm)->test(RoomManager::class)
            ->call('updateStatus', $r->id, 'under_maintenance');

        Livewire::actingAs($this->gm)->test(RoomManager::class)
            ->call('archiveRoom', $r->id);

        $this->assertSame(2, AuditLog::where('subsystem', 'SS1')
            ->whereIn('action', ['room_status_change', 'room_archived'])
            ->count());
    }
}
