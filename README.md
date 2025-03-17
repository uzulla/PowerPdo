# PowerPdo

A PHP library that extends PDO to provide comprehensive SQL query logging with debug information, supporting PHP versions 7.4-8.3.

## Features

- Extends PDO with full compatibility
- Logs all database operations with detailed context
- Captures debug backtrace to show where queries are executed
- Processes and modifies queries before execution
- Modular design with separate components
- Compatible with PHP 7.4-8.3
- Comprehensive test suite with SQLite

## Installation

```bash
composer require php-pdo-logger/php-pdo-logger
```

## Requirements

- PHP 7.4 or higher
- PDO extension
- SQLite extension (for running tests)

## Basic Usage

```php
<?php
require_once 'vendor/autoload.php';

use PowerPdo\Core\PDOLogger;
use PowerPdo\Logging\FileLogger;

// Create a logger that writes to a file
$logger = new FileLogger('/path/to/sql.log');

// Create PDO instance with logger
$pdo = new PDOLogger('sqlite::memory:', null, null, null, $logger);

// Use like a normal PDO instance
$pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)');

$stmt = $pdo->prepare('INSERT INTO users (name) VALUES (:name)');
$stmt->execute(['name' => 'John Doe']);

$users = $pdo->query('SELECT * FROM users')->fetchAll(PDO::FETCH_ASSOC);
```

## Advanced Usage: Query Processing

```php
<?php
use PowerPdo\Core\PDOLogger;
use PowerPdo\Logging\FileLogger;
use PowerPdo\QueryProcessor\FilterableQueryProcessor;

// Create a query processor that adds comments to queries
$processor = new FilterableQueryProcessor();
$processor->addFilter(function(string $query): string {
    return "/* Application: MyApp */ $query";
});

// Add another filter
$processor->addFilter(function(string $query): string {
    return "$query /* Timestamp: " . time() . " */";
});

// Create PDO instance with logger and processor
$pdo = new PDOLogger(
    'sqlite::memory:',
    null,
    null,
    null,
    new FileLogger('/path/to/sql.log'),
    $processor
);

// The query will be processed by all filters before execution
$stmt = $pdo->prepare('SELECT * FROM users');
```

## Log Format

The logs are written in JSON format with the following structure:

```json
{
    "timestamp": "2025-03-17 05:50:52",
    "action": "prepare",
    "caller": "/path/to/file.php:24",
    "data": {
        "query": "SELECT * FROM users WHERE id = :id",
        "options": [],
        "trace": [
            {
                "file": "/path/to/file.php",
                "line": 24,
                "function": "prepare",
                "class": "PowerPdo\\Core\\PDOLogger",
                "type": "->"
            }
        ]
    }
}
```

## Available Loggers

- `FileLogger`: Logs to a file in JSON format
- `NullLogger`: Disables logging (useful for production or testing)

## Available Query Processors

- `DefaultQueryProcessor`: Passes queries through unchanged
- `FilterableQueryProcessor`: Allows adding multiple filters to modify queries

## Version Compatibility

For detailed information about compatibility with different PHP versions, see [COMPATIBILITY.md](COMPATIBILITY.md) and [VERSION_SUPPORT.md](VERSION_SUPPORT.md).

## License

MIT
