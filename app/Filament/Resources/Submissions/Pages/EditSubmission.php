<?php

namespace App\Filament\Resources\Submissions\Pages;

use App\Filament\Resources\Submissions\SubmissionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSubmission extends EditRecord
{
    protected static string $resource = SubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();
        if (!$user->hasRole('super_admin')) {
            // Jika status saat ini Draft atau Rejected dan bukti pembayaran diunggah, ajukan ke admin
            if (in_array($this->record->status, ['Draft', 'Rejected']) && !empty($data['proof_of_payment'])) {
                $data['status'] = 'Pending';
                $data['submission_date'] = now();
            }
        }
        return $data;
    }
}
