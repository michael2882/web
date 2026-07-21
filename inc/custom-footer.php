<?php
/* ==========================================================================
   1. REGISTRO DE VARIABLES PARA EL FOOTER PREMIUM
   ========================================================================== */
add_action('admin_menu', 'mmc_crear_menu_footer');
function mmc_crear_menu_footer() {
    $page = add_menu_page('Configuración Footer', 'Footer Premium', 'manage_options', 'mmc-footer-premium', 'mmc_renderizar_panel_footer', 'dashicons-layout', 58);
    add_action('admin_print_scripts-' . $page, 'mmc_footer_admin_scripts');
}

function mmc_footer_admin_scripts() { wp_enqueue_script('jquery-ui-sortable'); }

add_action('admin_init', 'mmc_registrar_ajustes_footer');
function mmc_registrar_ajustes_footer() {
    register_setting('mmc_footer_grupo', 'mmc_footer_col_order');
    for($i=1; $i<=4; $i++) {
        register_setting('mmc_footer_grupo', 'mmc_footer_c'.$i.'_title');
        register_setting('mmc_footer_grupo', 'mmc_footer_c'.$i.'_text');
    }
    register_setting('mmc_footer_grupo', 'mmc_footer_libro_img');
    register_setting('mmc_footer_grupo', 'mmc_footer_libro_url');
    register_setting('mmc_footer_grupo', 'mmc_footer_logo_img');
    register_setting('mmc_footer_grupo', 'mmc_footer_telefono');
    register_setting('mmc_footer_grupo', 'mmc_footer_email');
    register_setting('mmc_footer_grupo', 'mmc_footer_atencion');
    
    $redes = array('fb', 'ig', 'yt', 'tk');
    foreach($redes as $red) {
        register_setting('mmc_footer_grupo', 'mmc_footer_'.$red.'_img');
        register_setting('mmc_footer_grupo', 'mmc_footer_'.$red.'_url');
    }
}

/* ==========================================================================
   2. EL PANEL DE ADMINISTRACIÓN VISUAL (DRAG AND DROP)
   ========================================================================== */
