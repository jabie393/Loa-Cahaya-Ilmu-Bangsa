<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;

class RoleBasedLoginResponse implements LoginResponseContract
{
    public function toResponse($request): \Illuminate\Http\RedirectResponse|\Livewire\Features\SupportRedirects\Redirector
    {
        $user = Auth::user();

        return redirect()->to('/dashboard');
    }
}
