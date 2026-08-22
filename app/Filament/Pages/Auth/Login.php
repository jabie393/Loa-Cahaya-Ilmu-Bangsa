<?php

namespace App\Filament\Pages\Auth;

use DiogoGPinto\AuthUIEnhancer\Pages\Auth\Concerns\HasCustomLayout;
use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

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
}
