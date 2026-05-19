<?php
/**
 * Vista: Detalle de Evento
 * 
 * @var array $evento Array con datos del evento
 * @var mixed $inscrito Datos de inscripción del usuario (false si no está inscrito)
 */
?>
<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= url('events') ?>">Eventos</a></li>
        <li class="breadcrumb-item active"><?= e(truncate($evento['nombre_corto'], 40)) ?></li>
    </ol>
</nav>

<div class="row g-4">

    <!-- Columna principal -->
    <div class="col-lg-8">
        <div class="card">

            <!-- Imagen -->
            <div style="height:280px;overflow:hidden;background:#1a2035;border-radius:12px 12px 0 0;">
                <?php
                    $pic = $evento['pic'];
                    if ($pic === 'no-disponible.jpeg' || $pic === 'NO-Aplica' || empty($pic)) {
                        $imgUrl = 'https://placehold.co/800x280/1a2035/ffffff?text=' . urlencode($evento['nombre_corto']);
                    } elseif (str_starts_with($pic, 'events/')) {
                        $imgUrl = url('uploads/' . $pic);
                    } else {
                        $imgUrl = url('uploads/events/' . $pic);
                    }
                ?>
                <img src="<?= e($imgUrl) ?>"
                     alt="<?= e($evento['nombre_corto']) ?>"
                     style="width:100%;height:100%;object-fit:cover;opacity:0.85;">
            </div>

            <div class="card-body p-4">

                <!-- Categoría y estado -->
                <div class="d-flex gap-2 mb-3">
                    <span class="badge bg-primary-subtle text-primary">
                        <?= e($evento['categoria'] ?? 'General') ?>
                    </span>
                    <?php if ($evento['estado'] == 1): ?>
                        <span class="badge bg-success-subtle text-success">Activo</span>
                    <?php else: ?>
                        <span class="badge bg-secondary-subtle text-secondary">Inactivo</span>
                    <?php endif; ?>
                    <?php if ($evento['inscripcion'] == 1): ?>
                        <span class="badge bg-warning-subtle text-warning">Inscripciones abiertas</span>
                    <?php endif; ?>
                </div>

                <!-- Título -->
                <h3 class="fw-bold mb-3"><?= e($evento['nombre_corto']) ?></h3>

                <!-- Descripción -->
                <div class="text-muted" style="line-height:1.7;">
                    <?= nl2br(e($evento['descripcion'])) ?>
                </div>

            </div>
        </div>
    </div>

    <!-- Columna lateral -->
    <div class="col-lg-4">

        <!-- Info del evento -->
        <div class="card mb-3">
            <div class="card-header py-3">
                <i class="bi bi-info-circle me-2"></i>Información
            </div>
            <div class="card-body p-3">
                <ul class="list-unstyled mb-0">
                    <li class="d-flex gap-3 py-2 border-bottom">
                        <i class="bi bi-calendar3 text-primary mt-1"></i>
                        <div>
                            <small class="text-muted d-block">Fecha de inicio</small>
                            <strong><?= formatDate($evento['fecha']) ?></strong>
                        </div>
                    </li>
                    <li class="d-flex gap-3 py-2 border-bottom">
                        <i class="bi bi-calendar-check text-primary mt-1"></i>
                        <div>
                            <small class="text-muted d-block">Fecha de cierre</small>
                            <strong><?= formatDate($evento['fecha2']) ?></strong>
                        </div>
                    </li>
                    <li class="d-flex gap-3 py-2 border-bottom">
                        <i class="bi bi-tag text-primary mt-1"></i>
                        <div>
                            <small class="text-muted d-block">Categoría</small>
                            <strong><?= e($evento['categoria'] ?? 'General') ?></strong>
                        </div>
                    </li>
                    <li class="d-flex gap-3 py-2">
                        <i class="bi bi-hash text-primary mt-1"></i>
                        <div>
                            <small class="text-muted d-block">Edición</small>
                            <strong><?= e($evento['edicion']) ?></strong>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Acciones del usuario -->
        <div class="card">
            <div class="card-body p-3">

                <?php 
                $user = Session::isLoggedIn() ? Session::user() : null;
                $esManager = $user && $user['tipoU'] == 4;
                $esEventoPropio = $user && isset($evento['id_admin']) && $evento['id_admin'] == $user['id'];
                ?>

                <?php if ($esManager && !$esEventoPropio): ?>
                    <!-- Manager viendo un evento que no es suyo -->
                    <div class="text-center py-3 text-muted">
                        <i class="bi bi-eye fs-2 d-block mb-2"></i>
                        <small>Solo puedes gestionar tu evento asignado.</small>
                    </div>

                <?php else: ?>
                    <?php if (!Session::isLoggedIn()): ?>
                    <p class="text-muted small mb-3">Debes iniciar sesión para inscribirte en este evento.</p>
                    <a href="<?= url('auth/login') ?>" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                    </a>
                    <a href="<?= url('auth/register') ?>" class="btn btn-outline-secondary w-100 mt-2">
                        <i class="bi bi-person-plus me-2"></i>Registrarse
                    </a>

                <?php elseif (!empty($inscrito)): ?>
                    <div class="text-center py-2">
                        <i class="bi bi-check-circle-fill text-success fs-2 d-block mb-2"></i>
                        <strong class="text-success">Ya estás inscrito</strong>
                        <p class="text-muted small mt-1">Tu inscripción está registrada.</p>
                    </div>
                    <?php if ($inscrito['estado'] == 0): ?>
                    <form action="<?= url('inscripciones/cancelar/' . $inscrito['id']) ?>"
                          method="POST"
                          onsubmit="return confirm('¿Cancelar tu inscripción?')">
                        <input type="hidden" name="id_evento" value="<?= $evento['id'] ?>">
                        <button class="btn btn-outline-danger btn-sm w-100 mt-2">
                            <i class="bi bi-x-circle me-1"></i>Cancelar Inscripción
                        </button>
                    </form>
                    <?php endif; ?>

                <?php elseif ($evento['inscripcion'] == 1): ?>
                    <p class="text-muted small mb-3">Las inscripciones están abiertas para este evento.</p>
                    <a href="<?= url('events/' . $evento['id'] . '/inscribirse') ?>"
                       class="btn btn-success w-100">
                        <i class="bi bi-person-check me-2"></i>Inscripciones
                    </a>

                <?php else: ?>
                    <div class="text-center py-2">
                        <i class="bi bi-x-circle text-muted fs-2 d-block mb-2"></i>
                        <strong class="text-muted">Inscripciones cerradas</strong>
                    </div>
                <?php endif; ?>

                <!-- Botón pago -->
                <?php if (!empty($inscrito) && ($inscrito['pago_estado'] ?? 0) != 2): ?>
                <a href="<?= url('events/' . $evento['id'] . '/pago') ?>"
                   class="btn btn-warning w-100 mt-2">
                    <i class="bi bi-credit-card me-2"></i>
                    <?= ($inscrito['pago_estado'] ?? 0) == 1 ? 'Ver Estado del Pago' : 'Subir Comprobante' ?>
                </a>
                <?php endif; ?>

                <!-- Botón credencial y certificado -->
                <?php if (!empty($inscrito) && Session::isLoggedIn()): ?>
                    <?php $user = Session::user(); ?>
                    <?php if ($user['tipoU'] != 2): ?>
                        <?php
                        require_once ROOT_PATH . '/app/Models/CredencialModel.php';
                        $credModel       = new CredencialModel();
                        $credAprobada    = $credModel->isAprobada($user['id'], $evento['id'], 1);
                        $certAprobado    = $credModel->isAprobada($user['id'], $evento['id'], 2);
                        ?>

                        <!-- Credencial -->
                        <?php if ($credAprobada): ?>
                        <a href="<?= url('credential/' . $user['id'] . '/' . $evento['id']) ?>"
                           class="btn btn-success w-100 mt-2 btn-sm" target="_blank">
                            <i class="bi bi-person-badge me-2"></i>Descargar Credencial
                        </a>
                        <?php else: ?>
                        <div class="alert alert-warning py-2 px-3 mt-2 mb-0 small">
                            <i class="bi bi-clock me-1"></i>Credencial pendiente de aprobación
                        </div>
                        <?php endif; ?>

                        <!-- Certificado -->
                        <?php if ($certAprobado): ?>
                        <a href="<?= url('certificate/' . $user['id'] . '/' . $evento['id']) ?>"
                           class="btn btn-warning w-100 mt-2 btn-sm" target="_blank">
                            <i class="bi bi-award me-2"></i>Descargar Certificado
                        </a>
                        <?php else: ?>
                        <div class="alert alert-secondary py-2 px-3 mt-2 mb-0 small">
                            <i class="bi bi-clock me-1"></i>Certificado pendiente de aprobación
                        </div>
                        <?php endif; ?>

                    <?php endif; ?>
                <?php endif; ?>

                <hr class="my-3">
              <a href="<?= url('events/' . $evento['id'] . '/agenda') ?>"
   class="btn btn-outline-primary w-100 btn-sm mb-2">
    <i class="bi bi-calendar3 me-2"></i>Ver Agenda
