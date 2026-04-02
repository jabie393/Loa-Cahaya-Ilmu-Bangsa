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
        $this->app->singleton(
            \Filament\Auth\Http\Responses\Contracts\LoginResponse::class,
            \App\Http\Responses\RoleBasedLoginResponse::class
        );
        $this->app->singleton(
            \Filament\Auth\Http\Responses\Contracts\RegistrationResponse::class,
            \App\Http\Responses\RoleBasedRegistrationResponse::class
        );
        $this->app->singleton(
            \Filament\Auth\Http\Responses\Contracts\LogoutResponse::class,
            \App\Http\Responses\LogoutResponse::class
        );
    }
}
