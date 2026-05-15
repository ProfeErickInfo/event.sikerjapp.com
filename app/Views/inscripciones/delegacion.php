<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= url('events') ?>">Eventos</a></li>
        <li class="breadcrumb-item"><a href="<?= url('events/' . $evento['id']) ?>"><?= e(truncate($evento['nombre_corto'], 30)) ?></a></li>
        <li class="breadcrumb-item active">Mi Delegación</li>
    </ol>
</nav>

<div class="row g-4">

    <!-- Info delegación + evento -->
    <div class="col-12">
        <div class="card" style="border-left:4px solid #2d3a5e;">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-0"><i class="bi bi-people me-2"></i><?= e($delegacion['nombre']) ?></h6>
                        <small class="text-muted">Representante: <?= e($delegacion['representante']) ?></small>
                    </div>
                 <div class="col-md-6 text-md-end mt-2 mt-md-0">
    <h6 class="fw-bold mb-0"><?= e($evento['nombre_corto']) ?></h6>
    <small class="text-muted"><?= formatDate($evento['fecha']) ?> — <?= formatDate($evento['fecha2']) ?></small>
    <?php
        $hayInscritos = array_filter($participantes, fn($p) => $p['id_inscripcion'] && $p['inscripcion_estado'] != 2);
    ?>
    <?php if (!empty($hayInscritos)): ?>
    <a href="<?= url('events/' . $evento['id'] . '/pago') ?>"
       class="btn btn-warning btn-sm mt-2">
        <i class="bi bi-credit-card me-2"></i>Subir Comprobante de Pago
    </a>
    <?php endif; ?>
</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de participantes -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <span><i class="bi bi-person-lines-fill me-2"></i>Participantes (<?= count($participantes) ?>)</span>
            </div>

            <?php if (empty($participantes)): ?>
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-people fs-1 d-block mb-2"></i>
                No hay participantes. Agrégalos usando el formulario.
            </div>
            <?php else: ?>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Documento</th>
                                <th>Estado</th>
                                <th>Credencial</th>
                                <th style="width:120px;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($participantes as $p): ?>
                            <tr>
                                <td>
                                    <strong><?= e($p['nombre']) ?></strong>
                                    <?php if (!empty($p['email'])): ?>
                                    <small class="d-block text-muted"><?= e($p['email']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><small><?= e($p['documento']) ?></small></td>
                                <td>
                                    <?php if ($p['id_inscripcion'] && $p['inscripcion_estado'] != 2): ?>
    <?php
    $estados = [0=>'Pendiente', 1=>'Activa'];
    $clases  = [0=>'warning',   1=>'success'];
    $est     = $p['inscripcion_estado'];
    ?>
    <span class="badge bg-<?= $clases[$est] ?>">
        <?= $estados[$est] ?>
    </span>
<?php else: ?>
    <span class="badge bg-secondary-subtle text-secondary">Sin inscribir</span>
<?php endif; ?>
                                </td>
                                <td>
    <?php if ($p['id_inscripcion'] && $p['inscripcion_estado'] == 1): ?>
        <?php
        require_once ROOT_PATH . '/app/Models/CredencialModel.php';
        $credModel = new CredencialModel();
        $credAprobada = $credModel->isAprobada($p['id'], $evento['id'], 2);
        ?>
        <?php if ($credAprobada): ?>
        <a href="<?= url('credential/participante/' . $p['id'] . '/' . $evento['id']) ?>"
           class="btn btn-sm btn-success" target="_blank" title="Descargar credencial">
            <i class="bi bi-person-badge"></i>
        </a>
        <?php else: ?>
        <small class="text-muted">Pendiente</small>
        <?php endif; ?>
    <?php else: ?>
        <small class="text-muted">—</small>
    <?php endif; ?>
</td>
                                <td>
    <div class="d-flex gap-1">
        <a href="<?= url('delegacion/participante/editar/' . $p['id'] . '?evento=' . $evento['id']) ?>"
           class="btn btn-sm btn-outline-warning" title="Editar">
            <i class="bi bi-pencil"></i>
        </a>
        <?php if (!$p['id_inscripcion'] || $p['inscripcion_estado'] == 2): ?>
            <form action="<?= url('delegacion/participante/eliminar/' . $p['id']) ?>"
                  method="POST"
                  onsubmit="return confirm('¿Eliminar participante?')">
                <input type="hidden" name="id_evento" value="<?= $evento['id'] ?>">
                <button class="btn btn-sm btn-outline-danger" title="Eliminar">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        <?php elseif ($p['inscripcion_estado'] == 0): ?>
            <form action="<?= url('inscripciones/cancelar/participante/' . $p['id_inscripcion']) ?>"
                  method="POST"
                  onsubmit="return confirm('¿Cancelar inscripción?')">
                <input type="hidden" name="id_evento" value="<?= $evento['id'] ?>">
                <button class="btn btn-sm btn-outline-danger" title="Cancelar inscripción">
                    <i class="bi bi-x-circle"></i>
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
            </div>

            <!-- Inscripción masiva -->
          <?php $sinInscribir = array_filter($participantes, fn($p) => !$p['id_inscripcion'] || $p['inscripcion_estado'] == 2); ?>
            <?php if (!empty($sinInscribir)): ?>
            <div class="card-footer py-3">
                <form action="<?= url('delegacion/inscribir/masivo') ?>" method="POST">
                    <input type="hidden" name="id_evento" value="<?= $evento['id'] ?>">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($sinInscribir as $p): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="participantes[]" value="<?= $p['id'] ?>"
                                       id="p<?= $p['id'] ?>" checked>
                                <label class="form-check-label small" for="p<?= $p['id'] ?>">
                                    <?= e(truncate($p['nombre'], 20)) ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm ms-auto">
                            <i class="bi bi-check-all me-1"></i>Inscribir Seleccionados
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>

    <!-- Agregar participante -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-person-plus me-2"></i>Agregar Participante
            </div>
            <div class="card-body p-3">
                <form action="<?= url('delegacion/participante/agregar') ?>" method="POST">
                    <input type="hidden" name="id_evento" value="<?= $evento['id'] ?>">

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Nombre completo *</label>
                        <input type="text" name="nombre" class="form-control form-control-sm" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Tipo documento</label>
                        <select name="tipo_doc" class="form-select form-select-sm">
                            <?php foreach ($tipoDocs as $td): ?>
                            <option value="<?= $td['id'] ?>"><?= e($td['descripcion']) ?> (<?= e($td['sigla']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Número documento *</label>
                        <input type="text" name="documento" class="form-control form-control-sm" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Teléfono</label>
                        <input type="tel" name="telefono" class="form-control form-control-sm">
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control form-control-sm">
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Fecha nacimiento</label>
                        <input type="date" name="fecha_nac" class="form-control form-control-sm">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Género</label>
                        <select name="genero" class="form-select form-select-sm">
                            <option value="1">Masculino</option>
                            <option value="2">Femenino</option>
                            <option value="3">Otro</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-plus-circle me-1"></i>Agregar
                    </button>

                </form>
            </div>
        </div>
    </div>

</div>
