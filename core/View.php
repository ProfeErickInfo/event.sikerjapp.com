<?php
// ============================================================
// CLASE VIEW
// Se encarga de cargar y mostrar los archivos de vista (.php)
// que están en app/Views/
// ============================================================

class View
{
    // Renderiza una vista pasándole datos
    // Uso: View::render('events/index', ['eventos' => $lista])
    public static function render(string $view, array $data = []): void
    {
        // Convierte el array $data en variables individuales
        // ['titulo' => 'Hola'] se convierte en $titulo = 'Hola'
        extract($data);

        // Construye la ruta al archivo de vista
        $viewPath = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            if (APP_ENV === 'development') {
                die("Vista no encontrada: {$viewPath}");
            } else {
                http_response_code(404);
                die('Página no encontrada.');
            }
        }

        require $viewPath;
    }

    // Renderiza una vista dentro de un layout
    // Uso: View::renderWithLayout('events/index', 'layouts/main', ['titulo' => 'Eventos'])
    public static function renderWithLayout(string $view, string $layout, array $data = []): void
    {
        extract($data);

        // Primero captura el contenido de la vista
        $viewPath = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewPath)) {
            die("Vista no encontrada: {$viewPath}");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean(); // $content tendrá el HTML de la vista

        // Luego carga el layout (que usará $content)
        $layoutPath = VIEWS_PATH . '/' . str_replace('.', '/', $layout) . '.php';
        if (!file_exists($layoutPath)) {
            die("Layout no encontrado: {$layoutPath}");
        }

        require $layoutPath;
    }

    // Genera una URL completa
    // Uso: View::url('events/create') → http://localhost/event.sikerjapp/public/events/create
    public static function url(string $path = ''): string
    {
        return BASE_URL . '/' . ltrim($path, '/');
    }

    // Escapa texto para mostrarlo seguro en HTML (previene XSS)
    // Uso: View::e($usuario['nombre'])
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}
