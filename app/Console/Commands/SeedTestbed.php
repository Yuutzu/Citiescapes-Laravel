<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use App\Models\Archive;
use App\Models\AuditLog;
use App\Models\Bill;
use App\Models\Contract;
use App\Models\InitialPayment;
use App\Models\Inquiry;
use App\Models\NotificationLog;
use App\Models\OtpRecord;
use App\Models\Room;
use App\Models\SystemSetting;
use App\Models\TenantRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * SeedTestbed
 *
 * Time-relative testbed for black-box + white-box verification.
 * Every scenario sets dates relative to now() so the same scenario
 * produces the same expected status whenever it runs.
 *
 * Marker convention (used for idempotent cleanup):
 *   - users.email ends in "@testbed.local"
 *   - rooms.room_number starts with "TB-"
 *   - all related rows hang off those users/rooms
 *   - inquiries / audit_logs / system_settings tagged with "[testbed]" in a text field
 *
 * Usage:
 *   php artisan testbed:seed                  # seed everything (no cron)
 *   php artisan testbed:seed --apply-crons    # seed + run penalty + warning + auto-archive crons
 *   php artisan testbed:seed --list           # show scenario catalog, no DB writes
 *   php artisan testbed:seed --clean          # wipe testbed rows, no reseed
 *   php artisan testbed:seed --subsystem=SS3  # one subsystem only
 *   php artisan testbed:seed --scenario=bill.grace_day1,contract.active_red_7
 */
class SeedTestbed extends Command
{
    protected $signature = 'testbed:seed
        {--list           : Show the scenario catalog and exit (no DB writes)}
        {--clean          : Wipe testbed rows and exit (no reseed)}
        {--apply-crons    : After seeding, run billing:apply-penalties + contracts:send-warnings + contracts:auto-archive}
        {--subsystem=     : Only scenarios for this subsystem (SS1..SS7)}
        {--scenario=      : Comma-separated scenario slugs}';

    protected $description = 'Time-relative testbed seeder covering every status branch for BBT + WBT';

    private const EMAIL_DOMAIN = '@testbed.local';
    private const ROOM_PREFIX  = 'TB-';
    private const TAG          = '[testbed]';

    /** Anchors created idempotently each run. */
    private User $gm;
    private User $tenant;
    private Room $room;

    /** Collected output rows: [slug => ['ids' => [...], 'status' => 'x', 'after_cron' => 'y']]. */
    private array $manifest = [];

    public function handle(): int
    {
        if ($this->option('list')) {
            return $this->showCatalog();
        }

        if ($this->option('clean')) {
            $this->cleanTestbed();
            $this->info('✅ Testbed wiped.');
            return self::SUCCESS;
        }

        $this->info('🌱 Seeding testbed (time-relative, idempotent)…');
        $this->cleanTestbed();
        $this->ensureAnchors();

        $slugs = $this->resolveSelectedSlugs();
        foreach ($slugs as $slug) {
            $this->runScenario($slug);
        }

        if ($this->option('apply-crons')) {
            $this->runCrons();
        }

        $this->printSummary();
        $this->writeManifest();

        $this->newLine();
        $this->info('✅ Done. Manifest: storage/app/testbed-manifest.json');
        $this->line('   Anchors: GM ' . $this->gm->email . ' / Tenant ' . $this->tenant->email . ' / Room ' . $this->room->room_number);
        if (!$this->option('apply-crons')) {
            $this->line('   Run "php artisan testbed:seed --apply-crons" or invoke the three crons manually to advance time-based statuses.');
        }

        return self::SUCCESS;
    }

    // ---------------------------------------------------------------------
    // Catalog
    // ---------------------------------------------------------------------

