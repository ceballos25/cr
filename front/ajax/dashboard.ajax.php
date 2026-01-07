<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json; charset=utf-8');

require_once "../../config/config.php";
require_once ROOT_PATH . "/controllers/apiRequest.controller.php";
require_once ROOT_PATH . "/controllers/ventas.controller.php";

try {

  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
  }

  $action = isset($_POST['action']) ? trim((string)$_POST['action']) : '';
  if ($action === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Action requerida']);
    exit;
  }

  switch ($action) {

    case 'obtenerDashboard':
      $result = DashboardFromVentas::obtenerDashboard($_POST);
      break;

    case 'obtenerSucursales':
      $result = VentasController::obtenerSucursales();
      break;

    case 'obtenerClientes':
      $result = VentasController::obtenerClientes();
      break;

    case 'obtenerTopProductosVendidos':
      $result = VentasController::obtenerTopProductosVendidos($_POST);
      break;

    case 'obtenerStockMasBajo':
      $result = VentasController::obtenerStockMasBajo();
      break;

    default:
      http_response_code(400);
      $result = ['success' => false, 'message' => 'Action desconocida: ' . $action];
      break;
  }

} catch (Throwable $e) {
  error_log('dashboard.ajax.php Exception: ' . $e->getMessage());
  http_response_code(500);
  $result = ['success' => false, 'message' => 'Error interno'];
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
exit;


/* =========================================================
 * DASHBOARD FROM VENTAS
 * - Usa VentasController para ventas (igualito)
 * - Agrega totalGastos desde expenses
 * ========================================================= */
class DashboardFromVentas
{
  public static function obtenerDashboard($post)
  {
    // 1) Ventas (tu lógica actual)
    $ventasResp = VentasController::obtenerVentas($post);
    if (empty($ventasResp['success'])) return $ventasResp;

    $ventas = is_array($ventasResp['data'] ?? null) ? $ventasResp['data'] : [];

    $totalVentas = 0.0;
    $transacciones = count($ventas);
    $totalArticulos = 0;

    foreach ($ventas as $v) {
      $totalVentas += (float)($v->total_amount_sale ?? 0);
      $totalArticulos += (int)($v->total_items_sale ?? 0);
    }

    $ticketPromedio = $transacciones > 0 ? ($totalVentas / $transacciones) : 0.0;

    // 2) ✅ Gastos (expenses) en el mismo rango de fechas del dashboard
    [$from, $to] = self::normalizeRange($post);
    $totalGastos = self::sumExpenses($from, $to);

    // 3) Tendencia por día
    $porDia = [];
    foreach ($ventas as $v) {
      $fecha = self::toYmd($v->date_created_sale ?? null);
      if (!$fecha) continue;
      $porDia[$fecha] = ($porDia[$fecha] ?? 0) + (float)($v->total_amount_sale ?? 0);
    }
    ksort($porDia);

    $tendencia = [];
    foreach ($porDia as $fecha => $ventasDia) {
      $tendencia[] = ['fecha' => $fecha, 'ventas' => $ventasDia];
    }

    // 4) últimas 10
    $ultimas = $ventas;
    usort($ultimas, fn($a,$b) => ((int)($b->id_sale ?? 0)) <=> ((int)($a->id_sale ?? 0)));
    $ultimas = array_slice($ultimas, 0, 10);

    // 5) ✅ Respuesta final (aquí va totalGastos)
    return [
      'success' => true,
      'data' => [
        'kpis' => [
          'totalVentas' => $totalVentas,
          'transacciones' => $transacciones,
          'ticketPromedio' => $ticketPromedio,
          'totalArticulos' => $totalArticulos,
          'totalGastos' => $totalGastos, // ✅ IMPORTANTÍSIMO
        ],
        'tendencia' => $tendencia,
        'ultimasVentas' => $ultimas,
      ]
    ];
  }

  /**
   * ✅ SUMA GASTOS robusta:
   * - Trae amount + status + fecha
   * - Filtra local por status (por si la API no filtra bien)
   */
  private static function sumExpenses(string $from, string $to): float
  {
    $query = 'expenses?select=amount_expense,status_expense,date_created_expense'
      . '&linkTo=date_created_expense&between1=' . urlencode($from) . '&between2=' . urlencode($to)
      . '&startAt=0&endAt=20000';

    $resp = ApiRequest::get($query, []);
    if (!$resp || empty($resp->results)) return 0.0;

    $items = is_array($resp->results) ? $resp->results : [$resp->results];

    $sum = 0.0;
    foreach ($items as $e) {
      $status = strtolower(trim((string)($e->status_expense ?? '')));
      if ($status !== 'completado') continue;
      $sum += (float)($e->amount_expense ?? 0);
    }
    return $sum;
  }

  private static function normalizeRange($data): array
  {
    $desde = isset($data['fechaDesde']) ? trim((string)$data['fechaDesde']) : '';
    $hasta = isset($data['fechaHasta']) ? trim((string)$data['fechaHasta']) : '';

    if ($desde !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) $desde = date('Y-m-d', strtotime($desde));
    if ($hasta !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) $hasta = date('Y-m-d', strtotime($hasta));

    if ($desde === '') $desde = date('Y-m-01');
    if ($hasta === '') $hasta = date('Y-m-d');

    $from = $desde . ' 00:00:00';
    $to   = $hasta . ' 23:59:59';
    return [$from, $to];
  }

  private static function toYmd($date): string
  {
    if (empty($date)) return '';
    $ts = strtotime((string)$date);
    if (!$ts) return '';
    return date('Y-m-d', $ts);
  }
}
