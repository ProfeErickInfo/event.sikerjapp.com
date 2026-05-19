<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= url('events') ?>">Eventos</a></li>
        <li class="breadcrumb-item">
            <a href="<?= url('events/' . $evento['id']) ?>"><?= e(truncate($evento['nombre_corto'], 30)) ?></a>
        </li>
        <li class="breadcrumb-item active">Pagos</li>
    </ol>
</nav>

<div class="row g-4 justify-content-center">
    <div class="col-lg-7">

        <!-- Resumen del evento -->
        <div class="card mb-4" style="border-left:4px solid #2d3a5e;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0"><?= e($evento['nombre_corto']) ?></h6>
                        <small class="text-muted"><?= formatDate($evento['fecha']) ?></small>
                    </div>
                    <?php //$valorTotal = (float) ($evento['valor_inscripcion'] ?? 0); ?>
                    <?php if ($valorTotal > 0): ?>
                    <div class="text-end">
                        <small class="text-muted d-block">Valor total</small>
                        <strong class="fs-5">$<?= number_format($valorTotal, 0, ',', '.') ?></strong>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Barra de progreso del pago -->
                <?php if ($valorTotal > 0): ?>
                <div class="mt-3">
                    <?php $porcentaje = min(100, round(($totalAprobado / $valorTotal) * 100)); ?>
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Pagado</small>
                        <small class="fw-semibold">
                            $<?= number_format($totalAprobado, 0, ',', '.') ?> 
                            de $<?= number_format($valorTotal, 0, ',', '.') ?>
                        </small>
                    </div>
                    <div class="progress" style="height:8px;">
                        <div class="progress-bar bg-success"
                             style="width:<?= $porcentaje ?>%"></div>
                    </div>
                    <?php if ($totalAprobado >= $valorTotal): ?>
                    <div class="mt-2 text-success small fw-semibold">
                        <i class="bi bi-check-circle me-1"></i>Pago completo
                    </div>
                    <?php else: ?>
                    <div class="mt-2 text-muted small">
                        Pendiente: $<?= number_format($valorTotal - $totalAprobado, 0, ',', '.') ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Historial de comprobantes -->
        <?php if (!empty($comprobantes)): ?>
        <div class="card mb-4">
            <div class="card-header py-3">
                <i class="bi bi-clock-history me-2"></i>Comprobantes enviados
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Archivo</th>
                            <th>Valor</th>
                            <th>Descripción</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comprobantes as $c): ?>
                        <tr>
                            <td>
                                <?php
                                $ext = strtolower(pathinfo($c['archivo'], PATHINFO_EXTENSION));
                                $fileUrl = url('uploads/' . $c['archivo']);
                                ?>
                                <?php if ($ext === 'pdf'): ?>
                                    <a href="<?= e($fileUrl) ?>" target="_blank"
                                       class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-file-pdf me-1"></i>PDF
                                    </a>
                                <?php else: ?>
                                    <a href="<?= e($fileUrl) ?>" target="_blank">
                                        <img src="<?= e($fileUrl) ?>"
                                             style="height:40px;width:56px;object-fit:cover;border-radius:6px;">
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($c['valor'] > 0): ?>
                                    <small>$<?= number_format($c['valor'], 0, ',', '.') ?></small>
                                <?php else: ?>
                                    <small class="text-muted">—</small>
                                <?php endif; ?>
                            </td>
                            <td><small class="text-muted"><?= e($c['descripcion'] ?? '—') ?></small></td>
                            <td><small><?= formatDateTime($c['fec_subida']) ?></small></td>
                            <td>
                                <?php
                                $estados = [0=>'Pendiente', 1=>'Aprobado', 2=>'Rechazado'];
                                $clases  = [0=>'warning',   1=>'success',  2=>'danger'];
                                $est     = $c['estado'];
                                ?>
                                <span class="badge bg-<?= $clases[$est] ?>">
                                    <?= $estados[$est] ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Formulario subir nuevo comprobante -->
        <?php if ($totalAprobado < $valorTotal || $valorTotal == 0): ?>
        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-upload me-2"></i>Subir Comprobante
            </div>
            <div class="card-body p-4">
                <form action="<?= url('events/' . $evento['id'] . '/pago/subir') ?>"
                      method="POST" enctype="multipart/form-data">

                    <div class="row g-3">

                        <!-- Archivo -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Archivo <span class="text-danger">*</span>
                            </label>
                            <div class="border rounded-3 p-4 text-center"
                                 style="border:2px dashed #dee2e6 !important;cursor:pointer;"
                                 onclick="document.getElementById('comprobante').click()">
                                <i class="bi bi-cloud-upload fs-2 text-muted d-block mb-2"></i>
                                <p class="mb-1 fw-semibold">Haz clic para seleccionar</p>
                                <small class="text-muted">JPG, PNG, PDF o WEBP — Máx 5MB</small>
                                <p class="mb-0 mt-2 text-primary small" id="fileName">
                                    Ningún archivo seleccionado
                                </p>
                            </div>
                            <input type="file" name="comprobante" id="comprobante"
                                   accept=".jpg,.jpeg,.png,.pdf,.webp"
                                   class="d-none" required
                                   onchange="document.getElementById('fileName').textContent = this.files[0]?.name">
                        </div>

                        <!-- Valor pagado -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Valor del comprobante</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="valor" class="form-control"
                                       placeholder="0"
                                       value="<?= $valorTotal > 0 ? $valorTotal - $totalAprobado : 0 ?>"
                                       min="0" step="1000">
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Descripción (opcional)</label>
                            <input type="text" name="descripcion" class="form-control"
                                   placeholder="Ej: Pago parcial, transferencia...">
                        </div>

                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-success px-4">
                            <i class="bi bi-upload me-2"></i>Enviar Comprobante
                        </button>
                        <a href="<?= url('events/' . $evento['id']) ?>"
                           class="btn btn-outline-secondary px-4">Cancelar</a>
                    </div>

                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-2"></i>
            <strong>¡Pago completo!</strong> Tu pago ha sido verificado y aprobado.
        </div>
        <?php endif; ?>

    </div>
</div>
