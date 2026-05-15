<!-- Encabezado -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Gestionar Eventos</h4>
        <small class="text-muted"><?= $total ?> eventos en total</small>
    </div>
    <a href="<?= url('admin/events/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Nuevo Evento
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Evento</th>
                        <th>Categoría</th>
                        <th>Fechas</th>
                        <th>Inscripción</th>
                        <th>Estado</th>
                        <th style="width:150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($eventos)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            No hay eventos registrados
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($eventos as $ev): ?>
                    <tr>
                        <td class="text-muted small"><?= $ev['id'] ?></td>
                        <td>
                            <strong><?= e($ev['nombre_corto']) ?></strong>
                            <small class="d-block text-muted">
                                Edición <?= e($ev['edicion']) ?>
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary">
                                <?= e($ev['categoria'] ?? 'General') ?>
                            </span>
                        </td>
                        <td>
                            <small class="d-block"><?= formatDate($ev['fecha']) ?></small>
                            <small class="text-muted">al <?= formatDate($ev['fecha2']) ?></small>
                        </td>
                        <td>
                            <?php if ($ev['inscripcion'] == 1): ?>
                                <span class="badge bg-success-subtle text-success">Abierta</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary">Cerrada</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($ev['estado'] == 1): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="<?= url('events/' . $ev['id']) ?>"
                                   class="btn btn-sm btn-outline-secondary" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <a href="<?= url('admin/agenda/' . $ev['id']) ?>"
                                    class="btn btn-sm btn-outline-primary" title="Agenda">
                                        <i class="bi bi-calendar3"></i>
                                </a>
                                <a href="<?= url('admin/credenciales/' . $ev['id']) ?>"
                                         class="btn btn-sm btn-outline-info" title="Credenciales">
                                          <i class="bi bi-person-badge"></i>
                                </a>

                                <a href="<?= url('admin/events/edit/' . $ev['id']) ?>"
                                   class="btn btn-sm btn-outline-warning" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="<?= url('admin/asistencia/' . $ev['id']) ?>"
   class="btn btn-sm btn-outline-success" title="Asistencia">
    <i class="bi bi-qr-code-scan"></i>
</a>
<a href="<?= url('admin/documentos/' . $ev['id']) ?>"
   class="btn btn-sm btn-outline-secondary" title="Documentos">
    <i class="bi bi-file-earmark"></i>
</a>
                                <form action="<?= url('admin/events/delete/' . $ev['id']) ?>"
                                      method="POST"
                                      onsubmit="return confirm('¿Eliminar este evento?')">
                                    <button class="btn btn-sm btn-outline-danger" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Paginación -->
<?php if ($pages > 1): ?>
<nav class="mt-4">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= url('admin/events?page=' . ($page - 1)) ?>">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
        <?php for ($i = 1; $i <= $pages; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="<?= url('admin/events?page=' . $i) ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= url('admin/events?page=' . ($page + 1)) ?>">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>
