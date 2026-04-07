<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class Dashboard extends \Filament\Pages\Dashboard
{
    use HasPageShield;
    // Mengubah rute dashboard agar tidak bertabrakan dengan '/' (welcome page)
    protected static string $routePath = 'dashboard';
}
