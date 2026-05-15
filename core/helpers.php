<?php
// ============================================================
// HELPERS - Funciones de uso global en todo el sistema
// ============================================================

// ── URLs ─────────────────────────────────────────────────────

// Genera una URL absoluta
// Uso: url('events/create') → http://localhost/.../public/events/create
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

// Redirige a una URL
function redirect(string $path = ''): void
{
    header('Location: ' . url($path));
    exit;
}

// ── SEGURIDAD ────────────────────────────────────────────────

// Escapa texto para HTML (previene XSS)
function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Genera un hash seguro de contraseña
function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_BCRYPT);
}

// Verifica una contraseña contra su hash
function verifyPassword(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

// ── FECHAS ───────────────────────────────────────────────────

// Formatea una fecha de MySQL (Y-m-d) a formato legible
// Uso: formatDate('2025-07-04') → "04 de julio de 2025"
function formatDate(string $date): string
{
    if (empty($date) || $date === '0000-00-00') return 'Sin fecha';
    $meses = [
        1=>'enero',2=>'febrero',3=>'marzo',4=>'abril',
        5=>'mayo',6=>'junio',7=>'julio',8=>'agosto',
        9=>'septiembre',10=>'octubre',11=>'noviembre',12=>'diciembre'
    ];
    $ts = strtotime($date);
    return date('d', $ts) . ' de ' . $meses[(int)date('n', $ts)] . ' de ' . date('Y', $ts);
}

// Formatea fecha y hora
function formatDateTime(string $datetime): string
{
    if (empty($datetime)) return 'Sin fecha';
    return date('d/m/Y H:i', strtotime($datetime));
}

// ── TEXTO ────────────────────────────────────────────────────

// Trunca un texto largo
// Uso: truncate('Texto muy largo...', 50)
function truncate(string $text, int $length = 100): string
{
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

// Convierte texto a slug para URLs
// Uso: slug('Torneo de Taekwondo') → 'torneo-de-taekwondo'
function slug(string $text): string
{
    $text = strtolower(trim($text));
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

// ── ARCHIVOS ─────────────────────────────────────────────────

// Sube una imagen y devuelve el nombre del archivo guardado
// Devuelve false si hay error
function uploadImage(array $file, string $folder = 'events'): string|false
{
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) return false;
    if ($file['size'] > 5 * 1024 * 1024) return false; // Máx 5MB

    $uploadDir = UPLOADS_PATH . '/' . $folder . '/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $filename = time() . '_' . uniqid() . '.' . $ext;
    $destination = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $folder . '/' . $filename;
    }
    return false;
}

// ── DEBUG ────────────────────────────────────────────────────

// Imprime una variable de forma legible (solo en desarrollo)
function dd(mixed ...$vars): void
{
    if (APP_ENV !== 'development') return;
    echo '<pre style="background:#1e1e1e;color:#d4d4d4;padding:16px;border-radius:8px;font-size:13px;margin:8px;">';
    foreach ($vars as $var) {
        var_dump($var);
    }
    echo '</pre>';
    die();
}

// ── MENSAJES FLASH ───────────────────────────────────────────

// Muestra el mensaje flash en HTML (Bootstrap 5)
function flashMessage(): void
{
    $types = ['success', 'error', 'warning', 'info'];
    $icons = [
        'success' => '✓',
        'error'   => '✗',
        'warning' => '⚠',
        'info'    => 'ℹ',
    ];
    $bsClass = [
        'success' => 'success',
        'error'   => 'danger',
        'warning' => 'warning',
        'info'    => 'info',
    ];

    foreach ($types as $type) {
        if (Session::hasFlash($type)) {
            $msg = Session::getFlash($type);
            $icon = $icons[$type];
            $cls  = $bsClass[$type];
            echo "<div class=\"alert alert-{$cls} alert-dismissible fade show\" role=\"alert\">";
           echo "{$icon} " . $msg;
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            echo '</div>';
        }
    }
}
