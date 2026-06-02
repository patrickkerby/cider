<?php

namespace App;

/**
 * Add <body> classes
 */
add_filter('body_class', function (array $classes) {
    /** Add page slug if it doesn't exist */
    if (is_single() || is_page() && !is_front_page()) {
        if (!in_array(basename(get_permalink()), $classes)) {
            $classes[] = basename(get_permalink());
        }
    }

    /** Add class if sidebar is active */
    if (display_sidebar()) {
        $classes[] = 'sidebar-primary';
    }

    /** Clean up class names for custom templates */
    $classes = array_map(function ($class) {
        return preg_replace(['/-blade(-php)?$/', '/^page-template-views/'], '', $class);
    }, $classes);

    return array_filter($classes);
});

/**
 * Add "… Continued" to the excerpt
 */
add_filter('excerpt_more', function () {
    return ' &hellip; <a href="' . get_permalink() . '">' . __('Continued', 'sage') . '</a>';
});

/**
 * Template Hierarchy should search for .blade.php files
 */
collect([
    'index', '404', 'archive', 'author', 'category', 'tag', 'taxonomy', 'date', 'home',
    'frontpage', 'page', 'paged', 'search', 'single', 'singular', 'attachment', 'embed'
])->map(function ($type) {
    add_filter("{$type}_template_hierarchy", __NAMESPACE__.'\\filter_templates');
});

/**
 * Render page using Blade
 * Priority 999 ensures this runs AFTER WooCommerce's template_loader (which runs at 10)
 */
add_filter('template_include', function ($template) {
    collect(['get_header', 'wp_head'])->each(function ($tag) {
        ob_start();
        do_action($tag);
        $output = ob_get_clean();
        remove_all_actions($tag);
        add_action($tag, function () use ($output) {
            echo $output;
        });
    });
    $data = collect(get_body_class())->reduce(function ($data, $class) use ($template) {
        return apply_filters("sage/template/{$class}/data", $data, $template);
    }, []);
    if ($template) {
        echo template($template, $data);
        return get_stylesheet_directory().'/index.php';
    }
    return $template;
}, PHP_INT_MAX);

/**
 * Render comments.blade.php
 */
add_filter('comments_template', function ($comments_template) {
    $comments_template = str_replace(
        [get_stylesheet_directory(), get_template_directory()],
        '',
        $comments_template
    );

    $data = collect(get_body_class())->reduce(function ($data, $class) use ($comments_template) {
        return apply_filters("sage/template/{$class}/data", $data, $comments_template);
    }, []);

    $theme_template = locate_template(["views/{$comments_template}", $comments_template]);

    if ($theme_template) {
        echo template($theme_template, $data);
        return get_stylesheet_directory().'/index.php';
    }

    return $comments_template;
}, 100);

add_action('acf/init', 'App\my_acf_op_init');
function my_acf_op_init() {

    // Check function exists.
    if( function_exists('acf_add_options_page') ) {

        // Register options page.
        $option_page = acf_add_options_page(array(
            'page_title'    => __('Prairie Bears General Settings'),
            'menu_title'    => __('Prairie Bears Settings'),
            'menu_slug'     => 'theme-general-settings',
            'capability'    => 'edit_posts',
            'redirect'      => false
        ));
    }
}

/**
 * WooCommerce template location for Sage/Blade templates
 * This tells WooCommerce to look for templates in resources/views/woocommerce/
 */
add_filter('woocommerce_locate_template', function ($template, $template_name, $template_path) {
    $blade_template = str_replace('.php', '.blade.php', $template_name);
    
    // Check for Blade template in Sage's resources/views directory
    $possible_locations = [
        get_stylesheet_directory() . "/resources/views/woocommerce/{$blade_template}",
        get_stylesheet_directory() . "/resources/views/{$blade_template}",
    ];
    
    foreach ($possible_locations as $file) {
        if (file_exists($file)) {
            return $file;
        }
    }
    
    return $template;
}, 10, 3);

/**
 * Let WooCommerce know we have theme support and will handle templates
 * Don't return empty array - that causes fallback to default WP theme
 */
// REMOVED - This was causing the issue!


remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );

remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );    // Strip out the default linking so we can control the quickview
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );     // Strip out the default linking so we can control the quickview
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );           // No prices in thumbnail view plz
// remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );           // We need to add our own button in for the quick view

