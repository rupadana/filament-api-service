<?php

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;
use Rupadana\ApiService\ApiService;
use Rupadana\ApiService\Exceptions\InvalidTenancyConfiguration;

Route::prefix('api')
    ->name('api.')
    ->group(function () {
        if (ApiService::tenancyAwareness() && (! ApiService::isRoutePrefixedByPanel() || ! ApiService::isTenancyEnabled())) {
            throw new InvalidTenancyConfiguration("Tenancy awareness is enabled!. Please set 'api-service.route.panel_prefix=true' and 'api-service.tenancy.enabled=true'");
        }

        $panels = Filament::getPanels();

        foreach ($panels as $key => $panel) {
            try {

                $hasTenancy = $panel->hasTenancy();
                $tenantRoutePrefix = $panel->getTenantRoutePrefix();
                $tenantSlugAttribute = $panel->getTenantSlugAttribute();
                $panelRoutePrefix = ApiService::isRoutePrefixedByPanel() ? '{panel}' : '';
                $panelNamePrefix = $panelRoutePrefix ? $panel->getId() . '.' : '';

                if (
                    $hasTenancy &&
                    ApiService::isTenancyEnabled() &&
                    ApiService::tenancyAwareness()
                ) {
                    Route::prefix($panelRoutePrefix . '/' . (($tenantRoutePrefix) ? "{$tenantRoutePrefix}/" : '') . '{tenant' . (($tenantSlugAttribute) ? ":{$tenantSlugAttribute}" : '') . '}')
                        ->name($panelNamePrefix)
                        ->group(function () use ($panel) {
                            $apiServicePlugin = $panel->getPlugin('api-service');
                            $apiServicePlugin->route($panel);
                        });
                }

                if (! ApiService::tenancyAwareness()) {
                    Route::prefix($panelRoutePrefix)
                        ->name($panelNamePrefix)
                        ->group(function () use ($panel) {
                            $apiServicePlugin = $panel->getPlugin('api-service');
                            $apiServicePlugin->route($panel);
                        });
                }
            } catch (Exception $e) {
            }
        }
    });
