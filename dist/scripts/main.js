/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./scripts/routes/about.js":
/*!*********************************!*\
  !*** ./scripts/routes/about.js ***!
  \*********************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ({
  init: function init() {
    // JavaScript to be fired on the about us page
  },
  finalize: function finalize() {
    
  },
});


/***/ }),

/***/ "./scripts/routes/common.js":
/*!**********************************!*\
  !*** ./scripts/routes/common.js ***!
  \**********************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* provided dependency */ var $ = __webpack_require__(/*! jquery */ "jquery");
/* harmony default export */ __webpack_exports__["default"] = ({
  init: function init() {
    // JavaScript to be fired on all pages
  },
  finalize: function finalize() {
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
            { $('.quickview-open').removeClass('quickview-open'); }
        });
        $(document).on('click', '.close', function(e) {
          if (!$(e.target).is('.quickview'))
            { $('.quickview-open').removeClass('quickview-open'); }
        });
        // remove class from body when you click on the overlay
        $(document).on('click', '.pp_overlay', function(e) {
          if (!$(e.target).is('.quickview-open'))
            { $('.quickview-open').removeClass('quickview-open'); }
        });        
        // remove class from body when you hit escape
        $(document).bind('keyup', function(e){ 
          if(e.which == 27){
            if (!$(e.target).is('.quickview-open'))
            { $('.quickview-open').removeClass('quickview-open'); }
           }
        });
        // close the modal when you click on our new button  
        $('.close-product').on('click',function() { $.prettyPhoto.close(); });
  
        $('.modal').each(function () {
          var modalId = "#" + ($(this).attr('id'));
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

        // Robust loader for Google Places (supports both legacy namespace and importLibrary)
        var placesLoadInProgress = false;
        var placesLoadAttempts = 0;
        function ensureGooglePlacesLoaded(callback) {
          var maxAttempts = 12;
          
          if (window.google && window.google.maps && window.google.maps.places) {
            callback();
            return;
          }
          
          if (window.google && window.google.maps && typeof window.google.maps.importLibrary === 'function') {
            if (!placesLoadInProgress) {
              placesLoadInProgress = true;
              // console.log('Loading Places via google.maps.importLibrary("places")...');
              window.google.maps.importLibrary('places').then(function() {
                placesLoadInProgress = false;
                // console.log('Places library loaded via importLibrary');
                callback();
              }).catch(function(err) {
                placesLoadInProgress = false;
                console.log('Failed loading Places via importLibrary', err);
              });
            }
            return;
          }
          
          placesLoadAttempts++;
          if (placesLoadAttempts <= maxAttempts) {
            // console.log('Waiting for Google Places API... attempt ' + placesLoadAttempts);
            setTimeout(function() {
              ensureGooglePlacesLoaded(callback);
            }, 1000);
          } else {
            console.log('Google Places API failed to load after ' + maxAttempts + ' attempts');
            console.log('Proceeding without Places functionality');
          }
        }
        
        function getBusinessName(marker) {
          var name = marker.title || marker.business_name || marker.name || '';
          if (name) {
            return name;
          }
          
          // Try to find name in FacetWP location data
          if (window.FWP && window.FWP.settings && window.FWP.settings.map && window.FWP.settings.map.locations) {
            var locations = window.FWP.settings.map.locations;
            var markerPos = getMarkerLocation(marker);
            
            if (markerPos) {
              for (var j = 0; j < locations.length; j++) {
                var loc = locations[j];
                if (Math.abs(parseFloat(loc.lat) - markerPos.lat) < 0.0001 && 
                    Math.abs(parseFloat(loc.lng) - markerPos.lng) < 0.0001) {
                  if (loc.post_title) {
                    return loc.post_title;
                  }
                  if (loc.title) {
                    return loc.title;
                  }
                }
              }
            }
          }
          
          // Try deriving from info window HTML
          if (marker.infoWindowContent && 'string' === typeof marker.infoWindowContent) {
            var temp = document.createElement('div');
            temp.innerHTML = marker.infoWindowContent;
            var heading = temp.querySelector('h1, h2, h3, h4, .title, .stockist-name, .location-title');
            if (heading && heading.textContent) {
              return heading.textContent.trim();
            }
            
            // Fallback: use first non-empty line of plain text
            var text = (temp.textContent || '').replace(/\r/g, '').trim();
            if (text) {
              var lines = text.split('\n');
              for (var i = 0; i < lines.length; i++) {
                var line = (lines[i] || '').trim();
                if (line) {
                  return line;
                }
              }
            }
          }
          
          return '';
        }
        
        // Google Places integration for FacetWP maps
        function initGooglePlacesForFacetWP() {
          // console.log('Attempting to initialize Google Places for FacetWP...');
          
          // Check all prerequisites
          if (typeof window.FWP === 'undefined') {
            // console.log('FacetWP not loaded yet');
            return;
          }
          if (!window.FWP.loaded) {
            // console.log('FacetWP not ready yet');
            return;
          }
          if (!window.google || !window.google.maps) {
            // console.log('Google Maps API not loaded');
            return;
          }
          if (!window.google.maps.places) {
            // console.log('Google Places API not loaded yet, attempting library load...');
            ensureGooglePlacesLoaded(initGooglePlacesForFacetWP);
            return;
          }
          if (typeof window.FWP_MAP === 'undefined' || !window.FWP_MAP.map_loaded || !window.FWP_MAP.map) {
            // console.log('FacetWP map not ready yet');
            return;
          }
          
          // console.log('All prerequisites met, using FWP_MAP instance');
          
          var map = window.FWP_MAP.map;
          var markers = window.FWP_MAP.markersArray || [];
          var placesService = new window.google.maps.places.PlacesService(document.createElement('div'));
          
          // console.log('Found ' + markers.length + ' markers');
          
          if (markers.length > 0) {
            for (var j = 0; j < markers.length; j++) {
              var marker = markers[j];
              // console.log('Processing marker ' + (j + 1));
              try {
                enhanceMarkerWithPlaces(marker, placesService, map);
              } catch (e) {
                console.log('Skipping marker due to error', e);
              }
            }
          } else {
            // console.log('No markers found on map');
          }
          
          // Add direct click listeners as fallback
          addDirectClickListeners();
        }
        
        function getMarkerLocation(marker) {
          var position = marker.position;
          
          if (!position && typeof marker.getPosition === 'function') {
            position = marker.getPosition();
          }
          
          if (!position) {
            return null;
          }
          
          // google.maps.LatLng instance
          if (typeof position.lat === 'function' && typeof position.lng === 'function') {
            return {
              lat: position.lat(),
              lng: position.lng(),
            };
          }
          
          // Literal { lat, lng }
          if (typeof position.lat !== 'undefined' && typeof position.lng !== 'undefined') {
            return {
              lat: position.lat,
              lng: position.lng,
            };
          }
          
          return null;
        }
        
        function enhanceMarkerWithPlaces(marker, placesService, map) {
          if (marker._pbcPlacesBound) {
            return;
          }
          marker._pbcPlacesBound = true;
          
          // Get business info from marker data
          var businessName = getBusinessName(marker);
          var location = getMarkerLocation(marker);
          if (!location) {
            console.log('No marker location available, skipping marker: ' + businessName);
            return;
          }
          
          // console.log('Enhancing marker for: ' + businessName);
          
          // Store data on marker for use by the global click handler
          marker._pbcBusinessName = businessName;
          marker._pbcLocation = location;
          marker._pbcPlacesService = placesService;
          marker._pbcMap = map;
        }
        
        function enhanceInfoWindowWithPlaces(marker, businessName, location, placesService, map) {
          // Try to extract business name from currently displayed info window if not provided
          if (!businessName) {
            var infoWindow = (window.FWP_MAP && window.FWP_MAP.infoWindow) ? window.FWP_MAP.infoWindow : marker.infoWindow;
            if (infoWindow) {
              var content = infoWindow.getContent();
              if (typeof content === 'string') {
                var temp = document.createElement('div');
                temp.innerHTML = content;
                var heading = temp.querySelector('h1, h2, h3, h4, .title, .stockist-name, .location-title');
                if (heading && heading.textContent) {
                  businessName = heading.textContent.trim();
                  // console.log('Extracted business name from info window: ' + businessName);
                } else {
                  // Try first line of text
                  var text = (temp.textContent || '').replace(/\r/g, '').trim();
                  if (text) {
                    var lines = text.split('\n');
                    for (var i = 0; i < lines.length; i++) {
                      var line = (lines[i] || '').trim();
                      if (line) {
                        businessName = line;
                        // console.log('Extracted business name from text content: ' + businessName);
                        break;
                      }
                    }
                  }
                }
              }
            }
          }
          
          // Add subtle loading indicator to existing content
          var currentInfoWindow = (window.FWP_MAP && window.FWP_MAP.infoWindow) ? window.FWP_MAP.infoWindow : marker.infoWindow;
          if (currentInfoWindow) {
            var currentContent = currentInfoWindow.getContent();
            if (typeof currentContent === 'string' && !currentContent.includes('places-loading')) {
              var loadingContent = currentContent + '<div class="places-loading" style="margin-top: 10px; padding: 5px; font-size: 12px; color: #666; font-style: italic;">🔍 Loading additional business info...</div>';
              currentInfoWindow.setContent(loadingContent);
            }
          }
          
          if (businessName) {
            // console.log('Searching for place: ' + businessName);
          } else {
            // console.log('No business name, searching nearest place by coordinates');
          }
          
          var handlePlaceResults = function(results, status) {
            // console.log('Places search status: ' + status);
            
            if (status === window.google.maps.places.PlacesServiceStatus.OK && results.length > 0) {
              var place = results[0];
              // console.log('Found place: ' + place.name);
              
              // Get detailed place information
              placesService.getDetails({
                placeId: place.place_id,
                fields: [
                  'name', 
                  'formatted_address', 
                  'opening_hours', 
                  'formatted_phone_number', 
                  'website', 
                  'rating',
                  'price_level', 
                  'photos', 
                  'reviews', 
                  'url', 
                  'business_status' ],
              }, function(placeDetails, detailsStatus) {
                // console.log('Place details status: ' + detailsStatus);
                
                if (detailsStatus === window.google.maps.places.PlacesServiceStatus.OK) {
                  updateInfoWindowContent(marker, placeDetails, map);
                } else {
                  // Fallback to basic place info
                  updateInfoWindowContent(marker, place, map);
                }
              });
            } else {
              // No place found, show basic marker info
              // console.log('No place found for: ' + businessName);
              showBasicMarkerInfo(marker, businessName || 'Stockist');
            }
          };
          
          if (businessName) {
            // Search for the place using text search
            var request = {
              query: businessName,
              location: location,
              radius: 1000, // Increased radius to 1km
              fields: [
                'name',
                'formatted_address',
                'opening_hours',
                'formatted_phone_number',
                'website',
                'rating',
                'price_level',
                'photos',
                'reviews',
                'place_id' ],
            };
            placesService.textSearch(request, handlePlaceResults);
          } else {
            // Fallback: nearest place by location
            placesService.nearbySearch({
              location: location,
              radius: 120,
            }, handlePlaceResults);
          }
        }
        
        function showBasicMarkerInfo(marker, businessName) {
          var content = '<div class="enhanced-info-window">';
          content += '<h3 class="place-name">' + businessName + '</h3>';
          content += '<p><em>Business details not available from Google Places</em></p>';
          content += '</div>';
          var basicInfoWindow = (window.FWP_MAP && window.FWP_MAP.infoWindow) ? window.FWP_MAP.infoWindow : marker.infoWindow;
          if (!basicInfoWindow) {
            basicInfoWindow = new window.google.maps.InfoWindow();
          }
          basicInfoWindow.setContent(content);
        }
        
        function updateInfoWindowContent(marker, place, map) {
          // Get existing content from FacetWP to preserve it
          var infoWindow = (window.FWP_MAP && window.FWP_MAP.infoWindow) ? window.FWP_MAP.infoWindow : marker.infoWindow;
          var existingContent = '';
          
          if (infoWindow) {
            var currentContent = infoWindow.getContent();
            if (typeof currentContent === 'string' && !currentContent.includes('enhanced-info-window')) {
              existingContent = currentContent;
            }
          }
          
          // Create enhanced info window content
          var content = '<div class="enhanced-info-window">';
          
          // Include original content first (if it exists and doesn't look like our enhanced content)
          if (existingContent && !existingContent.includes('Loading business details')) {
            content += '<div class="original-content">' + existingContent + '</div>';
            content += '<hr style="margin: 10px 0; border: none; border-top: 1px solid #eee;">';
          }
          
          // Business name (only if different from original)
          content += '<h3 class="place-name">' + place.name + '</h3>';
          
          // Address
          if (place.formatted_address) {
            content += '<p class="place-address"><strong>Address:</strong> ' + place.formatted_address + '</p>';
          }
          
          // Phone number
          if (place.formatted_phone_number) {
            content += '<p class="place-phone"><strong>Phone:</strong> <a href="tel:' + place.formatted_phone_number + '">' + place.formatted_phone_number + '</a></p>';
          }
          
          // Website
          if (place.website) {
            content += '<p class="place-website"><strong>Website:</strong> <a href="' + place.website + '" target="_blank" rel="noopener">Visit Website</a></p>';
          }
          
          // Rating
          if (place.rating) {
            var stars = '';
            var fullStars = Math.floor(place.rating);
            var emptyStars = 5 - fullStars;
            for (var i = 0; i < fullStars; i++) {
              stars += '★';
            }
            for (var j = 0; j < emptyStars; j++) {
              stars += '☆';
            }
            content += '<p class="place-rating"><strong>Rating:</strong> ' + stars + ' (' + place.rating + '/5)</p>';
          }
          
          // Hours
          if (place.opening_hours) {
            var isOpen = place.opening_hours.isOpen();
            var openStatus = isOpen ? '<span style="color: green;">Open</span>' : '<span style="color: red;">Closed</span>';
            content += '<p class="place-hours"><strong>Currently:</strong> ' + openStatus + '</p>';
            
            // Show today's hours
            if (place.opening_hours.weekday_text) {
              var today = new Date().getDay();
              var todayHours = place.opening_hours.weekday_text[today === 0 ? 6 : today - 1]; // Adjust for Sunday = 0
              content += '<p class="place-today-hours"><small>' + todayHours + '</small></p>';
              
              // Add expandable hours
              content += '<details class="all-hours"><summary>All Hours</summary><ul>';
              for (var k = 0; k < place.opening_hours.weekday_text.length; k++) {
                content += '<li>' + place.opening_hours.weekday_text[k] + '</li>';
              }
              content += '</ul></details>';
            }
          }
          
          // Google Maps link
          if (place.url) {
            content += '<p class="place-directions"><a href="' + place.url + '" target="_blank" rel="noopener">Get Directions</a></p>';
          }
          
          content += '</div>';
          
          // Create or update info window
          var finalInfoWindow = (window.FWP_MAP && window.FWP_MAP.infoWindow) ? window.FWP_MAP.infoWindow : marker.infoWindow;
          if (!finalInfoWindow) {
            finalInfoWindow = new window.google.maps.InfoWindow();
          }
          finalInfoWindow.setContent(content);
          finalInfoWindow.open(map, marker);
        }
        
        // Hook into FacetWP's marker click events (trying multiple event names)
        $(document).on('facetwp_map/marker/click facetwp-map-marker-click', function(event, marker) {
          // console.log('FacetWP marker clicked via event: ' + event.type, marker);
          
          if (marker && marker._pbcPlacesBound && marker._pbcBusinessName && marker._pbcLocation) {
            var businessName = marker._pbcBusinessName;
            var location = marker._pbcLocation;
            var placesService = marker._pbcPlacesService;
            
            // console.log('Enhancing info window with Places data for: ' + businessName);
            
            // Allow FacetWP to show its info window first, then enhance it
            setTimeout(function() {
              enhanceInfoWindowWithPlaces(marker, businessName, location, placesService, marker._pbcMap || window.FWP_MAP.map);
            }, 50);
          } else {
            // console.log('Marker not enhanced with Places data or missing data:', {
            //   hasMarker: !!marker,
            //   bound: marker && marker._pbcPlacesBound,
            //   hasName: marker && !!marker._pbcBusinessName,
            //   hasLocation: marker && !!marker._pbcLocation,
            // });
          }
        });
        
        // Also try direct click listeners as fallback
        function addDirectClickListeners() {
          // console.log('addDirectClickListeners called');
          if (window.FWP_MAP && window.FWP_MAP.markersArray) {
            // console.log('Found FWP_MAP.markersArray with ' + window.FWP_MAP.markersArray.length + ' markers');
            // var listenersAdded = 0;
            
            window.FWP_MAP.markersArray.forEach(function(marker) {
              if (marker._pbcPlacesBound && !marker._pbcClickListenerAdded) {
                marker._pbcClickListenerAdded = true;
                // listenersAdded++;
                
                // console.log('Adding click listener to marker: ' + marker._pbcBusinessName);
                
                var clickHandler = function() {
                  // console.log('Direct marker click detected for: ' + marker._pbcBusinessName);
                  setTimeout(function() {
                    enhanceInfoWindowWithPlaces(
                      marker, 
                      marker._pbcBusinessName, 
                      marker._pbcLocation, 
                      marker._pbcPlacesService, 
                      marker._pbcMap || window.FWP_MAP.map
                    );
                  }, 50);
                };
                
                // Try multiple event types and methods
                var eventsAdded = 0;
                
                if (typeof marker.addEventListener === 'function') {
                  // console.log('Using addEventListener for marker: ' + marker._pbcBusinessName);
                  // Try multiple event types for modern Google Maps
                  marker.addEventListener('gmp-click', clickHandler);
                  marker.addEventListener('click', clickHandler);
                  marker.addEventListener('tap', clickHandler);
                  eventsAdded += 3;
                }
                
                if (typeof marker.addListener === 'function') {
                  // console.log('Using addListener for marker: ' + marker._pbcBusinessName);
                  marker.addListener('click', clickHandler);
                  marker.addListener('tap', clickHandler);
                  eventsAdded += 2;
                }
                
                // Try DOM events on the marker element if it has one
                if (marker.element && typeof marker.element.addEventListener === 'function') {
                  // console.log('Using DOM events on marker element for: ' + marker._pbcBusinessName);
                  marker.element.addEventListener('click', clickHandler);
                  marker.element.addEventListener('touchend', clickHandler);
                  eventsAdded += 2;
                }
                
                if (eventsAdded === 0) {
                  console.log('No click listener method available for marker: ' + marker._pbcBusinessName, {
                    hasAddEventListener: typeof marker.addEventListener === 'function',
                    hasAddListener: typeof marker.addListener === 'function',
                    hasElement: !!marker.element,
                    markerType: marker.constructor ? marker.constructor.name : typeof marker,
                  });
                } else {
                  // console.log('Added ' + eventsAdded + ' event listeners for marker: ' + marker._pbcBusinessName);
                }
              }
            });
            
            // console.log('Added click listeners to ' + listenersAdded + ' markers');
          } else {
            // console.log('FWP_MAP or markersArray not available');
          }
        }
        
        // Initialize after FacetWP map has loaded markers
        $(document).on('facetwp-maps-loaded', function() {
          setTimeout(function() {
            initGooglePlacesForFacetWP();
            addDirectClickListeners();
          }, 200);
        });
        
        // Also try after generic FacetWP refresh (map may render shortly after)
        $(document).on('facetwp-loaded', function() {
          setTimeout(function() {
            initGooglePlacesForFacetWP();
            addDirectClickListeners();
          }, 600);
        });
        
        // Fallback for edge cases where map is already present
        setTimeout(function() {
          initGooglePlacesForFacetWP();
          addDirectClickListeners();
        }, 1500);
        
        // Expose test function to global scope for debugging
        window.testPlacesIntegration = function(markerIndex) {
          markerIndex = markerIndex || 0;
          if (window.FWP_MAP && window.FWP_MAP.markersArray && window.FWP_MAP.markersArray[markerIndex]) {
            var marker = window.FWP_MAP.markersArray[markerIndex];
            // console.log('Testing Places integration for marker: ' + (marker._pbcBusinessName || 'Unknown'));
            
            if (marker._pbcBusinessName && marker._pbcLocation && marker._pbcPlacesService) {
              enhanceInfoWindowWithPlaces(
                marker, 
                marker._pbcBusinessName, 
                marker._pbcLocation, 
                marker._pbcPlacesService, 
                marker._pbcMap || window.FWP_MAP.map
              );
            } else {
              console.log('Marker missing required data:', {
                hasName: !!marker._pbcBusinessName,
                hasLocation: !!marker._pbcLocation,
                hasService: !!marker._pbcPlacesService,
              });
            }
          } else {
            console.log('No markers available for testing');
          }
        };

        if(window.location.hash == '#stockists'){
          $('body').addClass('quickview-open');
        }

        // Modal functionality
        $('.modal-trigger').on('click', function(e) {
          e.preventDefault();
          var targetModal = $(this).data('modal') || $(this).attr('href').substring(1);
          $(("#" + targetModal)).addClass('active');
          $('body').addClass('modal-open');
        });

        $('.modal-close, .modal-overlay').on('click', function(e) {
          if (e.target === this) {
            $('.modal-overlay').removeClass('active');
            $('body').removeClass('modal-open');
          }
        });

        // Close modal with Escape key
        $(document).keyup(function(e) {
          if (e.keyCode === 27) { // ESC key
            $('.modal-overlay').removeClass('active');
            $('body').removeClass('modal-open');
          }
        });

        // Custom controls for Quantity buttons
      function initQuantityButtons() {
        // Only add buttons to quantity inputs that don't already have them
        $('.quantity input').each(function() {
          var $input = $(this);
          var $quantityContainer = $input.closest('.quantity');
          
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
});

/***/ }),

/***/ "./scripts/routes/home.js":
/*!********************************!*\
  !*** ./scripts/routes/home.js ***!
  \********************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ({
  init: function init() {
    // JavaScript to be fired on the home page
  },
  finalize: function finalize() {
    // JavaScript to be fired on the home page, after the init JS
  },
});


/***/ }),

