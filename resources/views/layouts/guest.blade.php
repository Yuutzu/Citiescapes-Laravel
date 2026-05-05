<!DOCTYPE html>
<html lang="en" class="h-full bg-brand-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Citiescapes' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full">
    <div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="flex justify-center">
                <div class="h-14 w-14 rounded-2xl bg-brand-700 flex items-center justify-center shadow-lg ring-4 ring-brand-800">
                    <span class="text-white font-bold text-2xl">CS</span>
                </div>
            </div>
            <h2 class="mt-5 text-center text-3xl font-bold tracking-tight text-white">
                Citiescapes
            </h2>
            <p class="text-center text-sm text-brand-300 mt-1">Apartment Rental Management</p>
        </div>
        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white px-6 py-8 shadow-2xl ring-1 ring-brand-900/20 sm:rounded-2xl sm:px-10">
                {{-- Flash messages --}}
                @if(session('error'))
                    <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-800 border border-red-200">{{ session('error') }}</div>
                @endif
                @if(session('success'))
                    <div class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-800 border border-green-200">{{ session('success') }}</div>
                @endif
                {{ $slot }}
            </div>
        </div>
    </div>
    @livewireScripts
</body>
</html>
