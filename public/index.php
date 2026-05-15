<?php
// ============================================================
// INDEX.PHP — Punto de entrada único del sistema
// Toda petición HTTP pasa por aquí primero.
// ============================================================

// 1. Cargar configuración
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

// 2. Cargar clases del núcleo
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/Session.php';
require_once ROOT_PATH . '/core/Model.php';
require_once ROOT_PATH . '/core/View.php';
require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/core/Middleware.php';
require_once ROOT_PATH . '/core/helpers.php';
require_once ROOT_PATH . '/core/Router.php';
require_once ROOT_PATH . '/core/Mailer.php';

// 3. Iniciar sesión
Session::start();

// 4. Crear el router y cargar las rutas
$router = new Router();
require_once ROOT_PATH . '/routes/web.php';

// 5. Despachar la petición actual
$router->dispatch();
