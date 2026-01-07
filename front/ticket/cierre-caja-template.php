<?php
require_once "../../config/config.php";
require_once ROOT_PATH . "/controllers/apiRequest.controller.php";

$idCash = isset($_GET['id_cash']) ? (int)$_GET['id_cash'] : 0;
if ($idCash <= 0) { echo "Caja inválida"; exit; }

/* =========================
 * 1) CAJA
 * ========================= */
$cajaResp = ApiRequest::get(
  'cash_registers?select=*&linkTo=id_cash_register&equalTo=' . $idCash,
  []
);

if (!$cajaResp || empty($cajaResp->results)) { echo "Caja no encontrada"; exit; }
$caja = is_array($cajaResp->results) ? $cajaResp->results[0] : $cajaResp->results;

/* =========================
 * 2) SUCURSAL
 * ========================= */
$branchResp = ApiRequest::get(
  'branches?select=*&linkTo=id_branch&equalTo=' . ($caja->id_branch_cash_register ?? 0),
  []
);
$branch = !empty($branchResp->results)
  ? (is_array($branchResp->results) ? $branchResp->results[0] : $branchResp->results)
  : null;

/* =========================
 * 3) ENCARGADO DE CAJA
 * ========================= */
$adminResp = ApiRequest::get(
  'admins?select=title_admin&linkTo=id_admin&equalTo=' . ($caja->id_admin_cash_register ?? 0),
  []
);
$admin = !empty($adminResp->results)
  ? (is_array($adminResp->results) ? $adminResp->results[0] : $adminResp->results)
  : null;

/* =========================
 * 4) OBTENER RESUMEN (productos y métodos)
 * ========================= */
$resumenResp = ApiRequest::get(
  "cash_register_products?select=*&linkTo=id_cash_register_cash_register_product&equalTo={$idCash}",
  []
);
$productos = $resumenResp->results ?? [];
if (!is_array($productos)) $productos = [$productos];

$metodosResp = ApiRequest::get(
  "cash_register_payments?select=*&linkTo=id_cash_register_cash_register_payment&equalTo={$idCash}",
  []
);
$metodos = $metodosResp->results ?? [];
if (!is_array($metodos)) $metodos = [$metodos];

/* =========================
 * 4.1) MAP NOMBRES PRODUCTOS
 * ========================= */
$productIds = [];
foreach ($productos as $p) {
  $pid = (int)($p->id_product_cash_register_product ?? 0);
  if ($pid > 0) $productIds[$pid] = true;
}

$productNameById = [];
if (!empty($productIds)) {
  foreach (array_keys($productIds) as $pid) {
    $pResp = ApiRequest::get(
      'products?select=id_product,name_product&linkTo=id_product&equalTo=' . (int)$pid,
      []
    );
    if ($pResp && isset($pResp->status) && (int)$pResp->status === 200 && !empty($pResp->results)) {
      $p = is_array($pResp->results) ? $pResp->results[0] : $pResp->results;
      $productNameById[(int)$p->id_product] = (string)($p->name_product ?? '');
    }
  }
}

/* =========================
 * 4.2) MAP NOMBRES MÉTODOS DE PAGO
 * ========================= */
$metodoPagIds = [];
foreach ($metodos as $m) {
  $mid = (int)($m->id_payment_method_cash_register_payment ?? 0);
  if ($mid > 0) $metodoPagIds[$mid] = true;
}

$metodoPagNameById = [];
if (!empty($metodoPagIds)) {
  foreach (array_keys($metodoPagIds) as $mid) {
    $mResp = ApiRequest::get(
      'payment_methods?select=id_payment_method,name_payment_method&linkTo=id_payment_method&equalTo=' . (int)$mid,
      []
    );
    if ($mResp && isset($mResp->status) && (int)$mResp->status === 200 && !empty($mResp->results)) {
      $m = is_array($mResp->results) ? $mResp->results[0] : $mResp->results;
      $metodoPagNameById[(int)$m->id_payment_method] = (string)($m->name_payment_method ?? '');
    }
  }
}

