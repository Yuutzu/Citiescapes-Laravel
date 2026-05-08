<?php

namespace App\Livewire\Admin\Billing;

use App\Models\AuditLog;
use App\Models\Bill;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\PenaltyOverride;
use App\Models\Archive;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Billing Management — Citiescapes')]
class BillingManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';

    // Sorting
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

    public function sortBy($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    // Generate bill modal
    public bool $showGenerate = false;
    public ?int $genContractId = null;
    public float $genElectricity = 0;
    public float $genWater = 0;
    public float $genWifi = 0;
    public float $genExtras = 0;
    public string $genExtrasNote = '';

    // Record initial fees modal
    public bool $showInitial = false;
    public ?int $initContractId = null;
    public float $initDeposit = 0;
    public float $initFirstMonth = 0;
    public float $initKeyFee = 0;

    // Confirm payment modal
    public bool $showPayment = false;
    public ?int $payBillId = null;
    public float $payAmount = 0;
    public string $payMethod = 'cash';
    public string $payReference = '';

    // Override penalty modal
    public bool $showOverride = false;
    public ?int $overrideBillId = null;
    public float $overrideAmount = 0;
    public string $overrideReason = '';

    public function openGenerate()
    {
        $this->reset(['genContractId', 'genElectricity', 'genWater', 'genWifi', 'genExtras', 'genExtrasNote']);
        $this->showGenerate = true;
    }

    public function generateBill()
    {
        $this->validate([
            'genContractId' => 'required|exists:contracts,id',
            'genElectricity' => 'required|numeric|min:0',
            'genWater' => 'required|numeric|min:0',
            'genWifi' => 'required|numeric|min:0',
            'genExtras' => 'required|numeric|min:0',
        ]);

        $contract = Contract::with('room')->findOrFail($this->genContractId);
        $period = now()->format('Y-m');
        $utilities = $this->genElectricity + $this->genWater + $this->genWifi + $this->genExtras;
        $total = $contract->base_rent_rate + $utilities;

        Bill::create([
            'tenant_id' => $contract->tenant_id,
            'contract_id' => $contract->id,
            'room_id' => $contract->room_id,
            'type' => 'monthly',
            'billing_period' => $period,
            'base_rent' => $contract->base_rent_rate,
            'utilities' => $utilities,
            'electricity' => $this->genElectricity,
            'water' => $this->genWater,
            'wifi' => $this->genWifi,
            'extras' => $this->genExtras,
            'extras_note' => $this->genExtrasNote ?: null,
            'total_amount' => $total,
            'due_date' => now()->endOfMonth(),
        ]);

        AuditLog::record('bill_generated', auth()->id(), 'gm', 'SS3', "Monthly bill for contract #{$contract->id}, period {$period}");
        $this->showGenerate = false;
        session()->flash('success', 'Monthly bill generated.');
    }

    public function openInitial()
    {
        $this->reset(['initContractId', 'initDeposit', 'initFirstMonth', 'initKeyFee']);
        $this->showInitial = true;
    }

    public function recordInitial()
    {
        $this->validate([
            'initContractId' => 'required|exists:contracts,id',
            'initDeposit' => 'required|numeric|min:0',
            'initFirstMonth' => 'required|numeric|min:0',
            'initKeyFee' => 'required|numeric|min:0',
        ]);

        $contract = Contract::findOrFail($this->initContractId);
        $total = $this->initDeposit + $this->initFirstMonth + $this->initKeyFee;

        Bill::create([
            'tenant_id' => $contract->tenant_id,
            'contract_id' => $contract->id,
            'room_id' => $contract->room_id,
            'type' => 'initial',
            'billing_period' => 'initial',
            'deposit_amount' => $this->initDeposit,
            'base_rent' => $this->initFirstMonth,
            'room_key_fee' => $this->initKeyFee,
            'total_amount' => $total,
            'due_date' => now()->addDays(3),
        ]);

        AuditLog::record('initial_fees_recorded', auth()->id(), 'gm', 'SS3', "Initial fees for contract #{$contract->id}");
        $this->showInitial = false;
        session()->flash('success', 'Initial fees recorded.');
    }

    public function openPayment(int $billId)
    {
        $bill = Bill::findOrFail($billId);
        $this->payBillId = $billId;
        $this->payAmount = (float) $bill->total_amount;
        $this->payMethod = 'cash';
        $this->payReference = '';
        $this->showPayment = true;
    }

    public function confirmPayment()
    {
        $this->validate([
            'payAmount' => 'required|numeric|min:0.01',
            'payMethod' => 'required',
        ]);

        $bill = Bill::findOrFail($this->payBillId);

        Payment::create([
            'bill_id' => $bill->id,
            'tenant_id' => $bill->tenant_id,
            'amount' => $this->payAmount,
            'payment_method' => $this->payMethod,
            'reference_number' => $this->payReference,
            'confirmed_by' => auth()->id(),
            'confirmed_at' => now(),
        ]);

        $bill->update(['status' => 'paid', 'paid_at' => now(), 'days_overdue' => 0]);
        AuditLog::record('payment_confirmed', auth()->id(), 'gm', 'SS3', "Payment of ₱{$this->payAmount} for bill #{$bill->id}");

        $this->showPayment = false;
        session()->flash('success', 'Payment confirmed.');
    }

    public function openOverride(int $billId)
    {
        $bill = Bill::findOrFail($billId);
        $this->overrideBillId = $billId;
        $this->overrideAmount = 0;
        $this->overrideReason = '';
        $this->showOverride = true;
    }

    public function saveOverride()
    {
        $this->validate([
            'overrideAmount' => 'required|numeric|min:0',
            'overrideReason' => 'required|max:500',
        ]);

        $bill = Bill::findOrFail($this->overrideBillId);
        $originalPenalty = $bill->penalty_amount;

        PenaltyOverride::create([
            'bill_id' => $bill->id,
            'overridden_by' => auth()->id(),
            'original_penalty' => $originalPenalty,
            'adjusted_penalty' => $this->overrideAmount,
            'reason' => $this->overrideReason,
        ]);

        $bill->update([
            'penalty_amount' => $this->overrideAmount,
            'total_amount' => $bill->base_rent + $bill->utilities + $this->overrideAmount,
        ]);

        AuditLog::record('penalty_override', auth()->id(), 'gm', 'SS3', "Penalty on bill #{$bill->id}: ₱{$originalPenalty} → ₱{$this->overrideAmount}. Reason: {$this->overrideReason}");
        $this->showOverride = false;
        session()->flash('success', 'Penalty overridden.');
    }

    public function archiveBills(int $tenantId)
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Bill> $bills */
        $bills = Bill::where('tenant_id', $tenantId)->get();
        foreach ($bills as $bill) {
            /** @var Bill $bill */
            Archive::create([
                'original_record_id' => $bill->id,
                'record_type' => 'payment',
                'source_subsystem' => 'SS3',
                'archive_reason' => 'Lease ended — GM archived',
                'data' => $bill->toArray(),
                'archived_by' => auth()->id(),
            ]);
            $bill->update(['status' => 'archived']);
        }
        session()->flash('success', 'Payment records archived.');
    }

    public function render()
    {
        $bills = Bill::with(['tenant', 'room', 'contract'])
            ->leftJoin('users', 'bills.tenant_id', '=', 'users.id')
            ->when($this->search, fn($q) => $q->where('users.full_name', 'like', "%{$this->search}%"))
            ->when($this->filterStatus, fn($q) => $q->where('bills.status', $this->filterStatus))
            ->when($this->sortBy === 'tenant_name', fn($q) => $q->orderBy('users.full_name', $this->sortDirection))
            ->when($this->sortBy !== 'tenant_name', fn($q) => $q->orderBy("bills.{$this->sortBy}", $this->sortDirection))
            ->select('bills.*')
            ->paginate(15);

        $activeContracts = Contract::active()->with('tenant', 'room')->get();

        return view('livewire.admin.billing.billing-manager', compact('bills', 'activeContracts'));
    }
}
