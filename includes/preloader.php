<div id="preloader-global" class="d-none position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center" 
     style="background: rgba(255,255,255,0.7); z-index: 9999;">
    <div class="text-center">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-2 fw-bold text-primary">Procesando...</p>
    </div>
</div>

<script>
    // Funciones globales disponibles en todas las vistas
    function showPreloader() {
        const p = document.getElementById('preloader-global');
        if(p) p.classList.remove('d-none');
    }

    function hidePreloader() {
        const p = document.getElementById('preloader-global');
        if(p) p.classList.add('d-none');
    }
</script>