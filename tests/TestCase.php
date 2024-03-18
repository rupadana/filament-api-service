<?php

namespace Rupadana\ApiService\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\SanctumServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Rupadana\ApiService\ApiServiceServiceProvider;
use Rupadana\ApiService\Tests\Fixtures\Providers\AdminPanelProvider;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;
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
        });
    }

    protected function defineDatabaseMigrations(): void
    {
        // Migrations for test fixtures
        if (version_compare(Application::VERSION, '11', '>=')) {
            $this->loadMigrationsFrom(realpath(__DIR__.'/Fixtures/Database/Migrations'));
        } else {
            (include __DIR__ . '/Fixtures/Database/Migrations/2014_10_12_000000_create_users_table.php')->up();
            (include __DIR__ . '/Fixtures/Database/Migrations/2019_12_14_000001_create_personal_access_tokens_table.php')->up();
            (include __DIR__ . '/Fixtures/Database/Migrations/01_create_products_table.php')->up();
        }
    }

    protected function defineRoutes($router)
    {
        // $router->group(['prefix' => 'api'], function () {
        //     ProductApiService::routes();
        // });
    }
}
