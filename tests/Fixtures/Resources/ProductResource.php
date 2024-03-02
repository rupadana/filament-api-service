<?php

namespace Rupadana\ApiService\Tests\Fixtures\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Rupadana\ApiService\Tests\Fixtures\Models\Product;
use Rupadana\ApiService\Tests\Fixtures\Resources\ProductResource\Api\ProductTransformer;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static bool $isDiscovered = false;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Product';
    protected static ?string $modelLabel = 'Product';
    protected static ?string $pluralModelLabel = 'Products';

    public static function getApiTransformer()
    {
        return ProductTransformer::class;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->maxLength(255)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
            ]);
    }
}
