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
    background-image: url('https://images.unsplash.com/photo-1587825140708-dfaf72ae4b04?w=1200&q=70&auto=format&fit=crop');

      background-size: cover;
        background-position: center;
        background-attachment: fixed;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    body::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(
            135deg,
            rgba(26, 32, 53, 0.92) 0%,
            rgba(45, 58, 94, 0.85) 50%,
            rgba(78, 110, 210, 0.75) 100%
        );
        backdrop-filter: blur(2px);
        -webkit-backdrop-filter: blur(2px);
    }
    .login-wrapper {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 420px;
        padding: 16px;
    }
    .login-brand {
        text-align: center;
        margin-bottom: 24px;
    }
    .login-brand .brand-icon {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, #4e6ed2, #2d3a5e);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 12px;
        box-shadow: 0 8px 32px rgba(78,110,210,0.4);
    }
    .login-brand h4 {
        color: white;
        font-weight: 700;
        margin: 0;
    }
    .login-brand small {
        color: rgba(255,255,255,0.5);
        font-size: 0.8rem;
    }
    .login-card {
        width: 100%;
        border: none;
        border-radius: 20px;
        box-shadow: 0 24px 64px rgba(0,0,0,0.4);
        background: rgba(255,255,255,0.97);
        backdrop-filter: blur(10px);
        overflow: hidden;
    }
    .login-body { padding: 32px; }
    .form-control:focus {
        border-color: #4e6ed2;
        box-shadow: 0 0 0 3px rgba(78,110,210,0.15);
    }
    .input-group-text {
        background: #f8f9fa;
        border-right: none;
        color: #4e6ed2;
    }
    .form-control { border-left: none; }
    .btn-login {
        background: linear-gradient(135deg, #1a2035, #4e6ed2);
        border: none;
        color: white;
        padding: 13px;
        font-weight: 600;
        border-radius: 10px;
        letter-spacing: 0.3px;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(78,110,210,0.3);
    }
    .btn-login:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 25px rgba(78,110,210,0.4);
        color: white;
    }
    .divider {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #adb5bd;
        font-size: 0.8rem;
        margin: 20px 0;
    }
    .divider::before, .divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e9ecef;
    }
</style>
</head>
<body>
<div class="login-wrapper">

    <!-- Brand arriba de la tarjeta -->
    <div class="login-brand">
        <div class="brand-icon">
            <i class="bi bi-calendar-event text-white fs-4"></i>
        </div>
        <h4><?= APP_NAME ?></h4>
        <small>Gestión profesional de eventos</small>
    </div>

    <div class="login-card card">
        <div class="login-body">

            <?php flashMessage(); ?>

            <h5 class="fw-bold mb-1">Bienvenido</h5>
            <p class="text-muted small mb-4">Ingresa tus credenciales para continuar</p>

            <form action="<?= url('auth/login') ?>" method="POST">

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Correo electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="text" name="nickz" class="form-control"
                               placeholder="tu@correo.com"
                               value="<?= e($old['nickz'] ?? '') ?>"
                               required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold small">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="pazz" id="pazz"
                               class="form-control" placeholder="Tu contraseña" required>
                        <button class="btn btn-outline-secondary" type="button"
                                onclick="document.getElementById('pazz').type = document.getElementById('pazz').type === 'password' ? 'text' : 'password'">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-login w-100">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                </button>

            </form>

            <div class="divider">o</div>

            <div class="text-center">
                <small class="text-muted">¿No tienes cuenta?</small>
                <a href="<?= url('auth/register') ?>"
                   class="d-block mt-1 fw-semibold text-decoration-none" style="color:#4e6ed2;">
                    <i class="bi bi-person-plus me-1"></i>Regístrate aquí
                </a>
            </div>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
