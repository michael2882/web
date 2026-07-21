<?php
/**
 * Template para mostrar el contenido del producto individual.
 */

defined( 'ABSPATH' ) || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form();
	return;
}
?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>

    <div class="mmc-product-top-container">
        
        <div class="mmc-column-images">
            
            <div id="mmc-custom-gallery" class="mmc-gallery-main-wrapper">
                <div id="mmc-thumbs-wrapper" class="mmc-thumbnails-stack"></div>

                <div class="mmc-main-image-container swiper mmc-main-swiper">
                    <div class="swiper-wrapper" id="mmc-main-swipe-track">
                        <div class="swiper-slide">
                            <img id="mmc-main-view" src="<?php echo wp_get_attachment_url( $product->get_image_id() ); ?>" />
                        </div>
                    </div>
                </div>
            </div> 
            
            <?php if (function_exists('mmc_pildora_beneficios_directa')) mmc_pildora_beneficios_directa('desktop'); ?>

        </div>


        <div class="summary entry-summary mmc-column-info"  style="margin-bottom: 0px;">
             
            <?php
            /**
             * Hook: woocommerce_single_product_summary.
             * ¡AQUÍ CARGAN EL TÍTULO, PRECIO Y BOTONES DE CARRITO!
             */
            do_action( 'woocommerce_single_product_summary' );
            ?>
            
            <div class="mmc-movil-benefits-area" style="width: 100%; margin-top: 25px; margin-bottom: 10px;">
                <?php if (function_exists('mmc_pildora_beneficios_directa')) mmc_pildora_beneficios_directa('mobile'); ?>
            </div>

        </div>

    </div>
    
<?php // Tu etiqueta de cierre PHP original
    /**
     * Hook: woocommerce_after_single_product_summary.
     * Aquí viven las PESTAÑAS (Tabs), Upsells y Relacionados.
     */
    do_action( 'woocommerce_after_single_product_summary' );
    ?>

</div>

<?php do_action( 'woocommerce_after_single_product' ); ?>