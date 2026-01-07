// ==========================================
// VARIABLES GLOBALES
// ==========================================
let clientesCache = [];
let idClienteEliminar = null;
let modalCliente = null;
let modalConfirm = null;

// ==========================================
// INICIALIZACIÓN
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar modales Bootstrap
    modalCliente = new bootstrap.Modal(document.getElementById('modalCliente'));
    modalConfirm = new bootstrap.Modal(document.getElementById('modalConfirm'));

    // Configurar Alertify
    if (window.alertify) {
        alertify.set('notifier', 'position', 'top-right');
        alertify.set('notifier', 'delay', 3);
    }

    // -----------------------------------------------------
    // INICIALIZACIÓN DE SELECT2 Y UBICACIÓN
    // -----------------------------------------------------
    $('.select2-departamento').select2({
        theme: 'bootstrap-5', // Forzamos el tema visual
        dropdownParent: $('#modalCliente'), // Esto arregla el problema del scroll y el foco
        width: '100%',
        placeholder: 'Seleccione un departamento',
        selectionCssClass: 'select2--small', // Opcional: para un look más refinado
        dropdownCssClass: 'select2--small'
    });

    $('.select2-ciudad').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#modalCliente'),
        width: '100%',
        placeholder: 'Seleccione una ciudad'
    });

    // Cargar la data de departamentos desde el otro archivo JS
    if (typeof inicializarUbicacion === 'function') {
        inicializarUbicacion();
    }
    // -----------------------------------------------------

    // Cargar datos iniciales de la tabla
    cargarClientes();

    // Event Listeners generales
    document.getElementById('searchClientes').addEventListener('input', debounce(cargarClientes, 500));
    document.getElementById('filterStatus').addEventListener('change', cargarClientes);

    $('#modalCliente').on('shown.bs.modal', function() {
    $(document).off('focusin.modal');
});
});

// ==========================================
// CARGAR CLIENTES (Sin cambios importantes)
// ==========================================
async function cargarClientes() {
    try {
        const formData = new FormData();
        formData.append('action', 'obtener');
        formData.append('search', document.getElementById('searchClientes').value.trim());
        formData.append('status', document.getElementById('filterStatus').value);

        const response = await fetch('ajax/clientes.ajax.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            clientesCache = data.data || [];
            renderTabla(clientesCache);
        } else {
            alertify.error(data.message || 'Error al cargar clientes');
            renderTabla([]);
        }
    } catch (error) {
        console.error('Error:', error);
        alertify.error('No se pudo conectar con el servidor');
        renderTabla([]);
    }
}

