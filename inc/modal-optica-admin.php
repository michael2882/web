<?php
if ( ! defined('ABSPATH') ) exit;

add_action('admin_menu', 'mmc_modal_optica_menu');
function mmc_modal_optica_menu() {
    add_menu_page('Flujo de Lentes', 'Flujo de Lentes', 'manage_options', 'mmc_flujo_lentes', 'mmc_modal_optica_admin_page', 'dashicons-visibility', 56);
}



// Base compartida por Protección y Sub-Protección (mismos campos, sin paso_previo/sub_protecciones)
function mmc_sanitize_nivel_base($p) {
    return [
        'nombre'           => sanitize_text_field($p['nombre'] ?? ''),
        'precio_antes'     => floatval($p['precio_antes'] ?? 0),
        'precio_ahora'     => floatval($p['precio_ahora'] ?? 0),
        'imagen_url'       => esc_url_raw($p['imagen_url'] ?? ''),
        'descripcion'      => wp_kses_post($p['descripcion'] ?? ''),
        'tooltip_texto'    => sanitize_textarea_field($p['tooltip_texto'] ?? ''),
        'tooltip_img'      => esc_url_raw($p['tooltip_img'] ?? ''),
        'tags'             => mmc_sanitize_tags($p['tags'] ?? []),
        'es_fotocromatico' => !empty($p['es_fotocromatico']) ? 1 : 0,
        'colores'          => mmc_sanitize_colores($p['colores'] ?? []),
        'indices'          => mmc_sanitize_indices($p['indices'] ?? []),
    ];
}


function mmc_sanitize_tags($raw_tags) {
    $out = [];
    foreach (($raw_tags ?? []) as $t) {
        if (!empty($t['texto'])) {
            $out[] = [
                'texto' => sanitize_text_field($t['texto']),
                'color' => sanitize_text_field($t['color'] ?? 'mmc-tag-azul'),
            ];
        }
    }
    return $out;
}

function mmc_sanitize_indices($raw_indices) {
    $out = [];
    foreach (($raw_indices ?? []) as $idx) {
        if (empty($idx['nombre'])) continue;
        $out[] = [
            'nombre'                    => sanitize_text_field($idx['nombre']),
            'precio_antes'              => floatval($idx['precio_antes'] ?? 0),
            'precio_ahora'              => floatval($idx['precio_ahora'] ?? 0),
            'descripcion'               => sanitize_text_field($idx['descripcion'] ?? ''),
            'imagen_url'                => esc_url_raw($idx['imagen_url'] ?? ''),
            'titulo_svg'                => esc_url_raw($idx['titulo_svg'] ?? ''),
            'titulo_img'                => esc_url_raw($idx['titulo_img'] ?? ''),
            'tooltip_texto'             => sanitize_textarea_field($idx['tooltip_texto'] ?? ''),
            'tooltip_img'               => esc_url_raw($idx['tooltip_img'] ?? ''),
            'tags'                      => mmc_sanitize_tags($idx['tags'] ?? []),
            'recubrimientos_ids'        => array_map('sanitize_text_field', (array)($idx['recubrimientos_ids'] ?? [])),
            'recubrimiento_recomendado' => sanitize_text_field($idx['recubrimiento_recomendado'] ?? ''),
        ];
    }
    return $out;
}

function mmc_sanitize_colores($raw_colores) {
    $out = [];
    foreach (($raw_colores ?? []) as $c) {
        if (empty($c['nombre'])) continue;
        $out[] = [
            'nombre'     => sanitize_text_field($c['nombre']),
            'tipo'       => (($c['tipo'] ?? 'solido') === 'degradado') ? 'degradado' : 'solido',
            'hex1'       => sanitize_hex_color($c['hex1'] ?? '') ?: '#cccccc',
            'hex2'       => sanitize_hex_color($c['hex2'] ?? '') ?: '',
            'imagen_url' => esc_url_raw($c['imagen_url'] ?? ''),
        ];
    }
    return $out;
}




// REEMPLAZAR POR:
function mmc_flujo_save() {
    if (!isset($_POST['mmc_flujo_save']) || !current_user_can('manage_options')) return false;

    // Ajustes generales del sidebar
    // REEMPLAZAR POR:
    update_option('mmc_ajustes_sidebar', [
        'envio_gratis_monto' => floatval($_POST['ajustes_sidebar']['envio_gratis_monto'] ?? 0),
        'whatsapp_url'       => esc_url_raw($_POST['ajustes_sidebar']['whatsapp_url'] ?? ''),
        'mascara_luna_url'   => esc_url_raw($_POST['ajustes_sidebar']['mascara_luna_url'] ?? ''),
    ]);

    // Paso 1

    // Paso 1
    $p1 = $_POST['flujo_paso1'] ?? [];
    $s1 = [];
    foreach ($p1 as $i => $op) {
        $s1[$i] = [
            'nombre'        => sanitize_text_field($op['nombre'] ?? ''),
            'descripcion'   => sanitize_text_field($op['descripcion'] ?? ''),
            'precio_antes'  => floatval($op['precio_antes'] ?? 0),
            'precio_ahora'  => floatval($op['precio_ahora'] ?? 0),
            'icono_url'     => esc_url_raw($op['icono_url'] ?? ''),
            'tooltip_texto' => sanitize_textarea_field($op['tooltip_texto'] ?? ''),
            'tooltip_img'   => esc_url_raw($op['tooltip_img'] ?? ''),
            'badge'         => sanitize_text_field($op['badge'] ?? ''),
        ];
    }
    update_option('mmc_flujo_paso1', $s1);

    // Progresivo
    $prog = $_POST['flujo_progresivo'] ?? [];
    $sp = [];
    foreach (['standard', 'office'] as $key) {
        $sp[$key] = [
            'titulo'      => sanitize_text_field($prog[$key]['titulo'] ?? ''),
            'desc'        => sanitize_textarea_field($prog[$key]['desc'] ?? ''),
            'precio'      => floatval($prog[$key]['precio'] ?? 0),
            'advertencia' => sanitize_text_field($prog[$key]['advertencia'] ?? ''),
            'icono_url'   => esc_url_raw($prog[$key]['icono_url'] ?? ''),
        ];
    }
    update_option('mmc_flujo_progresivo', $sp);

    // Prescripción habilitados
// REEMPLAZAR POR:
    // Prescripción: correo de recepción de recetas (reemplaza "Enviar después")
    update_option('mmc_flujo_prescripcion', [
        'correo_receta' => sanitize_email($_POST['prescripcion_correo'] ?? ''),
    ]);

// REEMPLAZAR POR:

// AGREGAR JUSTO ANTES DE ESA LÍNEA:
    // Recubrimientos globales (compartidos por todos los flujos/índices)
    $raw_rec = $_POST['recubrimientos'] ?? [];
    $saved_rec = [];
    foreach ($raw_rec as $r) {
        if (empty($r['nombre'])) continue;
        $id = sanitize_text_field($r['id'] ?? '');
        if (empty($id)) $id = 'rc_' . wp_generate_password(10, false);
        $saved_rec[] = [
            'id'            => $id,
            'nombre'        => sanitize_text_field($r['nombre']),
            'precio_antes'  => floatval($r['precio_antes'] ?? 0),
            'precio_ahora'  => floatval($r['precio_ahora'] ?? 0),
            'tooltip_texto' => sanitize_textarea_field($r['tooltip_texto'] ?? ''),
            'tooltip_img'   => esc_url_raw($r['tooltip_img'] ?? ''),
            'imagen_url'    => esc_url_raw($r['imagen_url'] ?? ''),
        ];
    }
    update_option('mmc_recubrimientos', $saved_rec);

    // Protecciones, Sub-Protecciones e Índices por flujo (estructura dinámica anidada)
    $flujos_protecciones = ['simple','cercana','progresivo','bifocal','sin_graduacion'];
    
    foreach ($flujos_protecciones as $flujo) {
        $raw = $_POST['protecciones'][$flujo] ?? [];
        $saved = [];
        foreach ($raw as $p) {
            if (empty($p['nombre'])) continue;

            $item = mmc_sanitize_nivel_base($p);
            $item['paso_previo'] = !empty($p['paso_previo']) ? 1 : 0;

            $subs = [];
            foreach (($p['sub_protecciones'] ?? []) as $sp) {
                if (empty($sp['nombre'])) continue;
                $subs[] = mmc_sanitize_nivel_base($sp);
            }
            $item['sub_protecciones'] = $subs;

            $saved[] = $item;
        }
        update_option('mmc_protecciones_' . $flujo, $saved);
    }

    return true;
}


// BUSCAR Y ELIMINAR: las funciones mmc_admin_render_indice_row() y mmc_admin_render_proteccion_card() completas del mensaje anterior.

// REEMPLAZAR POR TODO ESTE BLOQUE:

// BUSCAR Y REEMPLAZAR toda la función mmc_admin_render_indice_row() por:

function mmc_admin_render_indice_row($base_padre, $uid, $iid, $t = [], $colores_opts = [], $recubrimientos_globales = []) {
    $u = $uid . '_' . $iid;
    $base = $base_padre . "[indices][$iid]";
    ob_start(); ?>
    <div class="mmc-indice-row" data-iid="<?php echo esc_attr($iid); ?>">
        <div class="mmc-indice-row-header">
            <strong>Índice</strong>
            <button type="button" class="mmc-remove-indice" title="Eliminar índice">&times;</button>
        </div>
        <div class="mmc-grid-3">
            <div class="mmc-field">
                <label>Nombre (ej: 1.50)</label>
                <input type="text" name="<?php echo $base;?>[nombre]" value="<?php echo esc_attr($t['nombre'] ?? '');?>" placeholder="1.50">
            </div>
            <div class="mmc-field">
                <label>Precio antes (tachado)</label>
                <input type="number" step="0.01" min="0" name="<?php echo $base;?>[precio_antes]" value="<?php echo esc_attr($t['precio_antes'] ?? 0);?>">
            </div>
            <div class="mmc-field">
                <label>Precio adicional (0 = sin costo)</label>
                <input type="number" step="0.01" min="0" name="<?php echo $base;?>[precio_ahora]" value="<?php echo esc_attr($t['precio_ahora'] ?? 0);?>">
            </div>
        </div>
        <div class="mmc-field" style="margin-bottom:14px;">
            <label>Descripción corta</label>
            <input type="text" name="<?php echo $base;?>[descripcion]" value="<?php echo esc_attr($t['descripcion'] ?? '');?>" placeholder="Ej: Lentes para uso cotidiano">
        </div>
        <div class="mmc-grid-2">
            <div class="mmc-field">
                <label>Imagen del lente</label>
                <div class="mmc-img-row">
                    <input type="text" name="<?php echo $base;?>[imagen_url]" id="idx_img_<?php echo $u;?>" value="<?php echo esc_attr($t['imagen_url'] ?? '');?>" placeholder="URL">
                    <img class="mmc-img-preview <?php echo !empty($t['imagen_url'])?'visible':'';?>" src="<?php echo esc_url($t['imagen_url'] ?? '');?>" id="idx_img_prev_<?php echo $u;?>">
                    <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#idx_img_<?php echo $u;?>" data-preview="#idx_img_prev_<?php echo $u;?>">Subir</button>
                </div>
            </div>
            <div class="mmc-field">
                <label>Imagen tooltip</label>
                <div class="mmc-img-row">
                    <input type="text" name="<?php echo $base;?>[tooltip_img]" id="idx_tip_<?php echo $u;?>" value="<?php echo esc_attr($t['tooltip_img'] ?? '');?>" placeholder="URL">
                    <img class="mmc-img-preview <?php echo !empty($t['tooltip_img'])?'visible':'';?>" src="<?php echo esc_url($t['tooltip_img'] ?? '');?>" id="idx_tip_prev_<?php echo $u;?>">
                    <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#idx_tip_<?php echo $u;?>" data-preview="#idx_tip_prev_<?php echo $u;?>">Subir</button>
                </div>
            </div>
        </div>
        <div class="mmc-field" style="margin-bottom:0;">
            <label>Texto tooltip</label>
            <textarea name="<?php echo $base;?>[tooltip_texto]"><?php echo esc_textarea($t['tooltip_texto'] ?? '');?></textarea>
        </div>
        <hr class="mmc-divider">
        <p class="mmc-section-label">Título especial (logo SVG + imagen — para Transitions, opcional)</p>
        <div class="mmc-grid-2">
            <div class="mmc-field">
                <label>Logo SVG</label>
                <div class="mmc-img-row">
                    <input type="text" name="<?php echo $base;?>[titulo_svg]" id="idx_svg_<?php echo $u;?>" value="<?php echo esc_attr($t['titulo_svg'] ?? '');?>" placeholder="URL">
                    <img class="mmc-img-preview <?php echo !empty($t['titulo_svg'])?'visible':'';?>" src="<?php echo esc_url($t['titulo_svg'] ?? '');?>" id="idx_svg_prev_<?php echo $u;?>">
                    <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#idx_svg_<?php echo $u;?>" data-preview="#idx_svg_prev_<?php echo $u;?>">Subir</button>
                </div>
            </div>
            <div class="mmc-field">
                <label>Imagen secundaria (Photochromic)</label>
                <div class="mmc-img-row">
                    <input type="text" name="<?php echo $base;?>[titulo_img]" id="idx_timg_<?php echo $u;?>" value="<?php echo esc_attr($t['titulo_img'] ?? '');?>" placeholder="URL">
                    <img class="mmc-img-preview <?php echo !empty($t['titulo_img'])?'visible':'';?>" src="<?php echo esc_url($t['titulo_img'] ?? '');?>" id="idx_timg_prev_<?php echo $u;?>">
                    <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#idx_timg_<?php echo $u;?>" data-preview="#idx_timg_prev_<?php echo $u;?>">Subir</button>
                </div>
            </div>
        </div>

        <hr class="mmc-divider">
        <?php echo mmc_admin_render_tags_block($base, $t['tags'] ?? [], $colores_opts); ?>

        <?php echo mmc_admin_render_recubrimientos_checklist($base, $t, $recubrimientos_globales); ?>
    </div>
    <?php
    return ob_get_clean();
}