function mmc_renderizar_panel_footer() {
    $current_order = get_option('mmc_footer_col_order', '1,2,3,4,5');
    $order_array = explode(',', $current_order);
    $col_names = ['1'=>'Col 1 (Viñetas)', '2'=>'Col 2 (Viñetas)', '3'=>'Col 3 (Viñetas)', '4'=>'Col 4 (Libro Reclamos)', '5'=>'Col 5 (Logo y RRSS)'];
    ?>
    <div class="wrap">
        <h1>Footer Premium (Arrastrar y Soltar)</h1>
        <div style="background: #fff; border: 1px solid #ccc; padding: 20px; margin: 20px 0; border-radius: 5px;">
            <h3 style="margin-top:0;">Orden de las Columnas en la Tienda</h3>
            <p>Arrastra los bloques de izquierda a derecha para cambiar el orden en que se mostrarán en tu página web.</p>
            <ul id="mmc-sorter" style="display:flex; gap:15px; margin:0; padding:0; list-style:none; flex-wrap:wrap;">
                <?php foreach($order_array as $id): ?>
                    <li data-id="<?php echo $id; ?>" style="background:#eef6ff; border:2px dashed #0B2572; padding:15px 20px; border-radius:5px; cursor:grab; font-weight:bold; color:#0B2572; user-select:none;">↕️ <?php echo $col_names[$id]; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <form method="post" action="options.php">
            <?php settings_fields('mmc_footer_grupo'); ?>
            <input type="hidden" name="mmc_footer_col_order" id="mmc_footer_col_order_input" value="<?php echo esc_attr($current_order); ?>">
            <table class="form-table">
                <?php for($i=1; $i<=3; $i++): ?>
                <tr style="background:#f9f9f9; border-top:2px solid #ccc;">
                    <th scope="row"><strong>Columna <?php echo $i; ?></strong><br>Título:</th>
                    <td><input type="text" name="mmc_footer_c<?php echo $i; ?>_title" value="<?php echo esc_attr(get_option('mmc_footer_c'.$i.'_title')); ?>" style="width:100%; max-width:400px;" placeholder="Ej: PRODUCTOS" /></td>
                </tr>
                <tr>
                    <th scope="row">Contenido (Viñetas):</th>
                    <td><?php wp_editor(get_option('mmc_footer_c'.$i.'_text'), 'mmc_footer_c'.$i.'_text', array('textarea_rows'=>4, 'teeny'=>true)); ?></td>
                </tr>
                <?php endfor; ?>
                
                <tr style="background:#f9f9f9; border-top:2px solid #ccc;">
                    <th scope="row"><strong>Columna 4</strong><br>Título:</th>
                    <td><input type="text" name="mmc_footer_c4_title" value="<?php echo esc_attr(get_option('mmc_footer_c4_title')); ?>" style="width:100%; max-width:400px;" placeholder="Ej: LEGAL" /></td>
                </tr>
                <tr>
                    <th scope="row">Contenido (Viñetas):</th>
                    <td><?php wp_editor(get_option('mmc_footer_c4_text'), 'mmc_footer_c4_text', array('textarea_rows'=>4, 'teeny'=>true)); ?></td>
                </tr>
                <tr style="background:#eef6ff;">
                    <th scope="row"><strong>Libro de Reclamaciones</strong><br><small>(Aparecerá en la Col 4)</small></th>
                    <td>
                        <input type="text" name="mmc_footer_libro_img" value="<?php echo esc_attr(get_option('mmc_footer_libro_img')); ?>" placeholder="URL Imagen del Libro" style="width:100%; max-width:400px; margin-bottom:5px;" /><br>
                        <input type="text" name="mmc_footer_libro_url" value="<?php echo esc_attr(get_option('mmc_footer_libro_url')); ?>" placeholder="Link del Libro" style="width:100%; max-width:400px;" />
                    </td>
                </tr>

                <tr style="background:#fcf8e3; border-top:2px solid #faebcc;">
                    <th scope="row"><strong>Columna 5 (Logo, Info y Redes)</strong></th>
                    <td>
                        <input type="text" name="mmc_footer_logo_img" value="<?php echo esc_attr(get_option('mmc_footer_logo_img')); ?>" placeholder="URL del Logo Principal" style="width:100%; max-width:400px; margin-bottom:10px;" /><br>
                        <input type="text" name="mmc_footer_telefono" value="<?php echo esc_attr(get_option('mmc_footer_telefono')); ?>" placeholder="Ej: (+51) 955 141 819" style="width:100%; max-width:400px; margin-bottom:10px;" /><br>
                        <input type="text" name="mmc_footer_email" value="<?php echo esc_attr(get_option('mmc_footer_email')); ?>" placeholder="Ej: info@leoptica.com" style="width:100%; max-width:400px; margin-bottom:10px;" /><br>
                        <textarea name="mmc_footer_atencion" placeholder="Ej: Lunes a sábado de 10 am - 7:30 pm" style="width:100%; max-width:400px; margin-bottom:15px;"><?php echo esc_textarea(get_option('mmc_footer_atencion')); ?></textarea>
                        <hr style="max-width:400px; margin-left:0;">
                        <strong>Logos de Redes Sociales</strong><br>
                        <?php 
                        $redes_nombres = array('fb'=>'Facebook', 'ig'=>'Instagram', 'yt'=>'YouTube', 'tk'=>'TikTok');
                        foreach($redes_nombres as $id => $nombre): 
                        ?>
                        <div style="margin-bottom:10px; margin-top:10px;">
                            <input type="text" name="mmc_footer_<?php echo $id; ?>_img" value="<?php echo esc_attr(get_option('mmc_footer_'.$id.'_img')); ?>" placeholder="URL Icono <?php echo $nombre; ?>" style="width:48%;" />
                            <input type="text" name="mmc_footer_<?php echo $id; ?>_url" value="<?php echo esc_attr(get_option('mmc_footer_'.$id.'_url')); ?>" placeholder="Link <?php echo $nombre; ?>" style="width:48%;" />
                        </div>
                        <?php endforeach; ?>
                    </td>
                </tr>
            </table>
            <?php submit_button('Guardar Cambios'); ?>
        </form>
    </div>
    <script>
    jQuery(document).ready(function($) {
        $('#mmc-sorter').sortable({
            update: function() {
                var new_order = [];
                $(this).children('li').each(function() { new_order.push($(this).data('id')); });
                $('#mmc_footer_col_order_input').val(new_order.join(','));
            }
        });
        $('#mmc-sorter li').on('mousedown', function(){ $(this).css('cursor', 'grabbing'); });
        $('#mmc-sorter li').on('mouseup', function(){ $(this).css('cursor', 'grab'); });
    });
    </script>
    <?php
}

/* ==========================================================================
   3. EL SHORTCODE FRONTEND (CON LÓGICA DE ACORDEÓN)
   ========================================================================== */
