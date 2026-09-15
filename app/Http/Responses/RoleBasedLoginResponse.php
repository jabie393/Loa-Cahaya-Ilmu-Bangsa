<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;

class RoleBasedLoginResponse implements LoginResponseContract
{
    public function toResponse($request): \Illuminate\Http\RedirectResponse|\Livewire\Features\SupportRedirects\Redirector
    {
        $user = Auth::user();

        if ($user && $user->hasAnyRole(['ryu_dev', 'dev', 'developer'])) {
            return redirect()->to('/dev-payouts');
        }

        if ($request->hasSession()) {
            $request->session()->put('show_welcome_modal', true);
        }

        return redirect()->to('/journal');
    }
}
