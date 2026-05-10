<?php

namespace App\Livewire\Admin\Contracts;

use App\Models\Archive;
use App\Models\AuditLog;
use App\Models\Contract;
use App\Models\Room;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Contract Management — Citiescapes')]
class ContractManager extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public string $filterStatus = '';

    // Create/edit modal
    public bool $showModal = false;
    public bool $editing = false;
    public ?int $editId = null;

    public ?int $tenant_id = null;
    public ?int $room_id = null;
    public float $base_rent_rate = 0;
    public float $deposit = 0;
    public float $room_key_fee = 200;
    public string $start_date = '';
    public string $end_date = '';
    public float $penalty_rate = 100;
    public int $penalty_grace_days = 3;
    public string $house_rules = '';
    public $scanFile = null;

    /** @var array<int, array{name: string, fee: float|string}> */
    public array $amenities = [];

    // Terminate modal
    public bool $showTerminate = false;
    public ?int $terminateId = null;
    public string $terminateReason = '';

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->editing = false;
    }

    public function edit(int $id)
    {
        $c = Contract::findOrFail($id);
        $this->editId = $id;
        $this->tenant_id = $c->tenant_id;
        $this->room_id = $c->room_id;
        $this->base_rent_rate = (float) $c->base_rent_rate;
        $this->deposit = (float) $c->deposit;
        $this->room_key_fee = (float) $c->room_key_fee;
        $this->amenities = $c->requested_amenities ?? [];
        $this->start_date = $c->start_date->format('Y-m-d');
        $this->end_date = $c->end_date->format('Y-m-d');
        $this->penalty_rate = (float) $c->penalty_rate;
        $this->penalty_grace_days = $c->penalty_grace_days;
        $this->house_rules = $c->house_rules ?? '';
        $this->showModal = true;
        $this->editing = true;
    }

    public function addAmenity(): void
    {
        $this->amenities[] = ['name' => '', 'fee' => 0];
    }

    public function removeAmenity(int $index): void
    {
        unset($this->amenities[$index]);
        $this->amenities = array_values($this->amenities);
    }

    public function save()
    {
        $this->validate([
            'tenant_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'base_rent_rate' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'penalty_rate' => 'required|numeric|min:0',
            'amenities.*.name' => 'nullable|string|max:80',
            'amenities.*.fee'  => 'nullable|numeric|min:0',
        ]);

        $cleanAmenities = collect($this->amenities)
            ->map(fn($row) => [
                'name' => trim((string) ($row['name'] ?? '')),
                'fee'  => (float) ($row['fee'] ?? 0),
            ])
            ->filter(fn($row) => $row['name'] !== '')
            ->values()
            ->all();

        $data = [
            'tenant_id' => $this->tenant_id,
            'room_id' => $this->room_id,
            'base_rent_rate' => $this->base_rent_rate,
            'deposit' => $this->deposit,
            'room_key_fee' => $this->room_key_fee,
            'requested_amenities' => $cleanAmenities ?: null,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'penalty_rate' => $this->penalty_rate,
            'penalty_grace_days' => $this->penalty_grace_days,
            'house_rules' => $this->house_rules,
            'created_by' => auth()->id(),
        ];

        if ($this->scanFile) {
            $path = $this->scanFile->store('contracts', 'public');
            $data['scan_file_path'] = $path;
            $data['scan_file_name'] = $this->scanFile->getClientOriginalName();
        }

        if ($this->editing) {
            Contract::findOrFail($this->editId)->update($data);
            AuditLog::record('contract_updated', auth()->id(), 'gm', 'SS4', "Contract #{$this->editId} updated");
        } else {
            $data['status'] = 'draft';
            Contract::create($data);
            AuditLog::record('contract_created', auth()->id(), 'gm', 'SS4', "Draft contract created for tenant #{$this->tenant_id}");
        }

        $this->showModal = false;
        session()->flash('success', $this->editing ? 'Contract updated.' : 'Contract draft created.');
    }

    public function activate(int $id)
    {
        $contract = Contract::with(['tenant', 'room'])->findOrFail($id);

        $contract->update(['status' => 'active']);

        // Mark room as occupied and link the tenant
        if ($contract->room) {
            $contract->room->update([
                'status' => 'occupied',
                'current_tenant_id' => $contract->tenant_id,
                'last_updated_by' => auth()->id(),
                'last_status_update' => now(),
            ]);
        }

        AuditLog::record('contract_activated', auth()->id(), 'gm', 'SS4', "Contract #{$contract->id} activated — Room {$contract->room?->room_number} marked occupied");
        session()->flash('success', 'Contract activated and room marked as occupied.');
    }

    public function openTerminate(int $id)
    {
        $this->terminateId = $id;
        $this->terminateReason = '';
        $this->showTerminate = true;
    }

    public function terminate()
    {
        $this->validate(['terminateReason' => 'required|max:500']);

        $contract = Contract::with(['tenant', 'room'])->findOrFail($this->terminateId);
        $contract->update([
            'status' => 'terminated',
            'terminated_at' => now(),
            'termination_reason' => $this->terminateReason,
        ]);

        // Archive to SS5
        Archive::create([
            'original_record_id' => $contract->id,
            'record_type' => 'contract',
            'source_subsystem' => 'SS4',
            'archive_reason' => 'Terminated by GM: ' . $this->terminateReason,
            'data' => $contract->toArray(),
            'scan_file_path' => $contract->scan_file_path,
            'archived_by' => auth()->id(),
        ]);

        // Archive tenant (SS2)
        if ($contract->tenant?->status === 'active') {
            $contract->tenant->update(['status' => 'archived', 'archived_at' => now()]);
        }

        // Free room
        if ($contract->room?->current_tenant_id === $contract->tenant_id) {
            $contract->room->update(['current_tenant_id' => null, 'status' => 'available', 'last_status_update' => now()]);
        }

        AuditLog::record('contract_terminated', auth()->id(), 'gm', 'SS4', "Contract #{$contract->id} terminated");
        $this->showTerminate = false;
        session()->flash('success', 'Contract terminated and archived.');
    }

    public function renew(int $id)
    {
        $old = Contract::findOrFail($id);

        // Archive old
        Archive::create([
            'original_record_id' => $old->id,
            'record_type' => 'contract',
            'source_subsystem' => 'SS4',
            'archive_reason' => 'Renewed — old contract archived',
            'data' => $old->toArray(),
            'scan_file_path' => $old->scan_file_path,
            'archived_by' => auth()->id(),
        ]);
        $old->update(['status' => 'expired']);

        // Pre-fill new draft
        $this->resetForm();
        $this->tenant_id = $old->tenant_id;
        $this->room_id = $old->room_id;
        $this->base_rent_rate = (float) $old->base_rent_rate;
        $this->deposit = (float) $old->deposit;
        $this->room_key_fee = (float) $old->room_key_fee;
        $this->amenities = $old->requested_amenities ?? [];
        $this->penalty_rate = (float) $old->penalty_rate;
        $this->penalty_grace_days = $old->penalty_grace_days;
        $this->house_rules = $old->house_rules ?? '';
        $this->start_date = $old->end_date->addDay()->format('Y-m-d');
        $this->end_date = $old->end_date->addYear()->format('Y-m-d');
        $this->showModal = true;
        $this->editing = false;

        session()->flash('success', 'Old contract archived. Complete the renewal draft below.');
    }

    private function resetForm()
    {
        $this->editId = null;
        $this->tenant_id = null;
        $this->room_id = null;
        $this->base_rent_rate = 0;
        $this->deposit = 0;
        $this->room_key_fee = 200;
        $this->amenities = [];
        $this->start_date = '';
        $this->end_date = '';
        $this->penalty_rate = 100;
        $this->penalty_grace_days = 3;
        $this->house_rules = '';
        $this->scanFile = null;
    }

    public function render()
    {
        $contracts = Contract::with(['tenant', 'room'])
            ->when($this->search, fn($q) => $q->whereHas('tenant', fn($qq) => $qq->where('full_name', 'like', "%{$this->search}%")))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate(15);

        $tenants = User::where('role', 'tenant')->whereIn('status', ['active', 'pending_activation'])->get();
        $rooms = Room::orderBy('room_number')->get();

        return view('livewire.admin.contracts.contract-manager', compact('contracts', 'tenants', 'rooms'));
    }
}
