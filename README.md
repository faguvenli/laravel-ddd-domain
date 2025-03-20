# Laravel DDD Domain Generator

A Laravel package to quickly generate DDD (Domain-Driven Design) structured domains. This package works with Laravel 8 and above.

## Installation

You can install the package via composer:

```bash
composer require faguvenli/laravel-ddd-domain
```

The package will automatically register its service provider.

### Configuration

You can publish the configuration file with:

```bash
php artisan vendor:publish --provider="Laravelddd\Domain\LaravelDddDomainServiceProvider" --tag="config"
```

This will publish a `laravelddd-domain.php` file in your config directory with the following content:

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Base Paths
    |--------------------------------------------------------------------------
    |
    | These values determine the base paths where the domain and application
    | directories will be created. You can customize these if your Laravel
    | project uses a different structure.
    |
    */
    'paths' => [
        'domain' => 'src/Domain',
        'app' => 'src/app',
    ],

    /*
    |--------------------------------------------------------------------------
    | API Prefix
    |--------------------------------------------------------------------------
    |
    | This value determines the prefix for API controllers and resources.
    | Options might include 'Admin', 'Client', etc. Leave empty for no prefix.
    |
    */
    'api_prefix' => '',
];
```

You can customize the base paths for your domain and application code as needed. You can also set a default API prefix if you want all your domains to be placed under a specific prefix.

## Usage

To create a new DDD domain structure, run:

```bash
php artisan make:ddd-domain {name} [--api-prefix=]
```

Replace `{name}` with the name of your domain (singular form is recommended, but the command will handle pluralization as needed). You can optionally specify an API prefix using the `--api-prefix` option.

For example:

```bash
# Create a domain without API prefix
php artisan make:ddd-domain Product

# Create a domain with Admin API prefix
php artisan make:ddd-domain Product --api-prefix=Admin
```

This will create the following structure (example without API prefix):

```
src/
├── Domain/
│   └── Product/
│       ├── Actions/
│       │   ├── ProductCreateAction.php
│       │   └── ProductUpdateAction.php
│       ├── DataTransferObjects/
│       │   └── ProductData.php
│       ├── Exceptions/
│       ├── Models/
│       │   └── Product.php
│       └── QueryBuilders/
│           └── ProductQueryBuilder.php
└── app/
    └── Api/
        └── Product/
            ├── Controllers/
            │   └── ProductController.php
            ├── Factories/
            │   ├── ProductCreateDataFactory.php
            │   └── ProductUpdateDataFactory.php
            ├── Queries/
            │   └── ProductIndexQuery.php
            ├── Requests/
            │   ├── ProductCreateRequest.php
            │   └── ProductUpdateRequest.php
            └── Resources/
                └── ProductResource.php
```

If you use an API prefix like `--api-prefix=Admin`, the application layer will be structured as:

```
src/
└── app/
    └── Api/
        └── Admin/
            └── Product/
                ├── Controllers/
                ├── Factories/
                ├── Queries/
                ├── Requests/
                └── Resources/
```

The command also updates your `routes/api.php` file to include routes for the new domain.

## Structure Explanation

This package creates a DDD structure with the following components:

### Domain Layer
- **Models**: Your domain entities
- **Actions**: Business logic operations (Create, Update, etc.)
- **DataTransferObjects**: DTOs for passing data between layers
- **QueryBuilders**: Custom query builders for your models
- **Exceptions**: Domain-specific exceptions

### Application Layer
- **Controllers**: API controllers
- **Requests**: Form request validation
- **Resources**: API resources for response formatting
- **Factories**: Factories to create DTOs from requests
- **Queries**: Query objects for filtering and pagination

## Requirements

- PHP 8.0 or higher
- Laravel 8.0, 9.0, 10.0, 11.0, or 12.0

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
