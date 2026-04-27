<?php

namespace App\Livewire\Admin\Settings;

use App\Models\AuditLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Audit Log — Citiescapes')]
class AuditLogViewer extends Component
{
    use WithPagination;

    public string $filterAction = '';
    public string $filterSubsystem = '';
    public string $search = '';

    public function render()
    {
        $logs = AuditLog::with('user')
            ->when($this->filterAction, fn($q) => $q->where('action', $this->filterAction))
            ->when($this->filterSubsystem, fn($q) => $q->where('subsystem', $this->filterSubsystem))
            ->when($this->search, fn($q) => $q->where(fn($qq) =>
                $qq->where('details', 'like', "%{$this->search}%")
                   ->orWhereHas('user', fn($u) => $u->where('full_name', 'like', "%{$this->search}%"))
            ))
            ->latest()
            ->paginate(25);

        $actions = AuditLog::select('action')->distinct()->pluck('action');

        return view('livewire.admin.settings.audit-log', compact('logs', 'actions'));
    }
}
