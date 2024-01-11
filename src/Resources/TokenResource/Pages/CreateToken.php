<?php

namespace Rupadana\ApiService\Resources\TokenResource\Pages;


use Rupadana\ApiService\Resources\TokenResource;
use App\Models\User;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateToken extends CreateRecord
{
    protected static string $resource = TokenResource::class;

    protected function handleRecordCreation(array $data): Model
    {

        $user = User::find($data['tokenable_id']);

        $newToken = $user->createToken($data['name'], $data['ability']);

        Notification::make()
            ->title('Token created, save it!')
            ->body($newToken->plainTextToken)
            ->persistent()
            ->actions([
                Action::make('close')
                    ->close()
            ])
            ->success()
            ->send();
        return $user;
    }

    protected function sendCreatedNotificationAndRedirect(bool $shouldCreateAnotherInsteadOfRedirecting = true): void
    {
        if ($shouldCreateAnotherInsteadOfRedirecting) {
            // Ensure that the form record is anonymized so that relationships aren't loaded.
            $this->form->model($this->getRecord()::class);
            $this->record = null;

            $this->fillForm();

            return;
        }

        $this->redirect($this->getRedirectUrl());
    }
}
