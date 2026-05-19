<?php
/**
 * Vista: Perfil de Usuario
 * 
 * @var array $usuario Datos del usuario
 */

$roles = [
    1 => 'admin',
    2 => 'delegacion',
    3 => 'invitado',
    4 => 'manager',
];
$tipoNombre = $roles[$usuario['tipoU']] ?? 'Usuario';
?>

<div class="row g-4 justify-content-center">
    <div class="col-lg-8">

        <!-- Encabezado perfil -->
       <div class="card mb-4">
    <div class="card-body p-3">
        <div class="d-flex align-items-center gap-3 flex-wrap">
                    <!-- Avatar -->
                    <div style="width:72px;height:72px;background:linear-gradient(135deg,#1a2035,#2d3a5e);
                                border-radius:50%;display:flex;align-items:center;justify-content:center;
                                flex-shrink:0;">
                        <span style="color:white;font-size:1.8rem;font-weight:700;">
                            <?= strtoupper(substr($usuario['name'] ?? 'U', 0, 1)) ?>
                        </span>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1"><?= e($usuario['name']) ?></h4>
                        <p class="text-muted mb-1"><?= e($usuario['email']) ?></p>
                        <span class="badge bg-primary-subtle text-primary"><?= e($tipoNombre) ?></span>
                        <span class="badge bg-<?= $usuario['estado'] == 1 ? 'success' : 'danger' ?>-subtle
                                              text-<?= $usuario['estado'] == 1 ? 'success' : 'danger' ?> ms-1">
                            <?= $usuario['estado'] == 1 ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </div>
                  <div class="ms-auto text-end d-none d-md-block">
    <small class="text-muted d-block">Miembro desde</small>
    <strong><?= formatDate($usuario['fec_reg']) ?></strong>
</div>
                </div>
            </div>
        </div>

        <div class="row g-4">

            <!-- Editar datos -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header py-3">
                        <i class="bi bi-person me-2"></i>Datos Personales
                    </div>
                    <div class="card-body p-4">

                        <?php flashMessage(); ?>

                        <form action="<?= url('perfil/actualizar') ?>" method="POST">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nombre completo</label>
                                <input type="text" name="name" class="form-control"
                                       value="<?= e($usuario['name']) ?>" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Correo electrónico</label>
                                <input type="email" name="email" class="form-control"
                                       value="<?= e($usuario['email']) ?>" required>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Este correo es tu usuario de acceso.
                                </small>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-check-lg me-2"></i>Guardar Cambios
                            </button>

                        </form>
                    </div>
                </div>
            </div>

            <!-- Cambiar contraseña -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header py-3">
                        <i class="bi bi-lock me-2"></i>Cambiar Contraseña
                    </div>
                    <div class="card-body p-4">

                        <form action="<?= url('perfil/cambiar-password') ?>" method="POST">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Contraseña actual</label>
                                <div class="input-group">
                                    <input type="password" name="password_actual"
                                           class="form-control" id="passActual" required>
                                    <button class="btn btn-outline-secondary" type="button"
                                            onclick="togglePass('passActual')">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nueva contraseña</label>
                                <div class="input-group">
                                    <input type="password" name="password_nueva"
                                           class="form-control" id="passNueva"
                                           minlength="8" required>
                                    <button class="btn btn-outline-secondary" type="button"
                                            onclick="togglePass('passNueva')">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Mínimo 8 caracteres.</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Confirmar contraseña</label>
                                <div class="input-group">
                                    <input type="password" name="password_confirmar"
                                           class="form-control" id="passConfirmar"
                                           minlength="8" required>
                                    <button class="btn btn-outline-secondary" type="button"
                                            onclick="togglePass('passConfirmar')">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-warning w-100">
                                <i class="bi bi-lock me-2"></i>Cambiar Contraseña
                            </button>

                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function togglePass(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
