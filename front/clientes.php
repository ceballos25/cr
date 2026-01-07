<?php
require_once "../config/config.php";
$page_title = "Gestión de Clientes";
include_once ROOT_PATH . "/includes/head.php";
?>

<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">

    <?php include_once ROOT_PATH . "/includes/sidebar.php" ?>

    <div class="body-wrapper">
        <?php include_once ROOT_PATH . "/includes/header.php" ?>

        <div class="body-wrapper-inner">
            <div class="container-fluid">

                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="mb-0"><i class="ti ti-users me-1"></i> Clientes</h2>
                        <p class="text-muted mb-0">Gestiona tu base de clientes</p>
                    </div>
                    <button class="btn btn-primary" onclick="abrirModal()">
                        <i class="ti ti-plus"></i> Nuevo Cliente
                    </button>
                </div>

                <!-- Filtros -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-6">
                                <input type="text" id="searchClientes" class="form-control" 
                                       placeholder="🔍 Buscar por nombre, apellido, email o teléfono...">
                            </div>
                            <div class="col-md-3">
                                <select id="filterStatus" class="form-select">
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

                <!-- Tabla -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre Completo</th>
                                        <th>Teléfono</th>
                                        <th>Email</th>
                                        <th>Departamento</th>
                                        <th>Ciudad</th>
                                        <th>Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyTabla">
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
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

<!-- Modal ÚNICO Reutilizable -->
<div class="modal fade" id="modalCliente" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nuevo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCliente" onsubmit="return false;">
                <div class="modal-body">
                    <input type="hidden" id="clienteId">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellido <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="apellido" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="email">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Departamento</label>
                            <select class="form-select select2-departamento" id="departamento" style="width: 100%;">
                                <option value="">Seleccione...</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ciudad</label>
                            <select class="form-select select2-ciudad" id="ciudad" style="width: 100%;" disabled>
                                <option value="">Seleccione departamento primero...</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="estado">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarCliente()">
                        <i class="ti ti-device-floppy"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Confirmación Eliminar -->
<div class="modal fade" id="modalConfirm" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-alert-triangle text-warning me-1"></i> Confirmar
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">¿Eliminar este cliente?</p>
                <small class="text-muted">Esta acción no se puede deshacer.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="confirmarEliminar()">
                    <i class="ti ti-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$extra_js = '<link href="' . ASSETS_URL . '/libs/select2/css/select2.min.css" rel="stylesheet" />
<link href="' . ASSETS_URL . '/libs/select2/css/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<script src="' . ASSETS_URL . '/libs/select2/js/select2.min.js"></script>
<script src="' . ASSETS_URL . '/js/departamentos-ciudades.js"></script>
<script src="' . ASSETS_URL . '/js/clientes.js"></script>';
include_once ROOT_PATH . "/includes/footer.php";
?>