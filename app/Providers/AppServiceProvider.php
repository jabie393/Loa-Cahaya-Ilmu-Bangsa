<?php

namespace App\Providers;

use BladeUI\Icons\Factory;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('ai-review', function ($app) {
            return new \App\Services\AiReviewManager($app);
        });

        $this->app->singleton('plagiarism', function ($app) {
            return new \App\Services\PlagiarismManager($app);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Factory $factory): void
    {
        $factory->add('default', [
            'path' => resource_path('svg'),
            'prefix' => '',
        ]);

        FilamentView::registerRenderHook(
            'tables::toolbar.start',
            fn () => view('filament.plagiarism.table-header'),
            \App\Filament\Resources\PlagiarismChecks\Pages\ManagePlagiarismChecks::class,
        );

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

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
