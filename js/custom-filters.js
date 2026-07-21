jQuery(document).ready(function($) {
    
    // 1. ABRIR/CERRAR PANELES
    $('.pill-btn').click(function() {
        var target = $(this).data('target');
        var panel = $('#panel-' + target);
        if($(this).hasClass('active')) {
            panel.slideUp(); $(this).removeClass('active');
        } else {
            $('.filter-panel').slideUp(); $('.pill-btn').removeClass('active');
            panel.slideDown(); $(this).addClass('active');
        }
    });

    // 2. CHECKBOXES "TALLA GENERAL"
    $('.mmc-checkbox-box').click(function(e) {
        e.preventDefault();
        $(this).toggleClass('active');
    });

    // 3. INICIALIZAR TODOS LOS SLIDERS (Técnicos Y Precio)
    $('.mmc-tech-slider-ui').each(function() {
        var $s = $(this);
        var isPrice = $s.data('type') === 'price';
        var sym = isPrice ? $s.data('symbol') + ' ' : '';
        var suffix = isPrice ? '' : 'mm';
        
        var min = parseFloat($s.data('min'));
        var max = parseFloat($s.data('max'));
        var curMin = parseFloat($s.data('cur-min'));
        var curMax = parseFloat($s.data('cur-max'));
        
        var $lblMin = $s.siblings('.mmc-slider-labels').find('.mmc-lbl-min');
        var $lblMax = $s.siblings('.mmc-slider-labels').find('.mmc-lbl-max');

        $s.slider({
            range: true, min: min, max: max, values: [curMin, curMax],
            slide: function(event, ui) {
                // Guarda los datos internos
                $s.data('cur-min', ui.values[0]);
                $s.data('cur-max', ui.values[1]);
                
                // Actualiza el texto visible en vivo con su símbolo respectivo
                $lblMin.text(sym + ui.values[0] + suffix);
                $lblMax.text(sym + ui.values[1] + suffix);
            }
        });
    });

    // 4. BOTÓN APLICAR MEDIDAS
    $('#mmc-apply-medidas-btn').click(function() {
        var url = new URL(window.location.href);
        var tallasSelected = [];
        $('.mmc-talla-general-grid .mmc-checkbox-box.active').each(function() { tallasSelected.push($(this).data('value')); });
        if(tallasSelected.length > 0) { url.searchParams.set('filter_talla_general', tallasSelected.join(',')); } else { url.searchParams.delete('filter_talla_general'); }

        $('.mmc-tech-slider-ui[data-type="measure"]').each(function() {
            var tax = $(this).data('tax');
            var minVal = $(this).data('cur-min');
            var maxVal = $(this).data('cur-max');
            var originalMin = parseFloat($(this).data('min'));
            var originalMax = parseFloat($(this).data('max'));

            if (minVal > originalMin || maxVal < originalMax) {
                url.searchParams.set('min_' + tax, minVal);
                url.searchParams.set('max_' + tax, maxVal);
            } else {
                url.searchParams.delete('min_' + tax); url.searchParams.delete('max_' + tax);
            }
        });
        window.location.href = url.href;
    });

    // 5. BOTÓN APLICAR PRECIO
    $('#mmc-apply-price-btn').click(function(){
        var url = new URL(window.location.href);
        var $ps = $('#mmc-price-slider-ui');
        url.searchParams.set('min_price', $ps.data('cur-min'));
        url.searchParams.set('max_price', $ps.data('cur-max'));
        window.location.href = url.href;
    });

});