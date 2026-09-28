<?php

namespace App\Filament\Pages\Auth\PasswordReset;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DiogoGPinto\AuthUIEnhancer\Pages\Auth\Concerns\HasCustomLayout;
use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\PasswordResetResponse;
use Filament\Auth\Pages\PasswordReset\ResetPassword as BaseResetPassword;
use Filament\Forms\Components\TextInput;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Illuminate\Auth\Events\PasswordReset as PasswordResetEvent;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ResetPassword extends BaseResetPassword
{
    use HasCustomLayout;

    /**
     * Title of the page.
     */
    public function getTitle(): string | Htmlable
    {
        return 'Atur Ulang Sandi - ' . config('app.name', 'LOA CIB');
    }

    /**
     * Main heading text.
     */
    public function getHeading(): string | Htmlable
    {
        return 'Atur Ulang Sandi';
    }

    /**
     * Subheading text.
     */
    public function getSubheading(): string | Htmlable | null
    {
        return 'Silakan masukkan kata sandi baru untuk akun Anda.';
    }

    /**
     * Email field (read-only from token).
     */
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Alamat Email')
            ->disabled()
            ->autofocus();
    }

    /**
     * Password input field.
     */
    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Kata Sandi Baru')
            ->placeholder('Minimal 8 karakter')
            ->password()
            ->autocomplete('new-password')
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->rule(PasswordRule::default())
            ->same('passwordConfirmation')
            ->validationAttribute('kata sandi baru');
    }

    /**
     * Password confirmation field.
     */
    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label('Konfirmasi Kata Sandi Baru')
            ->placeholder('Ketik ulang kata sandi baru')
            ->password()
            ->autocomplete('new-password')
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->dehydrated(false)
            ->validationAttribute('konfirmasi kata sandi');
    }

    /**
     * Submit button action.
     */
    public function getResetPasswordFormAction(): Action
    {
        return Action::make('resetPassword')
            ->label('Simpan Sandi Baru')
            ->submit('resetPassword');
    }

    /**
     * Reset password execution with rate-limiting, secure hashing, and custom Indonesian notifications.
     */
    public function resetPassword(): ?PasswordResetResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        if ($this->isResetPasswordRateLimited($this->email)) {
            return null;
        }

        $data = $this->form->getState();

        $data['email'] = $this->email;
        $data['token'] = $this->token;

        $hasPanelAccess = true;

        $status = Password::broker(filament()->getAuthPasswordBroker())->reset(
            $this->getCredentialsFromFormData($data),
            function (CanResetPassword | Model | Authenticatable $user) use ($data, &$hasPanelAccess): void {
                if (
                    ($user instanceof FilamentUser) &&
                    (! $user->canAccessPanel(filament()->getCurrentOrDefaultPanel()))
                ) {
                    $hasPanelAccess = false;

                    return;
                }

                $user->forceFill([
                    $user->getAuthPasswordName() => Hash::make($data['password']),
                    $user->getRememberTokenName() => Str::random(60),
                ])->save();

                event(new PasswordResetEvent($user));
            }
        );

        if ($hasPanelAccess === false) {
            $status = Password::INVALID_USER;
        }

        if ($status === Password::PASSWORD_RESET) {
            Notification::make()
                ->title('Kata sandi berhasil diubah')
                ->body('Kata sandi berhasil diubah. Silakan login menggunakan kata sandi baru.')
                ->success()
                ->send();

            return app(PasswordResetResponse::class);
        }

        $errorMessage = match ($status) {
            Password::INVALID_USER => 'Akun pengguna tidak ditemukan atau tidak memiliki hak akses.',
            Password::INVALID_TOKEN => 'Token pengaturan ulang kata sandi tidak valid atau sudah pernah digunakan.',
            Password::RESET_THROTTLED => 'Percobaan terlalu sering. Harap tunggu beberapa saat.',
            default => 'Gagal mengubah kata sandi. Pastikan tautan masih berlaku.',
        };

        Notification::make()
            ->title('Gagal Mengubah Kata Sandi')
            ->body($errorMessage)
            ->danger()
            ->send();

        return null;
    }
}
