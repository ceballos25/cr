/**
 * vender.js - Versión Senior Refactorizada (Caballos Revelo Edition)
 * Proyecto: Sistema de Rifas (Mobile-First)
 */

const estado = {
    rifa: null,
    numerosLibres: [],
    carrito: [],
    paginaActual: 1,
    itemsPorPagina: 40,
    config: {
        rutas: {
            clientes: 'ajax/clientes.ajax.php',
            rifas: 'ajax/rifas.ajax.php',
            tickets: 'ajax/tickets.ajax.php',
            ventas: 'ajax/ventas.ajax.php'
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    inyectarEstilosMarca(); // Carga la identidad visual y fixes de diseño
    initComponentes();
    cargarRifasActivas();
    asignarEventos();
});

/**
 * 0. IDENTIDAD VISUAL (Negro y Dorado)
 * Fix para que los números queden en una sola línea y sin puntos en el paginador.
 */
function inyectarEstilosMarca() {
    const css = `
    <style>
        /* Botón de número base - FIX para una sola línea */
        .btn-ticket-revelo {
            background-color: #ffffff !important;
            border: 2px solid #1a1a1a !important;
            color: #1a1a1a !important;
            transition: all 0.2s ease-in-out;
            border-radius: 10px;
            font-weight: 700 !important;
            
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            font-size: 13px !important; 
            padding: 0 !important;
        }

        .btn-ticket-revelo:hover {
            border-color: #b59410 !important;
            color: #b59410 !important;
            transform: scale(1.05);
        }

        /* Estado Seleccionado (Marca Revelo) */
        .active-revelo {
            background-color: #1a1a1a !important;
            border-color: #d4af37 !important;
            color: #d4af37 !important;
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.4) !important;
        }

    </style>`;
    document.head.insertAdjacentHTML('beforeend', css);
}

function asignarEventos() {
    $('#selectRifa').on('change', cambiarRifa);
    
    $('#buscarNumeroInput').on('input', function() {
        estado.paginaActual = 1;
        renderizarGrid(this.value.trim());
    });

    $('#departamento').on('change', function() {
        cargarCiudadesVenta(this.value);
    });
}

function initComponentes() {
    $('#buscadorCliente').select2({
        theme: 'bootstrap-5',
        placeholder: 'Buscar cliente...',
        allowClear: true,
        width: '100%',
        ajax: {
            url: estado.config.rutas.clientes,
            type: 'POST',
            dataType: 'json',
            delay: 300,
            data: params => ({ action: 'obtener', search: params.term, status: 1 }),
            processResults: res => ({
                results: (res.success && res.data) ? res.data.map(c => ({
                    id: c.id_customer,
                    text: `${c.name_customer} ${c.lastname_customer} (${c.phone_customer})`,
                    cliente: c
                })) : []
            })
        }
    }).on('select2:select', e => llenarFormulario(e.params.data.cliente))
      .on('select2:unselecting', resetClienteForm);

    $('.select2-ubicacion').select2({ theme: 'bootstrap-5', width: '100%' });

    if (typeof datosColombia !== 'undefined') {
        const $depto = $('#departamento');
        $depto.empty().append('<option value="">Seleccione...</option>');
        Object.keys(datosColombia).sort().forEach(d => $depto.append(new Option(d, d)));
    }
}

async function cargarRifasActivas() {
    try {
        const fd = new FormData();
        fd.append('action', 'obtener_activas');
        const res = await fetch(estado.config.rutas.rifas, { method: 'POST', body: fd });
        const json = await res.json();
        
        const select = document.getElementById('selectRifa');
        if (json.success) {
            json.data.forEach(r => {
                const opt = new Option(r.title_raffle, r.id_raffle);
                opt.dataset.precio = r.price_raffle;
                opt.dataset.digitos = r.digits_raffle;
                select.add(opt);
            });
        }
    } catch (e) { console.error("Error al cargar rifas:", e); }
}

async function cambiarRifa() {
    const idRifa = $('#selectRifa').val();
    const grid = document.getElementById('gridNumeros');
    
    estado.carrito = [];
    estado.numerosLibres = [];
    estado.paginaActual = 1;
    actualizarCarritoUI();

    if (!idRifa) {
        grid.innerHTML = '<div class="text-center py-5 text-muted w-100">Selecciona un sorteo</div>';
        return;
    }

    grid.innerHTML = '<div class="text-center py-5 w-100"><div class="spinner-border text-dark"></div><p class="mt-2 fw-bold">Preparando números...</p></div>';

    const opt = document.getElementById('selectRifa').selectedOptions[0];
    estado.rifa = {
        id: idRifa,
        precio: parseFloat(opt.dataset.precio || 0),
        digitos: parseInt(opt.dataset.digitos || 0),
        total: Math.pow(10, parseInt(opt.dataset.digitos || 0))
    };

    try {
        const fd = new FormData();
        fd.append('action', 'obtener_ocupados');
        fd.append('id_raffle', idRifa);

        const res = await fetch(estado.config.rutas.tickets, { method: 'POST', body: fd });
        if (!res.ok) throw new Error("Error en servidor");
        
        const ocupadosArr = await res.json();
        const setOcupados = new Set(ocupadosArr.map(String));

        for (let i = 0; i < estado.rifa.total; i++) {
            let numStr = i.toString().padStart(estado.rifa.digitos, '0');
            if (!setOcupados.has(numStr)) estado.numerosLibres.push(numStr);
        }

        renderizarGrid();
    } catch (error) {
        grid.innerHTML = '<div class="alert alert-danger mx-auto border-0 shadow-sm">Error de comunicación con el servidor de tickets</div>';
    }
}

function renderizarGrid(filtro = '') {
    const grid = document.getElementById('gridNumeros');
    if (!grid) return;

    let datos = filtro ? estado.numerosLibres.filter(n => n.includes(filtro)) : estado.numerosLibres;

    if (datos.length === 0) {
        grid.innerHTML = '<div class="text-center py-5 text-muted w-100">No hay boletas disponibles</div>';
        PaginationHelper.render({ totalItems: 0, containerId: 'paginacionContainer' });
        return;
    }

    const items = PaginationHelper.getSegment(datos, estado.paginaActual, estado.itemsPorPagina);
    
    grid.innerHTML = '';
    const fragment = document.createDocumentFragment();
    
    items.forEach(num => {
        const btn = document.createElement('button');
        btn.type = "button";
        const estaEnCarrito = estado.carrito.includes(num);
        
        btn.className = `btn btn-ticket-revelo m-1 ${estaEnCarrito ? 'active-revelo' : ''}`;
        btn.style.width = '55px';
        btn.style.height = '55px';
        btn.textContent = num;
        
        btn.onclick = () => toggleCarrito(num, btn);
        fragment.appendChild(btn);
    });

    // FIX: Faltaba añadir el fragmento al grid para que se muestren los números
    grid.appendChild(fragment);

    PaginationHelper.render({
        totalItems: datos.length,
        currentPage: estado.paginaActual,
        limit: estado.itemsPorPagina,
        containerId: 'paginacionContainer',
        callbackName: 'window.cambiarPaginaGrid'
    });
}

window.cambiarPaginaGrid = (page) => {
    estado.paginaActual = page;
    renderizarGrid($('#buscarNumeroInput').val().trim());
    document.getElementById('gridNumeros').scrollTop = 0;
};

function toggleCarrito(num, btn = null) {
    const idx = estado.carrito.indexOf(num);
    if (idx === -1) estado.carrito.push(num);
    else estado.carrito.splice(idx, 1);
    
    if (btn) {
        btn.classList.toggle('active-revelo');
    } else {
        renderizarGrid($('#buscarNumeroInput').val());
    }
    actualizarCarritoUI();
}

function actualizarCarritoUI() {
    const total = estado.carrito.length * (estado.rifa?.precio || 0);
    const fmt = n => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(n);

    // Actualiza los precios
    $('#lblTotalDesktop, #lblTotalMobile').text(fmt(total));
    // MODIFICA ESTA LÍNEA PARA INCLUIR EL NUEVO ID:
        $('#lblCantidadMobile, #lblCantidadDesktop').text(estado.carrito.length);

    const listaHtml = estado.carrito.length === 0 
        ? '<li class="list-group-item text-center text-muted py-4 border-0 small">Selecciona tus números</li>'
        : estado.carrito.map(n => `
            <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-light">
                <span class="badge bg-dark rounded-pill"># ${n}</span>
                <span class="fw-bold small text-primary">${fmt(estado.rifa.precio)}</span>
                <button class="btn btn-sm text-danger border-0" onclick="window.removerItem('${n}')"><i class="ti ti-trash"></i></button>
            </li>`).join('');

    $('#listaCarritoDesktop, #listaCarritoMobile').html(listaHtml);
}

window.removerItem = (num) => toggleCarrito(num);

function llenarFormulario(c) {
    $('#idCliente').val(c.id_customer);
    $('#nombreCliente').val(c.name_customer);
    $('#apellidoCliente').val(c.lastname_customer);
    $('#celularCliente').val(c.phone_customer);
    $('#emailCliente').val(c.email_customer);
    if (c.department_customer) {
        $('#departamento').val(c.department_customer).trigger('change');
        setTimeout(() => $('#ciudad').val(c.city_customer).trigger('change'), 150);
    }
    $('#btnLimpiarCliente').removeClass('d-none');
    toggleInputs(true);
}

function resetClienteForm() {
    document.getElementById('formClienteVenta').reset();
    $('#departamento, #ciudad, #buscadorCliente').val(null).trigger('change');
    $('#btnLimpiarCliente').addClass('d-none');
    toggleInputs(false);
}

function toggleInputs(bloquear) {
    $('#formClienteVenta input:not([type="hidden"])').prop('readonly', bloquear);
}

function cargarCiudadesVenta(depto) {
    const $ciudad = $('#ciudad');
    $ciudad.empty().append('<option value="">Seleccione...</option>');
    if (depto && datosColombia[depto]) {
        $ciudad.prop('disabled', false);
        datosColombia[depto].forEach(c => $ciudad.append(new Option(c.display, c.value)));
    } else {
        $ciudad.prop('disabled', true);
    }
    $ciudad.trigger('change');
}

window.seleccionarAlAzar = () => {
    const disp = estado.numerosLibres.filter(n => !estado.carrito.includes(n));
    if (disp.length === 0) return alert("No hay más números disponibles");
    const num = disp[Math.floor(Math.random() * disp.length)];
    toggleCarrito(num);
};