<?php

namespace App\Filament\Pages\Auth;

use DiogoGPinto\AuthUIEnhancer\Pages\Auth\Concerns\HasCustomLayout;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class Login extends BaseLogin
{
    use HasCustomLayout;

    /**
     * Title / Heading text above the sign in form
     */
    public function getHeading(): string|Htmlable
    {
        return 'Masuk Akun';
    }

    /**
     * Subheading text under the title
     */
    public function getSubheading(): string|Htmlable|null
    {
        return 'Silakan masuk untuk mengakses portal LOA dan Repositori.';
    }

    /**
     * Password input with clear 'Lupa Sandi?' link
     */
    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
            ->label('Kata Sandi')
            ->placeholder('Masukkan kata sandi')
            ->hint(filament()->hasPasswordReset() ? new HtmlString(Blade::render('
                <x-filament::link :href="filament()->getRequestPasswordResetUrl()" tabindex="-1" class="text-xs font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 hover:underline">
                    Lupa Sandi?
                </x-filament::link>
            ')) : null);
    }
}
