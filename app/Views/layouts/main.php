<?php
/**
 * Layout: Main
 * 
 * @var string $content Contenido de la vista renderizada
 * @var string $title Título de la página (opcional)
 */
?>
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
        background: #f8f9ff;
        border-right: 1px solid #e2e8f0;
        position: fixed;
        top: 0; left: 0;
        z-index: 100;
        transition: all 0.3s;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }
    #sidebar .brand {
        padding: 20px 16px 16px;
        border-bottom: 1px solid #e2e8f0;
    }
    #sidebar .brand-icon {
        width: 34px;
        height: 34px;
        background: #3b5bdb;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    #sidebar .brand h5 {
        color: #1a1a2e;
        font-weight: 600;
        margin: 0;
        font-size: 0.9rem;
    }
    #sidebar .brand small { color: #9ca3af; font-size: 0.72rem; }

    #sidebar .nav-link {
        color: #4b5563;
        padding: 9px 12px;
        border-radius: 8px;
        margin: 2px 8px;
        font-size: 0.875rem;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    #sidebar .nav-link:hover {
        color: #3b5bdb;
        background: #eef1ff;
    }
    #sidebar .nav-link.active {
        color: #3b5bdb;
        background: #eef1ff;
        font-weight: 600;
    }
    #sidebar .nav-link i { font-size: 1rem; }
    #sidebar .section-title {
        color: #9ca3af;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 14px 24px 4px;
    }
    #sidebar .nav-link.logout { color: #ef4444; }
    #sidebar .nav-link.logout:hover { background: #fef2f2; color: #dc2626; }

    /* USER FOOTER */
    .sidebar-user {
        padding: 12px 16px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: auto;
    }
    .sidebar-user .avatar {
        width: 32px;
        height: 32px;
        background: #eef1ff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 1px solid #c5cff8;
        font-size: 0.75rem;
        font-weight: 600;
        color: #3b5bdb;
    }

    /* CONTENIDO PRINCIPAL */
    #main-content {
        margin-left: 250px;
        min-height: 100vh;
        transition: all 0.3s;
    }

    /* TOPBAR */
    .topbar {
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        padding: 12px 24px;
        position: sticky;
        top: 0;
        z-index: 99;
    }
    .topbar .page-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1a2035;
        margin: 0;
    }

    /* CARDS */
    .card {
        border: none;
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        border-radius: 12px;
    }
    .card-header {
        background: #fff;
        border-bottom: 1px solid #f0f0f0;
        font-weight: 600;
    }

    /* ── RESPONSIVE ─────────────────────────────────────────── */
    @media (max-width: 768px) {
        /* Sidebar oculto por defecto en móvil */
        #sidebar {
            margin-left: -250px;
            box-shadow: none;
        }
        #sidebar.show {
            margin-left: 0;
            box-shadow: 4px 0 20px rgba(0,0,0,0.15);
        }
        #main-content {
            margin-left: 0;
        }
        /* Overlay cuando el sidebar está abierto */
        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 99;
        }
        #sidebar-overlay.show {
            display: block;
        }
        /* Topbar más compacto */
        .topbar {
            padding: 10px 16px;
        }
        /* Contenido con menos padding */
        #main-content .p-4 {
            padding: 12px !important;
        }
        /* Cards full width */
        .card {
            border-radius: 8px;
        }
        /* Tablas scrolleables */
        .table-responsive {
            font-size: 0.8rem;
        }
        /* Botones más grandes para touch */
        .btn-sm {
            padding: 6px 10px;
            font-size: 0.8rem;
        }
    }

    @media (max-width: 480px) {
        /* Tarjetas estadísticas en 2 columnas */
        .col-md-3.col-sm-6 {
            flex: 0 0 50%;
            max-width: 50%;
        }
        /* Texto más pequeño en tablas */
        .table td, .table th {
            font-size: 0.75rem;
            padding: 6px 8px;
        }
        
    }


@media (max-width: 768px) {
    nav[aria-label="breadcrumb"] {
        display: none;
    }
}
</style>
</head>
<body>

