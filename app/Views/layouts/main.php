<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? APP_NAME) ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body { background: #f4f6f9; }

        /* SIDEBAR */
        #sidebar {
            width: 250px;
            min-height: 100vh;
            background: #1a2035;
            position: fixed;
            top: 0; left: 0;
            z-index: 100;
            transition: all 0.3s;
            overflow-y: auto;
        }
        #sidebar .brand {
            padding: 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        #sidebar .brand h5 {
            color: #fff;
            font-weight: 700;
            margin: 0;
            font-size: 1rem;
        }
        #sidebar .brand small { color: #8a9bb0; font-size: 0.75rem; }

        #sidebar .nav-link {
            color: #8a9bb0;
            padding: 10px 16px;
            border-radius: 8px;
            margin: 2px 8px;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        #sidebar .nav-link:hover,
        #sidebar .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,0.1);
        }
        #sidebar .nav-link i { margin-right: 8px; font-size: 1rem; }
        #sidebar .section-title {
            color: #4a5568;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 16px 24px 4px;
        }

        /* CONTENIDO PRINCIPAL */
        #main-content {
            margin-left: 250px;
            min-height: 100vh;
            transition: all 0.3s;
        }

        /* TOPBAR */
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 24px;
            position: sticky;
            top: 0;
            z-index: 99;
        }
        .topbar .page-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1a2035;
            margin: 0;
        }

        /* CARDS */
        .card { border: none; box-shadow: 0 1px 4px rgba(0,0,0,0.08); border-radius: 12px; }
        .card-header { background: #fff; border-bottom: 1px solid #f0f0f0; font-weight: 600; }

        /* Responsive */
        @media (max-width: 768px) {
            #sidebar { margin-left: -250px; }
            #sidebar.show { margin-left: 0; }
            #main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<nav id="sidebar">
    <div class="brand">
        <h5><i class="bi bi-calendar-event me-2"></i><?= APP_NAME ?></h5>
        <small>v<?= APP_VERSION ?></small>
    </div>

    <ul class="nav flex-column mt-2">

        <?php if (Session::isLoggedIn()): ?>
            <?php $user = Session::user(); ?>

            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="<?= url('dashboard') ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>

            <!-- Eventos -->
            <li><div class="section-title">Eventos</div></li>
            <li class="nav-item">
                <a class="nav-link" href="<?= url('events') ?>">
                    <i class="bi bi-calendar3"></i> Ver Eventos
                </a>
            </li>

            <?php if (in_array($user['tipoU'], [1, 4])): ?>
            <!-- Administración -->
            <li><div class="section-title">Administración</div></li>
            <li class="nav-item">
                <a class="nav-link" href="<?= url('admin/events') ?>">
                    <i class="bi bi-calendar-plus"></i> Gestionar Eventos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= url('admin/inscripciones') ?>">
                    <i class="bi bi-people"></i> Inscripciones
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= url('admin/pagos') ?>">
                    <i class="bi bi-credit-card"></i> Pagos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= url('admin/events') ?>"
                   title="Selecciona un evento para gestionar credenciales">
                    <i class="bi bi-person-badge"></i> Credenciales
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= url('admin/events') ?>"
                   title="Selecciona un evento para tomar asistencia">
                    <i class="bi bi-qr-code-scan"></i> Asistencia QR
                </a>
            </li>
            <?php endif; ?>

            <!-- Mi Cuenta -->
            <li><div class="section-title">Mi Cuenta</div></li>
            <li class="nav-item">
                <a class="nav-link" href="<?= url('perfil') ?>">
                    <i class="bi bi-person-circle"></i> Mi Perfil
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= url('auth/logout') ?>">
                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                </a>
            </li>

        <?php else: ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= url('events') ?>">
                    <i class="bi bi-calendar3"></i> Eventos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= url('auth/login') ?>">
                    <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= url('auth/register') ?>">
                    <i class="bi bi-person-plus"></i> Registrarse
                </a>
            </li>
        <?php endif; ?>

    </ul>
</nav>

<!-- CONTENIDO PRINCIPAL -->
<div id="main-content">

    <!-- TOPBAR -->
    <div class="topbar d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-light d-md-none" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <h6 class="page-title"><?= e($title ?? 'Inicio') ?></h6>
        </div>

        <?php if (Session::isLoggedIn()): ?>
            <?php $user = Session::user(); ?>
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-person-circle fs-5 text-muted"></i>
                <a href="<?= url('perfil') ?>" class="text-muted small text-decoration-none">
                    <?= e($user['name'] ?? $user['nickz']) ?>
                </a>
                <span class="badge bg-primary-subtle text-primary rounded-pill">
                    <?= e($user['role']) ?>
                </span>
            </div>
        <?php endif; ?>
    </div>

    <!-- MENSAJES FLASH -->
    <div class="px-4 pt-3">
        <?php flashMessage(); ?>
    </div>

    <!-- CONTENIDO DE LA VISTA -->
    <div class="p-4">
        <?= $content ?>
    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle sidebar en móvil
    document.getElementById('sidebarToggle')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('show');
    });

    // Marcar link activo en sidebar
    const currentPath = window.location.pathname;
    document.querySelectorAll('#sidebar .nav-link').forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.startsWith(href) && href !== '/') {
            link.classList.add('active');
        }
    });
</script>

</body>
</html>