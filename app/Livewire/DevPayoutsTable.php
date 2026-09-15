<?php

namespace App\Livewire;

use App\Models\DevPayout;
use App\Services\TelegramService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class DevPayoutsTable extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithTable;
    use InteractsWithForms;
    use InteractsWithActions;

    public function table(Table $table): Table
    {
        return $table
            ->query(DevPayout::query()->latest())
            ->headerActions([
                Action::make('create_payout')
                    ->visible(fn () => (bool) Auth::user()?->hasRole('super_admin'))
                    ->label('Buat Payout Manual (Draf)')
                    ->icon('heroicon-o-banknotes')
                    ->color('info')
                    ->modalHeading('Buat Draf Payout Developer')
                    ->modalDescription('Tentukan nominal hak dev yang ingin dicairkan. Status akan menjadi "Menunggu Payout" dan siap dibayar via QRIS.')
                    ->modalSubmitActionLabel('Buat Draf Payout')
                    ->modalWidth(Width::ExtraLarge)
                    ->schema([
                        TextInput::make('amount')
                            ->label('Nominal Pencairan (Rp)')
                            ->prefix('Rp')
                            ->numeric()
                            ->required()
                            ->default(function () {
                                $earned = (float) \App\Models\Payment::where('payment_status', 'paid')->sum('developer_net_share');
                                $locked = (float) \App\Models\DevPayout::whereIn('status', ['waiting_payout', 'waiting_confirmation', 'confirmed', 'completed'])->sum('amount');
                                return max(0, $earned - $locked);
                            })
                            ->helperText(function () {
                                $earned = (float) \App\Models\Payment::where('payment_status', 'paid')->sum('developer_net_share');
                                $locked = (float) \App\Models\DevPayout::whereIn('status', ['waiting_payout', 'waiting_confirmation', 'confirmed', 'completed'])->sum('amount');
                                $unpaid = max(0, $earned - $locked);
                                return 'Sisa saldo hak Dev yang belum ditahan/dicairkan: Rp ' . number_format($unpaid, 0, ',', '.');
                            }),
                        Textarea::make('notes')
                            ->label('Catatan Payout')
                            ->placeholder('Contoh: Pencairan bagi hasil manual...')
                            ->rows(3)
                            ->default('Pencairan Hak Developer (Manual)'),
                    ])
                    ->action(function (array $data) {
                        $amount = (float) ($data['amount'] ?? 0);

                        if ($amount <= 0) {
                            Notification::make()
                                ->title('Nominal Tidak Valid')
                                ->body('Nominal pencairan harus lebih besar dari Rp 0.')
                                ->warning()
                                ->send();
                            return;
                        }

                        $earned = (float) \App\Models\Payment::where('payment_status', 'paid')->sum('developer_net_share');
                        $locked = (float) \App\Models\DevPayout::whereIn('status', ['waiting_payout', 'waiting_confirmation', 'confirmed', 'completed'])->sum('amount');
                        $unpaid = max(0, $earned - $locked);

                        if ($amount > $unpaid) {
                            Notification::make()
                                ->title('Nominal Melebihi Sisa Saldo')
                                ->body('Maksimal pencairan yang tersedia saat ini adalah Rp ' . number_format($unpaid, 0, ',', '.'))
                                ->danger()
                                ->send();
                            return;
                        }

                        $payoutCount = DevPayout::count() + 1;
                        $payoutNo = 'PO-DEV-' . now()->format('Ym') . '-' . sprintf('%03d', $payoutCount);

                        while (DevPayout::where('payout_no', $payoutNo)->exists()) {
                            $payoutCount++;
                            $payoutNo = 'PO-DEV-' . now()->format('Ym') . '-' . sprintf('%03d', $payoutCount);
                        }

                        $payout = DevPayout::create([
                            'payout_no' => $payoutNo,
                            'user_id' => Auth::id(),
                            'amount' => $amount,
                            'notes' => $data['notes'] ?? 'Pencairan Hak Developer (Manual)',
                            'status' => 'waiting_payout',
                        ]);

                        $this->dispatch('payout-created');

                        Notification::make()
                            ->title('Draf Payout Dibuat!')
                            ->body("Payout {$payout->payout_no} sebesar Rp " . number_format($amount, 0, ',', '.') . " berhasil dibuat. Silakan klik 'Bayar via QRIS' pada tabel untuk membayar.")
                            ->success()
                            ->send();
                    }),
            ])
            ->columns([
                TextColumn::make('payout_no')
                    ->label('No. Payout')
                    ->fontFamily(FontFamily::Mono)
                    ->weight(FontWeight::Bold)
                    ->color('primary')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Waktu Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Nominal Payout')
                    ->money('IDR')
                    ->weight(FontWeight::Black)
                    ->color('success')
                    ->sortable(),
                TextColumn::make('reference_no')
                    ->label('No. Referensi')
                    ->fontFamily(FontFamily::Mono)
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('notes')
                    ->label('Keterangan')
                    ->limit(40)
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'waiting_payout' => 'warning',
                        'waiting_confirmation' => 'info',
                        'confirmed', 'completed' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'waiting_payout' => 'Menunggu Payout',
                        'waiting_confirmation' => 'Menunggu Konfirmasi',
                        'confirmed' => 'Dikonfirmasi',
                        'completed' => 'Selesai',
                        'rejected' => 'Ditolak / Belum Masuk',
                        default => strtoupper($state),
                    }),
            ])
            ->recordActions([
                Action::make('pay_with_qris')
                    ->label('Bayar via QRIS')
                    ->icon('heroicon-m-qr-code')
                    ->button()
                    ->color('success')
                    ->size('sm')
                    ->visible(fn (DevPayout $record): bool => 
                        $record->status === 'waiting_payout' && (bool) Auth::user()?->hasRole('super_admin')
                    )
                    ->modalHeading(fn (DevPayout $record): string => "Bayar Payout {$record->payout_no} via QRIS")
                    ->modalDescription('Scan kode QRIS di bawah ini dengan Mobile Banking atau E-Wallet untuk menyelesaikan transfer.')
                    ->modalSubmitActionLabel('Sudah Bayar via QRIS')
                    ->modalWidth(Width::ExtraLarge)
                    ->modalContent(fn (DevPayout $record) => view('filament.pages.settings.partials.pay-qris-modal', [
                        'record' => $record,
                    ]))
                    ->action(function (DevPayout $record) {
                        $refNo = $record->reference_no ?: ('QRIS-' . now()->format('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(2))));
                        $record->update([
                            'status' => 'waiting_confirmation',
                            'reference_no' => $refNo,
                        ]);

                        $earned = (float) \App\Models\Payment::where('payment_status', 'paid')->sum('developer_net_share');
                        $locked = (float) \App\Models\DevPayout::whereIn('status', ['waiting_payout', 'waiting_confirmation', 'confirmed', 'completed'])->sum('amount');
                        $remainingBalance = max(0, $earned - $locked);

                        app(TelegramService::class)->sendDevPayoutNotification(
                            $record,
                            $remainingBalance,
                            Auth::user()?->name ?? 'Admin'
                        );

                        $this->dispatch('payout-created');

                        Notification::make()
                            ->title('Pembayaran QRIS Berhasil!')
                            ->body("Status payout {$record->payout_no} kini 'Menunggu Konfirmasi'. Notifikasi telah dikirim ke Developer.")
                            ->success()
                            ->send();
                    }),

                ActionGroup::make([
                    Action::make('confirm_receipt')
                        ->label('Konfirmasi Diterima')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->visible(fn (DevPayout $record): bool => 
                            $record->status === 'waiting_confirmation' && (bool) Auth::user()?->hasRole('ryu_dev')
                        )
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Penerimaan Dana Payout')
                        ->modalWidth(Width::Large)
                        ->modalDescription(fn (DevPayout $record): string => 
                            "Apakah Anda yakin telah menerima transfer dana sebesar Rp " . number_format($record->amount, 0, ',', '.') . " ke rekening Anda?"
                        )
                        ->modalSubmitActionLabel('Ya, Dana Sudah Diterima')
                        ->action(function (DevPayout $record) {
                            $record->update(['status' => 'confirmed']);
                            $this->dispatch('payout-created');

                            Notification::make()
                                ->title('Payout Dikonfirmasi!')
                                ->body("Payout {$record->payout_no} telah berhasil Anda konfirmasi sebagai dana masuk.")
                                ->success()
                                ->send();
                        }),

                    Action::make('reject_receipt')
                        ->label('Tolak / Belum Masuk')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->visible(fn (DevPayout $record): bool => 
                            $record->status === 'waiting_confirmation' && (bool) Auth::user()?->hasRole('ryu_dev')
                        )
                        ->modalHeading('Laporkan Payout Belum Diterima / Bermasalah')
                        ->modalDescription('Dana akan dikembalikan ke saldo hak developer yang belum dicairkan.')
                        ->modalSubmitActionLabel('Kirim Laporan Penolakan')
                        ->modalWidth(Width::Large)
                        ->form([
                            Textarea::make('rejection_reason')
                                ->label('Alasan Penolakan / Masalah')
                                ->placeholder('Contoh: Mutasi rekening belum masuk setelah dicek, mohon cek kembali slip transfer...')
                                ->required()
                                ->rows(3),
                        ])
                        ->action(function (DevPayout $record, array $data) {
                            $record->update([
                                'status' => 'rejected',
                                'rejection_reason' => $data['rejection_reason'],
                            ]);
                            $this->dispatch('payout-created');

                            Notification::make()
                                ->title('Payout Ditolak')
                                ->body("Payout {$record->payout_no} ditandai sebagai belum diterima. Saldo telah dikembalikan ke hak dev belum cair.")
                                ->warning()
                                ->send();
                        }),

                    Action::make('detail')
                        ->label('Kuitansi')
                        ->icon('heroicon-m-document-text')
                        ->color('gray')
                        ->modalHeading('Kuitansi Pencairan Hak Developer')
                        ->modalWidth(Width::ExtraLarge)
                        ->modalContent(fn (DevPayout $record) => view('filament.pages.settings.partials.payout-receipt-modal', ['record' => $record]))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Tutup'),
                ]),
            ]);

    }

    public function render()
    {
        return view('livewire.dev-payouts-table');
    }
}
