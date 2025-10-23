<?php

namespace Rupadana\ApiService;

use Exception;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Rupadana\ApiService\Resources\TokenResource;

class ApiServicePlugin implements Plugin
{
    use CanManipulateFiles;

    /**
     * @var array<string>
     */
    protected array $middleware = [];

    public function getId(): string
    {
        return 'api-service';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            TokenResource::class,
        ]);
    }

    public function boot(Panel $panel): void {}

    public static function getAbilities(Panel $panel): array
    {
        $resources = $panel->getResources();

        $abilities = [];
        foreach ($resources as $key => $resource) {
            try {

                $resourceName = str($resource)->beforeLast('Resource')->explode('\\')->last();
                $pluralResourceName = str($resourceName)->pluralStudly();

                // Try with plural form first (as created by make:filament-api-service command)
                $apiServiceClass = str($resource)->remove($resourceName . 'Resource') . $pluralResourceName . '\\Api\\' . $resourceName . 'ApiService';
                
                // If the plural form doesn't exist, try without plural (legacy support)
                if (!class_exists($apiServiceClass)) {
                    $apiServiceClass = str($resource)->remove($resourceName . 'Resource') . 'Api\\' . $resourceName . 'ApiService';
                }

                $handlers = app($apiServiceClass)->handlers();

                if (count($handlers) > 0) {
                    $abilities[$resource] = [];
                    foreach ($handlers as $key => $handler) {
                        $abilities[$resource][$handler] = app($handler)->getAbility();
                    }
                }
            } catch (Exception $e) {
            }
        }

        return $abilities;
    }

    public function route(Panel $panel): void
    {
        $resources = $panel->getResources();

        foreach ($resources as $key => $resource) {
            try {
                $resourceName = str($resource)->beforeLast('Resource')->explode('\\')->last();
                $pluralResourceName = str($resourceName)->pluralStudly();

                // Try with plural form first (as created by make:filament-api-service command)
                $apiServiceClass = str($resource)->remove($resourceName . 'Resource') . $pluralResourceName . '\\Api\\' . $resourceName . 'ApiService';
                
                // If the plural form doesn't exist, try without plural (legacy support)
                if (!class_exists($apiServiceClass)) {
                    $apiServiceClass = str($resource)->remove($resourceName . 'Resource') . 'Api\\' . $resourceName . 'ApiService';
                }
                
                app($apiServiceClass)->registerRoutes($panel);
            } catch (Exception $e) {
                // Log error in debug mode
                if (config('app.debug')) {
                    logger()->error("Error registering API routes for resource '{$resource}': " . $e->getMessage(), [
                        'exception' => $e,
                        'resource' => $resource,
                    ]);
                }
            }
        }
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    /**
     * @param array<string> $middleware
     */
    public function middleware(array $middleware): static
    {
        $this->middleware = [
            ...$this->middleware,
            ...$middleware,
        ];

        return $this;
    }

    public function getMiddlewares(): array
    {
        return $this->middleware;
    }
}
