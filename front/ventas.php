<?php
require_once "../config/config.php";
$page_title = "Ventas";
include_once ROOT_PATH . "/includes/head.php";
?>
<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-sidebartype="full">
    <?php include_once ROOT_PATH . "/includes/sidebar.php" ?>
    <div class="body-wrapper">
        <?php include_once ROOT_PATH . "/includes/header.php" ?>
        <div class="body-wrapper-inner">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0 fw-bold"><i class="ti ti-shopping-cart me-1"></i> Ventas</h2>
                    <button class="btn btn-primary" onclick="abrirModalVenta()"><i class="ti ti-plus"></i> Nueva Venta</button>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body py-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-9">
                                <label class="form-label small fw-bold">Buscador</label>
                                <input type="text" id="searchVentas" class="form-control form-control-sm" placeholder="Buscar por cliente o rifa...">
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-outline-secondary btn-sm w-100" onclick="limpiarFiltros()"><i class="ti ti-refresh"></i> Limpiar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-hover table-striped align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="ps-3" width="60">ID</th>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Rifa</th>
                                        <th>Total</th>
                                        <th>Método</th>
                                        <th>Estado</th>
                                        <th width="100">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyTabla">
                                    <tr><td colspan="8" class="text-center py-5">Cargando ventas...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted" id="infoPaginacion"></small>
                            <nav><ul class="pagination pagination-sm mb-0" id="contenedorPaginacion"></ul></nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php 
$extra_js = '<script src="'.ASSETS_URL.'/js/ventas.js"></script>';
include_once ROOT_PATH . "/includes/footer.php"; 
?>