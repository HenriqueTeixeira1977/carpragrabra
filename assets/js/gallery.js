// Aguarda o carregamento do DOM antes de inicializar AOS
//document.addEventListener('DOMContentLoaded', function () {
//    if (typeof AOS !== 'undefined') {
//        AOS.init({ duration: 1000, once: true });
//    } else {
//        console.warn('AOS library is not loaded.');
//    }
//});

// Sincroniza o carrossel com a imagem clicada
//document.getElementById('imageModal').addEventListener('show.bs.modal', function (event) {
//    const button = event.relatedTarget;
//    const slideIndex = parseInt(button.getAttribute('data-slide'));
//    const carousel = document.getElementById('imageCarousel');
//    const carouselInstance = bootstrap.Carousel.getOrCreateInstance(carousel);
//    carouselInstance.to(slideIndex);
//});

// Aguarda o carregamento do DOM antes de inicializar AOS
document.addEventListener('DOMContentLoaded', function () {
    if (typeof AOS !== 'undefined') {
        AOS.init({ duration: 1000, once: true });
    } else {
        console.warn('AOS library is not loaded.');
    }
});

// Sincroniza o carrossel com a imagem clicada
document.getElementById('mosaicModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const slideIndex = parseInt(button.getAttribute('data-slide'));
    const carousel = document.getElementById('mosaicCarousel');
    const carouselInstance = bootstrap.Carousel.getOrCreateInstance(carousel);
    carouselInstance.to(slideIndex);
});
