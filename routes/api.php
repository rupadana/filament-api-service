<?php

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

Route::prefix('api')
    ->name('api.')
    ->group(function () {
        $panels = Filament::getPanels();

        foreach ($panels as $key => $panel) {
            try {
                if (config('api-service.route.wrap_with_panel_id', true)) {
                    Route::prefix($panel->getId())
                        ->name($panel->getId() . '.')
                        ->group(function () use ($panel) {
                            $panel->getPlugin('api-service')
                                ->route($panel);
                        });
                } else {
                    $panel->getPlugin('api-service')
                        ->route($panel);
                }
            } catch (Exception $e) {
            }
        }
    });