function pbc_product_loop_is_available(\WC_Product $product): bool
{
    return $product->is_purchasable() && ! pbc_product_loop_is_out_of_stock($product);
}

function pbc_loop_out_of_stock_badge(): void
{
    global $product;

    if (! $product instanceof \WC_Product || ! pbc_product_loop_is_out_of_stock($product)) {
        return;
    }

    echo '<span class="pbc-loop-stock-badge">' . esc_html__('Out of Stock', 'sage') . '</span>';
}

add_action('woocommerce_before_shop_loop_item_title', __NAMESPACE__ . '\\pbc_loop_out_of_stock_badge', 9);

// Setup for Product Modal Quickview
// WooCommerce only adds add_to_cart_button when purchasable + in stock. Production Quick View
// hooks that class, so Chai-style buttons (same href/data-product_id) skip the modal.
add_filter('quick_view_selector', function ($selector) {
    if (strpos($selector, ':not(.add_to_cart_button)') !== false) {
        return $selector;
    }

    return $selector . ', .product a.button:not(.add_to_cart_button):not(.quick-view-detail-button)';
});

add_filter('woocommerce_post_class', function ($classes, $product) {
    if ($product instanceof \WC_Product && ! pbc_product_loop_is_available($product)) {
        $classes[] = 'pbc-loop-quick-view-only';

        if (pbc_product_loop_is_out_of_stock($product)) {
            $classes[] = 'pbc-out-of-stock';
        }
    }

    return $classes;
}, 10, 2);

add_filter('woocommerce_loop_add_to_cart_args', function ($args, $product) {
    if (pbc_product_loop_is_available($product)) {
        return $args;
    }

    if (strpos($args['class'], 'add_to_cart_button') === false) {
        $args['class'] .= ' add_to_cart_button';
    }

    $args['class'] .= ' pbc-loop-quick-view-trigger';

    return $args;
}, 20, 2);

// Fallback when Quick View JS loses the race on iOS (href would otherwise navigate away).
add_filter('woocommerce_loop_add_to_cart_link', function ($link, $product) {
    if (pbc_product_loop_is_available($product)) {
        return $link;
    }

    $link = preg_replace('/\shref=(["\'])[^"\']*\1/', ' href="#"', $link, 1);

    if (strpos($link, 'role=') === false) {
        $link = preg_replace('/<a\s/', '<a role="button" ', $link, 1);
    }

    return $link;
}, 20, 2);
// remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );              // Get rid of sku and categories on product modal
/**
 * Remove related products output
 */
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

add_filter('woocommerce_product_add_to_cart_text', 'App\woocommerce_add_to_cart_button_text_archives');
function woocommerce_add_to_cart_button_text_archives()
{
    global $product;

    if ($product instanceof \WC_Product && pbc_product_loop_is_out_of_stock($product)) {
        return __('Out of Stock', 'sage');
    }

    return __('View Product', 'woocommerce');
}

add_action( 'woocommerce_after_shop_loop_item_title', 'App\pbc_shop_product_short_description', 35, 2 );
 
function pbc_shop_product_short_description() {
     the_excerpt();
}

// remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );


function add_custom_text_after_product_title(){
    $get_alc = get_field('alcohol_content');
    $get_vol = get_field('container_size');
    // $get_size = get_field('container_count');
    $alc = "";
    $vol = "";
    // $size = "";

    if ($get_alc) {
        $alc = ' / '.$get_alc;
    }

    // if ($get_size) {
    //     $size = $get_size.' / ';
    // }

    if ($get_vol) {
        $vol = $get_vol;
    }

    echo '<div class="cider_meta">'.$vol.$alc.'</div>';
}
add_action( 'woocommerce_single_product_summary', 'App\add_custom_text_after_product_title', 5);

// settings for product modal photo gallery. show bullets rather than thumbs. show prev and next arrows
add_filter( 'woocommerce_single_product_carousel_options', function( $options ) {
    $options['directionNav'] = true;
    $options['controlNav'] = true;
	return $options;
} );

/**
 * Cocktail Recipe Book — single product ID for cart/shipping rules.
 */
function pbc_cocktail_book_product_id() {
    return 679;
}

