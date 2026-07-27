jQuery(document).ready(function($) {
    var $productForm = $('form.variations_form');
    var $mainImg = $('#mmc-main-view'); // Asegúrate de que tu img tenga este ID
    var $thumbWrapper = $('#mmc-thumbs-wrapper');

    // Al cambiar la variación
    $productForm.on('show_variation', function(event, variation) {
        if (variation.mmc_gallery && variation.mmc_gallery.length > 0) {
            // 1. Limpiar miniaturas actuales
            $thumbWrapper.empty();
            
            // 2. Cambiar imagen principal a la primera de la variación
            $mainImg.attr('src', variation.mmc_gallery[0].full);

            // 3. Inyectar las nuevas miniaturas
            $.each(variation.mmc_gallery, function(index, item) {
                var activeClass = (index === 0) ? 'mmc-active-thumb' : '';
                $thumbWrapper.append(
                    '<div class="mmc-thumb-item ' + activeClass + '" data-full="' + item.full + '">' +
                    '<img src="' + item.thumb + '" />' +
                    '</div>'
                );
            });
        }
    });

    // Evento al hacer clic en una miniatura
    $(document).on('click', '.mmc-thumb-item', function() {
        var fullUrl = $(this).data('full');
        $mainImg.attr('src', fullUrl);
        
        $('.mmc-thumb-item').removeClass('mmc-active-thumb');
        $(this).addClass('mmc-active-thumb');
    });

    // Si se limpia la selección (vuelve a la imagen original)
    // REEMPLAZAR POR:
    // Si se limpia la selección (vuelve a la imagen original)
    $productForm.on('reset_data', function() {
        window.mmcUltimaImagenVariacion = null;
    });
});



jQuery(document).ready(function($) {
    var $productForm = $('form.variations_form');
    var $thumbWrapper = $('#mmc-thumbs-wrapper');
    var $swipeTrack = $('#mmc-main-swipe-track');
    var mainSwiper; // Guardará el motor del carrusel

    // Función que arranca o reinicia el carrusel
    function initSwiper() {
        if(mainSwiper) mainSwiper.destroy(true, true); // Mata el anterior si existe
        
        mainSwiper = new Swiper('.mmc-main-swiper', {
            loop: true, // Bucle infinito activado
            grabCursor: true, // Muestra la manito
            spaceBetween: 10, // Espacio entre fotos
            speed: 400, // Velocidad de acomodo (físicas)
            on: {
                // Cuando el usuario desliza la foto, cambiamos el punto activo abajo/izquierda
                slideChange: function () {
                    var realIndex = this.realIndex;
                    $('.mmc-thumb-item').removeClass('mmc-active-thumb');
                    $('.mmc-thumb-item').eq(realIndex).addClass('mmc-active-thumb');
                }
            }
        });
    }

    initSwiper(); // Arrancamos el carrusel con la primera foto por defecto

    // Cuando el usuario cambia de color (Variación)
    // REEMPLAZAR POR:
    // Cuando el usuario cambia de color (Variación)
    $productForm.on('show_variation', function(event, variation) {
        // Guardamos SIEMPRE la imagen real de esta variación (la que WooCommerce trae por defecto),
        // sin importar si además tiene o no galería personalizada configurada.
        window.mmcUltimaImagenVariacion = (variation.image && variation.image.src) ? variation.image.src : null;

        if (variation.mmc_gallery && variation.mmc_gallery.length > 0) {
            $thumbWrapper.empty();
            $swipeTrack.empty(); // Vaciamos la pista del carrusel

            $.each(variation.mmc_gallery, function(index, item) {
                var activeClass = (index === 0) ? 'mmc-active-thumb' : '';
                
                // 1. Inyectamos las miniaturas/puntos con su índice
                $thumbWrapper.append(
                    '<div class="mmc-thumb-item ' + activeClass + '" data-index="' + index + '">' +
                    '<img src="' + item.thumb + '" />' +
                    '</div>'
                );

                // 2. Inyectamos los "Slides" a la pista de Swiper
                $swipeTrack.append(
                    '<div class="swiper-slide"><img src="' + item.full + '" /></div>'
                );
            });

            initSwiper(); // Re-arrancamos el motor con las nuevas fotos
        }
    });

    // Cuando haces clic en una miniatura o punto, el carrusel viaja a esa foto
    $(document).on('click', '.mmc-thumb-item', function() {
        var index = $(this).data('index');
        if(mainSwiper) {
            mainSwiper.slideToLoop(index); // El viaje es animado y suave
        }
    });
    
    
    
    
// ==========================================
    // COLORES EN MÓVIL: CLON ESPEJO (OPTIMIZADO PARA TU PHP)
    // ==========================================
    function clonarColoresMovil() {
        var $summary = $('.summary.entry-summary');
        
        // ¡Magia! Ahora apuntamos directamente al div que creaste en tu PHP
        var $coloresOriginales = $('.mmc-color-selector-wrapper');

        // Si ya existe el clon o no hay colores, no hacemos nada
        if ($('.mmc-colores-movil-clon').length > 0 || $coloresOriginales.length === 0) return;

        // 1. Clonamos tu contenedor
        var $clonColores = $coloresOriginales.clone();
        
        // 2. Limpiamos el clon para no interferir con la compra
        $clonColores.addClass('mmc-colores-movil-clon').removeClass('mmc-color-selector-wrapper');
        $clonColores.find('*').removeAttr('id'); 
        
        // Borramos tu contenedor del select oculto para que no haya formularios duplicados
        $clonColores.find('.mmc-select-oculto').remove(); 

        // 3. Lo pegamos en la parte más alta del producto
        $summary.prepend($clonColores);

        // 4. Al tocar un color en el móvil (clon), simula un clic en el original
        $(document).on('click', '.mmc-colores-movil-clon .mmc-swatch-punto', function() {
            var valor = $(this).data('valor');
            
            // Clickea el original
            $('.mmc-color-selector-wrapper .mmc-swatch-punto[data-valor="' + valor + '"]').trigger('click');
        });
        
        // Nota: El texto de ".mmc-color-name" se actualizará solo en ambos lados 
        // gracias a tu código original de single-product.js que dice: $('.mmc-color-name').text(nombreColor);
    }

    clonarColoresMovil();
    
});















