# Version Support Guide

This guide provides information on how to ensure your application works correctly with PowerPdo across different PHP versions.

## PHP Version Requirements

| PHP Version | Status | Notes |
|-------------|--------|-------|
| PHP 7.4     | ✅ Supported | Requires careful type handling |
| PHP 8.0     | ✅ Supported | Full support |
| PHP 8.1     | ✅ Supported | Full support with attributes |
| PHP 8.2     | ✅ Supported | Full support with attributes |
| PHP 8.3     | ✅ Supported | Full support with attributes |
| < PHP 7.4   | ❌ Not Supported | Type system and PDO features not compatible |

## Required Extensions

For all supported PHP versions, the following extensions are required:

- PDO
- pdo_sqlite (for SQLite support)
- json (for logging functionality)

## Installation Guide

### Installing for PHP 7.4

```bash
composer require php-pdo-logger/php-pdo-logger
```

Ensure your `composer.json` includes:

```json
"require": {
    "php": "^7.4 || ^8.0",
    "ext-pdo": "*",
    "ext-json": "*"
}
```

### Installing for PHP 8.0+

Same as PHP 7.4, no special considerations needed.

## Usage Differences

The library API is consistent across all supported PHP versions. However, there are some internal differences to be aware of:

### Type Declarations

When extending this library, be mindful of type declarations:

- For PHP 7.4 compatibility, avoid union types in method signatures
- Use PHPDoc annotations for documenting complex types
- For PHP 8.1+, use the `#[\ReturnTypeWillChange]` attribute when overriding PDO methods

### Error Handling

Error handling is consistent across versions, with all errors thrown as PDOException.

## Testing Your Application

To ensure your application works correctly with this library across PHP versions:

1. Set up a test environment with multiple PHP versions
2. Run your test suite on each version
3. Check for deprecation warnings or errors
4. Verify that logging and query processing work as expected

## Upgrading PHP Versions

When upgrading PHP versions while using this library:

1. No changes to your code should be necessary
2. The library will automatically adapt to the PHP version
3. Test thoroughly after upgrading to ensure compatibility
