<div>
    {{-- Hero --}}
    <section class="bg-brand-900 text-white py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3 mb-4">
                <a href="/" class="text-brand-300 hover:text-white text-sm flex items-center gap-1">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Back to Rooms
                </a>
            </div>
            <h1 class="text-4xl font-bold tracking-tight">{{ $label }} Rooms</h1>
            <p class="mt-2 text-brand-200">Citiescapes — Remedios St., Bajada, Davao City</p>
            <div class="flex flex-wrap gap-4 mt-4 text-sm">
                <span class="flex items-center gap-1.5 text-brand-200">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z"/></svg>
                    {{ $rooms->count() }} room(s) total
                </span>
                <span class="flex items-center gap-1.5 text-green-300">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="4"/></svg>
                    {{ $available }} available
                </span>
                @if($minRate)
                    <span class="text-brand-200">₱{{ number_format($minRate, 0) }}{{ $minRate != $maxRate ? ' – ₱' . number_format($maxRate, 0) : '' }}/mo</span>
                @endif
            </div>
        </div>
    </section>

    {{-- Photo Gallery --}}
    @if(count($allPhotos) > 0)
        <section class="bg-gray-900 py-6">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8"
                x-data="{
                    current: 0,
                    photos: {{ json_encode($allPhotos) }},
                    timer: null,
                    init() { this.timer = setInterval(() => this.next(), 4000); },
                    next() { this.current = (this.current + 1) % this.photos.length; },
                    prev() { this.current = (this.current - 1 + this.photos.length) % this.photos.length; },
                    goto(i) { this.current = i; clearInterval(this.timer); this.timer = setInterval(() => this.next(), 4000); }
                }">
                <div class="relative aspect-video rounded-xl overflow-hidden">
                    <template x-for="(photo, i) in photos" :key="i">
                        <img :src="photo" :alt="'Room photo ' + (i+1)"
                            class="absolute inset-0 w-full h-full object-cover transition-opacity duration-700"
                            :class="current === i ? 'opacity-100' : 'opacity-0'">
                    </template>
                    <button @click="prev" class="absolute left-3 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-black/60 text-white flex items-center justify-center hover:bg-black/60">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                    </button>
                    <button @click="next" class="absolute right-3 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-black/60 text-white flex items-center justify-center hover:bg-black/60">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </button>
                    <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-2">
                        <template x-for="(photo, i) in photos" :key="i">
                            <button @click="goto(i)" class="h-2 w-2 rounded-full transition-colors" :class="current === i ? 'bg-white' : 'bg-white/40'"></button>
                        </template>
                    </div>
                    <div class="absolute top-3 right-3 bg-black/60 text-white text-xs px-2 py-1 rounded-full">
                        <span x-text="current + 1"></span> / <span x-text="photos.length"></span>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Common Amenities --}}
    @if(count($amenities) > 0)
        <section class="border-b border-gray-200 py-5">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap gap-2 items-center">
                    <span class="text-sm font-medium text-gray-500 mr-1">Amenities:</span>
                    @foreach($amenities as $amenity)
                        <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-sm text-brand-700 ring-1 ring-inset ring-brand-200">{{ $amenity }}</span>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Room List --}}
    <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
        <h2 class="text-xl font-bold text-gray-900 mb-6">All {{ $label }} Rooms</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($rooms as $room)
                <div class="card hover:shadow-md transition-shadow"
                    x-data="{
                        current: 0,
                        photos: {{ json_encode($room->photos ?? []) }},
                        timer: null,
                        init() { if (this.photos.length > 1) this.timer = setInterval(() => { this.current = (this.current + 1) % this.photos.length; }, 3500); }
                    }">
                    {{-- Room card photo --}}
                    @if($room->photos && count($room->photos) > 0)
                        <div class="relative -mx-5 -mt-5 mb-4 aspect-video rounded-t-xl overflow-hidden bg-gray-100">
                            <template x-for="(photo, i) in photos" :key="i">
                                <img :src="photo" :alt="'Room {{ $room->room_number }}'"
                                    class="absolute inset-0 w-full h-full object-cover transition-opacity duration-700"
                                    :class="current === i ? 'opacity-100' : 'opacity-0'">
                            </template>
                            @if(count($room->photos) > 1)
                                <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-1.5">
                                    <template x-for="(p, i) in photos" :key="i">
                                        <div class="h-1.5 w-1.5 rounded-full transition-colors" :class="current === i ? 'bg-white' : 'bg-white/40'"></div>
                                    </template>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="-mx-5 -mt-5 mb-4 aspect-video rounded-t-xl bg-gradient-to-br from-brand-100 to-brand-200 flex items-center justify-center">
                            <svg class="h-12 w-12 text-brand-400" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                            </svg>
                        </div>
                    @endif

                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <h3 class="font-semibold text-gray-900">Room {{ $room->room_number }}</h3>
                            <p class="text-xs text-gray-500">Floor {{ $room->floor_level }} &bull; Max {{ $room->max_occupants }} person(s)</p>
                        </div>
                        <span class="badge {{ $room->status_badge }}">{{ str_replace('_', ' ', ucfirst($room->status)) }}</span>
                    </div>
                    <p class="text-2xl font-bold text-brand-700 mb-3">₱{{ number_format($room->rate, 2) }}<span class="text-sm font-normal text-gray-400">/mo</span></p>
                    @if($room->amenities)
                        <div class="flex flex-wrap gap-1 mb-3">
                            @foreach($room->amenities as $a)
                                <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-0.5 text-[11px] text-gray-600 ring-1 ring-inset ring-gray-200">{{ $a }}</span>
                            @endforeach
                        </div>
                    @endif
                    @if($room->description)
                        <p class="text-xs text-gray-500 leading-relaxed">{{ $room->description }}</p>
                    @endif
                </div>
            @empty
                <div class="col-span-full text-center py-12 text-gray-400">No {{ strtolower($label) }} rooms found.</div>
            @endforelse
        </div>
    </section>

    {{-- Inquiry CTA --}}
    <section class="bg-brand-900 text-white py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl font-bold mb-3">Interested in a {{ $label }} Room?</h2>
            <p class="text-brand-200 mb-6">Send us your details and we'll get back to you regarding availability and pricing.</p>
            <a href="/#inquire" class="inline-flex items-center gap-2 bg-white text-brand-900 font-semibold px-6 py-3 rounded-lg hover:bg-brand-50 transition-colors">
                Inquire Now
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            </a>
        </div>
    </section>
</div>
