<?php

namespace Tests\Unit;

use App\Livewire\Admin\Reports\ReportManager;
use App\Models\Archive;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SS5 — Reports & Archives (White-Box)
 *
 * Branch coverage of:
 *   - ReportManager::restore()        (update restored=true + audit row)
 *   - ReportManager::permanentDelete() (audit BEFORE delete, then delete)
 *   - render() filter chain branches  (each `when` clause)
 *   - Archive::scopeNotRestored        (excludes restored=true rows)
 *
 * IDs: WBT_SS5_REP_001..007
 */
class SS5_ReportsWhiteBoxTest extends TestCase
{
    use RefreshDatabase;

    protected User $gm;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gm = User::create([
            'full_name' => 'GM', 'email' => 'gm@x.com', 'password' => Hash::make('p'),
            'role' => 'gm', 'status' => 'active',
        ]);
    }

    /* ── restore() ─────────────────────────────────── */

    #[Test] // WBT_SS5_REP_001 — restore() flips restored=true AND sets restored_at
    public function restore_sets_both_flag_and_timestamp(): void
    {
        $a = $this->makeArchive();

        Livewire::actingAs($this->gm)->test(ReportManager::class)
            ->call('restore', $a->id);

        $a->refresh();
        $this->assertTrue($a->restored);
        $this->assertNotNull($a->restored_at);
    }

    #[Test] // WBT_SS5_REP_002 — restore() writes an audit log entry
    public function restore_writes_audit_log(): void
    {
        $a = $this->makeArchive();

        Livewire::actingAs($this->gm)->test(ReportManager::class)
            ->call('restore', $a->id);

        $this->assertDatabaseHas('audit_logs', [
            'action'    => 'record_restored',
            'subsystem' => 'SS5',
        ]);
    }

    /* ── permanentDelete() ─────────────────────────── */

    #[Test] // WBT_SS5_REP_003 — permanentDelete: audit row created BEFORE delete (so it persists)
    public function permanent_delete_audits_before_deleting(): void
    {
        $a = $this->makeArchive();

        Livewire::actingAs($this->gm)->test(ReportManager::class)
            ->call('permanentDelete', $a->id);

        // Audit row should still exist after the archive row is gone
        $this->assertDatabaseMissing('archives', ['id' => $a->id]);
        $this->assertDatabaseHas('audit_logs', [
            'action'    => 'record_permanently_deleted',
            'subsystem' => 'SS5',
        ]);
    }

    /* ── render() filter branches ──────────────────── */

    #[Test] // WBT_SS5_REP_004 — render(): no filters → all (non-restored) returned
    public function render_with_no_filters_returns_all_non_restored(): void
    {
        $this->makeArchive(['archive_reason' => 'Visible']);
        $this->makeArchive(['restored' => true, 'archive_reason' => 'Hidden']);

        Livewire::actingAs($this->gm)->test(ReportManager::class)
            ->assertSee('Visible')
            ->assertDontSee('Hidden');
    }

    #[Test] // WBT_SS5_REP_005 — render(): dateFrom branch filters out earlier rows
    public function render_with_dateFrom_excludes_earlier_archives(): void
    {
        $old    = $this->makeArchive(['archive_reason' => 'PreFilter-Reason-XYZ']);
        $recent = $this->makeArchive(['archive_reason' => 'PostFilter-Reason-XYZ']);
        $this->backdate($old, now()->subDays(10));
        $this->backdate($recent, now()->subDays(1));

        Livewire::actingAs($this->gm)->test(ReportManager::class)
            ->set('dateFrom', now()->subDays(5)->toDateString())
            ->assertSee('PostFilter-Reason-XYZ')
            ->assertDontSee('PreFilter-Reason-XYZ');
    }

    #[Test] // WBT_SS5_REP_006 — render(): dateTo branch filters out later rows
    public function render_with_dateTo_excludes_later_archives(): void
    {
        $early = $this->makeArchive(['archive_reason' => 'BeforeCutoff-Reason-XYZ']);
        $late  = $this->makeArchive(['archive_reason' => 'AfterCutoff-Reason-XYZ']);
        $this->backdate($early, now()->subDays(10));
        $this->backdate($late, now()->subDays(1));

        Livewire::actingAs($this->gm)->test(ReportManager::class)
            ->set('dateTo', now()->subDays(5)->toDateString())
            ->assertSee('BeforeCutoff-Reason-XYZ')
            ->assertDontSee('AfterCutoff-Reason-XYZ');
    }

    /**
     * Bypass Eloquent to set a historical created_at — Eloquent's auto-timestamping
     * overrides any created_at passed to Model::create().
     */
    private function backdate(Archive $a, \Illuminate\Support\Carbon $when): void
    {
        \Illuminate\Support\Facades\DB::table('archives')
            ->where('id', $a->id)
            ->update(['created_at' => $when]);
    }

    /* ── scopeNotRestored ──────────────────────────── */

    #[Test] // WBT_SS5_REP_007 — scopeNotRestored: only restored=false rows returned
    public function not_restored_scope_excludes_restored_rows(): void
    {
        $this->makeArchive(['restored' => false, 'archive_reason' => 'A']);
        $this->makeArchive(['restored' => true,  'archive_reason' => 'B']);

        $rows = Archive::notRestored()->get();
        $this->assertCount(1, $rows);
        $this->assertSame('A', $rows->first()->archive_reason);
    }

    private function makeArchive(array $overrides = []): Archive
    {
        return Archive::create(array_merge([
            'original_record_id' => random_int(1, 9999),
            'record_type'        => 'room',
            'source_subsystem'   => 'SS1',
            'archive_reason'     => 'Test reason',
            'data'               => [],
            'archived_by'        => $this->gm->id,
            'restored'           => false,
        ], $overrides));
    }
}
