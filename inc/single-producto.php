<?php
defined( 'ABSPATH' ) || exit;

/* ========================================================
   1. SWATCHES DE COLOR + TEXTO DINÁMICO | DISEÑO COLOR EN INFO PRODUCTO
   ======================================================== */
add_filter( 'woocommerce_dropdown_variation_attribute_options_html', 'mmc_reemplazar_select_por_swatches_single', 10, 2 );
function mmc_reemplazar_select_por_swatches_single( $html, $args ) {
    if ( $args['attribute'] !== 'pa_color' ) return $html;
    $options = $args['options'];
    if ( empty( $options ) ) return $html;

    $nuevo_html = '<div class="mmc-color-selector-wrapper">';
    $nuevo_html .= '<div class="mmc-color-label">Color: <span class="mmc-color-name">Selecciona un color</span></div>';
    $nuevo_html .= '<div style="display:none;" class="mmc-select-oculto">' . $html . '</div>';
    $nuevo_html .= '<div class="mmc-swatches-individuales">';

    foreach ( $options as $option ) {
        $term = get_term_by( 'slug', $option, 'pa_color' );
        if ( ! $term ) continue;
        $bg_style = ''; $letra_rescate = '';
        $all_meta = get_term_meta( $term->term_id );
        if ( ! empty( $all_meta ) ) {
            foreach ( $all_meta as $key => $values ) {
                $val = $values[0] ?? '';
                if ( is_string($val) && preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $val) ) { $bg_style = 'background-color:' . $val . ';'; break; }
                if ( is_string($val) && preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $val) ) { $bg_style = 'background-image:url(' . esc_url($val) . '); background-size:cover;'; break; }
            }
        }
        if ( empty($bg_style) ) { $bg_style = 'background-color:#f0f0f0;'; $letra_rescate = strtoupper( substr($term->name, 0, 1) ); }
        $nuevo_html .= sprintf('<span class="mmc-swatch-punto" data-valor="%s" data-nombre="%s" style="%s" title="%s">%s</span>', esc_attr( $option ), esc_attr( $term->name ), $bg_style, esc_attr( $term->name ), esc_html( $letra_rescate ) );
    }
    $nuevo_html .= '</div></div>';
    return $nuevo_html;
}


/* ========================================================
   2. BOTONES CONDICIONALES DE INFO PRODUCTO PARA AÑADIR CARRITO O SELECCIONAR LUNA
   ======================================================== */
add_action( 'woocommerce_after_add_to_cart_form', 'mmc_botones_y_enlaces_optica' );
function mmc_botones_y_enlaces_optica() {
    global $product;
    $es_sol = has_term( array('lentes-de-sol', 'ninos'), 'product_cat', $product->get_id() );
    $es_accesorio = has_term( 'accesorios', 'product_cat', $product->get_id() ); 

    echo '<div class="mmc-contenedor-botones-dinamicos">';
    if ( !$es_accesorio ) {
        if ( $es_sol ) {
            echo '<button type="button" id="mmc-abrir-modal-btn" class="mmc-btn-azul-outline">Select Lenses</button>';
        } else {
            echo '<style>.woocommerce div.product form.cart .single_add_to_cart_button { display: none !important; }</style>';
            echo '<button type="button" id="mmc-abrir-modal-btn" class="mmc-btn-azul-solido">Select Lenses</button>';
        }
    }
    echo '</div>';
}



/* ========================================================
   2. REGISTRAR LAS OPCIONES
   ======================================================== */
