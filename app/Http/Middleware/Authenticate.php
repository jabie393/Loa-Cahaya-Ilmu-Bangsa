<?php

namespace App\Http\Middleware;

use Filament\Http\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request): ?string
    {
        return url('/');
    }
}
