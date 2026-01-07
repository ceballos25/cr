<?php
require_once __DIR__ . '/envLoader.php';

$envLoader = new EnvLoader(__DIR__ . '/../.env');
$envLoader->load();

/**
 * ===============================
 * RUTAS ABSOLUTAS (FIX WINDOWS/LINUX)
 * ===============================
 */
define('ROOT_PATH', realpath(__DIR__ . '/..'));
define('DS', DIRECTORY_SEPARATOR);
define('BASE_URL', rtrim(env('SITE_URL'), '/'));
define('ASSETS_URL', BASE_URL . '/assets');

/**
 * ===============================
 * CONFIG DB
 * ===============================
 */
define('DB_HOST', env('DB_HOST'));
define('DB_PORT', env('DB_PORT'));
define('DB_USER', env('DB_USER'));
define('DB_PASS', env('DB_PASS'));
define('DB_NAME', env('DB_NAME'));
define('DB_CHARSET', env('DB_CHARSET'));

/**
 * ===============================
 * CONFIG SITIO
 * ===============================
 */
define('SITE_NAME', env('SITE_NAME'));

/**
 * ===============================
 * TIMEZONE
 * ===============================
 */
date_default_timezone_set(env('TIMEZONE') ?: 'America/Bogota');

/**
 * ===============================
 * API
 * ===============================
 */
define('API_BASE', rtrim(env('API_BASE'), '/') . '/');
define('API_KEY', env('API_KEY'));

/**
 * ===============================
 * MODO / ERRORES
 * ===============================
 */
define('APP_ENV', env('APP_ENV'));
define('DEBUG_MODE', (bool) env('DEBUG_MODE'));

if (env('DISPLAY_ERRORS')) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

/**
 * ===============================
 * CONFIGURACIÓN DE POS
 * ===============================
 */
define('SALE_PREFIX', env('SALE_PREFIX') ?? 'CR');
define('SALE_PAD', (int)(env('SALE_PAD') ?? 6));

/**
 * ===============================
 * CONFIGURACIÓN Y MANEJO DE SESIÓN
 * ===============================
 */
ini_set('session.cookie_httponly', env('SESSION_COOKIE_HTTPONLY') ? '1' : '0');
ini_set('session.cookie_secure', env('SESSION_COOKIE_SECURE') ? '1' : '0');
ini_set('session.cookie_lifetime', env('SESSION_LIFETIME'));
ini_set('session.gc_maxlifetime', env('SESSION_LIFETIME'));

if (env('SESSION_AUTO_START') && session_status() === PHP_SESSION_NONE) {
    session_name(env('SESSION_NAME'));
    session_start();
}

/**
 * ===============================
 * PROTECCIÓN DE RUTAS
 * ===============================
 * Solo protege si NO estamos en páginas públicas
 */
$currentScript = basename($_SERVER['SCRIPT_FILENAME']);
$publicPages = ['index.php', 'login.php']; // Páginas sin protección

$isPublicPage = in_array($currentScript, $publicPages);

// Solo validar autenticación si NO es una página pública
if (!$isPublicPage) {
    if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id"])) {
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }
    
    // Validar expiración de token (opcional)
    if (isset($_SESSION["token_exp_admin"])) {
        $now = time();
        if ($now >= $_SESSION["token_exp_admin"]) {
            session_unset();
            session_destroy();
            header("Location: " . BASE_URL . "/index.php?error=session_expired");
            exit;
        }
    }
}