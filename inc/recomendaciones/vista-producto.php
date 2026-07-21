<?php
if ( ! defined( 'ABSPATH' ) ) exit;

remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
add_action( 'woocommerce_after_single_product_summary', 'mmc_mostrar_upsells_personalizados', 15 );

function mmc_mostrar_upsells_personalizados() {
    global $product;
    if ( ! $product ) return;

    $query = mmc_obtener_recomendaciones( $product->get_id(), 'upsell', 6 );

    if ( $query ) {
        echo '<div class="mmc-recomendaciones-wrapper mmc-upsells-wrapper" style="margin-top: 50px;">';
        echo '<h2 class="mmc-recomendaciones-titulo" style="margin-bottom: 20px;">Te puede interesar...</h2>';
        
        // 🔥 INICIO DE ESTRUCTURA SWIPER.JS 🔥
        echo '<div class="swiper mmc-recomendaciones-swiper">';
        echo '<ul class="swiper-wrapper products" style="margin:0; padding: 10px 5px 20px 5px; list-style:none;">'; 
        
        while ( $query->have_posts() ) {
            $query->the_post();
            global $product;
            
            // Lógica para la imagen hover
            $hover_img = '';
            $attachment_ids = $product->get_gallery_image_ids();
            if ( ! empty( $attachment_ids ) ) {
                $hover_img = wp_get_attachment_image( $attachment_ids[0], 'woocommerce_thumbnail', false, array( 'class' => 'hover-image' ) );
            }

            // AÑADIMOS LA CLASE "swiper-slide" A TU TARJETA
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
        
        echo '</ul>'; // Fin swiper-wrapper
        
        // 🔥 INYECTAMOS LAS FLECHAS DE NAVEGACIÓN 🔥
        echo '<div class="swiper-button-prev mmc-swiper-btn"></div>';
        echo '<div class="swiper-button-next mmc-swiper-btn"></div>';
        
        echo '</div>'; // Fin swiper container
        echo '</div>'; // Fin wrapper general
        
        wp_reset_postdata();
    }
}