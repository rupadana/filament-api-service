<?php

namespace Rupadana\ApiService\Commands;

use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

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
                placeholder: 'CreateHandler',
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

        $stubName = $this->getStubForHandler($handlerClass);
        $this->copyStubToApp($stubName, $handlerDirectory, [
            'resource' => "{$namespace}\\{$resourceClass}",
            'resourceClass' => $resourceClass,
            'handlersNamespace' => $handlersNamespace,
            'handlerClass' => $handlerClass,
        ]);

        $this->createOrUpdateApiServiceFile($baseResourcePath, $namespace, $resourceClass, $modelClass, $handlerClass);

        $this->components->info("Successfully created API Handler for {$resource}!");
        $this->components->info("Handler {$handlerClass} has been registered in the APIService.");

        return static::SUCCESS;
    }

    private function getStubForHandler(string $handlerClass): string
    {
        $handlerMap = [
            'CreateHandler' => 'CreateHandler',
            'UpdateHandler' => 'UpdateHandler',
            'DeleteHandler' => 'DeleteHandler',
            'DetailHandler' => 'DetailHandler',
            'PaginationHandler' => 'PaginationHandler',
        ];

        return $handlerMap[$handlerClass] ?? 'CustomHandler';
    }

    private function createOrUpdateApiServiceFile(string $baseResourcePath, string $namespace, string $resourceClass, string $modelClass, string $handlerClass): void
    {
        $apiServicePath = "{$baseResourcePath}/Api/{$modelClass}ApiService.php";
        $apiServiceNamespace = "{$namespace}\\{$resourceClass}\\Api";

        if (! File::exists($apiServicePath)) {
            $this->copyStubToApp('CustomApiService', $apiServicePath, [
                'namespace' => $apiServiceNamespace,
                'resource' => "{$namespace}\\{$resourceClass}",
                'resourceClass' => $resourceClass,
                'apiServiceClass' => "{$modelClass}ApiService",
                'handlers' => "Handlers\\{$handlerClass}::class",
            ]);
        } else {
            $content = File::get($apiServicePath);
            $updatedContent = $this->updateHandlersInContent($content, $handlerClass);
            File::put($apiServicePath, $updatedContent);
        }
    }

    private function updateHandlersInContent(string $content, string $newHandler): string
    {
        $pattern = '/public\s+static\s+function\s+handlers\(\)\s*:\s*array\s*\{[^}]*\}/s';

        if (preg_match($pattern, $content, $matches)) {
            $handlersBlock = $matches[0];
            $handlersList = $this->extractHandlersList($handlersBlock);

            if (! in_array("Handlers\\{$newHandler}::class", $handlersList)) {
                $handlersList[] = "Handlers\\{$newHandler}::class";
            }

            $newHandlersBlock = "public static function handlers() : array\n    {\n        return [\n            " .
                                implode(",\n            ", $handlersList) .
                                "\n        ];\n    }";

            return str_replace($handlersBlock, $newHandlersBlock, $content);
        }

        return $content;
    }

    private function extractHandlersList(string $handlersBlock): array
    {
        preg_match('/return\s*\[(.*?)\]/s', $handlersBlock, $matches);
        $handlersListString = $matches[1] ?? '';
        $handlersList = array_map('trim', explode(',', $handlersListString));

        return array_filter($handlersList);
    }
}
