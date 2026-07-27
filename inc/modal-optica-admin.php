jQuery(document).ready(function($) {
    

    // =========================================================================
    // ESTADO GLOBAL
    // =========================================================================
var precioBase        = (typeof mmcPrecioBase !== 'undefined') ? mmcPrecioBase : 0;
var selecciones       = {};

var ORDEN_CLAVES = ['uso','tipo-lente','readers','prescripcion','prescripcion_valores','prescripcion_imagen','proteccion','color_proteccion','subproteccion','color_subproteccion','indice','recubrimiento'];

function limpiarDesde(clave) {
    var idx = ORDEN_CLAVES.indexOf(clave);
    if (idx === -1) return;
    for (var i = idx; i < ORDEN_CLAVES.length; i++) {
        delete selecciones[ORDEN_CLAVES[i]];
    }
    actualizarResumen();
}

var pasoHeader        = 1;
var historial         = [];
var flujoActivo         = 'simple';
var proteccionActiva    = null;
var subproteccionActiva = null;
var indicesActivos      = [];

    // =========================================================================
    // 1. ABRIR / CERRAR
    // =========================================================================
    // REEMPLAZAR POR:
    $(document).on('click', '#mmc-abrir-modal-btn', function(e) {
        e.preventDefault();
        sincronizarImagenYColorModal();
        resetModal();
        $('#mmc-modal-overlay').addClass('activo');
        $('body').css('overflow', 'hidden');
    });

    // REEMPLAZAR POR:
    function sincronizarImagenYColorModal() {
        // Prioridad 1: la imagen real de la variación seleccionada (siempre existe en WooCommerce, sin depender de galería personalizada)
        // Prioridad 2 (fallback): lo que esté actualmente en #mmc-main-view
        var imgFinal = window.mmcUltimaImagenVariacion || $('#mmc-main-view').attr('src');
        if (imgFinal) {
            $('#mmc-modal-img-lente').attr('src', imgFinal);
        }

        // Nombre + color en el Resumen final: toma el color que ya está activo en el selector de swatches
        // REEMPLAZAR POR:
        // Nombre + color en el Resumen final: prioridad a la variable capturada en el clic del swatch
        // REEMPLAZAR POR:
        // Nombre + color: prioridad a la variable capturada en el clic del swatch
        var colorActual = window.mmcColorSeleccionadoNombre || $('.mmc-color-name').first().text().trim();
        if (colorActual && colorActual.toLowerCase() !== 'selecciona un color') {
            var nombreBase = (typeof mmcProductoNombre !== 'undefined' ? mmcProductoNombre : '');

            // Resumen Final (Paso 4): "Nombre, Color"
            $('#mmc-review-frame-nombre-color').text(nombreBase + ', ' + colorActual);

            // Barra lateral izquierda (Pasos 1-3): "Nombre (Color)"
            $('#mmc-sidebar-nombre-color').text(nombreBase + ' (' + colorActual + ')');
        }
    }

    $(document).on('click', '#mmc-cerrar-modal', function(e) {
        e.preventDefault();
        cerrarModal();
    });

    $('#mmc-btn-back').on('click', function(e) {
        e.preventDefault();
        cerrarModal();
    });

    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') cerrarModal();
    });

    function cerrarModal() {
        $('#mmc-modal-overlay').removeClass('activo');
        $('body').css('overflow', '');
    }

// REEMPLAZAR POR:
    function resetModal() {
        selecciones = {};
        historial   = [];
        pasoHeader  = 1;
        flujoActivo = 'simple';
        proteccionActiva = null;
        subproteccionActiva = null;
        indicesActivos = [];
        ocultarBadgeColor();
        desactivarVistaMascara();
    mostrarPantalla('uso');
        actualizarHeader(1);
        actualizarResumen();
        $('.mmc-opcion-item').removeClass('seleccionado');
        $('.mmc-reader-btn').removeClass('seleccionado');
        $('#btn-readers-continuar').prop('disabled', true);
    }

    // =========================================================================
    // 2. MOSTRAR PANTALLA
    // =========================================================================
    // REEMPLAZAR POR:
    // REEMPLAZAR POR:
    function mostrarPantalla(id) {
        $('.mmc-pantalla').hide();
        $('#pantalla-' + id).show();
        $('#mmc-modal-col-der').scrollTop(0);
        $('#mmc-modal-col-izq').toggleClass('mmc-paso4-activo', id === 'paso4');
        if (id === 'paso4') renderizarResumenFinal();
        sincronizarVistaColorSegunPantalla(id);
    }

    function irAPantalla(id, pasoNum) {
        historial.push($('.mmc-pantalla:visible').attr('id').replace('pantalla-', ''));
        mostrarPantalla(id);
        if (pasoNum) {
            pasoHeader = pasoNum;
            actualizarHeader(pasoNum);
        }
    }

    // =========================================================================
    // 3. BOTÓN ATRÁS DENTRO DEL MODAL
    // =========================================================================
    $(document).on('click', '.mmc-btn-volver-pantalla', function() {
        var destino = $(this).data('destino');
        if (destino) {
            mostrarPantalla(destino);
        } else if (historial.length) {
            mostrarPantalla(historial.pop());
        }
    });

    // =========================================================================
    // 4. HEADER DE PASOS
    // =========================================================================
    function actualizarHeader(num) {
        $('.mmc-paso').each(function() {
            var p = parseInt($(this).data('paso'));
            $(this).removeClass('mmc-paso-activo mmc-paso-completo');
            if (p === num) $(this).addClass('mmc-paso-activo');
            else if (p < num) $(this).addClass('mmc-paso-completo');
        });
    }

    // =========================================================================
    // 5. CLICKS EN OPCIONES DE USO (PASO 1)
    // =========================================================================
    $(document).on('click', '.mmc-opcion-item[data-accion="uso"]', function(e) {
        if ($(e.target).closest('.mmc-tooltip-btn').length) return;

        var $item  = $(this);
        var valor  = $item.data('valor');
        var precio = parseFloat($item.data('precio')) || 0;

        $('.mmc-opcion-item[data-accion="uso"]').removeClass('seleccionado');
        $item.addClass('seleccionado');

        // REEMPLAZAR POR:
        var precioAntesUso = parseFloat($item.data('precio-antes')) || 0;
        limpiarDesde('tipo-lente');
        selecciones['uso'] = { label: valor, precio: precio, antes: precioAntesUso };
        actualizarResumen();

        setTimeout(function() {
            var valorLower = valor.toLowerCase();

            if (valorLower.includes('simple') || valorLower.includes('visión simple')) {
                irAPantalla('prescripcion', 2);
            } else if (valorLower.includes('progresivo') || valorLower.includes('progressive')) {
                irAPantalla('progresivo', 2);
            } else if (valorLower.includes('bifocal')) {
                irAPantalla('prescripcion', 2);
            } else if (valorLower.includes('sin') || valorLower.includes('moda') || valorLower.includes('graduación')) {
                flujoActivo = 'sin_graduacion';
                irAProtecciones('sin_graduacion', 'uso');
            } else {
                irAPantalla('prescripcion', 2);
            }
        }, 300);
    });

    // Near Vision — expandir inline sin cambiar de pantalla
    $(document).on('click', '.mmc-opcion-item[data-accion="vision-cercana-expand"]', function(e) {
        if ($(e.target).closest('.mmc-tooltip-btn').length) return;
        if ($(e.target).closest('.mmc-sub-opcion').length) return;

        var $item  = $(this);
        var valor  = $item.data('valor');
        var precio = parseFloat($item.data('precio')) || 0;

        // Si ya está expandido, contraer
        if ($item.hasClass('expandido')) {
            $item.removeClass('expandido seleccionado');
            return;
        }

        // Contraer otros y expandir este
        $('.mmc-expandible').removeClass('expandido seleccionado');
        $item.addClass('expandido seleccionado');

        // REEMPLAZAR POR:
        var precioAntesVC = parseFloat($item.data('precio-antes')) || 0;
        limpiarDesde('tipo-lente');
        selecciones['uso'] = { label: valor, precio: precio, antes: precioAntesVC };
        actualizarResumen();
    });

    // Sub-opciones de Near Vision
    $(document).on('click', '.mmc-sub-opcion', function(e) {
        e.stopPropagation();
        var sub = $(this).data('sub');
        if (sub === 'prescripcion') {
            irAPantalla('prescripcion', 2);
        } else if (sub === 'readers') {
            irAPantalla('readers', 2);
        }
    });

    // =========================================================================
    // 6. PRESCRIPCIÓN (4 opciones)
    // =========================================================================
    $(document).on('click', '.mmc-opcion-item[data-accion="prescripcion"]', function() {
    if ($(this).hasClass('mmc-opcion-disabled')) return;
    var key = $(this).data('key');
    // REEMPLAZAR POR:
    limpiarDesde('prescripcion_valores');
    selecciones['prescripcion'] = { label: $(this).find('.mmc-opcion-nombre').text().trim(), precio: 0 };
    actualizarResumen();

    var uso = (selecciones['uso'] ? selecciones['uso'].label : '').toLowerCase();
    var flujo = 'simple';
    if (uso.includes('cercana') || uso.includes('near')) flujo = 'cercana';
    else if (uso.includes('progresivo'))                  flujo = 'progresivo';
    else if (uso.includes('bifocal'))                     flujo = 'bifocal';
    else if (uso.includes('sin') || uso.includes('moda')) flujo = 'sin_graduacion';
    flujoActivo = flujo;

    // REEMPLAZAR POR:
    if (key === 'online') {
        setTimeout(function() { irAPantalla('prescripcion-online'); }, 300);
        return;
    }
    if (key === 'imagen') {
        setTimeout(function() { irAPantalla('subir-imagen'); }, 300);
        return;
    }
    if (key === 'correo') {
        setTimeout(function() { irAPantalla('correo'); }, 300);
        return;
    }

    setTimeout(function() { irAProtecciones(flujo, 'prescripcion'); }, 300);
});

    // =========================================================================
    // 8. READERS LENS POWER
    // =========================================================================
    $(document).on('click', '.mmc-reader-btn', function() {
        $('.mmc-reader-btn').removeClass('seleccionado');
        $(this).addClass('seleccionado');
        $('#btn-readers-continuar').prop('disabled', false);
    });

    $(document).on('click', '#btn-readers-continuar', function() {
        var val = $('.mmc-reader-btn.seleccionado').data('valor');
        if (!val) return;
        // REEMPLAZAR POR:
    limpiarDesde('prescripcion');
    selecciones['readers'] = { label: 'Readers ' + val, precio: 0 };
    actualizarResumen();
    flujoActivo = 'cercana';
    irAProtecciones('cercana', 'readers');
    
    });

    // =========================================================================
    // 9. PROGRESIVO — Standard / Office
    // =========================================================================
