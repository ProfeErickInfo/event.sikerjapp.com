<?php
// ============================================================
// CLASE MIDDLEWARE
// Permite proteger rutas verificando condiciones antes
// de ejecutar el controlador.
// ============================================================

class Middleware
{
    // Verifica que el usuario esté autenticado
    public static function auth(): void
    {
        if (!Session::isLoggedIn()) {
            Session::flash('error', 'Debes iniciar sesión para continuar.');
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
    }

    // Verifica que el usuario tenga uno de los roles indicados
    // Uso: Middleware::role('admin', 'club')
    public static function role(string ...$roles): void
    {
        self::auth();
        $userRole = Session::role();
        if (!in_array($userRole, $roles)) {
            http_response_code(403);
            Session::flash('error', 'No tienes permiso para acceder a esta sección.');
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
    }

    // Verifica que el usuario NO esté logueado (para login/registro)
    // Si ya está logueado lo manda al dashboard
    public static function guest(): void
    {
        if (Session::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
    }
}
