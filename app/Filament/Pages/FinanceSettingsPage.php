<?php

namespace App\Filament\Pages;

use App\Models\Submission;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class FinanceSettingsPage extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static string | UnitEnum | null $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Finance & Payouts';
    protected static ?string $title = 'Finance, Revenue Split & Dev Payouts';
    protected static ?string $slug = 'settings/finance';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.settings.finance-settings-page';

    // Active Tab: 'transactions', 'payouts'
    public string $activeTab = 'transactions';

    // Dev balance tracking
    public float $devTotalEarned = 1485200;
    public float $devTotalPaid = 1000000;
    public float $devUnpaidBalance = 485200;

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('super_admin') ?? true;
    }

    public function mount(): void
    {
        $this->refreshBalances();
    }

    public function refreshBalances(): void
    {
        $calculatedDev = \App\Models\Submission::all()->sum('dev_cut');
        $this->devTotalEarned = $calculatedDev > 0 ? $calculatedDev : 1485200;
        $this->devTotalPaid = (float) \App\Models\DevPayout::sum('amount');
        $this->devUnpaidBalance = max(0, $this->devTotalEarned - $this->devTotalPaid);
    }

    #[\Livewire\Attributes\On('payout-created')]
    public function onPayoutCreated(): void
    {
        $this->refreshBalances();
    }

    /**
     * Native Filament Table for Transactions
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(Submission::query()->with(['journal', 'user'])->latest())
            ->columns([
                TextColumn::make('id')
                    ->label('Kode TRX')
                    ->prefix('TRX-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Waktu Bayar')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Rincian Transaksi')
                    ->limit(25)
                    ->tooltip(fn(Submission $record): string => $record->title ?? '')
                    ->searchable()
                    ->description(function (Submission $record): string {
                        $author = \Illuminate\Support\Str::limit($record->author_name, 20);
                        return "Pembayar: {$author}";
                    }),
                TextColumn::make('journal.name')
                    ->label('Jurnal / Sasaran')
                    ->limit(18)
                    ->tooltip(fn(Submission $record): string => $record->journal?->name ?? '')
                    ->searchable(),
                TextColumn::make('gross_price')
                    ->label('Total Kotor')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('qris_fee')
                    ->label('QRIS (0.7%)')
                    ->money('IDR')
                    ->color('warning'),
                TextColumn::make('dev_cut')
                    ->label('Cut Dev')
                    ->money('IDR')
                    ->badge()
                    ->color('success'),
                TextColumn::make('admin_cut')
                    ->label('Cut Admin')
                    ->money('IDR')
                    ->badge()
                    ->color('info'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Approved' => 'success',
                        'Rejected' => 'danger',
                        default => 'warning',
                    }),
            ])
            ->recordActions([
                Action::make('detail')
                    ->label('Detail Transaksi')
                    ->icon('heroicon-m-document-magnifying-glass')
                    ->color('primary')
                    ->modalHeading('Detail Transaksi & Pembagian Hasil')
                    ->modalContent(fn(Submission $record) => view('filament.pages.settings.partials.transaction-modal', ['record' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
            ]);
    }
}