// REEMPLAZAR POR:
    $(document).on('click', '.mmc-opcion-item[data-accion="progresivo-tipo"]', function() {
        var $item  = $(this);
        var precio = parseFloat($item.data('precio')) || 0;
        var titulo = $item.find('.mmc-opcion-nombre').clone().children().remove().end().text().trim();

        limpiarDesde('prescripcion');
        selecciones['tipo-lente'] = { label: titulo, precio: precio };
        actualizarResumen();
        setTimeout(function() { irAPantalla('prescripcion', 2); }, 300);
    });

// REEMPLAZAR POR:
// BUSCAR Y ELIMINAR el bloque completo desde "irSinGraduacion" hasta el cierre del click en '.mmc-paquete-card' (el que agregamos en la ronda anterior para Protección).

// REEMPLAZAR POR:

    function irSinGraduacion() {
        flujoActivo = 'sin_graduacion';
        irAProtecciones('sin_graduacion', 'uso');
    }

    // Helper: genera el HTML de una card "nivel" (diseño tipo-card) — usado por Protección y Sub-Protección
    function htmlNivelCard(obj, idx) {
        var precioNum   = parseFloat(obj.precio_ahora) || 0;
        var precioAntes = parseFloat(obj.precio_antes) || 0;
        var tieneAntes  = precioAntes > 0;
        var esGratis    = precioNum === 0;

        var precioHtml = '';
        if (!esGratis) {
            precioHtml = '(From ';
            if (tieneAntes) precioHtml += '<del>+' + formatPrecio(precioAntes) + '</del> ';
            precioHtml += '+' + formatPrecio(precioNum) + ')';
        }

        var tooltipAttr = '';
        if (obj.tooltip_texto || obj.tooltip_img) {
            tooltipAttr = 'data-texto="' + (obj.tooltip_texto||'') + '" data-img="' + (obj.tooltip_img||'') + '"';
        }

// REEMPLAZAR POR:
        var tituloInner = '<strong>' + obj.nombre + '</strong>';
        if (precioHtml) tituloInner += ' <span class="mmc-tipo-precio">' + precioHtml + '</span>';
        if (tooltipAttr) tituloInner += ' <button class="mmc-tooltip-btn" ' + tooltipAttr + '>?</button>';

        var tagsHtml = '';
        if (obj.tags && obj.tags.length) {
            var colores = ['mmc-tag-azul','mmc-tag-verde','mmc-tag-naranja','mmc-tag-gris','mmc-tag-morado'];
            obj.tags.forEach(function(tag, tidx) {
                if (!tag.texto) return;
                var color = tag.color || colores[tidx % colores.length];
                tagsHtml += '<span class="mmc-paquete-tag ' + color + '">' + tag.texto + '</span>';
            });
        }

        var imgHtml = obj.imagen_url
            ? '<img class="mmc-tipo-img" src="' + obj.imagen_url + '" alt="' + obj.nombre + '">'
            : '';

        var esFoto = !!obj.es_fotocromatico;
        var tienePasoPrevio = !!obj.paso_previo;

        var html = '<div class="mmc-nivel-item" data-index="' + idx + '">';
        html += '<div class="mmc-tipo-card mmc-nivel-card" data-index="' + idx + '" data-nombre="' + obj.nombre + '" data-precio="' + precioNum + '" data-paso-previo="' + (tienePasoPrevio?1:0) + '" data-fotocromatico="' + (esFoto?1:0) + '">';
        html += '<div class="mmc-tipo-textos"><div class="mmc-tipo-titulo">' + tituloInner + '</div>';
        html += '<span class="mmc-tipo-desc">' + (obj.descripcion || '') + '</span>';
        if (tagsHtml) html += '<div class="mmc-tipo-tags">' + tagsHtml + '</div>';
        html += '</div>';
        html += imgHtml;
        html += '</div>';

        if (esFoto && obj.colores && obj.colores.length) {
            html += '<div class="mmc-color-selector-inline" data-index="' + idx + '" style="display:none;">';
            html += '<div class="mmc-color-selector-titulo">Color: <span class="mmc-color-nombre-actual">' + obj.colores[0].nombre + '</span></div>';
            html += '<div class="mmc-color-swatches">';
            obj.colores.forEach(function(c, ci) {
                var bg = c.imagen_url
                    ? 'background-image:url(' + c.imagen_url + ');'
                    : (c.tipo === 'degradado'
                        ? 'background:linear-gradient(135deg,' + c.hex1 + ',' + c.hex2 + ');'
                        : 'background-color:' + c.hex1 + ';');
                html += '<span class="mmc-color-swatch' + (ci === 0 ? ' activo' : '') + '" style="' + bg + '" data-nombre="' + c.nombre + '"></span>';
            });
            html += '</div>';
            html += '<button type="button" class="mmc-btn-confirmar-color">Confirmar</button>';
            html += '</div>';
        }
        html += '</div>';
        return html;
    }

    // Avanza al siguiente paso según el objeto de protección/sub-protección elegido
    function avanzarDesdeNivel(obj) {
        if (obj.paso_previo) {
            irASubProtecciones(obj);
        } else {
            indicesActivos = obj.indices || [];
            irAIndices();
        }
    }

    // =========================================================================
    // PROTECCIÓN (Nivel 1) — diseño tipo-card
    // =========================================================================
    function irAProtecciones(flujo, pasoAnterior) {
        flujoActivo = flujo;
        renderizarProtecciones(flujo);
        $('#btn-volver-proteccion').data('destino', pasoAnterior || 'prescripcion');
        irAPantalla('proteccion', 3);
    }
    
    // AGREGAR (junto a los demás $('#btn-volver-...').on('click', ...)):
    $('#btn-volver-paso4').on('click', function() {
        mostrarPantalla('indice');
        actualizarHeader(4);
    });

    $('#btn-volver-proteccion').on('click', function() {
        var destino = $(this).data('destino') || 'prescripcion';
        mostrarPantalla(destino);
        actualizarHeader(destino === 'uso' ? 1 : 2);
    });

    function renderizarProtecciones(flujo) {
        var protecciones = (typeof mmcProtecciones !== 'undefined' && mmcProtecciones[flujo]) ? mmcProtecciones[flujo] : [];
        var $lista = $('#mmc-protecciones-lista');
        $lista.empty();

        var tipoLente  = selecciones['tipo-lente'] ? selecciones['tipo-lente'].label.toLowerCase() : '';
        var soloOffice = tipoLente.includes('oficina') || tipoLente.includes('office');

        if (!protecciones.length) {
            $lista.html('<p style="color:#94a3b8;font-size:14px;">No hay protecciones configuradas para este flujo.</p>');
            return;
        }

        protecciones.forEach(function(p, idx) {
            if (!p.nombre) return;
            if (soloOffice && idx >= 2) return;
            $lista.append(htmlNivelCard(p, idx));
        });
    }

    // Click en Protección
    $(document).on('click', '#mmc-protecciones-lista .mmc-tipo-card', function(e) {
        if ($(e.target).closest('.mmc-tooltip-btn').length) return;
        manejarClickNivel($(this), function(idx) {
            return (mmcProtecciones[flujoActivo] || [])[idx];
        }, function(obj) {
            limpiarDesde('subproteccion');
            proteccionActiva = obj;
            selecciones['proteccion'] = { label: obj.nombre, precio: parseFloat(obj.precio_ahora) || 0, antes: parseFloat(obj.precio_antes) || 0, descripcion: obj.descripcion || '' };
            actualizarResumen();
            avanzarDesdeNivel(obj);
            
            
        }, 'color_proteccion');
    });

    // =========================================================================
    // SUB-PROTECCIÓN (Nivel 2) — mismo diseño tipo-card
    // =========================================================================
    function irASubProtecciones(protObj) {
        renderizarSubProtecciones(protObj);
        irAPantalla('subproteccion', 3);
    }

    $('#btn-volver-subproteccion').on('click', function() {
        mostrarPantalla('proteccion');
        actualizarHeader(3);
    });

    function renderizarSubProtecciones(protObj) {
        var subs = protObj.sub_protecciones || [];
        var $lista = $('#mmc-subprotecciones-lista');
        $lista.empty();

        if (!subs.length) {
            $lista.html('<p style="color:#94a3b8;font-size:14px;">No hay opciones configuradas para esta protección.</p>');
            return;
        }
        subs.forEach(function(sp, idx) { $lista.append(htmlNivelCard(sp, idx)); });
    }

    // Click en Sub-Protección
    $(document).on('click', '#mmc-subprotecciones-lista .mmc-tipo-card', function(e) {
        if ($(e.target).closest('.mmc-tooltip-btn').length) return;
        manejarClickNivel($(this), function(idx) {
            return (proteccionActiva.sub_protecciones || [])[idx];
        }, function(obj) {
            // REEMPLAZAR POR (en AMBAS apariciones):
            limpiarDesde('indice');
            subproteccionActiva = obj;
            selecciones['subproteccion'] = { label: obj.nombre, precio: parseFloat(obj.precio_ahora) || 0, antes: parseFloat(obj.precio_antes) || 0, descripcion: obj.descripcion || '' };
            actualizarResumen();
            indicesActivos = obj.indices || [];
            irAIndices();
        }, 'color_subproteccion');
    });

    // Lógica compartida de click: si es fotocromático expande el selector de color; si no, avanza directo
    function manejarClickNivel($card, getObjByIndex, onConfirmar, claveColor) {
        var idx = parseInt($card.data('index'));
        var obj = getObjByIndex(idx);
        if (!obj) return;

        var esFoto = ($card.data('fotocromatico') == 1 || $card.data('fotocromatico') === '1');
        var $item  = $card.closest('.mmc-nivel-item');
        var $colorBox = $item.find('.mmc-color-selector-inline');

        // REEMPLAZAR POR:
        $card.closest('.mmc-tipos-lista').find('.mmc-tipo-card').removeClass('seleccionado');
        $card.closest('.mmc-tipos-lista').find('.mmc-color-selector-inline').hide();
        $card.addClass('seleccionado');
        desactivarVistaMascara();

        // REEMPLAZAR POR:
        if (esFoto && $colorBox.length) {
            $colorBox.show();
            activarVistaMascara();
            var primerColor = (obj.colores && obj.colores[0]) ? obj.colores[0] : null;
            pintarColorMascara(primerColor);
            return; // espera al botón Confirmar
        }

        desactivarVistaMascara();

        setTimeout(function() { onConfirmar(obj); }, 350);

        // referencia para el handler de Confirmar (por si luego se usa un color por defecto)
        $card.data('__claveColor', claveColor);
    }
    
    
    // AGREGAR, junto a las demás funciones (por ejemplo después de manejarClickNivel):

