<?php
/*
// inc/catalogo.php
if ( ! defined('ABSPATH') ) exit;

// 1. Quitar botón de añadir al carrito y estrellas
add_action('init', 'mmc_limpiar_catalogo_base', 999);
function mmc_limpiar_catalogo_base() {
    remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
    remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
}

// 2. Reemplazar imagen nativa por la nuestra con Hover
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
add_action( 'woocommerce_before_shop_loop_item_title', 'mmc_catalogo_imagen_hover_cuadrada', 10 );
function mmc_catalogo_imagen_hover_cuadrada() {
    global $product;
    echo '<div class="mmc-catalogo-img-wrapper">';
    woocommerce_template_loop_product_thumbnail(); 
    
    $attachment_ids = $product->get_gallery_image_ids();
    if ( $attachment_ids && ! empty( $attachment_ids ) ) {
        $hover_img_src = wp_get_attachment_image_url( $attachment_ids[0], 'woocommerce_thumbnail' );
        if ( $hover_img_src ) {
            echo '<img src="' . esc_url($hover_img_src) . '" class="hover-image" alt="Vista alternativa">';
        }
    }
    echo '</div>';
}

// 3. Añadir Categoría ANTES del título
add_action( 'woocommerce_shop_loop_item_title', 'mmc_catalogo_categoria', 5 );
function mmc_catalogo_categoria() {
    global $product;
    echo wc_get_product_category_list( $product->get_id(), ', ', '<div class="mmc-cat">', '</div>' );
}

// 4. Añadir Colores DESPUÉS del título
add_action( 'woocommerce_shop_loop_item_title', 'mmc_catalogo_colores', 15 );
function mmc_catalogo_colores() {
    if ( function_exists('mmc_obtener_colores_de_filtros') ) {
        echo mmc_obtener_colores_de_filtros( get_the_ID() );
    }
}
*/