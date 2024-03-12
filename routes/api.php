<?php

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;
use Rupadana\ApiService\ApiService;
use Rupadana\ApiService\Exceptions\InvalidTenancyConfiguration;

Route::prefix('api')
    ->name('api')
    ->group(function () {
        if (!ApiService::isTenancyEnabled() && ApiService::tenancyAwareness()) {
            throw new InvalidTenancyConfiguration('Tenancy awereness is enabled. But, Tenancy is disabled.');
        }
        foreach (Filament::getPanels() as $key => $panel) {
            try {
                $apiServicePlugin = $panel->getPlugin('api-service');
                $panelId = $panel->getId();
                $panelPath = $panel->getPath();
                $hasTenancy = $panel->hasTenancy();
                $tenantRoutePrefix = $panel->getTenantRoutePrefix();
                $tenantDomain = $panel->getTenantDomain();
                $tenantSlugAttribute = $panel->getTenantSlugAttribute();
                $panelPrefix = ApiService::isRoutePrefixedByPanel() ? $panelPath ?? $panelId : '';

                $handlerMiddlewares = ApiService::getHandlerMiddlewares();

                $routeGroup = Route::name($panelPrefix . '.')
                    ->middleware($handlerMiddlewares);

                if (
                    $hasTenancy &&
                    ApiService::isTenancyEnabled() &&
                    ApiService::tenancyAwareness()
                ) {
                    $domains = $panel->getDomains();
                    foreach ((empty($domains) ? [null] : $domains) as $domain) {
                        if (filled($tenantDomain)) {
                            $routeGroup->domain($tenantDomain);
                        } else {
                            $routeGroup->prefix(
                                $panelPrefix . '/' .
                                    (
                                        filled($tenantRoutePrefix) ?
                                        "{$tenantRoutePrefix}/" :
                                        ''
                                    ) . '{tenant' . (
                                        filled($tenantSlugAttribute) ?
                                        ":{$tenantSlugAttribute}" :
                                        ''
                                    ) . '}',
                            );
                        }
                        $routeGroup->group(function () use ($panel, $apiServicePlugin) {
                            $apiServicePlugin->route($panel);
                        });
                    }
                }
                if (!ApiService::tenancyAwareness()) {
                    $routeGroup
                        ->prefix($panelPrefix . '/')
                        ->group(function () use ($panel, $apiServicePlugin) {
                            $apiServicePlugin->route($panel);
                        });
                }
            } catch (Exception $e) {
            }
        }
    });
