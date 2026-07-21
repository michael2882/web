<?php
// inc/carrusel.php
if ( ! defined('ABSPATH') ) exit;

if ( ! function_exists('custom_product_carousel') ) {
  function custom_product_carousel( $atts ) {
    if ( is_admin() && ! ( defined('DOING_AJAX') && DOING_AJAX ) ) return '';

    $atts = shortcode_atts([
      'products'       => 12,
      'columns'        => 4,          
      'category'       => '',
      'orderby'        => 'date',
      'order'          => 'DESC',
      'on_sale'        => 'false',
      'featured'       => 'false',    
      'show_category'  => 'true',
      'show_sale'      => 'true',
      'show_button'    => 'true',
      'autoplay'       => 'true',
      'autoplay_speed' => 4000,
      'half_width'     => 'false', // 🔥 NUEVO PARÁMETRO PARA COLUMNAS PEQUEÑAS 🔥
    ], $atts, 'product_carousel');

    wp_enqueue_style( 'swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], '11.0.0' );
    wp_enqueue_script( 'swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], '11.0.0', true );

    $args = [
      'post_type'      => 'product',
      'posts_per_page' => intval($atts['products']),
      'post_status'    => 'publish',
      'orderby'        => 'date',
      'order'          => ( strtoupper($atts['order']) === 'ASC' ? 'ASC' : 'DESC' ),
      'tax_query'      => [],
    ];

    if ($atts['orderby'] === 'price') { $args['meta_key'] = '_price'; $args['orderby'] = 'meta_value_num'; }
    elseif ($atts['orderby'] === 'popularity') { $args['meta_key'] = 'total_sales'; $args['orderby'] = 'meta_value_num'; }
    elseif ($atts['orderby'] === 'rand') { $args['orderby'] = 'rand'; }

    if ( ! empty($atts['category']) ) {
      $cats = array_map('trim', explode(',', sanitize_text_field($atts['category'])));
      $args['tax_query'][] = ['taxonomy'=>'product_cat','field'=>'slug','terms'=>$cats];
    }
    if ( $atts['featured'] === 'true' ) {
      $args['tax_query'][] = ['taxonomy'=>'product_visibility','field'=>'name','terms'=>['featured'],'operator'=>'IN'];
    }
    if ( $atts['on_sale'] === 'true' && function_exists('wc_get_product_ids_on_sale') ) {
      $args['post__in'] = wc_get_product_ids_on_sale();
    }

    try { $loop = new WP_Query($args); } catch (Exception $e) { return ''; }

    $uid      = uniqid('swiper_');
    $columns  = max(1, intval($atts['columns']));
    $autoplay = ($atts['autoplay'] === 'true') ? 'true' : 'false';
    $is_half  = ($atts['half_width'] === 'true') ? 'true' : 'false';

    ob_start(); ?>
    
    <div id="<?php echo esc_attr($uid); ?>" class="swiper mmc-home-carousel" 
         data-columns="<?php echo esc_attr($columns); ?>"
         data-autoplay="<?php echo esc_attr($autoplay); ?>"
         data-speed="<?php echo esc_attr(intval($atts['autoplay_speed'])); ?>"
         data-half-width="<?php echo esc_attr($is_half); ?>">
         
      <ul class="swiper-wrapper products" style="margin:0; padding: 10px 5px 20px 5px; list-style:none;">
      <?php
      if ( $loop->have_posts() ) :
        while ( $loop->have_posts() ) : $loop->the_post(); global $product;

          $sale_badge = '';
          if ( $atts['show_sale'] === 'true' && $product->is_on_sale() ) {
            $regular = (float) $product->get_regular_price();
            $sale    = (float) $product->get_sale_price();
            if ($regular > 0 && $sale > 0) $sale_badge = '-' . round((($regular - $sale)/$regular)*100) . '%';
          }

          $hover_img = '';
          $attachment_ids = $product->get_gallery_image_ids();
          if ( ! empty( $attachment_ids ) ) {
            $hover_img = wp_get_attachment_image( $attachment_ids[0], 'woocommerce_thumbnail', false, array( 'class' => 'hover-image' ) );
          }
          ?>
          
          <li class="swiper-slide mmc-home-card">
            <?php if ($sale_badge): ?><span class="mmc-home-sale-badge"><?php echo esc_html($sale_badge); ?></span><?php endif; ?>

            <a href="<?php the_permalink(); ?>" class="mmc-home-img-link">
              <div class="mmc-home-img-wrapper">
                <?php echo $product->get_image('woocommerce_thumbnail'); echo $hover_img; ?>
              </div>
            </a>
            
            <div class="mmc-home-info-box">
              <?php if ( $atts['show_category'] === 'true' ): ?>
                <span class="mmc-home-cat"><?php echo wc_get_product_category_list( $product->get_id(), ', ' ); ?></span>
              <?php endif; ?>
              
              <div class="mmc-home-title-price-row">
                <a href="<?php the_permalink(); ?>" class="mmc-home-title-link">
                  <h3 class="mmc-home-title" title="<?php echo esc_attr(get_the_title()); ?>"><?php the_title(); ?></h3>
                </a>
                <span class="mmc-home-price"><?php echo $product->get_price_html(); ?></span>
              </div>
              
              <?php if ( function_exists('mmc_obtener_colores_de_filtros') ) echo mmc_obtener_colores_de_filtros( $product->get_id() ); ?>

              <?php if ( $atts['show_button'] === 'true' ): ?>
                  <a href="<?php echo esc_url( get_permalink() ); ?>" class="mmc-home-buy-btn">Ver producto</a>
              <?php endif; ?>
            </div>
          </li>

        <?php endwhile; wp_reset_postdata();
      else:
        echo '<li class="swiper-slide">No hay productos para mostrar.</li>';
      endif; ?>
      </ul> 

      <div class="swiper-button-prev mmc-home-swiper-btn"></div>
      <div class="swiper-button-next mmc-home-swiper-btn"></div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
      var sliderId = '<?php echo esc_js($uid); ?>';
      var el = document.getElementById(sliderId);
      
      if (el && typeof Swiper !== 'undefined') {
        var columns  = parseInt(el.getAttribute('data-columns')) || 4;
        var autoplay = el.getAttribute('data-autoplay') === 'true';
        var speed    = parseInt(el.getAttribute('data-speed')) || 4000;
        var isHalf   = el.getAttribute('data-half-width') === 'true';

        // LÓGICA DE BREAKPOINTS INTELIGENTE
        var responsiveBreakpoints = {};
        
        if (isHalf) {
            // Reglas para carrusel en "Mitad de Pantalla"
            responsiveBreakpoints = {
                0:    { slidesPerView: 1.2 }, 
                480:  { slidesPerView: 2 }, 
                768:  { slidesPerView: 1.3 }, 
                1024: { slidesPerView: 1.5 }, 
                1200: { slidesPerView: columns } 
            };
        } else {
            // 🔥 NUEVAS REGLAS: Ancho completo con tarjetas más grandes 🔥
            responsiveBreakpoints = {
                0:    { slidesPerView: 1.2 }, // Celulares verticales (1 grande + pedacito)
                480:  { slidesPerView: 1.5 }, // Celulares grandes u horizontales
                640:  { slidesPerView: 2 },   // Tablets pequeñas
                768:  { slidesPerView: 2.5 }, // iPad vertical (2 grandes + pedacito, en lugar de 3 apretadas)
                1024: { slidesPerView: 3 },   // Laptops pequeñas / iPad horizontal (3 grandes, en lugar de 4)
                1200: { slidesPerView: columns } // Monitores PC (Aquí recién mostramos las 4 completas)
            };
        }

        var swiperOptions = {
          grabCursor: true,
          spaceBetween: 15,
          navigation: { nextEl: el.querySelector('.swiper-button-next'), prevEl: el.querySelector('.swiper-button-prev') },
          breakpoints: responsiveBreakpoints
        };

        if (autoplay) {
          swiperOptions.autoplay = { delay: speed, disableOnInteraction: false, pauseOnMouseEnter: true };
        }

        new Swiper(el, swiperOptions);
      }

      if (typeof jQuery !== 'undefined') {
        jQuery(document).on('click', '#' + sliderId + ' .mmc-color-dot', function(e) {
          e.preventDefault(); 
          var $punto = jQuery(this);
          var nuevaImg = $punto.data('img'); 
          if (nuevaImg) {
              var $tarjeta = $punto.closest('.mmc-home-card');
              var $imagenPrincipal = $tarjeta.find('.mmc-home-img-wrapper > img:not(.hover-image)');
              $imagenPrincipal.attr('src', nuevaImg).attr('srcset', '');
              $tarjeta.find('.mmc-color-dot').css({'transform': 'scale(1)', 'border-color': '#dcdcdc'});
              $punto.css({'transform': 'scale(1.2)', 'border-color': '#111'});
          }
        });
      }
    });
    </script>
    <?php
    return ob_get_clean();
  }

  add_action('init', function(){ add_shortcode('product_carousel', 'custom_product_carousel'); });
}