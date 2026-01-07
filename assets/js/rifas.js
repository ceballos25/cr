// ==========================================
// VARIABLES GLOBALES
// ==========================================
let rifasCache = [], idRifaEliminar = null, modalRifa = null, modalConfirm = null;
let paginaActual = 1;
const registrosPorPagina = 10;

// ==========================================
// INICIALIZACIÓN
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    modalRifa = new bootstrap.Modal(document.getElementById('modalRifa'));
    modalConfirm = new bootstrap.Modal(document.getElementById('modalConfirm'));
    
    if (window.alertify) alertify.set('notifier', 'position', 'top-right');

    cargarRifas();

    // Filtros con Debounce
    document.getElementById('searchRifas').addEventListener('input', debounce(cargarRifas, 500));
    document.getElementById('filterStatus').addEventListener('change', cargarRifas);
});

// ==========================================
// PETICIONES API (CON PRELOADER)
// ==========================================
async function cargarRifas() {
    showPreloader();
    try {
        const fd = new FormData();
        fd.append('action', 'obtener');
        fd.append('search', document.getElementById('searchRifas').value.trim());
        fd.append('status', document.getElementById('filterStatus').value);

        const res = await fetch('ajax/rifas.ajax.php', { method: 'POST', body: fd });
        const data = await res.json();

        rifasCache = data.success ? (data.data || []) : [];
        paginaActual = 1; 
        renderizarTodo();
    } catch (e) { 
        console.error(e);
        renderizarTodo([]); 
    } finally { hidePreloader(); }
}

async function guardarRifa() {
    const id = document.getElementById('id_raffle').value;
    if (!document.getElementById('title_raffle').value.trim()) return alertify.warning('Título obligatorio');

    showPreloader();
    try {
        const fd = new FormData();
        fd.append('action', id ? 'actualizar' : 'crear');
        fd.append('id_raffle', id);
        
        // Mapeo automático de campos
        ['title_raffle', 'description_raffle', 'digits_raffle', 'price_raffle', 'date_raffle', 'status_raffle', 'promotions_raffle']
            .forEach(f => fd.append(f, document.getElementById(f).value));

        const res = await fetch('ajax/rifas.ajax.php', { method: 'POST', body: fd });
        const data = await res.json();
        
        if (data.success) {
            alertify.success(data.message);
            modalRifa.hide();
            cargarRifas();
        } else { alertify.error(data.message); }
    } catch (e) { alertify.error("Error de conexión"); } 
    finally { hidePreloader(); }
}

async function confirmarEliminar() {
    if (!idRifaEliminar) return;
    showPreloader();
    try {
        const fd = new FormData();
        fd.append('action', 'eliminar');
        fd.append('id_raffle', idRifaEliminar);

        const res = await fetch('ajax/rifas.ajax.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            alertify.success('Eliminado');
            modalConfirm.hide();
            cargarRifas();
        }
    } finally { hidePreloader(); }
}

// ==========================================
// RENDERIZADO (TABLA LIMPIA)
// ==========================================
function renderizarTodo() {
    const tbody = document.getElementById('bodyTabla');
    
    // Obtener segmento paginado desde el Helper Modular
    const segmento = PaginationHelper.getSegment(rifasCache, paginaActual, registrosPorPagina);

    if (!segmento.length) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center py-5 text-muted">No se encontraron registros</td></tr>`;
    } else {
        tbody.innerHTML = segmento.map(r => renderFila(r)).join('');
    }

    // Renderizar controles de paginación desde el Helper Modular
    PaginationHelper.render({
        totalItems: rifasCache.length,
        currentPage: paginaActual,
        limit: registrosPorPagina,
        containerId: 'contenedorPaginacion',
        infoId: 'infoPaginacion',
        callbackName: 'cambiarPagina'
    });

    actualizarEstadisticas();
}

// Genera el HTML de la fila sin alineaciones forzadas (text-center/end)
function renderFila(r) {
    const activo = parseInt(r.status_raffle) === 1;
    return `
        <tr>
            <td>${r.id_raffle}</td>
            <td>${r.title_raffle}</td>
            <td>${r.description_raffle || '-'}</td>
            <td>${r.digits_raffle}</td>
            <td>$${parseFloat(r.price_raffle).toLocaleString()}</td>
            <td>${r.date_raffle ? r.date_raffle.substring(0, 16) : '-'}</td>
            <td>${r.promotions_raffle || '-'}</td>
            <td>
                <span class="badge ${activo ? 'bg-success' : 'bg-secondary'}">
                    ${activo ? 'Activa' : 'Inactiva'}
                </span>
            </td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="editarRifa(${r.id_raffle})"><i class="ti ti-edit"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="eliminarRifa(${r.id_raffle})"><i class="ti ti-trash"></i></button>
            </td>
        </tr>`;
}

// ==========================================
// FUNCIONES AUXILIARES
// ==========================================
function cambiarPagina(p) { paginaActual = p; renderizarTodo(); }

function abrirModal() {
    document.getElementById('formRifa').reset();
    document.getElementById('id_raffle').value = '';
    document.getElementById('modalTitle').textContent = 'Nueva Rifa';
    modalRifa.show();
}

function editarRifa(id) {
    const r = rifasCache.find(x => parseInt(x.id_raffle) === parseInt(id));
    if (!r) return;
    document.getElementById('id_raffle').value = r.id_raffle;
    document.getElementById('title_raffle').value = r.title_raffle;
    document.getElementById('description_raffle').value = r.description_raffle || '';
    document.getElementById('digits_raffle').value = r.digits_raffle;
    document.getElementById('price_raffle').value = r.price_raffle;
    document.getElementById('status_raffle').value = r.status_raffle;
    document.getElementById('promotions_raffle').value = r.promotions_raffle || '';
    if (r.date_raffle) document.getElementById('date_raffle').value = r.date_raffle.replace(' ', 'T').substring(0, 16);
    document.getElementById('modalTitle').textContent = 'Editar Rifa';
    modalRifa.show();
}

function eliminarRifa(id) { idRifaEliminar = id; modalConfirm.show(); }


function limpiarFiltros() {
    document.getElementById('searchRifas').value = '';
    document.getElementById('filterStatus').value = '';
    cargarRifas();
}

function debounce(f, w) {
    let t;
    return (...a) => { clearTimeout(t); t = setTimeout(() => f(...a), w); };
}