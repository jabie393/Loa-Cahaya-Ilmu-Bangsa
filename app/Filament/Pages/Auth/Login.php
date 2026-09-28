<?php

namespace App\Filament\Pages\Auth;

use DiogoGPinto\AuthUIEnhancer\Pages\Auth\Concerns\HasCustomLayout;
use Filament\Actions\Action;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class Login extends BaseLogin
{
    use HasCustomLayout;

    /**
     * Browser title.
     */
    public function getTitle(): string | Htmlable
    {
        return 'Masuk Akun - ' . config('app.name', 'LOA CIB');
    }

    /**
     * Main heading text.
     */
    public function getHeading(): string|Htmlable
    {
        return 'Masuk Akun';
    }

    /**
     * Subheading with friendly message and registration link.
     */
    public function getSubheading(): string|Htmlable|null
    {
        $registerUrl = filament()->hasRegistration() ? filament()->getRegistrationUrl() : null;

        return new HtmlString('
            <span class="block text-sm text-slate-600 dark:text-slate-300">
                Silakan masuk untuk mengakses portal LOA dan Repositori.
            </span>
            ' . ($registerUrl ? '
            <span class="block mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                Belum memiliki akun? <a href="' . e($registerUrl) . '" class="font-semibold text-primary-600 hover:text-primary-500 dark:text-primary-400 hover:underline">Daftar sekarang</a>
            </span>' : '') . '
        ');
    }

    /**
     * Email input field with Indonesian label and placeholder.
     */
    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()
            ->label('Alamat Email')
            ->placeholder('nama@email.com');
    }

    /**
     * Password input with clear 'Lupa Sandi?' link.
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

    /**
     * Remember me checkbox label.
     */
    protected function getRememberFormComponent(): Component
    {
        return parent::getRememberFormComponent()
            ->label('Ingat saya');
    }

    /**
     * Submit button with Indonesian label.
     */
    public function getAuthenticateFormAction(): Action
    {
        return parent::getAuthenticateFormAction()
            ->label('Masuk');
    }
}
