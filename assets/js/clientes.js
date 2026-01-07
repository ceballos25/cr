// ==========================================
// VARIABLES GLOBALES
// ==========================================
let clientesCache = [], idClienteEliminar = null, modalCliente = null, modalConfirm = null;
let paginaActual = 1;
const registrosPorPagina = 10;

// ==========================================
// INICIALIZACIÓN BLINDADA
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    
    // Solo inicializar si los elementos existen para evitar el error de 'backdrop'
    const elModalCliente = document.getElementById('modalCliente');
    const elModalConfirm = document.getElementById('modalConfirm');

    if (elModalCliente && typeof bootstrap !== 'undefined') {
        modalCliente = bootstrap.Modal.getOrCreateInstance(elModalCliente);
    }
    
    if (elModalConfirm && typeof bootstrap !== 'undefined') {
        modalConfirm = bootstrap.Modal.getOrCreateInstance(elModalConfirm);
    }

    if (window.alertify) {
        alertify.set('notifier', 'position', 'top-right');
        alertify.set('notifier', 'delay', 3);
    }

    // Inicialización de Select2 (Tus estilos originales)
    if ($('.select2-departamento').length) {
        $('.select2-departamento').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalCliente'),
            width: '100%',
            placeholder: 'Seleccione un departamento'
        });
    }

    if ($('.select2-ciudad').length) {
        $('.select2-ciudad').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalCliente'),
            width: '100%',
            placeholder: 'Seleccione una ciudad'
        });
    }

    if (typeof inicializarUbicacion === 'function') inicializarUbicacion();

    // Solo cargar si existe el cuerpo de la tabla
    if (document.getElementById('bodyTabla')) {
        cargarClientes();
    }

    // Filtros con Debounce
    const btnSearch = document.getElementById('searchClientes');
    if (btnSearch) btnSearch.addEventListener('input', debounce(cargarClientes, 500));

    const btnStatus = document.getElementById('filterStatus');
    if (btnStatus) btnStatus.addEventListener('change', cargarClientes);

    if (elModalCliente) {
        $('#modalCliente').on('shown.bs.modal', () => $(document).off('focusin.modal'));
    }
});

// ==========================================
// CARGAR CLIENTES (CON PRELOADER)
// ==========================================
async function cargarClientes() {
    if (typeof showPreloader === 'function') showPreloader();
    
    try {
        const searchInput = document.getElementById('searchClientes');
        const statusSelect = document.getElementById('filterStatus');

        const formData = new FormData();
        formData.append('action', 'obtener');
        formData.append('search', searchInput ? searchInput.value.trim() : '');
        formData.append('status', statusSelect ? statusSelect.value : '');

        const response = await fetch('ajax/clientes.ajax.php', { method: 'POST', body: formData });
        const data = await response.json();

        if (data.success) {
            clientesCache = data.data || [];
            paginaActual = 1; 
            renderizarTodo();
        }
    } catch (error) {
        console.error('Error:', error);
        renderizarTodo([]);
    } finally {
        if (typeof hidePreloader === 'function') hidePreloader();
    }
}

// ==========================================
// RENDERIZADO MODULAR
// ==========================================
function renderizarTodo() {
    if (typeof PaginationHelper === 'undefined') return;

    const segmento = PaginationHelper.getSegment(clientesCache, paginaActual, registrosPorPagina);
    renderTabla(segmento);

    PaginationHelper.render({
        totalItems: clientesCache.length,
        currentPage: paginaActual,
        limit: registrosPorPagina,
        containerId: 'contenedorPaginacion',
        infoId: 'infoPaginacion',
        callbackName: 'cambiarPagina'
    });
}

