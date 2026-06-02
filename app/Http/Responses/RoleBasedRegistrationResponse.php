<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\RegistrationResponse as RegistrationResponseContract;

class RoleBasedRegistrationResponse implements RegistrationResponseContract
{
    public function toResponse($request): \Illuminate\Http\RedirectResponse|\Livewire\Features\SupportRedirects\Redirector
    {
        $request->session()->put('show_welcome_modal', true);

        return redirect()->to('/journal');
    }
}
