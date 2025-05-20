![GitHub Tests Action Status](https://github.com/Oltrematica/laravel-parking-hub/actions/workflows/run-tests.yml/badge.svg)
![GitHub PhpStan Action Status](https://github.com/Oltrematica/laravel-parking-hub/actions/workflows/phpstan.yml/badge.svg)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/oltrematica/laravel-parking-hub.svg?style=flat-square)](https://packagist.org/packages/oltrematica/laravel-parking-hub)
[![Total Downloads](https://img.shields.io/packagist/dt/oltrematica/laravel-parking-hub.svg?style=flat-square)](https://packagist.org/packages/oltrematica/laravel-parking-hub)

# Laravel ParkingHub

`laravel-parking-hub` is a Laravel package designed to create a unified and consistent way to interact with different
parking service APIs (e.g., Parkeon, My Cicero). It aims to standardize the input and output of parking validation
requests, making it easier to integrate and manage multiple parking providers.

## Key features:

- **Standardized Data Transfer Objects (DTOs)**: Defines DTOs for representing standard responses from parking service APIs, ensuring consistency across different providers.
- **Unified Interface**: Provides a common interface for executing parking validation requests (e.g., checking license plates) across different services.
- **Response Standardization**: Standardizes the response format, including status (success/error), parking validity, and expiration details.
- **Easy Integration**: Simplifies the integration of new parking service providers by providing a clear structure for data mapping and response handling.

## Prerequisites

- Laravel v10, v11 and v12
- PHP 8.3 or higher

## Installation

```bash
composer require oltrematica/laravel-parking-hub
```

# Usage Guide

## Code Quality

The project includes automated tests and tools for code quality control.

### Rector

Rector is a tool for automating code refactoring and migrations. It can be run using the following command:

```shell
composer refactor
```

### PhpStan

PhpStan is a tool for static analysis of PHP code. It can be run using the following command:

```shell
composer analyse
```

### Pint

Pint is a tool for formatting PHP code. It can be run using the following command:

```shell
composer format
```

### Automated Tests

The project includes automated tests and tools for code quality control.

```shell
composer test
```

## Contributing

Feel free to contribute to this package by submitting issues or pull requests. We welcome any improvements or bug fixes
you may have.