    /**
     * @return array<string,array{ss:string, label:string, bbt:string, wbt:string, expects:string}>
     */
    private function catalog(): array
    {
        return [
            // SS1 Rooms ------------------------------------------------------
            'room.available'         => ['ss'=>'SS1','label'=>'Room with status=available',         'bbt'=>'BBT_SS1_ROOM','wbt'=>'WBT_SS1_ROOM','expects'=>'available'],
            'room.occupied'          => ['ss'=>'SS1','label'=>'Room with status=occupied + tenant', 'bbt'=>'BBT_SS1_ROOM','wbt'=>'WBT_SS1_ROOM','expects'=>'occupied'],
            'room.under_maintenance' => ['ss'=>'SS1','label'=>'Room status=under_maintenance',       'bbt'=>'BBT_SS1_ROOM','wbt'=>'WBT_SS1_ROOM','expects'=>'under_maintenance'],
            'room.archived'          => ['ss'=>'SS1','label'=>'Room soft-deleted + Archive row',     'bbt'=>'BBT_SS1_ROOM','wbt'=>'WBT_SS1_ROOM','expects'=>'archived'],
            'inquiry.pending'        => ['ss'=>'SS1','label'=>'Inquiry status=pending',              'bbt'=>'BBT_SS1_INQ', 'wbt'=>'WBT_SS1_INQ', 'expects'=>'pending'],
            'inquiry.responded'      => ['ss'=>'SS1','label'=>'Inquiry status=responded',            'bbt'=>'BBT_SS1_INQ', 'wbt'=>'WBT_SS1_INQ', 'expects'=>'responded'],
            'inquiry.closed'         => ['ss'=>'SS1','label'=>'Inquiry status=closed',               'bbt'=>'BBT_SS1_INQ', 'wbt'=>'WBT_SS1_INQ', 'expects'=>'closed'],

            // SS2 Tenants ----------------------------------------------------
            'tenant.pending_activation' => ['ss'=>'SS2','label'=>'Tenant just created, must_change_password=true',           'bbt'=>'BBT_SS2_TEN','wbt'=>'WBT_SS2_TEN','expects'=>'pending_activation'],
            'tenant.with_valid_otp'     => ['ss'=>'SS2','label'=>'Pending tenant + OtpRecord expiring in 10 min',            'bbt'=>'BBT_SS2_TEN','wbt'=>'WBT_SS2_TEN','expects'=>'pending_activation+otp'],
            'tenant.with_expired_otp'   => ['ss'=>'SS2','label'=>'Pending tenant + expired OtpRecord (resend branch)',       'bbt'=>'BBT_SS2_TEN','wbt'=>'WBT_SS2_TEN','expects'=>'otp_expired'],
            'tenant.active'             => ['ss'=>'SS2','label'=>'Active tenant, activated_at set',                          'bbt'=>'BBT_SS2_TEN','wbt'=>'WBT_SS2_TEN','expects'=>'active'],
            'tenant.archived_manual'    => ['ss'=>'SS2','label'=>'Tenant manually archived by GM (Archive row + status)',    'bbt'=>'BBT_SS2_TEN','wbt'=>'WBT_SS2_TEN','expects'=>'archived'],

            // SS3 Bills ------------------------------------------------------
            'bill.unpaid_future'        => ['ss'=>'SS3','label'=>'Bill due_date=+5d (still upcoming)',                        'bbt'=>'BBT_SS3_BILL','wbt'=>'WBT_SS3_BILL','expects'=>'unpaid'],
            'bill.grace_day1'           => ['ss'=>'SS3','label'=>'Bill due_date=-1d → grace + first reminder',                 'bbt'=>'BBT_SS3_BILL','wbt'=>'WBT_SS3_BILL','expects'=>'grace'],
            'bill.grace_day3'           => ['ss'=>'SS3','label'=>'Bill due_date=-3d → grace + grace-ending reminder',          'bbt'=>'BBT_SS3_BILL','wbt'=>'WBT_SS3_BILL','expects'=>'grace'],
            'bill.overdue_day4'         => ['ss'=>'SS3','label'=>'Bill due_date=-4d → overdue + 1 day of penalty',             'bbt'=>'BBT_SS3_BILL','wbt'=>'WBT_SS3_BILL','expects'=>'overdue'],
            'bill.overdue_day13'        => ['ss'=>'SS3','label'=>'Bill due_date=-13d → overdue, just below delinquent',        'bbt'=>'BBT_SS3_BILL','wbt'=>'WBT_SS3_BILL','expects'=>'overdue'],
            'bill.delinquent_day14'     => ['ss'=>'SS3','label'=>'Bill due_date=-14d → delinquent + once-only notice',         'bbt'=>'BBT_SS3_BILL','wbt'=>'WBT_SS3_BILL','expects'=>'delinquent'],
            'bill.delinquent_day29'    => ['ss'=>'SS3','label'=>'Bill due_date=-29d → delinquent, just below eviction',        'bbt'=>'BBT_SS3_BILL','wbt'=>'WBT_SS3_BILL','expects'=>'delinquent'],
            'bill.eviction_day30'       => ['ss'=>'SS3','label'=>'Bill due_date=-30d → eviction + once-only notice',           'bbt'=>'BBT_SS3_BILL','wbt'=>'WBT_SS3_BILL','expects'=>'eviction'],
            'bill.eviction_day45'       => ['ss'=>'SS3','label'=>'Bill due_date=-45d → eviction, deep arrears',                'bbt'=>'BBT_SS3_BILL','wbt'=>'WBT_SS3_BILL','expects'=>'eviction'],
            'bill.paid'                 => ['ss'=>'SS3','label'=>'Bill marked paid (manual GM confirm)',                       'bbt'=>'BBT_SS3_BILL','wbt'=>'WBT_SS3_BILL','expects'=>'paid'],
            'bill.archived'             => ['ss'=>'SS3','label'=>'Bill status=archived (lease end)',                           'bbt'=>'BBT_SS3_BILL','wbt'=>'WBT_SS3_BILL','expects'=>'archived'],
            'bill.penalty_override'     => ['ss'=>'SS3','label'=>'Overdue bill with GM penalty override',                      'bbt'=>'BBT_SS3_BILL','wbt'=>'WBT_SS3_BILL','expects'=>'overdue+override'],
            'bill.initial_payment'      => ['ss'=>'SS3','label'=>'Initial payment row (deposit + 1st month + key)',            'bbt'=>'BBT_SS3_INIT','wbt'=>'WBT_SS3_INIT','expects'=>'recorded'],

            // SS4 Contracts --------------------------------------------------
            'contract.draft'                 => ['ss'=>'SS4','label'=>'Contract status=draft (no acks)',                       'bbt'=>'BBT_SS4_CTR','wbt'=>'WBT_SS4_CTR','expects'=>'draft'],
            'contract.active_green'          => ['ss'=>'SS4','label'=>'Active, end_date=+60d (green badge)',                   'bbt'=>'BBT_SS4_CTR','wbt'=>'WBT_SS4_CTR','expects'=>'green'],
            'contract.active_amber_30'       => ['ss'=>'SS4','label'=>'Active, end_date=+30d (30-day warning fires)',          'bbt'=>'BBT_SS4_CTR','wbt'=>'WBT_SS4_CTR','expects'=>'amber'],
            'contract.active_amber_15'       => ['ss'=>'SS4','label'=>'Active, end_date=+15d (amber, mid-warning)',            'bbt'=>'BBT_SS4_CTR','wbt'=>'WBT_SS4_CTR','expects'=>'amber'],
            'contract.active_red_7'          => ['ss'=>'SS4','label'=>'Active, end_date=+7d (7-day warning fires)',            'bbt'=>'BBT_SS4_CTR','wbt'=>'WBT_SS4_CTR','expects'=>'red'],
            'contract.active_red_1'          => ['ss'=>'SS4','label'=>'Active, end_date=+1d (red, last day)',                  'bbt'=>'BBT_SS4_CTR','wbt'=>'WBT_SS4_CTR','expects'=>'red'],
            'contract.expired_unarchived'    => ['ss'=>'SS4','label'=>'Active, end_date=-1d → auto-archive cron candidate',    'bbt'=>'BBT_SS4_CTR','wbt'=>'WBT_SS4_CTR','expects'=>'cron-will-flip'],
            'contract.expired_archived'      => ['ss'=>'SS4','label'=>'Expired + archived (terminal state)',                   'bbt'=>'BBT_SS4_CTR','wbt'=>'WBT_SS4_CTR','expects'=>'expired'],
            'contract.terminated'            => ['ss'=>'SS4','label'=>'Terminated mid-lease with reason',                      'bbt'=>'BBT_SS4_CTR','wbt'=>'WBT_SS4_CTR','expects'=>'terminated'],
            'contract.step1_ack_only'        => ['ss'=>'SS4','label'=>'Draft + step1_acknowledged_at set, awaiting step 2',    'bbt'=>'BBT_SS4_ACK','wbt'=>'WBT_SS4_ACK','expects'=>'draft+step1'],
            'contract.step2_ack_active'      => ['ss'=>'SS4','label'=>'Active after step 2 ack',                               'bbt'=>'BBT_SS4_ACK','wbt'=>'WBT_SS4_ACK','expects'=>'active'],

            // SS5 Archives ---------------------------------------------------
            'archive.room'        => ['ss'=>'SS5','label'=>'Archive(record_type=room, source=SS1)',                'bbt'=>'BBT_SS5_ARC','wbt'=>'WBT_SS5_ARC','expects'=>'archived'],
            'archive.tenant'      => ['ss'=>'SS5','label'=>'Archive(record_type=tenant_account, source=SS2)',      'bbt'=>'BBT_SS5_ARC','wbt'=>'WBT_SS5_ARC','expects'=>'archived'],
            'archive.contract'    => ['ss'=>'SS5','label'=>'Archive(record_type=contract, source=SS4)',            'bbt'=>'BBT_SS5_ARC','wbt'=>'WBT_SS5_ARC','expects'=>'archived'],
            'archive.bill'        => ['ss'=>'SS5','label'=>'Archive(record_type=payment, source=SS3)',             'bbt'=>'BBT_SS5_ARC','wbt'=>'WBT_SS5_ARC','expects'=>'archived'],
            'archive.restored'    => ['ss'=>'SS5','label'=>'Archive restored=true (record_restored audit)',        'bbt'=>'BBT_SS5_REST','wbt'=>'WBT_SS5_REST','expects'=>'restored'],

            // SS6 Auth -------------------------------------------------------
            'user.locked_active'      => ['ss'=>'SS6','label'=>'User locked, failed=5, locked_until=now+15m',      'bbt'=>'BBT_SS6_LOCK','wbt'=>'WBT_SS6_LOCK','expects'=>'locked'],
            'user.locked_expired'     => ['ss'=>'SS6','label'=>'User locked but locked_until past → auto-unlock',  'bbt'=>'BBT_SS6_LOCK','wbt'=>'WBT_SS6_LOCK','expects'=>'auto-unlock-pending'],
            'user.must_change_password'=>['ss'=>'SS6','label'=>'Active user with must_change_password=true',       'bbt'=>'BBT_SS6_PWD', 'wbt'=>'WBT_SS6_PWD', 'expects'=>'forced_change'],
            'audit.login'             => ['ss'=>'SS6','label'=>'AuditLog action=login tagged SS6',                 'bbt'=>'BBT_SS6_AUD','wbt'=>'WBT_SS6_AUD','expects'=>'logged'],
            'audit.failed_login'      => ['ss'=>'SS6','label'=>'AuditLog action=failed_login tagged SS6',          'bbt'=>'BBT_SS6_AUD','wbt'=>'WBT_SS6_AUD','expects'=>'logged'],
            'audit.password_change'   => ['ss'=>'SS6','label'=>'AuditLog action=password_change tagged SS6',       'bbt'=>'BBT_SS6_AUD','wbt'=>'WBT_SS6_AUD','expects'=>'logged'],
            'audit.settings_updated'  => ['ss'=>'SS6','label'=>'AuditLog action=settings_updated tagged SS6',      'bbt'=>'BBT_SS6_AUD','wbt'=>'WBT_SS6_AUD','expects'=>'logged'],
            'setting.session_timeout' => ['ss'=>'SS6','label'=>'SystemSetting session_timeout_minutes=20',         'bbt'=>'BBT_SS6_SET','wbt'=>'WBT_SS6_SET','expects'=>'persisted'],

            // SS7 Communications --------------------------------------------
            'request.open'         => ['ss'=>'SS7','label'=>'TenantRequest type=request, status=pending',         'bbt'=>'BBT_SS7_REQ','wbt'=>'WBT_SS7_REQ','expects'=>'pending'],
            'request.in_progress'  => ['ss'=>'SS7','label'=>'TenantRequest status=in_progress (GM working)',      'bbt'=>'BBT_SS7_REQ','wbt'=>'WBT_SS7_REQ','expects'=>'in_progress'],
            'request.resolved'     => ['ss'=>'SS7','label'=>'TenantRequest status=resolved (locked)',             'bbt'=>'BBT_SS7_REQ','wbt'=>'WBT_SS7_REQ','expects'=>'resolved'],
            'complaint.open'       => ['ss'=>'SS7','label'=>'TenantRequest type=complaint, status=pending',       'bbt'=>'BBT_SS7_REQ','wbt'=>'WBT_SS7_REQ','expects'=>'pending'],
            'announcement.broadcast' => ['ss'=>'SS7','label'=>'Announcement recipient_type=all',                   'bbt'=>'BBT_SS7_ANN','wbt'=>'WBT_SS7_ANN','expects'=>'sent'],
            'announcement.direct'    => ['ss'=>'SS7','label'=>'Announcement recipient_type=tenant + recipient_id', 'bbt'=>'BBT_SS7_ANN','wbt'=>'WBT_SS7_ANN','expects'=>'sent'],
            'notification.unread'    => ['ss'=>'SS7','label'=>'NotificationLog is_read=false',                     'bbt'=>'BBT_SS7_NTF','wbt'=>'WBT_SS7_NTF','expects'=>'unread'],
            'notification.read'      => ['ss'=>'SS7','label'=>'NotificationLog is_read=true',                      'bbt'=>'BBT_SS7_NTF','wbt'=>'WBT_SS7_NTF','expects'=>'read'],
        ];
    }

