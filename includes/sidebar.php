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

        <li class="sidebar-item <?= isActive('nueva_venta.php', $currentPage); ?>">
          <a class="sidebar-link" href="nueva_venta.php" aria-expanded="false">
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
              <a class="sidebar-link" href="ventas.php">
                <div class="round-16 d-flex align-items-center justify-content-center"><i class="ti ti-circle"></i></div>
                <span class="hide-menu">Ventas</span>
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

        <li class="sidebar-item <?= isActive('rifas.php', $currentPage); ?>">
          <a class="sidebar-link" href="rifas.php" aria-expanded="false">
            <i class="ti ti-credit-card"></i>
            <span class="hide-menu">Rifas</span>
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
            <span class="hide-menu">Ventas al detalle</span>
          </a>
        </li>


      </ul>
    </nav>
  </div>
</aside>
