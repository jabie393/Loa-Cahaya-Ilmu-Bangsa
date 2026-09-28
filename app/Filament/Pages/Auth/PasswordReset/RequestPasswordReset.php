<?php

namespace App\Filament\Pages\Auth\PasswordReset;

use DiogoGPinto\AuthUIEnhancer\Pages\Auth\Concerns\HasCustomLayout;
use Filament\Actions\Action;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Password;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    use HasCustomLayout;

    /**
     * Title of the page.
     */
    public function getTitle(): string | Htmlable
    {
        return 'Lupa Sandi - ' . config('app.name', 'LOA CIB');
    }

    /**
     * Main heading text.
     */
    public function getHeading(): string | Htmlable
    {
        return 'Lupa Sandi';
    }

    /**
     * Subheading with explanation text and back to login link.
     */
    public function getSubheading(): string | Htmlable | null
    {
        $loginUrl = filament()->getLoginUrl();

        return new HtmlString('
            <span class="block text-sm text-slate-600 dark:text-slate-300">
                Masukkan email yang terdaftar pada akun Anda. Kami akan mengirimkan link untuk mengatur ulang kata sandi.
            </span>
            <span class="block mt-2">
                <a href="' . e($loginUrl) . '" class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-600 hover:text-primary-500 dark:text-primary-400 hover:underline">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Halaman Masuk
                </a>
            </span>
        ');
    }

    /**
     * Customize email input field.
     */
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Alamat Email Terdaftar')
            ->placeholder('contoh@email.com')
            ->email()
            ->required()
            ->autocomplete('email')
            ->autofocus();
    }

    /**
     * Customize the submit button action.
     */
    public function getRequestFormAction(): Action
    {
        return Action::make('request')
            ->label('Kirim Link Reset Sandi')
            ->submit('request');
    }

    /**
     * Notification when reset link has been successfully dispatched.
     */
    protected function getSentNotification(string $status): ?Notification
    {
        return Notification::make()
            ->title('Link Reset Terkirim')
            ->body('Link reset kata sandi telah dikirim ke email Anda. Silakan periksa inbox atau folder spam.')
            ->success();
    }

    /**
     * Notification when request fails.
     */
    protected function getFailureNotification(string $status): ?Notification
    {
        $message = match ($status) {
            Password::INVALID_USER => 'Kami tidak dapat menemukan akun dengan alamat email tersebut.',
            Password::RESET_THROTTLED => 'Permintaan terlalu sering. Harap tunggu beberapa saat sebelum mencoba kembali.',
            default => 'Gagal memproses permintaan reset kata sandi.',
        };

        return Notification::make()
            ->title('Permintaan Gagal')
            ->body($message)
            ->danger();
    }
}