/* =========================
 * HELPERS
 * ========================= */
function money($n) { return number_format((float)$n, 0, ',', '.'); }

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function cutText($text, $max = 18) {
  $t = trim((string)$text);
  if ($t === '') return 'Producto';
  if (mb_strlen($t) <= $max) return $t;
  return mb_substr($t, 0, $max - 1) . '…';
}

/* Datos cabecera */
$branchName = $branch->name_branch ?? 'CABALLOS';
$branchAddr = $branch->address_branch ?? '';
$branchCity = $branch->city_branch ?? '';
$branchPhone = $branch->phone_branch ?? '';

$encargado = $admin->title_admin ?? '—';
// 2. ********** FORMATO DESEADO (Día/Mes/Año Hora:Minuto AM/PM) **********
$formato_colombiano = 'd/m/Y h:i A';

// 3. ********** APLICAR EL FORMATO **********

// Para la APERTURA
$apertura = !empty($caja->opening_datetime_cash_register) 
    ? date($formato_colombiano, strtotime($caja->opening_datetime_cash_register)) 
    : 'N/A';

// Para el CIERRE (si no hay fecha de cierre, usa la hora actual con el formato correcto)
$cierre = !empty($caja->closing_datetime_cash_register) 
    ? date($formato_colombiano, strtotime($caja->closing_datetime_cash_register)) 
    : date($formato_colombiano);

$montoBase = (float)($caja->opening_amount_cash_register ?? 0);
$montoContado = (float)($caja->closing_amount_cash_register ?? 0);

