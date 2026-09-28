<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Tests\TestCase;

class PasswordResetFlowTest extends TestCase
{
    public function test_reset_password_notification_formats_mail_correctly(): void
    {
        $user = new User([
            'name' => 'Fahd Test',
            'email' => 'fahd@example.com',
        ]);

        $notification = new ResetPasswordNotification('sample-token');
        $notification->url = 'http://localhost/password-reset/reset?token=sample-token&email=fahd%40example.com';

        $mailMessage = $notification->toMail($user);

        $this->assertStringContainsString('Atur Ulang Kata Sandi', $mailMessage->subject);
        $this->assertEquals('http://localhost/password-reset/reset?token=sample-token&email=fahd%40example.com', $mailMessage->actionUrl);
        $this->assertEquals('Halo, Fahd Test!', $mailMessage->greeting);
        $this->assertStringContainsString('LOA Cahaya Ilmu Bangsa', $mailMessage->salutation);
    }
}