// REEMPLAZAR POR:
    var MMC_TINT_OPACITY = 1; // opacidad fija de tinte por ahora — más adelante será ajustable por el usuario
    var mmcSvgUidCounter = 0;
   var mmcLensSvgConstruido = false;
   
    // Forma genérica de luna, autoría propia (no es un asset de terceros)
    // REEMPLAZAR POR:
    // Forma genérica de luna (más ancha que alta, con leve asimetría), autoría propia
    var MMC_LENS_PATH = 'M247.501 180.799c26.556-28.326 36.42-69.551 31.108-107.235-4.805-32.8785-33.637-51.8469-62.722-61.7105C161.005-8.37942 50.483-5.59739 23.4214 42.9617-4.90468 94.8086 49.9772 220 152.659 220c37.431-.253 60.446-10.116 82.45-27.82-1.265 1.011-2.529 1.77-3.794 2.782 5.817-4.3 11.381-8.852 16.186-14.163Z';

    function activarVistaMascara() {
        $('#mmc-modal-col-izq').addClass('mmc-mascara-activa');
    }

    function desactivarVistaMascara() {
        $('#mmc-modal-col-izq').removeClass('mmc-mascara-activa');
    }

    // Se construye UNA SOLA VEZ, con IDs de gradiente fijos.
    // Los clics posteriores solo cambian el stop-color, nunca se reconstruye el SVG.
