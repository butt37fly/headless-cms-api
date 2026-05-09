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
        $args = [];

        if (is_array($reference)) {
            $query = "SELECT id FROM {$table} WHERE {$reference[0]} = :reference";
            $args[":reference"] = $reference[1];
        } else {
            $query = "SELECT id FROM {$table} WHERE slug = :reference";
            $args[":reference"] = $reference;
        }

        if (!empty($name)) {
            $query .= " OR name = :name";
            $args[":name"] = $name;
        }

        $stmt = $this->db->prepare($query);

        $stmt->execute($args);

        return !empty($stmt->fetchAll());
    }
}
