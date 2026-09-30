<?php

namespace App\Providers;

use App\Listeners\TrackUserLogin;
use App\Listeners\TrackUserLogout;
use App\Models\StockMovement;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
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
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        Event::listen(Login::class, [TrackUserLogin::class, 'handle']);
        Event::listen(Logout::class, [TrackUserLogout::class, 'handle']);
        StockMovement::observe(\App\Observers\StockMovementObserver::class);
    }
}
