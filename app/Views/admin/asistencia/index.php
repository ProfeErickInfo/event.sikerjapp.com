<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= url('admin/events') ?>">Eventos</a></li>
        <li class="breadcrumb-item active">Asistencia</li>
    </ol>
</nav>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Control de Asistencia</h4>
        <small class="text-muted"><?= e($evento['nombre_corto']) ?></small>
    </div>
</div>

<div class="row g-4">

    <!-- Columna escáner -->
    <div class="col-lg-5">

        <!-- Selector de sesión -->
        <div class="card mb-3">
            <div class="card-header py-3">
                <i class="bi bi-list-check me-2"></i>Tipo de asistencia
            </div>
            <div class="card-body p-3">
                <form method="GET" action="<?= url('admin/asistencia/' . $evento['id']) ?>">
                    <div class="mb-2">
                        <select name="sesion" class="form-select form-select-sm"
                                onchange="this.form.submit()">
                            <option value="0" <?= $idSesion == 0 ? 'selected' : '' ?>>
                                Entrada general al evento
                            </option>
                            <?php foreach ($sesiones as $ses): ?>
                            <option value="<?= $ses['id'] ?>" <?= $idSesion == $ses['id'] ? 'selected' : '' ?>>
                                <?= e($ses['nombre']) ?> — <?= formatDate($ses['fecha']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
                <?php if ($sesionActual): ?>
                <div class="alert alert-info py-2 px-3 mb-0 small mt-2">
                    <i class="bi bi-info-circle me-1"></i>
                    Tomando asistencia para: <strong><?= e($sesionActual['nombre']) ?></strong>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Escáner QR -->
        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-qr-code-scan me-2"></i>Escáner QR
            </div>
            <div class="card-body p-3">

                <!-- Área de cámara -->
                <div id="reader" style="width:100%;border-radius:8px;overflow:hidden;"></div>

                <!-- Resultado del escaneo -->
                <div id="resultado" class="mt-3 d-none">
                    <div id="resultado-contenido" class="alert mb-0"></div>
                </div>

                <!-- Entrada manual como respaldo -->
                <div class="mt-3">
                    <p class="text-muted small mb-2">¿No funciona la cámara? Ingresa el código manualmente:</p>
                    <div class="input-group input-group-sm">
                        <input type="text" id="qrManual" class="form-control"
                               placeholder="Pega el código QR aquí">
                        <button class="btn btn-primary" onclick="procesarQR(document.getElementById('qrManual').value)">
                            <i class="bi bi-check-lg"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- Estadísticas por día -->
        <?php if (!empty($stats)): ?>
        <div class="card mt-3">
            <div class="card-header py-3">
                <i class="bi bi-bar-chart me-2"></i>Asistencia por día
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th class="text-center">Asistentes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats as $stat): ?>
                        <tr>
                            <td><?= formatDate($stat['fecha']) ?></td>
                            <td class="text-center">
                                <span class="badge bg-primary"><?= $stat['total_asistentes'] ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Columna lista de asistencia -->
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <span>
                    <i class="bi bi-people me-2"></i>
                    <?= $sesionActual ? 'Asistencia — ' . e($sesionActual['nombre']) : 'Entradas generales' ?>
                </span>
                <span class="badge bg-primary"><?= count($asistencia) ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($asistencia)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-qr-code fs-1 d-block mb-2"></i>
                    No hay registros aún. Escanea un QR para comenzar.
                </div>
                <?php else: ?>
                <div class="table-responsive" style="max-height:500px;overflow-y:auto;">
                    <table class="table table-hover table-sm mb-0 align-middle">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Nombre</th>
                                <th>Delegación</th>
                                <th>Hora</th>
                            </tr>
                        </thead>
                        <tbody id="tablaAsistencia">
                            <?php foreach ($asistencia as $a): ?>
                            <tr>
                                <td><strong><?= e($a['nombre']) ?></strong></td>
                                <td><small class="text-muted"><?= e($a['delegacion_nombre'] ?? '—') ?></small></td>
                                <td><small><?= date('d/m H:i', strtotime($a['fecha_hora'])) ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<!-- html5-qrcode desde CDN -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
const idEvento  = <?= $evento['id'] ?>;
const idSesion  = <?= $idSesion ?>;
const scanUrl   = '<?= url('admin/asistencia/scan') ?>';
let escaneando  = false;

// Inicializa el escáner
const html5QrCode = new Html5Qrcode("reader");
const config = { fps: 10, qrbox: { width: 200, height: 200 } };

html5QrCode.start(
    { facingMode: "environment" },
    config,
    onScanSuccess,
    onScanError
).catch(err => {
    document.getElementById('reader').innerHTML =
        '<div class="alert alert-warning">No se pudo acceder a la cámara. Usa la entrada manual.</div>';
});

function onScanSuccess(decodedText) {
    if (escaneando) return;
    escaneando = true;
    procesarQR(decodedText);
    setTimeout(() => { escaneando = false; }, 3000);
}

function onScanError(error) {
    // Silenciar errores de escaneo continuo
}

function procesarQR(qrData) {
    if (!qrData.trim()) return;

    fetch(scanUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `qr_data=${encodeURIComponent(qrData)}&id_evento=${idEvento}&id_sesion=${idSesion}`
    })
    .then(r => r.json())
    .then(data => {
        const div      = document.getElementById('resultado');
        const contenido = document.getElementById('resultado-contenido');

        div.classList.remove('d-none');

        if (data.success) {
            contenido.className = 'alert alert-success mb-0';
            contenido.innerHTML = `
                <strong>${data.message}</strong><br>
                <span>${data.nombre}</span>
                ${data.delegacion ? '<br><small>' + data.delegacion + '</small>' : ''}
                <br><small class="text-muted">Hora: ${data.hora}</small>
            `;
            // Agrega fila a la tabla sin recargar
            agregarFila(data.nombre, data.delegacion, data.hora);
        } else if (data.already) {
            contenido.className = 'alert alert-warning mb-0';
            contenido.innerHTML = `<strong>⚠ ${data.message}</strong><br>${data.nombre}`;
        } else {
            contenido.className = 'alert alert-danger mb-0';
            contenido.innerHTML = `<strong>✗ ${data.message}</strong>`;
        }

        // Oculta el resultado después de 4 segundos
        setTimeout(() => div.classList.add('d-none'), 4000);

        // Limpia entrada manual
        document.getElementById('qrManual').value = '';
    })
    .catch(() => {
        alert('Error de conexión al procesar el QR.');
    });
}

function agregarFila(nombre, delegacion, hora) {
    const tbody = document.getElementById('tablaAsistencia');
    if (!tbody) return;

    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><strong>${nombre}</strong></td>
        <td><small class="text-muted">${delegacion || '—'}</small></td>
        <td><small>${hora}</small></td>
    `;
    tr.style.background = '#d1fae5';
    tbody.insertBefore(tr, tbody.firstChild);
    setTimeout(() => tr.style.background = '', 2000);
}
</script>
