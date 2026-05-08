<?php

namespace App\Models;

use RuntimeException;

class Term extends BaseModel
{
    public int $taxonomy_id;
    public string $slug;

    public function __construct(string $slug)
    {
        parent::__construct();

        $this->slug = $slug;

        $this->taxonomy_id = $this->getTaxonomyId($slug);

        if ($this->taxonomy_id === 0) {
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

    private function termExist(int $id): bool
    {
        $query = "SELECT id FROM terms WHERE id = :id";

        $stmt = $this->db->prepare($query);

        $stmt->execute([":id" => $id]);

        return !empty($stmt->fetchAll());
    }

    private function getMeta(int $id): array
    {
        $query = "SELECT id, meta_key, meta_value FROM `term_meta` WHERE term_id = :term_id";

        $stmt = $this->db->prepare($query);

        $stmt->execute([":term_id" => $id]);

        $result = $stmt->fetchAll();

        return $result;
    }

    private function insertMeta(int $term_id, array $meta): void
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

        $query .= " ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)";

        $stmt = $this->db->prepare($query);

        $stmt->execute($params);
    }

    private function deleteMeta(int $term_id): void
    {
        $query = "DELETE FROM term_meta WHERE term_id = :term_id";

        $stmt = $this->db->prepare($query);

        $stmt->execute([':term_id' => $term_id]);
    }

    public function get(): array
    {
        $query = "SELECT id, name, slug FROM `terms` WHERE taxonomy_id = :taxonomy_id";

        $stmt = $this->db->prepare($query);

        $stmt->execute([":taxonomy_id" => $this->taxonomy_id]);

        $result = $stmt->fetchAll();

        foreach ($result as $key => $term) {
            $term_meta = $this->getMeta($term['id']);

            $result[$key]['meta'] = $term_meta;
        }

        return $result;
    }

    public function create(string $term_title, string $term_slug, array $meta): void
    {
        $args = [
            ":taxonomy_id" => $this->taxonomy_id,
            ":term_title"  => $term_title,
            ":term_slug"   => $term_slug
        ];

        $query = "INSERT INTO terms (taxonomy_id, name, slug) VALUES (:taxonomy_id, :term_title, :term_slug)";

        $stmt = $this->db->prepare($query);

        if (empty($meta)) {
            $stmt->execute($args);
        } else {
            $this->db->beginTransaction();

            try {
                $stmt->execute($args);

                $term_id = $this->db->lastInsertId();

                $this->insertMeta($term_id, $meta);

                $this->db->commit();
            } catch (\Throwable $e) {
                $this->db->rollBack();
                throw $e;
            }
        }
    }

    public function update(string $term_title, string $term_slug, array $meta, int $term_id): void
    {
        $args = [
            ":term_title"  => $term_title,
            ":term_slug"   => $term_slug,
            ":term_id"     => $term_id
        ];

        $query = "UPDATE terms SET name = :term_title, slug = :term_slug WHERE id = :term_id";

        $stmt = $this->db->prepare($query);

        if (empty($meta)) {
            $stmt->execute($args);
        } else {
            $this->db->beginTransaction();

            try {
                $stmt->execute($args);

                $this->insertMeta($term_id, $meta);

                $this->db->commit();
            } catch (\Throwable $e) {
                $this->db->rollBack();
                throw $e;
            }
        }
    }

    public function delete(int $term_id): bool
    {
        $this->db->beginTransaction();

        try {
            $this->deleteMeta($term_id);

            $query = "DELETE FROM terms WHERE id = :term_id";

            $stmt = $this->db->prepare($query);

            $stmt->execute([':term_id' => $term_id]);

            $this->db->commit();

            return $stmt->rowCount();
        } catch (\Throwable $th) {
            $this->db->rollBack();
            throw $th;
        }
    }
}
