<?php

namespace Rupadana\ApiService\Resources\TokenResource\Pages;

use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Rupadana\ApiService\Resources\TokenResource;

class CreateToken extends CreateRecord
{
    protected static string $resource = TokenResource::class;
    protected $newToken;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($data['all_abilities']) {
            unset($data['all_abilities']);
            $data["ability"] = ["*"];
        }
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        if (! isset($data['tokenable_id'])) {
            $data['tokenable_id'] = auth()->user()->id;
        }

        $user = User::find($data['tokenable_id']);

        $this->newToken = $user->createToken($data['name'], $data['ability']);

        return $user;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Token created, save it!')
            ->body($this->newToken->plainTextToken)
            ->persistent()
            ->actions([
                Action::make('close')
                    ->close(),
            ])
            ->success();
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
