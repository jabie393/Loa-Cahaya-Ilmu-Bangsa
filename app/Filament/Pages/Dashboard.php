<?php

namespace App\Filament\Pages;

class Dashboard extends \Filament\Pages\Dashboard
{
    // Mengubah rute dashboard agar tidak bertabrakan dengan '/' (welcome page)
    protected static string $routePath = 'dashboard';
}
