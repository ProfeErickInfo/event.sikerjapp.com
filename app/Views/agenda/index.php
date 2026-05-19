<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= url('events') ?>">Eventos</a></li>
        <li class="breadcrumb-item">
            <a href="<?= url('events/' . $evento['id']) ?>"><?= e(truncate($evento['nombre_corto'], 30)) ?></a>
        </li>
        <li class="breadcrumb-item active">Agenda</li>
    </ol>
</nav>

<!-- Encabezado -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Agenda del Evento</h4>
        <small class="text-muted"><?= e($evento['nombre_corto']) ?></small>
    </div>
   <?php if (Session::isLoggedIn() && in_array(Session::user()['tipoU'], [1, 4])): ?>
<a href="<?= url('admin/agenda/' . $evento['id']) ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-gear me-1"></i>Administrar
</a>
<?php endif; ?>

    
</div>

<?php if (empty($sesiones)): ?>
<div class="text-center py-5">
    <i class="bi bi-calendar-x fs-1 text-muted d-block mb-3"></i>
    <h5 class="text-muted">La agenda aún no está disponible</h5>
    <p class="text-muted">El organizador publicará el programa próximamente.</p>
</div>
<?php else: ?>

<?php foreach ($sesiones as $sesion): ?>
<div class="card mb-4">

    <!-- Encabezado de sesión -->
    <div class="card-header py-3"
         style="background:linear-gradient(135deg,#1a2035,#2d3a5e);color:white;border-radius:12px 12px 0 0;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-calendar3 me-2"></i><?= e($sesion['nombre']) ?>
                </h6>
                <small style="opacity:0.8;">
                    <?= formatDate($sesion['fecha']) ?>
                    <?php if (!empty($sesion['lugar'])): ?>
                        · <i class="bi bi-geo-alt me-1"></i><?= e($sesion['lugar']) ?>
                    <?php endif; ?>
                </small>
            </div>
            <span class="badge bg-light text-dark">Sesión <?= $sesion['orden'] ?></span>
        </div>
    </div>

    <!-- Cronograma de la sesión -->
    <div class="card-body p-0">
        <?php if (empty($sesion['cronograma'])): ?>
        <div class="text-center py-4 text-muted">
            <small>Sin actividades registradas aún</small>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:140px;">Horario</th>
                        <th>Actividad</th>
                        <th>Lugar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sesion['cronograma'] as $item): ?>
                    <tr>
                        <td>
                            <span class="badge bg-primary-subtle text-primary fw-semibold">
                                <?= substr($item['hora_i'], 0, 5) ?> — <?= substr($item['hora_f'], 0, 5) ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($item['nombre'])): ?>
                                <strong><?= e($item['nombre']) ?></strong>
                                <?php if (!empty($item['descripcion'])): ?>
                                <small class="d-block text-muted mt-1"><?= e($item['descripcion']) ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted"><?= e($item['descripcion']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?= !empty($item['lugar']) ? e($item['lugar']) : '—' ?>
                            </small>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

</div>
<?php endforeach; ?>

<?php endif; ?>
