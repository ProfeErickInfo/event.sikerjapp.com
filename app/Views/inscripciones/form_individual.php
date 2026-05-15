<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= url('events') ?>">Eventos</a></li>
        <li class="breadcrumb-item"><a href="<?= url('events/' . $evento['id']) ?>"><?= e(truncate($evento['nombre_corto'], 30)) ?></a></li>
        <li class="breadcrumb-item active">Inscripción</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-lg-7">

        <!-- Info del evento -->
        <div class="card mb-4" style="border-left:4px solid #2d3a5e;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0"><?= e($evento['nombre_corto']) ?></h6>
                        <small class="text-muted">
                            <?= formatDate($evento['fecha']) ?> — <?= formatDate($evento['fecha2']) ?>
                        </small>
                    </div>
                    <?php if (!empty($evento['valor_inscripcion']) && $evento['valor_inscripcion'] > 0): ?>
                    <div class="text-end">
                        <small class="text-muted d-block">Valor inscripción</small>
                        <strong class="text-success fs-5">
                            $<?= number_format($evento['valor_inscripcion'], 0, ',', '.') ?>
                        </strong>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Formulario -->
        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-person-check me-2"></i>Datos de Inscripción
            </div>
            <div class="card-body p-4">

                <form action="<?= url('events/' . $evento['id'] . '/inscribirse') ?>" method="POST">

                    <div class="row g-3">

                        <!-- Nombre -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nombre completo <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control"
                                   value="<?= e($user['name'] ?? '') ?>"
                                   placeholder="Tu nombre completo" required>
                        </div>

                        <!-- Tipo documento + Número -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tipo documento</label>
                            <select name="tipo_doc" class="form-select">
                                <?php foreach ($tipoDocs as $td): ?>
                                <option value="<?= $td['id'] ?>"><?= e($td['descripcion']) ?> (<?= e($td['sigla']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Número de documento <span class="text-danger">*</span></label>
                            <input type="text" name="documento" class="form-control"
                                   placeholder="Número de documento" required>
                        </div>

                        <!-- Teléfono -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Teléfono móvil <span class="text-danger">*</span></label>
                            <input type="tel" name="telefono" class="form-control"
                                   placeholder="Ej: 3001234567" required>
                        </div>

                        <!-- Nacionalidad -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nacionalidad</label>
                            <input type="text" name="nacionalidad" class="form-control"
                                   value="Colombiana" placeholder="Nacionalidad">
                        </div>

                        <!-- Fecha nacimiento -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha de nacimiento <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_nac" class="form-control" required>
                            <small class="text-muted">Debes ser mayor de 18 años.</small>
                        </div>

                        <!-- Género -->
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
                            <i class="bi bi-person-check me-2"></i>Confirmar Inscripción
                        </button>
                        <a href="<?= url('events/' . $evento['id']) ?>" class="btn btn-outline-secondary px-4">
                            Cancelar
                        </a>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>