/***/ "./scripts/util/Router.js":
/*!********************************!*\
  !*** ./scripts/util/Router.js ***!
  \********************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _camelCase__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./camelCase */ "./scripts/util/camelCase.js");


/**
 * DOM-based Routing
 *
 * Based on {@link http://goo.gl/EUTi53|Markup-based Unobtrusive Comprehensive DOM-ready Execution} by Paul Irish
 *
 * The routing fires all common scripts, followed by the page specific scripts.
 * Add additional events for more control over timing e.g. a finalize event
 */
var Router = function Router(routes) {
  this.routes = routes;
};

/**
 * Fire Router events
 * @param {string} route DOM-based route derived from body classes (`<body class="...">`)
 * @param {string} [event] Events on the route. By default, `init` and `finalize` events are called.
 * @param {string} [arg] Any custom argument to be passed to the event.
 */
Router.prototype.fire = function fire (route, event, arg) {
    if ( event === void 0 ) event = 'init';

  document.dispatchEvent(new CustomEvent('routed', {
    bubbles: true,
    detail: {
      route: route,
      fn: event,
    },
  }));
    
  var fire = route !== '' && this.routes[route] && typeof this.routes[route][event] === 'function';
  if (fire) {
    this.routes[route][event](arg);
  }
};

/**
 * Automatically load and fire Router events
 *
 * Events are fired in the following order:
 ** common init
 ** page-specific init
 ** page-specific finalize
 ** common finalize
 */
