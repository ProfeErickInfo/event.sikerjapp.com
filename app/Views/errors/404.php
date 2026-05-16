<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Página no encontrada | <?= APP_NAME ?></title>
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
        .error-card {
            text-align: center;
            color: white;
            padding: 48px 32px;
            max-width: 480px;
        }
        .error-number {
            font-size: 8rem;
            font-weight: 900;
            line-height: 1;
            background: linear-gradient(135deg, #4e6ed2, #8aa0e8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .btn-home {
            background: linear-gradient(135deg, #4e6ed2, #2d3a5e);
            border: none;
            color: white;
            padding: 12px 32px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: opacity 0.2s;
        }
        .btn-home:hover { opacity: 0.9; color: white; }
        .btn-back {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            padding: 12px 32px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }
        .btn-back:hover { background: rgba(255,255,255,0.2); color: white; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-number">404</div>
        <i class="bi bi-compass fs-1 d-block my-3" style="color:rgba(255,255,255,0.4);"></i>
        <h3 class="fw-bold mb-2">Página no encontrada</h3>
        <p style="color:rgba(255,255,255,0.6);" class="mb-4">
            La página que buscas no existe o fue movida.<br>
            Verifica la URL o regresa al inicio.
        </p>
        <div class="d-flex gap-3 justify-content-center">
            <a href="javascript:history.back()" class="btn-back">
                <i class="bi bi-arrow-left me-2"></i>Regresar
            </a>
            <a href="<?= BASE_URL ?>" class="btn-home">
                <i class="bi bi-house me-2"></i>Ir al inicio
            </a>
        </div>
        <p class="mt-4" style="color:rgba(255,255,255,0.3);font-size:0.8rem;">
            <?= APP_NAME ?> · v<?= APP_VERSION ?>
        </p>
    </div>
</body>
</html>