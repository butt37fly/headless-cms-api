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

    private function getPostTerms(int $post_id): array
    {
        $query = "
            SELECT pt.term_id, t.name as term_name, t.slug as term_slug, t.taxonomy_id, tt.name as taxonomy_name, tt.slug as taxonomy_slug, tm.id as term_meta_id, tm.meta_key, tm.meta_value
            FROM `post_terms` as pt
            INNER JOIN terms as t
            ON t.id = pt.term_id
            INNER JOIN taxonomies as tt
            ON tt.id = t.taxonomy_id
            LEFT JOIN term_meta as tm
            ON t.id = tm.term_id
            WHERE post_id = :post_id";

        $stmt = $this->db->prepare($query);

        $stmt->execute([':post_id' => $post_id]);

        $result = $stmt->fetchAll();

        $taxonomies = [];

        foreach ($result as $row) {
            $taxonomy_slug = $row['taxonomy_slug'];
            $term_id = $row['term_id'];

            if (!isset($taxonomies[$taxonomy_slug])) {
                $taxonomies[$taxonomy_slug] = [
                    'taxonomy_id'   => $row['taxonomy_id'],
                    'taxonomy_name' => $row['taxonomy_name'],
                    'taxonomy_slug' => $taxonomy_slug,
                    'terms'         => []
                ];
            }

            if (!isset($taxonomies[$taxonomy_slug]['terms'][$term_id])) {
                $taxonomies[$taxonomy_slug]['terms'][$term_id] = [
                    'term_id'   => $term_id,
                    'term_name' => $row['term_name'],
                    'term_slug' => $row['term_slug'],
                    'meta'      => []
                ];
            }

            if ($row['meta_key'] !== null) {
                $taxonomies[$taxonomy_slug]['terms'][$term_id]['meta'][] = [
                    'meta_key'   => $row['meta_key'],
                    'meta_value' => $row['meta_value']
                ];
            }
        }

        foreach ($taxonomies as &$taxonomy) {
            $taxonomy['terms'] = array_values($taxonomy['terms']);
        }

        return [$taxonomies];
    }

    public function getAll(): array
    {
        $query = "SELECT id, title, slug, content, excerpt, published_at FROM posts";

        $stmt = $this->db->prepare($query);

        $stmt->execute();

        $posts = $stmt->fetchAll();

        foreach ($posts as $key => $post) {
            $id = $post['id'];
            $terms = $this->getPostTerms($id);
            $posts[$key]['terms'] = [$terms];
        }

        return $posts;
    }

    public function get(int $post_id): array
    {
        $query = "SELECT id, title, slug, content, excerpt, published_at FROM posts WHERE id = :post_id";

        $stmt = $this->db->prepare($query);

        $stmt->execute([':post_id' => $post_id]);

        $post = $stmt->fetch();

        if (!$post) {
            throw new \InvalidArgumentException("No se encontró el post con el id indicado");
        }

        $id = $post['id'];
        $terms = $this->getPostTerms($id);

        $post['terms'] = [$terms];

        return $post;
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

    public function update(array $data): array
    {
        [
            'id'      => $post_id,
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

        $query = "  UPDATE posts 
                    SET title = :title, slug = :slug, content = :content, excerpt = :excerpt, status = :published, published_at = NOW()
                    WHERE id = :post_id";

        $stmt = $this->db->prepare($query);

        $this->db->beginTransaction();

        try {
            $stmt->execute([
                ":title"     => $title,
                ":slug"      => $slug,
                ":content"   => $content,
                ":excerpt"   => $excerpt,
                ":published" => $status,
                ":post_id"   => $post_id,
            ]);

            $this->insertTerms($post_id, $terms);

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        return [];
    }

    public function delete(int $int = 01): array
    {
        return [];
    }
}
