# A simple api service for supporting filamentphp

[![Total Downloads](https://img.shields.io/packagist/dt/rupadana/filament-api-service.svg?style=flat-square)](https://packagist.org/packages/rupadana/filament-api-service)


## Installation

You can install the package via composer:

```bash
composer require rupadana/filament-api-service
```

## Usage

```bash
php artisan make:filament-api-service BlogResource
```

Add this code to your routes file, example in routes/api.php

```php
...
use App\Filament\Resources\BlogResource\Api;
...

BlogApiService::routes();
```

and then you will got this routes:

- [GET] '/api/blogs'   - Return LengthAwarePaginator 
- [GET] '/api/blogs/1' - Return single resource   
- [PUT] '/api/blogs/1' - Update resource
- [POST] '/api/blogs' - Create resource
- [DELETE] '/api/blogs/1' - Delete resource


On CreateHandler, you need to be create your custom request validation.

Im using `"spatie/laravel-query-builder": "^5.3"` to handle query and filtering. u can see `"spatie/laravel-query-builder": "^5.3"` [https://spatie.be/docs/laravel-query-builder/v5/introduction](documentation)


You can specified `allowedFilters` and `allowedFields` in your model

Example
```php
class User extends Model {
    public static array $allowedFilters = [
        'name'
    ];
    
    public static array $allowedFields = [
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

You can edit prefix & group route name as you want, default im use model singular label;

```php
    class BlogApiService extends ApiService
    {
        ...
        protected static string | null $groupRouteName = 'myblog';
        ...
    }
```


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
