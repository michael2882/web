<?php
/**
 * ESTRUCTURA DEL PANEL LATERAL MÓVIL CORREGIDA
 */
function mmc_render_mobile_filter_button() {
    ?>
    <div id="mmc-mobile-side-panel" class="mmc-side-panel">
        <div class="mmc-panel-header">
            <span class="mmc-panel-title">Filtros</span>
            <button id="mmc-close-panel" class="mmc-close-btn">&times;</button>
        </div>
        <div class="mmc-panel-content">
            <div id="mmc-mobile-accordion-container"></div>
        </div>
        <div class="mmc-panel-footer">
            <button id="mmc-apply-mobile-total" class="mmc-apply-btn">Ver Resultados</button>
        </div>
    </div>
    <div id="mmc-panel-overlay"></div>
    <?php
}