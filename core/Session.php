<?php
// ============================================================
// CLASE SESSION
// Maneja todo lo relacionado con sesiones PHP de forma
// segura y centralizada.
// ============================================================

class Session
{
    // Inicia la sesión con configuración segura
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'secure'   => false,      // Cambiar a true en producción con HTTPS
                'httponly' => true,        // Evita acceso desde JavaScript
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    // Guarda un valor en sesión
    // Uso: Session::set('user_id', 5)
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    // Obtiene un valor de sesión
    // Uso: Session::get('user_id')
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    // Verifica si existe una clave en sesión
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    // Elimina una clave de sesión
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    // Destruye toda la sesión (logout)
    public static function destroy(): void
    {
        session_unset();
        session_destroy();
        // Elimina la cookie de sesión
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
    }

    // ── MENSAJES FLASH ───────────────────────────────────────
    // Los mensajes flash se muestran UNA sola vez y luego desaparecen.
    // Útiles para: "Registro exitoso", "Error al guardar", etc.

    // Guarda un mensaje flash
    // Tipo: 'success', 'error', 'warning', 'info'
    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][$type] = $message;
    }

    // Obtiene y elimina el mensaje flash
    public static function getFlash(string $type): ?string
    {
        $message = $_SESSION['_flash'][$type] ?? null;
        unset($_SESSION['_flash'][$type]);
        return $message;
    }

    // Verifica si hay mensajes flash
    public static function hasFlash(string $type): bool
    {
        return isset($_SESSION['_flash'][$type]);
    }

    // ── USUARIO AUTENTICADO ──────────────────────────────────

    // Guarda los datos del usuario logueado
    public static function login(array $user): void
    {
        self::set('auth_user', $user);
        self::set('auth_id',   $user['id']);
        self::set('auth_role', $user['role']);
        session_regenerate_id(true); // Previene session fixation
    }

    // Verifica si hay un usuario logueado
    public static function isLoggedIn(): bool
    {
        return self::has('auth_user');
    }

    // Obtiene el usuario logueado
    public static function user(): ?array
    {
        return self::get('auth_user');
    }

    // Obtiene el rol del usuario logueado
    public static function role(): ?string
    {
        return self::get('auth_role');
    }

    // Cierra sesión
    public static function logout(): void
    {
        self::destroy();
    }
}
