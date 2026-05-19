<?php
/**
 * @var array $evento Datos del evento
 */
?>
<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= url('admin/events') ?>">Eventos</a></li>
        <li class="breadcrumb-item active">Certificado — <?= e(truncate($evento['nombre_corto'], 30)) ?></li>
    </ol>
</nav>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Configurar Certificado</h4>
        <small class="text-muted"><?= e($evento['nombre_corto']) ?></small>
    </div>
</div>

<div class="row g-4">

    <!-- Columna izquierda: subir plantilla -->
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header py-3">
                <i class="bi bi-image me-2"></i>Plantilla del Certificado
            </div>
            <div class="card-body p-3">
                <form action="<?= url('admin/certificado/plantilla/' . $evento['id']) ?>"
                      method="POST" enctype="multipart/form-data">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Imagen de la plantilla</label>
                        <div class="border rounded p-3 text-center"
                             style="border:2px dashed #dee2e6 !important;cursor:pointer;"
                             onclick="document.getElementById('plantilla').click()">
                            <i class="bi bi-file-image fs-3 text-muted d-block mb-1"></i>
                            <small class="text-muted">JPG o PNG — Máx 10MB</small>
                            <p class="mb-0 mt-1 text-primary small" id="fileName">
                                <?= !empty($evento['cert_plantilla']) ? 'Plantilla actual cargada' : 'Ningún archivo' ?>
                            </p>
                        </div>
                        <input type="file" name="plantilla" id="plantilla"
                               class="d-none" accept=".jpg,.jpeg,.png"
                               onchange="previewPlantilla(this)">
                        <small class="text-muted">Se recomienda tamaño A4 horizontal (1122x794px)</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-upload me-1"></i>Subir Plantilla
                    </button>
                </form>
            </div>
        </div>

        <!-- Configuración de texto -->
        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-fonts me-2"></i>Configuración del Nombre
            </div>
            <div class="card-body p-3">
                <form action="<?= url('admin/certificado/config/' . $evento['id']) ?>"
                      method="POST" id="formConfig">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">
                            Posición X <small class="text-muted">(horizontal)</small>
                        </label>
                        <input type="number" name="cert_x" id="cert_x" class="form-control form-control-sm"
                               value="<?= $evento['cert_x'] ?? 0 ?>" step="0.1">
                    </div>
                        <div class="mb-3">
    <label class="form-label small fw-semibold">
        Posición X2 <small class="text-muted">(fin del rango)</small>
    </label>
    <input type="number" name="cert_x2" id="cert_x2" class="form-control form-control-sm"
           value="<?= $evento['cert_x2'] ?? 297 ?>" step="0.1">
</div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">
                            Posición Y <small class="text-muted">(vertical)</small>
                        </label>
                        <input type="number" name="cert_y" id="cert_y" class="form-control form-control-sm"
                               value="<?= $evento['cert_y'] ?? 0 ?>" step="0.1">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tamaño de fuente</label>
                        <input type="number" name="cert_font_size" id="cert_font_size"
                               class="form-control form-control-sm"
                               value="<?= $evento['cert_font_size'] ?? 24 ?>"
                               min="10" max="72">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Color del nombre</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">#</span>
                            <input type="text" name="cert_font_color" id="cert_font_color"
                                   class="form-control form-control-sm"
                                   value="<?= $evento['cert_font_color'] ?? '000000' ?>"
                                   maxlength="6" placeholder="000000">
                            <input type="color" class="form-control form-control-color form-control-sm"
                                   id="colorPicker"
                                   value="#<?= $evento['cert_font_color'] ?? '000000' ?>"
                                   oninput="document.getElementById('cert_font_color').value = this.value.replace('#','')">
                        </div>
                    </div>

                    <div class="alert alert-info py-2 px-3 small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        También puedes hacer <strong>clic en la imagen</strong> para posicionar el nombre automáticamente.
                    </div>

                    <button type="submit" class="btn btn-success btn-sm w-100">
                        <i class="bi bi-check-lg me-1"></i>Guardar Configuración
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Columna derecha: preview con clic -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-eye me-2"></i>Vista previa — Haz clic para posicionar el nombre</span>
                <?php if (!empty($evento['cert_plantilla'])): ?>
                <a href="<?= url('certificate/preview/' . $evento['id']) ?>"
                   target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-file-pdf me-1"></i>Ver PDF de prueba
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body p-3">

                <?php if (!empty($evento['cert_plantilla'])): ?>
                <div style="position:relative;cursor:crosshair;" id="previewContainer">
                    <img src="<?= url('uploads/' . $evento['cert_plantilla']) ?>"
                         id="plantillaImg"
                         style="width:100%;border-radius:8px;display:block;"
                         alt="Plantilla del certificado">

                    <!-- Marcador de posición del nombre -->
                    <div id="marker" style="
                        position:absolute;
                        transform:translateY(-50%);
                        background:rgba(78,110,210,0.2);
                        border:2px dashed #4e6ed2;
                        border-radius:4px;
                        padding:4px 12px;
                        color:#4e6ed2;
                        font-weight:bold;
                        font-size:14px;
                        pointer-events:none;
                        white-space:nowrap;
                        <?php if ($evento['cert_x'] > 0 || $evento['cert_y'] > 0): ?>
                        display:block;
                        <?php else: ?>
                        display:none;
                        <?php endif; ?>
                    ">
                        Nombre del Asistente
                    </div>
                </div>

                <small class="text-muted d-block mt-2">
                    <i class="bi bi-cursor me-1"></i>
                    Haz clic en la imagen para indicar dónde irá el nombre.
                    Posición actual: X=<?= $evento['cert_x'] ?? 0 ?>mm, Y=<?= $evento['cert_y'] ?? 0 ?>mm
                </small>

                <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-file-image fs-1 d-block mb-3"></i>
                    <p>Sube una plantilla para ver la vista previa y configurar la posición del nombre.</p>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

