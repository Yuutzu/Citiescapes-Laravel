<?php

namespace Tests\Feature;

use App\Livewire\Admin\Reports\ReportManager;
use App\Models\Archive;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SS5 — Reports & Archive (Extended Black-Box)
 *
 * Each export method audits before returning. We assert on those audit side-effects
 * since Livewire's testable does not serialize StreamedResponse objects into the
 * effects payload (you can't `assertReturned()` a stream).
 */
class SS5_ReportsExtendedTest extends TestCase
{
    use RefreshDatabase;

    protected User $gm;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gm = User::create([
            'full_name' => 'GM', 'email' => 'gm@x.com',
            'password' => Hash::make('p'), 'role' => 'gm',
            'status' => 'active', 'activated_at' => now(),
        ]);
    }

    private function makeArchive(array $overrides = []): Archive
    {
        return Archive::create(array_merge([
            'original_record_id' => 1,
            'record_type'        => 'room',
            'source_subsystem'   => 'SS1',
            'archive_reason'     => 'test',
            'data'               => ['k' => 'v'],
            'archived_by'        => $this->gm->id,
        ], $overrides));
    }

    /* ── Date range filter ─────────────────────────── */

    #[Test] // BBT_SS5_FILTER_DATE
    public function date_range_filter_includes_only_in_range_archives(): void
    {
        $old = $this->makeArchive(['original_record_id' => 10]);
        $old->created_at = '2026-04-15 10:00:00';
        $old->save();

        $in = $this->makeArchive(['original_record_id' => 20]);
        $in->created_at = '2026-05-10 10:00:00';
        $in->save();

        $future = $this->makeArchive(['original_record_id' => 30]);
        $future->created_at = '2026-06-01 10:00:00';
        $future->save();

        Livewire::actingAs($this->gm)->test(ReportManager::class)
            ->set('dateFrom', '2026-05-01')
            ->set('dateTo', '2026-05-31')
            ->assertSee('#20')
            ->assertDontSee('#10')
            ->assertDontSee('#30');
    }

    /* ── CSV export ────────────────────────────────── */

    #[Test] // BBT_SS5_EXPORT_CSV — audit row proves the export ran
    public function csv_export_audits_archive_exported_csv(): void
    {
        $this->makeArchive(['original_record_id' => 100]);
        $this->makeArchive(['original_record_id' => 101]);

        Livewire::actingAs($this->gm)->test(ReportManager::class)
            ->call('exportCsv');

        $log = \App\Models\AuditLog::where('action', 'archive_exported_csv')->latest()->first();
        $this->assertNotNull($log);
        $this->assertSame('SS5', $log->subsystem);
        $this->assertStringContainsString('2 archive', (string) $log->details);
    }

    #[Test] // BBT_SS5_EXPORT_CSV_EMPTY — zero matched rows still audits with count=0
    public function csv_export_with_zero_rows_still_audits(): void
    {
        Livewire::actingAs($this->gm)->test(ReportManager::class)
            ->set('filterType', 'contract')   // none exist
            ->call('exportCsv');

        $log = \App\Models\AuditLog::where('action', 'archive_exported_csv')->latest()->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('0 archive', (string) $log->details);
    }

    /* ── XLSX export ───────────────────────────────── */

    #[Test] // BBT_SS5_EXPORT_XLSX
    public function xlsx_export_audits_archive_exported_xlsx(): void
    {
        $this->makeArchive();

        Livewire::actingAs($this->gm)->test(ReportManager::class)
            ->call('exportXlsx');

        $this->assertDatabaseHas('audit_logs', [
            'action'    => 'archive_exported_xlsx',
            'subsystem' => 'SS5',
        ]);
    }

    /* ── Contract PDF export ───────────────────────── */

    #[Test] // BBT_SS5_EXPORT_PDF_CONTRACT
    public function pdf_export_for_contract_archive_audits_archive_contract_pdf(): void
    {
        $archive = $this->makeArchive([
            'record_type'        => 'contract',
            'source_subsystem'   => 'SS4',
            'original_record_id' => 7,
            'data'               => ['tenant_id' => 5, 'room_id' => 3, 'status' => 'expired'],
        ]);

        Livewire::actingAs($this->gm)->test(ReportManager::class)
            ->call('exportContractPdf', $archive->id);

        $this->assertDatabaseHas('audit_logs', [
            'action'    => 'archive_contract_pdf',
            'subsystem' => 'SS5',
        ]);
    }

    #[Test] // BBT_SS5_EXPORT_PDF_NOT_CONTRACT — rejected, no audit row written
    public function pdf_export_for_non_contract_archive_does_not_audit(): void
    {
        $archive = $this->makeArchive(['record_type' => 'room']);

        Livewire::actingAs($this->gm)->test(ReportManager::class)
            ->call('exportContractPdf', $archive->id);

        $this->assertDatabaseMissing('audit_logs', [
            'action' => 'archive_contract_pdf',
        ]);
    }
}
