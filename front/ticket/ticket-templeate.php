<?php
require_once "../../config/config.php";
require_once ROOT_PATH . "/controllers/apiRequest.controller.php";

$idSale = isset($_GET['id_sale']) ? (int)$_GET['id_sale'] : 0;
if ($idSale <= 0) { echo "Venta inválida"; exit; }

/* =========================
 * 1) VENTA
 * ========================= */
$ventaResp = ApiRequest::get(
  'sales?select=*&linkTo=id_sale&equalTo=' . $idSale,
  []
);

if (!$ventaResp || empty($ventaResp->results)) { echo "Venta no encontrada"; exit; }
$venta = is_array($ventaResp->results) ? $ventaResp->results[0] : $ventaResp->results;

/* =========================
 * 2) DETALLES
 * ========================= */
$detResp = ApiRequest::get(
  'sale_details?select=*&linkTo=id_sale_sale_detail&equalTo=' . $idSale,
  []
);
$items = $detResp->results ?? [];
if (!is_array($items)) $items = [$items];

/* =========================
 * 2.1) MAP NOMBRES PRODUCTOS
 * ========================= */
$productIds = [];
foreach ($items as $it) {
  $pid = (int)($it->id_product_sale_detail ?? 0);
  if ($pid > 0) $productIds[$pid] = true;
}

$productNameById = [];

