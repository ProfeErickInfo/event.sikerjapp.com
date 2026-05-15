<!-- Encabezado -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Eventos</h4>
        <small class="text-muted"><?= $total ?> evento<?= $total !== 1 ? 's' : '' ?> encontrado<?= $total !== 1 ? 's' : '' ?></small>
    </div>
    <?php if (Session::isLoggedIn() && in_array(Session::user()['tipoU'], [0, 3])): ?>
        <a href="<?= url('admin/events/create') ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Evento
        </a>
    <?php endif; ?>
</div>

<!-- Grid de eventos -->
<?php if (empty($eventos)): ?>
    <div class="text-center py-5">
        <i class="bi bi-calendar-x fs-1 text-muted d-block mb-3"></i>
        <h5 class="text-muted">No hay eventos disponibles por el momento</h5>
        <p class="text-muted">Vuelve pronto para ver los próximos eventos.</p>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($eventos as $ev): ?>
        <div class="col-md-4 col-sm-6">
            <div class="card h-100" style="border-radius:12px;overflow:hidden;transition:transform 0.2s;"
                 onmouseover="this.style.transform='translateY(-4px)'"
                 onmouseout="this.style.transform='translateY(0)'">

                <!-- Imagen del evento -->
                <div style="height:180px;overflow:hidden;background:#1a2035;position:relative;">
                    <?php
                        $pic = $ev['pic'];
                        // Determina la ruta correcta de la imagen
                        if ($pic === 'no-disponible.jpeg' || $pic === 'NO-Aplica' || empty($pic)) {
                            $imgUrl = 'https://placehold.co/400x180/1a2035/ffffff?text=' . urlencode($ev['nombre_corto']);
                        } elseif (str_starts_with($pic, 'events/')) {
                            $imgUrl = url('uploads/' . $pic);
                        } else {
                            $imgUrl = url('uploads/events/' . $pic);
                        }
                    ?>
                    <img src="<?= e($imgUrl) ?>"
                         alt="<?= e($ev['nombre_corto']) ?>"
                         style="width:100%;height:100%;object-fit:cover;opacity:0.85;">

                    <!-- Badge categoría -->
                    <span class="badge position-absolute"
                          style="top:12px;left:12px;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);">
                        <?= e($ev['categoria'] ?? 'General') ?>
                    </span>
                </div>

                <div class="card-body d-flex flex-column p-3">
                    <h6 class="fw-bold mb-2" style="line-height:1.3;">
                        <?= e($ev['nombre_corto']) ?>
                    </h6>

                    <p class="text-muted small mb-3" style="flex:1;">
                        <?= e(truncate($ev['descripcion'], 80)) ?>
                    </p>

                    <!-- Fechas -->
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-calendar3 text-primary small"></i>
                        <small class="text-muted">
                            <?= formatDate($ev['fecha']) ?>
                            <?php if ($ev['fecha'] !== $ev['fecha2']): ?>
                                — <?= formatDate($ev['fecha2']) ?>
                            <?php endif; ?>
                        </small>
                    </div>

                    <!-- Inscripción -->
                    <div class="d-flex align-items-center justify-content-between">
                        <?php if ($ev['inscripcion'] == 1): ?>
                            <span class="badge bg-success-subtle text-success">
                                <i class="bi bi-check-circle me-1"></i>Inscripciones abiertas
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary">
                                <i class="bi bi-x-circle me-1"></i>Cerrado
                            </span>
                        <?php endif; ?>

                        <a href="<?= url('events/' . $ev['id']) ?>"
                           class="btn btn-sm btn-outline-primary">
                            Ver más <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Paginación -->
    <?php if ($pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= url('events?page=' . ($page - 1)) ?>">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
            <?php for ($i = 1; $i <= $pages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="<?= url('events?page=' . $i) ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= url('events?page=' . ($page + 1)) ?>">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
<?php endif; ?>
