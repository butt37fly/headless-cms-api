<?php

ini_set('log_errors', 1);

ini_set(
    'error_log',
    dirname(__DIR__) . '/storage/logs/error.log'
);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

use App\Core\Router;

use App\Controllers\PostController;
use App\Controllers\TaxonomyController;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$router = new Router();

try {
    $router->set_methods(['GET', 'POST', 'PUT', 'DELETE']);

    $router->set_route("GET", "/posts", [PostController::class, 'getAll']);
    $router->set_route("GET", "/posts/{id}", [PostController::class, 'get']);
    $router->set_route("POST", "/post", [PostController::class, 'create']);

    $router->set_route("POST", "/taxonomies", [TaxonomyController::class, 'create']);
    $router->set_route("GET", "/taxonomies", [TaxonomyController::class, 'get']);
    $router->set_route("PUT", "/taxonomies/{slug}", [TaxonomyController::class, 'update']);
    $router->set_route("DELETE", "/taxonomies/{slug}", [TaxonomyController::class, 'delete']);
} catch (\Throwable $th) {
    throw $th;
}

$router->process_request($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
