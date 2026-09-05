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
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
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
                    ->label('Bayar Developer (Payout Baru)')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->modalHeading('Formulir Transfer / Bayar Developer')
                    ->modalDescription('Catat pengiriman bagi hasil ke Developer dan kurangi saldo hak dev secara real-time.')
                    ->modalSubmitActionLabel('Konfirmasi Transfer & Catat Payout')
                    ->schema([
                        Placeholder::make('qris_payment_dummy')
                            ->hiddenLabel()
                            ->content(fn () => view('filament.pages.settings.partials.qris-dummy-card')),
                        TextInput::make('amount')
                            ->label('Nominal yang Ditransfer (Rp)')
                            ->prefix('Rp')
                            ->numeric()
                            ->required()
                            ->default(function () {
                                $earned = (float) \App\Models\Payment::where('payment_status', 'paid')->sum('developer_net_share');
                                $paid = (float) \App\Models\DevPayout::whereIn('status', ['waiting_confirmation', 'confirmed', 'completed'])->sum('amount');
                                return max(0, $earned - $paid);
                            })
                            ->helperText(function () {
                                $earned = (float) \App\Models\Payment::where('payment_status', 'paid')->sum('developer_net_share');
                                $paid = (float) \App\Models\DevPayout::whereIn('status', ['waiting_confirmation', 'confirmed', 'completed'])->sum('amount');
                                $unpaid = max(0, $earned - $paid);
                                return 'Sisa hak Developer yang siap dicairkan: Rp ' . number_format($unpaid, 0, ',', '.');
                            }),
                        TextInput::make('reference_no')
                            ->label('Nomor Referensi Mutasi Bank')
                            ->placeholder('Contoh: TRF-BCA-98127391 (Opsional)'),
                        FileUpload::make('proof_file')
                            ->label('Upload Slip Bukti Transfer')
                            ->disk('public')
                            ->directory('payouts')
                            ->image()
                            ->maxSize(5120),
                        Textarea::make('notes')
                            ->label('Catatan Pembayaran Payout')
                            ->placeholder('Contoh: Pencairan bagi hasil periode 1-15 September 2026...')
                            ->rows(2)
                            ->default('Pencairan Hak Developer Periode Berjalan'),
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
                        $paid = (float) \App\Models\DevPayout::whereIn('status', ['waiting_confirmation', 'confirmed', 'completed'])->sum('amount');
                        $unpaid = max(0, $earned - $paid);

                        if ($amount > $unpaid) {
                            Notification::make()
                                ->title('Nominal Melebihi Sisa Saldo')
                                ->body('Maksimal pencairan yang dapat ditransfer saat ini adalah Rp ' . number_format($unpaid, 0, ',', '.'))
                                ->danger()
                                ->send();
                            return;
                        }

                        $payoutCount = DevPayout::count() + 1;
                        $payoutNo = 'PO-DEV-' . now()->format('Ym') . '-' . sprintf('%03d', $payoutCount);

                        $payout = DevPayout::create([
                            'payout_no' => $payoutNo,
                            'user_id' => Auth::id(),
                            'amount' => $amount,
                            'reference_no' => $data['reference_no'] ?: 'REF-' . strtoupper(bin2hex(random_bytes(4))),
                            'proof_file' => $data['proof_file'] ?? null,
                            'notes' => $data['notes'] ?? 'Pencairan Hak Developer',
                            'status' => 'waiting_confirmation',
                        ]);

                        $remainingBalance = max(0, $unpaid - $amount);
                        app(TelegramService::class)->sendDevPayoutNotification(
                            $payout,
                            $remainingBalance,
                            Auth::user()?->name ?? 'Admin'
                        );

                        $this->dispatch('payout-created');

                        Notification::make()
                            ->title('Pembayaran Developer Berhasil!')
                            ->body('Transfer sebesar Rp ' . number_format($amount, 0, ',', '.') . ' telah tercatat ke tabel riwayat pencairan.')
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
                    ->label('Waktu Transfer')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Nominal Ditransfer')
                    ->money('IDR')
                    ->weight(FontWeight::Black)
                    ->color('success')
                    ->sortable(),
                TextColumn::make('reference_no')
                    ->label('No. Referensi')
                    ->fontFamily(FontFamily::Mono)
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('notes')
                    ->label('Keterangan')
                    ->limit(40)
                    ->placeholder('-'),
                TextColumn::make('proof_file')
                    ->label('Bukti Slip')
                    ->formatStateUsing(fn ($state) => $state ? 'Lihat Slip' : '-')
                    ->color('primary')
                    ->url(fn (DevPayout $record) => $record->proof_file ? Storage::disk('public')->url($record->proof_file) : null)
                    ->openUrlInNewTab(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'waiting_confirmation' => 'warning',
                        'confirmed', 'completed' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'waiting_confirmation' => 'Menunggu Konfirmasi',
                        'confirmed' => 'Dikonfirmasi',
                        'completed' => 'Selesai',
                        'rejected' => 'Ditolak / Belum Masuk',
                        default => strtoupper($state),
                    }),
            ])
            ->recordActions([
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
