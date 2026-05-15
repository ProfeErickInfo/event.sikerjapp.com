<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= url('events') ?>">Eventos</a></li>
        <li class="breadcrumb-item"><a href="<?= url('events/' . $evento['id']) ?>"><?= e(truncate($evento['nombre_corto'], 30)) ?></a></li>
        <li class="breadcrumb-item active">Crear Delegación</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-lg-6">

        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-people me-2"></i>Registrar Delegación
            </div>
            <div class="card-body p-4">

                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Para inscribir participantes primero debes registrar tu delegación.
                    Esta información quedará guardada para futuros eventos.
                </div>

                <form action="<?= url('delegacion/crear') ?>" method="POST">
                    <input type="hidden" name="id_evento" value="<?= $evento['id'] ?>">

                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label fw-semibold">Nombre de la delegación <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control"
                                   placeholder="Ej: Club Taekwondo Cartagena" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Representante</label>
                            <input type="text" name="representante" class="form-control"
                                   placeholder="Nombre del representante">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Teléfono</label>
                            <input type="tel" name="telefono" class="form-control"
                                   placeholder="Ej: 3001234567">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ciudad</label>
                            <input type="text" name="ciudad" class="form-control"
                                   placeholder="Ciudad">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Email de contacto</label>
                            <input type="email" name="email" class="form-control"
                                   placeholder="correo@delegacion.com">
                        </div>

                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-2"></i>Crear Delegación
                        </button>
                        <a href="<?= url('events/' . $evento['id']) ?>" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>
