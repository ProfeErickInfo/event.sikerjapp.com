<!-- Encabezado -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Inscripciones</h4>
    <?php if ($idEvento && Session::user()['tipoU'] == 4): ?>
    <a href="<?= url('admin/inscripciones/nueva/' . $idEvento) ?>"
       class="btn btn-success btn-sm">
        <i class="bi bi-person-plus me-1"></i>Inscribir Persona
    </a>
    <?php endif; ?>
</div>

<!-- Selector de evento -->
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= url('admin/inscripciones') ?>" class="d-flex gap-3 align-items-end">
            <div class="flex-grow-1">
                <label class="form-label small fw-semibold mb-1">Seleccionar Evento</label>
                <select name="evento" class="form-select">
                    <option value="">— Selecciona un evento —</option>
                    <?php foreach ($eventos as $ev): ?>
                    <option value="<?= $ev['id'] ?>" <?= $idEvento == $ev['id'] ? 'selected' : '' ?>>
                        <?= e($ev['nombre_corto']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search me-1"></i>Ver
            </button>
        </form>
    </div>
</div>

<?php if ($idEvento && $eventoActual): ?>

<!-- Estadísticas -->
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['Total',       $stats['total']       ?? 0, 'primary',  'people'],
        ['Pendientes',  $stats['pendientes']  ?? 0, 'warning',  'clock'],
        ['Activas',     $stats['activas']     ?? 0, 'success',  'check-circle'],
        ['Individuales',$stats['individuales']?? 0, 'info',     'person'],
        ['Delegaciones',$stats['delegaciones']?? 0, 'secondary','people-fill'],
    ];
    foreach ($cards as [$label, $val, $color, $icon]):
    ?>
    <div class="col">
        <div class="card text-center">
            <div class="card-body py-3">
                <i class="bi bi-<?= $icon ?> text-<?= $color ?> fs-4 d-block mb-1"></i>
                <h4 class="fw-bold mb-0"><?= $val ?></h4>
                <small class="text-muted"><?= $label ?></small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Tabla de inscripciones -->
<div class="card">
    <div class="card-header py-3">
        <i class="bi bi-list-check me-2"></i>
        Inscripciones — <?= e($eventoActual['nombre_corto']) ?>
    </div>
    <div class="card-body p-0">
        <?php if (empty($inscripciones)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            No hay inscripciones para este evento
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inscripciones as $ins): ?>
                    <tr>
                        <td class="text-muted small"><?= $ins['id'] ?></td>
                        <td>
                            <?php if ($ins['tipo'] == 1): ?>
                                <strong><?= e($ins['nombre'] ?? $ins['usuario_nombre']) ?></strong>
                                <small class="d-block text-muted"><?= e($ins['usuario_email']) ?></small>
                            <?php else: ?>
                                <strong><?= e($ins['participante_nombre']) ?></strong>
                                <small class="d-block text-muted">
                                    <i class="bi bi-people me-1"></i><?= e($ins['delegacion_nombre']) ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($ins['tipo'] == 1): ?>
                                <span class="badge bg-info-subtle text-info">Individual</span>
                            <?php else: ?>
                                <span class="badge bg-primary-subtle text-primary">Delegación</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($ins['valor'] > 0): ?>
                                <small>$<?= number_format($ins['valor'], 0, ',', '.') ?></small>
                            <?php else: ?>
                                <small class="text-muted">—</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $estados = [0=>'Pendiente', 1=>'Activa', 2=>'Cancelada'];
                            $clases  = [0=>'warning',   1=>'success', 2=>'danger'];
                            $est     = $ins['estado'];
                            ?>
                            <span class="badge bg-<?= $clases[$est] ?>">
                                <?= $estados[$est] ?>
                            </span>
                        </td>
                        <td><small><?= formatDateTime($ins['fec_inscripcion']) ?></small></td>
                        <td>
                            <div class="d-flex gap-1">
                                <?php if ($ins['estado'] == 0): ?>
                                <form action="<?= url('admin/inscripciones/aprobar/' . $ins['id']) ?>" method="POST">
                                    <input type="hidden" name="id_evento" value="<?= $idEvento ?>">
                                    <button class="btn btn-sm btn-success" title="Aprobar">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <?php if ($ins['estado'] != 2): ?>
                                <form action="<?= url('admin/inscripciones/cancelar/' . $ins['id']) ?>" method="POST"
                                      onsubmit="return confirm('¿Cancelar esta inscripción?')">
                                    <input type="hidden" name="id_evento" value="<?= $idEvento ?>">
                                    <button class="btn btn-sm btn-outline-danger" title="Cancelar">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php endif; ?>
