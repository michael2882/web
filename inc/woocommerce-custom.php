<?php
/**
 * MEGA-PANEL DE FILTROS PREMIUM: V15 (Gender Icons + Admin Accordion)
 */

add_action('admin_enqueue_scripts', 'mmc_admin_scripts_filtros');
function mmc_admin_scripts_filtros($hook) {
    if ($hook != 'toplevel_page_mmc-config-filtros') return;
    wp_enqueue_media(); 
    wp_enqueue_style('wp-color-picker'); 
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_script('jquery-ui-sortable');
}

add_action('admin_menu', 'mmc_crear_menu_filtros_admin');
function mmc_crear_menu_filtros_admin() {
    add_menu_page('Filtros Premium', 'Filtros Premium', 'manage_options', 'mmc-config-filtros', 'mmc_render_panel_admin', 'dashicons-filter', 56);
}

function mmc_get_numeric_min_max($taxonomy) {
    if ( ! taxonomy_exists($taxonomy) ) return array('min' => 10, 'max' => 150);
    $terms = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => false));
    if (empty($terms) || is_wp_error($terms)) return array('min' => 10, 'max' => 150);
    $numbers = array();
    foreach ($terms as $term) {
        $num = preg_replace('/[^0-9.]/', '', $term->name);
        if (is_numeric($num)) $numbers[] = floatval($num);
    }
    return (empty($numbers)) ? array('min' => 10, 'max' => 150) : array('min' => min($numbers), 'max' => max($numbers));
}

