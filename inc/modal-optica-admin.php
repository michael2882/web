<?php
if ( ! defined('ABSPATH') ) exit;

add_action('admin_menu', 'mmc_modal_optica_menu');
function mmc_modal_optica_menu() {
    add_menu_page('Flujo de Lentes', 'Flujo de Lentes', 'manage_options', 'mmc_flujo_lentes', 'mmc_modal_optica_admin_page', 'dashicons-visibility', 56);
}

function mmc_flujo_save() {
    if (!isset($_POST['mmc_flujo_save']) || !current_user_can('manage_options')) return false;

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
    update_option('mmc_flujo_prescripcion', [
        'guardada_habilitado' => isset($_POST['prescripcion_guardada']) ? 1 : 0,
        'despues_habilitado'  => isset($_POST['prescripcion_despues'])  ? 1 : 0,
    ]);

    // Paquetes de lente por flujo
    $flujos_paquetes = ['simple','cercana','progresivo','bifocal','sin_graduacion'];
    foreach($flujos_paquetes as $flujo) {
        $raw = $_POST['paquetes'][$flujo] ?? [];
        $saved_paquetes = [];
        foreach($raw as $p) {
            if (empty($p['nombre'])) continue;
            $tags = [];
            foreach(($p['tags'] ?? []) as $t) {
                if (!empty($t['texto'])) {
                    $tags[] = [
                        'texto' => sanitize_text_field($t['texto']),
                        'color' => sanitize_text_field($t['color'] ?? 'mmc-tag-azul'),
                    ];
                }
            }
            $saved_paquetes[] = [
                'nombre'        => sanitize_text_field($p['nombre']),
                'precio_antes'  => floatval($p['precio_antes'] ?? 0),
                'precio_ahora'  => floatval($p['precio_ahora'] ?? 0),
                'imagen_url'    => esc_url_raw($p['imagen_url'] ?? ''),
                'descripcion'   => wp_kses_post($p['descripcion'] ?? ''),
                'tooltip_texto' => sanitize_textarea_field($p['tooltip_texto'] ?? ''),
                'tooltip_img'   => esc_url_raw($p['tooltip_img'] ?? ''),
                'tags'          => $tags,
            ];
        }
        update_option('mmc_paquetes_' . $flujo, $saved_paquetes);
    }

    // Tipos de lente (4 fijos, mismos para todos los flujos)
    $raw_tipos = $_POST['tipos_lente'] ?? [];
    $saved_tipos = [];
    foreach($raw_tipos as $t) {
        if (empty($t['nombre'])) continue;
        $saved_tipos[] = [
            'nombre'       => sanitize_text_field($t['nombre']),
            'precio_antes' => floatval($t['precio_antes'] ?? 0),
            'precio_ahora' => floatval($t['precio_ahora'] ?? 0),
            'descripcion'  => sanitize_text_field($t['descripcion'] ?? ''),
            'imagen_url'   => esc_url_raw($t['imagen_url'] ?? ''),
            'titulo_svg'   => esc_url_raw($t['titulo_svg'] ?? ''),
            'titulo_img'   => esc_url_raw($t['titulo_img'] ?? ''),
            'tooltip_texto'=> sanitize_textarea_field($t['tooltip_texto'] ?? ''),
            'tooltip_img'  => esc_url_raw($t['tooltip_img'] ?? ''),
        ];
    }
    update_option('mmc_tipos_lente', $saved_tipos);

    return true;
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
    $presc_cfg = get_option('mmc_flujo_prescripcion', ['guardada_habilitado'=>0,'despues_habilitado'=>0]);
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

        <?php if($saved): ?>
        <div class="mmc-saved">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            Configuración guardada correctamente.
        </div>
        <?php endif; ?>

        <form method="post">

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

            <!-- ===== PROGRESIVO: Standard / Office ===== -->
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

            <!-- ===== PRESCRIPCIÓN: Habilitar/deshabilitar ===== -->
            <p class="mmc-section-titulo">Opciones de prescripción — Habilitar</p>
            <div class="mmc-card">
                <div class="mmc-card-body">
                    <p style="font-size:13px;color:#64748b;margin:0 0 14px;">"Completar en línea" y "Subir imagen" están siempre habilitadas. Activa las siguientes si las tienes disponibles:</p>
                    <div class="mmc-toggle-row">
                        <input type="checkbox" id="prescripcion_guardada" name="prescripcion_guardada" <?php checked($presc_cfg['guardada_habilitado'], 1); ?>>
                        <label for="prescripcion_guardada">
                            <strong>Usar receta guardada</strong>
                            <span class="mmc-toggle-desc"> — El cliente puede usar una prescripción previamente guardada</span>
                        </label>
                    </div>
                    <div class="mmc-toggle-row">
                        <input type="checkbox" id="prescripcion_despues" name="prescripcion_despues" <?php checked($presc_cfg['despues_habilitado'], 1); ?>>
                        <label for="prescripcion_despues">
                            <strong>Enviar después</strong>
                            <span class="mmc-toggle-desc"> — El cliente puede enviar su receta después del pago</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- ===== PAQUETES DE LENTE POR FLUJO ===== -->
            <?php
            $flujos_config = [
                'simple'        => 'Visión Simple',
                'cercana'       => 'Visión Cercana',
                'progresivo'    => 'Progresivo',
                'bifocal'       => 'Bifocal',
                'sin_graduacion'=> 'Sin Graduación',
            ];
            $colores_opts = [
                'mmc-tag-azul'   => 'Azul',
                'mmc-tag-verde'  => 'Verde',
                'mmc-tag-naranja'=> 'Naranja',
                'mmc-tag-gris'   => 'Gris',
                'mmc-tag-morado' => 'Morado',
            ];
            foreach($flujos_config as $flujo_key => $flujo_nombre):
                $paquetes_flujo = get_option('mmc_paquetes_' . $flujo_key, []);
                // Asegurar siempre 6 slots
                while(count($paquetes_flujo) < 6) {
                    $paquetes_flujo[] = ['nombre'=>'','precio_antes'=>0,'precio_ahora'=>0,'imagen_url'=>'','descripcion'=>'','tooltip_texto'=>'','tooltip_img'=>'','tags'=>[]];
                }
            ?>
            <p class="mmc-section-titulo">Paquetes de lente — <?php echo $flujo_nombre; ?></p>

            <?php for($pi = 0; $pi < 6; $pi++):
                $p    = $paquetes_flujo[$pi] ?? [];
                $uid  = $flujo_key . '_' . $pi;
                $tags = $p['tags'] ?? [];
                while(count($tags) < 3) $tags[] = ['texto'=>'','color'=>'mmc-tag-azul'];
            ?>
            <div class="mmc-card mmc-paquete-admin-card">
                <div class="mmc-card-header">
                    <div class="mmc-card-num" style="background:#0891b2;"><?php echo $pi + 1; ?></div>
                    <p class="mmc-card-title">Paquete <?php echo $pi + 1; ?><?php if(!empty($p['nombre'])): ?> — <span style="color:#2563eb;"><?php echo esc_html($p['nombre']); ?></span><?php endif; ?></p>
                    <span style="font-size:11px;color:#94a3b8;margin-left:auto;">Dejar nombre vacío para ocultar esta opción</span>
                </div>
                <div class="mmc-card-body">

                    <!-- Fila 1: nombre, precio antes, precio ahora -->
                    <div class="mmc-grid-3">
                        <div class="mmc-field">
                            <label>Nombre del paquete</label>
                            <input type="text" name="paquetes[<?php echo $flujo_key;?>][<?php echo $pi;?>][nombre]" value="<?php echo esc_attr($p['nombre']??'');?>" placeholder="Ej: Estándar">
                        </div>
                        <div class="mmc-field">
                            <label>Precio antes (tachado)</label>
                            <input type="number" step="0.01" min="0" name="paquetes[<?php echo $flujo_key;?>][<?php echo $pi;?>][precio_antes]" value="<?php echo esc_attr($p['precio_antes']??0);?>" placeholder="0.00">
                            <p class="mmc-hint">0 = no mostrar tachado</p>
                        </div>
                        <div class="mmc-field">
                            <label>Precio adicional (0 = Gratis)</label>
                            <input type="number" step="0.01" min="0" name="paquetes[<?php echo $flujo_key;?>][<?php echo $pi;?>][precio_ahora]" value="<?php echo esc_attr($p['precio_ahora']??0);?>" placeholder="0.00">
                        </div>
                    </div>

                    <!-- Fila 2: imagen, descripción -->
                    <div class="mmc-grid-2">
                        <div class="mmc-field">
                            <label>Imagen del lente</label>
                            <div class="mmc-img-row">
                                <input type="text" name="paquetes[<?php echo $flujo_key;?>][<?php echo $pi;?>][imagen_url]" id="paq_img_<?php echo $uid;?>" value="<?php echo esc_attr($p['imagen_url']??'');?>" placeholder="URL de imagen">
                                <img class="mmc-img-preview <?php echo !empty($p['imagen_url'])?'visible':'';?>" src="<?php echo esc_url($p['imagen_url']??'');?>" id="paq_img_prev_<?php echo $uid;?>">
                                <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#paq_img_<?php echo $uid;?>" data-preview="#paq_img_prev_<?php echo $uid;?>">Subir</button>
                            </div>
                        </div>
                        <div class="mmc-field">
                            <label>Descripción (admite &lt;strong&gt; para negritas)</label>
                            <textarea name="paquetes[<?php echo $flujo_key;?>][<?php echo $pi;?>][descripcion]" placeholder="Ej: 1.5 Index Basic Lenses, &lt;strong&gt;Scratch Resistant&lt;/strong&gt;..."><?php echo esc_textarea($p['descripcion']??'');?></textarea>
                        </div>
                    </div>

                    <hr class="mmc-divider">

                    <!-- Tags de beneficios (hasta 3) -->
                    <p class="mmc-section-label">Tags de beneficios (hasta 3)</p>
                    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:14px;">
                        <?php for($ti = 0; $ti < 3; $ti++):
                            $tag = $tags[$ti] ?? ['texto'=>'','color'=>'mmc-tag-azul'];
                        ?>
                        <div style="display:flex; gap:6px; align-items:center; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:8px 10px;">
                            <div class="mmc-field" style="margin:0; min-width:140px;">
                                <label>Texto</label>
                                <input type="text" name="paquetes[<?php echo $flujo_key;?>][<?php echo $pi;?>][tags][<?php echo $ti;?>][texto]" value="<?php echo esc_attr($tag['texto']);?>" placeholder="Ej: Lens protection">
                            </div>
                            <div class="mmc-field" style="margin:0; min-width:110px;">
                                <label>Color</label>
                                <select name="paquetes[<?php echo $flujo_key;?>][<?php echo $pi;?>][tags][<?php echo $ti;?>][color]" style="height:36px;width:100%;padding:0 8px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;">
                                    <?php foreach($colores_opts as $cval => $clabel): ?>
                                    <option value="<?php echo $cval;?>" <?php selected($tag['color']??'mmc-tag-azul', $cval); ?>><?php echo $clabel; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>

                    <hr class="mmc-divider">

                    <!-- Tooltip -->
                    <p class="mmc-section-label">Tooltip (?)</p>
                    <div class="mmc-grid-2">
                        <div class="mmc-field">
                            <label>Texto del tooltip</label>
                            <textarea name="paquetes[<?php echo $flujo_key;?>][<?php echo $pi;?>][tooltip_texto]" placeholder="Descripción breve al hacer clic en ?"><?php echo esc_textarea($p['tooltip_texto']??'');?></textarea>
                        </div>
                        <div class="mmc-field">
                            <label>Imagen del tooltip</label>
                            <div class="mmc-img-row">
                                <input type="text" name="paquetes[<?php echo $flujo_key;?>][<?php echo $pi;?>][tooltip_img]" id="paq_tip_<?php echo $uid;?>" value="<?php echo esc_attr($p['tooltip_img']??'');?>" placeholder="URL">
                                <img class="mmc-img-preview <?php echo !empty($p['tooltip_img'])?'visible':'';?>" src="<?php echo esc_url($p['tooltip_img']??'');?>" id="paq_tip_prev_<?php echo $uid;?>">
                                <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#paq_tip_<?php echo $uid;?>" data-preview="#paq_tip_prev_<?php echo $uid;?>">Subir</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php endfor; ?>
            <?php endforeach; ?>

            <!-- ===== TIPOS DE LENTE (4 opciones, iguales para todos los flujos) ===== -->
            <p class="mmc-section-titulo">Tipos de lente — Paso 4</p>
            <p style="font-size:13px;color:#64748b;margin:-8px 0 16px;">Estas 4 opciones aparecen igual para todos los flujos. Para Progresivo Office solo se muestran las 2 primeras (Clear + Blue Light).</p>

            <?php
            $tipos_default = [
                ['nombre'=>'Clear',       'precio_antes'=>0,'precio_ahora'=>0,'descripcion'=>'Lentes para uso cotidiano',                              'imagen_url'=>'','titulo_svg'=>'','titulo_img'=>'','tooltip_texto'=>'','tooltip_img'=>''],
                ['nombre'=>'Blue Light',  'precio_antes'=>0,'precio_ahora'=>0,'descripcion'=>'Protege tus ojos de dispositivos digitales',             'imagen_url'=>'','titulo_svg'=>'','titulo_img'=>'','tooltip_texto'=>'','tooltip_img'=>''],
                ['nombre'=>'Transitions', 'precio_antes'=>0,'precio_ahora'=>0,'descripcion'=>'Se oscurecen al exterior, se aclaran al interior',       'imagen_url'=>'','titulo_svg'=>'','titulo_img'=>'','tooltip_texto'=>'','tooltip_img'=>''],
                ['nombre'=>'Sunglasses',  'precio_antes'=>0,'precio_ahora'=>0,'descripcion'=>'Tintados, espejados o polarizados',                      'imagen_url'=>'','titulo_svg'=>'','titulo_img'=>'','tooltip_texto'=>'','tooltip_img'=>''],
            ];
            $tipos_lente = get_option('mmc_tipos_lente', $tipos_default);
            while(count($tipos_lente) < 4) $tipos_lente[] = end($tipos_default);
            $tipo_labels = ['1 — Clear (siempre visible)', '2 — Blue Light (siempre visible)', '3 — Transitions / Photochromic', '4 — Sunglasses'];
            ?>

            <?php for($ti = 0; $ti < 4; $ti++):
                $t   = $tipos_lente[$ti] ?? [];
                $uid = 'tipo_' . $ti;
            ?>
            <div class="mmc-card">
                <div class="mmc-card-header">
                    <div class="mmc-card-num" style="background:#7c3aed;"><?php echo $ti+1; ?></div>
                    <p class="mmc-card-title"><?php echo $tipo_labels[$ti]; ?></p>
                    <?php if($ti < 2): ?><span style="font-size:11px;color:#16a34a;margin-left:auto;">✓ Siempre visible (incluso en Progresivo Office)</span><?php endif; ?>
                </div>
                <div class="mmc-card-body">

                    <div class="mmc-grid-3">
                        <div class="mmc-field">
                            <label>Nombre</label>
                            <input type="text" name="tipos_lente[<?php echo $ti;?>][nombre]" value="<?php echo esc_attr($t['nombre']??'');?>" placeholder="Ej: Clear">
                        </div>
                        <div class="mmc-field">
                            <label>Precio antes (tachado)</label>
                            <input type="number" step="0.01" min="0" name="tipos_lente[<?php echo $ti;?>][precio_antes]" value="<?php echo esc_attr($t['precio_antes']??0);?>" placeholder="0.00">
                        </div>
                        <div class="mmc-field">
                            <label>Precio adicional (0 = sin costo)</label>
                            <input type="number" step="0.01" min="0" name="tipos_lente[<?php echo $ti;?>][precio_ahora]" value="<?php echo esc_attr($t['precio_ahora']??0);?>" placeholder="0.00">
                        </div>
                    </div>

                    <div class="mmc-field" style="max-width:100%;margin-bottom:14px;">
                        <label>Descripción corta</label>
                        <input type="text" name="tipos_lente[<?php echo $ti;?>][descripcion]" value="<?php echo esc_attr($t['descripcion']??'');?>" placeholder="Ej: Lentes para uso cotidiano">
                    </div>

                    <hr class="mmc-divider">
                    <p class="mmc-section-label">Imagen del lente (derecha de la card)</p>
                    <div class="mmc-field" style="max-width:500px;margin-bottom:14px;">
                        <label>Imagen principal</label>
                        <div class="mmc-img-row">
                            <input type="text" name="tipos_lente[<?php echo $ti;?>][imagen_url]" id="tipo_img_<?php echo $uid;?>" value="<?php echo esc_attr($t['imagen_url']??'');?>" placeholder="URL">
                            <img class="mmc-img-preview <?php echo !empty($t['imagen_url'])?'visible':'';?>" src="<?php echo esc_url($t['imagen_url']??'');?>" id="tipo_img_prev_<?php echo $uid;?>">
                            <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#tipo_img_<?php echo $uid;?>" data-preview="#tipo_img_prev_<?php echo $uid;?>">Subir</button>
                        </div>
                    </div>

                    <hr class="mmc-divider">
                    <p class="mmc-section-label">Título especial (para Transitions — logo SVG + imagen Photochromic)</p>
                    <p class="mmc-hint" style="margin-bottom:12px;">Si rellenas estos campos, el título mostrará las imágenes en vez del texto. Dejar vacío para usar solo el nombre.</p>
                    <div class="mmc-grid-2">
                        <div class="mmc-field">
                            <label>Logo SVG / imagen izquierda del título</label>
                            <div class="mmc-img-row">
                                <input type="text" name="tipos_lente[<?php echo $ti;?>][titulo_svg]" id="tipo_svg_<?php echo $uid;?>" value="<?php echo esc_attr($t['titulo_svg']??'');?>" placeholder="URL del logo">
                                <img class="mmc-img-preview <?php echo !empty($t['titulo_svg'])?'visible':'';?>" src="<?php echo esc_url($t['titulo_svg']??'');?>" id="tipo_svg_prev_<?php echo $uid;?>">
                                <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#tipo_svg_<?php echo $uid;?>" data-preview="#tipo_svg_prev_<?php echo $uid;?>">Subir</button>
                            </div>
                        </div>
                        <div class="mmc-field">
                            <label>Imagen derecha del título (Photochromic)</label>
                            <div class="mmc-img-row">
                                <input type="text" name="tipos_lente[<?php echo $ti;?>][titulo_img]" id="tipo_timg_<?php echo $uid;?>" value="<?php echo esc_attr($t['titulo_img']??'');?>" placeholder="URL de la imagen">
                                <img class="mmc-img-preview <?php echo !empty($t['titulo_img'])?'visible':'';?>" src="<?php echo esc_url($t['titulo_img']??'');?>" id="tipo_timg_prev_<?php echo $uid;?>">
                                <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#tipo_timg_<?php echo $uid;?>" data-preview="#tipo_timg_prev_<?php echo $uid;?>">Subir</button>
                            </div>
                        </div>
                    </div>

                    <hr class="mmc-divider">
                    <p class="mmc-section-label">Tooltip (?)</p>
                    <div class="mmc-grid-2">
                        <div class="mmc-field">
                            <label>Texto del tooltip</label>
                            <textarea name="tipos_lente[<?php echo $ti;?>][tooltip_texto]" placeholder="Descripción que aparece al hacer clic en ?"><?php echo esc_textarea($t['tooltip_texto']??'');?></textarea>
                        </div>
                        <div class="mmc-field">
                            <label>Imagen del tooltip</label>
                            <div class="mmc-img-row">
                                <input type="text" name="tipos_lente[<?php echo $ti;?>][tooltip_img]" id="tipo_tip_<?php echo $uid;?>" value="<?php echo esc_attr($t['tooltip_img']??'');?>" placeholder="URL">
                                <img class="mmc-img-preview <?php echo !empty($t['tooltip_img'])?'visible':'';?>" src="<?php echo esc_url($t['tooltip_img']??'');?>" id="tipo_tip_prev_<?php echo $uid;?>">
                                <button type="button" class="mmc-btn-upload mmc-up-btn" data-target="#tipo_tip_<?php echo $uid;?>" data-preview="#tipo_tip_prev_<?php echo $uid;?>">Subir</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php endfor; ?>

            <!-- GUARDAR -->
            <div class="mmc-card">
                <div class="mmc-footer">
                    <span class="mmc-footer-note">Los cambios se aplican inmediatamente al guardar</span>
                    <button type="submit" name="mmc_flujo_save" class="mmc-btn-save">Guardar configuración</button>
                </div>
            </div>

        </form>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('.mmc-up-btn').on('click', function(e) {
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
    });
    </script>
    <?php
}