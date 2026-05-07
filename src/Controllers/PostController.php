<?php

namespace App\Controllers;

use App\Core\Response;
use App\Models\Post;
use App\Controllers\BaseController;

class PostController extends BaseController
{
    public function getAll(): Response
    {
        try {
            $posts = new Post()->getAll();
            return $this->success($posts);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            return $this->error("Error de conexión en la base de datos.", 500);
        }
    }

    public function get(int $id = 01): Response
    {
        $post = new Post()->get($id);
        return Response::json($post);
    }

    public function create(): Response
    {
        return Response::error("Pendiente por implementar");
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
