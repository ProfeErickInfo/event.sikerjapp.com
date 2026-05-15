<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a2035 0%, #2d3a5e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-header {
            background: linear-gradient(135deg, #1a2035, #2d3a5e);
            color: white;
            border-radius: 16px 16px 0 0;
            padding: 32px;
            text-align: center;
        }
        .login-header i { font-size: 2.5rem; margin-bottom: 8px; display: block; }
        .login-header h4 { margin: 0; font-weight: 700; }
        .login-header small { opacity: 0.7; }
        .login-body { padding: 32px; }
        .form-control:focus { border-color: #2d3a5e; box-shadow: 0 0 0 3px rgba(45,58,94,0.15); }
        .btn-login {
            background: linear-gradient(135deg, #1a2035, #2d3a5e);
            border: none;
            color: white;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
        }
        .btn-login:hover { opacity: 0.9; color: white; }
        .input-group-text { background: #f8f9fa; border-right: none; }
        .form-control { border-left: none; }
    </style>
</head>
<body>

<div class="login-card card">
    <div class="login-header">
        <i class="bi bi-calendar-event"></i>
        <h4><?= APP_NAME ?></h4>
        <small>Ingresa a tu cuenta</small>
    </div>

    <div class="login-body">

        <!-- Mensajes flash -->
        <?php flashMessage(); ?>

        <form action="<?= url('auth/login') ?>" method="POST">

            <!-- Usuario o Email -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Usuario o Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input
                        type="text"
                        name="nickz"
                        class="form-control"
                        placeholder="Tu usuario o email"
                        value="<?= e($old['nickz'] ?? '') ?>"
                        required
                        autofocus
                    >
                </div>
            </div>

            <!-- Contraseña -->
            <div class="mb-4">
                <label class="form-label fw-semibold">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input
                        type="password"
                        name="pazz"
                        class="form-control"
                        placeholder="Tu contraseña"
                        required
                    >
                </div>
            </div>

            <button type="submit" class="btn btn-login w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i> Iniciar Sesión
            </button>

        </form>

        <hr class="my-4">

        <div class="text-center">
            <small class="text-muted">¿No tienes cuenta?</small>
            <a href="<?= url('auth/register') ?>" class="d-block mt-1 fw-semibold text-decoration-none">
                <i class="bi bi-person-plus me-1"></i> Regístrate aquí
            </a>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
