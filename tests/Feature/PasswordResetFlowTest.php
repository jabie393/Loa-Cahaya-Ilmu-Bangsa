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
        $this->assertEquals('filament.emails.password-reset', $mailMessage->view);
        $this->assertEquals('http://localhost/password-reset/reset?token=sample-token&email=fahd%40example.com', $mailMessage->viewData['resetUrl']);
        $this->assertEquals('Fahd Test', $mailMessage->viewData['name']);

        // Verify the blade view renders properly with logo, content, and reset link
        $renderedHtml = view($mailMessage->view, $mailMessage->viewData)->render();
        $this->assertStringContainsString('https://aset.warunayama.org/images/logo.png', $renderedHtml);
        $this->assertStringContainsString('Permintaan Atur Ulang Kata Sandi', $renderedHtml);
        $this->assertStringContainsString('Halo <strong>Fahd Test</strong>', $renderedHtml);
        $this->assertStringContainsString('http://localhost/password-reset/reset?token=sample-token&email=fahd%40example.com', $renderedHtml);
        $this->assertStringContainsString('Tim LOA Cahaya Ilmu Bangsa', $renderedHtml);
    }
}
