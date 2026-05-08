<?php

namespace App\Models;

use PDO;
use App\Core\Database;

class BaseModel
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    protected function itemExist(string $table, string|array $reference, string $name = ""): bool
    {
        $args = [
            ":name"  => $name
        ];

        if (is_array($reference)) {
            $query = "SELECT id FROM {$table} WHERE {$reference[0]} = :reference OR name = :name";
            $args[":reference"] = $reference[1];
        } else {
            $query = "SELECT id FROM {$table} WHERE slug = :reference OR name = :name";
            $args[":reference"] = $reference;
        }

        $stmt = $this->db->prepare($query);

        $stmt->execute($args);

        return !empty($stmt->fetchAll());
    }
}
