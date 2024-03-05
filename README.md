# A simple api service for supporting filamentphp

[![Total Downloads](https://img.shields.io/packagist/dt/rupadana/filament-api-service.svg?style=flat-square)](https://packagist.org/packages/rupadana/filament-api-service)
![Fix Code](https://github.com/rupadana/filament-api-service/actions/workflows/fix-php-code-styling.yml/badge.svg?branch=main)
![Run Test](https://github.com/rupadana/filament-api-service/actions/workflows/run-tests.yml/badge.svg?branch=main)


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

### Publish config

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
    ],
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


## Token Resource

By default, Token resource only show on `super_admin` role. you can modify give permission to other permission too. 

Token Resource is protected by TokenPolicy. You can disable it by publishing the config and change this line.

```php
'models' => [
        'token' => [
            'enable_policy' => false // default: true
        ]
    ],
```

## Filtering & Allowed Field

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

## Create a Handler

To create a handler you can use this command. By default, i'm using CreateHandler

```bash
php artisan make:filament-api-handler BlogResource
``` 

or

```bash
php artisan make:filament-api-handler Blog
``` 

## Transform API Response

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


## Group Name & Prefix

You can edit prefix & group route name as you want, default this plugin use model singular label;

```php
    class BlogApiService extends ApiService
    {
        ...
        protected static string | null $groupRouteName = 'myblog';
        ...
    }
```

## Tenant Aware

If you want to make api routes tenant aware. you can set 'is_tenant_aware' to `true` in your published api-service.php.

You then also need to set the correct tenant relationship name in the api-service.php with the configkey: `tenant_ownership_relationship_name`.

Lastly make sure you add the `HasApiTenantScope` trait to all of your models which are used by all your Api Resources:

```php
use Rupadana\ApiService\Traits\HasApiTenantScope; // <-- add this line

class Blog extends Model
{
    use HasFactory;
    use HasApiTenantScope; // <-- add this line
}
```

Now your API endpoints will have URI prefix of `{tenant}` in the API routes like this:

```
  POST      api/admin/{tenant}/blog
  GET|HEAD  api/admin/{tenant}/blog
  PUT       api/admin/{tenant}/blog/{id}
  DELETE    api/admin/{tenant}/blog/{id}
  GET|HEAD  api/admin/{tenant}/blog/{id}
```

## How to secure it?

Since version 3.0, it will automatically detect routes and secure it using sanctum.

To Generate Token, you just need create it from admin panel. It will be Token Resource there.

![Image](https://res.cloudinary.com/rupadana/image/upload/v1704958748/Screenshot_2024-01-11_at_15.37.55_ncpg8n.png)

## Public API

Set API to public by overriding this property on your API Handler. Assume we have a `PaginationHandler`

```php
class PaginationHandler extends Handlers {
    public static bool $public = true;
}
```

## TODO

- [ ] Test Plugin for Tenancy purpose
- [ ] Each user can manage their own token only

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Rupadana](https://github.com/rupadana)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Supported By
<img src="https://res.cloudinary.com/rupadana/image/upload/v1707040287/phpstorm_xjblau.png" width="50px" height="50px"></img>
