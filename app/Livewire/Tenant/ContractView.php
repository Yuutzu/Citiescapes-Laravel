<?php

namespace App\Livewire\Tenant;

use App\Models\AuditLog;
use App\Models\Contract;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('My Contract — Citiescapes')]
class ContractView extends Component
{
    public ?Contract $contract = null;

    /**
     * Number of days an approved scan-view grant stays active before the
     * system auto-revokes it. Tenant must request again to view past this.
     * Kept in code (not config) since the value is part of the documented
     * SS4 use case.
     */
    public const SCAN_VIEW_WINDOW_DAYS = 7;

    public function mount()
    {
        $this->contract = Contract::with('room')
            ->where('tenant_id', auth()->id())
            ->whereIn('status', ['draft', 'active'])
            ->latest()
            ->first();

        $this->autoRevokeScanIfExpired();
    }

    /**
     * Lazy auto-revoke: if the scan-view approval is older than the configured
     * window (default 7 days), flip its status to 'revoked', clear the decision
     * timestamp, and emit an audit row. Runs on every page load — no cron
     * dependency. Idempotent: a second visit after revoke is a no-op.
     */
    private function autoRevokeScanIfExpired(): void
    {
        if (!$this->contract) return;
        if ($this->contract->scan_view_status !== 'approved') return;
        if (!$this->contract->scan_view_decided_at) return;

        $expiresAt = $this->contract->scan_view_decided_at->copy()->addDays(self::SCAN_VIEW_WINDOW_DAYS);
        if (now()->lt($expiresAt)) return; // still inside the window

        $this->contract->update([
            'scan_view_status'        => 'revoked',
            'scan_view_decision_note' => 'Auto-revoked after ' . self::SCAN_VIEW_WINDOW_DAYS . '-day access window expired.',
        ]);
        AuditLog::record('contract_scan_view_auto_revoked', auth()->id(), 'system', 'SS4',
            "Contract #{$this->contract->id} — scan access auto-revoked after " . self::SCAN_VIEW_WINDOW_DAYS . '-day window expired');
        $this->contract->refresh();
    }

    /**
     * Tenant requests GM approval to view the signed contract scan.
     */
    public function requestScanAccess(): void
    {
        if (!$this->contract || !$this->contract->scan_file_path) return;
        if (in_array($this->contract->scan_view_status, ['pending', 'approved'], true)) return;

        $this->contract->update([
            'scan_view_status'        => 'pending',
            'scan_view_requested_at'  => now(),
            'scan_view_decided_at'    => null,
            'scan_view_decided_by'    => null,
            'scan_view_decision_note' => null,
        ]);
        AuditLog::record('contract_scan_view_requested', auth()->id(), 'tenant', 'SS4', "Contract #{$this->contract->id} — tenant requested scan access");
        $this->contract->refresh();
        session()->flash('success', 'Request sent to the General Manager. You will see the scan here once it is approved.');
    }

    /**
     * Step 1: Acknowledge terms read.
     */
    public function acknowledgeStep1()
    {
        if (!$this->contract || $this->contract->step1_acknowledged_at) return;

        $this->contract->update(['step1_acknowledged_at' => now()]);
        AuditLog::record('contract_step1_ack', auth()->id(), 'tenant', 'SS4', "Contract #{$this->contract->id}");
        $this->contract->refresh();
    }

    /**
     * Step 2: Accept penalty clause → activates contract + triggers billing.
     */
    public function acknowledgeStep2()
    {
        if (!$this->contract || !$this->contract->step1_acknowledged_at || $this->contract->step2_acknowledged_at) return;

        $this->contract->update([
            'step2_acknowledged_at' => now(),
            'status'                => 'active',
            'activated_at'          => now(),
        ]);

        // Link room to tenant AND flip its status to occupied. Both fields
        // must move together — see ContractManager::activate() for the parallel
        // GM-side flow. Leaving status='available' here is what causes the room
        // table to show a tenant linked but the row still flagged as vacant.
        $this->contract->room->update([
            'status'             => 'occupied',
            'current_tenant_id'  => auth()->id(),
            'last_status_update' => now(),
        ]);

        AuditLog::record('contract_step2_ack', auth()->id(), 'tenant', 'SS4', "Contract #{$this->contract->id} activated");
        $this->contract->refresh();

        session()->flash('success', 'Contract activated! Your billing cycle has begun.');
    }

    public function render()
    {
        return view('livewire.tenant.contract-view');
    }
}
