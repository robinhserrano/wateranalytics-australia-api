<?php

namespace App\Providers;

use App\Models\User;
use Laravel\Pulse\Facades\Pulse;
use Illuminate\Support\Facades\Gate;

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
        \Illuminate\Validation\Rules\Password::defaults(function () {
            return \Illuminate\Validation\Rules\Password::min(6);
        });

        Gate::define('viewPulse', function (User $user) {
            return $user->email === 'it@wateranalytics.com.au';
        });
    }
}
