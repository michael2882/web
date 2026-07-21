document.addEventListener('DOMContentLoaded', function() {
    if (typeof Swiper !== 'undefined') {
        const swiperRecomendaciones = new Swiper('.mmc-recomendaciones-swiper', {
            // Configuración básica
            grabCursor: true,       // Pone la manito
            spaceBetween: 15,       // Espacio entre tarjetas
            slidesPerView: 1.3,     // En móviles: Muestra 2 tarjetas y un "pedacito" de la tercera para invitar a deslizar
            
            // Flechas de navegación
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },

            // Comportamiento responsivo
            breakpoints: {
                // Tablets (más de 768px)
                768: {
                    slidesPerView: 3,
                    spaceBetween: 20,
                },
                // Escritorio (más de 1024px)
                1024: {
                    slidesPerView: 4,
                    spaceBetween: 20,
                }
            }
        });
    }
});