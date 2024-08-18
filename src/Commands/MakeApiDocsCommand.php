<?php

namespace Rupadana\ApiService\Commands;

use Exception;
use ReflectionClass;
use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use OpenApi\Attributes as OAT;
use Rupadana\ApiService\Attributes\ApiPropertyInfo;

use function Laravel\Prompts\text;

class MakeApiDocsCommand extends Command
{
    use CanManipulateFiles;
    protected $description = 'Create a new API Swagger Docs Resource for supporting filamentphp Resource';
    protected $signature = 'make:filament-api-docs {resource?} {namespace?}';

    public function handle(): int
    {

        $baseServerPath = app_path('Virtual');
        $serverNameSpace = 'App\\Virtual';
        $serverFile = 'ApiDocsController.php';

        $virtualResourcePath = $baseServerPath . '/Filament/Resources';
        $virtualResourceNameSpace = $serverNameSpace . '\\Filament\\Resources';

        $isNotInstalled = $this->checkForCollision([$baseServerPath . '/' . $serverFile]);

        // ApiDocsController exists?
        if (!$isNotInstalled) {

            $this->components->info("Please provide basic API Docs information.");
            $this->components->info("All API Docs Resources will be placed in the app Virtual folder.");

            $serverTitle = text(
                label: 'Give the API Docs Server a name...',
                placeholder: 'API Documentation',
                default: 'API Documentation',
                required: true,
            );

            $serverVersion = text(
                label: 'Starting version of API Docs...',
                placeholder: '0.1',
                default: '0.1',
                required: true,
            );

            $serverContactName = text(
                label: 'API Contact Name',
                placeholder: 'your name',
                default: '',
                required: false,
            );

            $serverContactEmail = text(
                label: 'API Contact E-mail',
                placeholder: 'your@email.com',
                default: '',
                required: false,
            );

            $serverTerms = text(
                label: 'API Terms of Service url',
                placeholder: config('app.url') . '/terms-of-service',
                default: '',
            );

            $this->createDirectory('Virtual/Filament/Resources');

            $this->copyStubToApp('Api/ApiDocsController', $baseServerPath . '/' . $serverFile, [
                'namespace' => $serverNameSpace,
                'title' => $serverTitle,
                'version' => $serverVersion,
                'contactName' => $serverContactName,
                'contactEmail' => $serverContactEmail,
                'terms' => $serverTerms,
                'licenseName' => 'MIT License',
                'licenseUrl' => 'https://opensource.org/license/mit',
            ]);

            // Generate DefaultTransformer
            $transformerClass = 'DefaultTransformer';

            $stubVarsDefaultTransformer = [
                'namespace' => $serverNameSpace,
                'modelClass' => 'Default',
                'resourceClass' => null,
                'transformerName'   => $transformerClass,
            ];

            if (!$this->checkForCollision(["{$baseServerPath}/{$transformerClass}.php"])) {
                $this->copyStubToApp("Api/Transformer", $baseServerPath . '/' . $transformerClass . '.php', $stubVarsDefaultTransformer);
            }
        }

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

        if (!str($model)->contains('\\')) {
            $namespace = (string) str($this->argument('namespace') ?? text(
                label: 'What is the namespace of this Resource?',
                placeholder: 'App\Filament\Resources',
                default: 'App\Filament\Resources',
                required: true,
            ))
                ->studly()
                ->trim('/')
                ->trim('\\')
                ->trim(' ')
                ->studly()
                ->replace('/', '\\');

            if (blank($namespace)) {
                $namespace = 'App\\Filament\\Resources';
            }
        }

        $modelClass = (string) str($model)->afterLast('\\');

        $modelNamespace = str($model)->contains('\\') ?
            (string) str($model)->beforeLast('\\') :
            $namespace;

        $pluralModelClass = (string) str($modelClass)->pluralStudly();

        $resource = "{$modelNamespace}\\{$modelClass}Resource";
        $resourceClass = "{$modelClass}Resource";

        $resourcePath = (string) str($modelNamespace)->replace('\\', '/')->replace('//', '/');
        $baseResourceSourcePath =  (string) str($resourceClass)->prepend('/')->prepend(base_path($resourcePath))->replace('\\', '/')->replace('//', '/')->replace('App', 'app');

        $handlerSourceDirectory = "{$baseResourceSourcePath}/Api/Handlers/";
        $transformersSourceDirectory = "{$baseResourceSourcePath}/Api/Transformers/";

        $namespace = text(
            label: 'In which namespace would you like to create this API Docs Resource in?',
            default: $virtualResourceNameSpace
        );

        $modelOfResource = $resource::getModel();
        $modelDto = new $modelOfResource();

        $handlersVirtualNamespace = "{$namespace}\\{$resourceClass}\\Handlers";
        $transformersVirtualNamespace = "{$namespace}\\{$resourceClass}\\Transformers";

        $baseResourceVirtualPath = (string) str($resourceClass)->prepend('/')->prepend($virtualResourcePath)->replace('\\', '/')->replace('//', '/');

        $handlerVirtualDirectory = "{$baseResourceVirtualPath}/Handlers/";
        $transformersVirtualDirectory = "{$baseResourceVirtualPath}/Transformers/";

        if (method_exists($resource, 'getApiTransformer')) {
            // generate API transformer
            $transformers = method_exists($resource, 'apiTransformers') ? ($modelNamespace . "\\" . $resourceClass)::apiTransformers() : [($modelNamespace . "\\" . $resourceClass)::getApiTransformer()];
            foreach ($transformers as $transKey => $transformer) {
                $transformerClassPath = (string) str($transformer);
                $transformerClass = (string) str($transformerClassPath)->afterLast('\\');

                $stubVars = [
                    'namespace' => $namespace,
                    'modelClass' => $pluralModelClass,
                    'resourceClass' => '\\' . $resourceClass . '\\Transformers',
                    'transformerName'   => $transformerClass,
                    'transformerProperties' => '',
                ];

                if (property_exists($modelDto, 'dataClass') || method_exists($modelDto, 'dataClass')) {
                    $dtoProperties = $this->readModelDto($modelDto::class);
                    foreach ($dtoProperties as $dtoLine) {
                        $stubVars['transformerProperties'] .= $dtoLine . ",";
                        $stubVars['transformerProperties'] .= "\n                ";
                    }
                }

                if (!$this->checkForCollision(["{$transformersVirtualDirectory}/{$transformerClass}.php"])) {
                    $this->copyStubToApp("Api/Transformer", $transformersVirtualDirectory . '/' . $transformerClass . '.php', $stubVars);
                }
            }
        }

        try {
            $handlerMethods = File::allFiles($handlerSourceDirectory);

            foreach ($handlerMethods as $handler) {
                $handlerName = basename($handler->getFileName(), '.php');
                // stub exists?
                if (!$this->fileExists($this->getDefaultStubPath() . "/ApiDocs{$handlerName}")) {

                    if (!$this->checkForCollision(["{$handlerVirtualDirectory}/{$handlerName}.php"])) {

                        $this->copyStubToApp("Api/{$handlerName}", $handlerVirtualDirectory . '/' . $handlerName . '.php', [
                            'handlersVirtualNamespace' => $handlersVirtualNamespace,
                            'transformersVirtualNamespace' => $transformersVirtualNamespace,
                            'resource' => $resource,
                            'resourceClass' => $resourceClass,
                            'realResource' => "{$resource}\\Api\\Handlers\\{$handlerName}",
                            'resourceNamespace' => $modelNamespace,
                            'modelClass' => $modelClass,
                            'pluralClass' => $pluralModelClass,
                            'handlerClass' => $handler,
                            'handlerName' => $handlerName,
                            'capitalsResource' => strtoupper($modelClass),
                            'path' => '/' . str($pluralModelClass)->kebab(),
                        ]);
                    }
                }
            }
        } catch (Exception $e) {
            $this->components->error($e->getMessage());
        }

        $this->components->info("Successfully created API Docs for {$resource}!");

        return static::SUCCESS;
    }

