<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Citiescapes — Rooms' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|lora:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="icon" type="image/png" href="/storage/building/logo.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-paper-100 text-brand-950">
    {{-- Public top nav --}}
    <header class="bg-paper-50/95 backdrop-blur-sm shadow-sm border-b border-paper-300/60 sticky top-0 z-30" x-data="{ navOpen: false }">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between gap-3">
                <a href="/" class="flex items-center gap-3 min-w-0">
                    <img src="/storage/building/logo.png" alt="Citiescapes" class="h-9 w-9 rounded-lg object-cover ring-1 ring-brand-200 shrink-0">
                    <div class="leading-tight min-w-0">
                        <span class="block font-serif font-bold text-brand-900 text-base sm:text-lg truncate">Citiescapes</span>
                        <span class="hidden sm:block text-[10px] uppercase tracking-[0.18em] text-brand-700/80">Apartment Rental</span>
                    </div>
                </a>

                {{-- Desktop links --}}
                <div class="hidden sm:flex items-center gap-6">
                    <a href="/#rooms" class="text-sm font-medium text-brand-800 hover:text-brand-600 transition">Rooms</a>
                    <a href="/#inquire" class="text-sm font-medium text-brand-800 hover:text-brand-600 transition">Inquire</a>
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-md bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-4 py-2 shadow-sm transition">
                        <i class="fas fa-arrow-right-to-bracket text-xs"></i> Login
                    </a>
                </div>

                {{-- Mobile: login pill + hamburger --}}
                <div class="flex sm:hidden items-center gap-2">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 rounded-md bg-brand-700 hover:bg-brand-800 text-white text-xs font-semibold px-3 py-1.5 shadow-sm transition">
                        <i class="fas fa-arrow-right-to-bracket text-[10px]"></i> Login
                    </a>
                    <button @click="navOpen = !navOpen" class="p-2 -mr-2 text-brand-800 hover:text-brand-600" aria-label="Toggle menu">
                        <i class="fas" :class="navOpen ? 'fa-xmark' : 'fa-bars'"></i>
                    </button>
                </div>
            </div>

            {{-- Mobile dropdown panel --}}
            <div x-show="navOpen" x-cloak x-transition class="sm:hidden border-t border-paper-300/60 py-2">
                <a href="/#rooms" @click="navOpen = false" class="block px-2 py-2 text-sm font-medium text-brand-800 hover:bg-paper-100 rounded">Rooms</a>
                <a href="/#inquire" @click="navOpen = false" class="block px-2 py-2 text-sm font-medium text-brand-800 hover:bg-paper-100 rounded">Inquire</a>
            </div>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="bg-brand-900 mt-16">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 text-center">
            <div class="flex items-center justify-center gap-2 mb-2">
                <img src="/storage/building/logo.png" alt="" class="h-6 w-6 opacity-90">
                <span class="font-serif text-marigold-200 text-sm tracking-wide">Citiescapes Apartment</span>
            </div>
            <p class="text-xs text-brand-200/80">
                &copy; {{ date('Y') }} &bull; Remedios St., Bajada, Davao City
            </p>
        </div>
    </footer>
    @livewireScripts

    {{-- Global page-transition animation for wire:navigate --}}
    <script>
        document.addEventListener('livewire:navigating', () => {
            document.querySelectorAll('main').forEach(el => el.classList.add('cs-leaving'));
        });
        document.addEventListener('livewire:navigated', () => {
            document.querySelectorAll('main').forEach(el => {
                el.classList.remove('cs-leaving');
                el.style.animation = 'none';
                void el.offsetWidth;
                el.style.animation = '';
            });
        });
    </script>
</body>

</html>