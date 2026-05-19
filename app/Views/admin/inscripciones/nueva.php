<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= url('admin/inscripciones?evento=' . $evento['id']) ?>">Inscripciones</a></li>
        <li class="breadcrumb-item active">Inscribir Persona</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-lg-7">

        <!-- Info evento -->
        <div class="card mb-4" style="border-left:4px solid #3b5bdb;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0"><?= e($evento['nombre_corto']) ?></h6>
                        <small class="text-muted"><?= formatDate($evento['fecha']) ?> — <?= formatDate($evento['fecha2']) ?></small>
                    </div>
                    <?php if (!empty($evento['valor_inscripcion']) && $evento['valor_inscripcion'] > 0): ?>
                    <div class="text-end">
                        <small class="text-muted d-block">Valor</small>
                        <strong class="text-success">$<?= number_format($evento['valor_inscripcion'], 0, ',', '.') ?></strong>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-person-plus me-2"></i>Inscribir Persona Individual
            </div>
            <div class="card-body p-4">

                <div class="alert alert-info small mb-4">
                    <i class="bi bi-info-circle me-1"></i>
                    Si el correo no tiene cuenta en el sistema, se creará automáticamente y se le enviarán sus credenciales de acceso.
                </div>

                <form action="<?= url('admin/inscripciones/registrar') ?>" method="POST">
                    <input type="hidden" name="id_evento" value="<?= $evento['id'] ?>">

                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label fw-semibold">Nombre completo <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control"
                                   placeholder="Nombre completo" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Correo electrónico <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control"
                                   placeholder="correo@ejemplo.com" required>
                            <small class="text-muted">Si ya tiene cuenta se usará la existente.</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tipo documento</label>
                            <select name="tipo_doc" class="form-select">
                                <?php foreach ($tipoDocs as $td): ?>
                                <option value="<?= $td['id'] ?>">
                                    <?= e($td['descripcion']) ?> (<?= e($td['sigla']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Número documento <span class="text-danger">*</span></label>
                            <input type="text" name="documento" class="form-control"
                                   placeholder="Número de documento" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Teléfono <span class="text-danger">*</span></label>
                            <input type="tel" name="telefono" class="form-control"
                                   placeholder="Ej: 3001234567" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nacionalidad</label>
                            <input type="text" name="nacionalidad" class="form-control"
                                   value="Colombiana">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha de nacimiento</label>
                            <input type="date" name="fecha_nac" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Género</label>
                            <select name="genero" class="form-select">
                                <option value="1">Masculino</option>
                                <option value="2">Femenino</option>
                                <option value="3">Otro</option>
                            </select>
                        </div>

                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-success px-4">
                            <i class="bi bi-person-check me-2"></i>Inscribir
                        </button>
                        <a href="<?= url('admin/inscripciones?evento=' . $evento['id']) ?>"
                           class="btn btn-outline-secondary px-4">Cancelar</a>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>
