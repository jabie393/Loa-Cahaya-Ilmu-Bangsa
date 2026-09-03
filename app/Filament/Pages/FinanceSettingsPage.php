<?php

namespace App\Filament\Pages;

use App\Models\Payment;
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
    public float $devTotalEarned = 0;
    public float $devTotalPaid = 0;
    public float $devUnpaidBalance = 0;

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
        $this->devTotalEarned = (float) Payment::where('payment_status', 'paid')->sum('developer_net_share');
        $this->devTotalPaid = (float) \App\Models\DevPayout::sum('amount');
        $this->devUnpaidBalance = max(0, $this->devTotalEarned - $this->devTotalPaid);
    }

    #[\Livewire\Attributes\On('payout-created')]
    public function onPayoutCreated(): void
    {
        $this->refreshBalances();
    }

    /**
     * Native Filament Table for Midtrans Payments
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Payment::query()
                    ->where('payment_status', 'paid')
                    ->with(['user', 'submission.journal', 'items.submission.journal'])
                    ->latest('paid_at')
            )
            ->columns([
                TextColumn::make('order_id')
                    ->label('Order ID')
                    ->fontFamily(\Filament\Support\Enums\FontFamily::Mono)
                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                    ->color('primary')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('paid_at')
                    ->label('Waktu Bayar')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Rincian Transaksi')
                    ->formatStateUsing(function (Payment $record): string {
                        return match ($record->type) {
                            'bulk_submission' => 'Kolektif (' . count($record->items) . ' Naskah)',
                            'doi_addon' => 'Add-on DOI Resmi',
                            default => $record->submission?->title ?: 'Publikasi Naskah',
                        };
                    })
                    ->limit(30)
                    ->tooltip(function (Payment $record): string {
                        if ($record->type === 'bulk_submission') {
                            return 'Pembayaran Kolektif untuk ' . count($record->items) . ' naskah';
                        }
                        return $record->submission?->title ?? 'Pembayaran Midtrans';
                    })
                    ->searchable(query: function ($query, string $search) {
                        return $query->where('payer_name', 'like', "%{$search}%")
                            ->orWhere('order_id', 'like', "%{$search}%")
                            ->orWhereHas('items.submission', fn($q) => $q->where('title', 'like', "%{$search}%"));
                    })
                    ->description(function (Payment $record): string {
                        $payer = $record->payer_name ?: ($record->user?->name ?? 'Author');
                        return "Pembayar: {$payer}";
                    }),
                TextColumn::make('journal_target')
                    ->label('Jurnal / Sasaran')
                    ->state(function (Payment $record): string {
                        if ($record->type === 'bulk_submission') {
                            return count($record->items) . ' Target Jurnal';
                        }
                        if ($record->type === 'doi_addon') {
                            return 'Repository CIB (DOI)';
                        }
                        return $record->submission?->journal?->name ?? 'Jurnal CIB';
                    })
                    ->limit(20)
                    ->badge(fn(Payment $record) => $record->type === 'bulk_submission')
                    ->color(fn(Payment $record) => $record->type === 'bulk_submission' ? 'info' : null),
                TextColumn::make('gross_amount')
                    ->label('Total Kotor')
                    ->money('IDR')
                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                    ->sortable(),
                TextColumn::make('mdr_amount')
                    ->label('QRIS (0.7%)')
                    ->money('IDR')
                    ->color('warning'),
                TextColumn::make('developer_net_share')
                    ->label('Cut Dev (Net)')
                    ->money('IDR')
                    ->badge()
                    ->color('success'),
                TextColumn::make('journal_share')
                    ->label('Cut Admin')
                    ->money('IDR')
                    ->badge()
                    ->color('info'),
                TextColumn::make('payment_status')
                    ->label('Status')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn(string $state) => strtoupper($state)),
            ])
            ->recordActions([
                Action::make('detail')
                    ->label('Detail Transaksi')
                    ->icon('heroicon-m-document-magnifying-glass')
                    ->color('primary')
                    ->modalHeading('Detail Transaksi & Pembagian Hasil')
                    ->modalContent(fn(Payment $record) => view('filament.pages.settings.partials.transaction-modal', ['record' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
            ]);
    }
}
