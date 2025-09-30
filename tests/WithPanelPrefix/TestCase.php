<?php

namespace Rupadana\ApiService\Tests\WithPanelPrefix;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use DateInterval;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\SanctumServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Rupadana\ApiService\ApiServiceServiceProvider;
use Rupadana\ApiService\Tests\Fixtures\Models\Product;
use Rupadana\ApiService\Tests\Fixtures\Policies\ProductPolicy;
use Rupadana\ApiService\Tests\Fixtures\Providers\AdminPanelProvider;
use Rupadana\ApiService\Tests\Fixtures\Providers\AuthServiceProvider;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\QueryBuilder\QueryBuilderServiceProvider;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [
            ActionsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            LivewireServiceProvider::class,
            NotificationsServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            QueryBuilderServiceProvider::class,
            AuthServiceProvider::class,
            AdminPanelProvider::class,
            SanctumServiceProvider::class,
            ApiServiceServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        tap($app['config'], function (Repository $config) {
            $config->set('database.default', 'testing');
            $config->set('database.connections.testing', [
                'driver' => 'sqlite',
                'database' => ':memory:',
            ]);

            $config->set('app.env', env('APP_ENV', 'testing'));
            $config->set('app.debug', env('APP_DEBUG', true));
            $config->set('app.key', 'base64:Hupx3yAySikrM2/edkZQNQHslgDWYfiBfCuSThJ5SK8=');
            $config->set('api-service.use-spatie-permission-middleware', false);
            $config->set('permission', [
                'models' => [
                    'permission' => Permission::class,
                    'role' => Role::class,
                ],
                'table_names' => [
                    'roles' => 'roles',
                    'permissions' => 'permissions',
                    'model_has_permissions' => 'model_has_permissions',
                    'model_has_roles' => 'model_has_roles',
                    'role_has_permissions' => 'role_has_permissions',
                ],
                'column_names' => [
                    'role_pivot_key' => null, // default 'role_id',
                    'permission_pivot_key' => null, // default 'permission_id',
                    'model_morph_key' => 'model_id',
                    'team_foreign_key' => 'team_id',
                ],
                'register_permission_check_method' => true,
                'register_octane_reset_listener' => false,
                'teams' => false,
                'use_passport_client_credentials' => false,
                'display_permission_in_exception' => false,
                'display_role_in_exception' => false,
                'enable_wildcard_permission' => false,
                'cache' => [
                    'expiration_time' => DateInterval::createFromDateString('24 hours'),
                    'key' => 'spatie.permission.cache',
                    'store' => 'default',
                ],
            ]);
        });

        Gate::policy(Product::class, ProductPolicy::class);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(realpath(__DIR__ . '/../Fixtures/Database/Migrations'));
    }
}