</div>

<script>
// Preview de imagen antes de subir
function previewPlantilla(input) {
    document.getElementById('fileName').textContent = input.files[0]?.name || 'Ningún archivo';
}

<?php if (!empty($evento['cert_plantilla'])): ?>
// Clic en la imagen para posicionar el nombre
const container  = document.getElementById('previewContainer');
const img        = document.getElementById('plantillaImg');
const marker     = document.getElementById('marker');

// Dimensiones reales del PDF en mm (A4 horizontal)
const PDF_W = 297;
const PDF_H = 210;

let clickCount = 0;
let punto1 = null;

container.addEventListener('click', function(e) {
    const rect   = img.getBoundingClientRect();
    const clickX = e.clientX - rect.left;
    const clickY = e.clientY - rect.top;
    const imgW   = rect.width;
    const imgH   = rect.height;

    const mmX = ((clickX / imgW) * PDF_W).toFixed(2);
    const mmY = ((clickY / imgH) * PDF_H).toFixed(2);

    clickCount++;

    if (clickCount === 1) {
        // Primer clic — X inicio
        punto1 = { x: clickX, y: clickY, mmX, mmY };
        document.getElementById('cert_x').value = mmX;
        document.getElementById('cert_y').value = mmY;

        // Marcador punto 1
        marker.style.left    = (clickX / imgW * 100) + '%';
        marker.style.top     = (clickY / imgH * 100) + '%';
        marker.style.display = 'block';
        marker.style.width   = '10px';
        marker.textContent   = '|';

        alert('✅ Punto inicial marcado. Ahora haz clic en el punto final (derecha).');

    } else if (clickCount === 2) {
        // Segundo clic — X fin
        document.getElementById('cert_x2').value = mmX;

        // Muestra el rango completo
        const x1Pct  = (punto1.x / imgW * 100);
        const x2Pct  = (clickX  / imgW * 100);
        const yPct   = (punto1.y / imgH * 100);
        const widPct = x2Pct - x1Pct;

        marker.style.left    = x1Pct + '%';
        marker.style.top     = yPct + '%';
        marker.style.width   = widPct + '%';
        marker.style.transform = 'translateY(-50%)';
        marker.textContent   = 'Nombre del Asistente';

        clickCount = 0;
        punto1     = null;
    }
});

// Posiciona el marcador al cargar con coordenadas guardadas
<?php if ($evento['cert_x'] > 0 || $evento['cert_y'] > 0): ?>
window.addEventListener('load', function() {
    const rect  = img.getBoundingClientRect();
    const x1Pct = (<?= $evento['cert_x'] ?>  / PDF_W * 100);
    const x2Pct = (<?= $evento['cert_x2'] ?? 297 ?> / PDF_W * 100);
    const yPct  = (<?= $evento['cert_y'] ?>  / PDF_H * 100);

    marker.style.left      = x1Pct + '%';
    marker.style.top       = yPct + '%';
    marker.style.width     = (x2Pct - x1Pct) + '%';
    marker.style.transform = 'translateY(-50%)';
    marker.style.display   = 'block';
    marker.style.textAlign = 'center';
    marker.style.overflow  = 'hidden';
    marker.textContent     = 'Nombre del Asistente';

    // Actualiza también el tamaño de fuente visual
    const fontSize = <?= $evento['cert_font_size'] ?? 24 ?>;
    marker.style.fontSize = (fontSize * 0.5) + 'px';
});
<?php endif; ?>
<?php endif; ?>
</script>
