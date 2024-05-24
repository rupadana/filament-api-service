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

- [GET] '/api/`admin`/blogs'   - Return LengthAwarePaginator
- [GET] '/api/`admin`/blogs/1' - Return single resource
- [PUT] '/api/`admin`/blogs/1' - Update resource
- [POST] '/api/`admin`/blogs' - Create resource
- [DELETE] '/api/`admin`/blogs/1' - Delete resource

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

### Filtering & Allowed Field

We used `"spatie/laravel-query-builder": "^5.3"` to handle query selecting, sorting and filtering. Check out [the spatie/laravel-query-builder documentation](https://spatie.be/docs/laravel-query-builder/v5/introduction) for more information.
You can specified `allowedFilters` and `allowedFields` in your model. For example:

```php
class User extends Model {
    // Which fields can be selected from the database through the query string
    public static array $allowedFields = [
        'name'
    ];

    // Which fields can be used to sort the results through the query string
    public static array $allowedSorts = [
        'name',
        'created_at'
    ];

    // Which fields can be used to filter the results through the query string
    public static array $allowedFilters = [
        'name'
    ];
}
```

### Create a Handler

To create a handler you can use this command. By default, i'm using CreateHandler

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
        /**
        * @return string|null
        */
        public static function getApiTransformer(): ?string
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

Since version 3.0, it will automatically detect routes and secure it using sanctum.

To Generate Token, you just need create it from admin panel. It will be Token Resource there.

![Image](https://res.cloudinary.com/rupadana/image/upload/v1704958748/Screenshot_2024-01-11_at_15.37.55_ncpg8n.png)

### Public API

Set API to public by overriding this property on your API Handler. Assume we have a `PaginationHandler`

```php
class PaginationHandler extends Handlers {
    public static bool $public = true;
}
```

## Swagger Api Docs Generation

It is possible to generate Swagger API docs with this package. You have to make sure you have the following dependencies:

```
composer require darkaonline/l5-swagger
```

And make sure you install it correctly according to their [installation manual](https://github.com/DarkaOnLine/L5-Swagger/wiki/Installation-&-Configuration#installation).
In development we recommend setting the config in `l5-swagger.php` `defaults.generate_always` to `true`.

When generating Api Swagger Docs for an Filament Resource it is required to define a Transformer. Otherwise the generator does not know how your resource entity types are being handled. What the response format and types look like.

therefor you should always create an Transformer, which is explained above at the section "[Transform API Resonse](#transform-api-response)".

Then you can use the following command to generate API docs for your resources:

```bash
php artisan make:filament-api-docs {resource?} {namespace?}
```

so for example:

```bash
php artisan make:filament-api-docs BlogResource
```

The CLI command accepts two optional parameters: `{resource?}` and `{namespace?}`.
By default the Swagger API Docs will be placed in `app/Virtual/Filament/Resources` folder under their own resource name.

For example the BlogResource Api Docs wil be in the following folder `app/Virtual/Filament/Resource/BlogResource`.

First it will check if you have an existing the Swagger Docs Server config. This is a file `ApiDocsController.php` and resides in `app/Virtual/Filament/Resources`.
It holds some general information about your swagger API Docs server. All generated files can be manual edited afterwards.
Regenerating an API Docs Serverinfo or Resource will always ask you if you want to override the existing file.

When done, you can go to the defined swagger documentation URL as defined in `l5-swagger.php` config as `documentations.routes.api`.

If you want to generate the Api Docs manually because in your `l5-swagger.php` config you have set `defatuls.generate_always` to `false` you can do so by invoking:

```bash
php artisan l5-swagger:generate
```

## License

The MIT License (MIT).

## Supported By

<img src="https://res.cloudinary.com/rupadana/image/upload/v1707040287/phpstorm_xjblau.png" width="50px" height="50px"></img>
