<?php
// ====== Estilos padre/hijo ======
add_action('wp_enqueue_scripts', function () {

  // CSS del tema padre
  wp_enqueue_style(
    'parent-style',
    get_template_directory_uri() . '/style.css',
    [],
    wp_get_theme(get_template())->get('Version')
  );

  // CSS del tema hijo (style.css en la raíz del child) con cache-busting
  $child_css = get_stylesheet_directory() . '/style.css';
  wp_enqueue_style(
    'child-style',
    get_stylesheet_directory_uri() . '/style.css',
    ['parent-style'],
    filemtime($child_css)
  );

  // CSS extra del hijo (si existe) en assets/css/child.css con cache-busting
  $css_extra = get_stylesheet_directory() . '/assets/css/child.css';
  if ( file_exists($css_extra) ) {
    wp_enqueue_style(
      'child-extra',
      get_stylesheet_directory_uri() . '/assets/css/child.css',
      ['child-style'],
      filemtime($css_extra)
    );
  }

  // CSS del carrusel (si existe) con cache-busting
  $css_carrusel = get_stylesheet_directory() . '/css/carrusel.css';
  if ( file_exists($css_carrusel) ) {
    wp_enqueue_style(
      'mi-carrusel-css',
      get_stylesheet_directory_uri() . '/css/carrusel.css',
      ['child-style'],
      filemtime($css_carrusel)
    );
  }

}, 100);

// ====== Soportes WooCommerce ======
add_action('after_setup_theme', function () {
  add_theme_support('woocommerce');
  add_theme_support('wc-product-gallery-zoom');
  add_theme_support('wc-product-gallery-lightbox');
  add_theme_support('wc-product-gallery-slider');
});

// ====== Single product: quitar el extracto sobre el botón ======
add_action('init', function () {
  remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
});

// ====== Carrusel ======
// Incluir el archivo PHP del carrusel si existe
$carrusel_php = get_stylesheet_directory() . '/inc/carrusel.php';
if ( file_exists($carrusel_php) ) {
  require $carrusel_php;
}

/*
// ===// ===// ===// ===// ===// ===// ===// ===// ===// ===/ FLUJO LENTEES ELIMINAR

// ====== Flujo de lentes (modular) ====================================================================================================================================
require_once get_stylesheet_directory() . '/inc/custom-lens-config.php';
require_once get_stylesheet_directory() . '/inc/custom-lens-flow.php';

// ====== Estilos y scripts del flujo ======
add_action( 'wp_enqueue_scripts', function () {

    // Cargar SOLO en la página de producto y SOLO si el flujo aplica (no en accesorios, solares, etc.)
    if ( ! function_exists('is_product') || ! is_product() ) {
        return; // fuera del single product no cargamos nada
    }

    // Si tenemos helpers, respetamos el modo para no cargar assets cuando es 'none'
    $enqueue_assets = true;
    if ( function_exists('clf_get_flow_mode') && function_exists('clf_flow_applies') ) {
        global $post;
        if ( $post && isset($post->ID) ) {
            $mode = clf_get_flow_mode( $post->ID ); // 'full' | 'kids' | 'sport' | 'none'
            if ( ! clf_flow_applies( $mode ) ) {
                $enqueue_assets = false; // no cargar CSS/JS si el flujo no se muestra
            }
        }
    }
    if ( ! $enqueue_assets ) return;

    // CSS con cache-busting
    $css_file = get_stylesheet_directory() . '/css/custom-lens-flow.css';
    wp_enqueue_style(
        'custom-lens-flow-css',
        get_stylesheet_directory_uri() . '/css/custom-lens-flow.css',
        [],
        file_exists($css_file) ? filemtime($css_file) : '2.1'
    );

    // JS con cache-busting
    $js_file = get_stylesheet_directory() . '/js/custom-lens-flow.js';
    wp_enqueue_script(
        'custom-lens-flow-js',
        get_stylesheet_directory_uri() . '/js/custom-lens-flow.js',
        ['jquery'],
        file_exists($js_file) ? filemtime($js_file) : '2.1',
        true
    );

    // Asegurar scripts de Woo para AJAX (por compatibilidad con temas)
    if ( class_exists('WooCommerce') ) {
        wp_enqueue_script( 'wc-add-to-cart' );
        wp_enqueue_script( 'wc-cart-fragments' );
    }
}, 100);
*/




// sssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss
require_once get_stylesheet_directory() . '/inc/lens-compare.php';
// sssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss




/* =================================================================
   REDISEÑO TOTAL DE TARJETAS DE PRODUCTO (WOOCOMMERCE)
   ================================================================= */

// ====== Catálogo Personalizado ======
// 1. Cargar la lógica PHP
$catalogo_php = get_stylesheet_directory() . '/inc/catalogo.php';
if ( file_exists($catalogo_php) ) {
    require_once $catalogo_php;
}

// 2. Encolar el CSS del catálogo (solo en la tienda y categorías)
add_action( 'wp_enqueue_scripts', function() {
    if ( is_shop() || is_product_category() || is_product_tag() ) {
        $css_catalogo = get_stylesheet_directory() . '/css/catalogo.css';
        if ( file_exists($css_catalogo) ) {
            wp_enqueue_style(
                'mmc-catalogo-css',
                get_stylesheet_directory_uri() . '/css/catalogo.css',
                [],
                filemtime($css_catalogo)
            );
        }
    }
}, 110 );

   
/*   

// 1. LIMPIEZA: Desenganchamos los elementos por defecto de WooCommerce/Kadence
// Quitamos el botón "Añadir al carrito" original (suele verse tosco)
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
// Quitamos el título y precio de su posición original (pegados a la imagen)
remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
// Quitamos la valoración (estrellitas) si la hubiera
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );


// 2. REESTRUCTURACIÓN: Creamos un nuevo contenedor para la info
// Justo después de la imagen, abrimos un <div> para agrupar textos
add_action( 'woocommerce_before_shop_loop_item_title', 'mmc_abrir_wrapper_info', 20 );
function mmc_abrir_wrapper_info() {
    echo '<div class="mmc-product-info-wrap">';
}

// 3. REINSERCIÓN: Volvemos a meter el título y precio dentro de nuestro nuevo div
// El título primero
add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_title', 25 );
// El precio después
add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_price', 30 );

// 4. TOQUE PREMIUM: Agregamos un botón textual elegante "Ver Producto" en vez del carrito
add_action( 'woocommerce_before_shop_loop_item_title', 'mmc_boton_ver_elegante', 35 );
function mmc_boton_ver_elegante() {
    global $product;
    // Solo si es un producto simple y está en stock
    if ( $product->is_in_stock() ) {
        echo '<div class="mmc-view-btn">Ver Detalles</div>';
    }
}

// 5. CIERRE: Cerramos el div de información antes de que termine la tarjeta
add_action( 'woocommerce_after_shop_loop_item', 'mmc_cerrar_wrapper_info', 5 );
function mmc_cerrar_wrapper_info() {
    echo '</div>'; // Cierra .mmc-product-info-wrap
}

*/

/**
 * Carga de archivos para Filtros y Rediseño de Tienda
 */

/**
 * CARGA DE LÓGICA Y ASSETS: FILTROS PREMIUM (DESKTOP + MOBILE)
 */

// 1. Incluir lógica
require get_stylesheet_directory() . '/inc/woocommerce-custom.php';
require get_stylesheet_directory() . '/inc/mobile-filter-drawer.php';

// 2. Cargar Assets
add_action( 'wp_enqueue_scripts', 'mmc_custom_assets' );
function mmc_custom_assets() {
    if ( is_shop() || is_product_category() ) {
        wp_enqueue_style( 'mmc-custom-filters', get_stylesheet_directory_uri() . '/css/custom-filters.css', array(), time() );
        wp_enqueue_script( 'mmc-custom-filters-js', get_stylesheet_directory_uri() . '/js/custom-filters.js', array('jquery'), time(), true );

        // Assets móviles
        wp_enqueue_style( 'mmc-mobile-filters-css', get_stylesheet_directory_uri() . '/css/mobile-filters.css', array(), time() );
        wp_enqueue_script( 'mmc-mobile-filters-js', get_stylesheet_directory_uri() . '/js/mobile-filters.js', array('jquery'), time(), true );
    }
}

