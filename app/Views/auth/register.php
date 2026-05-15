<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a2035 0%, #2d3a5e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 0;
        }
        .register-card {
            width: 100%;
            max-width: 480px;
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .register-header {
            background: linear-gradient(135deg, #1a2035, #2d3a5e);
            color: white;
            border-radius: 16px 16px 0 0;
            padding: 28px 32px;
            text-align: center;
        }
        .register-header i { font-size: 2rem; margin-bottom: 6px; display: block; }
        .register-body { padding: 32px; }
        .form-control:focus, .form-select:focus {
            border-color: #2d3a5e;
            box-shadow: 0 0 0 3px rgba(45,58,94,0.15);
        }
        .btn-register {
            background: linear-gradient(135deg, #1a2035, #2d3a5e);
            border: none;
            color: white;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
        }
        .btn-register:hover { opacity: 0.9; color: white; }
        .tipo-card {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }
        .tipo-card:hover { border-color: #2d3a5e; background: #f8f9ff; }
        .tipo-card.selected { border-color: #2d3a5e; background: #f0f4ff; }
        .tipo-card i { font-size: 1.8rem; display: block; margin-bottom: 6px; }
        .info-box {
            background: #f0f7ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 0.85rem;
            color: #1e40af;
        }
    </style>
</head>
<body>

<div class="register-card card">
    <div class="register-header">
        <i class="bi bi-person-plus"></i>
        <h4>Crear Cuenta</h4>
        <small><?= APP_NAME ?></small>
    </div>

    <div class="register-body">

        <?php flashMessage(); ?>

        <!-- Aviso de contraseña automática -->
        <div class="info-box mb-4">
            <i class="bi bi-info-circle me-1"></i>
            Recibirás tu contraseña de acceso en el correo electrónico que registres.
        </div>

        <form action="<?= url('auth/register') ?>" method="POST">

            <!-- Tipo de usuario -->
            <div class="mb-4">
                <label class="form-label fw-semibold">Tipo de registro</label>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="tipo-card <?= ($old['tipoU'] ?? '8') == '8' ? 'selected' : '' ?>"
                             onclick="selectTipo(8, this)">
                            <i class="bi bi-person text-primary"></i>
                            <strong>Individual</strong>
                            <small class="d-block text-muted">Seminarios y eventos</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="tipo-card <?= ($old['tipoU'] ?? '') == '1' ? 'selected' : '' ?>"
                             onclick="selectTipo(1, this)">
                            <i class="bi bi-people text-success"></i>
                            <strong>Club / Delegación</strong>
                            <small class="d-block text-muted">Clubes afiliados</small>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="tipoU" id="tipoU" value="<?= e($old['tipoU'] ?? '8') ?>">
            </div>

            <!-- Nombre completo -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Nombre completo</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="name" class="form-control"
                           placeholder="Tu nombre completo"
                           value="<?= e($old['name'] ?? '') ?>" required>
                </div>
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label class="form-label fw-semibold">Correo electrónico</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control"
                           placeholder="tu@correo.com"
                           value="<?= e($old['email'] ?? '') ?>" required>
                </div>
                <small class="text-muted">
                    <i class="bi bi-shield-check me-1"></i>
                    Usarás este correo para iniciar sesión.
                </small>
            </div>

            <button type="submit" class="btn btn-register w-100">
                <i class="bi bi-send me-2"></i> Crear Cuenta y Recibir Contraseña
            </button>

        </form>

        <hr class="my-3">
        <div class="text-center">
            <small class="text-muted">¿Ya tienes cuenta?</small>
            <a href="<?= url('auth/login') ?>" class="d-block mt-1 fw-semibold text-decoration-none">
                <i class="bi bi-box-arrow-in-right me-1"></i> Inicia sesión
            </a>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function selectTipo(value, el) {
    document.querySelectorAll('.tipo-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('tipoU').value = value;
}
</script>
</body>
</html>
