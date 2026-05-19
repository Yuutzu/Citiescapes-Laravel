<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Archive;
use App\Models\AuditLog;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
#[Title('Archive — Citiescapes')]
class ReportManager extends Component
{
    use WithPagination;

    public string $filterType = '';
    public string $filterSubsystem = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function restore(int $id)
    {
        $archive = Archive::findOrFail($id);

        if ($archive->record_type === 'tenant_account') {
            $user = User::find($archive->original_record_id);
            if ($user) {
                $user->update([
                    'status'      => 'active',
                    'archived_at' => null,
                    'archived_by' => null,
                ]);
            }
        }

        $archive->update(['restored' => true, 'restored_at' => now()]);
        AuditLog::record('record_restored', auth()->id(), 'gm', 'SS5', "Restored {$archive->record_type} #{$archive->original_record_id}");
        session()->flash('success', 'Record restored.');
    }

    public function permanentDelete(int $id)
    {
        $archive = Archive::findOrFail($id);
        AuditLog::record('record_permanently_deleted', auth()->id(), 'gm', 'SS5', "Permanently deleted {$archive->record_type} #{$archive->original_record_id}");
        $archive->delete();
        session()->flash('success', 'Record permanently deleted.');
    }

    /**
     * Build the filtered, non-restored archive query (shared by table + exports).
     */
    private function filteredQuery()
    {
        return Archive::query()
            ->with('archivedByUser')
            ->when($this->filterType, fn($q) => $q->where('record_type', $this->filterType))
            ->when($this->filterSubsystem, fn($q) => $q->where('source_subsystem', $this->filterSubsystem))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->notRestored()
            ->latest();
    }

    /**
     * CSV export of the currently filtered archive list.
     * Streams as a download — large lists do not buffer in memory.
     */
    public function exportCsv(): StreamedResponse
    {
        $rows = $this->filteredQuery()->get();
        $filename = 'archive_' . now()->format('Ymd_His') . '.csv';

        AuditLog::record('archive_exported_csv', auth()->id(), 'gm', 'SS5',
            "Exported {$rows->count()} archive row(s) to CSV");

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            // BOM so Excel opens UTF-8 correctly
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'Archive ID', 'Original ID', 'Record Type', 'Source Subsystem',
                'Archive Reason', 'Has Scan', 'Archived By', 'Archived At',
            ]);
            foreach ($rows as $a) {
                fputcsv($out, [
                    $a->id,
                    $a->original_record_id,
                    $a->record_type,
                    $a->source_subsystem,
                    $a->archive_reason,
                    $a->scan_file_path ? 'yes' : 'no',
                    optional($a->archivedByUser)->full_name ?? 'system',
                    $a->created_at?->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Generate a printable PDF for one archived contract.
     * Includes the contract data payload + a reference / link to the
     * stored scan (the GM can still open the scan separately).
     */
    public function exportContractPdf(int $id)
    {
        $archive = Archive::with('archivedByUser')->findOrFail($id);

        if ($archive->record_type !== 'contract') {
            session()->flash('error', 'PDF export is only available for archived contracts.');
            return null;
        }

        $scanUrl = $archive->scan_file_path
            ? asset('storage/' . $archive->scan_file_path)
            : null;

        $pdf = Pdf::loadView('pdf.archived-contract', [
            'archive' => $archive,
            'data'    => $archive->data ?? [],
            'scanUrl' => $scanUrl,
        ])->setPaper('a4');

        $filename = "archived_contract_{$archive->original_record_id}.pdf";

        AuditLog::record('archive_contract_pdf', auth()->id(), 'gm', 'SS5',
            "Downloaded PDF for archived contract #{$archive->original_record_id}");

        $output = $pdf->output();

        return response()->streamDownload(
            function () use ($output) {
                echo $output;
            },
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    public function render()
    {
        $archives = $this->filteredQuery()->paginate(15);

        return view('livewire.admin.reports.report-manager', compact('archives'));
    }
}