add_action('admin_init', 'mmc_registrar_opciones_beneficios');
function mmc_registrar_opciones_beneficios() {
    // Cupón y Beneficios Superiores
    register_setting('mmc_beneficios_grupo', 'mmc_texto_cupon');
    register_setting('mmc_beneficios_grupo', 'mmc_codigo_cupon');
    for($i=1;$i<=3;$i++){ 
        register_setting('mmc_beneficios_grupo', "mmc_ben_{$i}_txt"); 
        register_setting('mmc_beneficios_grupo', "mmc_ben_{$i}_ico"); 
    }

    // Pestañas, Categorías y Envíos
    for($i=1;$i<=4;$i++){ register_setting('mmc_beneficios_grupo', "mmc_ico_tab_{$i}"); }
    register_setting('mmc_beneficios_grupo', 'mmc_envio_texto_libre');
    register_setting('mmc_beneficios_grupo', 'mmc_categorias_activas');
    
    // Garantía
    register_setting('mmc_beneficios_grupo', 'mmc_gar_main_ico');
    register_setting('mmc_beneficios_grupo', 'mmc_gar_titulo');
    register_setting('mmc_beneficios_grupo', 'mmc_gar_subtexto');
    for($j=1;$j<=3;$j++){ 
        register_setting('mmc_beneficios_grupo', "mmc_gar_item_{$j}_ico"); 
        register_setting('mmc_beneficios_grupo', "mmc_gar_item_{$j}_txt"); 
    }

    // Pestaña Nueva: Barra de Galería (Se guarda como array por Slugs)
    register_setting('mmc_beneficios_grupo', 'mmc_ben_por_categoria');
}

add_action('admin_menu', 'mmc_menu_beneficios');
function mmc_menu_beneficios() {
    add_submenu_page('woocommerce', 'Promo Lentes', 'Promo Lentes', 'manage_options', 'mmc-promo-lentes', 'mmc_render_pagina_beneficios');
}

/* ========================================================
   3. PANEL ADMINISTRABLE UNIFICADO CON PESTAÑAS
   ======================================================== */
