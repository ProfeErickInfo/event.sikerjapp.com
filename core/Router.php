<?php
// ============================================================
// CLASE ROUTER
// Se encarga de mapear URLs a controladores y métodos.
// Ejemplo: GET /events  →  EventosController@index
// ============================================================

class Router
{
    // Almacena todas las rutas registradas
    private array $routes = [];

    // ── REGISTRO DE RUTAS ────────────────────────────────────

    // Registra una ruta GET
    public function get(string $path, string $action, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $action, $middleware);
    }

    // Registra una ruta POST
    public function post(string $path, string $action, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $action, $middleware);
    }

    // Registra una ruta que acepta GET y POST
    public function any(string $path, string $action, array $middleware = []): void
    {
        $this->addRoute('GET',  $path, $action, $middleware);
        $this->addRoute('POST', $path, $action, $middleware);
    }

    // Agrega la ruta al array interno
    private function addRoute(string $method, string $path, string $action, array $middleware): void
    {
        $this->routes[] = [
            'method'     => $method,
            'path'       => '/' . trim($path, '/'),
            'action'     => $action,
            'middleware' => $middleware,
        ];
    }

    // ── DESPACHO ─────────────────────────────────────────────

    // Analiza la URL actual y ejecuta el controlador correspondiente
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Elimina el prefijo del proyecto de la URL
        // Si la URL es /event.sikerjapp/public/events, queda /events
        $basePath = parse_url(BASE_URL, PHP_URL_PATH);
        if ($basePath && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = '/' . trim($uri, '/');
        if ($uri === '') $uri = '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            // Convierte {param} en regex para capturar parámetros dinámicos
            // Ejemplo: /events/{id} captura el número en la URL
            $pattern = preg_replace('/\{[a-zA-Z_]+\}/', '([^/]+)', $route['path']);
            $pattern = '@^' . $pattern . '$@';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Elimina el match completo, deja solo los grupos

                // Ejecuta middleware
                foreach ($route['middleware'] as $mw) {
                    Middleware::$mw();
                }

                // Ejecuta el controlador
                $this->runAction($route['action'], $matches);
                return;
            }
        }

        // Ninguna ruta coincidió → 404
        $this->notFound();
    }

    // Instancia el controlador y llama al método
    // $action formato: 'EventosController@index'
    private function runAction(string $action, array $params = []): void
    {
        [$controllerName, $method] = explode('@', $action);

        $controllerFile = ROOT_PATH . '/app/Controllers/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            die("Controlador no encontrado: {$controllerName}");
        }

        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            die("Clase no encontrada: {$controllerName}");
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $method)) {
            die("Método no encontrado: {$controllerName}@{$method}");
        }

        call_user_func_array([$controller, $method], $params);
    }

    // Página 404
    private function notFound(): void
    {
        http_response_code(404);
        if (file_exists(VIEWS_PATH . '/errors/404.php')) {
            require VIEWS_PATH . '/errors/404.php';
        } else {
            echo '<h1>404 - Página no encontrada</h1>';
        }
    }
}
