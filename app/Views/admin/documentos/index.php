<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= url('admin/events') ?>">Eventos</a></li>
        <li class="breadcrumb-item active">Documentos</li>
    </ol>
</nav>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Documentos del Evento</h4>
        <small class="text-muted"><?= e($evento['nombre_corto']) ?></small>
    </div>
    <a href="<?= url('events/' . $evento['id'] . '/documentos') ?>"
       class="btn btn-outline-primary btn-sm">
        <i class="bi bi-eye me-1"></i>Vista pública
    </a>
</div>

<div class="row g-4">

    <!-- Lista de documentos -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-file-earmark me-2"></i>
                Documentos subidos (<?= count($documentos) ?>)
            </div>
            <div class="card-body p-0">
                <?php if (empty($documentos)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-file-earmark-x fs-1 d-block mb-2"></i>
                    No hay documentos. Sube el primero usando el formulario.
                </div>
                <?php else: ?>
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Documento</th>
                            <th>Tipo</th>
                            <th>Fecha</th>
                            <th style="width:100px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): ?>
                        <?php
                            $ext = strtolower(pathinfo($doc['archivo'], PATHINFO_EXTENSION));
                            $iconos = [
                                'pdf'  => ['bi-file-pdf',   'text-danger'],
                                'doc'  => ['bi-file-word',  'text-primary'],
                                'docx' => ['bi-file-word',  'text-primary'],
                                'xls'  => ['bi-file-excel', 'text-success'],
                                'xlsx' => ['bi-file-excel', 'text-success'],
                                'ppt'  => ['bi-file-ppt',   'text-warning'],
                                'pptx' => ['bi-file-ppt',   'text-warning'],
                                'jpg'  => ['bi-file-image', 'text-info'],
                                'jpeg' => ['bi-file-image', 'text-info'],
                                'png'  => ['bi-file-image', 'text-info'],
                                'zip'  => ['bi-file-zip',   'text-secondary'],
                            ];
                            [$icono, $color] = $iconos[$ext] ?? ['bi-file-earmark', 'text-muted'];
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi <?= $icono ?> <?= $color ?> fs-4"></i>
                                    <strong><?= e($doc['nombre']) ?></strong>
                                </div>
                            </td>
                            <td><span class="badge bg-secondary"><?= strtoupper($ext) ?></span></td>
                            <td><small><?= formatDate(substr($doc['fecha_subida'], 0, 10)) ?></small></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?= url('uploads/' . $doc['archivo']) ?>"
                                       class="btn btn-sm btn-outline-primary"
                                       target="_blank" title="Ver/Descargar">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <form action="<?= url('admin/documentos/delete/' . $doc['id']) ?>"
                                          method="POST"
                                          onsubmit="return confirm('¿Eliminar este documento?')">
                                        <input type="hidden" name="id_evento" value="<?= $evento['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Formulario subir documento -->
    <div class="col-lg-4">
        <div class="card sticky-top" style="top:80px;">
            <div class="card-header py-3">
                <i class="bi bi-upload me-2"></i>Subir Documento
            </div>
            <div class="card-body p-3">
                <form action="<?= url('admin/documentos/upload/' . $evento['id']) ?>"
                      method="POST" enctype="multipart/form-data">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nombre del documento *</label>
                        <input type="text" name="nombre" class="form-control form-control-sm"
                               placeholder="Ej: Programa oficial, Reglamento..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Archivo *</label>
                        <div class="border rounded-3 p-3 text-center"
                             style="border:2px dashed #dee2e6 !important;cursor:pointer;"
                             onclick="document.getElementById('archivo').click()">
                            <i class="bi bi-cloud-upload fs-3 text-muted d-block mb-1"></i>
                            <small class="text-muted">PDF, Word, Excel, PPT, imagen, ZIP</small>
                            <p class="mb-0 mt-1 text-primary small" id="fileName">
                                Ningún archivo seleccionado
                            </p>
                        </div>
                        <input type="file" name="archivo" id="archivo"
                               class="d-none" required
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip"
                               onchange="document.getElementById('fileName').textContent = this.files[0]?.name">
                        <small class="text-muted">Máx 20MB</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-upload me-1"></i>Subir Documento
                    </button>

                </form>
            </div>
        </div>
    </div>

</div>
