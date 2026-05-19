<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Citiescapes' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|lora:400,500,600,700" rel="stylesheet" />
    <link rel="icon" type="image/png" href="/storage/building/logo.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-brand-950 relative">
    {{-- Facade backdrop with warm overlay --}}
    <div class="fixed inset-0 bg-cover bg-center" style="background-image:url('/storage/building/facade.jpg');"></div>
    <div class="fixed inset-0 bg-gradient-to-br from-brand-950/85 via-brand-900/80 to-brand-800/85"></div>

    <div class="relative flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="flex justify-center">
                <div class="cs-anim-zoom-in h-16 w-16 rounded-2xl bg-marigold-100 flex items-center justify-center shadow-xl ring-2 ring-marigold-400/40 overflow-hidden">
                    <img src="/storage/building/logo.png" alt="Citiescapes" class="h-full w-full object-cover cs-anim-float">
                </div>
            </div>
            <h2 class="cs-anim-fade-down cs-delay-100 mt-5 text-center font-serif text-3xl font-semibold tracking-tight text-paper-50">
                Citiescapes
            </h2>
            <p class="cs-anim-fade-down cs-delay-200 text-center text-xs uppercase tracking-[0.28em] text-marigold-300 mt-2">Apartment Rental Management</p>
        </div>
        <div class="cs-anim-fade-up cs-delay-300 mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-paper-50/95 backdrop-blur-sm px-6 py-8 shadow-2xl ring-1 ring-brand-200 sm:rounded-2xl sm:px-10">
                {{-- Flash messages --}}
                @if(session('error'))
                    <div class="cs-anim-fade-down mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-800 border border-red-200">{{ session('error') }}</div>
                @endif
                @if(session('success'))
                    <div class="cs-anim-fade-down mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-800 border border-green-200">{{ session('success') }}</div>
                @endif
                {{ $slot }}
            </div>
            <p class="cs-anim-fade cs-delay-500 mt-6 text-center text-xs text-paper-200/80">
                Remedios St., Bajada · Davao City
            </p>
        </div>
    </div>
    @livewireScripts

    {{-- Global fade-in for the guest/login card --}}
    <script>
        document.addEventListener('livewire:navigated', () => {
            document.querySelectorAll('main, .bg-paper-50\\/95').forEach(el => {
                el.style.animation = 'none';
                void el.offsetWidth;
                el.style.animation = '';
            });
        });
    </script>
</body>
</html>
