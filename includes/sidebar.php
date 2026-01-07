<?php
// Verificar si el usuario está logueado
$isLoggedIn = isset($_SESSION['user_id']);
$userRole   = $_SESSION['user_role'] ?? 'administrador'; // Por defecto vendedor
$isAdmin    = ($userRole === 'administrador');

// Helper para marcar activo por página
$currentPage = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

function isActive($fileName, $currentPage) {
  return $currentPage === $fileName ? 'active' : '';
}
function isOpen($files, $currentPage) {
  return in_array($currentPage, $files) ? 'in' : '';
}
?>

<aside class="left-sidebar">
  <div>
    <div class="brand-logo d-flex align-items-center justify-content-between">
      <a href="index.php" class="text-nowrap logo-img">
        <img  style="width:70%; margin-top:10px; margin-left:20%" class="d-flex" src="<?= ASSETS_URL ?>/images/logos/logo.jpg" alt="<?php echo SITE_NAME; ?>" />
      </a>
      <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
        <i class="ti ti-x fs-6"></i>
      </div>
    </div>

    <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
      <ul id="sidebarnav">

        <!-- PRINCIPAL -->
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Principal</span>
        </li>

        <li class="sidebar-item <?= isActive('dashboard.php', $currentPage); ?>">
          <a class="sidebar-link" href="dashboard.php" aria-expanded="false">
            <i class="ti ti-home"></i>
            <span class="hide-menu">Dashboard</span>
          </a>
        </li>

        <li class="sidebar-item <?= isActive('pos.php', $currentPage); ?>">
          <a class="sidebar-link" href="pos.php" aria-expanded="false">
            <i class="ti ti-shopping-cart"></i>
            <span class="hide-menu">Vender</span>
          </a>
        </li>

        <li><span class="sidebar-divider lg"></span></li>

        <!-- CLIENTES / PROVEEDORES -->
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Terceros</span>
        </li>

        <li class="sidebar-item <?= isActive('clientes.php', $currentPage); ?>">
          <a class="sidebar-link" href="clientes.php" aria-expanded="false">
            <i class="ti ti-users"></i>
            <span class="hide-menu">Clientes</span>
          </a>
        </li>

        <li><span class="sidebar-divider lg"></span></li>

        <!-- PRODUCTOS / INVENTARIO -->
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Ventas e informes</span>
        </li>

        <?php
          $prodPages = [
            'productos.php', 'categorias.php', 'marcas.php', 'unidades.php',
            'inventario.php', 'ajustes-inventario.php', 'movimientos-inventario.php',
            'bodegas.php', 'transferencias.php', 'kardex.php'
          ];
        ?>
        <li class="sidebar-item">
          <a class="sidebar-link justify-content-between has-arrow" href="javascript:void(0)" aria-expanded="false">
            <div class="d-flex align-items-center gap-3">
              <span class="d-flex"><i class="ti ti-box"></i></span>
              <span class="hide-menu">Ventas & Números</span>
            </div>
          </a>

          <ul aria-expanded="false" class="collapse first-level <?= isOpen($prodPages, $currentPage); ?>">

            <li class="sidebar-item <?= isActive('categorias.php', $currentPage); ?>">
              <a class="sidebar-link" href="categorias.php">
                <div class="round-16 d-flex align-items-center justify-content-center"><i class="ti ti-circle"></i></div>
                <span class="hide-menu">Detalle Ventas</span>
              </a>
            </li>

            <li class="sidebar-item <?= isActive('inventario.php', $currentPage); ?>">
              <a class="sidebar-link" href="inventario.php">
                <div class="round-16 d-flex align-items-center justify-content-center"><i class="ti ti-circle"></i></div>
                <span class="hide-menu">Números Vendidos</span>
              </a>
            </li>

            <li class="sidebar-item <?= isActive('inventario.php', $currentPage); ?>">
              <a class="sidebar-link" href="inventario.php">
                <div class="round-16 d-flex align-items-center justify-content-center"><i class="ti ti-circle"></i></div>
                <span class="hide-menu">Números Disponibles</span>
              </a>
            </li>

            <?php if ($isAdmin): ?>

            <?php endif; ?>
          </ul>
        </li>

        <li><span class="sidebar-divider lg"></span></li>

        <!-- CAJA / PAGOS -->
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Configuración</span>
        </li>


        <li class="sidebar-item <?= isActive('cierres.php', $currentPage); ?>">
          <a class="sidebar-link" href="cierres.php" aria-expanded="false">
            <i class="ti ti-lock"></i>
            <span class="hide-menu">Respaldo</span>
          </a>
        </li>

        <li class="sidebar-item <?= isActive('metodos-pago.php', $currentPage); ?>">
          <a class="sidebar-link" href="metodos-pago.php" aria-expanded="false">
            <i class="ti ti-credit-card"></i>
            <span class="hide-menu">Métodos de pago</span>
          </a>
        </li>

        <li><span class="sidebar-divider lg"></span></li>

        <!-- REPORTES -->
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Reportería</span>
        </li>

        <li class="sidebar-item <?= isActive('reportes-ventas.php', $currentPage); ?>">
          <a class="sidebar-link" href="ventas.php" aria-expanded="false">
            <i class="ti ti-chart-bar"></i>
            <span class="hide-menu">Ventas</span>
          </a>
        </li>


        <li><span class="sidebar-divider lg"></span></li>

        <!-- ADMIN (solo admin) -->
        <?php if ($isAdmin): ?>
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">Administración</span>
        </li>

        <?php
          $adminPages = [
            'usuarios.php','roles.php','permisos.php',
            'empresa.php','sucursales.php',
            'impuestos.php','descuentos.php',
            'numeracion.php','integraciones.php',
            'respaldos.php','auditoria.php'
          ];
        ?>
        <li class="sidebar-item">
          <a class="sidebar-link justify-content-between has-arrow" href="javascript:void(0)" aria-expanded="false">
            <div class="d-flex align-items-center gap-3">
              <span class="d-flex"><i class="ti ti-settings"></i></span>
              <span class="hide-menu">Configuración</span>
            </div>
          </a>

          <ul aria-expanded="false" class="collapse first-level <?= isOpen($adminPages, $currentPage); ?>">
            <li class="sidebar-item <?= isActive('usuarios.php', $currentPage); ?>">
              <a class="sidebar-link" href="usuarios.php">
                <div class="round-16 d-flex align-items-center justify-content-center"><i class="ti ti-circle"></i></div>
                <span class="hide-menu">Usuarios</span>
              </a>
            </li>

            <li class="sidebar-item <?= isActive('empresa.php', $currentPage); ?>">
              <a class="sidebar-link" href="empresa.php">
                <div class="round-16 d-flex align-items-center justify-content-center"><i class="ti ti-circle"></i></div>
                <span class="hide-menu">Empresa</span>
              </a>
            </li>

            <li class="sidebar-item <?= isActive('sucursales.php', $currentPage); ?>">
              <a class="sidebar-link" href="sucursales.php">
                <div class="round-16 d-flex align-items-center justify-content-center"><i class="ti ti-circle"></i></div>
                <span class="hide-menu">Sucursales</span>
              </a>
            </li>


            <li class="sidebar-item <?= isActive('numeracion.php', $currentPage); ?>">
              <a class="sidebar-link" href="numeracion.php">
                <div class="round-16 d-flex align-items-center justify-content-center"><i class="ti ti-circle"></i></div>
                <span class="hide-menu">Numeración / Factura</span>
              </a>
            </li>


            <li class="sidebar-item <?= isActive('respaldos.php', $currentPage); ?>">
              <a class="sidebar-link" href="respaldos.php">
                <div class="round-16 d-flex align-items-center justify-content-center"><i class="ti ti-circle"></i></div>
                <span class="hide-menu">Respaldos</span>
              </a>
            </li>    
          </ul>
        </li>
        <?php endif; ?>

        <li><span class="sidebar-divider lg"></span></li>

      </ul>
    </nav>
  </div>
</aside>
