<?php
/**
 * PowerPdo Comprehensive Demo
 * 
 * This demo shows all PDO methods (exec, prepare, query) with file location comments
 */

// Autoload classes
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/src/DemoProductRepository.php';

use PowerPdo\Core\PDOLogger;
use PowerPdo\Logging\FileLogger;
use PowerPdo\QueryProcessor\FileLocationQueryProcessor;

// Create a log file
$logFile = __DIR__ . '/comprehensive_demo.log';
if (file_exists($logFile)) {
    unlink($logFile);
}

echo "PowerPdo Comprehensive Demo\n";
echo "================================\n\n";

// Create logger and query processor
echo "Step 1: Creating logger and file location query processor\n";
$logger = new FileLogger($logFile);
$processor = new FileLocationQueryProcessor();

// Create PDO instance directly (without repository)
echo "Step 2: Creating PDO instance with logger\n";
$pdo = new PDOLogger(
    'sqlite::memory:', 
    null, 
    null, 
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION], 
    $logger, 
    $processor
);

// EXAMPLE 1: Using exec() method
echo "\nEXAMPLE 1: Using exec() method\n";
echo "------------------------------\n";
$pdo->exec('CREATE TABLE products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    price REAL NOT NULL,
    description TEXT
)');
echo "Table created using exec()\n";

// EXAMPLE 2: Using prepare() and execute() methods
echo "\nEXAMPLE 2: Using prepare() and execute() methods\n";
echo "---------------------------------------------\n";
$stmt = $pdo->prepare('INSERT INTO products (name, price, description) VALUES (:name, :price, :description)');
$stmt->execute([
    'name' => 'Laptop',
    'price' => 999.99,
    'description' => 'High-performance laptop'
]);
echo "Product inserted using prepare() and execute()\n";

// EXAMPLE 3: Using query() method
echo "\nEXAMPLE 3: Using query() method\n";
echo "-----------------------------\n";
$result = $pdo->query('SELECT * FROM products');
$products = $result->fetchAll(PDO::FETCH_ASSOC);
echo "Products retrieved using query():\n";
foreach ($products as $product) {
    echo "  - {$product['name']}: ¥{$product['price']}\n";
}

// EXAMPLE 4: Using bindValue() with prepared statements
echo "\nEXAMPLE 4: Using bindValue() with prepared statements\n";
echo "------------------------------------------------\n";
$stmt = $pdo->prepare('INSERT INTO products (name, price, description) VALUES (:name, :price, :description)');
$stmt->bindValue(':name', 'Smartphone', PDO::PARAM_STR);
$stmt->bindValue(':price', 499.99, PDO::PARAM_STR);
$stmt->bindValue(':description', 'Latest smartphone model', PDO::PARAM_STR);
$stmt->execute();
echo "Product inserted using bindValue()\n";

// EXAMPLE 5: Using bindParam() with prepared statements
echo "\nEXAMPLE 5: Using bindParam() with prepared statements\n";
echo "------------------------------------------------\n";
$stmt = $pdo->prepare('INSERT INTO products (name, price, description) VALUES (:name, :price, :description)');
$name = 'Headphones';
$price = 149.99;
$description = 'Noise-cancelling headphones';
$stmt->bindParam(':name', $name, PDO::PARAM_STR);
$stmt->bindParam(':price', $price, PDO::PARAM_STR);
$stmt->bindParam(':description', $description, PDO::PARAM_STR);
$stmt->execute();
echo "Product inserted using bindParam()\n";

// EXAMPLE 6: Using transactions
echo "\nEXAMPLE 6: Using transactions\n";
echo "--------------------------\n";
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('INSERT INTO products (name, price, description) VALUES (:name, :price, :description)');
    $stmt->execute([
        'name' => 'Tablet',
        'price' => 349.99,
        'description' => 'Portable tablet'
    ]);
    
    $stmt = $pdo->prepare('UPDATE products SET price = :price WHERE name = :name');
    $stmt->execute([
        'price' => 129.99,
        'name' => 'Headphones'
    ]);
    
    $pdo->commit();
    echo "Transaction committed successfully\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Transaction rolled back: " . $e->getMessage() . "\n";
}

// EXAMPLE 7: Using query with fetchColumn
echo "\nEXAMPLE 7: Using query with fetchColumn\n";
echo "------------------------------------\n";
$count = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
echo "Total products: $count\n";

// EXAMPLE 8: Using query with different fetch modes
echo "\nEXAMPLE 8: Using query with different fetch modes\n";
echo "--------------------------------------------\n";
$stmt = $pdo->query('SELECT * FROM products ORDER BY price DESC');
echo "Products (ordered by price):\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  - {$row['name']}: ¥{$row['price']}\n";
}

// Show log file contents
echo "\nLog file contents (showing file location comments for different PDO methods)\n";
echo "------------------------------------------------------------------------\n";
$logContent = file_get_contents($logFile);
$logEntries = explode("---\n", $logContent);

// Show examples of different PDO methods in the log
$examples = [
    'exec' => null,
    'prepare' => null,
    'execute' => null,
    'query' => null,
    'bind_value' => null,
    'bind_param' => null,
    'beginTransaction' => null
];

foreach ($logEntries as $entry) {
    $entry = trim($entry);
    if (empty($entry)) continue;
    
    $data = json_decode($entry, true);
    if (!$data) continue;
    
    $action = $data['action'] ?? '';
    
    // Store the first occurrence of each action type
    if (array_key_exists($action, $examples) && $examples[$action] === null) {
        $examples[$action] = $entry;
    }
}

// Display examples of each PDO method
foreach ($examples as $method => $example) {
    if ($example !== null) {
        echo "\n## Example of '$method' method:\n";
        echo $example . "\n";
        echo "---\n";
    }
}

echo "\nComprehensive demo completed successfully!\n";
