<?php

namespace App\Controllers;

use App\Core\Response;
use App\Controllers\BaseController;

use App\Models\Taxonomy;

class TaxonomyController extends BaseController
{
    private Taxonomy $taxonomy;
    private int $term_id;

    public function __construct(array $params)
    {
        $this->taxonomy = new Taxonomy($params[0]);

        if (isset($params[1])) {
            $this->term_id = $params[1];
        }
    }

    private function parseMetadata(array $data): array
    {
        $result = [];

        foreach ($data as $obj) {
            foreach ($obj as $meta_key => $meta_value) {
                $meta_key = $this->getSlug($meta_key);
                $meta_value = trim($meta_value);

                $result[] = ['meta_key' => $meta_key, 'meta_value' => $meta_value];
            }
        }

        return $result;
    }

    public function getAll(): Response
    {
        $result = $this->taxonomy->getAll();
        return $this->success($result);
    }

    public function create(array $data): Response
    {
        if (empty($data['title'])) {
            return $this->error('El título es requerido.', 422);
        }

        $title = trim($data['title']);
        $slug = $this->getSlug($title);

        $meta = [];

        if (isset($data['meta'])) {
            $meta = json_decode($data['meta'], true);
            $meta = $this->parseMetadata($meta);
        }

        try {
            $this->taxonomy->upsertTerm($title, $slug, $meta);
            return $this->success(['message' => "El término '{$title}' se ha guardado correctamente."]);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->error("No se ha podido guardar el término '{$title}'.", 500);
        }
    }

    public function update(): Response
    {
        return $this->error("Pendiente por implementar");
    }

    public function delete(): Response
    {
        if (!isset($this->term_id) && $this->term_id === 0) {
            return $this->error("No se ha especificado el id a eliminar.", 422);
        }

        try {
            $this->taxonomy->delete($this->term_id);
            return $this->success(["message" => "El término se ha eliminado correctamente."]);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->error("No se ha podido eliminar el término.", 500);
        }
    }
}
