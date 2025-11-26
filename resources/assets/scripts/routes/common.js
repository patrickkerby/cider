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

        // Custom controls for Quantity buttons
      function initQuantityButtons() {
        // Only add buttons to quantity inputs that don't already have them
        $('.quantity input').each(function() {
          const $input = $(this);
          const $quantityContainer = $input.closest('.quantity');
          
          console.log('Found quantity input:', $input.length, 'Existing buttons:', $quantityContainer.find('.quantity-button').length);
          
          // Check if buttons already exist
          if ($quantityContainer.find('.quantity-button').length === 0) {
            // Add the quantity buttons directly after the input
            $('<div class="quantity-button quantity-up">+</div><div class="quantity-button quantity-down">-</div>').insertAfter($input);
            console.log('Added quantity buttons to input');
          }
        });
      }

      // Set up auto-update cart functionality - MOVED INSIDE document.ready
      if ($('body').hasClass('woocommerce-cart')) {
        $(document).off('change.autoUpdate', 'input.qty').on('change.autoUpdate', 'input.qty', function() {
          $('[name="update_cart"]').trigger('click');
        });
      }

      // Bind quantity button events
      $(document).off('click.quantityButtons').on('click.quantityButtons', '.quantity-up', function() {
        var $spinner = $(this).closest('.quantity');
        var $input = $spinner.find('input[type="number"]');
        var oldValue = parseFloat($input.val()) || 0;
        var max = parseFloat($input.attr('max')) || Infinity;
        var newVal = oldValue >= max ? oldValue : oldValue + 1;
        
        $input.val(newVal);
        
        // Only trigger change event on cart page for auto-update
        if ($('body').hasClass('woocommerce-cart')) {
          $input.trigger('change');
        }
      });

      $(document).off('click.quantityButtonsDown').on('click.quantityButtonsDown', '.quantity-down', function() {
        var $spinner = $(this).closest('.quantity');
        var $input = $spinner.find('input[type="number"]');
        var oldValue = parseFloat($input.val()) || 0;
        var min = parseFloat($input.attr('min')) || 0;
        var newVal = oldValue <= min ? oldValue : oldValue - 1;
        
        $input.val(newVal);
        
        // Only trigger change event on cart page for auto-update
        if ($('body').hasClass('woocommerce-cart')) {
          $input.trigger('change');
        }
      });

      // Initialize on page load
      initQuantityButtons();

      // Re-initialize after AJAX updates
      $(document.body).on('updated_cart_totals updated_checkout updated_wc_div', function() {
        initQuantityButtons();
      });

      // Initialize after quickview modal opens
      $(document).on('click', '.inside-thumb', function() {
        $('body').addClass('quickview-open');
        // Add multiple checks to ensure the modal content is loaded
        setTimeout(function() {
          initQuantityButtons();
        }, 200);
        
        // Also try after a longer delay in case content takes time to load
        setTimeout(function() {
          initQuantityButtons();
        }, 500);
      });

      // Listen for prettyPhoto events if available
      if (typeof $.prettyPhoto !== 'undefined') {
        $.prettyPhoto.open = (function(original) {
          return function() {
            var result = original.apply(this, arguments);
            // Multiple timeouts to catch different loading scenarios
            setTimeout(function() {
              initQuantityButtons();
            }, 100);
            setTimeout(function() {
              initQuantityButtons();
            }, 300);
            setTimeout(function() {
              initQuantityButtons();
            }, 600);
            return result;
          };
        })($.prettyPhoto.open);
      }

      // Also listen for any AJAX complete events that might indicate modal content loaded
      $(document).ajaxComplete(function() {
        if ($('body').hasClass('quickview-open')) {
          setTimeout(function() {
            initQuantityButtons();
          }, 50);
        }
      });
    });
  },
}