    private function readModelDto(string $model): array
    {
        $openAttrReflection = new ReflectionClass(OAT\Property::class);

        $typesProperty = array_values(array_filter($openAttrReflection->getProperties(), function ($v) {
            return  $v->getName() == '_types';
        }));

        $openApiAttrTypes = $typesProperty[0]->getValue();
        $modelReflection = new ReflectionClass($model);

        $dtoClass = $modelReflection->getProperty('dataClass')->getDefaultValue();
        $dtoReflection = new ReflectionClass($dtoClass);

        foreach ($dtoReflection->getProperties() as $property) {
            if (!empty($property->getAttributes())) {
                $attribute = $property->getAttributes()[0];

                if (strpos($attribute->getName(), ApiPropertyInfo::class) !== false) {
                    $propertyTxt = "";

                    $propertyTxt .= "new OAT\Property(property: '" . $property->getName() . "', type: '" . $property->getType()->getName() . "', ";

                    $propertyTxt .= match ($property->getType()->getName()) {
                        'array' => "items: new OAT\Items(), ",
                        'object' => "schema: '{}', ",
                        default => '',
                    };

                    foreach ($attribute->getArguments() as $key => $argument) {
                        if (array_key_exists($key, $openApiAttrTypes) && $openApiAttrTypes[$key] !== 'string') {
                            $propertyTxt .= $key . ": '" . $argument . "', ";

                            $propertyTxt .= match ($argument) {
                                'array' => "items: new OAT\Items(), ",
                                'object' => "schema: '{}', ",
                                default => '',
                            };
                        } else {
                            $propertyTxt .= $key . ": '" . $argument . "', ";
                        }
                    }
                    $propertyTxt = rtrim($propertyTxt, ', ');
                    $propertyTxt .= ")";
                    $properties[] = $propertyTxt;
                }
            }
        }

        return $properties;
    }

    private function createDirectory(string $path): void
    {
        $path = app_path($path);

        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true, true);
        }
    }
}
