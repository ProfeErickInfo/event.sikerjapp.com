<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/core/Database.php';
require_once dirname(__DIR__) . '/core/helpers.php';

$db = Database::getInstance()->getConnection();

// Datos del superadmin — cámbialos a los que quieras
$nombre = 'Super Administrador';
$email  = 'admin@sikerjapp.com';
$nick   = 'admin@sikerjapp.com';
$pass   = 'Admin2026$';
$tipoU  = 0; // 0 = Administrador general

// Verifica si ya existe
$stmt = $db->prepare("SELECT id FROM wx25_usu WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    die('⚠️ Ya existe un usuario con ese email.');
}

// Inserta el superadmin
$stmt = $db->prepare(
    "INSERT INTO wx25_usu 
     (tipoU, role, id_asocc, nickz, name, email, pazz, fec_reg, estado)
     VALUES (?, 'admin', 0, ?, ?, ?, ?, ?, 1)"
);
$stmt->execute([
    $tipoU,
    $nick,
    $nombre,
    $email,
    hashPassword($pass),
    date('Y-m-d'),
]);

echo '✅ Superadmin creado correctamente.<br>';
echo '📧 Email: ' . $email . '<br>';
echo '🔑 Contraseña: ' . $pass . '<br>';
echo '<br><strong style="color:red;">⚠️ ELIMINA ESTE ARCHIVO INMEDIATAMENTE después de usarlo.</strong>';