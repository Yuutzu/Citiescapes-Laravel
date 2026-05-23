<div>
    {{-- Hero with building photo backdrop --}}
    <section class="relative text-white overflow-hidden">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image:url('/storage/building/hero.jpg');"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-brand-950/85 via-brand-900/75 to-brand-800/70"></div>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-24 sm:py-32 text-center">
            <span class="cs-anim-fade-down inline-block text-[11px] uppercase tracking-[0.32em] text-marigold-300 font-semibold mb-5">Bajada · Davao City</span>
            <h1 class="cs-anim-fade-up cs-delay-100 font-serif text-4xl sm:text-5xl lg:text-6xl font-semibold tracking-tight">Welcome to <span class="text-marigold-300">Citiescapes</span></h1>
            <p class="cs-anim-fade-up cs-delay-200 mt-5 text-base sm:text-lg text-paper-100/90 max-w-2xl mx-auto">Comfortable, quality, and affordable room rentals — a quiet warm-tiled apartment just off Remedios Street.</p>
            <div class="cs-anim-fade-up cs-delay-300 mt-7 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 sm:gap-6 text-xs uppercase tracking-[0.24em] text-paper-200/80">
                <span><i class="fas fa-building text-marigold-300 mr-1.5"></i> 22 Rooms</span>
                <span class="hidden sm:inline-block h-3 w-px bg-paper-200/30"></span>
                <span><i class="fas fa-layer-group text-marigold-300 mr-1.5"></i> 3 Floors</span>
                <span class="hidden sm:inline-block h-3 w-px bg-paper-200/30"></span>
                <span><i class="fas fa-shield-halved text-marigold-300 mr-1.5"></i> 24/7 Secure</span>
            </div>
            <div class="cs-anim-fade-up cs-delay-400 mt-10 flex flex-wrap items-center justify-center gap-3">
                <a href="#rooms" class="inline-flex items-center gap-2 rounded-md bg-marigold-400 hover:bg-marigold-300 text-brand-950 text-sm font-semibold px-5 py-2.5 shadow-md transition cs-anim-pulse-soft">
                    Browse Rooms <i class="fas fa-arrow-down text-xs"></i>
                </a>
                <a href="#inquire" class="inline-flex items-center gap-2 rounded-md border border-paper-200/40 hover:bg-white/10 text-paper-50 text-sm font-medium px-5 py-2.5 transition">
                    Send an Inquiry
                </a>
            </div>
        </div>
    </section>

    {{-- ============ ROOMS SPECIFICATIONS ============ --}}
    <section id="rooms" class="py-20 bg-paper-100">
        <div class="text-center mb-12">
            <span class="inline-block text-[11px] uppercase tracking-[0.32em] text-brand-600 font-semibold mb-3">Choose your space</span>
            <h2 class="font-serif text-4xl font-semibold text-brand-900">Room Specifications</h2>
            <div class="mt-4 mx-auto h-0.5 w-16 rounded-full bg-marigold-400"></div>
            <p class="mt-4 text-sm text-brand-700/80">Two room types to fit students, professionals, or sharing families.</p>
        </div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">

            {{-- ===== COMPACT (SMALL) ROOM ===== --}}
            <div class="bg-white rounded-2xl shadow-lg ring-1 ring-brand-200 overflow-hidden" x-data="{
                     photos: @js($compactPhotos),
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
                            class="inline-flex items-center gap-1.5 rounded-full bg-white/95 px-3 py-1 text-xs font-semibold text-brand-800 shadow-sm">
                            <i class="fas fa-door-closed text-brand-700"></i> {{ $cards['compact']['title'] }}
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
                            <h3 class="text-xl font-bold text-brand-900">{{ $cards['compact']['title'] }}</h3>
                            <p class="mt-0.5 text-xs text-brand-500">{{ $cards['compact']['subtitle'] }}</p>
                        </div>
                        <div class="ml-4 shrink-0 text-right">
                            <p class="text-xl font-extrabold text-brand-900">
                                ₱{{ number_format($compactRoom?->rate ?? $cards['compact']['price'], 2) }}</p>
                            <p class="text-xs text-brand-400">per month</p>
                        </div>
                    </div>

                    {{-- Meta --}}
                    <div class="flex flex-wrap items-center gap-3 text-xs text-brand-600 mb-5">
                        <span class="flex items-center gap-1">
                            <i class="fas fa-users text-brand-600"></i>
                            Max {{ $compactRoom?->max_occupants ?? $cards['compact']['max_occupants'] }} persons
                        </span>
                        <span class="flex items-center gap-1 font-semibold text-green-700">
                            <i class="fas fa-circle-check"></i>
                            {{ $compactCount }} available now
                        </span>
                    </div>

                    {{-- Amenities --}}
                    <div class="border-t border-brand-100 pt-4">
                        <p class="mb-2.5 text-[11px] font-semibold uppercase tracking-wider text-brand-400">Amenities</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($cards['compact']['amenities'] ?? [] as $am)
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-brand-800 ring-1 ring-inset ring-brand-300">
                                    <i class="fas {{ $am['icon'] ?? 'fa-circle' }}"></i> {{ $am['label'] ?? '' }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <button type="button" wire:click="inquireAbout('compact')" class="btn-primary w-full justify-center mt-5">
                        <i class="fas fa-paper-plane mr-2"></i> Inquire About This Room
                    </button>
                </div>
            </div>

            {{-- ===== SPACIOUS (BIG) ROOM ===== --}}
            <div class="bg-white rounded-2xl shadow-lg ring-1 ring-brand-200 overflow-hidden" x-data="{
                     photos: @js($spaciousPhotos),
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
                            class="inline-flex items-center gap-1.5 rounded-full bg-white/95 px-3 py-1 text-xs font-semibold text-brand-800 shadow-sm">
                            <i class="fas fa-bed text-brand-700"></i> {{ $cards['spacious']['title'] }}
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
                            <h3 class="text-xl font-bold text-brand-900">{{ $cards['spacious']['title'] }}</h3>
                            <p class="mt-0.5 text-xs text-brand-500">{{ $cards['spacious']['subtitle'] }}</p>
                        </div>
                        <div class="ml-4 shrink-0 text-right">
                            <p class="text-xl font-extrabold text-brand-900">
                                ₱{{ number_format($spaciousRoom?->rate ?? $cards['spacious']['price'], 2) }}</p>
                            <p class="text-xs text-brand-400">per month</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 text-xs text-brand-600 mb-5">
                        <span class="flex items-center gap-1">
                            <i class="fas fa-users text-brand-600"></i>
                            Max {{ $spaciousRoom?->max_occupants ?? $cards['spacious']['max_occupants'] }} persons
                        </span>
                        <span class="flex items-center gap-1 font-semibold text-green-700">
                            <i class="fas fa-circle-check"></i>
                            {{ $spaciousCount }} available now
                        </span>
                    </div>

                    <div class="border-t border-brand-100 pt-4">
                        <p class="mb-2.5 text-[11px] font-semibold uppercase tracking-wider text-brand-400">Amenities</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($cards['spacious']['amenities'] ?? [] as $am)
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-brand-800 ring-1 ring-inset ring-brand-300">
                                    <i class="fas {{ $am['icon'] ?? 'fa-circle' }}"></i> {{ $am['label'] ?? '' }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <button type="button" wire:click="inquireAbout('spacious')" class="btn-primary w-full justify-center mt-5">
                        <i class="fas fa-paper-plane mr-2"></i> Inquire About This Room
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- Inquiry + Map --}}
    <section id="inquire" class="bg-white border-t border-gray-200"
        x-data
        x-on:scroll-to-inquiry.window="document.getElementById('inquire')?.scrollIntoView({behavior:'smooth', block:'start'})">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex flex-col lg:flex-row lg:items-stretch gap-8 lg:gap-0">

                {{-- Left: Google Map --}}
                <div class="w-full lg:w-1/2 lg:pr-12">
                    <h2 class="text-2xl font-bold text-brand-900 mb-2">Find Us Here</h2>
                    <p class="text-sm text-gray-500 mb-4">Remedios St., Bajada, Davao City</p>
                    <div class="rounded-2xl overflow-hidden shadow-lg ring-1 ring-brand-200 h-64 sm:h-80 lg:h-[420px]">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1979.6695606739013!2d125.60583978519254!3d7.086629150816199!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x32f96d007bbc275d%3A0x3db24c0e9ba43fdc!2sMQCJS%20Apartment!5e0!3m2!1sen!2sph!4v1777975215750!5m2!1sen!2sph"
                            width="100%" height="100%" style="border:0;"
                            allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>

                {{-- Divider: vertical on desktop, horizontal on mobile --}}
                <div class="hidden lg:block w-px bg-paper-300 shrink-0"></div>
                <div class="lg:hidden h-px bg-paper-300"></div>

                {{-- Right: Inquiry Form --}}
                <div class="w-full lg:w-1/2 lg:pl-12 flex flex-col">
                    <h2 class="text-2xl font-bold text-brand-900 mb-2">Inquire About a Room</h2>
                    <p class="text-sm text-gray-500 mb-6">Send us your details and we'll get back to you regarding availability.</p>

                    @if($inquirySent)
                        <div class="rounded-lg bg-green-50 p-4 text-sm text-green-800 border border-green-200">
                            Your inquiry has been sent! Our General Manager will contact you shortly.
                        </div>
                    @else
                        <form wire:submit="submitInquiry" class="flex flex-col flex-1 gap-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">Email (optional)</label>
                                    <input wire:model="inquiryEmail" type="email" class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">Preferred Room Type</label>
                                    <select wire:model="preferred_room_type" class="form-input">
                                        <option value="any">Any</option>
                                        <option value="compact">Compact</option>
                                        <option value="spacious">Spacious</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex flex-col flex-1">
                                <label class="form-label">Message *</label>
                                <textarea wire:model="message" class="form-input flex-1 resize-none min-h-[120px]"></textarea>
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