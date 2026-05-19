<?php $user = Session::user(); ?>

<div class="row g-4">

    <!-- Bienvenida -->
    <div class="col-12">
        <div class="card">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div style="width:56px;height:56px;background:linear-gradient(135deg,#1a2035,#2d3a5e);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-person-fill text-white fs-4"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold">¡Bienvenido, <?= e($user['name'] ?? $user['nickz']) ?>!</h5>
                    <small class="text-muted"><?= e($user['role']) ?> · <?= formatDate(date('Y-m-d')) ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas estadísticas -->
    <div class="col-md-3 col-sm-6">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Eventos Activos</p>
                        <h3 class="fw-bold mb-0"><?= $stats['eventos_activos'] ?? 0 ?></h3>
                    </div>
                    <div style="width:48px;height:48px;background:#dbeafe;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-calendar-event text-primary fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Mis Inscripciones</p>
                        <h3 class="fw-bold mb-0"><?= $stats['mis_inscripciones'] ?? 0 ?></h3>
                    </div>
                    <div style="width:48px;height:48px;background:#d1fae5;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-check-circle text-success fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Total Usuarios</p>
                        <h3 class="fw-bold mb-0"><?= $stats['total_usuarios'] ?? 0 ?></h3>
                    </div>
                    <div style="width:48px;height:48px;background:#fef3c7;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-people text-warning fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Total Eventos</p>
                        <h3 class="fw-bold mb-0"><?= $stats['total_eventos'] ?? 0 ?></h3>
                    </div>
                    <div style="width:48px;height:48px;background:#fce7f3;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-trophy text-danger fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mis Inscripciones — solo usuarios individuales -->
    <?php if (!empty($misInscripciones)): ?>
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <span><i class="bi bi-person-check me-2"></i>Mis Inscripciones</span>
                <a href="<?= url('events') ?>" class="btn btn-sm btn-outline-primary">Ver eventos</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Evento</th>
                                <th>Fecha</th>
                                <th>Valor</th>
                                <th>Inscripción</th>
                                <th>Pago</th>
                                <th style="width:80px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($misInscripciones as $ins): ?>
                           <tr>
    <td>
        <strong><?= e($ins['evento_nombre']) ?></strong>
        <?php if (!empty($ins['participante_nombre'])): ?>
        <small class="d-block text-muted">
            <i class="bi bi-person me-1"></i><?= e($ins['participante_nombre']) ?>
        </small>
        <?php endif; ?>
    </td>
    <td><small class="text-muted"><?= formatDate($ins['fecha']) ?></small></td>
    <td>
        <?php if (($ins['valor_inscripcion'] ?? 0) > 0): ?>
            <small>$<?= number_format($ins['valor_inscripcion'], 0, ',', '.') ?></small>
        <?php else: ?>
            <small class="text-success">Gratuito</small>
        <?php endif; ?>
    </td>
    <td>
        <?php
        $estados = [0=>'Pendiente', 1=>'Aprobada'];
        $clases  = [0=>'warning',   1=>'success'];
        $est     = $ins['estado'];
        ?>
        <span class="badge bg-<?= $clases[$est] ?>">
            <?= $estados[$est] ?>
        </span>
    </td>
    <td>
        <?php
        $pagoEstados = [0=>'Sin pago', 1=>'En revisión', 2=>'Aprobado', 3=>'Rechazado'];
        $pagoClases  = [0=>'secondary', 1=>'warning',    2=>'success',  3=>'danger'];
        $pest        = $ins['pago_estado'] ?? 0;
        ?>
        <span class="badge bg-<?= $pagoClases[$pest] ?>">
            <?= $pagoEstados[$pest] ?>
        </span>
    </td>
    <td>
        <div class="d-flex gap-1">
            <a href="<?= url('events/' . $ins['id_evento']) ?>"
               class="btn btn-sm btn-outline-secondary" title="Ver evento">
                <i class="bi bi-eye"></i>
            </a>
            <?php
// Verifica credencial aprobada
require_once ROOT_PATH . '/app/Models/CredencialModel.php';
$credModel = new CredencialModel();
$credAprobada = $credModel->isAprobada($user['id'], $ins['id_evento']);
?>
<?php if ($credAprobada): ?>
<a href="<?= url('credential/' . $user['id'] . '/' . $ins['id_evento']) ?>"
   class="btn btn-sm btn-success" title="Descargar credencial" target="_blank">
    <i class="bi bi-person-badge"></i>
</a>
<?php endif; ?>
            <?php if ($pest != 2 && ($ins['valor_inscripcion'] ?? 0) > 0): ?>
            <a href="<?= url('events/' . $ins['id_evento'] . '/pago') ?>"
               class="btn btn-sm btn-warning" title="Subir comprobante">
                <i class="bi bi-credit-card"></i>
            </a>
            <?php endif; ?>
        </div>
    </td>
</tr>


                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Próximos eventos -->
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <span><i class="bi bi-calendar3 me-2"></i>Próximos Eventos</span>
                <a href="<?= url('events') ?>" class="btn btn-sm btn-outline-primary">Ver todos</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($proximos_eventos)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                        No hay eventos próximos
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Evento</th>
                                    <th>Fecha</th>
                                    <th>Categoría</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($proximos_eventos as $ev): ?>
                                <tr>
                                    <td><strong><?= e($ev['nombre_corto']) ?></strong></td>
                                    <td><small><?= formatDate($ev['fecha']) ?></small></td>
                                    <td><small class="text-muted"><?= e($ev['detalle_categoria'] ?? '') ?></small></td>
                                    <td>
                                        <a href="<?= url('events/' . $ev['id']) ?>"
                                           class="btn btn-sm btn-outline-secondary">Ver</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Accesos rápidos -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header py-3">
                <i class="bi bi-lightning me-2"></i>Accesos Rápidos
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= url('events') ?>" class="btn btn-outline-primary btn-sm text-start">
                        <i class="bi bi-calendar3 me-2"></i>Ver Eventos
                    </a>
                    <?php if (in_array($user['tipoU'], [1, 4])): ?>
                    <a href="<?= url('admin/events/create') ?>" class="btn btn-outline-success btn-sm text-start">
                        <i class="bi bi-plus-circle me-2"></i>Crear Evento
                    </a>
                    <a href="<?= url('admin/inscripciones') ?>" class="btn btn-outline-info btn-sm text-start">
                        <i class="bi bi-people me-2"></i>Ver Inscripciones
                    </a>
                    <a href="<?= url('admin/pagos') ?>" class="btn btn-outline-warning btn-sm text-start">
                        <i class="bi bi-credit-card me-2"></i>Gestionar Pagos
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
@media (max-width: 480px) {
    /* Tabla de inscripciones simplificada en móvil */
    .table th:nth-child(3),
    .table td:nth-child(3) {
        display: none; /* oculta columna Valor en móvil */
    }
}
</style>