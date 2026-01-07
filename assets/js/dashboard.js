(() => {
  /* ============================================================
   * DASHBOARD.JS
   * - Mantiene tu arquitectura: state + postJSON + pintarDashboard
   * - NO rompe filtros ni eventos
   * - Agrega 3 tortas:
   *    1) Ventas vs Gastos (desde expenses en obtenerDashboard)
   *    2) Top 10 más vendidos (action: obtenerTopProductosVendidos)
   *    3) Top 10 stock más bajo (action: obtenerStockMasBajo)
   * ============================================================ */

  const state = {
    sucursales: [],
    clientes: [],
    filtros: {
      fechaDesde: '',
      fechaHasta: '',
      periodo: '',
      sucursal: '',
      cliente: '',
      referencia: ''
    },

    // Charts
    chartTendencia: null,      // area
    chartVentasGastos: null,   // donut
    chartTopVendidos: null,    // donut
    chartStockBajo: null       // donut
  };

  /* =========================
   * Helpers DOM
   * ========================= */
  const $ = (s) => document.querySelector(s);
  const val = (s) => $(s)?.value ?? '';

  /* =========================
   * Helpers Format
   * ========================= */
  const money = (v) =>
    '$' + Number(v || 0).toLocaleString('es-CO', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });

  // Y-m-d local (Colombia) sin UTC (evita corrimientos)
  const toLocalYMD = (d) => {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
  };

  /* =========================
   * Helper API
   * ========================= */
  async function postJSON(payload) {
    try {
      if (!window.API_URL) throw new Error('API_URL no está definida (window.API_URL)');

      const form = new FormData();
      Object.entries(payload || {}).forEach(([k, v]) => form.append(k, v));

      const resp = await fetch(window.API_URL, { method: 'POST', body: form });
      const data = await resp.json().catch(() => ({ success: false, message: 'Respuesta inválida' }));

      return { ok: resp.ok, data };
    } catch (e) {
      console.error('[DASHBOARD][postJSON]', e.message);
      return { ok: false, data: { success: false, message: e.message } };
    }
  }

  /* ============================================================
   * Charts Init (NO se recrean, solo se actualizan)
   * ============================================================ */

  function initChartTendencia() {
    const el = $('#chartVentasDiarias');
    if (!el || state.chartTendencia) return;

    state.chartTendencia = new ApexCharts(el, {
      chart: { type: 'area', height: 320, toolbar: { show: false } },
      series: [{ name: 'Ventas', data: [] }],
      xaxis: { categories: [] },
      dataLabels: { enabled: false },
      stroke: { curve: 'smooth', width: 2 },
      tooltip: { y: { formatter: (v) => money(v) } }
    });

    state.chartTendencia.render();
  }

  function initChartVentasGastos() {
    const el = $('#chartVentasVsGastos');
    if (!el || state.chartVentasGastos) return;

    state.chartVentasGastos = new ApexCharts(el, {
      chart: { type: 'donut', height: 320 },
      series: [1, 1],
      labels: ['Ventas', 'Gastos'],
      legend: { position: 'bottom' },
      dataLabels: { enabled: false },
      tooltip: { y: { formatter: (v) => money(v) } }
    });

    state.chartVentasGastos.render();
  }

  function initChartTopVendidos() {
    const el = $('#chartTopVendidos');
    if (!el || state.chartTopVendidos) return;

    state.chartTopVendidos = new ApexCharts(el, {
      chart: { type: 'donut', height: 320 },
      series: [1],
      labels: ['Cargando...'],
      legend: { position: 'bottom' },
      dataLabels: { enabled: false },
      tooltip: { y: { formatter: (v) => Number(v || 0).toLocaleString('es-CO') + ' und' } }
    });

    state.chartTopVendidos.render();
  }

  function initChartStockBajo() {
    const el = $('#chartStockBajo');
    if (!el || state.chartStockBajo) return;

    state.chartStockBajo = new ApexCharts(el, {
      chart: { type: 'donut', height: 320 },
      series: [1],
      labels: ['Cargando...'],
      legend: { position: 'bottom' },
      dataLabels: { enabled: false },
      tooltip: { y: { formatter: (v) => Number(v || 0).toLocaleString('es-CO') + ' und' } }
    });

    state.chartStockBajo.render();
  }

  function initCharts() {
    initChartTendencia();
    initChartVentasGastos();
    initChartTopVendidos();
    initChartStockBajo();
  }

  /* ============================================================
   * Filtros (MISMA lógica tuya)
   * ============================================================ */

  function aplicarPeriodoSiExiste() {
    const p = val('#filterPeriodo');
    if (!p) return;

    const hoy = new Date();
    let desde = new Date(hoy);

    switch (p) {
      case 'hoy':
        break;

      case 'semana': {
        const day = hoy.getDay(); // domingo=0
        desde.setDate(hoy.getDate() - day);
        break;
      }

      case 'mes':
        desde = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
        break;

      case 'ano':
        desde = new Date(hoy.getFullYear(), 0, 1);
        break;
    }

    const iDesde = $('#filterFechaDesde');
    const iHasta = $('#filterFechaHasta');

    if (iDesde) iDesde.value = toLocalYMD(desde);
    if (iHasta) iHasta.value = toLocalYMD(hoy);
  }

  function leerFiltros() {
    state.filtros = {
      fechaDesde: val('#filterFechaDesde'),
      fechaHasta: val('#filterFechaHasta'),
      periodo: val('#filterPeriodo'),
      sucursal: val('#filterSucursal'),
      cliente: val('#filterCliente'),
      referencia: val('#searchReferencia')
    };
  }

  function setDefaultRangeMesActual() {
    const hoy = new Date();
    const desde = new Date(hoy.getFullYear(), hoy.getMonth(), 1);

    const iDesde = $('#filterFechaDesde');
    const iHasta = $('#filterFechaHasta');

    if (iDesde) iDesde.value = toLocalYMD(desde);
    if (iHasta) iHasta.value = toLocalYMD(hoy);
  }

  /* ============================================================
   * Combos (Sucursales + Clientes)
   * ============================================================ */

  async function cargarCombos() {
    // Sucursales
    const { data: s } = await postJSON({ action: 'obtenerSucursales' });
    if (s?.success) {
      state.sucursales = Array.isArray(s.data) ? s.data : [];
      const sel = $('#filterSucursal');
      if (sel) {
        sel.innerHTML =
          '<option value="">Todas</option>' +
          state.sucursales.map((x) => `<option value="${x.id_branch}">${x.name_branch}</option>`).join('');
      }
    }

    // Clientes
    const { data: c } = await postJSON({ action: 'obtenerClientes' });
    if (c?.success) {
      state.clientes = Array.isArray(c.data) ? c.data : [];
      const sel = $('#filterCliente');
      if (sel) {
        sel.innerHTML =
          '<option value="">Todos los clientes</option>' +
          state.clientes.map((x) => `<option value="${x.id_customer}">${x.name_customer}</option>`).join('');
      }
    }
  }

  /* ============================================================
   * Pintar Dashboard (KPIs + tendencia + últimas + torta ventas/gastos)
   * ============================================================ */

  function pintarDashboard(resp) {
    const d = resp?.data || {};
    const k = d.kpis || {};

    // KPIs
    $('#kpiTotalVentas').textContent = money(k.totalVentas || 0);
    $('#kpiTransacciones').textContent = (k.transacciones || 0);
    $('#kpiTicketPromedio').textContent = money(k.ticketPromedio || 0);
    $('#kpiArticulos').textContent = (k.totalArticulos || 0);

    // Rango texto
    const rangoTxt =
      (state.filtros.fechaDesde && state.filtros.fechaHasta)
        ? `${state.filtros.fechaDesde} → ${state.filtros.fechaHasta}`
        : 'Rango: —';

    const elRango = $('#kpiRango');
    if (elRango) elRango.textContent = rangoTxt;

    // Tendencia (area)
    const tendencia = Array.isArray(d.tendencia) ? d.tendencia : [];
    $('#badgeTendencia').textContent = `${tendencia.length} días`;

    if (state.chartTendencia) {
      state.chartTendencia.updateOptions({
        xaxis: { categories: tendencia.map(x => x.fecha) },
        series: [{ name: 'Ventas', data: tendencia.map(x => Number(x.ventas || 0)) }]
      });
    }

    // Últimas ventas
    const ult = Array.isArray(d.ultimasVentas) ? d.ultimasVentas : [];
    $('#badgeUltimas').textContent = String(ult.length || 0);

    const cont = $('#listaUltimasVentas');
    if (cont) {
      cont.innerHTML = ult.length
        ? ult.map(v => `
          <div class="d-flex justify-content-between border-bottom py-2">
            <div class="pe-2">
              <div class="fw-bold">
                #${v.id_sale}
                <span class="text-muted small">${v.reference_number_sale || ''}</span>
              </div>
              <div class="text-muted small">${(v.date_created_sale || '').toString().replace('T', ' ')}</div>
            </div>
            <div class="fw-bold text-success">${money(v.total_amount_sale || 0)}</div>
          </div>
        `).join('')
        : `<p class="text-muted small mb-0">Sin transacciones</p>`;
    }

    // ✅ Torta Ventas vs Gastos (desde expenses)
    const ventas = Number(k.totalVentas || 0);
    const gastos = Number(k.totalGastos || 0);

    const badgeVG = $('#badgeVentasGastos');
    const subVG = $('#subVentasGastos');

    if (badgeVG) badgeVG.textContent = 'V vs G';
    if (subVG) subVG.textContent = `Ventas: ${money(ventas)} | Gastos: ${money(gastos)}`;

    if (state.chartVentasGastos) {
      state.chartVentasGastos.updateOptions({
        labels: ['Ventas', 'Gastos'],
        series: [ventas, gastos]
      });
    }
  }

  /* ============================================================
   * Pintar torta Top Vendidos (desde VentasController)
   * ============================================================ */
  function pintarTopVendidos(rows) {
    const data = Array.isArray(rows) ? rows : [];

    // Labels = nombres, Series = cantidad
    const labels = data.map(x => x.nombre || `Producto #${x.id_product}`);
    const series = data.map(x => Number(x.cantidad || 0));

    const badge = $('#badgeTopVendidos');
    if (badge) badge.textContent = String(data.length || 0);

    if (!state.chartTopVendidos) return;

    if (!data.length) {
      state.chartTopVendidos.updateOptions({
        labels: ['Sin datos'],
        series: [1]
      });
      return;
    }

    state.chartTopVendidos.updateOptions({ labels, series });
  }

  /* ============================================================
   * Pintar torta Stock más bajo (desde VentasController)
   * ============================================================ */
  function pintarStockBajo(rows) {
    const data = Array.isArray(rows) ? rows : [];

    const labels = data.map(x => x.nombre || `Producto #${x.id_product}`);
    const series = data.map(x => Number(x.stock || 0));

    const badge = $('#badgeStockBajo');
    if (badge) badge.textContent = String(data.length || 0);

    if (!state.chartStockBajo) return;

    if (!data.length) {
      state.chartStockBajo.updateOptions({
        labels: ['Sin datos'],
        series: [1]
      });
      return;
    }

    state.chartStockBajo.updateOptions({ labels, series });
  }

  /* ============================================================
   * Cargar dashboard completo
   * - 1 request: obtenerDashboard (kpis + tendencia + ultimas + gastos)
   * - 2 requests: top vendidos y stock bajo (tortas)
   * ============================================================ */
  async function cargarDashboard() {
    initCharts();
    leerFiltros();

    // 🔥 Pedimos todo en paralelo (más rápido)
    const [dash, topV, stockB] = await Promise.all([
      postJSON({ action: 'obtenerDashboard', ...state.filtros }),
      postJSON({ action: 'obtenerTopProductosVendidos', ...state.filtros }),
      postJSON({ action: 'obtenerStockMasBajo' })
    ]);

    // Dashboard principal
    if (!dash.ok || !dash.data?.success) {
      console.error('[DASHBOARD]', dash.data?.message || 'Error');
    } else {
      pintarDashboard(dash.data);
    }

    // Top vendidos
    if (!topV.ok || !topV.data?.success) {
      console.error('[TOP_VENDIDOS]', topV.data?.message || 'Error');
      pintarTopVendidos([]);
    } else {
      pintarTopVendidos(topV.data.data || []);
    }

    // Stock bajo
    if (!stockB.ok || !stockB.data?.success) {
      console.error('[STOCK_BAJO]', stockB.data?.message || 'Error');
      pintarStockBajo([]);
    } else {
      pintarStockBajo(stockB.data.data || []);
    }
  }

  /* ============================================================
   * Limpiar filtros sin romper nada
   * ============================================================ */
  function limpiar() {
    [
      '#filterFechaDesde',
      '#filterFechaHasta',
      '#filterPeriodo',
      '#filterSucursal',
      '#filterCliente',
      '#searchReferencia'
    ].forEach(id => {
      const el = $(id);
      if (el) el.value = '';
    });

    // default: mes actual
    setDefaultRangeMesActual();
    cargarDashboard();
  }

  /* ============================================================
   * Boot
   * ============================================================ */
  document.addEventListener('DOMContentLoaded', async () => {
    // 1) Asegurar ApexCharts
    if (!window.ApexCharts) {
      console.error('ApexCharts no está cargado');
      return;
    }

    // 2) Default rango
    setDefaultRangeMesActual();

    // 3) Cargar combos y luego dashboard
    await cargarCombos();
    await cargarDashboard();

    // 4) Eventos (MISMA lógica tuya)
    $('#btnAplicar')?.addEventListener('click', () => cargarDashboard());
    $('#btnLimpiar')?.addEventListener('click', () => limpiar());
    $('#btnRefrescar')?.addEventListener('click', () => cargarDashboard());

    $('#filterPeriodo')?.addEventListener('change', () => {
      aplicarPeriodoSiExiste();
      cargarDashboard();
    });

    $('#filterSucursal')?.addEventListener('change', () => cargarDashboard());
    $('#filterCliente')?.addEventListener('change', () => cargarDashboard());

    // evita spam: change
    $('#searchReferencia')?.addEventListener('change', () => cargarDashboard());

    // si toca fechas manuales, anula periodo
    $('#filterFechaDesde')?.addEventListener('change', () => {
      const p = $('#filterPeriodo');
      if (p) p.value = '';
    });

    $('#filterFechaHasta')?.addEventListener('change', () => {
      const p = $('#filterPeriodo');
      if (p) p.value = '';
    });
  });
})();
