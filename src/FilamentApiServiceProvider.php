<?php

namespace Rupadana\FilamentApiService;

use Rupadana\FilamentApiService\Commands\MakeApiHandlerCommand;
use Rupadana\FilamentApiService\Commands\MakeApiServiceCommand;
use Rupadana\FilamentApiService\Commands\MakeApiTransformerCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;


class FilamentApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-api-service')
            ->hasCommands([
                MakeApiServiceCommand::class,
                MakeApiTransformerCommand::class,
                MakeApiHandlerCommand::class
            ]);
    }
}