    // ---------------------------------------------------------------------
    // Routing
    // ---------------------------------------------------------------------

    private function showCatalog(): int
    {
        $rows = [];
        foreach ($this->catalog() as $slug => $meta) {
            $rows[] = [$meta['ss'], $slug, $meta['label'], $meta['expects']];
        }
        $this->table(['Subsystem', 'Slug', 'Description', 'Expected status'], $rows);
        $this->line('Total scenarios: ' . count($rows));
        return self::SUCCESS;
    }

    /** @return string[] */
    private function resolveSelectedSlugs(): array
    {
        $catalog = $this->catalog();
        $explicit = $this->option('scenario');
        if ($explicit) {
            $slugs = array_filter(array_map('trim', explode(',', $explicit)));
            foreach ($slugs as $s) {
                if (!isset($catalog[$s])) {
                    $this->warn("Unknown scenario: $s — skipping");
                }
            }
            return array_values(array_intersect($slugs, array_keys($catalog)));
        }
        $ss = $this->option('subsystem');
        if ($ss) {
            $ss = strtoupper($ss);
            return array_keys(array_filter($catalog, fn($m) => $m['ss'] === $ss));
        }
        return array_keys($catalog);
    }

    private function runScenario(string $slug): void
    {
        $catalog = $this->catalog();
        if (!isset($catalog[$slug])) return;

        $method = 'scn_' . str_replace(['.', '-'], '_', $slug);
        if (!method_exists($this, $method)) {
            $this->warn("Scenario method missing: $method");
            return;
        }

        try {
            $ids = $this->{$method}();
            $this->manifest[$slug] = [
                'subsystem' => $catalog[$slug]['ss'],
                'label'     => $catalog[$slug]['label'],
                'expects'   => $catalog[$slug]['expects'],
                'ids'       => $ids,
            ];
            $this->line(sprintf('  ✓ %-32s %s', $slug, json_encode($ids)));
        } catch (\Throwable $e) {
            $this->error("  ✗ {$slug}: " . $e->getMessage());
        }
    }

