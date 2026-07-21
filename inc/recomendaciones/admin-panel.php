<?php
// Evitar acceso directo
if ( ! defined( 'ABSPATH' ) ) exit;

// 1. Crear la cajita (Meta Box)
add_action( 'add_meta_boxes', 'mmc_agregar_caja_recomendaciones' );
function mmc_agregar_caja_recomendaciones() {
    add_meta_box(
        'mmc_recomendaciones_automaticas', // ID interno
        'Recomendaciones Automáticas (Up-sells y Cross-sells)', // Título visual
        'mmc_dibujar_caja_recomendaciones', // Función que pinta el HTML
        'product', // Dónde sale (productos)
        'normal', // Posición
        'high' // Prioridad
    );
}

// 2. Dibujar los campos por dentro
function mmc_dibujar_caja_recomendaciones( $post ) {
    // Seguridad de WordPress
    wp_nonce_field( 'mmc_guardar_recomendaciones', 'mmc_recomendaciones_nonce' );

    // Obtener valores guardados previamente
    $upsell_cat = get_post_meta( $post->ID, '_mmc_upsell_category', true );
    $upsell_ord = get_post_meta( $post->ID, '_mmc_upsell_orderby', true );
    $cross_cat  = get_post_meta( $post->ID, '_mmc_crosssell_category', true );
    $cross_ord  = get_post_meta( $post->ID, '_mmc_crosssell_orderby', true );

    // Obtener todas las categorías de productos
    $categorias = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
    $opciones_orden = array(
        'sales'  => 'Más Vendidos',
        'date'   => 'Más Recientes',
        'rand'   => 'Aleatorio (Random)'
    );

    echo '<div style="display:flex; gap: 30px; margin-top: 15px;">';
    
    // COLUMNA 1: UP-SELLS (Single Product)
    echo '<div style="flex: 1; background: #f9f9f9; padding: 15px; border: 1px solid #ddd;">';
    echo '<h4>1. Ventas Dirigidas (Página de Producto)</h4>';
    echo '<p><strong>Categoría a recomendar:</strong><br>';
    echo '<select name="mmc_upsell_category" style="width:100%;">';
    echo '<option value="">-- No recomendar nada --</option>';
    foreach ( $categorias as $cat ) {
        echo '<option value="' . esc_attr($cat->term_id) . '" ' . selected($upsell_cat, $cat->term_id, false) . '>' . esc_html($cat->name) . '</option>';
    }
    echo '</select></p>';

    echo '<p><strong>Ordenar por:</strong><br>';
    echo '<select name="mmc_upsell_orderby" style="width:100%;">';
    foreach ( $opciones_orden as $val => $label ) {
        echo '<option value="' . esc_attr($val) . '" ' . selected($upsell_ord, $val, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select></p>';
    echo '</div>';

    // COLUMNA 2: CROSS-SELLS (Carrito)
    echo '<div style="flex: 1; background: #f9f9f9; padding: 15px; border: 1px solid #ddd;">';
    echo '<h4>2. Ventas Cruzadas (Página de Carrito)</h4>';
    echo '<p><strong>Categoría a recomendar:</strong><br>';
    echo '<select name="mmc_crosssell_category" style="width:100%;">';
    echo '<option value="">-- No recomendar nada --</option>';
    foreach ( $categorias as $cat ) {
        echo '<option value="' . esc_attr($cat->term_id) . '" ' . selected($cross_cat, $cat->term_id, false) . '>' . esc_html($cat->name) . '</option>';
    }
    echo '</select></p>';

    echo '<p><strong>Ordenar por:</strong><br>';
    echo '<select name="mmc_crosssell_orderby" style="width:100%;">';
    foreach ( $opciones_orden as $val => $label ) {
        echo '<option value="' . esc_attr($val) . '" ' . selected($cross_ord, $val, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select></p>';
    echo '</div>';

    echo '</div>';
}

// 3. Guardar los datos cuando se actualiza el producto
add_action( 'save_post_product', 'mmc_guardar_datos_recomendaciones' );
function mmc_guardar_datos_recomendaciones( $post_id ) {
    if ( ! isset( $_POST['mmc_recomendaciones_nonce'] ) || ! wp_verify_nonce( $_POST['mmc_recomendaciones_nonce'], 'mmc_guardar_recomendaciones' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

    // Guardar Up-sells
    update_post_meta( $post_id, '_mmc_upsell_category', sanitize_text_field( $_POST['mmc_upsell_category'] ) );
    update_post_meta( $post_id, '_mmc_upsell_orderby', sanitize_text_field( $_POST['mmc_upsell_orderby'] ) );

    // Guardar Cross-sells
    update_post_meta( $post_id, '_mmc_crosssell_category', sanitize_text_field( $_POST['mmc_crosssell_category'] ) );
    update_post_meta( $post_id, '_mmc_crosssell_orderby', sanitize_text_field( $_POST['mmc_crosssell_orderby'] ) );
}