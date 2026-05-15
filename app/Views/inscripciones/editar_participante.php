<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= url('events') ?>">Eventos</a></li>
        <li class="breadcrumb-item">
            <a href="<?= url('events/' . $idEvento . '/delegacion') ?>">Mi Delegación</a>
        </li>
        <li class="breadcrumb-item active">Editar Participante</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-pencil me-2"></i>Editar Participante
            </div>
            <div class="card-body p-4">

                <form action="<?= url('delegacion/participante/actualizar/' . $participante['id']) ?>"
                      method="POST">
                    <input type="hidden" name="id_evento" value="<?= $idEvento ?>">

                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label fw-semibold">Nombre completo *</label>
                            <input type="text" name="nombre" class="form-control"
                                   value="<?= e($participante['nombre']) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tipo documento</label>
                            <select name="tipo_doc" class="form-select">
                                <?php foreach ($tipoDocs as $td): ?>
                                <option value="<?= $td['id'] ?>"
                                    <?= $participante['tipo_doc'] == $td['id'] ? 'selected' : '' ?>>
                                    <?= e($td['descripcion']) ?> (<?= e($td['sigla']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Número documento *</label>
                            <input type="text" name="documento" class="form-control"
                                   value="<?= e($participante['documento']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Teléfono</label>
                            <input type="tel" name="telefono" class="form-control"
                                   value="<?= e($participante['telefono']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= e($participante['email']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nacionalidad</label>
                            <input type="text" name="nacionalidad" class="form-control"
                                   value="<?= e($participante['nacionalidad'] ?? 'Colombiana') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha de nacimiento</label>
                            <input type="date" name="fecha_nac" class="form-control"
                                   value="<?= e($participante['fecha_nac']) ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Género</label>
                            <select name="genero" class="form-select">
                                <option value="1" <?= ($participante['genero'] ?? 1) == 1 ? 'selected' : '' ?>>Masculino</option>
                                <option value="2" <?= ($participante['genero'] ?? 1) == 2 ? 'selected' : '' ?>>Femenino</option>
                                <option value="3" <?= ($participante['genero'] ?? 1) == 3 ? 'selected' : '' ?>>Otro</option>
                            </select>
                        </div>

                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-2"></i>Guardar Cambios
                        </button>
                        <a href="<?= url('events/' . $idEvento . '/delegacion') ?>"
                           class="btn btn-outline-secondary px-4">
                            Cancelar
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>