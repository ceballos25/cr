<?php
header('Content-Type: application/json; charset=utf-8');
require_once "../../config/config.php";
require_once "../../controllers/apiRequest.controller.php"; 
require_once "../../controllers/tickets.controller.php";

$action = $_POST['action'] ?? '';
$result = [];

try {
    switch ($action) {
        case 'obtener_ocupados': 
            $idRifa = $_POST['id_raffle'] ?? 0;
            // Retorna directamente el array de números [1, 5, 20]
            $result = TicketsController::obtenerOcupados($idRifa); 
            break;
            
        default:
            $result = ['error' => 'Acción no válida'];
    }
} catch (Throwable $e) { 
    $result = []; // Retorna vacío en error para no romper el JS
}

echo json_encode($result);
?>