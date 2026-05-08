<?php

namespace App\Models;

class Taxonomy extends BaseModel
{
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
        if ($this->itemExist('taxonomies', $slug, $name)) {
            throw new \RuntimeException("El nombre o el slug están duplicados.");
        }

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
        if (!$this->itemExist('taxonomies', $reference)) {
            throw new \RuntimeException("La taxonomía indicada no existe.");
        }

        if ($this->itemExist('taxonomies', $slug, $name)) {
            throw new \RuntimeException("Ya existe una taxonomía con el mismo nombre o slug");
        }

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
