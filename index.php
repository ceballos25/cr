<?php
require_once "config/config.php";

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header("Location: front/dashboard.php");
    exit;
}

$error = $_GET['error'] ?? '';
$detail = $_GET['detail'] ?? '';

$messages = [
    'missing' => '⚠️ Completa usuario y contraseña.',
    'bad_credentials' => '❌ Usuario o contraseña incorrectos.',
    'session_expired' => '⏱️ Tu sesión ha expirado. Ingresa nuevamente.',
    'api' => '🔴 Error en la API. Intenta de nuevo.',
    'curl' => '🌐 No se pudo conectar a la API.',
    'json' => '⚠️ Respuesta inválida de la API.',
    'invalid_response' => '⚠️ Formato de respuesta incorrecto.',
    'no_token' => '🔑 No se generó el token de acceso.',
];

$msg = $messages[$error] ?? '';
if ($detail) {
    $msg .= "<br><small class='text-muted'>" . htmlspecialchars($detail) . "</small>";
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - <?= SITE_NAME ?></title>
    <link rel="shortcut icon" type="image/png" href="./assets/images/logos/logo.ico" />
    <link rel="stylesheet" href="./assets/css/styles.min.css" />
</head>
<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        <div class="position-relative overflow-hidden text-bg-light min-vh-100 d-flex align-items-center justify-content-center">
            <div class="d-flex align-items-center justify-content-center w-100">
                <div class="row justify-content-center w-100">
                    <div class="col-md-8 col-lg-6 col-xxl-3">
                        <div class="card mb-0">
                            <div class="card-body">
                                <a href="/" class="text-nowrap logo-img text-center d-block py-3 w-100">
                                    <img src="./assets/images/logos/logo.jpg" width="200" alt="">
                                </a>
                                <?php if ($msg): ?>
                                    <div class="alert alert-danger" role="alert">
                                        <?= $msg ?>
                                    </div>
                                <?php endif; ?>
                                <form action="functions/login.php" method="POST" autocomplete="off">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Usuario</label>
                                        <input type="text" value="irma.herrera" class="form-control" id="email" name="email" required>
                                    </div>

                                    <div class="mb-4">
                                        <label for="password" class="form-label">Contraseña</label>
                                        <input type="password" value="123456789" class="form-control" id="password" name="password" required>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2">
                                        Ingresar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="./assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="./assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>