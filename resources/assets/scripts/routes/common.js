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

        // Robust loader for Google Places (supports both legacy namespace and importLibrary)
        var placesLoadInProgress = false;
        var placesLoadAttempts = 0;
        
        // Simple throttling for Places API calls (only for click events)
        var placesThrottle = {
          lastPlacesCall: 0,
          minClickInterval: 500, // Minimum 500ms between marker clicks to prevent double-clicks
          
          // Check if we should allow a Places API call
          shouldAllowPlacesCall: function() {
            var now = Date.now();
            var timeSinceLastCall = now - this.lastPlacesCall;
            
            // Enforce minimum interval between calls to prevent double-clicks
            if (timeSinceLastCall < this.minClickInterval) {
              return false;
            }
            
            this.lastPlacesCall = now;
            return true;
          },
        };
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
                  'business_status',
                ],
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
                'place_id',
              ],
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
          // Create completely new enhanced info window content (no original content to avoid duplicates)
          var content = '<div class="enhanced-info-window">';
          
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
            // Get current status - try multiple methods for timezone accuracy
            var isOpen = false;
            var statusText = 'Unknown';
            
            try {
              // First try the standard method
              if (typeof place.opening_hours.isOpen === 'function') {
                isOpen = place.opening_hours.isOpen();
              }
              
              // If we have periods, try to calculate manually for Alberta timezone
              if (place.opening_hours.periods && place.opening_hours.periods.length > 0) {
                // Create a date object in Alberta timezone (Mountain Time)
                var now = new Date();
                var albertaTime = new Date(now.toLocaleString('en-US', {timeZone: 'America/Edmonton'}));
                var currentDay = albertaTime.getDay(); // 0 = Sunday, 1 = Monday, etc.
                var currentTime = albertaTime.getHours() * 100 + albertaTime.getMinutes(); // HHMM format
                
                // Find today's periods
                var todayPeriods = place.opening_hours.periods.filter(function(period) {
                  return period.open && period.open.day === currentDay;
                });
                
                // Check if currently within any open period
                isOpen = todayPeriods.some(function(period) {
                  var openTime = period.open.time ? parseInt(period.open.time, 10) : 0;
                  var closeTime = period.close && period.close.time ? parseInt(period.close.time, 10) : 2359;
                  
                  // Handle overnight periods (close time is next day)
                  if (period.close && period.close.day !== period.open.day) {
                    return currentTime >= openTime || currentTime <= closeTime;
                  } else {
                    return currentTime >= openTime && currentTime <= closeTime;
                  }
                });
              }
            } catch (e) {
              console.log('Error checking opening hours:', e);
              // Fallback to showing just the hours without status
              statusText = 'Status Unknown';
            }
            
            if (statusText !== 'Status Unknown') {
              statusText = isOpen ? '<span style="color: green;">Open</span>' : '<span style="color: red;">Closed</span>';
            }
            
            content += '<p class="place-hours"><strong>Currently:</strong> ' + statusText + '</p>';
            
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
        
        // Remove automatic FacetWP event listeners - we ONLY want direct marker clicks
        // The FacetWP events might be firing during viewport changes causing performance issues
        
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
                  console.log('🔵 MARKER CLICKED - Loading Places data for: ' + marker._pbcBusinessName);
                  
                  // Simple throttling to prevent double-clicks
                  if (!placesThrottle.shouldAllowPlacesCall()) {
                    console.log('⚠️ Places API call throttled for:', marker._pbcBusinessName);
                    return;
                  }
                  
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
        
        // Only initialize once when map initially loads - NOT on viewport changes
        var placesInitialized = false;
        
        function initializePlacesOnce() {
          if (placesInitialized || (window.FWP_MAP && window.FWP_MAP._pbcPlacesReady)) {
            // console.log('🛑 Places already initialized, skipping');
            return;
          }
          
          console.log('🚀 Initializing Places integration (one-time only)');
          initGooglePlacesForFacetWP();
          addDirectClickListeners();
          placesInitialized = true;
          
          // Mark on FWP_MAP to prevent re-initialization
          if (window.FWP_MAP) {
            window.FWP_MAP._pbcPlacesReady = true;
          }
        }
        
        // Initialize after FacetWP map has loaded markers (initial load only)
        $(document).on('facetwp-maps-loaded', function() {
          setTimeout(initializePlacesOnce, 200);
        });
        
        // Fallback for edge cases where map is already present
        setTimeout(initializePlacesOnce, 1500);
        
        // Note: Map filtering has been disabled for better UX
        // The map will just display all markers without viewport-based filtering
        
        // Map filtering has been disabled - just display all markers
        // This provides the best user experience for map interaction
        
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
          const targetModal = $(this).data('modal') || $(this).attr('href').substring(1);
          $(`#${targetModal}`).addClass('active');
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