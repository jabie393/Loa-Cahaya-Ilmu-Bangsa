<?php

namespace App\Filament\Pages;

use App\Models\DevPayout;
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
use Illuminate\Support\Str;
use UnitEnum;

class FinanceSettingsPage extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static string | UnitEnum | null $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Finance & Payouts';
    protected static ?string $title = 'Finance, Revenue Split & Dev Payouts';
    protected static ?string $slug = 'finance';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.settings.finance-settings-page';

    public static function getNavigationIcon(): string | \BackedEnum | null
    {
        if (Auth::user()?->hasRole('ryu_dev') && !Auth::user()?->hasRole('super_admin')) {
            return 'heroicon-o-credit-card';
        }

        return static::$navigationIcon;
    }

    public static function getNavigationLabel(): string
    {
        if (Auth::user()?->hasRole('ryu_dev') && !Auth::user()?->hasRole('super_admin')) {
            return 'Riwayat Transaksi';
        }

        return static::$navigationLabel ?? 'Finance & Payouts';
    }

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        if (Auth::user()?->hasRole('ryu_dev') && !Auth::user()?->hasRole('super_admin')) {
            return null;
        }

        return static::$navigationGroup;
    }

    public static function getNavigationSort(): ?int
    {
        if (Auth::user()?->hasRole('ryu_dev') && !Auth::user()?->hasRole('super_admin')) {
            return 2;
        }

        return static::$navigationSort;
    }

    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        if (Auth::user()?->hasRole('ryu_dev') && !Auth::user()?->hasRole('super_admin')) {
            return 'Riwayat Transaksi Pembayaran';
        }

        return static::$title ?? 'Finance, Revenue Split & Dev Payouts';
    }

    public static function getNavigationBadge(): ?string
    {
        $devTotalEarned = (float) Payment::where('payment_status', 'paid')->sum('developer_net_share');
        $devTotalCommitted = (float) DevPayout::whereIn('status', ['waiting_payout', 'waiting_confirmation', 'confirmed', 'completed'])->sum('amount');
        $devUnpaidBalance = max(0, $devTotalEarned - $devTotalCommitted);

        return $devUnpaidBalance > 0 ? '💸' : null;
    }

    public static function getNavigationBadgeColor(): string | array | null
    {
        return 'success';
    }

    // Active Tab: 'transactions', 'payouts'
    public string $activeTab = 'transactions';

    // Top cards stats overview
    public float $totalGross = 0;
    public float $totalQris = 0;
    public float $totalDev = 0;
    public float $totalAdmin = 0;
    public int $countPayments = 0;

    // Dev balance tracking
    public float $devTotalEarned = 0;
    public float $devTotalPaid = 0;
    public float $devUnpaidBalance = 0;
    public int $unpaidPayoutCount = 0;
    public float $unpaidPayoutTotal = 0;

    public static function canAccess(): bool
    {
        return Auth::user()?->hasAnyRole('super_admin','ryu_dev') ?? true;
    }

    public function mount(): void
    {
        $this->refreshBalances();
    }

    public function refreshBalances(): void
    {
        if (
            !empty($this->mountedActions) ||
            !empty($this->mountedTableActions) ||
            (method_exists($this, 'getMountedAction') && $this->getMountedAction() !== null)
        ) {
            return;
        }

        $this->totalGross = (float) Payment::where('payment_status', 'paid')->sum('gross_amount');
        $this->totalQris = (float) Payment::where('payment_status', 'paid')->sum('mdr_amount');
        $this->totalDev = (float) Payment::where('payment_status', 'paid')->sum('developer_net_share');
        $this->totalAdmin = (float) Payment::where('payment_status', 'paid')->sum('journal_share');
        $this->countPayments = Payment::where('payment_status', 'paid')->count();

        $this->devTotalEarned = $this->totalDev;
        $this->devTotalPaid = (float) \App\Models\DevPayout::whereIn('status', ['waiting_confirmation', 'confirmed', 'completed'])->sum('amount');
        $devTotalCommitted = (float) \App\Models\DevPayout::whereIn('status', ['waiting_payout', 'waiting_confirmation', 'confirmed', 'completed'])->sum('amount');
        $this->devUnpaidBalance = max(0, $this->devTotalEarned - $devTotalCommitted);
        $this->unpaidPayoutCount = \App\Models\DevPayout::where('status', 'waiting_payout')->count();
        $this->unpaidPayoutTotal = (float) \App\Models\DevPayout::where('status', 'waiting_payout')->sum('amount');
    }

    #[\Livewire\Attributes\On('payout-created')]
    #[\Livewire\Attributes\On('payout-updated')]
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
            ->poll(function ($livewire) {
                if (
                    !empty($livewire->mountedActions) ||
                    !empty($livewire->mountedTableActions) ||
                    (method_exists($livewire, 'getMountedAction') && $livewire->getMountedAction() !== null)
                ) {
                    return null;
                }

                return '5s';
            })
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
                TextColumn::make('gateway')
                    ->label('Gateway')
                    ->badge()
                    ->formatStateUsing(fn(?string $state) => $state === 'belibayar' ? 'Belibayar' : 'Midtrans')
                    ->color(fn(?string $state) => $state === 'belibayar' ? 'info' : 'warning')
                    ->icon(fn(?string $state) => $state === 'belibayar' ? 'heroicon-m-bolt' : 'heroicon-m-cube')
                    ->sortable(),
                TextColumn::make('paid_at')
                    ->label('Waktu Bayar')
                    ->searchable()
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Rincian Transaksi')
                    ->formatStateUsing(function (Payment $record): string {
                        return match ($record->type) {
                            'bulk_submission' => 'Kolektif (' . count($record->items) . ' Naskah)',
                            'doi_addon' => 'Add-on DOI Resmi',
                            'replace_pdf' => 'Ganti PDF Naskah',
                            default => $record->submission?->title ?: 'Publikasi Naskah',
                        };
                    })
                    ->limit(30)
                    ->tooltip(function (Payment $record): string {
                        if ($record->type === 'bulk_submission') {
                            return 'Pembayaran Kolektif untuk ' . count($record->items) . ' naskah';
                        }
                        if ($record->type === 'replace_pdf') {
                            return 'Layanan Ganti PDF Naskah';
                        }
                        return $record->submission?->title ?? 'Pembayaran Midtrans';
                    })
                    ->searchable(query: function ($query, string $search) {
                        return $query->where('payer_name', 'like', "%{$search}%")
                            ->orWhere('order_id', 'like', "%{$search}%")
                            ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('submission.user', fn($q) => $q->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('items.submission', fn($q) => $q->where('title', 'like', "%{$search}%"));
                    })
                    ->description(function (Payment $record): string {
                        $payerUser = $record->user ?? $record->submission?->user;
                        $payerName = $payerUser?->name ?: ($record->payer_name ?: 'Author');
                        $payer = Str::limit($payerName, 25);
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
                        if ($record->type === 'replace_pdf') {
                            return 'Pembaruan File PDF';
                        }
                        return $record->submission?->journal?->name ?? 'Jurnal CIB';
                    })
                    ->limit(20)
                    ->searchable(query: function ($query, string $search) {
                        return $query->where(function ($q) use ($search) {
                            $q->whereHas('submission.journal', fn($j) => $j->where('name', 'like', "%{$search}%"))
                                ->orWhereHas('items.submission.journal', fn($j) => $j->where('name', 'like', "%{$search}%"))
                                ->orWhereHas('items', fn($itemQ) => $itemQ->where('item_name', 'like', "%{$search}%"));

                            $lowerSearch = strtolower($search);
                            if (str_contains('repository cib (doi)', $lowerSearch) || str_contains('doi', $lowerSearch)) {
                                $q->orWhere('type', 'doi_addon');
                            }
                            if (str_contains('pembaruan file pdf', $lowerSearch) || str_contains('ganti pdf', $lowerSearch) || str_contains('pdf', $lowerSearch)) {
                                $q->orWhere('type', 'replace_pdf');
                            }
                            if (str_contains('target jurnal', $lowerSearch) || str_contains('bulk', $lowerSearch) || str_contains('kolektif', $lowerSearch)) {
                                $q->orWhere('type', 'bulk_submission');
                            }
                            if (str_contains('jurnal cib', $lowerSearch) || str_contains('cib', $lowerSearch)) {
                                $q->orWhereNull('submission_id');
                            }
                        });
                    })
                    ->badge(fn(Payment $record) => $record->type === 'bulk_submission')
                    ->color(fn(Payment $record) => $record->type === 'bulk_submission' ? 'info' : null),
                TextColumn::make('gross_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->badge()
                    ->color('success')
                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                    ->sortable(),
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
                    ->modalHeading(false)
                    ->modalWidth(\Filament\Support\Enums\Width::FourExtraLarge)
                    ->modalContent(fn(Payment $record) => view('filament.pages.settings.partials.transaction-modal', ['record' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
            ]);
    }
}