function mmc_render_pagina_beneficios() {
    ?>
    <div class="wrap">
        <h1>⚙️ Configuración Maestra de Producto</h1>
        <h2 class="nav-tab-wrapper">
            <a href="#tab-cupon" class="nav-tab nav-tab-active">Cupón y Beneficios</a>
            <a href="#tab-tabs" class="nav-tab">Iconos Pestañas</a>
            <a href="#tab-envios" class="nav-tab">Envíos y Retornos</a>
            <a href="#tab-garantia" class="nav-tab">Garantía</a>
            <a href="#tab-barra-galeria" class="nav-tab">Barra Galería</a>
        </h2>

        <form method="post" action="options.php" style="background:#fff; padding:20px; border:1px solid #ccc; border-top:none;">
            <?php settings_fields('mmc_beneficios_grupo'); ?>

            <div id="tab-cupon" class="mmc-tab-content">
                <h3>Banner Superior</h3>
                <table class="form-table">
                    <tr><th>Texto Cupón:</th><td><input type="text" name="mmc_texto_cupon" value="<?php echo esc_attr(get_option('mmc_texto_cupon')); ?>" class="regular-text" /></td></tr>
                    <tr><th>Código:</th><td><input type="text" name="mmc_codigo_cupon" value="<?php echo esc_attr(get_option('mmc_codigo_cupon')); ?>" class="regular-text" /></td></tr>
                </table>
                <h3>Los 3 Beneficios (Bajo el cupón)</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead><tr><th>Beneficio</th><th>Texto</th><th>Icono (Vacío = ✓)</th></tr></thead>
                    <tbody>
                        <?php for($i=1;$i<=3;$i++): ?>
                        <tr><td>#<?php echo $i; ?></td>
                            <td><input type="text" name="mmc_ben_<?php echo $i; ?>_txt" value="<?php echo esc_attr(get_option("mmc_ben_{$i}_txt")); ?>" style="width:100%" /></td>
                            <td><input type="text" name="mmc_ben_<?php echo $i; ?>_ico" value="<?php echo esc_attr(get_option("mmc_ben_{$i}_ico")); ?>" style="width:100%" /></td></tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
                <h3>Categorías Activas</h3>
                <div style="max-height:150px; overflow-y:auto; border:1px solid #ccc; padding:10px; margin-top:10px;">
                    <?php
                    $categorias = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => false));
                    $seleccionadas = get_option('mmc_categorias_activas', array());
                    if(!is_array($seleccionadas)) $seleccionadas = array();
                    foreach ($categorias as $cat) {
                        $checked = in_array($cat->slug, $seleccionadas) ? 'checked' : '';
                        echo '<label style="display:block;"><input type="checkbox" name="mmc_categorias_activas[]" value="'.$cat->slug.'" '.$checked.'> '.$cat->name.'</label>';
                    }
                    ?>
                </div>
            </div>

            <div id="tab-tabs" class="mmc-tab-content" style="display:none;">
                <h3>Iconos de Pestañas Inferiores (URLs)</h3>
                <table class="form-table">
                    <tr><th>1. About the frame:</th><td><input type="text" name="mmc_ico_tab_1" value="<?php echo esc_attr(get_option('mmc_ico_tab_1')); ?>" class="regular-text" /></td></tr>
                    <tr><th>2. Sizing & Fit:</th><td><input type="text" name="mmc_ico_tab_2" value="<?php echo esc_attr(get_option('mmc_ico_tab_2')); ?>" class="regular-text" /></td></tr>
                    <tr><th>3. Shipping:</th><td><input type="text" name="mmc_ico_tab_3" value="<?php echo esc_attr(get_option('mmc_ico_tab_3')); ?>" class="regular-text" /></td></tr>
                    <tr><th>4. Garantía:</th><td><input type="text" name="mmc_ico_tab_4" value="<?php echo esc_attr(get_option('mmc_ico_tab_4')); ?>" class="regular-text" /></td></tr>
                </table>
            </div>

            <div id="tab-envios" class="mmc-tab-content" style="display:none;">
                <h3>Contenido de Envíos y Retornos</h3>
                <?php wp_editor(get_option('mmc_envio_texto_libre'), 'mmc_envio_texto_libre', array('textarea_rows'=>12, 'media_buttons'=>false)); ?>
            </div>

            <div id="tab-garantia" class="mmc-tab-content" style="display:none;">
                <h3>Dashboard de Garantía</h3>
                <table class="form-table">
                    <tr><th>Escudo (Icono Main):</th><td><input type="text" name="mmc_gar_main_ico" value="<?php echo esc_attr(get_option('mmc_gar_main_ico')); ?>" class="regular-text" /></td></tr>
                    <tr><th>Título:</th><td><input type="text" name="mmc_gar_titulo" value="<?php echo esc_attr(get_option('mmc_gar_titulo')); ?>" class="regular-text" /></td></tr>
                    <tr><th>Subtexto:</th><td><textarea name="mmc_gar_subtexto" style="width:100%"><?php echo esc_textarea(get_option('mmc_gar_subtexto')); ?></textarea></td></tr>
                </table>
                <h3>Items Inferiores (3 bloques)</h3>
                <div style="display:flex; gap:10px;">
                    <?php for($j=1;$j<=3;$j++): ?>
                    <div style="flex:1; background:#f1f1f1; padding:10px;">
                        <strong>Bloque #<?php echo $j; ?></strong>
                        <input type="text" name="mmc_gar_item_<?php echo $j; ?>_ico" value="<?php echo esc_attr(get_option("mmc_gar_item_{$j}_ico")); ?>" placeholder="URL Icono" style="width:100%; margin:5px 0;" />
                        <input type="text" name="mmc_gar_item_<?php echo $j; ?>_txt" value="<?php echo esc_attr(get_option("mmc_gar_item_{$j}_txt")); ?>" placeholder="Texto" style="width:100%;" />
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div id="tab-barra-galeria" class="mmc-tab-content" style="display:none;">
                <h3>Barra de Beneficios (Por Categoría)</h3>
                <p>Configura la información para la píldora gris debajo de la galería. <strong>Si el Título está vacío, no se mostrará.</strong></p>

                <style>
                    .mmc-accordion { background-color: #f8f9fa; color: #2271b1; cursor: pointer; padding: 12px 15px; width: 100%; border: 1px solid #c3c4c7; text-align: left; outline: none; font-size: 14px; font-weight: 600; margin-top: 5px; display: flex; justify-content: space-between; align-items: center; border-radius: 3px; }
                    .mmc-panel-cat { padding: 15px; display: none; background-color: #fff; border: 1px solid #c3c4c7; border-top: none; margin-bottom: 10px; }
                    .mmc-activo-badge { background: #d1e7dd; color: #0f5132; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: normal; }
                </style>

                <?php
                $categorias_barra = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => false));
                $valores_barra = get_option('mmc_ben_por_categoria', array());

                foreach($categorias_barra as $cat): 
                    $slug = $cat->slug; 
                    $val = isset($valores_barra[$slug]) ? $valores_barra[$slug] : array();
                    $titulo = isset($val['titulo']) ? $val['titulo'] : '';
                    $badge = !empty($titulo) ? '<span class="mmc-activo-badge">Activo</span>' : '';
                ?>
                    <button type="button" class="mmc-accordion"><?php echo esc_html($cat->name); ?> <?php echo $badge; ?></button>
                    <div class="mmc-panel-cat">
                        <table class="form-table">
                            <tr><th>Título Principal (Azul):</th><td><input type="text" name="mmc_ben_por_categoria[<?php echo $slug; ?>][titulo]" value="<?php echo esc_attr($titulo); ?>" class="regular-text" placeholder="Ej: Including:"></td></tr>
                            <?php for($i=1; $i<=4; $i++): 
                                $ico = $val['ico_'.$i] ?? ''; $txt = $val['txt_'.$i] ?? '';
                            ?>
                            <tr>
                                <th>Beneficio <?php echo $i; ?>:</th>
                                <td>
                                    <input type="text" name="mmc_ben_por_categoria[<?php echo $slug; ?>][ico_<?php echo $i; ?>]" value="<?php echo esc_attr($ico); ?>" placeholder="URL Icono" style="width:30%">
                                    <input type="text" name="mmc_ben_por_categoria[<?php echo $slug; ?>][txt_<?php echo $i; ?>]" value="<?php echo esc_attr($txt); ?>" placeholder="Texto" style="width:65%">
                                </td>
                            </tr>
                            <?php endfor; ?>
                        </table>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="margin-top:20px;"><?php submit_button(); ?></div>
        </form>
    </div>

    <script>
    jQuery(document).ready(function($){
        // Manejo de Tabs
        $('.nav-tab').click(function(e){
            e.preventDefault();
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            $('.mmc-tab-content').hide();
            $($(this).attr('href')).show();
        });
        // Manejo de Acordeones
        $('.mmc-accordion').click(function(e){
            e.preventDefault();
            $(this).next('.mmc-panel-cat').slideToggle('fast');
        });
    });
    </script>
    <?php
}




