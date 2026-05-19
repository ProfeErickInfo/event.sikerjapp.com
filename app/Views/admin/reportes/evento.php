<?php
/**
 * @var array $evento Datos del evento actual
 * @var array $resumen Resumen estadístico de inscripciones
 * @var array $estadoPagos Estado de los pagos del evento
 * @var array|null $asistencia Datos de asistencia por día/sesión
 * @var array|null $porDeleg Inscritos agrupados por delegación
 */
?>
<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <?php if (Session::user()['tipoU'] == 1): ?>
        <li class="breadcrumb-item"><a href="<?= url('admin/reportes') ?>">Reportes</a></li>
        <?php endif; ?>
        <li class="breadcrumb-item active"><?= e(truncate($evento['nombre_corto'], 30)) ?></li>
    </ol>
</nav>

<div class="mb-4">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <div>
            <h4 class="fw-bold mb-1">Reportes del Evento</h4>
            <small class="text-muted"><?= e($evento['nombre_corto']) ?></small>
        </div>
    </div>
    <!-- Botones PDF — en móvil se muestran en fila completa -->
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= url('admin/reportes/' . $evento['id'] . '/pdf/inscritos') ?>"
           target="_blank" class="btn btn-sm btn-outline-danger flex-fill text-center">
            <i class="bi bi-people me-1"></i>
            <span class="d-none d-md-inline">PDF </span>Inscritos
        </a>
        <a href="<?= url('admin/reportes/' . $evento['id'] . '/pdf/pagos') ?>"
           target="_blank" class="btn btn-sm btn-outline-warning flex-fill text-center">
            <i class="bi bi-credit-card me-1"></i>
            <span class="d-none d-md-inline">PDF </span>Pagos
        </a>
        <a href="<?= url('admin/reportes/' . $evento['id'] . '/pdf/asistencia') ?>"
           target="_blank" class="btn btn-sm btn-outline-info flex-fill text-center">
            <i class="bi bi-qr-code-scan me-1"></i>
            <span class="d-none d-md-inline">PDF </span>Asistencia
        </a>
    </div>
</div>

<!-- Tarjetas resumen -->
<div class="row g-3 mb-4">
    <div class="col-md-2 col-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <i class="bi bi-people text-primary fs-3 d-block mb-1"></i>
                <h4 class="fw-bold mb-0"><?= $resumen['total_inscritos'] ?? 0 ?></h4>
                <small class="text-muted">Inscritos</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <i class="bi bi-check-circle text-success fs-3 d-block mb-1"></i>
                <h4 class="fw-bold mb-0"><?= $resumen['aprobados'] ?? 0 ?></h4>
                <small class="text-muted">Aprobados</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <i class="bi bi-clock text-warning fs-3 d-block mb-1"></i>
                <h4 class="fw-bold mb-0"><?= $resumen['pendientes'] ?? 0 ?></h4>
                <small class="text-muted">Pendientes</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <i class="bi bi-qr-code-scan text-info fs-3 d-block mb-1"></i>
                <h4 class="fw-bold mb-0"><?= $resumen['asistentes'] ?? 0 ?></h4>
                <small class="text-muted">Asistentes</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <i class="bi bi-cash-coin text-success fs-3 d-block mb-1"></i>
                <h4 class="fw-bold mb-0">$<?= number_format($resumen['recaudado'] ?? 0, 0, ',', '.') ?></h4>
                <small class="text-muted">Recaudado</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <i class="bi bi-hourglass text-warning fs-3 d-block mb-1"></i>
                <h4 class="fw-bold mb-0"><?= $estadoPagos['pendientes'] ?? 0 ?></h4>
                <small class="text-muted">Pagos Pend.</small>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="row g-4 mb-4">

    <!-- Gráfico torta — Estado inscripciones -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header py-3">
                <i class="bi bi-pie-chart me-2"></i>Estado Inscripciones
            </div>
            <div class="card-body">
                <canvas id="chartInscripciones" height="220"></canvas>
            </div>
        </div>
    </div>

    <!-- Gráfico torta — Estado pagos -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header py-3">
                <i class="bi bi-pie-chart me-2"></i>Estado Pagos
            </div>
            <div class="card-body">
                <canvas id="chartPagos" height="220"></canvas>
            </div>
        </div>
    </div>

    <!-- Gráfico barras — Tipo inscripciones -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header py-3">
                <i class="bi bi-bar-chart me-2"></i>Tipo de Inscritos
            </div>
            <div class="card-body">
                <canvas id="chartTipo" height="220"></canvas>
            </div>
        </div>
    </div>

