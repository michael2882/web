<?php
if ( ! defined('ABSPATH') ) exit;

add_action( 'wp_footer', 'mmc_construir_modal_optica' );
function mmc_construir_modal_optica() {
    if ( ! is_product() ) return;
    global $product;

    $imagen_url       = wp_get_attachment_image_url( $product->get_image_id(), 'large' );
    $paso1            = get_option('mmc_flujo_paso1', []);
    $progresivo       = get_option('mmc_flujo_progresivo', []);
    $prescripcion_cfg = get_option('mmc_flujo_prescripcion', []);

    $precio_base      = (float) $product->get_price();
    $precio_regular   = (float) $product->get_regular_price();
    $en_oferta        = $product->is_on_sale();

    $color_nombre = '';
    if ( $product->is_type('variable') ) {
        $terms = wc_get_product_terms( $product->get_id(), 'pa_color', ['fields' => 'names'] );
        if ( ! empty($terms) ) $color_nombre = $terms[0];
    }

    // Opciones de prescripción fijas
    // Solo 2 opciones activas por ahora — descomenta las otras cuando estén listas
    $opciones_prescripcion = [
        ['key' => 'online', 'titulo' => 'Completar en línea', 'desc' => 'Ingresa tu receta manualmente según tu prescripción impresa', 'icono' => 'rx-edit'],
        ['key' => 'imagen', 'titulo' => 'Subir imagen',       'desc' => 'Sube una foto de tu prescripción',                            'icono' => 'camera'],
        // Activar desde admin cuando estén disponibles:
        // ['key' => 'guardada', 'titulo' => 'Usar receta guardada', 'desc' => 'Usa una prescripción guardada anteriormente', 'icono' => 'rx-saved'],
        // ['key' => 'despues',  'titulo' => 'Enviar después',       'desc' => 'Puedes enviarla después del pago',            'icono' => 'mail'],
    ];

    $readers_values = ['+0.25','+0.50','+0.75','+1.00','+1.25','+1.50','+1.75','+2.00','+2.25','+2.50','+2.75','+3.00','+3.25','+3.50','+3.75','+4.00'];
    ?>
    <div id="mmc-modal-overlay">
    <div id="mmc-modal-inner">

        <!-- HEADER -->
        <div id="mmc-modal-header">
            <button id="mmc-btn-back">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                Volver al producto
            </button>
            <ul id="mmc-pasos-lista">
                <li class="mmc-paso mmc-paso-activo" data-paso="1"><span class="mmc-paso-num">1</span><span class="mmc-paso-txt">Uso</span></li>
                <li class="mmc-paso" data-paso="2"><span class="mmc-paso-num">2</span><span class="mmc-paso-txt">Prescripción</span></li>
                <li class="mmc-paso" data-paso="3"><span class="mmc-paso-num">3</span><span class="mmc-paso-txt">Lente</span></li>
                <li class="mmc-paso" data-paso="4"><span class="mmc-paso-num">4</span><span class="mmc-paso-txt">Resumen</span></li>
            </ul>
            <button id="mmc-cerrar-modal" aria-label="Cerrar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <!-- BODY -->
        <div id="mmc-modal-body">

            <!-- COLUMNA IZQUIERDA -->
            <div id="mmc-modal-col-izq">
                <div id="mmc-resumen-producto">
                    <img src="<?php echo esc_url($imagen_url); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" id="mmc-modal-img-lente">
                    <h3 class="mmc-modal-nombre"><?php echo esc_html($product->get_name()); ?></h3>
                    <?php if($color_nombre): ?>
                    <span class="mmc-modal-color"><?php echo esc_html($color_nombre); ?></span>
                    <?php endif; ?>
                </div>
                <div id="mmc-desglose-precio">
                    <div class="mmc-desglose-titulo">Desglose de precio</div>
                    <div class="mmc-desglose-row">
                        <span>Montura</span>
                        <?php if($en_oferta): ?>
                        <span><del class="mmc-precio-antes"><?php echo wc_price($precio_regular); ?></del> <strong class="mmc-precio-ahora"><?php echo wc_price($precio_base); ?></strong></span>
                        <?php else: ?>
                        <strong class="mmc-precio-ahora"><?php echo wc_price($precio_base); ?></strong>
                        <?php endif; ?>
                    </div>
                    <div id="mmc-selecciones-resumen"></div>
                    <div class="mmc-desglose-row mmc-desglose-total">
                        <span>Total</span>
                        <strong id="mmc-precio-total"><?php echo wc_price($precio_base); ?></strong>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA -->
            <div id="mmc-modal-col-der">

                <!-- ===================== PASO 1: USO ===================== -->
                <div class="mmc-pantalla" id="pantalla-uso">
                    <h2 class="mmc-titulo-paso">¿Para qué vas a usar tus lentes?</h2>
                    <p class="mmc-subtitulo-paso">Selecciona el tipo de uso para personalizar tus lunas.</p>
                    <div class="mmc-opciones-lista">
                        <?php foreach($paso1 as $i => $op):
                            if(empty($op['nombre'])) continue;
                            $tiene_precio  = !empty($op['precio_ahora']) && floatval($op['precio_ahora']) > 0;
                            $tiene_tachado = !empty($op['precio_antes']) && floatval($op['precio_antes']) > 0;
                            $tiene_badge   = !empty($op['badge']);
                            $tiene_tooltip = !empty($op['tooltip_texto']) || !empty($op['tooltip_img']);
                            $precio_num    = $tiene_precio ? floatval($op['precio_ahora']) : 0;
                            $precio_antes_num = $tiene_tachado ? floatval($op['precio_antes']) : 0;
                            $nombre_lower  = strtolower($op['nombre']);
                            $es_cercana    = strpos($nombre_lower, 'cercana') !== false || strpos($nombre_lower, 'near') !== false;
                        ?>
                        <div class="mmc-opcion-item <?php echo $es_cercana ? 'mmc-expandible' : ''; ?>"
                             data-accion="<?php echo $es_cercana ? 'vision-cercana-expand' : 'uso'; ?>"
                             data-valor="<?php echo esc_attr($op['nombre']); ?>"
                             data-precio="<?php echo $precio_num; ?>">

                            <?php if($tiene_badge): ?><span class="mmc-opcion-badge"><?php echo esc_html($op['badge']); ?></span><?php endif; ?>

                            <div class="mmc-expand-header">
                                <div class="mmc-opcion-left">
                                    <div class="mmc-opcion-textos">
                                        <strong class="mmc-opcion-nombre">
                                            <?php echo esc_html($op['nombre']); ?>
                                            <?php if($tiene_precio): ?>
                                            <span class="mmc-inline-precio">
                                                <?php if($tiene_tachado): ?><del>(+<?php echo wc_price($precio_antes_num); ?></del><?php endif; ?>
                                                +<?php echo wc_price($precio_num); ?><?php if($tiene_tachado): ?>)<?php endif; ?>
                                            </span>
                                            <?php endif; ?>
                                            <?php if($tiene_tooltip): ?>
                                            <button type="button" class="mmc-tooltip-btn" data-texto="<?php echo esc_attr($op['tooltip_texto']); ?>" data-img="<?php echo esc_attr($op['tooltip_img']); ?>">?</button>
                                            <?php endif; ?>
                                        </strong>
                                        <span class="mmc-opcion-desc"><?php echo esc_html($op['descripcion']); ?></span>
                                    </div>
                                </div>
                                <?php if(!empty($op['icono_url'])): ?>
                                <div class="mmc-opcion-icono"><img src="<?php echo esc_url($op['icono_url']); ?>" alt=""></div>
                                <?php endif; ?>
                            </div>

                            <?php if($es_cercana): ?>
                            <div class="mmc-expand-subopciones">
                                <button class="mmc-sub-opcion" data-sub="prescripcion">Ingresar mi prescripción</button>
                                <button class="mmc-sub-opcion" data-sub="readers">Para lentes de lectura: solo selecciona la potencia</button>
                            </div>
                            <?php endif; ?>

                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- ===================== PANTALLA: PRESCRIPCIÓN (4 opciones) ===================== -->
                <div class="mmc-pantalla" id="pantalla-prescripcion" style="display:none;">
                    <button class="mmc-btn-volver-pantalla" data-destino="uso">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                        Atrás
                    </button>
                    <h2 class="mmc-titulo-paso">¿Cómo quieres agregar tu prescripción?</h2>
                    <div class="mmc-opciones-lista">
                        <?php foreach($opciones_prescripcion as $op): ?>
                        <div class="mmc-opcion-item" data-accion="prescripcion" data-key="<?php echo esc_attr($op['key']); ?>">
                            <div class="mmc-opcion-left">
                                <div class="mmc-opcion-textos">
                                    <strong class="mmc-opcion-nombre"><?php echo esc_html($op['titulo']); ?></strong>
                                    <span class="mmc-opcion-desc"><?php echo esc_html($op['desc']); ?></span>
                                </div>
                            </div>
                            <div class="mmc-opcion-icono">
                                <?php echo mmc_get_prescripcion_icon($op['icono']); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- ===================== PANTALLA: VISIÓN CERCANA (expandible) ===================== -->
                <div class="mmc-pantalla" id="pantalla-vision-cercana" style="display:none;">
                    <button class="mmc-btn-volver-pantalla" data-destino="uso">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                        Atrás
                    </button>
                    <h2 class="mmc-titulo-paso">Visión Cercana — Lectura</h2>
                    <div class="mmc-opciones-lista">
                        <div class="mmc-opcion-item" data-accion="vision-cercana-sub" data-sub="prescripcion">
                            <div class="mmc-opcion-left">
                                <div class="mmc-opcion-textos">
                                    <strong class="mmc-opcion-nombre">Ingresar mi prescripción</strong>
                                    <span class="mmc-opcion-desc">Tengo mi receta médica</span>
                                </div>
                            </div>
                            <div class="mmc-opcion-icono"><?php echo mmc_get_prescripcion_icon('rx-edit'); ?></div>
                        </div>
                        <div class="mmc-opcion-item" data-accion="vision-cercana-sub" data-sub="readers">
                            <div class="mmc-opcion-left">
                                <div class="mmc-opcion-textos">
                                    <strong class="mmc-opcion-nombre">Lentes de lectura (Readers)</strong>
                                    <span class="mmc-opcion-desc">Selecciona solo la potencia de lente</span>
                                </div>
                            </div>
                            <div class="mmc-opcion-icono"><?php echo mmc_get_prescripcion_icon('rx-saved'); ?></div>
                        </div>
                    </div>
                </div>

                <!-- ===================== PANTALLA: READERS LENS POWER ===================== -->
                <div class="mmc-pantalla" id="pantalla-readers" style="display:none;">
                    <button class="mmc-btn-volver-pantalla" data-destino="vision-cercana">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                        Atrás
                    </button>
                    <h2 class="mmc-titulo-paso">Selecciona la potencia de tu lente</h2>
                    <p class="mmc-subtitulo-paso">Lentes de lectura con la misma graduación en ambos ojos.</p>
                    <div class="mmc-readers-grid">
                        <?php foreach($readers_values as $val): ?>
                        <button class="mmc-reader-btn" data-valor="<?php echo esc_attr($val); ?>"><?php echo esc_html($val); ?></button>
                        <?php endforeach; ?>
                    </div>
                    <button class="mmc-btn-continuar" id="btn-readers-continuar" disabled>Continuar</button>
                </div>

                <!-- ===================== PANTALLA: PROGRESIVO (Standard / Office) ===================== -->
                <div class="mmc-pantalla" id="pantalla-progresivo" style="display:none;">
                    <button class="mmc-btn-volver-pantalla" data-destino="uso">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                        Atrás
                    </button>
                    <h2 class="mmc-titulo-paso">Elige tu tipo de lente progresivo</h2>
                    <div class="mmc-opciones-lista">
                        <?php
                        $prog_default = [
                            'standard' => ['titulo' => 'Estándar', 'desc' => 'Progresión perfecta para todas las distancias. Ideal para conducir.', 'precio' => 0, 'advertencia' => '', 'icono_url' => ''],
                            'office'   => ['titulo' => 'Oficina',   'desc' => 'Diseñado para visión cercana e intermedia. No apto para conducir.',  'precio' => 0, 'advertencia' => 'No apto para conducir', 'icono_url' => ''],
                        ];
                        $prog = array_merge($prog_default, $progresivo ?: []);
                        foreach($prog as $key => $p):
                            $tiene_precio = !empty($p['precio']) && floatval($p['precio']) > 0;
                        ?>
                        <div class="mmc-opcion-item mmc-progresivo-card" data-accion="progresivo-tipo" data-key="<?php echo esc_attr($key); ?>" data-precio="<?php echo floatval($p['precio'] ?? 0); ?>">

                            <!-- Imagen circular + título debajo (izquierda) -->
                            <div class="mmc-progresivo-img-wrap">
                                <?php if(!empty($p['icono_url'])): ?>
                                <img src="<?php echo esc_url($p['icono_url']); ?>" alt="<?php echo esc_attr($p['titulo']); ?>">
                                <?php else: ?>
                                <div class="mmc-progresivo-img-placeholder">
                                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" opacity="0.3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12" stroke-dasharray="2 2"/><line x1="12" y1="14" x2="12" y2="16"/></svg>
                                </div>
                                <?php endif; ?>
                                <span class="mmc-progresivo-img-titulo">
                                    <?php echo esc_html($p['titulo']); ?>
                                    <?php if($tiene_precio): ?><span class="mmc-inline-precio">+<?php echo wc_price(floatval($p['precio'])); ?></span><?php endif; ?>
                                </span>
                            </div>

                            <!-- Descripción + advertencia (derecha) -->
                            <div class="mmc-progresivo-desc">
                                <?php echo esc_html($p['desc']); ?>
                                <?php if(!empty($p['advertencia'])): ?>
                                <div class="mmc-advertencia" style="margin-top:8px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    <?php echo esc_html($p['advertencia']); ?>
                                </div>
                                <?php endif; ?>
                            </div>

                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- ===================== PANTALLA: PAQUETES DE LENTE (PASO 3) ===================== -->
                <div class="mmc-pantalla" id="pantalla-paquetes" style="display:none;">
                    <button class="mmc-btn-volver-pantalla" id="btn-volver-paquetes">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                        Atrás
                    </button>
                    <h2 class="mmc-titulo-paso">Elige el paquete de lente</h2>
                    <div class="mmc-paquetes-lista" id="mmc-paquetes-lista">
                        <!-- Se llena dinámicamente con JS según el flujo activo -->
                    </div>
                </div>

                <!-- ===================== PANTALLA: TIPO DE LENTE (PASO 4) ===================== -->
                <div class="mmc-pantalla" id="pantalla-tipo-lente" style="display:none;">
                    <button class="mmc-btn-volver-pantalla" id="btn-volver-tipo-lente">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                        Atrás
                    </button>
                    <h2 class="mmc-titulo-paso">Tipo de lente</h2>
                    <div class="mmc-tipos-lista" id="mmc-tipos-lista">
                        <!-- Se llena dinámicamente con JS -->
                    </div>
                </div>

                <!-- ===================== PANTALLA: RESUMEN (PASO 4) ===================== -->
                <div class="mmc-pantalla" id="pantalla-paso4" style="display:none;">
                    <h2 class="mmc-titulo-paso">Resumen de tu pedido</h2>
                    <p class="mmc-subtitulo-paso">Este paso se configurará próximamente.</p>
                </div>


            </div>
        </div>
    </div>

    <!-- TOOLTIP FLOTANTE -->
    <div id="mmc-tooltip-box">
        <p id="mmc-tooltip-texto"></p>
        <img id="mmc-tooltip-img" src="" alt="" style="display:none;">
    </div>
    </div>

    <script>
        var mmcPrecioBase = <?php echo json_encode($precio_base); ?>;
        var mmcSymbol     = '<?php echo esc_js(get_woocommerce_currency_symbol()); ?>';
        var mmcPaquetes   = <?php
            $flujos = ['simple','cercana','progresivo','bifocal','sin_graduacion'];
            $paquetes_data = [];
            foreach($flujos as $flujo) {
                $paquetes_data[$flujo] = get_option('mmc_paquetes_' . $flujo, []);
            }
            echo json_encode($paquetes_data);
        ?>;
        var mmcTiposLente = <?php echo json_encode(get_option('mmc_tipos_lente', [])); ?>;
    </script>
    <?php
}

// Helper: íconos SVG para las opciones de prescripción
function mmc_get_prescripcion_icon($tipo) {
    $icons = [
        'rx-edit'  => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" opacity="0.4"><rect x="3" y="3" width="13" height="18" rx="2"/><path d="M8 7h5M8 11h5M8 15h3"/><path d="M17 13l4 4-4 4"/></svg>',
        'camera'   => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" opacity="0.4"><path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/><circle cx="12" cy="13" r="4"/><circle cx="19" cy="9" r="1" fill="currentColor"/></svg>',
        'rx-saved' => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" opacity="0.4"><rect x="3" y="3" width="13" height="18" rx="2"/><path d="M8 7h5M8 11h5M8 15h3"/><path d="M19 16v4m-2-2h4"/></svg>',
        'mail'     => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" opacity="0.4"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/><line x1="16" y1="17" x2="22" y2="17"/><line x1="19" y1="14" x2="19" y2="20"/></svg>',
    ];
    return $icons[$tipo] ?? '';
}