function pbc_is_cocktail_book_product( $product_id ) {
    $product_id = (int) $product_id;
    $book_id    = pbc_cocktail_book_product_id();

    if ( $product_id === $book_id ) {
        return true;
    }

    $parent_id = wp_get_post_parent_id( $product_id );

    return $parent_id && (int) $parent_id === $book_id;
}

function pbc_cart_contains_cider() {
    if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
        return false;
    }

    foreach ( WC()->cart->get_cart() as $cart_item ) {
        if ( has_term( 'cider', 'product_cat', $cart_item['product_id'] ) ) {
            return true;
        }
    }

    return false;
}

function pbc_cart_contains_cocktail_book() {
    if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
        return false;
    }

    foreach ( WC()->cart->get_cart() as $cart_item ) {
        if ( pbc_is_cocktail_book_product( $cart_item['product_id'] ) ) {
            return true;
        }
    }

    return false;
}

// Modal button for Cocktail Book Product
add_action('woocommerce_after_add_to_cart_button', function () {
    global $product;
    $product_id = method_exists($product, 'get_id') ? $product->get_id() : $product->id;

    if ( ! pbc_is_cocktail_book_product( $product_id ) ) {
        return;
    }

    echo '<div class="modal-trigger-container">
            <a class="modal-trigger" href="#excerpt-modal" data-modal="excerpt-modal">Read an excerpt</a>
            <a target="_blank" class="insta" href="https://www.instagram.com/cider.cocktails/">cider.cocktails</a>
        </div>';
} );

/**
 * Group cart notices and shipping guidance above the cart table.
 */
add_action( 'woocommerce_before_cart', function () {
    echo '<div class="cart-alerts" role="region" aria-label="' . esc_attr__( 'Cart notices', 'sage' ) . '">';
}, 9 );

add_action( 'woocommerce_before_cart', function () {
    if ( ! pbc_cart_contains_cocktail_book() ) {
        return;
    }

    $message = apply_filters( 'pbc_cart_shipping_notice_message', null );

    if ( null === $message ) {
        if ( pbc_cart_contains_cider() ) {
            $message = __(
                'The Cocktail Recipe Book ships Canada-wide. Cider in this order is for Edmonton-region delivery only — confirm your address in the calculator below.',
                'sage'
            );
        } else {
            $message = __( 'The Cocktail Recipe Book ships Canada-wide.', 'sage' );
        }
    }

    echo '<div class="shipping-notice" role="status">' . esc_html( $message ) . '</div>';
}, 12 );

add_action( 'woocommerce_before_cart', function () {
    echo '</div>';
}, 15 );

/**
 * Prompt for shipping address when cider is in the cart (queued before notices render).
 */
add_action( 'woocommerce_before_cart', function () {
    if ( ! pbc_cart_contains_cider() ) {
        return;
    }

    if ( ! apply_filters( 'pbc_show_cider_shipping_address_notice', true ) ) {
        return;
    }

    if ( ! empty( WC()->customer->get_shipping_postcode() ) ) {
        return;
    }

    wc_add_notice(
        __( 'Add your shipping address below to confirm Edmonton-region cider delivery.', 'sage' ),
        'notice'
    );
}, 8 );

/**
 * Switch to free local delivery in Edmonton Region when cart total is $50+
 * Changes flat rate shipping cost from $9 to $0
 */
add_filter('woocommerce_package_rates', 'App\adjust_edmonton_shipping_by_cart_total', 10, 2);
function adjust_edmonton_shipping_by_cart_total($rates, $package) {
    // Get cart subtotal
    $cart_subtotal = WC()->cart->subtotal;
    
    // Check if cart is $50 or more
    if ($cart_subtotal >= 50) {
        // Get the shipping zone for this package
        $zone = \WC_Shipping_Zones::get_zone_matching_package($package);
        $zone_name = $zone->get_zone_name();
        
        // If this is the Edmonton Region zone, make shipping free
        if (!empty($zone_name) && stripos($zone_name, 'Edmonton') !== false) {
            foreach ($rates as $rate_key => $rate) {
                // Set flat_rate cost to 0 for free local delivery
                if ('flat_rate' === $rate->method_id) {
                    $rates[$rate_key]->cost = 0;
                    $rates[$rate_key]->label = 'Free Local Delivery';
                    
                    // Also zero out any taxes
                    if (!empty($rates[$rate_key]->taxes)) {
                        foreach ($rates[$rate_key]->taxes as $key => $tax) {
                            $rates[$rate_key]->taxes[$key] = 0;
                        }
                    }
                }
            }
        }
    }
    
    return $rates;
}

