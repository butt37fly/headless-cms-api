<?php

namespace App\Models;

use PDO;
use App\Core\Database;

class Taxonomy
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        $query = "SELECT id, name, slug FROM taxonomies";

        $stmt = $this->db->prepare($query);

        $stmt->execute();

        $result = $stmt->fetchAll();

        return $result;
    }

    public function create(string $name, string $slug): void
    {
        $query = "INSERT INTO taxonomies (name, slug) VALUES (:name, :slug)";

        $stmt = $this->db->prepare($query);

        $stmt->execute(
            [
                ':name' => $name,
                ':slug' => $slug
            ]
        );
    }

    public function update(string $name, string $slug, string $reference): void
    {
        $query = "UPDATE taxonomies SET name = :name, slug = :slug WHERE slug = :reference";

        $stmt = $this->db->prepare($query);

        $stmt->execute(
            [
                ':name' => $name,
                ':slug' => $slug,
                ':reference' => $reference
            ]
        );
    }

    public function delete(string $slug): bool
    {
        $query = "DELETE FROM taxonomies WHERE slug = :slug";

        $stmt = $this->db->prepare($query);

        $stmt->execute([':slug' => $slug]);

        return $stmt->rowCount() > 0;
    }
}
