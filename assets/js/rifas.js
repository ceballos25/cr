let rifasCache = [];
let idRifaEliminar = null;
let modalRifa = null;
let modalConfirm = null;

document.addEventListener('DOMContentLoaded', function() {
    modalRifa = new bootstrap.Modal(document.getElementById('modalRifa'));
    modalConfirm = new bootstrap.Modal(document.getElementById('modalConfirm'));

    if (window.alertify) {
        alertify.set('notifier', 'position', 'top-right');
    }

    cargarRifas();
});

async function cargarRifas() {
    const formData = new FormData();
    formData.append('action', 'obtener');
    formData.append('search', document.getElementById('searchRifas').value);
    formData.append('status', document.getElementById('filterStatus').value);

    try {
        const response = await fetch('ajax/rifas.ajax.php', { method: 'POST', body: formData });
        const data = await response.json();

        if (data.success) {
            rifasCache = data.results;
            pintarRifas(data.results);
            actualizarEstadisticas(data.results);
        }
    } catch (error) { console.error('Error:', error); }
}

    function pintarRifas(rifas) {
        const tbody = document.getElementById('bodyTabla');
        
        if (rifas.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted">No se encontraron sorteos registrados</td></tr>';
            return;
        }

        tbody.innerHTML = rifas.map(r => `
            <tr>
                <td class="text-muted">#${r.id_raffle}</td>
                <td class="fw-bold text-dark">${r.title_raffle}</td>
                <td><small class="text-muted">${r.description_raffle || 'Sin descripción'}</small></td>
                <td class="text-center"><span class="badge bg-light text-dark border">${r.digits_raffle}</span></td>
                <td class="text-end fw-bold">$${parseFloat(r.price_raffle).toLocaleString()}</td>
                <td class="text-center small">${r.date_raffle ? r.date_raffle.substring(0, 16) : '---'}</td>
                <td><small class="text-info">${r.promotions_raffle || 'N/A'}</small></td>
                <td class="text-center">
                    <span class="badge ${r.status_raffle == 1 ? 'bg-success-lt text-success' : 'bg-danger-lt text-danger'}">
                        ${r.status_raffle == 1 ? 'ACTIVA' : 'INACTIVA'}
                    </span>
                </td>
                <td class="text-center">
                    <div class="btn-group shadow-sm">
                        <button class="btn btn-sm btn-outline-primary" onclick="editarRifa(${r.id_raffle})" title="Editar">
                            <i class="ti ti-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarRifa(${r.id_raffle})" title="Eliminar">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

function abrirModal() {
    document.getElementById('formRifa').reset();
    document.getElementById('id_raffle').value = '';
    document.getElementById('modalTitle').innerText = 'Nueva Rifa';
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

    if (r.date_raffle) {
        document.getElementById('date_raffle').value = r.date_raffle.replace(' ', 'T').substring(0, 16);
    }

    document.getElementById('modalTitle').innerText = 'Editar Rifa #' + r.id_raffle;
    modalRifa.show();
}

async function guardarRifa() {
    const id = document.getElementById('id_raffle').value;
    const formData = new FormData();
    
    formData.append('action', id ? 'actualizar' : 'crear');
    formData.append('id_raffle', id);
    formData.append('title_raffle', document.getElementById('title_raffle').value);
    formData.append('description_raffle', document.getElementById('description_raffle').value);
    formData.append('digits_raffle', document.getElementById('digits_raffle').value);
    formData.append('price_raffle', document.getElementById('price_raffle').value);
    formData.append('date_raffle', document.getElementById('date_raffle').value);
    formData.append('status_raffle', document.getElementById('status_raffle').value);
    formData.append('promotions_raffle', document.getElementById('promotions_raffle').value);

    try {
        const response = await fetch('ajax/rifas.ajax.php', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.success) {
            alertify.success(data.message);
            modalRifa.hide();
            cargarRifas();
        } else {
            alertify.error(data.message || 'Error en la operación');
        }
    } catch (error) {
        alertify.error('Error de conexión');
    }
}

function eliminarRifa(id) {
    idRifaEliminar = id;
    modalConfirm.show();
}

async function confirmarEliminar() {
    if (!idRifaEliminar) return;

    const formData = new FormData();
    formData.append('action', 'eliminar');
    formData.append('id_raffle', idRifaEliminar);

    try {
        const response = await fetch('ajax/rifas.ajax.php', { method: 'POST', body: formData });
        const data = await response.json();
        if (data.success) {
            alertify.success(data.message);
            modalConfirm.hide();
            idRifaEliminar = null;
            cargarRifas();
        }
    } catch (error) { console.error('Error:', error); }
}

function actualizarEstadisticas(rifas) {
    document.getElementById('totalRifas').innerText = rifas.length;
    document.getElementById('rifasActivas').innerText = rifas.filter(r => parseInt(r.status_raffle) === 1).length;
}

function limpiarFiltros() {
    document.getElementById('searchRifas').value = '';
    document.getElementById('filterStatus').value = '';
    cargarRifas();
}