# A simple api service for supporting filamentphp

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rupadana/filament-api-service.svg?style=flat-square)](https://packagist.org/packages/rupadana/filament-api-service)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/rupadana/filament-api-service/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/rupadana/filament-api-service/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
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

- '/api/blogs'   - This will return LengthAwarePaginator 
- '/api/blogs/1' - This will return single resource      


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
