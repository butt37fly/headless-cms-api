<?php

namespace App\Models;

use PDO;
use App\Core\Database;
use RuntimeException;

class Taxonomy
{
    protected PDO $db;

    public int $id;
    public string $slug;

    public function __construct(string $slug)
    {
        $this->db = Database::getConnection();

        $this->slug = $slug;

        $this->id = $this->getTaxonomyId($slug);

        if ($this->id === 0) {
            throw new RuntimeException("La taxonomía '{$slug}' no existe.");
        }
    }

    private function getTaxonomyId(string $slug): int
    {
        $query = "SELECT id FROM `taxonomies` WHERE slug = :slug";

        $stmt = $this->db->prepare($query);

        $stmt->execute([":slug" => $slug]);

        $result = $stmt->fetch();

        return $result['id'] ?? 0;
    }

    private function getTerm(int|string $reference): array
    {
        $query = "SELECT * FROM terms where ";
        $query .= is_string($reference) ? "slug = :reference" : "id = :reference";

        $stmt = $this->db->prepare($query);

        $stmt->execute([":reference" => $reference]);

        $result = $stmt->fetch();

        return $result ?: [];
    }

    private function getTermMeta(int $id): array
    {
        $query = "SELECT id, meta_key, meta_value FROM `term_meta` WHERE term_id = :term_id";

        $stmt = $this->db->prepare($query);

        $stmt->execute([":term_id" => $id]);

        $result = $stmt->fetchAll();

        return $result;
    }

    public function getAll(): array
    {
        $query = "SELECT id, name, slug FROM `terms` WHERE taxonomy_id = :taxonomy_id";

        $stmt = $this->db->prepare($query);

        $stmt->execute([":taxonomy_id" => $this->id]);

        $result = $stmt->fetchAll();

        foreach ($result as $key => $term) {
            $term_meta = $this->getTermMeta($term['id']);

            $result[$key]['meta'] = $term_meta;
        }

        return $result;
    }

    private function insertTermMeta(int $term_id, array $meta): void
    {
        $params = [];

        $query = "INSERT INTO term_meta (term_id, meta_key, meta_value) VALUES ";

        for ($i = 0; $i < count($meta); $i++) {
            $query .= "(:term_id_$i, :meta_key_$i, :meta_value_$i), ";

            $params[":term_id_$i"] = $term_id;

            $params[":meta_key_$i"] = $meta[$i]['meta_key'];

            $params[":meta_value_$i"] = $meta[$i]['meta_value'];
        }

        $query = rtrim($query, ', ');

        $query .= " ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), meta_value = VALUES(meta_value)";

        $stmt = $this->db->prepare($query);

        $stmt->execute($params);
    }

    public function upsertTerm(string $term_title, string $term_slug, array $meta): void
    {
        $query = "INSERT INTO terms (taxonomy_id, name, slug) VALUES (:taxonomy_id, :term_title, :term_slug) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), name = VALUES(name)";

        $stmt = $this->db->prepare($query);

        $this->db->beginTransaction();

        try {
            $stmt->execute([
                ":taxonomy_id" => $this->id,
                ":term_title"  => $term_title,
                ":term_slug"   => $term_slug
            ]);

            $term_id = $this->db->lastInsertId();

            $this->insertTermMeta($term_id, $meta);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function deleteTerms(int $term_id): void
    {
        $query = "DELETE FROM term_meta WHERE term_id = :term_id";

        $stmt = $this->db->prepare($query);

        $stmt->execute([':term_id' => $term_id]);
    }

    public function delete(int $term_id): void
    {
        $this->db->beginTransaction();

        try {
            $this->deleteTerms($term_id);

            $query = "DELETE FROM terms WHERE id = :term_id";

            $stmt = $this->db->prepare($query);

            $stmt->execute([':term_id' => $term_id]);

            $this->db->commit();
        } catch (\Throwable $th) {
            $this->db->rollBack();
            throw $th;
        }
    }
}