</div>

<!-- Gráfico asistencia por día -->
<?php if (!empty($asistencia)): ?>
<div class="card mb-4">
    <div class="card-header py-3">
        <i class="bi bi-graph-up me-2"></i>Asistencia por Día/Sesión
    </div>
    <div class="card-body">
        <canvas id="chartAsistencia" height="100"></canvas>
    </div>
</div>
<?php endif; ?>

<!-- Tabla por delegación -->
<?php if (!empty($porDeleg)): ?>
<div class="card mb-4">
    <div class="card-header py-3">
        <i class="bi bi-people me-2"></i>Inscritos por Delegación
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Delegación</th>
                        <th class="text-center">Participantes</th>
                        <th class="text-center">Aprobados</th>
                        <th class="text-center">Valor Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($porDeleg as $d): ?>
                    <tr>
                        <td><strong><?= e($d['delegacion'] ?? 'Sin delegación') ?></strong></td>
                        <td class="text-center">
                            <span class="badge bg-primary-subtle text-primary"><?= $d['total'] ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success"><?= $d['aprobados'] ?></span>
                        </td>
                        <td class="text-center">
                            <strong>$<?= number_format($d['valor_total'], 0, ',', '.') ?></strong>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const colores = {
    azul:    '#3b5bdb',
    verde:   '#10b981',
    amarillo:'#f59e0b',
    rojo:    '#ef4444',
    gris:    '#9ca3af',
    celeste: '#0ea5e9',
};

// Gráfico torta — Inscripciones
new Chart(document.getElementById('chartInscripciones'), {
    type: 'doughnut',
    data: {
        labels: ['Aprobadas', 'Pendientes'],
        datasets: [{
            data: [<?= $resumen['aprobados'] ?? 0 ?>, <?= $resumen['pendientes'] ?? 0 ?>],
            backgroundColor: [colores.verde, colores.amarillo],
            borderWidth: 2,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
    }
});

// Gráfico torta — Pagos
new Chart(document.getElementById('chartPagos'), {
    type: 'doughnut',
    data: {
        labels: ['Aprobados', 'Pendientes', 'Rechazados'],
        datasets: [{
            data: [
                <?= $estadoPagos['aprobados'] ?? 0 ?>,
                <?= $estadoPagos['pendientes'] ?? 0 ?>,
                <?= $estadoPagos['rechazados'] ?? 0 ?>
            ],
            backgroundColor: [colores.verde, colores.amarillo, colores.rojo],
            borderWidth: 2,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
    }
});

// Gráfico barras — Tipo inscritos
new Chart(document.getElementById('chartTipo'), {
    type: 'bar',
    data: {
        labels: ['Individuales', 'Delegaciones'],
        datasets: [{
            label: 'Inscritos',
            data: [<?= $resumen['individuales'] ?? 0 ?>, <?= $resumen['delegaciones'] ?? 0 ?>],
            backgroundColor: [colores.azul, colores.celeste],
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

// Gráfico barras — Asistencia por día
<?php if (!empty($asistencia)): ?>
const asistenciaLabels = <?= json_encode(array_map(fn($a) => $a['fecha'] . ' - ' . $a['sesion'], $asistencia)) ?>;
const asistenciaData   = <?= json_encode(array_column($asistencia, 'total')) ?>;

new Chart(document.getElementById('chartAsistencia'), {
    type: 'bar',
    data: {
        labels: asistenciaLabels,
        datasets: [{
            label: 'Asistentes',
            data: asistenciaData,
            backgroundColor: colores.azul,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});
<?php endif; ?>
</script>