    // ---------------------------------------------------------------------
    // Anchors + cleanup
    // ---------------------------------------------------------------------

    private function ensureAnchors(): void
    {
        $this->gm = User::updateOrCreate(
            ['email' => 'testbed.gm' . self::EMAIL_DOMAIN],
            [
                'full_name'  => 'Testbed GM',
                'password'   => Hash::make('password'),
                'role'       => 'gm',
                'status'     => 'active',
                'activated_at' => now(),
                'must_change_password' => false,
            ],
        );

        $this->tenant = User::updateOrCreate(
            ['email' => 'testbed.tenant' . self::EMAIL_DOMAIN],
            [
                'full_name'  => 'Testbed Tenant',
                'password'   => Hash::make('password'),
                'role'       => 'tenant',
                'status'     => 'active',
                'activated_at' => now()->subDays(30),
                'must_change_password' => false,
            ],
        );

        $this->room = Room::withTrashed()->updateOrCreate(
            ['room_number' => self::ROOM_PREFIX . '101'],
            [
                'floor_level'        => 1,
                'room_type'          => 'compact',
                'rate'               => 3500.00,
                'max_occupants'      => 2,
                'status'             => 'available',
                'description'        => self::TAG . ' anchor room',
                'amenities'          => ['WiFi', 'Aircon'],
                'last_updated_by'    => $this->gm->id,
                'last_status_update' => now(),
                'deleted_at'         => null,
            ],
        );
    }

