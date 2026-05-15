<!-- Encabezado -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Gestión de Pagos</h4>
</div>

<!-- Selector de evento -->
<div class="card mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= url('admin/pagos') ?>" class="d-flex gap-3 align-items-end">
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
    <div class="col-md-3 col-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <i class="bi bi-clock text-warning fs-4 d-block mb-1"></i>
                <h4 class="fw-bold mb-0"><?= $stats['pendientes'] ?? 0 ?></h4>
                <small class="text-muted">Pendientes</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <i class="bi bi-check-circle text-success fs-4 d-block mb-1"></i>
                <h4 class="fw-bold mb-0"><?= $stats['aprobados'] ?? 0 ?></h4>
                <small class="text-muted">Aprobados</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <i class="bi bi-x-circle text-danger fs-4 d-block mb-1"></i>
                <h4 class="fw-bold mb-0"><?= $stats['rechazados'] ?? 0 ?></h4>
                <small class="text-muted">Rechazados</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <i class="bi bi-cash-coin text-success fs-4 d-block mb-1"></i>
                <h4 class="fw-bold mb-0">
                    $<?= number_format($stats['total_recaudado'] ?? 0, 0, ',', '.') ?>
                </h4>
                <small class="text-muted">Recaudado</small>
            </div>
        </div>
    </div>
</div>

<!-- Tabla comprobantes -->
<div class="card">
    <div class="card-header py-3">
        <i class="bi bi-credit-card me-2"></i>
        Comprobantes — <?= e($eventoActual['nombre_corto']) ?>
    </div>
    <div class="card-body p-0">
        <?php if (empty($comprobantes)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            No hay comprobantes subidos para este evento
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Remitente</th>
                        <th>Comprobante</th>
                        <th>Valor</th>
                        <th>Descripción</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comprobantes as $c): ?>
                    <tr>
                        <td class="text-muted small"><?= $c['id'] ?></td>
                        <td>
                            <?php if ($c['id_delegacion']): ?>
                                <strong><?= e($c['delegacion_nombre']) ?></strong>
                                <span class="badge bg-primary-subtle text-primary ms-1">Delegación</span>
                            <?php else: ?>
                                <strong><?= e($c['usuario_nombre']) ?></strong>
                                <small class="d-block text-muted"><?= e($c['usuario_email']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $ext     = strtolower(pathinfo($c['archivo'], PATHINFO_EXTENSION));
                            $fileUrl = url('uploads/' . $c['archivo']);
                            ?>
                            <?php if ($ext === 'pdf'): ?>
                                <a href="<?= e($fileUrl) ?>" target="_blank"
                                   class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-file-pdf me-1"></i>PDF
                                </a>
                            <?php else: ?>
                                <a href="<?= e($fileUrl) ?>" target="_blank">
                                    <img src="<?= e($fileUrl) ?>"
                                         style="height:48px;width:64px;object-fit:cover;border-radius:6px;">
                                </a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($c['valor'] > 0): ?>
                                <strong class="text-success">
                                    $<?= number_format($c['valor'], 0, ',', '.') ?>
                                </strong>
                            <?php else: ?>
                                <small class="text-muted">—</small>
                            <?php endif; ?>
                        </td>
                        <td><small class="text-muted"><?= e($c['descripcion'] ?? '—') ?></small></td>
                        <td><small><?= formatDateTime($c['fec_subida']) ?></small></td>
                        <td>
                            <?php
                            $estados = [0=>'Pendiente', 1=>'Aprobado', 2=>'Rechazado'];
                            $clases  = [0=>'warning',   1=>'success',  2=>'danger'];
                            $est     = $c['estado'];
                            ?>
                            <span class="badge bg-<?= $clases[$est] ?>">
                                <?= $estados[$est] ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <?php if ($c['estado'] == 0): ?>
                                <form action="<?= url('admin/pagos/aprobar/' . $c['id']) ?>"
                                      method="POST">
                                    <input type="hidden" name="id_evento" value="<?= $idEvento ?>">
                                    <button class="btn btn-sm btn-success" title="Aprobar">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                <form action="<?= url('admin/pagos/rechazar/' . $c['id']) ?>"
                                      method="POST"
                                      onsubmit="return confirm('¿Rechazar este comprobante?')">
                                    <input type="hidden" name="id_evento" value="<?= $idEvento ?>">
                                    <button class="btn btn-sm btn-danger" title="Rechazar">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                                <?php elseif ($c['estado'] == 2): ?>
                                    <small class="text-muted">Esperando nuevo comprobante</small>
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