// REEMPLAZAR POR:
function construirEstructuraSVGLuna() {
    if (mmcLensSvgConstruido) return;

    var svg = ''
        + '<svg viewBox="0 0 280 248" xmlns="http://www.w3.org/2000/svg">'
        +   '<g filter="url(#mmcLensBlur)">'
        +     '<path d="M239.497 200.799c26.556-28.326 36.419-69.551 31.108-107.235-4.805-32.8785-33.637-51.8469-62.722-61.7105C153.001 11.6206 42.4786 14.4026 15.417 62.9617-12.9091 114.809 41.9728 240 144.655 240c37.431-.253 60.446-10.116 82.449-27.82-1.264 1.011-2.529 1.77-3.793 2.782 5.817-4.3 11.381-8.852 16.186-14.163Z" fill="url(#mmcLensB)"></path>'
        +     '<path d="M239.497 200.799c26.556-28.326 36.419-69.551 31.108-107.235-4.805-32.8785-33.637-51.8469-62.722-61.7105C153.001 11.6206 42.4786 14.4026 15.417 62.9617-12.9091 114.809 41.9728 240 144.655 240c37.431-.253 60.446-10.116 82.449-27.82-1.264 1.011-2.529 1.77-3.793 2.782 5.817-4.3 11.381-8.852 16.186-14.163Z" fill="url(#mmcLensC)" fill-opacity=".5"></path>'
        +   '</g>'
        +   '<g>'
        +     '<path d="M247.501 180.799c26.556-28.326 36.42-69.551 31.108-107.235-4.805-32.8785-33.637-51.8469-62.722-61.7105C161.005-8.37942 50.483-5.59739 23.4214 42.9617-4.90468 94.8086 49.9772 220 152.659 220c37.431-.253 60.446-10.116 82.45-27.82-1.265 1.011-2.529 1.77-3.794 2.782 5.817-4.3 11.381-8.852 16.186-14.163Z" fill="url(#mmcLensD)"></path>'
        +     '<g style="opacity:0.4; mix-blend-mode: soft-light;">'
        +       '<path d="M247.501 180.799c26.556-28.326 36.42-69.551 31.108-107.235-4.805-32.8785-33.637-51.8469-62.722-61.7105C161.005-8.37942 50.483-5.59739 23.4214 42.9617-4.90468 94.8086 49.9772 220 152.659 220c37.431-.253 60.446-10.116 82.45-27.82-1.265 1.011-2.529 1.77-3.794 2.782 5.817-4.3 11.381-8.852 16.186-14.163Z" fill="url(#mmcLensE)"></path>'
        +     '</g>'
        +     '<path d="M278.61 73.5641c-4.806-32.8785-33.637-51.8469-62.722-61.7105C169.099-5.34437 81.5914-5.85019 40.1139 24.7521c-1.7704 1.5175-3.2879 2.7821-5.0582 4.0466C85.3851-8.63222 255.089-7.36766 270.77 79.1282c5.058 31.8668-2.53 87.7608-43.754 118.6158 0 0 2.023-1.518 4.046-3.035 1.265-1.012 2.529-1.77 3.541-2.782-1.264 1.012-2.529 1.77-3.794 2.782 5.817-4.047 11.381-8.599 16.44-13.91 26.808-28.326 36.672-69.551 31.361-107.2349Z" fill="#0F0F0F" style="opacity:.05"></path>'
        +     '<path d="M152.66 220c37.43-.253 60.445-10.117 82.449-27.82-17.957 13.404-42.237 22.003-74.609 22.003-106.9819 0-162.36963-136.3196-124.6858-183.6141 2.5291-2.7821 7.8403-7.8403 10.8752-10.1165-9.8635 6.0699-17.9567 13.4043-23.0149 22.5091C-4.90458 94.8085 49.9773 220 152.66 220Z" fill="url(#mmcLensF)" style="opacity:.4; mix-blend-mode: multiply;"></path>'
        +   '</g>'
        +   '<defs>'
        +     '<linearGradient id="mmcLensB" x1="170" y1="26" x2="170.163" y2="240" gradientUnits="userSpaceOnUse"><stop stop-color="#B0BDC5"></stop><stop offset="1" stop-color="#F1F1F1"></stop></linearGradient>'
        +     '<linearGradient id="mmcLensC" x1="228" y1="213" x2="50" y2="35" gradientUnits="userSpaceOnUse"><stop stop-color="#fff"></stop><stop offset="1" stop-color="#fff" stop-opacity="0"></stop></linearGradient>'
        +     '<linearGradient id="mmcLensD" x1="148" y1="0" x2="148" y2="220" gradientUnits="userSpaceOnUse"><stop id="mmcLensStop1" stop-color="#9aa0a6"></stop><stop id="mmcLensStop2" offset="1" stop-color="#9aa0a6"></stop></linearGradient>'
        +     '<linearGradient id="mmcLensE" x1="-197.874" y1="125.807" x2="19.3888" y2="381.685" gradientUnits="userSpaceOnUse"><stop stop-color="#fff" stop-opacity=".01"></stop><stop offset=".36355" stop-color="#fff" stop-opacity=".01"></stop><stop offset=".52154" stop-color="#fff" stop-opacity=".8"></stop><stop offset=".64179" stop-color="#fff" stop-opacity=".2"></stop><stop offset=".69663" stop-color="#fff" stop-opacity=".6"></stop><stop offset=".78233" stop-color="#fff" stop-opacity=".01"></stop><stop offset="1" stop-color="#fff" stop-opacity=".01"></stop></linearGradient>'
        +     '<linearGradient id="mmcLensF" x1="-27.359" y1="88.3447" x2="175.893" y2="234.487" gradientUnits="userSpaceOnUse"><stop stop-color="#fff"></stop><stop offset="1" stop-color="#0F0F0F"></stop></linearGradient>'
        +     '<filter id="mmcLensBlur" x="0" y="12" width="279.991" height="236" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB"><feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood><feBlend in="SourceGraphic" in2="BackgroundImageFix" result="shape"></feBlend><feGaussianBlur stdDeviation="4" result="effect1_foregroundBlur"></feGaussianBlur></filter>'
        +   '</defs>'
        + '</svg>';

    $('#mmc-lente-mascara-svg').html(svg);
    mmcLensSvgConstruido = true;
}

    function construirVistaMirror(color) {
        return '<div class="mmc-lens-mirror-wrap">' +
                    '<img class="mmc-lens-mirror-bg" src="' + color.imagen_url + '" alt="">' +
                    '<div class="mmc-lens-mirror-tint" style="background:' + (color.hex1 || '#888') + '; opacity:' + MMC_TINT_OPACITY + ';"></div>' +
               '</div>';
    }

    function pintarColorMascara(color) {
    console.log('=== pintarColorMascara ===', color);
    if (!color) return;

    if (color.imagen_url) {
        console.log('-> Rama MIRROR');
        mmcLensSvgConstruido = false;
        $('#mmc-lente-mascara-svg').html(construirVistaMirror(color));
        return;
    }
    
    

    console.log('-> Rama SVG normal, mmcLensSvgConstruido =', mmcLensSvgConstruido);
    construirEstructuraSVGLuna();
    var c1 = color.hex1 || '#9aa0a6';
    var c2 = (color.tipo === 'degradado' && color.hex2) ? color.hex2 : c1;
    console.log('Pintando con c1:', c1, 'c2:', c2);
    console.log('Elemento mmcLensStop1 existe?', $('#mmcLensStop1').length);

    $('#mmcLensStop1').attr('stop-color', c1);
    $('#mmcLensStop2').attr('stop-color', c2);
    $('#mmcLensMirrorStop1').attr('stop-color', c1);
    $('#mmcLensMirrorStop2').attr('stop-color', c2);
    $('#mmcLensStopB1').attr('stop-color', c1);
$('#mmcLensStopB2').attr('stop-color', '#F1F1F1'); // mantener el brillo claro
        
    }

    // Se llama SIEMPRE que se navega (avance o retroceso). Decide si mostrar la
    // luna con selector de color, el badge de color confirmado, o ninguno.
    // REEMPLAZAR POR:
    function sincronizarVistaColorSegunPantalla(id) {
        if (id === 'proteccion' || id === 'subproteccion') {
            ocultarBadgeColor();
            var listaId  = (id === 'proteccion') ? 'mmc-protecciones-lista' : 'mmc-subprotecciones-lista';
            var $expandido = $('#' + listaId + ' .mmc-color-selector-inline:visible');
            if ($expandido.length) {
                var idx = parseInt($expandido.data('index'));
                var listaObj = (id === 'proteccion')
                    ? (mmcProtecciones[flujoActivo] || [])[idx]
                    : ((proteccionActiva && proteccionActiva.sub_protecciones) || [])[idx];
                if (listaObj && listaObj.colores && listaObj.colores.length) {
                    activarVistaMascara();
                    var $activo = $expandido.find('.mmc-color-swatch.activo');
                    var nombreActivo = $activo.data('nombre') || listaObj.colores[0].nombre;
                    var colorObj = listaObj.colores.find(function(c) { return c.nombre === nombreActivo; }) || listaObj.colores[0];
                    pintarColorMascara(colorObj);
                    return;
                }
            }
            desactivarVistaMascara();
            return;
        }

        desactivarVistaMascara();

        // El badge SOLO tiene sentido en las pantallas que vienen después de elegir color
        var pantallasConBadge = ['indice', 'paso4'];
        if (pantallasConBadge.indexOf(id) !== -1) {
            mostrarBadgeColorSiAplica();
        } else {
            ocultarBadgeColor();
        }
    }

    // REEMPLAZAR POR:
    function construirBadgeColor(protLabel, colorObj) {
        var bg;
        if (colorObj.imagen_url) {
            bg = 'background-image:url(' + colorObj.imagen_url + ');';
        } else if (colorObj.tipo === 'degradado' && colorObj.hex2) {
            bg = 'background:linear-gradient(135deg,' + colorObj.hex1 + ',' + colorObj.hex2 + ');';
        } else {
            bg = 'background:' + (colorObj.hex1 || '#ccc') + ';';
        }
        return '<div class="mmc-color-badge" id="mmc-color-badge">' +
                    '<span class="mmc-color-badge-circulo" style="' + bg + '"></span>' +
                    '<div class="mmc-color-badge-texto">' +
                        '<span class="mmc-color-badge-proteccion">' + (protLabel || '') + '</span>' +
                        '<strong class="mmc-color-badge-color">' + (colorObj.nombre || '') + '</strong>' +
                    '</div>' +
               '</div>';
    }

    function mostrarBadgeColorSiAplica() {
        ocultarBadgeColor();

        var colorSel = selecciones['color_subproteccion'] || selecciones['color_proteccion'];
        if (!colorSel) return;

        var protLabel = (selecciones['subproteccion'] || selecciones['proteccion'] || {}).label || '';
        var fuente = (selecciones['color_subproteccion'] ? subproteccionActiva : proteccionActiva);
        if (!fuente || !fuente.colores) return;

        var colorObj = fuente.colores.find(function(c) { return c.nombre === colorSel.label; });
        if (!colorObj) return;

        // REEMPLAZAR POR:
        var $badge = $(construirBadgeColor(protLabel, colorObj));
        $('#mmc-imagen-wrapper').append($badge);
        setTimeout(function() { $badge.addClass('mmc-color-badge-expandido'); }, 150);
    }

    function ocultarBadgeColor() {
        $('#mmc-color-badge').remove();
    }
    

    // Click en un swatch de color
    // REEMPLAZAR POR:
    $(document).on('click', '.mmc-color-swatch', function() {
        $(this).siblings('.mmc-color-swatch').removeClass('activo');
        $(this).addClass('activo');
        $(this).closest('.mmc-color-selector-inline').find('.mmc-color-nombre-actual').text($(this).data('nombre'));

        var idx  = parseInt($(this).closest('.mmc-color-selector-inline').data('index'));
        var esSub = ($(this).closest('.mmc-tipos-lista').attr('id') === 'mmc-subprotecciones-lista');
        var obj = esSub ? (proteccionActiva.sub_protecciones || [])[idx] : (mmcProtecciones[flujoActivo] || [])[idx];
        if (obj && obj.colores) {
            var colorObj = obj.colores.find(function(c) { return c.nombre === $(this).data('nombre'); }.bind(this));
            if (colorObj) pintarColorMascara(colorObj);
        }
    });

    // Click en Confirmar color
    // REEMPLAZAR POR:
    $(document).on('click', '.mmc-btn-confirmar-color', function() {
        desactivarVistaMascara();
        var $box = $(this).closest('.mmc-color-selector-inline');
        var $item = $box.closest('.mmc-nivel-item');
        var $card = $item.find('.mmc-tipo-card');
        var idx = parseInt($card.data('index'));
        var colorNombre = $box.find('.mmc-color-swatch.activo').data('nombre') || '';

        var esSub = ($card.closest('.mmc-tipos-lista').attr('id') === 'mmc-subprotecciones-lista');
        var obj = esSub ? (proteccionActiva.sub_protecciones || [])[idx] : (mmcProtecciones[flujoActivo] || [])[idx];
        if (!obj) return;

        if (colorNombre) {
            selecciones[esSub ? 'color_subproteccion' : 'color_proteccion'] = { label: colorNombre, precio: 0 };
            actualizarResumen();
        }

        if (esSub) {
            subproteccionActiva = obj;
            selecciones['subproteccion'] = { label: obj.nombre, precio: parseFloat(obj.precio_ahora) || 0 };
            actualizarResumen();
            indicesActivos = obj.indices || [];
            irAIndices();
        } else {
            // REEMPLAZAR POR:
            limpiarDesde('subproteccion');
            proteccionActiva = obj;
            selecciones['proteccion'] = { label: obj.nombre, precio: parseFloat(obj.precio_ahora) || 0, antes: parseFloat(obj.precio_antes) || 0, descripcion: obj.descripcion || '' };
            actualizarResumen();
            avanzarDesdeNivel(obj);
        }
    });

