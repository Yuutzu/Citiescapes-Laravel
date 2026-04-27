<div>
    
    <section class="bg-brand-900 text-white py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl font-bold tracking-tight">Welcome to Citiescapes</h1>
            <p class="mt-3 text-lg text-brand-200">Comfortable, quality, and affordable room rentals in Bajada, Davao City</p>
            <p class="mt-1 text-sm text-brand-300">Remedios St., Bajada, Davao City &bull; 22 Rooms &bull; 3 Floors</p>
        </div>
    </section>

    
    <section id="rooms" class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Available Rooms</h2>

        
        <div class="flex flex-wrap gap-3 mb-8">
            <select wire:model.live="filterFloor" class="form-input w-auto text-sm">
                <option value="">All Floors</option>
                <option value="1">1st Floor</option>
                <option value="2">2nd Floor</option>
                <option value="3">3rd Floor</option>
            </select>
            <select wire:model.live="filterType" class="form-input w-auto text-sm">
                <option value="">All Types</option>
                <option value="small">Small</option>
                <option value="big">Big</option>
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="card hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-semibold text-gray-900">Room <?php echo e($room->room_number); ?></h3>
                            <p class="text-xs text-gray-500">Floor <?php echo e($room->floor_level); ?> &bull; <?php echo e(ucfirst($room->room_type)); ?></p>
                        </div>
                        <span class="badge <?php echo e($room->status_badge); ?>"><?php echo e(str_replace('_', ' ', ucfirst($room->status))); ?></span>
                    </div>
                    <p class="text-2xl font-bold text-brand-700 mb-2">₱<?php echo e(number_format($room->rate, 2)); ?><span class="text-sm font-normal text-gray-400">/mo</span></p>
                    <p class="text-xs text-gray-500 mb-3">Max <?php echo e($room->max_occupants); ?> persons</p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($room->amenities): ?>
                        <div class="flex flex-wrap gap-1 mb-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $room->amenities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $amenity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-0.5 text-[11px] text-gray-600 ring-1 ring-inset ring-gray-200"><?php echo e($amenity); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($room->description): ?>
                        <p class="text-xs text-gray-500"><?php echo e($room->description); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-span-full text-center py-12 text-gray-400">No rooms match your filters.</div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </section>

    
    <section id="inquire" class="bg-white border-t border-gray-200">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 py-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Inquire About a Room</h2>
            <p class="text-sm text-gray-500 mb-6">Send us your details and we'll get back to you regarding availability.</p>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($inquirySent): ?>
                <div class="rounded-lg bg-green-50 p-4 text-sm text-green-800 border border-green-200">
                    Your inquiry has been sent! Our General Manager will contact you shortly.
                </div>
            <?php else: ?>
                <form wire:submit="submitInquiry" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Your Name *</label>
                            <input wire:model="sender_name" type="text" class="form-input">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['sender_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <label class="form-label">Contact Number *</label>
                            <input wire:model="contact_number" type="text" class="form-input">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['contact_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Email (optional)</label>
                            <input wire:model="inquiryEmail" type="email" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Preferred Room Type</label>
                            <select wire:model="preferred_room_type" class="form-input">
                                <option value="any">Any</option>
                                <option value="small">Small</option>
                                <option value="big">Big</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Message *</label>
                        <textarea wire:model="message" rows="3" class="form-input"></textarea>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <button type="submit" class="btn-primary">Send Inquiry</button>
                </form>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </section>
</div>
<?php /**PATH C:\laragon\www\citiescapes\resources\views/livewire/public/room-listings.blade.php ENDPATH**/ ?>