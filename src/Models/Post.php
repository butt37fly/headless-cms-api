<?php

namespace App\Models;

class Post extends BaseModel
{
    private function getValidTerms(array $terms): array
    {
        $placeholders = [];
        $params = [];

        for ($i = 0; $i < count($terms); $i++) {
            $placeholders[] = ":id_{$i}";
            $params[":id_{$i}"] = $terms[$i];
        }

        $query = "SELECT id FROM terms WHERE id IN (" . implode(", ", $placeholders) . ")";

        $stmt = $this->db->prepare($query);

        $stmt->execute($params);

        $result = $stmt->fetchAll();

        return $result;
    }

    private function insertTerms(int $post_id, array $terms): void
    {
        $params = [];

        $valid_terms = $this->getValidTerms($terms);

        $query = "INSERT INTO post_terms (post_id, term_id) VALUES ";

        foreach ($valid_terms as $i => $term) {
            $query .= "(:post_id_{$i}, :term_id_{$i}), ";

            $params[":term_id_{$i}"] = $term['id'];
            $params[":post_id_{$i}"] = $post_id;
        }

        $query = rtrim($query, ', ');

        $query .= " ON DUPLICATE KEY UPDATE term_id = VALUES(term_id)";

        $stmt = $this->db->prepare($query);

        $stmt->execute($params);
    }

    public function getAll(): array
    {
        return [];
    }

    public function get(int $int = 01): array
    {
        return [];
    }

    public function create(array $data): array
    {
        [
            'title'   => $title,
            'slug'    => $slug,
            'content' => $content,
            'excerpt' => $excerpt,
            'terms'   => $terms
        ] = $data;

        $status = 'published';

        if ($this->itemExist('posts', $slug)) {
            throw new \InvalidArgumentException("El slug está duplicado");
        }

        $query = "INSERT INTO posts (title, slug, content, excerpt, status, published_at) VALUES (:title, :slug, :content, :excerpt, :published, NOW())";

        $stmt = $this->db->prepare($query);

        $this->db->beginTransaction();

        try {
            $stmt->execute([
                ":title"   => $title,
                ":slug"    => $slug,
                ":content" => $content,
                ":excerpt" => $excerpt,
                ":published" => $status,
            ]);

            $post_id = $this->db->lastInsertId();

            $this->insertTerms($post_id, $terms);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        return [];
    }

    public function update(int $int = 01): array
    {
        return [];
    }

    public function delete(int $int = 01): array
    {
        return [];
    }
}
