<?php

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;
use Rupadana\ApiService\ApiService;

Route::prefix('api')
    ->name('api.')
    ->group(function () {
        $panels = Filament::getPanels();

        foreach ($panels as $key => $panel) {
            try {
                $panelPrefix = ApiService::isRoutePrefixedByPanel() ? $panel->getId() : '';
                Route::name($panelPrefix)
                    ->prefix($panelPrefix)
                    ->group(function () use ($panel) {
                        $panel->getPlugin('api-service')
                            ->route($panel);
                    });
            } catch (Exception $e) {
            }
        }
    });
