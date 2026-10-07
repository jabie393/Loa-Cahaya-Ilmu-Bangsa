<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getPinFormComponent(),
                $this->getPinConfirmationFormComponent(),
                $this->getCurrentPasswordFormComponent(),
            ]);
    }

    protected function getPinFormComponent(): Component
    {
        return TextInput::make('pin')
            ->label(__('New PIN'))
            ->password()
            ->revealable()
            ->numeric()
            ->maxLength(6)
            ->rule('digits:6')
            ->autocomplete('off')
            ->live(debounce: 500)
            ->same('pinConfirmation')
            ->validationAttribute(__('New PIN'))
            ->extraInputAttributes([
                'inputmode' => 'numeric',
                'pattern' => '[0-9]*',
                'maxlength' => '6',
                'oninput' => "this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6)",
            ])
            ->dehydrated(fn ($state): bool => filled($state))
            ->dehydrateStateUsing(fn ($state): string => Hash::make($state));
    }

    protected function getPinConfirmationFormComponent(): Component
    {
        return TextInput::make('pinConfirmation')
            ->label(__('Confirm new PIN'))
            ->password()
            ->revealable()
            ->numeric()
            ->maxLength(6)
            ->rule('digits:6')
            ->autocomplete('off')
            ->required()
            ->visible(fn (Get $get): bool => filled($get('pin')))
            ->dehydrated(false)
            ->validationAttribute(__('Confirm new PIN'))
            ->extraInputAttributes([
                'inputmode' => 'numeric',
                'pattern' => '[0-9]*',
                'maxlength' => '6',
                'oninput' => "this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6)",
            ]);
    }

    protected function getCurrentPasswordFormComponent(): Component
    {
        return parent::getCurrentPasswordFormComponent()
            ->autocomplete('off')
            ->extraInputAttributes([
                'autocomplete' => 'new-password',
                'autofill' => 'off',
                'data-lpignore' => 'true',
                'data-1p-ignore' => 'true',
            ])
            ->visible(fn (Get $get): bool =>
                filled($get('password')) ||
                filled($get('pin')) ||
                ($get('email') !== $this->getUser()->getAttributeValue('email'))
            );
    }

    protected function afterSave(): void
    {
        $this->data['pin'] = null;
        $this->data['pinConfirmation'] = null;
        $this->data['currentPassword'] = null;
    }

    public function save(): void
    {
        parent::save();

        $this->data['pin'] = null;
        $this->data['pinConfirmation'] = null;
        $this->data['currentPassword'] = null;
    }
}
