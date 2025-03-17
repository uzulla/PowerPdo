<?php

namespace Demo;

use PDO;
use PowerPdo\Core\PDOLogger;
use PowerPdo\Logging\LoggerInterface;
use PowerPdo\QueryProcessor\QueryProcessorInterface;

/**
 * Demo application class that uses the PDO Logger
 */
class DemoProductRepository
{
    private PDO $pdo;

    public function __construct(
        string $dsn,
        ?string $username = null,
        ?string $password = null,
        ?array $options = null,
        ?LoggerInterface $logger = null,
        ?QueryProcessorInterface $queryProcessor = null
    ) {
        // Create a PDOLogger instance (which extends PDO)
        $this->pdo = new PDOLogger($dsn, $username, $password, $options, $logger, $queryProcessor);
        
        // Initialize the database
        $this->initDatabase();
    }

    private function initDatabase(): void
    {
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            price REAL NOT NULL,
            description TEXT
        )');
    }

    public function addProduct(string $name, float $price, string $description): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO products (name, price, description) VALUES (:name, :price, :description)');
        $stmt->execute([
            'name' => $name,
            'price' => $price,
            'description' => $description
        ]);
        
        return (int) $this->pdo->lastInsertId();
    }

    public function getAllProducts(): array
    {
        return $this->pdo->query('SELECT * FROM products ORDER BY price DESC')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateProductPrice(string $name, float $price): bool
    {
        $stmt = $this->pdo->prepare('UPDATE products SET price = :price WHERE name = :name');
        return $stmt->execute([
            'price' => $price,
            'name' => $name
        ]);
    }

    public function deleteProduct(string $name): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM products WHERE name = :name');
        return $stmt->execute(['name' => $name]);
    }
}
