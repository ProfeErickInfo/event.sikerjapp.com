<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= url('admin/events') ?>">Eventos</a></li>
        <li class="breadcrumb-item active">Credenciales</li>
    </ol>
</nav>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Credenciales</h4>
        <small class="text-muted"><?= e($evento['nombre_corto']) ?></small>
    </div>
</div>

<div class="card">
    <div class="card-header py-3">
        <i class="bi bi-id-card me-2"></i>
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
                             <th>Credencial</th>
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
                            <?php if ($item['credencial_aprobada']): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check me-1"></i>Aprobada
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <!-- Aprobar credencial -->
                                <?php if (!$item['credencial_aprobada']): ?>
<form action="<?= url('admin/credenciales/aprobar') ?>" method="POST">
    <input type="hidden" name="id_evento" value="<?= $evento['id'] ?>">
    <input type="hidden" name="id_usuario"
           value="<?= $item['tipo'] == 1 ? $item['id_usuario'] : $item['id_participante'] ?>">
    <input type="hidden" name="tipo" value="<?= $item['tipo'] ?>">
    <button class="btn btn-sm btn-success" title="Aprobar credencial">
        <i class="bi bi-check-lg me-1"></i>Aprobar
    </button>
</form>
<?php else: ?>
<form action="<?= url('admin/credenciales/revocar') ?>" method="POST"
      onsubmit="return confirm('¿Revocar esta credencial?')">
    <input type="hidden" name="id_evento" value="<?= $evento['id'] ?>">
    <input type="hidden" name="id_usuario"
           value="<?= $item['tipo'] == 1 ? $item['id_usuario'] : $item['id_participante'] ?>">
    <input type="hidden" name="tipo" value="<?= $item['tipo'] ?>">
    <button class="btn btn-sm btn-warning" title="Revocar credencial">
        <i class="bi bi-arrow-counterclockwise me-1"></i>Revocar
    </button>
</form>
<?php endif; ?>

                                <!-- Descargar credencial -->
                                <?php if ($item['credencial_aprobada']): ?>
                                    <?php if ($item['tipo'] == 1): ?>
                                    <a href="<?= url('credential/' . $item['id_usuario'] . '/' . $evento['id']) ?>"
                                       class="btn btn-sm btn-outline-primary" target="_blank" title="Ver credencial">
                                        <i class="bi bi-person-badge me-1"></i>Ver
                                    </a>
                                    <?php else: ?>
                                    <a href="<?= url('credential/participante/' . $item['id_participante'] . '/' . $evento['id']) ?>"
                                       class="btn btn-sm btn-outline-primary" target="_blank" title="Ver credencial">
                                        <i class="bi bi-person-badge me-1"></i>Ver
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
