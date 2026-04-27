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

    public function mount()
    {
        $this->contract = Contract::with('room')
            ->where('tenant_id', auth()->id())
            ->whereIn('status', ['draft', 'active'])
            ->latest()
            ->first();
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

        // Link room to tenant
        $this->contract->room->update([
            'current_tenant_id'  => auth()->id(),
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
