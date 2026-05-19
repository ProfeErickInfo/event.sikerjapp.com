<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= url('admin/events') ?>">Eventos</a></li>
        <li class="breadcrumb-item active">Certificados</li>
    </ol>
</nav>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Certificados</h4>
        <small class="text-muted"><?= e($evento['nombre_corto']) ?></small>
    </div>
    <a href="<?= url('admin/certificado/' . $evento['id']) ?>"
       class="btn btn-outline-warning btn-sm">
        <i class="bi bi-gear me-1"></i>Configurar plantilla
    </a>
</div>

<?php if (empty($evento['cert_plantilla'])): ?>
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    Este evento no tiene plantilla de certificado configurada.
    <a href="<?= url('admin/certificado/' . $evento['id']) ?>" class="alert-link">Configúrala aquí</a>.
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header py-3">
        <i class="bi bi-award me-2"></i>
        Inscritos — <?= count($lista) ?> registros
    </div>
    <div class="card-body p-0">
        <?php if (empty($lista)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-people fs-1 d-block mb-2"></i>
            No hay inscritos aprobados para este evento
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Delegación</th>
                        <th>Pago</th>
                        <th>Certificado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lista as $item): ?>
                    <tr>
                        <td>
                            <strong><?= e($item['nombre']) ?></strong>
                            <?php if (!empty($item['email'])): ?>
                            <small class="d-block text-muted"><?= e($item['email']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($item['tipo'] == 1): ?>
                                <span class="badge bg-info-subtle text-info">Individual</span>
                            <?php else: ?>
                                <span class="badge bg-primary-subtle text-primary">Delegación</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?= !empty($item['delegacion_nombre']) ? e($item['delegacion_nombre']) : '—' ?>
                            </small>
                        </td>
                        <td>
                            <?php
                            $pagoEstados = [0=>'Sin pago', 1=>'Aprobado', 2=>'Rechazado'];
                            $pagoClases  = [0=>'secondary', 1=>'success', 2=>'danger'];
                            $pest        = $item['pago_estado'] ?? 0;
                            ?>
                            <span class="badge bg-<?= $pagoClases[$pest] ?>">
                                <?= $pagoEstados[$pest] ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($item['certificado_aprobado']): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check me-1"></i>Aprobado
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <!-- Aprobar / Revocar certificado -->
                                <?php if (!$item['certificado_aprobado']): ?>
                                <form action="<?= url('admin/certificados/aprobar') ?>" method="POST">
                                    <input type="hidden" name="id_evento" value="<?= $evento['id'] ?>">
                                    <input type="hidden" name="id_usuario"
                                           value="<?= $item['tipo'] == 1 ? $item['id_usuario'] : $item['id_participante'] ?>">
                                    <input type="hidden" name="tipo" value="<?= $item['tipo'] ?>">
                                    <button class="btn btn-sm btn-success" title="Aprobar certificado">
                                        <i class="bi bi-check-lg me-1"></i>Aprobar
                                    </button>
                                </form>
                                <?php else: ?>
                                <form action="<?= url('admin/certificados/revocar') ?>" method="POST"
                                      onsubmit="return confirm('¿Revocar este certificado?')">
                                    <input type="hidden" name="id_evento" value="<?= $evento['id'] ?>">
                                    <input type="hidden" name="id_usuario"
                                           value="<?= $item['tipo'] == 1 ? $item['id_usuario'] : $item['id_participante'] ?>">
                                    <input type="hidden" name="tipo" value="<?= $item['tipo'] ?>">
                                    <button class="btn btn-sm btn-warning" title="Revocar certificado">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Revocar
                                    </button>
                                </form>
                                <?php endif; ?>

                                <!-- Ver certificado -->
                                <?php if ($item['certificado_aprobado'] && !empty($evento['cert_plantilla'])): ?>
                                    <?php if ($item['tipo'] == 1): ?>
                                    <a href="<?= url('certificate/' . $item['id_usuario'] . '/' . $evento['id']) ?>"
                                       class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="bi bi-award"></i>
                                    </a>
                                    <?php else: ?>
                                    <a href="<?= url('certificate/participante/' . $item['id_participante'] . '/' . $evento['id']) ?>"
                                       class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="bi bi-award"></i>
                                    </a>
                                    <?php endif; ?>
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