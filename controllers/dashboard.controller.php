<?php

class DashboardController
{
  private const MAX_LIMIT_SALES     = 20000;
  private const MAX_LIMIT_DETAILS   = 60000;
  private const MAX_LIMIT_PRODUCTS  = 60000;
  private const MAX_LIMIT_CATEGORIES = 5000;
  private const MAX_LIMIT_ADMINS     = 2000;

  /* =========================
   * PUBLIC API
   * ========================= */

  public static function obtenerKPIs($data)
  {
    try {
      [$from, $to] = self::normalizeRange($data);

      $sales = self::getSalesCompletadasRange($from, $to);
      if (!$sales) return ['success' => true, 'data' => self::emptyKPIs()];

      $ventasNetas = 0.0;
      $transacciones = count($sales);
      $totalDescuentos = 0.0;

      foreach ($sales as $s) {
        $ventasNetas += (float)($s->total_amount_sale ?? 0);
        $totalDescuentos += (float)($s->discount_sale ?? 0);
      }

      $ticketPromedio = $transacciones > 0 ? ($ventasNetas / $transacciones) : 0.0;

      $details = self::getSaleDetailsRange($from, $to);
      $productsMap = self::getProductsMap();

      $margenBruto = 0.0;
      foreach ($details as $d) {
        $pid = (int)($d->id_product_sale_detail ?? 0);
        $qty = (int)($d->quantity_sale_detail ?? 0);
        $unit = (float)($d->unit_price_sale_detail ?? 0);

        $cost = isset($productsMap[$pid]) ? (float)($productsMap[$pid]->cost_product ?? 0) : 0.0;
        $margenBruto += ($unit - $cost) * $qty;
      }

      $margenPct = $ventasNetas > 0 ? (($margenBruto / $ventasNetas) * 100) : 0.0;

      return ['success' => true, 'data' => [
        'ventasNetas' => $ventasNetas,
        'margenBruto' => $margenBruto,
        'margenBrutoPorcentaje' => $margenPct,
        'ticketPromedio' => $ticketPromedio,
        'transacciones' => $transacciones,
        'totalDescuentos' => $totalDescuentos,
        'totalDevoluciones' => 0.0
      ]];

    } catch (Throwable $e) {
      return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
  }

  public static function obtenerTendenciaVentas($data)
  {
    try {
      [$from, $to] = self::normalizeRange($data);
      $sales = self::getSalesCompletadasRange($from, $to);

      $byDay = [];
      foreach ($sales as $s) {
        $day = self::toYmd($s->date_created_sale ?? null);
        if (!$day) continue;
        $byDay[$day] = ($byDay[$day] ?? 0) + (float)($s->total_amount_sale ?? 0);
      }
      ksort($byDay);

      $out = [];
      foreach ($byDay as $fecha => $ventas) $out[] = ['fecha' => $fecha, 'ventas' => $ventas];

      return ['success' => true, 'data' => $out];

    } catch (Throwable $e) {
      return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
  }

  public static function obtenerVentasPorCategoria($data)
  {
    try {
      [$from, $to] = self::normalizeRange($data);

      $details = self::getSaleDetailsRange($from, $to);
      $productsMap = self::getProductsMap();
      $categoriesMap = self::getCategoriesMap();

      $sum = []; // catId => total ventas
      foreach ($details as $d) {
        $pid = (int)($d->id_product_sale_detail ?? 0);
        if (!isset($productsMap[$pid])) continue;

        $catId = (int)($productsMap[$pid]->id_category_product ?? 0);
        $sum[$catId] = ($sum[$catId] ?? 0) + (float)($d->subtotal_sale_detail ?? 0);
      }

      arsort($sum);

      $out = [];
      foreach ($sum as $catId => $value) {
        $label = isset($categoriesMap[$catId]) ? ($categoriesMap[$catId]->name_category ?? ('Cat #' . $catId)) : ('Cat #' . $catId);
        $out[] = ['label' => $label, 'value' => $value];
      }

      return ['success' => true, 'data' => $out];

    } catch (Throwable $e) {
      return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
  }

  public static function obtenerDistribucionStock()
  {
    try {
      $products = self::getProductsAll();
      $categoriesMap = self::getCategoriesMap();

      $sum = []; // catId => stock total
      foreach ($products as $p) {
        if (($p->status_product ?? '') !== 'activo') continue;
        $catId = (int)($p->id_category_product ?? 0);
        $sum[$catId] = ($sum[$catId] ?? 0) + (int)($p->stock_current_product ?? 0);
      }

      arsort($sum);

      $out = [];
      foreach ($sum as $catId => $value) {
        $label = isset($categoriesMap[$catId]) ? ($categoriesMap[$catId]->name_category ?? ('Cat #' . $catId)) : ('Cat #' . $catId);
        $out[] = ['label' => $label, 'value' => $value];
      }

      return ['success' => true, 'data' => $out];

    } catch (Throwable $e) {
      return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
  }

  public static function obtenerTopProductos($data)
  {
    try {
      [$from, $to] = self::normalizeRange($data);

      $details = self::getSaleDetailsRange($from, $to);
      $productsMap = self::getProductsMap();
      $categoriesMap = self::getCategoriesMap();

      $agg = []; // pid => vendidos, ingresos
      foreach ($details as $d) {
        $pid = (int)($d->id_product_sale_detail ?? 0);
        $agg[$pid]['vendidos'] = ($agg[$pid]['vendidos'] ?? 0) + (int)($d->quantity_sale_detail ?? 0);
        $agg[$pid]['ingresos'] = ($agg[$pid]['ingresos'] ?? 0) + (float)($d->subtotal_sale_detail ?? 0);
      }

      uasort($agg, fn($a,$b) => ($b['ingresos'] <=> $a['ingresos']));

      $out = [];
      foreach ($agg as $pid => $m) {
        if (!isset($productsMap[$pid])) continue;

        $p = $productsMap[$pid];
        $catId = (int)($p->id_category_product ?? 0);

        $stock = (int)($p->stock_current_product ?? 0);
        $min   = (int)($p->stock_minimum_product ?? 0);

        $status = 'bueno';
        if ($stock <= $min) $status = 'critico';
        else if ($stock <= ($min * 2)) $status = 'bajo';

        $out[] = [
          'nombre' => $p->name_product ?? ('Producto #' . $pid),
          'categoria' => $categoriesMap[$catId]->name_category ?? ('Cat #' . $catId),
          'stock' => $stock,
          'vendidos' => (int)$m['vendidos'],
          'ingresos' => (float)$m['ingresos'],
          'status' => $status
        ];

        if (count($out) >= 10) break;
      }

      return ['success' => true, 'data' => $out];

    } catch (Throwable $e) {
      return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
  }

  public static function obtenerStockCritico()
  {
    try {
      $products = self::getProductsAll();
      $categoriesMap = self::getCategoriesMap();

      $out = [];
      foreach ($products as $p) {
        if (($p->status_product ?? '') !== 'activo') continue;

        $stock = (int)($p->stock_current_product ?? 0);
        $min   = (int)($p->stock_minimum_product ?? 0);

        if ($stock <= $min) {
          $catId = (int)($p->id_category_product ?? 0);
          $out[] = [
            'nombre' => $p->name_product ?? '',
            'categoria' => $categoriesMap[$catId]->name_category ?? '',
            'stock' => $stock,
            'minimo' => $min
          ];
        }
      }

      usort($out, fn($a,$b)=> ($a['stock'] <=> $b['stock']));
      return ['success' => true, 'data' => array_slice($out, 0, 20)];

    } catch (Throwable $e) {
      return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
  }

  public static function obtenerRendimientoPersonal($data)
  {
    try {
      [$from, $to] = self::normalizeRange($data);
      $sales = self::getSalesCompletadasRange($from, $to);
      $adminsMap = self::getAdminsMap();

      $agg = []; // adminId => transacciones, ventas
      foreach ($sales as $s) {
        $aid = (int)($s->id_admin_sale ?? 0);
        $agg[$aid]['transacciones'] = ($agg[$aid]['transacciones'] ?? 0) + 1;
        $agg[$aid]['ventas'] = ($agg[$aid]['ventas'] ?? 0) + (float)($s->total_amount_sale ?? 0);
      }

      uasort($agg, fn($a,$b)=> ($b['ventas'] <=> $a['ventas']));
      $top = array_slice($agg, 0, 5, true);

      $out = [];
      foreach ($top as $aid => $m) {
        $out[] = [
          'vendedor' => $adminsMap[$aid]->title_admin ?? ('Admin #' . $aid),
          'transacciones' => (int)$m['transacciones'],
          'ventas' => (float)$m['ventas']
        ];
      }

      return ['success' => true, 'data' => $out];

    } catch (Throwable $e) {
      return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
  }

  /* =========================
   * INTERNAL API CALLS (ApiRequest)
   * ========================= */

  private static function apiGet(string $query)
  {
    return ApiRequest::get($query, []);
  }

  private static function toArrayOfObjects($results): array
  {
    if (empty($results)) return [];
    return is_array($results) ? $results : [$results];
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

  private static function getSalesCompletadasRange(string $from, string $to): array
  {
    $query = 'sales?select=' . implode(',', [
      'id_sale',
      'id_admin_sale',
      'total_amount_sale',
      'discount_sale',
      'status_sale',
      'date_created_sale'
    ]);

    $query .= '&linkTo=date_created_sale&between1=' . urlencode($from) . '&between2=' . urlencode($to);
    $query .= '&filterTo=status_sale&inTo=completado';
    $query .= '&startAt=0&endAt=' . self::MAX_LIMIT_SALES;

    $resp = self::apiGet($query);
    return ($resp && !empty($resp->results)) ? self::toArrayOfObjects($resp->results) : [];
  }

  private static function getSaleDetailsRange(string $from, string $to): array
  {
    $query = 'sale_details?select=' . implode(',', [
      'id_sale_sale_detail',
      'id_product_sale_detail',
      'quantity_sale_detail',
      'unit_price_sale_detail',
      'subtotal_sale_detail',
      'date_created_sale_detail'
    ]);

    $query .= '&linkTo=date_created_sale_detail&between1=' . urlencode($from) . '&between2=' . urlencode($to);
    $query .= '&startAt=0&endAt=' . self::MAX_LIMIT_DETAILS;

    $resp = self::apiGet($query);
    return ($resp && !empty($resp->results)) ? self::toArrayOfObjects($resp->results) : [];
  }

  private static function getProductsAll(): array
  {
    $resp = self::apiGet(
      'products?select=id_product,id_category_product,name_product,cost_product,stock_current_product,stock_minimum_product,status_product' .
      '&startAt=0&endAt=' . self::MAX_LIMIT_PRODUCTS
    );
    return ($resp && !empty($resp->results)) ? self::toArrayOfObjects($resp->results) : [];
  }

  private static function getProductsMap(): array
  {
    $items = self::getProductsAll();
    $map = [];
    foreach ($items as $p) $map[(int)$p->id_product] = $p;
    return $map;
  }

  private static function getCategoriesMap(): array
  {
    $resp = self::apiGet(
      'categories?select=id_category,name_category,status_category' .
      '&startAt=0&endAt=' . self::MAX_LIMIT_CATEGORIES
    );

    $items = ($resp && !empty($resp->results)) ? self::toArrayOfObjects($resp->results) : [];
    $map = [];
    foreach ($items as $c) $map[(int)$c->id_category] = $c;
    return $map;
  }

  private static function getAdminsMap(): array
  {
    $resp = self::apiGet(
      'admins?select=id_admin,title_admin,status_admin' .
      '&startAt=0&endAt=' . self::MAX_LIMIT_ADMINS
    );

    $items = ($resp && !empty($resp->results)) ? self::toArrayOfObjects($resp->results) : [];
    $map = [];
    foreach ($items as $a) $map[(int)$a->id_admin] = $a;
    return $map;
  }

  private static function emptyKPIs(): array
  {
    return [
      'ventasNetas' => 0,
      'margenBruto' => 0,
      'margenBrutoPorcentaje' => 0,
      'ticketPromedio' => 0,
      'transacciones' => 0,
      'totalDescuentos' => 0,
      'totalDevoluciones' => 0
    ];
  }
  
}