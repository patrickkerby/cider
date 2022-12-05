export default {
  init() {
    // JavaScript to be fired on all pages
  },
  finalize() {
    // JavaScript to be fired on all pages, after page specific JS is fired
    $(document).ready(function(){
      $('.fade').slick({
        dots: false,
        infinite: true,
        speed: 1200,
        fade: true,
        cssEase: 'linear',
        autoplay: true,
        autoplaySpeed: 6000,
      });

      $('.slideshow').slick({
        centerMode: true,      
        slidesToShow: 1,
        variableWidth: true,
        adaptiveHeight: false,
        infinite: true,
        autoplay: true,
        fade: true,
        cssEase: 'linear',
      });
    });
  },
};