// 3. Ejecutar el botón móvil al final del footer (para el efecto sticky)
add_action('wp_footer', 'mmc_cargar_panel_solo_en_tienda');
function mmc_cargar_panel_solo_en_tienda() {
    // Verificamos que WooCommerce esté activo y que estemos en la tienda o categoría
    if ( class_exists( 'WooCommerce' ) && ( is_shop() || is_product_category() ) ) {
        mmc_render_mobile_filter_button();
    }
}










/* ==========================================================================
   CARGA DE FOOTER PERSONALIZADO (SHORTCODE Y CSS)
   ========================================================================== */

// 1. Incluir la lógica y estructura HTML del shortcode
require get_stylesheet_directory() . '/inc/custom-footer.php';

// 2. Cargar los estilos del footer en TODA la web
add_action( 'wp_enqueue_scripts', 'mmc_custom_footer_assets' );
function mmc_custom_footer_assets() {
    // A diferencia de los filtros, esto NO tiene condicional "is_shop()" 
    // porque el footer debe verse bien en todas las páginas (Home, Contacto, etc.)
    wp_enqueue_style( 'mmc-custom-footer-css', get_stylesheet_directory_uri() . '/css/custom-footer.css', array(), time() );
}









/*

// ==========================================================================
 //  SOLUCIÓN DEFINITIVA PARA EL ERROR ERR_CACHE_MISS AL RETROCEDER
 //  ==========================================================================
add_filter( 'woocommerce_add_to_cart_redirect', 'mmc_forzar_redireccion_get_limpia', 99, 1 );
function mmc_forzar_redireccion_get_limpia( $url ) {
    // Verificamos si el cliente NO tiene activado "Redirigir al carrito" en los ajustes
    if ( get_option( 'woocommerce_cart_redirect_after_add' ) !== 'yes' ) {
        
         Si se acaba de presionar el botón de añadir al carrito mediante POST
        if ( isset( $_POST['add-to-cart'] ) && is_numeric( $_POST['add-to-cart'] ) ) {
            $product_id = absint( $_POST['add-to-cart'] );
            
            // Forzamos al navegador a hacer un "salto limpio" hacia la URL original del producto
            // Esto borra el POST del historial de Chrome y evita el ERR_CACHE_MISS
            return get_permalink( $product_id );
        }
    }
    return $url;
} */


// ==========================================================================
 //  ELIMINAR MENSAJE NATIVO DE "AÑADIDO AL CARRITO" EN WOOCOMMERCE
 //  ========================================================================== 
add_filter( 'wc_add_to_cart_message_html', '__return_empty_string' );

/*
add_action( 'wp_enqueue_scripts', 'disable_woocommerce_cart_fragments', 11 );
function disable_woocommerce_cart_fragments() {
    wp_dequeue_script( 'wc-cart-fragments' );
}
// Asegurar que los datos del formulario se procesen en la petición AJAX
//add_filter('woocommerce_add_to_cart_handler', function($handler, $product) {
    if (isset($_POST['tipo_lente'])) {
        return $product->get_type(); // Fuerza el procesamiento
    }
    return $handler;
}, 10, 2);
*/


/**
 * Función MMC para obtener los colores de un producto desde los atributos
 */
/**
 * MOTOR MMC: Obtener colores desde filtros premium
 */
/**
 * MOTOR MMC: Obtener colores desde Filtros Premium sin plugins extra
 */
/**
 * MOTOR MMC: Obtener colores directos del atributo pa_color
 */
 
 // COLORES EN CATALOGO
