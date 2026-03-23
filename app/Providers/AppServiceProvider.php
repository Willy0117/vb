<?php

namespace App\Providers;

use Inertia\Inertia;
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
       //
       if (env('APP_ENV') !== 'local') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
       }
       Inertia::share([
            'csrf_token' => fn () => csrf_token(),
            'auth' => [
                'user' => fn () => auth()->user()
                    ? [
                        'id' => auth()->user()->id,
                        'name' => auth()->user()->name,
                        'roles' => auth()->user()->roles->pluck('name'),
                        'permissions' => auth()->user()->getAllPermissions()->pluck('name')->toArray(),
                    ]
                    : null,
            ],
       ]);
    }
}