// REEMPLAZAR POR:
// BUSCAR Y ELIMINAR el bloque completo de "ÍNDICES — renderizar y navegar" de la ronda anterior.

// REEMPLAZAR POR:

    // =========================================================================
    // ÍNDICES — diseño paquete-card (imagen grande + tags + descripción)
    // =========================================================================
    function irAIndices() {
        renderizarIndices();
        $('#btn-volver-indice').data('destino', proteccionActiva && proteccionActiva.paso_previo ? 'subproteccion' : 'proteccion');
        irAPantalla('indice', 4);
    }

    $('#btn-volver-indice').on('click', function() {
        var destino = $(this).data('destino') || 'proteccion';
        mostrarPantalla(destino);
        actualizarHeader(3);
    });

// BUSCAR Y ELIMINAR toda la función renderizarIndices() de la ronda anterior.

// REEMPLAZAR POR:
    function renderizarIndices() {
        var indices = indicesActivos || [];
        var $lista  = $('#mmc-indices-lista');
        $lista.empty();

        if (!indices.length) {
            $lista.html('<p style="color:#94a3b8;font-size:14px;">No hay índices configurados para esta selección.</p>');
            return;
        }

        indices.forEach(function(t, idx) {
            if (!t.nombre) return;

            var precioNum    = parseFloat(t.precio_ahora) || 0;
            var precioAntes  = parseFloat(t.precio_antes) || 0;
            var tieneAntes   = precioAntes > 0;
            var esGratis     = precioNum === 0;

            var precioHtml = '';
            if (esGratis) {
                precioHtml = '<span class="mmc-paquete-gratis">Gratis</span>';
            } else {
                if (tieneAntes) precioHtml = '<span class="mmc-paquete-precio-antes">+' + formatPrecio(precioAntes) + '</span>';
                precioHtml += '<span class="mmc-paquete-precio-ahora">+' + formatPrecio(precioNum) + '</span>';
            }

            var tagsHtml = '';
            if (t.tags && t.tags.length) {
                var colores = ['mmc-tag-azul','mmc-tag-verde','mmc-tag-naranja','mmc-tag-gris','mmc-tag-morado'];
                t.tags.forEach(function(tag, tidx) {
                    if (!tag.texto) return;
                    var color = tag.color || colores[tidx % colores.length];
                    tagsHtml += '<span class="mmc-paquete-tag ' + color + '">' + tag.texto + '</span>';
                });
            }

            var imgHtml = t.imagen_url
                ? '<img class="mmc-paquete-img" src="' + t.imagen_url + '" alt="' + t.nombre + '">'
                : '<div style="width:64px;height:48px;display:flex;align-items:center;justify-content:center;opacity:0.2;"><svg width="40" height="32" viewBox="0 0 40 32" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 16 Q14 6 20 16 Q26 26 32 16"/><path d="M12 16 Q18 8 24 16"/></svg></div>';

            var tooltipAttr = '';
            if (t.tooltip_texto || t.tooltip_img) {
                tooltipAttr = 'data-texto="' + (t.tooltip_texto || '') + '" data-img="' + (t.tooltip_img || '') + '"';
            }

            var html = '<div class="mmc-paquete-card" data-index="' + idx + '" data-precio="' + precioNum + '" data-nombre="' + t.nombre + '">';
            html += '<div class="mmc-paquete-izq">';
            html += imgHtml;
            html += '<span class="mmc-paquete-nombre">' + t.nombre + '</span>';
            html += '<div class="mmc-paquete-precio-wrap">' + precioHtml + '</div>';
            html += '</div>';
            if (tooltipAttr) html += '<button class="mmc-paquete-tooltip-btn mmc-tooltip-btn" ' + tooltipAttr + '>?</button>';
            html += '<div class="mmc-paquete-divider"></div>';
            html += '<div class="mmc-paquete-der">';
            if (tagsHtml) html += '<div class="mmc-paquete-tags">' + tagsHtml + '</div>';
            html += '<p class="mmc-paquete-desc">' + (t.descripcion || '') + '</p>';
            html += '</div></div>';

            $lista.append(html);
        });
    }

    // Construye el selector de recubrimiento a partir de los IDs habilitados en ese índice
    function construirSelectorRecubrimiento(indiceObj) {
        var ids = indiceObj.recubrimientos_ids || [];
        if (!ids.length) return null;

        var lista = ids.map(function(id) {
            return (mmcRecubrimientos || []).find(function(r) { return r.id === id; });
        }).filter(Boolean);
        if (!lista.length) return null;

        var html = '<div class="mmc-recub-selector-inline">';
        html += '<div class="mmc-recub-selector-titulo">Elige el recubrimiento (requerido)</div>';
        html += '<div class="mmc-recub-opciones">';
        lista.forEach(function(r) {
            var precioNum = parseFloat(r.precio_ahora) || 0;
            var precioTxt = (precioNum === 0) ? 'Gratis' : ('+' + formatPrecio(precioNum));
            var esRecom   = (indiceObj.recubrimiento_recomendado === r.id);
            var tooltipAttr = (r.tooltip_texto || r.tooltip_img) ? ('data-texto="' + (r.tooltip_texto||'') + '" data-img="' + (r.tooltip_img||'') + '"') : '';

            html += '<div class="mmc-recub-opcion' + (esRecom ? ' mmc-recub-recomendado' : '') + '" data-id="' + r.id + '" data-precio="' + precioNum + '" data-nombre="' + r.nombre + '">';
            if (esRecom) html += '<span class="mmc-recub-badge">Recomendado</span>';
            if (r.imagen_url) html += '<img class="mmc-recub-img" src="' + r.imagen_url + '">';
            html += '<div class="mmc-recub-textos"><strong>' + r.nombre + '</strong><span class="mmc-recub-precio">' + precioTxt + '</span></div>';
            if (tooltipAttr) html += '<button class="mmc-tooltip-btn" ' + tooltipAttr + '>?</button>';
            html += '</div>';
        });
        html += '</div>';
        html += '<button type="button" class="mmc-btn-confirmar-color mmc-btn-confirmar-recub" disabled>Confirmar</button>';
        html += '</div>';
        return html;
    }

    // Click en Índice — abre el selector de recubrimiento (obligatorio) en vez de avanzar directo
    $(document).on('click', '#mmc-indices-lista .mmc-paquete-card', function(e) {
        if ($(e.target).closest('.mmc-tooltip-btn').length) return;
        var $card  = $(this);
        var idx    = parseInt($card.data('index'));
        var nombre = $card.data('nombre');
        var precio = parseFloat($card.data('precio')) || 0;
        var obj    = (indicesActivos || [])[idx];

        $('#mmc-indices-lista .mmc-paquete-card').removeClass('seleccionado');
        $('.mmc-recub-selector-inline').remove();
        $card.addClass('seleccionado');

        var precioAntesIdx = obj ? (parseFloat(obj.precio_antes) || 0) : 0;
        selecciones['indice'] = { label: nombre, precio: precio, antes: precioAntesIdx };
        delete selecciones['recubrimiento'];
        actualizarResumen();

        if (!obj) { setTimeout(function() { irAPantalla('paso4', 4); }, 350); return; }

        var recubHtml = construirSelectorRecubrimiento(obj);
        if (recubHtml) {
            $card.after(recubHtml);
        } else {
            setTimeout(function() { irAPantalla('paso4', 4); }, 350);
        }
    });

    // Selección de un recubrimiento
    $(document).on('click', '.mmc-recub-opcion', function() {
        $(this).siblings('.mmc-recub-opcion').removeClass('activo');
        $(this).addClass('activo');
        $(this).closest('.mmc-recub-selector-inline').find('.mmc-btn-confirmar-recub').prop('disabled', false);
    });

    // Confirmar recubrimiento → suma precio y avanza
    $(document).on('click', '.mmc-btn-confirmar-recub', function(e) {
        if ($(e.target).closest('.mmc-tooltip-btn').length) return;
        var $sel = $(this).closest('.mmc-recub-selector-inline').find('.mmc-recub-opcion.activo');
        if (!$sel.length) return;

        // REEMPLAZAR POR:
        var recubId = $sel.data('id');
        var recubObj = (mmcRecubrimientos || []).find(function(r) { return r.id === recubId; });
        var antesRecub = recubObj ? (parseFloat(recubObj.precio_antes) || 0) : 0;
        selecciones['recubrimiento'] = { label: $sel.data('nombre'), precio: parseFloat($sel.data('precio')) || 0, antes: antesRecub };
        actualizarResumen();

        setTimeout(function() { irAPantalla('paso4', 4); }, 300);
    });


    
    // =========================================================================