<?php
// Preparar datos para el sidebar
$eventoManager = 0;
if (Session::isLoggedIn()) {
    $user = Session::user();
    if ($user['tipoU'] == 4) {
        $dbMenu = Database::getInstance()->getConnection();
        $stmtMenu = $dbMenu->prepare("SELECT id FROM tbx_eventos WHERE id_admin = ? LIMIT 1");
        $stmtMenu->execute([$user['id']]);
        $evManager = $stmtMenu->fetch();
        $eventoManager = $evManager['id'] ?? 0;
    }
}
?>

<!-- Overlay para móvil -->
<div id="sidebar-overlay"></div>

<!-- SIDEBAR -->
<nav id="sidebar">
    <!-- Brand -->
    <div class="brand d-flex align-items-center gap-2">
        <div class="brand-icon">
            <i class="bi bi-calendar-event text-white"></i>
        </div>
        <div>
            <h5><?= APP_NAME ?></h5>
            <small>v<?= APP_VERSION ?></small>
        </div>
    </div>

    <ul class="nav flex-column mt-2 flex-grow-1">
        <?php if (Session::isLoggedIn()): ?>
            <?php $user = Session::user(); ?>

            <li class="nav-item">
                <a class="nav-link" href="<?= url('dashboard') ?>">
                    <i class="bi bi-house"></i> Inicio
                </a>
            </li>

            <li><div class="section-title">Eventos</div></li>
            <li class="nav-item">
                <a class="nav-link" href="<?= url('events') ?>">
                    <i class="bi bi-calendar3"></i> Ver Eventos
                </a>
            </li>

            <?php if (in_array($user['tipoU'], [1, 4])): ?>
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
    <?php if ($user['tipoU'] == 4): ?>
        <a class="nav-link" href="<?= url('admin/credenciales/' . $eventoManager) ?>">
            <i class="bi bi-person-badge"></i> Credenciales
        </a>
    <?php else: ?>
        <a class="nav-link" href="<?= url('admin/events') ?>"
           title="Selecciona un evento para gestionar credenciales">
            <i class="bi bi-person-badge"></i> Credenciales
        </a>
    <?php endif; ?>
</li>
            <li class="nav-item">
                <?php if ($user['tipoU'] == 4): ?>
                    <a class="nav-link" href="<?= url('admin/asistencia/' . $eventoManager) ?>">
                        <i class="bi bi-qr-code-scan"></i> Asistencia QR
                    </a>
                <?php else: ?>
                    <a class="nav-link" href="<?= url('admin/events') ?>"
                       title="Selecciona un evento para tomar asistencia">
                        <i class="bi bi-qr-code-scan"></i> Asistencia QR
                    </a>
                <?php endif; ?>
            </li>
            <?php endif; ?>

            <li><div class="section-title">Mi Cuenta</div></li>
            <li class="nav-item">
                <a class="nav-link" href="<?= url('perfil') ?>">
                    <i class="bi bi-person-circle"></i> Mi Perfil
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link logout" href="<?= url('auth/logout') ?>">
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

    <!-- User footer -->
    <?php if (Session::isLoggedIn()): ?>
    <?php $user = Session::user(); ?>
    <div class="sidebar-user">
        <div class="avatar">
            <?= strtoupper(substr($user['name'] ?? 'U', 0, 2)) ?>
        </div>
        <div style="min-width:0;">
            <p class="mb-0 fw-semibold text-truncate" style="font-size:0.8rem;color:#1a1a2e;">
                <?= e($user['name'] ?? $user['nickz']) ?>
            </p>
            <p class="mb-0" style="font-size:0.72rem;color:#9ca3af;"><?= e($user['role']) ?></p>
        </div>
    </div>
    <?php endif; ?>
</nav>

<!-- CONTENIDO PRINCIPAL -->
<div id="main-content">

    <!-- TOPBAR -->
   <!-- TOPBAR -->
