<?php
// ============================================================
// CLASE BASE CONTROLLER
// Todos tus controladores (EventosController, AuthController,
// etc.) heredan de esta clase.
// ============================================================

class Controller
{
    // Renderiza una vista con datos
    // Uso desde un controlador hijo: $this->view('events/index', ['eventos' => $lista])
    protected function view(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    // Renderiza una vista dentro de un layout
    protected function viewWithLayout(string $view, string $layout, array $data = []): void
    {
        View::renderWithLayout($view, $layout, $data);
    }

    // Redirige a una URL
    // Uso: $this->redirect('events')  →  va a BASE_URL/events
    protected function redirect(string $path = ''): void
    {
        $url = BASE_URL . '/' . ltrim($path, '/');
        header("Location: {$url}");
        exit;
    }

    // Devuelve una respuesta JSON (para peticiones AJAX)
    // Uso: $this->json(['success' => true, 'data' => $eventos])
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Verifica si la petición es POST
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    // Verifica si la petición es GET
    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    // Obtiene un valor POST limpio
    // Uso: $this->input('nombre')
    protected function input(string $key, mixed $default = ''): mixed
    {
        return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
    }

    // Obtiene un valor GET limpio
    protected function query(string $key, mixed $default = ''): mixed
    {
        return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
    }

    // Verifica que el usuario esté logueado, si no redirige al login
    protected function requireAuth(): void
    {
        if (!Session::isLoggedIn()) {
            Session::flash('error', 'Debes iniciar sesión para acceder.');
            $this->redirect('auth/login');
        }
    }

    // Verifica que el usuario tenga un rol específico
    // Uso: $this->requireRole('admin')
    protected function requireRole(string ...$roles): void
    {
        $this->requireAuth();
        $userRole = Session::role();
        if (!in_array($userRole, $roles)) {
            Session::flash('error', 'No tienes permiso para acceder a esta sección.');
            $this->redirect('');
        }
    }
}
