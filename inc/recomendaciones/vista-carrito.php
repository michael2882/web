<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// 1. Quitamos las ventas cruzadas por defecto (que salían en la columna estrecha)
remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );

// 2. 🔥 Enganchamos nuestro carrusel al FINAL del carrito (Ancho completo) 🔥
add_action( 'woocommerce_after_cart', 'mmc_mostrar_crosssells_personalizados' );

function mmc_mostrar_crosssells_personalizados() {
    if ( WC()->cart->is_empty() ) return;

    $producto_base_id = 0;
    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
        $check_cat = get_post_meta( $cart_item['product_id'], '_mmc_crosssell_category', true );
        if ( ! empty( $check_cat ) ) {
            $producto_base_id = $cart_item['product_id'];
            break;
        }
    }

    if ( $producto_base_id === 0 ) return;

    $query = mmc_obtener_recomendaciones( $producto_base_id, 'crosssell', 6 );

    if ( $query ) {
        // Le añadimos un margen superior más grande para separarlo bien de la tabla del carrito
        echo '<div class="mmc-recomendaciones-wrapper mmc-crosssells-wrapper" style="margin-top: 60px; width: 100%; clear: both;">';
        echo '<h2 class="mmc-recomendaciones-titulo" style="margin-bottom: 20px;">Combina perfectamente con tu pedido:</h2>';
        
        echo '<div class="swiper mmc-recomendaciones-swiper">';
        echo '<ul class="swiper-wrapper products" style="margin:0; padding: 10px 5px 20px 5px; list-style:none;">'; 
        
        while ( $query->have_posts() ) {
            $query->the_post();
            global $product;
            
            $hover_img = '';
            $attachment_ids = $product->get_gallery_image_ids();
            if ( ! empty( $attachment_ids ) ) {
                $hover_img = wp_get_attachment_image( $attachment_ids[0], 'woocommerce_thumbnail', false, array( 'class' => 'hover-image' ) );
            }

            echo '<li class="swiper-slide mmc-custom-product-card">';
            
            echo '<a href="' . esc_url( get_permalink() ) . '" class="mmc-img-link">';
            echo '<div class="mmc-catalogo-img-wrapper">';
            echo $product->get_image( 'woocommerce_thumbnail' );
            echo $hover_img;
            echo '</div>';
            echo '</a>';

            echo '<div class="mmc-info-box">';
            echo '<span class="mmc-cat">' . wc_get_product_category_list( $product->get_id(), ', ' ) . '</span>';
            echo '<a href="' . esc_url( get_permalink() ) . '" class="mmc-title-link"><h3 class="mmc-title">' . esc_html( get_the_title() ) . '</h3></a>';
            
            if ( function_exists('mmc_obtener_colores_de_filtros') ) {
                echo mmc_obtener_colores_de_filtros( $product->get_id() );
            }
            
            echo '<span class="mmc-price">' . $product->get_price_html() . '</span>';
            echo '</div>'; 
            echo '</li>';
        }
        
        echo '</ul>'; 
        
        echo '<div class="swiper-button-prev mmc-swiper-btn"></div>';
        echo '<div class="swiper-button-next mmc-swiper-btn"></div>';
        
        echo '</div>'; 
        echo '</div>'; 
        
        wp_reset_postdata();
    }
}