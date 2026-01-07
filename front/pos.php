<?php
require_once "../config/config.php";
$page_title = "Punto de Venta";
include_once ROOT_PATH . "/includes/head.php";
?>

<div class="page-wrapper m-3" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    </a>
    <div class="body-wrapper">
        <div class="p-2 border-bottom bg-white d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <span id="badgeCaja" class="badge bg-danger">Caja: Cerrada</span>
            </div>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-dark" id="btnCerrarCaja" disabled>
                    <i class="ti ti-lock"></i> Cerrar caja
                </button>
            </div>
        </div>

        <div class="body-wrapper-inner">
            <div class="container-fluid" style="padding: 0;">
                <div class="row g-0" style="min-height: calc(100vh - 120px);">
                    <!-- COLUMNA 1: PRODUCTOS (col-lg-8 = 65% en desktop, 100% en móvil) -->
                    <div class="col-lg-8" style="overflow: hidden; display: flex; flex-direction: column; order-lg-1;">

                        <!-- TOP: BÚSQUEDA Y VISTA DE PRODUCTOS -->
                        <div style="flex: 1; display: flex; flex-direction: column; overflow: hidden;">

                            <!-- BÚSQUEDA -->
                            <div class="p-2 border-bottom">
                                <div class="row g-1">
                                    <div class="col-8">
                                        <input type="text" id="searchProductos" class="form-control form-control-sm"
                                            placeholder="🔍 Buscar...">
                                    </div>
                                    <div class="col-4">
                                        <select id="filterCategoria" class="form-select form-select-sm">
                                            <option value="">Todas</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- GRILLA DE PRODUCTOS -->
                            <div style="flex: 1; overflow-y: auto;">
                                <div class="p-2">
                                    <div id="productosContenedor" class="row g-2">
                                        <div class="col-12 text-center text-muted py-5">Cargando productos...</div>
                                    </div>
                                </div>
                            </div>

                            <!-- PAGINACIÓN -->
                            <div class="p-2 border-top text-center">
                                <nav>
                                    <ul class="pagination pagination-sm justify-content-center mb-0" id="paginacion">
                                    </ul>
                                </nav>
                            </div>

                        </div>

                    </div>

                    <!-- COLUMNA 2: CARRITO + CALCULADORA (col-lg-4 = 35% en desktop, 100% en móvil) -->
                    <div class="col-lg-4"
                        style="background: #f8f9fa; overflow-y: auto; order-lg-2; border-lg-start: 1px solid #dee2e6;">
                        <div class="p-2">
                            <!-- ENCABEZADO CARRITO -->
                            <h6 class="mb-2 d-lg-none"><i class="ti ti-shopping-cart"></i> Carrito</h6>

                            <!-- ITEMS CARRITO -->
                            <div id="carritoDetallado" class="mb-2" style="max-height: 150px; overflow-y: auto;">
                                <p class="text-muted text-center py-1" style="font-size: 13px;">Carrito vacío</p>
                            </div>

                            <!-- SEPARADOR -->
                            <hr class="my-2">

                            <!-- TOTALES -->
                            <div class="mb-2">
                                <div class="d-flex justify-content-between mb-1" style="font-size: 13px;">
                                    <span class="text-muted">Subtotal:</span>
                                    <strong id="lblSubtotal">$0.00</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1" style="font-size: 13px;">
                                    <span class="text-muted">Impuesto:</span>
                                    <strong id="lblImpuesto">$0.00</strong>
                                </div>
                                <div class="border-top pt-1">
                                    <div class="d-flex justify-content-between" style="font-size: 13px;">
                                        <strong>Total:</strong>
                                        <strong class="text-success" id="lblTotal">$0.00</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- CLIENTE -->
                            <div class="mb-2">
                                <label class="form-label small fw-bold mb-1">Cliente</label>
                                <select id="selectCliente" class="form-select form-select-sm" required>
                                    <option value=""></option>
                                </select>
                            </div>

                            <!-- SUCURSAL -->
                            <div class="mb-2">
                                <label class="form-label small fw-bold mb-1">Sucursal</label>
                                <select id="selectSucursal" class="form-select form-select-sm" required>
                                    <option value=""></option>
                                </select>
                            </div>

                            <!-- MÉTODO PAGO -->
                            <div class="mb-2">
                                <label class="form-label small fw-bold mb-1">Método de Pago</label>
                                <div id="metodosGrid" class="row g-1"></div>
                                <input type="hidden" id="selectedMetodo">
                            </div>

                            <!-- BOTÓN COMPLETAR -->
                            <button type="button" id="btnGuardarVenta"
                                class="btn btn-success btn-xl w-100 fw-bold mb-1">
                                <i class="ti ti-coin"></i>COMPLETAR
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-xl w-100"
                                onclick="limpiarCarrito()">
                                <i class="ti ti-trash"></i> Limpiar
                            </button>
                        </div>

                        <input type="hidden" id="inputSubtotal" value="0">
                        <input type="hidden" id="inputTotal" value="0">

                        <!-- BOTTOM: CALCULADORA Y TOTALES -->
                        <div class="border-top" style="background: #fff;">
                            <div class="p-2">
                                <div class="row g-2">
                                    <!-- CALCULADORA (IZQUIERDA) -->
                                    <div class="col-6 col-lg-6">
                                        <div class="border rounded p-1" style="background: #f8f9fa;">
                                            <div class="form-control text-end bg-dark text-light fw-bold p-1 mb-1"
                                                id="calcDisplay"
                                                style="font-family: monospace; font-size: 14px; min-height: 30px;">0
                                            </div>
                                            <div class="row g-1">
                                                <div class="col-4"><button
                                                        class="btn btn-xs btn-outline-secondary w-100 p-1"
                                                        onclick="calcPress('7')" style="font-size: 11px;">7</button>
                                                </div>
                                                <div class="col-4"><button
                                                        class="btn btn-xs btn-outline-secondary w-100 p-1"
                                                        onclick="calcPress('8')" style="font-size: 11px;">8</button>
                                                </div>
                                                <div class="col-4"><button
                                                        class="btn btn-xs btn-outline-secondary w-100 p-1"
                                                        onclick="calcPress('9')" style="font-size: 11px;">9</button>
                                                </div>

                                                <div class="col-4"><button
                                                        class="btn btn-xs btn-outline-secondary w-100 p-1"
                                                        onclick="calcPress('4')" style="font-size: 11px;">4</button>
                                                </div>
                                                <div class="col-4"><button
                                                        class="btn btn-xs btn-outline-secondary w-100 p-1"
                                                        onclick="calcPress('5')" style="font-size: 11px;">5</button>
                                                </div>
                                                <div class="col-4"><button
                                                        class="btn btn-xs btn-outline-secondary w-100 p-1"
                                                        onclick="calcPress('6')" style="font-size: 11px;">6</button>
                                                </div>

                                                <div class="col-4"><button
                                                        class="btn btn-xs btn-outline-secondary w-100 p-1"
                                                        onclick="calcPress('1')" style="font-size: 11px;">1</button>
                                                </div>
                                                <div class="col-4"><button
                                                        class="btn btn-xs btn-outline-secondary w-100 p-1"
                                                        onclick="calcPress('2')" style="font-size: 11px;">2</button>
                                                </div>
                                                <div class="col-4"><button
                                                        class="btn btn-xs btn-outline-secondary w-100 p-1"
                                                        onclick="calcPress('3')" style="font-size: 11px;">3</button>
                                                </div>

                                                <div class="col-6"><button
                                                        class="btn btn-xs btn-outline-secondary w-100 p-1"
                                                        onclick="calcPress('0')" style="font-size: 11px;">0</button>
                                                </div>
                                                <div class="col-6"><button
                                                        class="btn btn-xs btn-outline-secondary w-100 p-1"
                                                        onclick="calcPress('.')" style="font-size: 11px;">.</button>
                                                </div>

                                                <div class="col-4"><button class="btn btn-xs btn-danger w-100 p-1"
                                                        onclick="calcDelete()" style="font-size: 10px;">DEL</button>
                                                </div>
                                                <div class="col-4"><button class="btn btn-xs btn-warning w-100 p-1"
                                                        onclick="calcPress('+')" style="font-size: 11px;">+</button>
                                                </div>
                                                <div class="col-4"><button class="btn btn-xs btn-warning w-100 p-1"
                                                        onclick="calcPress('-')" style="font-size: 11px;">−</button>
                                                </div>

                                                <div class="col-12"><button
                                                        class="btn btn-xs btn-success w-100 fw-bold p-1"
                                                        onclick="calcEquals()" style="font-size: 11px;">=</button></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- INFO TOTALES (DERECHA) -->
                                    <div class="col-6 col-lg-6">
                                        <div class="border rounded p-2"
                                            style="background: #f8f9fa; text-align: center;">
                                            <div class="mb-1">
                                                <small class="text-muted fw-bold d-block"
                                                    style="font-size: 10px;">ITEMS</small>
                                                <h5 class="mb-0" id="lblItemsBottom" style="font-size: 18px;">0</h5>
                                            </div>
                                            <hr class="my-1">
                                            <div>
                                                <small class="text-muted fw-bold d-block" style="font-size: 10px;">A
                                                    PAGAR</small>
                                                <h4 class="mb-0 text-success" id="lblTotalBottom"
                                                    style="font-size: 18px;">$0.00</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Modal confirm printer ticket -->
    <div class="modal fade" id="modalConfirmPrint" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-printer text-primary me-2"></i>
                        Imprimir ticket
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <p class="mb-1" id="printModalMessage">
                        ¿Deseas imprimir el ticket de esta venta?
                    </p>
                    <small class="text-muted" id="printModalSub">
                        Esta acción abrirá la impresora térmica.
                    </small>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        No
                    </button>
                    <button type="button" class="btn btn-primary" id="btnConfirmPrint">
                        <i class="ti ti-printer me-1"></i> Imprimir
                    </button>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAperturaCaja" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
        <div class="modal-header">
            <h5 class="modal-title">
            <i class="ti ti-cash text-success me-2"></i> Apertura de caja
            </h5>
        </div>

        <div class="modal-body">
            <p class="mb-2">No hay una caja abierta. Debes abrir caja para vender.</p>

            <div class="mb-2">
            <label class="form-label small fw-bold mb-1">Sucursal</label>
            <select id="openCajaSucursal" class="form-select form-select-sm" required></select>
            </div>

            <div class="mb-2">
            <label class="form-label small fw-bold mb-1">Base</label>
            <input type="number" step="0.01" min="0" id="openCajaBase" class="form-control form-control-sm" value="50000">
            </div>

            <div class="mb-2">
            <label class="form-label small fw-bold mb-1">Observación</label>
            <input type="text" id="openCajaNota" class="form-control form-control-sm" placeholder="Ej: Apertura turno mañana">
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-success" id="btnAbrirCaja">
            <i class="ti ti-unlock me-1"></i> Abrir caja
            </button>
        </div>
        </div>
    </div>
    </div>

    <div class="modal fade" id="modalCierreCaja" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
        <div class="modal-header">
            <h5 class="modal-title">
            <i class="ti ti-report-money text-primary me-2"></i> Cierre de caja
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
            <div class="alert alert-info py-2">
            <div class="d-flex justify-content-between">
                <span><strong>Caja abierta desde:</strong> <span id="txtCajaApertura">—</span></span>
                <span><strong>Sucursal:</strong> <span id="txtCajaSucursal">—</span></span>
            </div>
            </div>

            <div class="row g-2">
            <div class="col-12 col-md-6">
                <div class="border rounded p-2">
                <div class="fw-bold mb-1">Ventas por método</div>
                <div id="boxTotalesMetodos" class="small text-muted">Cargando...</div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="border rounded p-2">
                <div class="fw-bold mb-1">Productos vendidos</div>
                <div id="boxProductosVendidos" class="small text-muted" style="max-height:180px; overflow:auto;">Cargando...</div>
                </div>
            </div>
            </div>

            <hr class="my-2">

            <div class="mb-2">
            <label class="form-label small fw-bold mb-1">Efectivo contado (opcional)</label>
            <input type="number" step="0.01" min="0" id="closeCajaContado" class="form-control form-control-sm" placeholder="Ej: 180000">
            </div>

            <div class="mb-2">
            <label class="form-label small fw-bold mb-1">Observación</label>
            <input type="text" id="closeCajaNota" class="form-control form-control-sm" placeholder="Ej: cierre normal">
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary" id="btnConfirmarCierreCaja">
            <i class="ti ti-lock me-1"></i> Confirmar cierre
            </button>
        </div>
        </div>
    </div>
    </div>

