<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    /**
     * Password reset token.
     */
    public string $token;

    /**
     * Target reset password URL.
     */
    public ?string $url = null;

    /**
     * Create a notification instance.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = $this->url ?? (
            class_exists(\Filament\Facades\Filament::class) && method_exists($notifiable, 'getEmailForPasswordReset')
                ? \Filament\Facades\Filament::getResetPasswordUrl($this->token, $notifiable)
                : \Illuminate\Support\Facades\URL::signedRoute('filament.admin.auth.password-reset.reset', [
                    'token' => $this->token,
                    'email' => $notifiable->getEmailForPasswordReset(),
                ])
        );

        $name = $notifiable->name ?? 'Pengguna';

        return (new MailMessage)
            ->subject('Atur Ulang Kata Sandi - LOA Cahaya Ilmu Bangsa')
            ->greeting("Halo, {$name}!")
            ->line('Kami menerima permintaan untuk mengatur ulang kata sandi akun Anda pada Portal LOA Cahaya Ilmu Bangsa.')
            ->action('Atur Ulang Kata Sandi', $resetUrl)
            ->line('Tautan pengaturan ulang kata sandi ini aman dan akan kedaluwarsa dalam 60 menit.')
            ->line('Jika Anda tidak pernah meminta pengaturan ulang kata sandi, abaikan email ini dan akun Anda tetap aman.')
            ->salutation("Salam hangat,\nTim LOA Cahaya Ilmu Bangsa");
    }
}
