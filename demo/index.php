<?php
/**
 * PowerPdo Demo
 * 
 * This demo shows how to use the PowerPdo library to log SQL queries
 * with debug information, using a clear separation between application and library code.
 * 
 * This version demonstrates the FileLocationQueryProcessor which automatically adds
 * file location comments to SQL queries.
 */

// Autoload classes
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/src/DemoProductRepository.php';

use Demo\DemoProductRepository;
use PowerPdo\Logging\FileLogger;
use PowerPdo\QueryProcessor\FileLocationQueryProcessor;

// Create a log file
$logFile = __DIR__ . '/demo.log';
if (file_exists($logFile)) {
    unlink($logFile);
}

echo "PowerPdo Demo with File Location Comments\n";
echo "=============================================\n\n";

// Step 1: Create a logger and query processor
echo "Step 1: Creating logger and file location query processor\n";
$logger = new FileLogger($logFile);
$processor = new FileLocationQueryProcessor();

// Step 2: Create our demo application repository that uses PDOLogger internally
echo "Step 2: Creating demo application repository\n";
$productRepo = new DemoProductRepository(
    'sqlite:' . __DIR__ . '/demo.sqlite', 
    null, 
    null, 
    null, 
    $logger, 
    $processor
);

// Step 3: Add products using our application code
echo "Step 3: Adding products using application repository\n";
$products = [
    ['name' => 'Laptop', 'price' => 999.99, 'description' => 'High-performance laptop'],
    ['name' => 'Smartphone', 'price' => 499.99, 'description' => 'Latest smartphone model'],
    ['name' => 'Headphones', 'price' => 149.99, 'description' => 'Noise-cancelling headphones']
];

foreach ($products as $product) {
    $productRepo->addProduct($product['name'], $product['price'], $product['description']);
    echo "  - Added product: {$product['name']}\n";
}

// Step 4: Query products
echo "Step 4: Querying products\n";
$allProducts = $productRepo->getAllProducts();

echo "Products (ordered by price):\n";
foreach ($allProducts as $product) {
    echo "  - {$product['name']}: ¥{$product['price']}\n";
}

// Step 5: Update product
echo "\nStep 5: Updating product price\n";
$productRepo->updateProductPrice('Headphones', 129.99);
echo "  - Updated Headphones price to ¥129.99\n";

// Step 6: Delete product
echo "\nStep 6: Deleting product\n";
$productRepo->deleteProduct('Smartphone');
echo "  - Deleted Smartphone\n";

// Step 7: Final query to show results
echo "\nStep 7: Final query to show results\n";
$finalProducts = $productRepo->getAllProducts();

echo "Remaining products:\n";
foreach ($finalProducts as $product) {
    echo "  - {$product['name']}: ¥{$product['price']}\n";
}

// Step 8: Show log file contents
echo "\nStep 8: Log file contents (showing file location comments in queries)\n";
echo "Log file: $logFile\n";
echo "Contents (first few entries):\n";
echo "----------------------------------------\n";
$logContent = file_get_contents($logFile);
$logEntries = explode("---\n", $logContent);
// Show just the first few entries to highlight the file location comments
for ($i = 0; $i < min(3, count($logEntries)); $i++) {
    echo $logEntries[$i] . "\n";
    if ($i < min(2, count($logEntries) - 1)) {
        echo "---\n";
    }
}
echo "----------------------------------------\n";

echo "\nDemo completed successfully!\n";