    private function cleanTestbed(): void
    {
        $testbedUserIds = User::where('email', 'like', '%' . self::EMAIL_DOMAIN)->pluck('id');
        $testbedRoomIds = Room::withTrashed()->where('room_number', 'like', self::ROOM_PREFIX . '%')->pluck('id');

        Bill::withTrashed()->whereIn('tenant_id', $testbedUserIds)->forceDelete();
        InitialPayment::whereIn('tenant_id', $testbedUserIds)->delete();
        Contract::whereIn('tenant_id', $testbedUserIds)->delete();
        OtpRecord::whereIn('user_id', $testbedUserIds)->delete();
        NotificationLog::whereIn('user_id', $testbedUserIds)->delete();
        TenantRequest::whereIn('tenant_id', $testbedUserIds)->delete();
        Announcement::where(fn($q) => $q->whereIn('sent_by', $testbedUserIds)->orWhereIn('recipient_id', $testbedUserIds))->delete();
        AuditLog::whereIn('user_id', $testbedUserIds)->orWhere('details', 'like', '%' . self::TAG . '%')->delete();
        Archive::where('archive_reason', 'like', '%' . self::TAG . '%')
            ->orWhere(fn($q) => $q->whereIn('archived_by', $testbedUserIds))->delete();
        Inquiry::where('gm_notes', 'like', '%' . self::TAG . '%')->delete();
        SystemSetting::where('key', 'like', 'testbed.%')->delete();

        Room::withTrashed()->whereIn('id', $testbedRoomIds)->forceDelete();
        User::whereIn('id', $testbedUserIds)->forceDelete();
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    private function makeTenant(string $key, array $overrides = []): User
    {
        return User::create(array_merge([
            'full_name'  => 'Testbed ' . Str::title(str_replace(['.', '_'], ' ', $key)),
            'email'      => 'testbed.' . $key . self::EMAIL_DOMAIN,
            'password'   => Hash::make('password'),
            'role'       => 'tenant',
            'status'     => 'active',
            'must_change_password' => false,
        ], $overrides));
    }

    private function makeRoom(string $suffix, array $overrides = []): Room
    {
        return Room::create(array_merge([
            'room_number'        => self::ROOM_PREFIX . $suffix,
            'floor_level'        => 1,
            'room_type'          => 'compact',
            'rate'               => 3500.00,
            'max_occupants'      => 2,
            'status'             => 'available',
            'description'        => self::TAG . ' ' . $suffix,
            'amenities'          => ['WiFi'],
            'last_updated_by'    => $this->gm->id,
            'last_status_update' => now(),
        ], $overrides));
    }

    /** Makes a baseline active contract bound to the anchor tenant + a fresh room. */
    private function makeContract(string $suffix, Carbon $end, string $status = 'active', array $overrides = []): Contract
    {
        $room = $this->makeRoom($suffix, ['status' => $status === 'active' ? 'occupied' : 'available', 'current_tenant_id' => $status === 'active' ? $this->tenant->id : null]);

        return Contract::create(array_merge([
            'tenant_id'              => $this->tenant->id,
            'room_id'                => $room->id,
            'base_rent_rate'         => 3500.00,
            'deposit'                => 3500.00,
            'first_month_rent'       => 3500.00,
            'room_key_fee'           => 200.00,
            'requested_amenities'    => [],
            'start_date'             => $end->copy()->subYear(),
            'end_date'               => $end,
            'status'                 => $status,
            'penalty_rate'           => 50.00,
            'penalty_grace_days'     => 3,
            'house_rules'            => self::TAG . ' standard rules',
            'activated_at'           => $status === 'active' ? now()->subMonths(6) : null,
            'created_by'             => $this->gm->id,
        ], $overrides));
    }

    private function makeBill(Contract $contract, Carbon $dueDate, string $status = 'unpaid', array $overrides = []): Bill
    {
        return Bill::create(array_merge([
            'tenant_id'      => $contract->tenant_id,
            'contract_id'    => $contract->id,
            'room_id'        => $contract->room_id,
            'type'           => 'monthly',
            'billing_period' => $dueDate->format('Y-m'),
            'base_rent'      => 3500.00,
            'electricity'    => 500.00,
            'water'          => 200.00,
            'wifi'           => 300.00,
            'utilities'      => 1000.00,
            'penalty_amount' => 0.00,
            'total_amount'   => 4500.00,
            'due_date'       => $dueDate,
            'status'         => $status,
            'days_overdue'   => 0,
        ], $overrides));
    }

    // ---------------------------------------------------------------------
    // SS1 Rooms + Inquiries
    // ---------------------------------------------------------------------

    private function scn_room_available(): array
    {
        $r = $this->makeRoom('R-AV');
        return ['room_id' => $r->id];
    }

    private function scn_room_occupied(): array
    {
        $r = $this->makeRoom('R-OC', ['status' => 'occupied', 'current_tenant_id' => $this->tenant->id]);
        return ['room_id' => $r->id];
    }

    private function scn_room_under_maintenance(): array
    {
        $r = $this->makeRoom('R-UM', ['status' => 'under_maintenance']);
        return ['room_id' => $r->id];
    }

    private function scn_room_archived(): array
    {
        $r = $this->makeRoom('R-AR');
        $a = Archive::create([
            'original_record_id' => $r->id,
            'record_type'        => 'room',
            'source_subsystem'   => 'SS1',
            'archive_reason'     => self::TAG . ' Manual archive by GM',
            'data'               => $r->toArray(),
            'archived_by'        => $this->gm->id,
            'restored'           => false,
        ]);
        $r->delete();
        return ['room_id' => $r->id, 'archive_id' => $a->id];
    }

    private function scn_inquiry_pending(): array
    {
        $i = Inquiry::create([
            'sender_name'         => 'Testbed Inquirer',
            'contact_number'      => '09171234567',
            'email'               => 'testbed.inquirer' . self::EMAIL_DOMAIN,
            'preferred_room_type' => 'compact',
            'message'             => 'Test inquiry',
            'status'              => 'pending',
            'gm_notes'            => self::TAG,
        ]);
        return ['inquiry_id' => $i->id];
    }

    private function scn_inquiry_responded(): array
    {
        $i = Inquiry::create([
            'sender_name'         => 'Testbed Responded',
            'contact_number'      => '09171234568',
            'email'               => 'testbed.responded' . self::EMAIL_DOMAIN,
            'preferred_room_type' => 'spacious',
            'message'             => 'Test inquiry — responded',
            'status'              => 'responded',
            'gm_notes'            => self::TAG . ' GM replied via email',
            'responded_at'        => now()->subDay(),
            'responded_by'        => $this->gm->id,
        ]);
        return ['inquiry_id' => $i->id];
    }

    private function scn_inquiry_closed(): array
    {
        $i = Inquiry::create([
            'sender_name'         => 'Testbed Closed',
            'contact_number'      => '09171234569',
            'email'               => 'testbed.closed' . self::EMAIL_DOMAIN,
            'preferred_room_type' => 'any',
            'message'             => 'Test inquiry — closed',
            'status'              => 'closed',
            'gm_notes'            => self::TAG,
            'responded_at'        => now()->subWeek(),
            'responded_by'        => $this->gm->id,
        ]);
        return ['inquiry_id' => $i->id];
    }

    // ---------------------------------------------------------------------
    // SS2 Tenants
    // ---------------------------------------------------------------------

    private function scn_tenant_pending_activation(): array
    {
        $u = $this->makeTenant('pending', [
            'status' => 'pending_activation',
            'must_change_password' => true,
            'activated_at' => null,
        ]);
        return ['user_id' => $u->id];
    }

    private function scn_tenant_with_valid_otp(): array
    {
        $u = $this->makeTenant('otp_valid', [
            'status' => 'pending_activation',
            'must_change_password' => true,
            'activated_at' => null,
        ]);
        $otp = OtpRecord::create([
            'user_id'    => $u->id,
            'code'       => Hash::make('123456'),
            'expires_at' => now()->addMinutes(10),
            'used'       => false,
        ]);
        return ['user_id' => $u->id, 'otp_id' => $otp->id];
    }

    private function scn_tenant_with_expired_otp(): array
    {
        $u = $this->makeTenant('otp_expired', [
            'status' => 'pending_activation',
            'must_change_password' => true,
            'activated_at' => null,
        ]);
        $otp = OtpRecord::create([
            'user_id'    => $u->id,
            'code'       => Hash::make('123456'),
            'expires_at' => now()->subMinutes(1),
            'used'       => false,
        ]);
        return ['user_id' => $u->id, 'otp_id' => $otp->id];
    }

    private function scn_tenant_active(): array
    {
        $u = $this->makeTenant('active', ['activated_at' => now()->subMonth()]);
        return ['user_id' => $u->id];
    }

    private function scn_tenant_archived_manual(): array
    {
        $u = $this->makeTenant('arc_manual', [
            'status'      => 'archived',
            'archived_at' => now()->subDay(),
            'archived_by' => $this->gm->id,
        ]);
        $a = Archive::create([
            'original_record_id' => $u->id,
            'record_type'        => 'tenant_account',
            'source_subsystem'   => 'SS2',
            'archive_reason'     => self::TAG . ' Manual archive by GM',
            'data'               => $u->toArray(),
            'archived_by'        => $this->gm->id,
            'restored'           => false,
        ]);
        return ['user_id' => $u->id, 'archive_id' => $a->id];
    }

    // ---------------------------------------------------------------------
    // SS3 Bills
    // ---------------------------------------------------------------------

    private function billScenarioFor(int $dayOffset, string $status, array $billOverrides = []): array
    {
        $c = $this->makeContract('BILL-' . abs($dayOffset) . '-' . $status, now()->addMonths(6));
        $b = $this->makeBill($c, now()->copy()->addDays($dayOffset), $status, $billOverrides);
        return ['contract_id' => $c->id, 'bill_id' => $b->id];
    }

    private function scn_bill_unpaid_future():    array { return $this->billScenarioFor(+5,   'unpaid'); }
    private function scn_bill_grace_day1():       array { return $this->billScenarioFor(-1,   'unpaid'); } // cron flips to grace
    private function scn_bill_grace_day3():       array { return $this->billScenarioFor(-3,   'unpaid'); }
    private function scn_bill_overdue_day4():     array { return $this->billScenarioFor(-4,   'unpaid'); } // cron flips to overdue
    private function scn_bill_overdue_day13():    array { return $this->billScenarioFor(-13,  'unpaid'); }
    private function scn_bill_delinquent_day14(): array { return $this->billScenarioFor(-14,  'unpaid'); }
    private function scn_bill_delinquent_day29(): array { return $this->billScenarioFor(-29,  'unpaid'); }
    private function scn_bill_eviction_day30():   array { return $this->billScenarioFor(-30,  'unpaid'); }
    private function scn_bill_eviction_day45():   array { return $this->billScenarioFor(-45,  'unpaid'); }

    private function scn_bill_paid(): array
    {
        return $this->billScenarioFor(-10, 'paid', ['paid_at' => now()->subDays(2)]);
    }

    private function scn_bill_archived(): array
    {
        return $this->billScenarioFor(-90, 'archived');
    }

    private function scn_bill_penalty_override(): array
    {
        $c = $this->makeContract('BILL-OVR', now()->addMonths(6));
        $b = $this->makeBill($c, now()->subDays(20), 'overdue', ['penalty_amount' => 0.00, 'total_amount' => 3500.00]);
        return ['contract_id' => $c->id, 'bill_id' => $b->id, 'note' => 'Open in /admin/billing and apply override to verify branch'];
    }

    private function scn_bill_initial_payment(): array
    {
        $c = $this->makeContract('BILL-INIT', now()->addMonths(11));
        $ip = InitialPayment::create([
            'tenant_id'        => $this->tenant->id,
            'contract_id'      => $c->id,
            'deposit_amount'   => 3500.00,
            'first_month_rent' => 3500.00,
            'room_key_fee'     => 200.00,
            'amenities'        => [],
            'amenities_total'  => 0.00,
            'total_collected'  => 7200.00,
            'date_received'    => now()->subDay(),
            'payment_method'   => 'cash',
            'reference_number' => null,
            'recorded_by'      => $this->gm->id,
        ]);
        return ['contract_id' => $c->id, 'initial_payment_id' => $ip->id];
    }

    // ---------------------------------------------------------------------
    // SS4 Contracts
    // ---------------------------------------------------------------------

    private function scn_contract_draft():              array { return ['contract_id' => $this->makeContract('CTR-DRAFT', now()->addYear(), 'draft', ['activated_at' => null])->id]; }
    private function scn_contract_active_green():       array { return ['contract_id' => $this->makeContract('CTR-GRN',  now()->addDays(60))->id]; }
    private function scn_contract_active_amber_30():    array { return ['contract_id' => $this->makeContract('CTR-A30',  now()->addDays(30))->id]; }
    private function scn_contract_active_amber_15():    array { return ['contract_id' => $this->makeContract('CTR-A15',  now()->addDays(15))->id]; }
    private function scn_contract_active_red_7():       array { return ['contract_id' => $this->makeContract('CTR-R7',   now()->addDays(7))->id]; }
    private function scn_contract_active_red_1():       array { return ['contract_id' => $this->makeContract('CTR-R1',   now()->addDays(1))->id]; }

    private function scn_contract_expired_unarchived(): array
    {
        return ['contract_id' => $this->makeContract('CTR-EXP-U', now()->subDay(), 'active')->id];
    }

    private function scn_contract_expired_archived(): array
    {
        $c = $this->makeContract('CTR-EXP-A', now()->subDays(30), 'expired');
        $a = Archive::create([
            'original_record_id' => $c->id,
            'record_type'        => 'contract',
            'source_subsystem'   => 'SS4',
            'archive_reason'     => self::TAG . ' Auto-archived expired contract',
            'data'               => $c->toArray(),
            'archived_by'        => null,
            'restored'           => false,
        ]);
        return ['contract_id' => $c->id, 'archive_id' => $a->id];
    }

    private function scn_contract_terminated(): array
    {
        return ['contract_id' => $this->makeContract('CTR-TERM', now()->addMonths(3), 'terminated', [
            'terminated_at'      => now()->subWeek(),
            'termination_reason' => self::TAG . ' Tenant moved out early',
        ])->id];
    }

    private function scn_contract_step1_ack_only(): array
    {
        return ['contract_id' => $this->makeContract('CTR-S1', now()->addYear(), 'draft', [
            'activated_at'           => null,
            'step1_acknowledged_at'  => now()->subDay(),
        ])->id];
    }

    private function scn_contract_step2_ack_active(): array
    {
        return ['contract_id' => $this->makeContract('CTR-S2', now()->addYear(), 'active', [
            'step1_acknowledged_at'  => now()->subDay(),
            'step2_acknowledged_at'  => now()->subHours(2),
            'activated_at'           => now()->subHours(2),
        ])->id];
    }

    // ---------------------------------------------------------------------
    // SS5 Archives
    // ---------------------------------------------------------------------

    private function archiveOf(string $recordType, string $source, array $data, ?int $by = null, bool $restored = false): Archive
    {
        return Archive::create([
            'original_record_id' => $data['id'] ?? 0,
            'record_type'        => $recordType,
            'source_subsystem'   => $source,
            'archive_reason'     => self::TAG . " {$recordType} archived for testing",
            'data'               => $data,
            'archived_by'        => $by ?? $this->gm->id,
            'restored'           => $restored,
            'restored_at'        => $restored ? now() : null,
        ]);
    }

    private function scn_archive_room():     array { return ['archive_id' => $this->archiveOf('room',           'SS1', ['id' => 9999, 'room_number' => 'TB-X', 'status' => 'archived'])->id]; }
    private function scn_archive_tenant():   array { return ['archive_id' => $this->archiveOf('tenant_account', 'SS2', ['id' => 9999, 'email' => 'archived@testbed.local'])->id]; }
    private function scn_archive_contract(): array { return ['archive_id' => $this->archiveOf('contract',       'SS4', ['id' => 9999, 'status' => 'expired'])->id]; }
    private function scn_archive_bill():     array { return ['archive_id' => $this->archiveOf('payment',        'SS3', ['id' => 9999, 'status' => 'archived'])->id]; }
    private function scn_archive_restored(): array { return ['archive_id' => $this->archiveOf('tenant_account', 'SS2', ['id' => 9998, 'note' => 'restored row'], null, true)->id]; }

    // ---------------------------------------------------------------------
    // SS6 Auth
    // ---------------------------------------------------------------------

    private function scn_user_locked_active(): array
    {
        $u = $this->makeTenant('locked_active', [
            'failed_login_attempts' => 5,
            'locked_until'          => now()->addMinutes(15),
        ]);
        return ['user_id' => $u->id];
    }

    private function scn_user_locked_expired(): array
    {
        $u = $this->makeTenant('locked_expired', [
            'failed_login_attempts' => 5,
            'locked_until'          => now()->subMinute(),
        ]);
        return ['user_id' => $u->id];
    }

    private function scn_user_must_change_password(): array
    {
        $u = $this->makeTenant('mustchange', ['must_change_password' => true]);
        return ['user_id' => $u->id];
    }

    private function auditOf(string $action, string $details, ?int $userId = null): AuditLog
    {
        return AuditLog::create([
            'user_id'    => $userId ?? $this->gm->id,
            'user_type'  => 'gm',
            'action'     => $action,
            'subsystem'  => 'SS6',
            'details'    => self::TAG . ' ' . $details,
            'ip_address' => '127.0.0.1',
        ]);
    }

    private function scn_audit_login():            array { return ['audit_id' => $this->auditOf('login',          'Test login event')->id]; }
    private function scn_audit_failed_login():     array { return ['audit_id' => $this->auditOf('failed_login',   'Test failed login event')->id]; }
    private function scn_audit_password_change():  array { return ['audit_id' => $this->auditOf('password_change','Test password change')->id]; }
    private function scn_audit_settings_updated(): array { return ['audit_id' => $this->auditOf('settings_updated','session_timeout_minutes 15 → 20')->id]; }

    private function scn_setting_session_timeout(): array
    {
        $s = SystemSetting::updateOrCreate(
            ['key' => 'testbed.session_timeout_minutes'],
            ['value' => '20', 'updated_by' => $this->gm->id],
        );
        return ['setting_id' => $s->id];
    }

    // ---------------------------------------------------------------------
    // SS7 Communications
    // ---------------------------------------------------------------------

    private function makeRequest(string $type, string $status, array $overrides = []): TenantRequest
    {
        return TenantRequest::create(array_merge([
            'tenant_id' => $this->tenant->id,
            'type'      => $type,
            'subject'   => self::TAG . " {$type} {$status}",
            'body'      => 'Test ' . $type . ' body',
            'status'    => $status,
        ], $overrides));
    }

    private function scn_request_open():        array { return ['request_id' => $this->makeRequest('request',   'pending')->id]; }
    private function scn_request_in_progress(): array { return ['request_id' => $this->makeRequest('request',   'in_progress', ['admin_response' => 'Working on it.', 'responded_by' => $this->gm->id, 'responded_at' => now()->subHour()])->id]; }
    private function scn_request_resolved():    array { return ['request_id' => $this->makeRequest('request',   'resolved',    ['admin_response' => 'Fixed.',         'responded_by' => $this->gm->id, 'responded_at' => now()->subHours(3)])->id]; }
    private function scn_complaint_open():      array { return ['request_id' => $this->makeRequest('complaint', 'pending')->id]; }

    private function scn_announcement_broadcast(): array
    {
        $a = Announcement::create([
            'title'          => self::TAG . ' Broadcast',
            'body'           => 'Testbed broadcast announcement body',
            'recipient_type' => 'all',
            'recipient_id'   => null,
            'sent_by'        => $this->gm->id,
            'email_sent'     => true,
        ]);
        return ['announcement_id' => $a->id];
    }

    private function scn_announcement_direct(): array
    {
        $a = Announcement::create([
            'title'          => self::TAG . ' Direct',
            'body'           => 'Testbed direct announcement body',
            'recipient_type' => 'specific',
            'recipient_id'   => $this->tenant->id,
            'sent_by'        => $this->gm->id,
            'email_sent'     => true,
        ]);
        return ['announcement_id' => $a->id];
    }

    private function scn_notification_unread(): array
    {
        $n = NotificationLog::create([
            'user_id' => $this->tenant->id,
            'type'    => 'tenant_request_responded',
            'source'  => 'SS7',
            'message' => self::TAG . ' Your request has a new reply.',
            'is_read' => false,
        ]);
        return ['notification_id' => $n->id];
    }

    private function scn_notification_read(): array
    {
        $n = NotificationLog::create([
            'user_id' => $this->tenant->id,
            'type'    => 'announcement_sent',
            'source'  => 'SS7',
            'message' => self::TAG . ' Read announcement.',
            'is_read' => true,
        ]);
        return ['notification_id' => $n->id];
    }

    // ---------------------------------------------------------------------
    // Crons + reporting
    // ---------------------------------------------------------------------

    private function runCrons(): void
    {
        $this->newLine();
        $this->info('⏰ Running time-based crons…');
        Artisan::call('billing:apply-penalties');
        $this->line('  • billing:apply-penalties done');
        Artisan::call('contracts:send-warnings');
        $this->line('  • contracts:send-warnings done');
        Artisan::call('contracts:auto-archive');
        $this->line('  • contracts:auto-archive done');
    }

    private function printSummary(): void
    {
        $this->newLine();
        $this->info('📊 Summary');
        $rows = [];
        foreach ($this->manifest as $slug => $info) {
            $rows[] = [$info['subsystem'], $slug, $info['expects'], json_encode($info['ids'])];
        }
        $this->table(['SS', 'Slug', 'Expected', 'IDs'], $rows);
    }

    private function writeManifest(): void
    {
        $payload = [
            'generated_at' => now()->toIso8601String(),
            'anchors' => [
                'gm_email'     => $this->gm->email,
                'tenant_email' => $this->tenant->email,
                'room_number'  => $this->room->room_number,
            ],
            'scenarios' => $this->manifest,
        ];
        Storage::disk('local')->put('testbed-manifest.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
