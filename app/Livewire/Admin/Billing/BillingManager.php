<?php

namespace App\Livewire\Admin\Billing;

use App\Models\AuditLog;
use App\Models\Bill;
use App\Models\Contract;
use App\Models\InitialPayment;
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

    // Record initial payment modal — amounts auto-fill from contract (SS4)
    public bool $showInitial = false;
    public ?int $initContractId = null;
    public string $initDateReceived = '';
    public string $initPaymentMethod = 'cash';
    public string $initReferenceNumber = '';

    /** @var array<int, array{name: string, fee: float|string}> */
    public array $initAmenities = [];

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
        $this->reset(['genContractId', 'genElectricity', 'genWater', 'genWifi']);
        $this->showGenerate = true;
    }

    public function generateBill()
    {
        $this->validate([
            'genContractId' => 'required|exists:contracts,id',
            'genElectricity' => 'required|numeric|min:0',
            'genWater' => 'required|numeric|min:0',
            'genWifi' => 'required|numeric|min:0',
        ]);

        $contract = Contract::with('room')->findOrFail($this->genContractId);
        $period = now()->format('Y-m');
        $utilities = $this->genElectricity + $this->genWater + $this->genWifi;
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
            'total_amount' => $total,
            'due_date' => now()->endOfMonth(),
        ]);

        AuditLog::record('bill_generated', auth()->id(), 'gm', 'SS3', "Monthly bill for contract #{$contract->id}, period {$period}");
        $this->showGenerate = false;
        session()->flash('success', 'Monthly bill generated.');
    }

    public function openInitial()
    {
        $this->reset(['initContractId', 'initPaymentMethod', 'initReferenceNumber', 'initAmenities']);
        $this->initDateReceived = now()->toDateString();
        $this->initPaymentMethod = 'cash';
        $this->showInitial = true;
    }

    /**
     * Selected contract for the initial-payment modal.
     * The view reads amounts from this object — fields are read-only.
     */
    public function getInitContractProperty(): ?Contract
    {
        return $this->initContractId ? Contract::find($this->initContractId) : null;
    }

    /**
     * When the GM picks a contract, prefill the amenities list with whatever
     * was captured on the contract (SS4). The GM can still add/remove rows.
     */
    public function updatedInitContractId($value): void
    {
        $contract = $value ? Contract::find($value) : null;
        $this->initAmenities = $contract?->requested_amenities ?? [];
    }

    public function addInitAmenity(): void
    {
        $this->initAmenities[] = ['name' => '', 'fee' => 0];
    }

    public function removeInitAmenity(int $index): void
    {
        unset($this->initAmenities[$index]);
        $this->initAmenities = array_values($this->initAmenities);
    }

    public function getInitAmenitiesTotalProperty(): float
    {
        return collect($this->initAmenities)
            ->sum(fn($row) => (float) ($row['fee'] ?? 0));
    }

    public function recordInitial()
    {
        $this->validate([
            'initContractId'         => 'required|exists:contracts,id',
            'initDateReceived'       => 'required|date',
            'initPaymentMethod'      => 'required|in:cash,bank_transfer,e_wallet',
            'initReferenceNumber'    => 'nullable|string|max:100',
            'initAmenities.*.name'   => 'nullable|string|max:80',
            'initAmenities.*.fee'    => 'nullable|numeric|min:0',
        ]);

        $contract = Contract::findOrFail($this->initContractId);

        if ($contract->initialPayment()->exists()) {
            session()->flash('error', 'Initial payment already recorded for this contract.');
            return;
        }

        $cleanAmenities = collect($this->initAmenities)
            ->map(fn($row) => [
                'name' => trim((string) ($row['name'] ?? '')),
                'fee'  => (float) ($row['fee'] ?? 0),
            ])
            ->filter(fn($row) => $row['name'] !== '')
            ->values()
            ->all();

        $amenitiesTotal = collect($cleanAmenities)->sum('fee');

        $deposit   = (float) $contract->deposit;
        $firstRent = (float) ($contract->first_month_rent ?? $contract->base_rent_rate);
        $keyFee    = (float) $contract->room_key_fee;
        $total     = $deposit + $firstRent + $keyFee + $amenitiesTotal;

        InitialPayment::create([
            'tenant_id'        => $contract->tenant_id,
            'contract_id'      => $contract->id,
            'deposit_amount'   => $deposit,
            'first_month_rent' => $firstRent,
            'room_key_fee'     => $keyFee,
            'amenities'        => $cleanAmenities ?: null,
            'amenities_total'  => $amenitiesTotal,
            'total_collected'  => $total,
            'date_received'    => $this->initDateReceived,
            'payment_method'   => $this->initPaymentMethod,
            'reference_number' => $this->initReferenceNumber ?: null,
            'recorded_by'      => auth()->id(),
        ]);

        AuditLog::record('initial_payment_recorded', auth()->id(), 'gm', 'SS3',
            "Initial payment for contract #{$contract->id}: ₱" . number_format($total, 2)
            . " ({$this->initPaymentMethod})");

        $this->showInitial = false;
        session()->flash('success', 'Initial payment recorded.');
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

        // Contracts eligible for initial payment recording: draft or active,
        // and no initial payment yet.
        $contractsAwaitingInitial = Contract::with('tenant', 'room')
            ->whereIn('status', ['draft', 'active'])
            ->whereDoesntHave('initialPayment')
            ->get();

        return view('livewire.admin.billing.billing-manager', compact(
            'bills', 'activeContracts', 'contractsAwaitingInitial'
        ));
    }
}