</a>
<a href="<?= url('events/' . $evento['id'] . '/documentos') ?>"
   class="btn btn-outline-secondary w-100 btn-sm">
    <i class="bi bi-folder me-2"></i>Documentos
</a>

                <?php endif; ?>

            </div>
        </div>

        <!-- Botones admin -->
        <?php 
        $userActual = Session::user();
        $puedeAdmin = Session::isLoggedIn() && (
            $userActual['tipoU'] == 1 || 
            ($userActual['tipoU'] == 4 && ($evento['id_admin'] ?? 0) == $userActual['id'])
        );
        ?>
        <?php if ($puedeAdmin): ?>
        <div class="card mt-3">
            <div class="card-header py-3">
                <i class="bi bi-gear me-2"></i>Administración
            </div>
            <div class="card-body p-3 d-grid gap-2">
                <a href="<?= url('admin/events/edit/' . $evento['id']) ?>"
                   class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil me-2"></i>Editar Evento
                </a>
                <a href="<?= url('admin/inscripciones?evento=' . $evento['id']) ?>"
                   class="btn btn-info btn-sm">
                    <i class="bi bi-people me-2"></i>Ver Inscripciones
                </a>
                <a href="<?= url('admin/credenciales/' . $evento['id']) ?>"
                   class="btn btn-secondary btn-sm">
                    <i class="bi bi-person-badge me-2"></i>Credenciales
                </a>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>