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
 * SS5 — Reports & Archives (Black-Box)
 *
 * Tests ReportManager filters, restore, and permanent-delete actions.
 * Technique: Equivalence Partitioning (EP) over filter dimensions + restore/delete state changes.
 *
 * IDs: BBT_SS5_REP_001..006
 */
class SS5_ReportsManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $gm;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gm = User::create([
            'full_name' => 'GM', 'email' => 'gm@x.com', 'password' => Hash::make('p'),
            'role' => 'gm', 'status' => 'active', 'activated_at' => now(),
        ]);
    }

    #[Test] // BBT_SS5_REP_001 — EP: no filter shows all (non-restored) archives
    public function unfiltered_render_lists_all_archives(): void
    {
        $this->makeArchive('room', 'SS1', 'Reason A');
        $this->makeArchive('contract', 'SS4', 'Reason B');

        Livewire::actingAs($this->gm)->test(ReportManager::class)
            ->assertSee('Reason A')
            ->assertSee('Reason B');
    }

    #[Test] // BBT_SS5_REP_002 — EP: filterType narrows to record_type
    public function filter_by_record_type_narrows_results(): void
    {
        $this->makeArchive('room', 'SS1', 'Room reason');
        $this->makeArchive('contract', 'SS4', 'Contract reason');

        Livewire::actingAs($this->gm)->test(ReportManager::class)
            ->set('filterType', 'room')
            ->assertSee('Room reason')
            ->assertDontSee('Contract reason');
    }

    #[Test] // BBT_SS5_REP_003 — EP: filterSubsystem narrows to source_subsystem
    public function filter_by_subsystem_narrows_results(): void
    {
        $this->makeArchive('room', 'SS1', 'From SS1');
        $this->makeArchive('contract', 'SS4', 'From SS4');

        Livewire::actingAs($this->gm)->test(ReportManager::class)
            ->set('filterSubsystem', 'SS4')
            ->assertSee('From SS4')
            ->assertDontSee('From SS1');
    }

    #[Test] // BBT_SS5_REP_004 — EP: restore marks archive restored=true (hidden from default view)
    public function restore_marks_archive_restored_and_hides_it(): void
    {
        $a = $this->makeArchive('room', 'SS1', 'To Restore');

        Livewire::actingAs($this->gm)->test(ReportManager::class)
            ->call('restore', $a->id);

        $a->refresh();
        $this->assertTrue($a->restored);
        $this->assertNotNull($a->restored_at);

        // After restore, default render() (uses notRestored scope) should not show it
        Livewire::actingAs($this->gm)->test(ReportManager::class)
            ->assertDontSee('To Restore');
    }

    #[Test] // BBT_SS5_REP_005 — EP: permanentDelete removes the row
    public function permanent_delete_removes_row(): void
    {
        $a = $this->makeArchive('room', 'SS1', 'To Delete');

        Livewire::actingAs($this->gm)->test(ReportManager::class)
            ->call('permanentDelete', $a->id);

        $this->assertDatabaseMissing('archives', ['id' => $a->id]);
    }

    #[Test] // BBT_SS5_REP_006 — EP: combined filters narrow further
    public function combined_filters_narrow_results_further(): void
    {
        $this->makeArchive('room', 'SS1', 'AlphaReason-Room-SS1');
        $this->makeArchive('room', 'SS4', 'BetaReason-Room-SS4');
        $this->makeArchive('contract', 'SS4', 'GammaReason-Contract-SS4');

        Livewire::actingAs($this->gm)->test(ReportManager::class)
            ->set('filterType', 'room')
            ->set('filterSubsystem', 'SS4')
            ->assertSee('BetaReason-Room-SS4')
            ->assertDontSee('AlphaReason-Room-SS1')
            ->assertDontSee('GammaReason-Contract-SS4');
    }

    private function makeArchive(string $type, string $ss, string $reason): Archive
    {
        return Archive::create([
            'original_record_id' => random_int(1, 9999),
            'record_type'        => $type,
            'source_subsystem'   => $ss,
            'archive_reason'     => $reason,
            'data'               => [],
            'archived_by'        => $this->gm->id,
            'restored'           => false,
        ]);
    }
}
