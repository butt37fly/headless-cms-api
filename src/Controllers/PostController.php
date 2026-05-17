<?php

namespace App\Controllers;

use App\Core\Response;
use App\Controllers\BaseController;

use App\Models\Post;

class PostController extends BaseController
{
    private Post $post;
    private int $post_id;

    public function __construct(array $args)
    {
        $this->post = new Post;

        if (!empty($args)) {
            $this->post_id = $args[0];
        }
    }

    private function parseFieldsToUpdate(array $post, array $new_post): array
    {
        $to_update = $post;

        if (isset($new_post['title']) && !empty($new_post['title'])) {
            if (strlen($new_post['title']) <= 50) {
                $to_update['title'] = $this->sanitizeText($new_post['title']);
            }
        }

        if (isset($new_post['content'])) {
            if (strlen($new_post['content']) <= 1500) {
                $to_update['content'] = trim($new_post['content']);
            }
        }

        if (isset($new_post['excerpt'])) {
            $to_update['excerpt'] = $this->sanitizeText($new_post['excerpt']);
        }

        $to_update['slug'] = $this->getSlug($new_post['title']);
        $to_update['terms'] = [];

        if (isset($new_post['terms']) && count($new_post['terms']) > 0) {
            $to_update['terms'] = array_map('intval', $new_post['terms']);
            $to_update['terms'] = array_filter($new_post['terms'], fn($id) => $id > 0);
        }

        return $to_update;
    }

    public function getAll(): Response
    {
        // TODO implementar query params
        try {
            $result = $this->post->getAll();
            return $this->success($result);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->error("No se ha podido obtener la información.");
        }
    }

    public function get(): Response
    {
        try {
            $result = $this->post->get($this->post_id);
            return $this->success($result);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->error($e->getMessage(), 404);
        }
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

    public function update(array $data): Response
    {
        if (!isset($this->post_id)) {
            return $this->error("No se ha especificado el post a editar.", 412);
        }

        try {
            $post = $this->post->get($this->post_id);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->error($e->getMessage(), 404);
        }

        $new_post = $this->parseFieldsToUpdate($post, $data);

        try {
            $this->post->update($new_post);
            return $this->success(["message" => "'{$new_post['title']}' se ha creado correctamente."]);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->error($e->getMessage(), 409);
        }
    }

    public function delete(): Response
    {
        return $this->error("Pendiente por implementar");
    }
}
