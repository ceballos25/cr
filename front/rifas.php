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

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="mb-0"><i class="ti ti-ticket me-1"></i> Rifas</h2>
                        <p class="text-muted mb-0">Configuración de sorteos dinámicos</p>
                    </div>
                    <button class="btn btn-primary" onclick="abrirModal()">
                        <i class="ti ti-plus"></i> Nueva Rifa
                    </button>
                </div>

                <div class="row mb-5">
                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <div class="card card-body shadow-sm">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-lg rounded bg-primary-lt me-3">
                                    <i class="ti ti-ticket fs-3 text-primary"></i>
                                </span>
                                <div class="flex-grow-1">
                                    <small class="text-muted d-block text-truncate">Total Rifas</small>
                                    <span class="h3 fw-bold text-dark mb-0" id="totalRifas">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3 mb-3">
                        <div class="card card-body shadow-sm">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-lg rounded bg-success-lt me-3">
                                    <i class="ti ti-circle-check fs-3 text-success"></i>
                                </span>
                                <div class="flex-grow-1">
                                    <small class="text-muted d-block text-truncate">Rifas Activas</small>
                                    <span class="h3 fw-bold text-dark mb-0" id="rifasActivas">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-6">
                                <input type="text" id="searchRifas" class="form-control" 
                                       placeholder="🔍 Buscar por título o descripción..." onkeyup="cargarRifas()">
                            </div>
                            <div class="col-md-3">
                                <select id="filterStatus" class="form-select" onchange="cargarRifas()">
                                    <option value="">Todos los estados</option>
                                    <option value="1">Activos</option>
                                    <option value="0">Inactivos</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-outline-secondary w-100" onclick="limpiarFiltros()">
                                    <i class="ti ti-refresh"></i> Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

              <div class="card border-0 shadow-sm">
                  <div class="card-body">
                      <div class="table-responsive">
                          <table class="table table-hover align-middle">
                              <thead class="table-light">
                                  <tr>
                                      <th width="50">ID</th>
                                      <th>Título</th>
                                      <th>Descripción</th>
                                      <th class="text-center">Cifras</th>
                                      <th class="text-end">Precio</th>
                                      <th class="text-center">Fecha Sorteo</th>
                                      <th>Promociones</th>
                                      <th class="text-center">Estado</th>
                                      <th class="text-center" width="120">Acciones</th>
                                  </tr>
                              </thead>
                              <tbody id="bodyTabla">
                                  <tr>
                                      <td colspan="9" class="text-center py-5">
                                          <div class="spinner-border text-primary" role="status">
                                              <span class="visually-hidden">Cargando...</span>
                                          </div>
                                      </td>
                                  </tr>
                              </tbody>
                          </table>
                      </div>
                  </div>
              </div>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRifa" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nueva Rifa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formRifa" onsubmit="return false;">
                <div class="modal-body">
                    <input type="hidden" id="id_raffle">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Título <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title_raffle" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cifras</label>
                            <select class="form-select" id="digits_raffle">
                                <option value="2">2 Cifras</option>
                                <option value="3">3 Cifras</option>
                                <option value="4">4 Cifras</option>
                                <option value="5">5 Cifras</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" id="description_raffle" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Precio <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="price_raffle" step="0.01" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha Sorteo <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="date_raffle" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="status_raffle">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Promociones</label>
                            <input type="text" class="form-control" id="promotions_raffle" placeholder="Ej: 3 por $10k">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarRifa()">
                        <i class="ti ti-device-floppy"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalConfirm" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header text-center">
                <h5 class="modal-title w-100">
                    <i class="ti ti-alert-triangle text-warning me-1"></i> Confirmar
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-0">¿Eliminar esta rifa?</p>
                <small class="text-muted">Esta acción no se puede deshacer.</small>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="confirmarEliminar()">
                    <i class="ti ti-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$extra_js = '<script src="' . ASSETS_URL . '/js/rifas.js"></script>';
include_once ROOT_PATH . "/includes/footer.php";
?>