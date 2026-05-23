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

    /** Fixed catalog of selectable amenities and their default one-time fees. */
    public const AMENITY_CATALOG = [
        ['name' => 'Air Conditioner',          'fee' => 10000.00],
        ['name' => 'Extra Mattress',           'fee' => 500.00],
        ['name' => 'Extra Double Deck Frame',  'fee' => 1500.00],
        ['name' => 'Aircon Remote',            'fee' => 300.00],
        ['name' => 'Extra Key',                'fee' => 200.00],
        ['name' => 'Extra Pillow',             'fee' => 150.00],
    ];

    // Terminate modal
    public bool $showTerminate = false;
    public ?int $terminateId = null;
    public string $terminateReason = '';

    // Scan viewer modal
    public ?int $viewingScanId = null;

    // Scan-access decision modal
    public bool $showScanDecision = false;
    public ?int $scanDecisionContractId = null;
    public string $scanDecisionAction = 'approve'; // approve | deny
    public string $scanDecisionNote = '';

    public function openScanDecision(int $contractId, string $action): void
    {
        if (!in_array($action, ['approve', 'deny'], true)) return;
        $this->scanDecisionContractId = $contractId;
        $this->scanDecisionAction = $action;
        $this->scanDecisionNote = '';
        $this->showScanDecision = true;
    }

    public function submitScanDecision(): void
    {
        $this->validate([
            'scanDecisionNote' => $this->scanDecisionAction === 'deny' ? 'required|string|max:500' : 'nullable|string|max:500',
        ]);

        $contract = Contract::findOrFail($this->scanDecisionContractId);
        $status = $this->scanDecisionAction === 'approve' ? 'approved' : 'denied';

        $contract->update([
            'scan_view_status'        => $status,
            'scan_view_decided_at'    => now(),
            'scan_view_decided_by'    => auth()->id(),
            'scan_view_decision_note' => $this->scanDecisionNote ?: null,
        ]);

        AuditLog::record(
            $status === 'approved' ? 'contract_scan_view_approved' : 'contract_scan_view_denied',
            auth()->id(), 'gm', 'SS4',
            "Contract #{$contract->id} scan access {$status} for tenant #{$contract->tenant_id}"
        );

        $this->showScanDecision = false;
        $this->scanDecisionContractId = null;
        session()->flash('success', "Scan access {$status}.");
    }

    public function revokeScanAccess(int $contractId): void
    {
        $contract = Contract::findOrFail($contractId);
        $contract->update([
            'scan_view_status'        => null,
            'scan_view_requested_at'  => null,
            'scan_view_decided_at'    => null,
            'scan_view_decided_by'    => null,
            'scan_view_decision_note' => null,
        ]);
        AuditLog::record('contract_scan_view_revoked', auth()->id(), 'gm', 'SS4', "Contract #{$contract->id} scan access revoked");
        session()->flash('success', 'Scan access revoked.');
    }

    public function viewScan(int $id): void
    {
        $this->viewingScanId = $id;
    }

    public function closeScan(): void
    {
        $this->viewingScanId = null;
    }

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
        $this->scanFile = null;
        $this->showModal = true;
        $this->editing = true;
    }

    public function updatedRoomId($value): void
    {
        if (! $value) {
            return;
        }
        $room = Room::find($value);
        if ($room) {
            $this->base_rent_rate = (float) $room->rate;
        }
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

    /**
     * Auto-fill the fee when an amenity name is picked from the dropdown.
     * Livewire 3 invokes this for nested array updates with $key like "0.name".
     */
    public function updatedAmenities($value, ?string $key = null): void
    {
        if (! $key || ! \str_ends_with($key, '.name')) {
            return;
        }
        $index = (int) explode('.', $key)[0];
        $match = \collect(self::AMENITY_CATALOG)->firstWhere('name', $value);
        if ($match) {
            $this->amenities[$index]['fee'] = $match['fee'];
        }
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
            'scanFile'   => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp,doc,docx|max:10240',
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
            if (!$path) {
                $this->addError('scanFile', 'Could not save uploaded scan to disk. Check storage permissions.');
                return;
            }
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

        // Guard: only drafts can be activated.
        if ($contract->status !== 'draft') {
            session()->flash('error', 'Only draft contracts can be activated.');
            return;
        }

        // Guard: room must exist and not already be occupied by a different tenant.
        if (!$contract->room) {
            session()->flash('error', 'Contract has no linked room.');
            return;
        }
        if (
            $contract->room->current_tenant_id
            && $contract->room->current_tenant_id !== $contract->tenant_id
        ) {
            session()->flash('error', "Room {$contract->room->room_number} is already occupied by another tenant. Resolve that first.");
            return;
        }

        // Activate contract + occupy room atomically-ish.
        $contract->update([
            'status'       => 'active',
            'activated_at' => $contract->activated_at ?? now(),
        ]);

        $contract->room->update([
            'status'             => 'occupied',
            'current_tenant_id'  => $contract->tenant_id,
            'last_updated_by'    => auth()->id(),
            'last_status_update' => now(),
        ]);

        // Ensure the tenant user is marked active (drafts often link to pending_activation tenants).
        if ($contract->tenant && $contract->tenant->status === 'pending_activation') {
            $contract->tenant->update(['status' => 'active']);
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

        // Notify the tenant: bell (authoritative) + email (best-effort).
        $this->notifyTenantOfTermination($contract, $this->terminateReason);

        $this->showTerminate = false;
        session()->flash('success', 'Contract terminated and archived. Tenant notified via bell + email.');
    }

    /**
     * Notify the tenant that their contract was terminated. Bell row always
     * lands; email is best-effort and logged on failure (same delivery
     * guarantee documented in SS7).
     */
    private function notifyTenantOfTermination(Contract $contract, string $reason): void
    {
        if (!$contract->tenant) return;

        \App\Models\NotificationLog::create([
            'user_id' => $contract->tenant_id,
            'type'    => 'contract_terminated',
            'source'  => 'SS4',
            'message' => "Your contract for Room {$contract->room?->room_number} was terminated effective " . now()->format('M d, Y'),
        ]);

        if ($contract->tenant->email) {
            try {
                \Illuminate\Support\Facades\Mail::to($contract->tenant->email)->send(
                    new \App\Mail\ContractTerminatedMail(
                        $contract->tenant->full_name,
                        (string) ($contract->room?->room_number ?? '—'),
                        now()->format('M d, Y'),
                        $reason,
                    )
                );
            } catch (\Throwable $e) {
                \Log::error('ContractTerminatedMail send failed', [
                    'contract_id' => $contract->id,
                    'error'       => $e->getMessage(),
                ]);
                // Bell already landed; email is best-effort.
            }
        }
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
            ->when($this->search, function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($qq) use ($term) {
                    $qq->whereHas('tenant', fn($t) => $t->where('full_name', 'like', $term)->orWhere('email', 'like', $term))
                       ->orWhereHas('room', fn($r) => $r->where('room_number', 'like', $term)->orWhere('room_type', 'like', $term))
                       ->orWhere('status', 'like', $term)
                       ->orWhere('house_rules', 'like', $term)
                       ->orWhere('base_rent_rate', 'like', $term)
                       ->orWhere('termination_reason', 'like', $term);
                });
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate(15);

        $tenants = User::where('role', 'tenant')->whereIn('status', ['active', 'pending_activation'])->get();

        // Only available rooms can host a new contract, but when EDITING an
        // existing contract its currently-linked room must stay in the list
        // (even if that room is now 'occupied' — by this contract itself) so
        // the dropdown can render the selected option.
        $rooms = Room::where(function ($q) {
                $q->where('status', 'available');
                if ($this->editing && $this->room_id) {
                    $q->orWhere('id', $this->room_id);
                }
            })
            ->orderBy('room_number')
            ->get();

        return view('livewire.admin.contracts.contract-manager', compact('contracts', 'tenants', 'rooms'));
    }
}