Router.prototype.loadEvents = function loadEvents () {
    var this$1 = this;

  // Fire common init JS
  this.fire('common');

  // Fire page-specific init JS, and then finalize JS
  document.body.className
    .toLowerCase()
    .replace(/-/g, '_')
    .split(/\s+/)
    .map(_camelCase__WEBPACK_IMPORTED_MODULE_0__["default"])
    .forEach(function (className) {
      this$1.fire(className);
      this$1.fire(className, 'finalize');
    });

  // Fire common finalize JS
  this.fire('common', 'finalize');
};

/* harmony default export */ __webpack_exports__["default"] = (Router);


/***/ }),

/***/ "./scripts/util/camelCase.js":
/*!***********************************!*\
  !*** ./scripts/util/camelCase.js ***!
  \***********************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* export default binding */ __WEBPACK_DEFAULT_EXPORT__; }
/* harmony export */ });
/**
 * the most terrible camelizer on the internet, guaranteed!
 * @param {string} str String that isn't camel-case, e.g., CAMeL_CaSEiS-harD
 * @return {string} String converted to camel-case, e.g., camelCaseIsHard
 */
/* harmony default export */ function __WEBPACK_DEFAULT_EXPORT__(str) { return ("" + (str.charAt(0).toLowerCase()) + (str.replace(/[\W_]/g, '|').split('|')
  .map(function (part) { return ("" + (part.charAt(0).toUpperCase()) + (part.slice(1))); })
  .join('')
  .slice(1))); };


/***/ }),

