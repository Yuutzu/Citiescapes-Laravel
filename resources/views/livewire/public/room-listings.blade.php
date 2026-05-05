<div>
    {{-- Hero --}}
    <section class="bg-brand-900 text-white py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl font-bold tracking-tight">Welcome to Citiescapes</h1>
            <p class="mt-3 text-lg text-brand-200">Comfortable, quality, and affordable room rentals in Bajada, Davao
                City</p>
            <p class="mt-1 text-sm text-brand-300">Remedios St., Bajada, Davao City &bull; 22 Rooms &bull; 3 Floors</p>
        </div>
    </section>

    {{-- ============ ROOMS SPECIFICATIONS ============ --}}
    <section id="rooms" class="py-16 bg-brand-100">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-brand-900">Rooms Specifications</h2>
            <div class="mt-3 mx-auto h-1 w-16 rounded-full bg-brand-700"></div>
            <p class="mt-4 text-sm text-brand-600">Choose the room type that fits your needs</p>
        </div>

        <div class="max-w-5xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-8">

            {{-- ===== COMPACT (SMALL) ROOM ===== --}}
            <div class="bg-white rounded-2xl shadow-lg ring-1 ring-brand-200 overflow-hidden" x-data="{
                     photos: @js($smallPhotos),
                     current: 0,
                     timer: null,
                     next() { this.current = (this.current + 1) % this.photos.length; },
                     prev() { this.current = (this.current - 1 + this.photos.length) % this.photos.length; },
                     startAuto() { this.timer = setInterval(() => this.next(), 4000); },
                     stopAuto()  { clearInterval(this.timer); }
                 }" x-init="startAuto()" @mouseenter="stopAuto()" @mouseleave="startAuto()">

                {{-- Image Carousel --}}
                <div class="relative h-64 sm:h-72 overflow-hidden bg-gradient-to-br from-brand-300 to-brand-600">

                    {{-- Slides --}}
                    <template x-for="(photo, idx) in photos" :key="idx">
                        <div class="absolute inset-0 transition-opacity duration-500"
                            :class="current === idx ? 'opacity-100' : 'opacity-0'"
                            :style="`background-image:url('${photo}');background-size:cover;background-position:center;`">
                        </div>
                    </template>

                    {{-- Placeholder icon --}}
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none select-none">
                        <i class="fas fa-door-closed text-8xl text-white/10"></i>
                    </div>

                    {{-- Room-type badge --}}
                    <div class="absolute top-3 left-3 z-10">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-white/95 px-3 py-1 text-xs font-semibold text-brand-800 shadow-sm backdrop-blur-sm">
                            <i class="fas fa-door-closed text-brand-700"></i> Compact Room
                        </span>
                    </div>

                    {{-- Prev arrow --}}
                    <button @click="prev(); stopAuto(); startAuto()"
                        class="absolute left-3 top-1/2 -translate-y-1/2 z-10 h-9 w-9 rounded-full bg-white/90 hover:bg-white text-brand-700 shadow-md flex items-center justify-center transition hover:scale-105 focus:outline-none focus:ring-2 focus:ring-brand-400">
                        <i class="fas fa-chevron-left text-sm"></i>
                    </button>

                    {{-- Next arrow --}}
                    <button @click="next(); stopAuto(); startAuto()"
                        class="absolute right-3 top-1/2 -translate-y-1/2 z-10 h-9 w-9 rounded-full bg-white/90 hover:bg-white text-brand-700 shadow-md flex items-center justify-center transition hover:scale-105 focus:outline-none focus:ring-2 focus:ring-brand-400">
                        <i class="fas fa-chevron-right text-sm"></i>
                    </button>

                    {{-- Dot indicators --}}
                    <div class="absolute bottom-3 left-1/2 -translate-x-1/2 z-10 flex items-center gap-1.5">
                        <template x-for="(photo, idx) in photos" :key="idx">
                            <button @click="current = idx; stopAuto(); startAuto()"
                                class="h-1.5 rounded-full transition-all duration-300 focus:outline-none"
                                :class="current === idx ? 'w-5 bg-brand-900' : 'w-1.5 bg-white/60 hover:bg-white'">
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Details & Tags --}}
                <div class="p-6">
                    {{-- Title + Price --}}
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-brand-900">Compact Room</h3>
                            <p class="mt-0.5 text-xs text-brand-500">Solo professionals &amp; students</p>
                        </div>
                        <div class="ml-4 shrink-0 text-right">
                            <p class="text-xl font-extrabold text-brand-900">
                                ₱{{ number_format($smallRoom?->rate ?? 3500, 2) }}</p>
                            <p class="text-xs text-brand-400">per month</p>
                        </div>
                    </div>

                    {{-- Meta --}}
                    <div class="flex flex-wrap items-center gap-3 text-xs text-brand-600 mb-5">
                        <span class="flex items-center gap-1">
                            <i class="fas fa-users text-brand-600"></i>
                            Max {{ $smallRoom?->max_occupants ?? 3 }} persons
                        </span>
                        <span class="flex items-center gap-1 font-semibold text-green-700">
                            <i class="fas fa-circle-check"></i>
                            {{ $smallCount }} available now
                        </span>
                    </div>

                    {{-- Amenities --}}
                    <div class="border-t border-brand-100 pt-4">
                        <p class="mb-2.5 text-[11px] font-semibold uppercase tracking-wider text-brand-400">Amenities
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-brand-800 ring-1 ring-inset ring-brand-300">
                                <i class="fas fa-snowflake"></i> Air Conditioner
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-brand-800 ring-1 ring-inset ring-brand-300">
                                <i class="fas fa-wifi"></i> WiFi
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-brand-800 ring-1 ring-inset ring-brand-300">
                                <i class="fas fa-table"></i> Tables
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-brand-800 ring-1 ring-inset ring-brand-300">
                                <i class="fas fa-chair"></i> Chairs
                            </span>
                        </div>
                    </div>

                    <a href="#inquire" class="btn-primary w-full justify-center mt-5">
                        <i class="fas fa-paper-plane mr-2"></i> Inquire About This Room
                    </a>
                </div>
            </div>

            {{-- ===== SPACIOUS (BIG) ROOM ===== --}}
            <div class="bg-white rounded-2xl shadow-lg ring-1 ring-brand-200 overflow-hidden" x-data="{
                     photos: @js($bigPhotos),
                     current: 0,
                     timer: null,
                     next() { this.current = (this.current + 1) % this.photos.length; },
                     prev() { this.current = (this.current - 1 + this.photos.length) % this.photos.length; },
                     startAuto() { this.timer = setInterval(() => this.next(), 4500); },
                     stopAuto()  { clearInterval(this.timer); }
                 }" x-init="startAuto()" @mouseenter="stopAuto()" @mouseleave="startAuto()">

                {{-- Image Carousel --}}
                <div class="relative h-64 sm:h-72 overflow-hidden bg-gradient-to-br from-brand-500 to-brand-800">

                    <template x-for="(photo, idx) in photos" :key="idx">
                        <div class="absolute inset-0 transition-opacity duration-500"
                            :class="current === idx ? 'opacity-100' : 'opacity-0'"
                            :style="`background-image:url('${photo}');background-size:cover;background-position:center;`">
                        </div>
                    </template>

                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none select-none">
                        <i class="fas fa-bed text-8xl text-white/10"></i>
                    </div>

                    <div class="absolute top-3 left-3 z-10">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-white/95 px-3 py-1 text-xs font-semibold text-brand-800 shadow-sm backdrop-blur-sm">
                            <i class="fas fa-bed text-brand-700"></i> Spacious Room
                        </span>
                    </div>

                    <button @click="prev(); stopAuto(); startAuto()"
                        class="absolute left-3 top-1/2 -translate-y-1/2 z-10 h-9 w-9 rounded-full bg-white/90 hover:bg-white text-brand-700 shadow-md flex items-center justify-center transition hover:scale-105 focus:outline-none focus:ring-2 focus:ring-brand-400">
                        <i class="fas fa-chevron-left text-sm"></i>
                    </button>

                    <button @click="next(); stopAuto(); startAuto()"
                        class="absolute right-3 top-1/2 -translate-y-1/2 z-10 h-9 w-9 rounded-full bg-white/90 hover:bg-white text-brand-700 shadow-md flex items-center justify-center transition hover:scale-105 focus:outline-none focus:ring-2 focus:ring-brand-400">
                        <i class="fas fa-chevron-right text-sm"></i>
                    </button>

                    <div class="absolute bottom-3 left-1/2 -translate-x-1/2 z-10 flex items-center gap-1.5">
                        <template x-for="(photo, idx) in photos" :key="idx">
                            <button @click="current = idx; stopAuto(); startAuto()"
                                class="h-1.5 rounded-full transition-all duration-300 focus:outline-none"
                                :class="current === idx ? 'w-5 bg-brand-900' : 'w-1.5 bg-white/60 hover:bg-white'">
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Details & Tags --}}
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-brand-900">Spacious Room</h3>
                            <p class="mt-0.5 text-xs text-brand-500">Couples, families &amp; sharing</p>
                        </div>
                        <div class="ml-4 shrink-0 text-right">
                            <p class="text-xl font-extrabold text-brand-900">
                                ₱{{ number_format($bigRoom?->rate ?? 5000, 2) }}</p>
                            <p class="text-xs text-brand-400">per month</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 text-xs text-brand-600 mb-5">
                        <span class="flex items-center gap-1">
                            <i class="fas fa-users text-brand-600"></i>
                            Max {{ $bigRoom?->max_occupants ?? 4 }} persons
                        </span>
                        <span class="flex items-center gap-1 font-semibold text-green-700">
                            <i class="fas fa-circle-check"></i>
                            {{ $bigCount }} available now
                        </span>
                    </div>

                    <div class="border-t border-brand-100 pt-4">
                        <p class="mb-2.5 text-[11px] font-semibold uppercase tracking-wider text-brand-400">Amenities
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-brand-800 ring-1 ring-inset ring-brand-300">
                                <i class="fas fa-snowflake"></i> Air Conditioner
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-brand-800 ring-1 ring-inset ring-brand-300">
                                <i class="fas fa-wifi"></i> WiFi
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-brand-800 ring-1 ring-inset ring-brand-300">
                                <i class="fas fa-table"></i> Tables
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-brand-800 ring-1 ring-inset ring-brand-300">
                                <i class="fas fa-chair"></i> Chairs
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-brand-800 ring-1 ring-inset ring-brand-300">
                                <i class="fas fa-layer-group"></i> Extra Double Deck Frame
                            </span>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-brand-800 ring-1 ring-inset ring-brand-300">
                                <i class="fas fa-bed"></i> Extra Mattress
                            </span>
                        </div>
                    </div>

                    <a href="#inquire" class="btn-primary w-full justify-center mt-5">
                        <i class="fas fa-paper-plane mr-2"></i> Inquire About This Room
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Inquiry + Map --}}
    <section id="inquire" class="bg-white border-t border-gray-200">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-12">
            <div style="display:flex; flex-direction:row; gap:0; align-items:stretch;">

                {{-- Left: Google Map --}}
                <div style="width:50%; padding-right:3rem;">
                    <h2 class="text-2xl font-bold text-brand-900 mb-2">Find Us Here</h2>
                    <p class="text-sm text-gray-500 mb-4">Remedios St., Bajada, Davao City</p>
                    <div class="rounded-2xl overflow-hidden shadow-lg ring-1 ring-brand-200" style="height:420px;">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1979.6695606739013!2d125.60583978519254!3d7.086629150816199!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x32f96d007bbc275d%3A0x3db24c0e9ba43fdc!2sMQCJS%20Apartment!5e0!3m2!1sen!2sph!4v1777975215750!5m2!1sen!2sph"
                            width="100%" height="100%" style="border:0;"
                            allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>

                {{-- Divider --}}
                <div style="width:1px; background-color:#c8d2e2; flex-shrink:0;"></div>

                {{-- Right: Inquiry Form --}}
                <div style="width:50%; padding-left:3rem; display:flex; flex-direction:column;">
                    <h2 class="text-2xl font-bold text-brand-900 mb-2">Inquire About a Room</h2>
                    <p class="text-sm text-gray-500 mb-6">Send us your details and we'll get back to you regarding availability.</p>

                    @if($inquirySent)
                        <div class="rounded-lg bg-green-50 p-4 text-sm text-green-800 border border-green-200">
                            Your inquiry has been sent! Our General Manager will contact you shortly.
                        </div>
                    @else
                        <form wire:submit="submitInquiry" style="display:flex; flex-direction:column; flex:1; gap:1rem;">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">Your Name *</label>
                                    <input wire:model="sender_name" type="text" class="form-input">
                                    @error('sender_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="form-label">Contact Number *</label>
                                    <input wire:model="contact_number" type="text" class="form-input">
                                    @error('contact_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
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
                            <div style="display:flex; flex-direction:column; flex:1;">
                                <label class="form-label">Message *</label>
                                <textarea wire:model="message" class="form-input" style="flex:1; resize:none;"></textarea>
                                @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <button type="submit" class="btn-primary w-full justify-center">Send Inquiry</button>
                            </div>
                        </form>
                    @endif
                </div>

            </div>
        </div>
    </section>
</div>