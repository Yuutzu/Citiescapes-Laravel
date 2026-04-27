<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Currency formatting helper
        Blade::directive('peso', function ($amount) {
            return "<?php echo '₱ ' . number_format($amount, 2); ?>";
        });
    }
}
