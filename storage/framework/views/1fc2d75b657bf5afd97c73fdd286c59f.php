<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-brand-900">Room Management</h1>
        <button wire:click="create" class="btn-primary">+ Add Room</button>
    </div>

    
    <div class="flex flex-wrap gap-3 mb-6">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search room #..." class="form-input w-auto text-sm">
        <select wire:model.live="filterFloor" class="form-input w-auto text-sm">
            <option value="">All Floors</option>
            <option value="1">Floor 1</option><option value="2">Floor 2</option><option value="3">Floor 3</option>
        </select>
        <select wire:model.live="filterStatus" class="form-input w-auto text-sm">
            <option value="">All Status</option>
            <option value="available">Available</option><option value="occupied">Occupied</option><option value="under_maintenance">Maintenance</option>
        </select>
    </div>

    
    <div class="card overflow-x-auto p-0">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-brand-900">
                <tr>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Room</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Floor</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Type</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Rate</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3.5 text-left text-xs font-semibold text-brand-200 uppercase tracking-wider">Tenant</th>
                    <th class="px-4 py-3.5 text-right text-xs font-semibold text-brand-200 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900"><?php echo e($room->room_number); ?></td>
                    <td class="px-4 py-3 text-sm text-gray-600"><?php echo e($room->floor_level); ?></td>
                    <td class="px-4 py-3 text-sm text-gray-600"><?php echo e(ucfirst($room->room_type)); ?></td>
                    <td class="px-4 py-3 text-sm text-gray-600">₱<?php echo e(number_format($room->rate, 2)); ?></td>
                    <td class="px-4 py-3"><span class="badge <?php echo e($room->status_badge); ?>"><?php echo e(str_replace('_',' ',ucfirst($room->status))); ?></span></td>
                    <td class="px-4 py-3 text-sm text-gray-600"><?php echo e($room->currentTenant?->full_name ?? '—'); ?></td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1" x-data="{ open: false }">
                            <button wire:click="edit(<?php echo e($room->id); ?>)" class="text-brand-600 hover:text-brand-800 text-xs font-medium">Edit</button>
                            <div class="relative">
                                <button @click="open = !open" class="text-gray-400 hover:text-gray-600 text-xs">Status ▾</button>
                                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1 w-40 bg-white rounded-lg shadow-lg ring-1 ring-gray-200 py-1 z-20">
                                    <button wire:click="updateStatus(<?php echo e($room->id); ?>, 'available')" @click="open=false" class="block w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50 text-green-700">Available</button>
                                    <button wire:click="updateStatus(<?php echo e($room->id); ?>, 'occupied')" @click="open=false" class="block w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50 text-blue-700">Occupied</button>
                                    <button wire:click="updateStatus(<?php echo e($room->id); ?>, 'under_maintenance')" @click="open=false" class="block w-full text-left px-3 py-1.5 text-xs hover:bg-gray-50 text-amber-700">Maintenance</button>
                                </div>
                            </div>
                            <button wire:click="archiveRoom(<?php echo e($room->id); ?>)" wire:confirm="Archive this room record?" class="text-gray-400 hover:text-red-600 text-xs">Archive</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
        <div class="px-4 py-3"><?php echo e($rooms->links()); ?></div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal): ?>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" x-data x-init="$el.focus()">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto p-6" @click.away="$wire.set('showModal', false)">
            <h3 class="text-lg font-semibold mb-4"><?php echo e($editing ? 'Edit Room' : 'Add Room'); ?></h3>
            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Room Number *</label>
                        <input wire:model="room_number" class="form-input" placeholder="101">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['room_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <label class="form-label">Floor *</label>
                        <select wire:model="floor_level" class="form-input">
                            <option value="1">1</option><option value="2">2</option><option value="3">3</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Type *</label>
                        <select wire:model="room_type" class="form-input">
                            <option value="small">Small</option><option value="big">Big</option>
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
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->availableAmenities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $amenity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="inline-flex items-center gap-1.5 text-sm cursor-pointer">
                                <input type="checkbox" wire:model="amenitiesInput" value="<?php echo e($amenity); ?>" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                <?php echo e($amenity); ?>

                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                    <button type="submit" class="btn-primary"><?php echo e($editing ? 'Update' : 'Create'); ?></button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\citiescapes\resources\views/livewire/admin/rooms/room-manager.blade.php ENDPATH**/ ?>