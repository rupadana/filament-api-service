<?php

namespace Rupadana\ApiService\Resources;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Rupadana\ApiService\ApiServicePlugin;
use Rupadana\ApiService\Models\Token;
use Rupadana\ApiService\Resources\TokenResource\Pages\CreateToken;
use Rupadana\ApiService\Resources\TokenResource\Pages\ListTokens;

class TokenResource extends Resource
{
    protected static ?string $model = Token::class;

    // protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static bool $isScopedToTenant = false;

    public static function shouldRegisterNavigation(): bool
    {
        return config('api-service.navigation.token.should_register_navigation', false);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('api-service::api-service.section.general'))
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
                    ->schema(static::getAbilitiesSchema())
                    ->columns(2),
            ])
            ->columns(1);
    }
    public static function getAbilitiesSchema(): array
    {
        $schema = [];

        $abilities = ApiServicePlugin::getAbilities(Filament::getCurrentOrDefaultPanel());

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
                        ->selectAllAction(fn (Action $action) => $action->label(__('api-service::api-service.action.select_all')))
                        ->deselectAllAction(fn (Action $action) => $action->label(__('api-service::api-service.action.unselect_all')))
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
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListTokens::route('/'),
            'create' => CreateToken::route('/create'),
        ];
    }

    public static function getCluster(): ?string
    {
        return config('api-service.navigation.token.cluster', null);
    }

    public static function getNavigationGroup(): ?string
    {
        return __(config('api-service.navigation.token.group') ?? config('api-service.navigation.group.token'));
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