function mmc_pildora_beneficios_directa($version = 'desktop') {
    global $product;
    if ( ! $product ) return;

    $config_categorias = get_option('mmc_ben_por_categoria');
    $terms = wp_get_post_terms( $product->get_id(), 'product_cat' );

    if ( empty($terms) || !$config_categorias ) return;

    $datos_a_mostrar = null;

    foreach ( $terms as $term ) {
        $slug = sanitize_title($term->slug);
        if ( isset($config_categorias[$slug]) && !empty($config_categorias[$slug]['titulo']) ) {
            $datos_a_mostrar = $config_categorias[$slug];
            break; 
        }
    }

    if ( ! $datos_a_mostrar ) return;

    if ($version === 'desktop'):
        // ====================================================
        // VERSIÓN DE ESCRITORIO (BARRA HORIZONTAL) -> SOLO 3
        // ====================================================
        echo '<div class="mmc-pildora-escritorio">';
        echo '<div class="mmc-contenedor-pildora-limitado">';
        echo '<div class="mmc-pildora-final-diseno">';
        echo '<b class="pildora-title">' . esc_html($datos_a_mostrar['titulo']) . '</b>';
        
        // BUCLE: Máximo 3 elementos para que no se apriete
        for($i=1; $i<=3; $i++){
            $t = $datos_a_mostrar["txt_$i"] ?? ''; 
            $ico = $datos_a_mostrar["ico_$i"] ?? '';
            if($t){
                echo '<div class="pildora-item">';
                if($ico) echo '<img src="'.esc_url($ico).'">';
                echo '<span>'.esc_html($t).'</span>';
                echo '</div>';
            }
        }
        echo '</div></div></div>';
    else:
        // ====================================================
        // VERSIÓN MÓVIL (CUADRÍCULA 2x2) -> MUESTRA LOS 4
        // ====================================================
        echo '<div class="mmc-cuadricula-movil">';
        echo '<h3 class="mmc-titulo-cuadricula">' . esc_html($datos_a_mostrar['titulo']) . '</h3>';
        echo '<div class="mmc-grid-beneficios">';
        
        // BUCLE: Máximo 4 elementos para formar el cuadrado
        for($i=1; $i<=4; $i++){
            $t = $datos_a_mostrar["txt_$i"] ?? ''; 
            $ico = $datos_a_mostrar["ico_$i"] ?? '';
            if($t){
                echo '<div class="mmc-grid-item">';
                if($ico) echo '<img src="'.esc_url($ico).'">';
                echo '<span class="mmc-grid-texto">'.esc_html($t).'</span>';
                echo '</div>';
            }
        }
        echo '</div></div>';
    endif;
}