var labelMap = {
        'uso':                'Tipo de uso',
        'tipo-lente':         'Tipo de lente',
        'prescripcion':       'Prescripción',
        'readers':            'Medida',
        'proteccion':         'Protección',
        'color_proteccion':   'Color',
        'subproteccion':      'Opción adicional',
        'color_subproteccion':'Color',
        'indice':             'Índice',
        'recubrimiento':      'Recubrimiento',
    };

    function formatPrecio(num) {
        return mmcSymbol + num.toFixed(2);
    }

// BUSCAR Y REEMPLAZAR la función completa actualizarResumen():

    function actualizarResumen() {
        var html        = '';
        var precioExtra = 0;

        function fila(label, valor, precio) {
            return { label: label, valor: valor, precio: precio || 0 };
        }

        function combinar(labelKey, colorKey, etiqueta) {
            var base  = selecciones[labelKey];
            var color = selecciones[colorKey];
            if (!base) return null;
            var valor = base.label;
            if (color) valor += ' - ' + color.label;
            return fila(etiqueta, valor, base.precio);
        }

        var filas = [];

        if (selecciones['uso'])          filas.push(fila('Tipo de uso', selecciones['uso'].label, selecciones['uso'].precio));
        if (selecciones['tipo-lente'])   filas.push(fila('Tipo de lente', selecciones['tipo-lente'].label, selecciones['tipo-lente'].precio));
        if (selecciones['readers'])      filas.push(fila('Medida', selecciones['readers'].label, selecciones['readers'].precio));
        if (selecciones['prescripcion']) filas.push(fila('Prescripción', selecciones['prescripcion'].label, selecciones['prescripcion'].precio));

        var protRow = combinar('proteccion', 'color_proteccion', 'Protección');
        if (protRow) filas.push(protRow);

        var subRow = combinar('subproteccion', 'color_subproteccion', 'Opción adicional');
        if (subRow) filas.push(subRow);

        if (selecciones['indice'])        filas.push(fila('Índice', selecciones['indice'].label, selecciones['indice'].precio));
        if (selecciones['recubrimiento']) filas.push(fila('Recubrimiento', selecciones['recubrimiento'].label, selecciones['recubrimiento'].precio));

        filas.forEach(function(f) {
            precioExtra += f.precio;
            html += '<div class="mmc-seleccion-item">';
            html += '<div class="mmc-seleccion-izq">';
            html += '<span class="mmc-seleccion-label">' + f.label + '</span>';
            html += '<span class="mmc-seleccion-valor">' + f.valor + '</span>';
            html += '</div>';
            if (f.precio > 0) {
                html += '<span class="mmc-seleccion-precio">+' + formatPrecio(f.precio) + '</span>';
            }
            html += '</div>';
        });

        $('#mmc-selecciones-resumen').html(html);
        var total = precioBase + precioExtra;
        $('#mmc-precio-total').text(formatPrecio(total));
    }

    // =========================================================================
    // 11. TOOLTIP
    // =========================================================================
$(document).on('click', '.mmc-tooltip-btn', function(e) {
    e.stopPropagation();
    var texto = $(this).data('texto') || '';
    var img   = $(this).data('img')   || '';

    $('#mmc-tooltip-texto').text(texto);
    img ? $('#mmc-tooltip-img').attr('src', img).show() : $('#mmc-tooltip-img').hide();

    $('#mmc-tooltip-overlay').addClass('visible');
});

$(document).on('click', '#mmc-tooltip-cerrar', function() {
    $('#mmc-tooltip-overlay').removeClass('visible');
});

$(document).on('click', '#mmc-tooltip-overlay', function(e) {
    if (e.target.id === 'mmc-tooltip-overlay') $(this).removeClass('visible');
});

$(document).on('keydown', function(e) {
    if (e.key === 'Escape') $('#mmc-tooltip-overlay').removeClass('visible');
});









// =========================================================================
// 12. PRESCRIPCIÓN ONLINE — SELECTS Y LÓGICA
// =========================================================================
function formatMedio(v) { return (v % 1 === 0) ? String(v) : v.toFixed(1); }

function llenarSelect($el, opciones) {
    var html = '';
    opciones.forEach(function(o) { html += '<option value="' + o.value + '">' + o.label + '</option>'; });
    $el.html(html);
}

function generarOpcionesEsfera() {
    var arr = [{ value: '', label: 'Elegir' }];
    for (var i = -36; i < 0; i++) { var v = (i * 0.25).toFixed(2); arr.push({ value: v, label: v }); }
    arr.push({ value: 'N', label: 'Neutro/N' });
    for (var j = 1; j <= 56; j++) { var v2 = (j * 0.25).toFixed(2); arr.push({ value: '+' + v2, label: '+' + v2 }); }
    return arr;
}

function generarOpcionesCilindro() {
    var arr = [{ value: '', label: 'Elegir' }, { value: 'N', label: 'Neutro/N' }];
    for (var i = 1; i <= 20; i++) { var v = (-(i * 0.25)).toFixed(2); arr.push({ value: v, label: v }); }
    return arr;
}

function generarOpcionesEje() {
    var arr = [{ value: '', label: 'Ninguno' }];
    for (var i = 1; i <= 180; i++) { var v = ('00' + i).slice(-3); arr.push({ value: v, label: v }); }
    return arr;
}

function generarOpcionesPDSingle() {
    var arr = [{ value: '', label: 'Elegir' }];
    for (var i = 40; i <= 80; i++) arr.push({ value: i, label: i });
    return arr;
}

function generarOpcionesPDDual() {
    var arr = [{ value: '', label: 'Elegir' }];
    for (var i = 40; i <= 80; i++) { var v = i * 0.5; arr.push({ value: formatMedio(v), label: formatMedio(v) }); }
    return arr;
}

function generarOpcionesAnio() {
    var arr = [{ value: '', label: 'Elegir' }];
    var actual = new Date().getFullYear();
    for (var y = actual - 5; y >= actual - 100; y--) arr.push({ value: y, label: y });
    return arr;
}