if (!empty($productIds)) {

  $ids = implode(',', array_keys($productIds));

  // ✅ Tu API soporta IN con: filterTo + inTo (según GetModel::getDataRange)
  $productsResp = ApiRequest::get(
    'products?select=id_product,name_product&filterTo=id_product&inTo=' . $ids,
    []
  );

  if ($productsResp && isset($productsResp->status) && (int)$productsResp->status === 200 && !empty($productsResp->results)) {
    $prods = is_array($productsResp->results) ? $productsResp->results : [$productsResp->results];

    foreach ($prods as $p) {
      $productNameById[(int)$p->id_product] = (string)($p->name_product ?? '');
    }
  } else {
    // Fallback seguro: 1 request por producto (por si tu endpoint no arma bien filterTo/inTo)
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
}


/* =========================
 * 3) SUCURSAL
 * ========================= */
$branchResp = ApiRequest::get(
  'branches?select=*&linkTo=id_branch&equalTo=' . ($venta->id_branch_sale ?? 0),
  []
);
$branch = !empty($branchResp->results)
  ? (is_array($branchResp->results) ? $branchResp->results[0] : $branchResp->results)
  : null;

/* =========================
 * 4) VENDEDOR
 * ========================= */
$adminResp = ApiRequest::get(
  'admins?select=title_admin&linkTo=id_admin&equalTo=' . ($venta->id_admin_sale ?? 0),
  []
);
$admin = !empty($adminResp->results)
  ? (is_array($adminResp->results) ? $adminResp->results[0] : $adminResp->results)
  : null;

/* =========================
 * 5) CLIENTE
 * ========================= */
$customer = null;
if (!empty($venta->id_customer_sale)) {
  $customerResp = ApiRequest::get(
    'customers?select=name_customer,document_customer&linkTo=id_customer&equalTo=' . (int)$venta->id_customer_sale,
    []
  );
  $customer = !empty($customerResp->results)
    ? (is_array($customerResp->results) ? $customerResp->results[0] : $customerResp->results)
    : null;
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

/* QR apuntando a tu endpoint (dinámico) */
$ticketUrl = "http://caballosrevelo.test/front/ticket/ticket-template.php?id_sale={$idSale}";
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=" . urlencode($ticketUrl);

/* Datos cabecera */
$branchName = $branch->name_branch ?? 'CABALLOS';
$branchAddr = $branch->address_branch ?? '';
$branchCity = $branch->city_branch ?? '';
$branchPhone = $branch->phone_branch ?? '';

$ref = $venta->reference_number_sale ?? ('CNL-' . str_pad($idSale, 5, '0', STR_PAD_LEFT));
// Formato deseado: Día/Mes/Año Hora:Minuto AM/PM
$formato_colombiano = 'd/m/Y h:i A';

// Aplicamos el formato y la hora de Colombia a la fecha de creación de la venta
$fecha = !empty($venta->date_created_sale) 
    ? date($formato_colombiano, strtotime($venta->date_created_sale)) 
    : date($formato_colombiano); // Si no hay fecha, usa la hora actual

$vendedor = $admin->title_admin ?? '—';
$clienteNombre = $customer->name_customer ?? 'Consumidor final';
$clienteDoc = $customer->document_customer ?? '—';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Ticket <?= esc($ref) ?></title>
<style>
@page { margin: 0; }
* { box-sizing: border-box; }

body{
  font-family: monospace;
  font-size: 11.5px;
  width: 58mm;          /* 58mm REAL */
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

.col-qty{ width: 8mm; }
.col-name{ width: 26mm; }
.col-unit{ width: 12mm; }
.col-sub{ width: 12mm; text-align:right; }

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

.qr img{
  width: 120px;
  height: 120px;
  display:block;
  margin: 6px auto 4px;
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
  <img src="/assets/images/logos/ticket.png" alt="CBALLOS REVELO">
</div>

<!-- EMPRESA -->
<div class="center block">
  <p class="bold"><?= esc($branchName) ?></p>
  <?php if ($branchAddr): ?><p><?= esc($branchAddr) ?></p><?php endif; ?>
  <?php if ($branchCity): ?><p><?= esc($branchCity) ?></p><?php endif; ?>
  <?php if ($branchPhone): ?><p>Tel: <?= esc($branchPhone) ?></p><?php endif; ?>
</div>

<div class="sep"></div>

<!-- INFO VENTA -->
<div class="block">
  <p>Ticket: <span class="bold"><?= esc($ref) ?></span></p>
  <p>Fecha: <?= esc($fecha) ?></p>
  <p>Vendedor: <?= esc($vendedor) ?></p>
  <p>Cliente: <?= esc($clienteNombre) ?></p>
  <p>Cédula: <?= esc($clienteDoc) ?></p>
</div>

<div class="sep"></div>

<!-- HEADER ITEMS -->
<table>
  <thead>
    <tr>
      <td class="col-qty">Cant</td>
      <td class="col-name">Producto</td>
      <td class="col-unit right">P.U</td>
      <td class="col-sub">Subt</td>
    </tr>
  </thead>
</table>

<!-- ITEMS -->
<table>
  <tbody>
  <?php foreach ($items as $i):
    $pid = (int)($i->id_product_sale_detail ?? 0);
    $qty = (int)($i->quantity_sale_detail ?? 0);
    $unit = (float)($i->unit_price_sale_detail ?? 0);
    $sub  = (float)($i->subtotal_sale_detail ?? ($qty * $unit));
    $name = $productNameById[$pid] ?? ('Producto #' . $pid);
  ?>
    <tr>
      <td class="col-qty"><?= $qty ?></td>
      <td class="col-name mini"><?= esc(cutText($name, 18)) ?></td>
      <td class="col-unit right mini"><?= money($unit) ?></td>
      <td class="col-sub mini"><?= money($sub) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<div class="sep"></div>

<!-- TOTAL -->
<table>
  <tr class="totalRow">
    <td class="bold">TOTAL</td>
    <td class="right total"><?= money($venta->total_amount_sale ?? 0) ?></td>
  </tr>
</table>

<div class="sep"></div>

<!-- QR -->
<div class="center qr">
  <img src="<?= esc($qrUrl) ?>" alt="QR Ticket">
  <div class="xs">Escanea para ver este ticket</div>
</div>

<div class="sep"></div>

<!-- FOOTER -->
<div class="center xs">
  ¡Gracias por su compra!<br>
  Vuelva pronto
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
  // En algunos navegadores, el print automático requiere interacción previa.
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
