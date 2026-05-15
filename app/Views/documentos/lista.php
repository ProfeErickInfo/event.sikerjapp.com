<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= url('events') ?>">Eventos</a></li>
        <li class="breadcrumb-item">
            <a href="<?= url('events/' . $evento['id']) ?>"><?= e(truncate($evento['nombre_corto'], 30)) ?></a>
        </li>
        <li class="breadcrumb-item active">Documentos</li>
    </ol>
</nav>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Documentos del Evento</h4>
        <small class="text-muted"><?= e($evento['nombre_corto']) ?></small>
    </div>
    <?php if (Session::isLoggedIn() && in_array(Session::user()['tipoU'], [0, 3])): ?>
    <a href="<?= url('admin/documentos/' . $evento['id']) ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-upload me-1"></i>Administrar
    </a>
    <?php endif; ?>
</div>

<?php if (empty($documentos)): ?>
<div class="text-center py-5">
    <i class="bi bi-file-earmark-x fs-1 text-muted d-block mb-3"></i>
    <h5 class="text-muted">No hay documentos disponibles aún</h5>
    <p class="text-muted">El organizador publicará los documentos próximamente.</p>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($documentos as $doc): ?>
    <?php
        $ext = strtolower(pathinfo($doc['archivo'], PATHINFO_EXTENSION));
        $iconos = [
            'pdf'  => ['bi-file-pdf',       'text-danger'],
            'doc'  => ['bi-file-word',       'text-primary'],
            'docx' => ['bi-file-word',       'text-primary'],
            'xls'  => ['bi-file-excel',      'text-success'],
            'xlsx' => ['bi-file-excel',      'text-success'],
            'ppt'  => ['bi-file-ppt',        'text-warning'],
            'pptx' => ['bi-file-ppt',        'text-warning'],
            'jpg'  => ['bi-file-image',      'text-info'],
            'jpeg' => ['bi-file-image',      'text-info'],
            'png'  => ['bi-file-image',      'text-info'],
            'zip'  => ['bi-file-zip',        'text-secondary'],
        ];
        [$icono, $color] = $iconos[$ext] ?? ['bi-file-earmark', 'text-muted'];
    ?>
    <div class="col-md-4 col-sm-6">
        <div class="card h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <i class="bi <?= $icono ?> <?= $color ?> fs-2 flex-shrink-0"></i>
                <div class="flex-grow-1 overflow-hidden">
                    <strong class="d-block text-truncate"><?= e($doc['nombre']) ?></strong>
                    <small class="text-muted text-uppercase"><?= $ext ?></small>
                    <small class="text-muted d-block"><?= formatDate(substr($doc['fecha_subida'], 0, 10)) ?></small>
                </div>
                <a href="<?= url('uploads/' . $doc['archivo']) ?>"
                   class="btn btn-sm btn-outline-primary flex-shrink-0"
                   target="_blank" download title="Descargar">
                    <i class="bi bi-download"></i>
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