function mmc_obtener_colores_de_filtros( $product_id ) {
    $product = wc_get_product( $product_id );
    if ( ! $product ) return '';

    // 1. Obtenemos los colores asignados al producto (sin importar si es simple o variable)
    $terms = wc_get_product_terms( $product_id, 'pa_color', array( 'fields' => 'all' ) );

    // Si el producto no tiene colores, no mostramos nada
    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        return '';
    }

    $html = '<div class="mmc-filtros-colores" style="display:flex; gap:6px; margin-top:8px; flex-wrap:wrap; z-index:10; position:relative;">';

    foreach ( $terms as $term ) {
        $bg_style = '';
        $letra_rescate = '';
        
        // 2. Buscamos el color o imagen en los metadatos del término (Filtros Premium)
        $all_meta = get_term_meta( $term->term_id );
        
        if ( ! empty( $all_meta ) ) {
            foreach ( $all_meta as $key => $values ) {
                $val = $values[0] ?? '';
                // ¿Es un color hexadecimal? (ej: #ff0000)
                if ( is_string($val) && preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $val) ) {
                    $bg_style = 'background-color:' . $val . ';';
                    break;
                }
                // ¿Es una URL de imagen de filtro?
                if ( is_string($val) && preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $val) ) {
                    $bg_style = 'background-image:url(' . esc_url($val) . '); background-size:cover;';
                    break;
                }
            }
        }

        // Si el filtro guarda el color de una forma muy extraña y no lo encontramos arriba:
        if ( empty($bg_style) ) {
            $bg_style = 'background-color:#f0f0f0;'; // Fondo gris claro
            $letra_rescate = strtoupper( substr($term->name, 0, 1) ); // Ej: 'N' para Negro
        }

        // 3. Buscar la foto del producto en ese color (si es variable)
        $img_variacion = '';
        if ( $product->is_type('variable') ) {
            $variaciones = $product->get_available_variations();
            foreach ( $variaciones as $var ) {
                if ( isset($var['attributes']['attribute_pa_color']) && $var['attributes']['attribute_pa_color'] === $term->slug ) {
                    $img_variacion = $var['image']['src'] ?? '';
                    break;
                }
            }
        }

        // 4. Construimos el círculo
        $html .= sprintf(
            '<span class="mmc-color-dot" style="%s width:22px; height:22px; border-radius:50%%; display:flex; align-items:center; justify-content:center; border:1px solid #dcdcdc; cursor:pointer; transition:transform 0.2s; box-shadow: inset 0 0 3px rgba(0,0,0,0.1); font-size:11px; font-weight:bold; color:#555;" title="%s" data-img="%s">%s</span>',
            $bg_style,
            esc_attr( $term->name ),
            esc_url( $img_variacion ),
            esc_html( $letra_rescate )
        );
    }
    
    $html .= '</div>';
    return $html;
}








/* =================================================================
   NUEVO MÓDULO: REDISEÑO SINGLE PRODUCT
   ================================================================= */
// 1. Cargar la lógica PHP (Swatches, Botones y Enlaces)
$single_producto_php = get_stylesheet_directory() . '/inc/single-producto.php';
if ( file_exists($single_producto_php) ) {
    require_once $single_producto_php;
}

// 2. Cargar el Diseño (CSS) y la Interacción (JS)
add_action( 'wp_enqueue_scripts', 'mmc_cargar_assets_single_product' );
function mmc_cargar_assets_single_product() {
    // Solo cargamos estos archivos si el cliente está viendo un producto individual
    if ( is_product() ) { 
        wp_enqueue_style( 'mmc-single-producto-css', get_stylesheet_directory_uri() . '/css/single-producto.css', [], time() );
        wp_enqueue_script( 'mmc-single-producto-js', get_stylesheet_directory_uri() . '/js/single-producto.js', ['jquery'], time(), true );
    }
}










// ====== Ecosistema Modal Óptica ======
// REEMPLAZAR POR:
add_action( 'wp_enqueue_scripts', 'mmc_cargar_archivos_modal' );
function mmc_cargar_archivos_modal() {
    if ( is_product() ) {
        wp_enqueue_style( 'mmc-modal-css', get_stylesheet_directory_uri() . '/css/modal-optica.css', [], time() );
        wp_enqueue_script( 'mmc-modal-js', get_stylesheet_directory_uri() . '/js/modal-optica.js', ['jquery'], time() , true );
        wp_localize_script( 'mmc-modal-js', 'mmcAjax', [
            'url'   => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mmc_receta_nonce'),
        ]);
    }
}




// Cargar el HTML del Modal
$modal_php = get_stylesheet_directory() . '/inc/modal-optica.php';
if ( file_exists($modal_php) ) {
    require_once $modal_php;
}

require_once get_stylesheet_directory() . '/inc/modal-optica-admin.php';
























// Agrégalo junto a tus otros register_setting
register_setting('TU_GRUPO_DE_OPCIONES', 'mmc_ben_por_categoria'); 
// OJO: Cambia 'TU_GRUPO_DE_OPCIONES' por el mismo nombre que tengan los de arriba.




