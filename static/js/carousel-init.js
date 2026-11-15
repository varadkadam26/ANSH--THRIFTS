const myCarousel = document.querySelector('#newItemsCarousel');
if (myCarousel && typeof bootstrap !== 'undefined') {
  const carousel = new bootstrap.Carousel(myCarousel, {
    interval: false,
    ride: false,
    wrap: true,
    touch: true,
    keyboard: true
  });
}
