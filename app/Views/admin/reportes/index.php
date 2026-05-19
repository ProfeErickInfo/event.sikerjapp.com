<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Reportes y Estadísticas</h4>
</div>

<!-- Totales generales -->
<?php
$totalInscritos  = array_sum(array_column($resumen, 'total_inscritos'));
$totalAprobados  = array_sum(array_column($resumen, 'aprobados'));
$totalAsistentes = array_sum(array_column($resumen, 'asistentes'));
$totalRecaudado  = array_sum(array_column($resumen, 'recaudado'));
?>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <i class="bi bi-calendar-event text-primary fs-3 d-block mb-1"></i>
                <h4 class="fw-bold mb-0"><?= count($resumen) ?></h4>
                <small class="text-muted">Eventos</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <i class="bi bi-people text-success fs-3 d-block mb-1"></i>
                <h4 class="fw-bold mb-0"><?= $totalInscritos ?></h4>
                <small class="text-muted">Total Inscritos</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <i class="bi bi-qr-code-scan text-info fs-3 d-block mb-1"></i>
                <h4 class="fw-bold mb-0"><?= $totalAsistentes ?></h4>
                <small class="text-muted">Total Asistentes</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <i class="bi bi-cash-coin text-warning fs-3 d-block mb-1"></i>
                <h4 class="fw-bold mb-0">$<?= number_format($totalRecaudado, 0, ',', '.') ?></h4>
                <small class="text-muted">Total Recaudado</small>
            </div>
        </div>
    </div>
</div>

<!-- Tabla por evento -->
<div class="card">
    <div class="card-header py-3">
        <i class="bi bi-bar-chart me-2"></i>Resumen por Evento
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Evento</th>
                        <th class="text-center">Inscritos</th>
                        <th class="text-center">Aprobados</th>
                        <th class="text-center">Asistentes</th>
                        <th class="text-center">Recaudado</th>
                        <th class="text-center">Reportes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resumen as $ev): ?>
                    <tr>
                        <td>
                            <strong><?= e($ev['nombre_corto']) ?></strong>
                            <small class="d-block text-muted"><?= formatDate($ev['fecha']) ?></small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary-subtle text-primary"><?= $ev['total_inscritos'] ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success"><?= $ev['aprobados'] ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info-subtle text-info"><?= $ev['asistentes'] ?></span>
                        </td>
                        <td class="text-center">
                            <strong class="text-success">$<?= number_format($ev['recaudado'], 0, ',', '.') ?></strong>
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="<?= url('admin/reportes/' . $ev['id']) ?>"
                                   class="btn btn-sm btn-outline-primary" title="Ver detalle">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= url('admin/reportes/' . $ev['id'] . '/pdf/inscritos') ?>"
                                   target="_blank" class="btn btn-sm btn-outline-danger" title="PDF Inscritos">
                                    <i class="bi bi-people"></i>
                                </a>
                                <a href="<?= url('admin/reportes/' . $ev['id'] . '/pdf/pagos') ?>"
                                   target="_blank" class="btn btn-sm btn-outline-warning" title="PDF Pagos">
                                    <i class="bi bi-credit-card"></i>
                                </a>
                                <a href="<?= url('admin/reportes/' . $ev['id'] . '/pdf/asistencia') ?>"
                                   target="_blank" class="btn btn-sm btn-outline-info" title="PDF Asistencia">
                                    <i class="bi bi-qr-code-scan"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
