# Troubleshooting Guide

This document provides solutions to common issues and questions about Filament API Service.

## Table of Contents
- [Plugin Configuration](#plugin-configuration)
- [FilamentShield Integration](#filamentshield-integration)
- [Tenant Access Control](#tenant-access-control)
- [API Access from Desktop Applications](#api-access-from-desktop-applications)
- [API Documentation with Scramble](#api-documentation-with-scramble)
- [Token Resource Navigation](#token-resource-navigation)
- [Resources in Clusters](#resources-in-clusters)
- [Publishing Package Resources](#publishing-package-resources)

## Plugin Configuration

### Error After Integration with Filament 4 / Laravel 12 (Issue #121)

**Problem:** After installing the package with Filament 4 and Laravel 12, artisan commands fail with errors.

**Investigation Steps:**

1. **Verify Laravel and Filament versions:**
```bash
composer show | grep laravel/framework
composer show | grep filament/filament
```

The package supports:
- Laravel 11.0+ or 12.0+
- Filament 4.0+
- PHP 8.2+

2. **Check for conflicting dependencies:**
```bash
composer why-not rupadana/filament-api-service
```

3. **Ensure all migrations are run:**
```bash
php artisan install:api  # For Laravel 11+
php artisan migrate
```

4. **Clear all caches:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

5. **Check plugin registration:**
Make sure the plugin is properly registered in your panel provider:

```php
use Rupadana\ApiService\ApiServicePlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... other configuration
        ->plugins([
            ApiServicePlugin::make(),
        ]);
}
```

6. **Enable debug mode to see detailed errors:**
```env
APP_DEBUG=true
```

If the issue persists, please provide:
- The exact error message
- Your `composer.json` dependencies
- Your panel provider configuration
- Output of `php artisan about`

### Plugin Load Order (Issue #108)

**Problem:** When using FilamentShield or other plugins, you may encounter errors if ApiServicePlugin is registered before other required plugins.

**Solution:** Make sure ApiServicePlugin is registered **after** FilamentShieldPlugin:

```php
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Rupadana\ApiService\ApiServicePlugin;

$panel->plugins([
    FilamentShieldPlugin::make(),
    ApiServicePlugin::make(), // Load ApiServicePlugin AFTER FilamentShieldPlugin
])
```

If you encounter errors, enable debug mode in your `.env` file to see detailed error messages:

```env
APP_DEBUG=true
```

The package now logs errors when debug mode is enabled, making it easier to diagnose configuration issues.

## FilamentShield Integration (Issue #112)

**Problem:** Getting "User does not have the right permissions" error when using filament-shield integration.

**Solution:** The package uses Spatie's permission middleware by default. Follow these steps:

1. **Setup FilamentShield:**
```bash
php artisan shield:setup --fresh
php artisan shield:install
```

2. **Generate permissions for all resources:**
```bash
php artisan shield:generate --all
```

3. **Verify permissions exist:**
The command should create permissions for your API handlers (e.g., `view_blog`, `create_blog`, `update_blog`, `delete_blog`).

4. **Assign permissions to roles:**
Use Filament Shield's UI to assign the generated permissions to your roles.

5. **Alternative - Disable permission middleware:**
If you don't want to use the permission middleware, set this in your config:

```php
// config/api-service.php
return [
    'use-spatie-permission-middleware' => false,
];
```

## Tenant Access Control (Issue #94)

**Problem:** Users can access all tenants via API, not just their assigned tenants.

**Solution:** The package respects Filament's tenancy configuration, but you need to ensure:

1. **Enable tenancy in the config:**
```php
// config/api-service.php
return [
    'tenancy' => [
        'enabled' => true,
        'awareness' => false, // Set to true only if you want tenant-aware routes
    ],
];
```

2. **Implement proper authorization:**
Make sure your resource policies check tenant membership:

```php
// app/Policies/BlogPolicy.php
public function view(User $user, Blog $blog): bool
{
    // Check if user belongs to the blog's tenant
    return $user->tenants->contains($blog->tenant_id);
}
```

3. **Tenant-aware routes (optional):**
If you want routes to include tenant slugs (e.g., `/api/admin/{tenant}/blogs`), set:

```php
// config/api-service.php
return [
    'route' => [
        'panel_prefix' => true, // Required for tenant awareness
    ],
    'tenancy' => [
        'enabled' => true,
        'awareness' => true, // Enable tenant-aware routes
    ],
];
```

## API Access from Desktop Applications (Issue #105)

**Problem:** Desktop application can access API locally but gets redirected to login when deployed online.

**Solution:** This is typically a middleware or authentication issue:

1. **Check middleware configuration:**
Make sure your API routes use `sanctum` middleware, not `auth` or `web`:

```php
// The package already uses the correct middleware by default
// But verify your config/api-service.php:
return [
    'logout-middleware' => [
        'auth:sanctum', // Correct
    ],
];
```

2. **Verify CORS configuration:**
If your desktop app is accessing the API from a different domain, configure CORS in `config/cors.php`:

```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_origins' => ['*'], // Or specify your desktop app's domain
    'allowed_methods' => ['*'],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

3. **Use token authentication:**
Desktop applications should use token-based authentication:

```php
// Desktop app should send token in Authorization header
Authorization: Bearer {your-token}
```

4. **Check session driver:**
Make sure you're not using session-based authentication for API routes. Sanctum should use token authentication for API requests.

## API Documentation with Scramble (Issue #101)

**Problem:** Scramble API docs don't show filter/sort options or proper transformer responses.

**Current Limitation:** This is a known limitation of how Scramble generates documentation from the package's dynamic routes.

**Workaround:**

1. **Document filters and sorts manually:**
Add PHPDoc annotations to your handlers:

```php
/**
 * Get paginated list of products
 * 
 * @queryParam filter[name] Filter by product name
 * @queryParam sort Sort by field (e.g., name, created_at)
 * @queryParam fields[products] Select specific fields (e.g., id,name,price)
 */
public function handler(): JsonResponse
{
    // Your handler logic
}
```

2. **Transformers:**
Scramble may not automatically detect custom transformers. The API response will follow your transformer structure at runtime, but the documentation might show the raw model structure.

## Token Resource Navigation (Issue #88)

**Problem:** Unable to hide the Token resource from navigation.

**Solution:** The config setting works correctly. To hide the Token resource:

```php
// config/api-service.php
return [
    'navigation' => [
        'token' => [
            'should_register_navigation' => false, // This hides the navigation
        ],
    ],
];
```

To **show** the Token resource in navigation:

```php
return [
    'navigation' => [
        'token' => [
            'should_register_navigation' => true, // This shows the navigation
        ],
    ],
];
```

Make sure to clear your config cache after making changes:

```bash
php artisan config:clear
```

## Resources in Clusters (Issues #118, #119, #120)

**Problem:** Cannot create API services for resources in Filament clusters.

**Current Status:** The package now supports auto-discovery of API services in clusters (as of version 4.0.1). However, creating new API services for clustered resources requires manual steps.

**Workaround for creating API services:**

1. **Create the resource first using Filament:**
```bash
php artisan make:filament-resource Product --cluster=Catalog
```

2. **Manually create API service structure:**
For a resource at `App\Filament\Clusters\Catalog\Resources\ProductResource`, create:

```
app/Filament/Clusters/Catalog/Resources/
└── Products/
    ├── ProductResource.php
    └── Api/
        ├── ProductApiService.php
        ├── Transformers/
        │   └── ProductTransformer.php
        └── Handlers/
            ├── CreateHandler.php
            ├── UpdateHandler.php
            ├── DeleteHandler.php
            ├── DetailHandler.php
            └── PaginationHandler.php
```

3. **Copy and adapt from a working API service:**
The easiest approach is to copy from `tests/Fixtures/Resources/Product/Api/` and adapt the namespaces.

**Note:** Auto-discovery works automatically once the API service structure is in place.

## Publishing Package Resources (Issue #90)

**Problem:** Cannot publish the package's Filament resources (like TokenResource).

**Explanation:** The package's Filament resources (like `TokenResource`) are designed to be configured, not published. This is intentional to ensure the package works out of the box.

**Customization Options:**

1. **Configure the Token resource:**
```php
// config/api-service.php
return [
    'navigation' => [
        'token' => [
            'cluster' => null,
            'group' => 'User',
            'sort' => -1,
            'icon' => 'heroicon-o-key',
            'should_register_navigation' => false,
        ],
    ],
    'models' => [
        'token' => [
            'enable_policy' => true,
        ],
    ],
];
```

2. **Extend the Token resource:**
Create your own resource that extends the package's TokenResource:

```php
namespace App\Filament\Resources;

use Rupadana\ApiService\Resources\TokenResource as BaseTokenResource;

class CustomTokenResource extends BaseTokenResource
{
    // Override methods as needed
    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-lock-closed';
    }
}
```

Then register your custom resource instead:

```php
// In your panel provider
use App\Filament\Resources\CustomTokenResource;

$panel->resources([
    CustomTokenResource::class,
]);
```

## Feature Requests

### API Versioning (Issue #68)

**Status:** Feature request for future consideration.

**Current Workaround:**
- Use custom transformers to handle different response formats
- Implement versioning at the application level using route prefixes
- Create separate panels for different API versions

If you need this feature, please upvote the issue and describe your use case to help prioritize development.

## Getting Help

If you encounter an issue not covered in this guide:

1. Check the [main documentation](README.md)
2. Search [existing issues](https://github.com/rupadana/filament-api-service/issues)
3. Create a new issue with:
   - Detailed description of the problem
   - Steps to reproduce
   - Your environment (PHP version, Laravel version, Filament version)
   - Relevant code snippets
   - Error messages or logs
