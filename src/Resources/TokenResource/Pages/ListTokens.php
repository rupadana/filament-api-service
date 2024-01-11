<?php

namespace Rupadana\ApiService\Resources\TokenResource\Pages;


use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Rupadana\ApiService\Resources\TokenResource;

class ListTokens extends ListRecords
{
    protected static string $resource = TokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
