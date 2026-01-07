<?php
require_once "../config/config.php";
$page_title = "Gestión de Rifas";
include_once ROOT_PATH . "/includes/head.php";
?>

<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">

    <?php include_once ROOT_PATH . "/includes/sidebar.php" ?>

    <div class="body-wrapper">
        <?php include_once ROOT_PATH . "/includes/header.php" ?>

        <div class="body-wrapper-inner">
            <div class="container-fluid">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-0"><i class="ti ti-ticket me-1"></i>Rifas</h2>
                    </div>
                    <button class="btn btn-primary" onclick="abrirModal()">
                        <i class="ti ti-plus"></i> Nueva Rifa
                    </button>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body py-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Buscador</label>
                                <input type="text" id="searchRifas" class="form-control form-control-sm" placeholder="Buscar título o descripción...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Estado</label>
                                <select id="filterStatus" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option value="1">Activas</option>
                                    <option value="0">Inactivas</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-outline-secondary btn-sm w-100" onclick="limpiarFiltros()">
                                    <i class="ti ti-refresh"></i> Limpiar
                                </button>
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
                                        <th>ID</th>
                                        <th>Título</th>
                                        <th>Descripción</th>
                                        <th>Cifras</th>
                                        <th>Precio</th>
                                        <th>Fecha Sorteo</th>
                                        <th>Promociones</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyTabla">
                                    <tr><td colspan="9" class="text-center py-5">Cargando sorteos...</td></tr>
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

<div class="modal fade" id="modalRifa" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nueva Rifa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formRifa">
                <div class="modal-body p-4">
                    <input type="hidden" id="id_raffle">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">Título del Sorteo *</label>
                            <input type="text" class="form-control" id="title_raffle" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Cifras</label>
                            <select class="form-select" id="digits_raffle">
                                <option value="2">2 Cifras</option>
                                <option value="3">3 Cifras</option>
                                <option value="4">4 Cifras</option>
                                <option value="5">5 Cifras</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Descripción del Premio</label>
                            <textarea class="form-control" id="description_raffle" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Precio Unitario *</label>
                            <input type="number" class="form-control" id="price_raffle" step="0.01" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Fecha del Sorteo *</label>
                            <input type="datetime-local" class="form-control" id="date_raffle" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Estado</label>
                            <select class="form-select" id="status_raffle">
                                <option value="1">Activa</option>
                                <option value="0">Inactiva</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Promociones Disponibles</label>
                            <input type="text" class="form-control" id="promotions_raffle" placeholder="Ej: 3 por $10,000">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary px-4" onclick="guardarRifa()">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalConfirm" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center border-0 shadow">
            <div class="modal-body p-4">
                <i class="ti ti-alert-triangle text-warning fs-1 d-block mb-3"></i>
                <h5 class="mb-2">¿Confirmar eliminación?</h5>
                <div class="d-flex gap-2 justify-content-center mt-4">
                    <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn btn-danger btn-sm px-3" onclick="confirmarEliminar()">Sí, eliminar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $extra_js = '<script src="' . ASSETS_URL . '/js/rifas.js"></script>'; include_once ROOT_PATH . "/includes/footer.php"; ?>