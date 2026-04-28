<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($title ?? 'Citiescapes'); ?></title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>

<body class="h-full">
    <a href="/" class="fixed top-6 right-6 btn-primary px-6 py-2 z-50">Return</a>
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
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
                    <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-800 border border-red-200">
                        <?php echo e(session('error')); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                    <div class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-800 border border-green-200">
                        <?php echo e(session('success')); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php echo e($slot); ?>

            </div>
        </div>
    </div>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

</body>

</html><?php /**PATH C:\laragon\www\citiescapes\resources\views/layouts/guest.blade.php ENDPATH**/ ?>