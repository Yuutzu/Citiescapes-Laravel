<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">

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
                <div class="h-12 w-12 rounded-xl bg-brand-700 flex items-center justify-center">
                    <span class="text-white font-bold text-xl">CS</span>
                </div>
            </div>
            <h2 class="mt-4 text-center text-2xl font-bold tracking-tight text-gray-900">
                Citiescapes
            </h2>
            <p class="text-center text-sm text-gray-500">Apartment Rental Management</p>
        </div>
        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white px-6 py-8 shadow-sm ring-1 ring-gray-950/5 sm:rounded-xl sm:px-10">
                {{-- Flash messages --}}
                @if(session('error'))
                    <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-800 border border-red-200">
                        {{ session('error') }}
                    </div>
                @endif
                @if(session('success'))
                    <div class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-800 border border-green-200">
                        {{ session('success') }}
                    </div>
                @endif
                {{ $slot }}
            </div>
        </div>
    </div>
    @livewireScripts
</body>

</html>