/***/ "../../node_modules/slick-carousel/slick/slick.min.js":
/*!************************************************************!*\
  !*** ../../node_modules/slick-carousel/slick/slick.min.js ***!
  \************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!function(i){"use strict"; true?!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ "jquery")], __WEBPACK_AMD_DEFINE_FACTORY__ = (i),
		__WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ?
		(__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__)) : __WEBPACK_AMD_DEFINE_FACTORY__),
		__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__)):0}(function(i){"use strict";var e=window.Slick||{};(e=function(){var e=0;return function(t,o){var s,n=this;n.defaults={accessibility:!0,adaptiveHeight:!1,appendArrows:i(t),appendDots:i(t),arrows:!0,asNavFor:null,prevArrow:'<button class="slick-prev" aria-label="Previous" type="button">Previous</button>',nextArrow:'<button class="slick-next" aria-label="Next" type="button">Next</button>',autoplay:!1,autoplaySpeed:3e3,centerMode:!1,centerPadding:"50px",cssEase:"ease",customPaging:function(e,t){return i('<button type="button" />').text(t+1)},dots:!1,dotsClass:"slick-dots",draggable:!0,easing:"linear",edgeFriction:.35,fade:!1,focusOnSelect:!1,focusOnChange:!1,infinite:!0,initialSlide:0,lazyLoad:"ondemand",mobileFirst:!1,pauseOnHover:!0,pauseOnFocus:!0,pauseOnDotsHover:!1,respondTo:"window",responsive:null,rows:1,rtl:!1,slide:"",slidesPerRow:1,slidesToShow:1,slidesToScroll:1,speed:500,swipe:!0,swipeToSlide:!1,touchMove:!0,touchThreshold:5,useCSS:!0,useTransform:!0,variableWidth:!1,vertical:!1,verticalSwiping:!1,waitForAnimate:!0,zIndex:1e3},n.initials={animating:!1,dragging:!1,autoPlayTimer:null,currentDirection:0,currentLeft:null,currentSlide:0,direction:1,$dots:null,listWidth:null,listHeight:null,loadIndex:0,$nextArrow:null,$prevArrow:null,scrolling:!1,slideCount:null,slideWidth:null,$slideTrack:null,$slides:null,sliding:!1,slideOffset:0,swipeLeft:null,swiping:!1,$list:null,touchObject:{},transformsEnabled:!1,unslicked:!1},i.extend(n,n.initials),n.activeBreakpoint=null,n.animType=null,n.animProp=null,n.breakpoints=[],n.breakpointSettings=[],n.cssTransitions=!1,n.focussed=!1,n.interrupted=!1,n.hidden="hidden",n.paused=!0,n.positionProp=null,n.respondTo=null,n.rowCount=1,n.shouldClick=!0,n.$slider=i(t),n.$slidesCache=null,n.transformType=null,n.transitionType=null,n.visibilityChange="visibilitychange",n.windowWidth=0,n.windowTimer=null,s=i(t).data("slick")||{},n.options=i.extend({},n.defaults,o,s),n.currentSlide=n.options.initialSlide,n.originalSettings=n.options,void 0!==document.mozHidden?(n.hidden="mozHidden",n.visibilityChange="mozvisibilitychange"):void 0!==document.webkitHidden&&(n.hidden="webkitHidden",n.visibilityChange="webkitvisibilitychange"),n.autoPlay=i.proxy(n.autoPlay,n),n.autoPlayClear=i.proxy(n.autoPlayClear,n),n.autoPlayIterator=i.proxy(n.autoPlayIterator,n),n.changeSlide=i.proxy(n.changeSlide,n),n.clickHandler=i.proxy(n.clickHandler,n),n.selectHandler=i.proxy(n.selectHandler,n),n.setPosition=i.proxy(n.setPosition,n),n.swipeHandler=i.proxy(n.swipeHandler,n),n.dragHandler=i.proxy(n.dragHandler,n),n.keyHandler=i.proxy(n.keyHandler,n),n.instanceUid=e++,n.htmlExpr=/^(?:\s*(<[\w\W]+>)[^>]*)$/,n.registerBreakpoints(),n.init(!0)}}()).prototype.activateADA=function(){this.$slideTrack.find(".slick-active").attr({"aria-hidden":"false"}).find("a, input, button, select").attr({tabindex:"0"})},e.prototype.addSlide=e.prototype.slickAdd=function(e,t,o){var s=this;if("boolean"==typeof t)o=t,t=null;else if(t<0||t>=s.slideCount)return!1;s.unload(),"number"==typeof t?0===t&&0===s.$slides.length?i(e).appendTo(s.$slideTrack):o?i(e).insertBefore(s.$slides.eq(t)):i(e).insertAfter(s.$slides.eq(t)):!0===o?i(e).prependTo(s.$slideTrack):i(e).appendTo(s.$slideTrack),s.$slides=s.$slideTrack.children(this.options.slide),s.$slideTrack.children(this.options.slide).detach(),s.$slideTrack.append(s.$slides),s.$slides.each(function(e,t){i(t).attr("data-slick-index",e)}),s.$slidesCache=s.$slides,s.reinit()},e.prototype.animateHeight=function(){var i=this;if(1===i.options.slidesToShow&&!0===i.options.adaptiveHeight&&!1===i.options.vertical){var e=i.$slides.eq(i.currentSlide).outerHeight(!0);i.$list.animate({height:e},i.options.speed)}},e.prototype.animateSlide=function(e,t){var o={},s=this;s.animateHeight(),!0===s.options.rtl&&!1===s.options.vertical&&(e=-e),!1===s.transformsEnabled?!1===s.options.vertical?s.$slideTrack.animate({left:e},s.options.speed,s.options.easing,t):s.$slideTrack.animate({top:e},s.options.speed,s.options.easing,t):!1===s.cssTransitions?(!0===s.options.rtl&&(s.currentLeft=-s.currentLeft),i({animStart:s.currentLeft}).animate({animStart:e},{duration:s.options.speed,easing:s.options.easing,step:function(i){i=Math.ceil(i),!1===s.options.vertical?(o[s.animType]="translate("+i+"px, 0px)",s.$slideTrack.css(o)):(o[s.animType]="translate(0px,"+i+"px)",s.$slideTrack.css(o))},complete:function(){t&&t.call()}})):(s.applyTransition(),e=Math.ceil(e),!1===s.options.vertical?o[s.animType]="translate3d("+e+"px, 0px, 0px)":o[s.animType]="translate3d(0px,"+e+"px, 0px)",s.$slideTrack.css(o),t&&setTimeout(function(){s.disableTransition(),t.call()},s.options.speed))},e.prototype.getNavTarget=function(){var e=this,t=e.options.asNavFor;return t&&null!==t&&(t=i(t).not(e.$slider)),t},e.prototype.asNavFor=function(e){var t=this.getNavTarget();null!==t&&"object"==typeof t&&t.each(function(){var t=i(this).slick("getSlick");t.unslicked||t.slideHandler(e,!0)})},e.prototype.applyTransition=function(i){var e=this,t={};!1===e.options.fade?t[e.transitionType]=e.transformType+" "+e.options.speed+"ms "+e.options.cssEase:t[e.transitionType]="opacity "+e.options.speed+"ms "+e.options.cssEase,!1===e.options.fade?e.$slideTrack.css(t):e.$slides.eq(i).css(t)},e.prototype.autoPlay=function(){var i=this;i.autoPlayClear(),i.slideCount>i.options.slidesToShow&&(i.autoPlayTimer=setInterval(i.autoPlayIterator,i.options.autoplaySpeed))},e.prototype.autoPlayClear=function(){var i=this;i.autoPlayTimer&&clearInterval(i.autoPlayTimer)},e.prototype.autoPlayIterator=function(){var i=this,e=i.currentSlide+i.options.slidesToScroll;i.paused||i.interrupted||i.focussed||(!1===i.options.infinite&&(1===i.direction&&i.currentSlide+1===i.slideCount-1?i.direction=0:0===i.direction&&(e=i.currentSlide-i.options.slidesToScroll,i.currentSlide-1==0&&(i.direction=1))),i.slideHandler(e))},e.prototype.buildArrows=function(){var e=this;!0===e.options.arrows&&(e.$prevArrow=i(e.options.prevArrow).addClass("slick-arrow"),e.$nextArrow=i(e.options.nextArrow).addClass("slick-arrow"),e.slideCount>e.options.slidesToShow?(e.$prevArrow.removeClass("slick-hidden").removeAttr("aria-hidden tabindex"),e.$nextArrow.removeClass("slick-hidden").removeAttr("aria-hidden tabindex"),e.htmlExpr.test(e.options.prevArrow)&&e.$prevArrow.prependTo(e.options.appendArrows),e.htmlExpr.test(e.options.nextArrow)&&e.$nextArrow.appendTo(e.options.appendArrows),!0!==e.options.infinite&&e.$prevArrow.addClass("slick-disabled").attr("aria-disabled","true")):e.$prevArrow.add(e.$nextArrow).addClass("slick-hidden").attr({"aria-disabled":"true",tabindex:"-1"}))},e.prototype.buildDots=function(){var e,t,o=this;if(!0===o.options.dots){for(o.$slider.addClass("slick-dotted"),t=i("<ul />").addClass(o.options.dotsClass),e=0;e<=o.getDotCount();e+=1)t.append(i("<li />").append(o.options.customPaging.call(this,o,e)));o.$dots=t.appendTo(o.options.appendDots),o.$dots.find("li").first().addClass("slick-active")}},e.prototype.buildOut=function(){var e=this;e.$slides=e.$slider.children(e.options.slide+":not(.slick-cloned)").addClass("slick-slide"),e.slideCount=e.$slides.length,e.$slides.each(function(e,t){i(t).attr("data-slick-index",e).data("originalStyling",i(t).attr("style")||"")}),e.$slider.addClass("slick-slider"),e.$slideTrack=0===e.slideCount?i('<div class="slick-track"/>').appendTo(e.$slider):e.$slides.wrapAll('<div class="slick-track"/>').parent(),e.$list=e.$slideTrack.wrap('<div class="slick-list"/>').parent(),e.$slideTrack.css("opacity",0),!0!==e.options.centerMode&&!0!==e.options.swipeToSlide||(e.options.slidesToScroll=1),i("img[data-lazy]",e.$slider).not("[src]").addClass("slick-loading"),e.setupInfinite(),e.buildArrows(),e.buildDots(),e.updateDots(),e.setSlideClasses("number"==typeof e.currentSlide?e.currentSlide:0),!0===e.options.draggable&&e.$list.addClass("draggable")},e.prototype.buildRows=function(){var i,e,t,o,s,n,r,l=this;if(o=document.createDocumentFragment(),n=l.$slider.children(),l.options.rows>1){for(r=l.options.slidesPerRow*l.options.rows,s=Math.ceil(n.length/r),i=0;i<s;i++){var d=document.createElement("div");for(e=0;e<l.options.rows;e++){var a=document.createElement("div");for(t=0;t<l.options.slidesPerRow;t++){var c=i*r+(e*l.options.slidesPerRow+t);n.get(c)&&a.appendChild(n.get(c))}d.appendChild(a)}o.appendChild(d)}l.$slider.empty().append(o),l.$slider.children().children().children().css({width:100/l.options.slidesPerRow+"%",display:"inline-block"})}},e.prototype.checkResponsive=function(e,t){var o,s,n,r=this,l=!1,d=r.$slider.width(),a=window.innerWidth||i(window).width();if("window"===r.respondTo?n=a:"slider"===r.respondTo?n=d:"min"===r.respondTo&&(n=Math.min(a,d)),r.options.responsive&&r.options.responsive.length&&null!==r.options.responsive){s=null;for(o in r.breakpoints)r.breakpoints.hasOwnProperty(o)&&(!1===r.originalSettings.mobileFirst?n<r.breakpoints[o]&&(s=r.breakpoints[o]):n>r.breakpoints[o]&&(s=r.breakpoints[o]));null!==s?null!==r.activeBreakpoint?(s!==r.activeBreakpoint||t)&&(r.activeBreakpoint=s,"unslick"===r.breakpointSettings[s]?r.unslick(s):(r.options=i.extend({},r.originalSettings,r.breakpointSettings[s]),!0===e&&(r.currentSlide=r.options.initialSlide),r.refresh(e)),l=s):(r.activeBreakpoint=s,"unslick"===r.breakpointSettings[s]?r.unslick(s):(r.options=i.extend({},r.originalSettings,r.breakpointSettings[s]),!0===e&&(r.currentSlide=r.options.initialSlide),r.refresh(e)),l=s):null!==r.activeBreakpoint&&(r.activeBreakpoint=null,r.options=r.originalSettings,!0===e&&(r.currentSlide=r.options.initialSlide),r.refresh(e),l=s),e||!1===l||r.$slider.trigger("breakpoint",[r,l])}},e.prototype.changeSlide=function(e,t){var o,s,n,r=this,l=i(e.currentTarget);switch(l.is("a")&&e.preventDefault(),l.is("li")||(l=l.closest("li")),n=r.slideCount%r.options.slidesToScroll!=0,o=n?0:(r.slideCount-r.currentSlide)%r.options.slidesToScroll,e.data.message){case"previous":s=0===o?r.options.slidesToScroll:r.options.slidesToShow-o,r.slideCount>r.options.slidesToShow&&r.slideHandler(r.currentSlide-s,!1,t);break;case"next":s=0===o?r.options.slidesToScroll:o,r.slideCount>r.options.slidesToShow&&r.slideHandler(r.currentSlide+s,!1,t);break;case"index":var d=0===e.data.index?0:e.data.index||l.index()*r.options.slidesToScroll;r.slideHandler(r.checkNavigable(d),!1,t),l.children().trigger("focus");break;default:return}},e.prototype.checkNavigable=function(i){var e,t;if(e=this.getNavigableIndexes(),t=0,i>e[e.length-1])i=e[e.length-1];else for(var o in e){if(i<e[o]){i=t;break}t=e[o]}return i},e.prototype.cleanUpEvents=function(){var e=this;e.options.dots&&null!==e.$dots&&(i("li",e.$dots).off("click.slick",e.changeSlide).off("mouseenter.slick",i.proxy(e.interrupt,e,!0)).off("mouseleave.slick",i.proxy(e.interrupt,e,!1)),!0===e.options.accessibility&&e.$dots.off("keydown.slick",e.keyHandler)),e.$slider.off("focus.slick blur.slick"),!0===e.options.arrows&&e.slideCount>e.options.slidesToShow&&(e.$prevArrow&&e.$prevArrow.off("click.slick",e.changeSlide),e.$nextArrow&&e.$nextArrow.off("click.slick",e.changeSlide),!0===e.options.accessibility&&(e.$prevArrow&&e.$prevArrow.off("keydown.slick",e.keyHandler),e.$nextArrow&&e.$nextArrow.off("keydown.slick",e.keyHandler))),e.$list.off("touchstart.slick mousedown.slick",e.swipeHandler),e.$list.off("touchmove.slick mousemove.slick",e.swipeHandler),e.$list.off("touchend.slick mouseup.slick",e.swipeHandler),e.$list.off("touchcancel.slick mouseleave.slick",e.swipeHandler),e.$list.off("click.slick",e.clickHandler),i(document).off(e.visibilityChange,e.visibility),e.cleanUpSlideEvents(),!0===e.options.accessibility&&e.$list.off("keydown.slick",e.keyHandler),!0===e.options.focusOnSelect&&i(e.$slideTrack).children().off("click.slick",e.selectHandler),i(window).off("orientationchange.slick.slick-"+e.instanceUid,e.orientationChange),i(window).off("resize.slick.slick-"+e.instanceUid,e.resize),i("[draggable!=true]",e.$slideTrack).off("dragstart",e.preventDefault),i(window).off("load.slick.slick-"+e.instanceUid,e.setPosition)},e.prototype.cleanUpSlideEvents=function(){var e=this;e.$list.off("mouseenter.slick",i.proxy(e.interrupt,e,!0)),e.$list.off("mouseleave.slick",i.proxy(e.interrupt,e,!1))},e.prototype.cleanUpRows=function(){var i,e=this;e.options.rows>1&&((i=e.$slides.children().children()).removeAttr("style"),e.$slider.empty().append(i))},e.prototype.clickHandler=function(i){!1===this.shouldClick&&(i.stopImmediatePropagation(),i.stopPropagation(),i.preventDefault())},e.prototype.destroy=function(e){var t=this;t.autoPlayClear(),t.touchObject={},t.cleanUpEvents(),i(".slick-cloned",t.$slider).detach(),t.$dots&&t.$dots.remove(),t.$prevArrow&&t.$prevArrow.length&&(t.$prevArrow.removeClass("slick-disabled slick-arrow slick-hidden").removeAttr("aria-hidden aria-disabled tabindex").css("display",""),t.htmlExpr.test(t.options.prevArrow)&&t.$prevArrow.remove()),t.$nextArrow&&t.$nextArrow.length&&(t.$nextArrow.removeClass("slick-disabled slick-arrow slick-hidden").removeAttr("aria-hidden aria-disabled tabindex").css("display",""),t.htmlExpr.test(t.options.nextArrow)&&t.$nextArrow.remove()),t.$slides&&(t.$slides.removeClass("slick-slide slick-active slick-center slick-visible slick-current").removeAttr("aria-hidden").removeAttr("data-slick-index").each(function(){i(this).attr("style",i(this).data("originalStyling"))}),t.$slideTrack.children(this.options.slide).detach(),t.$slideTrack.detach(),t.$list.detach(),t.$slider.append(t.$slides)),t.cleanUpRows(),t.$slider.removeClass("slick-slider"),t.$slider.removeClass("slick-initialized"),t.$slider.removeClass("slick-dotted"),t.unslicked=!0,e||t.$slider.trigger("destroy",[t])},e.prototype.disableTransition=function(i){var e=this,t={};t[e.transitionType]="",!1===e.options.fade?e.$slideTrack.css(t):e.$slides.eq(i).css(t)},e.prototype.fadeSlide=function(i,e){var t=this;!1===t.cssTransitions?(t.$slides.eq(i).css({zIndex:t.options.zIndex}),t.$slides.eq(i).animate({opacity:1},t.options.speed,t.options.easing,e)):(t.applyTransition(i),t.$slides.eq(i).css({opacity:1,zIndex:t.options.zIndex}),e&&setTimeout(function(){t.disableTransition(i),e.call()},t.options.speed))},e.prototype.fadeSlideOut=function(i){var e=this;!1===e.cssTransitions?e.$slides.eq(i).animate({opacity:0,zIndex:e.options.zIndex-2},e.options.speed,e.options.easing):(e.applyTransition(i),e.$slides.eq(i).css({opacity:0,zIndex:e.options.zIndex-2}))},e.prototype.filterSlides=e.prototype.slickFilter=function(i){var e=this;null!==i&&(e.$slidesCache=e.$slides,e.unload(),e.$slideTrack.children(this.options.slide).detach(),e.$slidesCache.filter(i).appendTo(e.$slideTrack),e.reinit())},e.prototype.focusHandler=function(){var e=this;e.$slider.off("focus.slick blur.slick").on("focus.slick blur.slick","*",function(t){t.stopImmediatePropagation();var o=i(this);setTimeout(function(){e.options.pauseOnFocus&&(e.focussed=o.is(":focus"),e.autoPlay())},0)})},e.prototype.getCurrent=e.prototype.slickCurrentSlide=function(){return this.currentSlide},e.prototype.getDotCount=function(){var i=this,e=0,t=0,o=0;if(!0===i.options.infinite)if(i.slideCount<=i.options.slidesToShow)++o;else for(;e<i.slideCount;)++o,e=t+i.options.slidesToScroll,t+=i.options.slidesToScroll<=i.options.slidesToShow?i.options.slidesToScroll:i.options.slidesToShow;else if(!0===i.options.centerMode)o=i.slideCount;else if(i.options.asNavFor)for(;e<i.slideCount;)++o,e=t+i.options.slidesToScroll,t+=i.options.slidesToScroll<=i.options.slidesToShow?i.options.slidesToScroll:i.options.slidesToShow;else o=1+Math.ceil((i.slideCount-i.options.slidesToShow)/i.options.slidesToScroll);return o-1},e.prototype.getLeft=function(i){var e,t,o,s,n=this,r=0;return n.slideOffset=0,t=n.$slides.first().outerHeight(!0),!0===n.options.infinite?(n.slideCount>n.options.slidesToShow&&(n.slideOffset=n.slideWidth*n.options.slidesToShow*-1,s=-1,!0===n.options.vertical&&!0===n.options.centerMode&&(2===n.options.slidesToShow?s=-1.5:1===n.options.slidesToShow&&(s=-2)),r=t*n.options.slidesToShow*s),n.slideCount%n.options.slidesToScroll!=0&&i+n.options.slidesToScroll>n.slideCount&&n.slideCount>n.options.slidesToShow&&(i>n.slideCount?(n.slideOffset=(n.options.slidesToShow-(i-n.slideCount))*n.slideWidth*-1,r=(n.options.slidesToShow-(i-n.slideCount))*t*-1):(n.slideOffset=n.slideCount%n.options.slidesToScroll*n.slideWidth*-1,r=n.slideCount%n.options.slidesToScroll*t*-1))):i+n.options.slidesToShow>n.slideCount&&(n.slideOffset=(i+n.options.slidesToShow-n.slideCount)*n.slideWidth,r=(i+n.options.slidesToShow-n.slideCount)*t),n.slideCount<=n.options.slidesToShow&&(n.slideOffset=0,r=0),!0===n.options.centerMode&&n.slideCount<=n.options.slidesToShow?n.slideOffset=n.slideWidth*Math.floor(n.options.slidesToShow)/2-n.slideWidth*n.slideCount/2:!0===n.options.centerMode&&!0===n.options.infinite?n.slideOffset+=n.slideWidth*Math.floor(n.options.slidesToShow/2)-n.slideWidth:!0===n.options.centerMode&&(n.slideOffset=0,n.slideOffset+=n.slideWidth*Math.floor(n.options.slidesToShow/2)),e=!1===n.options.vertical?i*n.slideWidth*-1+n.slideOffset:i*t*-1+r,!0===n.options.variableWidth&&(o=n.slideCount<=n.options.slidesToShow||!1===n.options.infinite?n.$slideTrack.children(".slick-slide").eq(i):n.$slideTrack.children(".slick-slide").eq(i+n.options.slidesToShow),e=!0===n.options.rtl?o[0]?-1*(n.$slideTrack.width()-o[0].offsetLeft-o.width()):0:o[0]?-1*o[0].offsetLeft:0,!0===n.options.centerMode&&(o=n.slideCount<=n.options.slidesToShow||!1===n.options.infinite?n.$slideTrack.children(".slick-slide").eq(i):n.$slideTrack.children(".slick-slide").eq(i+n.options.slidesToShow+1),e=!0===n.options.rtl?o[0]?-1*(n.$slideTrack.width()-o[0].offsetLeft-o.width()):0:o[0]?-1*o[0].offsetLeft:0,e+=(n.$list.width()-o.outerWidth())/2)),e},e.prototype.getOption=e.prototype.slickGetOption=function(i){return this.options[i]},e.prototype.getNavigableIndexes=function(){var i,e=this,t=0,o=0,s=[];for(!1===e.options.infinite?i=e.slideCount:(t=-1*e.options.slidesToScroll,o=-1*e.options.slidesToScroll,i=2*e.slideCount);t<i;)s.push(t),t=o+e.options.slidesToScroll,o+=e.options.slidesToScroll<=e.options.slidesToShow?e.options.slidesToScroll:e.options.slidesToShow;return s},e.prototype.getSlick=function(){return this},e.prototype.getSlideCount=function(){var e,t,o=this;return t=!0===o.options.centerMode?o.slideWidth*Math.floor(o.options.slidesToShow/2):0,!0===o.options.swipeToSlide?(o.$slideTrack.find(".slick-slide").each(function(s,n){if(n.offsetLeft-t+i(n).outerWidth()/2>-1*o.swipeLeft)return e=n,!1}),Math.abs(i(e).attr("data-slick-index")-o.currentSlide)||1):o.options.slidesToScroll},e.prototype.goTo=e.prototype.slickGoTo=function(i,e){this.changeSlide({data:{message:"index",index:parseInt(i)}},e)},e.prototype.init=function(e){var t=this;i(t.$slider).hasClass("slick-initialized")||(i(t.$slider).addClass("slick-initialized"),t.buildRows(),t.buildOut(),t.setProps(),t.startLoad(),t.loadSlider(),t.initializeEvents(),t.updateArrows(),t.updateDots(),t.checkResponsive(!0),t.focusHandler()),e&&t.$slider.trigger("init",[t]),!0===t.options.accessibility&&t.initADA(),t.options.autoplay&&(t.paused=!1,t.autoPlay())},e.prototype.initADA=function(){var e=this,t=Math.ceil(e.slideCount/e.options.slidesToShow),o=e.getNavigableIndexes().filter(function(i){return i>=0&&i<e.slideCount});e.$slides.add(e.$slideTrack.find(".slick-cloned")).attr({"aria-hidden":"true",tabindex:"-1"}).find("a, input, button, select").attr({tabindex:"-1"}),null!==e.$dots&&(e.$slides.not(e.$slideTrack.find(".slick-cloned")).each(function(t){var s=o.indexOf(t);i(this).attr({role:"tabpanel",id:"slick-slide"+e.instanceUid+t,tabindex:-1}),-1!==s&&i(this).attr({"aria-describedby":"slick-slide-control"+e.instanceUid+s})}),e.$dots.attr("role","tablist").find("li").each(function(s){var n=o[s];i(this).attr({role:"presentation"}),i(this).find("button").first().attr({role:"tab",id:"slick-slide-control"+e.instanceUid+s,"aria-controls":"slick-slide"+e.instanceUid+n,"aria-label":s+1+" of "+t,"aria-selected":null,tabindex:"-1"})}).eq(e.currentSlide).find("button").attr({"aria-selected":"true",tabindex:"0"}).end());for(var s=e.currentSlide,n=s+e.options.slidesToShow;s<n;s++)e.$slides.eq(s).attr("tabindex",0);e.activateADA()},e.prototype.initArrowEvents=function(){var i=this;!0===i.options.arrows&&i.slideCount>i.options.slidesToShow&&(i.$prevArrow.off("click.slick").on("click.slick",{message:"previous"},i.changeSlide),i.$nextArrow.off("click.slick").on("click.slick",{message:"next"},i.changeSlide),!0===i.options.accessibility&&(i.$prevArrow.on("keydown.slick",i.keyHandler),i.$nextArrow.on("keydown.slick",i.keyHandler)))},e.prototype.initDotEvents=function(){var e=this;!0===e.options.dots&&(i("li",e.$dots).on("click.slick",{message:"index"},e.changeSlide),!0===e.options.accessibility&&e.$dots.on("keydown.slick",e.keyHandler)),!0===e.options.dots&&!0===e.options.pauseOnDotsHover&&i("li",e.$dots).on("mouseenter.slick",i.proxy(e.interrupt,e,!0)).on("mouseleave.slick",i.proxy(e.interrupt,e,!1))},e.prototype.initSlideEvents=function(){var e=this;e.options.pauseOnHover&&(e.$list.on("mouseenter.slick",i.proxy(e.interrupt,e,!0)),e.$list.on("mouseleave.slick",i.proxy(e.interrupt,e,!1)))},e.prototype.initializeEvents=function(){var e=this;e.initArrowEvents(),e.initDotEvents(),e.initSlideEvents(),e.$list.on("touchstart.slick mousedown.slick",{action:"start"},e.swipeHandler),e.$list.on("touchmove.slick mousemove.slick",{action:"move"},e.swipeHandler),e.$list.on("touchend.slick mouseup.slick",{action:"end"},e.swipeHandler),e.$list.on("touchcancel.slick mouseleave.slick",{action:"end"},e.swipeHandler),e.$list.on("click.slick",e.clickHandler),i(document).on(e.visibilityChange,i.proxy(e.visibility,e)),!0===e.options.accessibility&&e.$list.on("keydown.slick",e.keyHandler),!0===e.options.focusOnSelect&&i(e.$slideTrack).children().on("click.slick",e.selectHandler),i(window).on("orientationchange.slick.slick-"+e.instanceUid,i.proxy(e.orientationChange,e)),i(window).on("resize.slick.slick-"+e.instanceUid,i.proxy(e.resize,e)),i("[draggable!=true]",e.$slideTrack).on("dragstart",e.preventDefault),i(window).on("load.slick.slick-"+e.instanceUid,e.setPosition),i(e.setPosition)},e.prototype.initUI=function(){var i=this;!0===i.options.arrows&&i.slideCount>i.options.slidesToShow&&(i.$prevArrow.show(),i.$nextArrow.show()),!0===i.options.dots&&i.slideCount>i.options.slidesToShow&&i.$dots.show()},e.prototype.keyHandler=function(i){var e=this;i.target.tagName.match("TEXTAREA|INPUT|SELECT")||(37===i.keyCode&&!0===e.options.accessibility?e.changeSlide({data:{message:!0===e.options.rtl?"next":"previous"}}):39===i.keyCode&&!0===e.options.accessibility&&e.changeSlide({data:{message:!0===e.options.rtl?"previous":"next"}}))},e.prototype.lazyLoad=function(){function e(e){i("img[data-lazy]",e).each(function(){var e=i(this),t=i(this).attr("data-lazy"),o=i(this).attr("data-srcset"),s=i(this).attr("data-sizes")||n.$slider.attr("data-sizes"),r=document.createElement("img");r.onload=function(){e.animate({opacity:0},100,function(){o&&(e.attr("srcset",o),s&&e.attr("sizes",s)),e.attr("src",t).animate({opacity:1},200,function(){e.removeAttr("data-lazy data-srcset data-sizes").removeClass("slick-loading")}),n.$slider.trigger("lazyLoaded",[n,e,t])})},r.onerror=function(){e.removeAttr("data-lazy").removeClass("slick-loading").addClass("slick-lazyload-error"),n.$slider.trigger("lazyLoadError",[n,e,t])},r.src=t})}var t,o,s,n=this;if(!0===n.options.centerMode?!0===n.options.infinite?s=(o=n.currentSlide+(n.options.slidesToShow/2+1))+n.options.slidesToShow+2:(o=Math.max(0,n.currentSlide-(n.options.slidesToShow/2+1)),s=n.options.slidesToShow/2+1+2+n.currentSlide):(o=n.options.infinite?n.options.slidesToShow+n.currentSlide:n.currentSlide,s=Math.ceil(o+n.options.slidesToShow),!0===n.options.fade&&(o>0&&o--,s<=n.slideCount&&s++)),t=n.$slider.find(".slick-slide").slice(o,s),"anticipated"===n.options.lazyLoad)for(var r=o-1,l=s,d=n.$slider.find(".slick-slide"),a=0;a<n.options.slidesToScroll;a++)r<0&&(r=n.slideCount-1),t=(t=t.add(d.eq(r))).add(d.eq(l)),r--,l++;e(t),n.slideCount<=n.options.slidesToShow?e(n.$slider.find(".slick-slide")):n.currentSlide>=n.slideCount-n.options.slidesToShow?e(n.$slider.find(".slick-cloned").slice(0,n.options.slidesToShow)):0===n.currentSlide&&e(n.$slider.find(".slick-cloned").slice(-1*n.options.slidesToShow))},e.prototype.loadSlider=function(){var i=this;i.setPosition(),i.$slideTrack.css({opacity:1}),i.$slider.removeClass("slick-loading"),i.initUI(),"progressive"===i.options.lazyLoad&&i.progressiveLazyLoad()},e.prototype.next=e.prototype.slickNext=function(){this.changeSlide({data:{message:"next"}})},e.prototype.orientationChange=function(){var i=this;i.checkResponsive(),i.setPosition()},e.prototype.pause=e.prototype.slickPause=function(){var i=this;i.autoPlayClear(),i.paused=!0},e.prototype.play=e.prototype.slickPlay=function(){var i=this;i.autoPlay(),i.options.autoplay=!0,i.paused=!1,i.focussed=!1,i.interrupted=!1},e.prototype.postSlide=function(e){var t=this;t.unslicked||(t.$slider.trigger("afterChange",[t,e]),t.animating=!1,t.slideCount>t.options.slidesToShow&&t.setPosition(),t.swipeLeft=null,t.options.autoplay&&t.autoPlay(),!0===t.options.accessibility&&(t.initADA(),t.options.focusOnChange&&i(t.$slides.get(t.currentSlide)).attr("tabindex",0).focus()))},e.prototype.prev=e.prototype.slickPrev=function(){this.changeSlide({data:{message:"previous"}})},e.prototype.preventDefault=function(i){i.preventDefault()},e.prototype.progressiveLazyLoad=function(e){e=e||1;var t,o,s,n,r,l=this,d=i("img[data-lazy]",l.$slider);d.length?(t=d.first(),o=t.attr("data-lazy"),s=t.attr("data-srcset"),n=t.attr("data-sizes")||l.$slider.attr("data-sizes"),(r=document.createElement("img")).onload=function(){s&&(t.attr("srcset",s),n&&t.attr("sizes",n)),t.attr("src",o).removeAttr("data-lazy data-srcset data-sizes").removeClass("slick-loading"),!0===l.options.adaptiveHeight&&l.setPosition(),l.$slider.trigger("lazyLoaded",[l,t,o]),l.progressiveLazyLoad()},r.onerror=function(){e<3?setTimeout(function(){l.progressiveLazyLoad(e+1)},500):(t.removeAttr("data-lazy").removeClass("slick-loading").addClass("slick-lazyload-error"),l.$slider.trigger("lazyLoadError",[l,t,o]),l.progressiveLazyLoad())},r.src=o):l.$slider.trigger("allImagesLoaded",[l])},e.prototype.refresh=function(e){var t,o,s=this;o=s.slideCount-s.options.slidesToShow,!s.options.infinite&&s.currentSlide>o&&(s.currentSlide=o),s.slideCount<=s.options.slidesToShow&&(s.currentSlide=0),t=s.currentSlide,s.destroy(!0),i.extend(s,s.initials,{currentSlide:t}),s.init(),e||s.changeSlide({data:{message:"index",index:t}},!1)},e.prototype.registerBreakpoints=function(){var e,t,o,s=this,n=s.options.responsive||null;if("array"===i.type(n)&&n.length){s.respondTo=s.options.respondTo||"window";for(e in n)if(o=s.breakpoints.length-1,n.hasOwnProperty(e)){for(t=n[e].breakpoint;o>=0;)s.breakpoints[o]&&s.breakpoints[o]===t&&s.breakpoints.splice(o,1),o--;s.breakpoints.push(t),s.breakpointSettings[t]=n[e].settings}s.breakpoints.sort(function(i,e){return s.options.mobileFirst?i-e:e-i})}},e.prototype.reinit=function(){var e=this;e.$slides=e.$slideTrack.children(e.options.slide).addClass("slick-slide"),e.slideCount=e.$slides.length,e.currentSlide>=e.slideCount&&0!==e.currentSlide&&(e.currentSlide=e.currentSlide-e.options.slidesToScroll),e.slideCount<=e.options.slidesToShow&&(e.currentSlide=0),e.registerBreakpoints(),e.setProps(),e.setupInfinite(),e.buildArrows(),e.updateArrows(),e.initArrowEvents(),e.buildDots(),e.updateDots(),e.initDotEvents(),e.cleanUpSlideEvents(),e.initSlideEvents(),e.checkResponsive(!1,!0),!0===e.options.focusOnSelect&&i(e.$slideTrack).children().on("click.slick",e.selectHandler),e.setSlideClasses("number"==typeof e.currentSlide?e.currentSlide:0),e.setPosition(),e.focusHandler(),e.paused=!e.options.autoplay,e.autoPlay(),e.$slider.trigger("reInit",[e])},e.prototype.resize=function(){var e=this;i(window).width()!==e.windowWidth&&(clearTimeout(e.windowDelay),e.windowDelay=window.setTimeout(function(){e.windowWidth=i(window).width(),e.checkResponsive(),e.unslicked||e.setPosition()},50))},e.prototype.removeSlide=e.prototype.slickRemove=function(i,e,t){var o=this;if(i="boolean"==typeof i?!0===(e=i)?0:o.slideCount-1:!0===e?--i:i,o.slideCount<1||i<0||i>o.slideCount-1)return!1;o.unload(),!0===t?o.$slideTrack.children().remove():o.$slideTrack.children(this.options.slide).eq(i).remove(),o.$slides=o.$slideTrack.children(this.options.slide),o.$slideTrack.children(this.options.slide).detach(),o.$slideTrack.append(o.$slides),o.$slidesCache=o.$slides,o.reinit()},e.prototype.setCSS=function(i){var e,t,o=this,s={};!0===o.options.rtl&&(i=-i),e="left"==o.positionProp?Math.ceil(i)+"px":"0px",t="top"==o.positionProp?Math.ceil(i)+"px":"0px",s[o.positionProp]=i,!1===o.transformsEnabled?o.$slideTrack.css(s):(s={},!1===o.cssTransitions?(s[o.animType]="translate("+e+", "+t+")",o.$slideTrack.css(s)):(s[o.animType]="translate3d("+e+", "+t+", 0px)",o.$slideTrack.css(s)))},e.prototype.setDimensions=function(){var i=this;!1===i.options.vertical?!0===i.options.centerMode&&i.$list.css({padding:"0px "+i.options.centerPadding}):(i.$list.height(i.$slides.first().outerHeight(!0)*i.options.slidesToShow),!0===i.options.centerMode&&i.$list.css({padding:i.options.centerPadding+" 0px"})),i.listWidth=i.$list.width(),i.listHeight=i.$list.height(),!1===i.options.vertical&&!1===i.options.variableWidth?(i.slideWidth=Math.ceil(i.listWidth/i.options.slidesToShow),i.$slideTrack.width(Math.ceil(i.slideWidth*i.$slideTrack.children(".slick-slide").length))):!0===i.options.variableWidth?i.$slideTrack.width(5e3*i.slideCount):(i.slideWidth=Math.ceil(i.listWidth),i.$slideTrack.height(Math.ceil(i.$slides.first().outerHeight(!0)*i.$slideTrack.children(".slick-slide").length)));var e=i.$slides.first().outerWidth(!0)-i.$slides.first().width();!1===i.options.variableWidth&&i.$slideTrack.children(".slick-slide").width(i.slideWidth-e)},e.prototype.setFade=function(){var e,t=this;t.$slides.each(function(o,s){e=t.slideWidth*o*-1,!0===t.options.rtl?i(s).css({position:"relative",right:e,top:0,zIndex:t.options.zIndex-2,opacity:0}):i(s).css({position:"relative",left:e,top:0,zIndex:t.options.zIndex-2,opacity:0})}),t.$slides.eq(t.currentSlide).css({zIndex:t.options.zIndex-1,opacity:1})},e.prototype.setHeight=function(){var i=this;if(1===i.options.slidesToShow&&!0===i.options.adaptiveHeight&&!1===i.options.vertical){var e=i.$slides.eq(i.currentSlide).outerHeight(!0);i.$list.css("height",e)}},e.prototype.setOption=e.prototype.slickSetOption=function(){var e,t,o,s,n,r=this,l=!1;if("object"===i.type(arguments[0])?(o=arguments[0],l=arguments[1],n="multiple"):"string"===i.type(arguments[0])&&(o=arguments[0],s=arguments[1],l=arguments[2],"responsive"===arguments[0]&&"array"===i.type(arguments[1])?n="responsive":void 0!==arguments[1]&&(n="single")),"single"===n)r.options[o]=s;else if("multiple"===n)i.each(o,function(i,e){r.options[i]=e});else if("responsive"===n)for(t in s)if("array"!==i.type(r.options.responsive))r.options.responsive=[s[t]];else{for(e=r.options.responsive.length-1;e>=0;)r.options.responsive[e].breakpoint===s[t].breakpoint&&r.options.responsive.splice(e,1),e--;r.options.responsive.push(s[t])}l&&(r.unload(),r.reinit())},e.prototype.setPosition=function(){var i=this;i.setDimensions(),i.setHeight(),!1===i.options.fade?i.setCSS(i.getLeft(i.currentSlide)):i.setFade(),i.$slider.trigger("setPosition",[i])},e.prototype.setProps=function(){var i=this,e=document.body.style;i.positionProp=!0===i.options.vertical?"top":"left","top"===i.positionProp?i.$slider.addClass("slick-vertical"):i.$slider.removeClass("slick-vertical"),void 0===e.WebkitTransition&&void 0===e.MozTransition&&void 0===e.msTransition||!0===i.options.useCSS&&(i.cssTransitions=!0),i.options.fade&&("number"==typeof i.options.zIndex?i.options.zIndex<3&&(i.options.zIndex=3):i.options.zIndex=i.defaults.zIndex),void 0!==e.OTransform&&(i.animType="OTransform",i.transformType="-o-transform",i.transitionType="OTransition",void 0===e.perspectiveProperty&&void 0===e.webkitPerspective&&(i.animType=!1)),void 0!==e.MozTransform&&(i.animType="MozTransform",i.transformType="-moz-transform",i.transitionType="MozTransition",void 0===e.perspectiveProperty&&void 0===e.MozPerspective&&(i.animType=!1)),void 0!==e.webkitTransform&&(i.animType="webkitTransform",i.transformType="-webkit-transform",i.transitionType="webkitTransition",void 0===e.perspectiveProperty&&void 0===e.webkitPerspective&&(i.animType=!1)),void 0!==e.msTransform&&(i.animType="msTransform",i.transformType="-ms-transform",i.transitionType="msTransition",void 0===e.msTransform&&(i.animType=!1)),void 0!==e.transform&&!1!==i.animType&&(i.animType="transform",i.transformType="transform",i.transitionType="transition"),i.transformsEnabled=i.options.useTransform&&null!==i.animType&&!1!==i.animType},e.prototype.setSlideClasses=function(i){var e,t,o,s,n=this;if(t=n.$slider.find(".slick-slide").removeClass("slick-active slick-center slick-current").attr("aria-hidden","true"),n.$slides.eq(i).addClass("slick-current"),!0===n.options.centerMode){var r=n.options.slidesToShow%2==0?1:0;e=Math.floor(n.options.slidesToShow/2),!0===n.options.infinite&&(i>=e&&i<=n.slideCount-1-e?n.$slides.slice(i-e+r,i+e+1).addClass("slick-active").attr("aria-hidden","false"):(o=n.options.slidesToShow+i,t.slice(o-e+1+r,o+e+2).addClass("slick-active").attr("aria-hidden","false")),0===i?t.eq(t.length-1-n.options.slidesToShow).addClass("slick-center"):i===n.slideCount-1&&t.eq(n.options.slidesToShow).addClass("slick-center")),n.$slides.eq(i).addClass("slick-center")}else i>=0&&i<=n.slideCount-n.options.slidesToShow?n.$slides.slice(i,i+n.options.slidesToShow).addClass("slick-active").attr("aria-hidden","false"):t.length<=n.options.slidesToShow?t.addClass("slick-active").attr("aria-hidden","false"):(s=n.slideCount%n.options.slidesToShow,o=!0===n.options.infinite?n.options.slidesToShow+i:i,n.options.slidesToShow==n.options.slidesToScroll&&n.slideCount-i<n.options.slidesToShow?t.slice(o-(n.options.slidesToShow-s),o+s).addClass("slick-active").attr("aria-hidden","false"):t.slice(o,o+n.options.slidesToShow).addClass("slick-active").attr("aria-hidden","false"));"ondemand"!==n.options.lazyLoad&&"anticipated"!==n.options.lazyLoad||n.lazyLoad()},e.prototype.setupInfinite=function(){var e,t,o,s=this;if(!0===s.options.fade&&(s.options.centerMode=!1),!0===s.options.infinite&&!1===s.options.fade&&(t=null,s.slideCount>s.options.slidesToShow)){for(o=!0===s.options.centerMode?s.options.slidesToShow+1:s.options.slidesToShow,e=s.slideCount;e>s.slideCount-o;e-=1)t=e-1,i(s.$slides[t]).clone(!0).attr("id","").attr("data-slick-index",t-s.slideCount).prependTo(s.$slideTrack).addClass("slick-cloned");for(e=0;e<o+s.slideCount;e+=1)t=e,i(s.$slides[t]).clone(!0).attr("id","").attr("data-slick-index",t+s.slideCount).appendTo(s.$slideTrack).addClass("slick-cloned");s.$slideTrack.find(".slick-cloned").find("[id]").each(function(){i(this).attr("id","")})}},e.prototype.interrupt=function(i){var e=this;i||e.autoPlay(),e.interrupted=i},e.prototype.selectHandler=function(e){var t=this,o=i(e.target).is(".slick-slide")?i(e.target):i(e.target).parents(".slick-slide"),s=parseInt(o.attr("data-slick-index"));s||(s=0),t.slideCount<=t.options.slidesToShow?t.slideHandler(s,!1,!0):t.slideHandler(s)},e.prototype.slideHandler=function(i,e,t){var o,s,n,r,l,d=null,a=this;if(e=e||!1,!(!0===a.animating&&!0===a.options.waitForAnimate||!0===a.options.fade&&a.currentSlide===i))if(!1===e&&a.asNavFor(i),o=i,d=a.getLeft(o),r=a.getLeft(a.currentSlide),a.currentLeft=null===a.swipeLeft?r:a.swipeLeft,!1===a.options.infinite&&!1===a.options.centerMode&&(i<0||i>a.getDotCount()*a.options.slidesToScroll))!1===a.options.fade&&(o=a.currentSlide,!0!==t?a.animateSlide(r,function(){a.postSlide(o)}):a.postSlide(o));else if(!1===a.options.infinite&&!0===a.options.centerMode&&(i<0||i>a.slideCount-a.options.slidesToScroll))!1===a.options.fade&&(o=a.currentSlide,!0!==t?a.animateSlide(r,function(){a.postSlide(o)}):a.postSlide(o));else{if(a.options.autoplay&&clearInterval(a.autoPlayTimer),s=o<0?a.slideCount%a.options.slidesToScroll!=0?a.slideCount-a.slideCount%a.options.slidesToScroll:a.slideCount+o:o>=a.slideCount?a.slideCount%a.options.slidesToScroll!=0?0:o-a.slideCount:o,a.animating=!0,a.$slider.trigger("beforeChange",[a,a.currentSlide,s]),n=a.currentSlide,a.currentSlide=s,a.setSlideClasses(a.currentSlide),a.options.asNavFor&&(l=(l=a.getNavTarget()).slick("getSlick")).slideCount<=l.options.slidesToShow&&l.setSlideClasses(a.currentSlide),a.updateDots(),a.updateArrows(),!0===a.options.fade)return!0!==t?(a.fadeSlideOut(n),a.fadeSlide(s,function(){a.postSlide(s)})):a.postSlide(s),void a.animateHeight();!0!==t?a.animateSlide(d,function(){a.postSlide(s)}):a.postSlide(s)}},e.prototype.startLoad=function(){var i=this;!0===i.options.arrows&&i.slideCount>i.options.slidesToShow&&(i.$prevArrow.hide(),i.$nextArrow.hide()),!0===i.options.dots&&i.slideCount>i.options.slidesToShow&&i.$dots.hide(),i.$slider.addClass("slick-loading")},e.prototype.swipeDirection=function(){var i,e,t,o,s=this;return i=s.touchObject.startX-s.touchObject.curX,e=s.touchObject.startY-s.touchObject.curY,t=Math.atan2(e,i),(o=Math.round(180*t/Math.PI))<0&&(o=360-Math.abs(o)),o<=45&&o>=0?!1===s.options.rtl?"left":"right":o<=360&&o>=315?!1===s.options.rtl?"left":"right":o>=135&&o<=225?!1===s.options.rtl?"right":"left":!0===s.options.verticalSwiping?o>=35&&o<=135?"down":"up":"vertical"},e.prototype.swipeEnd=function(i){var e,t,o=this;if(o.dragging=!1,o.swiping=!1,o.scrolling)return o.scrolling=!1,!1;if(o.interrupted=!1,o.shouldClick=!(o.touchObject.swipeLength>10),void 0===o.touchObject.curX)return!1;if(!0===o.touchObject.edgeHit&&o.$slider.trigger("edge",[o,o.swipeDirection()]),o.touchObject.swipeLength>=o.touchObject.minSwipe){switch(t=o.swipeDirection()){case"left":case"down":e=o.options.swipeToSlide?o.checkNavigable(o.currentSlide+o.getSlideCount()):o.currentSlide+o.getSlideCount(),o.currentDirection=0;break;case"right":case"up":e=o.options.swipeToSlide?o.checkNavigable(o.currentSlide-o.getSlideCount()):o.currentSlide-o.getSlideCount(),o.currentDirection=1}"vertical"!=t&&(o.slideHandler(e),o.touchObject={},o.$slider.trigger("swipe",[o,t]))}else o.touchObject.startX!==o.touchObject.curX&&(o.slideHandler(o.currentSlide),o.touchObject={})},e.prototype.swipeHandler=function(i){var e=this;if(!(!1===e.options.swipe||"ontouchend"in document&&!1===e.options.swipe||!1===e.options.draggable&&-1!==i.type.indexOf("mouse")))switch(e.touchObject.fingerCount=i.originalEvent&&void 0!==i.originalEvent.touches?i.originalEvent.touches.length:1,e.touchObject.minSwipe=e.listWidth/e.options.touchThreshold,!0===e.options.verticalSwiping&&(e.touchObject.minSwipe=e.listHeight/e.options.touchThreshold),i.data.action){case"start":e.swipeStart(i);break;case"move":e.swipeMove(i);break;case"end":e.swipeEnd(i)}},e.prototype.swipeMove=function(i){var e,t,o,s,n,r,l=this;return n=void 0!==i.originalEvent?i.originalEvent.touches:null,!(!l.dragging||l.scrolling||n&&1!==n.length)&&(e=l.getLeft(l.currentSlide),l.touchObject.curX=void 0!==n?n[0].pageX:i.clientX,l.touchObject.curY=void 0!==n?n[0].pageY:i.clientY,l.touchObject.swipeLength=Math.round(Math.sqrt(Math.pow(l.touchObject.curX-l.touchObject.startX,2))),r=Math.round(Math.sqrt(Math.pow(l.touchObject.curY-l.touchObject.startY,2))),!l.options.verticalSwiping&&!l.swiping&&r>4?(l.scrolling=!0,!1):(!0===l.options.verticalSwiping&&(l.touchObject.swipeLength=r),t=l.swipeDirection(),void 0!==i.originalEvent&&l.touchObject.swipeLength>4&&(l.swiping=!0,i.preventDefault()),s=(!1===l.options.rtl?1:-1)*(l.touchObject.curX>l.touchObject.startX?1:-1),!0===l.options.verticalSwiping&&(s=l.touchObject.curY>l.touchObject.startY?1:-1),o=l.touchObject.swipeLength,l.touchObject.edgeHit=!1,!1===l.options.infinite&&(0===l.currentSlide&&"right"===t||l.currentSlide>=l.getDotCount()&&"left"===t)&&(o=l.touchObject.swipeLength*l.options.edgeFriction,l.touchObject.edgeHit=!0),!1===l.options.vertical?l.swipeLeft=e+o*s:l.swipeLeft=e+o*(l.$list.height()/l.listWidth)*s,!0===l.options.verticalSwiping&&(l.swipeLeft=e+o*s),!0!==l.options.fade&&!1!==l.options.touchMove&&(!0===l.animating?(l.swipeLeft=null,!1):void l.setCSS(l.swipeLeft))))},e.prototype.swipeStart=function(i){var e,t=this;if(t.interrupted=!0,1!==t.touchObject.fingerCount||t.slideCount<=t.options.slidesToShow)return t.touchObject={},!1;void 0!==i.originalEvent&&void 0!==i.originalEvent.touches&&(e=i.originalEvent.touches[0]),t.touchObject.startX=t.touchObject.curX=void 0!==e?e.pageX:i.clientX,t.touchObject.startY=t.touchObject.curY=void 0!==e?e.pageY:i.clientY,t.dragging=!0},e.prototype.unfilterSlides=e.prototype.slickUnfilter=function(){var i=this;null!==i.$slidesCache&&(i.unload(),i.$slideTrack.children(this.options.slide).detach(),i.$slidesCache.appendTo(i.$slideTrack),i.reinit())},e.prototype.unload=function(){var e=this;i(".slick-cloned",e.$slider).remove(),e.$dots&&e.$dots.remove(),e.$prevArrow&&e.htmlExpr.test(e.options.prevArrow)&&e.$prevArrow.remove(),e.$nextArrow&&e.htmlExpr.test(e.options.nextArrow)&&e.$nextArrow.remove(),e.$slides.removeClass("slick-slide slick-active slick-visible slick-current").attr("aria-hidden","true").css("width","")},e.prototype.unslick=function(i){var e=this;e.$slider.trigger("unslick",[e,i]),e.destroy()},e.prototype.updateArrows=function(){var i=this;Math.floor(i.options.slidesToShow/2),!0===i.options.arrows&&i.slideCount>i.options.slidesToShow&&!i.options.infinite&&(i.$prevArrow.removeClass("slick-disabled").attr("aria-disabled","false"),i.$nextArrow.removeClass("slick-disabled").attr("aria-disabled","false"),0===i.currentSlide?(i.$prevArrow.addClass("slick-disabled").attr("aria-disabled","true"),i.$nextArrow.removeClass("slick-disabled").attr("aria-disabled","false")):i.currentSlide>=i.slideCount-i.options.slidesToShow&&!1===i.options.centerMode?(i.$nextArrow.addClass("slick-disabled").attr("aria-disabled","true"),i.$prevArrow.removeClass("slick-disabled").attr("aria-disabled","false")):i.currentSlide>=i.slideCount-1&&!0===i.options.centerMode&&(i.$nextArrow.addClass("slick-disabled").attr("aria-disabled","true"),i.$prevArrow.removeClass("slick-disabled").attr("aria-disabled","false")))},e.prototype.updateDots=function(){var i=this;null!==i.$dots&&(i.$dots.find("li").removeClass("slick-active").end(),i.$dots.find("li").eq(Math.floor(i.currentSlide/i.options.slidesToScroll)).addClass("slick-active"))},e.prototype.visibility=function(){var i=this;i.options.autoplay&&(document[i.hidden]?i.interrupted=!0:i.interrupted=!1)},i.fn.slick=function(){var i,t,o=this,s=arguments[0],n=Array.prototype.slice.call(arguments,1),r=o.length;for(i=0;i<r;i++)if("object"==typeof s||void 0===s?o[i].slick=new e(o[i],s):t=o[i].slick[s].apply(o[i].slick,n),void 0!==t)return t;return o}});


/***/ }),