<div class="topbar d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <!-- Botón hamburguesa solo en móvil -->
        <button class="btn btn-sm d-md-none" id="sidebarToggle"
                style="background:#eef1ff;border:none;color:#3b5bdb;padding:6px 10px;border-radius:8px;">
            <i class="bi bi-list fs-5"></i>
        </button>
        <!-- Título solo en PC -->
        <h6 class="page-title d-none d-md-block"><?= e($title ?? 'Inicio') ?></h6>
    </div>

    <?php if (Session::isLoggedIn()): ?>
        <?php $user = Session::user(); ?>
        <!-- Info usuario solo en PC -->
        <div class="d-none d-md-flex align-items-center gap-2">
            <i class="bi bi-person-circle fs-5 text-muted"></i>
            <a href="<?= url('perfil') ?>" class="text-muted small text-decoration-none">
                <?= e($user['name'] ?? $user['nickz']) ?>
            </a>
            <span class="badge bg-primary-subtle text-primary rounded-pill">
                <?= e($user['role']) ?>
            </span>
        </div>
        <!-- En móvil solo muestra avatar con enlace al perfil -->
        <a href="<?= url('perfil') ?>" class="d-md-none"
           style="width:32px;height:32px;background:#eef1ff;border-radius:50%;
                  display:flex;align-items:center;justify-content:center;
                  text-decoration:none;color:#3b5bdb;font-weight:600;font-size:0.75rem;">
            <?= strtoupper(substr($user['name'] ?? 'U', 0, 2)) ?>
        </a>
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
    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebar-overlay');
    const toggler  = document.getElementById('sidebarToggle');

    // Toggle sidebar
    toggler?.addEventListener('click', () => {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    });

    // Cierra sidebar al hacer clic en overlay
    overlay?.addEventListener('click', () => {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
    });

    // Cierra sidebar al hacer clic en un enlace (móvil)
    if (window.innerWidth <= 768) {
        document.querySelectorAll('#sidebar .nav-link').forEach(link => {
            link.addEventListener('click', () => {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        });
    }

    // Marcar link activo
    const currentPath = window.location.pathname;
    document.querySelectorAll('#sidebar .nav-link').forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.startsWith(href) && href !== '/') {
            link.classList.add('active');
        }
    });
</script>
<script>
// ── TABLAS RESPONSIVE EN MÓVIL ───────────────────────────────
if (window.innerWidth <= 768) {
    document.querySelectorAll('.table').forEach(function(table) {
        
        const headers = Array.from(table.querySelectorAll('thead th'))
                             .map(th => th.textContent.trim());
        
        if (headers.length <= 3) return; // Si tiene 3 o menos columnas no hace nada

        // Agrega columna de "ver más" en el header
        const thead = table.querySelector('thead tr');
        if (thead) {
            thead.querySelectorAll('th:nth-child(n+4)').forEach(th => {
                th.style.display = 'none';
            });
            const thMore = document.createElement('th');
            thMore.textContent = '';
            thMore.style.width = '40px';
            thead.appendChild(thMore);
        }

        // Procesa cada fila
        table.querySelectorAll('tbody tr').forEach(function(row, rowIndex) {
            const cells = Array.from(row.querySelectorAll('td'));
            if (cells.length <= 3) return;

            // Oculta columnas desde la 4ta
            cells.forEach((td, i) => {
                if (i >= 3) td.style.display = 'none';
            });

            // Construye contenido del modal
            let modalContent = '';
            cells.forEach((td, i) => {
                if (headers[i]) {
                    modalContent += `
                        <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                            <small class="text-muted fw-semibold" style="min-width:100px;">
                                ${headers[i]}
                            </small>
                            <div class="text-end">${td.innerHTML}</div>
                        </div>`;
                }
            });

            // Crea ID único para el modal
            const modalId = 'modal_row_' + rowIndex + '_' + Date.now();

            // Agrega botón "..." en la fila
            const tdBtn = document.createElement('td');
            tdBtn.innerHTML = `
                <button class="btn btn-sm" 
                        style="background:#eef1ff;color:#3b5bdb;border:none;padding:4px 8px;border-radius:6px;"
                        data-bs-toggle="modal" 
                        data-bs-target="#${modalId}">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>`;
            row.appendChild(tdBtn);

            // Crea el modal
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.id = modalId;
            modal.setAttribute('tabindex', '-1');
            modal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content" style="border-radius:16px;">
                        <div class="modal-header py-3" style="border-bottom:1px solid #f0f0f0;">
                            <h6 class="modal-title fw-bold">Detalle</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${modalContent}
                        </div>
                    </div>
                </div>`;
            document.body.appendChild(modal);
        });
    });
}
</script>
</body>
</html>