// ==========================================
// RENDERIZAR TABLA (Sin cambios)
// ==========================================
function renderTabla(clientes) {
    const tbody = document.getElementById('bodyTabla');
    
    if (!clientes || clientes.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-5 text-muted">
                    <i class="ti ti-inbox fs-1 d-block mb-2"></i>
                    <p class="mb-0">No se encontraron clientes</p>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = clientes.map(cliente => {
        const activo = parseInt(cliente.status_customer) === 1;
        const nombreCompleto = `${cliente.name_customer || ''} ${cliente.lastname_customer || ''}`.trim();
        
        return `
            <tr>
                <td><span class="badge bg-light text-dark">${cliente.id_customer}</span></td>
                <td><strong>${nombreCompleto || '-'}</strong></td>
                <td>${cliente.phone_customer || '-'}</td>
                <td>${cliente.email_customer || '-'}</td>
                <td>${cliente.department_customer || '-'}</td>
                <td>${cliente.city_customer || '-'}</td>
                <td>
                    <span class="badge ${activo ? 'bg-success' : 'bg-secondary'}">
                        ${activo ? 'Activo' : 'Inactivo'}
                    </span>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary" onclick="editarCliente(${cliente.id_customer})" title="Editar">
                        <i class="ti ti-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarCliente(${cliente.id_customer})" title="Eliminar">
                        <i class="ti ti-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

// ==========================================
// ABRIR MODAL (CREAR) - LÓGICA SELECT2 AGREGADA
// ==========================================
function abrirModal() {
    document.getElementById('formCliente').reset();
    document.getElementById('clienteId').value = '';
    document.getElementById('modalTitle').textContent = 'Nuevo Cliente';
    document.getElementById('estado').value = '1';

    // Resetear Select2
    $('#departamento').val('').trigger('change'); // Esto disparará la limpieza de ciudad automáticamente
    
    modalCliente.show();
}

// ==========================================
// EDITAR CLIENTE - LÓGICA SELECT2 AGREGADA
// ==========================================
function editarCliente(id) {
    const cliente = clientesCache.find(c => parseInt(c.id_customer) === parseInt(id));

    if (!cliente) {
        alertify.error('Cliente no encontrado');
        return;
    }

    // Llenar campos de texto básicos
    document.getElementById('clienteId').value = cliente.id_customer;
    document.getElementById('nombre').value = cliente.name_customer || '';
    document.getElementById('apellido').value = cliente.lastname_customer || '';
    document.getElementById('telefono').value = cliente.phone_customer || '';
    document.getElementById('email').value = cliente.email_customer || '';
    document.getElementById('estado').value = cliente.status_customer || '1';

    // ----------------------------------------------------------------
    // LÓGICA DEPARTAMENTO Y CIUDAD PARA EDICIÓN
    // ----------------------------------------------------------------
    
    // 1. Establecer el valor del Departamento en Select2
    const depto = cliente.department_customer;
    if (depto) {
        $('#departamento').val(depto).trigger('change'); // El trigger cargará las ciudades
    }

    // 2. Establecer el valor de la Ciudad
    // Nota: Como la data es local, la carga es instantánea. Si fuera API, necesitaríamos un await.
    const ciudad = cliente.city_customer;
    if (ciudad) {
        $('#ciudad').val(ciudad).trigger('change');
    }
    // ----------------------------------------------------------------

    document.getElementById('modalTitle').textContent = 'Editar Cliente';
    modalCliente.show();
}

// ==========================================
// GUARDAR CLIENTE (Sin cambios en lógica, solo validación)
// ==========================================
async function guardarCliente() {
    const id = document.getElementById('clienteId').value;
    const nombre = document.getElementById('nombre').value.trim();
    const apellido = document.getElementById('apellido').value.trim();
    // Obtener valores de select2 usando jQuery para mayor seguridad
    const departamento = $('#departamento').val();
    const ciudad = $('#ciudad').val();

    if (!nombre || !apellido) {
        alertify.warning('Nombre y apellido son obligatorios');
        return;
    }

    const formData = new FormData();
    formData.append('action', id ? 'actualizar' : 'crear');
    formData.append('id_customer', id);
    formData.append('name_customer', nombre);
    formData.append('lastname_customer', apellido);
    formData.append('phone_customer', document.getElementById('telefono').value.trim());
    formData.append('email_customer', document.getElementById('email').value.trim());
    
    // Enviar departamento y ciudad
    formData.append('department_customer', departamento || '');
    formData.append('city_customer', ciudad || '');
    
    formData.append('status_customer', document.getElementById('estado').value);

    try {
        const response = await fetch('ajax/clientes.ajax.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alertify.success(data.message || (id ? 'Cliente actualizado' : 'Cliente creado'));
            modalCliente.hide();
            cargarClientes();
        } else {
            alertify.error(data.message || 'Error al guardar');
        }
    } catch (error) {
        console.error('Error:', error);
        alertify.error('No se pudo conectar con el servidor');
    }
}

// ==========================================
// ELIMINAR Y OTRAS FUNCIONES (Sin cambios)
// ==========================================
function eliminarCliente(id) {
    idClienteEliminar = id;
    modalConfirm.show();
}

async function confirmarEliminar() {
    if (!idClienteEliminar) return;

    const formData = new FormData();
    formData.append('action', 'eliminar');
    formData.append('id_customer', idClienteEliminar);

    try {
        const response = await fetch('ajax/clientes.ajax.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alertify.success(data.message || 'Cliente eliminado');
            modalConfirm.hide();
            idClienteEliminar = null;
            cargarClientes();
        } else {
            alertify.error(data.message || 'No se pudo eliminar');
        }
    } catch (error) {
        console.error('Error:', error);
        alertify.error('No se pudo conectar con el servidor');
    }
}

function limpiarFiltros() {
    document.getElementById('searchClientes').value = '';
    document.getElementById('filterStatus').value = '';
    cargarClientes();
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
