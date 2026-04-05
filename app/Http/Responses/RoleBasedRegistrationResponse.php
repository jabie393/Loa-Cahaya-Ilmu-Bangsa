<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\RegistrationResponse as RegistrationResponseContract;
use Illuminate\Support\Facades\Auth;

class RoleBasedRegistrationResponse implements RegistrationResponseContract
{
    public function toResponse($request): \Illuminate\Http\RedirectResponse|\Livewire\Features\SupportRedirects\Redirector
    {
        $user = Auth::user();

        if ($user && $user->hasRole('super_admin')) {
            return redirect()->to('/admin');
        }

        return redirect()->to('/user');
    }
}
