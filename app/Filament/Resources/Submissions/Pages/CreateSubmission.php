<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\SubmissionResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Components\View;
use Filament\Forms\Components\Checkbox;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Actions\Action;

use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;


class CreateSubmission extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;

    protected static string $resource = SubmissionResource::class;

    protected function getSteps(): array
    {
        return [
            Step::make('Form LOA')
                ->schema([
                    Section::make('Informasi Penulis')
                        ->columnSpan(4)
                        ->description('Informasi Penulis')
                        ->schema([
                            Hidden::make('user_id')
                                ->default(Auth::user()->id),
                            TextInput::make('author_name')
                                ->label('Nama Penulis (Di Isi Semua, Pisahkan dengan Tanda Koma)')
                                ->default(Auth::user()->name)
                                ->required(),
                            TextInput::make('email')
                                ->label('Email (Maximal 1 Email, Email ini akan digunakan untuk pengiriman LOA)')
                                ->default(Auth::user()->email)
                                ->email()
                                ->required(),
                    
                                    
                            TextInput::make('title')
                                ->columnSpanFull()
                                ->label('Judul (Diisi Huruf Besar)')
                                ->required(),
                            TextInput::make('institution')
                                ->columnSpanFull()
                                ->label('Instansi (Jangan disingkat)')
                                ->required(),
                            Select::make('journal_id')
                                ->label('Jurnal (Pilih Salah satu)')
                                ->relationship('journal', 'name')
                                ->default(request()->query('journal_id'))
                                ->required(),
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('vol')
                                        ->live() // or ->reactive() in v2
                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                            $vol = $get('vol') ?? '';
                                            $no = $get('no') ?? '';
                                            $year = $get('year') ?? '';
                                            $set('volume', trim('Vol. ' . $vol . ' ' . 'No. ' . $no . ' ' . '(' . $year . ')'));
                                        }),

                                    TextInput::make('no')
                                        ->live() // or ->reactive() in v2
                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                            $vol = $get('vol') ?? '';
                                            $no = $get('no') ?? '';
                                            $year = $get('year') ?? '';
                                            $set('volume', trim('Vol. ' . $vol . ' ' . 'No. ' . $no . ' ' . '(' . $year . ')'));
                                        }),

                                    TextInput::make('year')
                                        ->live() // or ->reactive() in v2
                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                            $vol = $get('vol') ?? '';
                                            $no = $get('no') ?? '';
                                            $year = $get('year') ?? '';
                                            $set('volume', trim('Vol. ' . $vol . ' ' . 'No. ' . $no . ' ' . '(' . $year . ')'));
                                        }),

                                    Hidden::make('volume')
                                ]),

                            DatePicker::make('date_of_loa')
                                ->native(false)
                                ->displayFormat('d-m-Y')
                                ->label('Tanggal LOA')
                                ->required(),    
                            TextInput::make('publication_link')
                                ->columnSpanFull()
                                ->label('Publication Link'),
                            Hidden::make('submission_date')
                                ->default(now()),                
                            
                            Hidden::make('status')
                                ->default('Pending'),
                    ])->columns(2),
                    Section::make('Pembayaran')
                        ->columnSpan(2)
                        ->description('Bukti Pembayaran')
                        ->schema([
                            FileUpload::make('proof_of_payment')
                                ->label('Upload Bukti Pembayaran')
                                ->directory('proof-of-payment')
                                ->required()
                                ->disk('public')
                                ->image()
                        ]),
                ])->columns(6),
            Step::make('Konfirmasi')
                ->schema([
                    View::make('filament.pages.Confirmation'),
                    Checkbox::make('agreement')
                        ->label('LoA Berlaku Jika Dilengkapi Bukti Pembayaran dan Link Terbitan, Dengan ini saya bersedia naskah saya ditarik apabila dikemudian hari terdapat kecurangan dalam pengerjaannya')
                        ->accepted(),
                ]),    
        ];
    }
}
