<?php

namespace App\Filament\Auth\Pages;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Support\Facades\Auth;

class Login extends BaseLogin
{
    protected function getRedirectUrl(): ?string
    {
        $user = Auth::user();

        return '/dashboard';
    }
}
