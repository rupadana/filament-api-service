<?php

namespace Rupadana\ApiService\Resources;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Rupadana\ApiService\ApiServicePlugin;
use Rupadana\ApiService\Models\Token;
use Rupadana\ApiService\Resources\TokenResource\Pages;

class TokenResource extends Resource
{
    protected static ?string $model = Token::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('General')
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        Select::make('tokenable_id')
                            ->options(User::all()->pluck('name', 'id'))
                            ->label('User')
                            ->required(),
                    ]),

                Section::make('Abilities')
                    ->description('Select abilities of the token')
                    ->schema(static::getAbilitiesSchema()),
            ]);
    }

    public static function getAbilitiesSchema(): array
    {
        $schema = [];

        $abilities = ApiServicePlugin::getAbilities(Filament::getCurrentPanel());

        foreach ($abilities as $resource => $handler) {
            $extractedAbilities = [];
            foreach ($handler as $handlerClass => $ability) {
                foreach ($ability as $a) {
                    $extractedAbilities[$a] = $a;
                }
            }
            $schema[] = Section::make(str($resource)->beforeLast('Resource')->explode('\\')->last())
                ->description($resource)
                ->schema([
                    CheckboxList::make('ability')
                        ->options($extractedAbilities)
                        ->selectAllAction(fn (Action $action) => $action->label('Select all'))
                        ->deselectAllAction(fn (Action $action) => $action->label('Unselect All'))
                        ->bulkToggleable(),
                ])
                ->collapsible();
        }

        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('tokenable.name')
                    ->label('User'),
                TextColumn::make('abilities')
                    ->badge()
                    ->words(1),
                TextColumn::make('created_at')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTokens::route('/'),
            'create' => Pages\CreateToken::route('/create'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return config('api-service.navigation.group.token');
    }

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole(config('api-service.can_access.role', []));
    }
}
