<?php

namespace Rupadana\ApiService\Http;

use ReflectionClass;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;
use Rupadana\ApiService\ApiService;
use Rupadana\ApiService\Exceptions\TransformerNotFoundException;
use Rupadana\ApiService\Traits\HasHandlerTenantScope;
use Rupadana\ApiService\Traits\HttpResponse;
use Rupadana\ApiService\Transformers\DefaultTransformer;

class Handlers
{
    use HttpResponse;
    use HasHandlerTenantScope;
    protected Panel $panel;
    public static ?string $uri = '/';
    public static string $method = 'get';
    public static ?string $resource = null;
    protected static string $keyName = 'id';
    protected static bool $public = false;
    const POST = 'post';
    const GET = 'get';
    const DELETE = 'delete';
    const PATCH = 'patch';
    const PUT = 'put';

    public function __construct()
    {
        if (request()->routeIs('api.*') && ApiService::isRoutePrefixedByPanel()) {
            $this->panel(Filament::getPanel(request()->route()->parameter('panel')));
            Filament::setCurrentPanel($this->getPanel());
        }
    }

    public static function getMethod()
    {
        return static::$method;
    }

    public static function route(Router $router)
    {
        $method = static::getMethod();

        $router
            ->{$method}(static::$uri, [static::class, 'handler'])
            ->name(static::getKebabClassName())
            ->middleware(static::getRouteMiddleware());
    }

    public static function isPublic(): bool
    {
        return static::$public;
    }

    public static function getRouteMiddleware(): array
    {
        if (static::isPublic()) {
            return [];
        }

        return [
            'auth:sanctum',
            static::getMiddlewareAliasName() . ':' . static::stringifyAbility(),
        ];
    }

    protected static function getMiddlewareAliasName()
    {
        return 'ability';
    }

    public static function getKebabClassName()
    {
        return str(str(static::class)->beforeLast('Handler')->explode('\\')->last())->kebab();
    }

    public static function stringifyAbility()
    {
        return implode(',', static::getAbility());
    }

    public static function getAbility(): array
    {
        return [
            str(str(static::getModel())->explode('\\')->last())->kebab() . ':' . static::getKebabClassName(),
        ];
    }

    public static function getModel()
    {
        return static::$resource::getModel();
    }

    public static function getDto(): ?string
    {
        $modelReflection = new ReflectionClass(static::getModel());
        if (property_exists(static::getModel(), 'dataClass')) {
            return $modelReflection->getProperty('dataClass')->getDefaultValue();
        }
        return null;
    }

    public static function getApiTransformer(): ?string
    {
        return match (ApiService::getApiVersionMethod()) {
            'path' => static::getTransformerFromUrlPath(),
            'query' => static::getTransformerFromUrlQuery(),
            'header' => static::getTransformerFromRequestHeader(),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function getApiTransformers(): array
    {
        return array_merge([
            ApiService::getDefaultTransformerName() => DefaultTransformer::class,
        ], method_exists(static::$resource, 'apiTransformers') ? array_combine(
            array_map(fn ($class) => Str::kebab(class_basename($class)), $transformers = static::$resource::apiTransformers()),
            $transformers
        ) : []); // @phpstan-ignore-line
    }

    /**
     * @throws TransformerNotFoundException
     */
    protected static function getTransformerFromUrlPath(): string
    {
        // $routeApiVersion = request()->segment(1);
        $routeApiVersion = request()->route(ApiService::getApiVersionParameterName());
        $transformer = Str::kebab($routeApiVersion);

        if ($transformer && !array_key_exists($transformer, self::getApiTransformers())) {
            throw new TransformerNotFoundException($transformer);
        }

        return self::getApiTransformers()[$transformer];
    }

    /**
     * @throws TransformerNotFoundException
     */
    protected static function getTransformerFromUrlQuery(): string
    {
        $queryName = strtolower(ApiService::getApiVersionParameterName());

        if (!request()->filled($queryName)) {
            if (!method_exists(static::$resource, 'getApiTransformer')) {
                return self::getApiTransformers()[ApiService::getDefaultTransformerName()];
            }
            return static::$resource::getApiTransformer();
        }

        $transformer = request()->input($queryName);
        $transformer = Str::kebab($transformer);

        if ($transformer && !array_key_exists($transformer, self::getApiTransformers())) {
            throw new TransformerNotFoundException($transformer);
        }

        return self::getApiTransformers()[$transformer];

    }

    /**
     * @throws TransformerNotFoundException
     */
    protected static function getTransformerFromRequestHeader(): string
    {
        $headerName = strtolower(config('api-service.route.api_transformer_header'));
        if (!request()->headers->has($headerName)) {
            if (!method_exists(static::$resource, 'getApiTransformer')) {
                return self::getApiTransformers()[ApiService::getDefaultTransformerName()];
            }
            return static::$resource::getApiTransformer();
        }

        $transformer = request()->headers->get($headerName);
        $transformer = Str::kebab($transformer);

        if ($transformer && !array_key_exists($transformer, self::getApiTransformers())) {
            throw new TransformerNotFoundException($transformer);
        }

        return self::getApiTransformers()[$transformer];
    }


    public static function getKeyName(): ?string
    {
        return static::$keyName;
    }

    public static function getTenantOwnershipRelationship(Model $record): Relation
    {
        return static::$resource::getTenantOwnershipRelationship($record);
    }

    public static function getTenantOwnershipRelationshipName(): ?string
    {
        return static::$resource::getTenantOwnershipRelationshipName();
    }

    public static function isScopedToTenant(): bool
    {
        return static::$resource::isScopedToTenant();
    }

    public function panel(Panel $panel)
    {
        $this->panel = $panel;

        return $this;
    }

    public function getPanel(): Panel
    {
        return $this->panel;
    }

    protected static function getEloquentQuery()
    {
        $query = app(static::getModel())->query();

        if (static::isScopedToTenant() && ApiService::tenancyAwareness() && Filament::getCurrentPanel()) {
            $query = static::modifyTenantQuery($query);
        }

        return $query;
    }
}
