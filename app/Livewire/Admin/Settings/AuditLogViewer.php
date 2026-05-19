<?php

namespace App\Livewire\Admin\Settings;

use App\Models\AuditLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
#[Title('Audit Log — Citiescapes')]
class AuditLogViewer extends Component
{
    use WithPagination;

    public string $filterAction = '';
    public string $filterSubsystem = '';
    public string $search = '';

    private function filteredQuery()
    {
        return AuditLog::with('user')
            ->when($this->filterAction, fn($q) => $q->where('action', $this->filterAction))
            ->when($this->filterSubsystem, fn($q) => $q->where('subsystem', $this->filterSubsystem))
            ->when($this->search, function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($qq) use ($term) {
                    $qq->where('details', 'like', $term)
                       ->orWhere('action', 'like', $term)
                       ->orWhere('subsystem', 'like', $term)
                       ->orWhere('ip_address', 'like', $term)
                       ->orWhere('user_type', 'like', $term)
                       ->orWhereHas('user', fn($u) => $u->where('full_name', 'like', $term)->orWhere('email', 'like', $term));
                });
            })
            ->latest();
    }

    /**
     * CSV export of the currently filtered audit log.
     * Streams as a download — large logs do not buffer in memory.
     * The export action is itself audit-logged as audit_log_exported (SS6).
     */
    public function exportCsv(): StreamedResponse
    {
        $rows = $this->filteredQuery()->get();
        $filename = 'audit_log_' . now()->format('Ymd_His') . '.csv';

        AuditLog::record('audit_log_exported', auth()->id(), 'gm', 'SS6',
            "Exported {$rows->count()} audit log row(s) to CSV");

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            // BOM so Excel opens UTF-8 correctly
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'ID', 'Timestamp', 'Subsystem', 'Action', 'Actor', 'Role', 'IP', 'Details',
            ]);
            foreach ($rows as $log) {
                fputcsv($out, [
                    $log->id,
                    $log->created_at?->format('Y-m-d H:i:s'),
                    $log->subsystem,
                    $log->action,
                    optional($log->user)->full_name ?? 'system',
                    $log->user_type ?? '',
                    $log->ip_address ?? '',
                    $log->details ?? '',
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function render()
    {
        $logs = $this->filteredQuery()->paginate(25);

        $actions = AuditLog::select('action')->distinct()->pluck('action');

        return view('livewire.admin.settings.audit-log', compact('logs', 'actions'));
    }
}
