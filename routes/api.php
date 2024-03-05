<?php

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

Route::prefix('api')
    ->name('api.')
    ->group(function () {
        $panels = Filament::getPanels();

        foreach ($panels as $key => $panel) {
            try {

                $panelId = $panel->getId();
                $hasTenancy = $panel->hasTenancy();
                $tenantRoutePrefix = $panel->getTenantRoutePrefix();
                $tenantSlugAttribute = $panel->getTenantSlugAttribute();

                if (
                    $hasTenancy &&
                    config('api-service.tenancy.enabled') &&
                    config('api-service.tenancy.is_tenant_aware')
                ) {
                    Route::prefix($panelId . '/' . (($tenantRoutePrefix) ? "{$tenantRoutePrefix}/" : '') . '{tenant' . (($tenantSlugAttribute) ? ":{$tenantSlugAttribute}" : '') . '}')
                        ->name($panelId . '.')
                        ->group(function () use ($panel) {
                            $apiServicePlugin = $panel->getPlugin('api-service');
                            $apiServicePlugin->route($panel);
                        });
                }
                if (!config('api-service.tenancy.is_tenant_aware')) {
                    Route::prefix($panelId)
                        ->name($panelId . '.')
                        ->group(function () use ($panel) {
                            $apiServicePlugin = $panel->getPlugin('api-service');
                            $apiServicePlugin->route($panel);
                        });
                }
            } catch (Exception $e) {
            }
        }
    });
