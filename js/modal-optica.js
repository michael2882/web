jQuery(document).ready(function($) {

    // =========================================================================
    // ESTADO GLOBAL
    // =========================================================================
var precioBase        = (typeof mmcPrecioBase !== 'undefined') ? mmcPrecioBase : 0;
var selecciones       = {};
var pasoHeader        = 1;
var historial         = [];
var flujoActivo         = 'simple';
var proteccionActiva    = null;
var subproteccionActiva = null;
var indicesActivos      = [];

    // =========================================================================
    // 1. ABRIR / CERRAR
    // =========================================================================
    $(document).on('click', '#mmc-abrir-modal-btn', function(e) {
        e.preventDefault();
        resetModal();
        $('#mmc-modal-overlay').addClass('activo');
        $('body').css('overflow', 'hidden');
    });

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

function resetModal() {
    selecciones = {};
    historial   = [];
    pasoHeader  = 1;
    flujoActivo = 'simple';
    proteccionActiva = null;
    subproteccionActiva = null;
    indicesActivos = [];
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
    function mostrarPantalla(id) {
        $('.mmc-pantalla').hide();
        $('#pantalla-' + id).show();
        $('#mmc-modal-col-der').scrollTop(0);
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

        selecciones['uso'] = { label: valor, precio: precio };
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

        selecciones['uso'] = { label: valor, precio: precio };
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
    selecciones['prescripcion'] = { label: $(this).find('.mmc-opcion-nombre').text().trim(), precio: 0 };
    actualizarResumen();

    var uso = (selecciones['uso'] ? selecciones['uso'].label : '').toLowerCase();
    var flujo = 'simple';
    if (uso.includes('cercana') || uso.includes('near')) flujo = 'cercana';
    else if (uso.includes('progresivo'))                  flujo = 'progresivo';
    else if (uso.includes('bifocal'))                     flujo = 'bifocal';
    else if (uso.includes('sin') || uso.includes('moda')) flujo = 'sin_graduacion';
    flujoActivo = flujo;

    if (key === 'online') {
        setTimeout(function() { irAPantalla('prescripcion-online'); }, 300);
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
        selecciones['readers'] = { label: 'Readers ' + val, precio: 0 };
    actualizarResumen();
    flujoActivo = 'cercana';
    irAProtecciones('cercana', 'readers');
    
    });

    // =========================================================================
    // 9. PROGRESIVO — Standard / Office
    // =========================================================================
    $(document).on('click', '.mmc-opcion-item[data-accion="progresivo-tipo"]', function() {
        var $item  = $(this);
        var precio = parseFloat($item.data('precio')) || 0;
        var titulo = $item.find('.mmc-opcion-nombre').clone().children().remove().end().text().trim();

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
            proteccionActiva = obj;
            selecciones['proteccion'] = { label: obj.nombre, precio: parseFloat(obj.precio_ahora) || 0 };
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
            subproteccionActiva = obj;
            selecciones['subproteccion'] = { label: obj.nombre, precio: parseFloat(obj.precio_ahora) || 0 };
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

        $card.closest('.mmc-tipos-lista').find('.mmc-tipo-card').removeClass('seleccionado');
        $card.closest('.mmc-tipos-lista').find('.mmc-color-selector-inline').hide();
        $card.addClass('seleccionado');

        if (esFoto && $colorBox.length) {
            $colorBox.show();
            return; // espera al botón Confirmar
        }

        setTimeout(function() { onConfirmar(obj); }, 350);

        // referencia para el handler de Confirmar (por si luego se usa un color por defecto)
        $card.data('__claveColor', claveColor);
    }

    // Click en un swatch de color
    $(document).on('click', '.mmc-color-swatch', function() {
        $(this).siblings('.mmc-color-swatch').removeClass('activo');
        $(this).addClass('activo');
        $(this).closest('.mmc-color-selector-inline').find('.mmc-color-nombre-actual').text($(this).data('nombre'));
    });

    // Click en Confirmar color
    $(document).on('click', '.mmc-btn-confirmar-color', function() {
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
            proteccionActiva = obj;
            selecciones['proteccion'] = { label: obj.nombre, precio: parseFloat(obj.precio_ahora) || 0 };
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

        selecciones['indice'] = { label: nombre, precio: precio };
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

        selecciones['recubrimiento'] = { label: $sel.data('nombre'), precio: parseFloat($sel.data('precio')) || 0 };
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

    function actualizarResumen() {
        var html        = '';
        var precioExtra = 0;

        var orden = ['uso', 'tipo-lente', 'readers', 'prescripcion', 'proteccion', 'color_proteccion', 'subproteccion', 'color_subproteccion', 'indice', 'recubrimiento'];
        orden.forEach(function(key) {
            if (!selecciones[key]) return;
            var data  = selecciones[key];
            var label = labelMap[key] || key;
            precioExtra += data.precio;

            html += '<div class="mmc-seleccion-item">';
            html += '<div class="mmc-seleccion-izq">';
            html += '<span class="mmc-seleccion-label">' + label + '</span>';
            html += '<span class="mmc-seleccion-valor">' + data.label + '</span>';
            html += '</div>';
            if (data.precio > 0) {
                html += '<span class="mmc-seleccion-precio">+' + formatPrecio(data.precio) + '</span>';
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
$(document).on('change', '#mmc-rx-2pd-check', function() {
    if ($(this).is(':checked')) {
        $('#mmc-rx-pd-single-box').hide();
        $('#mmc-rx-pd-dual-box').show();
    } else {
        $('#mmc-rx-pd-dual-box').hide();
        $('#mmc-rx-pd-single-box').show();
    }
});

// Continuar — guarda los valores (sin precio, sin línea en el resumen) y avanza
$(document).on('click', '#btn-rx-online-continuar', function() {
    selecciones['prescripcion_valores'] = {
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






});
