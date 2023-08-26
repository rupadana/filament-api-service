<?php

namespace Rupadana\FilamentApiService;

use Rupadana\FilamentApiService\Commands\MakeApiServiceCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;


class FilamentApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-api-service')
            ->hasCommand(MakeApiServiceCommand::class);
    }
}
