<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-brand-900">Room Management</h1>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-3 mb-6">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search room #..."
            class="form-input w-auto text-sm">
        <select wire:model.live="filterFloor" class="form-input w-auto text-sm">
            <option value="">All Floors</option>
            <option value="1">Floor 1</option>
            <option value="2">Floor 2</option>
            <option value="3">Floor 3</option>
        </select>
        <select wire:model.live="filterStatus" class="form-input w-auto text-sm">
            <option value="">All Status</option>
            <option value="available">Available</option>
            <option value="occupied">Occupied</option>
            <option value="under_maintenance">Maintenance</option>
        </select>
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
                        <td class="px-4 py-3 text-sm text-gray-600">₱{{ number_format($room->rate, 2) }}</td>
                        <td class="px-4 py-3"><span
                                class="badge {{ $room->status_badge }}">{{ str_replace('_', ' ', ucfirst($room->status)) }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $room->currentTenant?->full_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2" x-data="{ open: false }">
                                {{-- Edit pill (brand) --}}
                                <button wire:click="edit({{ $room->id }})"
                                    class="inline-flex items-center gap-1 rounded-full bg-brand-600 px-3 py-1 text-xs font-semibold text-white shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-400 transition">
                                    <i class="fas fa-pen text-[10px]"></i> Edit
                                </button>

                                {{-- Status pill (gray, opens dropdown) --}}
                                <div class="relative">
                                    <button @click="open = !open" type="button"
                                        class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-400 transition">
                                        Status <span class="text-gray-400">▾</span>
                                    </button>
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
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" x-data x-init="$el.focus()">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto p-6"
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
                            <label class="form-label">Rate (₱/month) *</label>
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
                    <div>
                        <label class="form-label">Photos</label>
                        <input wire:model="newPhotos" type="file" multiple accept="image/*" class="form-input text-sm">
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