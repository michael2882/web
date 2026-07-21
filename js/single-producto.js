jQuery(document).ready(function($) {

    
    // ==========================================
    // 1. BOTON ADD TO CART PARA HABILITAR
    // ==========================================
    var $swatches = $('.mmc-swatch-punto');
    
    if ($swatches.length > 0) {
        $(document).on('click', '.mmc-swatch-punto', function() {
            var $punto = $(this);
            var valor = $punto.data('valor');
            var nombreColor = $punto.data('nombre');
            
            // Actualiza el texto
            $('.mmc-color-name').text(nombreColor);
            
            // Marca el select oculto
            var $select = $punto.closest('form.cart').find('.mmc-select-oculto select');
            if ($select.length) {
                $select.val(valor).trigger('change');
            }

            // Cambia el estilo activo
            $('.mmc-swatch-punto').removeClass('activo');
            $punto.addClass('activo');
        });

        // Simula clic en el primer color al cargar
        setTimeout(function() {
            if (!$('.mmc-swatch-punto.activo').length) {
                $swatches.first().trigger('click');
            }
        }, 100);
    }

    // ==========================================
    // 2. LÓGICA DE LOS ENLACES (SCROLL SUAVE)
    // ==========================================
    $('.mmc-link-seccion').on('click', function(e) {
        e.preventDefault();
        var target = $(this).attr('href');
        var $targetElement = $(target);
        
        if ($targetElement.length) {
            $('html, body').animate({
                scrollTop: $targetElement.offset().top - 100
            }, 800);
        }
    });

    // ==========================================
    // 3. FORZAR APERTURA DEL MODAL (ÓPTICA)
    // ==========================================
    // (Corregido: ahora está dentro del document.ready)
    $(document).on('click', '#mmc-abrir-modal-btn', function(e) {
        e.preventDefault();
        var $modal = $('#mmc-modal-optica'); // Asegúrate de que este es el ID de tu ventana negra
        if ($modal.length) {
            $modal.fadeIn(300); // Abre la ventana con un efecto suave
            $('body').css('overflow', 'hidden'); // Congela el fondo
        } else {
            console.log('Error: No se encuentra el HTML del modal en la página.');
        }
    });

// ==========================================
    // 4. VENTANA DESLIZANTE (ESTILO GLASSES USA - MÓVIL)
    // ==========================================
    function crearBottomSheetTabs() {
        var $tabsContainer = $('.woocommerce-tabs');
        if ($tabsContainer.length === 0 || $tabsContainer.hasClass('mmc-bottom-sheet-listo')) return;
        $tabsContainer.addClass('mmc-bottom-sheet-listo');

        // 1. Identificar la pestaña de Descripción
        var $descTab = $('#tab-description');
        if ($descTab.length === 0) {
            $descTab = $tabsContainer.find('.panel').first();
        }

        // Extraer contenido de la Descripción
        var $tempDesc = $('<div>').html($descTab.html());
        var $descTitle = $tempDesc.find('h2:first');
        var tituloDesc = $descTitle.length ? $descTitle.text() : 'About the frame';
        $descTitle.remove(); 

        // 2. MAGIA: Separamos los Atributos (el cuadro) del Texto
        // Buscamos la clase que pusiste en tu PHP (.mmc-about-frame-attrs)
        var $atributos = $tempDesc.find('.mmc-about-frame-attrs');
        var htmlAtributos = '';
        if ($atributos.length) {
            // Lo guardamos en una variable y lo borramos de la descripción
            htmlAtributos = '<div class="mmc-mobile-attributes-list">' + $atributos.prop('outerHTML') + '</div>';
            $atributos.remove(); 
        }

        // Crear el bloque de Descripción visible (AHORA SOLO TIENE EL TEXTO)
        var htmlDescVisible = '<div class="mmc-mobile-description-block">' +
                                '<h3>' + tituloDesc + '</h3>' +
                                '<div class="mmc-desc-texto">' + $tempDesc.html() + '</div>' +
                              '</div>';

        // 3. Crear la Tarjeta (Card) para las demás opciones
        var htmlLista = '<div class="mmc-mobile-tabs-card">';
        $tabsContainer.find('ul.tabs li').each(function() {
            var $li = $(this);
            var targetId = $li.find('a').attr('href');
            
            if (targetId === '#' + $descTab.attr('id')) return; // Saltamos descripción

            var titulo = $li.text().trim();
            htmlLista += '<div class="mmc-tab-boton" data-target="'+targetId+'">' + 
                            titulo + '<span class="mmc-icono-mas">+</span>' +
                         '</div>';
        });
        htmlLista += '</div>';

        // Insertar en la página
        $tabsContainer.before(htmlDescVisible + htmlLista);

        // 4. Crear el Bottom Sheet (Esqueleto)
        if ($('#mmc-bottom-sheet').length === 0) {
            $('body').append(
                '<div class="mmc-bottom-sheet-overlay" id="mmc-bs-overlay"></div>' +
                '<div class="mmc-bottom-sheet-modal" id="mmc-bottom-sheet">' +
                    '<div class="mmc-bs-drag-line"></div>' +
                    '<div class="mmc-bs-header">' +
                        '<h3 id="mmc-bs-titulo">Título</h3>' +
                        '<button id="mmc-bs-cerrar">×</button>' +
                    '</div>' +
                    '<div class="mmc-bs-content" id="mmc-bs-contenido"></div>' +
                '</div>'
            );
        }

        // 5. Eventos de abrir
        $(document).on('click', '.mmc-tab-boton', function() {
            var target = $(this).data('target');
            var titulo = $(this).text().replace('+', '').trim();
            
            var $panelNativo = $(target);
            var $tempDiv = $('<div>').html($panelNativo.html());
            $tempDiv.find('h2:first').remove(); 
            
            // MAGIA 2: Si es la pestaña Sizing & Fit, le inyectamos los atributos arriba
            if (target === '#tab-sizing_fit' || target.includes('sizing')) {
                $tempDiv.prepend(htmlAtributos);
            }
            
            $('#mmc-bs-titulo').text(titulo);
            $('#mmc-bs-contenido').html($tempDiv.html());
            
            $('#mmc-bs-overlay').fadeIn(200);
            $('#mmc-bottom-sheet').addClass('abierto');
            $('body').css('overflow', 'hidden'); 
        });

        // Eventos de cerrar
        $(document).on('click', '#mmc-bs-cerrar, #mmc-bs-overlay', function() {
            $('#mmc-bs-overlay').fadeOut(200);
            $('#mmc-bottom-sheet').removeClass('abierto');
            $('body').css('overflow', ''); 
        });
    }

    crearBottomSheetTabs();



}); // <-- FIN DEL DOCUMENT.READY (No borres esto)



        