/***/ "jquery":
/*!*************************!*\
  !*** external "jQuery" ***!
  \*************************/
/***/ (function(module) {

"use strict";
module.exports = jQuery;

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be in strict mode.
!function() {
"use strict";
var __webpack_exports__ = {};
/*!*************************!*\
  !*** ./scripts/main.js ***!
  \*************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "jquery");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var slick_carousel_slick_slick_min_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! slick-carousel/slick/slick.min.js */ "../../node_modules/slick-carousel/slick/slick.min.js");
/* harmony import */ var slick_carousel_slick_slick_min_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(slick_carousel_slick_slick_min_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _util_Router__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./util/Router */ "./scripts/util/Router.js");
/* harmony import */ var _routes_common__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./routes/common */ "./scripts/routes/common.js");
/* harmony import */ var _routes_home__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./routes/home */ "./scripts/routes/home.js");
/* harmony import */ var _routes_about__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./routes/about */ "./scripts/routes/about.js");
/* provided dependency */ var jQuery = __webpack_require__(/*! jquery */ "jquery");
// import external dependencies



// Import everything from autoload


// import local dependencies





/** Populate Router instance with DOM routes */
var routes = new _util_Router__WEBPACK_IMPORTED_MODULE_2__["default"]({
  // All pages
  common: _routes_common__WEBPACK_IMPORTED_MODULE_3__["default"],
  // Home page
  home: _routes_home__WEBPACK_IMPORTED_MODULE_4__["default"],
  // About Us page, note the change from about-us to aboutUs.
  about: _routes_about__WEBPACK_IMPORTED_MODULE_5__["default"],
});

// Load Events
jQuery(document).ready(function () { return routes.loadEvents(); });

}();
// This entry need to be wrapped in an IIFE because it need to be in strict mode.
!function() {
"use strict";
/*!**************************!*\
  !*** ./styles/main.scss ***!
  \**************************/
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin

}();
/******/ })()
;
//# sourceMappingURL=main.js.map