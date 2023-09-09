<?php

namespace Rupadana\ApiService\Commands;

use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class MakeApiHandlerCommand extends Command
{
    use CanManipulateFiles;

    protected $description = 'Create a new API Handler for supporting filamentphp Resource';

    protected $signature = 'make:filament-api-handler {resource?} {handler?} {--panel=}';

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

        $handler = (string) str(
            $this->argument('handler') ?? text(
                label: 'What is the Handler name?',
                placeholder: 'UpdateHandler',
                required: true
            )
        )
            ->studly()
            ->beforeLast('Handler')
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->studly()
            ->replace('/', '\\');

        if (blank($handler)) {
            $handler = 'Handler';
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
        $handlerClass = "{$handler}Handler";
        $resourceNamespace = $modelNamespace;
        $namespace .= $resourceNamespace !== '' ? "\\{$resourceNamespace}" : '';

        $baseResourcePath =
            (string) str($resource)
                ->prepend('/')
                ->prepend($path)
                ->replace('\\', '/')
                ->replace('//', '/');

        $handlersNamespace = "{$namespace}\\{$resourceClass}\\Api\\Handlers";

        $handlerDirectory = "{$baseResourcePath}/Api/Handlers/$handlerClass.php";

        $this->copyStubToApp('CustomHandler', $handlerDirectory, [
            'resource' => "{$namespace}\\{$resourceClass}",
            'resourceClass' => $resourceClass,
            'handlersNamespace' => $handlersNamespace,
            'handlerClass' => $handlerClass,
        ]);

        $this->components->info("Successfully created API Handler for {$resource}!");
        $this->components->info("You can register \"Handlers\\$handlerClass::route(\$router);\" to allRoutes method on APIService");

        return static::SUCCESS;
    }
}
