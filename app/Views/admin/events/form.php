<?php
/**
 * @var array|null $evento Datos del evento (null si es creación)
 * @var array $categorias Lista de categorías
 */
$isEdit = !is_null($evento);
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= url('admin/events') ?>">Eventos</a></li>
        <li class="breadcrumb-item active"><?= $isEdit ? 'Editar' : 'Nuevo Evento' ?></li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-<?= $isEdit ? 'pencil' : 'plus-circle' ?> me-2"></i>
                <?= $isEdit ? 'Editar: ' . e($evento['nombre_corto']) : 'Crear Nuevo Evento' ?>
            </div>
            <div class="card-body p-4">

                <form action="<?= $isEdit
                    ? url('admin/events/update/' . $evento['id'])
                    : url('admin/events/store') ?>"
                      method="POST"
                      enctype="multipart/form-data">

                    <div class="row g-3">

                        <!-- Nombre -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nombre del evento <span class="text-danger">*</span></label>
                            <input type="text" name="nombre_corto" class="form-control"
                                   placeholder="Ej: Torneo Nacional de Taekwondo 2026"
                                   value="<?= e($evento['nombre_corto'] ?? '') ?>" required>
                        </div>

                        <!-- Categoría -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Categoría <span class="text-danger">*</span></label>
                            <select name="id_categoria" class="form-select" required>
                                <option value="">Seleccionar categoría...</option>
                                <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id'] ?>"
                                    <?= ($evento['id_categoria'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= e($cat['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Edición -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Edición / Año</label>
                            <input type="number" name="edicion" class="form-control"
                                   value="<?= e($evento['edicion'] ?? date('Y')) ?>"
                                   min="2000" max="2099">
                        </div>

                        <!-- Fecha inicio -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha de inicio <span class="text-danger">*</span></label>
                            <input type="date" name="fecha" class="form-control"
                                   value="<?= e($evento['fecha'] ?? '') ?>" required>
                        </div>

                        <!-- Fecha fin -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha de cierre <span class="text-danger">*</span></label>
                            <input type="date" name="fecha2" class="form-control"
                                   value="<?= e($evento['fecha2'] ?? '') ?>" required>
                        </div>

                        <!-- Descripción -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="4"
                                      placeholder="Describe el evento..."><?= e($evento['descripcion'] ?? '') ?></textarea>
                        </div>

                        <!-- Imagen -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Imagen del evento</label>
                            <?php if ($isEdit && !empty($evento['pic']) && $evento['pic'] !== 'no-disponible.jpeg' && $evento['pic'] !== 'NO-Aplica'): ?>
                                <div class="mb-2">
                                    <img src="<?= url('uploads/events/' . $evento['pic']) ?>"
                                         alt="Imagen actual" style="height:80px;border-radius:8px;">
                                    <small class="text-muted ms-2">Imagen actual</small>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="pic" class="form-control" accept="image/*">
                            <small class="text-muted">Formatos: JPG, PNG, GIF, WEBP. Máx 5MB.</small>
                        </div>
<!-- Valor inscripción -->
<div class="col-12">
    <label class="form-label fw-semibold">Valor de inscripción</label>
    <div class="input-group">
        <span class="input-group-text">$</span>
        <input type="number" name="valor_inscripcion" class="form-control"
               placeholder="0"
               value="<?= e($evento['valor_inscripcion'] ?? 0) ?>"
               min="0" step="1000">
        <span class="input-group-text">COP</span>
    </div>
    <small class="text-muted">Ingresa 0 si el evento es gratuito.</small>
</div>
                        <!-- Manager del evento -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Manager del evento
                                <small class="text-muted fw-normal">(opcional — se crea automáticamente)</small>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-gear"></i></span>
                                <input type="email" name="email_admin" class="form-control"
                                       placeholder="correo@cliente.com"
                                       value="<?= e($evento['email_admin'] ?? '') ?>">
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Si ingresas un correo se creará un usuario Manager y se le enviarán sus credenciales.
                            </small>
                        </div>
                        <!-- Inscripción y Estado -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Inscripciones</label>
                            <select name="inscripcion" class="form-select">
                                <option value="1" <?= ($evento['inscripcion'] ?? 1) == 1 ? 'selected' : '' ?>>Abiertas</option>
                                <option value="0" <?= ($evento['inscripcion'] ?? 1) == 0 ? 'selected' : '' ?>>Cerradas</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="1" <?= ($evento['estado'] ?? 1) == 1 ? 'selected' : '' ?>>Activo</option>
                                <option value="0" <?= ($evento['estado'] ?? 1) == 0 ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </div>

                    </div>

                    <!-- Botones -->
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-2"></i>
                            <?= $isEdit ? 'Actualizar Evento' : 'Crear Evento' ?>
                        </button>
                        <a href="<?= url('admin/events') ?>" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-x me-2"></i>Cancelar
                        </a>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
