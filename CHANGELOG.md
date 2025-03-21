# Changelog

All notable changes to `faguvenli/laravel-ddd-domain` will be documented in this file.

## 1.2.0 - 2025-03-21

- Modernized code structure with improved patterns and syntax
- Replaced `execute()` methods with `__invoke()` for Actions
- Simplified DTO structure with modern PHP features
- Added integration with Spatie's QueryBuilder package
- Updated controllers to use consistent response patterns
- Improved type-hinting and method signatures

## 1.1.0 - 2025-03-20

- Added support for API prefixes (Admin, Client, etc.)
- Added command option `--api-prefix` and config option `api_prefix`
- Updated route generation to include API prefixes in URI
- Added support for Laravel 12

## 1.0.0 - 2025-03-03

- Initial release
- Added `make:ddd-domain` command to generate DDD structure
- Support for Laravel 8.0+
- Fixed package stability (set to stable)