llenarSelect($('#rx-anio-nacimiento'), generarOpcionesAnio());
// Poblar todos los selects una sola vez
llenarSelect($('#rx-od-sph'), generarOpcionesEsfera());
llenarSelect($('#rx-os-sph'), generarOpcionesEsfera());
llenarSelect($('#rx-od-cyl'), generarOpcionesCilindro());
llenarSelect($('#rx-os-cyl'), generarOpcionesCilindro());
llenarSelect($('#rx-od-axi'), generarOpcionesEje());
llenarSelect($('#rx-os-axi'), generarOpcionesEje());
llenarSelect($('#rx-pd-single'), generarOpcionesPDSingle());
llenarSelect($('#rx-pd-izq'), generarOpcionesPDDual());
llenarSelect($('#rx-pd-der'), generarOpcionesPDDual());

// Eje se habilita solo si el Cilindro no está en Neutro/N ni vacío
function actualizarEstadoEje($cyl, $axi) {
    var deshabilitar = ($cyl.val() === 'N' || $cyl.val() === '');
    $axi.prop('disabled', deshabilitar);
    if (deshabilitar) $axi.val('');
}
$(document).on('change', '#rx-od-cyl', function() { actualizarEstadoEje($(this), $('#rx-od-axi')); });
$(document).on('change', '#rx-os-cyl', function() { actualizarEstadoEje($(this), $('#rx-os-axi')); });

// Toggle PD simple / PD doble
// REEMPLAZAR POR:
// REEMPLAZAR POR:
$(document).on('change', '#mmc-rx-2pd-check', function() {
    if ($(this).is(':checked')) {
        $('.mmc-rx-pd-single-select').hide();
        $('#mmc-rx-pd-izq-cell, #mmc-rx-pd-der-cell').show();
    } else {
        $('#mmc-rx-pd-izq-cell, #mmc-rx-pd-der-cell').hide();
        $('.mmc-rx-pd-single-select').show();
    }
});

// Continuar — guarda los valores (sin precio, sin línea en el resumen) y avanza
// REEMPLAZAR POR:
$(document).on('click', '#btn-rx-online-continuar', function() {
    delete selecciones['prescripcion_imagen'];
    selecciones['prescripcion_valores'] = {
        nombre: $('#rx-nombre').val(),
        anio_nacimiento: $('#rx-anio-nacimiento').val(),
        od_sph: $('#rx-od-sph').val(), os_sph: $('#rx-os-sph').val(),
        od_cyl: $('#rx-od-cyl').val(), os_cyl: $('#rx-os-cyl').val(),
        od_axi: $('#rx-od-axi').val(), os_axi: $('#rx-os-axi').val(),
        dos_pd: $('#mmc-rx-2pd-check').is(':checked'),
        pd_single: $('#rx-pd-single').val(),
        pd_izq: $('#rx-pd-izq').val(),
        pd_der: $('#rx-pd-der').val(),
        comentarios: $('#rx-comentarios').val()
    };
    irAProtecciones(flujoActivo, 'prescripcion-online');
});
// =========================================================================
// 13. MODAL "CÓMO LEER MI RECETA"
// =========================================================================
var mmcComoLeerContenido = {
    sph: { titulo: 'Esfera (SPH)', texto: 'La potencia de la lente para cada ojo. El signo (+) indica corrección para la visión de cerca (hipermetropía). El signo (-) indica corrección para la visión de lejos (miopía).', col: 'sph' },
    cyl: { titulo: 'Cilindro (CYL)', texto: 'Se refiere a la potencia de la lente utilizada para corregir el astigmatismo. Al igual que con SPH, el signo positivo se usa para corregir el astigmatismo de cerca y el signo negativo para corregir la visión de lejos.', col: 'cyl' },
    axi: { titulo: 'Eje (AXI)', texto: 'Si la receta incluye un valor de cilindro, también incluirá un eje, que indica la orientación (entre 1° y 180°) del astigmatismo.', col: 'axi' },
    pd:  { titulo: 'PD', texto: 'La distancia entre sus pupilas en milímetros. Si su receta no incluye la distancia interpupilar (DP), déjela en 62 para visión monofocal. Para lentes progresivas/bifocales, indique la DP de visión cercana cuando corresponda; de lo contrario, puede dejarla en 59.', col: 'pd' },
    pd2: { titulo: '2 números PD', texto: 'Si su distancia interpupilar (DP) tiene dos números (por ejemplo, 33/31), estos indican la distancia entre el centro de cada pupila y el puente de la nariz. El primer número corresponde al ojo izquierdo y el segundo al derecho. Por favor, complete la información a continuación.', col: 'pd' }
};

function mostrarTabComoLeer(key) {
    var data = mmcComoLeerContenido[key];
    if (!data) return;
    $('.mmc-cl-tab').removeClass('active');
    $('.mmc-cl-tab[data-tab="' + key + '"]').addClass('active');
    $('#mmc-cl-titulo').text(data.titulo);
    $('#mmc-cl-texto').text(data.texto);
    $('.mmc-cl-ejemplo [data-col]').removeClass('mmc-cl-highlight');
    $('.mmc-cl-ejemplo [data-col="' + data.col + '"]').addClass('mmc-cl-highlight');

    if (key === 'pd' || key === 'pd2') {
        $('.mmc-cl-ejemplo').hide();
        $('.mmc-cl-ejemplo-pd').show().find('td').addClass('mmc-cl-highlight');
    } else {
        $('.mmc-cl-ejemplo-pd').hide();
        $('.mmc-cl-ejemplo').show();
    }
}

$(document).on('click', '#mmc-como-leer-btn', function() {
    $('#mmc-como-leer-overlay').addClass('activo');
    mostrarTabComoLeer('sph');
});
$(document).on('click', '#mmc-como-leer-cerrar', function() {
    $('#mmc-como-leer-overlay').removeClass('activo');
});
$(document).on('click', '#mmc-como-leer-overlay', function(e) {
    if (e.target.id === 'mmc-como-leer-overlay') $(this).removeClass('activo');
});
$(document).on('click', '.mmc-cl-tab', function() {
    mostrarTabComoLeer($(this).data('tab'));
});





// =========================================================================
    // 14. RESUMEN FINAL (PASO 4)
    // =========================================================================
    function formatPrecioFinal(num) { return mmcSymbol + num.toFixed(2); }

