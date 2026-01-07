<?php
// 1. ACTIVAR ERRORES (IMPORTANTE PARA DEPURAR)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

$result = ['success' => false, 'message' => 'Solicitud inválida'];

try {

  // 2. VALIDAR MÉTODO
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
  }

  // 3. INCLUDES CON RUTAS RELATIVAS (MÁS SEGURO EN AJAX)
  // Subimos 2 niveles: de /front/ajax/ a /
  require_once "../../config/config.php";
  
  // VERIFICA EL NOMBRE EXACTO DE ESTE ARCHIVO EN TU CARPETA
  // Si tu archivo es "api.request.controller.php", usa esta línea:
  require_once "../../controllers/apiRequest.controller.php"; 
  // Si tu archivo es "apiRequest.controller.php", usa la otra. (Usa solo una).
  
  require_once "../../controllers/clientes.controller.php";

  $action = isset($_POST['action']) ? trim((string)$_POST['action']) : '';

  if ($action === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Action requerida']);
    exit;
  }

  switch ($action) {

    case 'obtener':
      $result = ClientesController::obtenerClientes();
      break;

    case 'crear':
      // Asegúrate de enviar $_POST, el controlador espera el array completo
      $result = ClientesController::crearCliente($_POST);
      break;

    case 'actualizar':
      $result = ClientesController::actualizarCliente($_POST);
      break;

    case 'eliminar':
      $result = ClientesController::eliminarCliente($_POST);
      break;

    default:
      http_response_code(400);
      $result = ['success' => false, 'message' => 'Action desconocida: ' . $action];
      break;
  }

} catch (Throwable $e) {
  // Loguear error real
  error_log('clientes.ajax.php Exception: ' . $e->getMessage());
  // Mostrar error en pantalla solo si estamos depurando (ayuda a ver qué pasa en la pestaña Network)
  http_response_code(500);
  $result = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
exit;
?>