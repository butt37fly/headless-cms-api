<?php

namespace App\Controllers;

use App\Core\Response;
use App\Controllers\BaseController;

use App\Models\Taxonomy;

class TaxonomyController extends BaseController
{
    private Taxonomy $taxonomy;
    private array $args;

    public function __construct(array $args)
    {
        $this->taxonomy = new Taxonomy();

        if (!empty($args)) {
            $this->args['slug'] = $args[0];
        }
    }

    public function get(): Response
    {
        try {
            $result = $this->taxonomy->getAll();
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

        try {
            $this->taxonomy->create($title, $slug);
            return $this->success(['message' => "La taxonomía '{$slug}' se ha creado correctamente."], 201);
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
        $reference = $this->args['slug'];

        try {
            $this->taxonomy->update($title, $slug, $reference);
            return $this->success(['message' => "La taxonomía '{$reference}' se ha actualizado correctamente."], 201);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->error($e->getMessage(), 409);
        }
    }

    public function delete(): Response
    {
        $slug = $this->args['slug'];

        if (!isset($slug)) {
            return $this->error("El slug de la taxonomía es requerido.", 412);
        }

        try {
            $result = $this->taxonomy->delete($slug);
            if ($result) {
                return $this->success(["message" => "La taxonomía se ha eliminado correctamente."]);
            } else {
                return $this->success(["message" => "No se ha encontrado la taxonomía '{$slug}'."]);
            }
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->error("No se ha podido eliminar la taxonomía.", 500);
        }
    }
}
