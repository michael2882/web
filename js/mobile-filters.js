jQuery(document).ready(function($) {
    const $panel = $('#mmc-mobile-side-panel');
    const $overlay = $('#mmc-panel-overlay');

    // 1. Abrir Panel
    $(document).on('click', '#mmc-open-mobile-filters', function(e) {
        e.preventDefault();
        if ($('#mmc-mobile-accordion-container').is(':empty')) {
            $('.filter-panel').each(function() {
                let targetId = $(this).attr('id').replace('panel-', '');
                let title = $(`.pill-btn[data-target="${targetId}"]`).text() || "Filtro";
                let content = $(this).html();
                
                let accHtml = `
                    <div class="mobile-acc-item">
                        <div class="mobile-acc-header">${title} <span>+</span></div>
                        <div class="mobile-acc-content">
                            <div class="mobile-acc-inner">${content}</div> </div>
                    </div>`;
                $('#mmc-mobile-accordion-container').append(accHtml);
            });
        }
        $panel.addClass('open');
        $overlay.fadeIn(200);
        $('body').css('overflow', 'hidden');
    });

    // 2. Acordeón Ultra Ligero (Sin lag)
    $(document).on('click', '.mobile-acc-header', function() {
        const $content = $(this).next('.mobile-acc-content');
        
        // Cierra los otros
        $('.mobile-acc-content').not($content).stop(true, true).slideUp(250);
        $('.mobile-acc-header span').not($(this).find('span')).text('+');

        // Alterna el tocado
        $content.stop(true, true).slideToggle(250);
        const isVisible = $content.is(':visible');
        $(this).find('span').text(isVisible ? '-' : '+');
    });

    // 3. Cerrar Panel
    $(document).on('click', '#mmc-close-panel, #mmc-panel-overlay', function() {
        $panel.removeClass('open');
        $overlay.fadeOut(200);
        $('body').css('overflow', 'auto');
    });
});