// AÑADIR FOTOS DE VARIACIONES
add_action( 'woocommerce_product_after_variable_attributes', 'mmc_campos_galeria_variacion', 10, 3 );
function mmc_campos_galeria_variacion( $loop, $variation_data, $variation ) {
    $galeria = get_post_meta( $variation->ID, '_mmc_galeria_ids', true );
    ?>
    <div class="form-row form-row-full mmc-galeria-wrapper">
        <label>Galería adicional de la variación (IDs separados por coma):</label>
        <input type="text" name="mmc_galeria_ids[<?php echo $loop; ?>]" value="<?php echo esc_attr( $galeria ); ?>" class="mmc-galeria-input" />
        <button type="button" class="button mmc-add-gallery">Añadir Fotos</button>
        <small>Aquí puedes poner tantos IDs como quieras.</small>
    </div>
    <?php
}

// 2. Guardar los datos al guardar el producto
add_action( 'woocommerce_save_product_variation', 'mmc_guardar_galeria_variacion', 10, 2 );
function mmc_guardar_galeria_variacion( $variation_id, $i ) {
    if ( isset( $_POST['mmc_galeria_ids'][$i] ) ) {
        update_post_meta( $variation_id, '_mmc_galeria_ids', sanitize_text_field( $_POST['mmc_galeria_ids'][$i] ) );
    }
}



// GALERIA DE PRODUCTOS SINGLE PRODUCT
require_once get_stylesheet_directory() . '/inc/variation-gallery-logic.php';