function mmc_admin_render_color_row($base, $uid, $cid, $c = []) {
    $u = $uid . '_' . $cid;
    $tipo = $c['tipo'] ?? 'solido';
    ob_start(); ?>
    <div class="mmc-color-row" data-cid="<?php echo esc_attr($cid); ?>">
        <button type="button" class="mmc-remove-color" title="Eliminar color">&times;</button>
        <div class="mmc-field">
            <label>Nombre del color</label>
            <input type="text" name="<?php echo $base;?>[colores][<?php echo $cid;?>][nombre]" value="<?php echo esc_attr($c['nombre'] ?? '');?>" placeholder="Ej: Gris">
        </div>
        <div class="mmc-field">
            <label>Tipo</label>
            <select class="mmc-color-tipo-select" name="<?php echo $base;?>[colores][<?php echo $cid;?>][tipo]">
                <option value="solido" <?php selected($tipo, 'solido'); ?>>Sólido</option>
                <option value="degradado" <?php selected($tipo, 'degradado'); ?>>Degradado</option>
            </select>
        </div>
        <div class="mmc-field">
            <label>Color 1</label>
            <input type="color" name="<?php echo $base;?>[colores][<?php echo $cid;?>][hex1]" value="<?php echo esc_attr($c['hex1'] ?? '#cccccc');?>">
        </div>
        <div class="mmc-field mmc-color-hex2" style="<?php echo ($tipo === 'degradado') ? '' : 'display:none;'; ?>">
            <label>Color 2 (degradado)</label>
            <input type="color" name="<?php echo $base;?>[colores][<?php echo $cid;?>][hex2]" value="<?php echo esc_attr($c['hex2'] ?? '#666666');?>">
        </div>
        <div class="mmc-field" style="flex:1.3;">
            <label>Imagen (opcional, reemplaza el color plano)</label>
            <div class="mmc-img-row">
                <input type="text" name="<?php echo $base;?>[colores][<?php echo $cid;?>][imagen_url]" id="col_img_<?php echo $u;?>" value="<?php echo esc_attr($c['imagen_url'] ?? '');?>" placeholder="URL">
                <img class="mmc-img-preview <?php echo !empty($c['imagen_url'])?'visible':'';?>" src="<?php echo esc_url($c['imagen_url'] ?? '');?>" id="col_img_prev_<?php echo $u;?>">
                <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#col_img_<?php echo $u;?>" data-preview="#col_img_prev_<?php echo $u;?>">Subir</button>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function mmc_admin_render_fotocromatico_block($base, $uid, $p) {
    $checked = !empty($p['es_fotocromatico']);
    ob_start(); ?>
    <div class="mmc-toggle-row mmc-foto-toggle-row">
        <input type="checkbox" class="mmc-foto-checkbox" id="foto_<?php echo $uid;?>" name="<?php echo $base;?>[es_fotocromatico]" value="1" <?php checked($checked, true); ?>>
        <label for="foto_<?php echo $uid;?>" style="flex:1;">
            <strong>Es fotocromático / tintado (mostrar selector de color)</strong>
            <span class="mmc-toggle-desc"> — Al seleccionarlo en el frontend, se despliega un selector de colores antes de continuar.</span>
        </label>
    </div>
    <div class="mmc-colores-wrap" style="<?php echo $checked ? '' : 'display:none;'; ?>">
        <p class="mmc-section-label">Colores disponibles</p>
        <div class="mmc-colores-rows">
            <?php
            $colores = $p['colores'] ?? [];
            foreach ($colores as $cid => $c) {
                echo mmc_admin_render_color_row($base, $uid, $cid, $c);
            }
            ?>
        </div>
        <button type="button" class="button mmc-add-color" style="margin-top:8px;">+ Agregar color</button>
    </div>
    <?php
    return ob_get_clean();
}

