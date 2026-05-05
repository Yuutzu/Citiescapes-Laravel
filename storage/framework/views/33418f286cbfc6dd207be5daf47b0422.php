<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($title ?? 'Citiescapes — Rooms'); ?></title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>

<body class="bg-gray-50">
    
    <header class="bg-white shadow-sm">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <a href="/" class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-lg bg-brand-700 flex items-center justify-center">
                        <span class="text-white font-bold text-sm">CS</span>
                    </div>
                    <span class="font-bold text-brand-900 text-lg">Citiescapes</span>
                </a>
                <div class="flex items-center gap-4">
                    <a href="/#rooms" class="text-sm font-medium text-gray-600 hover:text-gray-900">Rooms</a>
                    <a href="/#inquire" class="text-sm font-medium text-gray-600 hover:text-gray-900">Inquire</a>
                    <a href="<?php echo e(route('login')); ?>" class="btn-primary text-sm">Login</a>
                </div>
            </div>
        </div>
    </header>

    <main>
        <?php echo e($slot); ?>

    </main>

    <footer class="bg-brand-900 mt-16">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-brand-300">
                &copy; <?php echo e(date('Y')); ?> Citiescapes Apartment Rental &bull; Remedios St., Bajada, Davao City
            </p>
        </div>
    </footer>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

</body>

</html><?php /**PATH C:\laragon\www\citiescapes\resources\views/layouts/public.blade.php ENDPATH**/ ?>