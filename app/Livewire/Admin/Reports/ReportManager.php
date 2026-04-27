<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Archive;
use App\Models\AuditLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Reports & Archives — Citiescapes')]
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

    public function render()
    {
        $archives = Archive::query()
            ->when($this->filterType, fn($q) => $q->where('record_type', $this->filterType))
            ->when($this->filterSubsystem, fn($q) => $q->where('source_subsystem', $this->filterSubsystem))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->notRestored()
            ->latest()
            ->paginate(15);

        return view('livewire.admin.reports.report-manager', compact('archives'));
    }
}
