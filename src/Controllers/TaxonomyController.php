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

        if (empty($data['meta'])) {
            return $this->error('Los atributos del término son requeridos.', 422);
        }

        $title = trim($data['title']);
        $slug = $this->getSlug($title);

        $meta = json_decode($data['meta'], true);
        $meta = $this->parseMetadata($meta);

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
        return Response::error("Pendiente por implementar");
    }

    public function delete(): Response
    {
        return Response::error("Pendiente por implementar");
    }
}
