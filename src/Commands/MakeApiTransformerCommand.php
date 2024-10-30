<?php

namespace Rupadana\ApiService\Commands;

use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use ReflectionClass;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class MakeApiTransformerCommand extends Command
{
    use CanManipulateFiles;
    protected $description = 'Create a Transformer for your API response';
    protected $signature = 'make:filament-api-transformer {resource?} {--panel=}';

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
                    fn(Panel $panel): string => $panel->getId(),
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
        $apiTransformerClass = "{$model}Transformer";
        $resourceNamespace = $modelNamespace;
        $namespace .= $resourceNamespace !== '' ? "\\{$resourceNamespace}" : '';

        $baseResourcePath =
            (string) str($resource)
                ->prepend('/')
                ->prepend($path)
                ->replace('\\', '/')
                ->replace('//', '/');

        $resourceApiTransformerDirectory = "{$baseResourcePath}/Api/Transformers/$apiTransformerClass.php";

        if ($this->confirm('Will this transformer use a DTO?')) {
            $dtoClass = $this->ask('What is the full Classname of the DTO? e.g. \\App\\Data\\PostData');
            if (!class_exists($dtoClass)) {
                $this->error("The class {$dtoClass} does not exist.");
                return 1;
            }

            $reflectionDtoClass = new ReflectionClass($dtoClass);
            $shortClassName = $reflectionDtoClass->getShortName();

            $extraStubArray = [
                'dtoNamespace' => "use " . $dtoClass . ";",
                'dtoUsesDtoAttribute' => "#[UsesDTO(" . $shortClassName . "::class)]",
                'returnToArray' => "return " . $shortClassName . "::from(\$this->resource)->transform();",
            ];
        } else {
            $extraStubArray = [
                'dtoNamespace' => "",
                'dtoUsesDtoAttribute' => "",
                'returnToArray' => "return \$this->resource->toArray();",
            ];
        }

        $this->copyStubToApp(
            'ApiTransformer',
            $resourceApiTransformerDirectory,
            array_merge([
                'namespace' => "{$namespace}\\{$resourceClass}\\Api\\Transformers",
                'resource' => "{$namespace}\\{$resourceClass}",
                'resourceClass' => $resourceClass,
                'resourcePageClass' => $resourceApiTransformerDirectory,
                'apiTransformerClass' => $apiTransformerClass,
            ], $extraStubArray)
        );

        $this->components->info("Successfully created API Transformer for {$resource}!");
        $this->components->info("Add this method to {$namespace}\\{$resourceClass}.php");
        $this->components->info("public static function getApiTransformer() {
            return $apiTransformerClass::class;
        }");

        return static::SUCCESS;
    }
}
