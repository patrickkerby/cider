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
        dots: false,
        infinite: true,
        speed: 1200,
        fade: true,
        cssEase: 'linear',
        autoplay: true,
      });

      $(document).ready(function() {
        $(document).on('click', 'li .product_type_simple', function() {
          $('body').addClass('quickview-open');
        });
        $(document).on('click', '.openModal', function() {
          $('body').addClass('quickview-open');
        });
        // remove class from body when close button is clicked  
        $(document).on('click', '.close-product', function(e) {
          if (!$(e.target).is('.quickview'))
            $('.quickview-open').removeClass('quickview-open');
        });
        $(document).on('click', '.close', function(e) {
          if (!$(e.target).is('.quickview'))
            $('.quickview-open').removeClass('quickview-open');
        });
        // remove class from body when you click on the overlay
        $(document).on('click', '.pp_overlay', function(e) {
          if (!$(e.target).is('.quickview-open'))
            $('.quickview-open').removeClass('quickview-open');
        });        
        // remove class from body when you hit escape
        $(document).bind('keyup', function(e){ 
          if(e.which == 27){
            if (!$(e.target).is('.quickview-open'))
            $('.quickview-open').removeClass('quickview-open');
           }
        });
        // close the modal when you click on our new button  
        $('.close-product').on('click',function() { $.prettyPhoto.close(); });
  
        $('.modal').each(function () {
          const modalId = `#${$(this).attr('id')}`;
          if (window.location.href.indexOf(modalId) !== -1) {
              $(modalId).modal('show');
          }
        });
  
        // remove class from body when close button is clicked  
        $(document).on('click', '.close-product', function(e) {
          if (!$(e.target).is('.quickview')) {
            $('.quickview-open').removeClass('quickview-open'); 
          }
        });
        $(document).on('click', '.close', function(e) {
          if (!$(e.target).is('.quickview')) {
            $('.quickview-open').removeClass('quickview-open'); 
          }
        });

        setTimeout(function() {
          $('.woocommerce-message').fadeOut('fast');
        }, 5000);

        if(window.location.hash == '#stockists'){
          $('body').addClass('quickview-open');
        }
      });
    });
  },
}