/* ========================================================
   4. FRONTEND: RENDER DE TODO
   ======================================================== */

// BANNER + BENEFICIOS
add_action('woocommerce_single_product_summary', 'mmc_inyectar_banner_y_beneficios', 25);
function mmc_inyectar_banner_y_beneficios() {
    global $product;
    $cats = get_option('mmc_categorias_activas', array());
    if(empty($cats)) return;
    $mostrar = false;
    foreach($cats as $c){ if(has_term($c, 'product_cat', $product->get_id())){ $mostrar = true; break; } }
    if(!$mostrar) return;

    $texto = get_option('mmc_texto_cupon'); $codigo = get_option('mmc_codigo_cupon');
    if($texto) echo '<div class="mmc-caja-cupon">⚡ '.esc_html($texto).' <span>'.esc_html($codigo).'</span></div>';

    echo '<ul class="mmc-lista-beneficios">';
    for($i=1;$i<=3;$i++){
        $t = get_option("mmc_ben_{$i}_txt"); $ico = get_option("mmc_ben_{$i}_ico");
        if($t){ echo '<li>' . ($ico ? '<img src="'.esc_url($ico).'" style="width:16px; margin-right:8px; vertical-align:middle;">' : '<span>✓</span> ') . esc_html($t) . '</li>'; }
    }
    echo '</ul>';
}

