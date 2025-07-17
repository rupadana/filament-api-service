<?php

use Filament\Facades\Filament;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Rupadana\ApiService\ApiService;
use Rupadana\ApiService\Exceptions\InvalidTenancyConfiguration;
use Rupadana\ApiService\Http\Controllers\AuthController;

Route::prefix('api')
    ->name('api.')
    ->group(function (Router $router) {
        $router->post('/auth/login', [AuthController::class, 'login'])->middleware(config('api-service.login-middleware', []));
        $router->post('/auth/logout', [AuthController::class, 'logout'])->middleware(config('api-service.logout-middleware', ['auth:sanctum']));

        if (ApiService::tenancyAwareness() && (!ApiService::isRoutePrefixedByPanel() || !ApiService::isTenancyEnabled())) {
            throw new InvalidTenancyConfiguration("Tenancy awareness is enabled!. Please set 'api-service.route.panel_prefix=true' and 'api-service.tenancy.enabled=true'");
        }

        $panels = Filament::getPanels();

        foreach ($panels as $key => $panel) {
            try {

                $hasTenancy = $panel->hasTenancy();
                $tenantRoutePrefix = $panel->getTenantRoutePrefix();
                $tenantSlugAttribute = $panel->getTenantSlugAttribute();
                $apiServicePlugin = $panel->getPlugin('api-service');
                $middlewares = $apiServicePlugin->getMiddlewares();
                $panelRoutePrefix = ApiService::isRoutePrefixedByPanel() ? '{panel}' : '';
                $panelNamePrefix = $panelRoutePrefix ? $panel->getId() . '.' : '';

                if (
                    $hasTenancy &&
                    ApiService::isTenancyEnabled() &&
                    ApiService::tenancyAwareness()
                ) {
                    Route::prefix($panelRoutePrefix . '/' . (($tenantRoutePrefix) ? "{$tenantRoutePrefix}/" : '') . '{tenant' . (($tenantSlugAttribute) ? ":{$tenantSlugAttribute}" : '') . '}')
                        ->name($panelNamePrefix)
                        ->middleware($middlewares)
                        ->group(function () use ($panel, $apiServicePlugin) {
                            $apiServicePlugin->route($panel);
                        });
                }

                if (!ApiService::tenancyAwareness()) {
                    Route::prefix($panelRoutePrefix)
                        ->name($panelNamePrefix)
                        ->middleware($middlewares)
                        ->group(function () use ($panel) {
                            $apiServicePlugin = $panel->getPlugin('api-service');
                            $apiServicePlugin->route($panel);
                        });
                }
            } catch (Exception $e) {
            }
        }
    });
