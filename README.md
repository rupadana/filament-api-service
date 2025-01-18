# Filament Api Service

[![Total Downloads](https://img.shields.io/packagist/dt/rupadana/filament-api-service.svg?style=flat-square)](https://packagist.org/packages/rupadana/filament-api-service)
![Run Test](https://github.com/rupadana/filament-api-service/actions/workflows/run-tests.yml/badge.svg?branch=main)

A simple API service for supporting FilamentPHP

## Installation

You can install the package via composer:

```bash
composer require rupadana/filament-api-service
```

Register it to your filament Provider

```php
use Rupadana\ApiService\ApiServicePlugin;

$panel->plugins([
    ApiServicePlugin::make()
])
```

### Config

```bash
php artisan vendor:publish --tag=api-service-config
```

```php

return [
    'navigation' => [
        'token' => [
            'cluster' => null,
            'group' => 'User',
            'sort' => -1,
            'icon' => 'heroicon-o-key'
        ]
    ],
    'models' => [
        'token' => [
            'enable_policy' => true,
        ],
    ],
    'route' => [
        'panel_prefix' => true,
        'use_resource_middlewares' => false,
    ],
    'tenancy' => [
        'enabled' => false,
        'awareness' => false,
    ]
];
```

## Usage

```bash
php artisan make:filament-api-service BlogResource
```

Since version 3.0, routes automatically registered. it will grouped as '/api/`admin`'. `admin` is panelId. to disable panelId prefix, please set `route.panel_prefix` to `false`

So, You don't need to register the routes manually.

The routes will be :

| Method | Endpoint             | Description                 |
| ------ | -------------------- | --------------------------- |
| GET    | /api/`admin`/blogs   | Return LengthAwarePaginator |
| GET    | /api/`admin`/blogs/1 | Return single resource      |
| PUT    | /api/`admin`/blogs/1 | Update resource             |
| POST   | /api/`admin`/blogs   | Create resource             |
| DELETE | /api/`admin`/blogs/1 | Delete resource             |

On CreateHandler, you need to be create your custom request validation.

### Token Resource

By default, Token resource only show on `super_admin` role. you can modify give permission to other permission too.

Token Resource is protected by TokenPolicy. You can disable it by publishing the config and change this line.

```php
'models' => [
        'token' => [
            'enable_policy' => false // default: true
        ]
    ],
```

> [!IMPORTANT]  
> If you use Laravel 11, don't forget to run ``` php artisan install:api ``` to publish the personal_access_tokens migration after that run ``` php artisan migrate ``` to migrate the migration, but as default if you run the ``` php artisan install:api ``` it will ask you to migrate your migration.

### Filtering & Allowed Field

We used `"spatie/laravel-query-builder": "^5.3"` to handle query selecting, sorting and filtering. Check out [the spatie/laravel-query-builder documentation](https://spatie.be/docs/laravel-query-builder/v5/introduction) for more information.

In order to allow modifying the query for your model you can implement the `HasAllowedFields`, `HasAllowedSorts` and `HasAllowedFilters` Contracts in your model.

```php
class User extends Model implements HasAllowedFields, HasAllowedSorts, HasAllowedFilters {
    // Which fields can be selected from the database through the query string
    public static function getAllowedFields(): array
    {
        // Your implementation here
    }

    // Which fields can be used to sort the results through the query string
    public static function getAllowedSorts(): array
    {
        // Your implementation here
    }

    // Which fields can be used to filter the results through the query string
    public static function getAllowedFilters(): array
    {
        // Your implementation here
    }
}
```

### Create a Handler

To create a handler you can use this command. We have 5 Handler, CreateHandler, UpdateHandler, DeleteHandler, DetailHandler, PaginationHandler, If you want a custom handler then write what handler you want.

```bash
php artisan make:filament-api-handler BlogResource
```

or

```bash
php artisan make:filament-api-handler Blog
```

### Transform API Response

```bash
php artisan make:filament-api-transformer Blog
```

it will be create BlogTransformer in `App\Filament\Resources\BlogResource\Api\Transformers`

```php
<?php
namespace App\Filament\Resources\BlogResource\Api\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class BlogTransformer extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource->toArray();

        // or

        return md5(json_encode($this->resource->toArray()));
    }
}
```

next step you need to edit & add it to your Resource

```php
    use App\Filament\Resources\BlogResource\Api\Transformers\BlogTransformer;

    class BlogResource extends Resource
    {
        ...
        public static function getApiTransformer()
        {
            return BlogTransformer::class;
        }
        ...
    }
```

### Group Name & Prefix

You can edit prefix & group route name as you want, default this plugin use model singular label;

```php
    class BlogApiService extends ApiService
    {
        ...
        protected static string | null $groupRouteName = 'myblog';
        ...
    }
```

### Middlewares

You can add or override middlewares at two specific places. Via the Filament Panel Provider and/or via the Resources $routeMiddleware.

If you set `route.use_resource_middlewares` to true, the package will register the middlewares for that specific resource as defined in:

```php
class BlogResource extends Resource
    {
        ...
        protected static string | array $routeMiddleware = []; // <-- your specific resource middlewares
        ...
    }
```

Then your API resource endpoint will go through these middlewares first.

Another method of adding/overriding middlewares is via the initialization of the plugin in your Panel Provider by adding the `middleware()` method like so:

```php
use Rupadana\ApiService\ApiServicePlugin;

$panel->plugins([
    ApiServicePlugin::make()
        ->middleware([
        // ... add your middlewares
        ])
])
```

### Tenancy

When you want to enable Tenancy on this package you can enable this by setting the config `tenancy.enabled` to `true`. This makes sure that your api responses only retreive the data which that user has access to. So if you have configured 5 tenants and an user has access to 2 tenants. Then, enabling this feature will return only the data of those 2 tenants.

If you have enabled tenancy on this package but on a specific Resource you have defined `protected static bool $isScopedToTenant = false;`, then the API will honour this for that specific resource and will return all records.

If you want to make api routes tenant aware. you can set `tenancy.awareness` to `true` in your published api-service.php. This way this package will register extra API routes which will return only the specific tenant data in the API response.

Now your API endpoints will have URI prefix of `{tenant}` in the API routes when `tenancy.awareness` is `true`.

It will look like this:

```bash
  POST      api/admin/{tenant}/blog
  GET|HEAD  api/admin/{tenant}/blog
  PUT       api/admin/{tenant}/blog/{id}
  DELETE    api/admin/{tenant}/blog/{id}
  GET|HEAD  api/admin/{tenant}/blog/{id}
```

Overriding tenancy ownership relationship name by adding this property to the Handlers `protected static ?string $tenantOwnershipRelationshipName = null;`

### How to secure it?

Since version 3.4, this plugin includes built-in authentication routes:

| Method | Endpoint             | Description                 |
| ------ | -------------------- | --------------------------- |
| POST   | /api/auth/login      | Login                       |
| POST   | /api/auth/logout     | Logout                      |

We also use the permission middleware from [spatie/laravel-permission](https://spatie.be/docs/laravel-permission/v6/basic-usage/middleware). making it easier to integrate with [filament-shield](https://github.com/bezhanSalleh/filament-shield)

If you prefer to use the old version of the middleware, please set 'use-spatie-permission-middleware' => false.

### Public API

Set API to public by overriding this property on your API Handler. Assume we have a `PaginationHandler`

```php
class PaginationHandler extends Handlers {
    public static bool $public = true;
}
```

### Documentation

For our documentation, we utilize [Scramble](https://scramble.dedoc.co), a powerful tool for generating and managing API documentation. All available routes and their detailed descriptions can be accessed at /docs/api. This ensures that developers have a centralized and well-organized resource to understand and integrate with the API effectively.

## License

The MIT License (MIT).

## Supported By

<img src="https://res.cloudinary.com/rupadana/image/upload/v1707040287/phpstorm_xjblau.png" width="50px" height="50px"></img>