// PESTAÑAS
add_filter( 'woocommerce_product_tabs', 'mmc_personalizar_pestanas_producto', 98 );
function mmc_personalizar_pestanas_producto( $tabs ) {
    $icos = array(get_option('mmc_ico_tab_1'), get_option('mmc_ico_tab_2'), get_option('mmc_ico_tab_3'), get_option('mmc_ico_tab_4'));
    if ( isset( $tabs['description'] ) ) {
        $tabs['description']['title'] = ($icos[0] ? '<img src="'.esc_url($icos[0]).'" style="width:18px; margin-right:8px; vertical-align:middle;">' : '') . 'About the frame';
        $tabs['description']['callback'] = 'mmc_tab_about_frame_content';
    }
    $tabs['sizing_fit'] = array('title' => ($icos[1] ? '<img src="'.esc_url($icos[1]).'" style="width:18px; margin-right:8px; vertical-align:middle;">' : '') . 'Sizing & Fit', 'priority' => 20, 'callback' => 'mmc_tab_sizing_fit_content');
    $tabs['shipping_returns'] = array('title' => ($icos[2] ? '<img src="'.esc_url($icos[2]).'" style="width:18px; margin-right:8px; vertical-align:middle;">' : '') . 'Shipping & Returns', 'priority' => 30, 'callback' => 'mmc_tab_shipping_returns_content');
    $tabs['garantia'] = array('title' => ($icos[3] ? '<img src="'.esc_url($icos[3]).'" style="width:18px; margin-right:8px; vertical-align:middle;">' : '') . 'Garantía', 'priority' => 40, 'callback' => 'mmc_tab_garantia_content');
    unset( $tabs['additional_information'] ); return $tabs;
}

function mmc_tab_about_frame_content() {
    global $product;
    echo '<div class="mmc-about-frame-container"><div class="mmc-about-frame-desc"><h3 class="mmc-about-title">'.$product->get_name().'</h3>';
    the_content(); echo '</div><div class="mmc-about-frame-attrs">';
    if($product->has_attributes()){ wc_display_product_attributes($product); } echo '</div></div>';
}

function mmc_tab_sizing_fit_content() {

    global $product; $img = wp_get_attachment_image_url($product->get_image_id(), 'large');

    $icons_base = get_stylesheet_directory_uri() . '/assets/images/medidas/';

    $measures = array('pa_talla'=>'Lens Width','pa_alto'=>'Lens Height','pa_puente'=>'Bridge','pa_largo'=>'Temple');

    echo '<div class="mmc-sizing-tab-container"><h4 class="mmc-sizing-title">Frame Measurements:</h4><div class="mmc-sizing-layout"><div class="mmc-sizing-image-container"><img src="'.esc_url($img).'"></div><div class="mmc-sizing-grid-container"><div class="mmc-sizing-grid">';

    foreach($measures as $tax => $lab){ $v = $product->get_attribute($tax) ?: '--'; echo '<div class="mmc-sizing-box"><div class="mmc-sizing-header">'.$lab.'</div><div class="mmc-sizing-body"><img src="'.$icons_base.str_replace('pa_','',$tax).'.svg" class="mmc-sizing-icon"><span>'.$v.'</span></div></div>'; }

    echo '</div><p class="mmc-sizing-help">Need help? <a href="#">Find My Size</a></p></div></div></div>';

}

function mmc_tab_shipping_returns_content() {
    echo '<div class="mmc-shipping-full-width">' . apply_filters('the_content', get_option('mmc_envio_texto_libre')) . '</div>';
}

function mmc_tab_garantia_content() {
    $m_ico = get_option('mmc_gar_main_ico'); $t = get_option('mmc_gar_titulo'); $st = get_option('mmc_gar_subtexto');
    echo '<div class="mmc-garantia-dashboard">';
    if($m_ico) echo '<div class="mmc-gar-main-icon"><img src="'.esc_url($m_ico).'"></div>';
    echo '<h2 class="mmc-gar-h2">'.esc_html($t).'</h2><p class="mmc-gar-p">'.esc_html($st).'</p><div class="mmc-gar-grid">';
    for($j=1;$j<=3;$j++){ $ico = get_option("mmc_gar_item_{$j}_ico"); $txt = get_option("mmc_gar_item_{$j}_txt");
        echo '<div class="mmc-gar-item">'.($ico ? '<img src="'.esc_url($ico).'" class="mmc-gar-small-ico">' : '').'<span>'.esc_html($txt).'</span></div>';
    }
    echo '</div></div>';
}