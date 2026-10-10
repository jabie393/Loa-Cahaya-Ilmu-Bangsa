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
    public int $devPaidCount = 0;
    public float $devUnpaidBalance = 0;
    public int $unpaidPayoutCount = 0;
    public float $unpaidPayoutTotal = 0;
    public int $waitingPayoutCount = 0;
    public int $waitingConfirmationCount = 0;
    public float $waitingPayoutAmount = 0;
    public float $waitingConfirmationAmount = 0;
    public int $rejectedPayoutCount = 0;
    public float $rejectedPayoutAmount = 0;

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('ryu_dev') ?? false;
    }

    protected $listeners = [
        'echo:dev-financial,financial.updated' => 'handleFinancialUpdated',
    ];

    public function handleFinancialUpdated(): void
    {
        $this->refreshBalances();
        $this->dispatch('$refresh');
    }

    public function mount(): void
    {
        $this->refreshBalances();
    }

    public function refreshBalances(): void
    {
        $this->devTotalEarned = (float) Payment::where('payment_status', 'paid')->sum('developer_net_share');
        $this->devTotalPaid = (float) DevPayout::whereIn('status', ['confirmed', 'completed'])->sum('amount');
        $this->devPaidCount = DevPayout::whereIn('status', ['confirmed', 'completed'])->count();

        $devTotalCommitted = (float) DevPayout::whereIn('status', ['waiting_payout', 'waiting_confirmation', 'confirmed', 'completed', 'rejected'])->sum('amount');
        $this->devUnpaidBalance = max(0, $this->devTotalEarned - $devTotalCommitted);

        $this->waitingPayoutCount = DevPayout::where('status', 'waiting_payout')->count();
        $this->waitingConfirmationCount = DevPayout::where('status', 'waiting_confirmation')->count();
        $this->waitingPayoutAmount = (float) DevPayout::where('status', 'waiting_payout')->sum('amount');
        $this->waitingConfirmationAmount = (float) DevPayout::where('status', 'waiting_confirmation')->sum('amount');
        $this->rejectedPayoutCount = DevPayout::where('status', 'rejected')->count();
        $this->rejectedPayoutAmount = (float) DevPayout::where('status', 'rejected')->sum('amount');

        $this->unpaidPayoutCount = $this->waitingPayoutCount + $this->waitingConfirmationCount + $this->rejectedPayoutCount;
        $this->unpaidPayoutTotal = $this->waitingPayoutAmount + $this->waitingConfirmationAmount + $this->rejectedPayoutAmount;
    }

    #[On('payout-created')]
    #[On('payout-updated')]
    public function onPayoutUpdated(): void
    {
        $this->refreshBalances();
    }
}
