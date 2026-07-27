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
    // REEMPLAZAR POR:
    $prescripcion_cfg_correo = get_option('mmc_flujo_prescripcion', ['correo_receta' => '']);
    $opciones_prescripcion = [
        ['key' => 'online', 'titulo' => 'Completar en línea', 'desc' => 'Ingresa tu receta manualmente según tu prescripción impresa', 'icono' => 'rx-edit'],
        ['key' => 'imagen', 'titulo' => 'Subir imagen',       'desc' => 'Sube una foto de tu prescripción',                            'icono' => 'camera'],
        ['key' => 'correo', 'titulo' => 'Enviar por correo',  'desc' => 'Envíanos una foto de tu receta a nuestro correo',             'icono' => 'mail'],
        // Próximamente (requiere sistema de cuentas de usuario, aún no implementado):
        // ['key' => 'guardada', 'titulo' => 'Usar receta guardada', 'desc' => 'Usa una prescripción guardada anteriormente', 'icono' => 'rx-saved'],
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
                <!-- REEMPLAZAR POR: -->
                
                
                
                <div id="mmc-resumen-producto">
                    <div id="mmc-imagen-wrapper">
                        <img src="<?php echo esc_url($imagen_url); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" id="mmc-modal-img-lente">
                        <div id="mmc-lente-mascara-view">
                            <div id="mmc-lente-mascara-svg"></div>
                        </div>
                    </div>
                </div>
                
                <div id="mmc-desglose-precio">
                    <div class="mmc-desglose-row mmc-desglose-row-principal">
                        <span id="mmc-sidebar-nombre-color"><?php echo esc_html($product->get_name()); ?><?php echo $color_nombre ? ' (' . esc_html($color_nombre) . ')' : ''; ?></span>
                        <?php if($en_oferta): ?>
                        <span><del class="mmc-precio-antes"><?php echo wc_price($precio_regular); ?></del> <strong class="mmc-precio-ahora"><?php echo wc_price($precio_base); ?></strong></span>
                        <?php else: ?>
                        <strong class="mmc-precio-ahora"><?php echo wc_price($precio_base); ?></strong>
                        <?php endif; ?>
                    </div>

                    <div id="mmc-selecciones-resumen"></div>

                    <div class="mmc-desglose-row mmc-desglose-total">
                        <span>Subtotal</span>
                        <strong id="mmc-precio-total"><?php echo wc_price($precio_base); ?></strong>
                    </div>

                    <?php
                    $ajustes_sidebar = get_option('mmc_ajustes_sidebar', ['envio_gratis_monto' => 0, 'whatsapp_url' => '']);
                    $envio_monto     = floatval($ajustes_sidebar['envio_gratis_monto'] ?? 0);
                    $whatsapp_url    = $ajustes_sidebar['whatsapp_url'] ?? '';
                    ?>
                    <?php if ($envio_monto > 0): ?>
                    <p class="mmc-envio-gratis-msg">Envío estándar gratis en pedidos superiores a <?php echo wc_price($envio_monto); ?></p>
                    <?php endif; ?>

                    <div class="mmc-confianza-iconos">
                        <?php if ($whatsapp_url): ?>
                        <a href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener" class="mmc-confianza-item">
                            <span class="mmc-confianza-icono"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg></span>
                            <span class="mmc-confianza-texto"><strong>¿Necesitas ayuda?</strong><span>Iniciar un chat en vivo</span></span>
                        </a>
                        <?php else: ?>
                        <div class="mmc-confianza-item mmc-confianza-disabled">
                            <span class="mmc-confianza-icono"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg></span>
                            <span class="mmc-confianza-texto"><strong>¿Necesitas ayuda?</strong><span>Iniciar un chat en vivo</span></span>
                        </div>
                        <?php endif; ?>

                        <div class="mmc-confianza-item">
                            <span class="mmc-confianza-icono"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 1l4 4-4 4M3 11V9a4 4 0 0 1 4-4h14M7 23l-4-4 4-4M21 13v2a4 4 0 0 1-4 4H3"/></svg></span>
                            <span class="mmc-confianza-texto"><strong>Cambio y devolución</strong><span>en 60 días</span></span>
                        </div>

                        <div class="mmc-confianza-item">
                            <span class="mmc-confianza-icono"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span>
                            <span class="mmc-confianza-texto"><strong>Garantía</strong><span>de 365 días</span></span>
                        </div>
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
                             data-precio="<?php echo $precio_num; ?>"
                             data-precio-antes="<?php echo $precio_antes_num; ?>">

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

<!-- ===================== PANTALLA: PRESCRIPCIÓN ONLINE (FORMULARIO) ===================== -->
<div class="mmc-pantalla" id="pantalla-prescripcion-online" style="display:none;">
    <button class="mmc-btn-volver-pantalla" data-destino="prescripcion">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        Atrás
    </button>
    <h2 class="mmc-titulo-paso">Completa tu receta en línea</h2>

    <button type="button" id="mmc-como-leer-btn" class="mmc-como-leer-link">
        Cómo leer mi receta
        <span class="mmc-help-circle">?</span>
    </button>

    
    <div class="mmc-rx-extra-grid">
        <div class="mmc-field">
            <label>Nombre</label>
            <input type="text" class="mmc-rx-select" id="rx-nombre" placeholder="Nombre completo">
        </div>
        <div class="mmc-field">
            <label>Año de nacimiento</label>
            <select class="mmc-rx-select" id="rx-anio-nacimiento"></select>
        </div>
    </div>

    <!-- REEMPLAZAR POR: -->
<!-- REEMPLAZAR POR ESTA TABLA ÚNICA (4x4): -->
    <div class="mmc-rx-table-wrap">
        <table class="mmc-rx-table">
            <colgroup>
                <col class="mmc-col-label">
                <col class="mmc-col-data"><col class="mmc-col-data"><col class="mmc-col-data">
            </colgroup>
            <thead>
                <tr>
                    <th></th>
                    <th>Esfera (SPH)</th>
                    <th>Cilindro (CYL)</th>
                    <th>Eje (AXI)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th>Derecho (OD)</th>
                    <td><select class="mmc-rx-select" id="rx-od-sph"></select></td>
                    <td><select class="mmc-rx-select" id="rx-od-cyl"></select></td>
                    <td><select class="mmc-rx-select" id="rx-od-axi" disabled></select></td>
                </tr>
                <tr>
                    <th>Izquierdo (OS)</th>
                    <td><select class="mmc-rx-select" id="rx-os-sph"></select></td>
                    <td><select class="mmc-rx-select" id="rx-os-cyl"></select></td>
                    <td><select class="mmc-rx-select" id="rx-os-axi" disabled></select></td>
                </tr>
                <tr id="mmc-rx-pd-row">
                    <th>PD</th>
                    <td id="mmc-rx-pd-td2">
                        <select class="mmc-rx-select mmc-rx-pd-single-select" id="rx-pd-single"></select>
                        <div class="mmc-rx-pd-dual-cell" id="mmc-rx-pd-izq-cell" style="display:none;">
                            <span class="mmc-rx-pd-sublabel">Izquierda</span>
                            <select class="mmc-rx-select" id="rx-pd-izq"></select>
                        </div>
                    </td>
                    <td id="mmc-rx-pd-td3">
                        <div class="mmc-rx-pd-dual-cell" id="mmc-rx-pd-der-cell" style="display:none;">
                            <span class="mmc-rx-pd-sublabel">Derecha</span>
                            <select class="mmc-rx-select" id="rx-pd-der"></select>
                        </div>
                    </td>
                    <td id="mmc-rx-pd-td4"></td>
                </tr>
            </tbody>
        </table>
        <label class="mmc-rx-checkbox-label">
            <input type="checkbox" id="mmc-rx-2pd-check"> Tengo 2 números PD
        </label>
    </div>

    <div class="mmc-rx-comentarios">
        <label>Comentarios adicionales</label>
        <textarea id="rx-comentarios" placeholder="Agregar un comentario..."></textarea>
    </div>

    <button class="mmc-btn-continuar" id="btn-rx-online-continuar">Continuar</button>
</div>




<!-- ===================== PANTALLA: SUBIR IMAGEN DE RECETA ===================== -->
<div class="mmc-pantalla" id="pantalla-subir-imagen" style="display:none;">
    <button class="mmc-btn-volver-pantalla" data-destino="prescripcion">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        Atrás
    </button>
    <h2 class="mmc-titulo-paso">Upload image</h2>
    <p class="mmc-subtitulo-paso">You can easily upload your prescription file in any of the following formats: PDF, JPG, GIF, PNG, JPEG</p>

    <div id="mmc-rx-dropzone">
        <input type="file" id="mmc-rx-file-input" accept=".pdf,.jpg,.jpeg,.png,.gif" hidden>
        <div class="mmc-rx-dropzone-inner" id="mmc-rx-dropzone-inner">
            <span class="mmc-rx-dropzone-plus">+</span>
            <p>Drag and drop file or <u>click to upload</u></p>
            <span class="mmc-rx-dropzone-hint">(Max Size 5 MB)</span>
        </div>
        <div class="mmc-rx-dropzone-preview" id="mmc-rx-dropzone-preview" style="display:none;">
            <span id="mmc-rx-filename"></span>
            <button type="button" id="mmc-rx-quitar-archivo">&times;</button>
        </div>
    </div>
    <div class="mmc-rx-upload-status" id="mmc-rx-upload-status"></div>

    <div class="mmc-rx-comentarios">
        <label>Comentarios adicionales</label>
        <textarea id="rx-imagen-comentarios" placeholder="Agregar un comentario..."></textarea>
    </div>

    <button class="mmc-btn-continuar" id="btn-rx-imagen-continuar" disabled>Continuar</button>
</div>

<!-- ===================== PANTALLA: ENVIAR RECETA POR CORREO ===================== -->
<div class="mmc-pantalla" id="pantalla-correo" style="display:none;">
    <button class="mmc-btn-volver-pantalla" data-destino="prescripcion">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        Atrás
    </button>
    <div class="mmc-correo-box">
        <h2 class="mmc-titulo-paso" style="text-align:center;">Envíanos tu medida por correo</h2>
        <div class="mmc-correo-icono">
            <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/></svg>
        </div>
        <p class="mmc-correo-texto">
            Escríbenos a <strong><?php echo esc_html($prescripcion_cfg_correo['correo_receta'] ?? 'tu-correo@dominio.com'); ?></strong> adjuntando la foto de tu medida.
        </p>
        <button class="mmc-btn-continuar" id="btn-rx-correo-continuar">Continuar</button>
    </div>
</div>







<!-- ===================== MODAL: CÓMO LEER MI RECETA ===================== -->
<div id="mmc-como-leer-overlay">
    <div id="mmc-como-leer-modal">
        <button id="mmc-como-leer-cerrar" aria-label="Cerrar">×</button>

        <div id="mmc-como-leer-tabs">
            <h3>Aprende a leer tu receta médica.</h3>
            <button type="button" class="mmc-cl-tab active" data-tab="sph">Esfera (SPH)</button>
            <button type="button" class="mmc-cl-tab" data-tab="cyl">Cilindro (CYL)</button>
            <button type="button" class="mmc-cl-tab" data-tab="axi">Eje (AXI)</button>
            <button type="button" class="mmc-cl-tab" data-tab="pd">PD</button>
            <button type="button" class="mmc-cl-tab" data-tab="pd2">2 números PD</button>
        </div>

        <div id="mmc-como-leer-contenido">
            <h4 id="mmc-cl-titulo">Esfera (SPH)</h4>
            <p id="mmc-cl-texto"></p>

            <table class="mmc-cl-ejemplo">
                <thead>
                    <tr><th></th><th data-col="sph">Esfera</th><th data-col="cyl">Cilindro</th><th data-col="axi">Eje</th></tr>
                </thead>
                <tbody>
                    <tr><th>Derecha (OD)</th><td data-col="sph">+0,50</td><td data-col="cyl">-0,50</td><td data-col="axi">095</td></tr>
                    <tr><th>Izquierda (OS)</th><td data-col="sph">+0,75</td><td data-col="cyl">-0,50</td><td data-col="axi">085</td></tr>
                </tbody>
            </table>

            <table class="mmc-cl-ejemplo-pd" style="display:none;">
                <tbody><tr><th>PD</th><td>62</td></tr></tbody>
            </table>
        </div>
    </div>
</div>


<!-- AGREGAR después de la sección de mmc-como-leer-overlay: -->
<!-- ===================== MODAL: VER PRESCRIPCIÓN INGRESADA ===================== -->
<div id="mmc-ver-rx-overlay">
    <div id="mmc-ver-rx-modal">
        <button id="mmc-ver-rx-cerrar" aria-label="Cerrar">×</button>
        <h3>Tu receta</h3>
        <table class="mmc-rx-table" id="mmc-ver-rx-table">
            <thead>
                <tr><th></th><th>Esfera (SPH)</th><th>Cilindro (CYL)</th><th>Eje (AXI)</th></tr>
            </thead>
            <tbody>
                <tr><th>Derecho (OD)</th><td id="ver-rx-od-sph"></td><td id="ver-rx-od-cyl"></td><td id="ver-rx-od-axi"></td></tr>
                <tr><th>Izquierdo (OS)</th><td id="ver-rx-os-sph"></td><td id="ver-rx-os-cyl"></td><td id="ver-rx-os-axi"></td></tr>
            </tbody>
        </table>
        <div class="mmc-ver-rx-pd" id="mmc-ver-rx-pd-wrap"></div>
        <div class="mmc-ver-rx-comentarios" id="mmc-ver-rx-comentarios" style="display:none;">
            <strong>Comentarios:</strong>
            <p id="mmc-ver-rx-comentarios-texto"></p>
        </div>
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

<!-- REEMPLAZAR POR: -->
<!-- REEMPLAZAR POR: -->
<!-- ===================== PANTALLA: PROTECCIÓN (PASO 3) — diseño "tipo-card" ===================== -->
<div class="mmc-pantalla" id="pantalla-proteccion" style="display:none;">
    <button class="mmc-btn-volver-pantalla" id="btn-volver-proteccion">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        Atrás
    </button>
    <h2 class="mmc-titulo-paso">Elige la protección de tu lente</h2>
    <div class="mmc-tipos-lista" id="mmc-protecciones-lista"></div>
</div>

<!-- ===================== PANTALLA: SUB-PROTECCIÓN (PASO 3b) — mismo diseño ===================== -->
<div class="mmc-pantalla" id="pantalla-subproteccion" style="display:none;">
    <button class="mmc-btn-volver-pantalla" id="btn-volver-subproteccion">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        Atrás
    </button>
    <h2 class="mmc-titulo-paso">Elige una opción</h2>
    <div class="mmc-tipos-lista" id="mmc-subprotecciones-lista"></div>
</div>

<!-- ===================== PANTALLA: ÍNDICE (PASO 4) — diseño "paquete-card" ===================== -->
<div class="mmc-pantalla" id="pantalla-indice" style="display:none;">
    <button class="mmc-btn-volver-pantalla" id="btn-volver-indice">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        Atrás
    </button>
    <h2 class="mmc-titulo-paso">Elige el índice de tu lente</h2>
    <div class="mmc-paquetes-lista" id="mmc-indices-lista"></div>
</div>

                <!-- REEMPLAZAR POR: -->
                <!-- ===================== PANTALLA: RESUMEN FINAL (PASO 4) ===================== -->
                <div class="mmc-pantalla" id="pantalla-paso4" style="display:none;">
                    <button class="mmc-btn-volver-pantalla" id="btn-volver-paso4">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                        Atrás
                    </button>
                    <h2 class="mmc-titulo-paso">Revisa tu selección</h2>

                    <div class="mmc-review-box">

                        <!-- FRAME -->
                        <div class="mmc-review-row">
                            <div class="mmc-review-icon">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="15" r="4"/><circle cx="18" cy="15" r="4"/><path d="M10 15h4M2 15l2-9h1M22 15l-2-9h-1"/></svg>
                            </div>
                            <div class="mmc-review-content">
                                <strong class="mmc-review-titulo">Montura</strong>
                                
                                <span class="mmc-review-sub">
                                    <span id="mmc-review-frame-nombre-color"><?php echo esc_html($product->get_name()); ?><?php echo $color_nombre ? ', ' . esc_html($color_nombre) : ''; ?></span>
                                    (<?php if ($en_oferta): ?><del class="mmc-precio-antes"><?php echo wc_price($precio_regular); ?></del> <?php endif; ?><?php echo wc_price($precio_base); ?>)
                                </span>
                            </div>
                        </div>

                        <!-- LENS -->
                        <div class="mmc-review-row">
                            <div class="mmc-review-icon">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            </div>
                            <div class="mmc-review-content">
                                <div class="mmc-review-header-row">
                                    <strong class="mmc-review-titulo">Lente</strong>
                                    <button type="button" class="mmc-review-edit" data-destino="uso">Editar</button>
                                </div>
                                <div id="mmc-review-uso-linea" class="mmc-review-uso-linea"></div>
                                <div id="mmc-review-lentes-lista" class="mmc-review-detalle-lista"></div>
                            </div>
                        </div>

                        <!-- CUPÓN (solo diseño, sin lógica todavía) -->
                        <!-- REEMPLAZAR POR: -->
                        <!-- UPGRADES (Recubrimiento) -->
                        <div class="mmc-review-row" id="mmc-review-upgrades-row" style="display:none;">
                            <div class="mmc-review-icon">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                            </div>
                            <div class="mmc-review-content">
                                <div class="mmc-review-header-row">
                                    <strong class="mmc-review-titulo">Upgrades</strong>
                                    <button type="button" class="mmc-review-edit" data-destino="indice">Editar</button>
                                </div>
                                <div id="mmc-review-upgrades-lista" class="mmc-review-detalle-lista"></div>
                            </div>
                        </div>

                        <!-- CUPÓN (solo diseño, sin lógica todavía) -->
                        <div class="mmc-review-cupon-row">
                            <span class="mmc-review-cupon-label">¿Tienes un código promocional?</span>
                            <div class="mmc-review-cupon-input-wrap">
                                <input type="text" id="mmc-review-cupon-input" placeholder="Código">
                                <button type="button" id="mmc-review-cupon-btn" disabled>Aplicar</button>
                            </div>
                        </div>

                        <!-- TOTAL -->
                        <div class="mmc-review-total-wrap">
                            <div class="mmc-review-ahorro-linea" id="mmc-review-ahorro-linea" style="display:none;"></div>
                            <div class="mmc-review-total-row">
                                <span>Total</span>
                                <span class="mmc-review-total-precios">
                                    <del id="mmc-review-precio-regular" style="display:none;"></del>
                                    <strong id="mmc-review-precio-final"></strong>
                                </span>
                            </div>
                        </div>

                        <!-- BOTONES -->
                        <div class="mmc-review-botones">
                            <button type="button" id="mmc-btn-add-to-cart" class="mmc-btn-review-outline">Añadir al carrito</button>
                            <button type="button" id="mmc-btn-buy-now" class="mmc-btn-review-solido">Comprar ahora</button>
                        </div>

                        <!-- CONFIANZA -->
                        <div class="mmc-review-confianza">
                            <span><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> Garantía de devolución 100%</span>
                            <span><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="7" width="15" height="10" rx="1"/><path d="M16 10h4l3 3v4h-7z"/><circle cx="6" cy="19" r="2"/><circle cx="18" cy="19" r="2"/></svg> Envío y devoluciones gratis</span>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>

    <!-- TOOLTIP FLOTANTE -->
    <div id="mmc-tooltip-overlay">
    <div id="mmc-tooltip-box">
        <button id="mmc-tooltip-cerrar" aria-label="Cerrar">×</button>
        <p id="mmc-tooltip-texto"></p>
        <img id="mmc-tooltip-img" src="" alt="" style="display:none;">
    </div>
</div>
    </div>

    <script>
        var mmcPrecioBase = <?php echo json_encode($precio_base); ?>;
        var mmcSymbol     = '<?php echo esc_js(get_woocommerce_currency_symbol()); ?>';
// REEMPLAZAR POR:
var mmcProtecciones = <?php
    $flujos = ['simple','cercana','progresivo','bifocal','sin_graduacion'];
    $protecciones_data = [];
    foreach($flujos as $flujo) {
        $protecciones_data[$flujo] = get_option('mmc_protecciones_' . $flujo, []);
    }
    echo json_encode($protecciones_data);
?>;
// REEMPLAZAR POR:
var mmcRecubrimientos = <?php echo json_encode(get_option('mmc_recubrimientos', [])); ?>;
var mmcPrecioRegular   = <?php echo json_encode($precio_regular); ?>;
// REEMPLAZAR POR:
var mmcCheckoutUrl     = <?php echo json_encode( function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : '' ); ?>;
// REEMPLAZAR POR:
// REEMPLAZAR POR:
var mmcMascaraLunaUrl  = <?php echo json_encode( $ajustes_sidebar['mascara_luna_url'] ?? '' ); ?>;
var mmcProductoNombre  = <?php echo json_encode( $product->get_name() ); ?>;
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
