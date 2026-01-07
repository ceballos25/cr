<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

$result = ['success' => false, 'message' => 'Solicitud inválida'];

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
  }

  require_once "../../config/config.php";
  require_once "../../controllers/apiRequest.controller.php"; 
  require_once "../../controllers/rifas.controller.php";

  $action = isset($_POST['action']) ? trim((string)$_POST['action']) : '';

  if ($action === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Action requerida']);
    exit;
  }

  switch ($action) {
    case 'obtener':
      $result = RifasController::obtenerRifas();
      break;
    case 'crear':
      $result = RifasController::crearRifa($_POST);
      break;
    case 'actualizar':
      $result = RifasController::actualizarRifa($_POST);
      break;
    case 'eliminar':
      $result = RifasController::eliminarRifa($_POST);
      break;
    default:
      http_response_code(400);
      $result = ['success' => false, 'message' => 'Action desconocida'];
      break;
  }

} catch (Throwable $e) {
  error_log('rifas.ajax.php Exception: ' . $e->getMessage());
  http_response_code(500);
  $result = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
exit;