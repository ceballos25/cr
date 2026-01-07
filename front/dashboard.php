<?php
require_once "../config/config.php";
$page_title = "Dashboard";
include_once ROOT_PATH . "/includes/head.php";
?>
<script>
window.API_URL = "<?= BASE_URL ?>/front/ajax/dashboard.ajax.php";
</script>

<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">

    <?php include_once ROOT_PATH . "/includes/sidebar.php" ?>

    <div class="body-wrapper">
        <?php include_once ROOT_PATH . "/includes/header.php" ?>

        <div class="body-wrapper-inner">
            <div class="container-fluid">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="mb-0"><i class="ti ti-dashboard me-1"></i> Dashboard de Ventas</h2>
                    </div>

                    <button class="btn btn-outline-primary btn-sm" id="btnRefrescar">
                        <i class="ti ti-refresh"></i> Actualizar
                    </button>
                </div>

                <!-- KPIs -->
                <div class="row mb-4">
                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <div class="card card-body shadow-sm border-0">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-lg rounded bg-primary-lt me-3">
                                    <i class="ti ti-currency-dollar fs-3 text-primary"></i>
                                </span>
                                <div class="flex-grow-1">
                                    <small class="text-muted d-block text-truncate">Total Ventas</small>
                                    <span class="h3 fw-bold text-dark mb-0" id="kpiTotalVentas">$0.00</span>
                                    <small class="text-muted d-none" id="kpiRango">—</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <div class="card card-body shadow-sm border-0">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-lg rounded bg-info-lt me-3">
                                    <i class="ti ti-shopping-cart fs-3 text-info"></i>
                                </span>
                                <div class="flex-grow-1">
                                    <small class="text-muted d-block text-truncate">Total Transacciones</small>
                                    <span class="h3 fw-bold text-dark mb-0" id="kpiTransacciones">0</span>
                                    <small class="text-muted">tickets</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <div class="card card-body shadow-sm border-0">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-lg rounded bg-warning-lt me-3">
                                    <i class="ti ti-receipt-2 fs-3 text-warning"></i>
                                </span>
                                <div class="flex-grow-1">
                                    <small class="text-muted d-block text-truncate">Ticket Promedio</small>
                                    <span class="h3 fw-bold text-dark mb-0" id="kpiTicketPromedio">$0.00</span>
                                    <small class="text-muted">por venta</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <div class="card card-body shadow-sm border-0">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-lg rounded bg-success-lt me-3">
                                    <i class="ti ti-circle-check fs-3 text-success"></i>
                                </span>
                                <div class="flex-grow-1">
                                    <small class="text-muted d-block text-truncate">Artículos Vendidos</small>
                                    <span class="h3 fw-bold text-success mb-0" id="kpiArticulos">0</span>
                                    <small class="text-muted">unidades</small>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- FILTROS -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0"><i class="ti ti-filter me-1"></i> Filtros</h6>
                            <small class="text-muted">Tip: cambia “Período” para auto-fechas</small>
                        </div>

                        <div class="row g-2 align-items-end">
                            <div class="col-6 col-md-2">
                                <label class="form-label small">Desde</label>
                                <input type="date" id="filterFechaDesde" class="form-control form-control-sm">
                            </div>

                            <div class="col-6 col-md-2">
                                <label class="form-label small">Hasta</label>
                                <input type="date" id="filterFechaHasta" class="form-control form-control-sm">
                            </div>

                            <div class="col-6 col-md-2">
                                <label class="form-label small">Período</label>
                                <select id="filterPeriodo" class="form-select form-select-sm">
                                    <option value="">Personalizado</option>
                                    <option value="hoy">Hoy</option>
                                    <option value="semana">Semana</option>
                                    <option value="mes">Mes</option>
                                    <option value="ano">Año</option>
                                </select>
                            </div>

                            <div class="col-6 col-md-2">
                                <label class="form-label small">Sucursal</label>
                                <select id="filterSucursal" class="form-select form-select-sm">
                                    <option value="">Todas</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4 d-flex gap-2">
                                <button class="btn btn-primary btn-sm w-100" id="btnAplicar">
                                    <i class="ti ti-search"></i> Buscar
                                </button>
                                <button class="btn btn-outline-secondary btn-sm w-100" id="btnLimpiar">
                                    <i class="ti ti-refresh"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BUSCADOR -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="text" id="searchReferencia" class="form-control form-control-sm"
                                    placeholder="🔍 Buscar por referencia...">
                            </div>
                            <div class="col-md-6">
                                <select id="filterCliente" class="form-select form-select-sm">
                                    <option value="">Todos los clientes</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CHART + LISTA -->
                <div class="row g-3">
                    <div class="col-lg-7">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="card-title mb-0"><i class="ti ti-chart-line me-1"></i> Tendencia de
                                        Ventas</h5>
                                    <span class="badge bg-dark" id="badgeTendencia">—</span>
                                </div>
                                <div id="chartVentasDiarias" style="height: 320px;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex flex-column">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="card-title mb-0">
                            <i class="ti ti-list-check me-1"></i> Últimas Transacciones
                            </h5>
                            <span class="badge bg-primary" id="badgeUltimas">10</span>
                        </div>

                        <!-- 👇 SCROLL AQUÍ -->
                        <div id="listaUltimasVentas" class="overflow-auto flex-grow-1" style="max-height: 320px;">
                            <p class="text-center text-muted py-4">
                            <i class="ti ti-hourglass-split"></i> Cargando...
                            </p>
                        </div>

                        </div>
                    </div>
                    </div>

                </div>

                <!-- TORTAS -->
                <div class="row g-3 mt-1">
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="card-title mb-0"><i class="ti ti-chart-pie me-1"></i> Top 10 más vendidos
                                    </h5>
                                    <span class="badge bg-success" id="badgeTopVendidos">—</span>
                                </div>
                                <div id="chartTopVendidos" style="height: 320px;"></div>
                                <small class="text-muted d-block mt-2">Basado en cantidad vendida (rango de fechas +
                                    filtros).</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="card-title mb-0"><i class="ti ti-alert-triangle me-1"></i> Top 10 stock
                                        más bajo</h5>
                                    <span class="badge bg-warning text-dark" id="badgeStockBajo">—</span>
                                </div>
                                <div id="chartStockBajo" style="height: 320px;"></div>
                                <small class="text-muted d-block mt-2">Productos con menor stock actual (sin
                                    filtros).</small>
                            </div>
                        </div>
                    </div>

                    <!-- DONUT Ventas vs Gastos -->

                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="card-title mb-0">
                                        <i class="ti ti-chart-pie me-1"></i> Ventas vs Gastos
                                    </h5>
                                    <span class="badge bg-dark" id="badgeVentasGastos">—</span>
                                </div>

                                <div id="chartVentasVsGastos" style="height: 320px;"></div>
                                <small class="text-muted d-block mt-2" id="subVentasGastos">—</small>
                            </div>
                        </div>
                    </div>

                </div>


            </div>
        </div>

    </div>
</div>

<?php
$extra_js = '
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js"></script>
<script src="' . ASSETS_URL . '/js/dashboard.js"></script>';
include_once ROOT_PATH . "/includes/footer.php";
?>