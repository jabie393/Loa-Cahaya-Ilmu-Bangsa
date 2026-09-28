<?php

namespace App\Filament\Pages\Auth;

use DiogoGPinto\AuthUIEnhancer\Pages\Auth\Concerns\HasCustomLayout;
use Filament\Actions\Action;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class Register extends BaseRegister
{
    use HasCustomLayout;

    /**
     * Browser title.
     */
    public function getTitle(): string | Htmlable
    {
        return 'Daftar Akun - ' . config('app.name', 'LOA CIB');
    }

    /**
     * Main heading text.
     */
    public function getHeading(): string | Htmlable
    {
        return 'Daftar Akun Baru';
    }

    /**
     * Subheading with link back to login page.
     */
    public function getSubheading(): string | Htmlable | null
    {
        $loginUrl = filament()->hasLogin() ? filament()->getLoginUrl() : null;

        if (! $loginUrl) {
            return null;
        }

        return new HtmlString('
            <span class="block text-sm text-slate-600 dark:text-slate-300">
                Sudah memiliki akun? <a href="' . e($loginUrl) . '" class="font-semibold text-primary-600 hover:text-primary-500 dark:text-primary-400 hover:underline">Masuk di sini</a>
            </span>
        ');
    }

    /**
     * Full name field.
     */
    protected function getNameFormComponent(): Component
    {
        return parent::getNameFormComponent()
            ->label('Nama Lengkap')
            ->placeholder('Masukkan nama lengkap Anda');
    }

    /**
     * Email field.
     */
    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()
            ->label('Alamat Email')
            ->placeholder('nama@email.com');
    }

    /**
     * Password field.
     */
    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
            ->label('Kata Sandi')
            ->placeholder('Minimal 8 karakter')
            ->validationAttribute('kata sandi');
    }

    /**
     * Password confirmation field.
     */
    protected function getPasswordConfirmationFormComponent(): Component
    {
        return parent::getPasswordConfirmationFormComponent()
            ->label('Konfirmasi Kata Sandi')
            ->placeholder('Ulangi kata sandi')
            ->validationAttribute('konfirmasi kata sandi');
    }

    /**
     * Submit button for registration.
     */
    public function getRegisterFormAction(): Action
    {
        return parent::getRegisterFormAction()
            ->label('Daftar Akun');
    }
}
