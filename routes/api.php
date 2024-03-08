<?php

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;
use Rupadana\ApiService\ApiService;
use Rupadana\ApiService\Exceptions\InvalidTenancyConfiguration;

Route::prefix('api')
    ->name('api.')
    ->group(function () {

        if (!ApiService::isTenancyEnabled() && ApiService::tenancyAwareness()) {
            throw new InvalidTenancyConfiguration("Tenancy awereness is enabled. But, Tenancy is disabled.");
        }
    
        $panels = Filament::getPanels();

        foreach ($panels as $key => $panel) {
            try {

                $panelRoutePrefix = ApiService::isRoutePrefixedByPanel() ? $panel->getId() : '';
                $hasTenancy = $panel->hasTenancy();
                $tenantRoutePrefix = $panel->getTenantRoutePrefix();
                $tenantSlugAttribute = $panel->getTenantSlugAttribute();

                if (
                    $hasTenancy &&
                    ApiService::isTenancyEnabled() &&
                    ApiService::tenancyAwareness()
                ) {
                    Route::prefix($panelRoutePrefix . '/' . (($tenantRoutePrefix) ? "{$tenantRoutePrefix}/" : '') . '{tenant' . (($tenantSlugAttribute) ? ":{$tenantSlugAttribute}" : '') . '}')
                        ->name($panelRoutePrefix . '.')
                        ->group(function () use ($panel) {
                            $apiServicePlugin = $panel->getPlugin('api-service');
                            $apiServicePlugin->route($panel);
                        });
                }

                if (!ApiService::tenancyAwareness()) {
                    Route::prefix($panelRoutePrefix)
                        ->name($panelRoutePrefix . '.')
                        ->group(function () use ($panel) {
                            $apiServicePlugin = $panel->getPlugin('api-service');
                            $apiServicePlugin->route($panel);
                        });
                }
            } catch (Exception $e) {
            }
        }
    });
