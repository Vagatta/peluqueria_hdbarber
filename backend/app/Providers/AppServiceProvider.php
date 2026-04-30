<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('api', fn (Request $r) => Limit::perMinute(60)->by($r->user()?->id ?: $r->ip()));

        RateLimiter::for('login', function (Request $r) {
            $max = (int) env('THROTTLE_LOGIN_MAX', 5);
            $decay = (int) env('THROTTLE_LOGIN_DECAY', 1);
            return Limit::perMinutes($decay, $max)->by(strtolower((string) $r->input('email')).'|'.$r->ip());
        });
    }
}
