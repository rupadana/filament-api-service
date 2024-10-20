<?php

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;
use Rupadana\ApiService\ApiService;
use Rupadana\ApiService\Exceptions\InvalidTenancyConfiguration;

Route::prefix('api')
    ->name('api.')
    ->group(function () {

        $panels = Filament::getPanels();

        if ((ApiService::tenancyAwareness() && count($panels) > 1) && (! ApiService::isRoutePrefixedByPanel() || ! ApiService::isTenancyEnabled())) {
            throw new InvalidTenancyConfiguration("Tenancy awareness is enabled!. Please set 'api-service.route.panel_prefix=true' and 'api-service.tenancy.enabled=true'");
        }

        foreach ($panels as $key => $panel) {
            try {

                $hasTenancy = $panel->hasTenancy();
                $tenantRoutePrefix = $panel->getTenantRoutePrefix();
                $tenantSlugAttribute = $panel->getTenantSlugAttribute();
                $apiServicePlugin = $panel->getPlugin('api-service');
                $middlewares = $apiServicePlugin->getMiddlewares();
                $panelRoutePrefix = ApiService::isRoutePrefixedByPanel() ? '{panel}/' : '';
                $panelNamePrefix = $panelRoutePrefix ? $panel->getId() . '.' : '';

                if (
                    $hasTenancy &&
                    ApiService::isTenancyEnabled()
                ) {
                    $routePrefix = $panelRoutePrefix . ((ApiService::tenancyAwareness() && $tenantRoutePrefix) ? "{$tenantRoutePrefix}/" : '') . '{tenant' . (($tenantSlugAttribute) ? ":{$tenantSlugAttribute}" : '') . '}/';
                    Route::name($panelNamePrefix)
                        ->middleware($middlewares)
                        ->group(function () use ($panel, $routePrefix, $apiServicePlugin) {
                            $apiServicePlugin->route($panel, $routePrefix);
                        });
                }

                if (! ApiService::tenancyAwareness()) {
                    $routePrefix = $panelRoutePrefix;
                    Route::name($panelNamePrefix)
                        ->middleware($middlewares)
                        ->group(function () use ($panel, $routePrefix, $apiServicePlugin) {
                            $apiServicePlugin = $panel->getPlugin('api-service');
                            $apiServicePlugin->route($panel, $routePrefix);
                        });
                }
            } catch (Exception $e) {
            }
        }
    });
