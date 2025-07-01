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
use Illuminate\Database\Eloquent\Builder;
use Rupadana\ApiService\ApiServicePlugin;
use Rupadana\ApiService\Models\Token;
use Rupadana\ApiService\Resources\TokenResource\Pages;

class TokenResource extends Resource
{
    protected static ?string $model = Token::class;

    // protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static bool $isScopedToTenant = false;

    public static function shouldRegisterNavigation(): bool
    {
        return config('api-service.navigation.token.should_register_navigation', false);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make( __('api-service::api-service.section.general'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('api-service::api-service.field.name'))
                            ->required(),
                        Select::make('tokenable_id')
                            ->options(User::all()->pluck('name', 'id'))
                            ->label(__('api-service::api-service.field.user'))
                            ->hidden(function () {
                                $user = auth()->user();

                                $policy = config('api-service.models.token.enable_policy', true);

                                if ($policy === false) {
                                    return false;
                                }

                                return ! $user->hasRole('super_admin');
                            })
                            ->required(),
                    ]),

                Section::make(__('api-service::api-service.section.abilities'))
                    ->description(__('api-service::api-service.section.abilities.description'))
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
                    $extractedAbilities[$a] = __($a);
                }
            }

            $schema[] = Section::make(str($resource)->beforeLast('Resource')->explode('\\')->last())
                ->description($resource)
                ->schema([
                    CheckboxList::make('ability')
                        ->label(__('api-service::api-service.field.ability'))
                        ->options($extractedAbilities)
                        ->selectAllAction(fn (Action $action) => $action->label( __('api-service::api-service.action.select_all')))
                        ->deselectAllAction(fn (Action $action) => $action->label( __('api-service::api-service.action.unselect_all')))
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
                TextColumn::make('name')
                    ->label(__('api-service::api-service.column.name')),
                TextColumn::make('tokenable.name')
                    ->label(__('api-service::api-service.column.user')),
                TextColumn::make('abilities')
                    ->label(__('api-service::api-service.column.abilities'))
                    ->badge()
                    ->words(1),
                TextColumn::make('created_at')
                    ->label(__('api-service::api-service.column.created_at'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

            ])
            ->actions([
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $authenticatedUser = auth()->user();

                if (method_exists($authenticatedUser, 'hasRole') && $authenticatedUser->hasRole('super_admin')) {
                    return $query;
                }

                return $query->where('tokenable_id', $authenticatedUser->id);
            });
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTokens::route('/'),
            'create' => Pages\CreateToken::route('/create'),
        ];
    }

    public static function getCluster(): ?string
    {
        return config('api-service.navigation.token.cluster', null);
    }

    public static function getNavigationGroup(): ?string
    {
        return config('api-service.navigation.token.group') ?? config('api-service.navigation.group.token');
    }

    public static function getNavigationSort(): ?int
    {
        return config('api-service.navigation.token.sort', -1);
    }

    public static function getNavigationIcon(): ?string
    {
        return config('api-service.navigation.token.icon', 'heroicon-o-key');
    }

    public static function getModelLabel(): string
    {
        return __('api-service::api-service.model');
    }

    public static function getPluralLabel(): string
    {
        return __('api-service::api-service.models');
    }
}