function renderTabla(clientes) {
    const tbody = document.getElementById('bodyTabla');
    if (!tbody) return;
    
    if (!clientes || clientes.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5 text-muted">No hay registros</td></tr>`;
        return;
    }

    // TABLA LIMPIA: Tal cual me la pasaste, sin <strong> ni badges extras en ID
    tbody.innerHTML = clientes.map(c => {
        const activo = parseInt(c.status_customer) === 1;
        const nombreCompleto = `${c.name_customer || ''} ${c.lastname_customer || ''}`.trim();
        return `
            <tr>
                <td>${c.id_customer}</td>
                <td>${nombreCompleto || '-'}</td>
                <td>${c.phone_customer || '-'}</td>
                <td>${c.email_customer || '-'}</td>
                <td>${c.department_customer || '-'}</td>
                <td>${c.city_customer || '-'}</td>
                <td>
                    <span class="badge ${activo ? 'bg-success' : 'bg-secondary'}">
                        ${activo ? 'Activo' : 'Inactivo'}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="editarCliente(${c.id_customer})"><i class="ti ti-edit"></i></button>
                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarCliente(${c.id_customer})"><i class="ti ti-trash"></i></button>
                </td>
            </tr>`;
    }).join('');
}

// ==========================================
// ACCIONES Y UTILIDADES
// ==========================================
function cambiarPagina(p) { 
    paginaActual = p; 
    renderizarTodo(); 
}

function abrirModal() {
    document.getElementById('formCliente').reset();
    document.getElementById('clienteId').value = '';
    $('#departamento').val('').trigger('change'); 
    if(modalCliente) modalCliente.show();
}

function editarCliente(id) {
    const c = clientesCache.find(x => parseInt(x.id_customer) === parseInt(id));
    if (!c) return;

    document.getElementById('clienteId').value = c.id_customer;
    document.getElementById('nombre').value = c.name_customer || '';
    document.getElementById('apellido').value = c.lastname_customer || '';
    document.getElementById('telefono').value = c.phone_customer || '';
    document.getElementById('email').value = c.email_customer || '';
    document.getElementById('estado').value = c.status_customer || '1';

    if (c.department_customer) $('#departamento').val(c.department_customer).trigger('change');
    if (c.city_customer) $('#ciudad').val(c.city_customer).trigger('change');

    if(modalCliente) modalCliente.show();
}

async function guardarCliente() {
    const id = document.getElementById('clienteId').value;
    const nombre = document.getElementById('nombre').value.trim();
    if (!nombre) return alertify.warning('Nombre obligatorio');

    if (typeof showPreloader === 'function') showPreloader();
    const formData = new FormData();
    formData.append('action', id ? 'actualizar' : 'crear');
    formData.append('id_customer', id);
    formData.append('name_customer', nombre);
    formData.append('lastname_customer', document.getElementById('apellido').value.trim());
    formData.append('phone_customer', document.getElementById('telefono').value.trim());
    formData.append('email_customer', document.getElementById('email').value.trim());
    formData.append('department_customer', $('#departamento').val() || '');
    formData.append('city_customer', $('#ciudad').val() || '');
    formData.append('status_customer', document.getElementById('estado').value);

    try {
        const res = await fetch('ajax/clientes.ajax.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            alertify.success(data.message);
            if(modalCliente) modalCliente.hide();
            cargarClientes();
        }
    } finally {
        if (typeof hidePreloader === 'function') hidePreloader();
    }
}

function eliminarCliente(id) { 
    idClienteEliminar = id; 
    if(modalConfirm) modalConfirm.show(); 
}

async function confirmarEliminar() {
    if (!idClienteEliminar) return;
    if (typeof showPreloader === 'function') showPreloader();
    try {
        const fd = new FormData();
        fd.append('action', 'eliminar');
        fd.append('id_customer', idClienteEliminar);
        const res = await fetch('ajax/clientes.ajax.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            alertify.success('Eliminado');
            if(modalConfirm) modalConfirm.hide();
            cargarClientes();
        }
    } finally {
        if (typeof hidePreloader === 'function') hidePreloader();
    }
}

function limpiarFiltros() {
    const s = document.getElementById('searchClientes');
    const f = document.getElementById('filterStatus');
    if(s) s.value = '';
    if(f) f.value = '';
    cargarClientes();
}

function debounce(f, w) {
    let t;
    return (...a) => { clearTimeout(t); t = setTimeout(() => f(...a), w); };
}