// Calcular total vendido
$totalVendido = 0;
foreach ($metodos as $m) {
  $totalVendido += (float)($m->total_amount_cash_register_payment ?? 0);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Cierre Caja <?= esc($idCash) ?></title>
<style>
@page { margin: 0; }
* { box-sizing: border-box; }

body{
  font-family: monospace;
  font-size: 11.5px;
  width: 58mm;
  margin: 0 auto;
  padding: 8px 6px 10px;
  color: #000;
  line-height: 1.35;
}

.center{ text-align:center; }
.right{ text-align:right; }
.bold{ font-weight:700; }
.xs{ font-size:10px; }
.sm{ font-size:11px; }

.sep{
  margin: 8px 0 6px;
}
.sep::before{
  content: "-----------------------------";
  display:block;
  letter-spacing: .5px;
}

.logo{ margin-bottom: 6px; }
.logo img{
  width: 120px;
  max-width: 100%;
  display:inline-block;
}

.block p{ margin: 0; }

table{ width:100%; border-collapse:collapse; }
thead td{
  font-weight:700;
  padding: 4px 0 3px;
}
td{
  padding: 3px 0;
  vertical-align: top;
}

.col-item{ width: 30mm; }
.col-qty{ width: 10mm; }
.col-monto{ width: 18mm; text-align:right; }

.mini{
  font-size: 10.5px;
  line-height: 1.25;
}

.totalRow td{
  padding-top: 5px;
}
.total{
  font-size: 14px;
  font-weight: 800;
}

.btns{
  display:flex;
  gap:8px;
  justify-content:center;
  margin-bottom:8px;
}
.btn{
  border: 1px solid #000;
  background:#fff;
  padding: 6px 10px;
  font-size: 12px;
  cursor:pointer;
}

@media print{
  .btns{ display:none; }
  body{ padding: 0; }
}
</style>
</head>

<body>

<!-- BOTONES PREVIEW -->
<div class="btns">
  <button class="btn" onclick="window.print()">🖨️ Imprimir</button>
  <button class="btn" onclick="window.close()">✖ Cerrar</button>
</div>

<!-- LOGO -->
<div class="logo center">
  <img src="/assets/images/logos/ticket.png" alt="Caballos Revelo">
</div>

<!-- EMPRESA -->
<div class="center block">
  <p class="bold"><?= esc($branchName) ?></p>
  <?php if ($branchAddr): ?><p><?= esc($branchAddr) ?></p><?php endif; ?>
  <?php if ($branchCity): ?><p><?= esc($branchCity) ?></p><?php endif; ?>
  <?php if ($branchPhone): ?><p>Tel: <?= esc($branchPhone) ?></p><?php endif; ?>
</div>

<div class="sep"></div>

<!-- ENCABEZADO CIERRE -->
<div class="center block">
  <p class="bold">CIERRE DE CAJA #<?= esc($idCash) ?></p>
</div>

<div class="sep"></div>

<!-- INFO CAJA -->
<div class="block">
  <p>Encargado: <?= esc($encargado) ?></p>
  <p>Apertura: <?= esc($apertura) ?></p>
  <p>Cierre: <?= esc($cierre) ?></p>
</div>

<div class="sep"></div>

<!-- RESUMEN MONTOS -->
<table>
  <tr>
    <td class="bold">Monto Base</td>
    <td class="right"><?= money($montoBase) ?></td>
  </tr>
  <tr>
    <td class="bold">Total Vendido</td>
    <td class="right"><?= money($totalVendido) ?></td>
  </tr>
  <tr>
    <td class="bold">Contado</td>
    <td class="right"><?= money($montoContado) ?></td>
  </tr>
</table>

<div class="sep"></div>

<!-- MÉTODOS DE PAGO -->
<div class="block">
  <p class="bold">Métodos de Pago</p>
</div>

<table>
  <tbody>
  <?php if (!empty($metodos)): ?>
    <?php foreach ($metodos as $m):
      $midPay = (int)($m->id_payment_method_cash_register_payment ?? 0);
      $monto = (float)($m->total_amount_cash_register_payment ?? 0);
      $nombre = $metodoPagNameById[$midPay] ?? ('Método #' . $midPay);
    ?>
      <tr>
        <td class="col-item mini"><?= esc(cutText($nombre, 20)) ?></td>
        <td class="col-monto mini"><?= money($monto) ?></td>
      </tr>
    <?php endforeach; ?>
  <?php else: ?>
    <tr>
      <td colspan="2" class="center xs">Sin ventas</td>
    </tr>
  <?php endif; ?>
  </tbody>
</table>

<div class="sep"></div>

<!-- PRODUCTOS VENDIDOS -->
<div class="block">
  <p class="bold">Productos Vendidos</p>
</div>

<table>
  <thead>
    <tr>
      <td class="col-qty xs">Cant</td>
      <td class="col-item xs">Producto</td>
      <td class="col-monto xs">Total</td>
    </tr>
  </thead>
</table>

<table>
  <tbody>
  <?php if (!empty($productos)): ?>
    <?php foreach ($productos as $p):
      $pid = (int)($p->id_product_cash_register_product ?? 0);
      $qty = (int)($p->quantity_sold_cash_register_product ?? 0);
      $total = (float)($p->total_sales_cash_register_product ?? 0);
      $nombre = $productNameById[$pid] ?? ('Producto #' . $pid);
    ?>
      <tr>
        <td class="col-qty mini"><?= $qty ?></td>
        <td class="col-item mini"><?= esc(cutText($nombre, 14)) ?></td>
        <td class="col-monto mini"><?= money($total) ?></td>
      </tr>
    <?php endforeach; ?>
  <?php else: ?>
    <tr>
      <td colspan="3" class="center xs">Sin productos</td>
    </tr>
  <?php endif; ?>
  </tbody>
</table>

<div class="sep"></div>

<!-- FOOTER -->
<div class="center xs">
  Cierre completado correctamente
</div>

<div class="sep"></div>

<div class="center xs">
  Software POS - Desarrollado por<br>
  <span class="bold">Cristian Ceballos</span><br>
  324 589 4268
</div>

<!-- AUTO PRINT -->
<script>
window.onload = () => {
  setTimeout(() => {
    window.focus();
    window.print();
  }, 250);
};

window.onafterprint = () => {
  setTimeout(() => window.close(), 250);
};
</script>

</body>
</html>