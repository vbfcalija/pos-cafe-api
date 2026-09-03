<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // NativePHP's mobile builds already run `migrate --force`
        // automatically on first app extraction — calling it again here on
        // every request bootstrap turned out to break NativePHP's
        // "persistent" runtime mode (it errors during persistent_boot and
        // silently falls back to a slower per-request classic mode), for no
        // benefit since migrate is already handled natively.
    }
}
