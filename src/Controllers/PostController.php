<?php

namespace App\Controllers;

use App\Core\Response;
use App\Controllers\BaseController;

use App\Models\Post;

class PostController extends BaseController
{
    private Post $post;

    public function __construct()
    {
        $this->post = new Post;
    }

    public function getAll(): Response
    {
        return $this->error("Pendiente por implementar");
    }

    public function get(): Response
    {
        return $this->error("Pendiente por implementar");
    }

    public function create(array $data): Response
    {
        if (!isset($data['title']) || empty($data['title'])) {
            return $this->error("El campo 'title' es requerido.", 412);
        }

        if (!isset($data['content']) || empty($data['content'])) {
            return $this->error("El campo 'content' es requerido.", 412);
        }

        $args = [];

        $args['title'] = $this->sanitizeText($data['title']);
        $args['content'] = trim($data['content']);
        $args['slug'] = $this->getSlug($args['title']);

        $args['excerpt'] = "";
        $args['terms'] = [];

        if (strlen($args['title']) > 50) {
            return $this->error("El campo 'title' es demasiado largo.", 412);
        }

        if (strlen($args['content']) > 1500) {
            return $this->error("El campo 'contenido' es demasiado largo.", 412);
        }

        if (isset($data['excerpt']) && !empty($data['excerpt'])) {
            $args['excerpt'] = $this->sanitizeText($data['excerpt']);
        }

        if (isset($data['terms']) && count($data['terms']) > 0) {
            $args['terms'] = array_map('intval', $data['terms']);
            $args['terms'] = array_filter($args['terms'], fn($id) => $id > 0);
        }

        try {
            $this->post->create($args);
            return $this->success(["message" => "'{$args['title']}' se ha creado correctamente."]);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->error($e->getMessage(), 409);
        }
    }

    public function update(): Response
    {
        return $this->error("Pendiente por implementar");
    }

    public function delete(): Response
    {
        return $this->error("Pendiente por implementar");
    }
}