add_action( 'woocommerce_check_cart_items', function () {
    $minimum_amount = 50;

    if ( ! pbc_cart_contains_cider() ) {
        return;
    }

    $postcode = WC()->customer->get_shipping_postcode();
    $city     = WC()->customer->get_shipping_city();
    $state    = WC()->customer->get_shipping_state();
    $country  = WC()->customer->get_shipping_country();

    if ( apply_filters( 'pbc_require_edmonton_zone_for_cider', true ) && ( ! empty( $postcode ) || ! empty( $city ) ) ) {
        $package = array(
            'destination' => array(
                'country'  => $country,
                'state'    => $state,
                'postcode' => $postcode,
                'city'     => $city,
            ),
        );

        $zone      = \WC_Shipping_Zones::get_zone_matching_package( $package );
        $zone_name = $zone->get_zone_name();

        if ( empty( $zone_name ) || stripos( $zone_name, 'Edmonton' ) === false ) {
            wc_add_notice(
                __( 'Cider products are only available for delivery within the Edmonton Region. Please remove cider from your cart or update your shipping address.', 'sage' ),
                'error'
            );
        }
    }

    if ( WC()->cart->subtotal < $minimum_amount ) {
        wc_add_notice(
            sprintf(
                __( 'A minimum total purchase amount of %s is required to checkout.', 'sage' ),
                wc_price( $minimum_amount )
            ),
            'error'
        );
    }
} );

add_filter( 'acf/fields/google_map/api', function($api) {
    $api['key'] = env('GOOGLEAPI');
    // Add Places API library for enhanced info windows
    $api['libraries'] = 'places';
    
    return $api;
} );

// Also ensure FacetWP map includes Places API
add_filter( 'facetwp_map_api_url', function( $api_url ) {
    // Add Places library to the API URL if not already present
    if ( strpos( $api_url, 'libraries=' ) === false ) {
        $api_url .= '&libraries=places';
    } elseif ( strpos( $api_url, 'places' ) === false ) {
        $api_url = str_replace( 'libraries=', 'libraries=places,', $api_url );
    }
    return $api_url;
});

// Additional hook to ensure Places API loads with FacetWP
add_filter( 'facetwp_map_settings', function( $settings ) {
    // Ensure Places library is included in map settings
    if ( !isset( $settings['libraries'] ) ) {
        $settings['libraries'] = 'places';
    } else {
        // Add places if not already included
        $libraries = explode( ',', $settings['libraries'] );
        if ( !in_array( 'places', $libraries ) ) {
            $libraries[] = 'places';
            $settings['libraries'] = implode( ',', $libraries );
        }
    }
    return $settings;
});

add_filter( 'facetwp_map_init_args', function ( $args ) {
 
  $args['init']['zoomControl']       = true; // +- zoom control
  $args['init']['mapTypeControl']    = false; // roadmap / satellite toggle
  $args['init']['streetViewControl'] = false; // street view / yellow man icon
  $args['init']['fullscreenControl'] = true; // full screen icon
  $args['init']['clickableIcons']    = false; // disable clicking on POIs (street names, neighborhoods, etc.)
  
  /** this overwrites all 4 lines above and will disable ALL of the default ui icons instead of the individual icons above */
  // $args['init']['disableDefaultUI']  = true; // disable the default ui
  
  return $args;
  
} );

add_filter( 'facetwp_map_init_args', function ( $args ) {

  if ( wp_is_mobile() ) {
    $args['init']['gestureHandling'] = 'greedy'; // Default: 'auto', other options: 'cooperative', 'greedy', 'none'a
  }
  else {
    $args['init']['gestureHandling'] = 'greedy'; // Default: 'auto', other options: 'cooperative', 'greedy', 'none'a
  }

  // $args['init']['gestureHandling'] = 'auto'; // Default: 'auto', other options: 'cooperative', 'greedy', 'none'a

  return $args;
} );

/**
 * Keep header cart count in sync after AJAX add/remove (matches a.cart-icon in layout).
 */
add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
    $fragments['a.cart-icon'] = pbc_cart_icon_html();
    return $fragments;
});