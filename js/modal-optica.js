jQuery(document).ready(function($) {

    // =========================================================================
    // ESTADO GLOBAL
    // =========================================================================
    var precioBase     = (typeof mmcPrecioBase !== 'undefined') ? mmcPrecioBase : 0;
    var selecciones    = {};
    var pasoHeader     = 1;
    var historial      = []; // stack para el botón atrás

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
                irAPaquetes('sin_graduacion', 'uso');
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
        selecciones['prescripcion'] = { label: $(this).find('.mmc-opcion-nombre').text().trim(), precio: 0 };
        actualizarResumen();
        // Determinar flujo activo para los paquetes
        var uso = (selecciones['uso'] ? selecciones['uso'].label : '').toLowerCase();
        var flujo = 'simple';
        if (uso.includes('cercana') || uso.includes('near')) flujo = 'cercana';
        else if (uso.includes('progresivo'))                  flujo = 'progresivo';
        else if (uso.includes('bifocal'))                     flujo = 'bifocal';
        else if (uso.includes('sin') || uso.includes('moda')) flujo = 'sin_graduacion';
        setTimeout(function() { irAPaquetes(flujo, 'prescripcion'); }, 300);
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
        irAPaquetes('cercana', 'readers');
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

    // Sin graduación — va directo a paquetes
    function irSinGraduacion() {
        irAPaquetes('sin_graduacion', 'uso');
    }

    // =========================================================================
    // PAQUETES DE LENTE — renderizar y navegar
    // =========================================================================
    var flujoActivo = 'simple'; // se actualiza según la selección

    function irAPaquetes(flujo, pasoAnterior) {
        flujoActivo = flujo;
        renderizarPaquetes(flujo);
        // Guardar pantalla anterior para el botón atrás
        $('#btn-volver-paquetes').data('destino', pasoAnterior || 'prescripcion');
        irAPantalla('paquetes', 3);
    }

    $('#btn-volver-paquetes').on('click', function() {
        var destino = $(this).data('destino') || 'prescripcion';
        mostrarPantalla(destino);
        actualizarHeader(destino === 'uso' ? 1 : 2);
    });

    function renderizarPaquetes(flujo) {
        var paquetes = (typeof mmcPaquetes !== 'undefined' && mmcPaquetes[flujo]) ? mmcPaquetes[flujo] : [];
        var $lista   = $('#mmc-paquetes-lista');
        $lista.empty();

        if (!paquetes.length) {
            $lista.html('<p style="color:#94a3b8;font-size:14px;">No hay paquetes configurados para este flujo.</p>');
            return;
        }

        paquetes.forEach(function(p) {
            if (!p.nombre) return;

            var precioNum    = parseFloat(p.precio_ahora) || 0;
            var precioAntes  = parseFloat(p.precio_antes) || 0;
            var tieneAntes   = precioAntes > 0;
            var esGratis     = precioNum === 0;

            var precioHtml = '';
            if (esGratis) {
                precioHtml = '<span class="mmc-paquete-gratis">Gratis</span>';
            } else {
                if (tieneAntes) {
                    precioHtml = '<span class="mmc-paquete-precio-antes">+' + formatPrecio(precioAntes) + '</span>';
                }
                precioHtml += '<span class="mmc-paquete-precio-ahora">+' + formatPrecio(precioNum) + '</span>';
            }

            // Tags
            var tagsHtml = '';
            if (p.tags && p.tags.length) {
                var colores = ['mmc-tag-azul','mmc-tag-verde','mmc-tag-naranja','mmc-tag-gris','mmc-tag-morado'];
                p.tags.forEach(function(tag, idx) {
                    if (!tag.texto) return;
                    var color = tag.color || colores[idx % colores.length];
                    tagsHtml += '<span class="mmc-paquete-tag ' + color + '">' + tag.texto + '</span>';
                });
            }

            var imgHtml = p.imagen_url
                ? '<img class="mmc-paquete-img" src="' + p.imagen_url + '" alt="' + p.nombre + '">'
                : '<div style="width:64px;height:48px;display:flex;align-items:center;justify-content:center;opacity:0.2;"><svg width="40" height="32" viewBox="0 0 40 32" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 16 Q14 6 20 16 Q26 26 32 16"/><path d="M12 16 Q18 8 24 16"/></svg></div>';

            var tooltipAttr = '';
            if (p.tooltip_texto || p.tooltip_img) {
                tooltipAttr = 'data-texto="' + (p.tooltip_texto || '') + '" data-img="' + (p.tooltip_img || '') + '"';
            }

            var html = '<div class="mmc-paquete-card" data-precio="' + precioNum + '" data-nombre="' + p.nombre + '">';
            html += '<div class="mmc-paquete-izq">';
            html += imgHtml;
            html += '<span class="mmc-paquete-nombre">' + p.nombre + '</span>';
            html += '<div class="mmc-paquete-precio-wrap">' + precioHtml + '</div>';
            html += '</div>';
            if (tooltipAttr) {
                html += '<button class="mmc-paquete-tooltip-btn mmc-tooltip-btn" ' + tooltipAttr + '>?</button>';
            }
            html += '<div class="mmc-paquete-divider"></div>';
            html += '<div class="mmc-paquete-der">';
            if (tagsHtml) html += '<div class="mmc-paquete-tags">' + tagsHtml + '</div>';
            html += '<p class="mmc-paquete-desc">' + (p.descripcion || '') + '</p>';
            html += '</div>';
            html += '</div>';

            $lista.append(html);
        });
    }

    // Click en paquete
    $(document).on('click', '.mmc-paquete-card', function(e) {
        if ($(e.target).closest('.mmc-tooltip-btn').length) return;
        var $card   = $(this);
        var nombre  = $card.data('nombre');
        var precio  = parseFloat($card.data('precio')) || 0;

        $('.mmc-paquete-card').removeClass('seleccionado');
        $card.addClass('seleccionado');

        selecciones['paquete'] = { label: nombre, precio: precio };
        actualizarResumen();

        // Ir a tipos de lente
        setTimeout(function() { irATiposLente(); }, 350);
    });

    // =========================================================================
    // TIPOS DE LENTE — renderizar y navegar
    // =========================================================================
    function irATiposLente() {
        renderizarTiposLente();
        $('#btn-volver-tipo-lente').data('destino', 'paquetes');
        irAPantalla('tipo-lente', 4);
    }

    $('#btn-volver-tipo-lente').on('click', function() {
        mostrarPantalla('paquetes');
        actualizarHeader(3);
    });

    function renderizarTiposLente() {
        var tipos    = (typeof mmcTiposLente !== 'undefined') ? mmcTiposLente : [];
        var $lista   = $('#mmc-tipos-lista');
        $lista.empty();

        // Detectar si es Progresivo Office — solo muestra clear y blue_light
        var tipoLente = selecciones['tipo-lente'] ? selecciones['tipo-lente'].label.toLowerCase() : '';
        var soloOffice = tipoLente.includes('oficina') || tipoLente.includes('office');

        if (!tipos.length) {
            $lista.html('<p style="color:#94a3b8;font-size:14px;">No hay tipos de lente configurados.</p>');
            return;
        }

        tipos.forEach(function(t, idx) {
            if (!t.nombre) return;
            // Progresivo Office: solo mostrar primeras 2 (clear + blue light)
            if (soloOffice && idx >= 2) return;

            var precioNum   = parseFloat(t.precio_ahora) || 0;
            var precioAntes = parseFloat(t.precio_antes) || 0;
            var tieneAntes  = precioAntes > 0;
            var esGratis    = precioNum === 0;

            var precioHtml = '';
            if (esGratis) {
                precioHtml = '';
            } else {
                precioHtml = '(From ';
                if (tieneAntes) precioHtml += '<del>+' + formatPrecio(precioAntes) + '</del> ';
                precioHtml += '+' + formatPrecio(precioNum) + ')';
            }

            var tooltipAttr = '';
            if (t.tooltip_texto || t.tooltip_img) {
                tooltipAttr = 'data-texto="' + (t.tooltip_texto||'') + '" data-img="' + (t.tooltip_img||'') + '"';
            }

            // Título — puede ser SVG+imagen (Transitions) o texto normal
            var tituloInner = '';
            if (t.titulo_svg && t.titulo_img) {
                // Transitions / Photochromic style
                tituloInner = '<span class="mmc-tipo-titulo-marca">'
                    + '<img src="' + t.titulo_svg + '" style="height:16px;width:auto;vertical-align:middle;" alt="' + t.nombre + '">'
                    + ' / <img src="' + t.titulo_img + '" style="height:10px;width:auto;vertical-align:middle;" alt="">'
                    + '</span>';
            } else if (t.titulo_svg) {
                tituloInner = '<img src="' + t.titulo_svg + '" style="height:16px;width:auto;vertical-align:middle;" alt="' + t.nombre + '">';
            } else {
                tituloInner = '<strong>' + t.nombre + '</strong>';
            }

            if (precioHtml) tituloInner += ' <span class="mmc-tipo-precio">' + precioHtml + '</span>';
            if (tooltipAttr) tituloInner += ' <button class="mmc-tooltip-btn" ' + tooltipAttr + '>?</button>';

            var imgHtml = t.imagen_url
                ? '<img class="mmc-tipo-img" src="' + t.imagen_url + '" alt="' + t.nombre + '">'
                : '';

            var html = '<div class="mmc-tipo-card" data-precio="' + precioNum + '" data-nombre="' + t.nombre + '">';
            html += '<div class="mmc-tipo-textos">';
            html += '<div class="mmc-tipo-titulo">' + tituloInner + '</div>';
            html += '<span class="mmc-tipo-desc">' + (t.descripcion || '') + '</span>';
            html += '</div>';
            html += imgHtml;
            html += '</div>';

            $lista.append(html);
        });
    }

    // Click en tipo de lente
    $(document).on('click', '.mmc-tipo-card', function(e) {
        if ($(e.target).closest('.mmc-tooltip-btn').length) return;
        var $card  = $(this);
        var nombre = $card.data('nombre');
        var precio = parseFloat($card.data('precio')) || 0;

        $('.mmc-tipo-card').removeClass('seleccionado');
        $card.addClass('seleccionado');

        selecciones['tipo_lente_final'] = { label: nombre, precio: precio };
        actualizarResumen();

        setTimeout(function() { irAPantalla('paso4', 4); }, 350);
    });
    // =========================================================================
    var labelMap = {
        'uso':              'Tipo de uso',
        'tipo-lente':       'Tipo de lente',
        'prescripcion':     'Prescripción',
        'readers':          'Medida',
        'paquete':          'Paquete de lente',
        'tipo_lente_final': 'Tipo de lente',
    };

    function formatPrecio(num) {
        return mmcSymbol + num.toFixed(2);
    }

    function actualizarResumen() {
        var html        = '';
        var precioExtra = 0;

        var orden = ['uso', 'tipo-lente', 'readers', 'prescripcion', 'paquete', 'tipo_lente_final'];
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
    var tooltipVisible = false;

    $(document).on('click', '.mmc-tooltip-btn', function(e) {
        e.stopPropagation();
        var texto = $(this).data('texto') || '';
        var img   = $(this).data('img')   || '';
        var $box  = $('#mmc-tooltip-box');

        if (tooltipVisible && $box.data('origen') && $box.data('origen')[0] === this) {
            ocultarTooltip(); return;
        }

        $('#mmc-tooltip-texto').text(texto);
        img ? $('#mmc-tooltip-img').attr('src', img).show() : $('#mmc-tooltip-img').hide();

        var offset = $(this).offset();
        var left   = offset.left + 28;
        if (left + 260 > $(window).width() - 20) left = offset.left - 268;

        $box.css({ top: offset.top + $(this).outerHeight() + 8, left: left })
            .addClass('visible').data('origen', $(this));
        tooltipVisible = true;
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#mmc-tooltip-box').length && !$(e.target).hasClass('mmc-tooltip-btn')) {
            ocultarTooltip();
        }
    });

    function ocultarTooltip() {
        $('#mmc-tooltip-box').removeClass('visible');
        tooltipVisible = false;
    }

});