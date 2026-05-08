<?php

namespace App\Core;

use App\Core\Response;

class Router
{
    private array $methods = [];
    private array $routes = [];

    public function set_methods(array $methods): void
    {
        $this->methods = array_merge($this->methods, $methods);
    }

    public function set_route(string $method, string $route, array $handler, array $args = []): void
    {
        if (!$this->is_method_allowed($method)) {
            throw new \InvalidArgumentException("El método '$method' no está permitido.");
        }

        if (!isset($this->routes[$method])) {
            $this->routes[$method] = [];
        }

        if (in_array($route, $this->routes[$method])) {
            throw new \InvalidArgumentException("La ruta '$route' ya está registrada para este método.");
        }

        $this->routes[$method][] = [
            'path' => $route,
            'handler' => $handler,
            'args' => $args
        ];
    }

    private function is_method_allowed(string $requested_method): bool
    {
        $methods = $this->methods;
        return in_array($requested_method, $methods);
    }

    private function getDataFromRequest(): array
    {
        $content = file_get_contents('php://input');

        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    public function process_request(string $method, string $uri)
    {
        $is_valid = $this->is_method_allowed($method);
        $requested_uri = parse_url($uri, PHP_URL_PATH);

        $path_exist = false;

        if (!$is_valid) {
            error_log("Debe registrar el método antes de utilizarlo.");
            new Response()::error("El método $method no está permitido.", 405)->send();
        }

        foreach ($this->routes[$method] as $route) {
            $pattern = preg_replace('#\{[^/]+\}#', '([^/]+)', $route['path']);
            $pattern = "#^$pattern$#";

            if (preg_match($pattern, $requested_uri, $matches)) {
                array_shift($matches);

                $path_exist = true;

                $handler = $route['handler'];
                $args = !empty($route['args']) ? $route['args'] : $this->getDataFromRequest();

                $class = $handler[0];
                $method = $handler[1];

                try {
                    $controller = new $class($matches);
                    $response = call_user_func_array(
                        [$controller, $method],
                        [$args]
                    );

                    if ($response instanceof Response) {
                        $response->send();
                    } else {
                        error_log("La respuesta debe ser una instancia de App\Core\Response");
                        new Response()::error("Error al obtener la información", 500)->send();
                    }
                } catch (\Throwable $e) {
                    error_log($e->getMessage());
                    new Response()::error("La ruta no existe.",)->send();
                }
            }
        }

        if (!$path_exist) {
            error_log("Debe registrar la ruta antes de utilizarla.");
            new Response()::error("La ruta no existe.")->send();
        }
    }
}
