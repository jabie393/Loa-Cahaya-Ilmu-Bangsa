<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\RegistrationResponse as RegistrationResponseContract;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleBasedRegistrationResponse implements RegistrationResponseContract
{
    public function toResponse($request): Response
    {
        $user = Auth::user();

        if ($user && $user->role === 'admin') {
            return redirect()->to('/admin');
        }

        return redirect()->to('/user');
    }
}