add_shortcode('mmc_custom_footer', 'mmc_mostrar_footer_frontend');
function mmc_mostrar_footer_frontend($atts) {
    $a = shortcode_atts( array('color' => 'negro'), $atts );
    $theme_class = ($a['color'] == 'blanco') ? 'mmc-footer-theme-blanco' : 'mmc-footer-theme-negro';
    
    $order = get_option('mmc_footer_col_order', '1,2,3,4,5');
    $order_arr = explode(',', $order);

    ob_start();
    ?>
    <div class="mmc-site-footer <?php echo esc_attr($theme_class); ?>">
        <div class="mmc-footer-grid-5">
            <?php 
            foreach($order_arr as $col_id):
                
                // COLUMNAS ACORDEÓN (1, 2, 3)
                if(in_array($col_id, ['1','2','3'])):
                    $title = get_option('mmc_footer_c'.$col_id.'_title');
                    $text = get_option('mmc_footer_c'.$col_id.'_text');
            ?>
                    <div class="mmc-footer-col mmc-accordion-col">
                        <?php if($title) echo '<h4 class="mmc-footer-title mmc-accordion-trigger"><span>' . esc_html($title) . '</span><span class="mmc-arrow">▼</span></h4>'; ?>
                        <div class="mmc-col-content mmc-accordion-content"><?php echo wpautop($text); ?></div>
                    </div>
            <?php 
                // COLUMNA ACORDEÓN 4 (Legal + Libro)
                elseif($col_id == '4'): 
                    $title4 = get_option('mmc_footer_c4_title');
                    $text4 = get_option('mmc_footer_c4_text');
            ?>
                    <div class="mmc-footer-col mmc-accordion-col">
                        <?php if($title4) echo '<h4 class="mmc-footer-title mmc-accordion-trigger"><span>' . esc_html($title4) . '</span><span class="mmc-arrow">▼</span></h4>'; ?>
                        <div class="mmc-col-content mmc-accordion-content">
                            <?php echo wpautop($text4); ?>
                            <?php 
                            $libro_img = get_option('mmc_footer_libro_img');
                            $libro_url = get_option('mmc_footer_libro_url');
                            if($libro_img && $libro_url): ?>
                                <a href="<?php echo esc_url($libro_url); ?>" target="_blank" class="mmc-libro-inline">
                                    <img src="<?php echo esc_url($libro_img); ?>" alt="Libro de Reclamaciones">
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
            <?php 
                // COLUMNA 5 (ESTÁTICA: Logo + Info + RRSS)
                elseif($col_id == '5'): 
            ?>
                    <div class="mmc-footer-col mmc-footer-col-contact">
                        <?php if($logo = get_option('mmc_footer_logo_img')): ?>
                            <img src="<?php echo esc_url($logo); ?>" alt="Logo Footer" class="mmc-footer-main-logo">
                        <?php endif; ?>

                        <?php if($tel = get_option('mmc_footer_telefono')): ?>
                            <p class="mmc-contact-info"><a href="tel:<?php echo esc_attr($tel); ?>"><?php echo esc_html($tel); ?></a></p>
                        <?php endif; ?>

                        <?php if($email = get_option('mmc_footer_email')): ?>
                            <p class="mmc-contact-info"><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></p>
                        <?php endif; ?>

                        <?php if($atencion = get_option('mmc_footer_atencion')): ?>
                            <div class="mmc-atencion-block">
                                <p><?php echo nl2br(esc_html($atencion)); ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="mmc-social-block">
                            <span class="mmc-social-title">SÍGUENOS</span>
                            <div class="mmc-social-icons">
                                <?php 
                                $redes = array('fb', 'ig', 'yt', 'tk');
                                foreach($redes as $red):
                                    $img = get_option('mmc_footer_'.$red.'_img');
                                    $url = get_option('mmc_footer_'.$red.'_url');
                                    if($img && $url): ?>
                                        <a href="<?php echo esc_url($url); ?>" target="_blank" class="mmc-social-link">
                                            <img src="<?php echo esc_url($img); ?>" alt="Red Social">
                                        </a>
                                <?php endif; endforeach; ?>
                            </div>
                        </div>
                    </div>
            <?php 
                endif; 
            endforeach; 
            ?>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        if ($(window).width() <= 768) {
            $('.mmc-accordion-trigger').on('click', function() {
                $(this).toggleClass('active');
                $(this).next('.mmc-accordion-content').slideToggle(300);
            });
        }
    });
    </script>
    <?php
    return ob_get_clean();
}