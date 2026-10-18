<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        $this->configureRateLimiters();
    }

    /**
     * Named rate limiters (ADMIN_PANEL.md §14, SECURITY.md §9). Later phases
     * add `media-import`, `contact` and `search` here.
     */
    private function configureRateLimiters(): void
    {
        RateLimiter::for('admin-login', function (Request $request) {
            $key = sha1('admin-login|'.$request->method().'|'.$request->ip());

            return Limit::perMinute(5)
                ->by($key)
                ->response(fn () => back()->withErrors([
                    'passcode' => 'Too many attempts. Please wait a minute and try again.',
                ]));
        });
    }
}
