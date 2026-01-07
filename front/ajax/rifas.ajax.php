<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Método denegado');
    require_once "../../config/config.php";
    require_once "../../controllers/apiRequest.controller.php"; 
    require_once "../../controllers/rifas.controller.php";
    $action = $_POST['action'] ?? '';
    switch ($action) {
        case 'obtener':   $res = RifasController::obtenerRifas(); break;
        case 'crear':     $res = RifasController::crearRifa($_POST); break;
        case 'actualizar':$res = RifasController::actualizarRifa($_POST); break;
        case 'eliminar':  $res = RifasController::eliminarRifa($_POST); break;
        default: throw new Exception('Acción inválida');
    }
    echo json_encode($res, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit;