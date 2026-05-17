<?php

namespace App\Controllers;

use App\Core\Response;
use App\Controllers\BaseController;

use App\Models\Term;
use RuntimeException;

class TermController extends BaseController
{
    private Term $term;
    private string $taxonomy_slug;
    private int $term_id;

    public function __construct(array $args)
    {
        if (empty($args)) {
            throw new RuntimeException("No se ha especificado la taxonomía a inicializar.");
        }

        $this->taxonomy_slug = $args[0];
        $this->term = new Term($this->taxonomy_slug);

        $this->term_id = $args[1] ?? 0;
    }

    private function parseMetadata(array $data): array
    {
        $result = [];

        foreach ($data as $meta_key => $meta_value) {
            $meta_key = $this->getSlug($meta_key);
            $meta_value = trim($meta_value);

            $result[] = ['meta_key' => $meta_key, 'meta_value' => $meta_value];
        }

        return $result;
    }

    public function get(): Response
    {
        try {
            $result = $this->term->get();
            return $this->success($result);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->error("No se ha podido obtener la información.");
        }
    }

    public function create(array $data): Response
    {
        if (empty($data['title'])) {
            return $this->error('El título es requerido.', 412);
        }

        $title = trim($data['title']);
        $slug = $this->getSlug($title);

        $meta = [];

        if (isset($data['meta'])) {
            $meta = $this->parseMetadata($data['meta']);
        }

        try {
            $this->term->create($title, $slug, $meta);
            return $this->success(['message' => "El término '{$title}' se ha creado correctamente."]);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->error($e->getMessage(), 409);
        }
    }

    public function update(array $data): Response
    {
        if (empty($data['title'])) {
            return $this->error('El título es requerido.', 412);
        }

        $title = trim($data['title']);
        $slug = $this->getSlug($title);

        $meta = [];

        if (isset($data['meta'])) {
            $meta = $this->parseMetadata($data['meta']);
        }

        try {
            $this->term->update($title, $slug, $meta, $this->term_id);
            return $this->success(['message' => "El término '{$title}' se ha actualizado correctamente."]);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->error($e->getMessage(), 409);
        }
    }

    public function delete(): Response
    {
        if (!isset($this->term_id) && $this->term_id === 0) {
            return $this->error("No se ha especificado el id a eliminar.", 412);
        }

        try {
            $result = $this->term->delete($this->term_id);

            if ($result) {
                return $this->success(["message" => "El término se ha eliminado correctamente."]);
            } else {
                return $this->success(["message" => "El término no existe."]);
            }
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->error("No se ha podido eliminar el término.", 500);
        }
    }
}
