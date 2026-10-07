<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
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
                $this->getAvatarFormComponent(),
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getPinFormComponent(),
                $this->getPinConfirmationFormComponent(),
                $this->getCurrentPasswordFormComponent(),
            ]);
    }

    protected function getAvatarFormComponent(): Component
    {
        return FileUpload::make('avatar_url')
            ->label(__('Profile Photo'))
            ->avatar()
            ->image()
            ->imageEditor()
            ->circleCropper()
            ->directory('avatars')
            ->disk('public')
            ->visibility('public')
            ->maxSize(2048)
            ->inlineLabel(false)
            ->alignCenter()
            ->columnSpanFull()
            ->placeholder('
                <div class="flex flex-col items-center justify-center text-center select-none pointer-events-none px-3 leading-tight">
                    <svg class="w-6 h-6 mb-1.5 text-gray-400 dark:text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Drag &amp; Drop</span>
                    <span class="text-[11px] text-gray-400 my-0.5 leading-none">/</span>
                    <span class="filepond--label-action text-xs font-medium text-gray-500 dark:text-gray-400 no-underline whitespace-nowrap pointer-events-auto">Choose Photo</span>
                </div>
            ')
            ->extraFieldWrapperAttributes([
                'class' => 'avatar-profile-wrapper',
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

        $this->redirect(static::getUrl(), navigate: false);
    }
}
