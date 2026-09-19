<?php

namespace App\Filament\Pages;

use App\Models\DevPayout;
use App\Models\Payment;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class DevPayoutsPage extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Dev Payouts';
    protected static ?string $title = 'Developer Payouts';
    protected static ?string $slug = 'dev-payouts';
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.dev-payouts-page';

    // Dev balance tracking
    public float $devTotalEarned = 0;
    public float $devTotalPaid = 0;
    public float $devUnpaidBalance = 0;
    public int $unpaidPayoutCount = 0;
    public float $unpaidPayoutTotal = 0;

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('ryu_dev') ?? false;
    }

    public function mount(): void
    {
        $this->refreshBalances();
    }

    public function refreshBalances(): void
    {
        $this->devTotalEarned = (float) Payment::where('payment_status', 'paid')->sum('developer_net_share');
        $this->devTotalPaid = (float) DevPayout::whereIn('status', ['waiting_confirmation', 'confirmed', 'completed'])->sum('amount');
        $devTotalCommitted = (float) DevPayout::whereIn('status', ['waiting_payout', 'waiting_confirmation', 'confirmed', 'completed'])->sum('amount');
        $this->devUnpaidBalance = max(0, $this->devTotalEarned - $devTotalCommitted);
        $this->unpaidPayoutCount = DevPayout::where('status', 'waiting_payout')->count();
        $this->unpaidPayoutTotal = (float) DevPayout::where('status', 'waiting_payout')->sum('amount');
    }

    #[On('payout-created')]
    #[On('payout-updated')]
    public function onPayoutUpdated(): void
    {
        $this->refreshBalances();
    }
}