// Cargar archivos en la web
add_action('wp_enqueue_scripts', 'mmc_assets_galeria');
function mmc_assets_galeria() {
    if ( is_product() ) {
        // 1. Cargamos el motor de Swiper.js
        wp_enqueue_style( 'swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', array(), null );
        wp_enqueue_script( 'swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), null, true );

        // 2. Cargamos nuestros propios archivos
        wp_enqueue_style( 'mmc-galeria-css', get_stylesheet_directory_uri() . '/css/mmc-gallery-style.css', array('swiper-css'), time() );
        wp_enqueue_script( 'mmc-galeria-js', get_stylesheet_directory_uri() . '/assets/js/mmc-gallery-engine.js', array('jquery', 'swiper-js'), time(), true );
    }
}












/* ========================================================
   SISTEMA CUSTOM DE RECOMENDACIONES (UP-SELLS Y CROSS-SELLS)
   ======================================================== */
require_once get_stylesheet_directory() . '/inc/recomendaciones/admin-panel.php';
require_once get_stylesheet_directory() . '/inc/recomendaciones/motor-busqueda.php';
require_once get_stylesheet_directory() . '/inc/recomendaciones/vista-producto.php';
require_once get_stylesheet_directory() . '/inc/recomendaciones/vista-carrito.php';

/* ========================================================
   CARGAR CSS DEL CATÁLOGO
   ======================================================== */
add_action( 'wp_enqueue_scripts', 'mmc_cargar_css_recomendaciones' );
function mmc_cargar_css_recomendaciones() {
    wp_enqueue_style( 
        'mmc-recomendaciones-style', 
        get_stylesheet_directory_uri() . '/css/catalogo.css', // Aquí irán también los estilos de Swiper
        array(), 
        time() // Evita el caché durante el desarrollo
    );
}

/* ========================================================
   CARGAR SWIPER.JS PARA EL CARRUSEL DE RECOMENDACIONES
   ======================================================== */
add_action( 'wp_enqueue_scripts', 'mmc_cargar_recursos_swiper' );
function mmc_cargar_recursos_swiper() {
    // Solo cargarlo si estamos en la página de un producto o en el carrito
    if ( is_product() || is_cart() ) {
        // 1. CSS base de Swiper desde su servidor
        wp_enqueue_style( 'swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', array(), '11.0.0' );
        
        // 2. JS principal de Swiper desde su servidor
        wp_enqueue_script( 'swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), '11.0.0', true );
        
        // 3. Tu archivo que "enciende" el carrusel (el que vamos a modificar ahora)
        wp_enqueue_script( 'mmc-carrusel-init', get_stylesheet_directory_uri() . '/js/carrusel-drag.js', array('swiper-js'), time(), true );
    }
}




/* ==========================================================================
   SUBIDA DE IMAGEN DE RECETA (Modal Óptica)
   ========================================================================== */
add_action('wp_ajax_mmc_subir_receta', 'mmc_subir_receta_callback');
add_action('wp_ajax_nopriv_mmc_subir_receta', 'mmc_subir_receta_callback');
function mmc_subir_receta_callback() {
    check_ajax_referer('mmc_receta_nonce', 'nonce');

    if (empty($_FILES['archivo'])) {
        wp_send_json_error(['message' => 'No se recibió ningún archivo.']);
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $permitidos = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    $ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $permitidos, true)) {
        wp_send_json_error(['message' => 'Formato no permitido. Usa PDF, JPG, GIF o PNG.']);
    }
    if ($_FILES['archivo']['size'] > 5 * 1024 * 1024) {
        wp_send_json_error(['message' => 'El archivo supera los 5MB.']);
    }

    $attachment_id = media_handle_upload('archivo', 0);

    if (is_wp_error($attachment_id)) {
        wp_send_json_error(['message' => $attachment_id->get_error_message()]);
    }

    wp_send_json_success([
        'attachment_id' => $attachment_id,
        'url'           => wp_get_attachment_url($attachment_id),
        'filename'      => basename(get_attached_file($attachment_id)),
    ]);
}





/* ==========================================================================
   FLUJO DE LENTES → METADATOS DEL CARRITO Y DEL PEDIDO
   ========================================================================== */

// 1. Capturar y sanitizar el flujo al añadir al carrito
add_action('woocommerce_add_cart_item_data', 'mmc_add_cart_item_data', 10, 2);
function mmc_add_cart_item_data($cart_item_data, $product_id) {
    if (!empty($_POST['mmc_flujo_lentes'])) {
        $raw     = wp_unslash($_POST['mmc_flujo_lentes']);
        $decoded = json_decode($raw, true);
        if (is_array($decoded) && !empty($decoded)) {
            $cart_item_data['mmc_flujo_lentes'] = mmc_sanitizar_flujo_lentes($decoded);
            // Clave única para que WooCommerce NO fusione dos configuraciones distintas del mismo producto
            $cart_item_data['unique_key'] = md5(microtime() . wp_rand());
        }
    }
    return $cart_item_data;
}

function mmc_sanitizar_flujo_lentes($data) {
    $campos_permitidos = [
        'label','precio','od_sph','os_sph','od_cyl','os_cyl','od_axi','os_axi',
        'pd_single','pd_izq','pd_der','dos_pd','comentarios','nombre','anio_nacimiento',
        'attachment_id','url','filename','metodo','correo_destino','descripcion',
    ];
    $limpio = [];
    foreach ($data as $key => $val) {
        if (!is_array($val)) continue;
        $item = [];
        foreach ($campos_permitidos as $campo) {
            if (!isset($val[$campo])) continue;
            if ($campo === 'precio')            { $item[$campo] = floatval($val[$campo]); }
            elseif ($campo === 'dos_pd')        { $item[$campo] = !empty($val[$campo]); }
            elseif ($campo === 'attachment_id') { $item[$campo] = absint($val[$campo]); }
            elseif ($campo === 'url')           { $item[$campo] = esc_url_raw($val[$campo]); }
            elseif ($campo === 'comentarios' || $campo === 'descripcion') { $item[$campo] = sanitize_textarea_field($val[$campo]); }
            else { $item[$campo] = sanitize_text_field($val[$campo]); }
        }
        $limpio[sanitize_key($key)] = $item;
    }
    return $limpio;
}

// 2. Etiquetas legibles (mismo criterio que el labelMap del JS)
function mmc_flujo_labels() {
    return [
        'uso'                   => 'Tipo de uso',
        'tipo-lente'             => 'Tipo de lente',
        'readers'                => 'Medida (Readers)',
        'prescripcion'           => 'Método de prescripción',
        'prescripcion_valores'   => 'Receta ingresada',
        'prescripcion_imagen'    => 'Receta (archivo/correo)',
        'proteccion'             => 'Protección',
        'color_proteccion'       => 'Color',
        'subproteccion'          => 'Opción adicional',
        'color_subproteccion'    => 'Color',
        'indice'                 => 'Índice',
        'recubrimiento'          => 'Recubrimiento',
    ];
}

function mmc_formatear_valor_flujo($key, $val) {
    if ($key === 'prescripcion_valores') {
        $partes = [];
        if (!empty($val['nombre']))           $partes[] = 'Nombre: ' . $val['nombre'];
        if (!empty($val['anio_nacimiento']))  $partes[] = 'Año nac.: ' . $val['anio_nacimiento'];
        $partes[] = 'OD → SPH ' . ($val['od_sph'] ?? '—') . ' / CYL ' . ($val['od_cyl'] ?? '—') . ' / AXI ' . ($val['od_axi'] ?? '—');
        $partes[] = 'OS → SPH ' . ($val['os_sph'] ?? '—') . ' / CYL ' . ($val['os_cyl'] ?? '—') . ' / AXI ' . ($val['os_axi'] ?? '—');
        if (!empty($val['dos_pd'])) {
            $partes[] = 'PD Izq: ' . ($val['pd_izq'] ?? '—') . ' | PD Der: ' . ($val['pd_der'] ?? '—');
        } else {
            $partes[] = 'PD: ' . ($val['pd_single'] ?? '—');
        }
        if (!empty($val['comentarios'])) $partes[] = 'Comentarios: ' . $val['comentarios'];
        return implode(' | ', $partes);
    }

    if ($key === 'prescripcion_imagen') {
        if (!empty($val['metodo']) && $val['metodo'] === 'correo') {
            return 'El cliente enviará la receta por correo a: ' . ($val['correo_destino'] ?? '');
        }
        return ''; // el link del archivo se agrega aparte como meta independiente (ver más abajo)
    }

    if (isset($val['label'])) {
        $texto = $val['label'];
        if (!empty($val['descripcion'])) $texto .= ' — ' . wp_strip_all_tags($val['descripcion']);
        return $texto;
    }

    return '';
}

// 3. Mostrar el resumen en la página de Carrito / Checkout
add_filter('woocommerce_get_item_data', 'mmc_mostrar_flujo_en_carrito', 10, 2);
function mmc_mostrar_flujo_en_carrito($item_data, $cart_item) {
    if (empty($cart_item['mmc_flujo_lentes'])) return $item_data;
    $labels = mmc_flujo_labels();

    foreach ($cart_item['mmc_flujo_lentes'] as $key => $val) {
        $texto = mmc_formatear_valor_flujo($key, $val);
        if ($texto === '') continue;
        $item_data[] = ['key' => $labels[$key] ?? ucfirst($key), 'value' => $texto];
    }

    if (!empty($cart_item['mmc_flujo_lentes']['prescripcion_imagen']['url'])) {
        $item_data[] = ['key' => 'Archivo de receta', 'value' => $cart_item['mmc_flujo_lentes']['prescripcion_imagen']['filename'] ?? 'Ver archivo'];
    }

    return $item_data;
}

// 4. Guardar como metadatos del ítem del pedido (visible en Pedidos → detalle del admin)
add_action('woocommerce_checkout_create_order_line_item', 'mmc_guardar_flujo_en_pedido', 10, 4);
function mmc_guardar_flujo_en_pedido($item, $cart_item_key, $values, $order) {
    if (empty($values['mmc_flujo_lentes'])) return;
    $labels = mmc_flujo_labels();

    foreach ($values['mmc_flujo_lentes'] as $key => $val) {
        $texto = mmc_formatear_valor_flujo($key, $val);
        if ($texto === '') continue;
        $item->add_meta_data($labels[$key] ?? ucfirst($key), $texto, true);
    }

    if (!empty($values['mmc_flujo_lentes']['prescripcion_imagen']['url'])) {
        $item->add_meta_data('Archivo de receta', $values['mmc_flujo_lentes']['prescripcion_imagen']['url'], true);
    }
}

// 5. Convierte el link del archivo de receta en un enlace clicable dentro del admin de Pedidos
add_filter('woocommerce_order_item_display_meta_value', 'mmc_link_receta_en_pedido', 10, 3);
function mmc_link_receta_en_pedido($value, $meta, $item) {
    if ($meta->key === 'Archivo de receta' && filter_var($value, FILTER_VALIDATE_URL)) {
        return '<a href="' . esc_url($value) . '" target="_blank" rel="noopener">Ver receta subida</a>';
    }
    return $value;
}
