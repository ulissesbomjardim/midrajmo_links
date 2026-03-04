<?php

namespace App\Repositories;

use PDO;

class CategoryRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function listAll()
    {
        $stmt = $this->pdo->query('SELECT id, name, created_at FROM categories ORDER BY name ASC');
        return $stmt->fetchAll();
    }

    public function create($name)
    {
        $stmt = $this->pdo->prepare('INSERT INTO categories (name) VALUES (:name)');
        $stmt->execute(['name' => $name]);

        return [
            'id' => (int) $this->pdo->lastInsertId(),
            'name' => $name,
        ];
    }
}
