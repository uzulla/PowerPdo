<?php
require_once 'vendor/autoload.php';

use PowerPdo\Core\PDOLogger;
use PowerPdo\Logging\FileLogger;
use PowerPdo\QueryProcessor\FilterableQueryProcessor;

// Create a logger that writes to a file
$logger = new FileLogger(__DIR__ . '/sql.log');

// Create a query processor that adds comments to queries
$processor = new FilterableQueryProcessor();
$processor->addFilter(function(string $query): string {
    return "/* Example Query */ $query";
});

// Create PDO instance with logger and processor
$pdo = new PDOLogger('sqlite::memory:', null, null, null, $logger, $processor);

// Create a table
$pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, email TEXT)');

// Insert data
$stmt = $pdo->prepare('INSERT INTO users (name, email) VALUES (:name, :email)');
$stmt->execute(['name' => 'John Doe', 'email' => 'john@example.com']);
$stmt->execute(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

// Query data
$result = $pdo->query('SELECT * FROM users')->fetchAll(PDO::FETCH_ASSOC);

// Display results
echo "Users in database:\n";
foreach ($result as $row) {
    echo "- {$row['name']} ({$row['email']})\n";
}

echo "\nSQL log file contents:\n";
echo file_get_contents(__DIR__ . '/sql.log');
