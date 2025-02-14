<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;

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
        Vite::prefetch(concurrency: 3);



        // if (app()->environment('local')) {
        //     URL::forceScheme('https');
        // }



        // Force HTTPS in production
        if (env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
            Config::set('session.secure', true);
            Config::set('app.url', env('APP_URL'));
            Config::set('app.asset_url', env('ASSET_URL'));
        }
    }
}
