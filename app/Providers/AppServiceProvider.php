<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Currency formatting helper
        Blade::directive('peso', function ($amount) {
            return "<?php echo '₱ ' . number_format($amount, 2); ?>";
        });

        // Force HTTPS scheme for generated URLs in production. Hostinger
        // terminates TLS upstream, so without this Laravel would emit
        // http:// asset/route URLs and trigger mixed-content warnings.
        // Gated behind APP_FORCE_HTTPS so local Laragon over plain HTTP
        // keeps working.
        if ($this->app->environment('production') && env('APP_FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }
    }
}
