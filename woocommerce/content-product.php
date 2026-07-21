<?php
/**
 * Plantilla Personalizada de Producto para WooCommerce
 * Sobrescribe a Kadence al 100%
 */
defined( 'ABSPATH' ) || exit;

global $product;
if ( empty( $product ) || ! $product->is_visible() ) {
    return;
}

// Preparamos la segunda imagen (Hover)
$attachment_ids = $product->get_gallery_image_ids();
$hover_img_src = '';
if ( $attachment_ids && ! empty( $attachment_ids ) ) {
    $hover_img_src = wp_get_attachment_image_url( $attachment_ids[0], 'woocommerce_thumbnail' );
}
?>

<li <?php wc_product_class( 'mmc-custom-product-card', $product ); ?>>
    
    <?php woocommerce_show_product_loop_sale_flash(); ?>

    <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="mmc-img-link">
        <div class="mmc-catalogo-img-wrapper">
            <?php echo woocommerce_get_product_thumbnail(); ?>
            <?php if ( $hover_img_src ) : ?>
                <img src="<?php echo esc_url( $hover_img_src ); ?>" class="hover-image" alt="Vista alternativa">
            <?php endif; ?>
        </div>
    </a>

    <div class="mmc-info-box">
        
        <div class="mmc-cat">
            <?php 
            $cats = wc_get_product_category_list( $product->get_id(), ', ' );
            echo $cats ? $cats : '<span class="mmc-no-cat"></span>'; 
            ?>
        </div>

        <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="mmc-title-link">
            <h2 class="mmc-title"><?php echo esc_html( $product->get_name() ); ?></h2>
        </a>

        <?php if ( function_exists('mmc_obtener_colores_de_filtros') ) {
            echo mmc_obtener_colores_de_filtros( $product->get_id() );
        } ?>

        <div class="mmc-price">
            <?php echo $product->get_price_html(); ?>
        </div>

    </div>
</li>