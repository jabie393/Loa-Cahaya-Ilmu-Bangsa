<?php

namespace App\Filament\Auth\Pages;

use Filament\Auth\Pages\Register as BaseRegister;
use Illuminate\Support\Facades\Auth;

class Register extends BaseRegister
{
    protected function getRedirectUrl(): ?string
    {
        $user = Auth::user();

        if ($user && $user->role === 'admin') {
            return '/admin';
        }

        return '/user';
    }
}