// REEMPLAZAR POR:
    function htmlDetalleItem(label, precio, antes, descripcion) {
        precio = precio || 0; antes = antes || 0;
        var precioHtml;
        if (precio === 0) {
            precioHtml = '<span class="mmc-review-detalle-gratis">Gratis</span>';
        } else {
            precioHtml = '';
            if (antes > precio) precioHtml += '<del>' + formatPrecioFinal(antes) + '</del>';
            precioHtml += formatPrecioFinal(precio);
        }
        var html = '<div class="mmc-review-detalle-item"><span class="mmc-review-detalle-nombre">' + label + '</span><span class="mmc-review-detalle-precio">' + precioHtml + '</span></div>';
        if (descripcion) {
            html += '<div class="mmc-review-detalle-desc">' + descripcion + '</div>';
        }
        return html;
    }

    function renderizarResumenFinal() {
        console.log('[MMC] renderizarResumenFinal ejecutándose. selecciones =', selecciones);

        // --- Línea de uso + link Ver prescripción ---
        var usoLabel = selecciones['uso'] ? selecciones['uso'].label : '';
        var lineaUso = usoLabel;
        if (selecciones['prescripcion_valores']) {
            lineaUso += ' <button type="button" class="mmc-review-ver-rx">Ver prescripción</button>';
        }
        $('#mmc-review-uso-linea').html(lineaUso);

        // --- Lista de detalle: Protección / Color / Sub-Protección / Índice ---
        var $lista = $('#mmc-review-lentes-lista');
        $lista.empty();

        // REEMPLAZAR POR:
        var itemsCombinados = [
            { key: 'proteccion',    colorKey: 'color_proteccion' },
            { key: 'subproteccion', colorKey: 'color_subproteccion' },
            { key: 'indice',        colorKey: null }
        ];
        itemsCombinados.forEach(function(it) {
            var data = selecciones[it.key];
            if (!data) return;
            var label = data.label;
            if (it.colorKey && selecciones[it.colorKey]) label += ' - ' + selecciones[it.colorKey].label;
            $lista.append(htmlDetalleItem(label, data.precio || 0, data.antes || 0, data.descripcion || ''));
        });

        // --- Upgrades (Recubrimiento) — sección independiente ---
        var $upgrades = $('#mmc-review-upgrades-lista');
        $upgrades.empty();
        var recub = selecciones['recubrimiento'];
        if (recub) {
            $upgrades.append(htmlDetalleItem(recub.label, recub.precio || 0, recub.antes || 0));
            $('#mmc-review-upgrades-row').show();
        } else {
            $('#mmc-review-upgrades-row').hide();
        }

        // --- Total y ahorro acumulado ---
        var totalFinal   = precioBase;
        var totalRegular = (typeof mmcPrecioRegular !== 'undefined' && mmcPrecioRegular > 0) ? mmcPrecioRegular : precioBase;

        Object.keys(selecciones).forEach(function(key) {
            if (key === 'prescripcion_valores') return;
            var d = selecciones[key];
            if (!d || typeof d.precio === 'undefined') return;
            totalFinal   += d.precio || 0;
            totalRegular += (d.antes && d.antes > d.precio) ? d.antes : (d.precio || 0);
        });

        var ahorro = totalRegular - totalFinal;

        $('#mmc-review-precio-final').text(formatPrecioFinal(totalFinal));
        if (ahorro > 0.009) {
            $('#mmc-review-precio-regular').text(formatPrecioFinal(totalRegular)).show();
            $('#mmc-review-ahorro-linea').text('Ahorras ' + formatPrecioFinal(ahorro)).show();
        } else {
            $('#mmc-review-precio-regular').hide();
            $('#mmc-review-ahorro-linea').hide();
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    

    // Editar (volver al inicio del flujo de lentes)
    $(document).on('click', '.mmc-review-edit', function() {
        var destino = $(this).data('destino') || 'uso';
        mostrarPantalla(destino);
        actualizarHeader(1);
    });

    // =========================================================================
    // 15. MODAL "VER PRESCRIPCIÓN"
    // =========================================================================
    $(document).on('click', '.mmc-review-ver-rx', function() {
        var v = selecciones['prescripcion_valores'];
        if (!v) return;

        $('#ver-rx-od-sph').text(v.od_sph || '—');
        $('#ver-rx-os-sph').text(v.os_sph || '—');
        $('#ver-rx-od-cyl').text(v.od_cyl || '—');
        $('#ver-rx-os-cyl').text(v.os_cyl || '—');
        $('#ver-rx-od-axi').text(v.od_axi || '—');
        $('#ver-rx-os-axi').text(v.os_axi || '—');

        var pdHtml = '';
        if (v.dos_pd) {
            pdHtml = '<span><strong>PD Izquierda:</strong> ' + (v.pd_izq || '—') + '</span><span><strong>PD Derecha:</strong> ' + (v.pd_der || '—') + '</span>';
        } else {
            pdHtml = '<span><strong>PD:</strong> ' + (v.pd_single || '—') + '</span>';
        }
        $('#mmc-ver-rx-pd-wrap').html(pdHtml);

        if (v.comentarios) {
            $('#mmc-ver-rx-comentarios-texto').text(v.comentarios);
            $('#mmc-ver-rx-comentarios').show();
        } else {
            $('#mmc-ver-rx-comentarios').hide();
        }

        $('#mmc-ver-rx-overlay').addClass('activo');
    });
    $(document).on('click', '#mmc-ver-rx-cerrar', function() { $('#mmc-ver-rx-overlay').removeClass('activo'); });
    $(document).on('click', '#mmc-ver-rx-overlay', function(e) { if (e.target.id === 'mmc-ver-rx-overlay') $(this).removeClass('activo'); });

    // =========================================================================
    // 16. BOTONES ADD TO CART / BUY NOW (funcionalidad básica, sin metadatos aún)
    // =========================================================================
    // REEMPLAZAR POR:
    function prepararEnvioFlujo() {
        var $form = $('form.cart');
        $form.find('input[name="mmc_flujo_lentes"]').remove();
        var $input = $('<input>', { type: 'hidden', name: 'mmc_flujo_lentes' });
        $input.val(JSON.stringify(selecciones));
        $form.append($input);
    }

    $(document).on('click', '#mmc-btn-add-to-cart', function() {
        prepararEnvioFlujo();
        $('form.cart .single_add_to_cart_button').trigger('click');
    });
    $(document).on('click', '#mmc-btn-buy-now', function() {
        prepararEnvioFlujo();
        $('form.cart .single_add_to_cart_button').trigger('click');
        setTimeout(function() {
            if (mmcCheckoutUrl) window.location.href = mmcCheckoutUrl;
        }, 600);
    });


// =========================================================================
    // 17. SUBIR IMAGEN DE RECETA (subida real vía AJAX a WordPress)
    // =========================================================================
    var archivoRxSeleccionado = null;
    var archivoRxSubido = null;

    function validarArchivoRx(file) {
        var permitidos = ['pdf','jpg','jpeg','png','gif'];
        var ext = file.name.split('.').pop().toLowerCase();
        if (permitidos.indexOf(ext) === -1) { alert('Formato no permitido. Usa PDF, JPG, GIF o PNG.'); return false; }
        if (file.size > 5 * 1024 * 1024) { alert('El archivo supera los 5MB.'); return false; }
        return true;
    }

    function mostrarArchivoRx(file) {
        archivoRxSeleccionado = file;
        archivoRxSubido = null;
        $('#mmc-rx-dropzone-inner').hide();
        $('#mmc-rx-filename').text(file.name);
        $('#mmc-rx-dropzone-preview').show();
        $('#btn-rx-imagen-continuar').prop('disabled', true);
        $('#mmc-rx-upload-status').text('');
        subirArchivoRx(file);
    }

    function subirArchivoRx(file) {
        if (typeof mmcAjax === 'undefined') { $('#mmc-rx-upload-status').text('Error de configuración: falta mmcAjax.'); return; }

        var formData = new FormData();
        formData.append('action', 'mmc_subir_receta');
        formData.append('nonce', mmcAjax.nonce);
        formData.append('archivo', file);

        $('#mmc-rx-upload-status').text('Subiendo...');
        $('#btn-rx-imagen-continuar').prop('disabled', true);

        $.ajax({
            url: mmcAjax.url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(resp) {
                if (resp.success) {
                    archivoRxSubido = resp.data;
                    $('#mmc-rx-upload-status').text('Archivo subido correctamente.');
                    $('#btn-rx-imagen-continuar').prop('disabled', false);
                } else {
                    $('#mmc-rx-upload-status').text(resp.data && resp.data.message ? resp.data.message : 'Error al subir el archivo.');
                }
            },
            error: function() {
                $('#mmc-rx-upload-status').text('Error de conexión al subir el archivo.');
            }
        });
    }

    $(document).on('click', '#mmc-rx-dropzone-inner', function() { $('#mmc-rx-file-input').trigger('click'); });
    $(document).on('change', '#mmc-rx-file-input', function() {
        var file = this.files[0];
        if (file && validarArchivoRx(file)) mostrarArchivoRx(file);
    });

    $(document).on('dragover', '#mmc-rx-dropzone', function(e) { e.preventDefault(); $(this).addClass('mmc-rx-dragover'); });
    $(document).on('dragleave', '#mmc-rx-dropzone', function(e) { e.preventDefault(); $(this).removeClass('mmc-rx-dragover'); });
    $(document).on('drop', '#mmc-rx-dropzone', function(e) {
        e.preventDefault();
        $(this).removeClass('mmc-rx-dragover');
        var file = e.originalEvent.dataTransfer.files[0];
        if (file && validarArchivoRx(file)) mostrarArchivoRx(file);
    });

    $(document).on('click', '#mmc-rx-quitar-archivo', function(e) {
        e.stopPropagation();
        archivoRxSeleccionado = null;
        archivoRxSubido = null;
        $('#mmc-rx-file-input').val('');
        $('#mmc-rx-dropzone-preview').hide();
        $('#mmc-rx-dropzone-inner').show();
        $('#mmc-rx-upload-status').text('');
        $('#btn-rx-imagen-continuar').prop('disabled', true);
    });

    $(document).on('click', '#btn-rx-imagen-continuar', function() {
        if (!archivoRxSubido) return;
        delete selecciones['prescripcion_valores'];
        selecciones['prescripcion_imagen'] = {
            attachment_id: archivoRxSubido.attachment_id,
            url: archivoRxSubido.url,
            filename: archivoRxSubido.filename,
            comentarios: $('#rx-imagen-comentarios').val()
        };
        irAProtecciones(flujoActivo, 'prescripcion');
    });

    // =========================================================================
    // 18. ENVIAR RECETA POR CORREO
    // =========================================================================
    $(document).on('click', '#btn-rx-correo-continuar', function() {
        delete selecciones['prescripcion_valores'];
        selecciones['prescripcion_imagen'] = {
            metodo: 'correo',
            correo_destino: (typeof mmcCorreoReceta !== 'undefined') ? mmcCorreoReceta : ''
        };
        irAProtecciones(flujoActivo, 'prescripcion');
    });



});