function mmc_render_panel_admin() {
    if ( isset($_POST['mmc_guardar_filtros']) ) {
        update_option('mmc_filtros_activos', $_POST['mmc_filtros_activos'] ?? array());
        if(isset($_POST['term_color'])) { foreach($_POST['term_color'] as $id => $col) update_term_meta($id, 'mmc_color_hex', sanitize_hex_color($col)); }
        if(isset($_POST['term_img'])) { foreach($_POST['term_img'] as $id => $url) update_term_meta($id, 'mmc_img_url', esc_url_raw($url)); }
        if(isset($_POST['cat_img'])) { foreach($_POST['cat_img'] as $id => $url) update_term_meta($id, 'mmc_img_url', esc_url_raw($url)); }
        echo '<div class="notice notice-success is-dismissible"><p>Configuración guardada correctamente.</p></div>';
    }

    $filtros_guardados = get_option('mmc_filtros_activos', array());
    if(!is_array($filtros_guardados)) $filtros_guardados = array();

    $atributos_woo = wc_get_attribute_taxonomies();
    $lista_disponible = array(
        'promociones' => 'Promociones y Ofertas',
        'precio'      => 'Precio',
        'medidas_grupo' => 'Talla General'
    );
    foreach ($atributos_woo as $attr) {
        $lista_disponible['pa_' . $attr->attribute_name] = $attr->attribute_label;
    }

    $orden_final = array_unique(array_merge($filtros_guardados, array_keys($lista_disponible)));
    $slugs_configurables = array('promociones', 'pa_color', 'pa_forma', 'pa_marca', 'pa_tipo_marco', 'pa_talla', 'pa_ancho', 'pa_alto', 'pa_puente', 'pa_largo');
    ?>
    <style>
        .mmc-item { background:#fff; border:1px solid #ccd0d4; margin-bottom:8px; max-width:850px; border-radius:4px; overflow:hidden; }
        .mmc-item-header { padding:12px 15px; cursor:move; display:flex; align-items:center; justify-content:space-between; background:#f9f9f9; border-bottom:1px solid #eee; }
        .mmc-item-header:hover { background:#f0f0f0; }
        .mmc-item-title { font-weight:bold; display:flex; align-items:center; gap:10px; flex-grow:1; }
        .mmc-toggle-arrow { cursor:pointer; color:#777; transition:0.2s; padding:5px; }
        .mmc-toggle-arrow.open { transform: rotate(180deg); }
        .mmc-item-settings { display:none; padding:20px; background:#fff; border-top:1px solid #eee; }
        .mmc-term-row { display:flex; align-items:center; gap:15px; margin-bottom:10px; padding-bottom:10px; border-bottom:1px solid #f9f9f9; font-size:12px; }
        .mmc-term-name { width:120px; font-weight:600; }
    </style>

    <div class="wrap">
        <h1>Organizar y Editar Filtros</h1>
        <p>Arrastra para ordenar. Usa la flecha para editar imágenes o colores de cada filtro.</p>
        <form method="POST">
            <div id="mmc-sortable-container">
                <?php foreach ($orden_final as $slug): 
                    if(!isset($lista_disponible[$slug])) continue;
                    $checked = in_array($slug, $filtros_guardados) ? 'checked' : '';
                    $es_configurable = in_array($slug, $slugs_configurables);
                    ?>
                    <div class="mmc-item">
                        <div class="mmc-item-header">
                            <div class="mmc-item-title">
                                <span class="dashicons dashicons-menu" style="color:#ccc;"></span>
                                <input type="checkbox" name="mmc_filtros_activos[]" value="<?php echo $slug; ?>" <?php echo $checked; ?>> 
                                <?php echo $lista_disponible[$slug]; ?>
                            </div>
                            <?php if($es_configurable): ?>
                                <span class="dashicons dashicons-arrow-down-alt2 mmc-toggle-arrow"></span>
                            <?php else: ?>
                                <span style="font-size:10px; color:#999; font-weight:normal;">(Sin ajustes extras)</span>
                            <?php endif; ?>
                        </div>

                        <?php if($es_configurable): ?>
                        <div class="mmc-item-settings">
                            <?php if($slug === 'promociones'): 
                                $cat_promo = get_term_by('slug', 'promociones', 'product_cat');
                                if($cat_promo):
                                    $ofertas = get_terms(array('taxonomy'=>'product_cat', 'parent'=>$cat_promo->term_id, 'hide_empty'=>false));
                                    foreach($ofertas as $of):
                                        $img = get_term_meta($of->term_id, 'mmc_img_url', true); ?>
                                        <div class="mmc-term-row">
                                            <span class="mmc-term-name"><?php echo $of->name; ?></span>
                                            <input type="text" id="cat_<?php echo $of->term_id; ?>" name="cat_img[<?php echo $of->term_id; ?>]" value="<?php echo $img; ?>" placeholder="URL Icono">
                                            <button type="button" class="button mmc-upload-btn" data-target="cat_<?php echo $of->term_id; ?>">Subir</button>
                                        </div>
                                    <?php endforeach;
                                else: echo '<p style="color:red; font-size:11px;">Crea la categoría "Promociones" para configurar iconos.</p>'; endif; ?>

                            <?php else: 
                                $terms = get_terms(array('taxonomy' => $slug, 'hide_empty' => false));
                                if(!is_wp_error($terms)):
                                    foreach($terms as $t): ?>
                                        <div class="mmc-term-row">
                                            <span class="mmc-term-name"><?php echo $t->name; ?></span>
                                            <?php if($slug === 'pa_color'): $c = get_term_meta($t->term_id, 'mmc_color_hex', true); ?>
                                                <input type="text" name="term_color[<?php echo $t->term_id; ?>]" value="<?php echo $c; ?>" class="mmc-color-field"> 
                                            <?php endif; ?>
                                            <?php $img = get_term_meta($t->term_id, 'mmc_img_url', true); ?>
                                            <input type="text" id="img_<?php echo $t->term_id; ?>" name="term_img[<?php echo $t->term_id; ?>]" value="<?php echo $img; ?>" placeholder="URL Imagen/Icono"> 
                                            <button type="button" class="button mmc-upload-btn" data-target="img_<?php echo $t->term_id; ?>">Subir</button>
                                        </div>
                                    <?php endforeach;
                                endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="margin-top:20px;"><input type="submit" name="mmc_guardar_filtros" class="button button-primary button-large" value="Guardar Todo el Panel"></div>
        </form>
    </div>

    <script>
    jQuery(document).ready(function($){
        $("#mmc-sortable-container").sortable({ handle: '.mmc-item-header', axis: 'y' });
        $('.mmc-toggle-arrow').click(function(){
            $(this).toggleClass('open');
            $(this).closest('.mmc-item').find('.mmc-item-settings').slideToggle(200);
        });
        $('.mmc-color-field').wpColorPicker();
        var frame; $('.mmc-upload-btn').click(function(e){
            e.preventDefault(); var target = $('#'+$(this).data('target'));
            if(frame){ frame.open(); return; }
            frame = wp.media({ title: 'Elegir Imagen', button: {text: 'Usar'}, multiple: false });
            frame.on('select', function(){ target.val(frame.state().get('selection').first().toJSON().url); });
            frame.open();
        });
    });
    </script>
    <?php
}

add_shortcode( 'filtros_premium_optica', 'mmc_render_filtros_frontend' );
function mmc_render_filtros_frontend() {
    ob_start(); $shop_url = get_permalink(wc_get_page_id('shop'));
    $filtros_guardados = get_option('mmc_filtros_activos', array());
    if (empty($filtros_guardados) || !is_array($filtros_guardados)) return '';
    wp_enqueue_script('jquery-ui-slider');

    echo '<div class="mmc-premium-filters-container">';
    ?>
    <div class="mmc-gender-tabs">
        <a href="/tienda/" class="gender-btn active">TODO
        </a>
        <a href="/categoria-producto/mujer/" class="gender-btn"> MUJER
        </a>
        <a href="/categoria-producto/hombre/" class="gender-btn">HOMBRE
        </a>
        <a href="/categoria-producto/ninos/" class="gender-btn">NIÑOS
        </a>
        <button type="button" id="mmc-open-mobile-filters" class="gender-btn mmc-mobile-trigger">
            <img src="https://opticaartevisual.com/wp-content/uploads/2026/02/filter_7420963-1.png" class="gender-icon"> FILTROS
        </button>
    </div>
    <div class="mmc-filter-bar"><div class="mmc-pills-row">
<?php
    foreach ($filtros_guardados as $slug) {
        if ($slug === 'precio') echo '<button class="pill-btn" data-target="precio">Precio</button>';
        elseif ($slug === 'promociones') echo '<button class="pill-btn" data-target="promociones">Ofertas</button>';
        elseif ($slug === 'medidas_grupo') echo '<button class="pill-btn" data-target="medidas_grupo">Tallas</button>';
        else echo '<button class="pill-btn" data-target="'.str_replace('pa_', '', $slug).'">'.wc_attribute_label($slug).'</button>';
    }
    echo '</div><div class="mmc-dropdown-panels">';

    foreach ($filtros_guardados as $slug) {
        if ($slug === 'precio') {
            global $wpdb; $max = $wpdb->get_var("SELECT MAX(max_price) FROM {$wpdb->wc_product_meta_lookup}") ?: 1000;
            $cur_min = $_GET['min_price'] ?? 0; $cur_max = $_GET['max_price'] ?? $max; $sym = get_woocommerce_currency_symbol();
            // TITULO "Precio" ELIMINADO
            echo '<div id="panel-precio" class="filter-panel"><div class="mmc-slider-container" style="max-width:350px;"><div class="mmc-slider-header"><span>Rango de Precio</span></div><div id="mmc-price-slider-ui" class="mmc-tech-slider-ui" data-type="price" data-symbol="'.$sym.'" data-min="0" data-max="'.$max.'" data-cur-min="'.$cur_min.'" data-cur-max="'.$cur_max.'"></div><div class="mmc-slider-labels"><span class="mmc-lbl-min">'.$sym.' '.$cur_min.'</span><span class="mmc-lbl-max">'.$sym.' '.$cur_max.'</span></div></div><button type="button" id="mmc-apply-price-btn" class="mmc-apply-btn">Aplicar</button></div>';
            continue;
        }
        if ($slug === 'promociones') {
            // TITULO "Ofertas Vigentes" ELIMINADO
            echo '<div id="panel-promociones" class="filter-panel"><div class="shapes-grid">';
            $cat_promo = get_term_by('slug', 'promociones', 'product_cat');
            if($cat_promo){
                $ofertas = get_terms(array('taxonomy'=>'product_cat', 'parent'=>$cat_promo->term_id, 'hide_empty'=>false));
                foreach($ofertas as $of){
                    $link = get_term_link($of); $img = get_term_meta($of->term_id, 'mmc_img_url', true);
                    echo '<a href="'.$link.'" class="shape-box">';
                    if($img) echo '<div class="shape-icon" style="background-image:url('.$img.');"></div>';
                    echo '<span class="shape-name">'.$of->name.' <small>('.$of->count.')</small></span></a>';
                }
            }
            echo '</div></div>';
            continue;
        }
        if ($slug === 'medidas_grupo') {
            echo '<div id="panel-medidas_grupo" class="filter-panel">';
            // TITULO "Talla General" ELIMINADO
            if(taxonomy_exists('pa_talla_general')){
                echo '<div class="shapes-grid mmc-talla-general-grid">';
                $ts = get_terms(array('taxonomy' => 'pa_talla_general', 'hide_empty' => false));
                foreach($ts as $t){
                    $act = (isset($_GET['filter_talla_general']) && strpos($_GET['filter_talla_general'], $t->slug) !== false) ? 'active' : '';
                    echo '<div class="shape-box mmc-checkbox-box '.$act.'" data-value="'.$t->slug.'"><span class="shape-name">'.$t->name.' <small>('.$t->count.')</small></span></div>';
                }
                echo '</div>';
            }
            $tecs = array('pa_talla'=>'Talla','pa_ancho'=>'Ancho','pa_alto'=>'Alto','pa_puente'=>'Puente','pa_largo'=>'Largo');
            echo '<div class="mmc-technical-sliders-grid">';
            foreach($tecs as $tax => $tit){
                $mm = mmc_get_numeric_min_max($tax); $tid = str_replace('pa_','',$tax);
                $c_min = $_GET['min_'.$tid] ?? $mm['min']; $c_max = $_GET['max_'.$tid] ?? $mm['max'];
                $terms_icon = get_terms(array('taxonomy'=>$tax,'number'=>1,'meta_key'=>'mmc_img_url','hide_empty'=>false));
                $icon = (!is_wp_error($terms_icon) && !empty($terms_icon)) ? get_term_meta($terms_icon[0]->term_id, 'mmc_img_url', true) : '';
                echo '<div class="mmc-slider-container"><div class="mmc-slider-header"><span>'.$tit.'</span>';
                if($icon) echo '<div class="mmc-header-icon" style="background-image:url('.$icon.');"></div>';
                echo '</div><div class="mmc-tech-slider-ui" data-type="measure" data-tax="'.$tid.'" data-min="'.$mm['min'].'" data-max="'.$mm['max'].'" data-cur-min="'.$c_min.'" data-cur-max="'.$c_max.'"></div><div class="mmc-slider-labels"><span class="mmc-lbl-min">'.$c_min.'mm</span><span class="mmc-lbl-max">'.$c_max.'mm</span></div></div>';
            }
            echo '</div><button type="button" id="mmc-apply-medidas-btn" class="mmc-apply-btn">Aplicar Filtros</button></div>';
            continue;
        }
        $tid = str_replace('pa_', '', $slug);
        // TITULO DE ATRIBUTOS ELIMINADO
        echo '<div id="panel-'.$tid.'" class="filter-panel"><div class="shapes-grid">';
        $ts = get_terms(array('taxonomy' => $slug, 'hide_empty' => false));
        if(!is_wp_error($ts)){
            foreach($ts as $t){
                $link = add_query_arg('filter_'.$tid, $t->slug, $shop_url);
                echo '<a href="'.$link.'" class="shape-box">';
                if(in_array($tid, array('forma','marca','tipo_marco'))){
                    $i = get_term_meta($t->term_id, 'mmc_img_url', true); if($i) echo '<div class="shape-icon" style="background-image:url('.$i.');"></div>';
                }
                if($tid === 'color'){
                    $c = get_term_meta($t->term_id, 'mmc_color_hex', true); $i = get_term_meta($t->term_id, 'mmc_img_url', true);
                    $bg = $i ? 'background-image:url('.$i.'); background-size:cover;' : ($c ? 'background-color:'.$c.';' : '');
                    echo '<span class="mmc-color-circle" style="'.$bg.' border:1px solid #ccc;"></span>';
                }
                echo '<span class="shape-name">'.$t->name.' <small>('.$t->count.')</small></span></a>';
            }
        }
        echo '</div></div>';
    }
    echo '</div></div></div>';
    return ob_get_clean();
}
add_action( 'woocommerce_before_shop_loop', function() { echo do_shortcode('[filtros_premium_optica]'); }, 15);