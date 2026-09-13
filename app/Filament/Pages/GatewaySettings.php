<?php

namespace App\Filament\Pages;

use App\Services\PaymentGateways\PaymentGatewayManager;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class GatewaySettings extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Gateway Settings';
    protected static ?string $title = 'Gateway Settings';
    protected static ?string $slug = 'gateway-settings';
    protected static ?int $navigationSort = 99;

    protected string $view = 'filament.pages.gateway-settings';

    public string $activeGateway = 'midtrans';

    public static function canAccess(): bool
    {
        return Auth::user()?->hasAnyRole(['ryu_dev', 'dev', 'developer']) ?? false;
    }

    public function mount(): void
    {
        $this->activeGateway = app(PaymentGatewayManager::class)->getActiveGatewayName();
    }

    public function switchGateway(string $gateway): void
    {
        try {
            app(PaymentGatewayManager::class)->setActiveGateway($gateway);
            $this->activeGateway = $gateway;

            Notification::make()
                ->title('Platform Pembayaran Berhasil Dialihkan')
                ->body('Gateway aktif sekarang: ' . ($gateway === 'belibayar' ? 'Belibayar.id' : 'Midtrans'))
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal Mengalihkan Platform')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
