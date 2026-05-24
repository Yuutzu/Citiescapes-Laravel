<?php

namespace App\Livewire\Admin\Rooms;

use App\Models\Archive;
use App\Models\AuditLog;
use App\Models\Room;
use App\Models\SystemSetting;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Room Management — Citiescapes')]
class RoomManager extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public string $filterFloor = '';
    public string $filterStatus = '';

    // Modal state
    public bool $showModal = false;
    public bool $editing = false;
    public ?int $editId = null;

    // Form fields
    public string $room_number = '';
    public int $floor_level = 1;
    public string $room_type = 'compact';
    public float $rate = 3500;
    public int $max_occupants = 3;
    public string $status = 'available';
    public string $description = '';
    public array $amenitiesInput = [];

    protected $availableAmenities = ['Air Conditioning', 'WiFi', 'Private Bathroom', 'Shared Bathroom', 'Mini Fridge', 'Study Desk', 'Wardrobe', 'Hot Shower'];

    public function getAvailableAmenitiesProperty() { return $this->availableAmenities; }

    // ====================================================================
    // PUBLIC ROOM TYPE CARDS (SS1) — admin edits the two cards (compact /
    // spacious) shown on the public landing page: title, subtitle, default
    // price, max occupants, amenity chips and photo paths. Persisted as
    // JSON in system_settings under key `room_type_cards`.
    // ====================================================================
    public array $roomCards = [];
    public bool $showCardsEditor = false;
    // Single-slot upload buffer for the cards editor.
    // The view sets $cardPhotoSlot ("compact.0") immediately before the user
    // picks a file, then $cardPhotoFile is a TemporaryUploadedFile bound to
    // wire:model. The "updatedCardPhotoFile" hook persists it into the matching slot.
    public $cardPhotoFile = null;
    public ?string $cardPhotoSlot = null;

    public array $iconOptions = [
        'fa-snowflake'      => 'Air Conditioner',
        'fa-wifi'           => 'WiFi',
        'fa-table'          => 'Tables',
        'fa-chair'          => 'Chairs',
        'fa-bed'            => 'Bed',
        'fa-layer-group'    => 'Double Deck',
        'fa-shower'         => 'Shower',
        'fa-toilet'         => 'Toilet',
        'fa-tv'             => 'TV',
        'fa-fan'            => 'Fan',
        'fa-mug-hot'        => 'Kitchenette',
        'fa-shield-halved'  => 'Security',
    ];

    public function mount(): void
    {
        $this->roomCards = static::loadRoomCards();
    }

    public static function defaultRoomCards(): array
    {
        return [
            'compact' => [
                'title' => 'Compact Room',
                'subtitle' => 'Solo professionals & students',
                'price' => 3500,
                'max_occupants' => 3,
                'amenities' => [
                    ['icon' => 'fa-snowflake', 'label' => 'Air Conditioner'],
                    ['icon' => 'fa-wifi',      'label' => 'WiFi'],
                    ['icon' => 'fa-table',     'label' => 'Tables'],
                    ['icon' => 'fa-chair',     'label' => 'Chairs'],
                ],
                // Empty by default — admin uploads via the SS1 editor. Pre-populated
                // entries here would show as broken thumbnails on a fresh install
                // because the seed files no longer ship with the repo.
                'photos' => [],
            ],
            'spacious' => [
                'title' => 'Spacious Room',
                'subtitle' => 'Couples, families & sharing',
                'price' => 5000,
                'max_occupants' => 4,
                'amenities' => [
                    ['icon' => 'fa-snowflake',   'label' => 'Air Conditioner'],
                    ['icon' => 'fa-wifi',        'label' => 'WiFi'],
                    ['icon' => 'fa-table',       'label' => 'Tables'],
                    ['icon' => 'fa-chair',       'label' => 'Chairs'],
                    ['icon' => 'fa-layer-group', 'label' => 'Extra Double Deck Frame'],
                    ['icon' => 'fa-bed',         'label' => 'Extra Mattress'],
                ],
                'photos' => [],
            ],
        ];
    }

    public static function loadRoomCards(): array
    {
        $raw = SystemSetting::getValue('room_type_cards', null);
        if (\is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (\is_array($decoded)) $raw = $decoded;
        }
        $defaults = static::defaultRoomCards();
        if (!\is_array($raw)) return $defaults;

        // Merge scalar/text fields with defaults, but take photos and amenities
        // verbatim from saved data — otherwise array_replace_recursive would
        // graft default photos back over an admin's deletions/replacements.
        foreach (['compact', 'spacious'] as $t) {
            $saved = $raw[$t] ?? [];

            $merged = $defaults[$t]; // start from defaults
            foreach (['title', 'subtitle', 'price', 'max_occupants'] as $scalar) {
                if (array_key_exists($scalar, $saved)) {
                    $merged[$scalar] = $saved[$scalar];
                }
            }

            // Photos: use saved exactly if the saved key exists (even if empty).
            // Only fall back to defaults when the admin has never touched this card.
            $merged['photos'] = array_key_exists('photos', $saved)
                ? array_values(array_filter(
                    (array) $saved['photos'],
                    fn($p) => \is_string($p) && trim($p) !== ''
                ))
                : $defaults[$t]['photos'];

            // Same logic for amenities.
            $merged['amenities'] = array_key_exists('amenities', $saved)
                ? array_values((array) $saved['amenities'])
                : $defaults[$t]['amenities'];

            $raw[$t] = $merged;
        }
        return $raw;
    }

    public function toggleCardsEditor(): void
    {
        $this->showCardsEditor = !$this->showCardsEditor;
    }

    public function addCardAmenity(string $type): void
    {
        if (!isset($this->roomCards[$type])) return;
        $this->roomCards[$type]['amenities'][] = ['icon' => 'fa-snowflake', 'label' => 'New Amenity'];
    }

    public function removeCardAmenity(string $type, int $index): void
    {
        if (!isset($this->roomCards[$type]['amenities'][$index])) return;
        array_splice($this->roomCards[$type]['amenities'], $index, 1);
    }

    public function addCardPhoto(string $type): void
    {
        if (!isset($this->roomCards[$type])) return;
        $this->roomCards[$type]['photos'][] = '';
    }

    public function removeCardPhoto(string $type, int $index): void
    {
        if (!isset($this->roomCards[$type]['photos'][$index])) return;
        array_splice($this->roomCards[$type]['photos'], $index, 1);
    }

    /**
     * Persist an inline-uploaded card image into the slot recorded in
     * $cardPhotoSlot ("compact.0" / "spacious.2"). The view sets the slot
     * via wire:click on the upload label, then wire:model="cardPhotoFile"
     * uploads the file and this hook stores it.
     */
    public function setCardPhotoSlot(string $slot): void
    {
        $this->cardPhotoSlot = $slot;
    }

    public function updatedCardPhotoFile($value): void
    {
        if ($value === null || $this->cardPhotoSlot === null) return;

        $parts = explode('.', $this->cardPhotoSlot, 2);
        if (\count($parts) !== 2) { $this->cardPhotoFile = null; $this->cardPhotoSlot = null; return; }
        [$type, $idxRaw] = $parts;
        if (!\in_array($type, ['compact', 'spacious'], true)) { $this->cardPhotoFile = null; $this->cardPhotoSlot = null; return; }
        $index = (int) $idxRaw;

        $this->validate([
            'cardPhotoFile' => 'file|image|mimes:jpg,jpeg,png,webp|max:8192',
        ]);

        $stored = $value->store('room-type-cards', 'public');
        if (!$stored) {
            session()->flash('error', 'Image upload failed.');
            $this->cardPhotoFile = null;
            $this->cardPhotoSlot = null;
            return;
        }

        if (!isset($this->roomCards[$type]['photos'])) {
            $this->roomCards[$type]['photos'] = [];
        }
        $this->roomCards[$type]['photos'][$index] = $stored;

        $this->cardPhotoFile = null;
        $this->cardPhotoSlot = null;
        session()->flash('success', 'Photo uploaded. Click "Save Public Cards" to keep it.');
    }

    public function saveRoomCards(): void
    {
        $this->validate([
            'roomCards.compact.title'         => 'required|string|max:60',
            'roomCards.compact.subtitle'      => 'nullable|string|max:120',
            'roomCards.compact.price'         => 'required|numeric|min:0',
            'roomCards.compact.max_occupants' => 'required|integer|min:1|max:20',
            'roomCards.compact.amenities.*.icon'  => 'required|string|max:40',
            'roomCards.compact.amenities.*.label' => 'required|string|max:60',
            'roomCards.compact.photos.*'      => 'nullable|string|max:255',
            'roomCards.spacious.title'         => 'required|string|max:60',
            'roomCards.spacious.subtitle'      => 'nullable|string|max:120',
            'roomCards.spacious.price'         => 'required|numeric|min:0',
            'roomCards.spacious.max_occupants' => 'required|integer|min:1|max:20',
            'roomCards.spacious.amenities.*.icon'  => 'required|string|max:40',
            'roomCards.spacious.amenities.*.label' => 'required|string|max:60',
            'roomCards.spacious.photos.*'      => 'nullable|string|max:255',
        ]);

        $cards = $this->roomCards;
        foreach (['compact', 'spacious'] as $t) {
            $cards[$t]['photos'] = array_values(array_filter(
                $cards[$t]['photos'] ?? [],
                fn($p) => \is_string($p) && trim($p) !== ''
            ));
        }
        SystemSetting::setValue('room_type_cards', json_encode($cards), auth()->id());
        AuditLog::record('room_cards_updated', auth()->id(), 'gm', 'SS1', 'Public room type cards updated');
        session()->flash('success', 'Public room type cards saved.');
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->editing = false;
    }

    public function edit(int $id)
    {
        $room = Room::findOrFail($id);
        $this->editId = $id;
        $this->room_number = $room->room_number;
        $this->floor_level = $room->floor_level;
        $this->room_type = $room->room_type;
        $this->rate = (float) $room->rate;
        $this->max_occupants = $room->max_occupants;
        $this->status = $room->status;
        $this->description = $room->description ?? '';
        $this->amenitiesInput = $room->amenities ?? [];
        $this->showModal = true;
        $this->editing = true;
    }

    public function save()
    {
        $this->validate([
            'room_number'  => 'required|max:10|unique:rooms,room_number,' . $this->editId,
            'floor_level'  => 'required|in:1,2,3',
            'room_type'    => 'required|in:compact,spacious',
            'rate'         => 'required|numeric|min:0',
            'max_occupants'=> 'required|integer|min:1|max:10',
            'status'       => 'required|in:available,occupied,under_maintenance',
        ]);

        $data = [
            'room_number'       => $this->room_number,
            'floor_level'       => $this->floor_level,
            'room_type'         => $this->room_type,
            'rate'              => $this->rate,
            'max_occupants'     => $this->max_occupants,
            'status'            => $this->status,
            'description'       => $this->description,
            'amenities'         => $this->amenitiesInput,
            'last_updated_by'   => auth()->id(),
            'last_status_update'=> now(),
        ];

        if ($this->editing) {
            Room::findOrFail($this->editId)->update($data);
            AuditLog::record('room_updated', auth()->id(), 'gm', 'SS1', "Room {$this->room_number} updated");
        } else {
            Room::create($data);
            AuditLog::record('room_created', auth()->id(), 'gm', 'SS1', "Room {$this->room_number} created");
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', $this->editing ? 'Room updated.' : 'Room created.');
    }

    public function updateStatus(int $id, string $newStatus)
    {
        $room = Room::findOrFail($id);

        // Block status change while a tenant is occupying the room.
        // The tenant must be moved out (contract terminated / room cleared) before
        // the GM can flip the room back to available or under_maintenance.
        if ($room->current_tenant_id && $newStatus !== 'occupied') {
            session()->flash('error', "Room {$room->room_number} is currently occupied by a tenant. Terminate or transfer the active contract before changing its status.");
            return;
        }

        $old = $room->status;
        $room->update([
            'status'             => $newStatus,
            'last_updated_by'    => auth()->id(),
            'last_status_update' => now(),
            'current_tenant_id'  => $newStatus !== 'occupied' ? null : $room->current_tenant_id,
        ]);
        AuditLog::record('room_status_change', auth()->id(), 'gm', 'SS1', "Room {$room->room_number}: {$old} → {$newStatus}");
    }

    public function archiveRoom(int $id)
    {
        $room = Room::findOrFail($id);
        // Snapshot the room's current state to SS5 for the audit trail. The
        // room itself stays in inventory — Archive Room is a record-keeping
        // operation, not a delete. (Use Permanently Delete Room for removal.)
        $archive = Archive::create([
            'original_record_id' => $room->id,
            'record_type'        => 'room',
            'source_subsystem'   => 'SS1',
            'archive_reason'     => 'Snapshot taken by management (room kept in inventory)',
            'data'               => $room->toArray(),
            'archived_by'        => auth()->id(),
        ]);

        // Occupancy handling. If a tenant currently occupies this room, we
        // protect their lease: snapshot is created but we DO NOT free the room
        // while the contract is still active. Once the contract ends (auto-
        // archive cron handles that), the room frees up the normal way.
        $note = "Room {$room->room_number} snapshotted to SS5 archive #{$archive->id}";

        if ($room->current_tenant_id) {
            $activeContract = \App\Models\Contract::where('room_id', $room->id)
                ->where('tenant_id', $room->current_tenant_id)
                ->where('status', 'active')
                ->whereDate('end_date', '>=', now())
                ->first();

            if ($activeContract) {
                $note .= " — tenant kept linked (contract #{$activeContract->id} still active until {$activeContract->end_date->format('Y-m-d')})";
            } else {
                // Tenant exists on the room but no live contract — safe to detach.
                $room->update([
                    'current_tenant_id'  => null,
                    'status'             => 'available',
                    'last_status_update' => now(),
                ]);
                $note .= ' — room freed (no active contract on file)';
            }
        }

        AuditLog::record('room_archived', auth()->id(), 'gm', 'SS1', $note);
        session()->flash('success',
            $room->current_tenant_id
                ? "Room {$room->room_number} snapshotted. Tenant lease is still active — room stays occupied."
                : "Room {$room->room_number} snapshotted to archive."
        );
    }

    public function deleteRoom(int $id)
    {
        $room = Room::findOrFail($id);
        $num = $room->room_number;

        // Refuse if any contracts/bills/archives still reference this room.
        // The contracts FK uses cascadeOnDelete (→ bills + payments), so a
        // forceDelete here would silently nuke the financial audit trail.
        $contractCount = \App\Models\Contract::where('room_id', $room->id)->count();
        $archiveCount  = \App\Models\Archive::where('record_type', 'room')
            ->where('original_record_id', $room->id)->count();

        if ($contractCount > 0 || $archiveCount > 0) {
            session()->flash('error',
                "Cannot permanently delete Room {$num}: {$contractCount} contract(s) and {$archiveCount} archive row(s) reference it. " .
                "Use Archive (soft-delete) instead so the financial history is preserved."
            );
            return;
        }

        $room->forceDelete();
        AuditLog::record('room_deleted', auth()->id(), 'gm', 'SS1', "Room {$num} permanently deleted");
        session()->flash('success', 'Room permanently deleted.');
    }

    private function resetForm()
    {
        $this->editId = null;
        $this->room_number = '';
        $this->floor_level = 1;
        $this->room_type = 'compact';
        $this->rate = 3500;
        $this->max_occupants = 3;
        $this->status = 'available';
        $this->description = '';
        $this->amenitiesInput = [];
    }

    public function render()
    {
        $rooms = Room::query()
            ->when($this->search, function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($qq) use ($term) {
                    $qq->where('room_number', 'like', $term)
                       ->orWhere('room_type', 'like', $term)
                       ->orWhere('status', 'like', $term)
                       ->orWhere('description', 'like', $term)
                       ->orWhere('rate', 'like', $term)
                       ->orWhere('floor_level', 'like', $term)
                       ->orWhereHas('currentTenant', fn($t) => $t->where('full_name', 'like', $term));
                });
            })
            ->when($this->filterFloor, fn($q) => $q->where('floor_level', $this->filterFloor))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->with('currentTenant')
            ->orderBy('floor_level')->orderBy('room_number')
            ->paginate(20);

        return view('livewire.admin.rooms.room-manager', compact('rooms'));
    }
}
