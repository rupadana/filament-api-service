<?php

namespace Rupadana\ApiService\Commands;

use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class MakeApiServiceCommand extends Command
{
    use CanManipulateFiles;
    protected $description = 'Create a new API Service for supporting filamentphp Resource';
    protected $signature = 'make:filament-api-service {resource?} {--panel=}';

    public function handle(): int
    {
        $model = (string) str($this->argument('resource') ?? text(
            label: 'What is the Resource name?',
            placeholder: 'Blog',
            required: true,
        ))
            ->studly()
            ->beforeLast('Resource')
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->studly()
            ->replace('/', '\\');

        if (blank($model)) {
            $model = 'Resource';
        }

        $modelClass = (string) str($model)->afterLast('\\');

        $modelNamespace = str($model)->contains('\\') ?
            (string) str($model)->beforeLast('\\') :
            '';
        $pluralModelClass = (string) str($modelClass)->pluralStudly();

        $panel = $this->option('panel');

        if ($panel) {
            $panel = Filament::getPanel($panel);
        }

        if (! $panel) {
            $panels = Filament::getPanels();

            /** @var Panel $panel */
            $panel = (count($panels) > 1) ? $panels[select(
                label: 'Which panel would you like to create this in?',
                options: array_map(
                    fn (Panel $panel): string => $panel->getId(),
                    $panels,
                ),
                default: Filament::getDefaultPanel()->getId()
            )] : Arr::first($panels);
        }

        $resourceDirectories = $panel->getResourceDirectories();
        $resourceNamespaces = $panel->getResourceNamespaces();

        $namespace = (count($resourceNamespaces) > 1) ?
            select(
                label: 'Which namespace would you like to create this in?',
                options: $resourceNamespaces
            ) : (Arr::first($resourceNamespaces) ?? 'App\\Filament\\Resources');
        $path = (count($resourceDirectories) > 1) ?
            $resourceDirectories[array_search($namespace, $resourceNamespaces)] : (Arr::first($resourceDirectories) ?? app_path('Filament/Resources/'));

        $resource = "{$model}Resource";
        $resourceClass = "{$modelClass}Resource";
        $apiServiceClass = "{$model}ApiService";
        $transformer = "{$model}Transformer";
        $resourceNamespace = $modelNamespace;
        $namespace .= $resourceNamespace !== '' ? "\\{$resourceNamespace}" : '';

        $createHandlerClass = 'CreateHandler';
        $updateHandlerClass = 'UpdateHandler';
        $detailHandlerClass = 'DetailHandler';
        $paginationHandlerClass = 'PaginationHandler';
        $deleteHandlerClass = 'DeleteHandler';

        $baseResourcePath =
            (string) str("{$pluralModelClass}")
                ->prepend('/')
                ->prepend($path)
                ->replace('\\', '/')
                ->replace('//', '/');

        $transformerClass = "{$namespace}\\{$pluralModelClass}\\Api\\Transformers\\{$transformer}";
        $handlersNamespace = "{$namespace}\\{$pluralModelClass}\\Api\\Handlers";

        $resourceApiDirectory = "{$baseResourcePath}/Api/$apiServiceClass.php";
        $createHandlerDirectory = "{$baseResourcePath}/Api/Handlers/$createHandlerClass.php";
        $updateHandlerDirectory = "{$baseResourcePath}/Api/Handlers/$updateHandlerClass.php";
        $detailHandlerDirectory = "{$baseResourcePath}/Api/Handlers/$detailHandlerClass.php";
        $paginationHandlerDirectory = "{$baseResourcePath}/Api/Handlers/$paginationHandlerClass.php";
        $deleteHandlerDirectory = "{$baseResourcePath}/Api/Handlers/$deleteHandlerClass.php";

        $this->call('make:filament-api-transformer', [
            'resource' => $model,
            '--panel' => $panel->getId(),
        ]);
        collect(['Create', 'Update'])
            ->each(function ($name) use ($model, $panel) {
                $this->call('make:filament-api-request', [
                    'name' => $name,
                    'resource' => $model,
                    '--panel' => $panel->getId(),
                ]);
            });

        $this->copyStubToApp('ResourceApiService', $resourceApiDirectory, [
            'namespace' => "{$namespace}\\{$pluralModelClass}\\Api",
            'resource' => "{$namespace}\\{$pluralModelClass}\\{$resourceClass}",
            'resourceClass' => $resourceClass,
            'resourcePageClass' => $resourceApiDirectory,
            'apiServiceClass' => $apiServiceClass,
        ]);

        $this->copyStubToApp('DeleteHandler', $deleteHandlerDirectory, [
            'resource' => "{$namespace}\\{$pluralModelClass}\\{$resourceClass}",
            'resourcePath' => "{$namespace}\\{$pluralModelClass}",
            'resourceClass' => $resourceClass,
            'handlersNamespace' => $handlersNamespace,
            'model' => $model,
        ]);

        $this->copyStubToApp('DetailHandler', $detailHandlerDirectory, [
            'resource' => "{$namespace}\\{$pluralModelClass}\\{$resourceClass}",
            'resourcePath' => "{$namespace}\\{$pluralModelClass}",
            'resourceClass' => $resourceClass,
            'handlersNamespace' => $handlersNamespace,
            'transformer' => $transformer,
            'transformerClass' => $transformerClass,
            'model' => $model,
        ]);

        $this->copyStubToApp('CreateHandler', $createHandlerDirectory, [
            'resource' => "{$namespace}\\{$pluralModelClass}\\{$resourceClass}",
            'resourcePath' => "{$namespace}\\{$pluralModelClass}",
            'resourceClass' => $resourceClass,
            'handlersNamespace' => $handlersNamespace,
            'model' => $model,
        ]);

        $this->copyStubToApp('UpdateHandler', $updateHandlerDirectory, [
            'resource' => "{$namespace}\\{$pluralModelClass}\\{$resourceClass}",
            'resourcePath' => "{$namespace}\\{$pluralModelClass}",
            'resourceClass' => $resourceClass,
            'handlersNamespace' => $handlersNamespace,
            'model' => $model,
        ]);

        $this->copyStubToApp('PaginationHandler', $paginationHandlerDirectory, [
            'resource' => "{$namespace}\\{$pluralModelClass}\\{$resourceClass}",
            'resourcePath' => "{$namespace}\\{$pluralModelClass}",
            'resourceClass' => $resourceClass,
            'handlersNamespace' => $handlersNamespace,
            'transformer' => $transformer,
            'transformerClass' => $transformerClass,
            'model' => $model,

        ]);

        $this->components->info("Successfully created API for {$resource}!");
        $this->components->info("It automatically registered to '/api' route group");

        return static::SUCCESS;
    }
}