<!-- Modal confirmación cierre print -->
<div class="modal fade" id="modalConfirmClosePrint" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-printer text-primary me-2"></i>
                    Imprimir cierre
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1" id="closeModalMessage">
                    ¿Deseas imprimir el cierre de caja?
                </p>
                <small class="text-muted">
                    Se abrirá la plantilla lista para imprimir.
                </small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    No
                </button>
                <button type="button" class="btn btn-primary" id="btnConfirmClosePrint">
                    <i class="ti ti-printer me-1"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>    



<!-- CONFIGURACIÓN API -->
<script>
  window.API_URL = 'http://caballosrevelo.test/front/ajax/pos.ajax.php';
  //window.SUCURSAL_ID = 1;
  window.PRODUCTOS_POR_PAGINA = 12;


let calcDisplay = '0';

function updateCalcDisplay() {
    const el = document.getElementById('calcDisplay');
    if (el) el.textContent = calcDisplay || '0';
}

function calcPress(val) {
    if (calcDisplay === '0' && val !== '.') {
        calcDisplay = val;
    } else {
        calcDisplay += val;
    }
    updateCalcDisplay();
}

function calcDelete() {
    calcDisplay = calcDisplay.slice(0, -1) || '0';
    updateCalcDisplay();
}

function calcEquals() {
    try {
        const result = eval(calcDisplay);
        calcDisplay = String(Math.round(result * 100) / 100);
        updateCalcDisplay();
    } catch (e) {
        calcDisplay = '0';
        updateCalcDisplay();
    }
}
</script>

<?php
$extra_js = "<script src=\"" . ASSETS_URL . "/js/pos.js\"></script>";
include_once ROOT_PATH . "/includes/footer.php";
?>