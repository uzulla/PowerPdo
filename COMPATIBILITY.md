# PowerPdo Compatibility

This document outlines compatibility information for the PowerPdo library across supported PHP versions.

## Supported PHP Versions

The library has been tested and verified to work on the following PHP versions:

- PHP 7.4
- PHP 8.0
- PHP 8.1
- PHP 8.2
- PHP 8.3

## Version-Specific Considerations

### PHP 7.4
- Union types (`|`) in method signatures are not supported, so we use PHPDoc annotations instead
- Return type declarations are compatible through careful method signature design
- The `#[\ReturnTypeWillChange]` attribute is not available, but not needed in this version

### PHP 8.0+
- Full support for all features
- No special considerations needed

### PHP 8.1+
- Uses `#[\ReturnTypeWillChange]` attribute to prevent deprecation warnings
- Fully compatible with PDO's method signatures

## Implementation Details

### Type Declarations
To maintain compatibility across all supported PHP versions, the library:

1. Uses PHPDoc annotations for documenting return types
2. Avoids union types in method signatures for PHP 7.4 compatibility
3. Uses the `#[\ReturnTypeWillChange]` attribute in PHP 8.1+ for method overrides

### PDO Statement Class Extension
The PDOStatementLogger class extends PDOStatement with special considerations:
- Uses protected constructor as required by PDO
- Initializes via a separate `init()` method after PDO creates the statement
- Maintains parameter compatibility with parent methods

### Debug Backtrace
The debug backtrace functionality works consistently across all PHP versions with minor differences:
- Function name reporting may vary slightly between PHP 7.4 and newer versions
- Tests are designed to accommodate these differences

## Testing
All unit and integration tests pass on all supported PHP versions. The test suite includes:
- Core PDO wrapper functionality
- PDOStatement extension
- Query processing
- SQL logging
- Debug backtrace capture
- Error handling

## SQLite Support
SQLite is fully supported across all PHP versions, requiring the php-sqlite3 extension.
