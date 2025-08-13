<?php

namespace Rupadana\ApiService\Tests\Fixtures\Resources;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Rupadana\ApiService\Tests\Fixtures\Models\Product;
use Rupadana\ApiService\Tests\Fixtures\Resources\ProductResource\Api\ProductTransformer;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static bool $isDiscovered = false;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Product';
    protected static ?string $modelLabel = 'Product';
    protected static ?string $pluralModelLabel = 'Products';

    public static function getApiTransformer()
    {
        return ProductTransformer::class;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->maxLength(255)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
            ]);
    }
}
