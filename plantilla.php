<?php 
// Configuración
require_once "config/config.php";

// Variables de la página
$page_title = "Plantilla";

include_once ROOT_PATH . "/includes/head.php"; 
?>

  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">

    <!-- Sidebar -->
    <?php include_once ROOT_PATH . "/includes/sidebar.php" ?>

    <!--  Main wrapper -->
    <div class="body-wrapper">

      <!-- Header -->
      <?php include_once ROOT_PATH . "/includes/header.php" ?>
        
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          
          <div class="card">
            <div class="card-body">
              <h5 class="card-title fw-semibold mb-4">Sample Page</h5>
              <p class="mb-0">This is a sample page</p>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

<?php 
// Scripts adicionales (opcional)
$extra_js = '
<script>
  console.log("Página cargada correctamente");
</script>
';

include_once ROOT_PATH . "/includes/footer.php"; 
?>