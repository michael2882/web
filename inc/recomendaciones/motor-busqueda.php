<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function mmc_obtener_recomendaciones( $product_id, $tipo = 'upsell', $limite = 4 ) {
    $categoria_id = get_post_meta( $product_id, "_mmc_{$tipo}_category", true );
    $orden        = get_post_meta( $product_id, "_mmc_{$tipo}_orderby", true );

    // Si no eligió categoría, no mostramos nada
    if ( empty( $categoria_id ) ) return false;

    $args = array(
        'post_type'           => 'product',
        'post_status'         => 'publish',
        'posts_per_page'      => $limite,
        'post__not_in'        => array( $product_id ), // No recomendar el mismo producto
        'tax_query'           => array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $categoria_id,
            ),
        ),
    );

    // Lógica de ordenamiento
    if ( $orden === 'sales' ) {
        $args['meta_key'] = 'total_sales';
        $args['orderby']  = 'meta_value_num';
        $args['order']    = 'DESC';
    } elseif ( $orden === 'rand' ) {
        $args['orderby'] = 'rand';
    } else {
        $args['orderby'] = 'date';
        $args['order']   = 'DESC';
    }

    $query = new WP_Query( $args );
    
    return $query->have_posts() ? $query : false;
}