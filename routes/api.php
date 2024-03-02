<?php

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

Route::prefix('api')
    ->name('api.')
    ->group(function () {
        $panels = Filament::getPanels();

        foreach ($panels as $key => $panel) {
            try {

                Route::prefix($panel->getId())
                    ->name($panel->getId() . '.')
                    ->group(function () use ($panel) {
                        $apiServicePlugin = $panel->getPlugin('api-service');
                        $apiServicePlugin->route($panel);
                    });

            } catch (Exception $e) {
            }
        }
    });
