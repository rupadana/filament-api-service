<?php

namespace Rupadana\ApiService\Resources\TokenResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Rupadana\ApiService\Resources\TokenResource;

class EditToken extends EditRecord
{
    protected static string $resource = TokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