function mmc_admin_render_subproteccion_card($flujo, $pid, $sid, $sp, $colores_opts, $num, $recubrimientos_globales) {
    $base = "protecciones[$flujo][$pid][sub_protecciones][$sid]";
    $uid  = $flujo . '_' . $pid . '_sub_' . $sid;
    $tags = $sp['tags'] ?? [];
    while (count($tags) < 3) $tags[] = ['texto'=>'','color'=>'mmc-tag-azul'];
    ob_start(); ?>
    <div class="mmc-card mmc-subproteccion-card" data-flujo="<?php echo esc_attr($flujo);?>" data-pid="<?php echo esc_attr($pid);?>" data-sid="<?php echo esc_attr($sid);?>" data-icounter="0" data-ccounter="0">
        <div class="mmc-card-header">
            <div class="mmc-card-num" style="background:#7c3aed;"><?php echo $num; ?></div>
            <p class="mmc-card-title">Sub-Protección<?php if(!empty($sp['nombre'])): ?> — <span style="color:#2563eb;"><?php echo esc_html($sp['nombre']); ?></span><?php endif; ?></p>
            <button type="button" class="mmc-remove-subproteccion">Eliminar</button>
        </div>
        <div class="mmc-card-body">
            <div class="mmc-grid-3">
                <div class="mmc-field">
                    <label>Nombre</label>
                    <input type="text" name="<?php echo $base;?>[nombre]" value="<?php echo esc_attr($sp['nombre'] ?? '');?>" placeholder="Ej: Gris intenso">
                </div>
                <div class="mmc-field">
                    <label>Precio antes (tachado)</label>
                    <input type="number" step="0.01" min="0" name="<?php echo $base;?>[precio_antes]" value="<?php echo esc_attr($sp['precio_antes'] ?? 0);?>">
                </div>
                <div class="mmc-field">
                    <label>Precio adicional (0 = Gratis)</label>
                    <input type="number" step="0.01" min="0" name="<?php echo $base;?>[precio_ahora]" value="<?php echo esc_attr($sp['precio_ahora'] ?? 0);?>">
                </div>
            </div>
            <div class="mmc-grid-2">
                <div class="mmc-field">
                    <label>Imagen</label>
                    <div class="mmc-img-row">
                        <input type="text" name="<?php echo $base;?>[imagen_url]" id="sub_img_<?php echo $uid;?>" value="<?php echo esc_attr($sp['imagen_url'] ?? '');?>" placeholder="URL">
                        <img class="mmc-img-preview <?php echo !empty($sp['imagen_url'])?'visible':'';?>" src="<?php echo esc_url($sp['imagen_url'] ?? '');?>" id="sub_img_prev_<?php echo $uid;?>">
                        <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#sub_img_<?php echo $uid;?>" data-preview="#sub_img_prev_<?php echo $uid;?>">Subir</button>
                    </div>
                </div>
                <div class="mmc-field">
                    <label>Descripción</label>
                    <textarea name="<?php echo $base;?>[descripcion]"><?php echo esc_textarea($sp['descripcion'] ?? '');?></textarea>
                </div>
            </div>

            <?php echo mmc_admin_render_fotocromatico_block($base, $uid, $sp); ?>

// BUSCAR: 
            <hr class="mmc-divider">
            <?php echo mmc_admin_render_tags_block($base, $sp['tags'] ?? [], $colores_opts); ?>

            <hr class="mmc-divider">
            <p class="mmc-section-label">Tooltip (?)</p>
            <div class="mmc-grid-2">
                <div class="mmc-field">
                    <label>Texto</label>
                    <textarea name="<?php echo $base;?>[tooltip_texto]"><?php echo esc_textarea($sp['tooltip_texto'] ?? '');?></textarea>
                </div>
                <div class="mmc-field">
                    <label>Imagen</label>
                    <div class="mmc-img-row">
                        <input type="text" name="<?php echo $base;?>[tooltip_img]" id="sub_tip_<?php echo $uid;?>" value="<?php echo esc_attr($sp['tooltip_img'] ?? '');?>" placeholder="URL">
                        <img class="mmc-img-preview <?php echo !empty($sp['tooltip_img'])?'visible':'';?>" src="<?php echo esc_url($sp['tooltip_img'] ?? '');?>" id="sub_tip_prev_<?php echo $uid;?>">
                        <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#sub_tip_<?php echo $uid;?>" data-preview="#sub_tip_prev_<?php echo $uid;?>">Subir</button>
                    </div>
                </div>
            </div>

// REEMPLAZAR POR:
            <hr class="mmc-divider">
            <p class="mmc-section-label">Índices de esta sub-protección</p>
            <div class="mmc-indices-wrap">
                <?php foreach (($sp['indices'] ?? []) as $iid => $t) { echo mmc_admin_render_indice_row($base, $uid, $iid, $t, $colores_opts, $recubrimientos_globales); } ?>
            </div>
            <button type="button" class="button mmc-add-indice" style="margin-top:10px;">+ Agregar índice</button>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function mmc_admin_render_proteccion_card($flujo, $pid, $p, $colores_opts, $num, $recubrimientos_globales) {
    $base = "protecciones[$flujo][$pid]";
    $uid  = $flujo . '_' . $pid;
    $tags = $p['tags'] ?? [];
    while (count($tags) < 3) $tags[] = ['texto'=>'','color'=>'mmc-tag-azul'];
    $paso_previo = !empty($p['paso_previo']);
    ?>
    <div class="mmc-card mmc-proteccion-card" data-flujo="<?php echo esc_attr($flujo);?>" data-pid="<?php echo esc_attr($pid);?>" data-icounter="0" data-scounter="0" data-ccounter="0">
        <div class="mmc-card-header">
            <div class="mmc-card-num" style="background:#0891b2;"><?php echo $num; ?></div>
            <p class="mmc-card-title">Protección<?php if(!empty($p['nombre'])): ?> — <span style="color:#2563eb;"><?php echo esc_html($p['nombre']); ?></span><?php endif; ?></p>
            <button type="button" class="mmc-remove-proteccion">Eliminar protección</button>
        </div>
        <div class="mmc-card-body">
            <div class="mmc-grid-3">
                <div class="mmc-field">
                    <label>Nombre de la protección</label>
                    <input type="text" name="<?php echo $base;?>[nombre]" value="<?php echo esc_attr($p['nombre'] ?? '');?>" placeholder="Ej: Clear">
                </div>
                <div class="mmc-field">
                    <label>Precio antes (tachado)</label>
                    <input type="number" step="0.01" min="0" name="<?php echo $base;?>[precio_antes]" value="<?php echo esc_attr($p['precio_antes'] ?? 0);?>">
                </div>
                <div class="mmc-field">
                    <label>Precio adicional (0 = Gratis)</label>
                    <input type="number" step="0.01" min="0" name="<?php echo $base;?>[precio_ahora]" value="<?php echo esc_attr($p['precio_ahora'] ?? 0);?>">
                </div>
            </div>

            <div class="mmc-grid-2">
                <div class="mmc-field">
                    <label>Imagen del lente</label>
                    <div class="mmc-img-row">
                        <input type="text" name="<?php echo $base;?>[imagen_url]" id="prot_img_<?php echo $uid;?>" value="<?php echo esc_attr($p['imagen_url'] ?? '');?>" placeholder="URL de imagen">
                        <img class="mmc-img-preview <?php echo !empty($p['imagen_url'])?'visible':'';?>" src="<?php echo esc_url($p['imagen_url'] ?? '');?>" id="prot_img_prev_<?php echo $uid;?>">
                        <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#prot_img_<?php echo $uid;?>" data-preview="#prot_img_prev_<?php echo $uid;?>">Subir</button>
                    </div>
                </div>
                <div class="mmc-field">
                    <label>Descripción (admite &lt;strong&gt;)</label>
                    <textarea name="<?php echo $base;?>[descripcion]" placeholder="Ej: Protección contra luz azul..."><?php echo esc_textarea($p['descripcion'] ?? '');?></textarea>
                </div>
            </div>

            <div class="mmc-toggle-row mmc-paso-previo-row">
                <input type="checkbox" class="mmc-paso-previo-checkbox" id="paso_previo_<?php echo $uid;?>" name="<?php echo $base;?>[paso_previo]" value="1" <?php checked($paso_previo, true); ?>>
                <label for="paso_previo_<?php echo $uid;?>" style="flex:1;">
                    <strong>Tiene paso previo (Sub-Protección)</strong>
                    <span class="mmc-toggle-desc"> — Si se marca, se muestra una segunda pantalla de opciones (mismo diseño) antes de llegar a Índices.</span>
                </label>
            </div>

            <?php echo mmc_admin_render_fotocromatico_block($base, $uid, $p); ?>

            <hr class="mmc-divider">
            <?php echo mmc_admin_render_tags_block($base, $p['tags'] ?? [], $colores_opts); ?>

            <hr class="mmc-divider">
            <p class="mmc-section-label">Tooltip (?)</p>
            <div class="mmc-grid-2">
                <div class="mmc-field">
                    <label>Texto del tooltip</label>
                    <textarea name="<?php echo $base;?>[tooltip_texto]"><?php echo esc_textarea($p['tooltip_texto'] ?? '');?></textarea>
                </div>
                <div class="mmc-field">
                    <label>Imagen del tooltip</label>
                    <div class="mmc-img-row">
                        <input type="text" name="<?php echo $base;?>[tooltip_img]" id="prot_tip_<?php echo $uid;?>" value="<?php echo esc_attr($p['tooltip_img'] ?? '');?>" placeholder="URL">
                        <img class="mmc-img-preview <?php echo !empty($p['tooltip_img'])?'visible':'';?>" src="<?php echo esc_url($p['tooltip_img'] ?? '');?>" id="prot_tip_prev_<?php echo $uid;?>">
                        <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#prot_tip_<?php echo $uid;?>" data-preview="#prot_tip_prev_<?php echo $uid;?>">Subir</button>
                    </div>
                </div>
            </div>

            <!-- BLOQUE SUB-PROTECCIONES (visible solo si paso_previo está marcado) -->
            <div class="mmc-subprotecciones-wrap" style="<?php echo $paso_previo ? '' : 'display:none;'; ?>">
                <hr class="mmc-divider">
                <p class="mmc-section-label">Sub-Protecciones</p>
                <div class="mmc-subprotecciones-lista">
                    <?php
                    $num_sub = 1;
                    foreach (($p['sub_protecciones'] ?? []) as $sid => $sp) {
                        echo mmc_admin_render_subproteccion_card($flujo, $pid, $sid, $sp, $colores_opts, $num_sub, $recubrimientos_globales);
                        $num_sub++;
                    }
                    ?>
                </div>
                <button type="button" class="button mmc-add-subproteccion" style="margin:10px 0 20px;">+ Agregar sub-protección</button>
            </div>

            <!-- BLOQUE ÍNDICES (visible solo si NO tiene paso previo) -->
            <div class="mmc-indices-wrap-outer" style="<?php echo $paso_previo ? 'display:none;' : ''; ?>">
                <hr class="mmc-divider">
                <p class="mmc-section-label">Índices de esta protección</p>
                <div class="mmc-indices-wrap">
                    <?php foreach (($p['indices'] ?? []) as $iid => $t) { echo mmc_admin_render_indice_row($base, $uid, $iid, $t, $colores_opts, $recubrimientos_globales); } ?>
                </div>
                <button type="button" class="button mmc-add-indice" style="margin-top:10px;">+ Agregar índice</button>
            </div>
        </div>
    </div>
    <?php
}


function mmc_admin_render_tag_row($base, $tid, $tag, $colores_opts) {
    ob_start(); ?>
    <div class="mmc-tag-row" data-tid="<?php echo esc_attr($tid); ?>">
        <div class="mmc-field" style="margin:0; min-width:160px;">
            <label>Texto</label>
            <input type="text" name="<?php echo $base;?>[tags][<?php echo $tid;?>][texto]" value="<?php echo esc_attr($tag['texto'] ?? '');?>" placeholder="Ej: Lens protection">
        </div>
        <div class="mmc-field" style="margin:0; min-width:110px;">
            <label>Color</label>
            <select name="<?php echo $base;?>[tags][<?php echo $tid;?>][color]">
                <?php foreach($colores_opts as $cval => $clabel): ?>
                <option value="<?php echo $cval;?>" <?php selected($tag['color']??'mmc-tag-azul', $cval); ?>><?php echo $clabel; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="button" class="mmc-remove-tag" title="Eliminar tag">&times;</button>
    </div>
    <?php
    return ob_get_clean();
}

function mmc_admin_render_tags_block($base, $tags, $colores_opts) {
    ob_start(); ?>
    <p class="mmc-section-label">Tags de beneficios (ilimitados)</p>
    <div class="mmc-tags-wrap" data-base="<?php echo esc_attr($base); ?>" data-tcounter="<?php echo max(0, count($tags)) - 1; ?>">
        <?php foreach ($tags as $tid => $tag) { echo mmc_admin_render_tag_row($base, $tid, $tag, $colores_opts); } ?>
    </div>
    <button type="button" class="button mmc-add-tag" style="margin:8px 0 14px;">+ Agregar tag</button>
    <?php
    return ob_get_clean();
}


function mmc_admin_render_recubrimientos_checklist($base, $t, $recubrimientos_globales) {
    $selected_ids = (array)($t['recubrimientos_ids'] ?? []);
    $recomendado  = $t['recubrimiento_recomendado'] ?? '';
    ob_start(); ?>
    <hr class="mmc-divider">
    <p class="mmc-section-label">Recubrimientos disponibles para este índice</p>
    <?php if (empty($recubrimientos_globales)): ?>
        <p class="mmc-hint">Aún no has creado Recubrimientos globales. Sube al inicio de la página y agrega al menos uno.</p>
    <?php else: ?>
    <div class="mmc-recub-checklist">
        <?php foreach ($recubrimientos_globales as $r): $rid = $r['id']; $checked = in_array($rid, $selected_ids); ?>
        <div class="mmc-recub-check-row">
            <label class="mmc-recub-enable-label">
                <input type="checkbox" name="<?php echo $base;?>[recubrimientos_ids][]" value="<?php echo esc_attr($rid); ?>" <?php checked($checked, true); ?>>
                <?php echo esc_html($r['nombre']); ?>
            </label>
            <label class="mmc-recub-recomendado-label">
                <input type="radio" name="<?php echo $base;?>[recubrimiento_recomendado]" value="<?php echo esc_attr($rid); ?>" <?php checked($recomendado, $rid); ?>>
                Recomendado
            </label>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}

function mmc_admin_render_recubrimiento_row($rid, $r = []) {
    $id = !empty($r['id']) ? $r['id'] : ('rc_' . wp_generate_password(10, false));
    ob_start(); ?>
    <div class="mmc-card mmc-recubrimiento-card" data-rid="<?php echo esc_attr($rid); ?>">
        <div class="mmc-card-header">
            <div class="mmc-card-num" style="background:#ea580c;"><?php echo intval($rid) + 1; ?></div>
            <p class="mmc-card-title">Recubrimiento<?php if(!empty($r['nombre'])): ?> — <span style="color:#2563eb;"><?php echo esc_html($r['nombre']); ?></span><?php endif; ?></p>
            <button type="button" class="mmc-remove-recubrimiento">Eliminar</button>
        </div>
        <div class="mmc-card-body">
            <input type="hidden" name="recubrimientos[<?php echo $rid;?>][id]" value="<?php echo esc_attr($id); ?>">
            <div class="mmc-grid-3">
                <div class="mmc-field">
                    <label>Nombre</label>
                    <input type="text" name="recubrimientos[<?php echo $rid;?>][nombre]" value="<?php echo esc_attr($r['nombre'] ?? '');?>" placeholder="Ej: Antirreflejo">
                </div>
                <div class="mmc-field">
                    <label>Precio antes (tachado)</label>
                    <input type="number" step="0.01" min="0" name="recubrimientos[<?php echo $rid;?>][precio_antes]" value="<?php echo esc_attr($r['precio_antes'] ?? 0);?>">
                </div>
                <div class="mmc-field">
                    <label>Precio adicional (0 = Gratis)</label>
                    <input type="number" step="0.01" min="0" name="recubrimientos[<?php echo $rid;?>][precio_ahora]" value="<?php echo esc_attr($r['precio_ahora'] ?? 0);?>">
                </div>
            </div>
            <div class="mmc-grid-2">
                <div class="mmc-field">
                    <label>Imagen (se muestra a la derecha)</label>
                    <div class="mmc-img-row">
                        <input type="text" name="recubrimientos[<?php echo $rid;?>][imagen_url]" id="rec_img_<?php echo $rid;?>" value="<?php echo esc_attr($r['imagen_url'] ?? '');?>" placeholder="URL">
                        <img class="mmc-img-preview <?php echo !empty($r['imagen_url'])?'visible':'';?>" src="<?php echo esc_url($r['imagen_url'] ?? '');?>" id="rec_img_prev_<?php echo $rid;?>">
                        <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#rec_img_<?php echo $rid;?>" data-preview="#rec_img_prev_<?php echo $rid;?>">Subir</button>
                    </div>
                </div>
                <div class="mmc-field">
                    <label>Texto tooltip</label>
                    <textarea name="recubrimientos[<?php echo $rid;?>][tooltip_texto]"><?php echo esc_textarea($r['tooltip_texto'] ?? '');?></textarea>
                </div>
            </div>
            <div class="mmc-field" style="max-width:500px;">
                <label>Imagen tooltip</label>
                <div class="mmc-img-row">
                    <input type="text" name="recubrimientos[<?php echo $rid;?>][tooltip_img]" id="rec_tip_<?php echo $rid;?>" value="<?php echo esc_attr($r['tooltip_img'] ?? '');?>" placeholder="URL">
                    <img class="mmc-img-preview <?php echo !empty($r['tooltip_img'])?'visible':'';?>" src="<?php echo esc_url($r['tooltip_img'] ?? '');?>" id="rec_tip_prev_<?php echo $rid;?>">
                    <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#rec_tip_<?php echo $rid;?>" data-preview="#rec_tip_prev_<?php echo $rid;?>">Subir</button>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function mmc_modal_optica_admin_page() {
    wp_enqueue_media();
    $saved = mmc_flujo_save();

    $defaults_p1 = [
        1 => ['nombre'=>'Visión Simple','descripcion'=>'Distancia','precio_antes'=>0,'precio_ahora'=>0,'icono_url'=>'','tooltip_texto'=>'','tooltip_img'=>'','badge'=>''],
        2 => ['nombre'=>'Visión Cercana','descripcion'=>'Lectura','precio_antes'=>0,'precio_ahora'=>0,'icono_url'=>'','tooltip_texto'=>'','tooltip_img'=>'','badge'=>''],
        3 => ['nombre'=>'Progresivo','descripcion'=>'Distancia y Lectura','precio_antes'=>0,'precio_ahora'=>0,'icono_url'=>'','tooltip_texto'=>'','tooltip_img'=>'','badge'=>''],
        4 => ['nombre'=>'Bifocal','descripcion'=>'Distancia y Lectura','precio_antes'=>0,'precio_ahora'=>0,'icono_url'=>'','tooltip_texto'=>'','tooltip_img'=>'','badge'=>''],
        5 => ['nombre'=>'Sin Graduación','descripcion'=>'Moda','precio_antes'=>0,'precio_ahora'=>0,'icono_url'=>'','tooltip_texto'=>'','tooltip_img'=>'','badge'=>''],
    ];
    $opciones  = get_option('mmc_flujo_paso1', $defaults_p1);
    $prog      = get_option('mmc_flujo_progresivo', ['standard'=>['titulo'=>'Estándar','desc'=>'','precio'=>0,'advertencia'=>'','icono_url'=>''],'office'=>['titulo'=>'Oficina','desc'=>'','precio'=>0,'advertencia'=>'No apto para conducir','icono_url'=>'']]);
    // REEMPLAZAR POR:
    $presc_cfg = get_option('mmc_flujo_prescripcion', ['correo_receta' => '']);
    ?>
    <style>
        #mmc-flujo-wrap * { box-sizing:border-box; }
        #mmc-flujo-wrap { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; max-width:900px; padding:24px 0 60px; }
        #mmc-flujo-wrap .mmc-header { display:flex; align-items:center; gap:14px; margin-bottom:32px; padding-bottom:20px; border-bottom:1px solid #e2e8f0; }
        #mmc-flujo-wrap .mmc-header-icon { width:42px; height:42px; background:#2563eb; border-radius:10px; display:flex; align-items:center; justify-content:center; }
        #mmc-flujo-wrap .mmc-header h1 { font-size:20px; font-weight:600; color:#111; margin:0; padding:0; }
        #mmc-flujo-wrap .mmc-header p { font-size:13px; color:#64748b; margin:4px 0 0; }
        #mmc-flujo-wrap .mmc-saved { background:#f0fdf4; border:1px solid #bbf7d0; color:#15803d; border-radius:8px; padding:10px 16px; font-size:13px; font-weight:500; margin-bottom:24px; display:flex; align-items:center; gap:8px; }
        #mmc-flujo-wrap .mmc-paquete-admin-card .mmc-card-header { background: #f0f9ff; border-bottom-color: #bae6fd; }
        #mmc-flujo-wrap .mmc-section-titulo { font-size:16px; font-weight:600; color:#111; margin:32px 0 12px; padding-bottom:8px; border-bottom:2px solid #e2e8f0; }
        #mmc-flujo-wrap .mmc-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; margin-bottom:14px; overflow:hidden; }
        #mmc-flujo-wrap .mmc-card-header { display:flex; align-items:center; gap:12px; padding:14px 20px; background:#f8fafc; border-bottom:1px solid #e2e8f0; }
        #mmc-flujo-wrap .mmc-card-num { width:26px; height:26px; background:#2563eb; color:#fff; border-radius:6px; font-size:12px; font-weight:600; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        #mmc-flujo-wrap .mmc-card-title { font-size:14px; font-weight:600; color:#111; margin:0; }
        #mmc-flujo-wrap .mmc-card-body { padding:20px; }
        #mmc-flujo-wrap .mmc-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px; }
        #mmc-flujo-wrap .mmc-grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; margin-bottom:14px; }
        #mmc-flujo-wrap .mmc-field label { display:block; font-size:11px; font-weight:600; color:#374151; margin-bottom:5px; text-transform:uppercase; letter-spacing:0.04em; }
        #mmc-flujo-wrap .mmc-field input[type="text"],
        #mmc-flujo-wrap .mmc-field input[type="number"],
        #mmc-flujo-wrap .mmc-field textarea { width:100%; height:36px; padding:0 10px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; color:#111; background:#fff; transition:border-color .15s; outline:none; }
        #mmc-flujo-wrap .mmc-field textarea { height:70px; padding:8px 10px; resize:vertical; }
        #mmc-flujo-wrap .mmc-field input:focus, #mmc-flujo-wrap .mmc-field textarea:focus { border-color:#2563eb; box-shadow:0 0 0 2px rgba(37,99,235,.08); }
        #mmc-flujo-wrap .mmc-hint { font-size:11px; color:#94a3b8; margin-top:4px; }
        #mmc-flujo-wrap .mmc-img-row { display:flex; gap:8px; align-items:center; }
        #mmc-flujo-wrap .mmc-img-row input { flex:1; }
        #mmc-flujo-wrap .mmc-img-preview { width:36px; height:36px; border:1px solid #e2e8f0; border-radius:6px; object-fit:cover; display:none; flex-shrink:0; }
        #mmc-flujo-wrap .mmc-img-preview.visible { display:block; }
        #mmc-flujo-wrap .mmc-btn-upload { height:36px; padding:0 12px; background:#f1f5f9; border:1px solid #d1d5db; border-radius:6px; font-size:12px; font-weight:500; color:#374151; cursor:pointer; white-space:nowrap; transition:background .15s; flex-shrink:0; }
        #mmc-flujo-wrap .mmc-btn-upload:hover { background:#e2e8f0; }
        #mmc-flujo-wrap .mmc-divider { border:none; border-top:1px dashed #e2e8f0; margin:16px 0; }
        #mmc-flujo-wrap .mmc-section-label { font-size:11px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:.06em; margin-bottom:12px; }
        #mmc-flujo-wrap .mmc-footer { display:flex; align-items:center; justify-content:space-between; padding:20px; background:#f8fafc; border-top:1px solid #e2e8f0; }
        #mmc-flujo-wrap .mmc-footer-note { font-size:12px; color:#94a3b8; }
        #mmc-flujo-wrap .mmc-btn-save { height:40px; padding:0 24px; background:#2563eb; color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; transition:background .15s; }
        #mmc-flujo-wrap .mmc-btn-save:hover { background:#1d4ed8; }
        #mmc-flujo-wrap .mmc-toggle-row { display:flex; align-items:center; gap:10px; padding:12px 0; border-bottom:1px solid #f1f5f9; }
        #mmc-flujo-wrap .mmc-toggle-row label { font-size:13px; color:#374151; cursor:pointer; flex:1; }
        #mmc-flujo-wrap .mmc-toggle-row .mmc-toggle-desc { font-size:12px; color:#94a3b8; }
        #mmc-flujo-wrap .mmc-proteccion-card { border-color: #0891b2; }
#mmc-flujo-wrap .mmc-indices-wrap { display:flex; flex-direction:column; gap:12px; margin-top:10px; }
#mmc-flujo-wrap .mmc-indice-row { background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:14px 16px; }
#mmc-flujo-wrap .mmc-indice-row-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
#mmc-flujo-wrap .mmc-indice-row-header strong { font-size:12px; text-transform:uppercase; letter-spacing:.04em; color:#64748b; }
#mmc-flujo-wrap .mmc-remove-indice { background:#fee2e2; color:#b91c1c; border:none; border-radius:6px; width:22px; height:22px; font-size:14px; cursor:pointer; line-height:1; }
#mmc-flujo-wrap .mmc-remove-proteccion { margin-left:auto; background:#fee2e2; color:#b91c1c; border:none; border-radius:6px; padding:6px 12px; font-size:12px; font-weight:600; cursor:pointer; white-space:nowrap; }
#mmc-flujo-wrap .mmc-paso-previo-row { border:1px solid #fde68a; background:#fffbeb; border-radius:8px; padding:12px 14px; margin-bottom:10px; }
#mmc-flujo-wrap .mmc-subproteccion-card { border-color:#7c3aed; margin-left:20px; background:#fbfaff; }
#mmc-flujo-wrap .mmc-remove-subproteccion { margin-left:auto; background:#fee2e2; color:#b91c1c; border:none; border-radius:6px; padding:6px 12px; font-size:12px; font-weight:600; cursor:pointer; }
#mmc-flujo-wrap .mmc-foto-toggle-row { border:1px solid #c4b5fd; background:#f5f3ff; border-radius:8px; padding:12px 14px; margin-bottom:10px; }
#mmc-flujo-wrap .mmc-colores-wrap { background:#faf9ff; border:1px dashed #c4b5fd; border-radius:8px; padding:14px; margin-bottom:14px; }
#mmc-flujo-wrap .mmc-color-row { display:flex; gap:12px; align-items:flex-end; background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-bottom:10px; position:relative; }
#mmc-flujo-wrap .mmc-color-row .mmc-field { margin-bottom:0; }
#mmc-flujo-wrap .mmc-color-row input[type="color"] { height:36px; width:52px; padding:2px; cursor:pointer; }
#mmc-flujo-wrap .mmc-remove-color { position:absolute; top:6px; right:8px; background:#fee2e2; color:#b91c1c; border:none; border-radius:50%; width:20px; height:20px; font-size:13px; cursor:pointer; line-height:1; }
#mmc-flujo-wrap .mmc-subprotecciones-lista { display:flex; flex-direction:column; gap:14px; }

#mmc-flujo-wrap .mmc-tags-wrap { display:flex; flex-wrap:wrap; gap:8px; margin-top:8px; }
#mmc-flujo-wrap .mmc-tag-row { display:flex; gap:6px; align-items:flex-end; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:8px 10px; }
#mmc-flujo-wrap .mmc-tag-row select { height:36px; padding:0 8px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; }
#mmc-flujo-wrap .mmc-remove-tag { background:#fee2e2; color:#b91c1c; border:none; border-radius:50%; width:24px; height:24px; font-size:14px; cursor:pointer; flex-shrink:0; }
#mmc-flujo-wrap .mmc-recubrimiento-card { border-color:#ea580c; }
#mmc-flujo-wrap .mmc-remove-recubrimiento { margin-left:auto; background:#fee2e2; color:#b91c1c; border:none; border-radius:6px; padding:6px 12px; font-size:12px; font-weight:600; cursor:pointer; }
#mmc-flujo-wrap .mmc-recub-checklist { display:flex; flex-direction:column; gap:6px; background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; padding:12px 14px; }
#mmc-flujo-wrap .mmc-recub-check-row { display:flex; align-items:center; gap:20px; font-size:13px; padding:4px 0; border-bottom:1px dashed #fed7aa; }
#mmc-flujo-wrap .mmc-recub-check-row:last-child { border-bottom:none; }
#mmc-flujo-wrap .mmc-recub-enable-label { flex:1; display:flex; align-items:center; gap:6px; font-weight:600; }
#mmc-flujo-wrap .mmc-recub-recomendado-label { display:flex; align-items:center; gap:6px; color:#c2410c; font-weight:600; }




#mmc-tree-editor-layout {
    display: flex;
    gap: 20px;
    align-items: flex-start;
    margin-bottom: 30px;
}
#mmc-tree-panel {
    width: 280px;
    flex-shrink: 0;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 14px;
    position: sticky;
    top: 32px;
    max-height: calc(100vh - 60px);
    overflow-y: auto;
}
.mmc-tree-panel-titulo { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #94a3b8; margin: 0 0 10px; letter-spacing: .04em; }
#mmc-tree-root { list-style: none; margin: 0; padding: 0; }
.mmc-tree-divider { height: 1px; background: #e2e8f0; margin: 10px 2px; list-style: none; }
.mmc-tree-item { margin: 1px 0; }
.mmc-tree-row {
    display: flex; align-items: center; gap: 6px;
    padding: 7px 8px; border-radius: 6px; cursor: pointer;
    font-size: 13px; color: #374151; user-select: none;
}
.mmc-tree-row:hover { background: #f1f5f9; }
.mmc-tree-row.activo { background: #dbeafe; color: #1e40af; font-weight: 600; }
.mmc-tree-toggle {
    width: 14px; text-align: center; color: #94a3b8; font-size: 11px;
    transition: transform .15s; flex-shrink: 0;
}
.mmc-tree-toggle.mmc-tree-toggle-open { transform: rotate(90deg); }
.mmc-tree-label { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* Jerarquía visual: sangría + línea guía por cada nivel */
.mmc-tree-children {
    display: none;
    list-style: none;
    margin: 0 0 0 8px;
    padding: 0 0 0 14px;
    border-left: 1px dashed #dbe2ea;
}

#mmc-editor-panel {
    flex: 1;
    min-width: 0;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 24px;
}
#mmc-editor-placeholder { color: #94a3b8; font-size: 14px; padding: 40px 0; text-align: center; }

/* Por defecto todo nodo editable está oculto; solo se muestra si está en la cadena activa */
.mmc-editor-node { display: none; }
.mmc-editor-node.mmc-editor-active { display: block; }

/* Cuando un nodo hijo está activo dentro de un padre también activo, separarlos visualmente */
.mmc-editor-node.mmc-editor-active .mmc-editor-node.mmc-editor-active {
    margin-top: 14px;
    padding: 16px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
}

    </style>

    <div id="mmc-flujo-wrap">
        <div class="mmc-header">
            <div class="mmc-header-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M20.188 10.934c.388.472.612 1.057.612 1.566S20.576 13.594 20.188 14.066C19.063 15.387 15.808 18 12 18s-7.063-2.613-8.188-3.934C3.424 13.594 3.2 13.009 3.2 12.5s.224-1.094.612-1.566C4.937 9.613 8.192 7 12 7s7.063 2.613 8.188 3.934z"/></svg>
            </div>
            <div>
                <h1>Flujo de Lentes</h1>
                <p>Configura todas las opciones del flujo de selección de lunas</p>
            </div>
        </div>

        <!-- REEMPLAZAR POR: -->
        <?php if($saved): ?>
        <div class="mmc-saved">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            Configuración guardada correctamente.
        </div>
        <?php endif; ?>
        
        

        
       
        <form method="post">

            <div id="mmc-tree-editor-layout">
                <div id="mmc-tree-panel">
                    <p class="mmc-tree-panel-titulo">Estructura</p>
                    <ul id="mmc-tree-root"></ul>
                </div>
                <div id="mmc-editor-panel">
                <div id="mmc-editor-placeholder">← Selecciona un elemento del árbol para editarlo.</div>

            <!-- ===== AJUSTES GENERALES DEL SIDEBAR ===== -->

            <!-- ===== AJUSTES GENERALES DEL SIDEBAR ===== -->

            <div class="mmc-top-section" data-tree-label="Ajustes Generales" id="mmc-section-ajustes">
                <p class="mmc-section-titulo">Ajustes Generales — Columna Izquierda</p>
                <?php $ajustes_sidebar = get_option('mmc_ajustes_sidebar', ['envio_gratis_monto' => 0, 'whatsapp_url' => '']); ?>
                <div class="mmc-card">
                    <div class="mmc-card-body">
                        <div class="mmc-grid-2">
                            <div class="mmc-field">
                            <label>Monto mínimo para envío gratis</label>
                            <input type="number" step="0.01" min="0" name="ajustes_sidebar[envio_gratis_monto]" value="<?php echo esc_attr($ajustes_sidebar['envio_gratis_monto'] ?? 0); ?>" placeholder="Ej: 276">
                            <p class="mmc-hint">Se muestra como "Envío gratis en pedidos superiores a [monto]". Déjalo en 0 para ocultar el mensaje.</p>
                            </div>
                        
                            <div class="mmc-field">
                            <label>Link de WhatsApp ("¿Necesitas ayuda?")</label>
                            <input type="text" name="ajustes_sidebar[whatsapp_url]" value="<?php echo esc_attr($ajustes_sidebar['whatsapp_url'] ?? ''); ?>" placeholder="https://wa.me/51999999999">
                            <p class="mmc-hint">A dónde lleva el ícono de chat en vivo.</p>
                            </div>
                        </div>
                    <hr class="mmc-divider">
                        <div class="mmc-field" style="max-width:500px;">
                        <label>Máscara de luna (fotocromáticos) — imagen genérica para todos los productos</label>
                        <div class="mmc-img-row">
                            <input type="text" name="ajustes_sidebar[mascara_luna_url]" id="mascara_luna_url" value="<?php echo esc_attr($ajustes_sidebar['mascara_luna_url'] ?? ''); ?>" placeholder="URL de la máscara (PNG con transparencia)">
                            <img class="mmc-img-preview <?php echo !empty($ajustes_sidebar['mascara_luna_url'])?'visible':'';?>" src="<?php echo esc_url($ajustes_sidebar['mascara_luna_url'] ?? ''); ?>" id="mascara_luna_prev">
                            <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#mascara_luna_url" data-preview="#mascara_luna_prev">Subir</button>
                        </div>
                        <p class="mmc-hint">Se usa como forma de la luna mientras el cliente elige el color fotocromático, sin importar la montura.</p>
                    </div>
                </div>
            </div>

            <!-- ===== PASO 1: OPCIONES DE USO ===== -->
            
            <div class="mmc-top-section" data-tree-label="Paso 1 — Opciones de uso" id="mmc-section-paso1">
            <!-- ===== PASO 1: OPCIONES DE USO ===== -->
            <p class="mmc-section-titulo">Paso 1 — Opciones de uso</p>

            <?php foreach($opciones as $i => $op): ?>
            <div class="mmc-card">
                <div class="mmc-card-header">
                    <div class="mmc-card-num"><?php echo $i; ?></div>
                    <p class="mmc-card-title"><?php echo esc_html($op['nombre'] ?: "Opción $i"); ?></p>
                </div>
                <div class="mmc-card-body">
                    <div class="mmc-grid-3">
                        <div class="mmc-field">
                            <label>Nombre</label>
                            <input type="text" name="flujo_paso1[<?php echo $i;?>][nombre]" value="<?php echo esc_attr($op['nombre']);?>" placeholder="Ej: Visión Simple">
                        </div>
                        <div class="mmc-field">
                            <label>Descripción corta</label>
                            <input type="text" name="flujo_paso1[<?php echo $i;?>][descripcion]" value="<?php echo esc_attr($op['descripcion']);?>" placeholder="Ej: Distancia">
                        </div>
                        <div class="mmc-field">
                            <label>Badge (opcional)</label>
                            <input type="text" name="flujo_paso1[<?php echo $i;?>][badge]" value="<?php echo esc_attr($op['badge']);?>" placeholder="Ej: 50% OFF">
                        </div>
                    </div>
                    <div class="mmc-grid-2" style="max-width:400px;">
                        <div class="mmc-field">
                            <label>Precio antes — tachado</label>
                            <input type="number" step="0.01" min="0" name="flujo_paso1[<?php echo $i;?>][precio_antes]" value="<?php echo esc_attr($op['precio_antes']);?>" placeholder="0.00">
                            <p class="mmc-hint">0 = no mostrar tachado</p>
                        </div>
                        <div class="mmc-field">
                            <label>Precio adicional actual</label>
                            <input type="number" step="0.01" min="0" name="flujo_paso1[<?php echo $i;?>][precio_ahora]" value="<?php echo esc_attr($op['precio_ahora']);?>" placeholder="0.00">
                            <p class="mmc-hint">0 = sin costo adicional</p>
                        </div>
                    </div>
                    <hr class="mmc-divider">
                    <p class="mmc-section-label">Ícono</p>
                    <div class="mmc-field" style="max-width:500px;margin-bottom:16px;">
                        <label>URL del ícono</label>
                        <div class="mmc-img-row">
                            <input type="text" name="flujo_paso1[<?php echo $i;?>][icono_url]" id="icono_<?php echo $i;?>" value="<?php echo esc_attr($op['icono_url']);?>" placeholder="URL">
                            <img class="mmc-img-preview <?php echo !empty($op['icono_url'])?'visible':'';?>" src="<?php echo esc_url($op['icono_url']);?>" id="icono_prev_<?php echo $i;?>">
                            <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#icono_<?php echo $i;?>" data-preview="#icono_prev_<?php echo $i;?>">Subir</button>
                        </div>
                    </div>
                    <hr class="mmc-divider">
                    <p class="mmc-section-label">Tooltip (?)</p>
                    <div class="mmc-grid-2">
                        <div class="mmc-field">
                            <label>Texto del tooltip</label>
                            <textarea name="flujo_paso1[<?php echo $i;?>][tooltip_texto]" placeholder="Texto breve"><?php echo esc_textarea($op['tooltip_texto']);?></textarea>
                        </div>
                        <div class="mmc-field">
                            <label>Imagen del tooltip</label>
                            <div class="mmc-img-row">
                                <input type="text" name="flujo_paso1[<?php echo $i;?>][tooltip_img]" id="tip_<?php echo $i;?>" value="<?php echo esc_attr($op['tooltip_img']);?>" placeholder="URL">
                                <img class="mmc-img-preview <?php echo !empty($op['tooltip_img'])?'visible':'';?>" src="<?php echo esc_url($op['tooltip_img']);?>" id="tip_prev_<?php echo $i;?>">
                                <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#tip_<?php echo $i;?>" data-preview="#tip_prev_<?php echo $i;?>">Subir</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
           
            <?php endforeach; ?>
            </div>

            <!-- ===== PROGRESIVO: Standard / Office ===== -->
            
            <div class="mmc-top-section" data-tree-label="Progresivo — Tipos de lente" id="mmc-section-progresivo">
            <p class="mmc-section-titulo">Progresivo — Tipos de lente</p>

            <?php
            $prog_labels = ['standard' => 'Estándar', 'office' => 'Oficina'];
            foreach(['standard','office'] as $key):
                $p = $prog[$key] ?? [];
            ?>
            <div class="mmc-card">
                <div class="mmc-card-header">
                    <div class="mmc-card-num" style="background:#7c3aed;"><?php echo strtoupper(substr($key,0,1)); ?></div>
                    <p class="mmc-card-title"><?php echo $prog_labels[$key]; ?></p>
                </div>
                <div class="mmc-card-body">
                    <div class="mmc-grid-2">
                        <div class="mmc-field">
                            <label>Título</label>
                            <input type="text" name="flujo_progresivo[<?php echo $key;?>][titulo]" value="<?php echo esc_attr($p['titulo']??'');?>" placeholder="Ej: Estándar">
                        </div>
                        <div class="mmc-field">
                            <label>Precio adicional</label>
                            <input type="number" step="0.01" min="0" name="flujo_progresivo[<?php echo $key;?>][precio]" value="<?php echo esc_attr($p['precio']??0);?>" placeholder="0.00">
                        </div>
                    </div>
                    <div class="mmc-grid-2">
                        <div class="mmc-field">
                            <label>Descripción</label>
                            <textarea name="flujo_progresivo[<?php echo $key;?>][desc]" placeholder="Descripción del tipo de lente"><?php echo esc_textarea($p['desc']??'');?></textarea>
                        </div>
                        <div class="mmc-field">
                            <label>Advertencia (opcional)</label>
                            <input type="text" name="flujo_progresivo[<?php echo $key;?>][advertencia]" value="<?php echo esc_attr($p['advertencia']??'');?>" placeholder="Ej: No apto para conducir">
                        </div>
                    </div>
                    <div class="mmc-field" style="max-width:500px;">
                        <label>Ícono</label>
                        <div class="mmc-img-row">
                            <input type="text" name="flujo_progresivo[<?php echo $key;?>][icono_url]" id="prog_ico_<?php echo $key;?>" value="<?php echo esc_attr($p['icono_url']??'');?>" placeholder="URL">
                            <img class="mmc-img-preview <?php echo !empty($p['icono_url'])?'visible':'';?>" src="<?php echo esc_url($p['icono_url']??'');?>" id="prog_ico_prev_<?php echo $key;?>">
                            <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#prog_ico_<?php echo $key;?>" data-preview="#prog_ico_prev_<?php echo $key;?>">Subir</button>
                        </div>
                    </div>
                </div>
            </div>
           
            <?php endforeach; ?>
            </div>

            
            <!-- ===== REempazada ===== -->
            <div class="mmc-top-section" data-tree-label="Prescripción — Ajustes" id="mmc-section-prescripcion">
            <!-- ===== PRESCRIPCIÓN: Ajustes ===== -->
            <p class="mmc-section-titulo">Opciones de prescripción — Ajustes</p>
            <div class="mmc-card">
                <div class="mmc-card-body">
                    <p style="font-size:13px;color:#64748b;margin:0 0 14px;">"Completar en línea", "Subir imagen" y "Enviar por correo" están siempre activas.</p>
                    <div class="mmc-field" style="max-width:400px;">
                        <label>Correo para recepción de recetas</label>
                        <input type="email" name="prescripcion_correo" value="<?php echo esc_attr($presc_cfg['correo_receta'] ?? ''); ?>" placeholder="hola@tudominio.com">
                        <p class="mmc-hint">Se muestra al cliente en la opción "Enviar por correo".</p>
                    </div>
                    <!-- Próximamente: "Usar receta guardada" (requiere sistema de cuentas de usuario, aún no implementado) -->
                </div>
            </div>
            </div>
            </div>

            <!-- ===== RECUBRIMIENTOS (GLOBAL) ===== -->
            
            <div class="mmc-top-section" data-tree-label="Recubrimientos (Global)" id="mmc-section-recubrimientos">
            <!-- ===== RECUBRIMIENTOS (GLOBAL) ===== -->
            <p class="mmc-section-titulo">Recubrimientos (Global)</p>
            <p style="font-size:13px;color:#64748b;margin:-8px 0 16px;">Crea aquí todos los recubrimientos disponibles. Luego, dentro de cada Índice, eliges cuáles aplican y cuál es el recomendado. Recuerda crear uno tipo "Sin recubrimiento" con precio 0 (se mostrará como "Gratis").</p>
            <?php
            $recubrimientos_globales = get_option('mmc_recubrimientos', []);
            ?>
            <div class="mmc-recubrimientos-wrap" id="mmc-recubrimientos-wrap" data-counter="<?php echo count($recubrimientos_globales) - 1; ?>">
                <?php foreach ($recubrimientos_globales as $rid => $r): ?>
                    <?php echo mmc_admin_render_recubrimiento_row($rid, $r); ?>
                <?php endforeach; ?>
            </div>
           
            <button type="button" class="button button-primary mmc-add-recubrimiento" style="margin-bottom:30px;">+ Agregar recubrimiento</button>
            </div>

            <!-- ===== PROTECCIONES E ÍNDICES POR FLUJO ===== -->

            <!-- ===== PROTECCIONES E ÍNDICES POR FLUJO ===== -->
            <?php
            $flujos_config = [
                'simple'         => 'Visión Simple',
                'cercana'        => 'Visión Cercana',
                'progresivo'     => 'Progresivo',
                'bifocal'        => 'Bifocal',
                'sin_graduacion' => 'Sin Graduación',
            ];
            $colores_opts = [
                'mmc-tag-azul'    => 'Azul',
                'mmc-tag-verde'   => 'Verde',
                'mmc-tag-naranja' => 'Naranja',
                'mmc-tag-gris'    => 'Gris',
                'mmc-tag-morado'  => 'Morado',
            ];
            // REEMPLAZAR POR:
            foreach ($flujos_config as $flujo_key => $flujo_nombre):
                $protecciones_flujo = get_option('mmc_protecciones_' . $flujo_key, []);
                $max_pid = !empty($protecciones_flujo) ? max(array_keys($protecciones_flujo)) : -1;
            ?>
            <div class="mmc-top-section mmc-flujo-section" data-tree-label="<?php echo esc_attr($flujo_nombre); ?>" data-flujo="<?php echo esc_attr($flujo_key); ?>" id="mmc-section-flujo-<?php echo esc_attr($flujo_key); ?>">
            <p class="mmc-section-titulo">Protecciones e Índices — <?php echo $flujo_nombre; ?></p>
            <div class="mmc-protecciones-wrap" id="mmc-protecciones-wrap-<?php echo $flujo_key;?>" data-counter="<?php echo $max_pid; ?>">
                <?php
            $num = 1;
            foreach ($protecciones_flujo as $pid => $p) {
                    mmc_admin_render_proteccion_card($flujo_key, $pid, $p, $colores_opts, $num, $recubrimientos_globales);
                    $num++;
                }
            ?>
            </div>
            
            <button type="button" class="button button-primary mmc-add-proteccion" data-flujo="<?php echo $flujo_key;?>" style="margin-bottom:30px;">+ Agregar protección</button>
            </div>
            <?php endforeach; ?>

            </div><!-- /#mmc-editor-panel -->
            </div><!-- /#mmc-tree-editor-layout -->

            <!-- ===== PLANTILLAS OCULTAS PARA AGREGAR DINÁMICAMENTE ===== -->

            <!-- ===== PLANTILLAS OCULTAS PARA AGREGAR DINÁMICAMENTE ===== -->
            
            <!-- AGREGAR, junto a las demás plantillas: -->
<script type="text/template" id="tpl-tag">
<div class="mmc-tag-row" data-tid="__TID__">
    <div class="mmc-field" style="margin:0;min-width:160px;">
        <label>Texto</label>
        <input type="text" name="__BASE__[tags][__TID__][texto]" placeholder="Ej: Lens protection">
    </div>
    <div class="mmc-field" style="margin:0;min-width:110px;">
        <label>Color</label>
        <select name="__BASE__[tags][__TID__][color]">
            <?php foreach ($colores_opts as $cval => $clabel): ?>
            <option value="<?php echo $cval; ?>"><?php echo $clabel; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="button" class="mmc-remove-tag" title="Eliminar tag">&times;</button>
</div>
</script>

<script type="text/template" id="tpl-recubrimiento">
<div class="mmc-card mmc-recubrimiento-card" data-rid="__RID__">
    <div class="mmc-card-header">
        <div class="mmc-card-num" style="background:#ea580c;">__NUM__</div>
        <p class="mmc-card-title">Recubrimiento (nuevo)</p>
        <button type="button" class="mmc-remove-recubrimiento">Eliminar</button>
    </div>
    <div class="mmc-card-body">
        <input type="hidden" name="recubrimientos[__RID__][id]" value="__NEWID__">
        <div class="mmc-grid-3">
            <div class="mmc-field">
                <label>Nombre</label>
                <input type="text" name="recubrimientos[__RID__][nombre]" placeholder="Ej: Antirreflejo">
            </div>
            <div class="mmc-field">
                <label>Precio antes (tachado)</label>
                <input type="number" step="0.01" min="0" name="recubrimientos[__RID__][precio_antes]" value="0">
            </div>
            <div class="mmc-field">
                <label>Precio adicional (0 = Gratis)</label>
                <input type="number" step="0.01" min="0" name="recubrimientos[__RID__][precio_ahora]" value="0">
            </div>
        </div>
        <div class="mmc-grid-2">
            <div class="mmc-field">
                <label>Imagen (derecha)</label>
                <div class="mmc-img-row">
                    <input type="text" name="recubrimientos[__RID__][imagen_url]" id="rec_img___RID__" placeholder="URL">
                    <img class="mmc-img-preview" src="" id="rec_img_prev___RID__">
                    <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#rec_img___RID__" data-preview="#rec_img_prev___RID__">Subir</button>
                </div>
            </div>
            <div class="mmc-field">
                <label>Texto tooltip</label>
                <textarea name="recubrimientos[__RID__][tooltip_texto]"></textarea>
            </div>
        </div>
        <div class="mmc-field" style="max-width:500px;">
            <label>Imagen tooltip</label>
            <div class="mmc-img-row">
                <input type="text" name="recubrimientos[__RID__][tooltip_img]" id="rec_tip___RID__" placeholder="URL">
                <img class="mmc-img-preview" src="" id="rec_tip_prev___RID__">
                <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#rec_tip___RID__" data-preview="#rec_tip_prev___RID__">Subir</button>
            </div>
        </div>
    </div>
</div>
</script>
            
            <script type="text/template" id="tpl-proteccion">
            <div class="mmc-card mmc-proteccion-card" data-flujo="__FLUJO__" data-pid="__PID__" data-icounter="0">
                <div class="mmc-card-header">
                    <div class="mmc-card-num" style="background:#0891b2;">__NUM__</div>
                    <p class="mmc-card-title">Protección (nueva)</p>
                    <button type="button" class="mmc-remove-proteccion">Eliminar protección</button>
                </div>
                <div class="mmc-card-body">
                    <div class="mmc-grid-3">
                        <div class="mmc-field">
                            <label>Nombre de la protección</label>
                            <input type="text" name="protecciones[__FLUJO__][__PID__][nombre]" placeholder="Ej: Clear">
                        </div>
                        <div class="mmc-field">
                            <label>Precio antes (tachado)</label>
                            <input type="number" step="0.01" min="0" name="protecciones[__FLUJO__][__PID__][precio_antes]" value="0">
                        </div>
                        <div class="mmc-field">
                            <label>Precio adicional (0 = Gratis)</label>
                            <input type="number" step="0.01" min="0" name="protecciones[__FLUJO__][__PID__][precio_ahora]" value="0">
                        </div>
                    </div>
                    <div class="mmc-grid-2">
                        <div class="mmc-field">
                            <label>Imagen del lente</label>
                            <div class="mmc-img-row">
                                <input type="text" name="protecciones[__FLUJO__][__PID__][imagen_url]" id="prot_img___FLUJO_____PID__" placeholder="URL de imagen">
                                <img class="mmc-img-preview" src="" id="prot_img_prev___FLUJO_____PID__">
                                <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#prot_img___FLUJO_____PID__" data-preview="#prot_img_prev___FLUJO_____PID__">Subir</button>
                            </div>
                        </div>
                        <div class="mmc-field">
                            <label>Descripción (admite &lt;strong&gt;)</label>
                            <textarea name="protecciones[__FLUJO__][__PID__][descripcion]" placeholder="Ej: Protección contra luz azul..."></textarea>
                        </div>
                    </div>
                    <div class="mmc-toggle-row mmc-paso-previo-row">
                        <input type="checkbox" id="paso_previo___FLUJO_____PID__" name="protecciones[__FLUJO__][__PID__][paso_previo]" value="1">
                        <label for="paso_previo___FLUJO_____PID__" style="flex:1;">
                            <strong>Tiene paso previo (aún no configurado)</strong>
                            <span class="mmc-toggle-desc"> — Si se marca, esta protección mostrará "Próximamente" en vez de avanzar a Índices.</span>
                        </label>
                    </div>
                   
                    <hr class="mmc-divider">
                    <p class="mmc-section-label">Tags de beneficios (ilimitados)</p>
                    <div class="mmc-tags-wrap" data-base="protecciones[__FLUJO__][__PID__]" data-tcounter="-1"></div>
                    <button type="button" class="button mmc-add-tag" style="margin:8px 0 14px;">+ Agregar tag</button>

                    <hr class="mmc-divider">
                    <p class="mmc-section-label">Tooltip (?)</p>
                    <div class="mmc-grid-2">
                        <div class="mmc-field">
                            <label>Texto del tooltip</label>
                            <textarea name="protecciones[__FLUJO__][__PID__][tooltip_texto]"></textarea>
                        </div>
                        <div class="mmc-field">
                            <label>Imagen del tooltip</label>
                            <div class="mmc-img-row">
                                <input type="text" name="protecciones[__FLUJO__][__PID__][tooltip_img]" id="prot_tip___FLUJO_____PID__" placeholder="URL">
                                <img class="mmc-img-preview" src="" id="prot_tip_prev___FLUJO_____PID__">
                                <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#prot_tip___FLUJO_____PID__" data-preview="#prot_tip_prev___FLUJO_____PID__">Subir</button>
                            </div>
                        </div>
                    </div>

                    <div class="mmc-toggle-row mmc-foto-toggle-row">
                        <input type="checkbox" class="mmc-foto-checkbox" id="foto___FLUJO_____PID__" name="protecciones[__FLUJO__][__PID__][es_fotocromatico]" value="1">
                        <label for="foto___FLUJO_____PID__" style="flex:1;">
                            <strong>Es fotocromático / tintado (mostrar selector de color)</strong>
                            <span class="mmc-toggle-desc"> — Al seleccionarlo en el frontend, se despliega un selector de colores antes de continuar.</span>
                        </label>
                    </div>
                    <div class="mmc-colores-wrap" style="display:none;">
                        <p class="mmc-section-label">Colores disponibles</p>
                        <div class="mmc-colores-rows"></div>
                        <button type="button" class="button mmc-add-color" style="margin-top:8px;">+ Agregar color</button>
                    </div>

                    <div class="mmc-subprotecciones-wrap" style="display:none;">
                        <hr class="mmc-divider">
                        <p class="mmc-section-label">Sub-Protecciones</p>
                        <div class="mmc-subprotecciones-lista"></div>
                        <button type="button" class="button mmc-add-subproteccion" style="margin:10px 0 20px;">+ Agregar sub-protección</button>
                    </div>

                    <div class="mmc-indices-wrap-outer">
                        <hr class="mmc-divider">
                        <p class="mmc-section-label">Índices de esta protección</p>
                        <div class="mmc-indices-wrap"></div>
                        <button type="button" class="button mmc-add-indice" style="margin-top:10px;">+ Agregar índice</button>
                    </div>

                </div>
            </div>
            </script>

            <script type="text/template" id="tpl-indice">
            <div class="mmc-indice-row" data-iid="__IID__">
                <div class="mmc-indice-row-header">
                    <strong>Índice</strong>
                    <button type="button" class="mmc-remove-indice" title="Eliminar índice">&times;</button>
                </div>
                <div class="mmc-grid-3">
                    <div class="mmc-field">
                        <label>Nombre (ej: 1.50)</label>
                        <input type="text" name="protecciones[__FLUJO__][__PID__][indices][__IID__][nombre]" placeholder="1.50">
                    </div>
                    <div class="mmc-field">
                        <label>Precio antes (tachado)</label>
                        <input type="number" step="0.01" min="0" name="protecciones[__FLUJO__][__PID__][indices][__IID__][precio_antes]" value="0">
                    </div>
                    <div class="mmc-field">
                        <label>Precio adicional (0 = sin costo)</label>
                        <input type="number" step="0.01" min="0" name="protecciones[__FLUJO__][__PID__][indices][__IID__][precio_ahora]" value="0">
                    </div>
                </div>
                <div class="mmc-field" style="margin-bottom:14px;">
                    <label>Descripción corta</label>
                    <input type="text" name="protecciones[__FLUJO__][__PID__][indices][__IID__][descripcion]" placeholder="Ej: Lentes para uso cotidiano">
                </div>
                <div class="mmc-grid-2">
                    <div class="mmc-field">
                        <label>Imagen del lente</label>
                        <div class="mmc-img-row">
                            <input type="text" name="protecciones[__FLUJO__][__PID__][indices][__IID__][imagen_url]" id="idx_img___FLUJO_____PID_____IID__" placeholder="URL">
                            <img class="mmc-img-preview" src="" id="idx_img_prev___FLUJO_____PID_____IID__">
                            <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#idx_img___FLUJO_____PID_____IID__" data-preview="#idx_img_prev___FLUJO_____PID_____IID__">Subir</button>
                        </div>
                    </div>
                    <div class="mmc-field">
                        <label>Imagen tooltip</label>
                        <div class="mmc-img-row">
                            <input type="text" name="protecciones[__FLUJO__][__PID__][indices][__IID__][tooltip_img]" id="idx_tip___FLUJO_____PID_____IID__" placeholder="URL">
                            <img class="mmc-img-preview" src="" id="idx_tip_prev___FLUJO_____PID_____IID__">
                            <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#idx_tip___FLUJO_____PID_____IID__" data-preview="#idx_tip_prev___FLUJO_____PID_____IID__">Subir</button>
                        </div>
                    </div>
                </div>
                <div class="mmc-field" style="margin-bottom:0;">
                    <label>Texto tooltip</label>
                    <textarea name="protecciones[__FLUJO__][__PID__][indices][__IID__][tooltip_texto]"></textarea>
                </div>
                <hr class="mmc-divider">
                <p class="mmc-section-label">Título especial (logo SVG + imagen — para Transitions, opcional)</p>
                <div class="mmc-grid-2">
                    <div class="mmc-field">
                        <label>Logo SVG</label>
                        <div class="mmc-img-row">
                            <input type="text" name="protecciones[__FLUJO__][__PID__][indices][__IID__][titulo_svg]" id="idx_svg___FLUJO_____PID_____IID__" placeholder="URL">
                            <img class="mmc-img-preview" src="" id="idx_svg_prev___FLUJO_____PID_____IID__">
                            <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#idx_svg___FLUJO_____PID_____IID__" data-preview="#idx_svg_prev___FLUJO_____PID_____IID__">Subir</button>
                        </div>
                    </div>
                    <div class="mmc-field">
                        <label>Imagen secundaria (Photochromic)</label>
                        <div class="mmc-img-row">
                            <input type="text" name="protecciones[__FLUJO__][__PID__][indices][__IID__][titulo_img]" id="idx_timg___FLUJO_____PID_____IID__" placeholder="URL">
                            <img class="mmc-img-preview" src="" id="idx_timg_prev___FLUJO_____PID_____IID__">
                            <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#idx_timg___FLUJO_____PID_____IID__" data-preview="#idx_timg_prev___FLUJO_____PID_____IID__">Subir</button>
                        </div>
                    </div>
                </div>
                
                <hr class="mmc-divider">
<p class="mmc-section-label">Tags de beneficios (ilimitados)</p>
<div class="mmc-tags-wrap" data-base="protecciones[__FLUJO__][__PID__][indices][__IID__]" data-tcounter="-1"></div>
<button type="button" class="button mmc-add-tag" style="margin:8px 0 14px;">+ Agregar tag</button>

<hr class="mmc-divider">
<p class="mmc-section-label">Recubrimientos disponibles para este índice</p>
<?php if (empty($recubrimientos_globales)): ?>
    <p class="mmc-hint">Aún no has creado Recubrimientos globales.</p>
<?php else: ?>
<div class="mmc-recub-checklist">
    <?php foreach ($recubrimientos_globales as $r): ?>
    <div class="mmc-recub-check-row">
        <label class="mmc-recub-enable-label">
            <input type="checkbox" name="protecciones[__FLUJO__][__PID__][indices][__IID__][recubrimientos_ids][]" value="<?php echo esc_attr($r['id']); ?>"> <?php echo esc_html($r['nombre']); ?>
        </label>
        <label class="mmc-recub-recomendado-label">
            <input type="radio" name="protecciones[__FLUJO__][__PID__][indices][__IID__][recubrimiento_recomendado]" value="<?php echo esc_attr($r['id']); ?>"> Recomendado
        </label>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
                
            </div>
            </script>
            
            <script type="text/template" id="tpl-subproteccion">
<div class="mmc-card mmc-subproteccion-card" data-flujo="__FLUJO__" data-pid="__PID__" data-sid="__SID__" data-icounter="0" data-ccounter="0">
    <div class="mmc-card-header">
        <div class="mmc-card-num" style="background:#7c3aed;">__NUM__</div>
        <p class="mmc-card-title">Sub-Protección (nueva)</p>
        <button type="button" class="mmc-remove-subproteccion">Eliminar</button>
    </div>
    <div class="mmc-card-body">
        <div class="mmc-grid-3">
            <div class="mmc-field">
                <label>Nombre</label>
                <input type="text" name="protecciones[__FLUJO__][__PID__][sub_protecciones][__SID__][nombre]" placeholder="Ej: Gris intenso">
            </div>
            <div class="mmc-field">
                <label>Precio antes (tachado)</label>
                <input type="number" step="0.01" min="0" name="protecciones[__FLUJO__][__PID__][sub_protecciones][__SID__][precio_antes]" value="0">
            </div>
            <div class="mmc-field">
                <label>Precio adicional (0 = Gratis)</label>
                <input type="number" step="0.01" min="0" name="protecciones[__FLUJO__][__PID__][sub_protecciones][__SID__][precio_ahora]" value="0">
            </div>
        </div>
        <div class="mmc-grid-2">
            <div class="mmc-field">
                <label>Imagen</label>
                <div class="mmc-img-row">
                    <input type="text" name="protecciones[__FLUJO__][__PID__][sub_protecciones][__SID__][imagen_url]" id="sub_img___FLUJO_____PID_____SID__" placeholder="URL">
                    <img class="mmc-img-preview" src="" id="sub_img_prev___FLUJO_____PID_____SID__">
                    <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#sub_img___FLUJO_____PID_____SID__" data-preview="#sub_img_prev___FLUJO_____PID_____SID__">Subir</button>
                </div>
            </div>
            <div class="mmc-field">
                <label>Descripción</label>
                <textarea name="protecciones[__FLUJO__][__PID__][sub_protecciones][__SID__][descripcion]"></textarea>
            </div>
        </div>

        <div class="mmc-toggle-row mmc-foto-toggle-row">
            <input type="checkbox" class="mmc-foto-checkbox" id="foto___FLUJO_____PID_____SID__" name="protecciones[__FLUJO__][__PID__][sub_protecciones][__SID__][es_fotocromatico]" value="1">
            <label for="foto___FLUJO_____PID_____SID__" style="flex:1;">
                <strong>Es fotocromático / tintado (mostrar selector de color)</strong>
                <span class="mmc-toggle-desc"> — Al seleccionarlo en el frontend, se despliega un selector de colores antes de continuar.</span>
            </label>
        </div>
        <div class="mmc-colores-wrap" style="display:none;">
            <p class="mmc-section-label">Colores disponibles</p>
            <div class="mmc-colores-rows"></div>
            <button type="button" class="button mmc-add-color" style="margin-top:8px;">+ Agregar color</button>
        </div>

<hr class="mmc-divider">
<p class="mmc-section-label">Tags de beneficios (ilimitados)</p>
<div class="mmc-tags-wrap" data-base="protecciones[__FLUJO__][__PID__][sub_protecciones][__SID__]" data-tcounter="-1"></div>
<button type="button" class="button mmc-add-tag" style="margin:8px 0 14px;">+ Agregar tag</button>

        <hr class="mmc-divider">
        <p class="mmc-section-label">Tooltip (?)</p>
        <div class="mmc-grid-2">
            <div class="mmc-field">
                <label>Texto</label>
                <textarea name="protecciones[__FLUJO__][__PID__][sub_protecciones][__SID__][tooltip_texto]"></textarea>
            </div>
            <div class="mmc-field">
                <label>Imagen</label>
                <div class="mmc-img-row">
                    <input type="text" name="protecciones[__FLUJO__][__PID__][sub_protecciones][__SID__][tooltip_img]" id="sub_tip___FLUJO_____PID_____SID__" placeholder="URL">
                    <img class="mmc-img-preview" src="" id="sub_tip_prev___FLUJO_____PID_____SID__">
                    <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#sub_tip___FLUJO_____PID_____SID__" data-preview="#sub_tip_prev___FLUJO_____PID_____SID__">Subir</button>
                </div>
            </div>
        </div>

        <hr class="mmc-divider">
        <p class="mmc-section-label">Índices de esta sub-protección</p>
        <div class="mmc-indices-wrap"></div>
        <button type="button" class="button mmc-add-indice" style="margin-top:10px;">+ Agregar índice</button>
    </div>
</div>
</script>

<script type="text/template" id="tpl-color">
<div class="mmc-color-row" data-cid="__CID__">
    <button type="button" class="mmc-remove-color" title="Eliminar color">&times;</button>
    <div class="mmc-field">
        <label>Nombre del color</label>
        <input type="text" name="__BASE__[colores][__CID__][nombre]" placeholder="Ej: Gris">
    </div>
    <div class="mmc-field">
        <label>Tipo</label>
        <select class="mmc-color-tipo-select" name="__BASE__[colores][__CID__][tipo]">
            <option value="solido">Sólido</option>
            <option value="degradado">Degradado</option>
        </select>
    </div>
    <div class="mmc-field">
        <label>Color 1</label>
        <input type="color" name="__BASE__[colores][__CID__][hex1]" value="#cccccc">
    </div>
    <div class="mmc-field mmc-color-hex2" style="display:none;">
        <label>Color 2 (degradado)</label>
        <input type="color" name="__BASE__[colores][__CID__][hex2]" value="#666666">
    </div>
    <div class="mmc-field" style="flex:1.3;">
        <label>Imagen (opcional)</label>
        <div class="mmc-img-row">
            <input type="text" name="__BASE__[colores][__CID__][imagen_url]" id="col_img___UID_____CID__" placeholder="URL">
            <img class="mmc-img-preview" src="" id="col_img_prev___UID_____CID__">
            <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#col_img___UID_____CID__" data-preview="#col_img_prev___UID_____CID__">Subir</button>
        </div>
    </div>
</div>
</script>
            

            <!-- GUARDAR -->
            <div class="mmc-card">
                <div class="mmc-footer">
                    <span class="mmc-footer-note">Los cambios se aplican inmediatamente al guardar</span>
                    <button type="submit" name="mmc_flujo_save" class="mmc-btn-save">Guardar configuración</button>
                </div>
            </div>

        </form>
    </div>

 <!-- ===== REEMPLAZAR POR:===== -->
<script>
jQuery(document).ready(function($) {

    $(document).on('click', '.mmc-up-btn', function(e) {
        e.preventDefault();
        var ti = $($(this).data('target'));
        var tp = $($(this).data('preview'));
        var f  = wp.media({multiple:false});
        f.on('select', function() {
            var url = f.state().get('selection').first().toJSON().url;
            ti.val(url); tp.attr('src',url).addClass('visible');
        });
        f.open();
    });

    function nextId($container, attr) {
        var current = parseInt($container.attr(attr) || '0');
        current++;
        $container.attr(attr, current);
        return current;
    }
    function replaceAll(str, find, val) { return str.split(find).join(val); }

    // ---------- Agregar Protección ----------
    $(document).on('click', '.mmc-add-proteccion', function() {
        var flujo = $(this).data('flujo');
        var $wrap = $('#mmc-protecciones-wrap-' + flujo);
        var pid   = nextId($wrap, 'data-counter');
        var num   = $wrap.children('.mmc-proteccion-card').length + 1;
        var html  = $('#tpl-proteccion').html();
        html = replaceAll(html, '__FLUJO__', flujo);
        html = replaceAll(html, '__PID__', pid);
        html = replaceAll(html, '__NUM__', num);
        $wrap.append(html);
    });

    // ---------- Agregar Sub-Protección ----------
    $(document).on('click', '.mmc-add-subproteccion', function() {
        var $protCard = $(this).closest('.mmc-proteccion-card');
        var flujo = $protCard.data('flujo');
        var pid   = $protCard.data('pid');
        var sid   = nextId($protCard, 'data-scounter');
        var num   = $protCard.find('.mmc-subprotecciones-lista').children('.mmc-subproteccion-card').length + 1;
        var html  = $('#tpl-subproteccion').html();
        html = replaceAll(html, '__FLUJO__', flujo);
        html = replaceAll(html, '__PID__', pid);
        html = replaceAll(html, '__SID__', sid);
        html = replaceAll(html, '__NUM__', num);
        $protCard.find('.mmc-subprotecciones-lista').append(html);
    });

    // ---------- Agregar Índice (protección o sub-protección, delegado por el .mmc-card más cercano) ----------
// BUSCAR el bloque completo de '.mmc-add-indice' (el handler existente) y REEMPLAZARLO por:

    $(document).on('click', '.mmc-add-indice', function() {
        var $card = $(this).closest('.mmc-subproteccion-card');
        var base, uid, esSub = false;
        if ($card.length) {
            esSub = true;
            var flujo = $card.data('flujo'), pid = $card.data('pid'), sid = $card.data('sid');
            base = 'protecciones[' + flujo + '][' + pid + '][sub_protecciones][' + sid + ']';
        } else {
            $card = $(this).closest('.mmc-proteccion-card');
            var flujo2 = $card.data('flujo'), pid2 = $card.data('pid');
            base = 'protecciones[' + flujo2 + '][' + pid2 + ']';
        }
        var flujoC = $card.data('flujo'), pidC = $card.data('pid');
        var iid  = nextId($card, 'data-icounter');
        var html = $('#tpl-indice').html();
        html = replaceAll(html, '__FLUJO__', flujoC);
        html = replaceAll(html, '__PID__', pidC);
        html = replaceAll(html, '__IID__', iid);

        if (esSub) {
            var oldBaseDefault = 'protecciones[' + flujoC + '][' + pidC + '][indices][' + iid + ']';
            var newBase = base + '[indices][' + iid + ']';
            html = html.split(oldBaseDefault).join(newBase);
        }
        $card.find('.mmc-indices-wrap').first().append(html);
    });

    // ---------- Tags dinámicos (protección, sub-protección, índice) ----------
    $(document).on('click', '.mmc-add-tag', function() {
        var $wrap = $(this).siblings('.mmc-tags-wrap');
        var base  = $wrap.data('base');
        var tid   = nextId($wrap, 'data-tcounter');
        var html  = $('#tpl-tag').html();
        html = replaceAll(html, '__BASE__', base);
        html = replaceAll(html, '__TID__', tid);
        $wrap.append(html);
    });
    $(document).on('click', '.mmc-remove-tag', function() { $(this).closest('.mmc-tag-row').remove(); });

    // ---------- Recubrimientos globales ----------
    $(document).on('click', '.mmc-add-recubrimiento', function() {
        var $wrap = $('#mmc-recubrimientos-wrap');
        var rid   = nextId($wrap, 'data-counter');
        var num   = $wrap.children('.mmc-recubrimiento-card').length + 1;
        var newId = 'rc_' + Date.now() + '_' + Math.floor(Math.random()*1000);
        var html  = $('#tpl-recubrimiento').html();
        html = replaceAll(html, '__RID__', rid);
        html = replaceAll(html, '__NUM__', num);
        html = replaceAll(html, '__NEWID__', newId);
        $wrap.append(html);
    });
    $(document).on('click', '.mmc-remove-recubrimiento', function() {
        if (confirm('¿Eliminar este recubrimiento? Se quitará de los índices que lo tengan asignado al guardar.')) {
            $(this).closest('.mmc-recubrimiento-card').remove();
        }
    });

    // ---------- Agregar Color (protección o sub-protección) ----------
    $(document).on('click', '.mmc-add-color', function() {
        var $wrapBtn = $(this);
        var $card = $wrapBtn.closest('.mmc-subproteccion-card');
        var base, uid;
        if ($card.length) {
            var flujo = $card.data('flujo'), pid = $card.data('pid'), sid = $card.data('sid');
            base = 'protecciones[' + flujo + '][' + pid + '][sub_protecciones][' + sid + ']';
            uid  = flujo + '_' + pid + '_sub_' + sid;
        } else {
            $card = $wrapBtn.closest('.mmc-proteccion-card');
            var flujo2 = $card.data('flujo'), pid2 = $card.data('pid');
            base = 'protecciones[' + flujo2 + '][' + pid2 + ']';
            uid  = flujo2 + '_' + pid2;
        }
        var cid  = nextId($card, 'data-ccounter');
        var html = $('#tpl-color').html();
        html = replaceAll(html, '__BASE__', base);
        html = replaceAll(html, '__UID__', uid);
        html = replaceAll(html, '__CID__', cid);
        $wrapBtn.siblings('.mmc-colores-rows').append(html);
    });

    // ---------- Toggle: paso previo → mostrar Sub-Protecciones / ocultar Índices ----------
    $(document).on('change', '.mmc-paso-previo-checkbox', function() {
        var $card = $(this).closest('.mmc-proteccion-card');
        if ($(this).is(':checked')) {
            $card.find('.mmc-subprotecciones-wrap').first().show();
            $card.find('.mmc-indices-wrap-outer').first().hide();
        } else {
            $card.find('.mmc-subprotecciones-wrap').first().hide();
            $card.find('.mmc-indices-wrap-outer').first().show();
        }
    });

    // ---------- Toggle: fotocromático → mostrar colores ----------
    $(document).on('change', '.mmc-foto-checkbox', function() {
        $(this).closest('.mmc-toggle-row').next('.mmc-colores-wrap').toggle($(this).is(':checked'));
    });

    // ---------- Toggle: tipo de color (degradado muestra Color 2) ----------
    $(document).on('change', '.mmc-color-tipo-select', function() {
        $(this).closest('.mmc-color-row').find('.mmc-color-hex2').toggle($(this).val() === 'degradado');
    });

    // ---------- Eliminar ----------
    $(document).on('click', '.mmc-remove-proteccion', function() {
        if (confirm('¿Eliminar esta protección, sus sub-protecciones e índices?')) $(this).closest('.mmc-proteccion-card').remove();
    });
    $(document).on('click', '.mmc-remove-subproteccion', function() {
        if (confirm('¿Eliminar esta sub-protección y sus índices?')) $(this).closest('.mmc-subproteccion-card').remove();
    });
    $(document).on('click', '.mmc-remove-indice', function() { $(this).closest('.mmc-indice-row').remove(); });
    $(document).on('click', '.mmc-remove-color', function() { $(this).closest('.mmc-color-row').remove(); });
    
    
// =========================================================================
    // ÁRBOL + EDITOR (no mueve el DOM — muestra la cadena de ancestros activa)
    // =========================================================================
    function mmcTreeEditorInit() {
        var $root = $('#mmc-tree-root');
        $root.empty();

        $('.mmc-top-section, .mmc-proteccion-card, .mmc-subproteccion-card, .mmc-indice-row, .mmc-recubrimiento-card').addClass('mmc-editor-node');

        function addTreeNode($ul, label, $target) {
            var $li  = $('<li class="mmc-tree-item"></li>');
            var $row = $('<div class="mmc-tree-row"><span class="mmc-tree-toggle">▸</span><span class="mmc-tree-label"></span></div>');
            $row.data('target', $target);
            $row.find('.mmc-tree-label').text(label && label.length ? label : '(sin nombre)');
            $li.append($row);
            var $childUl = $('<ul class="mmc-tree-children"></ul>');
            $li.append($childUl);
            $ul.append($li);

            var $nombreInput = $target.find('input[name$="[nombre]"]').first();
            if ($nombreInput.length) {
                $nombreInput.on('input', function() {
                    $row.find('.mmc-tree-label').text($(this).val() || '(sin nombre)');
                });
            }
            return { $row: $row, $childUl: $childUl, $li: $li };
        }

        function procesarProtecciones($wrap, $parentUl) {
            $wrap.children('.mmc-proteccion-card').each(function() {
                var $card  = $(this);
                var nombre = $card.find('input[name$="[nombre]"]').first().val();
                var node   = addTreeNode($parentUl, nombre, $card);

                var $subLista = $card.find('.mmc-subprotecciones-lista').first();
                $subLista.children('.mmc-subproteccion-card').each(function() {
                    var $sub      = $(this);
                    var subNombre = $sub.find('input[name$="[nombre]"]').first().val();
                    var subNode   = addTreeNode(node.$childUl, subNombre, $sub);

                    $sub.find('.mmc-indices-wrap').first().children('.mmc-indice-row').each(function() {
                        var $idx      = $(this);
                        var idxNombre = $idx.find('input[name$="[nombre]"]').first().val();
                        addTreeNode(subNode.$childUl, idxNombre, $idx);
                    });
                });

                $card.find('.mmc-indices-wrap-outer').first().find('.mmc-indices-wrap').first().children('.mmc-indice-row').each(function() {
                    var $idx      = $(this);
                    var idxNombre = $idx.find('input[name$="[nombre]"]').first().val();
                    addTreeNode(node.$childUl, idxNombre, $idx);
                });
            });
        }

        var flujoDividerAgregado = false;

        $('.mmc-top-section').each(function() {
            var $section = $(this);
            var label    = $section.data('tree-label');

            if ($section.hasClass('mmc-flujo-section') && !flujoDividerAgregado) {
                $root.append('<li class="mmc-tree-divider"></li>');
                flujoDividerAgregado = true;
            }

            var node = addTreeNode($root, label, $section);

            if ($section.hasClass('mmc-flujo-section')) {
                var flujo = $section.data('flujo');
                var $wrap = $section.find('#mmc-protecciones-wrap-' + flujo);
                procesarProtecciones($wrap, node.$childUl);
            }

            if ($section.attr('id') === 'mmc-section-recubrimientos') {
                $section.find('.mmc-recubrimiento-card').each(function() {
                    var $rc    = $(this);
                    var nombre = $rc.find('input[name$="[nombre]"]').first().val();
                    addTreeNode(node.$childUl, nombre, $rc);
                });
            }
        });
    }

    // Activa un nodo Y toda su cadena de ancestros (para que sus "+" sigan visibles)
   // REEMPLAZAR POR:
    var $rutaTocados = $(); // elementos que forzamos a mostrar/ocultar manualmente, para poder revertirlos

    function mmcResetRuta() {
        // Devuelve cada elemento tocado a su estado natural (sin estilos inline nuestros)
        $rutaTocados.removeAttr('style');
        $rutaTocados = $();
        // Vuelve a sincronizar las secciones que dependen de checkboxes (paso previo / fotocromático)
        // para que no queden mal mostradas tras quitar nuestros estilos forzados.
        $('.mmc-paso-previo-checkbox').trigger('change');
        $('.mmc-foto-checkbox').trigger('change');
    }

    // Activa ÚNICAMENTE el nodo clickeado. Los contenedores intermedios (que no son
    // en sí un "nodo editable") se hacen transparentes solo para dejar pasar la vista
    // hasta el nodo activo — no se muestra ningún campo de los padres.
    function mmcActivarNodo($target, $row) {
        mmcResetRuta();

        $('.mmc-editor-node').removeClass('mmc-editor-active');
        $('.mmc-tree-row').removeClass('activo');
        $('#mmc-editor-placeholder').hide();

        var $current = $target;
        var $parent  = $current.parent();
        while ($parent.length && $parent.attr('id') !== 'mmc-editor-panel') {
            var $hermanos = $parent.children().not($current);
            $hermanos.hide();
            $rutaTocados = $rutaTocados.add($hermanos);

            $parent.show();
            $rutaTocados = $rutaTocados.add($parent);

            $current = $parent;
            $parent  = $current.parent();
        }

        $target.addClass('mmc-editor-active').show();
        $rutaTocados = $rutaTocados.add($target);

        $row.addClass('activo');

        // Expande visualmente el árbol hasta este nodo
        $row.parents('.mmc-tree-children').show();
        $row.parents('.mmc-tree-children').each(function() {
            $(this).prev('.mmc-tree-row').find('.mmc-tree-toggle').addClass('mmc-tree-toggle-open');
        });
    }

    // REEMPLAZAR POR:
    $(document).on('click', '.mmc-tree-row', function(e) {
        if ($(e.target).hasClass('mmc-tree-toggle')) return;

        var $row    = $(this);
        var $target = $row.data('target');
        if ($target && $target.length) mmcActivarNodo($target, $row);

        // Además de activar el editor, expande los hijos propios de este nodo (si tiene)
        var $childUl = $row.closest('li').children('.mmc-tree-children');
        if ($childUl.length && $childUl.children().length) {
            $childUl.show();
            $row.find('.mmc-tree-toggle').addClass('mmc-tree-toggle-open');
        }
    });

    $(document).on('click', '.mmc-tree-toggle', function(e) {
        e.stopPropagation();
        $(this).closest('li').find('> .mmc-tree-children').slideToggle(150);
        $(this).toggleClass('mmc-tree-toggle-open');
    });

    mmcTreeEditorInit();

    $(document).on('click', '.mmc-add-proteccion, .mmc-add-subproteccion, .mmc-add-indice, .mmc-add-recubrimiento, .mmc-remove-proteccion, .mmc-remove-subproteccion, .mmc-remove-indice, .mmc-remove-recubrimiento', function() {
        setTimeout(mmcTreeEditorInit, 60);
    });

});
</script>
<?php
}
