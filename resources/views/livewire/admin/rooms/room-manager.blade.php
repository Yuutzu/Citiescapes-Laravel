<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <h1 class="text-2xl font-bold text-brand-900">Room Management</h1>
        <button type="button" wire:click="toggleCardsEditor"
            class="inline-flex items-center gap-2 rounded-lg bg-brand-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-800 transition">
            <i class="fas fa-images"></i>
            <span>Edit Public Room Cards</span>
        </button>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-800 border border-green-200">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-800 border border-red-200">
            {{ session('error') }}
        </div>
    @endif

    {{-- ============ PUBLIC ROOM TYPE CARDS EDITOR (SS1) â€” modal ============ --}}
    @if ($showCardsEditor)
        <div class="cs-modal"
             wire:click.self="toggleCardsEditor">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4">
            <div class="flex items-start justify-between px-6 py-4 border-b border-gray-200">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Public Room Type Cards</h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Edit the two room-type cards shown on the public landing page (compact and spacious).
                        Title, subtitle, default price, max occupants, amenity chips, and photos.
                        These appear above the individual room inventory on the public site.
                    </p>
                </div>
            </div>

            <form wire:submit="saveRoomCards">
                <div class="px-5 py-4 space-y-3 max-h-[70vh] overflow-y-auto">
                @foreach (['compact' => 'Compact Room Card', 'spacious' => 'Spacious Room Card'] as $type => $heading)
                    <div class="border border-brand-200 rounded-lg p-3">
                        <h4 class="text-sm font-semibold text-brand-800 mb-2">{{ $heading }}</h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="form-label text-xs !mb-1">Title</label>
                                <input wire:model="roomCards.{{ $type }}.title" type="text" class="form-input text-sm py-1.5">
                                @error('roomCards.'.$type.'.title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="form-label text-xs !mb-1">Subtitle</label>
                                <input wire:model="roomCards.{{ $type }}.subtitle" type="text" class="form-input text-sm py-1.5">
                            </div>
                            <div>
                                <label class="form-label text-xs !mb-1">Default Monthly Price (â‚±)</label>
                                <input wire:model="roomCards.{{ $type }}.price" type="number" step="0.01" class="form-input text-sm py-1.5">
                                @error('roomCards.'.$type.'.price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                <p class="text-[11px] text-gray-400 mt-0.5">Fallback when no live rooms of this type exist.</p>
                            </div>
                            <div>
                                <label class="form-label text-xs !mb-1">Default Max Occupants</label>
                                <input wire:model="roomCards.{{ $type }}.max_occupants" type="number" class="form-input text-sm py-1.5">
                                @error('roomCards.'.$type.'.max_occupants') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Amenities --}}
                        <div class="mb-3">
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="form-label text-xs !mb-0">Amenities</label>
                                <button type="button" wire:click="addCardAmenity('{{ $type }}')"
                                    class="text-xs text-brand-700 hover:text-brand-900 font-semibold">
                                    <i class="fas fa-plus mr-1"></i> Add Amenity
                                </button>
                            </div>
                            <div class="space-y-1.5">
                                @foreach ($roomCards[$type]['amenities'] ?? [] as $i => $am)
                                    <div wire:key="{{ $type }}-am-{{ $i }}" class="flex items-center gap-2">
                                        <select wire:model="roomCards.{{ $type }}.amenities.{{ $i }}.icon"
                                            class="form-input text-xs py-1.5" style="max-width:180px;">
                                            @foreach ($iconOptions as $iconKey => $iconHint)
                                                <option value="{{ $iconKey }}">{{ $iconKey }} â€” {{ $iconHint }}</option>
                                            @endforeach
                                        </select>
                                        <input wire:model="roomCards.{{ $type }}.amenities.{{ $i }}.label"
                                            type="text" class="form-input flex-1 text-sm py-1.5"
                                            placeholder="Label (e.g. Air Conditioner)">
                                        <button type="button" wire:click="removeCardAmenity('{{ $type }}', {{ $i }})"
                                            class="text-red-600 hover:text-red-800 px-2" title="Remove">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                @endforeach
                                @if (empty($roomCards[$type]['amenities']))
                                    <p class="text-xs text-gray-400 italic">No amenities yet. Click "Add Amenity" to start.</p>
                                @endif
                            </div>
                        </div>

                        {{-- Photos --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="form-label text-xs !mb-0">Photos</label>
                                <button type="button" wire:click="addCardPhoto('{{ $type }}')"
                                    class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-brand-600 text-white text-xs font-semibold hover:bg-brand-700">
                                    <i class="fas fa-plus text-[10px]"></i> Add Photo
                                </button>
                            </div>
                            <div class="space-y-1.5">
                                @foreach ($roomCards[$type]['photos'] ?? [] as $i => $photo)
                                    <div wire:key="{{ $type }}-ph-{{ $i }}"
                                        class="flex items-center gap-2 p-2 rounded-lg ring-1 ring-brand-100 bg-white">
                                        {{-- Thumbnail --}}
                                        @if ($photo)
                                            <img src="{{ str_starts_with($photo, 'http') ? $photo : asset('storage/' . ltrim($photo, '/')) }}"
                                                alt=""
                                                class="h-12 w-16 object-cover rounded ring-1 ring-brand-200 bg-gray-50 shrink-0"
                                                onerror="this.style.opacity=0.3">
                                        @else
                                            <div class="h-12 w-16 flex items-center justify-center rounded ring-1 ring-dashed ring-gray-300 bg-gray-50 text-[9px] text-gray-400 shrink-0">
                                                no image
                                            </div>
                                        @endif

                                        {{-- Filename + actions --}}
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[11px] text-gray-600 truncate" title="{{ $photo }}">
                                                {{ $photo ? basename($photo) : 'No file uploaded yet.' }}
                                            </p>
                                            <div class="flex items-center gap-1.5 mt-1">
                                                <label
                                                    wire:click="setCardPhotoSlot('{{ $type }}.{{ $i }}')"
                                                    class="inline-flex items-center gap-1 px-2 py-1 rounded bg-brand-600 text-white text-[11px] font-semibold cursor-pointer hover:bg-brand-700">
                                                    <i class="fas fa-upload text-[10px]"></i>
                                                    <span>{{ $photo ? 'Replace' : 'Choose' }}</span>
                                                    <input type="file" class="hidden"
                                                        accept="image/jpeg,image/png,image/webp"
                                                        wire:model="cardPhotoFile">
                                                </label>
                                                <button type="button"
                                                    wire:click="removeCardPhoto('{{ $type }}', {{ $i }})"
                                                    wire:confirm="Remove this photo from the public card?"
                                                    class="inline-flex items-center gap-1 px-2 py-1 rounded bg-white text-red-600 ring-1 ring-red-300 text-[11px] font-semibold hover:bg-red-50">
                                                    <i class="fas fa-trash text-[10px]"></i>
                                                    <span>Remove</span>
                                                </button>
                                                <span wire:loading wire:target="cardPhotoFile" class="text-[11px] text-brand-600">
                                                    <i class="fas fa-spinner fa-spin"></i> Uploadingâ€¦
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                @error('cardPhotoFile') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                @if (empty($roomCards[$type]['photos']))
                                    <p class="text-xs text-gray-400 italic">No photos yet. Click "Add Photo" to start.</p>
                                @endif
                            </div>
                            <p class="text-[10px] text-gray-500 mt-1.5">
                                <i class="fas fa-info-circle"></i>
                                Click <strong>Choose</strong> / <strong>Replace</strong> to upload (JPG, PNG, WebP). Click <strong>Remove</strong> to delete.
                            </p>
                        </div>
                    </div>
                @endforeach
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-xl">
                    <button type="button" wire:click="toggleCardsEditor" class="btn-secondary">Close</button>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save mr-1"></i> Save Public Cards
                    </button>
                </div>
            </form>
        </div>
        </div>
    @endif

    {{-- Filters --}}
    <div class="space-y-3 mb-6">
        <div class="relative max-w-md">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Search room number, type, status, tenant, rate, description..."
                class="form-input w-full pl-9 text-sm">
        </div>
        @php
            $floorBtn = fn($v) => 'inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition ' . ($filterFloor === (string)$v ? 'bg-brand-700 text-white hover:bg-brand-800' : 'bg-white text-brand-700 ring-1 ring-brand-300 hover:bg-brand-50');
            $statusBtn = fn($v) => 'inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition ' . ($filterStatus === (string)$v ? 'bg-brand-700 text-white hover:bg-brand-800' : 'bg-white text-brand-700 ring-1 ring-brand-300 hover:bg-brand-50');
        @endphp
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mr-1">Floor:</span>
            <button wire:click="$set('filterFloor','')" class="{{ $floorBtn('') }}">All</button>
            <button wire:click="$set('filterFloor','1')" class="{{ $floorBtn('1') }}">Floor 1</button>
            <button wire:click="$set('filterFloor','2')" class="{{ $floorBtn('2') }}">Floor 2</button>
            <button wire:click="$set('filterFloor','3')" class="{{ $floorBtn('3') }}">Floor 3</button>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mr-1">Status:</span>
            <button wire:click="$set('filterStatus','')" class="{{ $statusBtn('') }}">All</button>
            <button wire:click="$set('filterStatus','available')" class="{{ $statusBtn('available') }}">Available</button>
            <button wire:click="$set('filterStatus','occupied')" class="{{ $statusBtn('occupied') }}">Occupied</button>
            <button wire:click="$set('filterStatus','under_maintenance')" class="{{ $statusBtn('under_maintenance') }}">Maintenance</button>
        </div>
    </div>

    {{-- Table --}}
    <div class="card overflow-x-auto p-0">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-brand-900">
                <tr>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Room
                    </th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">
                        Floor</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Type
                    </th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Rate
                    </th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">
                        Status</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">
                        Tenant</th>
                    <th class="px-4 py-3.5 text-right text-xs font-semibold text-brand-200 uppercase tracking-wider">
                        Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($rooms as $room)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $room->room_number }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $room->floor_level }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ ucfirst($room->room_type) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">â‚±{{ number_format($room->rate, 2) }}</td>
                        <td class="px-4 py-3"><span
                                class="badge {{ $room->status_badge }}">{{ str_replace('_', ' ', ucfirst($room->status)) }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $room->currentTenant?->full_name ?? 'â€”' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2" x-data="{ open: false }">
                                {{-- Edit pill (brand) --}}
                                <button wire:click="edit({{ $room->id }})"
                                    class="inline-flex items-center gap-1 rounded-full bg-brand-600 px-3 py-1 text-xs font-semibold text-white shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-400 transition">
                                    <i class="fas fa-pen text-[10px]"></i> Edit
                                </button>

                                {{-- Status pill (gray, opens dropdown). Locked while a tenant occupies the room.
                                     Maintenance for an occupied room is handled in-place via a Tenant Request (SS7),
                                     not by flipping the room's status â€” the tenant stays put while the GM coordinates the fix. --}}
                                @php $statusLocked = (bool) $room->current_tenant_id; @endphp
                                <div class="relative">
                                    <button @click="open = !open" type="button"
                                        @if($statusLocked) disabled title="Tenant is currently occupying this room. Terminate the active contract to change status." @endif
                                        class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset transition
                                            {{ $statusLocked
                                                ? 'bg-gray-50 text-gray-400 ring-gray-200 cursor-not-allowed'
                                                : 'bg-gray-100 text-gray-700 ring-gray-300 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-400' }}">
                                        @if($statusLocked)<i class="fas fa-lock text-[10px]"></i>@endif
                                        Status <span class="text-gray-400">â–¾</span>
                                    </button>
                                    @unless($statusLocked)
                                    <div x-show="open" @click.away="open = false" x-cloak
                                        class="absolute right-0 mt-1 w-40 bg-white rounded-lg shadow-lg ring-1 ring-gray-200 py-1 z-20">
                                        <button wire:click="updateStatus({{ $room->id }}, 'available')" @click="open=false"
                                            class="block w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50 text-green-700">
                                            <i class="fas fa-circle text-[7px] mr-1.5"></i> Available
                                        </button>
                                        <button wire:click="updateStatus({{ $room->id }}, 'occupied')" @click="open=false"
                                            class="block w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50 text-blue-700">
                                            <i class="fas fa-circle text-[7px] mr-1.5"></i> Occupied
                                        </button>
                                        <button wire:click="updateStatus({{ $room->id }}, 'under_maintenance')"
                                            @click="open=false"
                                            class="block w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50 text-amber-700">
                                            <i class="fas fa-circle text-[7px] mr-1.5"></i> Maintenance
                                        </button>
                                    </div>
                                    @endunless
                                </div>

                                {{-- Archive pill (red outline) --}}
                                <button wire:click="archiveRoom({{ $room->id }})" wire:confirm="Archive this room record?"
                                    class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 text-xs font-semibold text-red-600 ring-1 ring-inset ring-red-300 hover:bg-red-50 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-400 transition">
                                    <i class="fas fa-box-archive text-[10px]"></i> Archive
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $rooms->links() }}</div>
    </div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
        <div class="cs-modal" x-data x-init="$el.focus()">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6"
                @click.away="$wire.set('showModal', false)">
                <h3 class="text-lg font-semibold mb-4">Edit Room</h3>
                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Room Number *</label>
                            <input wire:model="room_number" class="form-input" placeholder="101">
                            @error('room_number') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Floor *</label>
                            <select wire:model="floor_level" class="form-input">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Type *</label>
                            <select wire:model="room_type" class="form-input">
                                <option value="compact">Compact</option>
                                <option value="spacious">Spacious</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Rate (â‚±/month) *</label>
                            <input wire:model="rate" type="number" step="0.01" class="form-input">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Max Occupants</label>
                        <input wire:model="max_occupants" type="number" min="1" max="10" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Amenities</label>
                        <div class="flex flex-wrap gap-2 mt-1">
                            @foreach($this->availableAmenities as $amenity)
                                <label class="inline-flex items-center gap-1.5 text-sm cursor-pointer">
                                    <input type="checkbox" wire:model="amenitiesInput" value="{{ $amenity }}"
                                        class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                    {{ $amenity }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Description</label>
                        <textarea wire:model="description" rows="2" class="form-input"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="btn-secondary">Cancel</button>
                        <button type="submit" class="btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
