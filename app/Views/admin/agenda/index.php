<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= url('admin/events') ?>">Eventos</a></li>
        <li class="breadcrumb-item active">Agenda — <?= e(truncate($evento['nombre_corto'], 30)) ?></li>
    </ol>
</nav>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Administrar Agenda</h4>
        <small class="text-muted"><?= e($evento['nombre_corto']) ?></small>
    </div>
    <a href="<?= url('events/' . $evento['id'] . '/agenda') ?>" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-eye me-1"></i>Vista pública
    </a>
</div>

<div class="row g-4">

    <!-- Columna izquierda: sesiones + cronograma -->
    <div class="col-lg-8">

        <?php if (empty($sesiones)): ?>
        <div class="text-center py-5 text-muted card">
            <div class="card-body">
                <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                No hay sesiones. Crea la primera usando el formulario.
            </div>
        </div>
        <?php endif; ?>

        <?php foreach ($sesiones as $sesion): ?>
        <div class="card mb-4">

            <!-- Encabezado sesión -->
            <div class="card-header py-3 d-flex justify-content-between align-items-center"
                 style="background:linear-gradient(135deg,#1a2035,#2d3a5e);color:white;">
                <div>
                    <strong><?= e($sesion['nombre']) ?></strong>
                    <small class="d-block" style="opacity:0.8;">
                        <?= formatDate($sesion['fecha']) ?>
                        <?php if (!empty($sesion['lugar'])): ?>· <?= e($sesion['lugar']) ?><?php endif; ?>
                    </small>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge bg-light text-dark">Sesión <?= $sesion['orden'] ?></span>
                    <!-- Editar sesión -->
                    <button class="btn btn-sm btn-light"
                            onclick="editarSesion(<?= htmlspecialchars(json_encode($sesion)) ?>)"
                            title="Editar sesión">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <!-- Eliminar sesión -->
                    <form action="<?= url('admin/agenda/sesion/delete/' . $sesion['id']) ?>"
                          method="POST"
                          onsubmit="return confirm('¿Eliminar sesión y todo su cronograma?')">
                        <input type="hidden" name="id_evento" value="<?= $evento['id'] ?>">
                        <button class="btn btn-sm btn-danger" title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Cronograma de la sesión -->
            <div class="card-body p-0">
                <?php if (empty($sesion['cronograma'])): ?>
                <div class="text-center py-3 text-muted small">
                    Sin actividades. Agrégalas abajo.
                </div>
                <?php else: ?>
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:130px;">Horario</th>
                            <th>Actividad</th>
                            <th>Lugar</th>
                            <th style="width:60px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sesion['cronograma'] as $item): ?>
                        <tr>
                            <td>
                                <small class="fw-semibold text-primary">
                                    <?= substr($item['hora_i'], 0, 5) ?> — <?= substr($item['hora_f'], 0, 5) ?>
                                </small>
                            </td>
                            <td>
                                <?php if (!empty($item['nombre'])): ?>
                                    <strong><?= e($item['nombre']) ?></strong>
                                <?php endif; ?>
                                <?php if (!empty($item['descripcion'])): ?>
                                    <small class="d-block text-muted"><?= e(truncate($item['descripcion'], 60)) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><small class="text-muted"><?= e($item['lugar'] ?? '—') ?></small></td>
                            <td>
                                <form action="<?= url('admin/agenda/cronograma/delete/' . $item['id']) ?>"
                                      method="POST"
                                      onsubmit="return confirm('¿Eliminar este ítem?')">
                                    <input type="hidden" name="id_evento" value="<?= $evento['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Agregar ítem al cronograma -->
            <div class="card-footer bg-light py-3">
                <form action="<?= url('admin/agenda/cronograma/store') ?>" method="POST">
                    <input type="hidden" name="id_evento" value="<?= $evento['id'] ?>">
                    <input type="hidden" name="id_sesion" value="<?= $sesion['id'] ?>">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold mb-1">Nombre actividad</label>
                            <input type="text" name="nombre" class="form-control form-control-sm"
                                   placeholder="Ej: Charla magistral">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold mb-1">Descripción</label>
                            <input type="text" name="descripcion" class="form-control form-control-sm"
                                   placeholder="Ej: Charla sobre fuerza explosiva por Dr. Carlos">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small fw-semibold mb-1">Inicio</label>
                            <input type="time" name="hora_i" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small fw-semibold mb-1">Fin</label>
                            <input type="time" name="hora_f" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">Lugar</label>
                            <input type="text" name="lugar" class="form-control form-control-sm"
                                   placeholder="Sala A">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
        <?php endforeach; ?>

    </div>

    <!-- Columna derecha: crear sesión -->
    <div class="col-lg-4">
        <div class="card sticky-top" style="top:80px;">
            <div class="card-header py-3">
                <i class="bi bi-plus-circle me-2"></i>Nueva Sesión
            </div>
            <div class="card-body p-3">
                <form action="<?= url('admin/agenda/sesion/store') ?>" method="POST">
                    <input type="hidden" name="id_evento" value="<?= $evento['id'] ?>">

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Nombre *</label>
                        <input type="text" name="nombre" class="form-control form-control-sm"
                               placeholder="Ej: Trabajo de Fuerza" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Fecha *</label>
                        <input type="date" name="fecha" class="form-control form-control-sm"
                               value="<?= $evento['fecha'] ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Lugar</label>
                        <input type="text" name="lugar" class="form-control form-control-sm"
                               placeholder="Ej: Salón principal">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Orden</label>
                        <input type="number" name="orden" class="form-control form-control-sm"
                               value="<?= count($sesiones) + 1 ?>" min="1">
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-plus-circle me-1"></i>Crear Sesión
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

<!-- Modal editar sesión -->
<div class="modal fade" id="modalEditarSesion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Sesión</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditarSesion" method="POST">
                <input type="hidden" name="id_evento" value="<?= $evento['id'] ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre *</label>
                        <input type="text" name="nombre" id="editNombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Fecha *</label>
                        <input type="date" name="fecha" id="editFecha" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lugar</label>
                        <input type="text" name="lugar" id="editLugar" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Orden</label>
                        <input type="number" name="orden" id="editOrden" class="form-control" min="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editarSesion(sesion) {
    document.getElementById('editNombre').value = sesion.nombre;
    document.getElementById('editFecha').value  = sesion.fecha;
    document.getElementById('editLugar').value  = sesion.lugar || '';
    document.getElementById('editOrden').value  = sesion.orden || 1;
    document.getElementById('formEditarSesion').action =
        '<?= url('admin/agenda/sesion/update/') ?>' + sesion.id;
    new bootstrap.Modal(document.getElementById('modalEditarSesion')).show();
}
</script>
